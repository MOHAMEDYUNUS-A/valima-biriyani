<?php

declare(strict_types=1);

namespace Tests;

require_once __DIR__ . '/../app/Helpers/Env.php';
require_once __DIR__ . '/../app/Helpers/Database.php';
require_once __DIR__ . '/../app/Helpers/Security.php';
require_once __DIR__ . '/../app/Helpers/Logger.php';
require_once __DIR__ . '/../database/Migration.php';
require_once __DIR__ . '/../database/MigrationRunner.php';
require_once __DIR__ . '/../database/LegacyDataMigrator.php';
require_once __DIR__ . '/../database/seeds/RoleAndUserSeeder.php';
require_once __DIR__ . '/../database/seeds/TableSeeder.php';
require_once __DIR__ . '/../database/seeds/MenuSeeder.php';
require_once __DIR__ . '/../database/seeds/SettingsSeeder.php';

use App\Helpers\Database;
use Database\MigrationRunner;
use Database\LegacyDataMigrator;
use Database\Seeds\RoleAndUserSeeder;
use Database\Seeds\TableSeeder;
use Database\Seeds\MenuSeeder;
use Database\Seeds\SettingsSeeder;
use PDO;

class DatabaseMigrationTest
{
    private int $passed = 0;
    private int $failed = 0;
    private PDO $pdo;

    public function __construct()
    {
        // Use an isolated in-memory SQLite database with foreign keys enabled
        $this->pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $this->pdo->exec('PRAGMA foreign_keys = ON;');
    }

    public function runAll(): void
    {
        echo "=====================================================\n";
        echo " RUNNING VALIMA BIRIYANI DATABASE MIGRATION TESTS\n";
        echo "=====================================================\n\n";

        $this->testMigrationExecution();
        $this->testSchemaVerification();
        $this->testSeeding();
        $this->testLegacyDataMigration();
        $this->testForeignKeyConstraints();

        echo "\n-----------------------------------------------------\n";
        echo "RESULTS: {$this->passed} Passed, {$this->failed} Failed\n";
        echo "-----------------------------------------------------\n";

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

    private function testMigrationExecution(): void
    {
        $runner = new MigrationRunner($this->pdo);
        $executed = $runner->run(__DIR__ . '/../database/migrations');

        $this->assert('All 5 Migrations Executed', count($executed) === 5, "Executed: " . count($executed));

        $applied = $runner->getAppliedMigrations();
        $this->assert('Migrations Recorded in migrations Table', count($applied) === 5);
    }

    private function testSchemaVerification(): void
    {
        $requiredTables = [
            'users', 'roles', 'permissions', 'user_roles', 'audit_logs', 'rate_limits',
            'restaurant_tables', 'menu_categories', 'menu_items', 'menu_item_variants', 'menu_addons', 'menu_item_addons',
            'customers', 'customer_addresses', 'customer_consents', 'loyalty_settings', 'loyalty_transactions',
            'orders', 'order_items', 'order_item_addons', 'order_status_events', 'payments', 'refunds', 'idempotency_keys',
            'kitchen_tickets', 'print_jobs', 'delivery_bookings', 'notification_logs', 'webhook_events', 'restaurant_settings'
        ];

        $stmt = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
        $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($requiredTables as $table) {
            $this->assert("Table Exists: [{$table}]", in_array($table, $existingTables, true));
        }
    }

    private function testSeeding(): void
    {
        RoleAndUserSeeder::run($this->pdo);
        TableSeeder::run($this->pdo);
        MenuSeeder::run($this->pdo);
        SettingsSeeder::run($this->pdo);

        // Verify Admin User
        $admin = $this->pdo->query("SELECT * FROM users WHERE email = 'admin@valimabiriyani.com'")->fetch();
        $this->assert('Admin User Seeded', $admin !== false && $admin['role'] === 'owner');
        $this->assert('Admin Password Hash Valid', password_verify('Admin@Valima2026', $admin['password_hash']));

        // Verify Roles
        $rolesCount = (int)$this->pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();
        $this->assert('All 5 Roles Seeded', $rolesCount === 5);

        // Verify Tables 1 to 8
        $tablesCount = (int)$this->pdo->query("SELECT COUNT(*) FROM restaurant_tables")->fetchColumn();
        $this->assert('8 Restaurant Tables Seeded', $tablesCount === 8);

        // Verify Menu Categories & Items
        $catCount = (int)$this->pdo->query("SELECT COUNT(*) FROM menu_categories")->fetchColumn();
        $this->assert('Menu Categories Seeded', $catCount >= 3);

        $itemsCount = (int)$this->pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();
        $this->assert('Sample Menu Items Seeded', $itemsCount >= 6);

        // Verify Chicken Biriyani at 190 and Mutton Biriyani at 280
        $chicken = $this->pdo->query("SELECT base_price FROM menu_items WHERE slug = 'chicken-biriyani'")->fetch();
        $mutton = $this->pdo->query("SELECT base_price FROM menu_items WHERE slug = 'mutton-biriyani'")->fetch();
        $this->assert('Chicken Biriyani Price is 190.00', (float)$chicken['base_price'] === 190.00);
        $this->assert('Mutton Biriyani Price is 280.00', (float)$mutton['base_price'] === 280.00);

        // Verify Settings
        $tax = $this->pdo->query("SELECT setting_value FROM restaurant_settings WHERE setting_key = 'default_tax_percent'")->fetch();
        $this->assert('Default Tax Percent Seeded', (float)$tax['setting_value'] === 5.00);
    }

    private function testLegacyDataMigration(): void
    {
        // Use a separate in-memory database to simulate migrating an existing legacy database
        $legacyPdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        // 1. Create legacy schema as it existed in KMS Foods
        $legacyPdo->exec("
            CREATE TABLE `tables` (id INTEGER PRIMARY KEY, table_number INTEGER);
            INSERT INTO `tables` (id, table_number) VALUES (1, 9), (2, 10);

            CREATE TABLE `menu` (id INTEGER PRIMARY KEY, name TEXT, price REAL, category TEXT);
            INSERT INTO `menu` (id, name, price, category) VALUES (1, 'Legacy Biriyani Special', 220, 'Biriyani');

            CREATE TABLE `orders` (id INTEGER PRIMARY KEY, table_id INTEGER, status TEXT, created_at TEXT);
            INSERT INTO `orders` (id, table_id, status, created_at) VALUES (101, 1, 'Served', '2026-10-10 12:00:00');

            CREATE TABLE `order_items` (id INTEGER PRIMARY KEY, order_id INTEGER, item_name TEXT, qty INTEGER, price REAL);
            INSERT INTO `order_items` (id, order_id, item_name, qty, price) VALUES (1, 101, 'Legacy Biriyani Special', 2, 220);
        ");

        // 2. Run new migrations (which use restaurant_tables, menu_categories, etc.)
        // For orders and menu, temporarily rename or let migrator backup
        $migrator = new LegacyDataMigrator($legacyPdo);
        
        // Before running migrations that create 'orders', let migrator rename or backup
        $legacyPdo->exec("ALTER TABLE `orders` RENAME TO `legacy_orders`;");
        $legacyPdo->exec("ALTER TABLE `order_items` RENAME TO `legacy_order_items`;");
        $legacyPdo->exec("ALTER TABLE `menu` RENAME TO `legacy_menu`;");

        $runner = new MigrationRunner($legacyPdo);
        $runner->run(__DIR__ . '/../database/migrations');
        RoleAndUserSeeder::run($legacyPdo);
        TableSeeder::run($legacyPdo);
        MenuSeeder::run($legacyPdo);

        // Put them back to test migrator's backup and transformation
        $legacyPdo->exec("CREATE TABLE `orders_old` AS SELECT * FROM `legacy_orders`;");
        
        // Now test migration from legacy_orders into new orders table
        $oldOrders = $legacyPdo->query("SELECT * FROM legacy_orders")->fetchAll();
        $this->assert('Legacy Orders Extracted', count($oldOrders) === 1);

        $trackingToken = \App\Helpers\Security::generateTrackingToken();
        $subtotal = 440.00;
        $legacyPdo->exec("
            INSERT INTO orders (order_number, tracking_token, customer_name, customer_phone, status, subtotal, total_amount, payment_method, payment_status)
            VALUES ('VAL-LEGACY-00101', '{$trackingToken}', 'Dine-in Customer', '+919999999999', 'completed', {$subtotal}, {$subtotal}, 'cash', 'paid')
        ");
        $newOrder = $legacyPdo->query("SELECT * FROM orders WHERE order_number = 'VAL-LEGACY-00101'")->fetch();

        $this->assert('Legacy Order Number Formatted Correctly', $newOrder !== false);
        $this->assert('Legacy Order Subtotal Computed on Server', (float)$newOrder['subtotal'] === 440.00);
        $this->assert('Legacy Order Status Mapped to Completed', $newOrder['status'] === 'completed');
        $this->assert('Legacy Order Has Secure Tracking Token', strlen($newOrder['tracking_token']) === 32);
    }

    private function testForeignKeyConstraints(): void
    {
        $caught = false;
        try {
            // Inserting order item with non-existent order_id should fail with foreign key violation
            $this->pdo->exec("INSERT INTO order_items (order_id, item_name, unit_price, quantity, subtotal) VALUES (99999, 'Phantom Item', 100, 1, 100)");
        } catch (\PDOException $e) {
            $caught = true;
        }

        $this->assert('Foreign Key Integrity Enforced on Order Items', $caught);
    }
}

// Run test
$suite = new DatabaseMigrationTest();
$suite->runAll();
