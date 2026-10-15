<?php

use App\Helpers\Request;
use App\Helpers\Response;
use App\Helpers\View;
use App\Helpers\Session;
use App\Controllers\AuthController;
use App\Controllers\PublicController;
use App\Controllers\MenuController;
use App\Controllers\OrderController;
use App\Controllers\AdminMenuController;
use App\Controllers\AdminOrderController;
use App\Controllers\PosController;
use App\Controllers\KitchenController;
use App\Middleware\CsrfMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

/** @var \App\Router $router */

// ── Public Storefront Pages ──
$router->get('/', [PublicController::class, 'home']);
$router->get('/about', [PublicController::class, 'about']);
$router->get('/menu', [MenuController::class, 'index']);
$router->get('/contact', [PublicController::class, 'contact']);
$router->get('/hours-location', [PublicController::class, 'hoursAndLocation']);
$router->get('/faq', [PublicController::class, 'faq']);
$router->get('/privacy-policy', [PublicController::class, 'privacyPolicy']);
$router->get('/terms-of-service', [PublicController::class, 'termsOfService']);
$router->get('/refund-policy', [PublicController::class, 'refundPolicy']);
$router->get('/delivery-policy', [PublicController::class, 'deliveryPolicy']);

// ── Dine-in Table Flow & Checkout ──
$router->get('/dine-in', [OrderController::class, 'dineIn']);
$router->post('/dine-in', [OrderController::class, 'startDineIn'], [CsrfMiddleware::class]);
$router->get('/checkout', [OrderController::class, 'checkout']);
$router->post('/orders', [OrderController::class, 'store'], [CsrfMiddleware::class]);

$router->get('/health', function (Request $request) {
    Response::success([
        'status' => 'operational',
        'php_version' => PHP_VERSION,
        'timestamp' => gmdate('Y-m-d H:i:s'),
    ], 'Valima Biriyani Engine Operational');
});

// ── Staff Authentication ──
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login'], [CsrfMiddleware::class]);
$router->get('/logout', [AuthController::class, 'logout']);
$router->post('/logout', [AuthController::class, 'logout'], [CsrfMiddleware::class]);

// ── Admin Protected Console ──
$adminAuth = [AuthMiddleware::class, new RoleMiddleware(['owner', 'manager'])];
$staffAuth = [AuthMiddleware::class, new RoleMiddleware(['owner', 'manager', 'cashier'])];

// Admin Dashboard
$router->get('/admin', function (Request $request) {
    $user = Session::user();
    echo View::render('admin.dashboard', [
        'title' => 'Admin Dashboard',
        'currentPage' => 'dashboard',
        'currentUser' => $user,
    ], 'admin');
}, $adminAuth);

// Admin Central Orders Management
$router->get('/admin/orders', [AdminOrderController::class, 'index'], $adminAuth);
$router->get('/admin/orders/{id}', [AdminOrderController::class, 'show'], $adminAuth);
$router->post('/admin/orders/{id}/status', [AdminOrderController::class, 'updateStatus'], array_merge($adminAuth, [CsrfMiddleware::class]));

// Admin Menu Management
$router->get('/admin/menu', [AdminMenuController::class, 'index'], $adminAuth);
$router->get('/admin/menu/create', [AdminMenuController::class, 'createItem'], $adminAuth);
$router->post('/admin/menu/store', [AdminMenuController::class, 'storeItem'], array_merge($adminAuth, [CsrfMiddleware::class]));
$router->get('/admin/menu/{id}/edit', [AdminMenuController::class, 'editItem'], $adminAuth);
$router->post('/admin/menu/{id}/update', [AdminMenuController::class, 'updateItem'], array_merge($adminAuth, [CsrfMiddleware::class]));
$router->post('/admin/menu/{id}/toggle', [AdminMenuController::class, 'toggleAvailability'], array_merge($adminAuth, [CsrfMiddleware::class]));
$router->post('/admin/menu/{id}/delete', [AdminMenuController::class, 'deleteItem'], array_merge($adminAuth, [CsrfMiddleware::class]));

// ── Point-of-Sale (POS) Terminal ──
$router->get('/pos', [PosController::class, 'terminal'], $staffAuth);
$router->get('/pos/receipt/{id}', [PosController::class, 'receipt'], $staffAuth);

// ── Kitchen Display System (KDS) & KOT ──
$kitchenAuth = [AuthMiddleware::class, new RoleMiddleware(['owner', 'manager', 'kitchen'])];
$router->get('/kitchen', [KitchenController::class, 'display'], $kitchenAuth);
$router->get('/kitchen/kot/{id}', [KitchenController::class, 'kot'], [AuthMiddleware::class, new RoleMiddleware(['owner', 'manager', 'kitchen', 'cashier'])]);
