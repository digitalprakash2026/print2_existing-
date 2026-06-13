<?php

declare(strict_types=1);

namespace Designs;

final class UserDesigns
{
    public static function forUser(int $userId, int $limit = 24): array
    {
        if ($userId <= 0 || !self::tableReady()) {
            return [];
        }

        $limit = max(1, min(100, $limit));

        try {
            return \Database::rows(
                "SELECT af.id, af.filename, af.original_name, af.file_path, af.mime_type, af.file_size, af.created_at,
                        oi.product_id, oi.product_name, oi.design_choice, oi.quantity, oi.quality_name,
                        o.id AS order_db_id, o.order_id, o.status AS order_status, o.created_at AS order_created_at,
                        p.slug AS product_slug
                   FROM artwork_files af
                   INNER JOIN order_items oi ON oi.id = af.order_item_id
                   INNER JOIN orders o ON o.id = oi.order_id
                   LEFT JOIN products p ON p.id = oi.product_id
                  WHERE o.user_id = ?
                  ORDER BY COALESCE(af.created_at, o.created_at) DESC, af.id DESC
                  LIMIT {$limit}",
                [$userId]
            );
        } catch (\Throwable $e) {
            error_log('Unable to load user designs: ' . $e->getMessage());
            return [];
        }
    }

    public static function downloadForUser(int $userId, int $artworkId): ?array
    {
        if ($userId <= 0 || $artworkId <= 0 || !self::tableReady()) {
            return null;
        }

        try {
            $row = \Database::row(
                "SELECT af.*
                   FROM artwork_files af
                   INNER JOIN order_items oi ON oi.id = af.order_item_id
                   INNER JOIN orders o ON o.id = oi.order_id
                  WHERE af.id = ? AND o.user_id = ?
                  LIMIT 1",
                [$artworkId, $userId]
            );

            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('Unable to load user design download: ' . $e->getMessage());
            return null;
        }
    }

    public static function publicFilePath(array $design): string
    {
        $path = trim((string)($design['file_path'] ?? ''));
        if ($path === '') {
            return '';
        }

        return '/' . ltrim($path, '/');
    }

    public static function isImage(array $design): bool
    {
        return str_starts_with(strtolower((string)($design['mime_type'] ?? '')), 'image/');
    }

    public static function formattedSize(array $design): string
    {
        $bytes = (int)($design['file_size'] ?? 0);
        if ($bytes <= 0) {
            return 'File size unavailable';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = (float)$bytes;
        $unit = 0;
        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return rtrim(rtrim(number_format($size, 1), '0'), '.') . ' ' . $units[$unit];
    }

    private static function tableReady(): bool
    {
        static $ready = null;
        if ($ready !== null) {
            return $ready;
        }

        try {
            \Database::row('SELECT id FROM artwork_files LIMIT 1');
            $ready = true;
        } catch (\Throwable) {
            $ready = false;
        }

        return $ready;
    }
}
