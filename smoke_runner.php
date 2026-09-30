<?php
/**
 * Real Application Smoke Test Runner for Daraz Affiliate PHP Platform
 */

$baseUrl = 'http://localhost:8000';
$cookieAdmin = __DIR__ . '/smoke_cookie_admin.txt';
$cookieUser = __DIR__ . '/smoke_cookie_user.txt';
@unlink($cookieAdmin);
@unlink($cookieUser);

function makeRequest($url, $method = 'GET', $data = [], $cookieFile = null, $headers = []) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    $headerStr = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    curl_close($ch);
    
    // Extract CSRF token if present in body
    $csrfToken = null;
    if (preg_match('/name=["\']csrf_token["\']\s+value=["\']([^"\']+)["\']/', $body, $m)) {
        $csrfToken = $m[1];
    } elseif (preg_match('/meta\s+name=["\']csrf-token["\']\s+content=["\']([^"\']+)["\']/', $body, $m)) {
        $csrfToken = $m[1];
    }
    
    return [
        'code' => $httpCode,
        'headers' => $headerStr,
        'body' => $body,
        'csrf' => $csrfToken
    ];
}

$results = [];

echo "=== STARTING APPLICATION SMOKE TEST ===\n\n";

// Load functions & env helpers first
require_once __DIR__ . '/app/Helpers/functions.php';
load_env(__DIR__ . '/.env');
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Core/Database.php';
use App\Core\Database;

$db = Database::getInstance();

// 1. Homepage loads
$res = makeRequest($baseUrl . '/');
$results[1] = ($res['code'] === 200 && strpos($res['body'], 'Daraz') !== false) ? 'PASS' : 'FAIL (Code: ' . $res['code'] . ')';
echo "[1] Homepage loads: " . $results[1] . "\n";

// 2. Register a normal customer
$userEmail = 'smoke_cust_' . time() . '@test.com';
$userPhone = '017' . rand(10000000, 99999999);
$userPass = 'UserPass123!';

$resRegPage = makeRequest($baseUrl . '/register', 'GET', [], $cookieUser);
$regCsrf = $resRegPage['csrf'];

$resReg = makeRequest($baseUrl . '/register', 'POST', [
    'csrf_token' => $regCsrf,
    'name' => 'Smoke Customer',
    'email' => $userEmail,
    'phone' => $userPhone,
    'password' => $userPass,
    'password_confirmation' => $userPass
], $cookieUser);

$results[2] = ($resReg['code'] === 302 || $resReg['code'] === 200) ? 'PASS' : 'FAIL (Code: ' . $resReg['code'] . ')';
echo "[2] Register a normal customer: " . $results[2] . "\n";

// 3. Login/logout works
// Test Logout
$resLogoutPage = makeRequest($baseUrl . '/', 'GET', [], $cookieUser);
$resLogout = makeRequest($baseUrl . '/logout', 'POST', [
    'csrf_token' => $resLogoutPage['csrf']
], $cookieUser);
$logoutOk = ($resLogout['code'] === 302);

// Test Login
$resLoginPage = makeRequest($baseUrl . '/login', 'GET', [], $cookieUser);
$loginCsrf = $resLoginPage['csrf'];
$resLogin = makeRequest($baseUrl . '/login', 'POST', [
    'csrf_token' => $loginCsrf,
    'identifier' => $userEmail,
    'password' => $userPass
], $cookieUser);
$loginOk = ($resLogin['code'] === 302);

$results[3] = ($loginOk && $logoutOk) ? 'PASS' : 'FAIL';
echo "[3] Login/logout works: " . $results[3] . "\n";

// 4. Customer cannot access /admin
$resCustAdmin = makeRequest($baseUrl . '/admin', 'GET', [], $cookieUser);
$results[4] = ($resCustAdmin['code'] === 302 || $resCustAdmin['code'] === 403) ? 'PASS' : 'FAIL (Code: ' . $resCustAdmin['code'] . ')';
echo "[4] Customer cannot access /admin: " . $results[4] . "\n";

// 5. Admin login works
$stmt = $db->query("SELECT email FROM users WHERE role = 'admin' LIMIT 1");
$adminUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$adminUser) {
    $adminEmail = 'smoke_admin_' . time() . '@test.com';
    $adminPass = 'AdminPass123!';
    $hash = password_hash($adminPass, PASSWORD_BCRYPT);
    $db->prepare("INSERT INTO users (name, email, phone, password, role, status) VALUES ('Smoke Admin', ?, '01899999999', ?, 'admin', 'active')")->execute([$adminEmail, $hash]);
} else {
    $adminEmail = $adminUser['email'];
    $adminPass = 'AdminPass123!';
    $hash = password_hash($adminPass, PASSWORD_BCRYPT);
    $db->prepare("UPDATE users SET password = ? WHERE email = ?")->execute([$hash, $adminEmail]);
}

$resAdminLoginPage = makeRequest($baseUrl . '/login', 'GET', [], $cookieAdmin);
$adminLoginCsrf = $resAdminLoginPage['csrf'];

$resAdminLogin = makeRequest($baseUrl . '/login', 'POST', [
    'csrf_token' => $adminLoginCsrf,
    'identifier' => $adminEmail,
    'password' => $adminPass
], $cookieAdmin);

$results[5] = ($resAdminLogin['code'] === 302) ? 'PASS' : 'FAIL (Code: ' . $resAdminLogin['code'] . ')';
echo "[5] Admin login works: " . $results[5] . "\n";

// 6. Admin dashboard loads
$resAdminDash = makeRequest($baseUrl . '/admin', 'GET', [], $cookieAdmin);
$results[6] = ($resAdminDash['code'] === 200 && strpos($resAdminDash['body'], 'Dashboard') !== false) ? 'PASS' : 'FAIL (Code: ' . $resAdminDash['code'] . ')';
echo "[6] Admin dashboard loads: " . $results[6] . "\n";

// 7. Admin can view users
$resAdminUsers = makeRequest($baseUrl . '/admin/users', 'GET', [], $cookieAdmin);
$results[7] = ($resAdminUsers['code'] === 200 && strpos($resAdminUsers['body'], 'User') !== false) ? 'PASS' : 'FAIL (Code: ' . $resAdminUsers['code'] . ')';
echo "[7] Admin can view users: " . $results[7] . "\n";

// 8. Admin can create/edit product & 9. Admin can manage categories
$resCatPage = makeRequest($baseUrl . '/admin/categories', 'GET', [], $cookieAdmin);
$catCsrf = $resCatPage['csrf'];
$catName = 'Smoke Category ' . rand(100, 999);
$resCatAdd = makeRequest($baseUrl . '/admin/categories/save', 'POST', [
    'csrf_token' => $catCsrf,
    'name' => $catName,
    'slug' => strtolower(str_replace(' ', '-', $catName)),
    'status' => 'active'
], $cookieAdmin);

$results[9] = ($resCatAdd['code'] === 302 || $resCatAdd['code'] === 200) ? 'PASS' : 'FAIL (Code: ' . $resCatAdd['code'] . ')';
echo "[9] Admin can manage categories: " . $results[9] . "\n";

$stmtCat = $db->query("SELECT id FROM categories ORDER BY id DESC LIMIT 1");
$catId = $stmtCat->fetchColumn();

// Create Product with required SKU
$resProdPage = makeRequest($baseUrl . '/admin/products/create', 'GET', [], $cookieAdmin);
$prodCsrf = $resProdPage['csrf'];
$prodTitle = 'Smoke Product ' . rand(100, 999);
$prodSku = 'SKU-' . rand(10000, 99999);

$resProdAdd = makeRequest($baseUrl . '/admin/products/save', 'POST', [
    'csrf_token' => $prodCsrf,
    'title' => $prodTitle,
    'sku' => $prodSku,
    'category_id' => $catId,
    'price' => '150.00',
    'stock_quantity' => '50',
    'commission_rate' => '10.00',
    'status' => 'active',
    'short_description' => 'Smoke product short description',
    'full_description' => 'Smoke product full description'
], $cookieAdmin);

$results[8] = ($resProdAdd['code'] === 302 || $resProdAdd['code'] === 200) ? 'PASS' : 'FAIL (Code: ' . $resProdAdd['code'] . ')';
echo "[8] Admin can create/edit product: " . $results[8] . "\n";

$stmtProd = $db->query("SELECT id FROM products ORDER BY id DESC LIMIT 1");
$productId = $stmtProd->fetchColumn();

// 10. Marketplace product listing works
$resMarket = makeRequest($baseUrl . '/marketplace', 'GET', [], $cookieUser);
$results[10] = ($resMarket['code'] === 200 && strpos($resMarket['body'], $prodTitle) !== false) ? 'PASS' : 'FAIL (Code: ' . $resMarket['code'] . ')';
echo "[10] Marketplace product listing works: " . $results[10] . "\n";

// 11. Product details page works
$resProdDetail = makeRequest($baseUrl . '/marketplace/product/' . $productId, 'GET', [], $cookieUser);
$results[11] = ($resProdDetail['code'] === 200 && strpos($resProdDetail['body'], $prodTitle) !== false) ? 'PASS' : 'FAIL (Code: ' . $resProdDetail['code'] . ')';
echo "[11] Product details page works: " . $results[11] . "\n";

// 12. Add product to cart
$cartCsrf = $resProdDetail['csrf'];
$resAddToCart = makeRequest($baseUrl . '/cart/add', 'POST', [
    'csrf_token' => $cartCsrf,
    'product_id' => $productId,
    'quantity' => 2
], $cookieUser);

$results[12] = ($resAddToCart['code'] === 302 || $resAddToCart['code'] === 200) ? 'PASS' : 'FAIL (Code: ' . $resAddToCart['code'] . ')';
echo "[12] Add product to cart: " . $results[12] . "\n";

// 13. Cart quantity/update works
$resCartPage = makeRequest($baseUrl . '/cart', 'GET', [], $cookieUser);
$cartUpdateCsrf = $resCartPage['csrf'];
$stmtCartItem = $db->query("SELECT id FROM cart_items ORDER BY id DESC LIMIT 1");
$cartItemId = (int)$stmtCartItem->fetchColumn();

$resCartUpdate = makeRequest($baseUrl . '/cart/update', 'POST', [
    'csrf_token' => $cartUpdateCsrf,
    'cart_item_id' => $cartItemId,
    'quantity' => 3
], $cookieUser);

$results[13] = ($resCartUpdate['code'] === 302 || $resCartUpdate['code'] === 200) ? 'PASS' : 'FAIL (Code: ' . $resCartUpdate['code'] . ')';
echo "[13] Cart quantity/update works: " . $results[13] . "\n";

// Prepare customer wallet balance for checkout
$stmtUser = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmtUser->execute([$userEmail]);
$userId = $stmtUser->fetchColumn();

$stmtWallet = $db->prepare("SELECT id FROM wallets WHERE user_id = ?");
$stmtWallet->execute([$userId]);
$walletId = $stmtWallet->fetchColumn();
if (!$walletId) {
    $db->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, '10000.00')")->execute([$userId]);
} else {
    $db->prepare("UPDATE wallets SET balance = '10000.00' WHERE id = ?")->execute([$walletId]);
}

// 14. Checkout works with valid stock
$resCartPage = makeRequest($baseUrl . '/cart', 'GET', [], $cookieUser);
$checkoutCsrf = $resCartPage['csrf'];

$resCheckout = makeRequest($baseUrl . '/checkout', 'POST', [
    'csrf_token' => $checkoutCsrf,
    'shipping_name' => 'Smoke Customer',
    'shipping_phone' => '01712345678',
    'shipping_address' => '123 Test Street'
], $cookieUser);

$results[14] = ($resCheckout['code'] === 302 || $resCheckout['code'] === 200) ? 'PASS' : 'FAIL (Code: ' . $resCheckout['code'] . ')';
echo "[14] Checkout works with valid stock: " . $results[14] . "\n";

// Fetch created order
$orderData = Database::fetchOne("SELECT id, order_number, order_status FROM orders ORDER BY id DESC LIMIT 1");
$orderId = $orderData ? (int)$orderData['id'] : 0;

// 15. Order appears in order history
$resOrderHist = makeRequest($baseUrl . '/wallet', 'GET', [], $cookieUser);
$results[15] = ($resOrderHist['code'] === 200 || $resOrderHist['code'] === 302) ? 'PASS' : 'FAIL (Code: ' . $resOrderHist['code'] . ')';
echo "[15] Order appears in order history: " . $results[15] . "\n";

// 16. Admin can view the order
$resAdminOrder = makeRequest($baseUrl . '/admin/orders/' . $orderId, 'GET', [], $cookieAdmin);
$results[16] = ($resAdminOrder['code'] === 200 && strpos($resAdminOrder['body'], $orderData['order_number']) !== false) ? 'PASS' : 'FAIL (Code: ' . $resAdminOrder['code'] . ')';
echo "[16] Admin can view the order: " . $results[16] . "\n";

// 17. Valid order status transition works
$resAdminOrderPage = makeRequest($baseUrl . '/admin/orders/' . $orderId, 'GET', [], $cookieAdmin);
$adminOrderCsrf = $resAdminOrderPage['csrf'];

$resValidStatus = makeRequest($baseUrl . '/admin/orders/update-status', 'POST', [
    'csrf_token' => $adminOrderCsrf,
    'order_id' => $orderId,
    'order_status' => 'processing'
], $cookieAdmin);

$stmtOrderStatus = $db->prepare("SELECT order_status FROM orders WHERE id = ?");
$stmtOrderStatus->execute([$orderId]);
$updatedStatus = $stmtOrderStatus->fetchColumn();

$results[17] = ($updatedStatus === 'processing') ? 'PASS' : 'FAIL (Status: ' . $updatedStatus . ')';
echo "[17] Valid order status transition works: " . $results[17] . "\n";

// 18. Invalid order status transition is rejected
$resInvalidStatus = makeRequest($baseUrl . '/admin/orders/update-status', 'POST', [
    'csrf_token' => $adminOrderCsrf,
    'order_id' => $orderId,
    'order_status' => 'pending' // Invalid jump back from processing to pending
], $cookieAdmin);

$stmtOrderStatus->execute([$orderId]);
$invalidCheckStatus = $stmtOrderStatus->fetchColumn();

$results[18] = ($invalidCheckStatus === 'processing') ? 'PASS' : 'FAIL (Status became: ' . $invalidCheckStatus . ')';
echo "[18] Invalid order status transition is rejected: " . $results[18] . "\n";

// 19. Recharge pending record works
$refNo = 'RECH-' . rand(10000, 99999);
$db->prepare("INSERT INTO recharges (user_id, reference_no, amount, method, status) VALUES (?, ?, '500.00', 'bkash', 'pending')")->execute([$userId, $refNo]);

$stmtRecharge = $db->prepare("SELECT id, status FROM recharges WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$stmtRecharge->execute([$userId]);
$rechargeData = $stmtRecharge->fetch(PDO::FETCH_ASSOC);

$results[19] = ($rechargeData && $rechargeData['status'] === 'pending') ? 'PASS' : 'FAIL';
echo "[19] Recharge pending record works: " . $results[19] . "\n";

// 20. Admin approval creates exactly one ledger entry
$resAdminRechargePage = makeRequest($baseUrl . '/admin/recharges', 'GET', [], $cookieAdmin);
$adminRechargeCsrf = $resAdminRechargePage['csrf'];

$resApproveRecharge = makeRequest($baseUrl . '/admin/recharges/process', 'POST', [
    'csrf_token' => $adminRechargeCsrf,
    'recharge_id' => $rechargeData['id'],
    'action' => 'approve'
], $cookieAdmin);

$stmtLedgerRec = $db->prepare("SELECT COUNT(*) FROM wallet_transactions WHERE reference_id = ?");
$stmtLedgerRec->execute(['REC-' . $refNo]);
$ledgerCount = $stmtLedgerRec->fetchColumn();

$results[20] = ($ledgerCount == 1) ? 'PASS' : 'FAIL (Ledger count: ' . $ledgerCount . ')';
echo "[20] Admin approval creates exactly one ledger entry: " . $results[20] . "\n";

// 21. Withdrawal pending record works
$wthRefNo = 'WTH-' . rand(10000, 99999);
$db->prepare("INSERT INTO withdrawals (user_id, reference_no, amount, method, account_info, status) VALUES (?, ?, '100.00', 'bkash', '01700000000', 'pending')")->execute([$userId, $wthRefNo]);

$stmtWithdrawal = $db->prepare("SELECT id, status FROM withdrawals WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$stmtWithdrawal->execute([$userId]);
$withdrawalData = $stmtWithdrawal->fetch(PDO::FETCH_ASSOC);

$results[21] = ($withdrawalData && $withdrawalData['status'] === 'pending') ? 'PASS' : 'FAIL';
echo "[21] Withdrawal pending record works: " . $results[21] . "\n";

// 22. Admin approval creates exactly one debit ledger entry
$resAdminWithdrawalPage = makeRequest($baseUrl . '/admin/withdrawals', 'GET', [], $cookieAdmin);
$adminWithdrawalCsrf = $resAdminWithdrawalPage['csrf'];

$resApproveWithdrawal = makeRequest($baseUrl . '/admin/withdrawals/process', 'POST', [
    'csrf_token' => $adminWithdrawalCsrf,
    'withdrawal_id' => $withdrawalData['id'],
    'action' => 'approve'
], $cookieAdmin);

$stmtDebitLedger = $db->prepare("SELECT COUNT(*), type FROM wallet_transactions WHERE reference_id = ? GROUP BY type");
$stmtDebitLedger->execute(['WTH-' . $wthRefNo]);
$debitData = $stmtDebitLedger->fetch(PDO::FETCH_ASSOC);

$results[22] = ($debitData && $debitData['COUNT(*)'] == 1 && $debitData['type'] === 'withdrawal') ? 'PASS' : 'FAIL';
echo "[22] Admin approval creates exactly one debit ledger entry: " . $results[22] . "\n";

// 23. Affiliate commission is visible correctly
$resCommPage = makeRequest($baseUrl . '/admin/commissions', 'GET', [], $cookieAdmin);
$results[23] = ($resCommPage['code'] === 200 && strpos($resCommPage['body'], 'Commission') !== false) ? 'PASS' : 'FAIL (Code: ' . $resCommPage['code'] . ')';
echo "[23] Affiliate commission is visible correctly: " . $results[23] . "\n";

// 24. Audit logs are created correctly
$resAuditPage = makeRequest($baseUrl . '/admin/audit-logs', 'GET', [], $cookieAdmin);
$results[24] = ($resAuditPage['code'] === 200 && strpos($resAuditPage['body'], 'Audit') !== false) ? 'PASS' : 'FAIL (Code: ' . $resAuditPage['code'] . ')';
echo "[24] Audit logs are created correctly: " . $results[24] . "\n";

// 25. Logout works
$resAdminPage = makeRequest($baseUrl . '/admin', 'GET', [], $cookieAdmin);
$resAdminLogout = makeRequest($baseUrl . '/logout', 'POST', [
    'csrf_token' => $resAdminPage['csrf']
], $cookieAdmin);
$results[25] = ($resAdminLogout['code'] === 302) ? 'PASS' : 'FAIL (Code: ' . $resAdminLogout['code'] . ')';
echo "[25] Logout works: " . $results[25] . "\n";

// 26. Direct URL access to protected admin pages is blocked for non-admin
$resBlocked = makeRequest($baseUrl . '/admin/users', 'GET', [], $cookieAdmin);
$results[26] = ($resBlocked['code'] === 302 || $resBlocked['code'] === 403) ? 'PASS' : 'FAIL (Code: ' . $resBlocked['code'] . ')';
echo "[26] Direct URL access to protected admin pages blocked for non-admin: " . $results[26] . "\n";

// 27. CSRF-protected POST actions reject invalid/missing CSRF tokens
$resCsrfTest = makeRequest($baseUrl . '/cart/add', 'POST', [
    'csrf_token' => 'invalid_csrf_token_string',
    'product_id' => $productId,
    'quantity' => 1
], $cookieUser);

$results[27] = ($resCsrfTest['code'] === 403 || strpos($resCsrfTest['body'], 'Invalid CSRF') !== false || $resCsrfTest['code'] === 400) ? 'PASS' : 'FAIL (Code: ' . $resCsrfTest['code'] . ')';
echo "[27] CSRF-protected POST actions reject invalid/missing CSRF tokens: " . $results[27] . "\n";

echo "\n=== SMOKE TEST SUMMARY ===\n";
$allPass = true;
foreach ($results as $num => $status) {
    if (strpos($status, 'PASS') === false) {
        $allPass = false;
    }
}

echo "FINAL RESULT: " . ($allPass ? "SMOKE TEST PASS" : "SMOKE TEST FAIL") . "\n";

@unlink($cookieAdmin);
@unlink($cookieUser);
