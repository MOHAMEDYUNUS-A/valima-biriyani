<?php

declare(strict_types=1);

namespace Database;

use PDO;
use App\Helpers\Database;
use App\Helpers\Logger;
use RuntimeException;

class MigrationRunner
{
    private PDO $pdo;
    private string $driver;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::pdo();
        $this->driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $this->ensureMigrationsTable();
    }

    private function ensureMigrationsTable(): void
    {
        if ($this->driver === 'sqlite') {
            $sql = "
                CREATE TABLE IF NOT EXISTS migrations (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    migration VARCHAR(255) NOT NULL UNIQUE,
                    applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
            ";
        } else {
            $sql = "
                CREATE TABLE IF NOT EXISTS migrations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    migration VARCHAR(255) NOT NULL UNIQUE,
                    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ";
        }

        $this->pdo->exec($sql);
    }

    public function getAppliedMigrations(): array
    {
        $stmt = $this->pdo->query("SELECT migration FROM migrations ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    public function run(string $migrationsDir): array
    {
        $applied = $this->getAppliedMigrations();
        $files = glob(rtrim($migrationsDir, '/\\') . '/*.php');
        sort($files);

        $executed = [];

        foreach ($files as $file) {
            $filename = basename($file);
            if (in_array($filename, $applied, true)) {
                continue;
            }

            echo "Migrating: {$filename}...\n";
            require_once $file;

            $className = $this->resolveClassName($filename);
            if (!class_exists($className)) {
                throw new RuntimeException("Migration class [{$className}] not found in [{$filename}].");
            }

            /** @var Migration $migration */
            $migration = new $className($this->pdo, $this->driver);
            $migration->up();

            $stmt = $this->pdo->prepare("INSERT INTO migrations (migration, applied_at) VALUES (:migration, :applied_at)");
            $stmt->execute([
                ':migration' => $filename,
                ':applied_at' => gmdate('Y-m-d H:i:s'),
            ]);

            $executed[] = $filename;
            echo "Migrated:  {$filename} ✅\n";
        }

        return $executed;
    }

    private function resolveClassName(string $filename): string
    {
        // 001_create_core_auth_tables.php -> CreateCoreAuthTables
        $withoutExt = pathinfo($filename, PATHINFO_FILENAME);
        $parts = explode('_', $withoutExt);
        array_shift($parts); // remove prefix like 001
        $capitalized = array_map('ucfirst', $parts);
        return implode('', $capitalized);
    }
}
