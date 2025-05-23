<?php
require_once '../bootstrap.php';
define('IS_ADMIN_PAGE', true);

if (!is_admin()) {
    redirect('../login.php?error=Unauthorized');
}

global $conn;
$all_reservations = [];
$sql = "SELECT reservation_id, received_at, status, name, email, phone, reservation_date, reservation_time, guests, requests 
        FROM reservations 
        ORDER BY received_at DESC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $all_reservations[] = $row;
    }
    $result->free();
} else {
    $_SESSION['admin_feedback'] = "Error fetching reservations: " . $conn->error;
    $_SESSION['admin_feedback_type'] = 'error';
}

include 'admin_header.php';
?>
    <title>Admin - View Reservations</title>
    <style>
        /* ... (your existing styles) ... */
        /* Add this if not already in admin_header.css or this page's style */
        .reservation-actions form {
            display: inline-block; /* Or block if you want them on new lines */
        }
         .btn-confirm-action { /* Can reuse from orders or make specific */
            background-color: #28a745; color: white; padding: 4px 8px; font-size: 12px;
            border: none; border-radius: 3px; cursor: pointer;
        }
        .btn-confirm-action:hover { background-color: #218838; }
    </style>

    <h2>View Reservation Requests</h2>

    <?php if (empty($all_reservations)): ?>
        <p class="no-data-message">No reservation requests found.</p>
    <?php else: ?>
        <table class="reservations-table">
            <thead>
                <tr>
                    <!-- ... (your table headers) ... -->
                    <th class="col-actions">Actions</th> <!-- ADD THIS HEADER -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_reservations as $res):
                    // Skip reservations already marked "Completed" or "Cancelled" (or "No-Show")
                    if (in_array(strtolower($res['status']), ['completed', 'cancelled', 'no-show'])) {
                        continue;
                    }
                    $status_class = 'status-' . str_replace(' ', '-', htmlspecialchars(ucfirst(strtolower($res['status']))));
                ?>
                    <tr>
                        <!-- ... (your table data cells for reservation details) ... -->
                        <td class="col-received"><?php echo htmlspecialchars($res['received_at']); ?></td>
                        <td class="col-status"><span class="<?php echo $status_class; ?>"><?php echo htmlspecialchars($res['status']); ?></span></td>
                        <td class="col-name"><?php echo htmlspecialchars($res['name']); ?></td>
                        <td class="col-date"><?php echo htmlspecialchars($res['reservation_date']); ?></td>
                        <td class="col-time"><?php echo htmlspecialchars(substr($res['reservation_time'], 0, 5)); ?></td>
                        <td class="col-guests" style="text-align:center;"><?php echo htmlspecialchars($res['guests']); ?></td>
                        <td class="col-contact">
                            <?php if(!empty($res['email'])) echo 'Email: '.htmlspecialchars($res['email']).'<br>'; ?>
                            Phone: <?php echo htmlspecialchars($res['phone']); ?>
                        </td>
                        <td class="col-requests"><div class="requests-text"><?php echo nl2br(htmlspecialchars($res['requests'] ?? 'None')); ?></div></td>
                        <td class="col-id"><?php echo htmlspecialchars($res['reservation_id']); ?></td>

                        <!-- ACTION BUTTONS FOR RESERVATION -->
                        <td class="col-actions reservation-actions">
                            <form action="update_reservation_status.php" method="post">
                                <input type="hidden" name="reservation_id" value="<?php echo htmlspecialchars($res['reservation_id']); ?>">
                                <input type="hidden" name="new_status" value="Completed"> <!-- Or "Honored" -->
                                <button type="submit" class="btn-confirm-action"
                                        onclick="return confirm('Mark reservation for <?php echo htmlspecialchars(addslashes($res['name'])); ?> as Completed?');">
                                    Mark Completed
                                </button>
                            </form>
                            <form action="update_reservation_status.php" method="post" style="margin-top: 5px;">
                                <input type="hidden" name="reservation_id" value="<?php echo htmlspecialchars($res['reservation_id']); ?>">
                                <input type="hidden" name="new_status" value="Cancelled">
                                <button type="submit" class="btn-admin-danger btn-admin-small"
                                        onclick="return confirm('CANCEL reservation for <?php echo htmlspecialchars(addslashes($res['name'])); ?>?');">
                                    Cancel
                                </button>
                            </form>
                             <form action="update_reservation_status.php" method="post" style="margin-top: 5px;">
                                <input type="hidden" name="reservation_id" value="<?php echo htmlspecialchars($res['reservation_id']); ?>">
                                <input type="hidden" name="new_status" value="No-Show">
                                <button type="submit" class="btn-admin-secondary btn-admin-small"
                                        onclick="return confirm('Mark reservation for <?php echo htmlspecialchars(addslashes($res['name'])); ?> as No-Show?');">
                                    No-Show
                                </button>
                            </form>
                        </td>
                        <!-- END ACTION BUTTONS -->
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

<?php
include 'admin_footer.php';
?>