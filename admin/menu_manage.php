<?php
require_once '../bootstrap.php'; // This now includes db_connect.php
define('IS_ADMIN_PAGE', true);

if (!is_admin()) {
    redirect('../login.php?error=Unauthorized');
}

// $menu_items = get_menu_items(); // This function now gets from DB
// The get_menu_items() function already returns items keyed by item_id,
// and can be ordered by category from the SQL query itself.

global $conn; // Use the global connection

$items_by_category = [];
$sql = "SELECT item_id, name, price, description, category, image_path
        FROM menu_items
        ORDER BY category, name"; // Order by category then name

$result = $conn->query($sql);

if ($result) {
    while ($item = $result->fetch_assoc()) {
        $category_name = $item['category'] ?? 'Uncategorized';
        // The item_id from the DB is the key for the item itself within its category
        $items_by_category[$category_name][$item['item_id']] = $item;
    }
    $result->free();
} else {
    // Handle error, maybe set a message
    $_SESSION['admin_feedback'] = "Error fetching menu items: " . $conn->error;
    $_SESSION['admin_feedback_type'] = 'error';
}


include 'admin_header.php';
?>
    <title>Admin - Manage Menu</title>
    <!-- ... rest of your head ... -->
    <style>
        /* ... your styles ... */
    </style>

    <h2>Manage Menu Items</h2>
    <p style="margin-bottom: 20px;">
        <a href="menu_edit.php" class="btn-admin-action">Add New Item</a>
    </p>

    <?php if (empty($items_by_category)): ?>
        <p class="no-data-message">No menu items found. Add one!</p>
    <?php else: ?>
        <?php foreach ($items_by_category as $category_name => $items_in_category): ?>
            <h3 style="margin-top: 30px; ..."><?php echo htmlspecialchars($category_name); ?></h3>
            <table class="menu-manage-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items_in_category as $item_id_from_db => $item): // $item_id_from_db is the key from DB ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item_id_from_db); ?></td>
                            <td>
                                <?php
                                $image_path_manage = $item['image_path'] ?? ''; // Use image_path from DB
                                $image_url_manage = ($image_path_manage && file_exists('../' . $image_path_manage)) ? '../' . htmlspecialchars($image_path_manage) : 'https://via.placeholder.com/80x50/eee/aaa?text=No+Img';
                                ?>
                                <img src="<?php echo $image_url_manage; ?>" alt="<?php echo htmlspecialchars($item['name'] ?? ''); ?>">
                            </td>
                            <td><?php echo htmlspecialchars($item['name'] ?? 'N/A'); ?></td>
                            <td>$<?php echo number_format($item['price'] ?? 0, 2); ?></td>
                            <td class="action-buttons-cell">
                                <a href="menu_edit.php?id=<?php echo urlencode($item_id_from_db); ?>" class="btn-admin-action btn-admin-small btn-admin-edit">Edit</a>
                                <!-- Delete form should point to menu_delete.php and pass item_id_from_db -->
                                <form action="menu_delete.php" method="get" style="display: block;">
                                    <input type="hidden" name="id" value="<?php echo urlencode($item_id_from_db); ?>">
                                    <button type="submit" class="btn-admin-danger btn-admin-small"
                                            onclick="return confirm('Are you sure you want to delete this item: \'<?php echo htmlspecialchars(addslashes($item['name'] ?? '')); ?>\'? This cannot be undone.');">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    <?php endif; ?>

<?php
include 'admin_footer.php';
?>