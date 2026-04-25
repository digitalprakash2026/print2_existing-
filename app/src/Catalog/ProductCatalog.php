<?php
// ─────────────────────────────────────────────────────────────
//  RCS Graphic — Product Catalog
// ─────────────────────────────────────────────────────────────

declare(strict_types=1);

namespace Catalog;

class ProductCatalog
{
    private static function minPriceExpr(): string
    {
        return "(SELECT MIN(t.price) FROM product_quantity_tiers t WHERE t.product_id = p.id)";
    }

    private static function legacyMinPriceExpr(): string
    {
        return "(SELECT MIN(qs.price) FROM quantity_slabs qs WHERE qs.product_id = p.id)";
    }

    private static function primaryImageExpr(): string
    {
        return "COALESCE(p.image_path, (SELECT COALESCE(pi.image_path, pi.url) FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1))";
    }

    private static function fetchProductRows(string $whereSql, array $params = []): array
    {
        try {
            return \Database::rows(
                "SELECT p.*, c.name as category_name,
                        " . self::primaryImageExpr() . " as primary_image,
                        " . self::minPriceExpr() . " as min_price
                 FROM products p
                 LEFT JOIN categories c ON c.id = p.category_id
                 {$whereSql}",
                $params
            );
        } catch (\Throwable) {
            return \Database::rows(
                "SELECT p.*, c.name as category_name,
                        (SELECT pi.url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image,
                        " . self::legacyMinPriceExpr() . " as min_price
                 FROM products p
                 LEFT JOIN categories c ON c.id = p.category_id
                 {$whereSql}",
                $params
            );
        }
    }

    public static function all(bool $activeOnly = true): array
    {
        $where = $activeOnly ? 'WHERE p.is_active = 1' : '';
        return self::fetchProductRows($where . ' ORDER BY c.sort_order ASC, p.sort_order ASC');
    }

    public static function bySlug(string $slug): ?array
    {
        $product = \Database::row(
            "SELECT p.*, c.name as category_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.slug = ? AND p.is_active = 1",
            [$slug]
        );
        if (!$product) return null;
        return self::hydrate($product);
    }

    public static function byId(int $id): ?array
    {
        $product = \Database::row(
            "SELECT p.*, c.name as category_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.id = ?",
            [$id]
        );
        if (!$product) return null;
        return self::hydrate($product);
    }

    public static function categories(): array
    {
        return \Database::rows(
            "SELECT c.*, COUNT(p.id) as product_count
             FROM categories c
             LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
             GROUP BY c.id
             ORDER BY c.sort_order ASC"
        );
    }

    public static function related(int $productId, int $categoryId, int $limit = 4): array
    {
        return self::fetchProductRows(
            'WHERE p.is_active = 1 AND p.id != ? AND p.category_id = ? ORDER BY RAND() LIMIT ?',
            [$productId, $categoryId, $limit]
        );
    }

    public static function search(string $q): array
    {
        $like = '%' . $q . '%';
        return self::fetchProductRows(
            'WHERE p.is_active = 1 AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?) ORDER BY p.sort_order ASC',
            [$like, $like, $like]
        );
    }

    public static function byCategory(string $slug): ?array
    {
        $category = \Database::row(
            "SELECT * FROM categories WHERE slug = ? AND is_active = 1",
            [$slug]
        );
        if (!$category) return null;

        $products = self::fetchProductRows('WHERE p.is_active = 1 AND p.category_id = ? ORDER BY p.sort_order ASC', [$category['id']]);

        return ['category' => $category, 'products' => $products];
    }

    public static function allProductsPage(): array
    {
        $products   = self::all(true);
        $categories = self::categories();
        return compact('products', 'categories');
    }

    private static function hydrate(array $product): array
    {
        try {
            $product['images'] = \Database::rows(
                "SELECT id, product_id, COALESCE(image_path, url) as url, COALESCE(image_path, url) as image_path, alt_text, is_primary, sort_order FROM product_images WHERE product_id = ? ORDER BY sort_order ASC",
                [$product['id']]
            );
        } catch (\Throwable) {
            $product['images'] = \Database::rows(
                "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC",
                [$product['id']]
            );
            foreach ($product['images'] as &$img) {
                if (!isset($img['url']) && isset($img['image_path'])) $img['url'] = $img['image_path'];
                if (!isset($img['image_path']) && isset($img['url'])) $img['image_path'] = $img['url'];
            }
            unset($img);
        }

        if (empty($product['images']) && !empty($product['image_path'])) {
            $product['images'][] = [
                'id' => 0,
                'product_id' => (int)$product['id'],
                'url' => $product['image_path'],
                'image_path' => $product['image_path'],
                'alt_text' => $product['name'] ?? '',
                'is_primary' => 1,
                'sort_order' => 0,
            ];
        }

        $product['specs'] = \Database::rows(
            "SELECT * FROM product_specs WHERE product_id = ? ORDER BY sort_order ASC",
            [$product['id']]
        );

        $pricing = \Cart\Pricing::productPricingData((int)$product['id']);
        $product['qualities']      = $pricing['qualities'];
        $product['attr_groups']    = [];
        $product['quantity_tiers'] = $pricing['tiers'];

        return $product;
    }

    public static function upsert(array $data, ?int $editId = null): array
    {
        $errors = [];
        if (empty($data['name'])) $errors[] = 'Name required';
        if (empty($data['category_id'])) $errors[] = 'Category required';
        if ($errors) return ['ok' => false, 'msg' => implode(', ', $errors)];

        $slug = self::makeSlug($data['name'], $editId);

        try {
            if ($editId) {
                try {
                    \Database::query(
                        "UPDATE products SET name=?, slug=?, category_id=?, description=?,
                            meta_title=?, design_fee=?, image_path=?, is_active=?, sort_order=?, updated_at=NOW() WHERE id=?",
                        [
                            $data['name'], $slug, $data['category_id'],
                            $data['description'] ?? '',
                            $data['meta_title'] ?? $data['name'],
                            (float)($data['design_fee'] ?? 0),
                            $data['image_path'] ?? null,
                            $data['is_active'] ?? 1,
                            $data['sort_order'] ?? 0,
                            $editId,
                        ]
                    );
                } catch (\Throwable) {
                    \Database::query(
                        "UPDATE products SET name=?, slug=?, category_id=?, description=?,
                            meta_title=?, design_fee=?, is_active=?, sort_order=?, updated_at=NOW() WHERE id=?",
                        [
                            $data['name'], $slug, $data['category_id'],
                            $data['description'] ?? '',
                            $data['meta_title'] ?? $data['name'],
                            (float)($data['design_fee'] ?? 0),
                            $data['is_active'] ?? 1,
                            $data['sort_order'] ?? 0,
                            $editId,
                        ]
                    );
                }
                self::syncSpecs($editId, $data['specs'] ?? []);
                self::syncQuantityTiers($editId, $data['quantity_tiers'] ?? []);
                \Orders\AdminAudit::log('product_updated', "Product #{$editId}: {$data['name']}");
                return ['ok' => true, 'id' => $editId];
            }

            try {
                $id = \Database::insert(
                    "INSERT INTO products (name, slug, category_id, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                    [
                        $data['name'], $slug, $data['category_id'],
                        $data['description'] ?? '',
                        $data['meta_title'] ?? $data['name'],
                        (float)($data['design_fee'] ?? 0),
                        $data['image_path'] ?? null,
                        $data['is_active'] ?? 1,
                        $data['sort_order'] ?? 0,
                    ]
                );
            } catch (\Throwable) {
                $id = \Database::insert(
                    "INSERT INTO products (name, slug, category_id, description, meta_title, design_fee, is_active, sort_order, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                    [
                        $data['name'], $slug, $data['category_id'],
                        $data['description'] ?? '',
                        $data['meta_title'] ?? $data['name'],
                        (float)($data['design_fee'] ?? 0),
                        $data['is_active'] ?? 1,
                        $data['sort_order'] ?? 0,
                    ]
                );
            }

            self::syncSpecs((int)$id, $data['specs'] ?? []);
            self::syncQuantityTiers((int)$id, $data['quantity_tiers'] ?? []);
            \Orders\AdminAudit::log('product_created', "Product #{$id}: {$data['name']}");
            return ['ok' => true, 'id' => (int)$id];
        } catch (\Throwable $e) {
            error_log('Product upsert failed: ' . $e->getMessage());
            return ['ok' => false, 'msg' => 'Save failed. Check required DB columns/tables and server logs.'];
        }
    }

    private static function syncSpecs(int $productId, array $specs): void
    {
        \Database::query("DELETE FROM product_specs WHERE product_id = ?", [$productId]);
        foreach ($specs as $i => $spec) {
            if (!empty($spec['label'])) {
                \Database::insert(
                    "INSERT INTO product_specs (product_id, label, value, sort_order) VALUES (?, ?, ?, ?)",
                    [$productId, $spec['label'], $spec['value'] ?? '', $i]
                );
            }
        }
    }

    private static function syncQuantityTiers(int $productId, array $tiers): void
    {
        try {
            \Database::query("DELETE FROM product_quantity_tiers WHERE product_id = ?", [$productId]);
        } catch (\Throwable) {
            return;
        }

        $seen = [];
        usort($tiers, fn($a,$b) => ((int)($a['quantity'] ?? 0)) <=> ((int)($b['quantity'] ?? 0)));

        foreach ($tiers as $t) {
            $qty = (int)($t['quantity'] ?? 0);
            $price = (float)($t['price'] ?? 0);
            if ($qty <= 0 || $price <= 0 || isset($seen[$qty])) continue;
            $seen[$qty] = true;
            \Database::insert(
                "INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
                 VALUES (?, ?, ?, NOW())",
                [$productId, $qty, $price]
            );
        }
    }

    private static function makeSlug(string $name, ?int $excludeId = null): string
    {
        $base = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
        $base = trim($base, '-');
        $slug = $base;
        $i = 1;
        while (true) {
            $exists = \Database::row(
                "SELECT id FROM products WHERE slug = ?" . ($excludeId ? " AND id != {$excludeId}" : ''),
                [$slug]
            );
            if (!$exists) break;
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
