<?php

// Buyers can search, filter by category, and view products

require_once 'includes/db.php';
require_once 'includes/auth.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection not established.');
}

// Get search and filter values from URL
$search = isset($_GET['search']) ? clean($_GET['search']) : '';
$category = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Build the SQL query based on filters
$sql = "SELECT l.*, u.full_name as seller_name, u.location, c.name as category_name
        FROM listings l
        JOIN users u ON l.seller_id = u.user_id
        JOIN categories c ON l.category_id = c.category_id
        WHERE l.status = 'Listed'";

// Add search filter if user typed something
if ($search != '') {
    $sql .= " AND (l.title LIKE '%$search%' OR l.description LIKE '%$search%')";
}

// Add category filter if user selected one
if ($category > 0) {
    $sql .= " AND l.category_id = $category";
}

$sql .= " ORDER BY l.created_at DESC";

$result = mysqli_query($conn, $sql);

// Get all categories for the filter
$cat_sql = "SELECT * FROM categories ORDER BY name";
$cat_result = mysqli_query($conn, $cat_sql);

include 'includes/header.php';
?>

<section class="container" style="padding-top: 32px;">
    <h2 class="section-title">
        <?php if ($search != ''): ?>
            Search Results for "<?php echo $search; ?>"
        <?php elseif ($category > 0): 
            $cat_name = '';
            mysqli_data_seek($cat_result, 0);
            while ($c = mysqli_fetch_assoc($cat_result)) {
                if ($c['category_id'] == $category) $cat_name = $c['name'];
            }
        ?>
            <?php echo $cat_name; ?>
        <?php else: ?>
            All Electronics
        <?php endif; ?>
    </h2>

    <!-- Search Bar -->
    <form action="listings.php" method="GET" style="margin-bottom: 24px; max-width: 500px;">
        <div style="display: flex; gap: 8px;">
            <input type="text" name="search" placeholder="Search phones, laptops, tablets..." 
                   value="<?php echo $search; ?>"
                   style="flex: 1; padding: 12px 14px; border: 1px solid var(--border); border-radius: var(--radius); font-size: 15px;">
            <button type="submit" class="btn-primary" style="padding: 12px 20px;">
                <i class="ti ti-search"></i>
            </button>
        </div>
    </form>

    <!-- Category Filter -->
    <div class="category-chips" style="justify-content: flex-start; margin-bottom: 24px;">
        <a href="listings.php" class="category-chip <?php echo $category == 0 ? 'active' : ''; ?>">
            <i class="ti ti-layout-grid"></i> All
        </a>
        <?php mysqli_data_seek($cat_result, 0); while ($cat = mysqli_fetch_assoc($cat_result)): ?>
        <a href="listings.php?category=<?php echo $cat['category_id']; ?>" 
           class="category-chip <?php echo $category == $cat['category_id'] ? 'active' : ''; ?>">
            <i class="ti <?php echo $cat['icon']; ?>"></i> <?php echo $cat['name']; ?>
        </a>
        <?php endwhile; ?>
    </div>

    <!-- Product Grid -->
    <div class="product-grid">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($item = mysqli_fetch_assoc($result)): ?>
            <a href="/listing.php?id=<?php echo $item['listing_id']; ?>" class="p-card" style="display: block;">
                <div class="p-card-img">
                    <?php if ($item['image_url'] != ''): ?>
                        <img src="/uploads/<?php echo $item['image_url']; ?>" alt="<?php echo $item['title']; ?>">
                    <?php else: ?>
                        <div class="no-image">
                            <i class="ti ti-device-mobile"></i>
                            <span>No photo</span>
                        </div>
                    <?php endif; ?>
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
                    <div style="margin-top: 8px; font-size: 12px; color: var(--text-light);">
                        <i class="ti ti-tag"></i> <?php echo $item['category_name']; ?> 
                        <span style="margin-left: 8px;"><i class="ti ti-checkup-list"></i> <?php echo $item['condition_status']; ?></span>
                    </div>
                </div>
            </a>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px; color: var(--text-light);">
                <i class="ti ti-search" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                <p>No listings found. Try a different search or category.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>