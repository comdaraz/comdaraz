<?php
/**
 * Dummy Data Seeder for Local PC Testing
 * Project: Desktop\daraz-affiliate
 */

require_once __DIR__ . '/app/Helpers/functions.php';
load_env(__DIR__ . '/.env');
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Core/Session.php';

use App\Core\Database;

echo "=== SEEDING DUMMY DATABASE FOR LOCAL TESTING ===\n\n";

// Disable foreign key checks for clean re-seed
Database::query("SET FOREIGN_KEY_CHECKS = 0;");
Database::query("TRUNCATE TABLE audit_logs;");
Database::query("TRUNCATE TABLE withdrawals;");
Database::query("TRUNCATE TABLE recharges;");
Database::query("TRUNCATE TABLE affiliate_commissions;");
Database::query("TRUNCATE TABLE conversions;");
Database::query("TRUNCATE TABLE affiliate_clicks;");
Database::query("TRUNCATE TABLE affiliate_links;");
Database::query("TRUNCATE TABLE affiliate_profiles;");
Database::query("TRUNCATE TABLE order_items;");
Database::query("TRUNCATE TABLE orders;");
Database::query("TRUNCATE TABLE cart_items;");
Database::query("TRUNCATE TABLE carts;");
Database::query("TRUNCATE TABLE products;");
Database::query("TRUNCATE TABLE categories;");
Database::query("TRUNCATE TABLE wallet_transactions;");
Database::query("TRUNCATE TABLE wallets;");
Database::query("TRUNCATE TABLE users;");
Database::query("SET FOREIGN_KEY_CHECKS = 1;");

$passHash = password_hash('User123!', PASSWORD_BCRYPT);
$adminHash = password_hash('AdminPass123!', PASSWORD_BCRYPT);

// 1. Users
Database::query("INSERT INTO users (id, name, email, phone, password, role, status) VALUES 
(1, 'System Admin', 'admin@test.com', '01800000000', :adminPass, 'admin', 'active'),
(2, 'Test Customer', 'customer@test.com', '01711111111', :userPass1, 'customer', 'active'),
(3, 'Test Affiliate Marketer', 'affiliate@test.com', '01922222222', :userPass2, 'affiliate', 'active')", [
    'adminPass' => $adminHash,
    'userPass1' => $passHash,
    'userPass2' => $passHash
]);

// 2. Wallets
Database::query("INSERT INTO wallets (id, user_id, balance, pending_balance) VALUES 
(1, 1, '50000.00', '0.00'),
(2, 2, '5000.00', '0.00'),
(3, 3, '1250.00', '450.00')");

// 3. Categories
$categories = [
    ['id' => 1, 'name' => 'Electronics & Gadgets', 'slug' => 'electronics-gadgets', 'icon' => '⚡'],
    ['id' => 2, 'name' => 'Fashion & Lifestyle', 'slug' => 'fashion-lifestyle', 'icon' => '👗'],
    ['id' => 3, 'name' => 'Home & Kitchen Appliances', 'slug' => 'home-kitchen', 'icon' => '🏠'],
    ['id' => 4, 'name' => 'Beauty & Personal Care', 'slug' => 'beauty-care', 'icon' => '✨'],
    ['id' => 5, 'name' => 'Sports & Outdoor', 'slug' => 'sports-outdoor', 'icon' => '⚽']
];

foreach ($categories as $cat) {
    Database::query("INSERT INTO categories (id, name, slug, status, sort_order) VALUES (:id, :name, :slug, 'active', :sort)", [
        'id' => $cat['id'],
        'name' => $cat['name'],
        'slug' => $cat['slug'],
        'sort' => $cat['id']
    ]);
}

// 4. Products
$products = [
    [
        'category_id' => 1,
        'title' => 'Wireless Noise Cancelling Earbuds Pro 5',
        'slug' => 'wireless-noise-cancelling-earbuds-pro-5',
        'sku' => 'EAR-PRO5-BLK',
        'price' => '2500.00',
        'discount_price' => '1999.00',
        'stock_quantity' => 100,
        'commission_rate' => '10.00',
        'main_image' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600&auto=format&fit=crop&q=80',
        'short_description' => 'Premium active noise cancellation Bluetooth 5.3 earbuds with 30-hour battery life.',
        'full_description' => 'Experience crystal clear audio with high-definition drivers and intelligent active noise cancellation. Built-in dual microphones for crisp voice calls and ergonomic IPX5 water-resistant design.'
    ],
    [
        'category_id' => 1,
        'title' => 'Ultra HD Smart Fitness Watch Series 8',
        'slug' => 'ultra-hd-smart-fitness-watch-series-8',
        'sku' => 'SW-SERIES8-SIL',
        'price' => '3800.00',
        'discount_price' => '2950.00',
        'stock_quantity' => 75,
        'commission_rate' => '12.00',
        'main_image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop&q=80',
        'short_description' => 'AMOLED display smartwatch with heart rate, SpO2, and sleep tracking.',
        'full_description' => 'Track your daily workouts, monitor heart rate 24/7, and receive notifications directly on your wrist with a vibrant AMOLED curved glass display.'
    ],
    [
        'category_id' => 2,
        'title' => 'Classic Leather Minimalist Slim Wallet',
        'slug' => 'classic-leather-minimalist-slim-wallet',
        'sku' => 'WAL-LEATH-BRN',
        'price' => '1200.00',
        'discount_price' => '850.00',
        'stock_quantity' => 150,
        'commission_rate' => '15.00',
        'main_image' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=600&auto=format&fit=crop&q=80',
        'short_description' => 'Handcrafted genuine leather bifold wallet with RFID blocking protection.',
        'full_description' => 'Made from 100% full-grain genuine leather. Features multiple card slots, ID window, and built-in RFID blocking technology to keep your cards safe.'
    ],
    [
        'category_id' => 3,
        'title' => 'Automatic Espresso & Coffee Maker 15-Bar',
        'slug' => 'automatic-espresso-coffee-maker-15-bar',
        'sku' => 'CF-ESP-15BAR',
        'price' => '8500.00',
        'discount_price' => '6990.00',
        'stock_quantity' => 30,
        'commission_rate' => '8.00',
        'main_image' => 'https://images.unsplash.com/photo-1517668808822-9ebe02f2a6e8?w=600&auto=format&fit=crop&q=80',
        'short_description' => 'Professional Italian 15-bar pump espresso machine with milk frother.',
        'full_description' => 'Enjoy coffeehouse-quality cappuccinos and lattes at home. Powerful 15-bar pressure extraction system with adjustable steam wand.'
    ],
    [
        'category_id' => 4,
        'title' => 'Organic Vitamin C Radiant Skin Serum 30ml',
        'slug' => 'organic-vitamin-c-radiant-skin-serum-30ml',
        'sku' => 'SKIN-VITC-30ML',
        'price' => '1500.00',
        'discount_price' => '1150.00',
        'stock_quantity' => 200,
        'commission_rate' => '20.00',
        'main_image' => 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=600&auto=format&fit=crop&q=80',
        'short_description' => 'Pure Vitamin C + Hyaluronic Acid facial serum for glowing, youthful skin.',
        'full_description' => 'Formulated with botanical extracts to reduce dark spots, boost collagen, and protect against environmental skin stressors.'
    ],
    [
        'category_id' => 5,
        'title' => 'Pro Athletic Running Shoes Cushion Max',
        'slug' => 'pro-athletic-running-shoes-cushion-max',
        'sku' => 'SHO-RUN-MAX-42',
        'price' => '4500.00',
        'discount_price' => '3600.00',
        'stock_quantity' => 60,
        'commission_rate' => '10.00',
        'main_image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&auto=format&fit=crop&q=80',
        'short_description' => 'Lightweight breathable mesh running sneakers with impact-absorbing soles.',
        'full_description' => 'Designed for long-distance runners and everyday training. Breathable flyknit upper with high-traction rubber outsole.'
    ]
];

foreach ($products as $p) {
    Database::query(
        "INSERT INTO products (category_id, title, slug, sku, price, discount_price, stock_quantity, commission_rate, short_description, full_description, main_image, status) 
         VALUES (:category_id, :title, :slug, :sku, :price, :discount_price, :stock_quantity, :commission_rate, :short_description, :full_description, :main_image, 'active')",
        $p
    );
}

// 5. Affiliate Profile
Database::query("INSERT INTO affiliate_profiles (id, user_id, affiliate_code, status) VALUES (1, 3, 'AFF-DEMO123', 'approved')");
Database::query("INSERT INTO affiliate_links (id, affiliate_profile_id, product_id, slug) VALUES (1, 1, 1, 'earbuds-special-deal')");

// 6. Sample Orders & Commissions
Database::query("INSERT INTO orders (id, order_number, user_id, total_amount, payment_status, order_status, shipping_address) VALUES 
(101, 'ORD-20260921-A101', 2, '1999.00', 'paid', 'delivered', 'House 42, Road 7, Dhanmondi, Dhaka'),
(102, 'ORD-20260921-A102', 2, '2950.00', 'paid', 'processing', 'Flat B3, Sector 4, Uttara, Dhaka')");

Database::query("INSERT INTO order_items (order_id, product_id, product_title_snapshot, unit_price_snapshot, quantity, subtotal) VALUES 
(101, 1, 'Wireless Noise Cancelling Earbuds Pro 5', '1999.00', 1, '1999.00'),
(102, 2, 'Ultra HD Smart Fitness Watch Series 8', '2950.00', 1, '2950.00')");

Database::query("INSERT INTO conversions (id, affiliate_link_id, order_id, order_total, status) VALUES (1, 1, 101, '1999.00', 'approved')");
Database::query("INSERT INTO affiliate_commissions (id, conversion_id, affiliate_profile_id, commission_amount, status) VALUES (1, 1, 1, '199.90', 'paid')");

// 7. Sample Wallet Transactions
Database::query("INSERT INTO wallet_transactions (wallet_id, reference_id, type, amount, balance_before, balance_after, description, status) VALUES 
(2, 'REC-BKASH1001', 'recharge', '5000.00', '0.00', '5000.00', 'Initial Wallet Recharge via bKash', 'completed'),
(3, 'COM-1-1', 'commission_payout', '199.90', '1050.10', '1250.00', 'Affiliate Commission for Order #ORD-20260921-A101', 'completed')");

// Audit Log
audit_log("system_dummy_seeded", "Demo dummy database seeded successfully for local testing.");

echo "SUCCESS: Dummy database seeded with users, categories, products, orders, and commissions!\n";
