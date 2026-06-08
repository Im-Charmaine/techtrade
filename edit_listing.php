<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_seller();

$listing_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$seller_id = $_SESSION['user_id'];

// Fetch listing
$stmt = mysqli_prepare($conn, "SELECT * FROM listings WHERE listing_id = ? AND seller_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $listing_id, $seller_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$listing = mysqli_fetch_assoc($result);

if (!$listing) {
    die("Listing not found or access denied.");
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = clean($_POST['title']);
    $description = clean($_POST['description']);
    $price = floatval($_POST['price']);
    $condition_status = clean($_POST['condition_status']);
    $image_url = $listing['image_url'];

    // Handle new image
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $_FILES['image']['size'] <= 5 * 1024 * 1024) {
            $new_name = time() . '_' . basename($_FILES['image']['name']);
            $upload_path = __DIR__ . '/uploads/' . $new_name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image
                if (!empty($listing['image_url']) && file_exists('uploads/' . $listing['image_url'])) {
                    unlink('uploads/' . $listing['image_url']);
                }
                $image_url = $new_name;
            }
        }
    }

    $update = mysqli_prepare($conn, "UPDATE listings SET title = ?, description = ?, price = ?, condition_status = ?, image_url = ? WHERE listing_id = ? AND seller_id = ?");
    mysqli_stmt_bind_param($update, "ssdssii", $title, $description, $price, $condition_status, $image_url, $listing_id, $seller_id);
    
    if (mysqli_stmt_execute($update)) {
        $message = '<div class="alert alert-success">Listing updated!</div>';
        // Refresh data
        mysqli_stmt_execute($stmt);
        $listing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    } else {
        $message = '<div class="alert alert-error">Update failed.</div>';
    }
}

include 'includes/header.php';
?>

<div class="container" style="padding: 32px 20px; max-width: 600px;">
    <h1>Edit Listing</h1>
    <?php echo $message; ?>
    
    <form method="POST" enctype="multipart/form-data" class="form-card">
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($listing['title']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="4"><?php echo htmlspecialchars($listing['description']); ?></textarea>
        </div>
        
        <div class="form-group">
            <label>Price (R)</label>
            <input type="number" name="price" step="0.01" value="<?php echo $listing['price']; ?>" required>
        </div>
        
        <div class="form-group">
            <label>Condition</label>
            <select name="condition_status">
                <option value="New" <?php echo $listing['condition_status'] == 'New' ? 'selected' : ''; ?>>New</option>
                <option value="Used - Like New" <?php echo $listing['condition_status'] == 'Used - Like New' ? 'selected' : ''; ?>>Used - Like New</option>
                <option value="Used - Good" <?php echo $listing['condition_status'] == 'Used - Good' ? 'selected' : ''; ?>>Used - Good</option>
                <option value="Used - Fair" <?php echo $listing['condition_status'] == 'Used - Fair' ? 'selected' : ''; ?>>Used - Fair</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Current Image</label>
            <?php if (!empty($listing['image_url'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($listing['image_url']); ?>" style="max-width: 200px; border-radius: 8px; display: block; margin-bottom: 10px;">
            <?php else: ?>
                <p>No image</p>
            <?php endif; ?>
            <input type="file" name="image" accept="image/*">
            <small>Leave empty to keep current image. JPG, PNG, GIF. Max 5MB.</small>
        </div>
        
        <button type="submit" class="form-btn">Save Changes</button>
        <a href="seller_dashboard.php" style="display: block; text-align: center; margin-top: 12px; color: var(--text-light);">Cancel</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>