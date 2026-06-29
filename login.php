<?php
session_start(); 
require_once 'includes/db.php';
require_once 'includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = clean($_POST['email']);
    $password = $_POST['password'];
    
    if ($email == '' || $password == '') {
        $error = 'Please enter both email and password.';
    } else {
        // use LIMIT 1
        $stmt = mysqli_prepare($conn, 
            "SELECT user_id, full_name, email, password, role 
             FROM users 
             WHERE email = ? 
             LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            
            if (password_verify($password, $user['password'])) {
                
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                
                switch ($user['role']) {
                    case 'admin':
                        header("Location: admin_dashboard.php");
                        break;
                    case 'seller':
                        header("Location: seller_dashboard.php");
                        break;
                    default:
                        header("Location: index.php");
                }
                exit();
            } else {
                $error = 'Invalid password.';
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
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>