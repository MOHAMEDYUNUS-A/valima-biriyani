<?php

declare(strict_types=1);

namespace Database\Seeds;

use PDO;

class RoleAndUserSeeder
{
    public static function run(PDO $pdo): void
    {
        $roles = [
            ['name' => 'owner', 'description' => 'Full administrative access and financial oversight'],
            ['name' => 'manager', 'description' => 'Daily operations, menu availability, and staff coordination'],
            ['name' => 'cashier', 'description' => 'POS billing, customer orders, and counter payments'],
            ['name' => 'kitchen', 'description' => 'Kitchen order processing and KOT ticket management'],
            ['name' => 'delivery', 'description' => 'Delivery logistics and status updates'],
        ];

        $stmt = $pdo->prepare("INSERT OR IGNORE INTO roles (name, description) VALUES (:name, :description)");
        // On MySQL, adjust to ON DUPLICATE KEY UPDATE
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $stmt = $pdo->prepare("INSERT INTO roles (name, description) VALUES (:name, :description) ON DUPLICATE KEY UPDATE description=VALUES(description)");
        }

        foreach ($roles as $r) {
            $stmt->execute([':name' => $r['name'], ':description' => $r['description']]);
        }

        // Create Default Admin User
        $adminEmail = 'admin@valimabiriyani.com';
        $adminPass = password_hash('Admin@Valima2026', PASSWORD_BCRYPT);

        $check = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $check->execute([':email' => $adminEmail]);
        if (!$check->fetch()) {
            $userStmt = $pdo->prepare("
                INSERT INTO users (name, email, phone, password_hash, role, status, created_at)
                VALUES (:name, :email, :phone, :password_hash, :role, 'active', :created_at)
            ");
            $userStmt->execute([
                ':name' => 'Restaurant Owner',
                ':email' => $adminEmail,
                ':phone' => '+919876500000',
                ':password_hash' => $adminPass,
                ':role' => 'owner',
                ':created_at' => gmdate('Y-m-d H:i:s'),
            ]);
            $userId = $pdo->lastInsertId();

            // Also create demo accounts for Cashier and Kitchen
            $staff = [
                ['name' => 'Front Cashier', 'email' => 'cashier@valimabiriyani.com', 'role' => 'cashier', 'pass' => 'Cashier@2026'],
                ['name' => 'Head Chef', 'email' => 'kitchen@valimabiriyani.com', 'role' => 'kitchen', 'pass' => 'Kitchen@2026'],
            ];

            foreach ($staff as $s) {
                $userStmt->execute([
                    ':name' => $s['name'],
                    ':email' => $s['email'],
                    ':phone' => '+919876500001',
                    ':password_hash' => password_hash($s['pass'], PASSWORD_BCRYPT),
                    ':role' => $s['role'],
                    ':created_at' => gmdate('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
