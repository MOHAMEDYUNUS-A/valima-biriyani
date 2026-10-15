<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Database;
use App\Helpers\Request;
use App\Helpers\Response;
use App\Helpers\Session;
use App\Helpers\Logger;

class DeliveryController extends BaseController
{
    /**
     * Update manual delivery booking (e.g. Rapido Parcel booking reference and status).
     */
    public function update(Request $request, string $orderId): void
    {
        $this->requireRoles(['owner', 'manager', 'cashier']);

        $oid = (int)$orderId;
        $order = Database::fetchOne("SELECT id, order_number, order_type, status FROM orders WHERE id = :id", [':id' => $oid]);
        if (!$order) {
            $this->error('Order not found.', 404);
            return;
        }

        $provider = trim((string)$request->input('provider_name', 'rapido'));
        $bookingRef = trim((string)$request->input('booking_reference', ''));
        $deliveryFee = (float)$request->input('delivery_fee', 0.0);
        $trackingUrl = trim((string)$request->input('tracking_url', ''));
        $status = trim((string)$request->input('status', 'booked'));
        $notes = trim((string)$request->input('delivery_notes', ''));

        $validStatuses = [
            'awaiting_booking', 'booked', 'pickup_pending', 'picked_up',
            'in_transit', 'delivered', 'failed', 'cancelled'
        ];

        if (!in_array($status, $validStatuses, true)) {
            $status = 'booked';
        }

        $user = Session::user();
        $now = gmdate('Y-m-d H:i:s');

        Database::transaction(function ($pdo) use ($oid, $provider, $bookingRef, $deliveryFee, $trackingUrl, $status, $notes, $user, $now) {
            // Check if record exists
            $existing = Database::fetchOne("SELECT id FROM delivery_bookings WHERE order_id = :oid", [':oid' => $oid]);

            if ($existing) {
                Database::execute("
                    UPDATE delivery_bookings
                    SET provider_name = :p, booking_reference = :ref, delivery_fee = :fee,
                        tracking_url = :url, status = :st, delivery_notes = :notes,
                        booked_by_user_id = :uid, updated_at = :dt
                    WHERE order_id = :oid
                ", [
                    ':p' => $provider,
                    ':ref' => $bookingRef,
                    ':fee' => $deliveryFee,
                    ':url' => $trackingUrl,
                    ':st' => $status,
                    ':notes' => $notes,
                    ':uid' => $user['id'] ?? null,
                    ':dt' => $now,
                    ':oid' => $oid,
                ]);
            } else {
                Database::execute("
                    INSERT INTO delivery_bookings (order_id, provider_name, booking_reference, delivery_fee, tracking_url, status, delivery_notes, booked_by_user_id, booked_at, updated_at)
                    VALUES (:oid, :p, :ref, :fee, :url, :st, :notes, :uid, :dt, :dt)
                ", [
                    ':oid' => $oid,
                    ':p' => $provider,
                    ':ref' => $bookingRef,
                    ':fee' => $deliveryFee,
                    ':url' => $trackingUrl,
                    ':st' => $status,
                    ':notes' => $notes,
                    ':uid' => $user['id'] ?? null,
                    ':dt' => $now,
                ]);
            }

            // Sync with main order status where appropriate
            if ($status === 'in_transit' || $status === 'picked_up') {
                Database::execute("UPDATE orders SET status = 'out_for_delivery', updated_at = :dt WHERE id = :oid", [
                    ':dt' => $now,
                    ':oid' => $oid,
                ]);
            } elseif ($status === 'delivered') {
                Database::execute("UPDATE orders SET status = 'completed', updated_at = :dt WHERE id = :oid", [
                    ':dt' => $now,
                    ':oid' => $oid,
                ]);
            }

            // Audit status event
            Database::execute("
                INSERT INTO order_status_events (order_id, status, notes, created_by_user_id, created_at)
                VALUES (:oid, :st, :notes, :uid, :dt)
            ", [
                ':oid' => $oid,
                ':st' => $status,
                ':notes' => "Delivery updated ({$provider}): {$bookingRef} - {$status}",
                ':uid' => $user['id'] ?? null,
                ':dt' => $now,
            ]);
        });

        Logger::info("Delivery updated for order {$order['order_number']}", [
            'order_id' => $oid,
            'provider' => $provider,
            'status' => $status,
            'reference' => $bookingRef,
        ]);

        if ($request->isJson()) {
            $this->json(['success' => true, 'order_id' => $oid, 'status' => $status]);
            return;
        }

        Session::flash('success', "Delivery booking updated successfully for order #{$order['order_number']}.");
        $this->redirect("/admin/orders/{$oid}");
    }
}
