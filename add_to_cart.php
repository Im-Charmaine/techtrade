<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();

if ($_SESSION['role'] != 'buyer') {
    header('Location: index.php');
    exit;
}

$listing_id = intval($_POST['listing_id']);
$user_id = $_SESSION['user_id'];

// Check if already in cart
$check = mysqli_query($conn, "SELECT cart_id FROM cart WHERE user_id = $user_id AND listing_id = $listing_id");

if (mysqli_num_rows($check) > 0) {
    // Update quantity
    mysqli_query($conn, "UPDATE cart SET quantity = quantity + 1 WHERE user_id = $user_id AND listing_id = $listing_id");
} else {
    // Insert new
    mysqli_query($conn, "INSERT INTO cart (user_id, listing_id, quantity) VALUES ($user_id, $listing_id, 1)");
}

header('Location: cart.php');
exit;
?>