<?php

declare(strict_types=1);

use Database\Migration;

class CreateDiningAndMenuTables extends Migration
{
    public function up(): void
    {
        if ($this->isSqlite()) {
            $this->execute("
                CREATE TABLE IF NOT EXISTS restaurant_tables (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    table_number INTEGER NOT NULL UNIQUE,
                    capacity INTEGER NOT NULL DEFAULT 4,
                    status VARCHAR(20) NOT NULL DEFAULT 'available',
                    qr_token VARCHAR(64) UNIQUE,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS menu_categories (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR(100) NOT NULL UNIQUE,
                    slug VARCHAR(120) NOT NULL UNIQUE,
                    description TEXT,
                    icon VARCHAR(50) DEFAULT '🍲',
                    sort_order INTEGER NOT NULL DEFAULT 0,
                    is_active INTEGER NOT NULL DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS menu_items (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    category_id INTEGER NOT NULL,
                    name VARCHAR(150) NOT NULL,
                    slug VARCHAR(170) NOT NULL UNIQUE,
                    description TEXT,
                    base_price NUMERIC(10,2) NOT NULL,
                    image_url VARCHAR(255),
                    is_veg INTEGER NOT NULL DEFAULT 0,
                    is_available INTEGER NOT NULL DEFAULT 1,
                    prep_time_minutes INTEGER NOT NULL DEFAULT 15,
                    tax_percent NUMERIC(5,2) NOT NULL DEFAULT 5.00,
                    promo_price NUMERIC(10,2) DEFAULT NULL,
                    sort_order INTEGER NOT NULL DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (category_id) REFERENCES menu_categories(id) ON DELETE CASCADE
                );

                CREATE TABLE IF NOT EXISTS menu_item_variants (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    menu_item_id INTEGER NOT NULL,
                    name VARCHAR(100) NOT NULL,
                    price NUMERIC(10,2) NOT NULL,
                    is_available INTEGER NOT NULL DEFAULT 1,
                    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
                );

                CREATE TABLE IF NOT EXISTS menu_addons (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR(100) NOT NULL UNIQUE,
                    price NUMERIC(10,2) NOT NULL,
                    is_veg INTEGER NOT NULL DEFAULT 1,
                    is_available INTEGER NOT NULL DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS menu_item_addons (
                    menu_item_id INTEGER NOT NULL,
                    addon_id INTEGER NOT NULL,
                    PRIMARY KEY (menu_item_id, addon_id),
                    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE,
                    FOREIGN KEY (addon_id) REFERENCES menu_addons(id) ON DELETE CASCADE
                );
            ");
        } else {
            $this->execute("
                CREATE TABLE IF NOT EXISTS restaurant_tables (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    table_number INT NOT NULL UNIQUE,
                    capacity INT NOT NULL DEFAULT 4,
                    status ENUM('available', 'occupied', 'reserved') NOT NULL DEFAULT 'available',
                    qr_token VARCHAR(64) UNIQUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_table_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS menu_categories (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL UNIQUE,
                    slug VARCHAR(120) NOT NULL UNIQUE,
                    description TEXT,
                    icon VARCHAR(50) DEFAULT '🍲',
                    sort_order INT NOT NULL DEFAULT 0,
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_cat_active (is_active),
                    INDEX idx_cat_sort (sort_order)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS menu_items (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    category_id INT NOT NULL,
                    name VARCHAR(150) NOT NULL,
                    slug VARCHAR(170) NOT NULL UNIQUE,
                    description TEXT,
                    base_price DECIMAL(10,2) NOT NULL,
                    image_url VARCHAR(255),
                    is_veg TINYINT(1) NOT NULL DEFAULT 0,
                    is_available TINYINT(1) NOT NULL DEFAULT 1,
                    prep_time_minutes INT NOT NULL DEFAULT 15,
                    tax_percent DECIMAL(5,2) NOT NULL DEFAULT 5.00,
                    promo_price DECIMAL(10,2) DEFAULT NULL,
                    sort_order INT NOT NULL DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (category_id) REFERENCES menu_categories(id) ON DELETE CASCADE,
                    INDEX idx_item_category (category_id),
                    INDEX idx_item_avail (is_available),
                    INDEX idx_item_veg (is_veg)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS menu_item_variants (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    menu_item_id INT NOT NULL,
                    name VARCHAR(100) NOT NULL,
                    price DECIMAL(10,2) NOT NULL,
                    is_available TINYINT(1) NOT NULL DEFAULT 1,
                    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE,
                    INDEX idx_variant_item (menu_item_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS menu_addons (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL UNIQUE,
                    price DECIMAL(10,2) NOT NULL,
                    is_veg TINYINT(1) NOT NULL DEFAULT 1,
                    is_available TINYINT(1) NOT NULL DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS menu_item_addons (
                    menu_item_id INT NOT NULL,
                    addon_id INT NOT NULL,
                    PRIMARY KEY (menu_item_id, addon_id),
                    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE,
                    FOREIGN KEY (addon_id) REFERENCES menu_addons(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }
    }

    public function down(): void
    {
        $this->execute("
            DROP TABLE IF EXISTS menu_item_addons;
            DROP TABLE IF EXISTS menu_addons;
            DROP TABLE IF EXISTS menu_item_variants;
            DROP TABLE IF EXISTS menu_items;
            DROP TABLE IF EXISTS menu_categories;
            DROP TABLE IF EXISTS restaurant_tables;
        ");
    }
}
