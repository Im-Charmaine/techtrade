<?php

// Buyers can save items they are interested in

require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection not established.');
}

// Handle adding a favourite
if (isset($_GET['add'])) {
    $listing_id = intval($_GET['add']);
    $check_sql = "SELECT * FROM favourites WHERE user_id = $user_id AND listing_id = $listing_id";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) == 0) {
        $sql = "INSERT INTO favourites (user_id, listing_id) VALUES ($user_id, $listing_id)";
        mysqli_query($conn, $sql);
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

// Handle removing a favourite
if (isset($_GET['remove'])) {
    $listing_id = intval($_GET['remove']);
    $sql = "DELETE FROM favourites WHERE user_id = $user_id AND listing_id = $listing_id";
    mysqli_query($conn, $sql);

    header("Location: favourites.php");
    exit();
}

// Fetch all favourites
$fav_sql = "SELECT f.*, l.title, l.price, l.status, l.image_url, l.condition_status,
                   u.full_name as seller_name, u.location
            FROM favourites f
            JOIN listings l ON f.listing_id = l.listing_id
            JOIN users u ON l.seller_id = u.user_id
            WHERE f.user_id = $user_id
            ORDER BY f.created_at DESC";
$fav_result = mysqli_query($conn, $fav_sql);

include 'includes/header.php';
?>

<section class="dashboard container">
    <h2 class="section-title">My Saved Items</h2>

    <?php if (mysqli_num_rows($fav_result) > 0): ?>
    <div class="favourites-grid">
        <?php while ($fav = mysqli_fetch_assoc($fav_result)): ?>
        <div class="p-card">
            <div class="p-card-img">
                <?php if ($fav['image_url'] != ''): ?>
                    <img src="/techtrade/uploads/<?php echo $fav['image_url']; ?>" alt="<?php echo $fav['title']; ?>">
                <?php else: ?>
                    <div class="no-image">
                        <i class="ti ti-device-mobile"></i>
                        <span>No photo</span>
                    </div>
                <?php endif; ?>
                <a href="/techtrade/favourites.php?remove=<?php echo $fav['listing_id']; ?>" style="position: absolute; top: 10px; right: 10px; background: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--danger); box-shadow: var(--shadow);">
                    <i class="ti ti-trash"></i>
                </a>
            </div>
            <div class="p-card-body">
                <a href="/techtrade/listing.php?id=<?php echo $fav['listing_id']; ?>" style="font-weight: 600; color: var(--text);">
                    <?php echo $fav['title']; ?>
                </a>
                <div class="p-card-price">R<?php echo number_format($fav['price'], 2); ?></div>
                <div class="p-card-meta">
                    <span><i class="ti ti-user"></i> <?php echo $fav['seller_name']; ?></span>
                    <span><i class="ti ti-map-pin"></i> <?php echo $fav['location']; ?></span>
                </div>
                <div style="margin-top: 8px;">
                    <span class="badge badge-<?php echo strtolower($fav['status']); ?>"><?php echo $fav['status']; ?></span>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div style="background: var(--white); padding: 60px; border-radius: var(--radius-lg); text-align: center; color: var(--text-light);">
        <i class="ti ti-heart-broken" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
        <h3>No saved items</h3>
        <p style="margin-top: 8px;">Click the heart icon on any listing to save it here.</p>
        <a href="/techtrade/listings.php" class="btn-primary" style="margin-top: 20px;">Browse Listings</a>
    </div>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>