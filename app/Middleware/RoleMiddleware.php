<?php

namespace App\Middleware;

use App\Helpers\Request;
use App\Helpers\Response;
use App\Helpers\Session;

class RoleMiddleware
{
    private array $allowedRoles;

    public function __construct(array|string $roles = [])
    {
        $this->allowedRoles = is_array($roles) ? array_map('strtolower', $roles) : [strtolower($roles)];
    }

    public function handle(Request $request): void
    {
        $user = Session::user();
        if (!$user) {
            if ($request->isJson()) {
                Response::error('Authentication required.', 401);
            }
            Response::redirect('/login');
        }

        $userRole = strtolower($user['role'] ?? '');

        // Owner has superadmin access to everything
        if ($userRole === 'owner') {
            return;
        }

        if (!empty($this->allowedRoles) && !in_array($userRole, $this->allowedRoles, true)) {
            if ($request->isJson()) {
                Response::error('Forbidden: You do not have permission for this module.', 403);
            }
            Session::flash('error', 'Access denied.');
            Response::redirect('/dashboard');
        }
    }
}
