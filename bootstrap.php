<?php
/**
 * bootstrap.php
 *
 * Core application setup, session management, and utility functions.
 * Includes user authentication, menu loading, order/reservation handling, and helpers.
 * NOW MODIFIED FOR MYSQL DATABASE.
 */

// --- Session Management ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Database Connection ---
require_once __DIR__ . '/db_connect.php'; // The $conn variable will be available globally

// --- Configuration Constants ---

// Define image upload directory paths
// IMAGES ARE STORED DIRECTLY IN THE 'images' FOLDER
define('IMAGE_UPLOAD_DIR_ABS', __DIR__ . '/images/'); // Absolute path for PHP file operations (e.g., C:/xampp/htdocs/projectsql/images/)
define('IMAGE_UPLOAD_DIR_REL', 'images/');     // Relative path to store in DB and use in HTML src (e.g., images/)
                                               // ENSURE 'images' FOLDER EXISTS AND IS WRITABLE BY THE WEB SERVER

// --- Error Reporting (Development vs Production) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Data Handling & Loading Functions (MySQL Version) ---

/**
 * Reads menu item data from the MySQL database.
 * @return array Menu items (keyed by item_id)
 */
if (!function_exists('get_menu_items')) {
    function get_menu_items(): array {
        global $conn;
        $menu = [];
        $sql = "SELECT item_id, name, price, description, category, image_path FROM menu_items ORDER BY category, name";
        $result = $conn->query($sql);

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $menu[$row['item_id']] = $row;
            }
            $result->free();
        } else {
            error_log("Error fetching menu items: " . $conn->error);
        }
        return $menu;
    }
}

/**
 * Reads all user data from the MySQL database.
 * @return array Users (keyed by user id) or empty array.
 */
if (!function_exists('get_users')) {
    function get_users(): array {
        global $conn;
        $users = [];
        $sql = "SELECT id, username, email, password_hash, role, created_at FROM users ORDER BY username";
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $users[$row['id']] = $row;
            }
            $result->free();
        } else {
            error_log("Error fetching all users: " . $conn->error);
        }
        return $users;
    }
}

/**
 * Reads order data from the MySQL database.
 * @param string|null $order_id_filter Optional: Fetch a specific order.
 * @return array Orders or empty array.
 */
if (!function_exists('get_orders')) {
    function get_orders(?string $order_id_filter = null): array {
        global $conn;
        $orders = [];
        $sql_orders = "SELECT * FROM orders";
        if ($order_id_filter) {
            $sql_orders .= " WHERE order_id = ?";
        }
        $sql_orders .= " ORDER BY timestamp DESC";
        $stmt_orders = $conn->prepare($sql_orders);
        if (!$stmt_orders) {  error_log("Prepare failed for get_orders: " . $conn->error); return []; }
        if ($order_id_filter) {
            $stmt_orders->bind_param("s", $order_id_filter);
        }
        $stmt_orders->execute();
        $result_orders = $stmt_orders->get_result();
        if ($result_orders) {
            while ($order_row = $result_orders->fetch_assoc()) {
                $current_order_id = $order_row['order_id'];
                $orders[$current_order_id] = $order_row;
                $orders[$current_order_id]['items'] = [];
                $stmt_items = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
                if ($stmt_items) {
                    $stmt_items->bind_param("s", $current_order_id);
                    $stmt_items->execute();
                    $result_items = $stmt_items->get_result();
                    if ($result_items) {
                        while ($item_row = $result_items->fetch_assoc()) {
                            $orders[$current_order_id]['items'][] = $item_row;
                        }
                        $result_items->free();
                    } else { error_log("Error fetching items for order {$current_order_id}: " . $stmt_items->error); }
                    $stmt_items->close();
                } else { error_log("Prepare failed for order items query: " . $conn->error); }
            }
            $result_orders->free();
        } else { error_log("Error fetching orders: " . $stmt_orders->error); }
        $stmt_orders->close();
        if ($order_id_filter && !empty($orders)) {
            return $orders[$order_id_filter] ?? [];
        }
        return $orders;
    }
}

/**
 * Reads reservation data from the MySQL database.
 * @param string|null $reservation_id_filter Optional: Fetch a specific reservation.
 * @return array Reservations or empty array.
 */
if (!function_exists('get_reservations')) {
    function get_reservations(?string $reservation_id_filter = null): array {
        global $conn;
        $reservations = [];
        $sql = "SELECT * FROM reservations";
         if ($reservation_id_filter) {
            $sql .= " WHERE reservation_id = ?";
        }
        $sql .= " ORDER BY received_at DESC";
        $stmt = $conn->prepare($sql);
        if (!$stmt) { error_log("Prepare failed for get_reservations: " . $conn->error); return []; }
        if ($reservation_id_filter) {
            $stmt->bind_param("s", $reservation_id_filter);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $reservations[$row['reservation_id']] = $row;
            }
            $result->free();
        } else { error_log("Error fetching reservations: " . $stmt->error); }
        $stmt->close();
        if ($reservation_id_filter && !empty($reservations)) {
            return $reservations[$reservation_id_filter] ?? [];
        }
        return $reservations;
    }
}

/**
 * Saves a full order to the database using a transaction.
 * @param array $order_data Data for the `orders` table.
 * @param array $order_items_data Array of data for `order_items` table.
 * @return bool True on success, false on failure.
 */
if (!function_exists('save_full_order')) {
    function save_full_order(array $order_data, array $order_items_data): bool {
        global $conn;
        $conn->begin_transaction();
        try {
            $sql_order = "INSERT INTO orders (order_id, user_id, timestamp, status, 
                            customer_name, customer_address1, customer_address2, customer_city, 
                            customer_postal_code, customer_phone, customer_email, 
                            subtotal, tax, delivery_fee, grand_total, 
                            payment_method, payment_card_last4, payment_status) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_order = $conn->prepare($sql_order);
            if ($stmt_order === false) { throw new Exception("Order prepare failed: " . $conn->error); }
            $stmt_order->bind_param("sisssssssssddddsss",
                $order_data['order_id'], $order_data['user_id'], $order_data['timestamp'], $order_data['status'],
                $order_data['customer_name'], $order_data['customer_address1'], $order_data['customer_address2'],
                $order_data['customer_city'], $order_data['customer_postal_code'], $order_data['customer_phone'],
                $order_data['customer_email'], $order_data['subtotal'], $order_data['tax'],
                $order_data['delivery_fee'], $order_data['grand_total'], $order_data['payment_method'],
                $order_data['payment_card_last4'], $order_data['payment_status']
            );
            if (!$stmt_order->execute()) { throw new Exception("Execute failed for order table: " . $stmt_order->error); }
            $stmt_order->close();

            $sql_item = "INSERT INTO order_items (order_id, menu_item_id, item_name_snapshot, 
                                 item_price_snapshot, quantity, line_total) 
                         VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_item = $conn->prepare($sql_item);
            if ($stmt_item === false) { throw new Exception("Order item prepare failed: " . $conn->error); }
            foreach ($order_items_data as $item) {
                $stmt_item->bind_param("sssdis",
                    $order_data['order_id'], $item['id'], $item['name'], $item['price'],
                    $item['quantity'], $item['line_total']
                );
                if (!$stmt_item->execute()) { throw new Exception("Execute failed for order_items (item ID {$item['id']}): " . $stmt_item->error); }
            }
            $stmt_item->close();
            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Order save transaction failed: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Saves a reservation to the database.
 * @param array $reservation_data Data for the `reservations` table.
 * @return bool True on success, false on failure.
 */
if (!function_exists('save_reservation')) {
    function save_reservation(array $reservation_data): bool {
        global $conn;
        $sql = "INSERT INTO reservations (reservation_id, user_id, received_at, status, name, email, 
                                    phone, reservation_date, reservation_time, guests, requests)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) { error_log("Reservation prepare failed: " . $conn->error); return false; }
        $stmt->bind_param("sisssssssis",
            $reservation_data['reservation_id'], $reservation_data['user_id'], $reservation_data['received_at'],
            $reservation_data['status'], $reservation_data['name'], $reservation_data['email'],
            $reservation_data['phone'], $reservation_data['date'], $reservation_data['time'],
            $reservation_data['guests'], $reservation_data['requests']
        );
        if ($stmt->execute()) { $stmt->close(); return true; }
        else { error_log("Error saving reservation ({$reservation_data['reservation_id']}): " . $stmt->error); $stmt->close(); return false; }
    }
}

// --- User Authentication Functions (MySQL Version) ---

/**
 * Finds a user by username (case-insensitive) from MySQL.
 * @param string $username Username.
 * @return array|null User data or null.
 */
if (!function_exists('find_user_by_username')) {
    function find_user_by_username(string $username): ?array {
        global $conn;
        $stmt = $conn->prepare("SELECT id, username, email, password_hash, role FROM users WHERE username = ? LIMIT 1");
        if ($stmt === false) { error_log("Prepare failed for find_user_by_username: " . $conn->error); return null; }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user ?: null;
    }
}

/**
 * Finds a user by email (case-insensitive) from MySQL.
 * @param string $email Email.
 * @return array|null User data or null.
 */
if (!function_exists('find_user_by_email')) {
    function find_user_by_email(string $email): ?array {
        global $conn;
        $stmt = $conn->prepare("SELECT id, username, email, password_hash, role FROM users WHERE email = ? LIMIT 1");
         if ($stmt === false) { error_log("Prepare failed for find_user_by_email: " . $conn->error); return null; }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user ?: null;
    }
}

/**
 * Checks if a username exists.
 * @param string $username Username.
 * @return bool True if exists.
 */
if (!function_exists('username_exists')) {
    function username_exists(string $username): bool {
        return find_user_by_username($username) !== null;
    }
}

/**
 * Checks if an email exists.
 * @param string $email Email.
 * @return bool True if exists.
 */
if (!function_exists('email_exists')) {
    function email_exists(string $email): bool {
        return find_user_by_email($email) !== null;
    }
}

/**
 * Registers a new user in MySQL.
 * @param string $username Username.
 * @param string $email Email.
 * @param string $password Plain password.
 * @param string $role User role (default 'customer').
 * @return bool|int User ID on success, false on failure.
 */
if (!function_exists('register_user')) {
    function register_user(string $username, string $email, string $password, string $role = 'customer'): bool|int {
        global $conn;
        if (username_exists($username)) { return false; } // Specific error handled in register.php
        if (email_exists($email)) { return false; } // Specific error handled in register.php

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        if ($hashed_password === false) { error_log("Password hash failed for username: " . $username); return false; }
        $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
        if ($stmt === false) { error_log("Prepare failed for register_user: " . $conn->error); return false; }
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
        if ($stmt->execute()) {
            $new_id = $stmt->insert_id;
            $stmt->close();
            return $new_id;
        } else { error_log("Error registering user ($username): " . $stmt->error); $stmt->close(); return false; }
    }
}

/**
 * Verifies login credentials and rehashes password if needed.
 * @param string $username Username.
 * @param string $password Plain password.
 * @return array|null User data or null.
 */
if (!function_exists('verify_login')) {
    function verify_login(string $username, string $password): ?array {
        global $conn;
        $user = find_user_by_username($username);
        if ($user && isset($user['password_hash']) && password_verify($password, $user['password_hash'])) {
            if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                 $new_hash = password_hash($password, PASSWORD_DEFAULT);
                 if ($new_hash !== false) {
                     $stmt_rehash = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                     if($stmt_rehash) {
                        $stmt_rehash->bind_param("si", $new_hash, $user['id']);
                        if ($stmt_rehash->execute()) { $user['password_hash'] = $new_hash; }
                        else { error_log("Failed to rehash password for user ID " . $user['id'] . ": " . $stmt_rehash->error); }
                        $stmt_rehash->close();
                     } else { error_log("Prepare failed for password rehash: " . $conn->error); }
                 }
            }
            return $user;
        }
        return null;
    }
}

// --- Session & Helper Functions ---

/**
 * Checks if a user is logged in.
 * @return bool True if logged in.
 */
if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}

/**
 * Redirects the user and terminates script.
 * @param string $url URL to redirect to.
 * @return void
 */
if (!function_exists('redirect')) {
    function redirect(string $url): void {
        if (!headers_sent()) {
            header("Location: " . $url);
        } else {
            // Fallback if headers already sent (less ideal)
            echo "<script type='text/javascript'>window.location.href='$url';</script>";
            echo "<noscript><meta http-equiv='refresh' content='0;url=$url' /></noscript>";
        }
        exit();
    }
}

/**
 * Retrieves logged-in user data from session.
 * @return array|null User data or null.
 */
if (!function_exists('get_logged_in_user')) {
    function get_logged_in_user(): ?array {
        if (!is_logged_in()) { return null; }
        if (!isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['email'], $_SESSION['user_role'])) {
            error_log("Incomplete user session data. Forcing logout. Session: " . print_r($_SESSION, true));
            // Consider a more graceful logout or error display here
            // unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['email'], $_SESSION['user_role']);
            // redirect('logout.php?reason=session_error'); // Example
            return null;
        }
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['user_role']
        ];
    }
}

/**
 * Checks if the current logged-in user is an admin.
 * @return bool True if admin.
 */
if (!function_exists('is_admin')) {
    function is_admin(): bool {
        $user = get_logged_in_user(); // Use the getter to ensure session data is consistent
        return $user !== null && isset($user['role']) && $user['role'] === 'admin';
    }
}
?>