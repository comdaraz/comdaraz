<?php

namespace App\Core;

class CSRF {
    public static function token(): string {
        Session::start();
        $token = Session::get('csrf_token');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set('csrf_token', $token);
        }
        return $token;
    }

    public static function validate(?string $token): bool {
        Session::start();
        $stored = Session::get('csrf_token');
        if (!$stored || !$token) {
            return false;
        }
        return hash_equals($stored, $token);
    }

    public static function verifyOrDie(): void {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!self::validate($token)) {
            http_response_code(403);
            die("CSRF Token Validation Failed. Please refresh the page and try again.");
        }
    }
}
