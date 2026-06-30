<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];

// Fetch cart items
$cart_sql = "SELECT c.*, l.title, l.price, l.image_url, l.listing_id, u.user_id as seller_id, u.full_name as seller_name
             FROM cart c
             JOIN listings l ON c.listing_id = l.listing_id
             JOIN users u ON l.seller_id = u.user_id
             WHERE c.user_id = $user_id AND l.status = 'Listed'";
$cart_result = mysqli_query($conn, $cart_sql);

if (mysqli_num_rows($cart_result) == 0) {
    header("Location: cart.php");
    exit();
}

// Calculate total
$total = 0;
$items = [];
while ($row = mysqli_fetch_assoc($cart_result)) {
    $items[] = $row;
    $total += $row['price'] * $row['quantity'];
}

// Handle checkout submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = clean($_POST['payment_method']);
    $delivery_method = clean($_POST['delivery_method']);
    $buyer_phone = clean($_POST['buyer_phone']);
    $meetup_location = clean($_POST['meetup_location']);
    
    // Generate meetup code
    $meetup_code = strtoupper(substr(md5(uniqid()), 0, 8));
    
    // Create transaction for each item
    foreach ($items as $item) {
        $listing_id = $item['listing_id'];
        $seller_id = $item['seller_id'];
        
        $stmt = mysqli_prepare($conn, 
            "INSERT INTO transactions (listing_id, buyer_id, seller_id, status, payment_method, delivery_method, buyer_phone, meetup_location, meetup_code) 
             VALUES (?, ?, ?, 'Pending', ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "iiisssss", $listing_id, $user_id, $seller_id, $payment_method, $delivery_method, $buyer_phone, $meetup_location, $meetup_code);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        // Update listing status
        mysqli_query($conn, "UPDATE listings SET status = 'Pending' WHERE listing_id = $listing_id");
    }
    
    // Clear cart
    mysqli_query($conn, "DELETE FROM cart WHERE user_id = $user_id");
    
    // Store confirmation data in session
    $_SESSION['confirmation'] = [
        'meetup_code' => $meetup_code,
        'seller_name' => $items[0]['seller_name'],
        'buyer_phone' => $buyer_phone,
        'meetup_location' => $meetup_location,
        'total' => $total,
        'item_count' => count($items)
    ];
    
    // Redirects to confirmation page
    header('Location: order_confirmation.php');
    exit;
}

include 'includes/header.php';
?>

<div class="container" style="padding: 32px 20px; max-width: 900px; margin: 0 auto;">
    <h1 style="margin-bottom: 24px;">Checkout</h1>
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px;">
        <!-- Checkout Form -->
        <div>
            <form method="POST">
                <!-- Contact Info -->
                <div style="background: var(--bg-card); border: 1px solid rgba(100,100,200,0.2); border-radius: 12px; padding: 24px; margin-bottom: 24px;">
                    <h3 style="margin-bottom: 20px;"><i class="ti ti-user"></i> Contact Information</h3>
                    <div class="form-group">
                        <label>Phone Number *</label>
                        <input type="tel" name="buyer_phone" placeholder="082 123 4567" required>
                    </div>
                    <div class="form-group">
                        <label>Meetup Location *</label>
                        <input type="text" name="meetup_location" placeholder="e.g. Mall of Africa, Soweto Taxi Rank" required>
                    </div>
                </div>
                
                <!-- Payment Method -->
                <div style="background: var(--bg-card); border: 1px solid rgba(100,100,200,0.2); border-radius: 12px; padding: 24px; margin-bottom: 24px;">
                    <h3 style="margin-bottom: 20px;"><i class="ti ti-credit-card"></i> Payment Method</h3>
                    
                    <label style="display: flex; align-items: start; gap: 12px; padding: 16px; border: 2px solid var(--border); border-radius: 8px; margin-bottom: 12px; cursor: pointer;">
                        <input type="radio" name="payment_method" value="Cash on Collection" checked style="margin-top: 4px;">
                        <div>
                            <h4 style="font-size: 16px;"><i class="ti ti-cash"></i> Cash on Collection</h4>
                            <p style="font-size: 13px; color: var(--text-light);">Pay the seller in cash when you meet in person.</p>
                        </div>
                    </label>
                    
                    <label style="display: flex; align-items: start; gap: 12px; padding: 16px; border: 2px solid var(--border); border-radius: 8px; margin-bottom: 12px; cursor: pointer;">
                        <input type="radio" name="payment_method" value="EFT / Bank Transfer" style="margin-top: 4px;">
                        <div>
                            <h4 style="font-size: 16px;"><i class="ti ti-building-bank"></i> EFT / Bank Transfer</h4>
                            <p style="font-size: 13px; color: var(--text-light);">Transfer money directly to the seller's bank account.</p>
                        </div>
                    </label>
                </div>
                
                <!-- Delivery Method -->
                <div style="background: var(--bg-card); border: 1px solid rgba(100,100,200,0.2); border-radius: 12px; padding: 24px; margin-bottom: 24px;">
                    <h3 style="margin-bottom: 20px;"><i class="ti ti-truck"></i> Delivery Method</h3>
                    
                    <label style="display: flex; align-items: start; gap: 12px; padding: 16px; border: 2px solid var(--border); border-radius: 8px; margin-bottom: 12px; cursor: pointer;">
                        <input type="radio" name="delivery_method" value="Meet in Person" checked style="margin-top: 4px;">
                        <div>
                            <h4 style="font-size: 16px;"><i class="ti ti-users"></i> Meet in Person</h4>
                            <p style="font-size: 13px; color: var(--text-light);">Meet the seller at a safe public place.</p>
                        </div>
                    </label>
                    
                    <label style="display: flex; align-items: start; gap: 12px; padding: 16px; border: 2px solid var(--border); border-radius: 8px; cursor: pointer;">
                        <input type="radio" name="delivery_method" value="Pudo Locker" style="margin-top: 4px;">
                        <div>
                            <h4 style="font-size: 16px;"><i class="ti ti-package"></i> Pudo Locker</h4>
                            <p style="font-size: 13px; color: var(--text-light);">Affordable locker-to-locker delivery.</p>
                        </div>
                    </label>
                </div>
                
                <button type="submit" class="form-btn" style="font-size: 18px; padding: 16px;">
                    <i class="ti ti-check"></i> Place Order (R<?php echo number_format($total, 2); ?>)
                </button>
            </form>
        </div>
        
        <!-- Order Summary -->
        <div style="background: var(--bg-card); border: 1px solid rgba(100,100,200,0.2); border-radius: 12px; padding: 24px; height: fit-content;">
            <h3 style="margin-bottom: 20px;">Your Items</h3>
            <?php foreach ($items as $item): ?>
            <div style="display: flex; gap: 12px; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid rgba(100,100,200,0.2);">
                <div style="width: 60px; height: 60px; background: var(--surface); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <?php if ($item['image_url'] != ''): ?>
                        <img src="uploads/<?php echo htmlspecialchars($item['image_url']); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                    <?php else: ?>
                        <i class="ti ti-device-mobile" style="font-size: 24px; color: var(--aqua);"></i>
                    <?php endif; ?>
                </div>
                <div>
                    <h4 style="font-size: 14px;"><?php echo $item['title']; ?></h4>
                    <p style="font-size: 13px; color: var(--text-light);">Qty: <?php echo $item['quantity']; ?></p>
                    <p style="font-size: 14px; font-weight: 600; color: var(--primary);">R<?php echo number_format($item['price'], 2); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
            <div style="border-top: 2px solid rgba(100,100,200,0.2); padding-top: 16px; display: flex; justify-content: space-between; font-size: 20px; font-weight: 700;">
                <span>Total</span>
                <span style="color: var(--primary);">R<?php echo number_format($total, 2); ?></span>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>