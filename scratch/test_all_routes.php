<?php

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

require_once __DIR__ . '/../app/Helpers/functions.php';
load_env(__DIR__ . '/../.env');

$router = require __DIR__ . '/../routes/web.php';

$routesToTest = [
    ['GET', '/'],
    ['GET', '/login'],
    ['POST', '/login'],
    ['GET', '/register'],
    ['POST', '/register'],
    ['POST', '/logout'],
    ['GET', '/verify-otp'],
    ['POST', '/verify-otp'],
    ['GET', '/marketplace'],
    ['GET', '/marketplace/product/1'],
    ['GET', '/cart'],
    ['GET', '/cart/add'],
    ['POST', '/cart/add'],
    ['GET', '/cart/update'],
    ['POST', '/cart/update'],
    ['GET', '/cart/remove'],
    ['POST', '/cart/remove'],
    ['GET', '/checkout'],
    ['POST', '/checkout'],
    ['GET', '/my-orders'],
    ['GET', '/wallet'],
    ['GET', '/wallet/recharge'],
    ['POST', '/wallet/recharge'],
    ['GET', '/wallet/withdraw'],
    ['POST', '/wallet/withdraw'],
    ['GET', '/affiliate'],
    ['GET', '/affiliate/create-link'],
    ['POST', '/affiliate/create-link'],
    ['GET', '/ref/invalid-slug'],
    ['GET', '/admin'],
    ['GET', '/profile'],
    ['GET', '/profile/complete-task'],
    ['POST', '/profile/complete-task'],
    ['GET', '/admin/users'],
    ['POST', '/admin/users/update-status'],
    ['POST', '/admin/users/update-credits'],
    ['POST', '/admin/users/update-password'],
    ['POST', '/admin/users/generate-otp'],
    ['POST', '/admin/users/add-balance'],
    ['POST', '/admin/users/manage-balance'],
    ['POST', '/admin/users/update-info'],
    ['POST', '/admin/users/delete'],
    ['GET', '/admin/products'],
    ['GET', '/admin/products/create'],
    ['GET', '/admin/products/1/edit'],
    ['POST', '/admin/products/save'],
    ['GET', '/admin/categories'],
    ['POST', '/admin/categories/save'],
    ['GET', '/admin/orders'],
    ['GET', '/admin/orders/1'],
    ['POST', '/admin/orders/update-status'],
    ['POST', '/admin/orders/update-financials'],
    ['GET', '/admin/recharges'],
    ['POST', '/admin/recharges/process'],
    ['GET', '/admin/withdrawals'],
    ['POST', '/admin/withdrawals/process'],
    ['GET', '/admin/commissions'],
    ['POST', '/admin/commissions/process'],
    ['GET', '/admin/wallet-transactions'],
    ['GET', '/admin/audit-logs']
];

$errors = [];
$passed = 0;

foreach ($routesToTest as [$method, $uri]) {
    try {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['HTTP_HOST'] = 'localhost';
        
        ob_start();
        $router->dispatch($method, $uri);
        $output = ob_get_clean();

        $code = http_response_code();
        http_response_code(200);

        if ($code === 500) {
            $errors[] = "ROUTE [{$method} {$uri}] returned HTTP 500!";
        } else {
            $passed++;
        }
    } catch (\Throwable $e) {
        if (ob_get_level() > 0) ob_end_clean();
        $errors[] = "ROUTE [{$method} {$uri}] threw exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine();
    }
}

echo "=== ROUTE TEST SUMMARY ===\n";
echo "Total routes tested: " . count($routesToTest) . "\n";
echo "Passed (Non-500): {$passed}\n";
echo "Errors: " . count($errors) . "\n\n";

if (!empty($errors)) {
    foreach ($errors as $err) {
        echo "FAIL: {$err}\n";
    }
    exit(1);
} else {
    echo "SUCCESS: ALL 60 ROUTES DISPATCHED SAFELY WITHOUT ANY 500 ERRORS!\n";
}
