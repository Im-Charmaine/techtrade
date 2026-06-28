<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];

// Handle remove from cart
if (isset($_GET['remove'])) {
    $cart_id = intval($_GET['remove']);
    mysqli_query($conn, "DELETE FROM cart WHERE cart_id = $cart_id AND user_id = $user_id");
    header('Location: cart.php');
    exit;
}

// Handle quantity update
if (isset($_POST['update_qty'])) {
    $cart_id = intval($_POST['cart_id']);
    $qty = intval($_POST['quantity']);
    if ($qty > 0) {
        mysqli_query($conn, "UPDATE cart SET quantity = $qty WHERE cart_id = $cart_id AND user_id = $user_id");
    }
    header('Location: cart.php');
    exit;
}

// Fetch cart items with listing details
$cart_sql = "SELECT c.*, l.title, l.price, l.image_url, l.status, u.full_name as seller_name
             FROM cart c
             JOIN listings l ON c.listing_id = l.listing_id
             JOIN users u ON l.seller_id = u.user_id
             WHERE c.user_id = $user_id AND l.status = 'Listed'";
$cart_result = mysqli_query($conn, $cart_sql);

$total = 0;
$items = [];
while ($row = mysqli_fetch_assoc($cart_result)) {
    $items[] = $row;
    $total += $row['price'] * $row['quantity'];
}

include 'includes/header.php';
?>

<div class="container" style="padding: 32px 20px; max-width: 900px; margin: 0 auto;">
    <h1 style="margin-bottom: 8px;">Shopping Cart</h1>
    <p style="color: var(--text-light); margin-bottom: 24px;">
        <?php echo count($items); ?> item(s) in your cart
    </p>
    
    <?php if (count($items) > 0): ?>
        
        <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 24px;">
            <?php foreach ($items as $item): 
                $subtotal = $item['price'] * $item['quantity'];
            ?>
                <div style="background: var(--bg-card); border: 1px solid rgba(100,100,200,0.2); border-radius: 12px; padding: 20px; display: flex; gap: 20px; align-items: flex-start;">
                    
                    <!-- Product Image -->
                    <div style="flex-shrink: 0;">
                        <?php if (!empty($item['image_url'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($item['image_url']); ?>" 
                                 style="width: 100px; height: 100px; object-fit: cover; border-radius: 10px; border: 1px solid rgba(100,100,200,0.2);">
                        <?php else: ?>
                            <div style="width: 100px; height: 100px; background: var(--surface); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="ti ti-photo" style="color: var(--text-light); font-size: 32px;"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Product Info -->
                    <div style="flex: 1; min-width: 0;">
                        <h3 style="font-size: 18px; margin-bottom: 6px; color: var(--text);">
                            <a href="listing.php?id=<?php echo $item['listing_id']; ?>" style="color: var(--text); text-decoration: none;">
                                <?php echo htmlspecialchars($item['title']); ?>
                            </a>
                        </h3>
                        
                        <p style="color: var(--text-light); font-size: 14px; margin-bottom: 8px;">
                            <i class="ti ti-user" style="margin-right: 4px;"></i>
                            <?php echo htmlspecialchars($item['seller_name']); ?>
                        </p>
                        
                        <p style="color: var(--primary); font-weight: 700; font-size: 20px; margin-bottom: 12px;">
                            R<?php echo number_format($item['price'], 2); ?>
                            <?php if ($item['quantity'] > 1): ?>
                                <span style="color: var(--text-light); font-size: 14px; font-weight: 400;"> × <?php echo $item['quantity']; ?></span>
                            <?php endif; ?>
                        </p>
                        
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <form method="POST" style="display: flex; align-items: center; gap: 8px;">
                                <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" style="width: 60px; padding: 8px; border: 1px solid var(--border); border-radius: 6px; text-align: center; background: var(--bg-dark); color: var(--text);">
                                <button type="submit" name="update_qty" class="btn-small btn-view" style="padding: 8px 12px;"><i class="ti ti-check"></i></button>
                            </form>
                            
                            <a href="cart.php?remove=<?php echo $item['cart_id']; ?>" 
                               style="color: var(--danger); font-size: 14px; text-decoration: none; display: flex; align-items: center; gap: 4px;"
                               onclick="return confirm('Remove this item from your cart?')">
                                <i class="ti ti-trash"></i> Remove
                            </a>
                        </div>
                    </div>
                    
                    <!-- Subtotal -->
                    <div style="text-align: right; flex-shrink: 0;">
                        <p style="color: var(--text-light); font-size: 12px; margin-bottom: 4px;">Subtotal</p>
                        <p style="color: var(--text); font-weight: 700; font-size: 18px;">R<?php echo number_format($subtotal, 2); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Cart Summary -->
        <div style="background: var(--bg-card); border: 1px solid rgba(100,100,200,0.2); border-radius: 16px; padding: 24px; position: sticky; bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid rgba(100,100,200,0.2);">
                <div>
                    <p style="color: var(--text-light); font-size: 14px; margin-bottom: 4px;">Total (<?php echo count($items); ?> items)</p>
                    <p style="font-size: 14px; color: var(--text-light);">Excluding delivery</p>
                </div>
                <span style="font-size: 28px; font-weight: 800; color: var(--primary);">R<?php echo number_format($total, 2); ?></span>
            </div>
            <div style="display: flex; gap: 12px;">
                <a href="listings.php" class="btn-secondary" style="flex: 1; text-align: center; padding: 14px;">
                    <i class="ti ti-arrow-left"></i> Continue Shopping
                </a>
                <a href="checkout.php" class="btn-primary" style="flex: 1; text-align: center; padding: 14px; font-weight: 700;">
                    Proceed to Checkout <i class="ti ti-arrow-right"></i>
                </a>
            </div>
        </div>
        
    <?php else: ?>
        
        <!-- Empty Cart -->
        <div style="text-align: center; padding: 80px 20px;">
            <div style="width: 120px; height: 120px; background: var(--surface); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
                <i class="ti ti-shopping-cart-off" style="font-size: 56px; color: var(--text-light);"></i>
            </div>
            <h2 style="margin-bottom: 8px; font-size: 24px;">Your cart is empty</h2>
            <p style="color: var(--text-light); margin-bottom: 32px; font-size: 16px;">Looks like you haven't added anything to your cart yet.</p>
            <a href="listings.php" class="btn-primary" style="padding: 14px 32px; font-size: 16px;">
                <i class="ti ti-search"></i> Browse Listings
            </a>
        </div>
        
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>