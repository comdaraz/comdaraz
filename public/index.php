<?php

// 1. PSR-4 Autoloader for Nano Modular Monolith Architecture
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

// 2. Load Helpers & Environment variables
require_once __DIR__ . '/../app/Helpers/functions.php';
load_env(__DIR__ . '/../.env');

// 3. Initialize Secure Session
\App\Core\Session::start();

// 4. Load & Dispatch Web Routes
try {
    $router = require_once __DIR__ . '/../routes/web.php';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $router->dispatch($method, $uri);
} catch (\Throwable $e) {
    error_log("Unhandled Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    if (env('APP_DEBUG', false)) {
        throw $e;
    }
    http_response_code(500);
    \App\Core\View::render('pages/500', ['title' => '500 - Server Error', 'error' => $e->getMessage()]);
}

