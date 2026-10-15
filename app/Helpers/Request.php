<?php

namespace App\Helpers;

class Request
{
    private array $get;
    private array $post;
    private array $server;
    private ?array $json = null;

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Detect application base path (e.g. '' for root or '/hotel/hotel' when in a subdirectory).
     */
    public static function basePath(): string
    {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $dir = str_replace('\\', '/', dirname($script));
        $base = preg_replace('#/public/?$#i', '', $dir);

        return ($base === '/' || $base === '.' || $base === null) ? '' : rtrim($base, '/');
    }

    public function uri(): string
    {
        $rawUri = $this->server['REQUEST_URI'] ?? '/';
        $path = parse_url($rawUri, PHP_URL_PATH) ?? '/';

        // 1. Strip detected base directory (e.g. /hotel/hotel)
        $base = self::basePath();
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        // 2. If URL contains /public prefix, strip it
        if (str_starts_with($path, '/public')) {
            $path = substr($path, 7);
        }

        return rtrim($path, '/') ?: '/';
    }

    public function input(string $key, mixed $default = null): mixed
    {
        $all = $this->all();
        return $all[$key] ?? $default;
    }

    public function all(): array
    {
        $json = $this->json();
        if ($json !== null) {
            return array_merge($this->get, $this->post, $json);
        }
        return array_merge($this->get, $this->post);
    }

    public function json(): ?array
    {
        if ($this->json !== null) {
            return $this->json;
        }

        $contentType = $this->server['CONTENT_TYPE'] ?? '';
        if (str_contains(strtolower($contentType), 'application/json')) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            $this->json = is_array($decoded) ? $decoded : [];
            return $this->json;
        }

        return null;
    }

    public function rawBody(): string
    {
        return file_get_contents('php://input');
    }

    public function header(string $name, mixed $default = null): mixed
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $this->server[$key] ?? $default;
    }

    public function ip(): string
    {
        if (!empty($this->server['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', $this->server['HTTP_X_FORWARDED_FOR']);
            return trim($parts[0]);
        }
        return $this->server['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public function isJson(): bool
    {
        $contentType = $this->server['CONTENT_TYPE'] ?? '';
        $accept = $this->server['HTTP_ACCEPT'] ?? '';
        return str_contains(strtolower($contentType), 'application/json') ||
               str_contains(strtolower($accept), 'application/json');
    }
}
