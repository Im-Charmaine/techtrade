<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_seller();

$listing_id = intval($_GET['id']);
$seller_id = $_SESSION['user_id'];

// Verify ownership before deleting
$check = mysqli_prepare($conn, "SELECT image_url FROM listings WHERE listing_id = ? AND seller_id = ?");
mysqli_stmt_bind_param($check, "ii", $listing_id, $seller_id);
mysqli_stmt_execute($check);
$result = mysqli_stmt_get_result($check);
$listing = mysqli_fetch_assoc($result);

if (!$listing) {
    die('Listing not found or access denied');
}

// Delete image file if exists
if (!empty($listing['image_url']) && file_exists('uploads/' . $listing['image_url'])) {
    unlink('uploads/' . $listing['image_url']);
}

// Delete from database
$stmt = mysqli_prepare($conn, "DELETE FROM listings WHERE listing_id = ? AND seller_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $listing_id, $seller_id);
mysqli_stmt_execute($stmt);

header('Location: seller_dashboard.php');
exit;
?>