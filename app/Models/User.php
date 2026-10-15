<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Database;

class User
{
    public static function findByEmail(string $email): ?array
    {
        return Database::queryOne(
            "SELECT * FROM users WHERE email = :email AND status = 'active' LIMIT 1",
            [':email' => strtolower(trim($email))]
        );
    }

    public static function findById(int $id): ?array
    {
        return Database::queryOne(
            "SELECT * FROM users WHERE id = :id LIMIT 1",
            [':id' => $id]
        );
    }

    public static function updateLastLogin(int $id): void
    {
        Database::execute(
            "UPDATE users SET last_login_at = :dt WHERE id = :id",
            [':dt' => gmdate('Y-m-d H:i:s'), ':id' => $id]
        );
    }

    public static function recordFailedLogin(string $key, int $decaySeconds = 300): int
    {
        $existing = Database::queryOne(
            "SELECT * FROM rate_limits WHERE rate_key = :k LIMIT 1",
            [':k' => $key]
        );

        $now = time();
        if ($existing) {
            if ($existing['expires_at'] < $now) {
                // Expired, reset
                Database::execute(
                    "UPDATE rate_limits SET hits = 1, expires_at = :exp WHERE rate_key = :k",
                    [':exp' => $now + $decaySeconds, ':k' => $key]
                );
                return 1;
            }

            Database::execute(
                "UPDATE rate_limits SET hits = hits + 1 WHERE rate_key = :k",
                [':k' => $key]
            );
            return (int)$existing['hits'] + 1;
        }

        Database::execute(
            "INSERT INTO rate_limits (rate_key, hits, expires_at) VALUES (:k, 1, :exp)",
            [':k' => $key, ':exp' => $now + $decaySeconds]
        );
        return 1;
    }

    public static function isRateLimited(string $key, int $maxAttempts = 5): bool
    {
        $record = Database::queryOne(
            "SELECT * FROM rate_limits WHERE rate_key = :k LIMIT 1",
            [':k' => $key]
        );

        if (!$record) {
            return false;
        }

        if ($record['expires_at'] < time()) {
            return false;
        }

        return (int)$record['hits'] >= $maxAttempts;
    }

    public static function clearRateLimit(string $key): void
    {
        Database::execute(
            "DELETE FROM rate_limits WHERE rate_key = :k",
            [':k' => $key]
        );
    }
}
