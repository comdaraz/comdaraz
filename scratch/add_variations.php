<?php
$db = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $db->exec("ALTER TABLE products ADD COLUMN size_options TEXT DEFAULT NULL");
    echo "Added size_options column\n";
} catch (Throwable $e) {
    echo "size_options column already exists or error: " . $e->getMessage() . "\n";
}

try {
    $db->exec("ALTER TABLE products ADD COLUMN color_options TEXT DEFAULT NULL");
    echo "Added color_options column\n";
} catch (Throwable $e) {
    echo "color_options column already exists or error: " . $e->getMessage() . "\n";
}
