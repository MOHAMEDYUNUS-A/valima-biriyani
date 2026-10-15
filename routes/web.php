<?php

use App\Helpers\Request;
use App\Helpers\Response;
use App\Helpers\View;

/** @var \App\Router $router */

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
