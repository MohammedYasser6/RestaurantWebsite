<?php
require_once __DIR__ . '/../bootstrap.php';
define('IS_ADMIN_PAGE', true);

if (!is_admin()) {
    redirect('../login.php?error=Unauthorized');
}

$order_id_to_view = $_GET['order_id'] ?? null;

if (!$order_id_to_view) {
    $_SESSION['admin_feedback'] = "No order ID specified to view.";
    $_SESSION['admin_feedback_type'] = 'error';
    redirect('orders.php');
}

// Use the existing get_orders() function, passing the specific order_id
// This function should return an array containing the single order, or an empty array if not found.
// The structure is: $single_order_array[$order_id_to_view] = ['order_details_and_items']
$order_data_array = get_orders($order_id_to_view);

if (empty($order_data_array) ) {
    $_SESSION['admin_feedback'] = "Order with ID '" . htmlspecialchars($order_id_to_view) . "' not found.";
    $_SESSION['admin_feedback_type'] = 'error';
    redirect('orders.php');
}

// Since get_orders($id) might return an array keyed by the ID, extract the single order
$order = $order_data_array; // If get_orders($id) directly returns the single order array
                            // If get_orders($id) returns [$id => $order_details], then:
                            // $order = $order_data_array[$order_id_to_view] ?? null;
                            // if (!$order) { /* handle not found again, though get_orders should make it empty array*/ }


include 'admin_header.php';
?>
<title>Order Details - #<?php echo htmlspecialchars($order['order_id']); ?> - Admin</title>
<style>
    /* Styles for the order detail view page */
    .order-detail-container {
        background-color: #fff;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .order-detail-container h2 {
        margin-top: 0;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }
    .order-detail-section {
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px dotted #ddd;
    }
    .order-detail-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    .order-detail-section h3 {
        font-size: 18px;
        color: var(--primary-green);
        margin-bottom: 10px;
    }
    .order-detail-section p {
        margin: 5px 0;
        font-size: 14px;
        line-height: 1.6;
    }
    .order-detail-section strong {
        color: #333;
        min-width: 120px; /* Adjust for alignment */
        display: inline-block;
    }
    .order-items-detail-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        font-size: 14px;
    }
    .order-items-detail-table th, .order-items-detail-table td {
        border: 1px solid #e0e0e0;
        padding: 8px 10px;
        text-align: left;
    }
    .order-items-detail-table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    .order-items-detail-table td.qty, .order-items-detail-table td.price, .order-items-detail-table td.line-total {
        text-align: right;
    }
    .order-totals-summary {
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #ccc;
    }
    .order-totals-summary p { text-align: right; font-size: 14px; margin: 5px 0; }
    .order-totals-summary p.grand-total-line { font-size: 16px; font-weight: bold; color: var(--primary-green); }

    .status-update-form-detailview { margin-top: 20px; padding-top:15px; border-top:1px solid #eee;}
    .status-update-form-detailview label { font-weight:bold; margin-right:10px;}
    .status-update-form-detailview select { padding: 5px; border-radius:3px; border:1px solid #ccc; margin-right:10px;}
    .status-update-form-detailview button { padding: 5px 12px;}

</style>

<div class="order-detail-container">
    <h2>Order Details <span style="font-family: monospace; color: #555; font-size: 16px;">#<?php echo htmlspecialchars($order['order_id']); ?></span></h2>

    <div class="order-detail-section customer-info">
        <h3>Customer & Delivery Information</h3>
        <p><strong>Order Placed:</strong> <?php echo htmlspecialchars($order['timestamp']); ?></p>
        <p><strong>Current Status:</strong> <span class="status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>"><?php echo htmlspecialchars($order['status']); ?></span></p>
        <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
        <p><strong>Address Line 1:</strong> <?php echo htmlspecialchars($order['customer_address1']); ?></p>
        <?php if (!empty($order['customer_address2'])): ?>
            <p><strong>Address Line 2:</strong> <?php echo htmlspecialchars($order['customer_address2']); ?></p>
        <?php endif; ?>
        <p><strong>City:</strong> <?php echo htmlspecialchars($order['customer_city']); ?></p>
        <p><strong>Postal Code:</strong> <?php echo htmlspecialchars($order['customer_postal_code']); ?></p>
    </div>

    <div class="order-detail-section payment-info">
        <h3>Payment Information</h3>
        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars(ucfirst($order['payment_method'])); ?></p>
        <?php if ($order['payment_method'] === 'card' && !empty($order['payment_card_last4'])): ?>
            <p><strong>Card (Last 4):</strong> **** <?php echo htmlspecialchars($order['payment_card_last4']); ?></p>
        <?php endif; ?>
        <p><strong>Payment Status:</strong> <span class="payment-<?php echo strtolower(explode(' ', ($order['payment_status'] ?? ''))[0]); ?>"><?php echo htmlspecialchars($order['payment_status']); ?></span></p>
    </div>

    <div class="order-detail-section items-ordered">
        <h3>Items Ordered</h3>
        <?php if (!empty($order['items']) && is_array($order['items'])): ?>
            <table class="order-items-detail-table">
                <thead>
                    <tr>
                        <th>Menu Item ID</th>
                        <th>Item Name (at time of order)</th>
                        <th class="qty">Qty</th>
                        <th class="price">Unit Price</th>
                        <th class="line-total">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order['items'] as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['menu_item_id']); ?></td>
                            <td><?php echo htmlspecialchars($item['item_name_snapshot']); ?></td>
                            <td class="qty"><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td class="price">$<?php echo number_format($item['item_price_snapshot'], 2); ?></td>
                            <td class="line-total">$<?php echo number_format($item['line_total'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No items found for this order or items could not be loaded.</p>
        <?php endif; ?>
    </div>

    <div class="order-detail-section order-totals-summary">
        <h3>Order Totals</h3>
        <p><strong>Subtotal:</strong> $<?php echo number_format($order['subtotal'], 2); ?></p>
        <p><strong>Tax:</strong> $<?php echo number_format($order['tax'], 2); ?></p>
        <p><strong>Delivery Fee:</strong> $<?php echo number_format($order['delivery_fee'], 2); ?></p>
        <p class="grand-total-line"><strong>Grand Total:</strong> $<?php echo number_format($order['grand_total'], 2); ?></p>
    </div>

    <?php // Optional: Form to update status directly on this page ?>
    <?php if (!in_array(strtolower($order['status']), ['fulfilled', 'cancelled'])): ?>
    <div class="order-detail-section status-update-form-detailview">
        <h3>Update Order Status</h3>
        <form action="update_order_status.php" method="post">
            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
            <label for="new_status_select">New Status:</label>
            <select name="new_status" id="new_status_select">
                <option value="Processing" <?php if(strtolower($order['status']) == 'processing') echo 'selected'; ?>>Processing</option>
                <option value="Shipped" <?php if(strtolower($order['status']) == 'shipped') echo 'selected'; ?>>Shipped (Out for Delivery)</option>
                <option value="Fulfilled" <?php if(strtolower($order['status']) == 'fulfilled') echo 'selected'; ?>>Fulfilled (Completed)</option>
                <option value="Cancelled" <?php if(strtolower($order['status']) == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                <!-- Add other relevant statuses -->
            </select>
            <button type="submit" class="btn-admin-action">Update Status</button>
        </form>
    </div>
    <?php endif; ?>


    <p style="margin-top: 30px;">
        <a href="orders.php" class="btn-admin-secondary">« Back to All Orders</a>
    </p>

</div>

<?php
include 'admin_footer.php';
?>