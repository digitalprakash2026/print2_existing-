<?php
// ─────────────────────────────────────────────────────────────
//  RCS Graphic — Admin Router
// ─────────────────────────────────────────────────────────────

declare(strict_types=1);

if (!str_starts_with($uri, '/admin')) return;

if ($uri === '/admin/login' && $method === 'GET') {
    if (\Auth\Auth::isAdmin()) redirect('/admin');
    view('admin/login');
    exit;
}

if ($uri === '/admin/login' && $method === 'POST') {
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');
    $result   = \Auth\Auth::adminLogin($email, $password);
    if ($result['ok']) redirect('/admin');
    view('admin/login', ['loginError' => $result['msg']]);
    exit;
}

if ($uri === '/admin/logout') {
    \Auth\Auth::adminLogout();
    redirect('/admin/login');
}

\Auth\Auth::requireAdmin();
$orderSeenColumnReady = null;
$orderSeenColumnAvailable = static function () use (&$orderSeenColumnReady): bool {
    if ($orderSeenColumnReady !== null) return $orderSeenColumnReady;
    try {
        $row = Database::row(
            "SELECT 1 AS ok
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'orders'
               AND COLUMN_NAME = 'is_seen'
             LIMIT 1"
        );
        $orderSeenColumnReady = (bool)$row;
    } catch (\Throwable) {
        $orderSeenColumnReady = false;
    }
    return $orderSeenColumnReady;
};
$ensureOrderSeenColumn = static function () use (&$orderSeenColumnReady, $orderSeenColumnAvailable): bool {
    if ($orderSeenColumnAvailable()) return true;
    try {
        Database::query("ALTER TABLE orders ADD COLUMN is_seen TINYINT(1) NOT NULL DEFAULT 0 AFTER status");
        try { Database::query("CREATE INDEX idx_orders_is_seen ON orders (is_seen, created_at)"); } catch (\Throwable) {}
        $orderSeenColumnReady = true;
        return true;
    } catch (\Throwable $e) {
        error_log('Order is_seen column unavailable: ' . $e->getMessage());
        $orderSeenColumnReady = false;
        return false;
    }
};
\Orders\OrderManager::ensureWorkflowSchema();
\Orders\OrderManager::ensureDesignApprovalSchema();
\Approvals\ContentApprovalManager::ensureSchema();
\Faq\FaqManager::ensureSchema();

$adminUsersHasMobile = null;
$hasAdminUsersMobile = static function () use (&$adminUsersHasMobile): bool {
    if ($adminUsersHasMobile !== null) return $adminUsersHasMobile;
    try {
        $row = Database::row(
            "SELECT 1 AS ok
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'admin_users'
               AND COLUMN_NAME = 'mobile'
             LIMIT 1"
        );
        $adminUsersHasMobile = (bool)$row;
    } catch (\Throwable) {
        $adminUsersHasMobile = false;
    }
    return $adminUsersHasMobile;
};

if (str_starts_with($uri, '/admin/api/')) {
    header('Content-Type: application/json');
    $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $slugify = static function (string $value): string {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '';
        $value = trim($value, '-');
        return $value !== '' ? $value : 'blog-post';
    };
    $sanitizeBlogContent = static function (string $html): string {
        $allowed = '<p><br><strong><b><em><i><u><h2><h3><h4><ul><ol><li><a><blockquote><img><figure><figcaption><div><span><hr><iframe><video><source>';
        $clean = strip_tags($html, $allowed);
        $clean = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean) ?? $clean;
        $clean = preg_replace('/(href|src)\s*=\s*("|\')\s*javascript:[^"\']*("|\')/i', '$1="#"', $clean) ?? $clean;
        $clean = preg_replace_callback('/<iframe\b([^>]*)>/i', static function (array $m): string {
            $attrs = $m[1] ?? '';
            if (!preg_match('/src\s*=\s*("|\')([^"\']+)\1/i', $attrs, $srcMatch)) {
                return '';
            }
            $src = $srcMatch[2];
            if (!preg_match('#^https://(www\.)?(youtube\.com/embed/|player\.vimeo\.com/video/)#i', $src)) {
                return '';
            }
            return '<iframe src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" loading="lazy" allowfullscreen></iframe>';
        }, $clean) ?? $clean;
        return trim($clean);
    };
    $uniqueBlogSlug = static function (string $base, int $ignoreId = 0) use ($slugify): string {
        $slug = $slugify($base);
        $candidate = $slug;
        $i = 2;
        while (true) {
            $params = [$candidate];
            $sql = "SELECT id FROM blogs WHERE slug = ?";
            if ($ignoreId > 0) {
                $sql .= " AND id <> ?";
                $params[] = $ignoreId;
            }
            $sql .= " LIMIT 1";
            $row = Database::row($sql, $params);
            if (!$row) return $candidate;
            $candidate = $slug . '-' . $i;
            $i++;
        }
    };

    if ($uri === '/admin/api/faqs' && $method === 'GET') {
        json(['ok' => true, 'faqs' => \Faq\FaqManager::all(), 'page_labels' => \Faq\FaqManager::PAGE_LABELS]);
    }
    if ($uri === '/admin/api/faqs' && $method === 'POST') {
        $result = \Faq\FaqManager::save($body);
        json($result, ($result['ok'] ?? false) ? 200 : 422);
    }
    if (preg_match('#^/admin/api/faqs/(\d+)$#', $uri, $m) && $method === 'PUT') {
        $result = \Faq\FaqManager::save($body, (int)$m[1]);
        json($result, ($result['ok'] ?? false) ? 200 : 422);
    }
    if (preg_match('#^/admin/api/faqs/(\d+)/toggle$#', $uri, $m) && $method === 'POST') {
        $result = \Faq\FaqManager::toggle((int)$m[1]);
        json($result, ($result['ok'] ?? false) ? 200 : 422);
    }
    if (preg_match('#^/admin/api/faqs/(\d+)$#', $uri, $m) && $method === 'DELETE') {
        $result = \Faq\FaqManager::delete((int)$m[1]);
        json($result, ($result['ok'] ?? false) ? 200 : 422);
    }

    $adminProductImages = static function (int $productId): array {
        try {
            return Database::rows(
                "SELECT id, product_id, COALESCE(image_path, url) AS url, COALESCE(image_path, url) AS image_path, alt_text, is_primary, sort_order
                 FROM product_images
                 WHERE product_id = ?
                 ORDER BY is_primary DESC, sort_order ASC, id ASC",
                [$productId]
            );
        } catch (\Throwable) {
            $images = Database::rows(
                "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC, id ASC",
                [$productId]
            );
            foreach ($images as &$img) {
                if (!isset($img['url']) && isset($img['image_path'])) $img['url'] = $img['image_path'];
                if (!isset($img['image_path']) && isset($img['url'])) $img['image_path'] = $img['url'];
            }
            unset($img);
            return $images;
        }
    };

    $ensureHomeBannerClickColumns = static function (): void {
        static $ready = false;
        if ($ready) return;
        try {
            $rows = Database::rows(
                "SELECT COLUMN_NAME
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'home_banners'
                   AND COLUMN_NAME IN ('image_click_enabled', 'image_click_url')"
            );
            $present = array_flip(array_map(static fn($row) => (string)($row['COLUMN_NAME'] ?? ''), $rows));
            if (!isset($present['image_click_enabled'])) {
                Database::query("ALTER TABLE home_banners ADD COLUMN image_click_enabled TINYINT(1) NOT NULL DEFAULT 0 AFTER cta_secondary_url");
            }
            if (!isset($present['image_click_url'])) {
                Database::query("ALTER TABLE home_banners ADD COLUMN image_click_url VARCHAR(255) NOT NULL DEFAULT '' AFTER image_click_enabled");
            }
            $ready = true;
        } catch (\Throwable $e) {
            error_log('Home banner click columns unavailable: ' . $e->getMessage());
        }
    };

    $deleteProductImage = static function (int $productId, int $imageId) use ($adminProductImages): array {
        if ($productId <= 0 || $imageId <= 0) {
            return ['ok' => false, 'msg' => 'Invalid product image'];
        }

        try {
            $image = Database::row(
                "SELECT id, product_id, COALESCE(image_path, url) AS image_path, COALESCE(image_path, url) AS url, is_primary
                 FROM product_images
                 WHERE id = ? AND product_id = ?
                 LIMIT 1",
                [$imageId, $productId]
            );
        } catch (\Throwable) {
            $image = Database::row("SELECT * FROM product_images WHERE id = ? AND product_id = ? LIMIT 1", [$imageId, $productId]);
            if ($image) {
                if (!isset($image['url']) && isset($image['image_path'])) $image['url'] = $image['image_path'];
                if (!isset($image['image_path']) && isset($image['url'])) $image['image_path'] = $image['url'];
            }
        }

        if (!$image) {
            return ['ok' => false, 'msg' => 'Image not found'];
        }

        $path = (string)($image['image_path'] ?? $image['url'] ?? '');
        $wasPrimary = (int)($image['is_primary'] ?? 0) === 1;

        Database::query("DELETE FROM product_images WHERE id = ? AND product_id = ?", [$imageId, $productId]);

        $remaining = $adminProductImages($productId);
        $nextPrimary = null;
        foreach ($remaining as $candidate) {
            if ((int)($candidate['is_primary'] ?? 0) === 1) {
                $nextPrimary = $candidate;
                break;
            }
        }
        if (!$nextPrimary && $remaining) {
            $nextPrimary = $remaining[0];
            Database::query("UPDATE product_images SET is_primary = 1 WHERE id = ? AND product_id = ?", [(int)$nextPrimary['id'], $productId]);
        }

        $primaryPath = $nextPrimary ? (string)($nextPrimary['image_path'] ?? $nextPrimary['url'] ?? '') : null;
        try {
            Database::query("UPDATE products SET image_path = ? WHERE id = ?", [$primaryPath, $productId]);
        } catch (\Throwable) {
            // Older schemas may not have products.image_path; product_images still stays correct.
        }

        if ($path !== '' && str_starts_with($path, '/uploads/products/')) {
            $productUses = 0;
            $imageUses = 0;
            try { $productUses = (int)(Database::row("SELECT COUNT(*) c FROM products WHERE image_path = ?", [$path])['c'] ?? 0); } catch (\Throwable) {}
            try {
                $imageUses = (int)(Database::row(
                    "SELECT COUNT(*) c FROM product_images WHERE COALESCE(image_path, url) = ?",
                    [$path]
                )['c'] ?? 0);
            } catch (\Throwable) {
                try { $imageUses = (int)(Database::row("SELECT COUNT(*) c FROM product_images WHERE url = ?", [$path])['c'] ?? 0); } catch (\Throwable) {}
            }
            if ($productUses === 0 && $imageUses === 0) {
                $file = PUBLIC_PATH . $path;
                $uploadsRoot = realpath(PUBLIC_PATH . '/uploads/products') ?: (PUBLIC_PATH . '/uploads/products');
                $realFile = realpath($file);
                if ($realFile && str_starts_with($realFile, $uploadsRoot) && is_file($realFile)) {
                    @unlink($realFile);
                }
            }
        }

        return ['ok' => true, 'images' => $adminProductImages($productId), 'deleted_primary' => $wasPrimary];
    };


    // ── Product Reviews Moderation ────────────────────────────
    if ($uri === '/admin/api/reviews' && $method === 'GET') {
        $status = trim((string)($_GET['status'] ?? 'all'));
        $search = trim((string)($_GET['search'] ?? ''));
        json(['ok' => true, 'reviews' => \Reviews\ProductReview::adminList($status, $search, 200)]);
    }

    if (preg_match('#^/admin/api/reviews/(\d+)/(approve|reject|pending)$#', $uri, $m) && $method === 'POST') {
        $admin = \Auth\Auth::admin();
        $status = $m[2] === 'approve' ? 'approved' : ($m[2] === 'reject' ? 'rejected' : 'pending');
        json(\Reviews\ProductReview::moderate((int)$m[1], $status, (int)($admin['id'] ?? 0), trim((string)($body['note'] ?? ''))));
    }

    if (preg_match('#^/admin/api/reviews/(\d+)/feature$#', $uri, $m) && $method === 'POST') {
        json(\Reviews\ProductReview::setFeatured((int)$m[1], !empty($body['featured'])));
    }

    if (preg_match('#^/admin/api/reviews/(\d+)$#', $uri, $m) && $method === 'DELETE') {
        json(\Reviews\ProductReview::delete((int)$m[1]));
    }

    if ($uri === '/admin/api/dashboard' && $method === 'GET') {
        $hasSeen = $ensureOrderSeenColumn();
        $newOrderWhere = "status='new_order'";
        $statRows = [
            'total_orders'      => Database::row("SELECT COUNT(*) as c FROM orders")['c'] ?? 0,
            'new_orders'        => Database::row("SELECT COUNT(*) as c FROM orders WHERE $newOrderWhere")['c'] ?? 0,
            'total_revenue'     => Database::row("SELECT COALESCE(SUM(total_amount),0) as r FROM orders WHERE payment_status='paid'")['r'] ?? 0,
            'today_revenue'     => Database::row("SELECT COALESCE(SUM(total_amount),0) as r FROM orders WHERE DATE(created_at)=CURDATE() AND payment_status='paid'")['r'] ?? 0,
            'today_orders'      => Database::row("SELECT COUNT(*) as c FROM orders WHERE DATE(created_at)=CURDATE()")['c'] ?? 0,
            'pending_orders'    => Database::row("SELECT COUNT(*) as c FROM orders WHERE status IN ('new_order','received','whatsapp_pending')")['c'] ?? 0,
            'production_orders' => Database::row("SELECT COUNT(*) as c FROM orders WHERE status IN ('design_approved','other_process','processing','printing')")['c'] ?? 0,
            'ready_orders'      => Database::row("SELECT COUNT(*) as c FROM orders WHERE status='ready'")['c'] ?? 0,
            'delivered_orders'  => Database::row("SELECT COUNT(*) as c FROM orders WHERE status='delivered'")['c'] ?? 0,
            'pending_payments'  => Database::row("SELECT COALESCE(SUM(total_amount),0) as r FROM orders WHERE payment_status IS NULL OR payment_status <> 'paid'")['r'] ?? 0,
            'month_revenue'     => Database::row("SELECT COALESCE(SUM(total_amount),0) as r FROM orders WHERE created_at >= DATE_FORMAT(CURDATE(),'%Y-%m-01') AND payment_status='paid'")['r'] ?? 0,
            'avg_order_value'   => Database::row("SELECT COALESCE(AVG(total_amount),0) as a FROM orders WHERE payment_status='paid'")['a'] ?? 0,
            'total_customers'   => Database::row("SELECT COUNT(*) as c FROM users")['c'] ?? 0,
        ];
        $trendPct = static function ($current, $previous): ?float {
            $current = (float)$current;
            $previous = (float)$previous;
            if ($previous <= 0) {
                return $current > 0 ? 100.0 : 0.0;
            }
            return round((($current - $previous) / $previous) * 100, 2);
        };
        $comparisonRows = [
            'new_orders' => [
                'previous' => Database::row("SELECT COUNT(*) as c FROM orders WHERE status='new_order' AND DATE(created_at)=DATE_SUB(CURDATE(), INTERVAL 1 DAY)")['c'] ?? 0,
                'label' => 'vs yesterday',
            ],
            'pending_orders' => [
                'previous' => Database::row("SELECT COUNT(*) as c FROM orders WHERE status IN ('new_order','received','whatsapp_pending') AND DATE(created_at)=DATE_SUB(CURDATE(), INTERVAL 1 DAY)")['c'] ?? 0,
                'label' => 'vs yesterday',
            ],
            'production_orders' => [
                'previous' => Database::row("SELECT COUNT(*) as c FROM orders WHERE status IN ('design_approved','other_process','processing','printing') AND DATE(created_at)=DATE_SUB(CURDATE(), INTERVAL 1 DAY)")['c'] ?? 0,
                'label' => 'vs yesterday',
            ],
            'ready_orders' => [
                'previous' => Database::row("SELECT COUNT(*) as c FROM orders WHERE status='ready' AND DATE(created_at)=DATE_SUB(CURDATE(), INTERVAL 1 DAY)")['c'] ?? 0,
                'label' => 'vs yesterday',
            ],
            'delivered_orders' => [
                'previous' => Database::row("SELECT COUNT(*) as c FROM orders WHERE status='delivered' AND DATE(created_at)=DATE_SUB(CURDATE(), INTERVAL 1 DAY)")['c'] ?? 0,
                'label' => 'vs yesterday',
            ],
            'today_revenue' => [
                'previous' => Database::row("SELECT COALESCE(SUM(total_amount),0) as r FROM orders WHERE DATE(created_at)=DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND payment_status='paid'")['r'] ?? 0,
                'label' => 'vs yesterday',
            ],
            'month_revenue' => [
                'previous' => Database::row("SELECT COALESCE(SUM(total_amount),0) as r FROM orders WHERE created_at >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH),'%Y-%m-01') AND created_at < DATE_FORMAT(CURDATE(),'%Y-%m-01') AND payment_status='paid'")['r'] ?? 0,
                'label' => 'vs last month',
            ],
            'avg_order_value' => [
                'previous' => Database::row("SELECT COALESCE(AVG(total_amount),0) as a FROM orders WHERE created_at >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH),'%Y-%m-01') AND created_at < DATE_FORMAT(CURDATE(),'%Y-%m-01') AND payment_status='paid'")['a'] ?? 0,
                'label' => 'vs last month',
            ],
            'total_customers' => [
                'previous' => Database::row("SELECT COUNT(*) as c FROM users WHERE created_at < DATE_FORMAT(CURDATE(),'%Y-%m-01')")['c'] ?? 0,
                'label' => 'vs last month',
            ],
        ];
        $stats = [];
        foreach ($statRows as $key => $value) {
            $stats[$key] = $value;
            if (isset($comparisonRows[$key])) {
                $stats[$key . '_trend'] = $trendPct($value, $comparisonRows[$key]['previous']);
                $stats[$key . '_trend_label'] = $comparisonRows[$key]['label'];
            }
        }
        $queue = [
            'new_order' => (int)(Database::row("SELECT COUNT(*) as c FROM orders WHERE status='new_order'")['c'] ?? 0),
            'received' => (int)(Database::row("SELECT COUNT(*) as c FROM orders WHERE status='received'")['c'] ?? 0),
            'design_approved' => (int)(Database::row("SELECT COUNT(*) as c FROM orders WHERE status='design_approved'")['c'] ?? 0),
            'printing' => (int)(Database::row("SELECT COUNT(*) as c FROM orders WHERE status='printing'")['c'] ?? 0),
            'other_process' => (int)(Database::row("SELECT COUNT(*) as c FROM orders WHERE status IN ('other_process','processing')")['c'] ?? 0),
            'ready' => (int)(Database::row("SELECT COUNT(*) as c FROM orders WHERE status='ready'")['c'] ?? 0),
            'delivered' => (int)(Database::row("SELECT COUNT(*) as c FROM orders WHERE status='delivered'")['c'] ?? 0),
        ];
        $byStatus    = Database::rows("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
        $monthly     = Database::rows("SELECT DATE_FORMAT(created_at,'%b %Y') as month, SUM(total_amount) as revenue, COUNT(*) as orders FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY YEAR(created_at), MONTH(created_at) ORDER BY created_at ASC");
        $topProducts = Database::rows("SELECT product_name, COUNT(*) as count, SUM(total_price) as revenue FROM order_items GROUP BY product_name ORDER BY count DESC LIMIT 8");
        $recentOrders= Database::rows("SELECT o.*, COUNT(oi.id) as item_count, SUBSTRING_INDEX(GROUP_CONCAT(oi.product_name ORDER BY oi.id SEPARATOR ', '), ',', 1) as product_summary FROM orders o LEFT JOIN order_items oi ON oi.order_id=o.id GROUP BY o.id ORDER BY o.created_at DESC LIMIT 10");
        $recentNewOrders = Database::rows("SELECT o.*, COUNT(oi.id) as item_count, SUBSTRING_INDEX(GROUP_CONCAT(oi.product_name ORDER BY oi.id SEPARATOR ', '), ',', 1) as product_summary FROM orders o LEFT JOIN order_items oi ON oi.order_id=o.id WHERE $newOrderWhere GROUP BY o.id ORDER BY o.created_at DESC LIMIT 6");
        json(['ok'=>true,'stats'=>$stats,'queue'=>$queue,'by_status'=>$byStatus,'monthly'=>$monthly,'top_products'=>$topProducts,'recent_orders'=>$recentOrders,'recent_new_orders'=>$recentNewOrders,'seen_supported'=>$hasSeen]);
    }

    if ($uri === '/admin/api/order-notifications' && $method === 'GET') {
        $hasSeen = $ensureOrderSeenColumn();
        $newOrderWhere = "status='new_order'";
        $count = (int)(Database::row("SELECT COUNT(*) AS c FROM orders WHERE $newOrderWhere")['c'] ?? 0);
        $orders = Database::rows("SELECT id, order_id, customer_name, total_amount, status, created_at FROM orders WHERE $newOrderWhere ORDER BY created_at DESC LIMIT 5");
        json(['ok'=>true,'count'=>$count,'orders'=>$orders,'seen_supported'=>$hasSeen]);
    }

    if (preg_match('#^/admin/api/orders/(\d+)/seen$#', $uri, $m) && $method === 'POST') {
        if (!$ensureOrderSeenColumn()) json(['ok'=>false,'msg'=>'Seen tracking is unavailable. Run database migration.'], 500);
        Database::query("UPDATE orders SET is_seen = 1 WHERE id = ?", [(int)$m[1]]);
        json(['ok'=>true]);
    }

    if ($uri === '/admin/api/orders' && $method === 'GET') {
        $status = trim($_GET['status'] ?? '');
        $q      = trim($_GET['q'] ?? '');
        $where = [];
        $params = [];
        if ($status && $status !== 'all') {
            if ($status === 'other_process') {
                $where[] = "o.status IN ('other_process','processing')";
            } else {
                $where[] = 'o.status = ?';
                $params[] = $status;
            }
        }
        if ($q) {
            $where[] = '(o.order_id LIKE ? OR o.customer_name LIKE ? OR o.customer_phone LIKE ? OR o.customer_email LIKE ?)';
            $like = '%' . $q . '%';
            array_push($params, $like, $like, $like, $like);
        }
        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
        $orders = Database::rows("SELECT o.*, COUNT(oi.id) as item_count FROM orders o LEFT JOIN order_items oi ON oi.order_id=o.id $whereSql GROUP BY o.id ORDER BY o.created_at DESC", $params);
        json(['ok'=>true,'orders'=>$orders]);
    }

    if (preg_match('#^/admin/api/orders/(\d+)$#', $uri, $m) && $method === 'GET') {
        json(['ok'=>true,'order'=>\Orders\OrderManager::getOrder((int)$m[1])]);
    }

    if (preg_match('#^/admin/api/orders/(\d+)/status$#', $uri, $m) && $method === 'POST') {
        $ok = \Orders\OrderManager::updateStatus((int)$m[1], $body['status'] ?? '', $body['note'] ?? '');
        json(['ok'=>$ok]);
    }

    if (preg_match('#^/admin/api/orders/(\d+)/shipping$#', $uri, $m) && $method === 'POST') {
        try {
            Database::query(
                "UPDATE orders SET shipping_provider=?, tracking_code=?, shipping_status=?, shipping_notes=?, updated_at=NOW() WHERE id=?",
                [
                    trim((string)($body['shipping_provider'] ?? '')),
                    trim((string)($body['tracking_code'] ?? '')),
                    trim((string)($body['shipping_status'] ?? '')),
                    trim((string)($body['shipping_notes'] ?? '')),
                    (int)$m[1],
                ]
            );
            \Orders\AdminAudit::log('order_shipping_update', 'Order #' . $m[1] . ' shipping updated');
            json(['ok'=>true]);
        } catch (\Throwable $e) {
            json(['ok'=>false,'msg'=>'Shipping columns missing. Apply SQL migration first.'], 500);
        }
    }

    if ($uri === '/admin/api/product-filters' && $method === 'GET') {
        json(['ok' => true, 'filters' => \Catalog\ProductCatalog::filterOptions()]);
    }

    if ($uri === '/admin/api/products' && $method === 'GET') {
        try {
            json(['ok'=>true,'products'=>\Catalog\ProductCatalog::all(false)]);
        } catch (\Throwable $e) {
            error_log('Admin products list failed: ' . $e->getMessage());
            json(['ok'=>false,'msg'=>'Could not load products. Check DB schema and logs.','products'=>[]], 500);
        }
    }
    if ($uri === '/admin/api/products' && $method === 'POST') {
        if (!\Auth\Auth::isSuperAdmin()) $body['is_active'] = 0;
        $result = \Catalog\ProductCatalog::upsert($body);
        if (!empty($result['ok']) && !empty($result['id'])) \Approvals\ContentApprovalManager::applySaveState('products', (int)$result['id']);
        json($result);
    }
    if (preg_match('#^/admin/api/products/(\d+)$#', $uri, $m) && $method === 'GET') {
        $p = \Catalog\ProductCatalog::byId((int)$m[1]);
        json($p ? ['ok'=>true,'product'=>$p] : ['ok'=>false,'msg'=>'Not found'], $p ? 200 : 404);
    }
    if (preg_match('#^/admin/api/products/(\d+)$#', $uri, $m) && $method === 'PUT') {
        if (!\Auth\Auth::isSuperAdmin()) $body['is_active'] = 0;
        $result = \Catalog\ProductCatalog::upsert($body, (int)$m[1]);
        if (!empty($result['ok'])) \Approvals\ContentApprovalManager::applySaveState('products', (int)$m[1]);
        json($result);
    }
    if (preg_match('#^/admin/api/products/(\d+)/toggle$#', $uri, $m) && $method === 'POST') {
        \Approvals\ContentApprovalManager::requireSuperAdmin();
        Database::query("UPDATE products SET is_active = NOT is_active WHERE id=?", [$m[1]]);
        json(['ok'=>true]);
    }
    if (preg_match('#^/admin/api/products/(\d+)$#', $uri, $m) && $method === 'DELETE') {
        \Approvals\ContentApprovalManager::requireSuperAdmin();
        Database::query("DELETE FROM products WHERE id=?", [$m[1]]);
        json(['ok'=>true]);
    }

    if (preg_match('#^/admin/api/products/(\d+)/image-path$#', $uri, $m) && $method === 'DELETE') {
        $pid = (int)$m[1];
        try {
            $product = Database::row("SELECT id, image_path FROM products WHERE id = ? LIMIT 1", [$pid]);
        } catch (\Throwable) {
            json(['ok'=>false,'msg'=>'This product schema does not support a legacy main image field'], 422);
        }
        if (!$product) json(['ok'=>false,'msg'=>'Product not found'], 404);

        $path = (string)($product['image_path'] ?? '');
        try { Database::query("UPDATE products SET image_path = NULL WHERE id = ?", [$pid]); } catch (\Throwable) {}

        if ($path !== '' && str_starts_with($path, '/uploads/products/')) {
            $productUses = 0;
            $imageUses = 0;
            try { $productUses = (int)(Database::row("SELECT COUNT(*) c FROM products WHERE image_path = ?", [$path])['c'] ?? 0); } catch (\Throwable) {}
            try {
                $imageUses = (int)(Database::row(
                    "SELECT COUNT(*) c FROM product_images WHERE COALESCE(image_path, url) = ?",
                    [$path]
                )['c'] ?? 0);
            } catch (\Throwable) {
                try { $imageUses = (int)(Database::row("SELECT COUNT(*) c FROM product_images WHERE url = ?", [$path])['c'] ?? 0); } catch (\Throwable) {}
            }
            if ($productUses === 0 && $imageUses === 0) {
                $file = PUBLIC_PATH . $path;
                $uploadsRoot = realpath(PUBLIC_PATH . '/uploads/products') ?: (PUBLIC_PATH . '/uploads/products');
                $realFile = realpath($file);
                if ($realFile && str_starts_with($realFile, $uploadsRoot) && is_file($realFile)) {
                    @unlink($realFile);
                }
            }
        }

        json(['ok'=>true,'images'=>$adminProductImages($pid)]);
    }

    if (preg_match('#^/admin/api/products/(\d+)/tiers$#', $uri, $m) && $method === 'GET') {
        try {
            $tiers = Database::rows("SELECT id, quantity, price FROM product_quantity_tiers WHERE product_id=? ORDER BY quantity ASC", [(int)$m[1]]);
        } catch (\Throwable) {
            $tiers = [];
        }
        json(['ok'=>true,'tiers'=>$tiers]);
    }
    if (preg_match('#^/admin/api/products/(\d+)/tiers$#', $uri, $m) && $method === 'POST') {
        $tiers = $body['tiers'] ?? [];
        if (!is_array($tiers)) json(['ok'=>false,'msg'=>'Invalid tiers']);

        $seen = [];
        usort($tiers, fn($a,$b) => ((int)($a['quantity']??0)) <=> ((int)($b['quantity']??0)));
        foreach ($tiers as $t) {
            $q = (int)($t['quantity'] ?? 0);
            $pr = (float)($t['price'] ?? 0);
            if ($q <= 0 || $pr <= 0) json(['ok'=>false,'msg'=>'Quantity and price are required']);
            if (isset($seen[$q])) json(['ok'=>false,'msg'=>'Duplicate quantity: ' . $q]);
            $seen[$q] = true;
        }

        Database::query("DELETE FROM product_quantity_tiers WHERE product_id=?", [(int)$m[1]]);
        foreach ($tiers as $t) {
            Database::insert("INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at) VALUES (?,?,?,NOW())", [(int)$m[1], (int)$t['quantity'], (float)$t['price']]);
        }
        json(['ok'=>true]);
    }

    if (preg_match('#^/admin/api/products/(\d+)/image-upload$#', $uri, $m) && $method === 'POST') {
        if (empty($_FILES['image']) || !is_uploaded_file($_FILES['image']['tmp_name'])) {
            json(['ok'=>false,'msg'=>'Image file is required'], 400);
        }
        $pid = (int)$m[1];
        $isPrimary = (int)($_POST['is_primary'] ?? 0) === 1;

        $file = $_FILES['image'];
        if ((int)$file['size'] <= 0) json(['ok'=>false,'msg'=>'Empty upload'], 400);
        if ((int)$file['size'] > 5 * 1024 * 1024) json(['ok'=>false,'msg'=>'Max file size is 5MB'], 400);

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExt = ['jpg','jpeg','png','webp'];
        if (!in_array($ext, $allowedExt, true)) json(['ok'=>false,'msg'=>'Only jpg, png, webp allowed'], 400);

        $mime = mime_content_type($file['tmp_name']) ?: '';
        $allowedMime = ['image/jpeg','image/png','image/webp'];
        if (!in_array($mime, $allowedMime, true)) json(['ok'=>false,'msg'=>'Invalid image type'], 400);

        $dir = PUBLIC_PATH . '/uploads/products/';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        $name = 'prod_' . $pid . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $target = $dir . $name;
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            json(['ok'=>false,'msg'=>'Upload failed'], 500);
        }

        $publicPath = '/uploads/products/' . $name;

        try {
            if ($isPrimary) Database::query("UPDATE product_images SET is_primary=0 WHERE product_id=?", [$pid]);
            $imageId = Database::insert(
                "INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order) VALUES (?,?,?,?,?,?)",
                [$pid, $publicPath, $publicPath, 'Product image', $isPrimary ? 1 : 0, (int)($_POST['sort_order'] ?? 0)]
            );
        } catch (\Throwable) {
            if ($isPrimary) Database::query("UPDATE product_images SET is_primary=0 WHERE product_id=?", [$pid]);
            $imageId = Database::insert(
                "INSERT INTO product_images (product_id, url, alt_text, is_primary, sort_order) VALUES (?,?,?,?,?)",
                [$pid, $publicPath, 'Product image', $isPrimary ? 1 : 0, (int)($_POST['sort_order'] ?? 0)]
            );
        }

        if ($isPrimary) {
            try { Database::query("UPDATE products SET image_path=? WHERE id=?", [$publicPath, $pid]); } catch (\Throwable) {}
        }

        json(['ok'=>true,'path'=>$publicPath,'image_id'=>$imageId]);
    }

    if (preg_match('#^/admin/api/products/(\d+)/images-upload$#', $uri, $m) && $method === 'POST') {
        $files = $_FILES['images'] ?? null;
        if (!$files || !is_array($files['tmp_name'] ?? null)) json(['ok'=>false,'msg'=>'No files uploaded'], 400);
        $uploaded = [];
        $pid = (int)$m[1];
        foreach ($files['tmp_name'] as $i => $tmp) {
            if (!is_uploaded_file($tmp)) continue;
            $_FILES['image'] = [
                'name' => $files['name'][$i] ?? ('image_' . $i),
                'type' => $files['type'][$i] ?? '',
                'tmp_name' => $tmp,
                'error' => $files['error'][$i] ?? 0,
                'size' => $files['size'][$i] ?? 0,
            ];
            $_POST['is_primary'] = (string)(empty($uploaded) ? 1 : 0);
            $_POST['sort_order'] = (string)$i;
            // Reuse single upload route by internal call expectations.
            $file = $_FILES['image'];
            if ((int)$file['size'] <= 0) continue;
            if ((int)$file['size'] > 5 * 1024 * 1024) continue;
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) continue;
            $mime = mime_content_type($file['tmp_name']) ?: '';
            if (!in_array($mime, ['image/jpeg','image/png','image/webp'], true)) continue;
            $dir = PUBLIC_PATH . '/uploads/products/';
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            $name = 'prod_' . $pid . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            $target = $dir . $name;
            if (!move_uploaded_file($file['tmp_name'], $target)) continue;
            $publicPath = '/uploads/products/' . $name;
            try {
                if (empty($uploaded)) Database::query("UPDATE product_images SET is_primary=0 WHERE product_id=?", [$pid]);
                $imageId = Database::insert("INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order) VALUES (?,?,?,?,?,?)", [$pid, $publicPath, $publicPath, 'Product image', empty($uploaded)?1:0, $i]);
            } catch (\Throwable) {
                if (empty($uploaded)) Database::query("UPDATE product_images SET is_primary=0 WHERE product_id=?", [$pid]);
                $imageId = Database::insert("INSERT INTO product_images (product_id, url, alt_text, is_primary, sort_order) VALUES (?,?,?,?,?)", [$pid, $publicPath, 'Product image', empty($uploaded)?1:0, $i]);
            }
            if (empty($uploaded)) { try { Database::query("UPDATE products SET image_path=? WHERE id=?", [$publicPath, $pid]); } catch (\Throwable) {} }
            $uploaded[] = ['id'=>$imageId,'path'=>$publicPath];
        }
        if (!$uploaded) json(['ok'=>false,'msg'=>'No valid images were uploaded'], 400);
        json(['ok'=>true,'images'=>$uploaded]);
    }


    if (preg_match('#^/admin/api/products/(\d+)/images$#', $uri, $m) && $method === 'POST') {
        $pid = (int)$m[1];
        try {
            $id = Database::insert("INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order) VALUES (?,?,?,?,?,?)",
                [$pid, $body['image_path'] ?? $body['url'] ?? '', $body['image_path'] ?? $body['url'] ?? '', $body['alt_text']??'', $body['is_primary']??0, $body['sort_order']??0]);
        } catch (\Throwable) {
            $id = Database::insert("INSERT INTO product_images (product_id, url, alt_text, is_primary, sort_order) VALUES (?,?,?,?,?)",
                [$pid, $body['url'] ?? $body['image_path'] ?? '', $body['alt_text']??'', $body['is_primary']??0, $body['sort_order']??0]);
        }
        if (!empty($body['is_primary'])) {
            Database::query("UPDATE product_images SET is_primary=0 WHERE product_id=? AND id!=?", [$pid,$id]);
            $primaryPath = (string)($body['image_path'] ?? $body['url'] ?? '');
            try { Database::query("UPDATE products SET image_path=? WHERE id=?", [$primaryPath, $pid]); } catch (\Throwable) {}
        }
        json(['ok'=>true,'id'=>$id]);
    }
    if (preg_match('#^/admin/api/products/(\d+)/images/(\d+)$#', $uri, $m) && $method === 'DELETE') {
        $result = $deleteProductImage((int)$m[1], (int)$m[2]);
        json($result, $result['ok'] ? 200 : 404);
    }
    if (preg_match('#^/admin/api/products/(\d+)/images$#', $uri, $m) && $method === 'DELETE') {
        $pid = (int)$m[1];
        $images = $adminProductImages($pid);
        foreach ($images as $image) {
            if (!empty($image['id'])) $deleteProductImage($pid, (int)$image['id']);
        }
        json(['ok'=>true,'images'=>[]]);
    }
    if (preg_match('#^/admin/api/images/(\d+)$#', $uri, $m) && $method === 'DELETE') {
        $image = Database::row("SELECT product_id FROM product_images WHERE id = ? LIMIT 1", [(int)$m[1]]);
        if (!$image) json(['ok'=>false,'msg'=>'Image not found'], 404);
        $result = $deleteProductImage((int)$image['product_id'], (int)$m[1]);
        json($result, $result['ok'] ? 200 : 404);
    }

    if ($uri === '/admin/api/qualities' && $method === 'GET') {
        json(['ok'=>true,'qualities'=>Database::rows("SELECT * FROM qualities ORDER BY sort_order ASC")]);
    }
    if ($uri === '/admin/api/qualities' && $method === 'POST') {
        $id = Database::insert("INSERT INTO qualities (name, description, sort_order, is_active) VALUES (?,?,?,1)",
            [$body['name'], $body['description']??'', $body['sort_order']??0]);
        json(['ok'=>true,'id'=>$id]);
    }
    if (preg_match('#^/admin/api/qualities/(\d+)$#', $uri, $m) && $method === 'DELETE') {
        Database::query("DELETE FROM qualities WHERE id=?", [$m[1]]);
        json(['ok'=>true]);
    }
    if (preg_match('#^/admin/api/qualities/(\d+)/slabs$#', $uri, $m) && $method === 'POST') {
        $qid = (int)$m[1]; $pid = (int)($body['product_id']??0);
        foreach ($body['slabs']??[] as $qty => $price) {
            if ((float)$price > 0) {
                Database::query("INSERT INTO quantity_slabs (product_id, quality_id, quantity, price) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE price=VALUES(price)",
                    [$pid, $qid, (int)$qty, (float)$price]);
            }
        }
        json(['ok'=>true]);
    }

    if ($uri === '/admin/api/attribute-groups' && $method === 'GET') {
        $groups = Database::rows("SELECT * FROM attribute_groups ORDER BY sort_order");
        foreach ($groups as &$g) $g['options'] = Database::rows("SELECT * FROM attribute_options WHERE group_id=? ORDER BY sort_order", [$g['id']]);
        json(['ok'=>true,'groups'=>$groups]);
    }
    if ($uri === '/admin/api/attribute-groups' && $method === 'POST') {
        $gid = Database::insert("INSERT INTO attribute_groups (name, sort_order) VALUES (?,?)", [$body['name'], $body['sort_order']??0]);
        foreach ($body['options']??[] as $i => $opt) {
            if (!empty($opt['label'])) Database::insert("INSERT INTO attribute_options (group_id, label, price_addon, sort_order) VALUES (?,?,?,?)",
                [$gid, $opt['label'], (float)($opt['price_addon']??0), $i]);
        }
        json(['ok'=>true,'id'=>$gid]);
    }
    if (preg_match('#^/admin/api/attribute-groups/(\d+)$#', $uri, $m) && $method === 'DELETE') {
        Database::query("DELETE FROM attribute_groups WHERE id=?", [$m[1]]);
        json(['ok'=>true]);
    }
    if (preg_match('#^/admin/api/products/(\d+)/attribute-groups$#', $uri, $m) && $method === 'POST') {
        Database::query("INSERT IGNORE INTO product_attribute_groups (product_id, group_id, sort_order) VALUES (?,?,?)",
            [$m[1], $body['group_id'], $body['sort_order']??0]);
        json(['ok'=>true]);
    }
    if (preg_match('#^/admin/api/products/(\d+)/attribute-groups/(\d+)$#', $uri, $m) && $method === 'DELETE') {
        Database::query("DELETE FROM product_attribute_groups WHERE product_id=? AND group_id=?", [$m[1],$m[2]]);
        json(['ok'=>true]);
    }

    if ($uri === '/admin/api/coupons' && $method === 'GET') {
        try {
            $coupons = Database::rows(
                "SELECT c.*, cat.name AS category_name
                 FROM coupons c
                 LEFT JOIN categories cat ON cat.id = c.category_id
                 ORDER BY c.created_at DESC"
            );
        } catch (\Throwable) {
            $coupons = Database::rows("SELECT * FROM coupons ORDER BY created_at DESC");
            foreach ($coupons as &$c) $c['category_name'] = null;
        }
        json(['ok'=>true,'coupons'=>$coupons]);
    }
    if ($uri === '/admin/api/coupons' && $method === 'POST') {
        $code = strtoupper(trim($body['code']??''));
        if (!$code) json(['ok'=>false,'msg'=>'Code required']);
        if (Database::row("SELECT id FROM coupons WHERE code=?",[$code])) json(['ok'=>false,'msg'=>'Code exists']);
        $scopeType = ($body['scope_type'] ?? 'all') === 'category' ? 'category' : 'all';
        $categoryId = (int)($body['category_id'] ?? 0);
        if ($scopeType === 'category' && $categoryId <= 0) {
            json(['ok'=>false,'msg'=>'Please select a category for category-specific coupon.'], 422);
        }
        $active = \Auth\Auth::isSuperAdmin() ? 1 : 0;
        try {
            $id = Database::insert(
                "INSERT INTO coupons (code,description,discount_type,discount_value,min_order_amount,max_uses,valid_from,valid_until,scope_type,category_id,is_active)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?)",
                [
                    $code, $body['description'] ?? '', $body['discount_type'] ?? 'percent',
                    (float)($body['discount_value'] ?? 0), (float)($body['min_order_amount'] ?? 0),
                    (int)($body['max_uses'] ?? 0), $body['valid_from'] ?: null, $body['valid_until'] ?: null,
                    $scopeType, $scopeType === 'category' ? $categoryId : null, $active,
                ]
            );
        } catch (\Throwable) {
            $id = Database::insert(
                "INSERT INTO coupons (code,description,discount_type,discount_value,min_order_amount,max_uses,valid_from,valid_until,is_active)
                 VALUES (?,?,?,?,?,?,?,?,?)",
                [
                    $code, $body['description'] ?? '', $body['discount_type'] ?? 'percent',
                    (float)($body['discount_value'] ?? 0), (float)($body['min_order_amount'] ?? 0),
                    (int)($body['max_uses'] ?? 0), $body['valid_from'] ?: null, $body['valid_until'] ?: null, $active,
                ]
            );
        }
        \Orders\AdminAudit::log('coupon_created',"Coupon: $code");
        \Approvals\ContentApprovalManager::applySaveState('coupons', (int)$id);
        json(['ok'=>true,'id'=>$id]);
    }
    if (preg_match('#^/admin/api/coupons/(\d+)$#', $uri, $m) && $method === 'PUT') {
        $id = (int)$m[1];
        $code = strtoupper(trim($body['code']??''));
        if (!$code) json(['ok'=>false,'msg'=>'Code required']);
        if (Database::row("SELECT id FROM coupons WHERE code=? AND id<>?",[$code, $id])) json(['ok'=>false,'msg'=>'Code exists']);
        $scopeType = ($body['scope_type'] ?? 'all') === 'category' ? 'category' : 'all';
        $categoryId = (int)($body['category_id'] ?? 0);
        if ($scopeType === 'category' && $categoryId <= 0) {
            json(['ok'=>false,'msg'=>'Please select a category for category-specific coupon.'], 422);
        }
        try {
            Database::query(
                "UPDATE coupons
                 SET code=?, description=?, discount_type=?, discount_value=?, min_order_amount=?, max_uses=?, valid_from=?, valid_until=?, scope_type=?, category_id=?
                 WHERE id=?",
                [
                    $code, $body['description'] ?? '', $body['discount_type'] ?? 'percent',
                    (float)($body['discount_value'] ?? 0), (float)($body['min_order_amount'] ?? 0),
                    (int)($body['max_uses'] ?? 0), $body['valid_from'] ?: null, $body['valid_until'] ?: null,
                    $scopeType, $scopeType === 'category' ? $categoryId : null, $id,
                ]
            );
        } catch (\Throwable) {
            Database::query(
                "UPDATE coupons
                 SET code=?, description=?, discount_type=?, discount_value=?, min_order_amount=?, max_uses=?, valid_from=?, valid_until=?
                 WHERE id=?",
                [
                    $code, $body['description'] ?? '', $body['discount_type'] ?? 'percent',
                    (float)($body['discount_value'] ?? 0), (float)($body['min_order_amount'] ?? 0),
                    (int)($body['max_uses'] ?? 0), $body['valid_from'] ?: null, $body['valid_until'] ?: null, $id,
                ]
            );
        }
        \Orders\AdminAudit::log('coupon_updated',"Coupon: $code");
        \Approvals\ContentApprovalManager::applySaveState('coupons', $id);
        json(['ok'=>true,'id'=>$id]);
    }
    if (preg_match('#^/admin/api/coupons/(\d+)/toggle$#', $uri, $m) && $method === 'POST') {
        \Approvals\ContentApprovalManager::requireSuperAdmin();
        Database::query("UPDATE coupons SET is_active=NOT is_active WHERE id=?",[$m[1]]);
        json(['ok'=>true]);
    }
    if (preg_match('#^/admin/api/coupons/(\d+)$#', $uri, $m) && $method === 'DELETE') {
        \Approvals\ContentApprovalManager::requireSuperAdmin();
        Database::query("DELETE FROM coupons WHERE id=?",[$m[1]]);
        json(['ok'=>true]);
    }

    if ($uri === '/admin/api/categories' && $method === 'GET') {
        json(['ok'=>true,'categories'=>\Catalog\ProductCatalog::categories()]);
    }
    if (preg_match('#^/admin/api/categories/(\d+)/next-product-code$#', $uri, $m) && $method === 'GET') {
        $categoryId = (int)$m[1];
        $editId = (int)($_GET['edit_id'] ?? 0);
        $code = \Catalog\ProductCatalog::nextProductCodePreview($categoryId, $editId > 0 ? $editId : null);
        json(['ok' => true, 'code' => $code]);
    }
    if ($uri === '/admin/api/categories/upload' && $method === 'POST') {
        if (empty($_FILES['image']) || !is_uploaded_file($_FILES['image']['tmp_name'])) {
            json(['ok'=>false,'msg'=>'Image file is required'], 400);
        }
        $file = $_FILES['image'];
        if ((int)$file['size'] <= 0) json(['ok'=>false,'msg'=>'Empty upload'], 400);
        if ((int)$file['size'] > 6 * 1024 * 1024) json(['ok'=>false,'msg'=>'Max file size is 6MB'], 400);
        $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) json(['ok'=>false,'msg'=>'Only jpg, png, webp allowed'], 400);
        $mime = mime_content_type($file['tmp_name']) ?: '';
        if (!in_array($mime, ['image/jpeg','image/png','image/webp'], true)) json(['ok'=>false,'msg'=>'Invalid image type'], 400);
        $dir = PUBLIC_PATH . '/uploads/categories/';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $name = 'category_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $target = $dir . $name;
        if (!move_uploaded_file($file['tmp_name'], $target)) json(['ok'=>false,'msg'=>'Upload failed'], 500);
        json(['ok'=>true,'path'=>'/uploads/categories/' . $name]);
    }
    if ($uri === '/admin/api/categories' && $method === 'POST') {
        $name = trim((string)($body['name'] ?? ''));
        if ($name === '') json(['ok'=>false,'msg'=>'Category name is required'], 422);
        $slugBase = trim((string)($body['slug'] ?? $name));
        $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $slugBase) ?? '');
        $slug = trim($slug, '-') ?: strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
        $prefix = strtoupper(trim((string)($body['code_prefix'] ?? '')));
        $prefix = preg_replace('/[^A-Z0-9]/', '', $prefix) ?: null;
        $icon = trim((string)($body['icon'] ?? '🖨️')) ?: '🖨️';
        $imagePath = trim((string)($body['image_path'] ?? '')) ?: null;
        $imageAlt = trim((string)($body['image_alt'] ?? '')) ?: ($name . ' category image');
        $sort = (int)($body['sort_order'] ?? 0);
        $active = isset($body['is_active']) ? (int)((int)$body['is_active'] > 0) : 1;
        if (!\Auth\Auth::isSuperAdmin()) $active = 0;
        try {
            $id = Database::insert(
                "INSERT INTO categories (name,slug,code_prefix,icon,image_path,image_alt,sort_order,is_active) VALUES (?,?,?,?,?,?,?,?)",
                [$name,$slug,$prefix,$icon,$imagePath,$imageAlt,$sort,$active]
            );
        } catch (\Throwable) {
            try {
                $id = Database::insert(
                    "INSERT INTO categories (name,slug,icon,image_path,image_alt,sort_order,is_active) VALUES (?,?,?,?,?,?,?)",
                    [$name,$slug,$icon,$imagePath,$imageAlt,$sort,$active]
                );
            } catch (\Throwable) {
                $id = Database::insert(
                    "INSERT INTO categories (name,slug,icon,sort_order,is_active) VALUES (?,?,?,?,?)",
                    [$name,$slug,$icon,$sort,$active]
                );
            }
        }
        \Orders\AdminAudit::log('category_created', "Category #{$id}: {$name}");
        \Approvals\ContentApprovalManager::applySaveState('categories', (int)$id);
        json(['ok'=>true,'id'=>$id]);
    }
    if (preg_match('#^/admin/api/categories/(\d+)$#', $uri, $m) && $method === 'PUT') {
        $id = (int)$m[1];
        $existing = Database::row("SELECT * FROM categories WHERE id=?", [$id]);
        if (!$existing) json(['ok'=>false,'msg'=>'Category not found'], 404);

        $name = trim((string)($body['name'] ?? $existing['name']));
        if ($name === '') json(['ok'=>false,'msg'=>'Category name is required'], 422);

        $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $body['slug'] ?? $name));
        $slug = trim((string)$slug, '-') ?: ('category-' . $id);
        $prefix = strtoupper(trim((string)($body['code_prefix'] ?? ($existing['code_prefix'] ?? ''))));
        $prefix = preg_replace('/[^A-Z0-9]/', '', $prefix) ?: null;
        $icon = trim((string)($body['icon'] ?? ($existing['icon'] ?? '🖨️'))) ?: '🖨️';
        $imagePath = trim((string)($body['image_path'] ?? ($existing['image_path'] ?? ''))) ?: null;
        $imageAlt = trim((string)($body['image_alt'] ?? ($existing['image_alt'] ?? ''))) ?: ($name . ' category image');
        $sort = (int)($body['sort_order'] ?? ($existing['sort_order'] ?? 0));
        $active = isset($body['is_active']) ? (int)((int)$body['is_active'] > 0) : (int)($existing['is_active'] ?? 1);
        if (!\Auth\Auth::isSuperAdmin()) $active = 0;

        try {
            Database::query(
                "UPDATE categories SET name=?, slug=?, code_prefix=?, icon=?, image_path=?, image_alt=?, sort_order=?, is_active=? WHERE id=?",
                [$name, $slug, $prefix, $icon, $imagePath, $imageAlt, $sort, $active, $id]
            );
        } catch (\Throwable) {
            try {
                Database::query(
                    "UPDATE categories SET name=?, slug=?, icon=?, image_path=?, image_alt=?, sort_order=?, is_active=? WHERE id=?",
                    [$name, $slug, $icon, $imagePath, $imageAlt, $sort, $active, $id]
                );
            } catch (\Throwable) {
                Database::query(
                    "UPDATE categories SET name=?, slug=?, icon=?, sort_order=?, is_active=? WHERE id=?",
                    [$name, $slug, $icon, $sort, $active, $id]
                );
            }
        }
        \Orders\AdminAudit::log('category_updated', "Category #{$id}: {$name}");
        \Approvals\ContentApprovalManager::applySaveState('categories', $id);
        json(['ok'=>true]);
    }
    if (preg_match('#^/admin/api/categories/(\d+)/toggle$#', $uri, $m) && $method === 'POST') {
        \Approvals\ContentApprovalManager::requireSuperAdmin();
        $id = (int)$m[1];
        Database::query("UPDATE categories SET is_active = CASE WHEN is_active=1 THEN 0 ELSE 1 END WHERE id=?", [$id]);
        \Orders\AdminAudit::log('category_toggled', "Category #{$id} status toggled");
        json(['ok'=>true]);
    }
    if (preg_match('#^/admin/api/categories/(\d+)$#', $uri, $m) && $method === 'DELETE') {
        \Approvals\ContentApprovalManager::requireSuperAdmin();
        $id = (int)$m[1];
        $cat = Database::row("SELECT id,name FROM categories WHERE id=?", [$id]);
        if (!$cat) json(['ok'=>false,'msg'=>'Category not found'], 404);
        $usage = (int)(Database::row("SELECT COUNT(*) c FROM products WHERE category_id=?", [$id])['c'] ?? 0);
        if ($usage > 0) {
            json(['ok'=>false,'msg'=>'Category is in use by products. Reassign products before deleting.'], 422);
        }
        try {
            Database::query("DELETE FROM categories WHERE id=?", [$id]);
        } catch (\Throwable) {
            json(['ok'=>false,'msg'=>'Could not delete category. It may be referenced elsewhere.'], 422);
        }
        \Orders\AdminAudit::log('category_deleted', "Category #{$id}: {$cat['name']}");
        json(['ok'=>true]);
    }

    if ($uri === '/admin/api/banners' && $method === 'GET') {
        try {
            $ensureHomeBannerClickColumns();
            $rows = Database::rows("SELECT * FROM home_banners ORDER BY sort_order ASC, id DESC");
            json(['ok'=>true,'banners'=>$rows]);
        } catch (\Throwable) {
            json(['ok'=>false,'msg'=>'home_banners table missing. Run SQL migration first.','banners'=>[]], 500);
        }
    }
    if ($uri === '/admin/api/banners' && $method === 'POST') {
        if (trim((string)($body['image_path'] ?? '')) === '') {
            json(['ok'=>false,'msg'=>'Banner image path is required'], 400);
        }
        $imageClickEnabled = (int)($body['image_click_enabled'] ?? 0) === 1 ? 1 : 0;
        $imageClickUrl = trim((string)($body['image_click_url'] ?? ''));
        if ($imageClickEnabled && $imageClickUrl === '') {
            json(['ok'=>false,'msg'=>'Image click URL is required when clickable image is enabled'], 400);
        }
        try {
            $ensureHomeBannerClickColumns();
            $id = Database::insert(
                "INSERT INTO home_banners (eyebrow,title,subtitle,image_path,image_alt,cta_primary_text,cta_primary_url,cta_secondary_text,cta_secondary_type,cta_secondary_url,image_click_enabled,image_click_url,sort_order,is_active,created_at,updated_at)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())",
                [
                    trim((string)($body['eyebrow'] ?? '')),
                    trim((string)($body['title'] ?? '')),
                    trim((string)($body['subtitle'] ?? '')),
                    trim((string)($body['image_path'] ?? '')),
                    trim((string)($body['image_alt'] ?? '')),
                    trim((string)($body['cta_primary_text'] ?? '')),
                    trim((string)($body['cta_primary_url'] ?? '')),
                    trim((string)($body['cta_secondary_text'] ?? '')),
                    trim((string)($body['cta_secondary_type'] ?? 'whatsapp')),
                    trim((string)($body['cta_secondary_url'] ?? '')),
                    $imageClickEnabled,
                    $imageClickUrl,
                    (int)($body['sort_order'] ?? 0),
                    (int)($body['is_active'] ?? 1),
                ]
            );
            json(['ok'=>true,'id'=>$id]);
        } catch (\Throwable $e) {
            json(['ok'=>false,'msg'=>'Could not create banner. Run migration first.'], 500);
        }
    }
    if (preg_match('#^/admin/api/banners/(\d+)$#', $uri, $m) && $method === 'PUT') {
        if (trim((string)($body['image_path'] ?? '')) === '') {
            json(['ok'=>false,'msg'=>'Banner image path is required'], 400);
        }
        $imageClickEnabled = (int)($body['image_click_enabled'] ?? 0) === 1 ? 1 : 0;
        $imageClickUrl = trim((string)($body['image_click_url'] ?? ''));
        if ($imageClickEnabled && $imageClickUrl === '') {
            json(['ok'=>false,'msg'=>'Image click URL is required when clickable image is enabled'], 400);
        }
        try {
            $ensureHomeBannerClickColumns();
            Database::query(
                "UPDATE home_banners
                 SET eyebrow=?, title=?, subtitle=?, image_path=?, image_alt=?, cta_primary_text=?, cta_primary_url=?, cta_secondary_text=?, cta_secondary_type=?, cta_secondary_url=?, image_click_enabled=?, image_click_url=?, sort_order=?, is_active=?, updated_at=NOW()
                 WHERE id=?",
                [
                    trim((string)($body['eyebrow'] ?? '')),
                    trim((string)($body['title'] ?? '')),
                    trim((string)($body['subtitle'] ?? '')),
                    trim((string)($body['image_path'] ?? '')),
                    trim((string)($body['image_alt'] ?? '')),
                    trim((string)($body['cta_primary_text'] ?? '')),
                    trim((string)($body['cta_primary_url'] ?? '')),
                    trim((string)($body['cta_secondary_text'] ?? '')),
                    trim((string)($body['cta_secondary_type'] ?? 'whatsapp')),
                    trim((string)($body['cta_secondary_url'] ?? '')),
                    $imageClickEnabled,
                    $imageClickUrl,
                    (int)($body['sort_order'] ?? 0),
                    (int)($body['is_active'] ?? 1),
                    (int)$m[1],
                ]
            );
            json(['ok'=>true]);
        } catch (\Throwable) {
            json(['ok'=>false,'msg'=>'Could not update banner'], 500);
        }
    }
    if (preg_match('#^/admin/api/banners/(\d+)$#', $uri, $m) && $method === 'DELETE') {
        try {
            Database::query("DELETE FROM home_banners WHERE id=?", [(int)$m[1]]);
            json(['ok'=>true]);
        } catch (\Throwable) {
            json(['ok'=>false,'msg'=>'Could not delete banner'], 500);
        }
    }
    if ($uri === '/admin/api/banners/reorder' && $method === 'POST') {
        foreach (($body['items'] ?? []) as $item) {
            Database::query("UPDATE home_banners SET sort_order=?, updated_at=NOW() WHERE id=?", [(int)($item['sort_order'] ?? 0), (int)($item['id'] ?? 0)]);
        }
        json(['ok'=>true]);
    }
    if ($uri === '/admin/api/banners/upload' && $method === 'POST') {
        if (empty($_FILES['image']) || !is_uploaded_file($_FILES['image']['tmp_name'])) {
            json(['ok'=>false,'msg'=>'Image file is required'], 400);
        }
        $file = $_FILES['image'];
        if ((int)$file['size'] <= 0) json(['ok'=>false,'msg'=>'Empty upload'], 400);
        if ((int)$file['size'] > 6 * 1024 * 1024) json(['ok'=>false,'msg'=>'Max file size is 6MB'], 400);
        $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) json(['ok'=>false,'msg'=>'Only jpg, png, webp allowed'], 400);
        $mime = mime_content_type($file['tmp_name']) ?: '';
        if (!in_array($mime, ['image/jpeg','image/png','image/webp'], true)) json(['ok'=>false,'msg'=>'Invalid image type'], 400);
        $dir = PUBLIC_PATH . '/uploads/banners/';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $name = 'banner_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $target = $dir . $name;
        if (!move_uploaded_file($file['tmp_name'], $target)) json(['ok'=>false,'msg'=>'Upload failed'], 500);
        json(['ok'=>true,'path'=>'/uploads/banners/' . $name]);
    }


    if ($uri === '/admin/api/deals' && $method === 'GET') {
        try {
            $rows = Database::rows("SELECT * FROM home_deals ORDER BY sort_order ASC, id DESC");
            json(['ok'=>true,'deals'=>$rows]);
        } catch (\Throwable) {
            json(['ok'=>false,'msg'=>'home_deals table missing. Run SQL migration first.','deals'=>[]], 500);
        }
    }
    if ($uri === '/admin/api/deals' && $method === 'POST') {
        $dealType = strtolower(trim((string)($body['deal_type'] ?? 'deal')));
        $title = trim((string)($body['title'] ?? ''));
        $imagePath = trim((string)($body['image_path'] ?? ''));
        $priceText = trim((string)($body['price_text'] ?? ''));
        $theme = strtolower(trim((string)($body['color_theme'] ?? 'green')));
        if (!in_array($theme, ['green','orange','purple'], true)) $theme = 'green';
        if (!in_array($dealType, ['deal','promo'], true)) json(['ok'=>false,'msg'=>'Invalid deal type'], 400);
        if ($title === '') json(['ok'=>false,'msg'=>'Deal title is required'], 400);
        if ($dealType === 'deal' && $imagePath === '') json(['ok'=>false,'msg'=>'Deal image is required'], 400);
        if ($dealType === 'deal' && $priceText === '') json(['ok'=>false,'msg'=>'Deal price is required'], 400);
        try {
            $id = Database::insert(
                "INSERT INTO home_deals (deal_type,title,highlight_text,subtitle,price_text,description,image_path,image_alt,cta_text,cta_url,color_theme,sort_order,is_active,created_at,updated_at)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())",
                [
                    $dealType,
                    $title,
                    trim((string)($body['highlight_text'] ?? '')),
                    trim((string)($body['subtitle'] ?? ($dealType === 'deal' ? 'Starting from' : ''))),
                    $priceText,
                    trim((string)($body['description'] ?? '')),
                    $imagePath,
                    trim((string)($body['image_alt'] ?? '')),
                    trim((string)($body['cta_text'] ?? '')) ?: ($dealType === 'promo' ? 'Get Offer' : 'Order Now'),
                    trim((string)($body['cta_url'] ?? '/categories')) ?: '/categories',
                    $theme,
                    (int)($body['sort_order'] ?? 0),
                    \Auth\Auth::isSuperAdmin() ? (int)($body['is_active'] ?? 1) : 0,
                ]
            );
            \Approvals\ContentApprovalManager::applySaveState('home_deals', (int)$id);
            json(['ok'=>true,'id'=>$id]);
        } catch (\Throwable) {
            json(['ok'=>false,'msg'=>'Could not create deal. Run migration first.'], 500);
        }
    }
    if (preg_match('#^/admin/api/deals/(\d+)$#', $uri, $m) && $method === 'PUT') {
        $dealType = strtolower(trim((string)($body['deal_type'] ?? 'deal')));
        $title = trim((string)($body['title'] ?? ''));
        $imagePath = trim((string)($body['image_path'] ?? ''));
        $priceText = trim((string)($body['price_text'] ?? ''));
        $theme = strtolower(trim((string)($body['color_theme'] ?? 'green')));
        if (!in_array($theme, ['green','orange','purple'], true)) $theme = 'green';
        if (!in_array($dealType, ['deal','promo'], true)) json(['ok'=>false,'msg'=>'Invalid deal type'], 400);
        if ($title === '') json(['ok'=>false,'msg'=>'Deal title is required'], 400);
        if ($dealType === 'deal' && $imagePath === '') json(['ok'=>false,'msg'=>'Deal image is required'], 400);
        if ($dealType === 'deal' && $priceText === '') json(['ok'=>false,'msg'=>'Deal price is required'], 400);
        try {
            Database::query(
                "UPDATE home_deals
                 SET deal_type=?, title=?, highlight_text=?, subtitle=?, price_text=?, description=?, image_path=?, image_alt=?, cta_text=?, cta_url=?, color_theme=?, sort_order=?, is_active=?, updated_at=NOW()
                 WHERE id=?",
                [
                    $dealType,
                    $title,
                    trim((string)($body['highlight_text'] ?? '')),
                    trim((string)($body['subtitle'] ?? ($dealType === 'deal' ? 'Starting from' : ''))),
                    $priceText,
                    trim((string)($body['description'] ?? '')),
                    $imagePath,
                    trim((string)($body['image_alt'] ?? '')),
                    trim((string)($body['cta_text'] ?? '')) ?: ($dealType === 'promo' ? 'Get Offer' : 'Order Now'),
                    trim((string)($body['cta_url'] ?? '/categories')) ?: '/categories',
                    $theme,
                    (int)($body['sort_order'] ?? 0),
                    \Auth\Auth::isSuperAdmin() ? (int)($body['is_active'] ?? 1) : 0,
                    (int)$m[1],
                ]
            );
            \Approvals\ContentApprovalManager::applySaveState('home_deals', (int)$m[1]);
            json(['ok'=>true]);
        } catch (\Throwable) {
            json(['ok'=>false,'msg'=>'Could not update deal'], 500);
        }
    }
    if (preg_match('#^/admin/api/deals/(\d+)$#', $uri, $m) && $method === 'DELETE') {
        \Approvals\ContentApprovalManager::requireSuperAdmin();
        try {
            Database::query("DELETE FROM home_deals WHERE id=?", [(int)$m[1]]);
            json(['ok'=>true]);
        } catch (\Throwable) {
            json(['ok'=>false,'msg'=>'Could not delete deal'], 500);
        }
    }
    if ($uri === '/admin/api/deals/reorder' && $method === 'POST') {
        \Approvals\ContentApprovalManager::requireSuperAdmin();
        foreach (($body['items'] ?? []) as $item) {
            Database::query("UPDATE home_deals SET sort_order=?, updated_at=NOW() WHERE id=?", [(int)($item['sort_order'] ?? 0), (int)($item['id'] ?? 0)]);
        }
        json(['ok'=>true]);
    }
    if ($uri === '/admin/api/deals/upload' && $method === 'POST') {
        if (empty($_FILES['image']) || !is_uploaded_file($_FILES['image']['tmp_name'])) {
            json(['ok'=>false,'msg'=>'Image file is required'], 400);
        }
        $file = $_FILES['image'];
        if ((int)$file['size'] <= 0) json(['ok'=>false,'msg'=>'Empty upload'], 400);
        if ((int)$file['size'] > 6 * 1024 * 1024) json(['ok'=>false,'msg'=>'Max file size is 6MB'], 400);
        $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) json(['ok'=>false,'msg'=>'Only jpg, png, webp allowed'], 400);
        $mime = mime_content_type($file['tmp_name']) ?: '';
        if (!in_array($mime, ['image/jpeg','image/png','image/webp'], true)) json(['ok'=>false,'msg'=>'Invalid image type'], 400);
        $dir = PUBLIC_PATH . '/uploads/deals/';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $name = 'deal_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $target = $dir . $name;
        if (!move_uploaded_file($file['tmp_name'], $target)) json(['ok'=>false,'msg'=>'Upload failed'], 500);
        json(['ok'=>true,'path'=>'/uploads/deals/' . $name]);
    }


    if ($uri === '/admin/api/blogs' && $method === 'GET') {
        try {
            $rows = Database::rows("SELECT * FROM blogs ORDER BY sort_order ASC, published_at DESC, id DESC");
            json(['ok'=>true,'blogs'=>$rows]);
        } catch (\Throwable) {
            json(['ok'=>false,'msg'=>'blogs table missing. Run SQL migration first.','blogs'=>[]], 500);
        }
    }
    if (preg_match('#^/admin/api/blogs/(\d+)$#', $uri, $m) && $method === 'GET') {
        try {
            $blog = Database::row("SELECT * FROM blogs WHERE id=?", [(int)$m[1]]);
            if (!$blog) {
                json(['ok'=>false,'msg'=>'Blog not found'], 404);
            }
            json(['ok'=>true,'blog'=>$blog]);
        } catch (\Throwable) {
            json(['ok'=>false,'msg'=>'blogs table missing. Run SQL migration first.'], 500);
        }
    }
    if ($uri === '/admin/api/blogs' && $method === 'POST') {
        $title = trim((string)($body['title'] ?? ''));
        $content = $sanitizeBlogContent((string)($body['content'] ?? ''));
        if ($title === '') json(['ok'=>false,'msg'=>'Blog title is required'], 400);
        if ($content === '') json(['ok'=>false,'msg'=>'Blog content is required'], 400);
        $badgeTheme = strtolower(trim((string)($body['badge_theme'] ?? 'purple')));
        if (!in_array($badgeTheme, ['purple','orange','green'], true)) $badgeTheme = 'purple';
        try {
            $slug = $uniqueBlogSlug(trim((string)($body['slug'] ?? '')) ?: $title);
            $id = Database::insert(
                "INSERT INTO blogs (title,slug,excerpt,content,featured_image,image_alt,category,badge_theme,author_name,meta_title,meta_description,published_at,sort_order,is_featured,is_active,created_at,updated_at)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())",
                [
                    $title,
                    $slug,
                    trim((string)($body['excerpt'] ?? '')),
                    $content,
                    trim((string)($body['featured_image'] ?? '')),
                    trim((string)($body['image_alt'] ?? '')),
                    trim((string)($body['category'] ?? 'Print Tips')) ?: 'Print Tips',
                    $badgeTheme,
                    trim((string)($body['author_name'] ?? 'RCS Print Team')) ?: 'RCS Print Team',
                    trim((string)($body['meta_title'] ?? '')),
                    trim((string)($body['meta_description'] ?? '')),
                    trim((string)($body['published_at'] ?? '')) ?: date('Y-m-d H:i:s'),
                    (int)($body['sort_order'] ?? 0),
                    (int)($body['is_featured'] ?? 1),
                    (int)($body['is_active'] ?? 1),
                ]
            );
            json(['ok'=>true,'id'=>$id,'slug'=>$slug]);
        } catch (\Throwable) {
            json(['ok'=>false,'msg'=>'Could not create blog. Run migration first.'], 500);
        }
    }
    if (preg_match('#^/admin/api/blogs/(\d+)$#', $uri, $m) && $method === 'PUT') {
        $id = (int)$m[1];
        $title = trim((string)($body['title'] ?? ''));
        $content = $sanitizeBlogContent((string)($body['content'] ?? ''));
        if ($title === '') json(['ok'=>false,'msg'=>'Blog title is required'], 400);
        if ($content === '') json(['ok'=>false,'msg'=>'Blog content is required'], 400);
        $badgeTheme = strtolower(trim((string)($body['badge_theme'] ?? 'purple')));
        if (!in_array($badgeTheme, ['purple','orange','green'], true)) $badgeTheme = 'purple';
        try {
            $slug = $uniqueBlogSlug(trim((string)($body['slug'] ?? '')) ?: $title, $id);
            Database::query(
                "UPDATE blogs
                 SET title=?, slug=?, excerpt=?, content=?, featured_image=?, image_alt=?, category=?, badge_theme=?, author_name=?, meta_title=?, meta_description=?, published_at=?, sort_order=?, is_featured=?, is_active=?, updated_at=NOW()
                 WHERE id=?",
                [
                    $title,
                    $slug,
                    trim((string)($body['excerpt'] ?? '')),
                    $content,
                    trim((string)($body['featured_image'] ?? '')),
                    trim((string)($body['image_alt'] ?? '')),
                    trim((string)($body['category'] ?? 'Print Tips')) ?: 'Print Tips',
                    $badgeTheme,
                    trim((string)($body['author_name'] ?? 'RCS Print Team')) ?: 'RCS Print Team',
                    trim((string)($body['meta_title'] ?? '')),
                    trim((string)($body['meta_description'] ?? '')),
                    trim((string)($body['published_at'] ?? '')) ?: date('Y-m-d H:i:s'),
                    (int)($body['sort_order'] ?? 0),
                    (int)($body['is_featured'] ?? 1),
                    (int)($body['is_active'] ?? 1),
                    $id,
                ]
            );
            json(['ok'=>true,'slug'=>$slug]);
        } catch (\Throwable) {
            json(['ok'=>false,'msg'=>'Could not update blog'], 500);
        }
    }
    if (preg_match('#^/admin/api/blogs/(\d+)$#', $uri, $m) && $method === 'DELETE') {
        try {
            Database::query("DELETE FROM blogs WHERE id=?", [(int)$m[1]]);
            json(['ok'=>true]);
        } catch (\Throwable) {
            json(['ok'=>false,'msg'=>'Could not delete blog'], 500);
        }
    }
    if ($uri === '/admin/api/blogs/upload' && $method === 'POST') {
        if (empty($_FILES['image']) || !is_uploaded_file($_FILES['image']['tmp_name'])) {
            json(['ok'=>false,'msg'=>'Image file is required'], 400);
        }
        $file = $_FILES['image'];
        if ((int)$file['size'] <= 0) json(['ok'=>false,'msg'=>'Empty upload'], 400);
        if ((int)$file['size'] > 30 * 1024 * 1024) json(['ok'=>false,'msg'=>'Max file size is 30MB'], 400);
        $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp','mp4','webm'], true)) json(['ok'=>false,'msg'=>'Only jpg, png, webp, mp4, webm allowed'], 400);
        $mime = mime_content_type($file['tmp_name']) ?: '';
        $isVideo = in_array($mime, ['video/mp4','video/webm'], true);
        if (!in_array($mime, ['image/jpeg','image/png','image/webp','video/mp4','video/webm'], true)) json(['ok'=>false,'msg'=>'Invalid media type'], 400);
        $dir = PUBLIC_PATH . '/uploads/blogs/';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $name = 'blog_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $target = $dir . $name;
        if (!move_uploaded_file($file['tmp_name'], $target)) json(['ok'=>false,'msg'=>'Upload failed'], 500);
        json(['ok'=>true,'path'=>'/uploads/blogs/' . $name,'type'=>$isVideo ? 'video' : 'image']);
    }


    if ($uri === '/admin/api/theme' && $method === 'GET') {
        try {
            $theme = \Theme\SiteTheme::load();
            json(['ok'=>true,'theme'=>$theme,'defaults'=>\Theme\SiteTheme::defaults(),'element_styles'=>\Theme\SiteTheme::loadElementStyles(),'element_schema'=>\Theme\SiteTheme::elementSchema(),'css'=>\Theme\SiteTheme::css($theme)]);
        } catch (\Throwable $e) {
            error_log('Theme load failed: ' . $e->getMessage());
            json(['ok'=>false,'msg'=>'Theme load failed. Check database settings/theme_element_styles tables.'], 500);
        }
    }
    if ($uri === '/admin/api/theme' && $method === 'POST') {
        try {
            $saved = \Theme\SiteTheme::save(is_array($body) ? $body : []);
            $elementStyles = \Theme\SiteTheme::saveElementStyles(is_array($body['element_styles'] ?? null) ? $body['element_styles'] : \Theme\SiteTheme::loadElementStyles());
            $theme = array_merge(\Theme\SiteTheme::load(), $saved);
            $css = \Theme\SiteTheme::css($theme, $elementStyles);
            \Orders\AdminAudit::log('theme_updated','Website design theme updated');
            json(['ok'=>true,'theme'=>$theme,'element_styles'=>$elementStyles,'css'=>$css,'meta'=>array_merge(\Theme\SiteTheme::lastElementSaveMeta(), ['css_length'=>strlen($css), 'element_style_count'=>count($elementStyles)])]);
        } catch (\Throwable $e) {
            error_log('Theme save failed: ' . $e->getMessage());
            json(['ok'=>false,'msg'=>'Theme save failed. Run database/sql/add_theme_element_styles.sql and try again.'], 500);
        }
    }
    if ($uri === '/admin/api/theme/preview' && $method === 'POST') {
        try {
            $theme = array_merge(\Theme\SiteTheme::defaults(), is_array($body) ? $body : []);
            $theme = \Theme\SiteTheme::sanitizeValues($theme);
            $rawElementStyles = is_array($body['element_styles'] ?? null) ? $body['element_styles'] : \Theme\SiteTheme::loadElementStyles();
            $elementStyles = \Theme\SiteTheme::sanitizeElementStyles($rawElementStyles);
            $css = \Theme\SiteTheme::css($theme, $elementStyles);
            json(['ok'=>true,'theme'=>$theme,'element_styles'=>$elementStyles,'css'=>$css,'meta'=>['css_length'=>strlen($css),'element_style_count'=>count($elementStyles),'dropped_element_style_count'=>max(0, count($rawElementStyles) - count($elementStyles))]]);
        } catch (\Throwable $e) {
            error_log('Theme preview failed: ' . $e->getMessage());
            json(['ok'=>false,'msg'=>'Theme preview failed. Check generated style values.'], 500);
        }
    }
    if ($uri === '/admin/api/theme/reset' && $method === 'POST') {
        try {
            $theme = \Theme\SiteTheme::reset();
            $elementStyles = \Theme\SiteTheme::resetElementStyles();
            \Orders\AdminAudit::log('theme_reset','Website design theme reset to defaults');
            $css = \Theme\SiteTheme::css($theme, $elementStyles);
            json(['ok'=>true,'theme'=>$theme,'element_styles'=>$elementStyles,'css'=>$css,'meta'=>['css_length'=>strlen($css),'element_style_count'=>count($elementStyles)]]);
        } catch (\Throwable $e) {
            error_log('Theme reset failed: ' . $e->getMessage());
            json(['ok'=>false,'msg'=>'Theme reset failed. Check database permissions.'], 500);
        }
    }

    if ($uri === '/admin/api/settings' && $method === 'GET') {
        $rows = Database::rows("SELECT `key`,value FROM settings");
        json(['ok'=>true,'settings'=>array_column($rows,'value','key')]);
    }
    if ($uri === '/admin/api/settings' && $method === 'POST') {
        foreach ($body as $k=>$v) if ($k) Database::setSetting($k,$v);
        \Orders\AdminAudit::log('settings_updated','Settings saved');
        json(['ok'=>true]);
    }

    if ($uri === '/admin/api/leads' && $method === 'GET') {
        try {
            $data = \Leads\ContactLeadManager::adminList();
            json(['ok' => empty($data['msg'])] + $data, empty($data['msg']) ? 200 : 500);
        } catch (\Throwable $e) {
            error_log('Admin leads API failed: ' . $e->getMessage());
            json(['ok' => false, 'leads' => [], 'summary' => [], 'msg' => 'Unable to load leads.'], 500);
        }
    }
    if (preg_match('#^/admin/api/leads/(\d+)$#', $uri, $m) && $method === 'POST') {
        $result = \Leads\ContactLeadManager::update((int)$m[1], $body);
        json($result, ($result['ok'] ?? false) ? 200 : 422);
    }

    if ($uri === '/admin/api/customers' && $method === 'GET') {
        $customers = Database::rows(
            "SELECT u.id, u.name, u.email, u.phone, u.company, u.created_at,
                    COUNT(o.id) AS order_count,
                    COALESCE(SUM(o.total_amount),0) AS total_spent,
                    COALESCE(AVG(o.total_amount),0) AS avg_order_value,
                    MAX(o.created_at) AS last_order_at,
                    SUM(CASE WHEN o.status IN ('new_order','received','design_approved','printing','other_process','processing','ready','whatsapp_pending') THEN 1 ELSE 0 END) AS active_orders,
                    SUM(CASE WHEN o.status='delivered' THEN 1 ELSE 0 END) AS delivered_orders,
                    SUM(CASE WHEN o.status='cancelled' THEN 1 ELSE 0 END) AS cancelled_orders,
                    (SELECT oi.product_name
                       FROM order_items oi
                       INNER JOIN orders lo ON lo.id = oi.order_id
                      WHERE lo.user_id = u.id
                      ORDER BY lo.created_at DESC, oi.id ASC
                      LIMIT 1) AS last_product
             FROM users u
             LEFT JOIN orders o ON o.user_id = u.id
             GROUP BY u.id
             ORDER BY total_spent DESC, last_order_at DESC"
        );
        $summary = [
            'total_customers' => count($customers),
            'repeat_customers' => 0,
            'high_value_customers' => 0,
            'inactive_customers' => 0,
            'total_revenue' => 0,
        ];
        $now = time();
        foreach ($customers as &$customer) {
            $orders = (int)($customer['order_count'] ?? 0);
            $spent = (float)($customer['total_spent'] ?? 0);
            $lastOrderAt = (string)($customer['last_order_at'] ?? '');
            $daysSince = $lastOrderAt !== '' ? (int)floor(max(0, $now - app_timestamp($lastOrderAt)) / 86400) : null;
            $segment = 'new';
            if ($orders === 0) $segment = 'no_orders';
            elseif ($daysSince !== null && $daysSince >= 60) $segment = 'inactive';
            elseif ($spent >= 25000) $segment = 'high_value';
            elseif ($orders >= 2) $segment = 'repeat';
            $customer['segment'] = $segment;
            $customer['days_since_last_order'] = $daysSince;
            $customer['recent_orders'] = Database::rows(
                "SELECT order_id, total_amount, status, payment_status, created_at
                   FROM orders
                  WHERE user_id = ?
                  ORDER BY created_at DESC
                  LIMIT 4",
                [(int)$customer['id']]
            );
            if ($orders >= 2) $summary['repeat_customers']++;
            if ($spent >= 25000) $summary['high_value_customers']++;
            if ($segment === 'inactive') $summary['inactive_customers']++;
            $summary['total_revenue'] += $spent;
        }
        unset($customer);
        json(['ok'=>true,'customers'=>$customers,'summary'=>$summary]);
    }
    if ($uri === '/admin/api/approvals' && $method === 'GET') {
        \Approvals\ContentApprovalManager::requireSuperAdmin();
        json(['ok'=>true,'items'=>\Approvals\ContentApprovalManager::listPending(),'can_approve'=>true]);
    }
    if (preg_match('#^/admin/api/approvals/([a-z_]+)/(\d+)/(approve|reject)$#', $uri, $m) && $method === 'POST') {
        $result = \Approvals\ContentApprovalManager::decide((string)$m[1], (int)$m[2], (string)$m[3], trim((string)($body['note'] ?? '')));
        json($result, ($result['ok'] ?? false) ? 200 : 422);
    }

    if ($uri === '/admin/api/admin-users' && $method === 'GET') {
        \Approvals\ContentApprovalManager::requireSuperAdmin();
        $hasMobile = $hasAdminUsersMobile();
        $mobileSelect = $hasMobile ? "mobile" : "'' AS mobile";
        $admins = Database::rows(
            "SELECT id, name, email, role, is_active, created_at, last_login, $mobileSelect
             FROM admin_users
             ORDER BY created_at DESC"
        );
        json(['ok' => true, 'admins' => $admins, 'has_mobile_column' => $hasMobile]);
    }
    if ($uri === '/admin/api/admin-users' && $method === 'POST') {
        \Approvals\ContentApprovalManager::requireSuperAdmin();
        $name = trim((string)($body['name'] ?? ''));
        $email = strtolower(trim((string)($body['email'] ?? '')));
        $mobile = trim((string)($body['mobile'] ?? ''));
        $password = (string)($body['password'] ?? '');
        $role = trim((string)($body['role'] ?? 'admin')) ?: 'admin';
        $role = in_array($role, ['admin','super'], true) ? $role : 'admin';

        if ($name === '' || $email === '' || $mobile === '' || $password === '') {
            json(['ok'=>false,'msg'=>'Name, email, mobile and password are required.'], 422);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json(['ok'=>false,'msg'=>'Please enter a valid email address.'], 422);
        }
        if (!preg_match('/^[0-9]{10,15}$/', preg_replace('/\D+/', '', $mobile))) {
            json(['ok'=>false,'msg'=>'Please enter a valid mobile number (10-15 digits).'], 422);
        }
        if (strlen($password) < 6) {
            json(['ok'=>false,'msg'=>'Password must be at least 6 characters.'], 422);
        }

        if (Database::row("SELECT id FROM admin_users WHERE email = ? LIMIT 1", [$email])) {
            json(['ok'=>false,'msg'=>'Email is already used by another admin.'], 409);
        }

        $hasMobile = $hasAdminUsersMobile();
        if ($hasMobile && Database::row("SELECT id FROM admin_users WHERE mobile = ? LIMIT 1", [$mobile])) {
            json(['ok'=>false,'msg'=>'Mobile number is already used by another admin.'], 409);
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
        if ($hasMobile) {
            $id = Database::insert(
                "INSERT INTO admin_users (name, email, mobile, password, role, is_active, created_at)
                 VALUES (?, ?, ?, ?, ?, 1, NOW())",
                [$name, $email, $mobile, $hash, $role]
            );
        } else {
            $id = Database::insert(
                "INSERT INTO admin_users (name, email, password, role, is_active, created_at)
                 VALUES (?, ?, ?, ?, 1, NOW())",
                [$name, $email, $hash, $role]
            );
        }
        \Orders\AdminAudit::log('admin_user_created', "Admin user #{$id} created ({$email})");
        json(['ok' => true, 'id' => $id]);
    }
    if (preg_match('#^/admin/api/admin-users/(\d+)/password$#', $uri, $m) && $method === 'POST') {
        \Approvals\ContentApprovalManager::requireSuperAdmin();
        $adminId = (int)$m[1];
        $newPassword = (string)($body['new_password'] ?? '');
        if (strlen($newPassword) < 6) {
            json(['ok' => false, 'msg' => 'Password must be at least 6 characters.'], 422);
        }
        $target = Database::row("SELECT id, email FROM admin_users WHERE id = ? LIMIT 1", [$adminId]);
        if (!$target) json(['ok' => false, 'msg' => 'Admin user not found.'], 404);
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 10]);
        Database::query("UPDATE admin_users SET password = ? WHERE id = ?", [$hash, $adminId]);
        \Orders\AdminAudit::log('admin_user_password_changed', "Password changed for admin #{$adminId} ({$target['email']})");
        json(['ok' => true]);
    }
    if (preg_match('#^/admin/api/admin-users/(\d+)$#', $uri, $m) && $method === 'DELETE') {
        \Approvals\ContentApprovalManager::requireSuperAdmin();
        $targetId = (int)$m[1];
        $current = \Auth\Auth::admin();
        $currentId = (int)($current['id'] ?? 0);
        if ($targetId === $currentId) {
            json(['ok' => false, 'msg' => 'You cannot remove your own admin account.'], 422);
        }

        $target = Database::row("SELECT id, email, is_active FROM admin_users WHERE id = ? LIMIT 1", [$targetId]);
        if (!$target) json(['ok' => false, 'msg' => 'Admin user not found.'], 404);
        if ((int)$target['is_active'] !== 1) json(['ok' => false, 'msg' => 'Admin is already inactive.'], 422);

        $activeCount = (int)(Database::row("SELECT COUNT(*) AS c FROM admin_users WHERE is_active = 1")['c'] ?? 0);
        if ($activeCount <= 1) {
            json(['ok' => false, 'msg' => 'At least one active admin is required.'], 422);
        }

        Database::query("UPDATE admin_users SET is_active = 0 WHERE id = ?", [$targetId]);
        \Orders\AdminAudit::log('admin_user_removed', "Admin #{$targetId} deactivated ({$target['email']})");
        json(['ok' => true]);
    }
    if ($uri === '/admin/api/audit-logs' && $method === 'GET') {
        json(['ok'=>true,'logs'=>Database::rows("SELECT * FROM admin_audit_logs ORDER BY created_at DESC LIMIT 200")]);
    }
    if (preg_match('#^/admin/api/artwork/(\d+)$#', $uri, $m) && $method === 'GET') {
        $file = Database::row("SELECT * FROM artwork_files WHERE id=?",[$m[1]]);
        json($file ? ['ok'=>true,'file'=>$file] : ['ok'=>false,'msg'=>'Not found'],404);
    }
    if (preg_match('#^/admin/api/design-approvals/(\d+)$#', $uri, $m) && $method === 'POST') {
        $status = trim((string)($body['status'] ?? 'pending_review'));
        $note = trim((string)($body['admin_note'] ?? ''));
        $ok = \Orders\OrderManager::updateDesignApproval((int)$m[1], $status, $note);
        json(['ok' => $ok]);
    }
    if (preg_match('#^/admin/api/design-approvals/(\d+)/proof$#', $uri, $m) && $method === 'POST') {
        $approval = Database::row("SELECT * FROM order_design_approvals WHERE id = ?", [(int)$m[1]]);
        if (!$approval) json(['ok' => false, 'msg' => 'Design approval not found'], 404);
        if (empty($_FILES['proof'])) json(['ok' => false, 'msg' => 'No proof file uploaded'], 400);

        $file = $_FILES['proof'];
        $maxMb = (int)Database::setting('upload_max_mb', env('UPLOAD_MAX_SIZE_MB', '50'));
        if ($file['size'] > ($maxMb * 1024 * 1024)) json(['ok' => false, 'msg' => "File too large. Max {$maxMb}MB."], 400);
        $allowed = explode(',', Database::setting('upload_allowed_ext', 'pdf,ai,eps,png,jpg,jpeg,psd,cdr,svg,tif,tiff,zip'));
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) json(['ok' => false, 'msg' => "File type .{$ext} not allowed."], 400);

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        $dir = UPLOAD_PATH . '/artwork/proofs/' . date('Y/m/');
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $filename = uniqid('proof_', true) . '.' . $ext;
        $filepath = $dir . $filename;
        $publicPath = '/uploads/artwork/proofs/' . date('Y/m/') . $filename;
        if (!move_uploaded_file($file['tmp_name'], $filepath)) json(['ok' => false, 'msg' => 'Upload failed'], 500);

        $adminId = (int)($admin['id'] ?? 0);
        $fileId = Database::insert(
            "INSERT INTO artwork_files (uploaded_by, order_item_id, filename, original_name, file_path, mime_type, file_size, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
            [$adminId ?: 0, (int)$approval['order_item_id'], $filename, $file['name'], $publicPath, $mime, $file['size']]
        );
        \Orders\OrderManager::updateDesignApproval((int)$m[1], 'proof_uploaded', trim((string)($_POST['admin_note'] ?? '')), (int)$fileId);
        json(['ok' => true, 'file_id' => (int)$fileId]);
    }
    if ($uri === '/admin/api/sheets/retry' && $method === 'POST') {
        $failed = Database::rows("SELECT * FROM sheets_sync_log WHERE resolved=0 LIMIT 20");
        foreach ($failed as $row) Database::query("UPDATE sheets_sync_log SET resolved=1 WHERE id=?",[$row['id']]);
        json(['ok'=>true,'retried'=>count($failed)]);
    }

    json(['ok'=>false,'msg'=>'Admin API not found'],404);
}

if ($uri === '/admin/settings/save' && $method === 'POST') {
    foreach ($_POST as $k => $v) {
        if ($k !== '_token' && $k !== 'new_admin_password') Database::setSetting($k, trim((string)$v));
    }

    $newPass = trim((string)($_POST['new_admin_password'] ?? ''));
    if ($newPass !== '') {
        if (strlen($newPass) < 6) {
            redirect('/admin/settings?saved=0&err=password_min_6');
        }
        $hash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 10]);
        $admin = \Auth\Auth::admin();
        if ($admin && !empty($admin['id'])) {
            Database::query("UPDATE admin_users SET password=? WHERE id=?", [$hash, $admin['id']]);
        }
    }

    \Orders\AdminAudit::log('settings_updated', 'Settings saved via form');
    redirect('/admin/settings?saved=1');
}

if ($uri === '/admin/export/orders') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="orders-' . date('Y-m-d') . '.csv"');
    $orders = Database::rows("SELECT o.order_id,o.created_at,o.customer_name,o.customer_phone,o.customer_email,o.subtotal,o.discount_amount,o.gst_amount,o.total_amount,o.payment_status,o.status,o.coupon_code,o.payment_id FROM orders o ORDER BY o.created_at DESC");
    echo implode(',', ['Order ID','Date','Customer','Phone','Email','Subtotal','Discount','GST','Total','Payment','Status','Coupon','Payment ID']) . "\n";
    foreach ($orders as $row) echo implode(',', array_map(fn($v) => '"' . str_replace('"','""',$v??'') . '"', $row)) . "\n";
    exit;
}

if (preg_match('#^/admin/invoice/(.+)$#', $uri, $m)) {
    try {
        $order = \Orders\OrderManager::getOrderByOrderId($m[1]);
        if (!$order) { http_response_code(404); exit; }
        \Invoice\InvoiceGenerator::download($order);
    } catch (\Throwable $e) {
        error_log('Admin invoice failed for ' . $m[1] . ': ' . $e->getMessage());
        http_response_code(500);
        echo 'Invoice generation failed. Please check logs.';
    }
    exit;
}

if (preg_match('#^/admin/artwork/(\d+)/(download|view)$#', $uri, $m)) {
    $file = Database::row("SELECT * FROM artwork_files WHERE id=?", [$m[1]]);
    if (!$file) { http_response_code(404); exit('Not found'); }
    $full = PUBLIC_PATH . ($file['file_path'] ?? '');
    if (!is_file($full)) { http_response_code(404); exit('File missing'); }
    $downloadName = str_replace(['"', "\r", "\n"], '', basename($file['original_name'] ?: $file['filename']));
    $disposition = ($m[2] ?? 'download') === 'view' ? 'inline' : 'attachment';
    header('Content-Type: ' . ($file['mime_type'] ?: 'application/octet-stream'));
    header('Content-Disposition: ' . $disposition . '; filename="' . $downloadName . '"');
    header('Content-Length: ' . filesize($full));
    readfile($full);
    exit;
}

$settingsMap = [];
if (str_contains($uri, '/admin/settings') || str_contains($uri, '/admin/integrations')) {
    $rows = Database::rows("SELECT `key`, value FROM settings");
    foreach ($rows as $r) $settingsMap[$r['key']] = $r['value'];
}

if ($uri === '/admin/orders') {
    $search = trim((string)($_GET['search'] ?? ''));
    $status = trim((string)($_GET['status'] ?? 'all'));
    $paymentStatus = trim((string)($_GET['payment_status'] ?? 'all'));
    $seen = trim((string)($_GET['seen'] ?? 'all'));
    $sort = trim((string)($_GET['sort'] ?? 'newest'));
    $dateFrom = trim((string)($_GET['date_from'] ?? ''));
    $dateTo = trim((string)($_GET['date_to'] ?? ''));
    $page   = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 12;
    $hasSeen = $ensureOrderSeenColumn();

    $summaryRow = Database::row(
        "SELECT
            COUNT(*) AS total_orders,
            SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS new_today,
            SUM(CASE WHEN status IN ('new_order','received','whatsapp_pending') THEN 1 ELSE 0 END) AS pending_orders,
            SUM(CASE WHEN status IN ('other_process','processing') THEN 1 ELSE 0 END) AS processing_orders,
            SUM(CASE WHEN status = 'ready' THEN 1 ELSE 0 END) AS ready_orders,
            SUM(CASE WHEN status IN ('new_order','received','whatsapp_pending','design_approved','other_process','processing','printing') AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 ELSE 0 END) AS delayed_orders
         FROM orders"
    ) ?: [];
    $summaryCounts = [
        'total_orders' => (int)($summaryRow['total_orders'] ?? 0),
        'new_today' => (int)($summaryRow['new_today'] ?? 0),
        'pending_orders' => (int)($summaryRow['pending_orders'] ?? 0),
        'processing_orders' => (int)($summaryRow['processing_orders'] ?? 0),
        'ready_orders' => (int)($summaryRow['ready_orders'] ?? 0),
        'delayed_orders' => (int)($summaryRow['delayed_orders'] ?? 0),
    ];
    $statusCountRows = Database::rows("SELECT status, COUNT(*) AS c FROM orders GROUP BY status");
    $statusCounts = ['all' => $summaryCounts['total_orders']];
    foreach ($statusCountRows as $row) {
        $statusCounts[(string)$row['status']] = (int)($row['c'] ?? 0);
    }
    $statusCounts['new_order'] = (int)($statusCounts['new_order'] ?? 0);
    $statusCounts['design_approved'] = (int)($statusCounts['design_approved'] ?? 0);
    $statusCounts['other_process'] = (int)($statusCounts['other_process'] ?? 0) + (int)($statusCounts['processing'] ?? 0);
    $statusCounts['ready_dispatch'] = (int)($statusCounts['ready'] ?? 0);
    $statusCounts['attention'] = ($statusCounts['received'] ?? 0) + ($statusCounts['whatsapp_pending'] ?? 0) + ($statusCounts['design_approved'] ?? 0) + ($statusCounts['other_process'] ?? 0) + ($statusCounts['printing'] ?? 0);
    $statusCounts['delayed'] = $summaryCounts['delayed_orders'];

    $where = [];
    $params = [];
    if ($status === 'attention') {
        $where[] = "status IN ('new_order','received','whatsapp_pending','design_approved','other_process','processing','printing')";
    } elseif ($status === 'delayed') {
        $where[] = "status IN ('new_order','received','whatsapp_pending','design_approved','other_process','processing','printing') AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    } elseif ($status === 'other_process') {
        $where[] = "status IN ('other_process','processing')";
    } elseif ($status !== 'all' && $status !== '') { $where[] = 'status = ?'; $params[] = $status; }
    if ($paymentStatus !== 'all' && $paymentStatus !== '') { $where[] = 'payment_status = ?'; $params[] = $paymentStatus; }
    if ($seen === 'new') {
        $where[] = "status = 'new_order'";
    } elseif ($seen === 'seen' && $hasSeen) {
        $where[] = 'is_seen = 1';
    }
    if ($dateFrom !== '') { $where[] = 'DATE(created_at) >= ?'; $params[] = $dateFrom; }
    if ($dateTo !== '') { $where[] = 'DATE(created_at) <= ?'; $params[] = $dateTo; }
    if ($search !== '') {
        $where[] = '(order_id LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ? OR customer_email LIKE ?)';
        $like = '%' . $search . '%';
        array_push($params, $like, $like, $like, $like);
    }
    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
    $orderSql = match ($sort) {
        'oldest' => 'created_at ASC',
        'high_value' => 'total_amount DESC, created_at DESC',
        'urgent' => ($hasSeen ? 'is_seen ASC, ' : '') . "FIELD(status,'new_order','received','whatsapp_pending','design_approved','other_process','processing','printing','ready','delivered','cancelled'), created_at ASC",
        default => 'created_at DESC',
    };

    $countRow = Database::row("SELECT COUNT(*) c FROM orders $whereSql", $params);
    $total = (int)($countRow['c'] ?? 0);
    $offset = ($page - 1) * $perPage;

    $orders = Database::rows("SELECT * FROM orders $whereSql ORDER BY $orderSql LIMIT $perPage OFFSET $offset", $params);
    foreach ($orders as &$o) {
        $o['items'] = Database::rows(
            "SELECT oi.*,
                    COALESCE(pi.image_path, pi.url) AS product_image,
                    af.id AS artwork_file_id,
                    af.original_name AS artwork_original_name,
                    af.filename AS artwork_filename,
                    af.file_path AS artwork_file_path,
                    af.mime_type AS artwork_mime_type,
                    oda.id AS design_approval_id,
                    oda.status AS design_approval_status,
                    oda.admin_note AS design_admin_note,
                    oda.customer_note AS design_customer_note,
                    oda.approved_at AS design_approved_at,
                    oda.proof_file_id AS design_proof_file_id,
                    pf.original_name AS design_proof_original_name,
                    pf.filename AS design_proof_filename,
                    pf.file_path AS design_proof_file_path,
                    pf.mime_type AS design_proof_mime_type
             FROM order_items oi
             LEFT JOIN product_images pi ON pi.product_id = oi.product_id AND pi.is_primary = 1
             LEFT JOIN order_design_approvals oda ON oda.order_item_id = oi.id
             LEFT JOIN artwork_files af ON af.id = oda.customer_artwork_file_id
             LEFT JOIN artwork_files pf ON pf.id = oda.proof_file_id
             WHERE oi.order_id=?
             ORDER BY oi.id ASC",
            [$o['id']]
        );
        foreach ($o['items'] as &$item) {
            if (empty($item['design_approval_id'])) {
                $customerArtwork = Database::row(
                    "SELECT af.id FROM artwork_files af
                      WHERE af.order_item_id = ?
                        AND NOT EXISTS (SELECT 1 FROM order_design_approvals oda2 WHERE oda2.proof_file_id = af.id)
                      ORDER BY af.id ASC LIMIT 1",
                    [(int)$item['id']]
                );
                \Orders\OrderManager::ensureDesignApprovalForItem(
                    (int)$o['id'],
                    (int)$item['id'],
                    (string)($item['design_choice'] ?? 'upload'),
                    !empty($customerArtwork['id']) ? (int)$customerArtwork['id'] : null
                );
            }
        }
        unset($item);
    }

    view('admin/orders', compact('orders','total','page','perPage','status','search','summaryCounts','statusCounts','paymentStatus','seen','sort','dateFrom','dateTo','hasSeen'));
    exit;
}

if (preg_match('#^/admin/blogs/edit/(\d+)$#', $uri, $m) && $method === 'GET') {
    view('admin/blogs-new', ['blogEditId' => (int)$m[1]]);
    exit;
}

if (preg_match('#^/admin/deals/edit/(\d+)$#', $uri, $m) && $method === 'GET') {
    view('admin/deals-new', ['dealEditId' => (int)$m[1]]);
    exit;
}

if (preg_match('#^/admin/coupons/edit/(\d+)$#', $uri, $m) && $method === 'GET') {
    view('admin/coupons-new', ['couponEditId' => (int)$m[1]]);
    exit;
}

if (in_array($uri, ['/admin/admins', '/admin/approvals'], true)) {
    \Auth\Auth::requireSuperAdmin();
}

$adminPage = match(true) {
    $uri === '/admin' || $uri === '/admin/dashboard' => 'admin/dashboard',
    $uri === '/admin/analytics'  => 'admin/analytics',
    $uri === '/admin/products'   => 'admin/products',
    $uri === '/admin/categories' => 'admin/categories',
    $uri === '/admin/media'      => 'admin/media',
    $uri === '/admin/products/new' => 'admin/products-new',
    $uri === '/admin/banners'    => 'admin/banners',
    $uri === '/admin/deals'      => 'admin/deals',
    $uri === '/admin/deals/new'  => 'admin/deals-new',
    $uri === '/admin/blogs'      => 'admin/blogs',
    $uri === '/admin/blogs/new'  => 'admin/blogs-new',
    $uri === '/admin/pricing'    => 'admin/pricing',
    $uri === '/admin/coupons'    => 'admin/coupons',
    $uri === '/admin/coupons/new' => 'admin/coupons-new',
    $uri === '/admin/reviews'    => 'admin/reviews',
    $uri === '/admin/faqs'       => 'admin/faqs',
    $uri === '/admin/customers'  => 'admin/customers',
    $uri === '/admin/leads'      => 'admin/leads',
    $uri === '/admin/approvals'  => 'admin/approvals',
    $uri === '/admin/admins'     => 'admin/admins',
    $uri === '/admin/settings'   => 'admin/settings',
    $uri === '/admin/design'     => 'admin/design',
    $uri === '/admin/integrations' => 'admin/integrations',
    $uri === '/admin/audit-logs' => 'admin/audit-logs',
    default                      => null,
};

if ($adminPage) { view($adminPage, compact('settingsMap')); exit; }

http_response_code(404);
view('404');
exit;
