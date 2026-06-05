<?php

// Administrators manage the entire platform from here

require_once 'includes/db.php';
require_once 'includes/auth.php';
require_admin();

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection not established.');
}

// Count statistics
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'];
$total_sellers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'seller'"))['total'];
$total_listings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM listings"))['total'];
$total_transactions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM transactions"))['total'];
$pending_verifications = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'seller' AND is_verified = 0"))['total'];
$open_reports = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM reports WHERE status = 'Open'"))['total'];

// Fetch recent listings
$recent_sql = "SELECT l.*, u.full_name as seller_name FROM listings l JOIN users u ON l.seller_id = u.user_id ORDER BY l.created_at DESC LIMIT 5";
$recent_result = mysqli_query($conn, $recent_sql);

include 'includes/header.php';
?>

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-brand">
            <i class="ti ti-shield"></i> Admin Panel
        </div>
        <a href="/admin_dashboard.php" class="active">
            <i class="ti ti-layout-dashboard"></i> Overview
        </a>
        <a href="/admin_users.php">
            <i class="ti ti-users"></i> Users
        </a>
        <a href="/admin_listings.php">
            <i class="ti ti-shopping-bag"></i> Listings
        </a>
        <a href="/admin_transactions.php">
            <i class="ti ti-transfer"></i> Transactions
        </a>
        <a href="/index.php">
            <i class="ti ti-arrow-left"></i> Back to Site
        </a>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <div class="dashboard-header">
            <h1>Admin Dashboard</h1>
            <p>Platform overview and management</p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_users; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_sellers; ?></div>
                <div class="stat-label">Sellers</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_listings; ?></div>
                <div class="stat-label">Listings</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_transactions; ?></div>
                <div class="stat-label">Transactions</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--warning);"><?php echo $pending_verifications; ?></div>
                <div class="stat-label">Pending Verifications</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--danger);"><?php echo $open_reports; ?></div>
                <div class="stat-label">Open Reports</div>
            </div>
        </div>

        <!-- Recent Listings -->
        <h2 class="section-title">Recent Listings</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Seller</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = mysqli_fetch_assoc($recent_result)): ?>
                <tr>
                    <td><?php echo $item['title']; ?></td>
                    <td><?php echo $item['seller_name']; ?></td>
                    <td>R<?php echo number_format($item['price'], 2); ?></td>
                    <td><span class="badge badge-<?php echo strtolower($item['status']); ?>"><?php echo $item['status']; ?></span></td>
                    <td><?php echo date('d M Y', strtotime($item['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>
</div>

<?php include 'includes/footer.php'; ?>