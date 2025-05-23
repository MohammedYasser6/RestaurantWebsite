<?php
require_once '../bootstrap.php';
if (!is_admin()) {
    $_SESSION['admin_feedback'] = "Unauthorized access.";
    $_SESSION['admin_feedback_type'] = 'error';
    redirect('reservations.php'); // Or login
}

global $conn;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id'], $_POST['new_status'])) {
    $reservation_id = trim($_POST['reservation_id']);
    $new_status = trim($_POST['new_status']);

    // Validate status against a list of allowed values
    $allowed_statuses = ['Completed', 'Confirmed', 'Pending', 'Cancelled', 'No-Show', 'Honored']; // Adjust as needed
    if (!in_array($new_status, $allowed_statuses)) {
        $_SESSION['admin_feedback'] = "Invalid status value ('".htmlspecialchars($new_status)."') provided.";
        $_SESSION['admin_feedback_type'] = 'error';
        redirect('reservations.php');
    }

    if (empty($reservation_id)) {
        $_SESSION['admin_feedback'] = "Reservation ID was not provided.";
        $_SESSION['admin_feedback_type'] = 'error';
        redirect('reservations.php');
    }

    $stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
    if ($stmt) {
        $stmt->bind_param("ss", $new_status, $reservation_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $_SESSION['admin_feedback'] = "Reservation for ID " . htmlspecialchars($reservation_id) . " status updated to '" . htmlspecialchars($new_status) . "'.";
                $_SESSION['admin_feedback_type'] = 'success';
            } else {
                $_SESSION['admin_feedback'] = "Reservation for ID " . htmlspecialchars($reservation_id) . " not found or status already set to '" . htmlspecialchars($new_status) . "'.";
                $_SESSION['admin_feedback_type'] = 'info';
            }
        } else {
            $_SESSION['admin_feedback'] = "Error updating reservation status: " . $stmt->error;
            $_SESSION['admin_feedback_type'] = 'error';
            error_log("Admin: Error updating reservation #{$reservation_id} status: " . $stmt->error);
        }
        $stmt->close();
    } else {
        $_SESSION['admin_feedback'] = "Database error preparing to update reservation status: " . $conn->error;
        $_SESSION['admin_feedback_type'] = 'error';
        error_log("Admin: Prepare failed for update_reservation_status: " . $conn->error);
    }
} else {
    $_SESSION['admin_feedback'] = "Invalid request to update reservation status.";
    $_SESSION['admin_feedback_type'] = 'error';
}

redirect('reservations.php');
?>