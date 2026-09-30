<?php

namespace App\Core;

class View {
    public static function render(string $view, array $data = [], string $layout = 'layouts/main'): void {
        $viewPath = __DIR__ . '/../../views/' . ltrim($view, '/') . '.php';
        $layoutPath = __DIR__ . '/../../views/' . ltrim($layout, '/') . '.php';

        if (!file_exists($viewPath)) {
            die("View file not found: {$viewPath}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        if (file_exists($layoutPath)) {
            require $layoutPath;
        } else {
            echo $content;
        }
    }

    public static function component(string $component, array $data = []): void {
        $path = __DIR__ . '/../../views/components/' . ltrim($component, '/') . '.php';
        if (file_exists($path)) {
            extract($data, EXTR_SKIP);
            require $path;
        }
    }
}
