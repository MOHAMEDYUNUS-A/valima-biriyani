<?php

declare(strict_types=1);

namespace App\Helpers;

class Component
{
    private static string $basePath = __DIR__ . '/../../resources/views/components';

    public static function render(string $componentName, array $props = []): string
    {
        $filePath = self::$basePath . '/' . str_replace('.', '/', $componentName) . '.php';
        if (!file_exists($filePath)) {
            return "<!-- Component [{$componentName}] not found -->";
        }

        extract($props, EXTR_SKIP);
        ob_start();
        include $filePath;
        return ob_get_clean();
    }
}
