<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;
use App\Helpers\Logger;
use RuntimeException;

class LoyaltyService
{
    /**
     * Get loyalty configuration settings.
     */
    public static function getSettings(): array
    {
        $rows = Database::fetchAll("SELECT setting_key, setting_value FROM loyalty_settings");
        $settings = [
            'points_per_order' => 10,
            'min_redemption_points' => 50,
            'rupee_per_point' => 1.0,
            'max_redemption_per_order' => 100,
            'enabled' => 1,
        ];

        foreach ($rows as $row) {
            $key = $row['setting_key'];
            $val = $row['setting_value'];
            if ($key === 'points_per_order' || $key === 'min_redemption_points' || $key === 'max_redemption_per_order') {
                $settings[$key] = (int)$val;
            } elseif ($key === 'rupee_per_point') {
                $settings[$key] = (float)$val;
            } elseif ($key === 'enabled') {
                $settings[$key] = (int)$val;
            }
        }

        return $settings;
    }

    /**
     * Award loyalty points when an order completes (strictly idempotent).
     */
    public static function awardOrderPoints(int $orderId): ?array
    {
        $settings = self::getSettings();
        if (empty($settings['enabled'])) {
            return null;
        }

        return Database::transaction(function ($pdo) use ($orderId, $settings) {
            $order = Database::fetchOne("
                SELECT id, order_number, customer_id, status, payment_status, total_amount
                FROM orders
                WHERE id = :id
            ", [':id' => $orderId]);

            if (!$order || empty($order['customer_id'])) {
                return null;
            }

            // Must be completed or served
            $eligibleStatuses = ['completed', 'served', 'delivered'];
            if (!in_array($order['status'], $eligibleStatuses, true)) {
                return null;
            }

            $customerId = (int)$order['customer_id'];

            // Idempotency check: has this order already been awarded points?
            $alreadyAwarded = Database::fetchOne("
                SELECT id, points, balance_after
                FROM loyalty_transactions
                WHERE order_id = :oid AND type = 'earn'
            ", [':oid' => $orderId]);

            if ($alreadyAwarded) {
                return [
                    'awarded' => false,
                    'reason' => 'Already awarded',
                    'points' => (int)$alreadyAwarded['points'],
                    'balance_after' => (int)$alreadyAwarded['balance_after'],
                ];
            }

            $pointsToAward = (int)$settings['points_per_order'];
            $now = gmdate('Y-m-d H:i:s');

            // Fetch customer and lock row
            $customer = Database::fetchOne("SELECT id, loyalty_points FROM customers WHERE id = :cid", [':cid' => $customerId]);
            if (!$customer) {
                return null;
            }

            $newBalance = (int)$customer['loyalty_points'] + $pointsToAward;

            // 1. Update customer balance, total spend, order count
            Database::execute("
                UPDATE customers
                SET loyalty_points = :pts,
                    lifetime_spend = lifetime_spend + :spend,
                    total_orders = total_orders + 1,
                    last_order_at = :dt,
                    updated_at = :dt
                WHERE id = :cid
            ", [
                ':pts' => $newBalance,
                ':spend' => (float)$order['total_amount'],
                ':dt' => $now,
                ':cid' => $customerId,
            ]);

            // 2. Insert into immutable ledger
            Database::execute("
                INSERT INTO loyalty_transactions (customer_id, order_id, type, points, balance_after, description, created_at)
                VALUES (:cid, :oid, 'earn', :pts, :bal, :desc, :dt)
            ", [
                ':cid' => $customerId,
                ':oid' => $orderId,
                ':pts' => $pointsToAward,
                ':bal' => $newBalance,
                ':desc' => "Points earned for Order #{$order['order_number']}",
                ':dt' => $now,
            ]);

            Logger::info("Loyalty awarded: {$pointsToAward} pts to customer ID {$customerId} for order #{$order['order_number']}");

            return [
                'awarded' => true,
                'points' => $pointsToAward,
                'balance_after' => $newBalance,
            ];
        });
    }

    /**
     * Redeem loyalty points against an order.
     */
    public static function redeemPoints(int $customerId, int $pointsToRedeem, ?int $orderId = null): array
    {
        $settings = self::getSettings();
        if ($pointsToRedeem <= 0) {
            throw new RuntimeException("Invalid redemption points.");
        }

        if ($pointsToRedeem < $settings['min_redemption_points']) {
            throw new RuntimeException("Minimum {$settings['min_redemption_points']} points required for redemption.");
        }

        if ($pointsToRedeem > $settings['max_redemption_per_order']) {
            throw new RuntimeException("Maximum redemption limit is {$settings['max_redemption_per_order']} points per order.");
        }

        return Database::transaction(function ($pdo) use ($customerId, $pointsToRedeem, $orderId, $settings) {
            $customer = Database::fetchOne("SELECT id, loyalty_points FROM customers WHERE id = :cid", [':cid' => $customerId]);
            if (!$customer) {
                throw new RuntimeException("Customer not found.");
            }

            $currentBalance = (int)$customer['loyalty_points'];
            if ($currentBalance < $pointsToRedeem) {
                throw new RuntimeException("Insufficient loyalty balance. Available: {$currentBalance} points.");
            }

            $newBalance = $currentBalance - $pointsToRedeem;
            $discountValue = round($pointsToRedeem * $settings['rupee_per_point'], 2);
            $now = gmdate('Y-m-d H:i:s');

            // Deduct points
            Database::execute("
                UPDATE customers
                SET loyalty_points = :bal, updated_at = :dt
                WHERE id = :cid
            ", [
                ':bal' => $newBalance,
                ':dt' => $now,
                ':cid' => $customerId,
            ]);

            // Record transaction in ledger
            Database::execute("
                INSERT INTO loyalty_transactions (customer_id, order_id, type, points, balance_after, description, created_at)
                VALUES (:cid, :oid, 'redeem', :pts, :bal, :desc, :dt)
            ", [
                ':cid' => $customerId,
                ':oid' => $orderId,
                ':pts' => -$pointsToRedeem,
                ':bal' => $newBalance,
                ':desc' => "Redeemed {$pointsToRedeem} points for ₹{$discountValue} discount",
                ':dt' => $now,
            ]);

            Logger::info("Loyalty redeemed: {$pointsToRedeem} points by customer {$customerId}");

            return [
                'success' => true,
                'redeemed_points' => $pointsToRedeem,
                'discount_value' => $discountValue,
                'balance_after' => $newBalance,
            ];
        });
    }

    /**
     * Reverse awarded points if order is cancelled or refunded.
     */
    public static function reverseOrderPoints(int $orderId, string $reason = 'Order cancelled/refunded'): bool
    {
        return Database::transaction(function ($pdo) use ($orderId, $reason) {
            $earnTx = Database::fetchOne("
                SELECT customer_id, points
                FROM loyalty_transactions
                WHERE order_id = :oid AND type = 'earn'
            ", [':oid' => $orderId]);

            if (!$earnTx) {
                return false; // Nothing to reverse
            }

            // Check if already reversed
            $alreadyReversed = Database::fetchOne("
                SELECT id FROM loyalty_transactions
                WHERE order_id = :oid AND type = 'reverse'
            ", [':oid' => $orderId]);

            if ($alreadyReversed) {
                return false;
            }

            $customerId = (int)$earnTx['customer_id'];
            $pointsToDeduct = (int)$earnTx['points'];

            $customer = Database::fetchOne("SELECT loyalty_points FROM customers WHERE id = :cid", [':cid' => $customerId]);
            if (!$customer) {
                return false;
            }

            $newBalance = max(0, (int)$customer['loyalty_points'] - $pointsToDeduct);
            $now = gmdate('Y-m-d H:i:s');

            Database::execute("UPDATE customers SET loyalty_points = :bal, updated_at = :dt WHERE id = :cid", [
                ':bal' => $newBalance,
                ':dt' => $now,
                ':cid' => $customerId,
            ]);

            Database::execute("
                INSERT INTO loyalty_transactions (customer_id, order_id, type, points, balance_after, description, created_at)
                VALUES (:cid, :oid, 'reverse', :pts, :bal, :desc, :dt)
            ", [
                ':cid' => $customerId,
                ':oid' => $orderId,
                ':pts' => -$pointsToDeduct,
                ':bal' => $newBalance,
                ':desc' => "Points reversed: {$reason}",
                ':dt' => $now,
            ]);

            Logger::info("Loyalty reversed: {$pointsToDeduct} pts for order ID {$orderId}");

            return true;
        });
    }
}
