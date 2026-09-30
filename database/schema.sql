-- Daraz-Style Nano Modular Affiliate Platform
-- Database Schema (MySQL 8.0 / MariaDB Compatible)

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `withdrawals`;
DROP TABLE IF EXISTS `recharges`;
DROP TABLE IF EXISTS `affiliate_commissions`;
DROP TABLE IF EXISTS `conversions`;
DROP TABLE IF EXISTS `affiliate_clicks`;
DROP TABLE IF EXISTS `affiliate_links`;
DROP TABLE IF EXISTS `affiliate_profiles`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `cart_items`;
DROP TABLE IF EXISTS `carts`;
DROP TABLE IF EXISTS `product_images`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `wallet_transactions`;
DROP TABLE IF EXISTS `wallets`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. USERS (email and phone are both nullable & unique to support email-only, phone-only, or both)
CREATE TABLE `users` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) DEFAULT NULL UNIQUE,
  `phone` VARCHAR(20) DEFAULT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('customer', 'affiliate', 'vendor', 'admin') NOT NULL DEFAULT 'customer',
  `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
  `credit_current` INT UNSIGNED NOT NULL DEFAULT 0,
  `credit_max` INT UNSIGNED NOT NULL DEFAULT 0,
  `otp_code` VARCHAR(20) DEFAULT NULL,
  `is_verified` TINYINT(1) NOT NULL DEFAULT 1,
  `failed_login_attempts` INT UNSIGNED NOT NULL DEFAULT 0,
  `lockout_until` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_email_status` (`email`, `status`),
  INDEX `idx_phone_status` (`phone`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. WALLETS
CREATE TABLE `wallets` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL UNIQUE,
  `balance` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `pending_balance` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'BDT',
  `status` ENUM('active', 'frozen') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. WALLET TRANSACTIONS
CREATE TABLE `wallet_transactions` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `wallet_id` BIGINT UNSIGNED NOT NULL,
  `reference_id` VARCHAR(100) NOT NULL UNIQUE,
  `type` ENUM('recharge', 'withdrawal', 'commission_payout', 'purchase_payment', 'refund') NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `balance_before` DECIMAL(12,2) NOT NULL,
  `balance_after` DECIMAL(12,2) NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `status` ENUM('pending', 'completed', 'failed', 'reversed') NOT NULL DEFAULT 'completed',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`wallet_id`) REFERENCES `wallets`(`id`) ON DELETE CASCADE,
  INDEX `idx_wallet_type` (`wallet_id`, `type`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. CATEGORIES
CREATE TABLE `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `parent_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `icon` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  INDEX `idx_slug_status` (`slug`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. PRODUCTS
CREATE TABLE `products` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED NOT NULL,
  `vendor_id` BIGINT UNSIGNED DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(280) NOT NULL UNIQUE,
  `sku` VARCHAR(100) NOT NULL UNIQUE,
  `price` DECIMAL(12,2) NOT NULL,
  `discount_price` DECIMAL(12,2) DEFAULT NULL,
  `stock_quantity` INT NOT NULL DEFAULT 0,
  `commission_rate` DECIMAL(5,2) NOT NULL DEFAULT 5.00,
  `short_description` TEXT DEFAULT NULL,
  `full_description` LONGTEXT DEFAULT NULL,
  `main_image` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'inactive', 'out_of_stock') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`vendor_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_category_status` (`category_id`, `status`),
  INDEX `idx_price` (`price`),
  INDEX `idx_status_created` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. PRODUCT IMAGES
CREATE TABLE `product_images` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. CARTS
CREATE TABLE `carts` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `session_id` VARCHAR(128) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_session` (`session_id`),
  INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. CART ITEMS
CREATE TABLE `cart_items` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `cart_id` BIGINT UNSIGNED NOT NULL,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
  `selected_size` VARCHAR(50) DEFAULT NULL,
  `selected_color` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`cart_id`) REFERENCES `carts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. ORDERS
CREATE TABLE `orders` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `customer_name` VARCHAR(150) DEFAULT NULL,
  `customer_phone` VARCHAR(30) DEFAULT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL,
  `paid_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `due_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `payment_method` VARCHAR(50) DEFAULT 'Wallet',
  `payment_type` VARCHAR(20) DEFAULT 'full',
  `payment_status` ENUM('unpaid', 'paid', 'refunded') NOT NULL DEFAULT 'unpaid',
  `order_status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
  `shipping_address` TEXT NOT NULL,
  `payment_proof` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_status` (`user_id`, `order_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. ORDER ITEMS
CREATE TABLE `order_items` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `product_title_snapshot` VARCHAR(255) NOT NULL,
  `unit_price_snapshot` DECIMAL(12,2) NOT NULL,
  `quantity` INT UNSIGNED NOT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. AFFILIATE PROFILES
CREATE TABLE `affiliate_profiles` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL UNIQUE,
  `affiliate_code` VARCHAR(30) NOT NULL UNIQUE,
  `payment_details` TEXT DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'approved',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. AFFILIATE LINKS
CREATE TABLE `affiliate_links` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `affiliate_profile_id` BIGINT UNSIGNED NOT NULL,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`affiliate_profile_id`) REFERENCES `affiliate_profiles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. AFFILIATE CLICKS
CREATE TABLE `affiliate_clicks` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `affiliate_link_id` BIGINT UNSIGNED NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `referer` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`affiliate_link_id`) REFERENCES `affiliate_links`(`id`) ON DELETE CASCADE,
  INDEX `idx_link_created` (`affiliate_link_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. CONVERSIONS
CREATE TABLE `conversions` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `affiliate_link_id` BIGINT UNSIGNED NOT NULL,
  `order_id` BIGINT UNSIGNED NOT NULL UNIQUE,
  `order_total` DECIMAL(12,2) NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`affiliate_link_id`) REFERENCES `affiliate_links`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. AFFILIATE COMMISSIONS
CREATE TABLE `affiliate_commissions` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `conversion_id` BIGINT UNSIGNED NOT NULL UNIQUE,
  `affiliate_profile_id` BIGINT UNSIGNED NOT NULL,
  `commission_amount` DECIMAL(12,2) NOT NULL,
  `status` ENUM('pending', 'paid', 'cancelled') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`conversion_id`) REFERENCES `conversions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`affiliate_profile_id`) REFERENCES `affiliate_profiles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. RECHARGES
CREATE TABLE `recharges` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `reference_no` VARCHAR(80) NOT NULL UNIQUE,
  `amount` DECIMAL(12,2) NOT NULL,
  `method` VARCHAR(255) NOT NULL,
  `payment_proof` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending', 'completed', 'rejected') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. WITHDRAWALS
CREATE TABLE `withdrawals` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `reference_no` VARCHAR(80) NOT NULL UNIQUE,
  `amount` DECIMAL(12,2) NOT NULL,
  `method` VARCHAR(255) NOT NULL,
  `account_info` TEXT NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `rejection_reason` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17B. WITHDRAWAL REJECTION TEMPLATES
CREATE TABLE IF NOT EXISTS `withdrawal_rejection_templates` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL,
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. NOTIFICATIONS
CREATE TABLE `notifications` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_read` (`user_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. AUDIT LOGS
CREATE TABLE `audit_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `details` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEED FOUNDATION DATA
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`) VALUES
(1, 'Electronics & Gadgets', 'electronics-gadgets', 'bolt'),
(2, 'Fashion & Apparel', 'fashion-apparel', 'shopping-bag'),
(3, 'Home & Lifestyle', 'home-lifestyle', 'home'),
(4, 'Beauty & Health', 'beauty-health', 'heart');

INSERT INTO `products` (`id`, `category_id`, `title`, `slug`, `sku`, `price`, `discount_price`, `stock_quantity`, `commission_rate`, `short_description`, `full_description`, `main_image`, `status`) VALUES
(1, 1, 'Wireless Noise-Canceling Earbuds Pro', 'wireless-earbuds-pro', 'EAR-PRO-01', 3500.00, 2990.00, 50, 8.00, 'Premium high-fidelity audio with active noise cancellation.', 'Experience deep bass and crystal clear treble with the new Earbuds Pro. Features 24h battery life, IPX5 water resistance, and fast charging.', 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=500&auto=format&fit=crop&q=60', 'active'),
(2, 1, 'Smartwatch Fitness Tracker V2', 'smartwatch-fitness-v2', 'WATCH-V2-02', 4200.00, 3750.00, 30, 10.00, 'Track your heart rate, sleep, steps, and sports activities.', 'High resolution AMOLED display smartwatch with full health monitoring suite, GPS, and custom watch faces.', 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&auto=format&fit=crop&q=60', 'active'),
(3, 2, 'Classic Leather Travel Backpack', 'classic-leather-backpack', 'BAG-LTHR-03', 2800.00, 2400.00, 25, 7.50, 'Handcrafted durable genuine leather backpack with laptop compartment.', 'Stylish and spacious leather backpack designed for everyday urban commute and long weekend trips.', 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500&auto=format&fit=crop&q=60', 'active'),
(4, 3, 'Ergonomic Mesh Office Chair', 'ergonomic-office-chair', 'CHAIR-ERG-04', 8500.00, 7900.00, 15, 5.00, 'Lumbar support breathable mesh office chair for maximum productivity.', 'Designed for long hours of desk work. Features adjustable armrests, tilt mechanism, and lumbar support.', 'https://images.unsplash.com/photo-1580481072645-022f9a6d1270?w=500&auto=format&fit=crop&q=60', 'active');
