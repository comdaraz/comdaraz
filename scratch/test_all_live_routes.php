<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

require __DIR__ . '/../app/Helpers/functions.php';
load_env(__DIR__ . '/../.env');

use App\Core\Session;
use App\Core\Database;

Session::start();
$admin = Database::fetchOne("SELECT id, name, role FROM users WHERE role = 'admin' LIMIT 1");
if ($admin) {
    Session::set('user_id', $admin['id']);
    Session::set('user_name', $admin['name']);
    Session::set('user_role', 'admin');
}

$routes = [
    '/',
    '/login',
    '/register',
    '/marketplace',
    '/cart',
    '/my-orders',
    '/wallet',
    '/affiliate',
    '/profile',
    '/admin',
    '/admin/users',
    '/admin/products',
    '/admin/categories',
    '/admin/orders',
    '/admin/recharges',
    '/admin/withdrawals',
    '/admin/commissions',
    '/admin/wallet-transactions',
    '/admin/audit-logs'
];

$results = [];

foreach ($routes as $route) {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = $route;
    try {
        ob_start();
        $router = require __DIR__ . '/../routes/web.php';
        $router->dispatch('GET', $route);
        ob_end_clean();
        $results[] = "[PASS] {$route}";
    } catch (\Throwable $e) {
        ob_end_clean();
        $results[] = "[FAIL] {$route}: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine();
    }
}

echo "=== ROUTE SCAN SUMMARY ===\n";
foreach ($results as $res) {
    echo $res . "\n";
}
