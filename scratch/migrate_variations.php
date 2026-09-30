<?php
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $base_dir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});

require_once __DIR__ . '/../app/Helpers/functions.php';
load_env(__DIR__ . '/../.env');

$db = \App\Core\Database::getInstance();

try {
    $db->exec("ALTER TABLE products ADD COLUMN size_options VARCHAR(255) DEFAULT NULL");
    echo "SUCCESS: Added size_options column to products table.\n";
} catch (Throwable $e) {
    echo "INFO: size_options - " . $e->getMessage() . "\n";
}

try {
    $db->exec("ALTER TABLE products ADD COLUMN color_options VARCHAR(255) DEFAULT NULL");
    echo "SUCCESS: Added color_options column to products table.\n";
} catch (Throwable $e) {
    echo "INFO: color_options - " . $e->getMessage() . "\n";
}
