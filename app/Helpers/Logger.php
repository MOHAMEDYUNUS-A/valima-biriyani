<?php

namespace App\Helpers;

class Logger
{
    private static string $logDir = __DIR__ . '/../../storage/logs';

    public static function log(string $level, string $message, array $context = []): void
    {
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }

        $date = date('Y-m-d');
        $time = date('Y-m-d H:i:s');
        $logFile = self::$logDir . "/app-{$date}.log";

        // Redact any sensitive keys from context (passwords, card tokens, secrets)
        $cleanContext = self::redactSensitiveData($context);
        $contextStr = !empty($cleanContext) ? ' ' . json_encode($cleanContext, JSON_UNESCAPED_SLASHES) : '';

        $entry = sprintf("[%s] %s: %s%s%s", $time, strtoupper($level), $message, $contextStr, PHP_EOL);

        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('info', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('warning', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('error', $message, $context);
    }

    public static function audit(string $action, string $performedBy, array $details = []): void
    {
        $date = date('Y-m-d');
        $time = date('Y-m-d H:i:s');
        $auditFile = self::$logDir . "/audit-{$date}.log";

        $cleanDetails = self::redactSensitiveData($details);
        $entry = sprintf(
            "[%s] AUDIT: Action=[%s] User=[%s] IP=[%s] Details=%s%s",
            $time,
            $action,
            $performedBy,
            $_SERVER['REMOTE_ADDR'] ?? 'CLI',
            json_encode($cleanDetails, JSON_UNESCAPED_SLASHES),
            PHP_EOL
        );

        file_put_contents($auditFile, $entry, FILE_APPEND | LOCK_EX);

        // Also record in database audit_logs table if table exists
        try {
            $pdo = Database::pdo();
            $stmt = $pdo->prepare("
                INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, created_at)
                VALUES (:user_id, :action, :entity_type, :entity_id, :old_values, :new_values, :ip_address, :created_at)
            ");
            $stmt->execute([
                ':user_id' => $cleanDetails['user_id'] ?? null,
                ':action' => $action,
                ':entity_type' => $cleanDetails['entity_type'] ?? null,
                ':entity_id' => $cleanDetails['entity_id'] ?? null,
                ':old_values' => isset($cleanDetails['old_values']) ? json_encode($cleanDetails['old_values']) : null,
                ':new_values' => isset($cleanDetails['new_values']) ? json_encode($cleanDetails['new_values']) : null,
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                ':created_at' => gmdate('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable) {
            // Silently proceed if audit_logs table not yet migrated
        }
    }

    private static function redactSensitiveData(array $data): array
    {
        $sensitiveKeys = ['password', 'secret', 'token', 'key', 'razorpay_signature', 'cvv', 'card_number'];
        $clean = [];

        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $clean[$k] = self::redactSensitiveData($v);
            } elseif (in_array(strtolower((string)$k), $sensitiveKeys, true)) {
                $clean[$k] = '********';
            } else {
                $clean[$k] = $v;
            }
        }

        return $clean;
    }
}
