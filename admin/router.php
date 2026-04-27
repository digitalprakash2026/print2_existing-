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

if (str_starts_with($uri, '/admin/api/')) {
    header('Content-Type: application/json');
    $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $normalizePublicImagePath = static function (mixed $path): string {
        $p = trim((string)$path);
        if ($p === '') return '';
        if (preg_match('#^https?://#i', $p)) return $p;
        return '/' . ltrim($p, '/');
    };

    if ($uri === '/admin/api/dashboard' && $method === 'GET') {
        $stats = [
            'total_orders'    => Database::row("SELECT COUNT(*) as c FROM orders")['c'] ?? 0,
            'total_revenue'   => Database::row("SELECT COALESCE(SUM(total_amount),0) as r FROM orders WHERE payment_status='paid'")['r'] ?? 0,
            'today_orders'    => Database::row("SELECT COUNT(*) as c FROM orders WHERE DATE(created_at)=CURDATE()")['c'] ?? 0,
            'today_revenue'   => Database::row("SELECT COALESCE(SUM(total_amount),0) as r FROM orders WHERE DATE(created_at)=CURDATE() AND payment_status='paid'")['r'] ?? 0,
            'pending_orders'  => Database::row("SELECT COUNT(*) as c FROM orders WHERE status IN ('received','processing','printing')")['c'] ?? 0,
            'total_customers' => Database::row("SELECT COUNT(*) as c FROM users")['c'] ?? 0,
        ];
        $byStatus    = Database::rows("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
        $monthly     = Database::rows("SELECT DATE_FORMAT(created_at,'%b %Y') as month, SUM(total_amount) as revenue, COUNT(*) as orders FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY YEAR(created_at), MONTH(created_at) ORDER BY created_at ASC");
        $topProducts = Database::rows("SELECT product_name, COUNT(*) as count, SUM(total_price) as revenue FROM order_items GROUP BY product_name ORDER BY count DESC LIMIT 8");
        $recentOrders= Database::rows("SELECT o.*, COUNT(oi.id) as item_count FROM orders o LEFT JOIN order_items oi ON oi.order_id=o.id GROUP BY o.id ORDER BY o.created_at DESC LIMIT 10");
        json(['ok'=>true,'stats'=>$stats,'by_status'=>$byStatus,'monthly'=>$monthly,'top_products'=>$topProducts,'recent_orders'=>$recentOrders]);
    }

    if ($uri === '/admin/api/orders' && $method === 'GET') {
        $status = trim($_GET['status'] ?? '');
        $q      = trim($_GET['q'] ?? '');
        $where = [];
        $params = [];
        if ($status && $status !== 'all') { $where[] = 'o.status = ?'; $params[] = $status; }
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

    if ($uri === '/admin/api/products' && $method === 'GET') {
        try {
            json(['ok'=>true,'products'=>\Catalog\ProductCatalog::all(false)]);
        } catch (\Throwable $e) {
            error_log('Admin products list failed: ' . $e->getMessage());
            json(['ok'=>false,'msg'=>'Could not load products. Check DB schema and logs.','products'=>[]], 500);
        }
    }
    if ($uri === '/admin/api/products' && $method === 'POST') {
        json(\Catalog\ProductCatalog::upsert($body));
    }
    if (preg_match('#^/admin/api/products/(\d+)$#', $uri, $m) && $method === 'GET') {
        $p = \Catalog\ProductCatalog::byId((int)$m[1]);
        json($p ? ['ok'=>true,'product'=>$p] : ['ok'=>false,'msg'=>'Not found'], $p ? 200 : 404);
    }
    if (preg_match('#^/admin/api/products/(\d+)$#', $uri, $m) && $method === 'PUT') {
        json(\Catalog\ProductCatalog::upsert($body, (int)$m[1]));
    }
    if (preg_match('#^/admin/api/products/(\d+)/toggle$#', $uri, $m) && $method === 'POST') {
        Database::query("UPDATE products SET is_active = NOT is_active WHERE id=?", [$m[1]]);
        json(['ok'=>true]);
    }
    if (preg_match('#^/admin/api/products/(\d+)$#', $uri, $m) && $method === 'DELETE') {
        Database::query("DELETE FROM products WHERE id=?", [$m[1]]);
        json(['ok'=>true]);
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
        if (!empty($body['is_primary'])) Database::query("UPDATE product_images SET is_primary=0 WHERE product_id=? AND id!=?", [$pid,$id]);
        json(['ok'=>true,'id'=>$id]);
    }
    if (preg_match('#^/admin/api/products/(\d+)/images$#', $uri, $m) && $method === 'DELETE') {
        Database::query("DELETE FROM product_images WHERE product_id=?", [$m[1]]);
        json(['ok'=>true]);
    }
    if (preg_match('#^/admin/api/images/(\d+)$#', $uri, $m) && $method === 'DELETE') {
        Database::query("DELETE FROM product_images WHERE id=?", [$m[1]]);
        json(['ok'=>true]);
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
        json(['ok'=>true,'coupons'=>Database::rows("SELECT * FROM coupons ORDER BY created_at DESC")]);
    }
    if ($uri === '/admin/api/coupons' && $method === 'POST') {
        $code = strtoupper(trim($body['code']??''));
        if (!$code) json(['ok'=>false,'msg'=>'Code required']);
        if (Database::row("SELECT id FROM coupons WHERE code=?",[$code])) json(['ok'=>false,'msg'=>'Code exists']);
        $id = Database::insert("INSERT INTO coupons (code,description,discount_type,discount_value,min_order_amount,max_uses,valid_from,valid_until,is_active) VALUES (?,?,?,?,?,?,?,?,1)",
            [$code,$body['description']??'',$body['discount_type']??'percent',(float)($body['discount_value']??0),(float)($body['min_order_amount']??0),(int)($body['max_uses']??0),$body['valid_from']?:null,$body['valid_until']?:null]);
        \Orders\AdminAudit::log('coupon_created',"Coupon: $code");
        json(['ok'=>true,'id'=>$id]);
    }
    if (preg_match('#^/admin/api/coupons/(\d+)/toggle$#', $uri, $m) && $method === 'POST') {
        Database::query("UPDATE coupons SET is_active=NOT is_active WHERE id=?",[$m[1]]);
        json(['ok'=>true]);
    }
    if (preg_match('#^/admin/api/coupons/(\d+)$#', $uri, $m) && $method === 'DELETE') {
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
    if ($uri === '/admin/api/categories' && $method === 'POST') {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/','-',$body['name']??''));
        $prefix = strtoupper(trim((string)($body['code_prefix'] ?? '')));
        $prefix = preg_replace('/[^A-Z0-9]/', '', $prefix) ?: null;
        try {
            $id = Database::insert("INSERT INTO categories (name,slug,code_prefix,icon,sort_order,is_active) VALUES (?,?,?,?,?,1)",
                [$body['name'],$slug,$prefix,$body['icon']??'🖨️',$body['sort_order']??0]);
        } catch (\Throwable) {
            $id = Database::insert("INSERT INTO categories (name,slug,icon,sort_order,is_active) VALUES (?,?,?,?,1)",
                [$body['name'],$slug,$body['icon']??'🖨️',$body['sort_order']??0]);
        }
        json(['ok'=>true,'id'=>$id]);
    }

    if ($uri === '/admin/api/banners' && $method === 'GET') {
        try {
            $rows = Database::rows("SELECT * FROM home_banners ORDER BY sort_order ASC, id DESC");
            json(['ok'=>true,'banners'=>$rows]);
        } catch (\Throwable) {
            json(['ok'=>false,'msg'=>'home_banners table missing. Run SQL migration first.','banners'=>[]], 500);
        }
    }
    if ($uri === '/admin/api/banners' && $method === 'POST') {
        try {
            $id = Database::insert(
                "INSERT INTO home_banners (eyebrow,title,subtitle,image_path,image_alt,cta_primary_text,cta_primary_url,cta_secondary_text,cta_secondary_type,cta_secondary_url,sort_order,is_active,created_at,updated_at)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())",
                [
                    trim((string)($body['eyebrow'] ?? '')),
                    trim((string)($body['title'] ?? '')),
                    trim((string)($body['subtitle'] ?? '')),
                    $normalizePublicImagePath($body['image_path'] ?? ''),
                    trim((string)($body['image_alt'] ?? '')),
                    trim((string)($body['cta_primary_text'] ?? 'View Products →')),
                    trim((string)($body['cta_primary_url'] ?? '#')),
                    trim((string)($body['cta_secondary_text'] ?? '💬 WhatsApp')),
                    trim((string)($body['cta_secondary_type'] ?? 'whatsapp')),
                    trim((string)($body['cta_secondary_url'] ?? '')),
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
        try {
            Database::query(
                "UPDATE home_banners
                 SET eyebrow=?, title=?, subtitle=?, image_path=?, image_alt=?, cta_primary_text=?, cta_primary_url=?, cta_secondary_text=?, cta_secondary_type=?, cta_secondary_url=?, sort_order=?, is_active=?, updated_at=NOW()
                 WHERE id=?",
                [
                    trim((string)($body['eyebrow'] ?? '')),
                    trim((string)($body['title'] ?? '')),
                    trim((string)($body['subtitle'] ?? '')),
                    $normalizePublicImagePath($body['image_path'] ?? ''),
                    trim((string)($body['image_alt'] ?? '')),
                    trim((string)($body['cta_primary_text'] ?? 'View Products →')),
                    trim((string)($body['cta_primary_url'] ?? '#')),
                    trim((string)($body['cta_secondary_text'] ?? '💬 WhatsApp')),
                    trim((string)($body['cta_secondary_type'] ?? 'whatsapp')),
                    trim((string)($body['cta_secondary_url'] ?? '')),
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
        $mime = '';
        if (function_exists('finfo_open')) {
            $f = finfo_open(FILEINFO_MIME_TYPE);
            if ($f) {
                $mime = (string)(finfo_file($f, $file['tmp_name']) ?: '');
                finfo_close($f);
            }
        } elseif (function_exists('mime_content_type')) {
            $mime = (string)(mime_content_type($file['tmp_name']) ?: '');
        }
        if ($mime === '') {
            $mime = strtolower((string)($file['type'] ?? ''));
        }
        if (!in_array($mime, ['image/jpeg','image/png','image/webp'], true)) json(['ok'=>false,'msg'=>'Invalid image type'], 400);
        $dir = PUBLIC_PATH . '/uploads/banners/';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $name = 'banner_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $target = $dir . $name;
        if (!move_uploaded_file($file['tmp_name'], $target)) json(['ok'=>false,'msg'=>'Upload failed'], 500);
        json(['ok'=>true,'path'=>'/uploads/banners/' . $name]);
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
    if ($uri === '/admin/api/email/test' && $method === 'POST') {
        $to = trim((string)($body['email'] ?? ''));
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            json(['ok'=>false,'msg'=>'Valid email required'], 422);
        }
        $ok = \Email\Mailer::send(
            $to,
            'Test User',
            'Test Email — RCS Graphic',
            '<h2>Test email successful ✅</h2><p>Email configuration is working from Admin Panel settings.</p>'
        );
        json($ok ? ['ok'=>true] : ['ok'=>false,'msg'=>'Send failed. Check provider credentials and logs.']);
    }

    if ($uri === '/admin/api/customers' && $method === 'GET') {
        json(['ok'=>true,'customers'=>Database::rows("SELECT u.*,COUNT(o.id) as order_count, COALESCE(SUM(o.total_amount),0) as total_spent FROM users u LEFT JOIN orders o ON o.user_id=u.id GROUP BY u.id ORDER BY total_spent DESC")]);
    }
    if ($uri === '/admin/api/audit-logs' && $method === 'GET') {
        json(['ok'=>true,'logs'=>Database::rows("SELECT * FROM admin_audit_logs ORDER BY created_at DESC LIMIT 200")]);
    }
    if (preg_match('#^/admin/api/artwork/(\d+)$#', $uri, $m) && $method === 'GET') {
        $file = Database::row("SELECT * FROM artwork_files WHERE id=?",[$m[1]]);
        json($file ? ['ok'=>true,'file'=>$file] : ['ok'=>false,'msg'=>'Not found'],404);
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

if (preg_match('#^/admin/artwork/(\d+)/download$#', $uri, $m)) {
    $file = Database::row("SELECT * FROM artwork_files WHERE id=?", [$m[1]]);
    if (!$file) { http_response_code(404); exit('Not found'); }
    $full = PUBLIC_PATH . ($file['file_path'] ?? '');
    if (!is_file($full)) { http_response_code(404); exit('File missing'); }
    header('Content-Type: ' . ($file['mime_type'] ?: 'application/octet-stream'));
    header('Content-Disposition: attachment; filename="' . basename($file['original_name'] ?: $file['filename']) . '"');
    header('Content-Length: ' . filesize($full));
    readfile($full);
    exit;
}

$settingsMap = [];
if (str_contains($uri, '/admin/settings') || str_contains($uri, '/admin/integrations') || str_contains($uri, '/admin/email-settings')) {
    $rows = Database::rows("SELECT `key`, value FROM settings");
    foreach ($rows as $r) $settingsMap[$r['key']] = $r['value'];
}

if ($uri === '/admin/orders') {
    $search = trim((string)($_GET['search'] ?? ''));
    $status = trim((string)($_GET['status'] ?? 'all'));
    $page   = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 12;

    $where = [];
    $params = [];
    if ($status !== 'all' && $status !== '') { $where[] = 'status = ?'; $params[] = $status; }
    if ($search !== '') {
        $where[] = '(order_id LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ? OR customer_email LIKE ?)';
        $like = '%' . $search . '%';
        array_push($params, $like, $like, $like, $like);
    }
    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $countRow = Database::row("SELECT COUNT(*) c FROM orders $whereSql", $params);
    $total = (int)($countRow['c'] ?? 0);
    $offset = ($page - 1) * $perPage;

    $orders = Database::rows("SELECT * FROM orders $whereSql ORDER BY created_at DESC LIMIT $perPage OFFSET $offset", $params);
    foreach ($orders as &$o) {
        $o['items'] = Database::rows(
            "SELECT oi.*,
                    af.id AS artwork_file_id,
                    af.original_name AS artwork_original_name,
                    af.filename AS artwork_filename,
                    af.file_path AS artwork_file_path
             FROM order_items oi
             LEFT JOIN artwork_files af ON af.order_item_id = oi.id
             WHERE oi.order_id=?
             ORDER BY oi.id ASC",
            [$o['id']]
        );
    }

    view('admin/orders', compact('orders','total','page','perPage','status','search'));
    exit;
}

$adminPage = match(true) {
    $uri === '/admin' || $uri === '/admin/dashboard' => 'admin/dashboard',
    $uri === '/admin/analytics'  => 'admin/analytics',
    $uri === '/admin/products'   => 'admin/products',
    $uri === '/admin/products/new' => 'admin/products-new',
    $uri === '/admin/banners'    => 'admin/banners',
    $uri === '/admin/pricing'    => 'admin/pricing',
    $uri === '/admin/coupons'    => 'admin/coupons',
    $uri === '/admin/customers'  => 'admin/customers',
    $uri === '/admin/settings'   => 'admin/settings',
    $uri === '/admin/email-settings' => 'admin/email-settings',
    $uri === '/admin/integrations' => 'admin/integrations',
    $uri === '/admin/audit-logs' => 'admin/audit-logs',
    default                      => null,
};

if ($adminPage) { view($adminPage, compact('settingsMap')); exit; }

http_response_code(404);
view('404');
exit;
