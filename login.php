<?php
require_once 'bootstrap.php';

// Redirect if already logged in
if (is_logged_in()) {
    // Redirect admin or customer appropriately
    if (is_admin()) {
        redirect('admin/index.php');
    } else {
        redirect('profile.php'); // Or index.php
    }
}

$error_message = '';
$username_value = ''; // Keep username in field on error

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $username_value = $username; // Store for repopulating field

    if (empty($username) || empty($password)) {
        $error_message = "Username and Password are required.";
    } else {
        $user = verify_login($username, $password);
        if ($user) {
            session_regenerate_id(true); // Security measure
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'] ?? 'customer'; // Store role in session

            // Redirect based on role
            if ($_SESSION['user_role'] === 'admin') {
                 redirect('admin/index.php');
            } else {
                 redirect('profile.php'); // Or redirect to index.php or order-online.php
            }
        } else {
            $error_message = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Colibri</title>
  <link rel="stylesheet" href="style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
  <div id="logo">
    <a href="index.php" style="text-decoration: none; color: var(--primary-green);">
        <span class="logo-icon">C</span> colibri
    </a>
  </div>

  <div class="content-container">
    <h1>Login</h1>
    <?php if (!empty($error_message)): ?>
        <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <?php // Display success message from registration
        if (isset($_SESSION['success_message'])) {
            echo '<div class="success-message" style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">'
                 . htmlspecialchars($_SESSION['success_message'])
                 . '</div>';
            unset($_SESSION['success_message']);
        }
    ?>

    <form id="login-form" action="login.php" method="post">
      <div>
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($username_value); ?>" />
      </div>
      <div>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required />
      </div>
      <input type="submit" value="Login" />
    </form>
    <p class="form-link">
      Don't have an account? <a href="register.php">Register here</a>
    </p>
    <p class="form-link" style="margin-top: 10px;">
        <a href="index.php">← Back to Homepage</a>
    </p>

  </div>
</body>
</html>