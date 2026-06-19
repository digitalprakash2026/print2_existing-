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

if ($uri === '/api/auth/account-exists' && $method === 'POST') {
    $email = strtolower(trim((string)($body['email'] ?? '')));
    $phone = trim((string)($body['phone'] ?? ''));
    if ($email === '' || $phone === '') {
        json(['ok' => false, 'msg' => 'Email and phone are required.'], 422);
    }
    $row = \Database::row(
        "SELECT id FROM users WHERE is_active = 1 AND (email = ? OR phone = ?) LIMIT 1",
        [$email, $phone]
    );
    json(['ok' => true, 'exists' => !empty($row)]);
}

if ($uri === '/api/profile' && $method === 'GET') {
    \Auth\Auth::require();
    $user = \Auth\Auth::user();
    $profile = \Auth\Auth::getProfile((int)$user['id']);
    json(['ok' => true, 'profile' => $profile]);
}

if ($uri === '/api/profile' && in_array($method, ['POST', 'PUT'], true)) {
    \Auth\Auth::require();
    $user = \Auth\Auth::user();
    $result = \Auth\Auth::updateProfile((int)$user['id'], $body);
    json($result, ($result['ok'] ?? false) ? 200 : 422);
}

if ($uri === '/api/profile/password' && $method === 'POST') {
    \Auth\Auth::require();
    $user = \Auth\Auth::user();
    $result = \Auth\Auth::changePassword((int)$user['id'], $body);
    json($result, ($result['ok'] ?? false) ? 200 : 422);
}

if (preg_match('#^/api/design-approvals/(\d+)/approve$#', $uri, $m) && $method === 'POST') {
    \Auth\Auth::require();
    $user = \Auth\Auth::user();
    $result = \Orders\OrderManager::customerDesignDecision((int)$m[1], (int)$user['id'], 'approve', trim((string)($body['note'] ?? '')));
    json($result, ($result['ok'] ?? false) ? 200 : 422);
}

if (preg_match('#^/api/design-approvals/(\d+)/revision$#', $uri, $m) && $method === 'POST') {
    \Auth\Auth::require();
    $user = \Auth\Auth::user();
    $result = \Orders\OrderManager::customerDesignDecision((int)$m[1], (int)$user['id'], 'revision', trim((string)($body['message'] ?? '')));
    json($result, ($result['ok'] ?? false) ? 200 : 422);
}

if ($uri === '/api/contact-leads' && $method === 'POST') {
    $result = \Leads\ContactLeadManager::create($body);
    json($result, ($result['ok'] ?? false) ? 200 : 422);
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

// ── Wishlist ─────────────────────────────────────────────────

if ($uri === '/api/wishlist' && $method === 'GET') {
    \Auth\Auth::require();
    $user = \Auth\Auth::user();
    json(['ok' => true, 'items' => \Wishlist\Wishlist::itemsForUser((int)$user['id'])]);
}

if ($uri === '/api/wishlist/toggle' && $method === 'POST') {
    \Auth\Auth::require();
    $user = \Auth\Auth::user();
    $result = \Wishlist\Wishlist::toggle((int)$user['id'], (int)($body['product_id'] ?? 0));
    json($result, ($result['ok'] ?? false) ? 200 : 422);
}

if (preg_match('#^/api/wishlist/(\\d+)$#', $uri, $m) && $method === 'DELETE') {
    \Auth\Auth::require();
    $user = \Auth\Auth::user();
    $result = \Wishlist\Wishlist::remove((int)$user['id'], (int)$m[1]);
    json($result, ($result['ok'] ?? false) ? 200 : 422);
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

if (preg_match('#^/api/cart/update/(.+)$#', $uri, $m) && $method === 'POST') {
    json(\Cart\Cart::updateQuantity($m[1], (int)($body['quantity'] ?? 0)));
}

if ($uri === '/api/cart/clear' && $method === 'POST') {
    \Cart\Cart::clear();
    json(['ok' => true]);
}

if ($uri === '/api/coupon/validate' && $method === 'POST') {
    $items    = \Cart\Cart::get();
    $subtotal = array_sum(array_column($items, 'total_price'));
    $result   = \Cart\Pricing::validateCoupon($body['code'] ?? '', $subtotal, $items);
    json($result);
}

// ── Artwork Upload ────────────────────────────────────────────

if ($uri === '/api/upload/artwork' && $method === 'POST') {
    $user = \Auth\Auth::user();
    $userId = (int)($user['id'] ?? 0);

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
        error_log("Unusual MIME type upload: {$mime} from user {$userId}");
    }

    $dir = UPLOAD_PATH . '/artwork/' . date('Y/m/');
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $filename = uniqid('art_', true) . '.' . $ext;
    $filepath = $dir . $filename;
    $publicPath = '/uploads/artwork/' . date('Y/m/') . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        json(['ok' => false, 'msg' => 'Upload failed'], 500);
    }

    $ownerForInsert = $userId > 0 ? $userId : null;
    try {
        $fileId = Database::insert(
            "INSERT INTO artwork_files (uploaded_by, filename, original_name, file_path, mime_type, file_size, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())",
            [$ownerForInsert, $filename, $file['name'], $publicPath, $mime, $file['size']]
        );
    } catch (\Throwable $e) {
        // Some schemas may have `uploaded_by` as NOT NULL.
        // Fallback to 0 for guests and keep flow working.
        if ($userId === 0) {
            $fileId = Database::insert(
                "INSERT INTO artwork_files (uploaded_by, filename, original_name, file_path, mime_type, file_size, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())",
                [0, $filename, $file['name'], $publicPath, $mime, $file['size']]
            );
        } else {
            throw $e;
        }
    }

    if ($userId === 0) {
        $_SESSION['guest_artwork_ids'] = $_SESSION['guest_artwork_ids'] ?? [];
        $_SESSION['guest_artwork_ids'][] = (int)$fileId;
        $_SESSION['guest_artwork_ids'] = array_values(array_unique(array_map('intval', $_SESSION['guest_artwork_ids'])));
    }

    json(['ok' => true, 'artwork_id' => $fileId, 'filename' => $file['name'], 'path' => $publicPath]);
}

// ── Orders ────────────────────────────────────────────────────

if ($uri === '/api/orders/place' && $method === 'POST') {
    $ensure = \Auth\Auth::ensureCheckoutUser($body['customer'] ?? []);
    if (!$ensure['ok']) json($ensure, 400);
    json(\Orders\OrderManager::place([
        ...$body,
        'shipping' => $body['shipping'] ?? null,
    ]));
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

    $razorpayOrderId = trim((string)($body['razorpay_order_id'] ?? ''));
    $razorpayPaymentId = trim((string)($body['razorpay_payment_id'] ?? ''));
    $razorpaySignature = trim((string)($body['razorpay_signature'] ?? ''));

    if ($razorpayOrderId === '' || $razorpayPaymentId === '' || $razorpaySignature === '') {
        json(['ok' => false, 'msg' => 'Missing payment verification fields'], 422);
    }

    // Idempotency: if this payment ID is already recorded, return existing order directly.
    $existingOrder = \Payment\Razorpay::findOrderByPaymentId($razorpayPaymentId);
    if ($existingOrder) {
        json(['ok' => true, 'already_processed' => true, 'order' => $existingOrder]);
    }

    // 1) Place order first (records in DB)
    $placeResult = \Orders\OrderManager::place([
        'coupon_code'    => $body['coupon_code'] ?? null,
        'payment_method' => 'razorpay',
        'payment_status' => 'pending',
        'billing'        => $body['billing'] ?? null,
        'shipping'       => $body['shipping'] ?? null,
    ]);

    if (!$placeResult['ok']) json($placeResult);

    // 2) Verify signature + mark payment success
    $verifyResult = \Payment\Razorpay::handleSuccess(
        (int)$placeResult['order']['id'],
        $razorpayOrderId,
        $razorpayPaymentId,
        $razorpaySignature
    );

    // Defensive: always try to return order object so frontend can redirect reliably.
    if (($verifyResult['ok'] ?? false) && empty($verifyResult['order'])) {
        $verifyResult['order'] = \Orders\OrderManager::getOrder((int)$placeResult['order']['id']);
    }

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
        'billing'        => $body['billing'] ?? null,
        'shipping'       => $body['shipping'] ?? null,
    ]);

    if ($result['ok']) {
        // Update status to whatsapp_pending
        \Orders\OrderManager::updateStatus($result['order']['id'], 'whatsapp_pending', 'Placed via WhatsApp');
    }

    json($result);
}


// ── Product Reviews ─────────────────────────────────────────

if ($uri === '/api/reviews/my' && $method === 'GET') {
    \Auth\Auth::require();
    $user = \Auth\Auth::user();
    json([
        'ok' => true,
        'reviewable_items' => \Reviews\ProductReview::reviewableItemsForUser((int)$user['id']),
        'reviews' => \Reviews\ProductReview::userReviews((int)$user['id']),
    ]);
}

if ($uri === '/api/reviews' && $method === 'POST') {
    \Auth\Auth::require();
    $user = \Auth\Auth::user();
    $result = \Reviews\ProductReview::createOrUpdate((int)$user['id'], $body);
    json($result, ($result['ok'] ?? false) ? 200 : 422);
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


// ── Chatbot ───────────────────────────────────────────────────

if ($uri === '/api/chat/ask' && $method === 'POST') {
    $question = trim((string)($body['question'] ?? ''));
    $history = $body['history'] ?? [];
    if ($question === '') {
        json(['ok' => false, 'msg' => 'Question is required'], 422);
    }

    $result = \Chatbot\SupportBot::ask($question, is_array($history) ? $history : []);
    json($result, $result['ok'] ? 200 : 400);
}

json(['ok' => false, 'msg' => 'API endpoint not found'], 404);
