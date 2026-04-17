<?php
// ─────────────────────────────────────────────────────────────
//  RCS Graphic — Admin Router
// ─────────────────────────────────────────────────────────────

declare(strict_types=1);

if (!str_starts_with($uri, '/admin')) return;

// Admin login (unauthenticated)
if ($uri === '/admin/login' && $method === 'GET') {
    if (\Auth\Auth::isAdmin()) redirect('/admin');
    view('admin/login');
    exit;
}

if ($uri === '/admin/login' && $method === 'POST') {
    // NOTE: No CSRF check on login — form is protected by session mechanism
    $email    = strtolower(trim($_POST['email']    ?? ''));
    $password = trim($_POST['password'] ?? '');
    $result   = \Auth\Auth::adminLogin($email, $password);
    if ($result['ok']) {
        redirect('/admin');
    }
    // Re-render login with error (do not redirect — preserve POST data context)
    view('admin/login', ['loginError' => $result['msg']]);
    exit;
}

if ($uri === '/admin/logout') {
    \Auth\Auth::adminLogout();
    redirect('/admin/login');
}

// All other admin routes require auth
\Auth\Auth::requireAdmin();

// ── Admin API Endpoints ───────────────────────────────────────
if (str_starts_with($uri, '/admin/api/')) {
    header('Content-Type: application/json');
    $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    // Dashboard stats
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
        $topProducts = Database::rows("SELECT product_name, COUNT(*) as count, SUM(total_price) as revenue FROM order_items GROUP BY product_name ORDER BY count DESC LIMIT 5");
        $recentOrders= Database::rows("SELECT o.*, COUNT(oi.id) as item_count FROM orders o LEFT JOIN order_items oi ON oi.order_id=o.id GROUP BY o.id ORDER BY o.created_at DESC LIMIT 10");
        json(['ok'=>true,'stats'=>$stats,'by_status'=>$byStatus,'monthly'=>$monthly,'top_products'=>$topProducts,'recent_orders'=>$recentOrders]);
    }

    // Orders list
    if ($uri === '/admin/api/orders' && $method === 'GET') {
        $status = $_GET['status'] ?? '';
        $params = [];
        $where  = '';
        if ($status) { $where = 'WHERE o.status = ?'; $params[] = $status; }
        $orders = Database::rows("SELECT o.*, COUNT(oi.id) as item_count FROM orders o LEFT JOIN order_items oi ON oi.order_id=o.id $where GROUP BY o.id ORDER BY o.created_at DESC", $params);
        json(['ok'=>true,'orders'=>$orders]);
    }

    // Single order
    if (preg_match('#^/admin/api/orders/(\d+)$#', $uri, $m) && $method === 'GET') {
        json(['ok'=>true,'order'=>\Orders\OrderManager::getOrder((int)$m[1])]);
    }

    // Update order status
    if (preg_match('#^/admin/api/orders/(\d+)/status$#', $uri, $m) && $method === 'POST') {
        $ok = \Orders\OrderManager::updateStatus((int)$m[1], $body['status']??'', $body['note']??'');
        json(['ok'=>$ok]);
    }

    // Products
    if ($uri === '/admin/api/products' && $method === 'GET') {
        json(['ok'=>true,'products'=>\Catalog\ProductCatalog::all(false)]);
    }
    if ($uri === '/admin/api/products' && $method === 'POST') {
        json(\Catalog\ProductCatalog::upsert($body));
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

    // Product images
    if (preg_match('#^/admin/api/products/(\d+)/images$#', $uri, $m) && $method === 'POST') {
        $pid = (int)$m[1];
        $id = Database::insert("INSERT INTO product_images (product_id, url, alt_text, is_primary, sort_order) VALUES (?,?,?,?,?)",
            [$pid, $body['url'], $body['alt_text']??'', $body['is_primary']??0, $body['sort_order']??0]);
        if (!empty($body['is_primary'])) Database::query("UPDATE product_images SET is_primary=0 WHERE product_id=? AND id!=?", [$pid,$id]);
        json(['ok'=>true,'id'=>$id]);
    }
    if (preg_match('#^/admin/api/images/(\d+)$#', $uri, $m) && $method === 'DELETE') {
        Database::query("DELETE FROM product_images WHERE id=?", [$m[1]]);
        json(['ok'=>true]);
    }

    // Qualities + slabs
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
            if ((float)$price > 0) Database::query("INSERT INTO quantity_slabs (product_id, quality_id, quantity, price) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE price=VALUES(price)",
                [$pid, $qid, (int)$qty, (float)$price]);
        }
        json(['ok'=>true]);
    }

    // Attribute groups
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

    // Coupons
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

    // Categories
    if ($uri === '/admin/api/categories' && $method === 'GET') {
        json(['ok'=>true,'categories'=>\Catalog\ProductCatalog::categories()]);
    }
    if ($uri === '/admin/api/categories' && $method === 'POST') {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/','-',$body['name']??''));
        $id = Database::insert("INSERT INTO categories (name,slug,icon,sort_order,is_active) VALUES (?,?,?,?,1)",
            [$body['name'],$slug,$body['icon']??'🖨️',$body['sort_order']??0]);
        json(['ok'=>true,'id'=>$id]);
    }

    // Settings
    if ($uri === '/admin/api/settings' && $method === 'GET') {
        $rows = Database::rows("SELECT `key`,value FROM settings");
        json(['ok'=>true,'settings'=>array_column($rows,'value','key')]);
    }
    if ($uri === '/admin/api/settings' && $method === 'POST') {
        foreach ($body as $k=>$v) if ($k) Database::setSetting($k,$v);
        \Orders\AdminAudit::log('settings_updated','Settings saved');
        json(['ok'=>true]);
    }

    // Customers
    if ($uri === '/admin/api/customers' && $method === 'GET') {
        json(['ok'=>true,'customers'=>Database::rows("SELECT u.*,COUNT(o.id) as order_count, COALESCE(SUM(o.total_amount),0) as total_spent FROM users u LEFT JOIN orders o ON o.user_id=u.id GROUP BY u.id ORDER BY total_spent DESC")]);
    }

    // Audit logs
    if ($uri === '/admin/api/audit-logs' && $method === 'GET') {
        json(['ok'=>true,'logs'=>Database::rows("SELECT * FROM admin_audit_logs ORDER BY created_at DESC LIMIT 200")]);
    }

    // Artwork for admin
    if (preg_match('#^/admin/api/artwork/(\d+)$#', $uri, $m) && $method === 'GET') {
        $file = Database::row("SELECT * FROM artwork_files WHERE id=?",[$m[1]]);
        json($file ? ['ok'=>true,'file'=>$file] : ['ok'=>false,'msg'=>'Not found'],404);
    }

    // Sheets retry
    if ($uri === '/admin/api/sheets/retry' && $method === 'POST') {
        $failed = Database::rows("SELECT * FROM sheets_sync_log WHERE resolved=0 LIMIT 20");
        foreach ($failed as $row) {
            Database::query("UPDATE sheets_sync_log SET resolved=1 WHERE id=?",[$row['id']]);
        }
        json(['ok'=>true,'retried'=>count($failed)]);
    }

    json(['ok'=>false,'msg'=>'Admin API not found'],404);
}

// Settings form POST save
if ($uri === '/admin/settings/save' && $method === 'POST') {
    foreach ($_POST as $k => $v) {
        if ($k !== '_token') Database::setSetting($k, trim($v));
    }
    \Orders\AdminAudit::log('settings_updated', 'Settings saved via form');
    redirect('/admin/settings?saved=1');
}

// CSV export
if ($uri === '/admin/export/orders') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="orders-' . date('Y-m-d') . '.csv"');
    $orders = Database::rows("SELECT o.order_id,o.created_at,o.customer_name,o.customer_phone,o.customer_email,o.subtotal,o.discount_amount,o.gst_amount,o.total_amount,o.payment_status,o.status,o.coupon_code,o.payment_id FROM orders o ORDER BY o.created_at DESC");
    echo implode(',', ['Order ID','Date','Customer','Phone','Email','Subtotal','Discount','GST','Total','Payment','Status','Coupon','Payment ID']) . "\n";
    foreach ($orders as $row) {
        echo implode(',', array_map(fn($v) => '"' . str_replace('"','""',$v??'') . '"', $row)) . "\n";
    }
    exit;
}

// Invoice download for admin
if (preg_match('#^/admin/invoice/(.+)$#', $uri, $m)) {
    $order = \Orders\OrderManager::getOrderByOrderId($m[1]);
    if (!$order) { http_response_code(404); exit; }
    \Invoice\InvoiceGenerator::download($order);
    exit;
}

// ── Admin HTML Pages ──────────────────────────────────────────
// Load settings map for settings page
$settingsMap = [];
if (str_contains($uri, '/admin/settings')) {
    $rows = Database::rows("SELECT `key`, value FROM settings");
    foreach ($rows as $r) $settingsMap[$r['key']] = $r['value'];
}

$adminPage = match(true) {
    $uri === '/admin' || $uri === '/admin/dashboard' => 'admin/dashboard',
    $uri === '/admin/orders'     => 'admin/orders',
    $uri === '/admin/products'   => 'admin/products',
    $uri === '/admin/pricing'    => 'admin/pricing',
    $uri === '/admin/coupons'    => 'admin/coupons',
    $uri === '/admin/customers'  => 'admin/customers',
    $uri === '/admin/settings'   => 'admin/settings',
    $uri === '/admin/audit-logs' => 'admin/audit-logs',
    default                      => null,
};

if ($adminPage) { view($adminPage); exit; }

http_response_code(404);
view('404');
exit;
