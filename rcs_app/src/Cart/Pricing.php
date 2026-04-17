<?php
// ─────────────────────────────────────────────────────────────
//  RCS Graphic — Pricing Engine
//  Per requirements: price = base (qty×quality) + design_fee (if RCS design)
//  Attribute add-ons are stored for production reference but do NOT
//  affect the customer-facing price.
// ─────────────────────────────────────────────────────────────

declare(strict_types=1);

namespace Cart;

class Pricing
{
    /**
     * Calculate price for a product configuration.
     *
     * Pricing formula:
     *   Total = base_price (qty × quality slab)
     *         + design_fee (from settings, only when design_choice = 'rcs')
     *
     * Attribute selections are recorded but do NOT add to price.
     */
    public static function calculate(
        int    $productId,
        int    $qualityId,
        int    $quantity,
        array  $attributeSelections = [],
        string $designChoice = 'upload'
    ): array {
        // ── 1. Fetch the quantity slab ────────────────────────
        $slab = \Database::row(
            "SELECT qs.*, q.name AS quality_name, q.description AS quality_desc
             FROM quantity_slabs qs
             JOIN qualities q ON qs.quality_id = q.id
             WHERE qs.quality_id = ? AND qs.product_id = ? AND qs.quantity = ?",
            [$qualityId, $productId, $quantity]
        );

        if (!$slab) {
            return [
                'ok'  => false,
                'msg' => 'No pricing configured for this quantity and quality combination.',
            ];
        }

        $basePrice = (float)$slab['price'];

        // ── 2. Design fee (from Admin → Settings → design_fee) ─
        //    Only applied when the customer chooses RCS to provide design.
        //    Set the fee in Admin Panel → Settings → Design Fee field.
        $designFee = 0.0;
        if ($designChoice === 'rcs') {
            try {
                $feeSetting = \Database::setting('design_fee', '0');
                $designFee  = (float)$feeSetting;
            } catch (\Throwable) {
                $designFee = 0.0;
            }
        }

        // ── 3. Attribute snapshot (saved for production, not priced) ─
        $attrSnapshot = [];
        foreach ($attributeSelections as $groupId => $optionId) {
            try {
                $opt = \Database::row(
                    "SELECT ao.label, ag.name AS group_name
                     FROM attribute_options ao
                     JOIN attribute_groups ag ON ao.group_id = ag.id
                     WHERE ao.id = ? AND ag.id = ?",
                    [$optionId, $groupId]
                );
                if ($opt) {
                    $attrSnapshot[] = [
                        'group'  => $opt['group_name'],
                        'option' => $opt['label'],
                    ];
                }
            } catch (\Throwable) {}
        }

        $total = $basePrice + $designFee;

        return [
            'ok'    => true,
            'total' => $total,
            'breakdown' => [
                'base_price'   => $basePrice,
                'quality_name' => $slab['quality_name'],
                'quantity'     => $quantity,
                'design_fee'   => $designFee,
                'design_choice'=> $designChoice,
                'attr_addons'  => 0,       // kept for schema compat, always 0
                'attr_items'   => $attrSnapshot,
                'total'        => $total,
            ],
        ];
    }

    // ── Coupon Validation ─────────────────────────────────────

    public static function validateCoupon(string $code, float $subtotal): array
    {
        $coupon = \Database::row(
            "SELECT * FROM coupons WHERE code = ? AND is_active = 1",
            [strtoupper(trim($code))]
        );

        if (!$coupon) return ['ok' => false, 'msg' => 'Invalid coupon code.'];

        $today = date('Y-m-d');
        if ($coupon['valid_from']  && $coupon['valid_from']  > $today)
            return ['ok' => false, 'msg' => 'Coupon not yet active.'];
        if ($coupon['valid_until'] && $coupon['valid_until'] < $today)
            return ['ok' => false, 'msg' => 'Coupon has expired.'];
        if ($coupon['min_order_amount'] > 0 && $subtotal < $coupon['min_order_amount'])
            return ['ok' => false, 'msg' => 'Minimum order ₹' . number_format($coupon['min_order_amount']) . ' required.'];
        if ($coupon['max_uses'] > 0 && $coupon['used_count'] >= $coupon['max_uses'])
            return ['ok' => false, 'msg' => 'Coupon usage limit reached.'];

        $discount = $coupon['discount_type'] === 'percent'
            ? round($subtotal * $coupon['discount_value'] / 100)
            : (float)$coupon['discount_value'];

        return [
            'ok'       => true,
            'discount' => min($discount, $subtotal),
            'coupon'   => $coupon,
        ];
    }

    // ── Product Pricing Data (for product detail page) ────────

    public static function productPricingData(int $productId): array
    {
        $qualities = \Database::rows(
            "SELECT q.id, q.name, q.description, q.sort_order
             FROM qualities q
             JOIN quantity_slabs qs ON qs.quality_id = q.id
             WHERE qs.product_id = ? AND q.is_active = 1
             GROUP BY q.id
             ORDER BY q.sort_order ASC",
            [$productId]
        );

        foreach ($qualities as &$q) {
            $slabs = \Database::rows(
                "SELECT quantity, price FROM quantity_slabs
                 WHERE product_id = ? AND quality_id = ?
                 ORDER BY quantity ASC",
                [$productId, $q['id']]
            );
            $q['slabs']     = $slabs;
            $q['min_price'] = $slabs ? min(array_column($slabs, 'price')) : 0;
        }
        unset($q);

        $attrGroups = \Database::rows(
            "SELECT ag.id, ag.name, ag.sort_order
             FROM attribute_groups ag
             JOIN product_attribute_groups pag ON pag.group_id = ag.id
             WHERE pag.product_id = ? AND ag.is_active = 1
             ORDER BY ag.sort_order ASC",
            [$productId]
        );

        foreach ($attrGroups as &$ag) {
            $ag['options'] = \Database::rows(
                "SELECT id, label, price_addon, sort_order
                 FROM attribute_options
                 WHERE group_id = ? AND is_active = 1
                 ORDER BY sort_order ASC",
                [$ag['id']]
            );
        }
        unset($ag);

        // Also return current design fee for display
        $designFee = 0.0;
        try { $designFee = (float)\Database::setting('design_fee', '0'); } catch (\Throwable) {}

        return [
            'qualities'   => $qualities,
            'attr_groups' => $attrGroups,
            'design_fee'  => $designFee,
        ];
    }
}
