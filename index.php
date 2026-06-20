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
        $homeBanners = Database::rows("SELECT * FROM home_banners WHERE is_active=1 ORDER BY sort_order ASC, id DESC");
        $homeDeals   = [];
        try {
            $homeDeals = Database::rows("SELECT * FROM home_deals WHERE is_active=1 ORDER BY sort_order ASC, id DESC");
        } catch (\Throwable $e) {
            error_log('Home deals unavailable: ' . $e->getMessage());
        }
        $homeBlogs = [];
        try {
            $homeBlogs = Database::rows("SELECT * FROM blogs WHERE is_active=1 AND is_featured=1 ORDER BY sort_order ASC, published_at DESC, id DESC");
        } catch (\Throwable $e) {
            error_log('Home blogs unavailable: ' . $e->getMessage());
        }
        $settings    = Database::rows("SELECT `key`, value FROM settings");
        $settingsMap = array_column($settings, 'value', 'key');
        $homeReviews = \Reviews\ProductReview::featured(6);
    } catch (\Throwable $e) {
        error_log('Home error: ' . $e->getMessage());
        $categories = $products = [];
        $homeBanners = [];
        $homeDeals = [];
        $homeBlogs = [];
        $settingsMap = [];
        $homeReviews = [];
    }
    view('home', compact('categories', 'products', 'settingsMap', 'homeBanners', 'homeDeals', 'homeBlogs', 'homeReviews'));
    exit;
}


// Blogs Listing Page — /blogs
if ($uri === '/blogs' && $method === 'GET') {
    try {
        $blogs = Database::rows(
            "SELECT id,title,slug,excerpt,featured_image,image_alt,category,badge_theme,published_at
             FROM blogs
             WHERE is_active = 1
             ORDER BY is_featured DESC, sort_order ASC, published_at DESC, id DESC"
        );
        $settings = Database::rows("SELECT `key`, value FROM settings");
        $settingsMap = array_column($settings, 'value', 'key');
    } catch (\Throwable $e) {
        error_log('Blogs listing error: ' . $e->getMessage());
        $blogs = [];
        $settingsMap = [];
    }
    view('blogs', compact('blogs', 'settingsMap'));
    exit;
}

// Blog Detail Page — /blog/{slug}
if (preg_match('#^/blog/([a-z0-9\-]+)$#', $uri, $m) && $method === 'GET') {
    try {
        $blog = Database::row(
            "SELECT * FROM blogs WHERE slug = ? AND is_active = 1 LIMIT 1",
            [$m[1]]
        );
        $relatedBlogs = Database::rows(
            "SELECT id,title,slug,excerpt,featured_image,image_alt,category,published_at
             FROM blogs
             WHERE is_active = 1 AND slug <> ?
             ORDER BY is_featured DESC, sort_order ASC, published_at DESC, id DESC
             LIMIT 3",
            [$m[1]]
        );
        $settings = Database::rows("SELECT `key`, value FROM settings");
        $settingsMap = array_column($settings, 'value', 'key');
    } catch (\Throwable $e) {
        error_log('Blog detail error: ' . $e->getMessage());
        $blog = null;
        $relatedBlogs = [];
        $settingsMap = [];
    }

    if (!$blog) { http_response_code(404); view('404'); exit; }

    view('blog-detail', compact('blog', 'relatedBlogs', 'settingsMap'));
    exit;
}

// Product Page
if (preg_match('#^/product/([a-z0-9\-]+)$#', $uri, $m) && $method === 'GET') {
    try { $product = \Catalog\ProductCatalog::bySlug($m[1]); }
    catch (\Throwable $e) { error_log('Product error: '.$e->getMessage()); $product = null; }

    if (!$product) { http_response_code(404); view('404'); exit; }

    try { $relatedProducts = \Catalog\ProductCatalog::randomRecommendations((int)($product['id'] ?? 0), 5); }
    catch (\Throwable) { $relatedProducts = []; }
    $reviewSummary = \Reviews\ProductReview::summaryForProduct((int)$product['id']);
    $productReviews = \Reviews\ProductReview::approvedForProduct((int)$product['id'], 12);
    $wishlistActive = false;
    if ($user = \Auth\Auth::user()) {
        $wishlistActive = \Wishlist\Wishlist::isWishlisted((int)$user['id'], (int)($product['id'] ?? 0));
    }

    view('product', compact('product', 'relatedProducts', 'reviewSummary', 'productReviews', 'wishlistActive'));
    exit;
}

// ── All Categories Page — /categories ─────────────────────────
if ($uri === '/categories' && $method === 'GET') {
    try {
        $categories = \Catalog\ProductCatalog::categories();
        $settings = Database::rows("SELECT `key`, value FROM settings");
        $settingsMap = array_column($settings, 'value', 'key');
    } catch (\Throwable $e) {
        error_log('Categories page error: ' . $e->getMessage());
        $categories = [];
        $settingsMap = [];
    }
    view('categories', compact('categories', 'settingsMap'));
    exit;
}

// ── Category Page — /category/{slug} ─────────────────────────
if (preg_match('#^/category/([a-z0-9\-]+)$#', $uri, $m) && $method === 'GET') {
    try {
        $selectedFilters = \Catalog\ProductCatalog::normalizeFilterSelections($_GET['filters'] ?? []);
        $result = \Catalog\ProductCatalog::byCategory($m[1], $selectedFilters);
        $filterOptions = \Catalog\ProductCatalog::filterOptions();
    } catch (\Throwable $e) {
        error_log('Category error: ' . $e->getMessage());
        $result = null;
        $selectedFilters = $filterOptions = [];
    }

    if (!$result) { http_response_code(404); view('404'); exit; }

    $category = $result['category'];
    $products = $result['products'];

    try { $categories = \Catalog\ProductCatalog::categories(); }
    catch (\Throwable) { $categories = []; }

    view('category', compact('category', 'products', 'categories', 'filterOptions', 'selectedFilters'));
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
    redirect('/profile#orders');
}

// My Profile
if ($uri === '/profile' && $method === 'GET') {
    \Auth\Auth::require();
    $user = \Auth\Auth::user();
    $profile = \Auth\Auth::getProfile((int)$user['id']);
    try { $orders = \Orders\OrderManager::getUserOrders((int)$user['id']); }
    catch (\Throwable) { $orders = []; }
    $reviewableItems = \Reviews\ProductReview::reviewableItemsForUser((int)$user['id']);
    $myReviews = \Reviews\ProductReview::userReviews((int)$user['id']);
    $wishlistItems = \Wishlist\Wishlist::itemsForUser((int)$user['id']);
    $myDesigns = \Designs\UserDesigns::forUser((int)$user['id']);
    view('profile', compact('user', 'profile', 'orders', 'reviewableItems', 'myReviews', 'wishlistItems', 'myDesigns'));
    exit;
}


if (preg_match('#^/account/artwork/(\d+)/(download|view)$#', $uri, $m) && $method === 'GET') {
    \Auth\Auth::require();
    $user = \Auth\Auth::user();
    $file = \Designs\UserDesigns::downloadForUser((int)$user['id'], (int)$m[1]);
    if (!$file) {
        http_response_code(404);
        view('404');
        exit;
    }

    $relativePath = '/' . ltrim((string)($file['file_path'] ?? ''), '/');
    $fullPath = PUBLIC_PATH . $relativePath;
    $realPath = realpath($fullPath);
    $uploadRoot = realpath(UPLOAD_PATH);
    if (!$realPath || !$uploadRoot || !str_starts_with($realPath, $uploadRoot . DIRECTORY_SEPARATOR) || !is_file($realPath)) {
        http_response_code(404);
        exit('File missing');
    }

    $downloadName = basename((string)($file['original_name'] ?: $file['filename'] ?: 'artwork-file'));
    $downloadName = str_replace(['"', "\r", "\n"], '', $downloadName);
    $disposition = ($m[2] ?? 'download') === 'view' ? 'inline' : 'attachment';
    header('Content-Type: ' . (($file['mime_type'] ?? '') ?: 'application/octet-stream'));
    header('Content-Disposition: ' . $disposition . '; filename="' . $downloadName . '"');
    header('Content-Length: ' . filesize($realPath));
    readfile($realPath);
    exit;
}

if ($uri === '/profile/security' && $method === 'GET') {
    \Auth\Auth::require();
    redirect('/profile#security');
}

// Static information pages
$sitePageRoutes = [
    '/about' => 'about',
    '/shipping-policy' => 'shipping-policy',
    '/refund-return-policy' => 'refund-return-policy',
    '/terms-and-conditions' => 'terms-and-conditions',
    '/privacy-policy' => 'privacy-policy',
];
if (isset($sitePageRoutes[$uri]) && $method === 'GET') {
    $sitePages = require APP_PATH . '/data/site_pages.php';
    $page = $sitePages[$sitePageRoutes[$uri]] ?? null;
    if (!$page) { http_response_code(404); view('404'); exit; }
    try {
        $settings = Database::rows("SELECT `key`, value FROM settings");
        $settingsMap = array_column($settings, 'value', 'key');
    } catch (\Throwable) {
        $settingsMap = [];
    }
    view('info-page', compact('page', 'settingsMap'));
    exit;
}

if ($uri === '/contact' && $method === 'GET') {
    try {
        $settings = Database::rows("SELECT `key`, value FROM settings");
        $settingsMap = array_column($settings, 'value', 'key');
    } catch (\Throwable) {
        $settingsMap = [];
    }
    view('contact', compact('settingsMap'));
    exit;
}


// Cart Page
if ($uri === '/cart' && $method === 'GET') {
    try {
        $cartItems = \Cart\Cart::get();
        $totals    = \Cart\Cart::totals($cartItems);
    } catch (\Throwable) {
        $cartItems = [];
        $totals = ['subtotal'=>0,'discount'=>0,'gst_pct'=>18,'gst_amt'=>0,'total'=>0];
    }
    view('cart', compact('cartItems', 'totals'));
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
