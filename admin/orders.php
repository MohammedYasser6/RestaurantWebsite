<?php
require_once __DIR__ . '/../bootstrap.php';
define('IS_ADMIN_PAGE', true);

if (!is_admin()) {
    redirect('../login.php?error=Unauthorized');
}

$all_orders_from_db = get_orders(); // This fetches all orders with their items

include 'admin_header.php';
?>
    <title>Admin - View Orders</title>
    <style>
        /* ... your existing styles for orders.php ... */
        .order-actions .btn-view-details {
            background-color: #007bff; /* Blue */
            color: white;
            padding: 5px 10px;
            font-size: 13px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none; /* For <a> tag */
            display: inline-block; /* For <a> tag */
            margin-right: 10px; /* Space before other buttons */
        }
        .order-actions .btn-view-details:hover {
            background-color: #0056b3;
        }
    </style>

    <h2>View Online Orders</h2>

    <?php if (empty($all_orders_from_db)): ?>
        <p class="no-data-message">No online orders found in the database.</p>
    <?php else: ?>
        <?php foreach ($all_orders_from_db as $order_id_from_db => $order_details):
              // Optional: Filter out already fulfilled/cancelled orders from this main list
              // if (in_array(strtolower($order_details['status']), ['fulfilled', 'cancelled'])) {
              //    continue;
              // }

              $display_order_id = htmlspecialchars($order_details['order_id']);
              // ... (all your other variable extractions for display) ...
        ?>
            <div class="order-card">
                 <h4>
                    Order Received: <?php echo htmlspecialchars($order_details['timestamp']); ?>
                    <span class="order-id">ID: <?php echo $display_order_id; ?></span>
                </h4>
                <div class="order-details">
                    <!-- ... (existing order details like status, customer info) ... -->
                    <p><strong>Status:</strong> <span class="status-<?php echo strtolower(str_replace(' ', '-', $order_details['status'])); ?>"><?php echo htmlspecialchars($order_details['status']); ?></span></p>
                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($order_details['customer_name']); ?> | <strong>Total: $<?php echo number_format($order_details['grand_total'], 2); ?></strong></p>
                    <!-- Keep this summary brief, more details on the dedicated page -->
                </div>

                <div class="order-actions">
                    <!-- "VIEW DETAILS" LINK ADDED HERE -->
                    <a href="order_details_view.php?order_id=<?php echo urlencode($display_order_id); ?>" class="btn-view-details">
                        View Details
                    </a>

                    <?php // Only show action buttons if order is actionable ?>
                    <?php if (!in_array(strtolower($order_details['status']), ['fulfilled', 'cancelled'])): ?>
                        <form action="update_order_status.php" method="post" style="display:inline;">
                            <input type="hidden" name="order_id" value="<?php echo $display_order_id; ?>">
                            <input type="hidden" name="new_status" value="Fulfilled">
                            <button type="submit" class="btn-confirm-action" 
                                    onclick="return confirm('Mark order #<?php echo $display_order_id; ?> as Fulfilled?');">
                                Mark Fulfilled
                            </button>
                        </form>
                        <form action="update_order_status.php" method="post" style="margin-left: 10px; display:inline;">
                            <input type="hidden" name="order_id" value="<?php echo $display_order_id; ?>">
                            <input type="hidden" name="new_status" value="Cancelled">
                            <button type="submit" class="btn-admin-danger btn-admin-small" 
                                    onclick="return confirm('CANCEL order #<?php echo $display_order_id; ?>?');">
                                Cancel Order
                            </button>
                        </form>
                    <?php else: ?>
                        <span style="font-style: italic; color: #555; font-size:13px;">Order is <?php echo htmlspecialchars($order_details['status']); ?></span>
                    <?php endif; ?>
                </div>
                <!-- Items and totals summary could be removed from here if desired, to keep list cleaner -->
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

<?php
include 'admin_footer.php';
?>