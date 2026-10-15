<?php

declare(strict_types=1);

/**
 * Valima Biriyani - Front Controller
 * Free-Stack Core PHP 8.2+ Architecture
 */

// 1. Error Reporting Configuration
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Safe default; overridden if APP_DEBUG=true

// 2. PSR-4 Class Autoloader
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// 3. Load Environment Variables
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    \App\Helpers\Env::load($envPath);
}

// 4. Debug Mode & Error Display
$isDebug = (bool)\App\Helpers\Env::get('APP_DEBUG', false);
if ($isDebug) {
    ini_set('display_errors', '1');
}

// 5. Timezone Configuration
$timezone = \App\Helpers\Env::get('TIMEZONE', 'Asia/Kolkata');
date_default_timezone_set($timezone);

// 6. Start Secure Session
\App\Helpers\Session::start();

// 7. Initialize Database Connection Config
$dbConfig = require __DIR__ . '/../config/database.php';
\App\Helpers\Database::init($dbConfig);

// 8. Bootstrap Router & Request
$request = new \App\Helpers\Request();
$router = new \App\Router($request);

// 9. Load Web & API Route Definitions
$webRoutes = __DIR__ . '/../routes/web.php';
if (file_exists($webRoutes)) {
    require $webRoutes;
}

$apiRoutes = __DIR__ . '/../routes/api.php';
if (file_exists($apiRoutes)) {
    require $apiRoutes;
}

// 10. Dispatch
$router->dispatch();
