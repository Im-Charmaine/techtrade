<?php
// how_it_works.php - Explains how TechTrade works for buyers and sellers

require_once 'includes/db.php';
require_once 'includes/auth.php';

include 'includes/header.php';
?>

<section class="hero" style="padding: 40px 0;">
    <div class="container">
        <h1>How <span>TechTrade</span> Works</h1>
        <p>Simple steps to buy and sell electronics in your community</p>
    </div>
</section>

<section class="container" style="padding: 48px 0;">
    <div class="steps-grid">
        <div class="step-card">
            <div class="step-number">1</div>
            <h3>Create Your Account</h3>
            <p>Register as a buyer or seller. Sellers need admin verification before they can list items. This keeps the marketplace safe and trustworthy.</p>
        </div>
        <div class="step-card">
            <div class="step-number">2</div>
            <h3>Browse or List</h3>
            <p>Buyers search and filter electronics by category, price, and location. Sellers upload photos, set prices, and describe their items.</p>
        </div>
        <div class="step-card">
            <div class="step-number">3</div>
            <h3>Connect & Deal</h3>
            <p>Message sellers directly on the platform. Agree on price, payment method, and delivery. Meet in person or use Pudo/Courier.</p>
        </div>
    </div>
    
    <div style="margin-top: 48px; background: var(--white); padding: 32px; border-radius: var(--radius-lg); box-shadow: var(--shadow);">
        <h2 style="margin-bottom: 24px;">For Buyers</h2>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <div>
                <h4 style="margin-bottom: 8px; color: var(--primary);"><i class="ti ti-search"></i> Search & Filter</h4>
                <p style="color: var(--text-light); font-size: 14px;">Find exactly what you need by category, price range, condition, and seller location.</p>
            </div>
            <div>
                <h4 style="margin-bottom: 8px; color: var(--primary);"><i class="ti ti-heart"></i> Save Favourites</h4>
                <p style="color: var(--text-light); font-size: 14px;">Bookmark items you like and compare them later before making a decision.</p>
            </div>
            <div>
                <h4 style="margin-bottom: 8px; color: var(--primary);"><i class="ti ti-message-circle"></i> Chat with Sellers</h4>
                <p style="color: var(--text-light); font-size: 14px;">Ask questions, negotiate price, and arrange meeting details without sharing your phone number.</p>
            </div>
            <div>
                <h4 style="margin-bottom: 8px; color: var(--primary);"><i class="ti ti-star"></i> Rate Your Experience</h4>
                <p style="color: var(--text-light); font-size: 14px;">After completing a deal, rate the seller to help other buyers make informed choices.</p>
            </div>
        </div>
    </div>
    
    <div style="margin-top: 32px; background: var(--white); padding: 32px; border-radius: var(--radius-lg); box-shadow: var(--shadow);">
        <h2 style="margin-bottom: 24px;">For Sellers</h2>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <div>
                <h4 style="margin-bottom: 8px; color: var(--accent);"><i class="ti ti-user-check"></i> Get Verified</h4>
                <p style="color: var(--text-light); font-size: 14px;">Admin verifies your identity before you can list. This builds trust with buyers.</p>
            </div>
            <div>
                <h4 style="margin-bottom: 8px; color: var(--accent);"><i class="ti ti-camera"></i> Post with Photos</h4>
                <p style="color: var(--text-light); font-size: 14px;">Upload clear images, set a fair price, and describe condition honestly for faster sales.</p>
            </div>
            <div>
                <h4 style="margin-bottom: 8px; color: var(--accent);"><i class="ti ti-cash"></i> Zero Listing Fees</h4>
                <p style="color: var(--text-light); font-size: 14px;">Keep 100% of your sale. No commissions, no hidden charges, no monthly subscriptions.</p>
            </div>
            <div>
                <h4 style="margin-bottom: 8px; color: var(--accent);"><i class="ti ti-shield-check"></i> Safe Transactions</h4>
                <p style="color: var(--text-light); font-size: 14px;">Choose from multiple payment options: Cash, EFT, PayJustNow, or SnapScan.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>