<?php

namespace App\Helpers;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $instance = null;
    private static array $config = [];

    public static function init(array $config): void
    {
        self::$config = $config;
        self::$instance = null;
    }

    public static function pdo(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        if (empty(self::$config)) {
            $configFile = __DIR__ . '/../../config/database.php';
            if (file_exists($configFile)) {
                self::$config = require $configFile;
            }
        }

        $driver = self::$config['default'] ?? 'mysql';
        $connection = self::$config['connections'][$driver] ?? null;

        if (!$connection) {
            throw new RuntimeException("Database driver [{$driver}] is not configured.");
        }

        try {
            if ($driver === 'sqlite') {
                $dbPath = $connection['database'];
                if ($dbPath !== ':memory:') {
                    if (!preg_match('/^([a-zA-Z]:|\/|\\\\)/', $dbPath)) {
                        $dbPath = __DIR__ . '/../../' . $dbPath;
                    }
                    $dir = dirname($dbPath);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                }
                $dsn = "sqlite:{$dbPath}";
                self::$instance = new PDO($dsn, null, null, $connection['options'] ?? []);
                self::$instance->exec('PRAGMA foreign_keys = ON;');
            } else {
                $host = $connection['host'];
                $port = $connection['port'];
                $database = $connection['database'];
                $charset = $connection['charset'] ?? 'utf8mb4';

                $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";
                self::$instance = new PDO(
                    $dsn,
                    $connection['username'],
                    $connection['password'],
                    $connection['options'] ?? []
                );
            }

            return self::$instance;
        } catch (PDOException $e) {
            Logger::error("Database connection failed: " . $e->getMessage());
            throw new RuntimeException("Database connection error. Please verify your environment configuration.");
        }
    }

    public static function query(string $sql, array $params = []): array
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public static function lastInsertId(): string|int
    {
        return self::pdo()->lastInsertId();
    }

    public static function beginTransaction(): bool
    {
        return self::pdo()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::pdo()->commit();
    }

    public static function rollBack(): bool
    {
        return self::pdo()->rollBack();
    }

    public static function transaction(callable $callback): mixed
    {
        self::beginTransaction();
        try {
            $result = $callback(self::pdo());
            self::commit();
            return $result;
        } catch (\Throwable $e) {
            if (self::pdo()->inTransaction()) {
                self::rollBack();
            }
            throw $e;
        }
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
