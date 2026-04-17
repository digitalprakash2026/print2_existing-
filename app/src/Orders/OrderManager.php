<?php
// ─────────────────────────────────────────────────────────────
//  RCS Graphic — Order Manager
// ─────────────────────────────────────────────────────────────

declare(strict_types=1);

namespace Orders;

class OrderManager
{
    public static function place(array $params): array
    {
        $user = \Auth\Auth::user();
        if (!$user) return ['ok' => false, 'msg' => 'Not authenticated'];

        $cartItems = \Cart\Cart::get();
        if (empty($cartItems)) return ['ok' => false, 'msg' => 'Cart is empty'];

        $couponCode = $params['coupon_code'] ?? null;
        $totals = \Cart\Cart::totals($cartItems, $couponCode);

        // Generate readable order ID
        $orderId = self::generateOrderId();
        $now = date('Y-m-d H:i:s');

        $db = \Database::get();
        $db->beginTransaction();

        try {
            // Create order
            $dbOrderId = \Database::insert(
                "INSERT INTO orders (order_id, user_id, customer_name, customer_email, customer_phone,
                    subtotal, discount_amount, gst_amount, gst_percent, total_amount,
                    coupon_code, payment_method, payment_status, status, notes, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'received', ?, ?)",
                [
                    $orderId,
                    $user['id'],
                    $user['name'],
                    $user['email'],
                    $user['phone'],
                    $totals['subtotal'],
                    $totals['discount'],
                    $totals['gst_amt'],
                    $totals['gst_pct'],
                    $totals['total'],
                    $couponCode,
                    $params['payment_method'] ?? 'razorpay',
                    $params['payment_status'] ?? 'pending',
                    $params['notes'] ?? '',
                    $now,
                ]
            );

            // Insert order items (snapshot of cart)
            foreach ($cartItems as $item) {
                $orderItemId = \Database::insert(
                    "INSERT INTO order_items (order_id, product_id, quality_id, quantity,
                        product_name, quality_name, attribute_selections, design_choice,
                        design_brief, notes, price_breakdown, total_price, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $dbOrderId,
                        $item['product_id'],
                        $item['quality_id'],
                        $item['quantity'],
                        $item['product_name'],
                        $item['quality_name'],
                        $item['attribute_selections'],
                        $item['design_choice'],
                        $item['design_brief'],
                        $item['notes'],
                        $item['price_breakdown'],
                        $item['total_price'],
                        $now,
                    ]
                );

                // Link artwork files
                if (!empty($item['id'])) {
                    \Database::query(
                        "UPDATE artwork_files SET order_item_id = ?, cart_item_id = NULL
                         WHERE cart_item_id = ?",
                        [$orderItemId, $item['id']]
                    );
                }
            }

            // Status history
            \Database::insert(
                "INSERT INTO order_status_history (order_id, status, note, created_by, created_at)
                 VALUES (?, 'received', 'Order placed', ?, ?)",
                [$dbOrderId, 'system', $now]
            );

            // Mark coupon as used
            if ($couponCode && $totals['coupon']) {
                \Database::query(
                    "UPDATE coupons SET used_count = used_count + 1 WHERE code = ?",
                    [$couponCode]
                );
                \Database::insert(
                    "INSERT INTO coupon_uses (coupon_id, order_id, user_id, discount_applied, used_at)
                     VALUES (?, ?, ?, ?, ?)",
                    [$totals['coupon']['id'], $dbOrderId, $user['id'], $totals['discount'], $now]
                );
            }

            $db->commit();

            // Clear cart
            \Cart\Cart::clear();

            $order = self::getOrder($dbOrderId);

            // Async tasks (non-blocking)
            self::afterOrderPlaced($order);

            return ['ok' => true, 'order' => $order, 'order_id' => $orderId];

        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('Order placement failed: ' . $e->getMessage());
            return ['ok' => false, 'msg' => 'Order placement failed. Please try again.'];
        }
    }

    public static function updateStatus(int $orderId, string $status, string $note = '', string $actor = 'admin'): bool
    {
        $validStatuses = ['received', 'processing', 'printing', 'ready', 'delivered', 'cancelled', 'whatsapp_pending'];
        if (!in_array($status, $validStatuses)) return false;

        \Database::query(
            "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?",
            [$status, $orderId]
        );

        \Database::insert(
            "INSERT INTO order_status_history (order_id, status, note, created_by, created_at)
             VALUES (?, ?, ?, ?, NOW())",
            [$orderId, $status, $note, $actor]
        );

        // Audit log
        if (\Auth\Auth::isAdmin()) {
            AdminAudit::log('order_status_update', "Order #{$orderId} → {$status}");
        }

        // Google Sheets sync
        $order = self::getOrder($orderId);
        if ($order) {
            \Sheets\SheetsSync::syncStatusUpdate($order);
            // Notify customer via email
            \Email\Mailer::sendStatusUpdate($order);
        }

        return true;
    }

    public static function getOrder(int $id): ?array
    {
        $order = \Database::row("SELECT * FROM orders WHERE id = ?", [$id]);
        if (!$order) return null;

        $order['items'] = \Database::rows(
            "SELECT oi.*, af.filename, af.original_name, af.file_path
             FROM order_items oi
             LEFT JOIN artwork_files af ON af.order_item_id = oi.id
             WHERE oi.order_id = ?",
            [$id]
        );
        foreach ($order['items'] as &$item) {
            $item['attribute_selections'] = json_decode($item['attribute_selections'] ?? '[]', true);
            $item['price_breakdown'] = json_decode($item['price_breakdown'] ?? '{}', true);
        }

        $order['status_history'] = \Database::rows(
            "SELECT * FROM order_status_history WHERE order_id = ? ORDER BY created_at ASC",
            [$id]
        );

        return $order;
    }

    public static function getOrderByOrderId(string $orderId): ?array
    {
        $row = \Database::row("SELECT id FROM orders WHERE order_id = ?", [$orderId]);
        return $row ? self::getOrder((int)$row['id']) : null;
    }

    public static function getUserOrders(int $userId): array
    {
        $orders = \Database::rows(
            "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC",
            [$userId]
        );
        foreach ($orders as &$order) {
            $order['items'] = \Database::rows(
                "SELECT * FROM order_items WHERE order_id = ?",
                [$order['id']]
            );
        }
        return $orders;
    }

    // ── After Order Hook ──────────────────────────────────────

    private static function afterOrderPlaced(array $order): void
    {
        try { \Email\Mailer::sendOrderConfirmation($order); } catch (\Throwable) {}
        try { \Sheets\SheetsSync::syncOrder($order); } catch (\Throwable) {}
    }

    // ── Helpers ───────────────────────────────────────────────

    private static function generateOrderId(): string
    {
        $count = \Database::row("SELECT COUNT(*) as c FROM orders")['c'] ?? 0;
        return 'RCS' . str_pad((string)((int)$count + 1001), 5, '0', STR_PAD_LEFT);
    }
}
