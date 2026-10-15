<?php

namespace App\Middleware;

use App\Helpers\Request;
use App\Helpers\Response;
use App\Helpers\Session;

class AuthMiddleware
{
    public function handle(Request $request): void
    {
        if (!Session::check()) {
            if ($request->isJson()) {
                Response::error('Authentication required.', 401);
            }

            Session::flash('error', 'Please log in to access this area.');
            Response::redirect('/login');
        }
    }
}
