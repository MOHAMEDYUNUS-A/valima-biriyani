<?php

declare(strict_types=1);

/**
 * Valima Biriyani - CLI Database Migration & Seeding Runner
 */

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

use App\Helpers\Env;
use App\Helpers\Database;
use Database\MigrationRunner;
use Database\LegacyDataMigrator;
use Database\Seeds\RoleAndUserSeeder;
use Database\Seeds\TableSeeder;
use Database\Seeds\MenuSeeder;
use Database\Seeds\SettingsSeeder;

$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    Env::load($envPath);
}

$dbConfig = require __DIR__ . '/../config/database.php';
Database::init($dbConfig);
$pdo = Database::pdo();
$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

echo "=====================================================\n";
echo " VALIMA BIRIYANI DATABASE MIGRATION ENGINE\n";
echo " Target Driver: [{$driver}]\n";
echo "=====================================================\n\n";

$runner = new MigrationRunner($pdo);
$migrationsDir = __DIR__ . '/../database/migrations';
$applied = $runner->run($migrationsDir);

if (empty($applied)) {
    echo "No new migrations to apply. Database is up to date.\n";
}

// Check flags
$runSeed = in_array('--seed', $argv, true);
$migrateLegacy = in_array('--migrate-legacy', $argv, true);

if ($runSeed || empty($applied) === false) {
    echo "\nSeeding initial data...\n";
    RoleAndUserSeeder::run($pdo);
    echo " -> Roles and Admin User seeded ✅\n";
    TableSeeder::run($pdo);
    echo " -> Tables (1–8) seeded ✅\n";
    MenuSeeder::run($pdo);
    echo " -> Categories, Menu Items & Addons seeded ✅\n";
    SettingsSeeder::run($pdo);
    echo " -> Business Settings & Loyalty Rules seeded ✅\n";
}

if ($migrateLegacy) {
    echo "\nRunning Legacy Data Migration...\n";
    $migrator = new LegacyDataMigrator($pdo);
    $rep = $migrator->migrate();
    echo " -> Legacy Migration Complete:\n";
    echo "    - Tables Migrated: {$rep['tables_migrated']}\n";
    echo "    - Menu Items Migrated: {$rep['menu_items_migrated']}\n";
    echo "    - Orders Migrated: {$rep['orders_migrated']}\n";
    echo "    - Order Items Migrated: {$rep['order_items_migrated']}\n";
}

echo "\nMigration and initialization completed successfully! 🎉\n";
