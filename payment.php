<?php

// Buyers choose how they want to pay and receive the item

require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection not established.');
}

$buyer_id = $_SESSION['user_id'];
$transaction_id = isset($_GET['transaction']) ? intval($_GET['transaction']) : 0;
$message = '';

// Fetch transaction details
$trans_sql = "SELECT t.*, l.title, l.price, l.description, u.full_name as seller_name, u.phone as seller_phone, u.location as seller_location
              FROM transactions t
              JOIN listings l ON t.listing_id = l.listing_id
              JOIN users u ON t.seller_id = u.user_id
              WHERE t.transaction_id = $transaction_id AND t.buyer_id = $buyer_id";
$trans_result = mysqli_query($conn, $trans_sql);

if (mysqli_num_rows($trans_result) == 0) {
    header("Location: /my_account.php");
    exit();
}

$trans = mysqli_fetch_assoc($trans_result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = clean($_POST['payment_method']);
    $delivery_method = clean($_POST['delivery_method']);

    $update_sql = "UPDATE transactions 
                   SET payment_method = '$payment_method', delivery_method = '$delivery_method'
                   WHERE transaction_id = $transaction_id";

    if (mysqli_query($conn, $update_sql)) {
        $message = 'Payment and delivery options saved successfully!';
    }
}

include 'includes/header.php';
?>

<section class="dashboard container">
    <h2 class="section-title">Complete Your Purchase</h2>

    <?php if ($message != ''): ?>
        <div class="alert alert-success" style="margin-bottom: 24px;"><?php echo $message; ?></div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px;">
        <!-- Product Summary -->
        <div style="background: var(--white); padding: 24px; border-radius: var(--radius-lg); box-shadow: var(--shadow);">
            <h3 style="margin-bottom: 16px;">Order Summary</h3>
            <div style="border-bottom: 1px solid var(--border); padding-bottom: 16px; margin-bottom: 16px;">
                <h4 style="font-size: 18px; margin-bottom: 8px;"><?php echo $trans['title']; ?></h4>
                <p style="color: var(--text-light); font-size: 14px;"><?php echo substr($trans['description'], 0, 100); ?>...</p>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span>Item Price</span>
                <span style="font-weight: 600;">R<?php echo number_format($trans['price'], 2); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span>Platform Fee</span>
                <span style="color: var(--success);">R0.00</span>
            </div>
            <div style="border-top: 1px solid var(--border); padding-top: 16px; margin-top: 16px; display: flex; justify-content: space-between; font-size: 20px; font-weight: 700;">
                <span>Total</span>
                <span style="color: var(--primary);">R<?php echo number_format($trans['price'], 2); ?></span>
            </div>

            <!-- Seller Info -->
            <div style="background: var(--surface); padding: 16px; border-radius: var(--radius); margin-top: 24px;">
                <h4 style="font-size: 14px; color: var(--text-light); margin-bottom: 8px;">Seller Information</h4>
                <p style="font-weight: 600;"><?php echo $trans['seller_name']; ?></p>
                <p style="font-size: 13px; color: var(--text-light);"><i class="ti ti-map-pin"></i> <?php echo $trans['seller_location']; ?></p>
                <p style="font-size: 13px; color: var(--text-light);"><i class="ti ti-phone"></i> <?php echo $trans['seller_phone']; ?></p>
            </div>
        </div>

        <!-- Payment & Delivery Options -->
        <div>
            <form method="POST">
                <div style="background: var(--white); padding: 24px; border-radius: var(--radius-lg); box-shadow: var(--shadow); margin-bottom: 24px;">
                    <h3 style="margin-bottom: 16px;">Choose Payment Method</h3>

                    <div class="payment-options" style="grid-template-columns: 1fr;">
                        <label class="payment-option" style="display: flex; align-items: start; gap: 12px; cursor: pointer;">
                            <input type="radio" name="payment_method" value="Cash on Collection" checked style="margin-top: 4px;">
                            <div>
                                <h4><i class="ti ti-cash"></i> Cash on Collection</h4>
                                <p>Pay the seller in cash when you meet in person. Most popular in townships.</p>
                            </div>
                        </label>

                        <label class="payment-option" style="display: flex; align-items: start; gap: 12px; cursor: pointer;">
                            <input type="radio" name="payment_method" value="EFT / Bank Transfer" style="margin-top: 4px;">
                            <div>
                                <h4><i class="ti ti-building-bank"></i> EFT / Bank Transfer</h4>
                                <p>Transfer money directly to the seller's bank account before collection.</p>
                            </div>
                        </label>

                        <label class="payment-option" style="display: flex; align-items: start; gap: 12px; cursor: pointer;">
                            <input type="radio" name="payment_method" value="PayJustNow" style="margin-top: 4px;">
                            <div>
                                <h4><i class="ti ti-credit-card"></i> PayJustNow</h4>
                                <p>Buy now, pay in 3 instalments. Zero interest. Popular for higher priced items.</p>
                            </div>
                        </label>

                        <label class="payment-option" style="display: flex; align-items: start; gap: 12px; cursor: pointer;">
                            <input type="radio" name="payment_method" value="SnapScan / Zapper" style="margin-top: 4px;">
                            <div>
                                <h4><i class="ti ti-qrcode"></i> SnapScan / Zapper</h4>
                                <p>Scan QR code and pay instantly with your phone. Quick and easy.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div style="background: var(--white); padding: 24px; border-radius: var(--radius-lg); box-shadow: var(--shadow); margin-bottom: 24px;">
                    <h3 style="margin-bottom: 16px;">Choose Delivery Method</h3>

                    <div class="payment-options" style="grid-template-columns: 1fr;">
                        <label class="payment-option" style="display: flex; align-items: start; gap: 12px; cursor: pointer;">
                            <input type="radio" name="delivery_method" value="Meet in Person" checked style="margin-top: 4px;">
                            <div>
                                <h4><i class="ti ti-users"></i> Meet in Person</h4>
                                <p>Meet the seller at a safe public place. Recommended for local transactions.</p>
                            </div>
                        </label>

                        <label class="payment-option" style="display: flex; align-items: start; gap: 12px; cursor: pointer;">
                            <input type="radio" name="delivery_method" value="Pudo Locker" style="margin-top: 4px;">
                            <div>
                                <h4><i class="ti ti-package"></i> Pudo Locker</h4>
                                <p>Affordable locker-to-locker delivery. Pick up at your nearest Pudo locker.</p>
                            </div>
                        </label>

                        <label class="payment-option" style="display: flex; align-items: start; gap: 12px; cursor: pointer;">
                            <input type="radio" name="delivery_method" value="The Courier Guy" style="margin-top: 4px;">
                            <div>
                                <h4><i class="ti ti-truck"></i> The Courier Guy</h4>
                                <p>Door-to-door delivery. Reliable and trackable. Cost varies by location.</p>
                            </div>
                        </label>

                        <label class="payment-option" style="display: flex; align-items: start; gap: 12px; cursor: pointer;">
                            <input type="radio" name="delivery_method" value="Seller Arranges" style="margin-top: 4px;">
                            <div>
                                <h4><i class="ti ti-handshake"></i> Seller Arranges</h4>
                                <p>The seller will organise their own delivery method and share details with you.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <button type="submit" class="form-btn" style="font-size: 16px;">
                    <i class="ti ti-check"></i> Confirm Options
                </button>
            </form>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>