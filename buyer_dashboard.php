<?php

// Shows buyer's order history and saved items

require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();

$buyer_id = $_SESSION['user_id'];

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection not established.');
}

// Fetch buyer's transactions
$trans_sql = "SELECT t.*, l.title, l.price, u.full_name as seller_name
              FROM transactions t
              JOIN listings l ON t.listing_id = l.listing_id
              JOIN users u ON t.seller_id = u.user_id
              WHERE t.buyer_id = $buyer_id
              ORDER BY t.created_at DESC";
$trans_result = mysqli_query($conn, $trans_sql);

// Count stats
$active_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM transactions WHERE buyer_id = $buyer_id AND status = 'Pending'"))['total'];
$completed_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM transactions WHERE buyer_id = $buyer_id AND status = 'Sold'"))['total'];
$fav_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM favourites WHERE user_id = $buyer_id"))['total'];

include 'includes/header.php';
?>

<section class="dashboard container">
    <div class="dashboard-header">
        <h1>Buyer Dashboard</h1>
        <p>Track your purchases and saved items</p>
    </div>

    <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
        <div class="stat-card">
            <div class="stat-value"><?php echo $active_count; ?></div>
            <div class="stat-label">Active Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $completed_count; ?></div>
            <div class="stat-label">Completed</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $fav_count; ?></div>
            <div class="stat-label">Saved Items</div>
        </div>
    </div>

    <h2 class="section-title">My Purchases</h2>

    <?php if (mysqli_num_rows($trans_result) > 0): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Seller</th>
                <th>Price</th>
                <th>Status</th>
                <th>Payment</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($trans = mysqli_fetch_assoc($trans_result)): ?>
            <tr>
                <td>
                    <a href="/listing.php?id=<?php echo $trans['listing_id']; ?>" style="font-weight: 600;">
                        <?php echo $trans['title']; ?>
                    </a>
                </td>
                <td><?php echo $trans['seller_name']; ?></td>
                <td>R<?php echo number_format($trans['price'], 2); ?></td>
                <td><span class="badge badge-<?php echo strtolower($trans['status']); ?>"><?php echo $trans['status']; ?></span></td>
                <td><?php echo $trans['payment_method']; ?></td>
                <td>
                    <?php if ($trans['status'] == 'Pending'): ?>
                        <a href="/payment.php?transaction=<?php echo $trans['transaction_id']; ?>" class="btn-small btn-view">Pay</a>
                    <?php elseif ($trans['status'] == 'Sold'): ?>
                        <a href="/my_account.php?rate=<?php echo $trans['transaction_id']; ?>" class="btn-small btn-verify">Rate</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div style="background: var(--white); padding: 48px; border-radius: var(--radius-lg); text-align: center; color: var(--text-light);">
        <i class="ti ti-shopping-cart" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
        <p>No purchases yet. <a href="/listings.php" style="color: var(--primary);">Start browsing</a></p>
    </div>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>