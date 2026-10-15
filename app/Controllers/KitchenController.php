<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Database;
use App\Helpers\Request;
use App\Helpers\Response;
use App\Helpers\Security;
use App\Helpers\Session;
use App\Helpers\Logger;

class KitchenController extends BaseController
{
    /**
     * Kitchen Display System (KDS) main view.
     */
    public function display(Request $request): Response
    {
        $this->requireRoles(['owner', 'manager', 'kitchen']);

        // 1. Fetch tables 1-8 and their occupancy status
        $tables = $this->getTablesOverview();

        // 2. Fetch active kitchen orders (confirmed, preparing, ready)
        $activeOrders = $this->getActiveOrders();

        // 3. Stats for summary cards
        $stats = $this->getKitchenStats();

        $this->render('kitchen.display', [
            'tables' => $tables,
            'activeOrders' => $activeOrders,
            'stats' => $stats,
            'user' => Session::user(),
        ]);
    }

    /**
     * Live JSON stream endpoint for polling without page reload.
     */
    public function stream(Request $request): Response
    {
        $this->requireRoles(['owner', 'manager', 'kitchen']);

        $activeOrders = $this->getActiveOrders();
        $tables = $this->getTablesOverview();
        $stats = $this->getKitchenStats();

        return $this->json([
            'success' => true,
            'timestamp' => time(),
            'stats' => $stats,
            'tables' => $tables,
            'orders' => $activeOrders,
        ]);
    }

    /**
     * Update kitchen order status with transition validation.
     */
    public function updateStatus(Request $request, array $params): Response
    {
        $this->requireRoles(['owner', 'manager', 'kitchen']);

        $orderId = (int)($params['id'] ?? 0);
        $newStatus = trim((string)($request->input('status') ?? ''));

        $allowedTransitions = [
            'new' => ['accepted', 'preparing', 'cancelled'],
            'pending' => ['accepted', 'confirmed', 'preparing', 'cancelled'],
            'accepted' => ['preparing', 'cancelled'],
            'confirmed' => ['preparing', 'cancelled'],
            'preparing' => ['ready', 'cancelled'],
            'ready' => ['served', 'completed'],
            'served' => ['completed'],
        ];

        $order = Database::fetchOne("SELECT id, order_number, status, table_id FROM orders WHERE id = :id", [':id' => $orderId]);
        if (!$order) {
            return $this->json(['success' => false, 'error' => 'Order not found.'], 404);
        }

        $currentStatus = $order['status'];
        $validTargets = $allowedTransitions[$currentStatus] ?? [];

        if (!in_array($newStatus, $validTargets, true)) {
            return $this->json([
                'success' => false,
                'error' => "Invalid transition from [{$currentStatus}] to [{$newStatus}].",
            ], 422);
        }

        Database::transaction(function ($pdo) use ($orderId, $newStatus, $currentStatus, $order) {
            // Update order status
            Database::execute("
                UPDATE orders
                SET status = :st, updated_at = :dt
                WHERE id = :id
            ", [
                ':st' => $newStatus,
                ':dt' => gmdate('Y-m-d H:i:s'),
                ':id' => $orderId,
            ]);

            // Map kitchen ticket status
            $ktStatus = 'in_kitchen';
            if ($newStatus === 'ready' || $newStatus === 'served' || $newStatus === 'completed') {
                $ktStatus = 'completed';
            }

            Database::execute("
                UPDATE kitchen_tickets
                SET status = :kt
                WHERE order_id = :oid
            ", [
                ':kt' => $ktStatus,
                ':oid' => $orderId,
            ]);

            // Record status event
            $user = Session::user();
            Database::execute("
                INSERT INTO order_status_events (order_id, status, notes, created_by_user_id, created_at)
                VALUES (:oid, :st, :notes, :uid, :dt)
            ", [
                ':oid' => $orderId,
                ':st' => $newStatus,
                ':notes' => "Kitchen advanced status to {$newStatus}",
                ':uid' => $user['id'] ?? null,
                ':dt' => gmdate('Y-m-d H:i:s'),
            ]);
        });

        Logger::info("Kitchen updated order {$order['order_number']} to {$newStatus}", [
            'order_id' => $orderId,
            'previous_status' => $currentStatus,
            'new_status' => $newStatus,
        ]);

        return $this->json([
            'success' => true,
            'order_id' => $orderId,
            'order_number' => $order['order_number'],
            'new_status' => $newStatus,
        ]);
    }

    /**
     * Thermal KOT printable template (58mm / 80mm).
     */
    public function kot(Request $request, array $params): Response
    {
        $this->requireRoles(['owner', 'manager', 'kitchen', 'cashier']);

        $orderId = (int)($params['id'] ?? 0);
        $order = Database::fetchOne("
            SELECT o.*, kt.id AS ticket_id, kt.ticket_number, kt.printed_at
            FROM orders o
            LEFT JOIN kitchen_tickets kt ON kt.order_id = o.id
            WHERE o.id = :id
        ", [':id' => $orderId]);

        if (!$order) {
            return $this->notFound("Order not found.");
        }

        $items = Database::fetchAll("
            SELECT * FROM order_items WHERE order_id = :oid ORDER BY id ASC
        ", [':oid' => $orderId]);

        // Check if reprint
        $isReprint = !empty($order['printed_at']);
        $reprintCount = 0;

        if ($isReprint) {
            $prevJobs = (int)Database::fetchValue("
                SELECT COUNT(*) FROM print_jobs WHERE order_id = :oid AND job_type = 'kot'
            ", [':oid' => $orderId]);
            $reprintCount = $prevJobs;
        }

        // Record print job
        Database::transaction(function ($pdo) use ($orderId, $order, $isReprint, $reprintCount) {
            // Update ticket printed_at
            Database::execute("
                UPDATE kitchen_tickets
                SET printed_at = :dt, status = 'in_kitchen'
                WHERE order_id = :oid
            ", [
                ':dt' => gmdate('Y-m-d H:i:s'),
                ':oid' => $orderId,
            ]);

            Database::execute("
                INSERT INTO print_jobs (ticket_id, order_id, job_type, content_text, status, is_reprint, reprint_count, created_at)
                VALUES (:tid, :oid, 'kot', :txt, 'printed', :is_rep, :rep_cnt, :dt)
            ", [
                ':tid' => $order['ticket_id'] ?? null,
                ':oid' => $orderId,
                ':txt' => "KOT Order #{$order['order_number']}",
                ':is_rep' => $isReprint ? 1 : 0,
                ':rep_cnt' => $reprintCount + ($isReprint ? 1 : 0),
                ':dt' => gmdate('Y-m-d H:i:s'),
            ]);
        });

        $this->render('kitchen.kot', [
            'order' => $order,
            'items' => $items,
            'isReprint' => $isReprint,
            'reprintCount' => $reprintCount + ($isReprint ? 1 : 0),
        ]);
    }

    /**
     * Get 3D table overview data with live order info.
     */
    private function getTablesOverview(): array
    {
        $tables = Database::fetchAll("SELECT * FROM restaurant_tables ORDER BY table_number ASC");

        $activeOrders = Database::fetchAll("
            SELECT id, order_number, table_id, status, created_at
            FROM orders
            WHERE table_id IS NOT NULL AND status NOT IN ('served', 'completed', 'cancelled')
        ");

        $orderMap = [];
        foreach ($activeOrders as $ao) {
            $orderMap[(int)$ao['table_id']] = $ao;
        }

        foreach ($tables as &$tbl) {
            $num = (int)$tbl['table_number'];
            if (isset($orderMap[$num])) {
                $tbl['is_occupied'] = true;
                $tbl['active_order'] = $orderMap[$num];
            } else {
                $tbl['is_occupied'] = false;
                $tbl['active_order'] = null;
            }
        }
        unset($tbl);

        return $tables;
    }

    /**
     * Fetch all active orders for the kitchen screen, redacting PII.
     */
    private function getActiveOrders(): array
    {
        $orders = Database::fetchAll("
            SELECT o.id, o.order_number, o.table_id, o.channel, o.order_type,
                   o.status, o.special_instructions, o.created_at,
                   kt.ticket_number, kt.printed_at
            FROM orders o
            LEFT JOIN kitchen_tickets kt ON kt.order_id = o.id
            WHERE o.status IN ('new', 'accepted', 'pending', 'confirmed', 'preparing', 'ready')
            ORDER BY o.id ASC
        ");

        if (empty($orders)) {
            return [];
        }

        $orderIds = array_column($orders, 'id');
        $inClause = implode(',', array_map('intval', $orderIds));

        $allItems = Database::fetchAll("
            SELECT order_id, item_name, variant_name, quantity, notes
            FROM order_items
            WHERE order_id IN ({$inClause})
            ORDER BY id ASC
        ");

        $itemMap = [];
        foreach ($allItems as $it) {
            $itemMap[(int)$it['order_id']][] = $it;
        }

        $now = time();
        foreach ($orders as &$ord) {
            $ord['items'] = $itemMap[(int)$ord['id']] ?? [];

            // Compute elapsed preparation time in minutes
            $createdTs = strtotime($ord['created_at']);
            $elapsedMinutes = max(0, (int)floor(($now - $createdTs) / 60));
            $ord['elapsed_minutes'] = $elapsedMinutes;
            $ord['is_delayed'] = ($elapsedMinutes >= 20); // Flag if waiting over 20 mins
        }
        unset($ord);

        return $orders;
    }

    /**
     * Quick stats for KDS header.
     */
    private function getKitchenStats(): array
    {
        $newOrders = (int)Database::fetchValue("
            SELECT COUNT(*) FROM orders WHERE status IN ('new', 'accepted', 'pending', 'confirmed')
        ");

        $preparingOrders = (int)Database::fetchValue("
            SELECT COUNT(*) FROM orders WHERE status = 'preparing'
        ");

        $readyOrders = (int)Database::fetchValue("
            SELECT COUNT(*) FROM orders WHERE status = 'ready'
        ");

        $todayServed = (int)Database::fetchValue("
            SELECT COUNT(*) FROM orders
            WHERE status IN ('served', 'completed')
              AND DATE(created_at) = DATE('now')
        ");

        return [
            'new' => $newOrders,
            'preparing' => $preparingOrders,
            'ready' => $readyOrders,
            'served_today' => $todayServed,
        ];
    }
}
