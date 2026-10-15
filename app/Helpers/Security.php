<?php

namespace App\Helpers;

class Security
{
    public static function e(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function randomToken(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }

    public static function generateCsrfToken(): string
    {
        Session::start();

        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }

    public static function csrfField(): string
    {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="_csrf_token" value="' . self::e($token) . '">';
    }

    public static function verifyCsrfToken(?string $token): bool
    {
        Session::start();

        $sessionToken = $_SESSION['_csrf_token'] ?? null;
        if (!$sessionToken || !$token) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    public static function normalizePhone(string $phone): string
    {
        // Strip everything except digits
        $digits = preg_replace('/\D/', '', $phone);

        // If 10 digits (standard Indian mobile), prepend +91
        if (strlen($digits) === 10) {
            return '+91' . $digits;
        }

        // If 11 digits starting with 0, strip leading 0 and prepend +91
        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return '+91' . substr($digits, 1);
        }

        // If 12 digits starting with 91, format with +
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return '+' . $digits;
        }

        return '+' . $digits;
    }

    public static function isValidIndianPhone(string $phone): bool
    {
        $normalized = self::normalizePhone($phone);
        // +91 followed by 6, 7, 8, or 9 and 9 more digits
        return (bool)preg_match('/^\+91[6-9]\d{9}$/', $normalized);
    }

    public static function sanitizeString(string $input): string
    {
        return trim(strip_tags($input));
    }

    public static function generateOrderNumber(): string
    {
        // Format: VAL-YYYYMMDD-XXXX (e.g. VAL-20261015-8492)
        $date = date('Ymd');
        $random = strtoupper(bin2hex(random_bytes(2)));
        return "VAL-{$date}-{$random}";
    }

    public static function generateTrackingToken(): string
    {
        return bin2hex(random_bytes(16)); // 32 hex characters
    }
}
