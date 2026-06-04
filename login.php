<?php

// Uses prepared statements to prevent SQL injection

require_once 'includes/db.php';
require_once 'includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    //  Get and clean the inputs
    $email = clean($_POST['email']);
    $password = $_POST['password'];
    
    //  Validate
    if ($email == '' || $password == '') {
        $error = 'Please enter both email and password.';
    } else {
        
        //  Find user in database (SECURE - prepared statement)
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            
            //  Verify password against stored hash
            if (password_verify($password, $user['password_hash'])) {
                
                // Save user info in session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                //  Redirect based on role
                if ($user['role'] == 'admin') {
                    header("Location: /techtrade/admin_dashboard.php");
                } elseif ($user['role'] == 'seller') {
                    header("Location: /techtrade/seller_dashboard.php");
                } else {
                    header("Location: /techtrade/index.php");
                }
                exit();
                
            } else {
                $error = 'Password is incorrect.';
            }
        } else {
            $error = 'No account found with that email.';
        }
        
        mysqli_stmt_close($stmt);
    }
}

include 'includes/header.php';
?>

<section class="form-section">
    <div class="form-card">
        <h2>Welcome Back</h2>
        <p class="subtitle">Login to your TechTrade account</p>
        
        <?php if ($error != ''): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form action="login.php" method="POST" onsubmit="return validateLogin()">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" id="email" placeholder="you@example.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" id="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="form-btn">Login</button>
        </form>
        
        <p class="form-link">
            Don't have an account? <a href="/techtrade/register.php">Register here</a>
        </p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>