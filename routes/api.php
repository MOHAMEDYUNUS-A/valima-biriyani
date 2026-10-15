<?php

use App\Helpers\Request;
use App\Helpers\Response;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Controllers\OrderController;
use App\Controllers\PosController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

/** @var \App\Router $router */

$router->get('/api/ping', function (Request $request) {
    Response::success(['pong' => true], 'Valima Biriyani API Online');
});

// Public Menu APIs
$router->get('/api/menu/categories', function (Request $request) {
    $categories = MenuCategory::allActive();
    Response::success($categories, 'Active categories retrieved.');
});

$router->get('/api/menu', function (Request $request) {
    $items = MenuItem::allActive();
    Response::success($items, 'Active menu items retrieved.');
});

// Customer Order APIs
$router->post('/api/orders/calculate', [OrderController::class, 'calculate']);
$router->post('/api/orders', [OrderController::class, 'store']);

// Authenticated POS APIs
$staffAuth = [AuthMiddleware::class, new RoleMiddleware(['owner', 'manager', 'cashier'])];
$router->get('/api/pos/customers/search', [PosController::class, 'searchCustomers'], $staffAuth);
$router->post('/api/pos/orders', [PosController::class, 'storeOrder'], $staffAuth);

// Kitchen APIs
$kitchenAuth = [AuthMiddleware::class, new RoleMiddleware(['owner', 'manager', 'kitchen'])];
$router->get('/api/kitchen/stream', [\App\Controllers\KitchenController::class, 'stream'], $kitchenAuth);
$router->post('/api/kitchen/orders/{id}/status', [\App\Controllers\KitchenController::class, 'updateStatus'], $kitchenAuth);

