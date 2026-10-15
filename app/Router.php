<?php

namespace App;

use App\Helpers\Request;
use App\Helpers\Response;
use App\Helpers\Logger;
use App\Helpers\View;
use Throwable;

class Router
{
    private array $routes = [];
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function get(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    private function addRoute(string $method, string $path, array|callable $handler, array $middleware): void
    {
        $normalizedPath = '/' . trim($path, '/');
        if ($normalizedPath === '//') {
            $normalizedPath = '/';
        }

        // Convert route pattern with {param} to regex
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $normalizedPath);
        $regex = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => $method,
            'path' => $normalizedPath,
            'regex' => $regex,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(): void
    {
        $method = $this->request->method();
        // Check for _method override in POST forms (PUT/DELETE support)
        if ($method === 'POST') {
            $override = $this->request->input('_method');
            if ($override && in_array(strtoupper($override), ['PUT', 'DELETE', 'PATCH'], true)) {
                $method = strtoupper($override);
            }
        }

        $uri = $this->request->uri();
        $matchedRoute = null;
        $matchedParams = [];
        $methodAllowed = false;

        foreach ($this->routes as $route) {
            if (preg_match($route['regex'], $uri, $matches)) {
                if ($route['method'] === $method) {
                    $matchedRoute = $route;
                    // Extract named parameters
                    foreach ($matches as $key => $val) {
                        if (is_string($key)) {
                            $matchedParams[$key] = $val;
                        }
                    }
                    break;
                }
                $methodAllowed = true;
            }
        }

        // Smart Subdirectory Fallback: If no route matched directly, check if URI has a subfolder prefix (e.g. /hotel/hotel/install)
        if (!$matchedRoute) {
            foreach ($this->routes as $route) {
                if ($route['path'] !== '/' && str_ends_with($uri, $route['path'])) {
                    $prefix = substr($uri, 0, strlen($uri) - strlen($route['path']));
                    $strippedUri = substr($uri, strlen($prefix));
                    if (preg_match($route['regex'], $strippedUri, $matches)) {
                        if ($route['method'] === $method) {
                            $matchedRoute = $route;
                            foreach ($matches as $key => $val) {
                                if (is_string($key)) {
                                    $matchedParams[$key] = $val;
                                }
                            }
                            break;
                        }
                        $methodAllowed = true;
                    }
                }
            }
        }

        if (!$matchedRoute) {
            if ($methodAllowed) {
                if ($this->request->isJson()) {
                    Response::error('Method Not Allowed', 405);
                }
                http_response_code(405);
                echo "<h1>405 Method Not Allowed</h1>";
                return;
            }

            if ($this->request->isJson()) {
                Response::error('Endpoint Not Found', 404);
            }
            http_response_code(404);
            echo $this->renderErrorPage(404, 'Page Not Found', 'The requested URL was not found on this server.');
            return;
        }

        try {
            // Execute route middleware stack
            foreach ($matchedRoute['middleware'] as $mw) {
                if (is_string($mw)) {
                    $mwInstance = new $mw();
                    $mwInstance->handle($this->request);
                } elseif (is_callable($mw)) {
                    $mw($this->request);
                }
            }

            // Execute handler
            $handler = $matchedRoute['handler'];
            if (is_callable($handler)) {
                call_user_func_array($handler, array_merge([$this->request], $matchedParams));
            } elseif (is_array($handler) && count($handler) === 2) {
                [$controllerClass, $action] = $handler;
                $controller = new $controllerClass($this->request);
                call_user_func_array([$controller, $action], array_merge([$this->request], $matchedParams));
            }
        } catch (Throwable $e) {
            Logger::error("Unhandled exception: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            $isDebug = (bool)\App\Helpers\Env::get('APP_DEBUG', false);

            if ($this->request->isJson()) {
                Response::error(
                    $isDebug ? $e->getMessage() : 'An internal server error occurred.',
                    500,
                    $isDebug ? ['file' => $e->getFile(), 'line' => $e->getLine()] : null
                );
            }

            http_response_code(500);
            $details = $isDebug ? "<pre style='background:#f4f4f4;padding:15px;border-radius:8px;font-size:12px;overflow-x:auto;'>" . htmlspecialchars($e->getMessage() . "\n" . $e->getTraceAsString()) . "</pre>" : "We are experiencing a temporary error. Please try again in a few moments.";
            echo $this->renderErrorPage(500, 'Server Error', $details);
        }
    }

    private function renderErrorPage(int $code, string $title, string $message): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$code} - {$title} | Valima Biriyani</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0; padding: 0; font-family: 'DM Sans', sans-serif;
            background: #fff8f5; color: #1c0a00;
            display: flex; align-items: center; justify-content: center; min-height: 100vh;
        }
        .box {
            text-align: center; max-width: 500px; padding: 40px 30px;
            background: #fff; border-radius: 16px; box-shadow: 0 10px 40px rgba(255,107,44,0.12);
            border: 1px solid #f0ddd5;
        }
        h1 { font-family: 'Poppins', sans-serif; font-size: 64px; margin: 0; color: #ff6b2c; }
        h2 { font-family: 'Poppins', sans-serif; margin: 10px 0 15px; font-size: 22px; }
        p { color: #7a5c4e; line-height: 1.6; font-size: 14px; margin-bottom: 25px; }
        a {
            display: inline-block; background: #ff6b2c; color: #fff;
            padding: 12px 24px; border-radius: 8px; text-decoration: none;
            font-weight: 600; font-size: 14px; transition: background 0.2s;
        }
        a:hover { background: #e05520; }
    </style>
</head>
<body>
    <div class="box">
        <h1>{$code}</h1>
        <h2>{$title}</h2>
        <div>{$message}</div>
        <p><a href="/">Return to Home</a></p>
    </div>
</body>
</html>
HTML;
    }
}
