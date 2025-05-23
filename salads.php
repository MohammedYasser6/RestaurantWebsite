<?php
require_once 'bootstrap.php'; // Includes session start and functions
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Salads - Colibri</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* Styles from original file */
        .page-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 40px; background-color: #fff; border-bottom: 1px solid var(--border-color); margin-bottom: 30px; }
        .page-header .logo a { text-decoration: none; color: var(--primary-green); font-family: var(--font-serif); font-size: 24px; font-weight: 700; display: flex; align-items: center; }
        .page-header .logo .logo-icon { margin-right: 8px; }
        .page-header .user-actions a { margin-left: 15px; text-decoration: none; }
        .page-content { padding: 0 40px 40px 40px; max-width: 1200px; margin: 0 auto; }
        .page-content h1 { font-family: var(--font-serif); color: var(--primary-green); text-align: center; margin-bottom: 40px; font-size: 36px; }
        .menu-grid { grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }
        .menu-cta { display: none; }
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
                 <a href="profile.php" class="btn btn-secondary">Profile (<?php echo htmlspecialchars($user['username']); ?>)</a>
                 <a href="logout.php" class="btn btn-secondary-outline">Logout</a>
             <?php else: ?>
                 <a href="login.php" class="btn btn-secondary">Login</a>
                 <a href="register.php" class="btn btn-primary">Register</a>
             <?php endif; ?>
        </div>
    </header>

    <main class="page-content">
        <h1>Our Fresh Salads</h1>

        <!-- Hardcoded Salad Items -->
        <div class="menu-grid">
            <article class="menu-item">
                <span class="menu-item-price">$14.50</span>
                <!-- Use local path or placeholder -->
                <img src="images/salad-caesar-chicken.jpg" alt="Grilled Chicken Caesar Salad">
                <h3>Grilled Chicken Caesar Salad</h3>
                <p>Crisp romaine lettuce, juicy grilled chicken, garlic croutons, parmesan shavings, creamy homemade Caesar dressing.</p>
            </article>

            <article class="menu-item">
                <span class="menu-item-price">$15.00</span>
                <img src="images/salad-greek.jpg" alt="Authentic Greek Salad">
                <h3>Authentic Greek Salad</h3>
                <p>Ripe tomatoes, fresh cucumber, green peppers, red onions, Kalamata olives, feta cheese, oregano & olive oil vinaigrette.</p>
            </article>

            <article class="menu-item">
                <span class="menu-item-price">$16.00</span>
                <img src="images/salad-quinoa-avocado.jpg" alt="Quinoa & Avocado Salad">
                <h3>Quinoa & Avocado Salad</h3>
                <p>Quinoa, creamy avocado, roasted corn, black beans, colorful bell peppers, fresh cilantro, zesty lime-cumin vinaigrette.</p>
            </article>

             <article class="menu-item">
                <span class="menu-item-price">$15.50</span>
                <img src="images/salad-nicoise.jpg" alt="Modern Niçoise Salad">
                <h3>Modern Niçoise Salad</h3>
                <p>Seared tuna (or smoked tofu), blanched green beans, roasted baby potatoes, cherry tomatoes, olives, soft-boiled egg, Dijon vinaigrette.</p>
            </article>
        </div>

         <a href="index.php" class="back-link">← Back to Homepage</a>
    </main>
</body>
</html>