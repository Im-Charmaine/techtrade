<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_seller();

$listing_id = intval($_GET['id']);
$status = $_GET['status']; // 'Sold' or 'Listed'

if (!in_array($status, ['Listed', 'Sold', 'Pending', 'Cancelled'])) {
    die('Invalid status');
}

$stmt = mysqli_prepare($conn, "UPDATE listings SET status = ? WHERE listing_id = ? AND seller_id = ?");
mysqli_stmt_bind_param($stmt, "sii", $status, $listing_id, $_SESSION['user_id']);

if (mysqli_stmt_execute($stmt)) {
    header('Location: seller_dashboard.php');
    exit;
} else {
    die('Error updating status');
}
?>