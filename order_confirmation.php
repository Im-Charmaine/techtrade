<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();

if (!isset($_SESSION['confirmation'])) {
    header('Location: index.php');
    exit;
}

$conf = $_SESSION['confirmation'];
unset($_SESSION['confirmation']); 
include 'includes/header.php';
?>

<div class="container" style="padding: 32px 20px; max-width: 600px; margin: 0 auto; text-align: center;">
    <div style="margin-bottom: 24px;">
        <i class="ti ti-circle-check" style="font-size: 64px; color: var(--success);"></i>
    </div>
    
    <h1 style="margin-bottom: 8px;">Order Placed!</h1>
    <p style="color: var(--text-light); margin-bottom: 32px;">
        Your order has been sent to <strong><?php echo htmlspecialchars($conf['seller_name']); ?></strong>
    </p>
    
    <!-- Meetup Code -->
    <div style="background: var(--bg-card); border: 2px solid var(--primary); border-radius: 16px; padding: 32px; margin-bottom: 24px;">
        <p style="font-size: 14px; color: var(--text-light); margin-bottom: 8px;">Your Meetup Code</p>
        <p style="font-size: 42px; font-weight: 800; color: var(--primary); letter-spacing: 8px; margin-bottom: 16px;">
            <?php echo $conf['meetup_code']; ?>
        </p>
        <p style="font-size: 13px; color: var(--text-light);">
            Share this code with the seller when you meet to verify identity
        </p>
    </div>
    
    <!-- Order Details -->
    <div style="background: var(--surface); border-radius: 12px; padding: 24px; margin-bottom: 24px; text-align: left;">
        <h3 style="margin-bottom: 16px; font-size: 16px;">Order Details</h3>
        <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
            <span style="color: var(--text-light);">Items</span>
            <span><?php echo $conf['item_count']; ?></span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
            <span style="color: var(--text-light);">Total</span>
            <span style="font-weight: 700;">R<?php echo number_format($conf['total'], 2); ?></span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
            <span style="color: var(--text-light);">Your Phone</span>
            <span><?php echo htmlspecialchars($conf['buyer_phone']); ?></span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span style="color: var(--text-light);">Meetup Location</span>
            <span style="text-align: right;"><?php echo htmlspecialchars($conf['meetup_location']); ?></span>
        </div>
    </div>
    
    
    <div style="margin-bottom: 24px;">
        <p style="color: var(--text-light); font-size: 14px; margin-bottom: 16px;">
            The seller will contact you to arrange the meetup. You can also message them directly.
        </p>
        <a href="messages.php" class="btn-primary" style="margin-right: 8px;">
            <i class="ti ti-message-circle"></i> View Messages
        </a>
        <a href="my_account.php" class="btn-secondary">
            <i class="ti ti-user"></i> My Account
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>