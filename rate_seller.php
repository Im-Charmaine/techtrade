<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (!is_logged_in() || $_SESSION['role'] != 'buyer') {
    header('Location: login.php');
    exit;
}

$transaction_id = isset($_GET['transaction_id']) ? intval($_GET['transaction_id']) : 0;
$buyer_id = $_SESSION['user_id'];

// Verify transaction exists, is completed, and buyer hasn't rated yet
$check = mysqli_prepare($conn, "SELECT t.*, l.seller_id, u.username as seller_name 
    FROM transactions t 
    JOIN listings l ON t.listing_id = l.listing_id 
    JOIN users u ON l.seller_id = u.user_id 
    WHERE t.transaction_id = ? AND t.buyer_id = ? AND t.status = 'Sold' 
    AND NOT EXISTS (SELECT 1 FROM ratings WHERE transaction_id = t.transaction_id)");
mysqli_stmt_bind_param($check, "ii", $transaction_id, $buyer_id);
mysqli_stmt_execute($check);
$result = mysqli_stmt_get_result($check);
$transaction = mysqli_fetch_assoc($result);

if (!$transaction) {
    die("Invalid transaction or already rated.");
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $score = intval($_POST['score']);
    $comment = clean($_POST['comment']);
    
    if ($score >= 1 && $score <= 5) {
        $insert = mysqli_prepare($conn, "INSERT INTO ratings (transaction_id, buyer_id, seller_id, score, comment) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($insert, "iiiis", $transaction_id, $buyer_id, $transaction['seller_id'], $score, $comment);
        
        if (mysqli_stmt_execute($insert)) {
            $message = '<div class="alert alert-success">Thank you for rating!</div>';
        } else {
            $message = '<div class="alert alert-error">Rating failed.</div>';
        }
    }
}

include 'includes/header.php';
?>

<div class="container" style="padding: 32px 20px; max-width: 500px;">
    <h1>Rate Seller</h1>
    <p style="color: var(--text-light); margin-bottom: 20px;">
        Rate your experience with <?php echo htmlspecialchars($transaction['seller_name']); ?>
    </p>
    
    <?php echo $message; ?>
    
    <form method="POST" class="form-card">
        <div class="form-group">
            <label>Rating (1-5 stars)</label>
            <div style="display: flex; gap: 8px; font-size: 28px;">
                <?php for($i = 1; $i <= 5; $i++): ?>
                <label style="cursor: pointer;">
                    <input type="radio" name="score" value="<?php echo $i; ?>" required style="display: none;">
                    <span class="star" onclick="selectStar(this)">★</span>
                </label>
                <?php endfor; ?>
            </div>
        </div>
        
        <div class="form-group">
            <label>Comment (optional)</label>
            <textarea name="comment" rows="3" placeholder="How was your experience?"></textarea>
        </div>
        
        <button type="submit" class="form-btn">Submit Rating</button>
    </form>
</div>

<style>
.star { color: var(--border); transition: color 0.2s; }
.star:hover, .star.active { color: var(--accent); cursor: pointer; }
</style>

<script>
function selectStar(el) {
    document.querySelectorAll('.star').forEach(s => s.classList.remove('active'));
    let current = el;
    while(current) {
        current.classList.add('active');
        current = current.previousElementSibling;
    }
}
</script>

<?php include 'includes/footer.php'; ?>