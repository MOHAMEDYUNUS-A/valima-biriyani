<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Request;
use App\Helpers\Response;
use App\Helpers\Security;
use App\Helpers\View;
use Database\MigrationRunner;
use Database\Seeds\RoleAndUserSeeder;
use Database\Seeds\TableSeeder;
use Database\Seeds\MenuSeeder;
use Database\Seeds\SettingsSeeder;
use PDO;
use Exception;

class InstallController extends BaseController
{
    private string $lockFile;

    public function __construct()
    {
        $this->lockFile = dirname(__DIR__, 2) . '/storage/installed.lock';
    }

    /**
     * Display the installation and database setup wizard.
     */
    public function index(Request $request): void
    {
        if (file_exists($this->lockFile)) {
            $this->render('install.locked', [
                'title' => 'Valima Biriyani — Already Installed',
                'lockDate' => date('Y-m-d H:i:s', (int)filemtime($this->lockFile)),
            ]);
            return;
        }

        $systemChecks = $this->runSystemChecks();

        $this->render('install.index', [
            'title' => 'Valima Biriyani — Hostinger Installation & Database Setup',
            'checks' => $systemChecks,
            'allChecksPass' => $systemChecks['all_passed'],
            'errors' => [],
            'old' => [
                'db_host' => 'localhost',
                'db_port' => '3306',
                'db_name' => '',
                'db_user' => '',
                'store_name' => 'Valima Biriyani',
                'store_phone' => '+91 98765 43210',
                'admin_name' => 'Admin Owner',
                'admin_email' => 'admin@valimabiriyani.com',
            ],
        ]);
    }

    /**
     * Process installation: test connection, write .env, run migrations & seeders, lock installer.
     */
    public function setup(Request $request): void
    {
        if (file_exists($this->lockFile)) {
            Response::redirect('/login');
            return;
        }

        $dbConnection = trim($request->input('db_connection', 'mysql'));
        $dbHost = trim($request->input('db_host', 'localhost'));
        $dbPort = trim($request->input('db_port', '3306'));
        $dbName = trim($request->input('db_name', ''));
        $dbUser = trim($request->input('db_user', ''));
        $dbPass = (string)$request->input('db_pass', '');

        $storeName = trim($request->input('store_name', 'Valima Biriyani'));
        $storePhone = trim($request->input('store_phone', '+91 98765 43210'));

        $adminName = trim($request->input('admin_name', 'Admin Owner'));
        $adminEmail = trim($request->input('admin_email', 'admin@valimabiriyani.com'));
        $adminPass = (string)$request->input('admin_pass', '');
        $adminConfirm = (string)$request->input('admin_confirm', '');

        $seedSampleData = (bool)$request->input('seed_data', true);

        $errors = [];

        if ($dbConnection === 'mysql') {
            if (empty($dbName)) {
                $errors[] = 'Database Name is required.';
            }
            if (empty($dbUser)) {
                $errors[] = 'Database Username is required.';
            }
        }

        if (empty($adminEmail) || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid Admin Email address is required.';
        }
        if (strlen($adminPass) < 8) {
            $errors[] = 'Admin password must be at least 8 characters long.';
        }
        if ($adminPass !== $adminConfirm) {
            $errors[] = 'Admin passwords do not match.';
        }

        // Test database connectivity
        $pdo = null;
        if (empty($errors)) {
            try {
                if ($dbConnection === 'sqlite') {
                    $sqlitePath = dirname(__DIR__, 2) . '/storage/' . ($dbName ?: 'database.sqlite');
                    $pdo = new PDO("sqlite:{$sqlitePath}", null, null, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]);
                } else {
                    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
                    $pdo = new PDO($dsn, $dbUser, $dbPass, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_TIMEOUT => 5,
                    ]);
                }
            } catch (Exception $e) {
                $errors[] = 'Database connection failed: ' . $e->getMessage() . '. Please verify your Hostinger database name, user, and password in cPanel/hPanel.';
            }
        }

        if (!empty($errors)) {
            $systemChecks = $this->runSystemChecks();
            $this->render('install.index', [
                'title' => 'Installation Error — Valima Biriyani',
                'checks' => $systemChecks,
                'allChecksPass' => $systemChecks['all_passed'],
                'errors' => $errors,
                'old' => [
                    'db_host' => $dbHost,
                    'db_port' => $dbPort,
                    'db_name' => $dbName,
                    'db_user' => $dbUser,
                    'store_name' => $storeName,
                    'store_phone' => $storePhone,
                    'admin_name' => $adminName,
                    'admin_email' => $adminEmail,
                ],
            ]);
            return;
        }

        // Execute migrations
        try {
            require_once dirname(__DIR__, 2) . '/database/Migration.php';
            require_once dirname(__DIR__, 2) . '/database/MigrationRunner.php';

            $runner = new MigrationRunner($pdo);
            $migrationsDir = dirname(__DIR__, 2) . '/database/migrations';
            $runner->run($migrationsDir);

            // Execute seeders if requested
            if ($seedSampleData) {
                require_once dirname(__DIR__, 2) . '/database/seeds/RoleAndUserSeeder.php';
                require_once dirname(__DIR__, 2) . '/database/seeds/TableSeeder.php';
                require_once dirname(__DIR__, 2) . '/database/seeds/MenuSeeder.php';
                require_once dirname(__DIR__, 2) . '/database/seeds/SettingsSeeder.php';

                RoleAndUserSeeder::run($pdo);
                TableSeeder::run($pdo);
                MenuSeeder::run($pdo);
                SettingsSeeder::run($pdo);
            }

            // Create or update Super Admin user
            $hashedPass = password_hash($adminPass, PASSWORD_BCRYPT);
            $now = gmdate('Y-m-d H:i:s');

            $existingAdmin = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $existingAdmin->execute([':email' => $adminEmail]);
            $adminRow = $existingAdmin->fetch(PDO::FETCH_ASSOC);

            if ($adminRow) {
                $upd = $pdo->prepare("UPDATE users SET password_hash = :hash, name = :name WHERE id = :id");
                $upd->execute([':hash' => $hashedPass, ':name' => $adminName, ':id' => $adminRow['id']]);
            } else {
                $ins = $pdo->prepare("
                    INSERT INTO users (name, email, password_hash, status, created_at, updated_at)
                    VALUES (:name, :email, :hash, 'active', :dt, :dt)
                ");
                $ins->execute([
                    ':name' => $adminName,
                    ':email' => $adminEmail,
                    ':hash' => $hashedPass,
                    ':dt' => $now,
                ]);
                $newUserId = (int)$pdo->lastInsertId();

                // Assign 'owner' role
                $ownerRole = $pdo->query("SELECT id FROM roles WHERE name = 'owner'")->fetch(PDO::FETCH_ASSOC);
                if ($ownerRole) {
                    $assign = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (:uid, :rid)");
                    $assign->execute([':uid' => $newUserId, ':rid' => $ownerRole['id']]);
                }
            }

            // Write .env configuration file
            $this->writeEnvFile([
                'APP_ENV' => 'production',
                'APP_DEBUG' => 'false',
                'DB_CONNECTION' => $dbConnection,
                'DB_HOST' => $dbHost,
                'DB_PORT' => $dbPort,
                'DB_DATABASE' => $dbName,
                'DB_USERNAME' => $dbUser,
                'DB_PASSWORD' => $dbPass,
                'APP_URL' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
            ]);

            // Create installation lock file
            file_put_contents($this->lockFile, json_encode([
                'installed_at' => gmdate('Y-m-d H:i:s'),
                'admin_email' => $adminEmail,
                'driver' => $dbConnection,
                'version' => '1.0.0',
            ], JSON_PRETTY_PRINT));

            $this->render('install.success', [
                'title' => 'Installation Complete — Valima Biriyani',
                'adminEmail' => $adminEmail,
                'storeName' => $storeName,
            ]);

        } catch (Exception $e) {
            $systemChecks = $this->runSystemChecks();
            $this->render('install.index', [
                'title' => 'Installation Failed — Valima Biriyani',
                'checks' => $systemChecks,
                'allChecksPass' => $systemChecks['all_passed'],
                'errors' => ['An unexpected error occurred during database migration: ' . $e->getMessage()],
                'old' => [
                    'db_host' => $dbHost,
                    'db_port' => $dbPort,
                    'db_name' => $dbName,
                    'db_user' => $dbUser,
                    'store_name' => $storeName,
                    'store_phone' => $storePhone,
                    'admin_name' => $adminName,
                    'admin_email' => $adminEmail,
                ],
            ]);
        }
    }

    /**
     * Run system prerequisites checks.
     */
    private function runSystemChecks(): array
    {
        $phpVersion = PHP_VERSION;
        $phpPass = version_compare($phpVersion, '8.2.0', '>=');

        $requiredExtensions = ['pdo', 'curl', 'mbstring', 'openssl', 'fileinfo'];
        $extensionStatus = [];
        $allExtensionsPass = true;

        foreach ($requiredExtensions as $ext) {
            $loaded = extension_loaded($ext);
            $extensionStatus[$ext] = $loaded;
            if (!$loaded) {
                $allExtensionsPass = false;
            }
        }

        // MySQL PDO check
        $pdoMysql = extension_loaded('pdo_mysql') || extension_loaded('pdo_sqlite');

        $rootDir = dirname(__DIR__, 2);
        $storageDir = $rootDir . '/storage';
        $envFile = $rootDir . '/.env';

        $storageWritable = is_writable($storageDir);
        $envWritable = file_exists($envFile) ? is_writable($envFile) : is_writable($rootDir);

        $allPassed = $phpPass && $allExtensionsPass && $pdoMysql && $storageWritable && $envWritable;

        return [
            'php_version' => $phpVersion,
            'php_pass' => $phpPass,
            'extensions' => $extensionStatus,
            'pdo_mysql' => $pdoMysql,
            'storage_writable' => $storageWritable,
            'env_writable' => $envWritable,
            'all_passed' => $allPassed,
        ];
    }

    /**
     * Write or update .env file with production credentials.
     */
    private function writeEnvFile(array $values): void
    {
        $envFile = dirname(__DIR__, 2) . '/.env';
        $content = '';

        if (file_exists($envFile)) {
            $content = file_get_contents($envFile) ?: '';
            foreach ($values as $k => $v) {
                if (preg_match("/^{$k}=.*/m", $content)) {
                    $content = preg_replace("/^{$k}=.*/m", "{$k}={$v}", $content);
                } else {
                    $content .= "\n{$k}={$v}";
                }
            }
        } else {
            $exampleFile = dirname(__DIR__, 2) . '/.env.example';
            if (file_exists($exampleFile)) {
                $content = file_get_contents($exampleFile) ?: '';
                foreach ($values as $k => $v) {
                    $content = preg_replace("/^{$k}=.*/m", "{$k}={$v}", $content);
                }
            } else {
                foreach ($values as $k => $v) {
                    $content .= "{$k}={$v}\n";
                }
            }
        }

        file_put_contents($envFile, trim($content) . "\n");
    }
}
