<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;
use App\Helpers\Security;
use App\Helpers\Logger;
use App\Models\MenuItem;
use RuntimeException;

class OrderService
{
    /**
     * Allocate the first available table transactionally to avoid race conditions.
     */
    public static function allocateTable(int $requestedId = 1): ?int
    {
        return Database::transaction(function ($pdo) use ($requestedId) {
            // Find active table IDs with unserved orders
            $busyStmt = $pdo->query("
                SELECT DISTINCT table_id
                FROM orders
                WHERE table_id IS NOT NULL AND status NOT IN ('served', 'completed', 'cancelled')
            ");
            $busyTables = $busyStmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];

            // Try requested table first if free
            if ($requestedId >= 1 && $requestedId <= 8 && !in_array($requestedId, $busyTables, true)) {
                return $requestedId;
            }

            // Otherwise allocate first available table 1-8
            for ($i = 1; $i <= 8; $i++) {
                if (!in_array($i, $busyTables, true)) {
                    return $i;
                }
            }

            return null; // All tables occupied
        });
    }

    /**
     * Validate cart items and recalculate totals entirely on the server.
     */
    public static function calculateCart(array $rawCartItems, string $orderType = 'dine_in'): array
    {
        if (empty($rawCartItems)) {
            throw new RuntimeException("Cart is empty.");
        }

        $validatedItems = [];
        $subtotal = 0.0;

        foreach ($rawCartItems as $raw) {
            $itemId = (int)($raw['id'] ?? 0);
            $qty = (int)($raw['qty'] ?? 1);

            if ($itemId <= 0 || $qty <= 0) {
                continue;
            }

            $dbItem = MenuItem::findById($itemId);
            if (!$dbItem) {
                throw new RuntimeException("Item ID [{$itemId}] does not exist.");
            }

            if (!$dbItem['is_available']) {
                throw new RuntimeException("Item '{$dbItem['name']}' is currently out of stock.");
            }

            // Server-authoritative unit price
            $unitPrice = !empty($dbItem['promo_price']) && (float)$dbItem['promo_price'] > 0
                ? (float)$dbItem['promo_price']
                : (float)$dbItem['base_price'];

            $itemSubtotal = $unitPrice * $qty;
            $subtotal += $itemSubtotal;

            $validatedItems[] = [
                'menu_item_id' => (int)$dbItem['id'],
                'item_name' => $dbItem['name'],
                'variant_name' => null,
                'unit_price' => $unitPrice,
                'quantity' => $qty,
                'subtotal' => $itemSubtotal,
                'notes' => trim((string)($raw['notes'] ?? '')),
            ];
        }

        if (empty($validatedItems)) {
            throw new RuntimeException("No valid items in cart.");
        }

        $taxPercent = 5.00;
        $taxAmount = round(($subtotal * ($taxPercent / 100)), 2);

        $deliveryFee = ($orderType === 'delivery') ? 40.00 : 0.00;
        $discountAmount = 0.00;
        $totalAmount = round($subtotal + $taxAmount + $deliveryFee - $discountAmount, 2);

        return [
            'items' => $validatedItems,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'delivery_fee' => $deliveryFee,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * Create order with idempotency protection and atomic transaction.
     */
    public static function createOrder(array $params): array
    {
        $idempotencyKey = $params['idempotency_key'] ?? null;
        if ($idempotencyKey) {
            $existing = Database::queryOne(
                "SELECT * FROM idempotency_keys WHERE idempotency_key = :k LIMIT 1",
                [':k' => $idempotencyKey]
            );
            if ($existing && !empty($existing['response_payload'])) {
                return json_decode($existing['response_payload'], true);
            }
        }

        return Database::transaction(function ($pdo) use ($params, $idempotencyKey) {
            $customerName = Security::sanitizeString($params['customer_name'] ?? 'Guest');
            $customerPhone = Security::normalizePhone($params['customer_phone'] ?? '');

            if (empty($customerPhone) || strlen($customerPhone) < 10) {
                throw new RuntimeException("A valid mobile number is required.");
            }

            // 1. Resolve or Create Customer record
            $customer = Database::queryOne(
                "SELECT * FROM customers WHERE phone = :p LIMIT 1",
                [':p' => $customerPhone]
            );

            if (!$customer) {
                $customerCode = 'VAL-C' . str_pad((string)random_int(1, 999999), 6, '0', STR_PAD_LEFT);
                Database::execute(
                    "INSERT INTO customers (customer_code, name, phone, is_verified, created_at)
                     VALUES (:code, :name, :phone, 0, :dt)",
                    [
                        ':code' => $customerCode,
                        ':name' => $customerName,
                        ':phone' => $customerPhone,
                        ':dt' => gmdate('Y-m-d H:i:s'),
                    ]
                );
                $customerId = (int)Database::lastInsertId();
            } else {
                $customerId = (int)$customer['id'];
                // Update name if changed
                if ($customer['name'] !== $customerName) {
                    Database::execute("UPDATE customers SET name = :n WHERE id = :id", [':n' => $customerName, ':id' => $customerId]);
                }
            }

            // 2. Calculate cart server-side
            $orderType = $params['order_type'] ?? 'dine_in';
            $calculated = self::calculateCart($params['cart_items'] ?? [], $orderType);

            // 3. Generate secure identifiers
            $orderNumber = Security::generateOrderNumber();
            $trackingToken = Security::generateTrackingToken();
            $tableId = ($orderType === 'dine_in') ? (int)($params['table_id'] ?? null) : null;

            $channel = $params['channel'] ?? 'website';
            $paymentMethod = $params['payment_method'] ?? 'cash';
            $status = ($orderType === 'dine_in') ? 'accepted' : 'new';
            $paymentStatus = 'pending';

            $deliveryAddressJson = !empty($params['delivery_address'])
                ? json_encode($params['delivery_address'])
                : null;

            // 4. Insert into orders
            Database::execute(
                "INSERT INTO orders (
                    order_number, tracking_token, customer_id, customer_name, customer_phone,
                    table_id, channel, order_type, status, subtotal, discount_amount,
                    tax_amount, delivery_fee, total_amount, payment_method, payment_status,
                    special_instructions, delivery_address_json, idempotency_key, created_at
                ) VALUES (
                    :num, :token, :cid, :cname, :cphone,
                    :tbl, :channel, :type, :status, :subtotal, :disc,
                    :tax, :deliv, :total, :pay_method, :pay_status,
                    :instr, :addr, :idemp, :dt
                )",
                [
                    ':num' => $orderNumber,
                    ':token' => $trackingToken,
                    ':cid' => $customerId,
                    ':cname' => $customerName,
                    ':cphone' => $customerPhone,
                    ':tbl' => $tableId,
                    ':channel' => $channel,
                    ':type' => $orderType,
                    ':status' => $status,
                    ':subtotal' => $calculated['subtotal'],
                    ':disc' => $calculated['discount_amount'],
                    ':tax' => $calculated['tax_amount'],
                    ':deliv' => $calculated['delivery_fee'],
                    ':total' => $calculated['total_amount'],
                    ':pay_method' => $paymentMethod,
                    ':pay_status' => $paymentStatus,
                    ':instr' => $params['special_instructions'] ?? null,
                    ':addr' => $deliveryAddressJson,
                    ':idemp' => $idempotencyKey,
                    ':dt' => gmdate('Y-m-d H:i:s'),
                ]
            );
            $orderId = (int)Database::lastInsertId();

            // 5. Insert order items
            $itemStmt = $pdo->prepare("
                INSERT INTO order_items (order_id, menu_item_id, item_name, variant_name, unit_price, quantity, subtotal, notes)
                VALUES (:oid, :mid, :name, :vname, :price, :qty, :sub, :notes)
            ");

            foreach ($calculated['items'] as $item) {
                $itemStmt->execute([
                    ':oid' => $orderId,
                    ':mid' => $item['menu_item_id'],
                    ':name' => $item['item_name'],
                    ':vname' => $item['variant_name'],
                    ':price' => $item['unit_price'],
                    ':qty' => $item['quantity'],
                    ':sub' => $item['subtotal'],
                    ':notes' => $item['notes'],
                ]);
            }

            // 6. Record Initial Status Event
            Database::execute(
                "INSERT INTO order_status_events (order_id, status, notes, created_at)
                 VALUES (:oid, :status, 'Order created successfully', :dt)",
                [
                    ':oid' => $orderId,
                    ':status' => $status,
                    ':dt' => gmdate('Y-m-d H:i:s'),
                ]
            );

            // 7. Initialize Initial Payment Record
            $transRef = 'TXN-' . bin2hex(random_bytes(6));
            Database::execute(
                "INSERT INTO payments (order_id, transaction_ref, provider, amount, status, created_at)
                 VALUES (:oid, :ref, :provider, :amt, 'pending', :dt)",
                [
                    ':oid' => $orderId,
                    ':ref' => $transRef,
                    ':provider' => $paymentMethod,
                    ':amt' => $calculated['total_amount'],
                    ':dt' => gmdate('Y-m-d H:i:s'),
                ]
            );

            // 8. Create Kitchen Ticket
            $ticketNumber = 'KOT-' . str_pad((string)$orderId, 5, '0', STR_PAD_LEFT);
            Database::execute(
                "INSERT INTO kitchen_tickets (order_id, ticket_number, status, created_at)
                 VALUES (:oid, :tnum, 'pending', :dt)",
                [
                    ':oid' => $orderId,
                    ':tnum' => $ticketNumber,
                    ':dt' => gmdate('Y-m-d H:i:s'),
                ]
            );

            $result = [
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'tracking_token' => $trackingToken,
                'total_amount' => $calculated['total_amount'],
                'status' => $status,
                'table_id' => $tableId,
            ];

            // Record idempotency response
            if ($idempotencyKey) {
                $hash = hash('sha256', json_encode($params));
                Database::execute(
                    "INSERT INTO idempotency_keys (idempotency_key, request_hash, response_payload, status_code, expires_at)
                     VALUES (:k, :h, :resp, 200, :exp)",
                    [
                        ':k' => $idempotencyKey,
                        ':h' => $hash,
                        ':resp' => json_encode($result),
                        ':exp' => date('Y-m-d H:i:s', time() + 86400),
                    ]
                );
            }

            Logger::info("New order placed: {$orderNumber}", $result);

            return $result;
        });
    }
}
