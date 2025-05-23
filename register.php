<?php
require_once 'bootstrap.php';

// Redirect if already logged in
if (is_logged_in()) {
     if (is_admin()) { redirect('admin/index.php'); } else { redirect('profile.php'); }
}

$error_message = '';
$submitted_username = ''; // Keep values on error
$submitted_email = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Store submitted values to repopulate form on error
    $submitted_username = $username;
    $submitted_email = $email;

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (username_exists($username)) {
        $error_message = "Username already taken. Please choose another.";
    } else {
        // Attempt registration (registers as 'customer' via bootstrap function)
        if (register_user($username, $email, $password)) {
            $_SESSION['success_message'] = "Registration successful! Please login.";
            redirect('login.php');
        } else {
            $error_message = "Registration failed due to a server error. Please try again.";
            error_log("Registration failed for username: " . $username);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Colibri</title>
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
    <h1>Register</h1>
    <?php if (!empty($error_message)): ?>
        <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <form id="register-form" action="register.php" method="post">
      <div>
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" placeholder="Choose a username" required value="<?php echo htmlspecialchars($submitted_username); ?>" />
      </div>
      <div>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" placeholder="Enter your email" required value="<?php echo htmlspecialchars($submitted_email); ?>" />
      </div>
      <div>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" placeholder="Create a password (min 6 chars)" required />
      </div>
      <div>
        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required />
      </div>
      <input type="submit" value="Register" />
    </form>
    <p class="form-link">
      Already have an account? <a href="login.php">Login here</a>
    </p>
     <p class="form-link" style="margin-top: 10px;">
        <a href="index.php">← Back to Homepage</a>
    </p>

  </div>
</body>
</html>