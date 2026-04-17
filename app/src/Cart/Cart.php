<?php
// ─────────────────────────────────────────────────────────────
//  RCS Graphic — Cart
// ─────────────────────────────────────────────────────────────

declare(strict_types=1);

namespace Cart;

class Cart
{
    // ── Core ──────────────────────────────────────────────────

    public static function add(array $data): array
    {
        $validation = self::validateItem($data);
        if (!$validation['ok']) return $validation;

        $priceInfo = Pricing::calculate(
            (int)$data['product_id'],
            (int)$data['quality_id'],
            (int)$data['quantity'],
            $data['attribute_selections'] ?? [],
            $data['design_choice'] ?? 'upload'
        );

        if (!$priceInfo['ok']) return $priceInfo;

        $item = [
            'product_id'          => (int)$data['product_id'],
            'quality_id'          => (int)$data['quality_id'],
            'quantity'            => (int)$data['quantity'],
            'attribute_selections'=> json_encode($data['attribute_selections'] ?? []),
            'design_choice'       => $data['design_choice'] ?? 'upload',
            'design_brief'        => $data['design_brief'] ?? '',
            'notes'               => $data['notes'] ?? '',
            'price_breakdown'     => json_encode($priceInfo['breakdown']),
            'total_price'         => $priceInfo['total'],
        ];

        $userId = \Auth\Auth::user()['id'] ?? null;

        if ($userId) {
            // Ensure DB cart exists
            $cart = \Database::row("SELECT id FROM carts WHERE user_id = ?", [$userId]);
            if (!$cart) {
                $cartId = \Database::insert("INSERT INTO carts (user_id, created_at) VALUES (?, NOW())", [$userId]);
            } else {
                $cartId = $cart['id'];
            }

            $cartItemId = \Database::insert(
                "INSERT INTO cart_items (cart_id, product_id, quality_id, quantity, attribute_selections,
                  design_choice, design_brief, notes, price_breakdown, total_price, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [
                    $cartId,
                    $item['product_id'], $item['quality_id'], $item['quantity'],
                    $item['attribute_selections'],
                    $item['design_choice'], $item['design_brief'], $item['notes'],
                    $item['price_breakdown'], $item['total_price'],
                ]
            );

            // Handle artwork upload reference if provided
            if (!empty($data['artwork_id'])) {
                \Database::query(
                    "UPDATE artwork_files SET cart_item_id = ? WHERE id = ? AND uploaded_by = ?",
                    [$cartItemId, $data['artwork_id'], $userId]
                );
            }

        } else {
            // Guest cart in session
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
            $item['id']         = uniqid('ci_', true);
            $item['artwork_id'] = $data['artwork_id'] ?? null;
            $_SESSION['cart'][] = $item;
            $cartItemId = $item['id'];
        }

        return ['ok' => true, 'cart_item_id' => $cartItemId, 'price' => $priceInfo];
    }

    public static function remove(string $itemId): array
    {
        $userId = \Auth\Auth::user()['id'] ?? null;

        if ($userId) {
            $item = \Database::row(
                "SELECT ci.id FROM cart_items ci
                 JOIN carts c ON ci.cart_id = c.id
                 WHERE ci.id = ? AND c.user_id = ?",
                [$itemId, $userId]
            );
            if (!$item) return ['ok' => false, 'msg' => 'Item not found'];
            \Database::query("DELETE FROM cart_items WHERE id = ?", [$itemId]);
        } else {
            $_SESSION['cart'] = array_filter(
                $_SESSION['cart'] ?? [],
                fn($i) => $i['id'] !== $itemId
            );
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }

        return ['ok' => true];
    }

    public static function get(): array
    {
        $userId = \Auth\Auth::user()['id'] ?? null;

        if ($userId) {
            return \Database::rows(
                "SELECT ci.*, p.name as product_name, p.slug,
                        q.name as quality_name,
                        pi.url as product_image
                 FROM cart_items ci
                 JOIN carts c ON ci.cart_id = c.id
                 JOIN products p ON ci.product_id = p.id
                 JOIN qualities q ON ci.quality_id = q.id
                 LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
                 LEFT JOIN artwork_files af ON af.cart_item_id = ci.id
                 WHERE c.user_id = ?
                 ORDER BY ci.created_at ASC",
                [$userId]
            );
        }

        // Guest cart — enrich with product data
        $items = $_SESSION['cart'] ?? [];
        foreach ($items as &$item) {
            $prod = \Database::row(
                "SELECT p.name as product_name, p.slug, pi.url as product_image,
                        q.name as quality_name
                 FROM products p
                 JOIN qualities q ON q.id = ?
                 LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
                 WHERE p.id = ?",
                [$item['quality_id'], $item['product_id']]
            );
            if ($prod) $item = array_merge($item, $prod);
        }
        return $items;
    }

    public static function clear(): void
    {
        $userId = \Auth\Auth::user()['id'] ?? null;
        if ($userId) {
            $cart = \Database::row("SELECT id FROM carts WHERE user_id = ?", [$userId]);
            if ($cart) {
                \Database::query("DELETE FROM cart_items WHERE cart_id = ?", [$cart['id']]);
            }
        } else {
            $_SESSION['cart'] = [];
        }
    }

    public static function totals(array $items, ?string $couponCode = null): array
    {
        $subtotal = array_sum(array_column($items, 'total_price'));
        $discount = 0;
        $coupon = null;

        if ($couponCode) {
            $coupon = Pricing::validateCoupon($couponCode, $subtotal);
            if ($coupon['ok']) {
                $discount = $coupon['discount'];
                $coupon = $coupon['coupon'];
            }
        }

        $gstPct = (float)(\Database::setting('gst_percent', env('GST_PERCENT', '18')));
        $taxable = $subtotal - $discount;
        $gstAmt  = round($taxable * $gstPct / 100);

        $shippingMode = (string)\Database::setting('shipping_mode', 'flat');
        $shippingFlat = (float)\Database::setting('shipping_flat_fee', '0');
        $freeAbove    = (float)\Database::setting('shipping_free_above', '0');
        $shipping = 0.0;
        if ($shippingMode === 'flat') {
            $shipping = $shippingFlat;
        } elseif ($shippingMode === 'threshold') {
            $shipping = ($taxable >= $freeAbove && $freeAbove > 0) ? 0.0 : $shippingFlat;
        } else {
            $shipping = 0.0;
        }

        $total   = $taxable + $gstAmt + $shipping;

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'gst_pct'  => $gstPct,
            'gst_amt'  => $gstAmt,
            'shipping' => $shipping,
            'shipping_mode' => $shippingMode,
            'total'    => $total,
            'coupon'   => $coupon,
        ];
    }

    // Merge guest session cart into DB cart on login
    public static function mergeGuestCart(int $userId): void
    {
        $guestItems = $_SESSION['cart'] ?? [];
        if (empty($guestItems)) return;

        $cart = \Database::row("SELECT id FROM carts WHERE user_id = ?", [$userId]);
        if (!$cart) {
            $cartId = \Database::insert("INSERT INTO carts (user_id, created_at) VALUES (?, NOW())", [$userId]);
        } else {
            $cartId = $cart['id'];
        }

        foreach ($guestItems as $item) {
            \Database::insert(
                "INSERT INTO cart_items (cart_id, product_id, quality_id, quantity, attribute_selections,
                  design_choice, design_brief, notes, price_breakdown, total_price, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [
                    $cartId,
                    $item['product_id'], $item['quality_id'], $item['quantity'],
                    $item['attribute_selections'] ?? '[]',
                    $item['design_choice'] ?? 'upload',
                    $item['design_brief'] ?? '',
                    $item['notes'] ?? '',
                    $item['price_breakdown'] ?? '{}',
                    $item['total_price'] ?? 0,
                ]
            );
        }

        $_SESSION['cart'] = [];
    }

    private static function validateItem(array $d): array
    {
        if (empty($d['product_id'])) return ['ok' => false, 'msg' => 'Product required'];
        if (empty($d['quality_id'])) return ['ok' => false, 'msg' => 'Quality required'];
        if (empty($d['quantity']))   return ['ok' => false, 'msg' => 'Quantity required'];
        if (empty($d['design_choice'])) return ['ok' => false, 'msg' => 'Design choice required'];
        return ['ok' => true];
    }
}
