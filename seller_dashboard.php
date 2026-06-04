<?php
// Seller dashboard with buyer notifications and messages

require_once 'includes/db.php';
require_once 'includes/auth.php';
require_seller();

$seller_id = $_SESSION['user_id'];

// Count stats
$listings_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM listings WHERE seller_id = $seller_id"))['total'];
$active_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM listings WHERE seller_id = $seller_id AND status = 'Listed'"))['total'];
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM transactions WHERE seller_id = $seller_id AND status = 'Pending'"))['total'];
$sold_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM transactions WHERE seller_id = $seller_id AND status = 'Sold'"))['total'];

// Count unread messages
$unread_sql = "SELECT COUNT(*) as total FROM messages WHERE receiver_id = $seller_id AND is_read = 0";
$unread_count = mysqli_fetch_assoc(mysqli_query($conn, $unread_sql))['total'];

// Fetch seller's listings
$listings_sql = "SELECT l.*, c.name as category_name
                 FROM listings l
                 JOIN categories c ON l.category_id = c.category_id
                 WHERE l.seller_id = $seller_id
                 ORDER BY l.created_at DESC";
$listings_result = mysqli_query($conn, $listings_sql);

// Fetch pending transactions with buyer info
$trans_sql = "SELECT t.*, l.title, l.price, l.listing_id, u.full_name as buyer_name, u.user_id as buyer_id, u.phone as buyer_phone
              FROM transactions t
              JOIN listings l ON t.listing_id = l.listing_id
              JOIN users u ON t.buyer_id = u.user_id
              WHERE t.seller_id = $seller_id AND t.status = 'Pending'
              ORDER BY t.created_at DESC";
$trans_result = mysqli_query($conn, $trans_sql);

// Fetch recent messages from buyers
$msg_sql = "SELECT m.*, u.full_name as sender_name, l.title as listing_title
            FROM messages m
            JOIN users u ON m.sender_id = u.user_id
            LEFT JOIN listings l ON m.listing_id = l.listing_id
            WHERE m.receiver_id = $seller_id
            ORDER BY m.created_at DESC
            LIMIT 5";
$msg_result = mysqli_query($conn, $msg_sql);

include 'includes/header.php';
?>

<section class="dashboard container">
    <div class="dashboard-header">
        <h1>Seller Dashboard</h1>
        <p>Manage your listings and track your sales</p>
    </div>
    
    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?php echo $listings_count; ?></div>
            <div class="stat-label">Total Listings</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $active_count; ?></div>
            <div class="stat-label">Active Listings</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: var(--accent);"><?php echo $pending_count; ?></div>
            <div class="stat-label">Pending Deals</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: var(--success);"><?php echo $sold_count; ?></div>
            <div class="stat-label">Completed Sales</div>
        </div>
    </div>
    
    <!-- Notifications Banner -->
    <?php if ($unread_count > 0): ?>
    <div style="background: var(--primary-light); border: 1px solid var(--primary); padding: 16px 24px; border-radius: var(--radius); margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
        <i class="ti ti-bell" style="font-size: 24px; color: var(--primary);"></i>
        <div>
            <strong style="color: var(--primary);">You have <?php echo $unread_count; ?> new message(s)</strong>
            <a href="messages.php" style="color: var(--primary); text-decoration: underline; margin-left: 12px;">View Messages</a>
        </div>
    </div>
    <?php endif; ?>
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px;">
        <!-- My Listings -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 class="section-title" style="margin: 0;">My Listings</h2>
                <a href="/techtrade/post_listing.php" class="btn-primary" style="padding: 8px 16px; font-size: 14px;">
                    <i class="ti ti-plus"></i> New Listing
                </a>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = mysqli_fetch_assoc($listings_result)): ?>
                    <tr>
                        <td>
                            <a href="/techtrade/listing.php?id=<?php echo $item['listing_id']; ?>" style="font-weight: 600;">
                                <?php echo $item['title']; ?>
                            </a>
                            <br><small style="color: var(--text-light);"><?php echo $item['category_name']; ?></small>
                        </td>
                        <td>R<?php echo number_format($item['price'], 2); ?></td>
                        <td><span class="badge badge-<?php echo strtolower($item['status']); ?>"><?php echo $item['status']; ?></span></td>
                        <td><?php echo date('d M Y', strtotime($item['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pending Deals -->
        <div>
            <h2 class="section-title">Pending Deals <?php if ($pending_count > 0): ?><span style="background: var(--accent); color: white; padding: 2px 10px; border-radius: 10px; font-size: 14px;"><?php echo $pending_count; ?></span><?php endif; ?></h2>
            
            <?php if (mysqli_num_rows($trans_result) > 0): ?>
                <?php while ($trans = mysqli_fetch_assoc($trans_result)): ?>
                <div style="background: var(--white); padding: 20px; border-radius: var(--radius); box-shadow: var(--shadow); margin-bottom: 16px; border-left: 4px solid var(--accent);">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                        <h4 style="font-size: 15px;"><?php echo $trans['title']; ?></h4>
                        <span class="badge badge-pending">Pending</span>
                    </div>
                    <p style="font-size: 14px; color: var(--primary); font-weight: 700; margin-bottom: 8px;">R<?php echo number_format($trans['price'], 2); ?></p>
                    
                    <div style="background: var(--surface); padding: 12px; border-radius: var(--radius); margin-bottom: 12px;">
                        <p style="font-size: 13px; margin-bottom: 4px;"><strong>Buyer:</strong> <?php echo $trans['buyer_name']; ?></p>
                        <p style="font-size: 13px; color: var(--text-light);"><i class="ti ti-phone"></i> <?php echo $trans['buyer_phone']; ?></p>
                        <p style="font-size: 12px; color: var(--text-light); margin-top: 4px;">
                            <i class="ti ti-clock"></i> <?php echo date('d M Y H:i', strtotime($trans['created_at'])); ?>
                        </p>
                    </div>
                    
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <a href="/techtrade/messages.php?to=<?php echo $trans['buyer_id']; ?>&listing=<?php echo $trans['listing_id']; ?>" class="btn-small btn-view">
                            <i class="ti ti-message-circle"></i> Message Buyer
                        </a>
                        <a href="/techtrade/payment.php?transaction=<?php echo $trans['transaction_id']; ?>" class="btn-small btn-verify">
                            <i class="ti ti-check"></i> Mark Sold
                        </a>
                        <a href="/techtrade/listing.php?id=<?php echo $trans['listing_id']; ?>" class="btn-small" style="background: var(--surface); color: var(--text);">
                            <i class="ti ti-eye"></i> View
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="background: var(--white); padding: 32px; border-radius: var(--radius); text-align: center; color: var(--text-light);">
                    <i class="ti ti-inbox" style="font-size: 32px; margin-bottom: 8px; display: block;"></i>
                    <p>No pending deals</p>
                    <p style="font-size: 13px; margin-top: 8px;">When a buyer expresses interest, they'll appear here.</p>
                </div>
            <?php endif; ?>
            
            <!-- Recent Messages -->
            <h2 class="section-title" style="margin-top: 32px;">Recent Messages <?php if ($unread_count > 0): ?><span style="background: var(--danger); color: white; padding: 2px 10px; border-radius: 10px; font-size: 14px;"><?php echo $unread_count; ?> new</span><?php endif; ?></h2>
            
            <?php if (mysqli_num_rows($msg_result) > 0): ?>
                <?php while ($msg = mysqli_fetch_assoc($msg_result)): ?>
                <a href="messages.php?to=<?php echo $msg['sender_id']; ?>&listing=<?php echo $msg['listing_id']; ?>" 
                   style="display: block; background: var(--white); padding: 16px; border-radius: var(--radius); box-shadow: var(--shadow); margin-bottom: 12px; <?php echo $msg['is_read'] ? '' : 'border-left: 4px solid var(--primary);'; ?>">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div>
                            <h4 style="font-size: 14px; margin-bottom: 4px;">
                                <?php echo $msg['sender_name']; ?>
                                <?php if (!$msg['is_read']): ?>
                                    <span style="background: var(--primary); color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; margin-left: 8px;">New</span>
                                <?php endif; ?>
                            </h4>
                            <p style="font-size: 12px; color: var(--text-light); margin-bottom: 4px;">
                                <?php echo $msg['listing_title'] ? 'Re: ' . $msg['listing_title'] : 'General inquiry'; ?>
                            </p>
                            <p style="font-size: 13px; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;">
                                <?php echo $msg['message']; ?>
                            </p>
                        </div>
                        <span style="font-size: 11px; color: var(--text-light); white-space: nowrap;">
                            <?php echo date('H:i', strtotime($msg['created_at'])); ?>
                        </span>
                    </div>
                </a>
                <?php endwhile; ?>
                <a href="messages.php" style="display: block; text-align: center; padding: 12px; color: var(--primary); font-size: 14px;">
                    View All Messages <i class="ti ti-arrow-right"></i>
                </a>
            <?php else: ?>
                <div style="background: var(--white); padding: 24px; border-radius: var(--radius); text-align: center; color: var(--text-light);">
                    <i class="ti ti-message-circle" style="font-size: 24px; margin-bottom: 8px; display: block;"></i>
                    <p style="font-size: 13px;">No messages yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>