# TechTrade — C2C Electronics Marketplace 🛒

> A consumer-to-consumer (C2C) e-commerce platform built for informal electronics resellers in South African townships and peri-urban communities.

**Live Site:** [techtrade.infinityfree.me](https://techtrade.infinityfree.me)    
**Developer:** Charmaine

---

##  About TechTrade

Many township electronics resellers rely on unstructured channels like WhatsApp groups and Facebook Marketplace — platforms with no buyer protection, no organised listings, and no way to build seller reputation.

TechTrade solves this by providing a dedicated, structured marketplace where:
- **Buyers** can browse verified listings, search by category, and contact sellers safely
- **Sellers** can post listings with photos, manage inventory, and build a trusted profile
- **Admins** can monitor the platform, verify sellers, and manage reported content

---

## Features

### Buyer
- Register and log in with role-based access
- Browse and search listings by keyword and category
- View product details with seller profile and ratings
- Add items to cart and proceed to checkout
- Message sellers directly through the platform
- Save listings to favourites
- Rate sellers after completed transactions

### Seller
- Register as a verified seller
- Post listings with title, price, condition, category, and photo upload
- Edit and delete own listings
- Mark items as Sold from the dashboard
- View listing statistics and buyer interest

### Admin
- View platform-wide statistics (users, listings, transactions, reports)
- Verify and unverify seller accounts
- Review and action reported listings
- Monitor all platform activity

---

## 🛠 Tech Stack

| Layer | Technology |
|---|---|
| **Frontend** | HTML5, CSS3, JavaScript, Bootstrap 5 |
| **Backend** | PHP 8.x |
| **Database** | MySQL |
| **Icons** | Tabler Icons |
| **Analytics** | Google Analytics (G-4EWM40GR4Y) |
| **Hosting** | InfinityFree (free tier) |
| **Version Control** | Git / GitHub |
| **Local Dev** | XAMPP (Apache + MySQL + PHP) |

---

##  Demo Credentials

>  InfinityFree free hosting may experience intermittent downtime. If the live site is unreachable, use the local setup below.

| Role | Email | Password |
|---|---|---|
| Admin | admin@techtrade.co.za | password |
| Seller | seller@techtrade.co.za | password |
| Buyer | buyer@techtrade.co.za | password |

---

##  Local Setup

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP)
- Web browser
- Git (optional)

### Steps

**1. Clone the repository**
```bash
git clone https://github.com/Im-Charmaine/techtrade.git
```

**2. Move files to XAMPP**
```
C:\xampp\htdocs\techtrade\
```

**3. Import the database**
- Open [phpMyAdmin](http://localhost/phpmyadmin)
- Create a new database named `techtrade`
- Import `database/techtrade.sql`

**4. Configure the database connection**

Open `includes/db.php` and set your local credentials:
```php
if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
    $host   = 'localhost';
    $user   = 'root';
    $pass   = '';           // default XAMPP password is empty
    $dbname = 'techtrade';
} else {
    // Live InfinityFree credentials
    $host   = 'sql207.infinityfree.com';
    $user   = 'if0_42100238';
    $pass   = 'your_password';
    $dbname = 'if0_42100238_techtrade';
}
```

**5. Open in browser**
```
http://localhost/techtrade/
```

---

##  Project Structure

```
techtrade/
│
├── index.php                 # Homepage — featured listings
├── listings.php              # Browse & search all listings
├── listing.php               # Single listing detail page
├── login.php                 # User login
├── register.php              # User registration (buyer/seller)
├── logout.php                # Session logout
│
├── post_listing.php          # Seller — create a listing
├── edit_listing.php          # Seller — edit a listing
├── delete_listing.php        # Seller — delete a listing
├── update_status.php         # Seller — mark listing as sold
├── seller_dashboard.php      # Seller control panel
│
├── cart.php                  # Shopping cart
├── add_to_cart.php           # Add item to cart handler
├── checkout.php              # Checkout with payment/delivery options
│
├── messages.php              # Buyer ↔ Seller messaging
├── my_account.php            # Buyer orders, favourites, profile
├── edit_profile.php          # Edit bio, location, profile picture
├── rate_seller.php           # Submit seller rating after transaction
│
├── report_listing.php        # Report a suspicious listing
├── manage_report.php         # Admin — action a report
├── verify_seller.php         # Admin — verify/unverify a seller
│
├── admin_dashboard.php       # Admin control panel
│
├── includes/
│   ├── db.php                # Database connection (local/live detection)
│   ├── auth.php              # Authentication helpers & RBAC functions
│   ├── header.php            # Global navigation bar (cart badge, dark mode)
│   ├── footer.php            # Global footer
│   └── mailer.php            # Email notification helper (PHP mail())
│
├── css/
│   └── style.css             # Custom dark theme, CSS variables, responsive layout
│
├── js/
│   └── main.js               # Dark mode toggle, form validation, image preview
│
├── uploads/                  # Product listing images (user uploaded)
│
└── database/
    └── techtrade.sql         # Full database export
```

---

##  Database Schema

| Table | Purpose |
|---|---|
| `users` | Buyers, sellers, and admins with roles and verification status |
| `listings` | Product listings with title, price, condition, category, image |
| `categories` | Electronics categories (Phones, Laptops, Tablets, etc.) |
| `cart` | Shopping cart items linked to users and listings |
| `transactions` | Orders with payment method and delivery method |
| `messages` | Buyer ↔ Seller direct messages per listing |
| `ratings` | Seller ratings (1–5 stars) submitted by buyers |
| `reports` | Flagged listings reported by users for admin review |
| `favourites` | Listings saved by buyers for later |

---

##  Security

- Passwords hashed using `password_hash()` with `PASSWORD_DEFAULT`
- All database queries use **prepared statements** (`mysqli_prepare`) to prevent SQL injection
- All user input sanitised via `htmlspecialchars()` and `strip_tags()`
- File uploads restricted to JPG, PNG, GIF with a 5MB size limit
- Session-based authentication with role-based access control (RBAC)
- Users cannot access pages outside their role (buyers can't post listings, sellers can't access admin, etc.)

---

##  Known Limitations

- **Hosting:** InfinityFree free tier has intermittent downtime and no guaranteed uptime SLA
- **Payments:** Checkout records payment method preference but does not process real payments (PayFast integration is planned — requires business registration)
- **Messaging:** Requires page refresh to see new messages (no WebSocket/real-time)
- **Email:** Uses PHP `mail()` which may land in spam on some providers; production would use SendGrid or Mailgun

---

##  Future Enhancements

| Priority | Feature |
|---|---|
| 🔴 High | PayFast payment gateway integration (real card/EFT processing) |
| 🔴 High | Real-time messaging with WebSockets |
| 🟡 Medium | Courier API integration (Pudo, The Courier Guy) for shipment tracking |
| 🟡 Medium | SMTP email notifications via SendGrid/Mailgun |
| 🟡 Medium | Progressive Web App (PWA) — installable on Android |
| 🟢 Low | AI-powered listing recommendations based on browsing history |
| 🟢 Low | Browser push notifications for messages and order updates |

---

##  References

- Iqbolshoh (2024) *PHP MySQL Marketplace* [Source code]. GitHub. Available at: https://github.com/Iqbolshoh/php-mysql-marketplace
- Origami Marketplace (2024) *How to Build a C2C Marketplace from Scratch: Ultimate Guide*. Available at: https://origami-marketplace.com/en-gb/how-to-build-c2c-marketplace-from-scratch-ultimate-guide/
- Coding with Eli (2024) *PHP MySQL E-Commerce Website Tutorial* [Online video]. YouTube. Available at: https://youtube.com/playlist?list=PL-h5aNeRKouEaGrQj6EXaqZsagEphQboI
- Bootstrap Team (2024) *Bootstrap 5.3 Documentation*. Available at: https://getbootstrap.com/docs/5.3/
- PHP Group (2024) *PHP Manual*. Available at: https://www.php.net/docs.php

---

## 📄 License

This project was developed for educational purposes at Eduvos, South Africa.  
Not licensed for commercial use.

---

<p align="center">
  Built with 💙 by Charmaine &bull; Eduvos South Africa &bull; June 2026
</p>
