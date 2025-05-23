<?php
require_once 'bootstrap.php'; // Includes session start, DB connection, and functions

$menu_items_from_db = get_menu_items(); // Load menu from DB (keyed by item_id)

// --- Cart Logic (Initialize, Handle Actions, Feedback) ---
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions (add, update, remove, clear)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];
    $item_id_cart_action = $_POST['item_id'] ?? null;
    $feedback_type = 'success'; // Default feedback type
    $feedback_text = null;

    if ($action === 'clear') {
        $_SESSION['cart'] = [];
        $feedback_text = "Your order has been cleared.";
    } elseif ($item_id_cart_action !== null) {
        if (isset($menu_items_from_db[$item_id_cart_action])) { // Check against DB loaded items
            $item_name_for_feedback = htmlspecialchars($menu_items_from_db[$item_id_cart_action]['name']);
            switch ($action) {
                case 'add':
                    $quantity_to_add = isset($_POST['quantity']) ? filter_var($_POST['quantity'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : 1;
                    if ($quantity_to_add === false || $quantity_to_add < 1) $quantity_to_add = 1;
                    $_SESSION['cart'][$item_id_cart_action] = ($_SESSION['cart'][$item_id_cart_action] ?? 0) + $quantity_to_add;
                    $feedback_text = $item_name_for_feedback . " (x" . $quantity_to_add . ") added to your order.";
                    break;
                case 'update':
                    $new_quantity = isset($_POST['quantity']) ? filter_var($_POST['quantity'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) : false; // Allow 0 to remove
                    if ($new_quantity !== false) {
                        if ($new_quantity > 0) {
                            $_SESSION['cart'][$item_id_cart_action] = $new_quantity;
                            $feedback_text = $item_name_for_feedback . " quantity updated to " . $new_quantity . ".";
                        } else { // Quantity is 0 or invalid non-positive
                            unset($_SESSION['cart'][$item_id_cart_action]);
                            $feedback_text = $item_name_for_feedback . " removed from your order.";
                        }
                    } else {
                        $feedback_text = "Invalid quantity for " . $item_name_for_feedback . ".";
                        $feedback_type = 'error';
                    }
                    break;
                case 'remove':
                    if (isset($_SESSION['cart'][$item_id_cart_action])) {
                        unset($_SESSION['cart'][$item_id_cart_action]);
                        $feedback_text = $item_name_for_feedback . " removed from your order.";
                    }
                    break;
                default:
                    $feedback_text = "Unknown cart action.";
                    $feedback_type = 'error';
                    break;
            }
        } else {
            $feedback_text = "Sorry, the item (ID: " . htmlspecialchars($item_id_cart_action) . ") is not available.";
            $feedback_type = 'error';
        }
    } elseif (in_array($action, ['add', 'update', 'remove'])) { // Action requires item_id but not provided
        $feedback_text = "No item was specified for the action.";
        $feedback_type = 'error';
    }

    if ($feedback_text !== null) {
        $_SESSION['feedback'] = ["message" => $feedback_text, "type" => $feedback_type];
    }

    // Prevent form re-submission issues
    header("Location: order-online.php");
    exit();
}

// Retrieve and clear feedback message from session
$feedback_session = $_SESSION['feedback'] ?? null;
if ($feedback_session) {
    $feedback_message_display = $feedback_session['message'];
    $feedback_type_display = $feedback_session['type'];
    unset($_SESSION['feedback']);
}


// --- Group Menu Items by Category for Display ---
$items_by_category_display = [];
if (!empty($menu_items_from_db)) {
    foreach ($menu_items_from_db as $item_db) { // Iterate over values if $menu_items_from_db is already keyed
        $category = $item_db['category'] ?? 'Uncategorized';
        $items_by_category_display[$category][] = $item_db;
    }
}
// Define a preferred order for categories
$category_order_preference = ['Bowls', 'Salads', 'Main Dishes (Bowls)', 'Lunch / Other', 'Appetizers', 'Drinks', 'Desserts', 'Uncategorized'];
$sorted_items_by_category_display = [];
foreach ($category_order_preference as $cat_pref) {
    if (isset($items_by_category_display[$cat_pref])) {
        $sorted_items_by_category_display[$cat_pref] = $items_by_category_display[$cat_pref];
        unset($items_by_category_display[$cat_pref]); // Avoid duplication
    }
}
// Add any remaining categories not in the preference list
$sorted_items_by_category_display = array_merge($sorted_items_by_category_display, $items_by_category_display);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Online - Colibri Restaurant</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* --- Styles from previous order-online.php --- */
        .page-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 40px; background-color: #fff; border-bottom: 1px solid var(--border-color); margin-bottom: 30px; }
        .page-header .logo a { text-decoration: none; color: var(--primary-green); font-family: var(--font-serif); font-size: 24px; font-weight: 700; display: flex; align-items: center; }
        .page-header .logo .logo-icon { margin-right: 8px; }
        .page-header .user-actions a { margin-left: 15px; text-decoration: none; }
        .page-content { padding: 0 40px 80px 40px; max-width: 1300px; margin: 0 auto; }
        .page-content h1 { font-family: var(--font-serif); color: var(--primary-green); text-align: center; margin-bottom: 30px; font-size: 36px; }
        .page-content h2.category-title { font-family: var(--font-serif); color: var(--primary-green); text-align: left; font-size: 28px; margin-top: 40px; margin-bottom: 25px; border-bottom: 2px solid var(--primary-green); padding-bottom: 8px; }
        .back-link { display: block; text-align: center; margin-top: 40px; color: var(--primary-green); font-weight: 700; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        .order-layout { display: flex; flex-wrap: wrap; gap: 30px; } /* Reduced gap slightly */
        .menu-column { flex: 3; min-width: 300px; /* Adjusted min-width for better responsiveness */ }
        .cart-column { flex: 2; min-width: 300px; background-color: #f8f9fa; padding: 25px; border-radius: 8px; border: 1px solid var(--border-color); align-self: flex-start; position: sticky; top: 20px; max-height: calc(100vh - 40px); overflow-y: auto;}
        .order-menu-item { display: flex; align-items: flex-start; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color-light, #eee); } /* Use a lighter border */
        .order-menu-item:last-child { border-bottom: none; margin-bottom: 0; }
        .order-menu-item img.item-image { width: 100px; height: 80px; object-fit: cover; border-radius: 4px; margin-right: 20px; flex-shrink: 0; border: 1px solid #ddd; }
        .order-menu-item img.placeholder-img { background-color: #eee; display:flex; align-items: center; justify-content:center; color:#aaa; font-size: 12px; text-align: center; }
        .order-menu-item-details { flex-grow: 1; }
        .order-menu-item h3.item-name { font-size: 18px; font-family: var(--font-sans); font-weight: 700; color: var(--dark-text); margin: 0 0 6px 0; }
        .order-menu-item p.description { font-size: 13px; color: var(--secondary-text); margin-bottom: 8px; line-height: 1.5; }
        .order-menu-item span.price { font-size: 16px; font-weight: 700; color: var(--primary-green); display: block; margin-bottom: 10px; }
        .order-menu-item form.add-to-cart-form { margin-top: 5px; text-align: left; display: flex; align-items: center; gap: 10px;}
        .order-menu-item form.add-to-cart-form input[type="number"]{ width: 60px; padding: 6px 8px; font-size: 13px; border-radius: 4px; border: 1px solid #ccc; text-align: center;}
        .btn-sm { padding: 6px 15px; font-size: 13px; border-radius: 20px; }
        .cart-column h2.cart-title { font-size: 24px; text-align: center; border-bottom: none; margin-bottom: 20px; color: var(--primary-green); }
        .cart-item { display: grid; grid-template-columns: auto 1fr auto auto; gap: 10px 15px; /* row-gap column-gap */ align-items: center; font-size: 14px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px dotted #ccc; }
        .cart-item:last-child { border-bottom: none; }
        .cart-item-remove { grid-column: 1 / 2; }
        .cart-item-details { grid-column: 2 / 3; }
        .cart-item-qty { grid-column: 3 / 4; text-align: center; }
        .cart-item-line-total { grid-column: 4 / 5; min-width: 65px; text-align: right; font-weight: bold; color: var(--dark-text); }
        .cart-item-name { font-weight: bold; color: var(--dark-text); display: block; margin-bottom: 2px; }
        .cart-item-unit-price { font-size: 12px; color: var(--secondary-text); }
        .cart-item-qty input[type="number"].quantity-input { width: 60px; padding: 5px 6px; text-align: center; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; -moz-appearance: textfield; appearance: textfield; }
        .cart-item-qty input[type=number].quantity-input::-webkit-inner-spin-button, .cart-item-qty input[type=number].quantity-input::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        .cart-item-remove button.remove-btn { background: none; border: none; color: #e74c3c; font-size: 20px; /* Slightly larger */ cursor: pointer; padding: 0 5px; line-height: 1; transition: color 0.2s ease; }
        .cart-item-remove button.remove-btn:hover { color: #c0392b; }
        .cart-total { margin-top: 25px; padding-top: 15px; border-top: 2px solid var(--primary-green); text-align: right; font-size: 18px; font-weight: bold; }
        .cart-total strong { color: var(--primary-green); font-size: 20px; }
        .cart-actions { margin-top: 25px; display: flex; justify-content: space-between; align-items: center; }
        .cart-actions .btn { padding: 10px 20px; font-size: 15px; }
        .btn-danger { background-color: #e74c3c; color: white; border: none; }
        .btn-danger:hover { background-color: #c0392b; }
        a.btn-checkout.disabled, button.btn-checkout.disabled { background-color: #ccc !important; color: #666 !important; /* Darker grey for text */ cursor: not-allowed !important; pointer-events: none; border: 1px solid #bbb !important; }
        .feedback-message-inline { padding: 12px 20px; border-radius: 5px; margin-bottom: 25px; text-align: center; font-weight: 500; font-size: 14px; border: 1px solid transparent; }
        .feedback-message-inline.success { background-color: #d1e7dd; color: #0f5132; border-color: #badbcc; }
        .feedback-message-inline.error { background-color: #f8d7da; color: #721c24; border-color: #f5c2c7; }
        .feedback-message-inline.info { background-color: #cff4fc; color: #055160; border-color: #b6effb; }
        .feedback-message-inline.warning { background-color: #fff3cd; color: #664d03; border-color: #ffecb5;}
        .header-actions .btn-admin { background-color: var(--admin-color, #dc3545); border-color: var(--admin-color, #dc3545); color: white; }
        .header-actions .btn-admin:hover { background-color: var(--admin-color-hover, #c82333); border-color: var(--admin-color-hover, #bd2130); }
        .empty-cart-message { text-align: center; color: var(--secondary-text); padding: 20px; font-style: italic;}
    </style>
</head>
<body>
     <header class="page-header">
        <div class="logo"> <a href="index.php"> <span class="logo-icon">C</span> colibri </a> </div>
        <div class="user-actions">
            <?php if (is_logged_in()):
                $userForHeaderOrder = get_logged_in_user(); // Avoid var conflict
            ?>
                <?php if (is_admin()): ?> <a href="admin/index.php" class="btn btn-admin">ADMIN</a> <?php endif; ?>
                <a href="profile.php" class="btn btn-secondary">Profile (<?php echo htmlspecialchars($userForHeaderOrder['username'] ?? 'User'); ?>)</a>
                <a href="logout.php" class="btn btn-secondary-outline">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-secondary">Login</a>
                <a href="register.php" class="btn btn-primary">Register</a>
            <?php endif; ?>
        </div>
    </header>

     <main class="page-content">
        <h1>Order Online</h1>
        <?php if (isset($feedback_message_display)): ?>
            <div class="feedback-message-inline <?php echo htmlspecialchars($feedback_type_display ?? 'info'); ?>">
                <?php echo htmlspecialchars($feedback_message_display); ?>
            </div>
        <?php endif; ?>

        <div class="order-layout">
            <section class="menu-column">
                 <?php if (empty($menu_items_from_db)): ?>
                     <h2 class="category-title">Menu Items</h2>
                     <p>Our online menu is currently being updated. Please check back shortly!</p>
                 <?php else: ?>
                     <?php foreach ($sorted_items_by_category_display as $category_display_name => $items_in_category_display): ?>
                        <h2 class="category-title" id="category-<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $category_display_name))); ?>">
                            <?php echo htmlspecialchars($category_display_name); ?>
                        </h2>
                        <?php foreach ($items_in_category_display as $item_for_display):
                            $item_id_display = $item_for_display['item_id'];
                            $image_path_display_db = $item_for_display['image_path'] ?? null;
                            // Construct URL using IMAGE_UPLOAD_DIR_REL if image_path_display_db is just filename
                            // Assuming image_path_display_db is already like 'images/uploads/file.jpg'
                            $image_url_display = ($image_path_display_db && file_exists(__DIR__ . '/' . $image_path_display_db))
                                ? htmlspecialchars($image_path_display_db)
                                : 'https://via.placeholder.com/100x80/eee/aaa?text=No+Image';
                            $is_placeholder_display = !($image_path_display_db && file_exists(__DIR__ . '/' . $image_path_display_db));
                            $image_alt_display = htmlspecialchars($item_for_display['name']);
                        ?>
                            <div class="order-menu-item" id="item-<?php echo htmlspecialchars($item_id_display); ?>">
                                <img src="<?php echo $image_url_display; ?>" alt="<?php echo $image_alt_display; ?>"
                                     class="item-image <?php if ($is_placeholder_display) echo 'placeholder-img'; ?>">
                                <div class="order-menu-item-details">
                                    <h3 class="item-name"><?php echo htmlspecialchars($item_for_display['name']); ?></h3>
                                    <p class="description"><?php echo htmlspecialchars($item_for_display['description'] ?? ''); ?></p>
                                    <span class="price">$<?php echo number_format($item_for_display['price'], 2); ?></span>
                                    <form action="order-online.php" method="post" class="add-to-cart-form">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item_id_display); ?>">
                                        <input type="number" name="quantity" value="1" min="1" max="20" aria-label="Quantity for <?php echo htmlspecialchars($item_for_display['name']); ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">Add to Order</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                     <?php endforeach; ?>
                 <?php endif; ?>
            </section>

            <aside class="cart-column">
                 <h2 class="cart-title">Your Order</h2>
                 <?php
                 $cart_total_price = 0;
                 $cart_is_truly_empty = true; // Flag to check if cart has valid items after processing
                 if (!empty($_SESSION['cart'])):
                     foreach ($_SESSION['cart'] as $cart_item_id => $cart_item_quantity):
                         if (!isset($menu_items_from_db[$cart_item_id]) || $cart_item_quantity <= 0) {
                             unset($_SESSION['cart'][$cart_item_id]); // Clean up invalid cart items
                             continue; // Skip to next item
                         }
                         $cart_is_truly_empty = false; // Found at least one valid item
                         $cart_item_details = $menu_items_from_db[$cart_item_id];
                         $cart_line_total = $cart_item_details['price'] * $cart_item_quantity;
                         $cart_total_price += $cart_line_total;
                 ?>
                        <div class="cart-item">
                            <div class="cart-item-remove">
                                <form action="order-online.php" method="post">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($cart_item_id); ?>">
                                    <button type="submit" class="remove-btn" title="Remove <?php echo htmlspecialchars($cart_item_details['name']); ?>">×</button>
                                </form>
                            </div>
                            <div class="cart-item-details">
                                <span class="cart-item-name"><?php echo htmlspecialchars($cart_item_details['name']); ?></span>
                                <span class="cart-item-unit-price">($<?php echo number_format($cart_item_details['price'], 2); ?> each)</span>
                            </div>
                            <div class="cart-item-qty">
                                <form action="order-online.php" method="post">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($cart_item_id); ?>">
                                    <input type="number" name="quantity" value="<?php echo $cart_item_quantity; ?>" min="0" max="99"
                                           class="quantity-input" aria-label="Quantity for <?php echo htmlspecialchars($cart_item_details['name']); ?>"
                                           onchange="this.form.submit()">
                                </form>
                            </div>
                            <div class="cart-item-line-total">$<?php echo number_format($cart_line_total, 2); ?></div>
                        </div>
                    <?php endforeach; ?>
                 <?php endif; ?>

                 <?php if ($cart_is_truly_empty): ?>
                     <p class="empty-cart-message">Your order is currently empty.</p>
                 <?php else: ?>
                     <div class="cart-total">
                         Total: <strong>$<?php echo number_format($cart_total_price, 2); ?></strong>
                     </div>
                     <div class="cart-actions">
                         <form action="order-online.php" method="post" style="display: inline;">
                             <input type="hidden" name="action" value="clear">
                             <button type="submit" class="btn btn-danger btn-sm">Clear Order</button>
                         </form>
                         <a href="checkout.php" class="btn btn-primary btn-checkout <?php echo ($cart_total_price <= 0) ? 'disabled' : ''; ?>">
                             Proceed to Checkout
                         </a>
                     </div>
                 <?php endif; ?>
            </aside>
        </div>
        <a href="index.php" class="back-link">← Back to Homepage</a>
        <a href="menu.php" class="back-link" style="margin-top: 10px;">View Full Menu Details →</a>
    </main>
</body>
</html>