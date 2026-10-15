<?php

declare(strict_types=1);

use Database\Migration;

class CreateOrdersAndBillingTables extends Migration
{
    public function up(): void
    {
        if ($this->isSqlite()) {
            $this->execute("
                CREATE TABLE IF NOT EXISTS orders (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    order_number VARCHAR(50) NOT NULL UNIQUE,
                    tracking_token VARCHAR(64) NOT NULL UNIQUE,
                    customer_id INTEGER,
                    customer_name VARCHAR(120) NOT NULL,
                    customer_phone VARCHAR(20) NOT NULL,
                    table_id INTEGER,
                    channel VARCHAR(30) NOT NULL DEFAULT 'website', -- website, pos, phone, whatsapp, swiggy, zomato
                    order_type VARCHAR(30) NOT NULL DEFAULT 'dine_in', -- dine_in, pickup, delivery
                    status VARCHAR(30) NOT NULL DEFAULT 'new', -- new, accepted, preparing, ready, out_for_delivery, served, completed, cancelled
                    subtotal NUMERIC(10,2) NOT NULL DEFAULT 0.00,
                    discount_amount NUMERIC(10,2) NOT NULL DEFAULT 0.00,
                    tax_amount NUMERIC(10,2) NOT NULL DEFAULT 0.00,
                    delivery_fee NUMERIC(10,2) NOT NULL DEFAULT 0.00,
                    total_amount NUMERIC(10,2) NOT NULL DEFAULT 0.00,
                    payment_method VARCHAR(30) NOT NULL DEFAULT 'cash', -- cash, counter_upi, razorpay, card
                    payment_status VARCHAR(30) NOT NULL DEFAULT 'pending', -- pending, paid, failed, refunded
                    special_instructions TEXT,
                    delivery_address_json TEXT,
                    estimated_prep_minutes INTEGER NOT NULL DEFAULT 20,
                    idempotency_key VARCHAR(100) UNIQUE,
                    external_reference VARCHAR(100),
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
                    FOREIGN KEY (table_id) REFERENCES restaurant_tables(id) ON DELETE SET NULL
                );

                CREATE TABLE IF NOT EXISTS order_items (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    order_id INTEGER NOT NULL,
                    menu_item_id INTEGER,
                    item_name VARCHAR(150) NOT NULL,
                    variant_name VARCHAR(100),
                    unit_price NUMERIC(10,2) NOT NULL,
                    quantity INTEGER NOT NULL,
                    subtotal NUMERIC(10,2) NOT NULL,
                    notes VARCHAR(255),
                    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE SET NULL
                );

                CREATE TABLE IF NOT EXISTS order_item_addons (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    order_item_id INTEGER NOT NULL,
                    addon_name VARCHAR(100) NOT NULL,
                    price NUMERIC(10,2) NOT NULL,
                    quantity INTEGER NOT NULL DEFAULT 1,
                    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE
                );

                CREATE TABLE IF NOT EXISTS order_status_events (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    order_id INTEGER NOT NULL,
                    status VARCHAR(30) NOT NULL,
                    notes VARCHAR(255),
                    created_by_user_id INTEGER,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL
                );

                CREATE TABLE IF NOT EXISTS payments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    order_id INTEGER NOT NULL,
                    transaction_ref VARCHAR(100) NOT NULL UNIQUE,
                    provider VARCHAR(50) NOT NULL DEFAULT 'cash', -- cash, counter_upi, razorpay, pos_card
                    amount NUMERIC(10,2) NOT NULL,
                    status VARCHAR(30) NOT NULL DEFAULT 'pending', -- pending, captured, failed, refunded
                    provider_payment_id VARCHAR(100),
                    provider_order_id VARCHAR(100),
                    raw_payload TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
                );

                CREATE TABLE IF NOT EXISTS refunds (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    order_id INTEGER NOT NULL,
                    payment_id INTEGER,
                    amount NUMERIC(10,2) NOT NULL,
                    reason VARCHAR(255) NOT NULL,
                    authorized_by_user_id INTEGER,
                    provider_refund_id VARCHAR(100),
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL,
                    FOREIGN KEY (authorized_by_user_id) REFERENCES users(id) ON DELETE SET NULL
                );

                CREATE TABLE IF NOT EXISTS idempotency_keys (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    idempotency_key VARCHAR(100) NOT NULL UNIQUE,
                    request_hash VARCHAR(64) NOT NULL,
                    response_payload TEXT,
                    status_code INTEGER NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    expires_at DATETIME NOT NULL
                );
            ");
        } else {
            $this->execute("
                CREATE TABLE IF NOT EXISTS orders (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    order_number VARCHAR(50) NOT NULL UNIQUE,
                    tracking_token VARCHAR(64) NOT NULL UNIQUE,
                    customer_id INT NULL,
                    customer_name VARCHAR(120) NOT NULL,
                    customer_phone VARCHAR(20) NOT NULL,
                    table_id INT NULL,
                    channel ENUM('website', 'pos', 'phone', 'whatsapp', 'swiggy', 'zomato') NOT NULL DEFAULT 'website',
                    order_type ENUM('dine_in', 'pickup', 'delivery') NOT NULL DEFAULT 'dine_in',
                    status ENUM('new', 'accepted', 'preparing', 'ready', 'out_for_delivery', 'served', 'completed', 'cancelled') NOT NULL DEFAULT 'new',
                    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    payment_method ENUM('cash', 'counter_upi', 'razorpay', 'card') NOT NULL DEFAULT 'cash',
                    payment_status ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
                    special_instructions TEXT,
                    delivery_address_json JSON NULL,
                    estimated_prep_minutes INT NOT NULL DEFAULT 20,
                    idempotency_key VARCHAR(100) NULL UNIQUE,
                    external_reference VARCHAR(100) NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
                    FOREIGN KEY (table_id) REFERENCES restaurant_tables(id) ON DELETE SET NULL,
                    INDEX idx_orders_status (status),
                    INDEX idx_orders_payment_status (payment_status),
                    INDEX idx_orders_channel (channel),
                    INDEX idx_orders_type (order_type),
                    INDEX idx_orders_table (table_id),
                    INDEX idx_orders_created (created_at),
                    INDEX idx_orders_phone (customer_phone)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS order_items (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    order_id INT NOT NULL,
                    menu_item_id INT NULL,
                    item_name VARCHAR(150) NOT NULL,
                    variant_name VARCHAR(100) NULL,
                    unit_price DECIMAL(10,2) NOT NULL,
                    quantity INT NOT NULL,
                    subtotal DECIMAL(10,2) NOT NULL,
                    notes VARCHAR(255) NULL,
                    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE SET NULL,
                    INDEX idx_oi_order (order_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS order_item_addons (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    order_item_id INT NOT NULL,
                    addon_name VARCHAR(100) NOT NULL,
                    price DECIMAL(10,2) NOT NULL,
                    quantity INT NOT NULL DEFAULT 1,
                    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE,
                    INDEX idx_oia_item (order_item_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS order_status_events (
                    id BIGINT AUTO_INCREMENT PRIMARY KEY,
                    order_id INT NOT NULL,
                    status VARCHAR(30) NOT NULL,
                    notes VARCHAR(255) NULL,
                    created_by_user_id INT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
                    INDEX idx_ose_order (order_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS payments (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    order_id INT NOT NULL,
                    transaction_ref VARCHAR(100) NOT NULL UNIQUE,
                    provider VARCHAR(50) NOT NULL DEFAULT 'cash',
                    amount DECIMAL(10,2) NOT NULL,
                    status ENUM('pending', 'captured', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
                    provider_payment_id VARCHAR(100) NULL,
                    provider_order_id VARCHAR(100) NULL,
                    raw_payload JSON NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                    INDEX idx_payments_order (order_id),
                    INDEX idx_payments_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS refunds (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    order_id INT NOT NULL,
                    payment_id INT NULL,
                    amount DECIMAL(10,2) NOT NULL,
                    reason VARCHAR(255) NOT NULL,
                    authorized_by_user_id INT NULL,
                    provider_refund_id VARCHAR(100) NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL,
                    FOREIGN KEY (authorized_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
                    INDEX idx_refunds_order (order_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS idempotency_keys (
                    id BIGINT AUTO_INCREMENT PRIMARY KEY,
                    idempotency_key VARCHAR(100) NOT NULL UNIQUE,
                    request_hash VARCHAR(64) NOT NULL,
                    response_payload JSON NULL,
                    status_code INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    expires_at TIMESTAMP NOT NULL,
                    INDEX idx_idemp_key (idempotency_key),
                    INDEX idx_idemp_expires (expires_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }
    }

    public function down(): void
    {
        $this->execute("
            DROP TABLE IF EXISTS idempotency_keys;
            DROP TABLE IF EXISTS refunds;
            DROP TABLE IF EXISTS payments;
            DROP TABLE IF EXISTS order_status_events;
            DROP TABLE IF EXISTS order_item_addons;
            DROP TABLE IF EXISTS order_items;
            DROP TABLE IF EXISTS orders;
        ");
    }
}
