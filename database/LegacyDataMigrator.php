<?php

declare(strict_types=1);

namespace Database;

use PDO;
use App\Helpers\Security;
use App\Helpers\Logger;
use RuntimeException;

class LegacyDataMigrator
{
    private PDO $pdo;
    private string $driver;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    public function migrate(): array
    {
        $report = [
            'tables_migrated' => 0,
            'menu_items_migrated' => 0,
            'orders_migrated' => 0,
            'order_items_migrated' => 0,
            'backups_created' => [],
        ];

        // 1. Check legacy tables existence
        $tablesExist = $this->hasTable('tables') || $this->hasTable('menu') || $this->hasTable('orders');
        if (!$tablesExist) {
            echo "No legacy tables found to migrate. Skipping.\n";
            return $report;
        }

        // 2. Safe non-destructive backups
        $legacyTables = ['tables', 'menu', 'orders', 'order_items'];
        foreach ($legacyTables as $lt) {
            if ($this->hasTable($lt)) {
                $backupName = "_backup_legacy_{$lt}_" . date('Ymd_His');
                $this->pdo->exec("CREATE TABLE IF NOT EXISTS `{$backupName}` AS SELECT * FROM `{$lt}`");
                $report['backups_created'][] = $backupName;
                echo "Backed up legacy table [{$lt}] to [{$backupName}]\n";
            }
        }

        // 3. Migrate Tables
        if ($this->hasTable('tables')) {
            $stmt = $this->pdo->query("SELECT * FROM `tables`");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $ins = $this->pdo->prepare("
                INSERT OR IGNORE INTO restaurant_tables (table_number, capacity, status, qr_token)
                VALUES (:num, 4, 'available', :qr)
            ");
            if ($this->driver === 'mysql') {
                $ins = $this->pdo->prepare("
                    INSERT INTO restaurant_tables (table_number, capacity, status, qr_token)
                    VALUES (:num, 4, 'available', :qr)
                    ON DUPLICATE KEY UPDATE table_number=VALUES(table_number)
                ");
            }

            foreach ($rows as $r) {
                $tnum = (int)($r['table_number'] ?? $r['id'] ?? 1);
                $ins->execute([
                    ':num' => $tnum,
                    ':qr' => bin2hex(random_bytes(16)),
                ]);
                $report['tables_migrated']++;
            }
        }

        // 4. Migrate Menu
        if ($this->hasTable('menu')) {
            $stmt = $this->pdo->query("SELECT * FROM `menu`");
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Default category ID
            $catId = $this->pdo->query("SELECT id FROM menu_categories LIMIT 1")->fetchColumn() ?: 1;

            $insMenu = $this->pdo->prepare("
                INSERT OR IGNORE INTO menu_items (category_id, name, slug, description, base_price, is_available)
                VALUES (:cid, :name, :slug, :desc, :price, 1)
            ");
            if ($this->driver === 'mysql') {
                $insMenu = $this->pdo->prepare("
                    INSERT INTO menu_items (category_id, name, slug, description, base_price, is_available)
                    VALUES (:cid, :name, :slug, :desc, :price, 1)
                    ON DUPLICATE KEY UPDATE base_price=VALUES(base_price)
                ");
            }

            foreach ($items as $item) {
                $name = trim($item['name'] ?? 'Item');
                $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
                $price = (float)($item['price'] ?? 0);

                $insMenu->execute([
                    ':cid' => $catId,
                    ':name' => $name,
                    ':slug' => $slug,
                    ':desc' => 'Legacy menu item',
                    ':price' => $price,
                ]);
                $report['menu_items_migrated']++;
            }
        }

        // 5. Migrate Orders & Order Items
        if ($this->hasTable('orders') && $this->hasTable('order_items')) {
            $orders = $this->pdo->query("SELECT * FROM `orders` ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

            // Prepare default legacy customer
            $custCheck = $this->pdo->query("SELECT id FROM customers WHERE phone = '+919999999999'")->fetchColumn();
            if (!$custCheck) {
                $custIns = $this->pdo->prepare("
                    INSERT INTO customers (customer_code, name, phone, is_verified, created_at)
                    VALUES ('VAL-C000000', 'Guest / Walk-in Customer', '+919999999999', 0, :dt)
                ");
                $custIns->execute([':dt' => gmdate('Y-m-d H:i:s')]);
                $custId = (int)$this->pdo->lastInsertId();
            } else {
                $custId = (int)$custCheck;
            }

            foreach ($orders as $o) {
                $oldId = $o['id'];
                $orderNumber = "VAL-LEGACY-" . str_pad((string)$oldId, 5, '0', STR_PAD_LEFT);

                // Check if already migrated
                $exists = $this->pdo->prepare("SELECT id FROM orders WHERE order_number = :num");
                $exists->execute([':num' => $orderNumber]);
                if ($exists->fetch()) {
                    continue;
                }

                $rawStatus = $o['status'] ?? 'Order Taken';
                $status = match (trim($rawStatus)) {
                    'Order Taken' => 'accepted',
                    'Preparing' => 'preparing',
                    'Ready' => 'ready',
                    'Served' => 'completed',
                    default => 'completed',
                };

                // Get items for order to calculate total
                $itemsStmt = $this->pdo->prepare("SELECT * FROM `order_items` WHERE order_id = :oid");
                $itemsStmt->execute([':oid' => $oldId]);
                $oldItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

                $subtotal = 0.0;
                foreach ($oldItems as $oi) {
                    $subtotal += ((float)$oi['price']) * ((int)$oi['qty']);
                }

                $trackingToken = Security::generateTrackingToken();
                $tableId = !empty($o['table_id']) ? (int)$o['table_id'] : null;

                $orderIns = $this->pdo->prepare("
                    INSERT INTO orders (
                        order_number, tracking_token, customer_id, customer_name, customer_phone,
                        table_id, channel, order_type, status, subtotal, total_amount,
                        payment_method, payment_status, created_at
                    ) VALUES (
                        :num, :token, :cid, 'Dine-in Customer', '+919999999999',
                        :tbl, 'pos', 'dine_in', :status, :subtotal, :total,
                        'cash', :pay_status, :created
                    )
                ");

                $payStatus = ($status === 'completed') ? 'paid' : 'pending';
                $orderIns->execute([
                    ':num' => $orderNumber,
                    ':token' => $trackingToken,
                    ':cid' => $custId,
                    ':tbl' => $tableId,
                    ':status' => $status,
                    ':subtotal' => $subtotal,
                    ':total' => $subtotal,
                    ':pay_status' => $payStatus,
                    ':created' => $o['created_at'] ?? gmdate('Y-m-d H:i:s'),
                ]);
                $newOrderId = (int)$this->pdo->lastInsertId();
                $report['orders_migrated']++;

                // Migrate order items
                $itemIns = $this->pdo->prepare("
                    INSERT INTO order_items (order_id, item_name, unit_price, quantity, subtotal)
                    VALUES (:oid, :name, :price, :qty, :sub)
                ");

                foreach ($oldItems as $oi) {
                    $itemPrice = (float)$oi['price'];
                    $qty = (int)$oi['qty'];
                    $itemIns->execute([
                        ':oid' => $newOrderId,
                        ':name' => $oi['item_name'],
                        ':price' => $itemPrice,
                        ':qty' => $qty,
                        ':sub' => $itemPrice * $qty,
                    ]);
                    $report['order_items_migrated']++;
                }

                // Initial status event
                $eventIns = $this->pdo->prepare("
                    INSERT INTO order_status_events (order_id, status, notes, created_at)
                    VALUES (:oid, :status, 'Migrated from legacy KMS Foods system', :dt)
                ");
                $eventIns->execute([
                    ':oid' => $newOrderId,
                    ':status' => $status,
                    ':dt' => $o['created_at'] ?? gmdate('Y-m-d H:i:s'),
                ]);
            }
        }

        return $report;
    }

    private function hasTable(string $table): bool
    {
        try {
            if ($this->driver === 'sqlite') {
                $stmt = $this->pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name = :t");
                $stmt->execute([':t' => $table]);
                return (bool)$stmt->fetch();
            } else {
                $stmt = $this->pdo->prepare("SHOW TABLES LIKE :t");
                $stmt->execute([':t' => $table]);
                return (bool)$stmt->fetch();
            }
        } catch (\Throwable) {
            return false;
        }
    }
}
