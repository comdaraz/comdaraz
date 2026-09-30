<?php

use App\Core\Router;
use App\Core\View;
use App\Modules\Auth\AuthController;
use App\Modules\Profile\ProfileController;
use App\Modules\Marketplace\MarketplaceController;
use App\Modules\Product\ProductController;
use App\Modules\Cart\CartController;
use App\Modules\Order\OrderController;
use App\Modules\Wallet\WalletController;
use App\Modules\Affiliate\AffiliateController;
use App\Modules\Admin\AdminController;

$router = new Router();

// 1. User Storefront Routes
$router->get('/', function() {
    $success = \App\Core\Session::getFlash('success');
    $error = \App\Core\Session::getFlash('error');
    View::render('pages/home', [
        'title' => 'Daraz-Style Nano Modular Affiliate Platform',
        'success' => $success,
        'error' => $error
    ]);
});

// Authentication
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'handleLogin']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'handleRegister']);
$router->post('/logout', [AuthController::class, 'handleLogout']);
$router->get('/verify-otp', [ProfileController::class, 'showOTPVerify']);
$router->post('/verify-otp', [ProfileController::class, 'verifyOTP']);

// Marketplace & Catalog
$router->get('/marketplace', [MarketplaceController::class, 'index']);
$router->get('/marketplace/product/{id}', [ProductController::class, 'show']);

// Shopping Cart
$router->get('/cart', [CartController::class, 'index']);
$router->post('/cart/add', [CartController::class, 'add']);
$router->post('/cart/update', [CartController::class, 'update']);
$router->post('/cart/remove', [CartController::class, 'remove']);

// Order Checkout & History
$router->post('/checkout', [OrderController::class, 'checkout']);
$router->get('/my-orders', [OrderController::class, 'myOrders']);

// Financial Wallet
$router->get('/wallet', [WalletController::class, 'show']);
$router->post('/wallet/recharge', [WalletController::class, 'handleRecharge']);
$router->post('/wallet/withdraw', [WalletController::class, 'handleWithdrawal']);

// Affiliate Referral Panel
$router->get('/affiliate', [AffiliateController::class, 'dashboard']);
$router->post('/affiliate/create-link', [AffiliateController::class, 'createLink']);
$router->get('/ref/{slug}', [AffiliateController::class, 'trackClick']);


// 2. Protected Admin Console Routes (All server-side role-verified via requireAdmin())
$router->get('/admin', [AdminController::class, 'dashboard']);

// User Profile & Task Dashboard
$router->get('/profile', [ProfileController::class, 'index']);
$router->post('/profile/complete-task', [ProfileController::class, 'completeTask']);
$router->post('/profile/verify-otp', [ProfileController::class, 'verifyOTP']);

// Admin User Management
$router->get('/admin/users', [AdminController::class, 'users']);
$router->post('/admin/users/update-status', [AdminController::class, 'updateUserStatus']);
$router->post('/admin/users/update-credits', [AdminController::class, 'updateUserCredits']);
$router->post('/admin/users/update-password', [AdminController::class, 'updateUserPassword']);
$router->post('/admin/users/generate-otp', [AdminController::class, 'generateUserOTP']);
$router->post('/admin/users/add-balance', [AdminController::class, 'manageUserBalance']);
$router->post('/admin/users/manage-balance', [AdminController::class, 'manageUserBalance']);
$router->post('/admin/users/update-info', [AdminController::class, 'updateUserInfo']);
$router->post('/admin/users/delete', [AdminController::class, 'deleteUser']);

// Admin Product Management
$router->get('/admin/products', [AdminController::class, 'products']);
$router->get('/admin/products/create', [AdminController::class, 'createProductForm']);
$router->get('/admin/products/{id}/edit', [AdminController::class, 'editProductForm']);
$router->post('/admin/products/save', [AdminController::class, 'saveProduct']);

// Admin Category Management
$router->get('/admin/categories', [AdminController::class, 'categories']);
$router->post('/admin/categories/save', [AdminController::class, 'saveCategory']);

// Admin Order Management
$router->get('/admin/orders', [AdminController::class, 'orders']);
$router->get('/admin/orders/{id}', [AdminController::class, 'showOrder']);
$router->post('/admin/orders/update-status', [AdminController::class, 'updateOrderStatus']);
$router->post('/admin/orders/update-financials', [AdminController::class, 'updateOrderFinancials']);

// Admin Financial Recharges
$router->get('/admin/recharges', [AdminController::class, 'recharges']);
$router->post('/admin/recharges/process', [AdminController::class, 'processRecharge']);

// Admin Financial Withdrawals
$router->get('/admin/withdrawals', [AdminController::class, 'withdrawals']);
$router->post('/admin/withdrawals/process', [AdminController::class, 'processWithdrawal']);

// Admin Affiliate Commissions
$router->get('/admin/commissions', [AdminController::class, 'commissions']);
$router->post('/admin/commissions/process', [AdminController::class, 'processCommission']);

// Admin Immutable Wallet Ledger
$router->get('/admin/wallet-transactions', [AdminController::class, 'walletTransactions']);

// Admin System Audit Logs
$router->get('/admin/audit-logs', [AdminController::class, 'auditLogs']);

return $router;
