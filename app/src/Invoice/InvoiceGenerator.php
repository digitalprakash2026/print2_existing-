<?php
// ─────────────────────────────────────────────────────────────
//  RCS Graphic — GST Invoice PDF Generator (Dompdf)
// ─────────────────────────────────────────────────────────────

declare(strict_types=1);

namespace Invoice;

class InvoiceGenerator
{
    public static function generate(array $order): string
    {
        // Try Dompdf first, fallback to HTML-print
        if (class_exists('\Dompdf\Dompdf')) {
            return self::withDompdf($order);
        }
        // Fallback: return HTML for browser print
        return self::htmlFallback($order);
    }

    public static function download(array $order): void
    {
        if (class_exists('\Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf(['enable_remote' => false, 'default_font' => 'helvetica']);
            $dompdf->loadHtml(self::buildHtml($order));
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $filename = 'Invoice-' . $order['order_id'] . '.pdf';
            $dompdf->stream($filename, ['Attachment' => true]);
        } else {
            // HTML print fallback
            header('Content-Type: text/html; charset=UTF-8');
            echo self::htmlFallback($order);
        }
    }

    private static function withDompdf(array $order): string
    {
        $dompdf = new \Dompdf\Dompdf(['enable_remote' => false]);
        $dompdf->loadHtml(self::buildHtml($order));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return $dompdf->output();
    }

    public static function buildHtml(array $order): string
    {
        $s = self::settings();
        $items = $order['items'] ?? [];
        $discount = (float)($order['discount_amount'] ?? 0);
        $now = date('d M Y');
        $orderDate = date('d M Y', strtotime($order['created_at']));

        $itemRows = '';
        foreach ($items as $i => $item) {
            $attrText = '';
            if (is_array($item['attribute_selections'])) {
                foreach ($item['attribute_selections'] as $gid => $oid) {
                    try {
                        $opt = \Database::row(
                            "SELECT ao.label, ag.name FROM attribute_options ao
                             JOIN attribute_groups ag ON ao.group_id = ag.id WHERE ao.id = ?", [$oid]
                        );
                        if ($opt) $attrText .= $opt['name'] . ': ' . $opt['label'] . ', ';
                    } catch (\Throwable) {}
                }
                $attrText = rtrim($attrText, ', ');
            }

            $desc = $item['quality_name'];
            if ($attrText) $desc .= ' | ' . $attrText;
            if ($item['design_choice'] === 'rcs') $desc .= ' | Design by RCS Graphic';

            $itemRows .= "
            <tr>
                <td class='tc'>" . ($i + 1) . "</td>
                <td>{$item['product_name']}<br><small style='color:#666'>{$desc}</small></td>
                <td class='tc'>{$item['quantity']} pcs</td>
                <td class='tr'>₹" . number_format($item['total_price'], 2) . "</td>
                <td class='tr'>₹" . number_format($item['total_price'], 2) . "</td>
            </tr>";
        }

        $discRow = $discount > 0
            ? "<tr><td colspan='4' class='tr'>Discount ({$order['coupon_code']})</td><td class='tr' style='color:green'>-₹" . number_format($discount, 2) . "</td></tr>"
            : '';

        return "<!DOCTYPE html><html><head><meta charset='UTF-8'>
        <style>
            *{box-sizing:border-box;margin:0;padding:0}
            body{font-family:Helvetica,Arial,sans-serif;font-size:13px;color:#111;padding:30px}
            .header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px;padding-bottom:16px;border-bottom:2px solid #1A56E8}
            .biz-name{font-size:22px;font-weight:700;color:#1A56E8}
            .biz-sub{font-size:11px;color:#666;margin-top:4px;line-height:1.7}
            .inv-title{text-align:right}
            .inv-title h2{font-size:18px;color:#1A56E8;font-weight:700}
            .inv-meta{font-size:11px;color:#666;margin-top:5px;line-height:1.7}
            .section{margin:16px 0}
            .section-t{font-size:10px;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:.05em;margin-bottom:5px}
            .section p{font-size:13px;line-height:1.7}
            table{width:100%;border-collapse:collapse;margin:16px 0;font-size:12px}
            th{background:#1A56E8;color:#fff;padding:9px 10px;text-align:left;font-size:11px}
            td{padding:8px 10px;border-bottom:1px solid #e5e7eb}
            .tc{text-align:center}
            .tr{text-align:right}
            .totals{width:240px;margin-left:auto}
            .totals td{padding:5px 8px;border:none}
            .tot-total{font-weight:700;font-size:15px;border-top:2px solid #1A56E8;color:#1A56E8}
            .footer{text-align:center;font-size:11px;color:#9CA3AF;margin-top:28px;padding-top:14px;border-top:1px solid #e5e7eb}
            .badge{display:inline-block;background:#ECFDF5;color:#059669;padding:4px 10px;border-radius:6px;font-weight:700;font-size:12px}
            @media print{body{padding:0}}
        </style></head><body>

        <div class='header'>
            <div>
                <div class='biz-name'>{$s['name']}</div>
                <div class='biz-sub'>
                    {$s['address']}<br>
                    📞 {$s['phone']} · ✉ {$s['email']}<br>
                    GSTIN: {$s['gst_no']}
                </div>
            </div>
            <div class='inv-title'>
                <h2>TAX INVOICE</h2>
                <div class='inv-meta'>
                    Invoice No: <strong>{$order['order_id']}</strong><br>
                    Date: {$orderDate}<br>
                    " . ($order['payment_id'] ? "Payment ID: {$order['payment_id']}" : '') . "
                </div>
                <div style='margin-top:8px'><span class='badge'>" . ucfirst($order['payment_status'] ?? 'pending') . "</span></div>
            </div>
        </div>

        <div style='display:flex;gap:32px;margin-bottom:16px'>
            <div class='section' style='flex:1'>
                <div class='section-t'>Bill To</div>
                <p><strong>{$order['customer_name']}</strong><br>
                {$order['customer_phone']}" . ($order['customer_email'] ? "<br>{$order['customer_email']}" : '') . "</p>
            </div>
            <div class='section' style='flex:1'>
                <div class='section-t'>Order Info</div>
                <p>Order ID: <strong>{$order['order_id']}</strong><br>
                Order Date: {$orderDate}<br>
                Status: <strong>" . ucfirst($order['status']) . "</strong></p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th class='tc' style='width:36px'>#</th>
                    <th>Product / Description</th>
                    <th class='tc'>Qty</th>
                    <th class='tr'>Rate</th>
                    <th class='tr'>Amount</th>
                </tr>
            </thead>
            <tbody>
                {$itemRows}
            </tbody>
        </table>

        <table class='totals'>
            <tr><td>Subtotal</td><td class='tr'>₹" . number_format($order['subtotal'], 2) . "</td></tr>
            {$discRow}
            <tr><td>CGST ({$order['gst_percent']}%/2)</td><td class='tr'>₹" . number_format($order['gst_amount'] / 2, 2) . "</td></tr>
            <tr><td>SGST ({$order['gst_percent']}%/2)</td><td class='tr'>₹" . number_format($order['gst_amount'] / 2, 2) . "</td></tr>
            <tr class='tot-total'><td><strong>Total</strong></td><td class='tr'><strong>₹" . number_format($order['total_amount'], 2) . "</strong></td></tr>
        </table>

        <div class='footer'>
            Thank you for your business! · {$s['name']} · {$s['address']}<br>
            This is a computer-generated invoice. Generated on {$now}.
        </div>

        </body></html>";
    }

    private static function htmlFallback(array $order): string
    {
        return self::buildHtml($order) . "<script>window.print();</script>";
    }

    private static function settings(): array
    {
        try {
            return [
                'name'    => \Database::setting('biz_name', env('APP_NAME', 'RCS Graphic')),
                'address' => \Database::setting('biz_address', env('BIZ_ADDRESS', '')),
                'phone'   => \Database::setting('biz_phone', env('BIZ_PHONE', '')),
                'email'   => \Database::setting('biz_email', env('BIZ_EMAIL', '')),
                'gst_no'  => \Database::setting('biz_gst_no', env('BIZ_GST_NO', 'N/A')),
            ];
        } catch (\Throwable) {
            return ['name' => 'RCS Graphic', 'address' => '', 'phone' => '', 'email' => '', 'gst_no' => ''];
        }
    }
}
