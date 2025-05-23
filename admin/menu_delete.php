<?php
require_once '../bootstrap.php';
define('IS_ADMIN_PAGE', true);

if (!is_admin()) {
    redirect('../login.php?error=Unauthorized');
}

global $conn;
$item_id_to_delete = $_GET['id'] ?? null;

if (!$item_id_to_delete) {
    $_SESSION['admin_feedback'] = "No item ID specified for deletion.";
    $_SESSION['admin_feedback_type'] = 'error';
    redirect('menu_manage.php');
}

// With ON DELETE CASCADE, we no longer need the PHP check for existing orders.
// The database will handle deleting related order_items.

// Get item details (name and image_path) to delete its image file
$stmt_get_item_details = $conn->prepare("SELECT name, image_path FROM menu_items WHERE item_id = ?");
if (!$stmt_get_item_details) {
    $_SESSION['admin_feedback'] = "Database error preparing to get item details: " . $conn->error;
    $_SESSION['admin_feedback_type'] = 'error';
    error_log("Admin menu_delete: Prepare failed for get_item_details: " . $conn->error);
    redirect('menu_manage.php');
}

$stmt_get_item_details->bind_param("s", $item_id_to_delete);
$stmt_get_item_details->execute();
$result_get_details = $stmt_get_item_details->get_result();
$item_to_delete_details = $result_get_details->fetch_assoc();
$stmt_get_item_details->close();

if (!$item_to_delete_details) {
    $_SESSION['admin_feedback'] = "Item with ID '" . htmlspecialchars($item_id_to_delete) . "' not found (or already deleted).";
    $_SESSION['admin_feedback_type'] = 'error';
    redirect('menu_manage.php');
}

$deleted_item_name = $item_to_delete_details['name'] ?? $item_id_to_delete;
$image_to_delete_db_path = $item_to_delete_details['image_path'] ?? null;

// Attempt to delete the record from the menu_items table.
// The database will automatically delete related order_items due to ON DELETE CASCADE.
$stmt_delete_item = $conn->prepare("DELETE FROM menu_items WHERE item_id = ?");
if (!$stmt_delete_item) {
    $_SESSION['admin_feedback'] = "Database error preparing to delete item: " . $conn->error;
    $_SESSION['admin_feedback_type'] = 'error';
    error_log("Admin menu_delete: Prepare failed for delete_item: " . $conn->error);
    redirect('menu_manage.php');
}

$stmt_delete_item->bind_param("s", $item_id_to_delete);

if ($stmt_delete_item->execute()) {
    $rows_affected = $stmt_delete_item->affected_rows;
    $stmt_delete_item->close();

    if ($rows_affected > 0) {
        $_SESSION['admin_feedback'] = "Item '" . htmlspecialchars($deleted_item_name) . "' (ID: " . htmlspecialchars($item_id_to_delete) . ") and its associated order line items have been deleted from the database.";
        $_SESSION['admin_feedback_type'] = 'success';

        // Delete the associated image file
        if ($image_to_delete_db_path) {
            $absolute_image_path_to_delete = __DIR__ . '/../' . $image_to_delete_db_path;
            if (file_exists($absolute_image_path_to_delete) && strpos(strtolower($image_to_delete_db_path), 'placeholder.com') === false) {
                if (!@unlink($absolute_image_path_to_delete)) {
                    $_SESSION['admin_feedback'] .= " Note: Failed to delete the associated image file: " . htmlspecialchars(basename($image_to_delete_db_path)) . ".";
                    error_log("Admin menu_delete: Could not delete image file: " . $absolute_image_path_to_delete);
                } else {
                    error_log("Admin menu_delete: Successfully deleted image file: " . $absolute_image_path_to_delete);
                }
            }
        }
    } else {
        $_SESSION['admin_feedback'] = "Item '" . htmlspecialchars($deleted_item_name) . "' was not found in the database.";
        $_SESSION['admin_feedback_type'] = 'info';
    }
} else {
    $_SESSION['admin_feedback'] = "Error deleting item '" . htmlspecialchars($deleted_item_name) . "' from database: " . $stmt_delete_item->error;
    $_SESSION['admin_feedback_type'] = 'error';
    error_log("Admin menu_delete: Error executing delete for item ID " . $item_id_to_delete . ": " . $stmt_delete_item->error);
    if ($stmt_delete_item) $stmt_delete_item->close();
}

redirect('menu_manage.php');
?>