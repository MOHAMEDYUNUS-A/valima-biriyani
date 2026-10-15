<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Request;
use App\Helpers\Response;
use App\Helpers\Database;
use App\Helpers\Session;
use App\Helpers\Security;
use App\Services\OrderService;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Throwable;

class PosController extends BaseController
{
    public function terminal(Request $request): void
    {
        $categories = MenuCategory::allActive();
        $items = MenuItem::allActive();
        $tables = Database::query("SELECT * FROM restaurant_tables ORDER BY table_number ASC");

        $this->render('pos.terminal', [
            'title' => 'POS Terminal Billing',
            'currentPage' => 'pos',
            'categories' => $categories,
            'items' => $items,
            'tables' => $tables,
        ], 'admin');
    }

    public function searchCustomers(Request $request): void
    {
        $term = trim((string)$request->input('q', ''));
        if (strlen($term) < 2) {
            $this->success([], 'Enter at least 2 characters.');
        }

        $customers = Database::query(
            "SELECT id, customer_code, name, phone, loyalty_points
             FROM customers
             WHERE phone LIKE :p OR name LIKE :n
             ORDER BY name ASC LIMIT 10",
            [':p' => "%{$term}%", ':n' => "%{$term}%"]
        );

        $this->success($customers, 'Customers found.');
    }

    public function storeOrder(Request $request): void
    {
        try {
            $raw = $request->json() ?? $request->all();

            $orderType = $raw['order_type'] ?? 'dine_in';
            $tableId = !empty($raw['table_id']) ? (int)$raw['table_id'] : null;

            $params = [
                'customer_name' => $raw['customer_name'] ?? 'Walk-in Customer',
                'customer_phone' => $raw['customer_phone'] ?? '9999999999',
                'table_id' => $tableId,
                'channel' => 'pos',
                'order_type' => $orderType,
                'payment_method' => $raw['payment_method'] ?? 'cash',
                'cart_items' => $raw['cart'] ?? [],
                'idempotency_key' => bin2hex(random_bytes(16)),
            ];

            $order = OrderService::createOrder($params);

            // In POS, walk-in orders are immediately paid and accepted
            Database::execute(
                "UPDATE orders SET status = 'accepted', payment_status = 'paid' WHERE id = :id",
                [':id' => $order['order_id']]
            );
            Database::execute(
                "UPDATE payments SET status = 'captured' WHERE order_id = :id",
                [':id' => $order['order_id']]
            );

            $this->success($order, 'POS order billed successfully.');
        } catch (Throwable $e) {
            $this->error($e->getMessage(), 422);
        }
    }

    public function receipt(Request $request, string $id): void
    {
        $orderId = (int)$id;
        $order = Database::queryOne(
            "SELECT o.*, t.table_number, c.customer_code
             FROM orders o
             LEFT JOIN restaurant_tables t ON o.table_id = t.id
             LEFT JOIN customers c ON o.customer_id = c.id
             WHERE o.id = :id LIMIT 1",
            [':id' => $orderId]
        );

        if (!$order) {
            echo "Receipt not found.";
            exit;
        }

        $items = Database::query(
            "SELECT * FROM order_items WHERE order_id = :id ORDER BY id ASC",
            [':id' => $orderId]
        );

        $this->render('pos.receipt', [
            'title' => "Receipt #{$order['order_number']}",
            'order' => $order,
            'items' => $items,
        ]);
    }
}
