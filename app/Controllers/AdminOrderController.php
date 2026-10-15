<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Request;
use App\Helpers\Database;
use App\Helpers\Session;
use App\Helpers\Logger;
use RuntimeException;

class AdminOrderController extends BaseController
{
    public function index(Request $request): void
    {
        $statusFilter = $request->input('status');
        $channelFilter = $request->input('channel');
        $search = trim((string)$request->input('q', ''));

        $sql = "SELECT o.*, t.table_number
                FROM orders o
                LEFT JOIN restaurant_tables t ON o.table_id = t.id
                WHERE 1=1";
        $params = [];

        if (!empty($statusFilter)) {
            $sql .= " AND o.status = :status";
            $params[':status'] = $statusFilter;
        }

        if (!empty($channelFilter)) {
            $sql .= " AND o.channel = :channel";
            $params[':channel'] = $channelFilter;
        }

        if (!empty($search)) {
            $sql .= " AND (o.order_number LIKE :q OR o.customer_name LIKE :q2 OR o.customer_phone LIKE :q3)";
            $params[':q'] = "%{$search}%";
            $params[':q2'] = "%{$search}%";
            $params[':q3'] = "%{$search}%";
        }

        $sql .= " ORDER BY o.id DESC LIMIT 100";
        $orders = Database::query($sql, $params);

        $this->render('admin.orders.index', [
            'title' => 'Live Orders Management',
            'currentPage' => 'orders',
            'orders' => $orders,
            'statusFilter' => $statusFilter,
            'channelFilter' => $channelFilter,
            'search' => $search,
        ], 'admin');
    }

    public function show(Request $request, string $id): void
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
            Session::flash('error', 'Order not found.');
            $this->redirect('/admin/orders');
        }

        $items = Database::query(
            "SELECT * FROM order_items WHERE order_id = :id ORDER BY id ASC",
            [':id' => $orderId]
        );

        $events = Database::query(
            "SELECT e.*, u.name as user_name
             FROM order_status_events e
             LEFT JOIN users u ON e.created_by_user_id = u.id
             WHERE e.order_id = :id
             ORDER BY e.created_at ASC",
            [':id' => $orderId]
        );

        $payments = Database::query(
            "SELECT * FROM payments WHERE order_id = :id ORDER BY id ASC",
            [':id' => $orderId]
        );

        $this->render('admin.orders.show', [
            'title' => "Order #{$order['order_number']}",
            'currentPage' => 'orders',
            'order' => $order,
            'items' => $items,
            'events' => $events,
            'payments' => $payments,
        ], 'admin');
    }

    public function updateStatus(Request $request, string $id): void
    {
        $orderId = (int)$id;
        $newStatus = trim((string)$request->input('status', ''));
        $notes = trim((string)$request->input('notes', ''));

        $validStatuses = ['new', 'accepted', 'preparing', 'ready', 'out_for_delivery', 'served', 'completed', 'cancelled'];
        if (!in_array($newStatus, $validStatuses, true)) {
            Session::flash('error', "Invalid order status: {$newStatus}");
            $this->redirect("/admin/orders/{$orderId}");
        }

        $order = Database::queryOne("SELECT * FROM orders WHERE id = :id", [':id' => $orderId]);
        if (!$order) {
            Session::flash('error', 'Order not found.');
            $this->redirect('/admin/orders');
        }

        $userId = Session::user()['id'] ?? null;
        $userName = Session::user()['name'] ?? 'Staff';

        Database::transaction(function ($pdo) use ($orderId, $newStatus, $notes, $userId, $order) {
            $paymentStatusUpdate = '';
            if ($newStatus === 'completed' || $newStatus === 'served') {
                $paymentStatusUpdate = ", payment_status = 'paid'";
                Database::execute("UPDATE payments SET status = 'captured' WHERE order_id = :id", [':id' => $orderId]);
            }

            Database::execute(
                "UPDATE orders SET status = :status {$paymentStatusUpdate}, updated_at = :dt WHERE id = :id",
                [':status' => $newStatus, ':dt' => gmdate('Y-m-d H:i:s'), ':id' => $orderId]
            );

            Database::execute(
                "INSERT INTO order_status_events (order_id, status, notes, created_by_user_id, created_at)
                 VALUES (:oid, :status, :notes, :uid, :dt)",
                [
                    ':oid' => $orderId,
                    ':status' => $newStatus,
                    ':notes' => $notes ?: "Status changed from {$order['status']} to {$newStatus}",
                    ':uid' => $userId,
                    ':dt' => gmdate('Y-m-d H:i:s'),
                ]
            );
        });

        Logger::audit('ORDER_STATUS_UPDATED', $userName, [
            'order_id' => $orderId,
            'from' => $order['status'],
            'to' => $newStatus,
        ]);

        Session::flash('success', "Order status updated to " . ucfirst($newStatus) . ".");
        $this->redirect("/admin/orders/{$orderId}");
    }
}
