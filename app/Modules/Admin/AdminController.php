<?php

namespace App\Modules\Admin;

use App\Core\Database;
use App\Core\Session;
use App\Core\CSRF;
use App\Core\View;
use App\Core\Cache;
use App\Modules\Wallet\WalletController;

class AdminController {
    public function __construct() {
        requireAdmin();
    }

    /**
     * Admin Dashboard Overview
     */
    public function dashboard(): void {
        $totalUsers = (int)(Database::fetchOne("SELECT COUNT(*) as cnt FROM users")['cnt'] ?? 0);
        $activeUsers = (int)(Database::fetchOne("SELECT COUNT(*) as cnt FROM users WHERE status = 'active'")['cnt'] ?? 0);
        $totalOrders = (int)(Database::fetchOne("SELECT COUNT(*) as cnt FROM orders")['cnt'] ?? 0);
        $pendingOrders = (int)(Database::fetchOne("SELECT COUNT(*) as cnt FROM orders WHERE order_status = 'pending'")['cnt'] ?? 0);
        $totalProducts = (int)(Database::fetchOne("SELECT COUNT(*) as cnt FROM products")['cnt'] ?? 0);
        $pendingRecharges = (int)(Database::fetchOne("SELECT COUNT(*) as cnt FROM recharges WHERE status = 'pending'")['cnt'] ?? 0);
        $pendingWithdrawals = (int)(Database::fetchOne("SELECT COUNT(*) as cnt FROM withdrawals WHERE status = 'pending'")['cnt'] ?? 0);
        
        $commRes = Database::fetchOne("SELECT SUM(commission_amount) as total FROM affiliate_commissions WHERE status = 'paid'");
        $totalCommission = format_money($commRes['total'] ?? '0.00');

        $recentOrders = Database::fetchAll(
            "SELECT o.id, o.order_number, o.total_amount, o.order_status, o.created_at, u.name as customer_name 
             FROM orders o 
             JOIN users u ON o.user_id = u.id 
             ORDER BY o.id DESC LIMIT 5"
        );

        View::render('admin/dashboard', [
            'title' => 'Admin Dashboard - Management Console',
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'totalOrders' => $totalOrders,
            'pendingOrders' => $pendingOrders,
            'totalProducts' => $totalProducts,
            'pendingRecharges' => $pendingRecharges,
            'pendingWithdrawals' => $pendingWithdrawals,
            'totalCommission' => $totalCommission,
            'recentOrders' => $recentOrders,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ], 'layouts/admin');
    }

    /**
     * User Management
     */
    public function users(): void {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $search = trim($_GET['q'] ?? '');

        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $cleanSearch = ltrim($search, '#');
            if (is_numeric($cleanSearch)) {
                $where[] = "(u.id = :q_id OR u.name LIKE :q_name OR u.email LIKE :q_email OR u.phone LIKE :q_phone)";
                $params['q_id'] = (int)$cleanSearch;
            } else {
                $where[] = "(u.name LIKE :q_name OR u.email LIKE :q_email OR u.phone LIKE :q_phone)";
            }
            $params['q_name'] = '%' . $search . '%';
            $params['q_email'] = '%' . $search . '%';
            $params['q_phone'] = '%' . $search . '%';
        }

        $whereSql = implode(' AND ', $where);

        $totalUsers = (int)(Database::fetchOne("SELECT COUNT(*) as cnt FROM users u WHERE {$whereSql}", $params)['cnt'] ?? 0);
        $totalPages = (int)ceil($totalUsers / $limit);

        $users = Database::fetchAll(
            "SELECT u.id, u.name, u.email, u.phone, u.role, u.status, u.credit_current, u.credit_max, u.otp_code, u.is_verified, u.created_at, w.balance 
             FROM users u 
             LEFT JOIN wallets w ON u.id = w.user_id 
             WHERE {$whereSql} 
             ORDER BY u.id DESC 
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        View::render('admin/users', [
            'title' => 'User Management - Admin Console',
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ], 'layouts/admin');
    }

    public function updateUserStatus(): void {
        CSRF::verifyOrDie();
        
        $targetUserId = (int)($_POST['user_id'] ?? 0);
        $newStatus = $_POST['status'] ?? '';
        $newRole = $_POST['role'] ?? '';

        $adminId = (int)Session::get('user_id');

        if (!in_array($newStatus, ['active', 'inactive', 'suspended'], true) || !in_array($newRole, ['customer', 'affiliate', 'vendor', 'admin'], true)) {
            Session::setFlash('error', 'Invalid status or role selected.');
            redirect(url('/admin/users'));
        }

        // Security Guard: Prevent self-deactivation or self-demotion
        if ($targetUserId === $adminId) {
            if ($newStatus !== 'active' || $newRole !== 'admin') {
                Session::setFlash('error', 'Security Violation: You cannot deactivate or demote your own admin account.');
                redirect(url('/admin/users'));
            }
        }

        // Security Guard: Prevent removing the last active admin
        if ($newRole !== 'admin' || $newStatus !== 'active') {
            $adminCount = (int)(Database::fetchOne("SELECT COUNT(*) as cnt FROM users WHERE role = 'admin' AND status = 'active'")['cnt'] ?? 0);
            $targetUser = Database::fetchOne("SELECT role, status FROM users WHERE id = :id", ['id' => $targetUserId]);
            if ($targetUser && $targetUser['role'] === 'admin' && $adminCount <= 1) {
                Session::setFlash('error', 'Security Violation: Cannot demote or deactivate the system\'s last remaining active admin.');
                redirect(url('/admin/users'));
            }
        }

        Database::query("UPDATE users SET status = :status, role = :role WHERE id = :id", [
            'status' => $newStatus,
            'role' => $newRole,
            'id' => $targetUserId
        ]);

        audit_log("admin_user_update", "Updated User #{$targetUserId} status to {$newStatus}, role to {$newRole}");

        Session::setFlash('success', 'User updated successfully.');
        redirect(url('/admin/users'));
    }

    public function updateUserCredits(): void {
        CSRF::verifyOrDie();
        $targetUserId = (int)($_POST['user_id'] ?? 0);
        $creditCurrent = max(0, (int)($_POST['credit_current'] ?? 0));
        $creditMax = max(0, (int)($_POST['credit_max'] ?? 0));

        Database::query("UPDATE users SET credit_current = :cc, credit_max = :cm WHERE id = :id", [
            'cc' => $creditCurrent,
            'cm' => $creditMax,
            'id' => $targetUserId
        ]);

        audit_log("admin_update_credit", "Admin updated User #{$targetUserId} credits to {$creditCurrent} / {$creditMax}");
        Session::setFlash('success', "Updated credits for User #{$targetUserId} to {$creditCurrent} / {$creditMax}.");
        redirect(url('/admin/users'));
    }

    public function updateUserPassword(): void {
        CSRF::verifyOrDie();
        $targetUserId = (int)($_POST['user_id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';

        if (empty($newPassword) || strlen($newPassword) < 6) {
            Session::setFlash('error', 'New password must be at least 6 characters long.');
            redirect(url('/admin/users'));
        }

        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
        Database::query("UPDATE users SET password = :hash, failed_login_attempts = 0, lockout_until = NULL WHERE id = :id", [
            'hash' => $hashed,
            'id' => $targetUserId
        ]);

        audit_log("admin_change_user_password", "Admin changed password for User #{$targetUserId}");
        Session::setFlash('success', "Password for User #{$targetUserId} successfully updated.");
        redirect(url('/admin/users'));
    }

    public function generateUserOTP(): void {
        CSRF::verifyOrDie();
        $targetUserId = (int)($_POST['user_id'] ?? 0);
        $otp = (string)rand(100000, 999999);

        Database::query("UPDATE users SET otp_code = :otp, is_verified = 0 WHERE id = :id", [
            'otp' => $otp,
            'id' => $targetUserId
        ]);

        audit_log("admin_generate_otp", "Admin generated OTP {$otp} for User #{$targetUserId}");
        Session::setFlash('success', "One-Time OTP [{$otp}] generated for User #{$targetUserId}.");
        redirect(url('/admin/users'));
    }

    public function manageUserBalance(): void {
        CSRF::verifyOrDie();
        $targetUserId = (int)($_POST['user_id'] ?? 0);
        $action = $_POST['action'] ?? 'add'; // 'add' or 'deduct'
        $amountInput = trim($_POST['amount'] ?? '0.00');
        $amount = format_money($amountInput);

        if (money_comp($amount, "0.00") <= 0) {
            Session::setFlash('error', 'Please enter a valid amount greater than 0.');
            redirect(url('/admin/users'));
        }

        $targetUser = Database::fetchOne("SELECT id, name FROM users WHERE id = :id LIMIT 1", ['id' => $targetUserId]);
        if (!$targetUser) {
            Session::setFlash('error', 'User not found.');
            redirect(url('/admin/users'));
        }

        Database::beginTransaction();
        try {
            $wallet = Database::fetchOne("SELECT id, balance FROM wallets WHERE user_id = :uid FOR UPDATE", ['uid' => $targetUserId]);
            if (!$wallet) {
                Database::query("INSERT INTO wallets (user_id, balance, pending_balance, currency, status) VALUES (:uid, 0.00, 0.00, 'BDT', 'active')", ['uid' => $targetUserId]);
                $walletId = (int)Database::lastInsertId();
                $balanceBefore = "0.00";
            } else {
                $walletId = (int)$wallet['id'];
                $balanceBefore = format_money($wallet['balance']);
            }

            if ($action === 'deduct') {
                if (money_comp($balanceBefore, $amount) < 0) {
                    throw new \Exception("Insufficient wallet balance to deduct ৳{$amount}. Current balance: ৳{$balanceBefore}");
                }
                $balanceAfter = money_sub($balanceBefore, $amount);
                $txType = 'withdrawal';
                $refId = 'ADM-DED-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
                $description = "Admin Manual Balance Deduction from User #{$targetUserId} ({$targetUser['name']})";
                $logAction = "admin_manual_deduction";
                $flashMsg = "Successfully deducted ৳{$amount} from {$targetUser['name']} (#{$targetUserId}). New Balance: ৳{$balanceAfter}";
            } else {
                $balanceAfter = money_add($balanceBefore, $amount);
                $txType = 'recharge';
                $refId = 'ADM-DEP-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
                $description = "Admin Manual Wallet Deposit for User #{$targetUserId} ({$targetUser['name']})";
                $logAction = "admin_manual_deposit";
                $flashMsg = "Successfully deposited ৳{$amount} to {$targetUser['name']} (#{$targetUserId}). New Balance: ৳{$balanceAfter}";

                $rechargeRef = 'REC-ADM-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
                Database::query(
                    "INSERT INTO recharges (user_id, reference_no, amount, method, status) 
                     VALUES (:uid, :ref, :amt, 'Admin Direct Deposit', 'completed')",
                    ['uid' => $targetUserId, 'ref' => $rechargeRef, 'amt' => $amount]
                );
            }

            Database::query("UPDATE wallets SET balance = :bal WHERE id = :wid", ['bal' => $balanceAfter, 'wid' => $walletId]);

            Database::query(
                "INSERT INTO wallet_transactions (wallet_id, reference_id, type, amount, balance_before, balance_after, description, status) 
                 VALUES (:wid, :ref, :type, :amt, :bb, :ba, :desc, 'completed')",
                [
                    'wid' => $walletId,
                    'ref' => $refId,
                    'type' => $txType,
                    'amt' => $amount,
                    'bb' => $balanceBefore,
                    'ba' => $balanceAfter,
                    'desc' => $description
                ]
            );

            audit_log($logAction, "Admin adjusted User #{$targetUserId} ({$targetUser['name']}) wallet: {$action} ৳{$amount}. New balance: ৳{$balanceAfter}");
            Database::commit();

            Session::setFlash('success', $flashMsg);
        } catch (\Throwable $e) {
            Database::rollBack();
            Session::setFlash('error', 'Failed to adjust balance: ' . $e->getMessage());
        }

        redirect(url('/admin/users'));
    }

    public function updateUserInfo(): void {
        CSRF::verifyOrDie();
        $targetUserId = (int)($_POST['user_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $role = $_POST['role'] ?? 'customer';
        $status = $_POST['status'] ?? 'active';

        $adminId = (int)Session::get('user_id');

        if (empty($name)) {
            Session::setFlash('error', 'Customer name is required.');
            redirect(url('/admin/users'));
        }

        // Email & Phone Uniqueness Check excluding current user
        if (!empty($email)) {
            $existingEmail = Database::fetchOne("SELECT id FROM users WHERE email = :email AND id != :uid LIMIT 1", ['email' => $email, 'uid' => $targetUserId]);
            if ($existingEmail) {
                Session::setFlash('error', 'Email address is already in use by another account.');
                redirect(url('/admin/users'));
            }
        }

        if (!empty($phone)) {
            $existingPhone = Database::fetchOne("SELECT id FROM users WHERE phone = :phone AND id != :uid LIMIT 1", ['phone' => $phone, 'uid' => $targetUserId]);
            if ($existingPhone) {
                Session::setFlash('error', 'Phone number is already in use by another account.');
                redirect(url('/admin/users'));
            }
        }

        // Security Guards: Prevent self demotion or deactivation
        if ($targetUserId === $adminId) {
            if ($status !== 'active' || $role !== 'admin') {
                Session::setFlash('error', 'Security Violation: You cannot deactivate or demote your own admin account.');
                redirect(url('/admin/users'));
            }
        }

        // Security Guard: Prevent removing last remaining admin
        if ($role !== 'admin' || $status !== 'active') {
            $adminCount = (int)(Database::fetchOne("SELECT COUNT(*) as cnt FROM users WHERE role = 'admin' AND status = 'active'")['cnt'] ?? 0);
            $targetUser = Database::fetchOne("SELECT role, status FROM users WHERE id = :id", ['id' => $targetUserId]);
            if ($targetUser && $targetUser['role'] === 'admin' && $adminCount <= 1) {
                Session::setFlash('error', "Security Violation: Cannot demote or deactivate the system's last remaining active admin.");
                redirect(url('/admin/users'));
            }
        }

        Database::query("UPDATE users SET name = :name, email = :email, phone = :phone, role = :role, status = :status WHERE id = :id", [
            'name' => $name,
            'email' => !empty($email) ? $email : null,
            'phone' => !empty($phone) ? $phone : null,
            'role' => $role,
            'status' => $status,
            'id' => $targetUserId
        ]);

        audit_log("admin_edit_user_info", "Admin updated details for User #{$targetUserId}: {$name} ({$role}, {$status})");
        Session::setFlash('success', "Updated user details for #{$targetUserId} ({$name}) successfully.");
        redirect(url('/admin/users'));
    }

    public function deleteUser(): void {
        CSRF::verifyOrDie();
        $targetUserId = (int)($_POST['user_id'] ?? 0);
        $adminId = (int)Session::get('user_id');

        if ($targetUserId === $adminId) {
            Session::setFlash('error', 'Security Violation: You cannot delete your own admin account while logged in.');
            redirect(url('/admin/users'));
        }

        $targetUser = Database::fetchOne("SELECT id, name, role, status FROM users WHERE id = :id LIMIT 1", ['id' => $targetUserId]);
        if (!$targetUser) {
            Session::setFlash('error', 'User not found.');
            redirect(url('/admin/users'));
        }

        if ($targetUser['role'] === 'admin') {
            $adminCount = (int)(Database::fetchOne("SELECT COUNT(*) as cnt FROM users WHERE role = 'admin' AND status = 'active'")['cnt'] ?? 0);
            if ($adminCount <= 1) {
                Session::setFlash('error', "Security Violation: Cannot delete the system's last remaining active admin.");
                redirect(url('/admin/users'));
            }
        }

        Database::beginTransaction();
        try {
            // Delete dependent records gracefully
            Database::query("DELETE FROM carts WHERE user_id = :uid", ['uid' => $targetUserId]);
            Database::query("DELETE FROM recharges WHERE user_id = :uid", ['uid' => $targetUserId]);
            Database::query("DELETE FROM withdrawals WHERE user_id = :uid", ['uid' => $targetUserId]);
            Database::query("DELETE FROM affiliate_profiles WHERE user_id = :uid", ['uid' => $targetUserId]);
            
            $wallet = Database::fetchOne("SELECT id FROM wallets WHERE user_id = :uid LIMIT 1", ['uid' => $targetUserId]);
            if ($wallet) {
                Database::query("DELETE FROM wallet_transactions WHERE wallet_id = :wid", ['wid' => $wallet['id']]);
                Database::query("DELETE FROM wallets WHERE id = :wid", ['wid' => $wallet['id']]);
            }

            Database::query("DELETE FROM users WHERE id = :id", ['id' => $targetUserId]);

            audit_log("admin_delete_user", "Admin deleted User #{$targetUserId} ({$targetUser['name']})");
            Database::commit();

            Session::setFlash('success', "User #{$targetUserId} ({$targetUser['name']}) was permanently deleted.");
        } catch (\Throwable $e) {
            Database::rollBack();
            Session::setFlash('error', 'Failed to delete user: ' . $e->getMessage());
        }

        redirect(url('/admin/users'));
    }

    /**
     * Product Management
     */
    public function products(): void {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $search = trim($_GET['q'] ?? '');

        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $cleanSearch = ltrim($search, '#');
            if (is_numeric($cleanSearch)) {
                $where[] = "(p.id = :q_id OR p.title LIKE :q_title OR p.sku LIKE :q_sku)";
                $params['q_id'] = (int)$cleanSearch;
            } else {
                $where[] = "(p.title LIKE :q_title OR p.sku LIKE :q_sku)";
            }
            $params['q_title'] = '%' . $search . '%';
            $params['q_sku'] = '%' . $search . '%';
        }

        $whereSql = implode(' AND ', $where);

        $totalProducts = (int)(Database::fetchOne("SELECT COUNT(*) as cnt FROM products p WHERE {$whereSql}", $params)['cnt'] ?? 0);
        $totalPages = (int)ceil($totalProducts / $limit);

        $products = Database::fetchAll(
            "SELECT p.id, p.title, p.sku, p.price, p.discount_price, p.stock_quantity, p.commission_rate, p.status, c.name as category_name 
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE {$whereSql} 
             ORDER BY p.id DESC 
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        View::render('admin/products', [
            'title' => 'Product Management - Admin Console',
            'products' => $products,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ], 'layouts/admin');
    }

    public function createProductForm(): void {
        $categories = Database::fetchAll("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC");
        View::render('admin/product_form', [
            'title' => 'Add New Product - Admin Console',
            'categories' => $categories,
            'product' => null,
            'error' => Session::getFlash('error')
        ], 'layouts/admin');
    }

    public function editProductForm(string $id): void {
        $productId = (int)$id;
        $product = Database::fetchOne(
            "SELECT id, category_id, title, slug, sku, price, discount_price, stock_quantity, commission_rate, short_description, full_description, main_image, status 
             FROM products WHERE id = :id LIMIT 1",
            ['id' => $productId]
        );
        if (!$product) {
            Session::setFlash('error', 'Product not found.');
            redirect(url('/admin/products'));
        }
        $categories = Database::fetchAll("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC");
        View::render('admin/product_form', [
            'title' => "Edit Product #{$productId} - Admin Console",
            'categories' => $categories,
            'product' => $product,
            'error' => Session::getFlash('error')
        ], 'layouts/admin');
    }

    public function saveProduct(): void {
        CSRF::verifyOrDie();

        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $sku = trim($_POST['sku'] ?? '');
        $price = format_money($_POST['price'] ?? '0.00');
        $discountPrice = !empty($_POST['discount_price']) ? format_money($_POST['discount_price']) : null;
        $stockQuantity = max(0, (int)($_POST['stock_quantity'] ?? 0));
        $commissionRate = format_money($_POST['commission_rate'] ?? '5.00');
        $shortDesc = trim($_POST['short_description'] ?? '');
        $fullDesc = trim($_POST['full_description'] ?? '');
        $mainImage = trim($_POST['main_image'] ?? '');
        $uploadedFileUrl = upload_image_file('image_file', 'products');
        if ($uploadedFileUrl) {
            $mainImage = $uploadedFileUrl;
        }
        $status = $_POST['status'] ?? 'active';
        $sizeOptions = trim($_POST['size_options'] ?? '');
        $colorOptions = trim($_POST['color_options'] ?? '');

        if (empty($title) || empty($sku) || $categoryId <= 0) {
            Session::setFlash('error', 'Title, SKU, and Category are required.');
            redirect(url('/admin/products/create'));
        }

        if (money_comp($price, "0.01") < 0) {
            Session::setFlash('error', 'Product price must be a valid positive amount.');
            redirect(url('/admin/products/create'));
        }

        if (money_comp($commissionRate, "0.00") < 0 || money_comp($commissionRate, "100.00") > 0) {
            Session::setFlash('error', 'Commission rate must be between 0% and 100%.');
            redirect(url('/admin/products/create'));
        }

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-')) . '-' . substr(md5($sku), 0, 4);

        if ($id > 0) {
            Database::query(
                "UPDATE products SET category_id = :cat, title = :title, slug = :slug, sku = :sku, price = :price, 
                        discount_price = :dprice, stock_quantity = :stock, commission_rate = :comm, 
                        short_description = :short, full_description = :full, main_image = :img, status = :status,
                        size_options = :size, color_options = :color 
                 WHERE id = :id",
                [
                    'cat' => $categoryId,
                    'title' => $title,
                    'slug' => $slug,
                    'sku' => $sku,
                    'price' => $price,
                    'dprice' => $discountPrice,
                    'stock' => $stockQuantity,
                    'comm' => $commissionRate,
                    'short' => $shortDesc,
                    'full' => $fullDesc,
                    'img' => $mainImage,
                    'status' => $status,
                    'size' => $sizeOptions ?: null,
                    'color' => $colorOptions ?: null,
                    'id' => $id
                ]
            );
            audit_log("admin_product_update", "Updated Product #{$id}: {$title}");
            Session::setFlash('success', 'Product updated successfully.');
        } else {
            Database::query(
                "INSERT INTO products (category_id, title, slug, sku, price, discount_price, stock_quantity, commission_rate, short_description, full_description, main_image, status, size_options, color_options)
                 VALUES (:cat, :title, :slug, :sku, :price, :dprice, :stock, :comm, :short, :full, :img, :status, :size, :color)",
                [
                    'cat' => $categoryId,
                    'title' => $title,
                    'slug' => $slug,
                    'sku' => $sku,
                    'price' => $price,
                    'dprice' => $discountPrice,
                    'stock' => $stockQuantity,
                    'comm' => $commissionRate,
                    'short' => $shortDesc,
                    'full' => $fullDesc,
                    'img' => $mainImage,
                    'status' => $status,
                    'size' => $sizeOptions ?: null,
                    'color' => $colorOptions ?: null
                ]
            );
            $newId = Database::lastInsertId();
            audit_log("admin_product_create", "Created Product #{$newId}: {$title}");
            Session::setFlash('success', 'Product created successfully.');
        }

        Cache::clear();
        redirect(url('/admin/products'));
    }

    /**
     * Category Management
     */
    public function categories(): void {
        $categories = Database::fetchAll("SELECT id, name, slug, status, sort_order, created_at FROM categories ORDER BY sort_order ASC");
        View::render('admin/categories', [
            'title' => 'Category Management - Admin Console',
            'categories' => $categories,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ], 'layouts/admin');
    }

    public function saveCategory(): void {
        CSRF::verifyOrDie();
        $name = trim($_POST['name'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if (empty($name)) {
            Session::setFlash('error', 'Category name is required.');
            redirect(url('/admin/categories'));
        }

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        Database::query("INSERT INTO categories (name, slug, status) VALUES (:name, :slug, :status)", [
            'name' => $name,
            'slug' => $slug,
            'status' => $status
        ]);

        audit_log("admin_category_create", "Created Category: {$name}");
        Cache::clear();
        Session::setFlash('success', 'Category created successfully.');
        redirect(url('/admin/categories'));
    }

    /**
     * Order Management
     */
    public function orders(): void {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $statusFilter = $_GET['status'] ?? '';

        $where = ["1=1"];
        $params = [];

        if (!empty($statusFilter)) {
            $where[] = "order_status = :status";
            $params['status'] = $statusFilter;
        }

        $whereSql = implode(' AND ', $where);

        $totalOrders = (int)(Database::fetchOne("SELECT COUNT(*) as cnt FROM orders WHERE {$whereSql}", $params)['cnt'] ?? 0);
        $totalPages = (int)ceil($totalOrders / $limit);

        $orders = Database::fetchAll(
            "SELECT o.id, o.order_number, o.total_amount, 
                    o.payment_status, o.order_status, o.created_at, o.payment_proof,
                    u.name as customer_name,
                    u.phone as customer_phone
             FROM orders o 
             JOIN users u ON o.user_id = u.id 
             WHERE {$whereSql} 
             ORDER BY o.id DESC 
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        foreach ($orders as &$o) {
            $o['payment_method'] = 'Wallet';
            if ($o['payment_status'] === 'paid') {
                $o['paid_amount'] = $o['total_amount'];
                $o['due_amount'] = '0.00';
            } else {
                $o['paid_amount'] = '0.00';
                $o['due_amount'] = $o['total_amount'];
            }
        }
        unset($o);

        View::render('admin/orders', [
            'title' => 'Order Management - Admin Console',
            'orders' => $orders,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'statusFilter' => $statusFilter,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ], 'layouts/admin');
    }

    public function showOrder(string $id): void {
        $orderId = (int)$id;
        $order = Database::fetchOne(
            "SELECT o.id, o.order_number, o.user_id, o.total_amount, o.payment_status, o.order_status, o.shipping_address, o.payment_proof, o.created_at, u.email, u.name as reg_name, u.phone as reg_phone, u.name as customer_name, u.phone as customer_phone 
             FROM orders o 
             JOIN users u ON o.user_id = u.id 
             WHERE o.id = :id LIMIT 1",
            ['id' => $orderId]
        );

        if ($order) {
            $order['payment_method'] = 'Wallet';
            $order['payment_type'] = 'full';
            if ($order['payment_status'] === 'paid') {
                $order['paid_amount'] = $order['total_amount'];
                $order['due_amount'] = '0.00';
            } else {
                $order['paid_amount'] = '0.00';
                $order['due_amount'] = $order['total_amount'];
            }
        }

        if (!$order) {
            Session::setFlash('error', 'Order not found.');
            redirect(url('/admin/orders'));
        }

        $items = Database::fetchAll(
            "SELECT product_title_snapshot, unit_price_snapshot, quantity, subtotal, selected_size, selected_color 
             FROM order_items WHERE order_id = :oid",
            ['oid' => $orderId]
        );

        View::render('admin/order_detail', [
            'title' => "Order #{$order['order_number']} - Admin Console",
            'order' => $order,
            'items' => $items,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ], 'layouts/admin');
    }

    public function updateOrderStatus(): void {
        CSRF::verifyOrDie();
        $orderId = (int)($_POST['order_id'] ?? 0);
        $newStatus = $_POST['order_status'] ?? '';

        $allowedStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($newStatus, $allowedStatuses, true)) {
            Session::setFlash('error', 'Invalid order status selected.');
            redirect(url('/admin/orders'));
        }

        $order = Database::fetchOne("SELECT order_status FROM orders WHERE id = :id LIMIT 1", ['id' => $orderId]);
        if (!$order) {
            Session::setFlash('error', 'Order not found.');
            redirect(url('/admin/orders'));
        }

        $currentStatus = $order['order_status'];

        $validTransitions = [
            'pending' => ['pending', 'processing', 'cancelled'],
            'processing' => ['processing', 'shipped', 'cancelled'],
            'shipped' => ['shipped', 'delivered'],
            'delivered' => ['delivered'],
            'cancelled' => ['cancelled']
        ];

        if (!isset($validTransitions[$currentStatus]) || !in_array($newStatus, $validTransitions[$currentStatus], true)) {
            Session::setFlash('error', "Invalid order status transition from '{$currentStatus}' to '{$newStatus}'.");
            redirect(url('/admin/orders/' . $orderId));
        }

        Database::query("UPDATE orders SET order_status = :status WHERE id = :id", [
            'status' => $newStatus,
            'id' => $orderId
        ]);

        audit_log("admin_order_status_update", "Updated Order #{$orderId} status from {$currentStatus} to {$newStatus}");
        
        Session::setFlash('success', 'Order status updated successfully.');
        redirect(url('/admin/orders/' . $orderId));
    }

    public function updateOrderFinancials(): void {
        CSRF::verifyOrDie();
        $orderId = (int)($_POST['order_id'] ?? 0);
        
        $order = Database::fetchOne("SELECT id, order_number FROM orders WHERE id = :id LIMIT 1", ['id' => $orderId]);
        if (!$order) {
            Session::setFlash('error', 'Order not found.');
            redirect(url('/admin/orders'));
        }

        $customerName = trim($_POST['customer_name'] ?? '');
        $customerPhone = trim($_POST['customer_phone'] ?? '');
        $shippingAddress = trim($_POST['shipping_address'] ?? '');
        $totalAmount = format_money($_POST['total_amount'] ?? '0.00');
        $paidAmount = format_money($_POST['paid_amount'] ?? '0.00');

        if (money_comp($paidAmount, $totalAmount) > 0) {
            $paidAmount = $totalAmount;
        }

        $dueAmount = money_sub($totalAmount, $paidAmount);
        $paymentMethod = trim($_POST['payment_method'] ?? 'COD');
        $paymentType = trim($_POST['payment_type'] ?? 'full');
        $paymentStatus = trim($_POST['payment_status'] ?? 'unpaid');
        $orderStatus = trim($_POST['order_status'] ?? 'pending');

        $allowedPaymentStatuses = ['unpaid', 'partially_paid', 'paid', 'refunded'];
        $allowedOrderStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

        if (!in_array($paymentStatus, $allowedPaymentStatuses, true) || !in_array($orderStatus, $allowedOrderStatuses, true)) {
            Session::setFlash('error', 'Invalid payment status or order status selected.');
            redirect(url('/admin/orders/' . $orderId));
        }

        Database::query(
            "UPDATE orders SET customer_name = :cname, customer_phone = :cphone, shipping_address = :addr, 
                               total_amount = :total, paid_amount = :paid, due_amount = :due, 
                               payment_method = :pmethod, payment_type = :ptype, payment_status = :pstatus, order_status = :ostatus 
             WHERE id = :id",
            [
                'cname' => $customerName,
                'cphone' => $customerPhone,
                'addr' => $shippingAddress,
                'total' => $totalAmount,
                'paid' => $paidAmount,
                'due' => $dueAmount,
                'pmethod' => $paymentMethod,
                'ptype' => $paymentType,
                'pstatus' => $paymentStatus,
                'ostatus' => $orderStatus,
                'id' => $orderId
            ]
        );

        audit_log("admin_order_update", "Admin updated Order #{$order['order_number']}: Total BDT {$totalAmount}, Paid BDT {$paidAmount}, Due BDT {$dueAmount}, Payment: {$paymentStatus}, Status: {$orderStatus}");
        Session::setFlash('success', 'Order details, price, and payment status updated successfully.');
        redirect(url('/admin/orders/' . $orderId));
    }

    /**
     * Financial Recharge Management
     */
    public function recharges(): void {
        $recharges = Database::fetchAll(
            "SELECT r.id, r.reference_no, r.amount, r.method, r.payment_proof, r.status, r.created_at, u.name as user_name, u.email 
             FROM recharges r 
             JOIN users u ON r.user_id = u.id 
             ORDER BY r.id DESC"
        );
        View::render('admin/recharges', [
            'title' => 'Recharge Requests - Admin Console',
            'recharges' => $recharges,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ], 'layouts/admin');
    }

    public function processRecharge(): void {
        CSRF::verifyOrDie();
        $rechargeId = (int)($_POST['recharge_id'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($action === 'approve') {
            $success = WalletController::approveRecharge($rechargeId);
            if ($success) {
                Session::setFlash('success', 'Recharge request approved and wallet credited!');
            } else {
                Session::setFlash('error', 'Failed to approve recharge request or request is no longer pending.');
            }
        } elseif ($action === 'reject') {
            // Conditional pending status update with rowCount check
            $stmt = Database::query("UPDATE recharges SET status = 'rejected' WHERE id = :id AND status = 'pending'", ['id' => $rechargeId]);
            if ($stmt->rowCount() > 0) {
                audit_log("admin_recharge_reject", "Rejected recharge request #{$rechargeId}");
                Session::setFlash('success', 'Recharge request rejected.');
            } else {
                Session::setFlash('error', 'Recharge request is no longer pending.');
            }
        }

        redirect(url('/admin/recharges'));
    }

    /**
     * Financial Withdrawal Management
     */
    public function withdrawals(): void {
        $withdrawals = Database::fetchAll(
            "SELECT w.id, w.reference_no, w.amount, w.method, w.account_info, w.status, w.rejection_reason, w.created_at, u.name as user_name, u.email 
             FROM withdrawals w 
             JOIN users u ON w.user_id = u.id 
             ORDER BY w.id DESC"
        );
        $rejectionTemplates = Database::fetchAll("SELECT id, title, message FROM withdrawal_rejection_templates ORDER BY id ASC");

        View::render('admin/withdrawals', [
            'title' => 'Withdrawal Requests - Admin Console',
            'withdrawals' => $withdrawals,
            'rejectionTemplates' => $rejectionTemplates,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ], 'layouts/admin');
    }

    public function processWithdrawal(): void {
        CSRF::verifyOrDie();
        $withdrawalId = (int)($_POST['withdrawal_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        $rejectionReason = trim($_POST['rejection_reason'] ?? '');

        if ($action === 'approve') {
            $success = WalletController::approveWithdrawal($withdrawalId);
            if ($success) {
                Session::setFlash('success', 'Withdrawal request approved and processed!');
            } else {
                Session::setFlash('error', 'Failed to approve withdrawal request or request is no longer pending.');
            }
        } elseif ($action === 'reject') {
            // Conditional pending status update with rowCount check
            $stmt = Database::query(
                "UPDATE withdrawals SET status = 'rejected', rejection_reason = :reason WHERE id = :id AND status = 'pending'",
                ['reason' => !empty($rejectionReason) ? $rejectionReason : 'Request rejected by admin.', 'id' => $withdrawalId]
            );
            if ($stmt->rowCount() > 0) {
                audit_log("admin_withdrawal_reject", "Rejected withdrawal request #{$withdrawalId}. Reason: {$rejectionReason}");
                Session::setFlash('success', 'Withdrawal request rejected with reason.');
            } else {
                Session::setFlash('error', 'Withdrawal request is no longer pending.');
            }
        }

        redirect(url('/admin/withdrawals'));
    }

    /**
     * Affiliate Commission Management
     */
    public function commissions(): void {
        $commissions = Database::fetchAll(
            "SELECT ac.id, ac.conversion_id, ac.commission_amount, ac.status, ac.created_at, u.name as affiliate_name, o.order_number 
             FROM affiliate_commissions ac 
             JOIN affiliate_profiles ap ON ac.affiliate_profile_id = ap.id 
             JOIN users u ON ap.user_id = u.id 
             JOIN conversions c ON ac.conversion_id = c.id 
             JOIN orders o ON c.order_id = o.id 
             ORDER BY ac.id DESC"
        );
        View::render('admin/commissions', [
            'title' => 'Affiliate Commissions - Admin Console',
            'commissions' => $commissions,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ], 'layouts/admin');
    }

    public function processCommission(): void {
        CSRF::verifyOrDie();
        $commissionId = (int)($_POST['commission_id'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($action === 'approve') {
            $success = WalletController::approveCommission($commissionId);
            if ($success) {
                Session::setFlash('success', 'Commission approved and credited to affiliate wallet!');
            } else {
                Session::setFlash('error', 'Failed to approve commission or request is no longer pending.');
            }
        } elseif ($action === 'reject') {
            // Conditional pending status update with rowCount check
            $stmt = Database::query("UPDATE affiliate_commissions SET status = 'cancelled' WHERE id = :id AND status = 'pending'", ['id' => $commissionId]);
            if ($stmt->rowCount() > 0) {
                audit_log("admin_commission_reject", "Rejected commission payout #{$commissionId}");
                Session::setFlash('success', 'Commission request cancelled.');
            } else {
                Session::setFlash('error', 'Commission request is no longer pending.');
            }
        }

        redirect(url('/admin/commissions'));
    }

    /**
     * Immutable Wallet Ledger Viewer
     */
    public function walletTransactions(): void {
        $transactions = Database::fetchAll(
            "SELECT wt.reference_id, wt.type, wt.amount, wt.balance_before, wt.balance_after, wt.description, wt.status, wt.created_at, u.name as user_name 
             FROM wallet_transactions wt 
             JOIN wallets w ON wt.wallet_id = w.id 
             JOIN users u ON w.user_id = u.id 
             ORDER BY wt.id DESC LIMIT 50"
        );
        View::render('admin/wallet_transactions', [
            'title' => 'Immutable Wallet Ledger - Admin Console',
            'transactions' => $transactions
        ], 'layouts/admin');
    }

    /**
     * Immutable Audit Logs Viewer
     */
    public function auditLogs(): void {
        $logs = Database::fetchAll(
            "SELECT a.id, a.action, a.details, a.ip_address, a.created_at, u.name as user_name 
             FROM audit_logs a 
             LEFT JOIN users u ON a.user_id = u.id 
             ORDER BY a.id DESC LIMIT 50"
        );
        View::render('admin/audit_logs', [
            'title' => 'Audit Logs - Admin Console',
            'logs' => $logs
        ], 'layouts/admin');
    }
}
