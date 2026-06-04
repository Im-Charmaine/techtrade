<?php

// Admin monitors deals between buyers and sellers

require_once 'includes/db.php';
require_once 'includes/auth.php';
require_admin();

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection not established.');
}

// Fetch all transactions
$trans_sql = "SELECT t.*, l.title, l.price, b.full_name as buyer_name, s.full_name as seller_name
              FROM transactions t
              JOIN listings l ON t.listing_id = l.listing_id
              JOIN users b ON t.buyer_id = b.user_id
              JOIN users s ON t.seller_id = s.user_id
              ORDER BY t.created_at DESC";
$trans_result = mysqli_query($conn, $trans_sql);

// Transaction stats by status
$status_stats = [];
$status_sql = "SELECT status, COUNT(*) as count FROM transactions GROUP BY status";
$status_result = mysqli_query($conn, $status_sql);
while ($row = mysqli_fetch_assoc($status_result)) {
    $status_stats[$row['status']] = $row['count'];
}

include 'includes/header.php';
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="sidebar-brand"><i class="ti ti-shield"></i> Admin Panel</div>
        <a href="/techtrade/admin_dashboard.php"><i class="ti ti-layout-dashboard"></i> Overview</a>
        <a href="/techtrade/admin_users.php"><i class="ti ti-users"></i> Users</a>
        <a href="/techtrade/admin_listings.php"><i class="ti ti-shopping-bag"></i> Listings</a>
        <a href="/techtrade/admin_transactions.php" class="active"><i class="ti ti-transfer"></i> Transactions</a>
        <a href="/techtrade/index.php"><i class="ti ti-arrow-left"></i> Back to Site</a>
    </aside>

    <main class="admin-main">
        <div class="dashboard-header">
            <h1>Transactions</h1>
            <p>Monitor all buyer-seller deals on the platform</p>
        </div>

        <!-- Status Summary -->
        <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 32px;">
            <div class="stat-card">
                <div class="stat-value"><?php echo isset($status_stats['Pending']) ? $status_stats['Pending'] : 0; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--success);"><?php echo isset($status_stats['Sold']) ? $status_stats['Sold'] : 0; ?></div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--danger);"><?php echo isset($status_stats['Cancelled']) ? $status_stats['Cancelled'] : 0; ?></div>
                <div class="stat-label">Cancelled</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo isset($status_stats['Listed']) ? $status_stats['Listed'] : 0; ?></div>
                <div class="stat-label">Listed</div>
            </div>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Buyer</th>
                    <th>Seller</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Delivery</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($trans = mysqli_fetch_assoc($trans_result)): ?>
                <tr>
                    <td><strong><?php echo $trans['title']; ?></strong></td>
                    <td><?php echo $trans['buyer_name']; ?></td>
                    <td><?php echo $trans['seller_name']; ?></td>
                    <td>R<?php echo number_format($trans['price'], 2); ?></td>
                    <td><span class="badge badge-<?php echo strtolower($trans['status']); ?>"><?php echo $trans['status']; ?></span></td>
                    <td><?php echo $trans['payment_method']; ?></td>
                    <td><?php echo $trans['delivery_method']; ?></td>
                    <td><?php echo date('d M Y', strtotime($trans['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>
</div>

<?php include 'includes/footer.php'; ?>