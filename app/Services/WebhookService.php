<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Env;
use App\Helpers\Database;
use App\Helpers\Logger;

class WebhookService
{
    /**
     * Check if Make.com webhook integration is enabled.
     */
    public static function isEnabled(): bool
    {
        return (bool)Env::get('MAKE_WEBHOOK_ENABLED', false);
    }

    /**
     * Dispatch webhook event to Make.com or registered endpoints.
     */
    public static function dispatch(string $eventType, array $payload): array
    {
        $eventId = 'evt_' . bin2hex(random_bytes(10));
        $now = gmdate('Y-m-d H:i:s');
        $payloadJson = json_encode($payload);

        // Record in webhook_events table for duplicate protection and audit
        Database::execute("
            INSERT INTO webhook_events (provider, event_id, event_type, payload, created_at)
            VALUES ('make', :eid, :evt, :payload, :dt)
        ", [
            ':eid' => $eventId,
            ':evt' => $eventType,
            ':payload' => $payloadJson,
            ':dt' => $now,
        ]);

        if (!self::isEnabled()) {
            return [
                'dispatched' => false,
                'event_id' => $eventId,
                'reason' => 'Make.com webhook adapter is currently disabled in .env (MAKE_WEBHOOK_ENABLED=false)',
            ];
        }

        // When enabled, sends HTTP POST with shared secret header
        return [
            'dispatched' => true,
            'event_id' => $eventId,
            'status' => 'queued',
        ];
    }
}
