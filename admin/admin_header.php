<?php
// This file assumes bootstrap.php was already required by the including page
// and IS_ADMIN_PAGE was defined.
if (!defined('IS_ADMIN_PAGE')) {
    // A simple check to prevent direct access if bootstrap.php didn't define it.
    // Though the individual pages should handle the full admin check.
    die("Restricted access.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- The <title> will be set by the individual admin pages after including this header -->
    <link rel="stylesheet" href="../style.css"> <!-- Link back to main site style.css -->
    <style>
        /* ===== UNIFIED ADMIN CSS ===== */
        body {
            background-color: #f4f6f9; /* Lighter grey background */
            font-family: var(--font-sans, Arial, sans-serif); /* Use variable from main style or fallback */
            color: #333;
            margin: 0;
            padding: 0;
        }

        .admin-container {
            max-width: 1100px; /* Slightly wider */
            margin: 25px auto;
            background: #fff;
            padding: 25px 30px; /* More padding */
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        /* --- Typography --- */
        .admin-container h1 {
            text-align: center;
            color: var(--primary-green, #4A7729);
            margin-top: 0;
            margin-bottom: 15px;
            font-family: var(--font-serif, Georgia, serif);
            font-size: 28px;
            border-bottom: 2px solid var(--primary-green, #4A7729);
            padding-bottom: 10px;
        }
        .admin-container h2 { /* Page specific titles */
            text-align: left;
            color: var(--dark-text, #333);
            margin-top: 30px;
            margin-bottom: 20px;
            font-size: 22px;
            font-family: var(--font-serif, Georgia, serif);
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
        }
        .admin-container p {
            line-height: 1.6;
            margin-bottom: 15px;
        }

        /* --- Navigation --- */
        .admin-top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        .admin-top-bar h1 {
             margin-bottom: 0; /* Remove bottom margin from main H1 when in top bar */
             border-bottom: none;
             padding-bottom: 0;
             font-size: 26px; /* Slightly smaller */
        }
        .admin-welcome span {
            font-size: 14px;
            color: #555;
        }
        .admin-welcome a {
            margin-left: 10px;
            font-size: 13px;
            color: var(--primary-green, #4A7729);
            text-decoration: none;
        }
        .admin-welcome a:hover { text-decoration: underline; }

        .admin-nav {
            background-color: var(--primary-green, #4A7729);
            padding: 0; /* Remove padding if links have padding */
            margin-bottom: 25px;
            border-radius: 5px;
            text-align: center;
            display: flex; /* Use flexbox for better alignment */
            justify-content: center; /* Center links */
        }
        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 12px 22px; /* More padding */
            font-weight: bold;
            display: inline-block; /* Needed for padding */
            transition: background-color 0.2s ease;
        }
        .admin-nav a:hover, .admin-nav a.active { /* Added .active class */
            background-color: #3a5e20; /* Darker green */
        }
        .admin-nav a:first-child { border-top-left-radius: 5px; border-bottom-left-radius: 5px; }
        .admin-nav a:last-child { border-top-right-radius: 5px; border-bottom-right-radius: 5px; }


        /* --- Tables --- */
        .admin-table, .orders-table, .reservations-table, .menu-manage-table { /* Target all table classes */
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }
        .admin-table th, .orders-table th, .reservations-table th, .menu-manage-table th,
        .admin-table td, .orders-table td, .reservations-table td, .menu-manage-table td {
            border: 1px solid #e0e0e0; /* Lighter border */
            padding: 10px 12px;
            text-align: left;
            vertical-align: middle; /* Better vertical alignment */
        }
        .admin-table th, .orders-table th, .reservations-table th, .menu-manage-table th {
            background-color: #f8f9fa; /* Very light grey for headers */
            color: #333;
            font-weight: 600; /* Semibold */
        }
        .admin-table tr:nth-child(even), .orders-table tr:nth-child(even),
        .reservations-table tr:nth-child(even), .menu-manage-table tr:nth-child(even) {
             /* background-color: #f9f9f9; */ /* Removing zebra striping for cleaner look, optional */
        }
        .admin-table tr:hover, .orders-table tr:hover,
        .reservations-table tr:hover, .menu-manage-table tr:hover {
            background-color: #f1f8ff; /* Light blue hover */
        }
        .admin-table img { max-width: 60px; height: auto; vertical-align: middle; border-radius: 3px; }

        /* --- Forms --- */
        .admin-form .form-group { margin-bottom: 18px; }
        .admin-form label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600; /* Semibold */
            color: #444;
            font-size: 14px;
        }
        .admin-form input[type="text"],
        .admin-form input[type="number"],
        .admin-form input[type="email"], /* Added email type */
        .admin-form input[type="password"], /* Added password type */
        .admin-form textarea,
        .admin-form select {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 5px; /* Smaller margin if help text follows */
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .admin-form input:focus, .admin-form textarea:focus, .admin-form select:focus {
            border-color: var(--primary-green, #4A7729);
            outline: none;
            box-shadow: 0 0 0 2px rgba(74, 119, 41, 0.15);
        }
        .admin-form textarea { min-height: 120px; resize: vertical; }
        .admin-form input[type="file"] { padding: 8px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 5px;}
        .admin-form .help-text { font-size: 12px; color: #666; display: block; margin-top: 3px;}

        .admin-form button, .admin-form input[type="submit"], .btn-admin-action { /* Generic button style */
            padding: 10px 22px;
            background-color: var(--primary-green, #4A7729);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: background-color 0.2s ease;
            text-decoration: none; /* For <a> styled as button */
            display: inline-block; /* For <a> styled as button */
        }
        .admin-form button:hover, .admin-form input[type="submit"]:hover, .btn-admin-action:hover {
            background-color: #3a5e20; /* Darker green */
        }
        .btn-admin-secondary {
            background-color: #6c757d; /* Grey */
        }
        .btn-admin-secondary:hover {
            background-color: #5a6268;
        }
        .btn-admin-danger {
            background-color: #dc3545; /* Red */
        }
        .btn-admin-danger:hover {
            background-color: #c82333;
        }

        /* --- Action Links (e.g., Edit/Delete in tables) --- */
        .action-links a {
            margin-right: 12px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }
        .action-links .edit { color: #E0A800; } /* Amber/Yellow */
        .action-links .delete { color: #dc3545; }
        .action-links .edit:hover { text-decoration: underline; }
        .action-links .delete:hover { text-decoration: underline; }

        /* --- Feedback Messages --- */
        .feedback-message {
            padding: 12px 20px;
            border-radius: 5px;
            margin: 0 0 20px 0; /* Margin only at bottom */
            text-align: left; /* Align text left */
            font-weight: 500; /* Normal weight */
            font-size: 14px;
            border: 1px solid transparent;
        }
        .feedback-message.success { background-color: #d1e7dd; color: #0f5132; border-color: #badbcc; }
        .feedback-message.error { background-color: #f8d7da; color: #842029; border-color: #f5c2c7; }
        .feedback-message.info { background-color: #cff4fc; color: #055160; border-color: #b6effb; }


        /* --- Specific Admin Page Styles (can be moved to individual pages if extensive) --- */
        /* Orders Page */
        .order-card { border: 1px solid #e0e0e0; padding: 20px; margin-bottom: 25px; border-radius: 6px; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .order-card h4 { margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px; font-size: 18px; color: var(--primary-green); }
        .order-card h4 .order-id { font-family: monospace; font-size: 14px; color: #444; float: right; font-weight: normal; }
        .order-details p { margin: 6px 0; font-size: 14px; line-height: 1.6; }
        .order-details strong { color: #333; }
        .order-items-table { margin-top: 10px; margin-bottom: 10px; } /* Less margin for items table within card */
        .order-summary-totals { border-top: 1px dashed #ccc; margin-top: 15px; padding-top: 10px; text-align: right; font-size: 14px; }
        .status-new, .status-Pending { color: #007bff; font-weight: bold; } /* Combined for pending order status */
        .status-Confirmed, .status-processing { color: #ffc107; font-weight: bold; }
        .status-Completed { color: #28a745; font-weight: bold; }
        .status-Cancelled { color: #dc3545; font-weight: bold; }
        .status-unknown { color: #6c757d; font-style: italic; }
        .payment-pending { color: #fd7e14; } /* Orange for pending payment */
        .payment-paid, .payment-cash { color: #198754; } /* Green for paid or cash */

        /* Menu Manage Page */
        .menu-manage-table img { max-width: 80px; max-height: 50px; border-radius: 3px; }

        /* Menu Edit Page */
        .current-image-admin img { max-height: 80px; max-width: 120px; vertical-align: middle; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; }
        .current-image-admin code { font-size: 11px; color: #555; margin-left: 10px; }

        /* No Orders/Reservations Message */
        .no-data-message { text-align: center; padding: 30px 20px; color: #666; font-style: italic; background-color: #f9f9f9; border: 1px dashed #ddd; border-radius: 5px; margin-top: 20px; }
        .btn-admin-small {
    padding: 4px 10px !important; /* Add !important if needed to override general .btn */
    font-size: 12px !important;
    border-radius: 3px !important;
    min-width: 60px; /* Ensure buttons have some width */
    text-align: center;
}

.btn-admin-edit { /* Specific class for edit button */
    background-color: #ffc107 !important; /* Amber */
    color: #212529 !important; /* Dark text for better contrast */
    border-color: #E0A800 !important;
}
.btn-admin-edit:hover {
    background-color: #e0a800 !important;
    border-color: #d39e00 !important;
    color: #212529 !important;
}

/* .btn-admin-danger is likely already defined for delete button */

/* Action Buttons Cell in Tables */
.action-buttons-cell {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 5px;
}
.action-buttons-cell .btn-admin-action,
.action-buttons-cell form button {
    width: 100%; /* Make buttons in cell take full width of cell for better alignment */
    box-sizing: border-box;
}
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-top-bar">
             <h1>Admin Panel</h1>
             <span class="admin-welcome">
                Welcome, <?php echo htmlspecialchars(get_logged_in_user()['username'] ?? 'Admin'); ?>!
                <a href="../logout.php">(Logout)</a>
                <a href="../index.php">(View Site)</a>
             </span>
        </div>

        <nav class="admin-nav">
            <a href="index.php">Dashboard</a>
            <a href="menu_manage.php">Manage Menu</a>
            <a href="reservations.php">Reservations</a>
            <a href="orders.php">Orders</a>
            <!-- <a href="users_manage.php">Manage Users</a> -->
        </nav>
        <!-- Separator hr removed, nav has margin-bottom -->
        <?php
        // Display session feedback if any
        if (isset($_SESSION['admin_feedback'])) {
            $feedback_type_class = ($_SESSION['admin_feedback_type'] ?? 'success') === 'error' ? 'error' : 'success';
            // Ensure admin_feedback_type is also unset
            unset($_SESSION['admin_feedback_type']);
            echo '<div class="feedback-message ' . $feedback_type_class . '">' . htmlspecialchars($_SESSION['admin_feedback']) . '</div>';
            unset($_SESSION['admin_feedback']);
        }
        ?>
        <!-- Main content of individual admin page will start here -->