<?php
// ─────────────────────────────────────────────────────────────
//  RCS Graphic — API Router
//  All endpoints return JSON
// ─────────────────────────────────────────────────────────────

declare(strict_types=1);

if (!str_starts_with($uri, '/api/')) return; // Not an API route

header('Content-Type: application/json');

// Parse JSON body
$body = [];
if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true) ?? $_POST;
}

// ── Auth ──────────────────────────────────────────────────────

if ($uri === '/api/auth/login' && $method === 'POST') {
    $result = \Auth\Auth::login(
        trim($body['identifier'] ?? ''),
        $body['password'] ?? ''
    );
    json($result);
}

if ($uri === '/api/auth/register' && $method === 'POST') {
    $result = \Auth\Auth::register($body);
    json($result);
}

if ($uri === '/api/auth/logout' && $method === 'POST') {
    \Auth\Auth::logout();
    json(['ok' => true]);
}

if ($uri === '/api/auth/me' && $method === 'GET') {
    json(['ok' => true, 'user' => \Auth\Auth::user()]);
}

// ── Products ──────────────────────────────────────────────────

if ($uri === '/api/products' && $method === 'GET') {
    $q    = $_GET['q'] ?? '';
    $cat  = $_GET['category'] ?? '';
    $prods = $q
        ? \Catalog\ProductCatalog::search($q)
        : \Catalog\ProductCatalog::all();
    if ($cat) {
        $prods = array_filter($prods, fn($p) => $p['category_name'] === $cat);
    }
    json(['ok' => true, 'products' => array_values($prods)]);
}

if ($uri === '/api/categories' && $method === 'GET') {
    json(['ok' => true, 'categories' => \Catalog\ProductCatalog::categories()]);
}

if (preg_match('#^/api/products/(\d+)/pricing$#', $uri, $m) && $method === 'GET') {
    $data = \Cart\Pricing::productPricingData((int)$m[1]);
    json(['ok' => true, ...$data]);
}

// Live price calculation
if ($uri === '/api/price/calculate' && $method === 'POST') {
    $result = \Cart\Pricing::calculate(
        (int)($body['product_id'] ?? 0),
        (int)($body['quality_id'] ?? 0),
        (int)($body['quantity'] ?? 0),
        $body['attribute_selections'] ?? [],
        $body['design_choice'] ?? 'upload'
    );
    json($result);
}

// ── Cart ──────────────────────────────────────────────────────

if ($uri === '/api/cart' && $method === 'GET') {
    $items  = \Cart\Cart::get();
    $coupon = $_GET['coupon'] ?? null;
    $totals = \Cart\Cart::totals($items, $coupon);
    json(['ok' => true, 'items' => $items, 'totals' => $totals]);
}

if ($uri === '/api/cart/add' && $method === 'POST') {
    $result = \Cart\Cart::add($body);
    json($result);
}

if (preg_match('#^/api/cart/remove/(.+)$#', $uri, $m) && $method === 'DELETE') {
    json(\Cart\Cart::remove($m[1]));
}

if ($uri === '/api/cart/clear' && $method === 'POST') {
    \Cart\Cart::clear();
    json(['ok' => true]);
}

if ($uri === '/api/coupon/validate' && $method === 'POST') {
    $items    = \Cart\Cart::get();
    $subtotal = array_sum(array_column($items, 'total_price'));
    $result   = \Cart\Pricing::validateCoupon($body['code'] ?? '', $subtotal);
    json($result);
}

// ── Artwork Upload ────────────────────────────────────────────

if ($uri === '/api/upload/artwork' && $method === 'POST') {
    \Auth\Auth::require();
    $user = \Auth\Auth::user();

    if (empty($_FILES['artwork'])) {
        json(['ok' => false, 'msg' => 'No file uploaded'], 400);
    }

    $file    = $_FILES['artwork'];
    $maxMb   = (int)Database::setting('upload_max_mb', env('UPLOAD_MAX_SIZE_MB', '50'));
    $maxSize = $maxMb * 1024 * 1024;
    $allowed = explode(',', Database::setting('upload_allowed_ext', 'pdf,ai,eps,png,jpg,jpeg,psd,cdr'));

    if ($file['size'] > $maxSize) {
        json(['ok' => false, 'msg' => "File too large. Max {$maxMb}MB."], 400);
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        json(['ok' => false, 'msg' => "File type .{$ext} not allowed."], 400);
    }

    // Validate MIME to prevent file spoofing
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $safeMimes = [
        'application/pdf', 'image/png', 'image/jpeg', 'image/gif', 'image/svg+xml',
        'image/tiff', 'application/zip', 'application/x-zip-compressed',
        'application/postscript', 'application/illustrator',
    ];
    // Allow unknown MIME for specialized print files (AI, CDR, PSD)
    if (!in_array($mime, $safeMimes) && !str_starts_with($mime, 'image/')) {
        // Still allow; log it
        error_log("Unusual MIME type upload: {$mime} from user {$user['id']}");
    }

    $dir = UPLOAD_PATH . '/artwork/' . date('Y/m/');
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $filename = uniqid('art_', true) . '.' . $ext;
    $filepath = $dir . $filename;
    $publicPath = '/uploads/artwork/' . date('Y/m/') . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        json(['ok' => false, 'msg' => 'Upload failed'], 500);
    }

    $fileId = Database::insert(
        "INSERT INTO artwork_files (uploaded_by, filename, original_name, file_path, mime_type, file_size, created_at)
         VALUES (?, ?, ?, ?, ?, ?, NOW())",
        [$user['id'], $filename, $file['name'], $publicPath, $mime, $file['size']]
    );

    json(['ok' => true, 'artwork_id' => $fileId, 'filename' => $file['name'], 'path' => $publicPath]);
}

// ── Orders ────────────────────────────────────────────────────

if ($uri === '/api/orders/place' && $method === 'POST') {
    $ensure = \Auth\Auth::ensureCheckoutUser($body['customer'] ?? []);
    if (!$ensure['ok']) json($ensure, 400);
    json(\Orders\OrderManager::place($body));
}

if ($uri === '/api/orders' && $method === 'GET') {
    \Auth\Auth::require();
    $user   = \Auth\Auth::user();
    $orders = \Orders\OrderManager::getUserOrders($user['id']);
    json(['ok' => true, 'orders' => $orders]);
}

// ── Razorpay ──────────────────────────────────────────────────

if ($uri === '/api/payment/create-order' && $method === 'POST') {
    $ensure = \Auth\Auth::ensureCheckoutUser($body['customer'] ?? []);
    if (!$ensure['ok']) json($ensure, 400);
    $items  = \Cart\Cart::get();
    $coupon = $body['coupon_code'] ?? null;
    $totals = \Cart\Cart::totals($items, $coupon);

    if ($totals['total'] <= 0) json(['ok' => false, 'msg' => 'Invalid order total']);

    $user   = \Auth\Auth::user();
    $result = \Payment\Razorpay::createOrder(
        $totals['total'],
        'rcpt_' . time(),
        ['customer_name' => $user['name'], 'customer_phone' => $user['phone']]
    );
    json($result);
}

if ($uri === '/api/payment/verify' && $method === 'POST') {
    $ensure = \Auth\Auth::ensureCheckoutUser($body['customer'] ?? []);
    if (!$ensure['ok']) json($ensure, 400);

    // 1. Place order first (records in DB)
    $placeResult = \Orders\OrderManager::place([
        'coupon_code'    => $body['coupon_code'] ?? null,
        'payment_method' => 'razorpay',
        'payment_status' => 'pending',
    ]);

    if (!$placeResult['ok']) json($placeResult);

    // 2. Verify Razorpay signature (server-side)
    $verifyResult = \Payment\Razorpay::handleSuccess(
        (int)$placeResult['order']['id'],
        $body['razorpay_order_id'] ?? '',
        $body['razorpay_payment_id'] ?? '',
        $body['razorpay_signature'] ?? ''
    );

    json($verifyResult);
}

// ── WhatsApp Order (no payment) ───────────────────────────────

if ($uri === '/api/orders/whatsapp' && $method === 'POST') {
    $ensure = \Auth\Auth::ensureCheckoutUser($body['customer'] ?? []);
    if (!$ensure['ok']) json($ensure, 400);
    $result = \Orders\OrderManager::place([
        'coupon_code'    => $body['coupon_code'] ?? null,
        'payment_method' => 'whatsapp',
        'payment_status' => 'pending',
        'notes'          => $body['notes'] ?? '',
    ]);

    if ($result['ok']) {
        // Update status to whatsapp_pending
        \Orders\OrderManager::updateStatus($result['order']['id'], 'whatsapp_pending', 'Placed via WhatsApp');
    }

    json($result);
}

// ── Settings (public read-only) ───────────────────────────────

if ($uri === '/api/settings/public' && $method === 'GET') {
    $publicKeys = ['biz_name', 'biz_phone', 'biz_whatsapp', 'biz_email', 'biz_address', 'gst_percent', 'razorpay_key_id'];
    $settings = [];
    foreach ($publicKeys as $k) {
        $settings[$k] = Database::setting($k, '');
    }
    json(['ok' => true, 'settings' => $settings]);
}

json(['ok' => false, 'msg' => 'API endpoint not found'], 404);
