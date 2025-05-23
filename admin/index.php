<?php
require_once '../bootstrap.php'; // Go up one directory
define('IS_ADMIN_PAGE', true); // Flag for header include

// Access Control: Ensure user is admin
if (!is_admin()) {
    redirect('../login.php?error=Unauthorized');
}

// ---- Page Specific Logic (Example counts) ----
$order_count = count(get_orders());
$reservation_count = count(get_reservations());
$menu_item_count = count(get_menu_items());
// ---- End Page Specific Logic ----

include 'admin_header.php'; // Include the common admin header
?>
    <!-- Page Title (Unique to this page) -->
    <title>Admin Dashboard - Colibri</title>
    <!-- No need for <meta name="viewport"> here, it's in admin_header.php -->

    <h2>Dashboard Overview</h2>
    <p>Welcome to the admin control panel. Use the navigation above to manage the website content.</p>
    <ul style="list-style-type: disc; padding-left: 20px; font-size: 15px;">
        <li><a href="menu_manage.php">Manage Menu Items</a> (Total: <?php echo $menu_item_count; ?> items)</li>
        <li><a href="reservations.php">View Reservation Requests</a> (Total: <?php echo $reservation_count; ?> requests)</li>
        <li><a href="orders.php">View Online Orders</a> (Total: <?php echo $order_count; ?> orders)</li>
    </ul>
    <p><em>More detailed statistics or summaries could be added here later.</em></p>

<?php
include 'admin_footer.php'; // Include the common admin footer
?>