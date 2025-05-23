<?php
require_once 'bootstrap.php'; // Includes session start and functions

// --- Define Dessert Menu Items ---
$menu_items = [
    'DES01' => ['id' => 'DES01', 'name' => 'Chocolate Lava Cake', 'price' => 8.00, 'description' => 'Warm chocolate cake with a molten center, served with vanilla ice cream.', 'image' => 'https://picsum.photos/seed/dessert1/300/200', 'category' => 'Desserts'],
    'DES02' => ['id' => 'DES02', 'name' => 'Classic Cheesecake', 'price' => 7.50, 'description' => 'Creamy New York style cheesecake with a graham cracker crust, berry coulis.', 'image' => 'https://picsum.photos/seed/dessert2/300/200', 'category' => 'Desserts'],
    'DES03' => ['id' => 'DES03', 'name' => 'Seasonal Fruit Tart', 'price' => 7.00, 'description' => 'Buttery tart shell filled with pastry cream and topped with fresh seasonal fruits.', 'image' => 'https://picsum.photos/seed/dessert3/300/200', 'category' => 'Desserts'],
    'DES04' => ['id' => 'DES04', 'name' => 'Tiramisu', 'price' => 8.50, 'description' => 'Layers of coffee-soaked ladyfingers and rich mascarpone cream, dusted with cocoa.', 'image' => 'https://picsum.photos/seed/dessert4/300/200', 'category' => 'Desserts'],
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desserts Menu - Colibri</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* --- Reusable Menu Page Styles --- */
        .page-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 40px; background-color: #fff; border-bottom: 1px solid var(--border-color); margin-bottom: 30px; }
        .page-header .logo a { text-decoration: none; color: var(--primary-green); font-family: var(--font-serif); font-size: 24px; font-weight: 700; display: flex; align-items: center; }
        .page-header .logo .logo-icon { margin-right: 8px; }
        .page-header .user-actions a { margin-left: 15px; text-decoration: none; }
        .page-content { padding: 0 40px 40px 40px; max-width: 1200px; margin: 0 auto; }
        .page-content h1 { font-family: var(--font-serif); color: var(--primary-green); text-align: center; margin-bottom: 40px; font-size: 36px; }
        .menu-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 35px; }
        .menu-item { background-color: #ffffff; border-radius: 8px; border: 1px solid var(--border-color); padding: 25px; text-align: center; position: relative; box-shadow: 0 3px 10px rgba(0,0,0,0.05); transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .menu-item:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        .menu-item img { max-width: 100%; height: 200px; object-fit: cover; border-radius: 4px; margin-bottom: 15px; }
        .menu-item-price { position: absolute; top: 15px; left: 15px; background-color: rgba(255, 255, 255, 0.9); color: var(--primary-green); font-weight: 700; padding: 5px 12px; border-radius: 15px; font-size: 13px; }
        .menu-item h3 { font-family: var(--font-serif); font-size: 22px; color: var(--primary-green); margin-bottom: 10px; }
        .menu-item p { font-size: 14px; color: var(--secondary-text); line-height: 1.6; margin-bottom: 0; }
        .back-link { display: block; text-align: center; margin-top: 40px; color: var(--primary-green); font-weight: 700; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <header class="page-header">
        <div class="logo">
            <a href="index.php">
                <span class="logo-icon">C</span> colibri
            </a>
        </div>
        <div class="user-actions">
            <?php if (is_logged_in()): $user = get_logged_in_user(); ?>
                <a href="profile.php" class="btn btn-secondary">Profile (<?php echo htmlspecialchars($user['username'] ?? 'User'); ?>)</a>
                <a href="logout.php" class="btn btn-secondary-outline">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-secondary">Login</a>
                <a href="register.php" class="btn btn-primary">Register</a>
            <?php endif; ?>
        </div>
    </header>

     <main class="page-content">
        <h1>Our Desserts</h1>

        <div class="menu-grid">
             <?php if (empty($menu_items)): ?>
                <p>Dessert menu is currently unavailable.</p>
            <?php else: ?>
                <?php foreach ($menu_items as $item): ?>
                    <article class="menu-item">
                        <span class="menu-item-price">$<?php echo number_format($item['price'], 2); ?></span>
                         <img src="<?php echo htmlspecialchars($item['image'] ?? 'https://via.placeholder.com/300x200?text=No+Image'); ?>"
                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div> <!-- End menu-grid -->

         <a href="index.php" class="back-link">← Back to Homepage</a>
         <a href="order-online.php" class="back-link" style="margin-top: 10px;">Order Online →</a>

    </main>
</body>
</html>