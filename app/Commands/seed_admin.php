<?php

require_once __DIR__ . '/../Helpers/functions.php';
load_env(__DIR__ . '/../../.env');

use App\Core\Database;

if (php_sapi_name() !== 'cli') {
    die("Error: Admin seed script can only be executed via CLI.\n");
}

$args = $argv;
if (count($args) < 6) {
    die("Usage: php app/Commands/seed_admin.php <Name> <Email> <Phone> <Password> <SeedSecret>\n");
}

$name = trim($args[1]);
$email = trim($args[2]);
$phone = trim($args[3]);
$password = $args[4];
$secret = trim($args[5]);

$expectedSecret = env('ADMIN_SEED_SECRET');
if (empty($expectedSecret)) {
    die("Security Error: ADMIN_SEED_SECRET is not configured in .env environment file.\n");
}

if (!hash_equals($expectedSecret, $secret)) {
    die("Security Error: Invalid admin seed secret key.\n");
}

$existingAdmin = Database::fetchOne("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
if ($existingAdmin) {
    die("Security Error: An active Admin account already exists. Seed mechanism is permanently locked.\n");
}

$passVal = validate_password_policy($password);
if ($passVal !== true) {
    die("Password Policy Error: {$passVal}\n");
}

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

Database::beginTransaction();
try {
    Database::query(
        "INSERT INTO users (name, email, phone, password, role, status) VALUES (:name, :email, :phone, :pass, 'admin', 'active')",
        [
            'name' => $name,
            'email' => $email ?: null,
            'phone' => $phone ?: null,
            'pass' => $hashedPassword
        ]
    );
    $adminId = (int)Database::lastInsertId();

    Database::query("INSERT INTO wallets (user_id, balance, pending_balance) VALUES (:uid, 0.00, 0.00)", ['uid' => $adminId]);

    audit_log("system_first_admin_seeded", "Initial Admin account seeded via CLI", $adminId);

    Database::commit();
    echo "SUCCESS: Initial Admin user created successfully. Seed lock activated.\n";
} catch (\Exception $e) {
    Database::rollBack();
    die("Database Error: " . $e->getMessage() . "\n");
}
