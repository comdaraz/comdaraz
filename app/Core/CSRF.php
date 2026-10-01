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
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'CSRF Token Validation Failed. Please refresh and try again.']);
                exit;
            }

            Session::start();
            Session::setFlash('error', 'Security token expired or invalid. Please refresh the page and try again.');
            $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
            redirect($referer);
        }
    }
}
