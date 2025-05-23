<?php
require_once 'bootstrap.php'; // Includes session start, DB connection, and functions

$menu_items = get_menu_items(); // Load menu from DB (keyed by item_id)

// --- Initialize messages ---
$error_message = '';
$success_message = '';

// --- Check Cart and Prepare Data ---
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    // If page is loaded directly without POST (e.g. bookmark) and cart is empty, redirect
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        $_SESSION['feedback'] = ["message" => "Your cart is empty. Please add items to your order first.", "type" => "info"];
        redirect('order-online.php');
    } else {
        // If it's a POST request (e.g. trying to submit empty form) but cart is empty
        $error_message = "Your cart appears to be empty. Cannot proceed with checkout.";
        $cart_items_for_summary = []; // Ensure this is initialized
        // No further processing needed if cart is empty on POST
    }
} else {
    $cart_items_for_summary = []; // For displaying in the summary
    $cart_items_for_db = [];    // For saving to order_items table
    $subtotal = 0.00;

    foreach ($_SESSION['cart'] as $item_id => $quantity) {
        if (isset($menu_items[$item_id]) && $quantity > 0) {
            $item = $menu_items[$item_id];
            $line_total = $item['price'] * $quantity;
            $subtotal += $line_total;

            // For display in summary (can be same as for DB if structure matches)
            $cart_items_for_summary[] = [
                'id' => $item_id, // menu_item_id
                'name' => $item['name'],
                'price' => $item['price'], // unit price
                'quantity' => $quantity,
                'line_total' => $line_total
            ];
            // For saving to DB (this structure matches what save_full_order expects for items)
            $cart_items_for_db[] = [
                'id' => $item_id,
                'name' => $item['name'],     // Name snapshot
                'price' => $item['price'],  // Price snapshot
                'quantity' => $quantity,
                'line_total' => $line_total
            ];
        } else {
            // Invalid item or quantity, remove from cart (could happen if menu changes)
            unset($_SESSION['cart'][$item_id]);
        }
    }

    if (empty($cart_items_for_summary)) { // If all items were invalid
        $_SESSION['feedback'] = ["message" => "Your cart contained invalid items, which have been removed. It is now empty.", "type" => "warning"];
        redirect('order-online.php');
    }

    // Define tax rate and delivery fee (consider making these configurable)
    $tax_rate = 0.10; // 10%
    $delivery_fee = 5.00;

    $tax = $subtotal * $tax_rate;
    $grand_total = $subtotal + $tax + $delivery_fee;
}


// --- Handle form submission ---
if (isset($cart_items_for_db) && !empty($cart_items_for_db) && $_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data
    $delivery_name = trim($_POST['delivery_name'] ?? '');
    $delivery_address1 = trim($_POST['delivery_address1'] ?? '');
    $delivery_address2 = trim($_POST['delivery_address2'] ?? ''); // Optional
    $delivery_city = trim($_POST['delivery_city'] ?? '');
    $delivery_postal = trim($_POST['delivery_postal'] ?? '');
    $delivery_phone = trim($_POST['delivery_phone'] ?? '');
    $delivery_email_form = trim($_POST['delivery_email'] ?? ''); // Add email field to your form

    $payment_method = $_POST['payment_method'] ?? '';
    $card_number = $_POST['card_number'] ?? ''; // DEMO ONLY
    // $card_expiry = $_POST['card_expiry'] ?? ''; // DEMO ONLY
    // $card_cvc = $_POST['card_cvc'] ?? '';     // DEMO ONLY

    $validation_passed = true; // Assume true, set to false on error

    // Basic Validation (add more as needed)
    if (empty($delivery_name) || empty($delivery_address1) || empty($delivery_city) || empty($delivery_postal) || empty($delivery_phone) || empty($delivery_email_form)) {
        $error_message = "All delivery fields (including email) are required.";
        $validation_passed = false;
    } elseif (!filter_var($delivery_email_form, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid delivery email format.";
        $validation_passed = false;
    } elseif (empty($payment_method)) {
        $error_message = "Please select a payment method.";
        $validation_passed = false;
    } elseif ($payment_method === 'card' && empty($card_number) ) { // Keep card validation simple for demo
        $error_message = "Card number is required for card payment (Demo).";
        $validation_passed = false;
    }
    // Add more validation: phone format, postal code format, etc.

    if ($validation_passed) {
        $order_id = 'ord_' . uniqid(); // Your existing order ID format
        $order_timestamp = date('Y-m-d H:i:s');
        $masked_card_num = ($payment_method === 'card') ? "****" . substr(preg_replace('/[^0-9]/', '', $card_number), -4) : null;

        $loggedInUser = get_logged_in_user();
        $user_id_for_db = $loggedInUser ? $loggedInUser['id'] : null;
        // Use form email for customer_email, as user might order for someone else or not be logged in
        $customer_email_for_db = $delivery_email_form;


        $order_data_for_db = [
            'order_id' => $order_id,
            'user_id' => $user_id_for_db,
            'timestamp' => $order_timestamp,
            'status' => 'New', // Default status
            'customer_name' => $delivery_name,
            'customer_address1' => $delivery_address1,
            'customer_address2' => $delivery_address2,
            'customer_city' => $delivery_city,
            'customer_postal_code' => $delivery_postal,
            'customer_phone' => $delivery_phone,
            'customer_email' => $customer_email_for_db,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'delivery_fee' => $delivery_fee,
            'grand_total' => $grand_total,
            'payment_method' => $payment_method,
            'payment_card_last4' => $masked_card_num,
            'payment_status' => ($payment_method === 'card' ? 'Paid (Simulated)' : 'Pending (Cash on Delivery)')
        ];

        // $cart_items_for_db is already prepared above

        if (save_full_order($order_data_for_db, $cart_items_for_db)) {
            $success_message = "Thank you, " . htmlspecialchars($delivery_name) . "! Your order (ID: #" . htmlspecialchars($order_id) . ") has been successfully placed. Your total is $" . number_format($grand_total, 2) . ".";
            if ($payment_method === 'cash') {
                $success_message .= " Please have the exact amount ready for cash on delivery.";
            } elseif ($masked_card_num) {
                $success_message .= " Your card ending in " . htmlspecialchars($masked_card_num) . " has been processed (Simulated).";
            }
            // Clear the cart and form data
            $_SESSION['cart'] = [];
            // $_POST = []; // Be careful with this, might clear other unrelated POST data if on a complex page.
                         // Better to unset specific POST variables or rely on PRG pattern (Post-Redirect-Get)
                         // For now, we'll rely on the success message display to prevent re-submission.
        } else {
            $error_message = "We encountered an error while trying to save your order. Please try again or contact customer support if the problem persists.";
            error_log("Failed to save order data to DB for order ID attempt: " . $order_id);
        }
    }
}

// Auto-fill form fields if user is logged in
$loggedInUser = get_logged_in_user();
$default_name = $_POST['delivery_name'] ?? ($loggedInUser ? $loggedInUser['username'] : '');
$default_email = $_POST['delivery_email'] ?? ($loggedInUser ? $loggedInUser['email'] : '');
// Add other fields if you store address for users (requires more DB tables and logic)
$default_address1 = $_POST['delivery_address1'] ?? '';
$default_address2 = $_POST['delivery_address2'] ?? '';
$default_city = $_POST['delivery_city'] ?? '';
$default_postal = $_POST['delivery_postal'] ?? '';
$default_phone = $_POST['delivery_phone'] ?? '';


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Colibri Restaurant</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* --- Paste Checkout CSS --- */
        .page-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 40px; background-color: #fff; border-bottom: 1px solid var(--border-color); margin-bottom: 30px; }
        .page-header .logo a { text-decoration: none; color: var(--primary-green); font-family: var(--font-serif); font-size: 24px; font-weight: 700; display: flex; align-items: center; }
        .page-header .logo .logo-icon { margin-right: 8px; }
        .page-header .user-actions a { margin-left: 15px; text-decoration: none; }
        .page-content { padding: 0 40px 40px 40px; max-width: 900px; margin: 0 auto; }
        .page-content h1 { font-family: var(--font-serif); color: var(--primary-green); text-align: center; margin-bottom: 40px; font-size: 36px; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: var(--primary-green); font-weight: 700; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        .checkout-container { display: grid; grid-template-columns: 1fr; gap: 30px; }
        @media (min-width: 768px) { .checkout-container { grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr); } } /* Adjusted ratio */
        .order-summary { background-color: #f8f9fa; border: 1px solid var(--border-color); border-radius: 8px; padding: 25px; align-self: flex-start; /* Keep summary at top */ }
        .order-summary h2 { font-family: var(--font-serif); color: var(--primary-green); margin-bottom: 20px; font-size: 22px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px; }
        .order-summary ul { list-style: none; padding: 0; margin-bottom: 15px; }
        .order-summary li { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; }
        .order-summary .item-name { flex-grow: 1; margin-right: 10px; }
        .order-summary .item-price { font-weight: 700; }
        .order-summary .totals-section { border-top: 1px dashed var(--border-color); padding-top: 15px; margin-top: 15px; }
        .order-summary .totals-section div { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
        .order-summary .total-label { }
        .order-summary .total-value { font-weight: 700; }
        .order-summary .grand-total { font-size: 18px; font-weight: bold; margin-top: 10px; color: var(--primary-green); }
        .checkout-form h2 { font-family: var(--font-serif); color: var(--primary-green); margin-bottom: 20px; font-size: 22px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px; }
        .checkout-form .form-group { margin-bottom: 18px; }
        .checkout-form label { display: block; margin-bottom: 6px; font-weight: 700; font-size: 14px; color: var(--secondary-text); }
        .checkout-form input[type="text"], .checkout-form input[type="email"], .checkout-form input[type="tel"] { width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 14px; font-family: var(--font-sans); box-sizing: border-box; }
        .checkout-form input:focus { outline: none; border-color: var(--primary-green); box-shadow: 0 0 0 2px rgba(74, 119, 41, 0.2); }
        .checkout-form button[type="submit"] { width: 100%; padding: 15px; background-color: var(--primary-green); color: white; border: none; border-radius: 8px; font-size: 18px; font-weight: 700; cursor: pointer; transition: background-color 0.3s ease; margin-top: 20px; }
        .checkout-form button[type="submit"]:hover { background-color: #3a5e20; }
        .success-message { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; line-height: 1.5; text-align: center; }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c2c7; padding: 10px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .payment-methods { margin-bottom: 25px; }
        .payment-option { display: flex; align-items: center; margin-bottom: 10px; background-color: #f8f9fa; border: 1px solid var(--border-color); padding: 10px 15px; border-radius: 8px; cursor: pointer; }
        .payment-option input[type="radio"] { margin-right: 12px; cursor: pointer; transform: scale(1.1); }
        .payment-option label { margin-bottom: 0; font-weight: normal; font-size: 15px; color: var(--dark-text); cursor: pointer; flex-grow: 1; }
        #card-details-section, #cash-details-section { border: 1px dashed #ccc; padding: 20px; margin-top: 15px; border-radius: 8px; background-color: #fafafa; display: none; }
        .security-warning { font-size: 12px; color: #dc3545; font-weight: bold; margin-top: 5px; display: block; }
        .cash-note { font-size: 14px; color: var(--secondary-text); }
        .header-actions .btn-admin { background-color: var(--admin-color, #dc3545); border-color: var(--admin-color, #dc3545); color: white; } /* Use CSS var if defined */
        .header-actions .btn-admin:hover { background-color: var(--admin-color-hover, #c82333); border-color: var(--admin-color-hover, #bd2130); }
        .checkout-form .half-width-container { display: flex; gap: 15px; }
        .checkout-form .half-width-container .form-group { flex: 1; }
    </style>
</head>
<body>
    <header class="page-header">
        <div class="logo"> <a href="index.php"> <span class="logo-icon">C</span> colibri </a> </div>
        <div class="user-actions">
            <?php if (is_logged_in()):
                $userForHeader = get_logged_in_user(); // Use a different var name to avoid conflict
            ?>
                <?php if (is_admin()): ?> <a href="admin/index.php" class="btn btn-admin">ADMIN</a> <?php endif; ?>
                <a href="profile.php" class="btn btn-secondary">Profile (<?php echo htmlspecialchars($userForHeader['username'] ?? 'User'); ?>)</a>
                <a href="logout.php" class="btn btn-secondary-outline">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-secondary">Login</a>
                <a href="register.php" class="btn btn-primary">Register</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="page-content">
        <h1>Checkout</h1>
        <?php if (!empty($error_message)): ?> <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div> <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?php echo $success_message; // Already HTML escaped if needed during creation ?></div>
            <a href="index.php" class="back-link">← Back to Homepage</a>
            <a href="order-online.php" class="back-link" style="margin-top: 10px;">Place Another Order →</a>
        <?php elseif (isset($cart_items_for_summary) && !empty($cart_items_for_summary)): ?>
            <div class="checkout-container">
                 <form class="checkout-form" action="checkout.php" method="post" id="checkout-form">
                     <h2>Delivery Details</h2>
                     <div class="form-group">
                         <label for="delivery_name">Full Name:</label>
                         <input type="text" id="delivery_name" name="delivery_name" required value="<?php echo htmlspecialchars($default_name); ?>">
                     </div>
                     <div class="form-group">
                        <label for="delivery_email">Email Address:</label>
                        <input type="email" id="delivery_email" name="delivery_email" required value="<?php echo htmlspecialchars($default_email); ?>">
                    </div>
                     <div class="form-group">
                         <label for="delivery_address1">Address Line 1:</label>
                         <input type="text" id="delivery_address1" name="delivery_address1" required value="<?php echo htmlspecialchars($default_address1); ?>">
                     </div>
                     <div class="form-group">
                         <label for="delivery_address2">Address Line 2 (Optional):</label>
                         <input type="text" id="delivery_address2" name="delivery_address2" value="<?php echo htmlspecialchars($default_address2); ?>">
                     </div>
                     <div class="half-width-container">
                        <div class="form-group">
                            <label for="delivery_city">City:</label>
                            <input type="text" id="delivery_city" name="delivery_city" required value="<?php echo htmlspecialchars($default_city); ?>">
                        </div>
                        <div class="form-group">
                            <label for="delivery_postal">Postal Code:</label>
                            <input type="text" id="delivery_postal" name="delivery_postal" required value="<?php echo htmlspecialchars($default_postal); ?>">
                        </div>
                     </div>
                     <div class="form-group">
                         <label for="delivery_phone">Phone Number:</label>
                         <input type="tel" id="delivery_phone" name="delivery_phone" required value="<?php echo htmlspecialchars($default_phone); ?>" placeholder="e.g., 555-123-4567">
                     </div>

                     <h2>Payment Method</h2>
                     <div class="payment-methods">
                         <div class="payment-option">
                             <input type="radio" id="payment_card" name="payment_method" value="card" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'card') ? 'checked' : ''; ?> required>
                             <label for="payment_card">Credit/Debit Card</label>
                         </div>
                         <div class="payment-option">
                             <input type="radio" id="payment_cash" name="payment_method" value="cash" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'cash') ? 'checked' : ''; ?> required>
                             <label for="payment_cash">Cash on Delivery</label>
                         </div>
                     </div>

                     <div id="card-details-section">
                         <h3>Card Details</h3>
                         <span class="security-warning">DEMO ONLY. Do not enter real card details!</span>
                         <div class="form-group">
                             <label for="card_number">Card Number:</label>
                             <input type="text" id="card_number" name="card_number" placeholder="•••• •••• •••• ••••" value="">
                         </div>
                         <!-- Add expiry and CVC if you want to simulate them, ensure they are NOT required for demo -->
                     </div>
                     <div id="cash-details-section">
                         <p class="cash-note">Please have the exact amount ready upon delivery.</p>
                     </div>

                     <button type="submit" name="place_order">Place Order ($<?php echo number_format($grand_total ?? 0, 2); ?>)</button>
                 </form>

                 <div class="order-summary">
                    <h2>Order Summary</h2>
                    <?php if (isset($cart_items_for_summary) && !empty($cart_items_for_summary)): ?>
                    <ul>
                        <?php foreach ($cart_items_for_summary as $item_in_summary): ?>
                        <li>
                            <span class="item-name"><?php echo htmlspecialchars($item_in_summary['name']); ?> (x<?php echo $item_in_summary['quantity']; ?>)</span>
                            <span class="item-price">$<?php echo number_format($item_in_summary['line_total'], 2); ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="totals-section">
                        <div> <span class="total-label">Subtotal:</span> <span class="total-value">$<?php echo number_format($subtotal ?? 0, 2); ?></span> </div>
                        <div> <span class="total-label">Taxes (<?php echo ($tax_rate ?? 0) * 100; ?>%):</span> <span class="total-value">$<?php echo number_format($tax ?? 0, 2); ?></span> </div>
                        <div> <span class="total-label">Delivery Fee:</span> <span class="total-value">$<?php echo number_format($delivery_fee ?? 0, 2); ?></span> </div>
                        <div class="grand-total">
                            <span class="total-label">Total Due:</span>
                            <span class="total-value">$<?php echo number_format($grand_total ?? 0, 2); ?></span>
                        </div>
                    </div>
                    <?php else: ?>
                        <p>Your cart is empty.</p>
                    <?php endif; ?>
                 </div>
            </div>
            <a href="order-online.php" class="back-link">← Back to Modify Order</a>
        <?php else: // Cart is empty, and no success message (likely direct access or error before form submission)
            if (empty($success_message) && empty($error_message)) { // Only show if no other message is present
                echo '<p style="text-align:center; font-size: 16px;">Your cart is currently empty or there was an issue loading your order details.</p>';
            }
            echo '<a href="order-online.php" class="back-link">← Go to Order Page</a>';
        endif; ?>
    </main>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentCardRadio = document.getElementById('payment_card');
        const paymentCashRadio = document.getElementById('payment_cash');
        const cardDetailsSection = document.getElementById('card-details-section');
        const cashDetailsSection = document.getElementById('cash-details-section');
        const cardInputs = cardDetailsSection.querySelectorAll('input');

        function togglePaymentDetails() {
            if (paymentCardRadio.checked) {
                cardDetailsSection.style.display = 'block';
                cashDetailsSection.style.display = 'none';
                // For demo, card inputs are not strictly required, adjust if needed
                // cardInputs.forEach(input => input.required = true);
            } else if (paymentCashRadio.checked) {
                cardDetailsSection.style.display = 'none';
                cashDetailsSection.style.display = 'block';
                cardInputs.forEach(input => input.required = false);
            } else { // Neither selected (should not happen with 'required' on radios)
                cardDetailsSection.style.display = 'none';
                cashDetailsSection.style.display = 'none';
                cardInputs.forEach(input => input.required = false);
            }
        }

        if (paymentCardRadio && paymentCashRadio && cardDetailsSection && cashDetailsSection) {
            paymentCardRadio.addEventListener('change', togglePaymentDetails);
            paymentCashRadio.addEventListener('change', togglePaymentDetails);
            // Initial call to set correct display based on pre-checked radio (if any)
            togglePaymentDetails();
        }
    });
    </script>
</body>
</html>