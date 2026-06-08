<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (!function_exists('require_logged_in')) {
    function require_logged_in()
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }
    }
}

require_logged_in();

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bio = clean($_POST['bio']);
    $location = clean($_POST['location']);
    $phone = clean($_POST['phone']);
    
    $stmt = mysqli_prepare($conn, "UPDATE users SET bio = ?, location = ?, phone = ? WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "sssi", $bio, $location, $phone, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $message = '<div class="alert alert-success">Profile updated!</div>';
    }
}

// Fetch current
$result = mysqli_query($conn, "SELECT * FROM users WHERE user_id = $user_id");
$user = mysqli_fetch_assoc($result);

include 'includes/header.php';
?>

<div class="container" style="padding: 32px 20px; max-width: 500px;">
    <h1>Edit Profile</h1>
    <?php echo $message; ?>
    
    <form method="POST" class="form-card">
        <div class="form-group">
            <label>Bio</label>
            <textarea name="bio" rows="3" placeholder="Tell buyers about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label>Location</label>
            <input type="text" name="location" value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>" placeholder="e.g. Soweto, Johannesburg">
        </div>
        
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
        </div>
        
        <button type="submit" class="form-btn">Save Profile</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>