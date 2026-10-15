<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Database;
use App\Helpers\Request;
use App\Helpers\Response;
use App\Helpers\Security;
use App\Helpers\Session;

class TrackingController extends BaseController
{
    /**
     * Customer live tracking page accessed via unguessable tracking token.
     */
    public function show(Request $request, string $token): void
    {
        $order = Database::fetchOne("
            SELECT o.id, o.order_number, o.tracking_token, o.table_id, o.order_type,
                   o.status, o.subtotal, o.tax_amount, o.delivery_fee, o.total_amount,
                   o.estimated_prep_minutes, o.created_at, o.updated_at
            FROM orders o
            WHERE o.tracking_token = :tok
        ", [':tok' => $token]);

        if (!$order) {
            http_response_code(404);
            $this->render('tracking.not_found', [
                'token' => $token,
                'title' => 'Order Not Found',
            ], 'main');
            return;
        }

        $items = Database::fetchAll("
            SELECT item_name, variant_name, unit_price, quantity, subtotal, notes
            FROM order_items
            WHERE order_id = :oid
            ORDER BY id ASC
        ", [':oid' => $order['id']]);

        $events = Database::fetchAll("
            SELECT status, notes, created_at
            FROM order_status_events
            WHERE order_id = :oid
            ORDER BY id ASC
        ", [':oid' => $order['id']]);

        $delivery = null;
        if ($order['order_type'] === 'delivery') {
            $delivery = Database::fetchOne("
                SELECT provider_name, booking_reference, tracking_url, status, delivery_notes, updated_at
                FROM delivery_bookings
                WHERE order_id = :oid
            ", [':oid' => $order['id']]);
        }

        $stepIndex = $this->calculateStepIndex($order['status']);

        $this->render('tracking.show', [
            'order' => $order,
            'items' => $items,
            'events' => $events,
            'delivery' => $delivery,
            'stepIndex' => $stepIndex,
            'title' => "Track Order #{$order['order_number']}",
        ], 'main');
    }

    /**
     * Live JSON polling endpoint for customer tracking page.
     */
    public function statusApi(Request $request, string $token): void
    {
        $order = Database::fetchOne("
            SELECT id, order_number, status, order_type, updated_at
            FROM orders
            WHERE tracking_token = :tok
        ", [':tok' => $token]);

        if (!$order) {
            $this->error('Tracking token not found.', 404);
            return;
        }

        $delivery = null;
        if ($order['order_type'] === 'delivery') {
            $delivery = Database::fetchOne("
                SELECT provider_name, booking_reference, tracking_url, status, updated_at
                FROM delivery_bookings
                WHERE order_id = :oid
            ", [':oid' => $order['id']]);
        }

        $events = Database::fetchAll("
            SELECT status, notes, created_at
            FROM order_status_events
            WHERE order_id = :oid
            ORDER BY id DESC LIMIT 5
        ", [':oid' => $order['id']]);

        $this->json([
            'success' => true,
            'status' => $order['status'],
            'step_index' => $this->calculateStepIndex($order['status']),
            'delivery' => $delivery,
            'recent_events' => $events,
            'updated_at' => $order['updated_at'],
        ]);
    }

    /**
     * "Add More Items" handler: preserves table session and redirects to menu (Fixes KMS bug #15).
     */
    public function addMoreItems(Request $request, string $token): void
    {
        $order = Database::fetchOne("
            SELECT order_number, table_id, order_type, customer_name, customer_phone
            FROM orders
            WHERE tracking_token = :tok
        ", [':tok' => $token]);

        if (!$order) {
            $this->redirect('/menu');
            return;
        }

        // Restore customer & table context in session
        if (!empty($order['table_id'])) {
            Session::set('dine_in_table', (int)$order['table_id']);
        }
        if (!empty($order['customer_name'])) {
            Session::set('customer_name', $order['customer_name']);
        }
        if (!empty($order['customer_phone'])) {
            Session::set('customer_phone', $order['customer_phone']);
        }
        Session::set('parent_order_number', $order['order_number']);

        Session::flash('success', "Adding more items to Table {$order['table_id']} (Ref: #{$order['order_number']})");
        $this->redirect('/menu');
    }

    /**
     * Map order status to progress step (0 to 4).
     */
    private function calculateStepIndex(string $status): int
    {
        return match ($status) {
            'new', 'pending', 'accepted' => 1, // Order Confirmed
            'preparing' => 2,                 // In Kitchen / Cooking
            'ready' => 3,                     // Ready / Packed
            'out_for_delivery' => 3,          // In Transit
            'served', 'completed', 'delivered' => 4, // Completed
            'cancelled' => -1,                // Cancelled
            default => 1,
        };
    }
}
