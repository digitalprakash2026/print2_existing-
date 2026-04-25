<?php
// ═══════════════════════════════════════════════════════════════
//  RCS Graphic — index.php
//  Yeh file: domains/print.rcsgraphic.com/public_html/index.php
// ═══════════════════════════════════════════════════════════════

declare(strict_types=1);

$rootPath = __DIR__;

if (!is_dir($rootPath . '/config')) {
    die('\n    <div style="font-family:monospace;padding:30px;background:#1e1e1e;color:#ff6b6b;min-height:100vh">\n    <h2>⚠️ RCS Graphic — Setup Error</h2>\n    <p>Required <code>config/</code> folder not found at: <code>' . htmlspecialchars($rootPath) . '</code></p>\n    </div>\n    ');
}

require_once $rootPath . '/config/config.php';
require_once SRC_PATH . '/Database.php';

// ── Helper Functions ──────────────────────────────────────────
function view(string $template, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $csrf    = \Auth\Auth::csrfToken();
    $user    = \Auth\Auth::user();
    $isAdmin = \Auth\Auth::isAdmin();
    $uri     = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    $file = TMPL_PATH . '/' . $template . '.php';
    if (!file_exists($file)) {
        http_response_code(404);
        echo '<h1 style="font-family:sans-serif;padding:30px">Page not found: ' . htmlspecialchars($template) . '</h1>';
        exit;
    }
    include $file;
}

function json(mixed $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function redirect(string $url): never
{
    header('Location: ' . $url, true, 302);
    exit;
}

// ── Request Parse Karo ────────────────────────────────────────
$method = strtoupper($_SERVER['REQUEST_METHOD']);
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = '/' . trim($uri, '/');
if ($uri === '//') $uri = '/';

// ── Security Headers ──────────────────────────────────────────
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

// ═══════════════════════════════════════════════════════════════
//  ROUTES — Public Pages
// ═══════════════════════════════════════════════════════════════

// Home Page
if ($uri === '/' && $method === 'GET') {
    try {
        $categories  = \Catalog\ProductCatalog::categories();
        $products    = \Catalog\ProductCatalog::all();
        $settings    = Database::rows("SELECT `key`, value FROM settings");
        $settingsMap = array_column($settings, 'value', 'key');
    } catch (\Throwable $e) {
        error_log('Home error: ' . $e->getMessage());
        $categories = $products = [];
        $settingsMap = [];
    }
    view('home', compact('categories', 'products', 'settingsMap'));
    exit;
}

// Product Page
if (preg_match('#^/product/([a-z0-9\-]+)$#', $uri, $m) && $method === 'GET') {
    try { $product = \Catalog\ProductCatalog::bySlug($m[1]); }
    catch (\Throwable $e) { error_log('Product error: '.$e->getMessage()); $product = null; }

    if (!$product) { http_response_code(404); view('404'); exit; }

    try { $related = \Catalog\ProductCatalog::related((int)$product['id'], (int)$product['category_id']); }
    catch (\Throwable) { $related = []; }

    view('product', compact('product', 'related'));
    exit;
}

// ── Category Page — /category/{slug} ─────────────────────────
if (preg_match('#^/category/([a-z0-9\-]+)$#', $uri, $m) && $method === 'GET') {
    try {
        $result = \Catalog\ProductCatalog::byCategory($m[1]);
    } catch (\Throwable $e) {
        error_log('Category error: ' . $e->getMessage());
        $result = null;
    }

    if (!$result) { http_response_code(404); view('404'); exit; }

    $category = $result['category'];
    $products = $result['products'];

    try { $categories = \Catalog\ProductCatalog::categories(); }
    catch (\Throwable) { $categories = []; }

    view('category', compact('category', 'products', 'categories'));
    exit;
}

// ── All Products Page — /products ─────────────────────────────
if ($uri === '/products' && $method === 'GET') {
    try {
        $categories = \Catalog\ProductCatalog::categories();
        $products   = \Catalog\ProductCatalog::all();
        $settings   = Database::rows("SELECT `key`, value FROM settings");
        $settingsMap = array_column($settings, 'value', 'key');
    } catch (\Throwable $e) {
        error_log('Products page error: ' . $e->getMessage());
        $categories = $products = [];
        $settingsMap = [];
    }
    view('all-products', compact('categories', 'products', 'settingsMap'));
    exit;
}

// Login
if ($uri === '/login') {
    if ($method === 'GET') {
        if (\Auth\Auth::check()) redirect('/my-orders');
        view('auth/login');
        exit;
    }
}

// Register
if ($uri === '/register' && $method === 'GET') {
    if (\Auth\Auth::check()) redirect('/');
    view('auth/register');
    exit;
}

// Logout
if ($uri === '/logout') {
    \Auth\Auth::logout();
    redirect('/');
}

// My Orders
if ($uri === '/my-orders' && $method === 'GET') {
    \Auth\Auth::require();
    $user = \Auth\Auth::user();
    try { $orders = \Orders\OrderManager::getUserOrders((int)$user['id']); }
    catch (\Throwable) { $orders = []; }
    view('my-orders', compact('orders', 'user'));
    exit;
}

// My Profile
if ($uri === '/profile' && $method === 'GET') {
    \Auth\Auth::require();
    $user = \Auth\Auth::user();
    $profile = \Auth\Auth::getProfile((int)$user['id']);
    view('profile', compact('user', 'profile'));
    exit;
}

// Checkout
if ($uri === '/checkout' && $method === 'GET') {
    try {
        $cartItems = \Cart\Cart::get();
        $totals    = \Cart\Cart::totals($cartItems);
    } catch (\Throwable) {
        $cartItems = [];
        $totals = ['subtotal'=>0,'discount'=>0,'gst_pct'=>18,'gst_amt'=>0,'total'=>0];
    }
    if (empty($cartItems)) redirect('/');
    $user = \Auth\Auth::user();
    view('checkout', compact('cartItems', 'totals', 'user'));
    exit;
}

// Order Confirmation
if (preg_match('#^/order/confirm/([A-Z0-9]+)$#', $uri, $m) && $method === 'GET') {
    \Auth\Auth::require();
    try { $order = \Orders\OrderManager::getOrderByOrderId($m[1]); }
    catch (\Throwable) { $order = null; }
    if (!$order || (int)$order['user_id'] !== (int)\Auth\Auth::user()['id']) {
        http_response_code(404); view('404'); exit;
    }
    view('confirm', compact('order'));
    exit;
}

// Invoice Download
if (preg_match('#^/invoice/([A-Z0-9]+)$#', $uri, $m) && $method === 'GET') {
    \Auth\Auth::require();
    try { $order = \Orders\OrderManager::getOrderByOrderId($m[1]); }
    catch (\Throwable) { $order = null; }
    if (!$order || (int)$order['user_id'] !== (int)\Auth\Auth::user()['id']) {
        http_response_code(403); exit;
    }
    \Invoice\InvoiceGenerator::download($order);
    exit;
}

// ── API Routes ────────────────────────────────────────────────
if (str_starts_with($uri, '/api/')) {
    require_once APP_PATH . '/api/router.php';
    exit;
}

// ── Admin Routes ──────────────────────────────────────────────
if (str_starts_with($uri, '/admin')) {
    require_once ADMIN_PATH . '/router.php';
    exit;
}

// ── 404 ───────────────────────────────────────────────────────
http_response_code(404);
view('404');
