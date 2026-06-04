<?php

// This is the first page visitors see when they visit TechTrade

$conn = null;
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (!$conn) {
    die('Database connection not established.');
}

// Fetch all active listings from the database
$sql = "SELECT l.*, u.full_name as seller_name, u.location, c.name as category_name
        FROM listings l
        JOIN users u ON l.seller_id = u.user_id
        JOIN categories c ON l.category_id = c.category_id
        WHERE l.status = 'Listed'
        ORDER BY l.created_at DESC
        LIMIT 8";
$result = mysqli_query($conn, $sql);

// Fetch categories for the filter chips
$cat_sql = "SELECT * FROM categories ORDER BY name";
$cat_result = mysqli_query($conn, $cat_sql);

// Count total listings for stats
$count_sql = "SELECT COUNT(*) as total FROM listings WHERE status = 'Listed'";
$count_result = mysqli_query($conn, $count_sql);
$count_row = mysqli_fetch_assoc($count_result);
$total_listings = $count_row['total'];

// Count total sellers
$seller_sql = "SELECT COUNT(*) as total FROM users WHERE role = 'seller' AND is_verified = 1";
$seller_result = mysqli_query($conn, $seller_sql);
$seller_row = mysqli_fetch_assoc($seller_result);
$total_sellers = $seller_row['total'];

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Buy & Sell <span>Electronics</span> with Confidence</h1>
        <p>South Africa's trusted marketplace for township resellers. Verified sellers, rated transactions, secure deals.</p>
        <div class="hero-buttons">
            <a href="/techtrade/listings.php" class="btn-primary">
                <i class="ti ti-search"></i> Browse Listings
            </a>
            <a href="/techtrade/register.php" class="btn-secondary">
                <i class="ti ti-user-plus"></i> Start Selling
            </a>
        </div>
    </div>
</section>

<!-- Stats Bar -->
<section style="background: var(--white); padding: 20px 0; border-bottom: 1px solid var(--border);">
    <div class="container" style="display: flex; justify-content: center; gap: 48px; flex-wrap: wrap;">
        <div style="text-align: center;">
            <div style="font-size: 24px; font-weight: 700; color: var(--primary);"><?php echo $total_listings; ?>+</div>
            <div style="font-size: 13px; color: var(--text-light);">Active Listings</div>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 24px; font-weight: 700; color: var(--primary);"><?php echo $total_sellers; ?>+</div>
            <div style="font-size: 13px; color: var(--text-light);">Verified Sellers</div>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 24px; font-weight: 700; color: var(--primary);">R0</div>
            <div style="font-size: 13px; color: var(--text-light);">Listing Fees</div>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 24px; font-weight: 700; color: var(--primary);">100%</div>
            <div style="font-size: 13px; color: var(--text-light);">Local & Trusted</div>
        </div>
    </div>
</section>

<!-- Category Chips -->
<section class="categories">
    <div class="container">
        <div class="category-chips">
            <a href="/techtrade/listings.php" class="category-chip active">
                <i class="ti ti-layout-grid"></i> All
            </a>
            <?php while ($cat = mysqli_fetch_assoc($cat_result)): ?>
            <a href="/techtrade/listings.php?category=<?php echo $cat['category_id']; ?>" class="category-chip">
                <i class="ti <?php echo $cat['icon']; ?>"></i> <?php echo $cat['name']; ?>
            </a>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Featured Listings -->
<section class="container">
    <h2 class="section-title">Featured Electronics</h2>
    <div class="product-grid">
        <?php while ($item = mysqli_fetch_assoc($result)): ?>
        <div class="p-card">
            <div class="p-card-img">
                <?php if ($item['image_url'] != ''): ?>
                    <img src="/techtrade/uploads/<?php echo $item['image_url']; ?>" alt="<?php echo $item['title']; ?>">
                <?php else: ?>
                    <div class="no-image">
                        <i class="ti ti-device-mobile"></i>
                        <span>No photo</span>
                    </div>
                <?php endif; ?>
                <span class="status-badge status-<?php echo strtolower($item['status']); ?>"><?php echo $item['status']; ?></span>
            </div>
            <div class="p-card-body">
                <div class="p-card-title"><?php echo $item['title']; ?></div>
                <div class="p-card-price">R<?php echo number_format($item['price'], 2); ?></div>
                <div class="p-card-meta">
                    <span class="p-card-seller">
                        <i class="ti ti-user"></i> <?php echo $item['seller_name']; ?>
                    </span>
                    <span><i class="ti ti-map-pin"></i> <?php echo $item['location']; ?></span>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <div style="text-align: center; padding: 20px 0 40px;">
        <a href="/techtrade/listings.php" class="btn-primary">
            View All Listings <i class="ti ti-arrow-right"></i>
        </a>
    </div>
</section>

<!-- Trust Strip -->
<section class="trust-strip">
    <div class="container">
        <div class="trust-grid">
            <div class="trust-item">
                <i class="ti ti-shield-check"></i>
                <h4>Verified Sellers</h4>
                <p>Every seller is verified by our admin team before they can list products.</p>
            </div>
            <div class="trust-item">
                <i class="ti ti-star"></i>
                <h4>Rated Transactions</h4>
                <p>Buyers rate sellers after every deal. See ratings before you buy.</p>
            </div>
            <div class="trust-item">
                <i class="ti ti-cash"></i>
                <h4>Zero Listing Fees</h4>
                <p>Sellers keep 100% of their sale. No hidden fees, no commissions.</p>
            </div>
            <div class="trust-item">
                <i class="ti ti-message-circle"></i>
                <h4>Direct Messaging</h4>
                <p>Chat with sellers directly on the platform before meeting in person.</p>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="how-it-works">
    <div class="container">
        <h2 class="section-title" style="text-align: center;">How TechTrade Works</h2>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <h3>Register</h3>
                <p>Create your account as a buyer or seller. Sellers get verified by our admin team.</p>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <h3>List or Browse</h3>
                <p>Sellers post their electronics with photos and prices. Buyers search and filter listings.</p>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <h3>Connect & Deal</h3>
                <p>Message the seller, agree on price and payment method, meet up and exchange safely.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>