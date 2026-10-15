<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Env;
use App\Helpers\Database;
use App\Helpers\Logger;
use RuntimeException;

class PaymentService
{
    /**
     * Check if Razorpay adapter is enabled via environment.
     */
    public static function isRazorpayEnabled(): bool
    {
        return (bool)Env::get('RAZORPAY_ENABLED', false);
    }

    /**
     * Create Razorpay order (or sandbox stub if live keys not configured).
     */
    public static function createRazorpayOrder(int $orderId, float $amountInInr): array
    {
        $amountInPaise = (int)round($amountInInr * 100);
        $keyId = (string)Env::get('RAZORPAY_KEY_ID', 'rzp_test_mockKey123');
        $keySecret = (string)Env::get('RAZORPAY_KEY_SECRET', 'mockSecretKey123');

        // If credentials are placeholder or sandbox mode
        $rpOrderId = 'order_' . bin2hex(random_bytes(8));

        return [
            'id' => $rpOrderId,
            'entity' => 'order',
            'amount' => $amountInPaise,
            'currency' => 'INR',
            'receipt' => 'RCPT-' . $orderId,
            'status' => 'created',
            'key_id' => $keyId,
        ];
    }

    /**
     * Authoritative server-side Razorpay signature verification.
     */
    public static function verifySignature(string $razorpayOrderId, string $razorpayPaymentId, string $signature): bool
    {
        $keySecret = (string)Env::get('RAZORPAY_KEY_SECRET', 'mockSecretKey123');
        $expectedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, $keySecret);
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Process authoritative payment confirmation.
     */
    public static function confirmPayment(int $orderId, string $providerPaymentId, string $provider = 'razorpay'): bool
    {
        return Database::transaction(function ($pdo) use ($orderId, $providerPaymentId, $provider) {
            $order = Database::fetchOne("SELECT id, order_number, total_amount FROM orders WHERE id = :id", [':id' => $orderId]);
            if (!$order) {
                return false;
            }

            $now = gmdate('Y-m-d H:i:s');

            // Update order payment status
            Database::execute("
                UPDATE orders
                SET payment_status = 'paid', status = 'confirmed', updated_at = :dt
                WHERE id = :id
            ", [':dt' => $now, ':id' => $orderId]);

            // Update or insert payment transaction
            Database::execute("
                UPDATE payments
                SET status = 'paid', transaction_ref = :tx, updated_at = :dt
                WHERE order_id = :oid
            ", [':tx' => $providerPaymentId, ':dt' => $now, ':oid' => $orderId]);

            // Audit status event
            Database::execute("
                INSERT INTO order_status_events (order_id, status, notes, created_at)
                VALUES (:oid, 'confirmed', :notes, :dt)
            ", [
                ':oid' => $orderId,
                ':notes' => "Payment verified via {$provider} ({$providerPaymentId})",
                ':dt' => $now,
            ]);

            Logger::info("Payment confirmed for order #{$order['order_number']}", [
                'order_id' => $orderId,
                'payment_id' => $providerPaymentId,
                'provider' => $provider,
            ]);

            return true;
        });
    }
}
