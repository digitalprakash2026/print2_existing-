<?php
// ─────────────────────────────────────────────────────────────
//  Fast2SMS helper (order confirmation only)
// ─────────────────────────────────────────────────────────────

declare(strict_types=1);

namespace SMS;

class Fast2Sms
{
    public static function sendOrderConfirmation(array $order): void
    {
        $enabled = (string)\Database::setting('fast2sms_enabled', env('FAST2SMS_ENABLED', '0'));
        if (!in_array(strtolower($enabled), ['1', 'true', 'yes', 'on'], true)) return;

        $apiKey = trim((string)\Database::setting('fast2sms_api_key', env('FAST2SMS_API_KEY', '')));
        if ($apiKey === '') return;

        $phone = self::normalizeIndianMobile((string)($order['customer_phone'] ?? ''));
        if ($phone === '') return;

        $message = self::buildOrderConfirmationMessage($order);
        if ($message === '') return;

        self::sendQuickSms($apiKey, $phone, $message);
    }

    private static function buildOrderConfirmationMessage(array $order): string
    {
        $tpl = trim((string)\Database::setting(
            'fast2sms_order_confirmation_template',
            'Hi {name}, your order {order_id} is confirmed. Amount: Rs {amount}. Thank you for choosing RCS Graphic.'
        ));
        if ($tpl === '') return '';

        $name = trim((string)($order['customer_name'] ?? 'Customer'));
        $orderId = trim((string)($order['order_id'] ?? ''));
        $amount = number_format((float)($order['total_amount'] ?? 0), 0, '.', '');

        $msg = strtr($tpl, [
            '{name}' => $name !== '' ? $name : 'Customer',
            '{order_id}' => $orderId,
            '{amount}' => $amount,
        ]);

        return trim(preg_replace('/\s+/', ' ', $msg) ?? '');
    }

    private static function sendQuickSms(string $apiKey, string $phone, string $message): void
    {
        $ch = curl_init('https://www.fast2sms.com/dev/bulkV2');
        if (!$ch) return;

        $payload = json_encode([
            'route' => 'q',
            'message' => $message,
            'numbers' => $phone,
        ], JSON_UNESCAPED_UNICODE);
        if ($payload === false) return;

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_HTTPHEADER => [
                'authorization: ' . $apiKey,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_POSTFIELDS => $payload,
        ]);

        $resp = curl_exec($ch);
        $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($resp === false || $err !== '') {
            error_log('Fast2SMS order SMS failed: ' . $err);
            return;
        }

        if ($http < 200 || $http >= 300) {
            error_log('Fast2SMS order SMS HTTP ' . $http . ': ' . substr((string)$resp, 0, 400));
        }
    }

    private static function normalizeIndianMobile(string $raw): string
    {
        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if (strlen($digits) >= 10) {
            return substr($digits, -10);
        }
        return '';
    }
}

