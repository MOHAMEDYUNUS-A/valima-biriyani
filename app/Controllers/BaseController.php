<?php

namespace App\Controllers;

use App\Helpers\Request;
use App\Helpers\Response;
use App\Helpers\View;
use App\Helpers\Session;

abstract class BaseController
{
    protected Request $request;

    public function __construct(?Request $request = null)
    {
        $this->request = $request ?? new Request();
    }

    protected function render(string $view, array $data = [], ?string $layout = null): void
    {
        echo View::render($view, $data, $layout);
        exit;
    }

    protected function json(mixed $data, int $statusCode = 200): void
    {
        Response::json($data, $statusCode);
    }

    protected function success(mixed $data = null, string $message = 'Success', int $statusCode = 200): void
    {
        Response::success($data, $message, $statusCode);
    }

    protected function error(string $message = 'Error', int $statusCode = 400, mixed $errors = null): void
    {
        Response::error($message, $statusCode, $errors);
    }

    protected function redirect(string $url, int $statusCode = 302): void
    {
        Response::redirect($url, $statusCode);
    }

    protected function requireAuth(?string $requiredRole = null): array
    {
        $user = Session::user();
        if (!$user) {
            if ($this->request->isJson()) {
                $this->error('Authentication required', 401);
            }
            Session::flash('error', 'Please log in to continue.');
            $this->redirect('/login');
        }

        if ($requiredRole !== null) {
            $userRole = strtolower($user['role'] ?? '');
            if ($userRole !== 'owner' && $userRole !== strtolower($requiredRole)) {
                if ($this->request->isJson()) {
                    $this->error('Access denied. Insufficient permissions.', 403);
                }
                Session::flash('error', 'Access denied.');
                $this->redirect('/admin');
            }
        }

        return $user;
    }

    protected function requireRoles(array $roles): array
    {
        $user = Session::user();
        if (!$user) {
            if ($this->request->isJson()) {
                $this->error('Authentication required', 401);
            }
            Session::flash('error', 'Please log in to continue.');
            $this->redirect('/login');
        }

        $userRole = strtolower($user['role'] ?? '');
        $allowed = array_map('strtolower', $roles);
        if ($userRole !== 'owner' && !in_array($userRole, $allowed, true)) {
            if ($this->request->isJson()) {
                $this->error('Access denied. Insufficient permissions.', 403);
            }
            Session::flash('error', 'Access denied.');
            $this->redirect('/admin');
        }

        return $user;
    }
}
