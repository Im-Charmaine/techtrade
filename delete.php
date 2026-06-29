<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();

// Only sellers and admins can delete
if ($_SESSION['role'] != 'seller' && $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

$listing_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

if ($listing_id > 0) {
    // Verify ownership (admin can delete any)
    if ($_SESSION['role'] == 'admin') {
        $check_sql = "SELECT listing_id, image_url FROM listings WHERE listing_id = $listing_id";
    } else {
        $check_sql = "SELECT listing_id, image_url FROM listings WHERE listing_id = $listing_id AND seller_id = $user_id";
    }
    
    $check_result = mysqli_query($conn, $check_sql);
    $listing = mysqli_fetch_assoc($check_result);
    
    if ($listing) {
        $image_url = $listing['image_url'];
        
        
        mysqli_query($conn, "DELETE FROM cart WHERE listing_id = $listing_id");
        mysqli_query($conn, "DELETE FROM messages WHERE listing_id = $listing_id");
        mysqli_query($conn, "DELETE FROM transactions WHERE listing_id = $listing_id");
        
        // Delete the listing
        $delete_sql = "DELETE FROM listings WHERE listing_id = $listing_id";
        if ($_SESSION['role'] != 'admin') {
            $delete_sql .= " AND seller_id = $user_id";
        }
        
        if (mysqli_query($conn, $delete_sql)) {
            // Delete image file
            if (!empty($image_url) && file_exists('uploads/' . $image_url)) {
                unlink('uploads/' . $image_url);
            }
            
            // Set success message
            $_SESSION['success_message'] = 'Listing deleted successfully.';
        } else {
            $_SESSION['error_message'] = 'Error deleting listing.';
        }
    } else {
        $_SESSION['error_message'] = 'Listing not found or permission denied.';
    }
} else {
    $_SESSION['error_message'] = 'Invalid listing ID.';
}

header('Location: seller_dashboard.php');
exit;
?>