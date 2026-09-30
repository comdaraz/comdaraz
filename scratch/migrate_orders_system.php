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

$cols = [
    "ALTER TABLE orders ADD COLUMN customer_name VARCHAR(100) DEFAULT NULL",
    "ALTER TABLE orders ADD COLUMN customer_phone VARCHAR(20) DEFAULT NULL",
    "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) NOT NULL DEFAULT 'COD'",
    "ALTER TABLE orders ADD COLUMN payment_type VARCHAR(20) NOT NULL DEFAULT 'full'",
    "ALTER TABLE orders ADD COLUMN paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00",
    "ALTER TABLE orders ADD COLUMN due_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00",
    "ALTER TABLE orders MODIFY COLUMN payment_status ENUM('unpaid', 'partially_paid', 'paid', 'refunded') NOT NULL DEFAULT 'unpaid'",
    "ALTER TABLE order_items ADD COLUMN selected_size VARCHAR(50) DEFAULT NULL",
    "ALTER TABLE order_items ADD COLUMN selected_color VARCHAR(50) DEFAULT NULL",
    "ALTER TABLE cart_items ADD COLUMN selected_size VARCHAR(50) DEFAULT NULL",
    "ALTER TABLE cart_items ADD COLUMN selected_color VARCHAR(50) DEFAULT NULL"
];

foreach ($cols as $sql) {
    try {
        $db->exec($sql);
        echo "SUCCESS: Executed: {$sql}\n";
    } catch (Throwable $e) {
        echo "INFO: " . $e->getMessage() . "\n";
    }
}
