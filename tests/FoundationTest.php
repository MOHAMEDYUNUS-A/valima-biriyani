<?php

declare(strict_types=1);

namespace Tests;

// Standalone test runner for Phase 2 Foundation
require_once __DIR__ . '/../app/Helpers/Env.php';
require_once __DIR__ . '/../app/Helpers/Database.php';
require_once __DIR__ . '/../app/Helpers/Security.php';
require_once __DIR__ . '/../app/Helpers/Request.php';
require_once __DIR__ . '/../app/Helpers/Response.php';
require_once __DIR__ . '/../app/Helpers/Session.php';
require_once __DIR__ . '/../app/Helpers/Logger.php';
require_once __DIR__ . '/../app/Helpers/View.php';
require_once __DIR__ . '/../app/Controllers/BaseController.php';
require_once __DIR__ . '/../app/Router.php';

use App\Helpers\Env;
use App\Helpers\Database;
use App\Helpers\Security;
use App\Helpers\Request;
use App\Helpers\Session;
use App\Helpers\View;
use App\Router;

class FoundationTest
{
    private int $passed = 0;
    private int $failed = 0;

    public function runAll(): void
    {
        echo "========================================\n";
        echo " RUNNING VALIMA BIRIYANI FOUNDATION TESTS\n";
        echo "========================================\n\n";

        $this->testEnvLoading();
        $this->testSecurityHelpers();
        $this->testPhoneNormalization();
        $this->testDatabaseConnection();
        $this->testRouterParameters();
        $this->testViewRendering();

        echo "\n----------------------------------------\n";
        echo "RESULTS: {$this->passed} Passed, {$this->failed} Failed\n";
        echo "----------------------------------------\n";

        if ($this->failed > 0) {
            exit(1);
        }
    }

    private function assert(string $name, bool $condition, string $msg = ''): void
    {
        if ($condition) {
            echo " [PASS] {$name}\n";
            $this->passed++;
        } else {
            echo " [FAIL] {$name} - {$msg}\n";
            $this->failed++;
        }
    }

    private function testEnvLoading(): void
    {
        Env::load(__DIR__ . '/../.env');
        $appName = Env::get('APP_NAME');
        $this->assert('Env Loads Correctly', $appName === 'Valima Biriyani', "Expected 'Valima Biriyani', got: " . var_export($appName, true));
        $this->assert('Env Casts Boolean', Env::get('APP_DEBUG') === true);
    }

    private function testSecurityHelpers(): void
    {
        Session::start();
        $token = Security::generateCsrfToken();
        $this->assert('CSRF Token Generated', strlen($token) === 64);
        $this->assert('CSRF Validates Matching Token', Security::verifyCsrfToken($token));
        $this->assert('CSRF Rejects Tampered Token', !Security::verifyCsrfToken('invalid-token-123'));

        $xss = '<script>alert("xss")</script>';
        $escaped = Security::e($xss);
        $this->assert('Output Escaping Works', !str_contains($escaped, '<script>'), "Escaped: {$escaped}");
    }

    private function testPhoneNormalization(): void
    {
        $p1 = Security::normalizePhone('9876543210');
        $this->assert('10-digit Indian Mobile Normalizes to +91', $p1 === '+919876543210', "Got: {$p1}");

        $p2 = Security::normalizePhone('09876543210');
        $this->assert('Leading 0 Mobile Normalizes to +91', $p2 === '+919876543210', "Got: {$p2}");

        $p3 = Security::normalizePhone('+91 98765 43210');
        $this->assert('Formatted Mobile Normalizes to +91', $p3 === '+919876543210', "Got: {$p3}");

        $this->assert('Phone Validation Accepts Valid Mobile', Security::isValidIndianPhone('9876543210'));
        $this->assert('Phone Validation Rejects Short Mobile', !Security::isValidIndianPhone('12345'));
    }

    private function testDatabaseConnection(): void
    {
        // Test SQLite in-memory / file PDO connection
        Database::init([
            'default' => 'sqlite',
            'connections' => [
                'sqlite' => [
                    'driver' => 'sqlite',
                    'database' => ':memory:',
                    'options' => [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    ],
                ],
            ],
        ]);

        $pdo = Database::pdo();
        $this->assert('Database PDO Instantiates', $pdo instanceof \PDO);

        Database::execute("CREATE TABLE test_items (id INTEGER PRIMARY KEY, name TEXT, price REAL)");
        Database::execute("INSERT INTO test_items (name, price) VALUES (:name, :price)", [':name' => 'Chicken Biriyani', ':price' => 190.00]);

        $item = Database::queryOne("SELECT * FROM test_items WHERE id = 1");
        $this->assert('Database Prepared Query Succeeds', $item !== null && $item['name'] === 'Chicken Biriyani');

        // Test transaction
        $transResult = Database::transaction(function ($p) {
            Database::execute("INSERT INTO test_items (name, price) VALUES (:name, :price)", [':name' => 'Mutton Biriyani', ':price' => 280.00]);
            return Database::queryOne("SELECT COUNT(*) as cnt FROM test_items")['cnt'];
        });
        $this->assert('Database Transaction Commits', (int)$transResult === 2);
    }

    private function testRouterParameters(): void
    {
        // Mock server variables for router test
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/track/abc123token456';

        $req = new Request();
        $router = new Router($req);

        $capturedToken = null;
        $router->get('/track/{token}', function (Request $r, string $token) use (&$capturedToken) {
            $capturedToken = $token;
        });

        $router->dispatch();
        $this->assert('Router Matches Named Parameter', $capturedToken === 'abc123token456', "Got: " . var_export($capturedToken, true));
    }

    private function testViewRendering(): void
    {
        $output = View::render('customer.home', [
            'title' => 'Test Title',
        ], 'main');

        $this->assert('View Renders HTML', str_contains($output, '<!DOCTYPE html>'));
        $this->assert('View Injects Global App Title', str_contains($output, 'Valima Biriyani'));
        $this->assert('View Contains Hero Title', str_contains($output, 'A Symphony of Spices'));
    }
}

// Execute tests
$test = new FoundationTest();
$test->runAll();
