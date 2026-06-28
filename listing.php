<?php

// Buyers see full product info, seller details, and can express interest

require_once 'includes/db.php';
require_once 'includes/auth.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection not established.');
}

// Get the listing ID from the URL
$listing_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($listing_id == 0) {
    header("Location: /listings.php");
    exit();
}

// Fetch the listing with seller info
$sql = "SELECT l.*, u.full_name as seller_name, u.location, u.phone, u.is_verified,
               c.name as category_name,
               (SELECT AVG(rating) FROM ratings WHERE seller_id = l.seller_id) as avg_rating,
               (SELECT COUNT(*) FROM ratings WHERE seller_id = l.seller_id) as total_ratings
        FROM listings l
        JOIN users u ON l.seller_id = u.user_id
        JOIN categories c ON l.category_id = c.category_id
        WHERE l.listing_id = $listing_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: /listings.php");
    exit();
}

$item = mysqli_fetch_assoc($result);

// Check if user has favourited this
$fav_check = false;
if (is_logged_in()) {
    $uid = $_SESSION['user_id'];
    $fav_sql = "SELECT * FROM favourites WHERE user_id = $uid AND listing_id = $listing_id";
    $fav_result = mysqli_query($conn, $fav_sql);
    $fav_check = mysqli_num_rows($fav_result) > 0;
}

// Handle express interest
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['express_interest'])) {
    if (!is_logged_in()) {
        header("Location: /login.php");
        exit();
    }

    $buyer_id = $_SESSION['user_id'];
    $seller_id = $item['seller_id'];

    // Check if transaction already exists
    $check_sql = "SELECT * FROM transactions WHERE listing_id = $listing_id AND buyer_id = $buyer_id";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) == 0) {
        $trans_sql = "INSERT INTO transactions (listing_id, buyer_id, seller_id, status)
                      VALUES ($listing_id, $buyer_id, $seller_id, 'Pending')";
        mysqli_query($conn, $trans_sql);

        // Update listing status
        mysqli_query($conn, "UPDATE listings SET status = 'Pending' WHERE listing_id = $listing_id");

        $message = 'Interest expressed! The seller will contact you soon.';
    } else {
        $message = 'You have already expressed interest in this item.';
    }
}

include 'includes/header.php';
?>

<section class="listing-detail container">
    <?php if ($message != ''): ?>
        <div class="alert alert-success" style="margin-bottom: 20px;"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="listing-layout">
        <!-- Product Image -->
        <div class="listing-image">
            <?php if ($item['image_url'] != ''): ?>
                <img src="/uploads/<?php echo $item['image_url']; ?>" alt="<?php echo $item['title']; ?>">
            <?php else: ?>
                <div class="no-image" style="flex-direction: column;">
                    <i class="ti ti-device-mobile" style="font-size: 64px;"></i>
                    <span>No photo uploaded</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div class="listing-info">
            <span class="meta-tag"><i class="ti ti-tag"></i> <?php echo $item['category_name']; ?></span>
            <span class="meta-tag"><i class="ti ti-checkup-list"></i> <?php echo $item['condition_status']; ?></span>
            <span class="meta-tag status-<?php echo strtolower($item['status']); ?>">
                <i class="ti ti-circle-check"></i> <?php echo $item['status']; ?>
            </span>

            <h1><?php echo $item['title']; ?></h1>
            <div class="listing-price">R<?php echo number_format($item['price'], 2); ?></div>

            <div class="listing-description">
                <?php echo nl2br($item['description']); ?>
            </div>

            <!-- Seller Box -->
            <div class="seller-box">
                <h4>Seller Information</h4>
                <div class="seller-name">
                    <?php echo $item['seller_name']; ?>
                    <?php if ($item['is_verified']): ?>
                        <span class="badge badge-verified" style="margin-left: 8px;"><i class="ti ti-shield-check"></i> Verified</span>
                    <?php endif; ?>
                </div>
                <div class="seller-location">
                    <i class="ti ti-map-pin"></i> <?php echo $item['location']; ?>
                </div>
                <?php if ($item['avg_rating']): ?>
                    <div style="margin-top: 8px; color: var(--accent);">
                        <?php for ($i = 0; $i < round($item['avg_rating']); $i++): ?><i class="ti ti-star-filled"></i><?php endfor; ?>
                        <?php for ($i = round($item['avg_rating']); $i < 5; $i++): ?><i class="ti ti-star"></i><?php endfor; ?>
                        <span style="color: var(--text-light); font-size: 13px;">(<?php echo $item['total_ratings']; ?> reviews)</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <?php if (is_logged_in() && $_SESSION['user_id'] != $item['seller_id'] && $item['status'] == 'Listed'): ?>
                    <form method="POST" action="add_to_cart.php" style="display: inline;">
                        <input type="hidden" name="listing_id" value="<?php echo $listing_id; ?>">
                        <button type="submit" class="btn-outline" style="border-color: var(--primary); color: var(--primary); cursor: pointer;">
                            <i class="ti ti-shopping-cart"></i> Add to Cart
                        </button>
                    </form>
                    <a href="messages.php?to=<?php echo $item['seller_id']; ?>&listing=<?php echo $listing_id; ?>" class="btn-outline">
                        <i class="ti ti-message-circle"></i> Message Seller
                    </a>
                <?php elseif (!is_logged_in()): ?>
                    <a href="login.php" class="btn-primary btn-large">
                        <i class="ti ti-login"></i> Login to Buy
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>