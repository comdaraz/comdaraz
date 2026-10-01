<?php

namespace App\Core;

class Router {
    private array $routes = [];

    public function get(string $path, callable|array $handler): void {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable|array $handler): void {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $path);
        $pattern = '#^' . $pattern . '$#';
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }

    public function dispatch(string $method, string $uri): void {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rawurldecode($uri);
        
        // Remove trailing slash except for root
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                $handler = $route['handler'];
                if (is_array($handler)) {
                    [$class, $action] = $handler;
                    $controller = new $class();
                    call_user_func_array([$controller, $action], $params);
                    return;
                } elseif (is_callable($handler)) {
                    call_user_func_array($handler, $params);
                    return;
                }
            }
        }

        // Check if path matched a POST route when requested via GET
        if ($method === 'GET') {
            foreach ($this->routes as $route) {
                if ($route['method'] === 'POST' && preg_match($route['pattern'], $uri)) {
                    if (str_starts_with($uri, '/cart') || $uri === '/checkout') {
                        redirect(url('/cart'));
                    } elseif (str_starts_with($uri, '/wallet')) {
                        redirect(url('/wallet'));
                    } elseif (str_starts_with($uri, '/affiliate')) {
                        redirect(url('/affiliate'));
                    } elseif (str_starts_with($uri, '/profile')) {
                        redirect(url('/profile'));
                    } elseif (str_starts_with($uri, '/admin/users')) {
                        redirect(url('/admin/users'));
                    } elseif (str_starts_with($uri, '/admin/products')) {
                        redirect(url('/admin/products'));
                    } elseif (str_starts_with($uri, '/admin/categories')) {
                        redirect(url('/admin/categories'));
                    } elseif (str_starts_with($uri, '/admin/orders')) {
                        redirect(url('/admin/orders'));
                    } elseif (str_starts_with($uri, '/admin/recharges')) {
                        redirect(url('/admin/recharges'));
                    } elseif (str_starts_with($uri, '/admin/withdrawals')) {
                        redirect(url('/admin/withdrawals'));
                    } elseif (str_starts_with($uri, '/admin/commissions')) {
                        redirect(url('/admin/commissions'));
                    } else {
                        redirect(url('/'));
                    }
                    return;
                }
            }
        }

        http_response_code(404);
        View::render('pages/404', ['title' => '404 - Page Not Found']);
    }
}
