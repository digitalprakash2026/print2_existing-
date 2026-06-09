<?php
// ─────────────────────────────────────────────────────────────
//  RCS Graphic — Product Catalog
// ─────────────────────────────────────────────────────────────

declare(strict_types=1);

namespace Catalog;

class ProductCatalog
{
    private static ?bool $hasProductCodeColumn = null;
    private static ?bool $hasCategoryCodePrefixColumn = null;

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
                "SELECT p.*, c.name as category_name, c.slug as category_slug,
                        " . self::primaryImageExpr() . " as primary_image,
                        " . self::minPriceExpr() . " as min_price
                 FROM products p
                 LEFT JOIN categories c ON c.id = p.category_id
                 {$whereSql}",
                $params
            );
        } catch (\Throwable) {
            return \Database::rows(
                "SELECT p.*, c.name as category_name, c.slug as category_slug,
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
        $products = self::fetchProductRows('WHERE p.slug = ? AND p.is_active = 1 LIMIT 1', [$slug]);
        $product = $products[0] ?? null;
        if (!$product) return null;
        $product = self::ensureProductCode($product);
        return self::hydrate($product);
    }

    public static function byId(int $id): ?array
    {
        $products = self::fetchProductRows('WHERE p.id = ? LIMIT 1', [$id]);
        $product = $products[0] ?? null;
        if (!$product) return null;
        $product = self::ensureProductCode($product);
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

    public static function relatedFromFixedCategories(int $productId, int $limit = 5): array
    {
        $preferred = [
            ['business-cards', 'business-card', 'visiting-cards'],
            ['flyers', 'flyer'],
            ['brochures', 'brochure'],
            ['posters', 'poster'],
            ['calendars', 'calendar'],
        ];
        $picked = [];
        $usedCategoryIds = [];

        foreach ($preferred as $slugGroup) {
            if (count($picked) >= $limit) break;
            $row = self::fetchOneFromCategorySlugs($productId, $slugGroup);
            if (!$row) continue;
            $picked[] = $row;
            $usedCategoryIds[] = (int)($row['category_id'] ?? 0);
        }

        if (count($picked) < $limit) {
            $remaining = $limit - count($picked);
            $fallback = self::fetchFallbackRelated($productId, $usedCategoryIds, $remaining);
            foreach ($fallback as $row) {
                $picked[] = $row;
            }
        }

        return array_slice($picked, 0, $limit);
    }

    private static function fetchOneFromCategorySlugs(int $productId, array $slugs): ?array
    {
        $slugs = array_values(array_filter(array_map('strval', $slugs)));
        if (empty($slugs)) {
            return null;
        }

        $placeholders = implode(',', array_fill(0, count($slugs), '?'));
        $params = array_merge([$productId], $slugs);
        $rows = self::fetchProductRows(
            "WHERE p.is_active = 1 AND p.id != ? AND c.slug IN ($placeholders) ORDER BY p.sort_order ASC, p.id DESC LIMIT 1",
            $params
        );
        return $rows[0] ?? null;
    }

    private static function fetchFallbackRelated(int $productId, array $excludeCategoryIds, int $limit): array
    {
        if ($limit <= 0) return [];
        $params = [$productId];
        $where = 'WHERE p.is_active = 1 AND p.id != ?';
        if (!empty($excludeCategoryIds)) {
            $ph = implode(',', array_fill(0, count($excludeCategoryIds), '?'));
            $where .= " AND p.category_id NOT IN ($ph)";
            array_push($params, ...$excludeCategoryIds);
        }
        $where .= ' ORDER BY c.sort_order ASC, p.sort_order ASC, p.id DESC LIMIT ' . (int)$limit;
        return self::fetchProductRows($where, $params);
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
        $productCode = self::resolveProductCode($data, $editId);

        try {
            if ($editId) {
                self::updateProduct((int)$editId, $data, $slug, $productCode);
                self::syncSpecs($editId, $data['specs'] ?? []);
                self::syncQuantityTiers($editId, $data['quantity_tiers'] ?? []);
                \Orders\AdminAudit::log('product_updated', "Product #{$editId}: {$data['name']}");
                return ['ok' => true, 'id' => $editId];
            }

            $id = self::insertProduct($data, $slug, $productCode);

            self::syncSpecs((int)$id, $data['specs'] ?? []);
            self::syncQuantityTiers((int)$id, $data['quantity_tiers'] ?? []);
            \Orders\AdminAudit::log('product_created', "Product #{$id}: {$data['name']}");
            return ['ok' => true, 'id' => (int)$id];
        } catch (\Throwable $e) {
            error_log('Product upsert failed: ' . $e->getMessage());
            return ['ok' => false, 'msg' => 'Save failed. Check required DB columns/tables and server logs.'];
        }
    }

    private static function updateProduct(int $editId, array $data, string $slug, ?string $productCode): void
    {
        if (self::productCodeColumnReady()) {
            try {
                \Database::query(
                    "UPDATE products SET name=?, slug=?, category_id=?, product_code=?, description=?,
                        meta_title=?, design_fee=?, image_path=?, is_active=?, sort_order=?, updated_at=NOW() WHERE id=?",
                    [
                        $data['name'], $slug, $data['category_id'], $productCode,
                        $data['description'] ?? '',
                        $data['meta_title'] ?? $data['name'],
                        (float)($data['design_fee'] ?? 0),
                        $data['image_path'] ?? null,
                        $data['is_active'] ?? 1,
                        $data['sort_order'] ?? 0,
                        $editId,
                    ]
                );
                return;
            } catch (\Throwable) {}
        }

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
    }

    private static function insertProduct(array $data, string $slug, ?string $productCode): int
    {
        if (self::productCodeColumnReady()) {
            try {
                return (int)\Database::insert(
                    "INSERT INTO products (name, slug, category_id, product_code, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                    [
                        $data['name'], $slug, $data['category_id'], $productCode,
                        $data['description'] ?? '',
                        $data['meta_title'] ?? $data['name'],
                        (float)($data['design_fee'] ?? 0),
                        $data['image_path'] ?? null,
                        $data['is_active'] ?? 1,
                        $data['sort_order'] ?? 0,
                    ]
                );
            } catch (\Throwable) {}
        }

        try {
            return (int)\Database::insert(
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
            return (int)\Database::insert(
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
    }

    private static function resolveProductCode(array $data, ?int $editId = null): ?string
    {
        if (!self::productCodeColumnReady()) return null;

        $manual = strtoupper(trim((string)($data['product_code'] ?? '')));
        $manual = preg_replace('/[^A-Z0-9\-]/', '', $manual) ?: '';
        if ($manual !== '') return $manual;

        $categoryId = (int)($data['category_id'] ?? 0);
        if ($categoryId <= 0) return null;
        return self::generateProductCode($categoryId, $editId);
    }

    private static function generateProductCode(int $categoryId, ?int $editId = null): ?string
    {
        $prefix = 'RCSPRD';
        if (self::categoryCodePrefixColumnReady()) {
            $cat = \Database::row("SELECT code_prefix FROM categories WHERE id = ? LIMIT 1", [$categoryId]);
            $fromDb = strtoupper(trim((string)($cat['code_prefix'] ?? '')));
            if ($fromDb !== '') {
                $prefix = preg_replace('/[^A-Z0-9]/', '', $fromDb) ?: $prefix;
            }
        }

        $like = $prefix . '-%';
        $params = [$categoryId, $like];
        $sql = "SELECT product_code FROM products WHERE category_id = ? AND product_code LIKE ?";
        if ($editId) {
            $sql .= " AND id <> ?";
            $params[] = $editId;
        }
        $rows = \Database::rows($sql, $params);

        $max = 0;
        foreach ($rows as $r) {
            $code = (string)($r['product_code'] ?? '');
            if (preg_match('/-(\d+)$/', $code, $m)) {
                $max = max($max, (int)$m[1]);
            }
        }
        $next = $max + 1;
        return $prefix . '-' . str_pad((string)$next, 3, '0', STR_PAD_LEFT);
    }

    public static function nextProductCodePreview(int $categoryId, ?int $editId = null): ?string
    {
        if ($categoryId <= 0) return null;
        if (!self::productCodeColumnReady()) return null;
        return self::generateProductCode($categoryId, $editId);
    }

    private static function ensureProductCode(array $product): array
    {
        if (!self::productCodeColumnReady()) return $product;
        if (!empty($product['product_code'])) return $product;

        $categoryId = (int)($product['category_id'] ?? 0);
        $productId = (int)($product['id'] ?? 0);
        if ($categoryId <= 0 || $productId <= 0) return $product;

        $code = self::generateProductCode($categoryId, $productId);
        if (!$code) return $product;

        try {
            \Database::query("UPDATE products SET product_code=? WHERE id=? AND (product_code IS NULL OR product_code='')", [$code, $productId]);
            $product['product_code'] = $code;
        } catch (\Throwable) {
            // keep response backward-compatible even if DB update fails
        }

        return $product;
    }

    private static function productCodeColumnReady(): bool
    {
        if (self::$hasProductCodeColumn !== null) return self::$hasProductCodeColumn;
        try {
            $row = \Database::row(
                "SELECT COUNT(*) AS c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'products' AND COLUMN_NAME = 'product_code'",
                [DB_NAME]
            );
            self::$hasProductCodeColumn = (int)($row['c'] ?? 0) === 1;
        } catch (\Throwable) {
            self::$hasProductCodeColumn = false;
        }
        return self::$hasProductCodeColumn;
    }

    private static function categoryCodePrefixColumnReady(): bool
    {
        if (self::$hasCategoryCodePrefixColumn !== null) return self::$hasCategoryCodePrefixColumn;
        try {
            $row = \Database::row(
                "SELECT COUNT(*) AS c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'categories' AND COLUMN_NAME = 'code_prefix'",
                [DB_NAME]
            );
            self::$hasCategoryCodePrefixColumn = (int)($row['c'] ?? 0) === 1;
        } catch (\Throwable) {
            self::$hasCategoryCodePrefixColumn = false;
        }
        return self::$hasCategoryCodePrefixColumn;
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
