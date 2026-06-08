<?php

// This file is included at the top of every page
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechTrade - Buy & Sell Electronics in South Africa</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">

    <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-4EWM40GR4Y"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-4EWM40GR4Y');
</script>
</head>

<body>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container nav-container">

            <!-- Back Button (hidden on homepage) -->
            <?php
            $current_page = basename($_SERVER['PHP_SELF']);
            if ($current_page != 'index.php' && $current_page != 'index.html'):
            ?>
                <a href="javascript:history.back()" style="color: white; font-size: 20px; margin-right: 12px; padding: 8px; border-radius: 50%; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.15)'" onmouseout="this.style.background='none'">
                    <i class="ti ti-arrow-left"></i>
                </a>
            <?php endif; ?>

            <!-- Logo -->
            <a href="/index.php" class="nav-logo">
                <a href="/index.php" class="nav-logo">
                    <svg width="130" height="32" viewBox="0 0 130 32">
                        <rect x="0" y="4" width="24" height="24" rx="6" fill="#1A56DB" />
                        <text x="12" y="21" font-family="Arial" font-size="14" font-weight="700" fill="white" text-anchor="middle">T</text>
                        <text x="32" y="22" font-family="Arial" font-size="17" font-weight="600" fill="white">TechTrade</text>
                    </svg>
                </a>

                <!-- Search Bar -->
                <form action="/listings.php" method="GET" class="nav-search">
                    <input type="text" name="search" placeholder="Search electronics..." value="<?php echo isset($_GET['search']) ? clean($_GET['search']) : ''; ?>">
                    <button type="submit"><i class="ti ti-search"></i></button>
                </form>

                <!-- Navigation Links -->
                <ul class="nav-links">
                    <li><a href="/index.php">Home</a></li>
                    <li><a href="/listings.php">Browse</a></li>

                    <?php $cart_count = 0; ?>
                    <?php if (is_logged_in()): ?>

                        <?php if (is_seller()): ?>
                            <li><a href="/seller_dashboard.php">Dashboard</a></li>
                            <li><a href="/post_listing.php" class="btn-post">+ Sell Item</a></li>
                        <?php endif; ?>

                        <?php if (is_admin()): ?>
                            <li><a href="/admin_dashboard.php">Admin</a></li>
                        <?php endif; ?>

                        <?php

                        $cart_count = 0;
                        if (is_logged_in() && isset($conn)) {
                            $uid = intval($_SESSION['user_id']);
                            $count_sql = "SELECT SUM(quantity) AS total FROM cart WHERE user_id = $uid";
                            $count_result = mysqli_query($conn, $count_sql);
                            if ($count_result) {
                                $count_row = mysqli_fetch_assoc($count_result);
                                $cart_count = $count_row['total'] ? intval($count_row['total']) : 0;
                            }
                        }
                        ?>
                        <li>
                            <a href="/cart.php" style="position: relative;">
                                <i class="ti ti-shopping-cart"></i> Cart
                                <?php if ($cart_count > 0): ?>
                                    <span style="position: absolute; top: -8px; right: -8px; background: var(--accent); color: white; font-size: 11px; font-weight: 700; padding: 2px 6px; border-radius: 10px; min-width: 18px; text-align: center;"><?php echo $cart_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li><a href="/my_account.php">My Account</a></li>
                        <li><a href="/logout.php">Logout</a></li>

                    <?php else: ?>
                        <li><a href="/login.php">Login</a></li>
                        <li><a href="/register.php" class="btn-register">Register</a></li>
                    <?php endif; ?>
                </ul>
                </ul>

                </ul>

                <!-- Always-visible icons group -->
                <div class="nav-icons">
                    <a href="/cart.php" class="nav-icon" style="position: relative; color: white;">
                        <i class="ti ti-shopping-cart"></i>
                        <?php if ($cart_count > 0): ?>
                            <span style="position: absolute; top: -6px; right: -6px; background: var(--accent); color: white; font-size: 10px; font-weight: 700; padding: 2px 5px; border-radius: 10px; min-width: 16px; text-align: center;"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>

                    <button class="theme-toggle" onclick="toggleDarkMode()" title="Toggle Dark Mode">
                        <i class="ti ti-moon" id="themeIcon"></i>
                    </button>
                </div>

                <!-- Mobile Menu Button -->
                <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                    <i class="ti ti-menu-2"></i>
                </button>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <a href="/index.php">Home</a>
        <a href="/listings.php">Browse</a>
        <?php if (is_logged_in()): ?>
            <a href="/cart.php">
                <i class="ti ti-shopping-cart"></i> Cart
                <?php if ($cart_count > 0): ?>
                    (<?php echo $cart_count; ?>)
                <?php endif; ?>
            </a>
            <?php if (is_seller()): ?>
                <a href="/seller_dashboard.php">Dashboard</a>
                <a href="/post_listing.php">+ Sell Item</a>
            <?php endif; ?>
            <?php if (is_admin()): ?>
                <a href="/admin_dashboard.php">Admin</a>
            <?php endif; ?>
            <a href="/my_account.php">My Account</a>
            <a href="/favourites.php">Favourites</a>
            <a href="/messages.php">Messages</a>
            <a href="/logout.php">Logout</a>
        <?php else: ?>
            <a href="/login.php">Login</a>
            <a href="/register.php">Register</a>
        <?php endif; ?>
    </div>