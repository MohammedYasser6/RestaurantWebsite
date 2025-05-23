<?php
// db_connect.php

$servername = "localhost";
$username = "root";        // Default XAMPP username
$password = "";            // Default XAMPP password (empty)
$dbname = "colibri_db";    // The database name you created in phpMyAdmin

// Create connection using mysqli
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("MySQL Connection Error: (" . $conn->connect_errno . ") " . $conn->connect_error);
    // For a public site, you'd show a user-friendly error page instead of die()
    die("Database connection failed. We're working to fix this. Please try again later.");
}

// Set character set to utf8mb4 for better Unicode support
if (!$conn->set_charset("utf8mb4")) {
    error_log("Error loading character set utf8mb4: " . $conn->error);
    // This is usually a critical error if it happens
    die("Database character set configuration error.");
}

// The $conn variable is now globally available to scripts that include this file (via bootstrap.php)
?>