<?php

// Admin can view, edit status, and remove listings

require_once 'includes/db.php';
require_once 'includes/auth.php';
require_admin();

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection not established.');
}

// Handle status change
if (isset($_GET['status']) && isset($_GET['id'])) {
    $listing_id = intval($_GET['id']);
    $status = clean($_GET['status']);
    mysqli_query($conn, "UPDATE listings SET status = '$status' WHERE listing_id = $listing_id");
    log_admin_action($conn, "Changed listing status to $status", "listing", $listing_id);
    header("Location: admin_listings.php");
    exit();
}

// Handle listing removal
if (isset($_GET['remove'])) {
    $listing_id = intval($_GET['remove']);
    mysqli_query($conn, "UPDATE listings SET status = 'Cancelled' WHERE listing_id = $listing_id");
    log_admin_action($conn, "Removed listing", "listing", $listing_id);
    header("Location: admin_listings.php");
    exit();
}

// Fetch all listings with seller info
$listings_sql = "SELECT l.*, u.full_name as seller_name, c.name as category_name
                 FROM listings l
                 JOIN users u ON l.seller_id = u.user_id
                 JOIN categories c ON l.category_id = c.category_id
                 ORDER BY l.created_at DESC";
$listings_result = mysqli_query($conn, $listings_sql);

include 'includes/header.php';
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="sidebar-brand"><i class="ti ti-shield"></i> Admin Panel</div>
        <a href="/techtrade/admin_dashboard.php"><i class="ti ti-layout-dashboard"></i> Overview</a>
        <a href="/techtrade/admin_users.php"><i class="ti ti-users"></i> Users</a>
        <a href="/techtrade/admin_listings.php" class="active"><i class="ti ti-shopping-bag"></i> Listings</a>
        <a href="/techtrade/admin_transactions.php"><i class="ti ti-transfer"></i> Transactions</a>
        <a href="/techtrade/index.php"><i class="ti ti-arrow-left"></i> Back to Site</a>
    </aside>

    <main class="admin-main">
        <div class="dashboard-header">
            <h1>Manage Listings</h1>
            <p>View and moderate all product listings</p>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Seller</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = mysqli_fetch_assoc($listings_result)): ?>
                <tr>
                    <td><strong><?php echo $item['title']; ?></strong></td>
                    <td><?php echo $item['seller_name']; ?></td>
                    <td><?php echo $item['category_name']; ?></td>
                    <td>R<?php echo number_format($item['price'], 2); ?></td>
                    <td><span class="badge badge-<?php echo strtolower($item['status']); ?>"><?php echo $item['status']; ?></span></td>
                    <td><?php echo date('d M Y', strtotime($item['created_at'])); ?></td>
                    <td>
                        <a href="/techtrade/listing.php?id=<?php echo $item['listing_id']; ?>" class="btn-small btn-view">View</a>
                        <?php if ($item['status'] != 'Sold' && $item['status'] != 'Cancelled'): ?>
                            <a href="admin_listings.php?status=Sold&id=<?php echo $item['listing_id']; ?>" class="btn-small btn-verify">Mark Sold</a>
                            <a href="admin_listings.php?remove=<?php echo $item['listing_id']; ?>" 
                               class="btn-small btn-delete"
                               onclick="return confirmDelete('Remove this listing?')">Remove</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>
</div>

<?php include 'includes/footer.php'; ?>