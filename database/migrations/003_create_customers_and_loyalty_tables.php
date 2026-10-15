<?php

declare(strict_types=1);

use Database\Migration;

class CreateCustomersAndLoyaltyTables extends Migration
{
    public function up(): void
    {
        if ($this->isSqlite()) {
            $this->execute("
                CREATE TABLE IF NOT EXISTS customers (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    customer_code VARCHAR(30) NOT NULL UNIQUE,
                    name VARCHAR(120) NOT NULL,
                    phone VARCHAR(20) NOT NULL UNIQUE,
                    is_verified INTEGER NOT NULL DEFAULT 0,
                    loyalty_points INTEGER NOT NULL DEFAULT 0,
                    lifetime_spend NUMERIC(12,2) NOT NULL DEFAULT 0.00,
                    total_orders INTEGER NOT NULL DEFAULT 0,
                    last_order_at DATETIME,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS customer_addresses (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    customer_id INTEGER NOT NULL,
                    label VARCHAR(50) DEFAULT 'Home',
                    address_line1 TEXT NOT NULL,
                    address_line2 TEXT,
                    landmark VARCHAR(150),
                    postal_code VARCHAR(20),
                    latitude NUMERIC(10,8),
                    longitude NUMERIC(11,8),
                    is_default INTEGER NOT NULL DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
                );

                CREATE TABLE IF NOT EXISTS customer_consents (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    customer_id INTEGER NOT NULL,
                    consent_type VARCHAR(50) NOT NULL,
                    is_granted INTEGER NOT NULL DEFAULT 1,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
                    UNIQUE (customer_id, consent_type)
                );

                CREATE TABLE IF NOT EXISTS loyalty_settings (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    setting_key VARCHAR(100) NOT NULL UNIQUE,
                    setting_value TEXT NOT NULL,
                    description TEXT,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS loyalty_transactions (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    customer_id INTEGER NOT NULL,
                    order_id INTEGER,
                    type VARCHAR(20) NOT NULL, -- earn, redeem, reverse, adjust
                    points INTEGER NOT NULL,
                    balance_after INTEGER NOT NULL,
                    description VARCHAR(255),
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
                );
            ");
        } else {
            $this->execute("
                CREATE TABLE IF NOT EXISTS customers (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    customer_code VARCHAR(30) NOT NULL UNIQUE,
                    name VARCHAR(120) NOT NULL,
                    phone VARCHAR(20) NOT NULL UNIQUE,
                    is_verified TINYINT(1) NOT NULL DEFAULT 0,
                    loyalty_points INT NOT NULL DEFAULT 0,
                    lifetime_spend DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                    total_orders INT NOT NULL DEFAULT 0,
                    last_order_at TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_customer_phone (phone),
                    INDEX idx_customer_points (loyalty_points)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS customer_addresses (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    customer_id INT NOT NULL,
                    label VARCHAR(50) DEFAULT 'Home',
                    address_line1 TEXT NOT NULL,
                    address_line2 TEXT,
                    landmark VARCHAR(150),
                    postal_code VARCHAR(20),
                    latitude DECIMAL(10,8) NULL,
                    longitude DECIMAL(11,8) NULL,
                    is_default TINYINT(1) NOT NULL DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
                    INDEX idx_addr_customer (customer_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS customer_consents (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    customer_id INT NOT NULL,
                    consent_type VARCHAR(50) NOT NULL,
                    is_granted TINYINT(1) NOT NULL DEFAULT 1,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
                    UNIQUE KEY uq_customer_consent (customer_id, consent_type)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS loyalty_settings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    setting_key VARCHAR(100) NOT NULL UNIQUE,
                    setting_value TEXT NOT NULL,
                    description TEXT,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS loyalty_transactions (
                    id BIGINT AUTO_INCREMENT PRIMARY KEY,
                    customer_id INT NOT NULL,
                    order_id INT NULL,
                    type ENUM('earn', 'redeem', 'reverse', 'adjust') NOT NULL,
                    points INT NOT NULL,
                    balance_after INT NOT NULL,
                    description VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
                    INDEX idx_loyalty_customer (customer_id),
                    INDEX idx_loyalty_order (order_id),
                    INDEX idx_loyalty_created (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }
    }

    public function down(): void
    {
        $this->execute("
            DROP TABLE IF EXISTS loyalty_transactions;
            DROP TABLE IF EXISTS loyalty_settings;
            DROP TABLE IF EXISTS customer_consents;
            DROP TABLE IF EXISTS customer_addresses;
            DROP TABLE IF EXISTS customers;
        ");
    }
}
