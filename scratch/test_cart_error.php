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
use App\Modules\Cart\CartController;

try {
    Session::start();
    echo "Testing CartController index()...\n";
    $controller = new CartController();
    $controller->index();
    echo "\nCART RENDER SUCCESS!\n";
} catch (\Throwable $e) {
    echo "\nEXACT CART ERROR: " . $e->getMessage() . "\n";
    echo "FILE: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "TRACE:\n" . $e->getTraceAsString() . "\n";
}
