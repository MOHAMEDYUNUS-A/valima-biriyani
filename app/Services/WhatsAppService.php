<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Env;
use App\Helpers\Database;
use App\Helpers\Logger;

class WhatsAppService
{
    /**
     * Check if paid WhatsApp Cloud API is enabled (default false).
     */
    public static function isCloudApiEnabled(): bool
    {
        return (bool)Env::get('WHATSAPP_API_ENABLED', false);
    }

    /**
     * Generate free WhatsApp click-to-chat link with prefilled message.
     */
    public static function generateClickToChatUrl(string $phone, string $message): string
    {
        // Strip non-numeric characters
        $cleanPhone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '91' . substr($cleanPhone, 1);
        } elseif (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone;
        }

        return "https://wa.me/{$cleanPhone}?text=" . rawurlencode($message);
    }

    /**
     * Generate Order Confirmation message with tracking link.
     */
    public static function getOrderConfirmationMessage(array $order, string $trackingUrl): string
    {
        return "✨ *VALIMA BIRIYANI — ORDER CONFIRMED* ✨\n\n"
            . "Dear {$order['customer_name']},\n"
            . "Thank you for your order! Your hot dum biriyani is now in our kitchen.\n\n"
            . "📦 *Order Number:* #{$order['order_number']}\n"
            . "💰 *Total Amount:* ₹" . number_format((float)$order['total_amount'], 2) . "\n"
            . "🍛 *Type:* " . ucfirst($order['order_type']) . "\n\n"
            . "🔗 *Track Live Preparation & Delivery:*\n"
            . "{$trackingUrl}\n\n"
            . "📞 Contact us: +91 98765 43210\n"
            . "Valima Biriyani — Biriyani Specialist";
    }

    /**
     * Send or log notification.
     */
    public static function notify(string $recipientPhone, string $eventType, string $message): array
    {
        $now = gmdate('Y-m-d H:i:s');
        $isCloud = self::isCloudApiEnabled();
        $status = $isCloud ? 'pending' : 'wa_me_link_ready';

        Database::execute("
            INSERT INTO notification_logs (channel, recipient, event_type, status, provider_response, created_at)
            VALUES ('whatsapp', :rec, :evt, :st, :resp, :dt)
        ", [
            ':rec' => $recipientPhone,
            ':evt' => $eventType,
            ':st' => $status,
            ':resp' => json_encode(['mode' => $isCloud ? 'cloud_api' : 'free_click_to_chat']),
            ':dt' => $now,
        ]);

        return [
            'success' => true,
            'channel' => 'whatsapp',
            'mode' => $isCloud ? 'cloud_api' : 'free_click_to_chat',
            'click_to_chat_url' => self::generateClickToChatUrl($recipientPhone, $message),
        ];
    }
}
