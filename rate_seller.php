<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();

if ($_SESSION['role'] != 'buyer') {
    header('Location: index.php');
    exit;
}

$listing_id = isset($_GET['listing_id']) ? intval($_GET['listing_id']) : 0;
$buyer_id = $_SESSION['user_id'];

// Get listing and seller info
$listing_sql = "SELECT l.*, u.full_name as seller_name, u.user_id as seller_id 
                FROM listings l 
                JOIN users u ON l.seller_id = u.user_id 
                WHERE l.listing_id = $listing_id";
$listing_result = mysqli_query($conn, $listing_sql);
$listing = mysqli_fetch_assoc($listing_result);

if (!$listing) {
    header('Location: my_account.php');
    exit;
}

$seller_id = $listing['seller_id'];

// Checks if already rated
$check_sql = "SELECT rating_id FROM ratings WHERE listing_id = $listing_id AND buyer_id = $buyer_id";
$check_result = mysqli_query($conn, $check_sql);
$already_rated = mysqli_num_rows($check_result) > 0;

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$already_rated) {
    $rating = intval($_POST['rating']);
    $comment = isset($_POST['comment']) ? clean($_POST['comment']) : '';
    
    if ($rating >= 1 && $rating <= 5) {
        $stmt = mysqli_prepare($conn, 
            "INSERT INTO ratings (listing_id, buyer_id, seller_id, rating, comment) VALUES (?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "iiiis", $listing_id, $buyer_id, $seller_id, $rating, $comment);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = '<div class="alert alert-success"><i class="ti ti-check"></i> Thank you for your rating!</div>';
            $already_rated = true;
        } else {
            $message = '<div class="alert alert-error"><i class="ti ti-alert-circle"></i> Rating failed: ' . mysqli_error($conn) . '</div>';
        }
        mysqli_stmt_close($stmt);
    }
}

include 'includes/header.php';
?>

<div class="container" style="padding: 32px 20px; max-width: 600px; margin: 0 auto;">
    <h1 style="margin-bottom: 8px;">Rate Seller</h1>
    <p style="color: var(--text-light); margin-bottom: 24px;">
        How was your experience with <strong><?php echo htmlspecialchars($listing['seller_name']); ?></strong> 
        for <strong><?php echo htmlspecialchars($listing['title']); ?></strong>?
    </p>

    <?php echo $message; ?>

    <?php if ($already_rated): ?>
        <div class="alert alert-info" style="padding: 16px; background: rgba(100,100,200,0.1); border-radius: 8px; margin-bottom: 24px;">
            <i class="ti ti-info-circle"></i> You have already rated this seller for this listing.
        </div>
        <a href="my_account.php" class="btn-primary"><i class="ti ti-arrow-left"></i> Back to My Account</a>
    <?php else: ?>
        <form method="POST" style="background: var(--bg-card); border: 1px solid rgba(100,100,200,0.2); border-radius: 12px; padding: 24px;">
            <div style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 12px; font-weight: 600;">Your Rating</label>
                <div style="display: flex; gap: 12px; justify-content: center;" id="starContainer">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <label style="cursor: pointer;">
                            <input type="radio" name="rating" value="<?php echo $i; ?>" required style="display: none;">
                            <i class="ti ti-star star-rating" data-value="<?php echo $i; ?>" 
                               style="font-size: 40px; color: var(--text-light); transition: color 0.2s;"></i>
                        </label>
                    <?php endfor; ?>
                </div>
                <p id="ratingText" style="text-align: center; margin-top: 8px; color: var(--text-light); font-size: 14px;"></p>
            </div>
            
            <div class="form-group" style="margin-bottom: 24px;">
                <label>Your Comment (optional)</label>
                <textarea name="comment" rows="4" placeholder="Share your experience with this seller..."
                    style="width: 100%; padding: 12px; border: 1px solid rgba(100,100,200,0.3); border-radius: 8px; background: var(--bg-dark); color: var(--text); resize: vertical;"></textarea>
            </div>
            
            <button type="submit" class="form-btn" style="width: 100%;">
                <i class="ti ti-star-filled"></i> Submit Rating
            </button>
        </form>
    <?php endif; ?>
</div>

<style>
.star-rating:hover,
.star-rating.active { color: #ffc107 !important; }
</style>

<script>
const stars = document.querySelectorAll('.star-rating');
const ratingText = document.getElementById('ratingText');
const labels = ['Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];

stars.forEach(star => {
    star.parentElement.addEventListener('mouseenter', function() {
        const val = parseInt(this.querySelector('input').value);
        updateStars(val);
    });
});

document.getElementById('starContainer').addEventListener('mouseleave', function() {
    const checked = document.querySelector('input[name="rating"]:checked');
    updateStars(checked ? parseInt(checked.value) : 0);
});

stars.forEach(star => {
    star.parentElement.addEventListener('click', function() {
        const val = parseInt(this.querySelector('input').value);
        updateStars(val);
        ratingText.textContent = labels[val - 1];
    });
});

function updateStars(val) {
    stars.forEach((s, i) => {
        s.style.color = i < val ? '#ffc107' : 'var(--text-light)';
    });
}
</script>

<?php include 'includes/footer.php'; ?>