<?php
require_once '../bootstrap.php'; // Defines IS_ADMIN_PAGE after this line is better
define('IS_ADMIN_PAGE', true);

if (!is_admin()) { redirect('../login.php?error=Unauthorized'); }

global $conn; // Use global database connection

$edit_item_id_from_url = $_GET['id'] ?? null;
$is_editing = ($edit_item_id_from_url !== null);
$page_title = $is_editing ? "Edit Menu Item" : "Add New Menu Item";

// Initialize form data array
$item_data = [
    'item_id' => $edit_item_id_from_url ?? '',
    'name' => '',
    'price' => '',
    'description' => '',
    'category' => '',
    'image_path' => '' // This will hold the relative path like "images/filename.jpg"
];
$final_item_id_for_db = $item_data['item_id']; // Initialize this early

// If editing, load item data from database
if ($is_editing) {
    $stmt_load = $conn->prepare("SELECT item_id, name, price, description, category, image_path FROM menu_items WHERE item_id = ?");
    if ($stmt_load) {
        $stmt_load->bind_param("s", $edit_item_id_from_url);
        $stmt_load->execute();
        $result = $stmt_load->get_result();
        if ($loaded_item = $result->fetch_assoc()) {
            $item_data = $loaded_item; // Overwrite with DB data
            $final_item_id_for_db = $item_data['item_id']; // Confirm this for editing
        } else {
            $_SESSION['admin_feedback'] = "Item with ID '" . htmlspecialchars($edit_item_id_from_url) . "' not found for editing.";
            $_SESSION['admin_feedback_type'] = 'error';
            redirect('menu_manage.php');
        }
        $stmt_load->close();
    } else {
        // Handle prepare error
        $_SESSION['admin_feedback'] = "Database error preparing to load item.";
        $_SESSION['admin_feedback_type'] = 'error';
        error_log("Prepare failed in menu_edit (load): " . $conn->error);
        redirect('menu_manage.php');
    }
}


// --- Handle Form Submission (Add/Update) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_id_field = trim($_POST['item_id_field'] ?? ''); // ID from form (only for new items)
    $name = trim($_POST['name'] ?? '');
    $price_str = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    // Path of image already in DB (if editing and no new image uploaded)
    $existing_image_db_path = $_POST['existing_image_path_hidden'] ?? ($item_data['image_path'] ?? '');

    $price = filter_var($price_str, FILTER_VALIDATE_FLOAT);
    $errors = [];

    // Determine the item ID to use for DB operations
    if ($is_editing) {
        $final_item_id_for_db = $edit_item_id_from_url; // Use ID from URL for existing items
    } else {
        $final_item_id_for_db = $submitted_id_field; // Use ID from form for new items
        if (empty($final_item_id_for_db)) {
            $errors[] = "Item ID is required for new items.";
        } else {
            // Check if new ID already exists
            $check_stmt = $conn->prepare("SELECT item_id FROM menu_items WHERE item_id = ?");
            if ($check_stmt) {
                $check_stmt->bind_param("s", $final_item_id_for_db);
                $check_stmt->execute();
                if ($check_stmt->get_result()->num_rows > 0) {
                    $errors[] = "Item ID '" . htmlspecialchars($final_item_id_for_db) . "' already exists. Choose a unique ID.";
                }
                $check_stmt->close();
            } else {
                $errors[] = "Database error checking item ID uniqueness.";
                error_log("Prepare failed for ID uniqueness check: " . $conn->error);
            }
        }
    }

    if (empty($name)) { $errors[] = "Name is required."; }
    if (empty($category)) { $errors[] = "Category is required."; }
    if ($price === false || $price < 0) { $errors[] = "Price must be a valid positive number."; }
    // Add more validation as needed

    if (empty($errors)) {
        $new_image_path_for_db = $existing_image_db_path; // Assume existing image path

        // --- Handle Image Upload ---
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
             $upload_dir_relative_for_db = IMAGE_UPLOAD_DIR_REL; // "images/"
             $upload_dir_absolute_for_php = IMAGE_UPLOAD_DIR_ABS; // "C:/xampp/htdocs/projectsql/images/"

             if (!is_dir($upload_dir_absolute_for_php)) {
                 $errors[] = "Image directory does not exist: " . htmlspecialchars($upload_dir_absolute_for_php);
                 goto show_form_with_errors;
             }
             if (!is_writable($upload_dir_absolute_for_php)) {
                 $errors[] = "Image directory is not writable: " . htmlspecialchars($upload_dir_absolute_for_php);
                 goto show_form_with_errors;
             }

             $tmp_name = $_FILES['image_file']['tmp_name'];
             $original_name = basename($_FILES['image_file']['name']);
             $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
             $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
             $max_file_size = 2 * 1024 * 1024; // 2MB

             if (!in_array($file_extension, $allowed_extensions)) {
                 $errors[] = "Invalid image file type. Allowed: " . implode(', ', $allowed_extensions);
             } elseif ($_FILES['image_file']['size'] > $max_file_size) {
                 $errors[] = "Image file is too large (Max: " . ($max_file_size / 1024 / 1024) . "MB).";
             } else {
                  $safe_id = preg_replace('/[^a-zA-Z0-9_-]/', '_', $final_item_id_for_db);
                  $new_filename = $safe_id . '_' . time() . '.' . $file_extension;
                  $destination_absolute = rtrim($upload_dir_absolute_for_php, '/') . '/' . $new_filename;

                  if (move_uploaded_file($tmp_name, $destination_absolute)) {
                       $new_image_path_for_db = rtrim($upload_dir_relative_for_db, '/') . '/' . $new_filename; // e.g., "images/D01_timestamp.jpg"

                       if ($is_editing && $existing_image_db_path && $existing_image_db_path !== $new_image_path_for_db) {
                           $old_image_absolute_path = __DIR__ . '/../' . $existing_image_db_path; // Assumes $existing_image_db_path is "images/file.jpg"
                           if (file_exists($old_image_absolute_path) && strpos($old_image_absolute_path, 'placeholder.com') === false) {
                               if (!@unlink($old_image_absolute_path)) {
                                    error_log("Admin: Could not delete old image file: " . $old_image_absolute_path);
                               } else {
                                    error_log("Admin: Deleted old image file: " . $old_image_absolute_path);
                               }
                           }
                       }
                  } else {
                       $errors[] = "Failed to move uploaded image to: " . htmlspecialchars($destination_absolute);
                       error_log("Failed move_uploaded_file: " . $destination_absolute . " from " . $tmp_name . ". Check permissions.");
                  }
             }
        } elseif (isset($_FILES['image_file']) && $_FILES['image_file']['error'] !== UPLOAD_ERR_NO_FILE) {
              $errors[] = "Image upload error (Code: " . $_FILES['image_file']['error'] . ").";
              error_log("File upload error code: " . $_FILES['image_file']['error']);
        }

        // If still no errors after image handling
        if (empty($errors)) {
            if ($is_editing) {
                $stmt_save = $conn->prepare("UPDATE menu_items SET name = ?, price = ?, description = ?, category = ?, image_path = ? WHERE item_id = ?");
                if($stmt_save) $stmt_save->bind_param("sdssss", $name, $price, $description, $category, $new_image_path_for_db, $final_item_id_for_db);
            } else {
                $stmt_save = $conn->prepare("INSERT INTO menu_items (item_id, name, price, description, category, image_path) VALUES (?, ?, ?, ?, ?, ?)");
                if($stmt_save) $stmt_save->bind_param("ssdsss", $final_item_id_for_db, $name, $price, $description, $category, $new_image_path_for_db);
            }

            if ($stmt_save && $stmt_save->execute()) {
                $_SESSION['admin_feedback'] = "Item '" . htmlspecialchars($name) . "' saved successfully.";
                $_SESSION['admin_feedback_type'] = 'success';
                $stmt_save->close();
                redirect("menu_manage.php");
            } else {
                $db_error = $stmt_save ? $stmt_save->error : $conn->error;
                $errors[] = "Database error saving item: " . $db_error;
                error_log("DB save error for item ID " . $final_item_id_for_db . ": " . $db_error);
                if($stmt_save) $stmt_save->close();
            }
        }
    }

    show_form_with_errors:
    if (!empty($errors)) {
        $_SESSION['admin_feedback'] = implode('<br>', $errors);
        $_SESSION['admin_feedback_type'] = 'error';
        // Repopulate $item_data with submitted values for form redisplay
        $item_data['item_id'] = $final_item_id_for_db;
        $item_data['name'] = $name;
        $item_data['price'] = $price_str;
        $item_data['description'] = $description;
        $item_data['category'] = $category;
        // If image upload failed, $new_image_path_for_db might be the old path or an attempted new one.
        // It's safer to revert to the known existing path on error unless a new one was successfully moved.
        // The $new_image_path_for_db is updated only on successful move_uploaded_file.
        $item_data['image_path'] = $new_image_path_for_db;
    }
}

include 'admin_header.php'; // This includes the <head> and initial <body> tags
?>
    <!-- Page Title is usually set in admin_header.php based on the script name or passed variable -->
    <!-- Or set it here if you prefer more control per page -->
    <title><?php echo $page_title; ?> - Colibri Admin</title>

    <h2><?php echo $page_title; ?></h2>

    <form action="menu_edit.php<?php echo $is_editing ? '?id=' . urlencode($edit_item_id_from_url) : ''; ?>" method="post" class="admin-form" enctype="multipart/form-data">
        <div class="form-group">
            <label for="item_id_field">Item ID (Unique Code, e.g., B01, S03):</label>
            <input type="text" id="item_id_field" name="item_id_field" value="<?php echo htmlspecialchars($item_data['item_id']); ?>" <?php echo $is_editing ? 'readonly' : 'required'; ?>>
            <?php if ($is_editing): ?> <small class="help-text" style="color: #666;">Cannot change ID after creation.</small> <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($item_data['name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="category">Category:</label>
            <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($item_data['category']); ?>" required placeholder="e.g., Bowls, Appetizers, Drinks">
        </div>
        <div class="form-group">
            <label for="price">Price:</label>
            <input type="number" step="0.01" min="0" id="price" name="price" value="<?php echo htmlspecialchars($item_data['price']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($item_data['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="image_file">Image File (Optional<?php echo $is_editing ? ', replaces current' : ''; ?>):</label>
            <input type="file" id="image_file" name="image_file" accept="image/jpeg,image/png,image/gif,image/webp">
            <small class="help-text" style="color: #666;">Max 2MB. Allowed types: jpg, png, gif, webp.</small>
            
            <input type="hidden" name="existing_image_path_hidden" value="<?php echo htmlspecialchars($item_data['image_path']); ?>">

            <?php if ($is_editing && !empty($item_data['image_path'])):
                 // $item_data['image_path'] is like "images/filename.jpg"
                 $current_img_src_relative = $item_data['image_path'];
                 $current_img_src_absolute_check = __DIR__ . '/../' . $current_img_src_relative; // Path for file_exists

                 if (file_exists($current_img_src_absolute_check)) {
                     $current_img_display_url = '../' . htmlspecialchars($current_img_src_relative); // Relative path for browser
                 } else {
                     $current_img_display_url = 'https://via.placeholder.com/100x60/eee/aaa?text=Not+Found';
                 }
            ?>
                <div class="current-image-admin" style="margin-top: 10px;">
                    <strong style="font-size: 13px;">Current Image:</strong><br>
                    <img src="<?php echo $current_img_display_url; ?>" alt="Current image for <?php echo htmlspecialchars($item_data['name']); ?>" style="max-height: 60px; max-width: 100px; vertical-align: middle; margin-top: 5px; border: 1px solid #ddd; border-radius: 3px;">
                    <code style="font-size: 12px; color: #555; margin-left: 10px;"><?php echo htmlspecialchars($item_data['image_path']); ?></code>
                </div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary"><?php echo $is_editing ? 'Update Item' : 'Add Item'; ?></button>
        <a href="menu_manage.php" style="margin-left: 15px; color: #555; text-decoration: none;">Cancel</a>
    </form>

<?php
// The admin_footer.php will close the .admin-container div and body/html tags
include 'admin_footer.php';
?>