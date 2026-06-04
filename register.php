<?php

// Uses prepared statements to prevent SQL injection

require_once 'includes/db.php';
require_once 'includes/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    //  Get and CLEAN all form data
    $full_name = clean($_POST['full_name']);
    $email = clean($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = clean($_POST['phone']);
    $location = clean($_POST['location']);
    $role = clean($_POST['role']);
    
    //  Validate the data
    if ($full_name == '' || $email == '' || $password == '' || $role == '') {
        $error = 'Please fill in all required fields.';
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    }
    elseif ($password != $confirm_password) {
        $error = 'Passwords do not match.';
    }
    elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    }
    else {
        
        //  Check if email already exists (SECURE - prepared statement)
        $check_stmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check_stmt, "s", $email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        
        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $error = 'An account with this email already exists.';
        } else {
            
            //  Hash the password securely
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user into database (SECURE - prepared statement)
            $stmt = mysqli_prepare($conn, 
                "INSERT INTO users (full_name, email, password_hash, phone, location, role, is_verified) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            
            $verified = 0;
            mysqli_stmt_bind_param($stmt, "ssssssi", $full_name, $email, $hash, $phone, $location, $role, $verified);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Account created successfully! You can now login.';
            } else {
                $error = 'Something went wrong. Please try again.';
            }
            
            mysqli_stmt_close($stmt);
        }
        
        mysqli_stmt_close($check_stmt);
    }
}

include 'includes/header.php';
?>

<section class="form-section">
    <div class="form-card">
        <h2>Create Account</h2>
        <p class="subtitle">Join TechTrade and start buying or selling electronics</p>
        
        <?php if ($error != ''): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success != ''): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form action="register.php" method="POST" onsubmit="return validateRegister()">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" id="full_name" placeholder="Thabo Mokoena" required>
            </div>
            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" id="email" placeholder="thabo@example.com" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" placeholder="082 123 4567">
            </div>
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" placeholder="Soweto, Johannesburg">
            </div>
            <div class="form-group">
                <label>I want to... *</label>
                <select name="role" id="role" required>
                    <option value="">Select one...</option>
                    <option value="buyer">Buy electronics</option>
                    <option value="seller">Sell electronics</option>
                </select>
            </div>
            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" id="password" placeholder="Min 6 characters" required>
            </div>
            <div class="form-group">
                <label>Confirm Password *</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Repeat password" required>
            </div>
            <button type="submit" class="form-btn">Create Account</button>
        </form>
        
        <p class="form-link">
            Already have an account? <a href="/techtrade/login.php">Login here</a>
        </p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>