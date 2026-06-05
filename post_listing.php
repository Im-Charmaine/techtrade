<?php

// Uses prepared statements and file upload validation

require_once 'includes/db.php';
require_once 'includes/auth.php';
require_seller();

$error = '';
$success = '';

$title = '';
$description = '';
$price = 0.0;
$category_id = 0;
$condition_status = '';
$seller_id = 0;
$image_url = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Clean all text inputs
    $title = clean($_POST['title']);
    $description = clean($_POST['description']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $condition_status = clean($_POST['condition_status']);
    $seller_id = $_SESSION['user_id'];

    // Handle image upload with VALIDATION
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {

        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $file_size = $_FILES['image']['size'];
        $max_size = 5 * 1024 * 1024; // 5MB in bytes

        // Check file type
        if (!in_array($ext, $allowed)) {
            $error = 'Invalid file type. Only JPG, PNG, and GIF are allowed.';
        }
        // Check file size (max 5MB)
        elseif ($file_size > $max_size) {
            $error = 'Image is too large. Maximum size is 5MB.';
        }
        else {
            // All checks passed — proceed with upload
            $new_name = time() . '_' . basename($filename);
            $upload_dir = __DIR__ . '/uploads/';
            $upload_path = $upload_dir . $new_name;

            // Create uploads directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_url = $new_name;
            } else {
                $error = 'Failed to save image. Please check uploads folder permissions.';
            }
        }
    }

    // Validate required fields
    if ($title == '' || $price <= 0 || $category_id == 0) {
        $error = 'Please fill in all required fields.';
    } elseif ($error == '') {

        // SECURE INSERT with prepared statement
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO listings (seller_id, category_id, title, description, price, condition_status, image_url, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $status = 'Listed';
        mysqli_stmt_bind_param($stmt, "iissdsss", $seller_id, $category_id, $title, $description, $price, $condition_status, $image_url, $status);

        if (mysqli_stmt_execute($stmt)) {
            $success = 'Listing created successfully! Your item is now live.';
        } else {
            $error = 'Error creating listing. Please try again.';
        }

        mysqli_stmt_close($stmt);
    }
}

// Fetch categories for dropdown
$cat_sql = "SELECT * FROM categories ORDER BY name";
$cat_result = mysqli_query($conn, $cat_sql);

include 'includes/header.php';
?>

<section class="form-section" style="align-items: flex-start; padding-top: 40px;">
    <div class="form-card" style="max-width: 600px;">
        <h2>Post a Listing</h2>
        <p class="subtitle">Sell your electronics to buyers in your community</p>

        <?php if ($error != ''): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success != ''): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form action="post_listing.php" method="POST" enctype="multipart/form-data" onsubmit="return validateListing()">
            <div class="form-group">
                <label>Product Title *</label>
                <input type="text" name="title" id="title" placeholder="e.g. iPhone 13 Pro 256GB" required>
            </div>

            <div class="form-group">
                <label>Category *</label>
                <select name="category_id" id="category_id" required>
                    <option value="">Select category...</option>
                    <?php while ($cat = mysqli_fetch_assoc($cat_result)): ?>
                        <option value="<?php echo $cat['category_id']; ?>"><?php echo $cat['name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Condition *</label>
                <select name="condition_status" required>
                    <option value="New">New</option>
                    <option value="Used - Like New" selected>Used - Like New</option>
                    <option value="Used - Good">Used - Good</option>
                    <option value="Used - Fair">Used - Fair</option>
                </select>
            </div>

            <div class="form-group">
                <label>Price (R) *</label>
                <input type="number" name="price" id="price" placeholder="e.g. 4500" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" placeholder="Describe your item - specs, condition, why you're selling, etc."></textarea>
            </div>

            <div class="form-group">
                <label>Product Photo</label>
                <input type="file" name="image" accept="image/*">
                <small style="color: var(--text-light);">JPG, PNG, or GIF. Max 5MB.</small>
            </div>

            <button type="submit" class="form-btn">
                <i class="ti ti-plus"></i> Post Listing
            </button>
        </form>
    </div>
</section>

<?php include 'includes/footer.php'; ?>