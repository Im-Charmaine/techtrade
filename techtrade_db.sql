-- TechTrade Database
-- C2C Electronics Marketplace for South African Township Resellers


/*CREATE DATABASE IF NOT EXISTS techtrade CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE techtrade;*/

-- Users: buyers, sellers, and admins all in one table
-- Role field controls what each user can see and do
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    location VARCHAR(100),
    role ENUM('buyer','seller','admin') DEFAULT 'buyer',
    is_verified TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories for electronics only
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    icon VARCHAR(30) DEFAULT 'ti-device-mobile'
);

-- Listings: products posted by sellers
CREATE TABLE listings (
    listing_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    condition_status ENUM('New','Used - Like New','Used - Good','Used - Fair') DEFAULT 'Used - Good',
    image_url VARCHAR(255) DEFAULT '',
    status ENUM('Listed','Pending','Sold','Cancelled') DEFAULT 'Listed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

-- Transactions: tracks deals between buyers and sellers
CREATE TABLE transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    status ENUM('Listed','Pending','Sold','Cancelled') DEFAULT 'Pending',
    payment_method VARCHAR(50) DEFAULT 'Cash on Collection',
    delivery_method VARCHAR(50) DEFAULT 'Meet in Person',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Ratings: buyers rate sellers after completed deals
CREATE TABLE ratings (
    rating_id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Messages: 
CREATE TABLE messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    listing_id INT,
    message TEXT NOT NULL,
    is_read TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE SET NULL
);

-- Favourites: 
CREATE TABLE favourites (
    favourite_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    listing_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE CASCADE,
    UNIQUE KEY unique_fav (user_id, listing_id)
);

-- Reports: users report suspicious listings or behaviour
CREATE TABLE reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    reporter_id INT NOT NULL,
    reported_user_id INT,
    listing_id INT,
    reason VARCHAR(100) NOT NULL,
    details TEXT,
    status ENUM('Open','Resolved','Dismissed') DEFAULT 'Open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (reported_user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE SET NULL
);

-- Admin logs: tracks everything admins do
CREATE TABLE admin_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    target_type VARCHAR(50),
    target_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    listing_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, listing_id)
);

-- Seed data
INSERT INTO users (full_name, email, password_hash, phone, location, role, is_verified) VALUES
('Admin User', 'admin@techtrade.co.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0111234567', 'Johannesburg', 'admin', 1),
('Thabo Mokoena', 'thabo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0821234567', 'Soweto', 'seller', 1),
('Naledi Khumalo', 'naledi@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0837654321', 'Temba', 'buyer', 0),
('Sipho Dlamini', 'sipho@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0712345678', 'Alexandra', 'seller', 1),
('Lerato Peters', 'lerato@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0765432109', 'Mamelodi', 'buyer', 0);


INSERT INTO categories (name, icon) VALUES
('Smartphones', 'ti-device-mobile'),
('Laptops', 'ti-device-laptop'), 
('Tablets', 'ti-device-tablet'),
('Audio', 'ti-headphones'),
('Gaming', 'ti-device-gamepad-2'),
('Accessories', 'ti-plug');

INSERT INTO listings (seller_id, category_id, title, description, price, condition_status, image_url, status) VALUES
(2, 1, 'iPhone 13 Pro 256GB', 'Great condition, battery health 92%. Comes with original box and charger. No scratches on screen. Selling because I upgraded to iPhone 15.', 8500.00, 'Used - Like New', '', 'Listed'),
(2, 2, 'HP Pavilion Laptop 15"', 'Intel i5, 8GB RAM, 512GB SSD. Perfect for students and remote work. Minor wear on keyboard but everything works perfectly.', 4500.00, 'Used - Good', '', 'Listed'),
(4, 1, 'Samsung Galaxy S21 128GB', 'Unlocked, screen protector on since day one. Includes case and fast charger. ', 5500.00, 'Used - Like New', '', 'Listed'),
(4, 3, 'iPad Air 4th Gen 64GB', 'WiFi only. Minor dent on corner but screen is perfect. Apple Pencil compatible. Great for note taking.', 6000.00, 'Used - Good', '', 'Listed'),
(2, 5, 'PS5 Console Digital Edition', '1 controller included. 6 months old, barely used. Still under warranty.', 7500.00, 'Used - Like New', '', 'Pending'),
(4, 4, 'Sony  Headphones', 'Noise cancelling headphones. Original cable and case included. Battery lasts 30 hours. Best headphones I have owned.', 500.00, 'Used - Good', '', 'Listed'),
(2, 6, 'Anker PowerCore 20000mAh', 'Barely used power bank. Can charge a phone 5 times.', 350.00, 'Used - Like New', '', 'Listed'),
(4, 1, 'iPhone 11 64GB', 'Good condition, small crack on back glass but screen is fine. Battery 85%. Comes with charger.', 3200.00, 'Used - Fair', '', 'Listed');

INSERT INTO transactions (listing_id, buyer_id, seller_id, status, payment_method, delivery_method) VALUES
(5, 3, 2, 'Pending', 'Cash on Collection', 'Meet in Person');

INSERT INTO ratings (transaction_id, buyer_id, seller_id, rating, comment) VALUES
(1, 3, 2, 5, 'Thabo was punctual and the phone was exactly as described. Highly recommended!');
