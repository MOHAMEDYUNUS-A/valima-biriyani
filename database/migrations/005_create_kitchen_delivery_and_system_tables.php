<?php

declare(strict_types=1);

use Database\Migration;

class CreateKitchenDeliveryAndSystemTables extends Migration
{
    public function up(): void
    {
        if ($this->isSqlite()) {
            $this->execute("
                CREATE TABLE IF NOT EXISTS kitchen_tickets (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    order_id INTEGER NOT NULL,
                    ticket_number VARCHAR(50) NOT NULL UNIQUE,
                    status VARCHAR(30) NOT NULL DEFAULT 'pending', -- pending, in_kitchen, completed
                    printed_at DATETIME,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
                );

                CREATE TABLE IF NOT EXISTS print_jobs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    ticket_id INTEGER,
                    order_id INTEGER,
                    job_type VARCHAR(30) NOT NULL DEFAULT 'kot', -- kot, receipt
                    content_text TEXT NOT NULL,
                    status VARCHAR(30) NOT NULL DEFAULT 'queued', -- queued, printed, failed
                    is_reprint INTEGER NOT NULL DEFAULT 0,
                    reprint_count INTEGER NOT NULL DEFAULT 0,
                    error_message TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (ticket_id) REFERENCES kitchen_tickets(id) ON DELETE SET NULL,
                    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
                );

                CREATE TABLE IF NOT EXISTS delivery_bookings (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    order_id INTEGER NOT NULL UNIQUE,
                    provider_name VARCHAR(50) NOT NULL DEFAULT 'rapido',
                    booking_reference VARCHAR(100),
                    delivery_fee NUMERIC(10,2) NOT NULL DEFAULT 0.00,
                    tracking_url VARCHAR(255),
                    status VARCHAR(40) NOT NULL DEFAULT 'awaiting_booking', -- awaiting_booking, booked, pickup_pending, picked_up, in_transit, delivered, failed, cancelled
                    delivery_notes TEXT,
                    booked_by_user_id INTEGER,
                    booked_at DATETIME,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                    FOREIGN KEY (booked_by_user_id) REFERENCES users(id) ON DELETE SET NULL
                );

                CREATE TABLE IF NOT EXISTS notification_logs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    channel VARCHAR(30) NOT NULL, -- whatsapp, sms, email
                    recipient VARCHAR(100) NOT NULL,
                    event_type VARCHAR(50) NOT NULL,
                    status VARCHAR(30) NOT NULL DEFAULT 'pending', -- sent, failed, pending
                    provider_response TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS webhook_events (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    provider VARCHAR(50) NOT NULL, -- razorpay, whatsapp, make
                    event_id VARCHAR(100) NOT NULL UNIQUE,
                    event_type VARCHAR(100) NOT NULL,
                    payload TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS restaurant_settings (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    setting_key VARCHAR(100) NOT NULL UNIQUE,
                    setting_value TEXT NOT NULL,
                    group_name VARCHAR(50) NOT NULL DEFAULT 'general',
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
            ");
        } else {
            $this->execute("
                CREATE TABLE IF NOT EXISTS kitchen_tickets (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    order_id INT NOT NULL,
                    ticket_number VARCHAR(50) NOT NULL UNIQUE,
                    status ENUM('pending', 'in_kitchen', 'completed') NOT NULL DEFAULT 'pending',
                    printed_at TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                    INDEX idx_kt_order (order_id),
                    INDEX idx_kt_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS print_jobs (
                    id BIGINT AUTO_INCREMENT PRIMARY KEY,
                    ticket_id INT NULL,
                    order_id INT NULL,
                    job_type ENUM('kot', 'receipt') NOT NULL DEFAULT 'kot',
                    content_text MEDIUMTEXT NOT NULL,
                    status ENUM('queued', 'printed', 'failed') NOT NULL DEFAULT 'queued',
                    is_reprint TINYINT(1) NOT NULL DEFAULT 0,
                    reprint_count INT NOT NULL DEFAULT 0,
                    error_message TEXT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (ticket_id) REFERENCES kitchen_tickets(id) ON DELETE SET NULL,
                    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                    INDEX idx_pj_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS delivery_bookings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    order_id INT NOT NULL UNIQUE,
                    provider_name VARCHAR(50) NOT NULL DEFAULT 'rapido',
                    booking_reference VARCHAR(100) NULL,
                    delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    tracking_url VARCHAR(255) NULL,
                    status ENUM('awaiting_booking', 'booked', 'pickup_pending', 'picked_up', 'in_transit', 'delivered', 'failed', 'cancelled') NOT NULL DEFAULT 'awaiting_booking',
                    delivery_notes TEXT NULL,
                    booked_by_user_id INT NULL,
                    booked_at TIMESTAMP NULL,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                    FOREIGN KEY (booked_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
                    INDEX idx_delivery_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS notification_logs (
                    id BIGINT AUTO_INCREMENT PRIMARY KEY,
                    channel VARCHAR(30) NOT NULL,
                    recipient VARCHAR(100) NOT NULL,
                    event_type VARCHAR(50) NOT NULL,
                    status ENUM('sent', 'failed', 'pending') NOT NULL DEFAULT 'pending',
                    provider_response JSON NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_nl_recipient (recipient),
                    INDEX idx_nl_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS webhook_events (
                    id BIGINT AUTO_INCREMENT PRIMARY KEY,
                    provider VARCHAR(50) NOT NULL,
                    event_id VARCHAR(100) NOT NULL UNIQUE,
                    event_type VARCHAR(100) NOT NULL,
                    payload JSON NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_we_provider (provider)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS restaurant_settings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    setting_key VARCHAR(100) NOT NULL UNIQUE,
                    setting_value TEXT NOT NULL,
                    group_name VARCHAR(50) NOT NULL DEFAULT 'general',
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_rs_group (group_name)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }
    }

    public function down(): void
    {
        $this->execute("
            DROP TABLE IF EXISTS restaurant_settings;
            DROP TABLE IF EXISTS webhook_events;
            DROP TABLE IF EXISTS notification_logs;
            DROP TABLE IF EXISTS delivery_bookings;
            DROP TABLE IF EXISTS print_jobs;
            DROP TABLE IF EXISTS kitchen_tickets;
        ");
    }
}
