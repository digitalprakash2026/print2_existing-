<?php

declare(strict_types=1);

namespace Catalog;

final class CatalogImportExport
{
    private const CATEGORY_HEADERS = ['name','slug','code_prefix','icon','image_path','image_alt','sort_order','is_active'];
    private const PRODUCT_HEADERS = ['product_code','name','category_slug','category_name','description','meta_title','design_fee','original_price','image_path','sort_order','is_active'];

    public static function streamCategoriesCsv(): never
    {
        self::streamCsv('categories-' . date('Y-m-d') . '.csv', self::CATEGORY_HEADERS, self::categoryExportRows());
    }

    public static function streamProductsCsv(): never
    {
        self::streamCsv('products-' . date('Y-m-d') . '.csv', self::PRODUCT_HEADERS, self::productExportRows());
    }

    public static function streamCategorySampleCsv(): never
    {
        self::streamCsv('categories-import-template.csv', self::CATEGORY_HEADERS, [[
            'Business Cards', 'business-cards', 'RCSBC', '💳', '/uploads/categories/business-cards.jpg', 'Business Cards category image', '1', '1',
        ]]);
    }

    public static function streamProductSampleCsv(): never
    {
        self::streamCsv('products-import-template.csv', self::PRODUCT_HEADERS, [[
            'RCSBC001', 'Premium Business Card', 'business-cards', '', 'Premium 350 GSM business cards.', 'Premium Business Card', '350', '999', '/uploads/products/business-card.jpg', '1', '1',
        ]]);
    }

    public static function importCategories(array $file, string $mode = 'create_update'): array
    {
        $parsed = self::parseUploadedCsv($file, ['name']);
        if (!$parsed['ok']) return $parsed;

        $summary = self::emptySummary(count($parsed['rows']));
        $mode = self::normalizeMode($mode);

        foreach ($parsed['rows'] as $index => $row) {
            $line = $index + 2;
            $name = trim((string)($row['name'] ?? ''));
            if ($name === '') {
                self::fail($summary, $line, 'Category name is required.');
                continue;
            }

            $slug = self::slug((string)($row['slug'] ?? $name));
            $existing = self::categoryBySlug($slug);
            if ($existing && $mode === 'create') {
                $summary['skipped']++;
                $summary['errors'][] = ['row' => $line, 'message' => 'Skipped existing category slug: ' . $slug];
                continue;
            }
            if (!$existing && $mode === 'update') {
                $summary['skipped']++;
                $summary['errors'][] = ['row' => $line, 'message' => 'Skipped new category in update-only mode: ' . $slug];
                continue;
            }

            $data = self::categoryData($row, $name, $slug, $existing);
            try {
                if ($existing) {
                    self::updateCategory((int)$existing['id'], $data);
                    $summary['updated']++;
                } else {
                    self::insertCategory($data);
                    $summary['created']++;
                }
            } catch (\Throwable $e) {
                self::fail($summary, $line, 'Category save failed: ' . $e->getMessage());
            }
        }

        self::audit('categories_imported', $summary);
        return ['ok' => true] + $summary;
    }

    public static function importProducts(array $file, string $mode = 'create_update'): array
    {
        $parsed = self::parseUploadedCsv($file, ['name']);
        if (!$parsed['ok']) return $parsed;

        $summary = self::emptySummary(count($parsed['rows']));
        $mode = self::normalizeMode($mode);

        foreach ($parsed['rows'] as $index => $row) {
            $line = $index + 2;
            $name = trim((string)($row['name'] ?? ''));
            if ($name === '') {
                self::fail($summary, $line, 'Product name is required.');
                continue;
            }

            $category = self::resolveCategory($row);
            if (!$category) {
                self::fail($summary, $line, 'Category not found. Use category_slug, category_name, or category_id.');
                continue;
            }

            $existing = self::findProduct($row, $name);
            if ($existing && $mode === 'create') {
                $summary['skipped']++;
                $summary['errors'][] = ['row' => $line, 'message' => 'Skipped existing product: ' . $name];
                continue;
            }
            if (!$existing && $mode === 'update') {
                $summary['skipped']++;
                $summary['errors'][] = ['row' => $line, 'message' => 'Skipped new product in update-only mode: ' . $name];
                continue;
            }

            $payload = self::productPayload($row, $name, (int)$category['id']);
            if ($existing) {
                try {
                    self::updateProductBasic((int)$existing['id'], $payload);
                    $summary['updated']++;
                } catch (\Throwable $e) {
                    self::fail($summary, $line, 'Product update failed: ' . $e->getMessage());
                }
                continue;
            }

            $result = ProductCatalog::upsert($payload);
            if (!($result['ok'] ?? false)) {
                self::fail($summary, $line, (string)($result['msg'] ?? 'Product save failed.'));
                continue;
            }
            $summary['created']++;
        }

        self::audit('products_imported', $summary);
        return ['ok' => true] + $summary;
    }

    private static function categoryExportRows(): array
    {
        return array_map(static fn(array $c): array => [
            $c['name'] ?? '', $c['slug'] ?? '', $c['code_prefix'] ?? '', $c['icon'] ?? '', $c['image_path'] ?? '', $c['image_alt'] ?? '', $c['sort_order'] ?? 0, $c['is_active'] ?? 1,
        ], ProductCatalog::categories());
    }

    private static function productExportRows(): array
    {
        return array_map(static fn(array $p): array => [
            $p['product_code'] ?? '', $p['name'] ?? '', $p['category_slug'] ?? '', $p['category_name'] ?? '', $p['description'] ?? '', $p['meta_title'] ?? '', $p['design_fee'] ?? 0, $p['original_price'] ?? '', $p['image_path'] ?? ($p['primary_image'] ?? ''), $p['sort_order'] ?? 0, $p['is_active'] ?? 1,
        ], ProductCatalog::all(false));
    }

    private static function streamCsv(string $filename, array $headers, array $rows): never
    {
        while (ob_get_level() > 0) @ob_end_clean();
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        fputcsv($out, $headers);
        foreach ($rows as $row) fputcsv($out, $row);
        fclose($out);
        exit;
    }

    private static function parseUploadedCsv(array $file, array $required): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file((string)($file['tmp_name'] ?? ''))) {
            return ['ok' => false, 'msg' => 'Please upload a CSV file.'];
        }
        if ((int)($file['size'] ?? 0) > 8 * 1024 * 1024) {
            return ['ok' => false, 'msg' => 'CSV file must be 8MB or smaller.'];
        }
        $ext = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            return ['ok' => false, 'msg' => 'Only CSV import is supported in this version. Download the sample template and save it as CSV from Excel.'];
        }

        $handle = fopen((string)$file['tmp_name'], 'r');
        if (!$handle) return ['ok' => false, 'msg' => 'Could not read uploaded file.'];

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return ['ok' => false, 'msg' => 'CSV header row is missing.'];
        }
        $header = array_map([self::class, 'normalizeHeader'], $header);
        foreach ($required as $column) {
            if (!in_array($column, $header, true)) {
                fclose($handle);
                return ['ok' => false, 'msg' => 'Required column missing: ' . $column];
            }
        }

        $rows = [];
        while (($values = fgetcsv($handle)) !== false) {
            if (count(array_filter($values, static fn($v): bool => trim((string)$v) !== '')) === 0) continue;
            $row = [];
            foreach ($header as $i => $key) {
                if ($key === '') continue;
                $row[$key] = isset($values[$i]) ? trim((string)$values[$i]) : '';
            }
            $rows[] = $row;
        }
        fclose($handle);

        return ['ok' => true, 'rows' => $rows];
    }

    private static function normalizeHeader(string $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;
        $header = strtolower(trim($header));
        return trim(preg_replace('/[^a-z0-9]+/', '_', $header) ?? '', '_');
    }

    private static function categoryData(array $row, string $name, string $slug, ?array $existing): array
    {
        $prefix = strtoupper(preg_replace('/[^A-Z0-9]/', '', strtoupper(trim((string)($row['code_prefix'] ?? ($existing['code_prefix'] ?? ''))))) ?? '');
        return [
            'name' => $name,
            'slug' => $slug,
            'code_prefix' => $prefix !== '' ? $prefix : null,
            'icon' => trim((string)($row['icon'] ?? ($existing['icon'] ?? '🖨️'))) ?: '🖨️',
            'image_path' => trim((string)($row['image_path'] ?? ($existing['image_path'] ?? ''))) ?: null,
            'image_alt' => trim((string)($row['image_alt'] ?? ($existing['image_alt'] ?? ''))) ?: ($name . ' category image'),
            'sort_order' => self::intValue($row['sort_order'] ?? ($existing['sort_order'] ?? 0)),
            'is_active' => self::boolValue($row['is_active'] ?? ($existing['is_active'] ?? 1)),
        ];
    }

    private static function insertCategory(array $data): int
    {
        try {
            return (int)\Database::insert(
                "INSERT INTO categories (name,slug,code_prefix,icon,image_path,image_alt,sort_order,is_active) VALUES (?,?,?,?,?,?,?,?)",
                [$data['name'],$data['slug'],$data['code_prefix'],$data['icon'],$data['image_path'],$data['image_alt'],$data['sort_order'],$data['is_active']]
            );
        } catch (\Throwable) {
            return (int)\Database::insert(
                "INSERT INTO categories (name,slug,icon,image_path,image_alt,sort_order,is_active) VALUES (?,?,?,?,?,?,?)",
                [$data['name'],$data['slug'],$data['icon'],$data['image_path'],$data['image_alt'],$data['sort_order'],$data['is_active']]
            );
        }
    }

    private static function updateCategory(int $id, array $data): void
    {
        try {
            \Database::query(
                "UPDATE categories SET name=?, slug=?, code_prefix=?, icon=?, image_path=?, image_alt=?, sort_order=?, is_active=? WHERE id=?",
                [$data['name'],$data['slug'],$data['code_prefix'],$data['icon'],$data['image_path'],$data['image_alt'],$data['sort_order'],$data['is_active'],$id]
            );
        } catch (\Throwable) {
            \Database::query(
                "UPDATE categories SET name=?, slug=?, icon=?, image_path=?, image_alt=?, sort_order=?, is_active=? WHERE id=?",
                [$data['name'],$data['slug'],$data['icon'],$data['image_path'],$data['image_alt'],$data['sort_order'],$data['is_active'],$id]
            );
        }
    }

    private static function updateProductBasic(int $productId, array $data): void
    {
        $sets = [
            'name = ?', 'category_id = ?', 'description = ?', 'meta_title = ?', 'design_fee = ?', 'is_active = ?', 'sort_order = ?', 'updated_at = NOW()',
        ];
        $params = [
            $data['name'], $data['category_id'], $data['description'] ?? '', $data['meta_title'] ?? $data['name'],
            (float)($data['design_fee'] ?? 0), (int)($data['is_active'] ?? 1), (int)($data['sort_order'] ?? 0),
        ];

        if (self::columnExists('products', 'image_path')) {
            $sets[] = 'image_path = ?';
            $params[] = $data['image_path'] ?? null;
        }

        if (self::columnExists('products', 'product_code')) {
            $sets[] = 'product_code = ?';
            $params[] = trim((string)($data['product_code'] ?? '')) ?: null;
        }
        if (self::columnExists('products', 'original_price')) {
            $sets[] = 'original_price = ?';
            $params[] = $data['original_price'] ?? null;
        }

        $params[] = $productId;
        \Database::query('UPDATE products SET ' . implode(', ', $sets) . ' WHERE id = ?', $params);
    }

    private static function columnExists(string $table, string $column): bool
    {
        static $cache = [];
        $key = $table . '.' . $column;
        if (array_key_exists($key, $cache)) return $cache[$key];
        try {
            $row = \Database::row(
                'SELECT COUNT(*) AS c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                [$table, $column]
            );
            return $cache[$key] = ((int)($row['c'] ?? 0) > 0);
        } catch (\Throwable) {
            return $cache[$key] = false;
        }
    }

    private static function productPayload(array $row, string $name, int $categoryId): array
    {
        return [
            'name' => $name,
            'category_id' => $categoryId,
            'product_code' => trim((string)($row['product_code'] ?? '')),
            'description' => trim((string)($row['description'] ?? '')),
            'meta_title' => trim((string)($row['meta_title'] ?? '')) ?: $name,
            'design_fee' => self::floatValue($row['design_fee'] ?? 0),
            'original_price' => self::nullableFloat($row['original_price'] ?? null),
            'image_path' => trim((string)($row['image_path'] ?? '')) ?: null,
            'is_active' => self::boolValue($row['is_active'] ?? 1),
            'sort_order' => self::intValue($row['sort_order'] ?? 0),
            'specs' => [],
            'quantity_tiers' => [],
            'filter_options' => [],
        ];
    }

    private static function resolveCategory(array $row): ?array
    {
        $id = (int)($row['category_id'] ?? 0);
        if ($id > 0) {
            $cat = \Database::row('SELECT * FROM categories WHERE id = ? LIMIT 1', [$id]);
            if ($cat) return $cat;
        }
        $slug = self::slug((string)($row['category_slug'] ?? ''));
        if ($slug !== '') {
            $cat = self::categoryBySlug($slug);
            if ($cat) return $cat;
        }
        $name = trim((string)($row['category_name'] ?? ''));
        if ($name !== '') {
            return \Database::row('SELECT * FROM categories WHERE LOWER(name) = LOWER(?) LIMIT 1', [$name]) ?: null;
        }
        return null;
    }

    private static function findProduct(array $row, string $name): ?array
    {
        $code = trim((string)($row['product_code'] ?? ''));
        if ($code !== '') {
            try {
                $found = \Database::row('SELECT * FROM products WHERE product_code = ? LIMIT 1', [$code]);
                if ($found) return $found;
            } catch (\Throwable) {}
        }
        $slug = self::slug((string)($row['slug'] ?? $name));
        return \Database::row('SELECT * FROM products WHERE slug = ? LIMIT 1', [$slug]) ?: null;
    }

    private static function categoryBySlug(string $slug): ?array
    {
        if ($slug === '') return null;
        return \Database::row('SELECT * FROM categories WHERE slug = ? LIMIT 1', [$slug]) ?: null;
    }

    private static function slug(string $value): string
    {
        $value = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        return trim($slug, '-');
    }

    private static function normalizeMode(string $mode): string
    {
        return in_array($mode, ['create', 'update', 'create_update'], true) ? $mode : 'create_update';
    }

    private static function boolValue(mixed $value): int
    {
        $value = strtolower(trim((string)$value));
        if (in_array($value, ['0','false','no','inactive','disabled'], true)) return 0;
        return 1;
    }

    private static function intValue(mixed $value): int
    {
        return (int)preg_replace('/[^0-9\-]/', '', (string)$value);
    }

    private static function floatValue(mixed $value): float
    {
        return (float)preg_replace('/[^0-9.\-]/', '', (string)$value);
    }

    private static function nullableFloat(mixed $value): ?float
    {
        $raw = trim((string)$value);
        if ($raw === '') return null;
        return self::floatValue($raw);
    }

    private static function emptySummary(int $total): array
    {
        return ['total' => $total, 'created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => []];
    }

    private static function fail(array &$summary, int $row, string $message): void
    {
        $summary['failed']++;
        $summary['errors'][] = ['row' => $row, 'message' => $message];
    }

    private static function audit(string $action, array $summary): void
    {
        try {
            \Orders\AdminAudit::log($action, sprintf('Rows: %d, created: %d, updated: %d, skipped: %d, failed: %d', $summary['total'], $summary['created'], $summary['updated'], $summary['skipped'], $summary['failed']));
        } catch (\Throwable) {}
    }
}
