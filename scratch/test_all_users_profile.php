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
use App\Modules\Profile\ProfileController;

$users = Database::fetchAll("SELECT id, name, email FROM users");
echo "FOUND " . count($users) . " USERS IN DATABASE\n\n";

foreach ($users as $user) {
    echo "--- TESTING PROFILE FOR USER #{$user['id']} ({$user['name']}) ---\n";
    try {
        Session::start();
        Session::set('user_id', $user['id']);
        Session::set('user_name', $user['name']);
        
        ob_start();
        $controller = new ProfileController();
        $controller->index();
        $html = ob_get_clean();
        echo "SUCCESS! Rendered " . strlen($html) . " bytes of HTML.\n";
    } catch (\Throwable $e) {
        if (ob_get_level()) ob_end_clean();
        echo "ERROR FOR USER #{$user['id']}: " . $e->getMessage() . "\n";
        echo "FILE: " . $e->getFile() . ":" . $e->getLine() . "\n";
        echo "TRACE:\n" . $e->getTraceAsString() . "\n\n";
    }
}
