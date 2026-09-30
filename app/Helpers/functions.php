<?php

if (!function_exists('load_env')) {
    function load_env(string $path): void {
        if (!file_exists($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (str_contains($line, '=')) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value, " \t\n\r\0\x0B\"'");
                if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                    putenv("{$name}={$value}");
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed {
        $val = getenv($key);
        if ($val === false) {
            $val = $_ENV[$key] ?? $default;
        }
        if ($val === 'true') return true;
        if ($val === 'false') return false;
        if ($val === 'null') return null;
        return $val;
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        $baseUrl = rtrim(env('APP_URL', 'http://localhost:8000'), '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        return \App\Core\CSRF::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void {
        header("Location: " . $url);
        exit;
    }
}

if (!function_exists('json_response')) {
    function json_response(mixed $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

if (!function_exists('validate_password_policy')) {
    function validate_password_policy(string $password): true|string {
        if (strlen($password) < 8) {
            return "Password must be at least 8 characters long.";
        }
        if (!preg_match('/[0-9]/', $password)) {
            return "Password must contain at least one number.";
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            return "Password must contain at least one special character (e.g. !@#$%^&*).";
        }
        return true;
    }
}

if (!function_exists('ensure_bcmath_loaded')) {
    function ensure_bcmath_loaded(): void {
        if (!extension_loaded('bcmath')) {
            throw new \RuntimeException("CRITICAL SERVER ERROR: PHP 'bcmath' extension is required for financial calculations.");
        }
    }
}

if (!function_exists('format_money')) {
    function format_money(mixed $amount): string {
        ensure_bcmath_loaded();
        $str = trim((string)$amount);
        if ($str === '' || !is_numeric($str)) {
            return "0.00";
        }
        return bcadd($str, "0", 2);
    }
}

if (!function_exists('money_add')) {
    function money_add(string $a, string $b): string {
        ensure_bcmath_loaded();
        return bcadd(format_money($a), format_money($b), 2);
    }
}

if (!function_exists('money_sub')) {
    function money_sub(string $a, string $b): string {
        ensure_bcmath_loaded();
        return bcsub(format_money($a), format_money($b), 2);
    }
}

if (!function_exists('money_mul')) {
    function money_mul(string $a, string $b): string {
        ensure_bcmath_loaded();
        return bcmul(format_money($a), format_money($b), 2);
    }
}

if (!function_exists('money_comp')) {
    function money_comp(string $a, string $b): int {
        ensure_bcmath_loaded();
        return bccomp(format_money($a), format_money($b), 2);
    }
}

if (!function_exists('audit_log')) {
    function audit_log(string $action, ?string $details = null, ?int $userId = null): void {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $uid = $userId ?? \App\Core\Session::get('user_id');
        
        try {
            \App\Core\Database::query(
                "INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (:uid, :act, :det, :ip)",
                [
                    'uid' => $uid ?: null,
                    'act' => $action,
                    'det' => $details,
                    'ip' => $ip
                ]
            );
        } catch (\Throwable $t) {
            error_log("Audit Log Failure: " . $t->getMessage());
        }
    }
}

if (!function_exists('requireAdmin')) {
    function requireAdmin(): void {
        \App\Core\Session::start();
        $userId = \App\Core\Session::get('user_id');
        $userRole = \App\Core\Session::get('user_role');

        if (!$userId || $userRole !== 'admin') {
            http_response_code(403);
            \App\Core\Session::setFlash('error', 'Access Denied: Admin authorization required.');
            redirect(url('/login'));
        }

        $userExists = \App\Core\Database::fetchOne("SELECT id FROM users WHERE id = :uid AND role = 'admin' AND status = 'active' LIMIT 1", ['uid' => $userId]);
        if (!$userExists) {
            \App\Core\Session::destroy();
            redirect(url('/login'));
        }
    }
}

if (!function_exists('upload_image_file')) {
    function upload_image_file(string $field, string $subfolder = 'products'): ?string {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file = $_FILES[$field];
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            return null;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $allowed, true)) {
            return null;
        }

        $uploadDir = __DIR__ . '/../../public/uploads/' . $subfolder;
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $filename = 'img_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $targetPath = $uploadDir . '/' . $filename;

        $moved = is_uploaded_file($file['tmp_name']) 
            ? move_uploaded_file($file['tmp_name'], $targetPath) 
            : @copy($file['tmp_name'], $targetPath);

        if ($moved) {
            return url('uploads/' . $subfolder . '/' . $filename);
        }

        return null;
    }
}
