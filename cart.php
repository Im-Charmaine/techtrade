<?php
// cart.php - Shopping cart page

require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];

// Handle adding to cart
if (isset($_GET['add'])) {
    $listing_id = intval($_GET['add']);
    
    // Check if item already in cart
    $check_sql = "SELECT * FROM cart WHERE user_id = $user_id AND listing_id = $listing_id";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) == 0) {
        $insert_sql = "INSERT INTO cart (user_id, listing_id, quantity) VALUES ($user_id, $listing_id, 1)";
        mysqli_query($conn, $insert_sql);
    }
    
    header("Location: cart.php");
    exit();
}

// Handle removing from cart
if (isset($_GET['remove'])) {
    $cart_id = intval($_GET['remove']);
    mysqli_query($conn, "DELETE FROM cart WHERE cart_id = $cart_id AND user_id = $user_id");
    header("Location: cart.php");
    exit();
}

// Handle quantity update
if (isset($_POST['update_qty'])) {
    $cart_id = intval($_POST['cart_id']);
    $qty = intval($_POST['quantity']);
    if ($qty > 0) {
        mysqli_query($conn, "UPDATE cart SET quantity = $qty WHERE cart_id = $cart_id AND user_id = $user_id");
    }
    header("Location: cart.php");
    exit();
}

// Fetch cart items
$cart_sql = "SELECT c.*, l.title, l.price, l.image_url, l.status, u.full_name as seller_name
             FROM cart c
             JOIN listings l ON c.listing_id = l.listing_id
             JOIN users u ON l.seller_id = u.user_id
             WHERE c.user_id = $user_id AND l.status = 'Listed'";
$cart_result = mysqli_query($conn, $cart_sql);

// Calculate total
$total = 0;
$items = [];
while ($row = mysqli_fetch_assoc($cart_result)) {
    $items[] = $row;
    $total += $row['price'] * $row['quantity'];
}

include 'includes/header.php';
?>

<section class="dashboard container">
    <h2 class="section-title">Shopping Cart</h2>
    
    <?php if (count($items) > 0): ?>
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px;">
        <!-- Cart Items -->
        <div>
            <?php foreach ($items as $item): ?>
            <div style="background: var(--white); padding: 20px; border-radius: var(--radius-lg); box-shadow: var(--shadow); margin-bottom: 16px; display: flex; gap: 16px; align-items: center;">
                <div style="width: 100px; height: 100px; background: var(--surface); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                    <?php if ($item['image_url'] != ''): ?>
                        <img src="/techtrade/uploads/<?php echo $item['image_url']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <i class="ti ti-device-mobile" style="font-size: 32px; color: var(--aqua);"></i>
                    <?php endif; ?>
                </div>
                <div style="flex: 1;">
                    <h4 style="font-size: 16px; margin-bottom: 4px;"><?php echo $item['title']; ?></h4>
                    <p style="font-size: 13px; color: var(--text-light); margin-bottom: 8px;">Seller: <?php echo $item['seller_name']; ?></p>
                    <div style="font-size: 18px; font-weight: 700; color: var(--primary);">R<?php echo number_format($item['price'], 2); ?></div>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <form method="POST" style="display: flex; align-items: center; gap: 8px;">
                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" style="width: 60px; padding: 8px; border: 1px solid var(--border); border-radius: var(--radius); text-align: center;">
                        <button type="submit" name="update_qty" class="btn-small btn-view" style="padding: 8px 12px;"><i class="ti ti-check"></i></button>
                    </form>
                    <a href="cart.php?remove=<?php echo $item['cart_id']; ?>" class="btn-small btn-delete" onclick="return confirmDelete('Remove this item?')"><i class="ti ti-trash"></i></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Cart Summary -->
        <div style="background: var(--white); padding: 24px; border-radius: var(--radius-lg); box-shadow: var(--shadow); height: fit-content;">
            <h3 style="margin-bottom: 20px;">Order Summary</h3>
            <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                <span style="color: var(--text-light);">Subtotal</span>
                <span style="font-weight: 600;">R<?php echo number_format($total, 2); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                <span style="color: var(--text-light);">Delivery</span>
                <span style="color: var(--success);">Calculated at checkout</span>
            </div>
            <div style="border-top: 1px solid var(--border); padding-top: 16px; margin-top: 16px; display: flex; justify-content: space-between; font-size: 20px; font-weight: 700;">
                <span>Total</span>
                <span style="color: var(--primary);">R<?php echo number_format($total, 2); ?></span>
            </div>
            <a href="checkout.php" class="btn-primary" style="width: 100%; margin-top: 24px; text-align: center; display: block;">
                <i class="ti ti-credit-card"></i> Proceed to Checkout
            </a>
            <a href="listings.php" style="display: block; text-align: center; margin-top: 12px; color: var(--text-light); font-size: 14px;">
                <i class="ti ti-arrow-left"></i> Continue Shopping
            </a>
        </div>
    </div>
    <?php else: ?>
    <div style="background: var(--white); padding: 60px; border-radius: var(--radius-lg); text-align: center; color: var(--text-light);">
        <i class="ti ti-shopping-cart" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
        <h3 style="margin-bottom: 8px;">Your cart is empty</h3>
        <p style="margin-bottom: 24px;">Browse our listings and add items you like.</p>
        <a href="listings.php" class="btn-primary">Browse Listings</a>
    </div>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>