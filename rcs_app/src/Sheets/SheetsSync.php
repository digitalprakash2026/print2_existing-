<?php
// ─────────────────────────────────────────────────────────────
//  RCS Graphic — Google Sheets Sync (Apps Script Webhook)
// ─────────────────────────────────────────────────────────────

declare(strict_types=1);

namespace Sheets;

class SheetsSync
{
    private const MAX_RETRIES = 3;

    // Sync full order on creation / payment
    public static function syncOrder(array $order): void
    {
        $webhookUrl = self::webhookUrl();
        if (!$webhookUrl) return;

        foreach ($order['items'] as $item) {
            $payload = [
                'action'         => 'addOrder',
                'orderId'        => $order['order_id'],
                'date'           => date('d/m/Y', strtotime($order['created_at'])),
                'time'           => date('H:i', strtotime($order['created_at'])),
                'customerName'   => $order['customer_name'],
                'customerPhone'  => $order['customer_phone'],
                'customerEmail'  => $order['customer_email'] ?? '',
                'product'        => $item['product_name'],
                'quantity'       => $item['quantity'],
                'quality'        => $item['quality_name'],
                'options'        => self::attrSummary($item['attribute_selections'] ?? []),
                'designOption'   => $item['design_choice'] === 'rcs' ? 'Design by RCS' : 'Customer Upload',
                'designBrief'    => $item['design_brief'] ?? '',
                'itemPrice'      => $item['total_price'],
                'coupon'         => $order['coupon_code'] ?? '',
                'discount'       => $order['discount_amount'] ?? 0,
                'gst'            => $order['gst_amount'] ?? 0,
                'total'          => $order['total_amount'],
                'paymentId'      => $order['payment_id'] ?? '',
                'paymentStatus'  => $order['payment_status'] ?? 'pending',
                'status'         => $order['status'],
            ];

            self::dispatch($webhookUrl, $payload);
        }
    }

    // Sync status update only
    public static function syncStatusUpdate(array $order): void
    {
        $webhookUrl = self::webhookUrl();
        if (!$webhookUrl) return;

        self::dispatch($webhookUrl, [
            'action'    => 'updateStatus',
            'orderId'   => $order['order_id'],
            'status'    => $order['status'],
            'updatedAt' => date('Y-m-d H:i:s'),
        ]);
    }

    // ── Internal ──────────────────────────────────────────────

    private static function dispatch(string $url, array $payload, int $attempt = 1): void
    {
        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_FOLLOWLOCATION => true,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode < 200 || $httpCode >= 300) {
                throw new \RuntimeException("HTTP {$httpCode}: {$response}");
            }

        } catch (\Throwable $e) {
            error_log("SheetsSync attempt {$attempt} failed: " . $e->getMessage());

            // Log to failed sync table for manual retry
            try {
                \Database::insert(
                    "INSERT INTO sheets_sync_log (payload, error, attempt, created_at)
                     VALUES (?, ?, ?, NOW())
                     ON DUPLICATE KEY UPDATE error = VALUES(error), attempt = VALUES(attempt)",
                    [json_encode($payload), $e->getMessage(), $attempt]
                );
            } catch (\Throwable) {}

            // Retry with backoff (only if we have retries left)
            if ($attempt < self::MAX_RETRIES) {
                sleep($attempt * 2);
                self::dispatch($url, $payload, $attempt + 1);
            }
        }
    }

    private static function webhookUrl(): string
    {
        try {
            return \Database::setting('sheets_webhook_url', env('SHEETS_WEBHOOK_URL', ''));
        } catch (\Throwable) {
            return env('SHEETS_WEBHOOK_URL', '');
        }
    }

    private static function attrSummary(mixed $selections): string
    {
        if (!is_array($selections)) return '';
        $parts = [];
        foreach ($selections as $groupId => $optionId) {
            try {
                $opt = \Database::row(
                    "SELECT ao.label, ag.name FROM attribute_options ao
                     JOIN attribute_groups ag ON ao.group_id = ag.id
                     WHERE ao.id = ?",
                    [$optionId]
                );
                if ($opt) $parts[] = $opt['name'] . ': ' . $opt['label'];
            } catch (\Throwable) {}
        }
        return implode(', ', $parts);
    }
}
