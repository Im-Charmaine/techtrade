<?php
// Seller Dashboard — Manage listings and view activity

require_once 'includes/db.php';
require_once 'includes/auth.php';
require_seller();

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection not established.');
}

$seller_id = $_SESSION['user_id'];

// Fetch seller's listings
$listings_sql = "SELECT * FROM listings WHERE seller_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $listings_sql);
mysqli_stmt_bind_param($stmt, "i", $seller_id);
mysqli_stmt_execute($stmt);
$listings_result = mysqli_stmt_get_result($stmt);

// Fetch seller stats
$total_listings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM listings WHERE seller_id = $seller_id"))['total'];
$active_listings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM listings WHERE seller_id = $seller_id AND status = 'Listed'"))['total'];
$sold_listings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM listings WHERE seller_id = $seller_id AND status = 'Sold'"))['total'];

include 'includes/header.php';
?>

<div class="container" style="padding: 32px 20px;">
    <div class="dashboard-header">
        <h1>Seller Dashboard</h1>
        <p>Manage your listings and track sales</p>
    </div>

    <!--stats-->
    <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 32px;">
        <div class="stat-card">
            <div class="stat-value"><?php echo $total_listings; ?></div>
            <div class="stat-label">Total Listings</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: var(--primary);"><?php echo $active_listings; ?></div>
            <div class="stat-label">Active</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: var(--success);"><?php echo $sold_listings; ?></div>
            <div class="stat-label">Sold</div>
        </div>
    </div>

    <!-- Messages Card -->
    <div class="dashboard-card" style="background: var(--bg-card); border: 1px solid rgba(100,100,200,0.2); border-radius: 12px; padding: 24px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h3 style="margin-bottom: 8px;"><i class="ti ti-message-circle" style="color: var(--primary);"></i> Messages</h3>
                <p style="color: var(--text-light); font-size: 14px;">View and reply to buyer inquiries</p>
            </div>
            <?php
            // Count unread messages for this seller
            $unread_sql = "SELECT COUNT(*) as unread FROM messages WHERE receiver_id = $seller_id AND is_read = 0";
            $unread_result = mysqli_query($conn, $unread_sql);
            $unread = mysqli_fetch_assoc($unread_result)['unread'];
            if ($unread > 0):
            ?>
                <span style="background: var(--danger); color: white; font-size: 14px; font-weight: 700; padding: 4px 12px; border-radius: 20px;">
                    <?php echo $unread; ?> new
                </span>
            <?php endif; ?>
        </div>
        <a href="messages.php" class="btn-primary" style="margin-top: 16px; display: inline-block;">
            <i class="ti ti-inbox"></i> View Messages
        </a>
    </div>

    <!-- Actions -->
    <div style="margin-bottom: 24px;">
        <a href="post_listing.php" class="btn-primary">
            <i class="ti ti-plus"></i> Post New Listing
        </a>

    </div>

    <!-- Listings Table -->
    <h2 class="section-title">My Listings</h2>

    <?php if (mysqli_num_rows($listings_result) == 0): ?>
        <div class="alert" style="background: var(--surface); padding: 40px; text-align: center; border-radius: var(--radius-lg);">
            <i class="ti ti-package" style="font-size: 48px; color: var(--text-light);"></i>
            <p style="margin-top: 16px; color: var(--text-light);">No listings yet. Start selling!</p>
            <a href="post_listing.php" class="btn-primary" style="margin-top: 16px;">Create Listing</a>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = mysqli_fetch_assoc($listings_result)): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <?php if (!empty($item['image_url'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($item['image_url']); ?>"
                                        alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: var(--radius);">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: var(--surface); border-radius: var(--radius); display: flex; align-items: center; justify-content: center;">
                                        <i class="ti ti-photo" style="color: var(--text-light);"></i>
                                    </div>
                                <?php endif; ?>
                                <span><?php echo htmlspecialchars($item['title']); ?></span>
                            </div>
                        </td>
                        <td>R<?php echo number_format($item['price'], 2); ?></td>
                        <td>
                            <span class="badge badge-<?php echo strtolower($item['status']); ?>">
                                <?php echo $item['status']; ?>
                            </span>
                        </td>
                        <td><?php echo date('d M Y', strtotime($item['created_at'])); ?></td>
                        <td>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <a href="listing.php?id=<?php echo $item['listing_id']; ?>"
                                    class="btn-small btn-view" target="_blank">
                                    <i class="ti ti-eye"></i> View
                                </a>

                                <?php if ($item['status'] == 'Listed'): ?>
                                    <a href="update_status.php?id=<?php echo $item['listing_id']; ?>&status=Sold"
                                        class="btn-small"
                                        style="background: var(--success); color: white;"
                                        onclick="return confirm('Mark this listing as sold?')">
                                        <i class="ti ti-check"></i> Mark Sold
                                    </a>
                                <?php endif; ?>

                                <a href="delete_listing.php?id=<?php echo $item['listing_id']; ?>"
                                    class="btn-small btn-delete"
                                    onclick="return confirm('Delete this listing permanently?')">
                                    <i class="ti ti-trash"></i> Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>