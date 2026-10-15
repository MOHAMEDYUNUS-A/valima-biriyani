<?php

declare(strict_types=1);

namespace Database\Seeds;

use PDO;

class SettingsSeeder
{
    public static function run(PDO $pdo): void
    {
        $isMysql = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql';

        $settings = [
            ['key' => 'restaurant_name', 'value' => 'Valima Biriyani', 'group' => 'general'],
            ['key' => 'restaurant_tagline', 'value' => 'Authentic Traditional Dum Biriyani & More', 'group' => 'general'],
            ['key' => 'restaurant_phone', 'value' => '+91 98765 43210', 'group' => 'contact'],
            ['key' => 'restaurant_whatsapp', 'value' => '+919876543210', 'group' => 'contact'],
            ['key' => 'restaurant_address', 'value' => 'Grand Southern Trunk Rd, Chennai, Tamil Nadu 600045', 'group' => 'contact'],
            ['key' => 'currency_symbol', 'value' => '₹', 'group' => 'billing'],
            ['key' => 'currency_code', 'value' => 'INR', 'group' => 'billing'],
            ['key' => 'default_tax_percent', 'value' => '5.00', 'group' => 'billing'],
            ['key' => 'delivery_fee_base', 'value' => '40.00', 'group' => 'delivery'],
            ['key' => 'min_order_amount', 'value' => '100.00', 'group' => 'ordering'],
            ['key' => 'online_ordering_enabled', 'value' => '1', 'group' => 'ordering'],
            ['key' => 'dinein_enabled', 'value' => '1', 'group' => 'ordering'],
            ['key' => 'pickup_enabled', 'value' => '1', 'group' => 'ordering'],
            ['key' => 'delivery_enabled', 'value' => '1', 'group' => 'ordering'],
        ];

        $stmt = $isMysql
            ? $pdo->prepare("INSERT INTO restaurant_settings (setting_key, setting_value, group_name) VALUES (:key, :value, :group) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)")
            : $pdo->prepare("INSERT OR IGNORE INTO restaurant_settings (setting_key, setting_value, group_name) VALUES (:key, :value, :group)");

        foreach ($settings as $s) {
            $stmt->execute([
                ':key' => $s['key'],
                ':value' => $s['value'],
                ':group' => $s['group'],
            ]);
        }

        // Loyalty Settings
        $loyalty = [
            ['key' => 'points_per_order', 'value' => '10', 'desc' => 'Fixed loyalty points awarded for each completed order'],
            ['key' => 'point_value_inr', 'value' => '1.00', 'desc' => 'Value in INR for 1 loyalty point when redeemed'],
            ['key' => 'min_redemption_points', 'value' => '50', 'desc' => 'Minimum points required to redeem on an order'],
            ['key' => 'max_redemption_per_order', 'value' => '200', 'desc' => 'Maximum points that can be redeemed on a single order'],
        ];

        $lStmt = $isMysql
            ? $pdo->prepare("INSERT INTO loyalty_settings (setting_key, setting_value, description) VALUES (:key, :value, :desc) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)")
            : $pdo->prepare("INSERT OR IGNORE INTO loyalty_settings (setting_key, setting_value, description) VALUES (:key, :value, :desc)");

        foreach ($loyalty as $l) {
            $lStmt->execute([
                ':key' => $l['key'],
                ':value' => $l['value'],
                ':desc' => $l['desc'],
            ]);
        }
    }
}
