<?php
// contact.php - Contact page for TechTrade

require_once 'includes/db.php';
require_once 'includes/auth.php';

$message = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = clean($_POST['name']);
    $email = clean($_POST['email']);
    $subject = clean($_POST['subject']);
    $body = clean($_POST['message']);
    
    if ($name == '' || $email == '' || $body == '') {
        $message = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
    } else {
        
        $success = 'Thank you for contacting us! We will reply within 24 hours.';
    }
}

include 'includes/header.php';
?>

<section class="hero" style="padding: 40px 0;">
    <div class="container">
        <h1>Contact <span>TechTrade</span></h1>
        <p>Have questions or need help? We're here for you.</p>
    </div>
</section>

<section class="container" style="padding: 48px 0;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 48px;">
        <div>
            <h2 style="margin-bottom: 24px;">Get in Touch</h2>
            
            <?php if ($message != ''): ?>
                <div class="alert alert-error" style="margin-bottom: 20px;"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($success != ''): ?>
                <div class="alert alert-success" style="margin-bottom: 20px;"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="contact.php">
                <div class="form-group">
                    <label>Your Name *</label>
                    <input type="text" name="name" placeholder="Thabo Mokoena" required>
                </div>
                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" placeholder="thabo@example.com" required>
                </div>
                <div class="form-group">
                    <label>Subject</label>
                    <select name="subject">
                        <option>General Question</option>
                        <option>Report a Problem</option>
                        <option>Seller Verification</option>
                        <option>Payment Issue</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Message *</label>
                    <textarea name="message" placeholder="How can we help you?" required style="min-height: 120px;"></textarea>
                </div>
                <button type="submit" class="form-btn">Send Message</button>
            </form>
        </div>
        
        <div>
            <h2 style="margin-bottom: 24px;">Other Ways to Reach Us</h2>
            
            <div style="background: var(--white); padding: 24px; border-radius: var(--radius-lg); box-shadow: var(--shadow); margin-bottom: 20px;">
                <h4 style="margin-bottom: 8px; color: var(--primary);"><i class="ti ti-mail"></i> Email</h4>
                <p style="color: var(--text-light); font-size: 14px;">support@techtrade.co.za<br>admin@techtrade.co.za</p>
            </div>
            
            <div style="background: var(--white); padding: 24px; border-radius: var(--radius-lg); box-shadow: var(--shadow); margin-bottom: 20px;">
                <h4 style="margin-bottom: 8px; color: var(--primary);"><i class="ti ti-phone"></i> Phone</h4>
                <p style="color: var(--text-light); font-size: 14px;">011 123 4567 (Mon-Fri, 8am-5pm)<br>082 123 4567 (WhatsApp only)</p>
            </div>
            
            <div style=" margin-bottom: 20px;">
               
            </div>
            
            <div style="background: var(--white); padding: 24px; border-radius: var(--radius-lg); box-shadow: var(--shadow);">
                <h4 style="margin-bottom: 8px; color: var(--primary);"><i class="ti ti-clock"></i> Response Time</h4>
                <p style="color: var(--text-light); font-size: 14px;">We typically respond within 24 hours during business days. For urgent issues, use WhatsApp.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>