<?php
require_once 'bootstrap.php'; // Includes session start, db_connect and DB functions

// $menu_items = get_menu_items(); // This now loads from DB, keyed by item_id
// The $items_by_category logic can be adapted or directly queried from DB with ordering.

global $conn;
$sorted_items_by_category = [];
$sql = "SELECT item_id, name, price, description, category, image_path FROM menu_items ORDER BY category, name";
$result = $conn->query($sql);

if ($result) {
    while ($item = $result->fetch_assoc()) {
        $category_name = $item['category'] ?? 'Uncategorized';
        $sorted_items_by_category[$category_name][] = $item; // Group by category
    }
    $result->free();
} else {
    // Potentially set an error message for the user
    error_log("Error fetching menu for public page: " . $conn->error);
}

// Category order preference (can be dynamic if you add a sort_order column to categories table later)
$category_order_preference = ['Main Dishes (Bowls)', 'Bowls', 'Salads', 'Lunch / Other', 'Appetizers', 'Drinks', 'Desserts', 'Uncategorized'];
$final_display_categories = [];
foreach($category_order_preference as $cat_pref) {
    if (isset($sorted_items_by_category[$cat_pref])) {
        $final_display_categories[$cat_pref] = $sorted_items_by_category[$cat_pref];
        unset($sorted_items_by_category[$cat_pref]); // Remove from original to avoid duplication
    }
}
// Add any remaining categories not in preference list
$final_display_categories = array_merge($final_display_categories, $sorted_items_by_category);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ... your head content ... -->
    <meta charset="UTF-8"> <!-- Good practice to include charset -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Essential for responsiveness -->
    <title>Our Menu - Colibri Restaurant</title>
    
    <!-- THIS IS MISSING -->
    <link rel="stylesheet" href="style.css"> 
    
    <!-- Any other links like Google Fonts should also be here -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@400;700&display=swap" rel="stylesheet">

    <!-- If you had inline <style> specific to menu.php, it would go here too -->
    <style>
        /* Example: from your other files, you had specific styles sometimes */
        .page-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 40px; background-color: #fff; border-bottom: 1px solid var(--border-color); margin-bottom: 30px; }
        .page-content { padding: 0 40px 40px 40px; max-width: 1200px; margin: 0 auto; }
        .page-content h1 { font-family: var(--font-serif); color: var(--primary-green); text-align: center; margin-bottom: 40px; font-size: 36px; }
        .page-content h2 { font-family: var(--font-serif); color: var(--primary-green); text-align: left; font-size: 28px; margin-top: 50px; margin-bottom: 25px; border-bottom: 2px solid var(--primary-green); padding-bottom: 8px; }
        .menu-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 35px; }
        .menu-item { background-color: #ffffff; border-radius: 8px; border: 1px solid var(--border-color); padding: 25px; text-align: center; position: relative; box-shadow: 0 3px 10px rgba(0,0,0,0.05); transition: transform 0.3s ease, box-shadow 0.3s ease; display: flex; flex-direction: column; }
        .menu-item:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        .menu-item img { max-width: 100%; height: 200px; object-fit: cover; border-radius: 4px; margin-bottom: 15px; }
        .placeholder-img { background-color: #eee; display:flex; align-items: center; justify-content:center; color:#aaa; font-size: 12px; text-align: center; }
        .menu-item h3 { font-family: var(--font-serif); font-size: 22px; color: var(--primary-green); margin-bottom: 10px; }
        .menu-item p { font-size: 14px; color: var(--secondary-text); line-height: 1.6; margin-bottom: auto; padding-bottom: 15px; flex-grow: 1; }
        .menu-item .price-display { font-size: 16px; font-weight: 700; color: var(--primary-green); margin-top: 10px; }
        .back-link { display: block; text-align: center; margin-top: 40px; color: var(--primary-green); font-weight: 700; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        .order-now-button { text-align: center; margin-top: 40px; }
    </style>
    <title>Our Menu - Colibri Restaurant</title>
</head>
<body>
    <header class="page-header">
        <!-- ... your header content ... -->
    </header>

     <main class="page-content">
        <h1>Our Full Menu</h1>
        <?php if (empty($final_display_categories)): ?>
            <p style="text-align: center;">Our menu is currently being updated. Please check back soon!</p>
        <?php else: ?>
            <?php foreach ($final_display_categories as $category => $items): ?>
                 <section id="<?php echo htmlspecialchars(strtolower(str_replace([' ', '(', ')', '/'], '-', $category))); ?>">
                     <h2><?php echo htmlspecialchars($category); ?></h2>
                     <div class="menu-grid">
                         <?php foreach ($items as $item): // $item is now an associative array directly from DB query
                             $image_path_from_db = $item['image_path'] ?? null; // Use 'image_path'
                             $image_url = ($image_path_from_db && file_exists(__DIR__ . '/' . $image_path_from_db)) ? htmlspecialchars($image_path_from_db) : 'https://via.placeholder.com/300x200/eee/aaa?text=No+Image';
                             $is_placeholder = !($image_path_from_db && file_exists(__DIR__ . '/' . $image_path_from_db));
                             $image_alt = htmlspecialchars($item['name']);
                         ?>
                             <article class="menu-item">
                                 <img src="<?php echo $image_url; ?>" alt="<?php echo $image_alt; ?>" <?php if ($is_placeholder) echo 'class="placeholder-img"'; ?>>
                                 <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                 <p><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
                                 <div class="price-display">$<?php echo number_format($item['price'], 2); ?></div>
                             </article>
                         <?php endforeach; ?>
                     </div>
                 </section>
            <?php endforeach; ?>
        <?php endif; ?>
        <!-- ... rest of your page ... -->
    </main>
</body>
</html>