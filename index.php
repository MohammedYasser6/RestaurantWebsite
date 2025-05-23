<?php
require_once 'bootstrap.php'; // Includes session start and functions
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colibri Restaurant - Fresh Ingredients, Memorable Meals</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* --- Styles from previous index.php --- */
        /* HERO SECTION STYLES */
        .hero { background-image: linear-gradient(rgba(0, 0, 0, 0.45), rgba(0, 0, 0, 0.45)), url('images/background.jpg'); background-size: cover; background-position: center center; background-attachment: fixed; padding: 150px 40px; text-align: center; color: #ffffff; display: flex; justify-content: center; align-items: center; min-height: 70vh; margin-bottom: 0; }
        .hero-text { max-width: 800px; background-color: rgba(0, 0, 0, 0.3); padding: 30px; border-radius: 8px; }
        .hero h1 { font-size: 56px; color: #ffffff; margin-bottom: 20px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6); }
        .hero p { font-size: 18px; color: #f0f0f0; margin-bottom: 30px; line-height: 1.7; text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5); }
        .hero .btn { padding: 12px 30px; font-size: 16px; background-color: var(--primary-green); color: white; border: 2px solid var(--primary-green); border-radius: 30px; text-decoration: none; transition: background-color 0.3s, color 0.3s, border-color 0.3s; margin: 5px; }
        .hero .btn:hover { background-color: transparent; color: white; border-color: white; }
        .hero .btn-secondary-hero { background-color: transparent; border: 2px solid white; color: white; }
        .hero .btn-secondary-hero:hover { background-color: white; color: var(--dark-text); }

        /* MENU SECTION STYLES */
        .menu-section { padding: 60px 40px; text-align: center; }
        .menu-section h2 { text-align: center; margin-bottom: 20px; font-size: 36px; }
        .menu-section p { max-width: 700px; margin: 0 auto 30px auto; color: var(--secondary-text); font-size: 16px; line-height: 1.7; }
        .menu-section .btn-view-category { display: inline-block; padding: 10px 25px; border-radius: 25px; background-color: transparent; color: var(--primary-green); border: 1px solid var(--primary-green); text-decoration: none; font-size: 14px; font-weight: 700; transition: background-color 0.3s ease, color 0.3s ease; }
        .menu-section .btn-view-category:hover { background-color: var(--primary-green); color: #ffffff; }

        /* Locations Section */
        #locations { background-color: #f8f9fa; padding: 60px 40px; text-align: center; }
        #locations h2 { margin-bottom: 40px; }
        .locations-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px; max-width: 1000px; margin: 0 auto; }
        .location-details { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.07); text-align: left; border: 1px solid var(--border-color); }
        .location-details h3 { font-family: var(--font-sans); font-size: 20px; color: var(--primary-green); margin-bottom: 10px; }
        .location-details p { margin-bottom: 15px; line-height: 1.6; color: var(--secondary-text); font-size: 15px; }
        .location-details p strong { color: var(--dark-text); }
        .map-placeholder { background-color: #e0e0e0; height: 200px; margin-top: 15px; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #888; font-style: italic; font-size: 13px; }

        /* General adjustments */
        .main-content { padding: 30px 0; }
        #contact { padding: 60px 40px; }
        /* Style for Admin button (optional) */
        .header-actions .btn-admin { background-color: #dc3545; border-color: #dc3545; color: white; }
        .header-actions .btn-admin:hover { background-color: #c82333; border-color: #bd2130; color: white; }
    </style>
</head>
<body>
    <div class="page-container">
        <aside class="sidebar">
             <div class="logo-container-side"> <a href="index.php"> <span class="logo-icon">C</span> <span class="logo-text-side">colibri</span> </a> </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="menu.php">MENU</a></li>
                    <li><a href="#locations">LOCATIONS</a></li>
                    <li><a href="#contact">CONTACT</a></li>
                </ul>
            </nav>
            <div class="social-icon"> <a href="#" aria-label="Instagram"><img src="https://via.placeholder.com/24/cccccc/888888?text=I" alt="Instagram Icon"></a> </div>
        </aside>

        <div class="main-content">
            <header class="main-header" style="padding: 0 40px;">
                <div class="status"> <span class="status-dot closed"></span> Closed    Opens in less than an hour </div>
                <!-- **** MODIFIED HEADER ACTIONS **** -->
                <div class="header-actions">
                    <a href="reserve.php" class="btn btn-secondary">RESERVE</a>
                    <a href="order-online.php" class="btn btn-primary">ORDER ONLINE</a>

                    <?php if (is_logged_in()):
                        $user = get_logged_in_user();
                        $isAdmin = ($user !== null) && is_admin(); // Check if admin
                    ?>
                        <?php // Display Admin Link IF user is admin ?>
                        <?php if ($isAdmin): ?>
                            <a href="admin/index.php" class="btn btn-admin">ADMIN</a> <!-- Use btn-admin or btn-danger etc. -->
                        <?php endif; ?>

                        <?php // Standard Profile and Logout Links for all logged-in users ?>
                        <a href="profile.php" class="btn btn-secondary">Profile (<?php echo htmlspecialchars($user['username'] ?? 'User'); ?>)</a>
                        <a href="logout.php" class="btn btn-secondary-outline">Logout</a>

                    <?php else: // User is not logged in ?>
                        <a href="login.php" class="btn btn-secondary">Login</a>
                        <!-- Register button might be redundant if Login leads to it -->
                        <!-- <a href="register.php" class="btn btn-primary">Register</a> -->
                    <?php endif; ?>
                </div>
                 <!-- **** END MODIFIED HEADER ACTIONS **** -->
            </header>

            <!-- ===== HERO SECTION ===== -->
            <section class="hero">
                <div class="hero-text">
                    <h1>Experience Authentic Flavors at Colibri</h1>
                    <p> Discover a culinary journey where fresh, locally-sourced ingredients meet passion. From our signature main dishes to delightful starters and drinks, every bite tells a story. </p>
                    <a href="menu.php" class="btn">View Full Menu</a>
                    <a href="order-online.php" class="btn btn-secondary-hero">Order Online Now</a>
                </div>
            </section>
            <!-- ===== END HERO SECTION ===== -->

            <!-- ===== START MENU CATEGORY LINKS ===== -->
            <div style="background-color: #fff;">
                <section id="main-dishes" class="menu-section">
                    <h2>Our Signature Main Dishes</h2>
                    <p>Featuring vibrant bowls packed with fresh ingredients and bold flavors. Choose from options like our zesty Guerrilla bowl, savory Teriyaki Salmon, or the classic Chicken, Buffalo & Blue.</p>
                    <a href="menu.php#main-dishes-bowls" class="btn-view-category">View Main Dishes</a>
                </section>
                <section id="appetizers-link" class="menu-section" style="background-color: #fdfbf5;">
                    <h2>Appetizers</h2>
                    <p>Start your meal right with our tempting selection of appetizers. Perfect for sharing or enjoying solo, from crispy bites to fresh platters.</p>
                    <a href="menu.php#appetizers" class="btn-view-category">View Appetizers</a>
                </section>
                <section id="drinks-link" class="menu-section">
                    <h2>Drinks</h2>
                    <p>Quench your thirst with our refreshing beverages, including house-made lemonades, iced teas, lassis, and sparkling options.</p>
                     <a href="menu.php#drinks" class="btn-view-category">View Drinks</a>
                </section>
                <section id="desserts-link" class="menu-section" style="background-color: #fdfbf5;">
                    <h2>Desserts</h2>
                    <p>Indulge your sweet tooth with our delightful desserts, crafted to provide the perfect ending to your meal.</p>
                    <a href="menu.php#desserts" class="btn-view-category">View Desserts</a>
                </section>
            </div>
            <!-- ===== END MENU CATEGORY LINKS ===== -->

            <!-- ===== LOCATIONS SECTION ===== -->
            <section id="locations">
                 <h2>Our Locations</h2>
                <div class="locations-grid">
                    <div class="location-details"> <h3>Colibri Downtown</h3> <p> 123 Main Street<br> Anytown, QC H0H 0H0<br> Phone: (555) 123-4567 </p> <p> <strong>Hours:</strong><br> Mon - Fri: 11:00 AM - 9:00 PM<br> Sat - Sun: 12:00 PM - 10:00 PM </p> <div class="map-placeholder"> ( Downtown Map Placeholder ) </div> </div>
                    <div class="location-details"> <h3>Colibri West End</h3> <p> 456 Oak Avenue<br> Anytown, QC H1H 1H1<br> Phone: (555) 987-6543 </p> <p> <strong>Hours:</strong><br> Mon - Sat: 11:30 AM - 9:30 PM<br> Sunday: Closed </p> <div class="map-placeholder"> ( West End Map Placeholder ) </div> </div>
                    <div class="location-details"> <h3>Colibri North Market</h3> <p> 789 Maple Plaza, Unit 5<br> Anytown, QC H2H 2H2<br> Phone: (555) 555-1111 </p> <p> <strong>Hours:</strong><br> Mon - Sun: 11:00 AM - 8:00 PM<br> (Takeaway & Delivery Only) </p> <div class="map-placeholder"> ( North Market Map Placeholder ) </div> </div>
                </div>
            </section>
            <!-- ===== END LOCATIONS SECTION ===== -->

            <!-- Contact Section -->
             <section id="contact" style="background-color: #fff;">
                 <h2>Contact Us</h2>
                 <p style="text-align: center; max-width: 600px; margin: 0 auto;">Have questions? Reach out to us!<br> General Inquiries: info@colibri.test | Downtown: (555) 123-4567</p>
             </section>

        </div> <!-- End main-content -->
    </div> <!-- End page-container -->
</body>
</html>