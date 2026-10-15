<?php

use App\Helpers\Request;
use App\Helpers\Response;
use App\Models\MenuCategory;
use App\Models\MenuItem;

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
