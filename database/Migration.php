<?php

declare(strict_types=1);

namespace Database;

use PDO;

abstract class Migration
{
    protected PDO $pdo;
    protected string $driver;

    public function __construct(PDO $pdo, string $driver)
    {
        $this->pdo = $pdo;
        $this->driver = $driver;
    }

    abstract public function up(): void;
    abstract public function down(): void;

    protected function isSqlite(): bool
    {
        return $this->driver === 'sqlite';
    }

    protected function execute(string $sql): void
    {
        $this->pdo->exec($sql);
    }
}
