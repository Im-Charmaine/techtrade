<?php

// PURPOSE: Allow sellers to edit their existing listings, including uploading or changing the product photo

require_once 'includes/auth.php';
require_once 'includes/db.php';

require_seller();

$user_id    = $_SESSION['user_id'];
$listing_id = isset($_GET['id']) ? $_GET['id'] : null;
$success    = "";
$error      = "";

if (!$listing_id) {
    header("Location: /seller_dashboard.php");
    exit();
}

// Get listing - make sure it belongs to this seller
$sql     = "SELECT listings.*, categories.name AS category_name
            FROM listings
            JOIN categories ON listings.category_id = categories.category_id
            WHERE listings.listing_id = '$listing_id'
            AND   listings.seller_id  = '$user_id'";
$result  = mysqli_query($conn, $sql);
$listing = mysqli_fetch_assoc($result);

if (!$listing) {
    header("Location: /seller_dashboard.php");
    exit();
}

// Get all categories for the dropdown
$cat_result = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");

// HANDLE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $title       = clean($_POST['title']);
    $description = clean($_POST['description']);
    $price       = $_POST['price'];
    $category_id = $_POST['category_id'];
    $condition   = $_POST['condition_type'];
    $shipping    = clean($_POST['shipping_notes']);
    $image_url   = $listing['image_url']; // keep existing image by default

    if (empty($title) || empty($description) || empty($price) || empty($category_id)) {
        $error = "Please fill in all required fields.";

    } elseif (!is_numeric($price) || $price <= 0) {
        $error = "Please enter a valid price.";

    } else {

        // Handle new image upload if a file was selected
        if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {

            $file    = $_FILES['image'];
            $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = array('jpg', 'jpeg', 'png', 'webp');

            if (!in_array($ext, $allowed)) {
                $error = "Only JPG, PNG and WEBP images are allowed.";

            } elseif ($file['size'] > 2000000) {
                $error = "Image must be smaller than 2MB.";

            } else {
                // Delete old image if it exists
                if ($listing['image_url'] != '' && file_exists("uploads/" . $listing['image_url'])) {
                    unlink("uploads/" . $listing['image_url']);
                }

                // Save new image with unique filename
                $new_name  = uniqid() . "." . $ext;
                move_uploaded_file($file['tmp_name'], "uploads/" . $new_name);
                $image_url = $new_name;
            }
        }

        if ($error == "") {

            // Update the listing in the database
            $update_sql = "UPDATE listings
                           SET title          = '$title',
                               description    = '$description',
                               price          = '$price',
                               category_id    = '$category_id',
                               condition_type = '$condition',
                               shipping_notes = '$shipping',
                               image_url      = '$image_url'
                           WHERE listing_id = '$listing_id'
                           AND   seller_id  = '$user_id'";

            if (mysqli_query($conn, $update_sql)) {
                $success = "Listing updated successfully!";

                // Refresh listing data to show updated values
                $result  = mysqli_query($conn, $sql);
                $listing = mysqli_fetch_assoc($result);

            } else {
                $error = "Could not update listing. Please try again.";
            }
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="tt-main" style="max-width:640px; margin:0 auto">

    <!-- Back link -->
    <a href="/seller_dashboard.php"
       style="font-size:13px; color:var(--blue); display:inline-block; margin-bottom:20px">
        &larr; Back to dashboard
    </a>

    <h2 style="font-size:20px; font-weight:600; margin-bottom:4px; color:var(--text)">
        Edit listing
    </h2>
    <p style="font-size:13px; color:var(--muted); margin-bottom:24px">
        Update your item details or add a photo
    </p>

    <?php if ($error != ""): ?>
        <div class="tt-alert tt-alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success != ""): ?>
        <div class="tt-alert tt-alert-success">
            <?php echo $success; ?>
            <a href="/listing.php?id=<?php echo $listing_id; ?>"
               style="color:var(--sold-text); font-weight:500; margin-left:8px">
                View listing
            </a>
        </div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data" id="listing-form">

        <!-- CURRENT PHOTO + UPLOAD NEW ONE -->
        <div class="form-group">
            <label class="form-label">Product photo</label>

            <!-- Show current image if exists -->
            <?php if ($listing['image_url'] != ''): ?>
                <div style="margin-bottom:12px">
                    <p style="font-size:12px; color:var(--muted); margin-bottom:6px">
                        Current photo:
                    </p>
                    <img src="/uploads/<?php echo $listing['image_url']; ?>"
                         alt="Current photo"
                         style="max-height:200px; border-radius:10px;
                                border:1px solid var(--border); object-fit:cover">
                </div>
            <?php else: ?>
                <div style="background:var(--surface-2); border:1px dashed var(--border);
                            border-radius:10px; padding:20px; text-align:center;
                            margin-bottom:12px; color:var(--muted); font-size:13px">
                    <i class="ti ti-photo-off" style="font-size:28px; display:block; margin-bottom:6px"></i>
                    No photo uploaded yet
                </div>
            <?php endif; ?>

            <!-- Upload new image -->
            <label class="form-label" style="margin-top:8px">
                <?php echo $listing['image_url'] != '' ? 'Replace photo' : 'Upload photo'; ?>
                (JPG, PNG or WEBP, max 2MB)
            </label>
            <input class="form-control" type="file" id="image" name="image"
                   accept="image/*" style="padding:8px">

            <!-- Preview before upload -->
            <img id="image-preview" src="" alt="Preview"
                 style="display:none; margin-top:10px; max-height:200px;
                        border-radius:10px; border:1px solid var(--border)">
        </div>

        <!-- TITLE -->
        <div class="form-group">
            <label class="form-label" for="title">Item title *</label>
            <input class="form-control" type="text" id="title" name="title"
                   value="<?php echo $listing['title']; ?>" required>
            <span class="form-error" id="title-error"></span>
        </div>

        <!-- CATEGORY AND CONDITION -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px">

            <div class="form-group">
                <label class="form-label" for="category_id">Category *</label>
                <select class="form-control" id="category_id" name="category_id" required>
                    <?php while ($cat = mysqli_fetch_assoc($cat_result)): ?>
                        <option value="<?php echo $cat['category_id']; ?>"
                            <?php echo ($cat['category_id'] == $listing['category_id']) ? 'selected' : ''; ?>>
                            <?php echo $cat['name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="condition_type">Condition *</label>
                <select class="form-control" id="condition_type" name="condition_type" required>
                    <?php
                    $conditions = array('new', 'like new', 'good', 'fair', 'poor');
                    foreach ($conditions as $c):
                    ?>
                        <option value="<?php echo $c; ?>"
                            <?php echo ($c == $listing['condition_type']) ? 'selected' : ''; ?>>
                            <?php echo ucfirst($c); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>

        <!-- PRICE -->
        <div class="form-group">
            <label class="form-label" for="price">Price (R) *</label>
            <input class="form-control" type="number" id="price" name="price"
                   value="<?php echo $listing['price']; ?>"
                   min="1" step="0.01" required>
            <span class="form-error" id="price-error"></span>
        </div>

        <!-- DESCRIPTION -->
        <div class="form-group">
            <label class="form-label" for="description">Description *</label>
            <textarea class="form-control" id="description" name="description"
                      rows="5" required><?php echo $listing['description']; ?></textarea>
            <span class="form-error" id="desc-error"></span>
        </div>

        <!-- SHIPPING NOTES -->
        <div class="form-group">
            <label class="form-label" for="shipping_notes">
                Shipping / collection notes
            </label>
            <input class="form-control" type="text" id="shipping_notes"
                   name="shipping_notes"
                   placeholder="e.g. Collection only in Soweto, or can courier for R100"
                   value="<?php echo $listing['shipping_notes']; ?>">
        </div>

        <!-- BUTTONS -->
        <div style="display:flex; gap:10px">
            <button type="submit" class="btn btn-orange" style="flex:1">
                Save changes
            </button>
            <a href="/listing.php?id=<?php echo $listing_id; ?>"
               class="btn btn-outline">
                View listing
            </a>
        </div>

    </form>

</div>

<?php include 'includes/footer.php'; ?>