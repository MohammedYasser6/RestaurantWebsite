<?php
// This file assumes IS_ADMIN_PAGE was defined and bootstrap was included by the parent page.
if (!defined('IS_ADMIN_PAGE')) {
    // Optional: Redirect or show error if accessed directly, though less critical for a simple footer.
    // die("Cannot access this file directly.");
}
?>
    <footer class="admin-footer">
        <p>&copy; <?php echo date('Y'); ?> <a href="http://colibri.com">Colibri</a> - Admin Panel</p>
    </footer>
    </div> <!-- Close admin-container from admin_header.php -->
</body>
</html>
