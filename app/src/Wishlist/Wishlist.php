<?php

declare(strict_types=1);

namespace Wishlist;

class Wishlist
{
    private static ?bool $schemaReady = null;

    public static function itemsForUser(int $userId): array
    {
        if ($userId <= 0 || !self::schemaReady()) return [];

        try {
            return \Database::rows(
                "SELECT w.created_at AS wishlisted_at,
                        p.*,
                        c.name AS category_name,
                        c.slug AS category_slug,
                        (SELECT COALESCE(pi.image_path, pi.url)
                         FROM product_images pi
                         WHERE pi.product_id = p.id AND pi.is_primary = 1
                         ORDER BY pi.sort_order ASC, pi.id ASC
                         LIMIT 1) AS primary_image,
                        (SELECT MIN(t.price)
                         FROM product_quantity_tiers t
                         WHERE t.product_id = p.id) AS min_price
                 FROM wishlists w
                 JOIN products p ON p.id = w.product_id AND p.is_active = 1
                 LEFT JOIN categories c ON c.id = p.category_id
                 WHERE w.user_id = ?
                 ORDER BY w.created_at DESC, w.id DESC",
                [$userId]
            );
        } catch (\Throwable $e) {
            error_log('Wishlist items unavailable: ' . $e->getMessage());
            return [];
        }
    }

    public static function isWishlisted(int $userId, int $productId): bool
    {
        if ($userId <= 0 || $productId <= 0 || !self::schemaReady()) return false;

        try {
            $row = \Database::row(
                "SELECT id FROM wishlists WHERE user_id = ? AND product_id = ? LIMIT 1",
                [$userId, $productId]
            );
            return !empty($row);
        } catch (\Throwable) {
            return false;
        }
    }

    public static function toggle(int $userId, int $productId): array
    {
        if ($userId <= 0) return ['ok' => false, 'msg' => 'Login required'];
        if ($productId <= 0) return ['ok' => false, 'msg' => 'Invalid product'];
        if (!self::schemaReady()) return ['ok' => false, 'msg' => 'Wishlist table missing. Please run the wishlist SQL migration.'];

        try {
            $product = \Database::row("SELECT id FROM products WHERE id = ? AND is_active = 1 LIMIT 1", [$productId]);
            if (!$product) return ['ok' => false, 'msg' => 'Product not found'];

            if (self::isWishlisted($userId, $productId)) {
                \Database::query("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
                return ['ok' => true, 'wishlisted' => false, 'msg' => 'Removed from wishlist'];
            }

            \Database::query(
                "INSERT IGNORE INTO wishlists (user_id, product_id, created_at) VALUES (?, ?, NOW())",
                [$userId, $productId]
            );
            return ['ok' => true, 'wishlisted' => true, 'msg' => 'Added to wishlist'];
        } catch (\Throwable $e) {
            error_log('Wishlist toggle failed: ' . $e->getMessage());
            return ['ok' => false, 'msg' => 'Could not update wishlist'];
        }
    }

    public static function remove(int $userId, int $productId): array
    {
        if ($userId <= 0) return ['ok' => false, 'msg' => 'Login required'];
        if ($productId <= 0) return ['ok' => false, 'msg' => 'Invalid product'];
        if (!self::schemaReady()) return ['ok' => false, 'msg' => 'Wishlist table missing. Please run the wishlist SQL migration.'];

        try {
            \Database::query("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
            return ['ok' => true, 'msg' => 'Removed from wishlist'];
        } catch (\Throwable $e) {
            error_log('Wishlist remove failed: ' . $e->getMessage());
            return ['ok' => false, 'msg' => 'Could not remove wishlist item'];
        }
    }

    public static function countForUser(int $userId): int
    {
        if ($userId <= 0 || !self::schemaReady()) return 0;
        try {
            $row = \Database::row("SELECT COUNT(*) AS c FROM wishlists WHERE user_id = ?", [$userId]);
            return (int)($row['c'] ?? 0);
        } catch (\Throwable) {
            return 0;
        }
    }

    private static function schemaReady(): bool
    {
        if (self::$schemaReady !== null) return self::$schemaReady;
        try {
            $row = \Database::row(
                "SELECT COUNT(*) AS c
                 FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'wishlists'",
                [DB_NAME]
            );
            self::$schemaReady = (int)($row['c'] ?? 0) === 1;
        } catch (\Throwable) {
            self::$schemaReady = false;
        }
        return self::$schemaReady;
    }
}
