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
$router = require_once __DIR__ . '/../routes/web.php';
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
