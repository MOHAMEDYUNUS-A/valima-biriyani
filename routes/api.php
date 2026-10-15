<?php

use App\Helpers\Request;
use App\Helpers\Response;

/** @var \App\Router $router */

$router->get('/api/ping', function (Request $request) {
    Response::success(['pong' => true], 'Valima Biriyani API Online');
});
