<?php

declare(strict_types=1);

namespace Database\Seeds;

use PDO;

class TableSeeder
{
    public static function run(PDO $pdo): void
    {
        $isMysql = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql';

        $stmt = $isMysql
            ? $pdo->prepare("INSERT INTO restaurant_tables (table_number, capacity, status, qr_token) VALUES (:table_number, :capacity, 'available', :qr_token) ON DUPLICATE KEY UPDATE capacity=VALUES(capacity)")
            : $pdo->prepare("INSERT OR IGNORE INTO restaurant_tables (table_number, capacity, status, qr_token) VALUES (:table_number, :capacity, 'available', :qr_token)");

        for ($i = 1; $i <= 8; $i++) {
            $qrToken = bin2hex(random_bytes(16));
            $stmt->execute([
                ':table_number' => $i,
                ':capacity' => ($i === 4 || $i === 8) ? 6 : 4,
                ':qr_token' => $qrToken,
            ]);
        }
    }
}
