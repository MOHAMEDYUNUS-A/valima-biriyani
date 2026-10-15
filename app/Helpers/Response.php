<?php

namespace App\Helpers;

class Response
{
    public static function json(array $data, int $statusCode = 200, array $headers = []): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        foreach ($headers as $key => $val) {
            header("{$key}: {$val}");
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function success(mixed $data = null, string $message = 'Success', int $statusCode = 200): void
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    public static function error(string $message = 'Error', int $statusCode = 400, mixed $errors = null): void
    {
        self::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }

    public static function redirect(string $url, int $statusCode = 302): void
    {
        // Prepend base path for subfolder deployments (e.g. /hotel/hotel)
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            $base = Request::basePath();
            if ($base !== '' && !str_starts_with($url, $base)) {
                $url = $base . $url;
            }
        }

        http_response_code($statusCode);
        header("Location: {$url}");
        exit;
    }
}
