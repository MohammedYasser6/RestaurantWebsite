<?php
require_once 'bootstrap.php';

// Ensure user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

// Get logged-in user data
$user = get_logged_in_user();

// If user data somehow missing, log out
if (!$user) {
    error_log("Error: User data missing from session on profile page. Session: " . print_r($_SESSION, true));
    redirect('logout.php');
}

$isAdmin = is_admin(); // Check if user is admin

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile - <?php echo htmlspecialchars($user['username'] ?? 'User'); ?> - Colibri</title>
  <link rel="stylesheet" href="style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
  <style>
      /* Add specific profile page styles if needed */
       .profile-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 40px; background-color: #fff; border-bottom: 1px solid var(--border-color); margin-bottom: 30px; }
       .profile-header .logo a { text-decoration: none; color: var(--primary-green); font-family: var(--font-serif); font-size: 24px; font-weight: 700; display: flex; align-items: center; }
       .profile-header .logo .logo-icon { margin-right: 8px; }
       .profile-header .user-actions a { margin-left: 15px; text-decoration: none; }
       .content-container { max-width: 600px; margin: 30px auto; padding: 30px; background-color: #fff; border-radius: 8px; box-shadow: 0 3px 10px rgba(0,0,0,0.05); border: 1px solid var(--border-color); }
       .content-container h1 { text-align: center; margin-bottom: 25px; font-family: var(--font-serif); color: var(--primary-green); }
       .content-container h2 { font-size: 20px; color: var(--primary-green); margin-top: 30px; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
       .content-container ul { list-style: none; padding: 0; margin-bottom: 25px; }
       .content-container li { margin-bottom: 12px; font-size: 15px; }
       .content-container li strong { color: var(--dark-text); min-width: 100px; display: inline-block; }
       .content-container .action-links { margin-top: 30px; text-align: center; }
       .content-container .action-links a { margin: 0 10px; color: var(--primary-green); text-decoration: none; font-weight: bold; }
       .content-container .action-links a:hover { text-decoration: underline; }
       /* Style for Admin button (optional) */
        .user-actions .btn-admin { background-color: #dc3545; border-color: #dc3545; color: white; }
        .user-actions .btn-admin:hover { background-color: #c82333; border-color: #bd2130; color: white; }
        /* Style for general header buttons */
        .btn { display: inline-block; padding: 8px 18px; border-radius: 25px; text-decoration: none; font-size: 14px; font-weight: 700; border: 1px solid transparent; cursor: pointer; transition: all 0.3s ease; text-align: center; }
        .btn-primary { background-color: var(--primary-green); color: #ffffff; border-color: var(--primary-green); }
        .btn-primary:hover { background-color: #3a5e20; border-color: #3a5e20; }
        .btn-secondary { background-color: #ffffff; color: var(--primary-green); border-color: var(--border-color); box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .btn-secondary:hover { background-color: #f8f8f8; border-color: #ccc; }
        .btn-secondary-outline { background-color: transparent; color: var(--primary-green); border: 1px solid var(--primary-green); }
        .btn-secondary-outline:hover { background-color: var(--primary-green); color: #ffffff; }

  </style>
</head>
<body>
    <!-- Using a simpler header structure for profile page -->
    <header class="profile-header">
        <div class="logo"> <a href="index.php"> <span class="logo-icon">C</span> colibri </a> </div>
        <!-- **** MODIFIED HEADER ACTIONS **** -->
        <div class="user-actions">
             <?php // $isAdmin should be set from logic above ?>
             <?php if ($isAdmin): ?>
                 <a href="admin/index.php" class="btn btn-admin">ADMIN</a>
             <?php endif; ?>
             <a href="order-online.php" class="btn btn-primary">Order Online</a>
             <a href="logout.php" class="btn btn-secondary-outline">Logout</a>
        </div>
         <!-- **** END MODIFIED HEADER ACTIONS **** -->
    </header>

    <div class="content-container">
        <h1>Your Profile</h1>
        <p style="text-align: center; margin-bottom: 25px;">Welcome back, <strong><?php echo htmlspecialchars($user['username'] ?? 'User'); ?></strong>!</p>

        <h2>Account Information:</h2>
        <ul>
            <li><strong>Username:</strong> <?php echo htmlspecialchars($user['username'] ?? 'N/A'); ?></li>
            <li><strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></li>
            <!-- Add more profile fields here if needed (e.g., from users.json) -->
        </ul>

        <!-- Optional: Add links to edit profile, view order history etc. -->
         <div class="action-links">
             <a href="index.php">← Back to Homepage</a>
             <?php // Add link to order history page if you create one ?>
             <!-- <a href="order_history.php">View Order History</a> -->
         </div>

    </div>
</body>
</html>