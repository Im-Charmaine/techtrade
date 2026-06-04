<?php
// my_account.php - User account with active orders, cancel option, and messaging

require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];

// Handle order cancellation
if (isset($_GET['cancel'])) {
    $transaction_id = intval($_GET['cancel']);
    
    // Verify this transaction belongs to the current buyer and is Pending
    $check_sql = "SELECT * FROM transactions WHERE transaction_id = $transaction_id AND buyer_id = $user_id AND status = 'Pending'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        $trans = mysqli_fetch_assoc($check_result);
        $listing_id = $trans['listing_id'];
        
        // Update transaction status to Cancelled
        mysqli_query($conn, "UPDATE transactions SET status = 'Cancelled' WHERE transaction_id = $transaction_id");
        
        // Set listing back to Listed so others can buy
        mysqli_query($conn, "UPDATE listings SET status = 'Listed' WHERE listing_id = $listing_id");
    }
    
    header("Location: my_account.php");
    exit();
}

// Fetch user's transactions as buyer (all statuses)
$buyer_sql = "SELECT t.*, l.title, l.price, l.listing_id, u.full_name as seller_name, u.user_id as seller_id
              FROM transactions t
              JOIN listings l ON t.listing_id = l.listing_id
              JOIN users u ON t.seller_id = u.user_id
              WHERE t.buyer_id = $user_id
              ORDER BY t.created_at DESC";
$buyer_result = mysqli_query($conn, $buyer_sql);

// Fetch user's favourites
$fav_sql = "SELECT f.*, l.title, l.price, l.status, u.full_name as seller_name
            FROM favourites f
            JOIN listings l ON f.listing_id = l.listing_id
            JOIN users u ON l.seller_id = u.user_id
            WHERE f.user_id = $user_id
            ORDER BY f.created_at DESC";
$fav_result = mysqli_query($conn, $fav_sql);

include 'includes/header.php';
?>

<section class="dashboard container">
    <div class="dashboard-header">
        <h1>My Account</h1>
        <p>Welcome back, <?php echo $_SESSION['full_name']; ?></p>
    </div>
    
    <!-- Profile Card -->
    <div style="background: var(--white); padding: 24px; border-radius: var(--radius-lg); box-shadow: var(--shadow); margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
            <div style="width: 64px; height: 64px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700;">
                <?php echo substr($_SESSION['full_name'], 0, 1); ?>
            </div>
            <div>
                <h3 style="font-size: 20px;"><?php echo $_SESSION['full_name']; ?></h3>
                <p style="color: var(--text-light); font-size: 14px;"><?php echo $_SESSION['email']; ?></p>
                <span class="badge badge-<?php echo $_SESSION['role']; ?>" style="margin-top: 4px;">
                    <?php echo ucfirst($_SESSION['role']); ?>
                </span>
            </div>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px;">
        <!-- My Orders -->
        <div>
            <h2 class="section-title">My Orders</h2>
            <?php if (mysqli_num_rows($buyer_result) > 0): ?>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                <?php while ($order = mysqli_fetch_assoc($buyer_result)): ?>
                <div style="background: var(--white); padding: 20px; border-radius: var(--radius); box-shadow: var(--shadow);">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                        <a href="listing.php?id=<?php echo $order['listing_id']; ?>" style="font-size: 15px; font-weight: 600; color: var(--text);">
                            <?php echo $order['title']; ?>
                        </a>
                        <span class="badge badge-<?php echo strtolower($order['status']); ?>"><?php echo $order['status']; ?></span>
                    </div>
                    <p style="font-size: 14px; color: var(--text-light); margin-bottom: 8px;">
                        Seller: <?php echo $order['seller_name']; ?> | R<?php echo number_format($order['price'], 2); ?>
                    </p>
                    <p style="font-size: 12px; color: var(--text-light); margin-bottom: 12px;">
                        <i class="ti ti-credit-card"></i> <?php echo $order['payment_method']; ?> | 
                        <i class="ti ti-truck"></i> <?php echo $order['delivery_method']; ?>
                    </p>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <a href="messages.php?to=<?php echo $order['seller_id']; ?>&listing=<?php echo $order['listing_id']; ?>" class="btn-small btn-view">
                            <i class="ti ti-message-circle"></i> Message Seller
                        </a>
                        <?php if ($order['status'] == 'Pending'): ?>
                            <a href="payment.php?transaction=<?php echo $order['transaction_id']; ?>" class="btn-small btn-verify">
                                <i class="ti ti-credit-card"></i> Pay
                            </a>
                            <a href="my_account.php?cancel=<?php echo $order['transaction_id']; ?>" 
                               class="btn-small btn-delete"
                               onclick="return confirmDelete('Cancel this order? The item will be returned to listings.')">
                                <i class="ti ti-x"></i> Cancel
                            </a>
                        <?php elseif ($order['status'] == 'Sold'): ?>
                            <a href="my_account.php?rate=<?php echo $order['transaction_id']; ?>" class="btn-small btn-verify">
                                <i class="ti ti-star"></i> Rate Seller
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div style="background: var(--white); padding: 40px; border-radius: var(--radius); text-align: center; color: var(--text-light);">
                    <i class="ti ti-shopping-bag" style="font-size: 40px; margin-bottom: 12px; display: block;"></i>
                    <p>No orders yet. <a href="/techtrade/listings.php" style="color: var(--primary);">Browse listings</a></p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- My Favourites -->
        <div>
            <h2 class="section-title">Saved Items</h2>
            <?php if (mysqli_num_rows($fav_result) > 0): ?>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                <?php while ($fav = mysqli_fetch_assoc($fav_result)): ?>
                <div style="background: var(--white); padding: 16px; border-radius: var(--radius); box-shadow: var(--shadow); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <a href="/techtrade/listing.php?id=<?php echo $fav['listing_id']; ?>" style="font-weight: 600; color: var(--text);">
                            <?php echo $fav['title']; ?>
                        </a>
                        <p style="font-size: 13px; color: var(--text-light);">
                            R<?php echo number_format($fav['price'], 2); ?> | <?php echo $fav['seller_name']; ?> | <?php echo $fav['status']; ?>
                        </p>
                    </div>
                    <a href="/techtrade/favourites.php?remove=<?php echo $fav['listing_id']; ?>" style="color: var(--danger); font-size: 20px;">
                        <i class="ti ti-trash"></i>
                    </a>
                </div>
                <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div style="background: var(--white); padding: 40px; border-radius: var(--radius); text-align: center; color: var(--text-light);">
                    <i class="ti ti-heart" style="font-size: 40px; margin-bottom: 12px; display: block;"></i>
                    <p>No saved items. <a href="/techtrade/listings.php" style="color: var(--primary);">Browse listings</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>