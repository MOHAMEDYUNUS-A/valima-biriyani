<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Request;
use App\Helpers\Response;
use App\Helpers\Session;
use App\Helpers\Security;
use App\Helpers\Logger;
use App\Models\User;

class AuthController extends BaseController
{
    public function showLogin(Request $request): void
    {
        if (Session::check()) {
            $this->redirectBasedOnRole(Session::user()['role'] ?? 'owner');
        }

        $this->render('auth.login', [
            'title' => 'Staff Login',
        ]);
    }

    public function login(Request $request): void
    {
        $email = strtolower(trim((string)$request->input('email', '')));
        $password = (string)$request->input('password', '');
        $ip = $request->ip();

        if (empty($email) || empty($password)) {
            Session::flash('error', 'Please provide both email and password.');
            $this->redirect('/login');
        }

        $rateKey = "login_attempts_{$ip}_" . md5($email);
        if (User::isRateLimited($rateKey, 5)) {
            Logger::warning("Rate limit exceeded for login attempt", ['email' => $email, 'ip' => $ip]);
            Session::flash('error', 'Too many failed login attempts. Please wait 5 minutes before trying again.');
            $this->redirect('/login');
        }

        $user = User::findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $attempts = User::recordFailedLogin($rateKey, 300);
            Logger::warning("Failed login attempt", ['email' => $email, 'ip' => $ip, 'attempt' => $attempts]);
            Session::flash('error', 'Invalid email or password.');
            $this->redirect('/login');
        }

        // Login Successful
        User::clearRateLimit($rateKey);
        User::updateLastLogin((int)$user['id']);

        // Secure Session Regeneration
        Session::regenerate();

        $sessionUser = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
        Session::set('user', $sessionUser);

        Logger::audit('USER_LOGIN', $user['name'], [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
        ]);

        Session::flash('success', "Welcome back, {$user['name']}!");
        $this->redirectBasedOnRole($user['role']);
    }

    public function logout(Request $request): void
    {
        $currentUser = Session::user();
        if ($currentUser) {
            Logger::audit('USER_LOGOUT', $currentUser['name'], [
                'user_id' => $currentUser['id'],
            ]);
        }

        Session::destroy();
        Session::start();
        Session::flash('success', 'You have been successfully signed out.');
        $this->redirect('/login');
    }

    private function redirectBasedOnRole(string $role): void
    {
        match (strtolower($role)) {
            'kitchen' => $this->redirect('/kitchen'),
            'cashier' => $this->redirect('/pos'),
            'delivery' => $this->redirect('/delivery'),
            default => $this->redirect('/admin'),
        };
    }
}
