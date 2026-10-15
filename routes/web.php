<?php

use App\Helpers\Request;
use App\Helpers\Response;
use App\Helpers\View;
use App\Helpers\Session;
use App\Controllers\AuthController;
use App\Middleware\CsrfMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

/** @var \App\Router $router */

// Public Storefront Routes
$router->get('/', function (Request $request) {
    echo View::render('customer.home', [
        'title' => 'Home - Royal Dining & Authentic Biriyani',
    ], 'main');
});

$router->get('/health', function (Request $request) {
    Response::success([
        'status' => 'operational',
        'php_version' => PHP_VERSION,
        'timestamp' => gmdate('Y-m-d H:i:s'),
    ], 'Valima Biriyani Engine Operational');
});

// Authentication Routes
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login'], [CsrfMiddleware::class]);
$router->get('/logout', [AuthController::class, 'logout']);
$router->post('/logout', [AuthController::class, 'logout'], [CsrfMiddleware::class]);

// Protected Area Placeholders (Will be expanded in Phase 5-9)
$router->get('/admin', function (Request $request) {
    $user = Session::user();
    echo View::render('admin.dashboard_placeholder', [
        'title' => 'Admin Dashboard',
        'currentPage' => 'dashboard',
        'currentUser' => $user,
    ], 'admin');
}, [AuthMiddleware::class, new RoleMiddleware(['owner', 'manager'])]);

$router->get('/pos', function (Request $request) {
    $user = Session::user();
    echo "<h1>Point of Sale Billing - Staff: " . htmlspecialchars($user['name']) . "</h1><p><a href='/admin'>Admin</a> | <a href='/logout'>Sign Out</a></p>";
}, [AuthMiddleware::class, new RoleMiddleware(['owner', 'manager', 'cashier'])]);

$router->get('/kitchen', function (Request $request) {
    $user = Session::user();
    echo "<h1>Kitchen Display System - Staff: " . htmlspecialchars($user['name']) . "</h1><p><a href='/logout'>Sign Out</a></p>";
}, [AuthMiddleware::class, new RoleMiddleware(['owner', 'kitchen'])]);
