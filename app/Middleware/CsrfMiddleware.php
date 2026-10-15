<?php

namespace App\Middleware;

use App\Helpers\Request;
use App\Helpers\Response;
use App\Helpers\Security;

class CsrfMiddleware
{
    public function handle(Request $request): void
    {
        $method = $request->method();

        // Safe methods don't require CSRF validation
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return;
        }

        // Exempt external webhooks which use signature verification instead
        $uri = $request->uri();
        if (str_starts_with($uri, '/api/webhooks/')) {
            return;
        }

        // Retrieve token from form field or request header
        $token = $request->input('_csrf_token') ?? $request->header('X_CSRF_TOKEN') ?? $request->header('X_XSRF_TOKEN');

        if (!Security::verifyCsrfToken($token)) {
            if ($request->isJson()) {
                Response::error('Invalid or expired CSRF token.', 419);
            }

            http_response_code(419);
            echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>419 - Page Expired</title>
    <style>
        body { font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; background: #fff8f5; color: #1c0a00; }
        .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); text-align: center; }
        a { color: #ff6b2c; font-weight: bold; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Session Expired (419)</h2>
        <p>Your session or CSRF token has expired. Please refresh the page and try again.</p>
        <p><a href="javascript:history.back()">Go Back</a> | <a href="/">Return Home</a></p>
    </div>
</body>
</html>
HTML;
            exit;
        }
    }
}
