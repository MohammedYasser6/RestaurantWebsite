# Colibri Restaurant Website & Admin Panel

## Project Overview

Colibri is a full-featured restaurant website built with PHP. It includes a public-facing site for customers to view the menu, make reservations, and place online orders. It also features a comprehensive admin panel for managing menu items, viewing orders, and handling reservations. This project demonstrates dynamic content management, user authentication, session handling, and database interaction (MySQL).

Initially, data was stored in JSON files, but the project has been refactored to use a MySQL database for improved scalability, data integrity, and performance.


**GitHub Repository:** https://github.com/MohammedYasser6/RestaurantWebsite

## Features

**Public Website:**
*   **Dynamic Menu Display:** Menu items fetched from the database and displayed by category.
*   **Online Ordering:**
    *   Interactive cart functionality (add, update quantity, remove items, clear cart).
    *   Checkout process with delivery details and payment simulation (Cash on Delivery & Card).
    *   Order data saved to the database.
*   **Reservations:**
    *   Form for customers to request table reservations.
    *   Reservation data saved to the database.
*   **User Authentication:**
    *   Customer registration and login.
    *   Password hashing for security.
    *   Session management.
    *   Profile page for logged-in users.
*   **Responsive Design:** (Assuming your CSS aims for this) Basic styling for usability across devices.

**Admin Panel (`/admin` directory):**
*   **Secure Access:** Admin login required.
*   **Dashboard:** Overview of key metrics (e.g., total menu items, new orders, pending reservations).
*   **Menu Management (CRUD):**
    *   List all menu items, grouped by category.
    *   Add new menu items with details (ID, name, category, price, description, image).
    *   Edit existing menu items.
    *   Delete menu items (with considerations for items linked to past orders).
    *   Image upload functionality for menu items.
*   **Order Management:**
    *   View a list of all online orders with key details.
    *   View full details of individual orders, including items ordered and customer information.
    *   Update order status (e.g., "New", "Processing", "Fulfilled", "Cancelled").
*   **Reservation Management:**
    *   View a list of all reservation requests.
    *   Update reservation status (e.g., "Pending", "Confirmed", "Completed", "Cancelled", "No-Show").
*   **(Future/Optional) User Management:** (If you plan to add it) Manage user accounts.

## Technologies Used

*   **Backend:** PHP
*   **Database:** MySQL (managed via XAMPP/phpMyAdmin during development)
*   **Frontend:** HTML, CSS (JavaScript for minor interactivity, e.g., payment method toggle)
*   **Web Server (Development):** Apache (via XAMPP)
*   **Version Control:** Git & GitHub

## Project Structure
colibri-restaurant/
*├── admin/ # Admin panel files
*│ ├── index.php # Admin dashboard
*│ ├── menu_manage.php
*│ ├── menu_edit.php
*│ ├── menu_delete.php
*│ ├── orders.php
*│ ├── order_details_view.php
*│ ├── update_order_status.php
*│ ├── reservations.php
*│ ├── update_reservation_status.php
*│ ├── admin_header.php # Common admin page header
*│ └── admin_footer.php # Common admin page footer
*├── data/ # (Optional: Original JSON data files, can be removed/archived)
*│ ├── menu.json
*│ └── ...
*├── images/ # Static images and uploaded menu item images
*│ ├── background.jpg
*│ ├── bowl1.jpg
*│ └── ... (uploaded images)
*├── css/ # (If you have a separate CSS folder)
*│ └── style.css
*├── style.css # Main stylesheet (if in root)
*├── bootstrap.php # Core PHP functions, session start, DB connection include
*├── db_connect.php # MySQL database connection script
*├── index.php # Public homepage
*├── menu.php # Public menu page
*├── order-online.php # Page for customers to place orders
*├── checkout.php # Order checkout page
*├── reserve.php # Reservation form page (You might have this or similar)
*├── login.php # User login page
*├── logout.php # Handles user logout
*├── register.php # User registration page
*├── profile.php # User profile page
*└── README.md # This file
## Setup and Installation (for XAMPP Environment)

1.  **Prerequisites:**
    *   [XAMPP](https://www.apachefriends.org/index.html) installed (which includes Apache, MySQL, PHP, phpMyAdmin).
    *   A code editor (e.g., VS Code, Sublime Text, PhpStorm).
    *   Git (optional, for cloning).

2.  **Clone the Repository (Optional):**
    ```bash
    git clone https://github.com/MohammedYasser6/RestaurantWebsite colibri-restaurant
    cd colibri-restaurant
    ```
    Or download the ZIP from GitHub and extract it.

3.  **Place Project Files:**
    *   Copy the entire project folder (e.g., `colibri-restaurant`) into your XAMPP `htdocs` directory (usually `C:\xampp\htdocs\` on Windows).

4.  **Start XAMPP:**
    *   Open the XAMPP Control Panel.
    *   Start the **Apache** and **MySQL** modules.

5.  **Create the Database:**
    *   Open your web browser and go to `http://localhost/phpmyadmin`.
    *   Click on "New" in the left sidebar.
    *   Enter database name: `colibri_db`
    *   Choose collation: `utf8mb4_general_ci` (or `utf8mb4_unicode_ci`)
    *   Click "Create".

6.  **Create Database Tables:**
    *   Select the `colibri_db` database in phpMyAdmin.
    *   Go to the "SQL" tab.
    *   Copy and paste the SQL `CREATE TABLE` statements (provided in project documentation or a `.sql` file) into the query box. These statements define the `users`, `menu_items`, `orders`, `order_items`, and `reservations` tables.
    *   Click "Go" to execute the statements and create the tables.
    *   **Important:** Ensure your `order_items` foreign key to `menu_items` is set to `ON DELETE CASCADE` if you chose that option, or `ON DELETE SET NULL` if you chose that. The default is `ON DELETE RESTRICT`. (Refer to project issue history or specific SQL schema file for this detail).

7.  **Configure Database Connection:**
    *   Open `db_connect.php` in your code editor.
    *   Verify the database credentials:
        ```php
        $servername = "localhost";
        $username = "root";        // Default XAMPP username
        $password = "";            // Default XAMPP password (empty)
        $dbname = "colibri_db";
        ```
    *   These are the default XAMPP settings. Adjust if your MySQL setup is different.

8.  **Migrate Initial Data (Optional, if using migration scripts):**
    *   If you have existing data in JSON files and migration scripts (`migrate_menu.php`, `migrate_users.php`, etc.) are provided:
        *   Ensure your old `data/` folder with JSON files is in place.
        *   Access each migration script via your browser one by one:
            *   `http://localhost/projectsql/migrate_users.php` (Replace `projectsql` with your actual project folder name in `htdocs`)
            *   `http://localhost/projectsql/migrate_menu.php`
            *   `http://localhost/projectsql/migrate_reservations.php`
            *   `http://localhost/projectsql/migrate_orders.php`
        *   Review the output for any errors.
        *   **Important:** These scripts are for one-time use. They can be removed or secured after successful migration.

9.  **Set File Permissions (Mainly for Image Uploads):**
    *   Ensure the `images/` directory (or `images/uploads/` if you used that) inside your project folder is **writable** by the web server (Apache). In XAMPP on Windows, this is usually not an issue, but on Linux/macOS, you might need to set permissions (e.g., `chmod 775 images`).

10. **Access the Website:**
    *   Open your browser and navigate to `http://localhost/projectsql/` (replace `projectsql` with your project folder name).
    *   To access the admin panel: `http://localhost/projectsql/admin/`
        *   Default admin credentials (if seeded via migration or you create one): **Username:** `admin`, **Password:** (the password you set for the admin user, e.g., `password123`)

## Usage

*   **Public Site:** Browse the menu, register/login, place orders, make reservations.
*   **Admin Panel:** Log in with admin credentials to manage content.
    *   The `admin` user is typically created with the role 'admin'. See `users.json` or your user migration logic for default admin credentials.
    *   Example admin login: `username: admin`, `password: (your admin password)`

## Key PHP Files and Logic

*   **`bootstrap.php`**: Central hub for session management, global functions (database access, user auth, etc.), and including `db_connect.php`.
*   **`db_connect.php`**: Handles the MySQL database connection.
*   **`admin/` directory**: Contains all server-side logic and views for the admin panel.
    *   `menu_edit.php`: Handles adding and updating menu items, including image uploads.
    *   `update_order_status.php` / `update_reservation_status.php`: Process status changes.
*   **Root PHP files (`index.php`, `menu.php`, etc.)**: Handle public-facing pages.
*   **Image Handling**: Images for menu items are uploaded to the `/images/` directory. The path is stored in the `menu_items` table.

## Error Handling & Security

*   **Error Reporting:** `display_errors` is ON for development. For production, this should be turned OFF and errors logged to a file.
*   **SQL Injection Prevention:** Prepared statements (`mysqli_prepare`, `bind_param`, `execute`) are used for database queries involving user input.
*   **Cross-Site Scripting (XSS) Prevention:** `htmlspecialchars()` is used when outputting user-supplied data to HTML.
*   **Password Security:** Passwords are hashed using `password_hash()` and verified with `password_verify()`.
*   **Session Management:** PHP sessions are used to maintain login state. `session_regenerate_id()` is used upon login for security.
*   **Access Control:** Admin pages check for an active admin session.

## Future Enhancements / To-Do

*   [ ] Implement more robust input validation for all forms.
*   [ ] Add advanced filtering and search to admin lists (orders, reservations).
*   [ ] Develop a "soft delete" or "archiving" feature for menu items instead of permanent deletion, to preserve links in old orders if `ON DELETE SET NULL` is not used for the foreign key.
*   [ ] Implement email notifications (e.g., for new orders, reservation confirmations).
*   [ ] Add pagination to admin lists if data grows large.
*   [ ] Enhance frontend UI/UX with more JavaScript for a more dynamic experience.
*   [ ] Write unit/integration tests.
*   [ ] Improve responsive design for mobile/tablet views.
*   [ ] Add user profile editing features (e.g., change password, update delivery addresses).
*   [ ] Implement proper logging for production environments.
