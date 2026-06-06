<?php
// safety_tips.php - Safety guidelines for buyers and sellers

require_once 'includes/db.php';
require_once 'includes/auth.php';

include 'includes/header.php';
?>

<section class="hero" style="padding: 40px 0;">
    <div class="container">
        <h1>Safety <span>Tips</span></h1>
        <p>Stay safe when buying and selling electronics</p>
    </div>
</section>

<section class="container" style="padding: 48px 0;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px;">
        <div style="background: var(--white); padding: 32px; border-radius: var(--radius-lg); box-shadow: var(--shadow);">
            <h2 style="margin-bottom: 24px; color: var(--primary);"><i class="ti ti-shopping-cart"></i> For Buyers</h2>

            <div style="margin-bottom: 20px;">
                <h4 style="margin-bottom: 8px;"><i class="ti ti-map-pin" style="color: var(--accent);"></i> Meet in Public Places</h4>
                <p style="color: var(--text-light); font-size: 14px;">Always meet at a busy public location like a mall, police station, or petrol station. Never go to someone's home alone.</p>
            </div>

            <div style="margin-bottom: 20px;">
                <h4 style="margin-bottom: 8px;"><i class="ti ti-device-mobile" style="color: var(--accent);"></i> Inspect Before Paying</h4>
                <p style="color: var(--text-light); font-size: 14px;">Test the device fully before handing over money. Check screen, battery, buttons, and ask for the original box if available.</p>
            </div>

            <div style="margin-bottom: 20px;">
                <h4 style="margin-bottom: 8px;"><i class="ti ti-shield-check" style="color: var(--accent);"></i> Check Seller Ratings</h4>
                <p style="color: var(--text-light); font-size: 14px;">Only buy from verified sellers with good ratings. Read previous buyer reviews before committing.</p>
            </div>

            <div style="margin-bottom: 20px;">
                <h4 style="margin-bottom: 8px;"><i class="ti ti-cash" style="color: var(--accent);"></i> Use Safe Payment Methods</h4>
                <p style="color: var(--text-light); font-size: 14px;">Cash on collection is safest for local deals. For EFT, verify the seller's bank details before transferring.</p>
            </div>

            <div>
                <h4 style="margin-bottom: 8px;"><i class="ti ti-report" style="color: var(--accent);"></i> Report Suspicious Listings</h4>
                <p style="color: var(--text-light); font-size: 14px;">If a deal seems too good to be true, it probably is. Use the Report button on any suspicious listing.</p>
            </div>
        </div>

        <div style="background: var(--white); padding: 32px; border-radius: var(--radius-lg); box-shadow: var(--shadow);">
            <h2 style="margin-bottom: 24px; color: var(--accent);"><i class="ti ti-tag"></i> For Sellers</h2>

            <div style="margin-bottom: 20px;">
                <h4 style="margin-bottom: 8px;"><i class="ti ti-camera" style="color: var(--primary);"></i> Be Honest in Descriptions</h4>
                <p style="color: var(--text-light); font-size: 14px;">Accurately describe condition, defects, and battery health. Honesty builds trust and avoids disputes.</p>
            </div>

            <div style="margin-bottom: 20px;">
                <h4 style="margin-bottom: 8px;"><i class="ti ti-photo" style="color: var(--primary);"></i> Upload Clear Photos</h4>
                <p style="color: var(--text-light); font-size: 14px;">Show the device from all angles including any scratches or damage. Good photos sell faster.</p>
            </div>

            <div style="margin-bottom: 20px;">
                <h4 style="margin-bottom: 8px;"><i class="ti ti-users" style="color: var(--primary);"></i> Meet in Public Places</h4>
                <p style="color: var(--text-light); font-size: 14px;">Same rule for sellers — meet at safe public locations. Bring a friend if possible.</p>
            </div>

            <div style="margin-bottom: 20px;">
                <h4 style="margin-bottom: 8px;"><i class="ti ti-receipt" style="color: var(--primary);"></i> Keep Proof of Sale</h4>
                <p style="color: var(--text-light); font-size: 14px;">Save screenshots of the listing, chat messages, and payment confirmation. Useful if disputes arise.</p>
            </div>

            <div>
                <h4 style="margin-bottom: 8px;"><i class="ti ti-lock" style="color: var(--primary);"></i> Don't Share Personal Info</h4>
                <p style="color: var(--text-light); font-size: 14px;">Use TechTrade's messaging system. Don't share your home address or ID documents with strangers.</p>
            </div>
        </div>
    </div>

    <div style="margin-top: 32px; background: var(--primary); color: white; padding: 24px; border-radius: var(--radius-lg); text-align: center;">
        <h3 style="margin-bottom: 8px;"><i class="ti ti-alert-circle"></i> Emergency?</h3>
        <p>If you feel unsafe during a meetup, leave immediately and contact local authorities. Your safety is more important than any deal.</p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>