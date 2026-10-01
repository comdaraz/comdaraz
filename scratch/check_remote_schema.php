<?php

require_once __DIR__ . '/../app/Helpers/functions.php';
load_env(__DIR__ . '/../.env');

$host = env('DB_HOST', 'localhost');
$port = env('DB_PORT', '3306');
$db   = env('DB_DATABASE', 'u866412713_yes');
$user = env('DB_USERNAME', 'u866412713_yes');
$pass = env('DB_PASSWORD', 'DaraDB#47mQ9!');

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $t) {
        echo "=== TABLE: {$t} ===\n";
        $cols = $pdo->query("DESCRIBE {$t}")->fetchAll();
        foreach ($cols as $c) {
            echo "  - {$c['Field']} ({$c['Type']})\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
