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


// SEO Sitemap — dynamic URLs for launch indexing
if ($uri === '/sitemap.xml' && $method === 'GET') {
    $baseUrl = defined('APP_URL') ? rtrim((string)APP_URL, '/') : 'https://print.rcsgraphic.com';
    $today = date('Y-m-d');
    $urls = [];
    $addUrl = static function (string $path, string $priority = '0.80', string $changefreq = 'weekly', ?string $lastmod = null) use (&$urls, $baseUrl, $today): void {
        $path = '/' . ltrim($path, '/');
        $urls[$path] = [
            'loc' => $baseUrl . $path,
            'lastmod' => $lastmod ?: $today,
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    };

    $addUrl('/', '1.00', 'daily');
    $addUrl('/products', '0.90', 'daily');
    $addUrl('/categories', '0.80', 'weekly');
    $addUrl('/blogs', '0.70', 'weekly');
    $addUrl('/about', '0.70', 'monthly');
    $addUrl('/portfolio', '0.70', 'monthly');
    $addUrl('/contact', '0.70', 'monthly');
    $addUrl('/shipping-policy', '0.40', 'monthly');
    $addUrl('/refund-return-policy', '0.40', 'monthly');
    $addUrl('/terms-and-conditions', '0.40', 'monthly');
    $addUrl('/privacy-policy', '0.40', 'monthly');

    try {
        foreach (\Catalog\ProductCatalog::categories() as $category) {
            $slug = trim((string)($category['slug'] ?? ''));
            if ($slug !== '') $addUrl('/category/' . rawurlencode($slug), '0.80', 'weekly');
        }
    } catch (\Throwable $e) {
        error_log('Sitemap categories unavailable: ' . $e->getMessage());
    }

    try {
        foreach (\Catalog\ProductCatalog::all(true) as $product) {
            $slug = trim((string)($product['slug'] ?? ''));
            if ($slug !== '') $addUrl('/product/' . rawurlencode($slug), '0.90', 'weekly', substr((string)($product['updated_at'] ?? ''), 0, 10) ?: null);
        }
    } catch (\Throwable $e) {
        error_log('Sitemap products unavailable: ' . $e->getMessage());
    }

    try {
        $blogs = Database::rows("SELECT slug, updated_at, published_at FROM blogs WHERE is_active = 1 ORDER BY published_at DESC, id DESC");
        foreach ($blogs as $blog) {
            $slug = trim((string)($blog['slug'] ?? ''));
            if ($slug !== '') {
                $lastmod = substr((string)($blog['updated_at'] ?? $blog['published_at'] ?? ''), 0, 10) ?: null;
                $addUrl('/blog/' . rawurlencode($slug), '0.70', 'monthly', $lastmod);
            }
        }
    } catch (\Throwable $e) {
        error_log('Sitemap blogs unavailable: ' . $e->getMessage());
    }

    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as $url) {
        echo "  <url>\n";
        echo '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
        echo '    <lastmod>' . htmlspecialchars($url['lastmod'], ENT_XML1, 'UTF-8') . "</lastmod>\n";
        echo '    <changefreq>' . htmlspecialchars($url['changefreq'], ENT_XML1, 'UTF-8') . "</changefreq>\n";
        echo '    <priority>' . htmlspecialchars($url['priority'], ENT_XML1, 'UTF-8') . "</priority>\n";
        echo "  </url>\n";
    }
    echo '</urlset>';
    exit;
}

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
    $blogPage = max(1, (int)($_GET['page'] ?? 1));
    $blogSearch = trim((string)($_GET['search'] ?? ''));
    $blogCategory = trim((string)($_GET['category'] ?? ''));
    $blogsPerPage = 6;
    $blogTotal = 0;
    $blogTotalPages = 1;
    $blogCategories = [];
    $popularBlogs = [];
    $featuredBlog = null;

    try {
        $settings = Database::rows("SELECT `key`, value FROM settings");
        $settingsMap = array_column($settings, 'value', 'key');

        $where = ['is_active = 1'];
        $params = [];
        if ($blogSearch !== '') {
            $where[] = '(title LIKE ? OR excerpt LIKE ? OR category LIKE ?)';
            $like = '%' . $blogSearch . '%';
            array_push($params, $like, $like, $like);
        }
        if ($blogCategory !== '') {
            $where[] = 'category = ?';
            $params[] = $blogCategory;
        }
        $whereSql = implode(' AND ', $where);

        $countRow = Database::row("SELECT COUNT(*) AS total FROM blogs WHERE $whereSql", $params);
        $blogTotal = (int)($countRow['total'] ?? 0);
        $blogTotalPages = max(1, (int)ceil($blogTotal / $blogsPerPage));
        $blogPage = min($blogPage, $blogTotalPages);
        $offset = ($blogPage - 1) * $blogsPerPage;

        $blogs = Database::rows(
            "SELECT id,title,slug,excerpt,featured_image,image_alt,category,badge_theme,author_name,published_at,is_featured
             FROM blogs
             WHERE $whereSql
             ORDER BY is_featured DESC, sort_order ASC, published_at DESC, id DESC
             LIMIT $blogsPerPage OFFSET $offset",
            $params
        );

        $featuredBlog = Database::row(
            "SELECT id,title,slug,excerpt,featured_image,image_alt,category,badge_theme,author_name,published_at
             FROM blogs
             WHERE is_active = 1
             ORDER BY is_featured DESC, sort_order ASC, published_at DESC, id DESC
             LIMIT 1"
        );

        $blogCategories = Database::rows(
            "SELECT category, COUNT(*) AS total
             FROM blogs
             WHERE is_active = 1 AND category <> ''
             GROUP BY category
             ORDER BY total DESC, category ASC"
        );

        $popularBlogs = Database::rows(
            "SELECT id,title,slug,featured_image,image_alt,category,published_at
             FROM blogs
             WHERE is_active = 1
             ORDER BY is_featured DESC, published_at DESC, id DESC
             LIMIT 6"
        );
    } catch (\Throwable $e) {
        error_log('Blogs listing error: ' . $e->getMessage());
        $blogs = [];
        $settingsMap = [];
        $blogCategories = [];
        $popularBlogs = [];
        $featuredBlog = null;
        $blogTotal = 0;
        $blogTotalPages = 1;
        $blogPage = 1;
    }
    view('blogs', compact('blogs', 'settingsMap', 'featuredBlog', 'blogCategories', 'popularBlogs', 'blogSearch', 'blogCategory', 'blogPage', 'blogTotalPages', 'blogTotal'));
    exit;
}

// Blog Detail Page — /blog/{slug}
if (preg_match('#^/blog/([a-z0-9\-]+)$#', $uri, $m) && $method === 'GET') {
    try {
        $blog = Database::row(
            "SELECT * FROM blogs WHERE slug = ? AND is_active = 1 LIMIT 1",
            [$m[1]]
        );
        $relatedBlogs = [];
        $blogCategories = [];
        $popularBlogs = [];
        $previousBlog = null;
        $nextBlog = null;
        if ($blog) {
            $relatedBlogs = Database::rows(
                "SELECT id,title,slug,excerpt,featured_image,image_alt,category,published_at
                 FROM blogs
                 WHERE is_active = 1 AND slug <> ?
                 ORDER BY CASE WHEN category = ? THEN 0 ELSE 1 END, is_featured DESC, sort_order ASC, published_at DESC, id DESC
                 LIMIT 10",
                [$m[1], (string)($blog['category'] ?? '')]
            );
            $blogCategories = Database::rows(
                "SELECT category, COUNT(*) AS total
                 FROM blogs
                 WHERE is_active = 1 AND category <> ''
                 GROUP BY category
                 ORDER BY total DESC, category ASC"
            );
            $popularBlogs = Database::rows(
                "SELECT id,title,slug,featured_image,image_alt,category,published_at
                 FROM blogs
                 WHERE is_active = 1 AND slug <> ?
                 ORDER BY is_featured DESC, published_at DESC, id DESC
                 LIMIT 5",
                [$m[1]]
            );
            $publishedForNav = (string)($blog['published_at'] ?? '1970-01-01 00:00:00');
            $idForNav = (int)($blog['id'] ?? 0);
            $previousBlog = Database::row(
                "SELECT id,title,slug
                 FROM blogs
                 WHERE is_active = 1 AND (published_at < ? OR (published_at = ? AND id < ?))
                 ORDER BY published_at DESC, id DESC
                 LIMIT 1",
                [$publishedForNav, $publishedForNav, $idForNav]
            );
            $nextBlog = Database::row(
                "SELECT id,title,slug
                 FROM blogs
                 WHERE is_active = 1 AND (published_at > ? OR (published_at = ? AND id > ?))
                 ORDER BY published_at ASC, id ASC
                 LIMIT 1",
                [$publishedForNav, $publishedForNav, $idForNav]
            );
        }
        $settings = Database::rows("SELECT `key`, value FROM settings");
        $settingsMap = array_column($settings, 'value', 'key');
    } catch (\Throwable $e) {
        error_log('Blog detail error: ' . $e->getMessage());
        $blog = null;
        $relatedBlogs = [];
        $blogCategories = [];
        $popularBlogs = [];
        $previousBlog = null;
        $nextBlog = null;
        $settingsMap = [];
    }

    if (!$blog) { http_response_code(404); view('404'); exit; }

    view('blog-detail', compact('blog', 'relatedBlogs', 'blogCategories', 'popularBlogs', 'previousBlog', 'nextBlog', 'settingsMap'));
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
    '/portfolio' => 'portfolio',
    '/shipping-policy' => 'shipping-policy',
    '/refund-return-policy' => 'refund-return-policy',
    '/terms-and-conditions' => 'terms-and-conditions',
    '/privacy-policy' => 'privacy-policy',
];
if (isset($sitePageRoutes[$uri]) && $method === 'GET') {
    $sitePages = require APP_PATH . '/data/site_pages.php';
    $page = $sitePages[$sitePageRoutes[$uri]] ?? null;
    if (!$page) { http_response_code(404); view('404'); exit; }
    $aboutReviews = [];
    try {
        $settings = Database::rows("SELECT `key`, value FROM settings");
        $settingsMap = array_column($settings, 'value', 'key');
        if ($sitePageRoutes[$uri] === 'about') {
            $aboutReviews = \Reviews\ProductReview::featured(3);
        }
    } catch (\Throwable) {
        $settingsMap = [];
        $aboutReviews = [];
    }
    if ($sitePageRoutes[$uri] === 'portfolio') {
        $portfolioCategories = [];
        $portfolioItems = [];
        $portfolioCategory = trim((string)($_GET['category'] ?? ''));
        $portfolioPage = max(1, (int)($_GET['page'] ?? 1));
        $portfolioPerPage = 12;
        $portfolioTotalPages = 1;
        try {
            $portfolioCategories = Database::rows("SELECT id, name, slug, icon, sort_order FROM portfolio_categories WHERE is_active=1 ORDER BY sort_order ASC, name ASC");
            $categoryRow = null;
            if ($portfolioCategory !== '') {
                $categoryRow = Database::row("SELECT id, slug FROM portfolio_categories WHERE slug=? AND is_active=1 LIMIT 1", [$portfolioCategory]);
                if (!$categoryRow) {
                    $portfolioCategory = '';
                }
            }
            $where = ["pi.is_active=1"];
            $params = [];
            if ($categoryRow) {
                $where[] = "pi.category_id=?";
                $params[] = (int)$categoryRow['id'];
            }
            $whereSql = implode(' AND ', $where);
            $totalRow = Database::row("SELECT COUNT(*) AS total FROM portfolio_items pi WHERE {$whereSql}", $params) ?: [];
            $totalItems = (int)($totalRow['total'] ?? 0);
            $portfolioTotalPages = max(1, (int)ceil($totalItems / $portfolioPerPage));
            $portfolioPage = min($portfolioPage, $portfolioTotalPages);
            $offset = max(0, ($portfolioPage - 1) * $portfolioPerPage);
            $portfolioItems = Database::rows(
                "SELECT pi.*, pc.name AS category_name, pc.slug AS category_slug, pc.icon AS category_icon
                 FROM portfolio_items pi
                 LEFT JOIN portfolio_categories pc ON pc.id = pi.category_id
                 WHERE {$whereSql}
                 ORDER BY pi.is_featured DESC, pi.sort_order ASC, pi.created_at DESC, pi.id DESC
                 LIMIT {$portfolioPerPage} OFFSET {$offset}",
                $params
            );
        } catch (\Throwable) {
            $portfolioCategories = null;
            $portfolioItems = null;
            $portfolioCategory = '';
            $portfolioPage = 1;
            $portfolioTotalPages = 1;
        }
        view('portfolio', compact('settingsMap', 'portfolioCategories', 'portfolioItems', 'portfolioCategory', 'portfolioPage', 'portfolioTotalPages'));
        exit;
    }
    view('info-page', compact('page', 'settingsMap', 'aboutReviews'));
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
