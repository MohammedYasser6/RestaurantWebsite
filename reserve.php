<?php
require_once 'bootstrap.php'; // Includes session start and functions

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- Retrieve form data ---
    $name = trim($_POST['reservation_name'] ?? '');
    $email = trim($_POST['reservation_email'] ?? '');
    $phone = trim($_POST['reservation_phone'] ?? '');
    $date = trim($_POST['reservation_date'] ?? '');
    $time = trim($_POST['reservation_time'] ?? '');
    $guests = filter_var(trim($_POST['reservation_guests'] ?? ''), FILTER_VALIDATE_INT);
    $requests = trim($_POST['special_requests'] ?? '');

    // --- Basic Server-Side Validation ---
    $validation_passed = false;
    if (empty($name) || empty($email) || empty($phone) || empty($date) || empty($time) || $guests === false || $guests < 1) {
        $error_message = "Please fill in all required fields (Name, Email, Phone, Date, Time, Guests).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please provide a valid email address.";
    } else {
         // Basic date/time validation (can be more complex)
         if (strtotime($date) === false || strtotime($date) < strtotime(date('Y-m-d'))) {
             $error_message = "Please select a valid future date.";
         } elseif (strtotime($time) === false) {
             $error_message = "Please select a valid time.";
         } else {
             $validation_passed = true;
         }
    }

    // --- If Validation Passed, Process and SAVE RESERVATION ---
    if ($validation_passed) {
        // 1. Prepare Reservation Data
        $reservation_id = uniqid('res_'); // Generate a unique reservation ID
        $received_timestamp = date('Y-m-d H:i:s'); // Current date and time

        $new_reservation_data = [
            'reservation_id' => $reservation_id,
            'received_at' => $received_timestamp,
            'status' => 'Pending', // Initial status
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'date' => $date,
            'time' => $time,
            'guests' => $guests,
            'requests' => $requests,
        ];

        // 2. Load existing reservations
        $reservations = get_reservations(); // Use the function from bootstrap

        // 3. Add the new reservation
        $reservations[$reservation_id] = $new_reservation_data;

        // 4. Save reservations back to file
        if (save_reservations($reservations)) { // Use the function from bootstrap
            // --- Reservation Request Saved Successfully ---
            $success_message = "Thank you, " . htmlspecialchars($name) . "! Your reservation request (#" . $reservation_id . ") for " . htmlspecialchars($guests) . " guest(s) on " . htmlspecialchars($date) . " at " . htmlspecialchars($time) . " has been received. We will contact you shortly to confirm.";

            // Clear form fields only on success (optional)
            $_POST = []; // Reset POST array to clear the form
        } else {
            // --- Error Saving Reservation ---
            $error_message = "Error: Could not save your reservation request. Please try again or call us.";
            error_log("Failed to save reservation data to file: " . RESERVATION_DATA_FILE);
        }

    } // End if validation_passed

} // End form submission check

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make a Reservation - Colibri</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* --- Paste reserve.php CSS from previous version --- */
        .page-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 40px; background-color: #fff; border-bottom: 1px solid var(--border-color); margin-bottom: 30px; }
        .page-header .logo a { text-decoration: none; color: var(--primary-green); font-family: var(--font-serif); font-size: 24px; font-weight: 700; display: flex; align-items: center; }
        .page-header .logo .logo-icon { margin-right: 8px; }
        .page-header .user-actions a { margin-left: 15px; text-decoration: none; }
        .page-content { padding: 0 40px 40px 40px; max-width: 800px; margin: 0 auto; }
        .page-content h1 { font-family: var(--font-serif); color: var(--primary-green); text-align: center; margin-bottom: 40px; font-size: 36px; }
        .back-link { display: block; text-align: center; margin-top: 40px; color: var(--primary-green); font-weight: 700; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        .reservation-form div { margin-bottom: 18px; }
        .reservation-form label { display: block; margin-bottom: 6px; font-weight: 700; font-size: 14px; color: var(--secondary-text); }
        .reservation-form input[type="text"], .reservation-form input[type="email"], .reservation-form input[type="tel"], .reservation-form input[type="date"], .reservation-form input[type="time"], .reservation-form input[type="number"], .reservation-form textarea { width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 14px; font-family: var(--font-sans); }
        .reservation-form input:focus, .reservation-form textarea:focus { outline: none; border-color: var(--primary-green); box-shadow: 0 0 0 2px rgba(74, 119, 41, 0.2); }
        .reservation-form textarea { min-height: 100px; resize: vertical; }
        .reservation-form input[type="submit"] { width: 100%; padding: 12px; background-color: var(--primary-green); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 700; cursor: pointer; transition: background-color 0.3s ease; margin-top: 10px; }
        .reservation-form input[type="submit"]:hover { background-color: #3a5e20; }
        .success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; line-height: 1.5; }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .form-note { font-size: 13px; color: var(--secondary-text); margin-top: 20px; text-align: center; }
         /* Style for Admin button (optional) */
        .header-actions .btn-admin { background-color: #dc3545; border-color: #dc3545; color: white; }
        .header-actions .btn-admin:hover { background-color: #c82333; border-color: #bd2130; color: white; }

    </style>
</head>
<body>
    <header class="page-header">
         <div class="logo"> <a href="index.php"> <span class="logo-icon">C</span> colibri </a> </div>
         <div class="user-actions">
            <?php if (is_logged_in()): $user = get_logged_in_user(); $isAdmin = ($user !== null) && is_admin(); ?>
                <?php if ($isAdmin): ?> <a href="admin_orders.php" class="btn btn-admin">ADMIN</a> <?php endif; ?>
                <a href="profile.php" class="btn btn-secondary">Profile (<?php echo htmlspecialchars($user['username'] ?? 'User'); ?>)</a>
                <a href="logout.php" class="btn btn-secondary-outline">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-secondary">Login</a>
                <a href="register.php" class="btn btn-primary">Register</a>
            <?php endif; ?>
         </div>
    </header>

    <main class="page-content">
        <h1>Make a Reservation</h1>

        <?php if (!empty($error_message)): ?> <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div> <?php endif; ?>
        <?php if (!empty($success_message)): ?> <div class="success-message"><?php echo $success_message; ?></div> <?php endif; ?>

        <?php if (empty($success_message)): // Only show form if not successful ?>
            <form class="reservation-form" action="reserve.php" method="post">
                <div> <label for="reservation_name">Full Name:</label> <input type="text" id="reservation_name" name="reservation_name" required value="<?php echo htmlspecialchars($_POST['reservation_name'] ?? ''); ?>"> </div>
                <div> <label for="reservation_email">Email Address:</label> <input type="email" id="reservation_email" name="reservation_email" required value="<?php echo htmlspecialchars($_POST['reservation_email'] ?? ''); ?>"> </div>
                <div> <label for="reservation_phone">Phone Number:</label> <input type="tel" id="reservation_phone" name="reservation_phone" required value="<?php echo htmlspecialchars($_POST['reservation_phone'] ?? ''); ?>"> </div>
                <div> <label for="reservation_date">Date:</label> <input type="date" id="reservation_date" name="reservation_date" required value="<?php echo htmlspecialchars($_POST['reservation_date'] ?? ''); ?>" min="<?php echo date('Y-m-d'); ?>"> </div>
                <div> <label for="reservation_time">Time:</label> <input type="time" id="reservation_time" name="reservation_time" required value="<?php echo htmlspecialchars($_POST['reservation_time'] ?? ''); ?>" min="11:00" max="21:00"> <small style="font-size: 12px; color: #777;"> (11:00 AM - 9:00 PM)</small> </div>
                <div> <label for="reservation_guests">Number of Guests:</label> <input type="number" id="reservation_guests" name="reservation_guests" required min="1" max="12" value="<?php echo htmlspecialchars($_POST['reservation_guests'] ?? '2'); ?>"> <small style="font-size: 12px; color: #777;"> (Max 12 online)</small> </div>
                <div> <label for="special_requests">Special Requests (Optional):</label> <textarea id="special_requests" name="special_requests" placeholder="e.g., Dietary needs, high chair..."><?php echo htmlspecialchars($_POST['special_requests'] ?? ''); ?></textarea> </div>
                <input type="submit" value="Submit Reservation Request">
            </form>
            <p class="form-note"> This is a request, not a guaranteed reservation. We will contact you to confirm. </p>
        <?php endif; ?>

        <a href="index.php" class="back-link">← Back to Homepage</a>
    </main>
</body>
</html>