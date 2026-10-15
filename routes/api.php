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

// Customer Tracking API
$router->get('/api/track/{token}', [\App\Controllers\TrackingController::class, 'statusApi']);

// Authenticated Staff Customer Loyalty Balance API
$router->get('/api/customers/{id}/loyalty', function (Request $request, string $id) {
    $cid = (int)$id;
    $customer = \App\Helpers\Database::fetchOne("SELECT id, name, phone, loyalty_points FROM customers WHERE id = :id", [':id' => $cid]);
    if (!$customer) {
        \App\Helpers\Response::error('Customer not found.', 404);
        return;
    }
    $settings = \App\Services\LoyaltyService::getSettings();
    \App\Helpers\Response::success([
        'customer_id' => $cid,
        'name' => $customer['name'],
        'points' => (int)$customer['loyalty_points'],
        'value_in_inr' => round((int)$customer['loyalty_points'] * (float)$settings['rupee_per_point'], 2),
        'min_redemption_points' => $settings['min_redemption_points'],
        'can_redeem' => (int)$customer['loyalty_points'] >= $settings['min_redemption_points'],
    ]);
}, $staffAuth);

