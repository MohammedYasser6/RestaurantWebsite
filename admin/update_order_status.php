<?php
require_once '../bootstrap.php';
// No define('IS_ADMIN_PAGE', true); here as it's not a display page, but still check admin status
if (!is_admin()) {
    // If accessed directly without being admin, or if session expired
    $_SESSION['admin_feedback'] = "Unauthorized access to update order status.";
    $_SESSION['admin_feedback_type'] = 'error';
    redirect('orders.php'); // Or redirect to login
}

global $conn;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['new_status'])) {
    $order_id = trim($_POST['order_id']);
    $new_status = trim($_POST['new_status']);

    // Basic validation for status (you might want a predefined list of allowed statuses)
    $allowed_statuses = ['Fulfilled', 'Processing', 'Shipped', 'Cancelled', 'New', 'Pending Payment']; // Add more as needed
    if (!in_array($new_status, $allowed_statuses)) {
        $_SESSION['admin_feedback'] = "Invalid status value provided.";
        $_SESSION['admin_feedback_type'] = 'error';
        redirect('orders.php');
    }

    if (empty($order_id)) {
        $_SESSION['admin_feedback'] = "Order ID was not provided.";
        $_SESSION['admin_feedback_type'] = 'error';
        redirect('orders.php');
    }

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    if ($stmt) {
        $stmt->bind_param("ss", $new_status, $order_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $_SESSION['admin_feedback'] = "Order #" . htmlspecialchars($order_id) . " status updated to '" . htmlspecialchars($new_status) . "'.";
                $_SESSION['admin_feedback_type'] = 'success';
            } else {
                $_SESSION['admin_feedback'] = "Order #" . htmlspecialchars($order_id) . " not found or status already set to '" . htmlspecialchars($new_status) . "'.";
                $_SESSION['admin_feedback_type'] = 'info';
            }
        } else {
            $_SESSION['admin_feedback'] = "Error updating order status: " . $stmt->error;
            $_SESSION['admin_feedback_type'] = 'error';
            error_log("Admin: Error updating order #{$order_id} status: " . $stmt->error);
        }
        $stmt->close();
    } else {
        $_SESSION['admin_feedback'] = "Database error preparing to update order status: " . $conn->error;
        $_SESSION['admin_feedback_type'] = 'error';
        error_log("Admin: Prepare failed for update_order_status: " . $conn->error);
    }
} else {
    $_SESSION['admin_feedback'] = "Invalid request to update order status.";
    $_SESSION['admin_feedback_type'] = 'error';
}

redirect('orders.php'); // Redirect back to the orders list
?>