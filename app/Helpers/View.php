<?php

namespace App\Helpers;

require_once __DIR__ . '/Icon.php';
require_once __DIR__ . '/Component.php';

use RuntimeException;

class View
{
    private static string $viewPath = __DIR__ . '/../../resources/views';

    public static function render(string $view, array $data = [], ?string $layout = null): string
    {
        $viewFile = self::$viewPath . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new RuntimeException("View file not found: [{$viewFile}]");
        }

        // Global view data
        $appConfig = require __DIR__ . '/../../config/app.php';
        $globalData = [
            'app' => $appConfig,
            'basePath' => Request::basePath(),
            'csrf_field' => Security::csrfField(),
            'csrf_token' => Security::generateCsrfToken(),
            'currentUser' => Session::user(),
            'flashSuccess' => Session::flash('success'),
            'flashError' => Session::flash('error'),
        ];

        extract(array_merge($globalData, $data), EXTR_SKIP);

        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        if ($layout !== null) {
            $layoutFile = self::$viewPath . '/layouts/' . $layout . '.php';
            if (!file_exists($layoutFile)) {
                throw new RuntimeException("Layout file not found: [{$layoutFile}]");
            }
            ob_start();
            include $layoutFile;
            return ob_get_clean();
        }

        return $content;
    }

    public static function asset(string $path): string
    {
        $base = Request::basePath();
        $clean = '/' . ltrim($path, '/');
        return $base . $clean;
    }

    public static function url(string $path = ''): string
    {
        $base = Request::basePath();
        $clean = '/' . ltrim($path, '/');
        return $base . ($clean === '/' ? '' : $clean);
    }
}
