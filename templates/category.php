<?php
/**
 * category.php — Category Page
 * Shows all products belonging to a single category
 * Route: /category/{slug}
 * Variables: $category (array), $products (array), $categories (array), $settingsMap (array)
 */
$pageTitle = htmlspecialchars($category['name']) . ' Printing — RCS Graphic';
$pageDesc  = 'Browse all ' . htmlspecialchars($category['name']) . ' products at RCS Graphic. Premium quality printing in Rajkot.';

$settingsMap = [];
try {
    $settings    = Database::rows("SELECT `key`, value FROM settings");
    $settingsMap = array_column($settings, 'value', 'key');
} catch (\Throwable) {}

$bizWa = $settingsMap['biz_whatsapp'] ?? '919876543210';
$productCount = count($products ?? []);
$categoryName = (string)($category['name'] ?? 'Category');
$categoryImage = trim((string)($category['image_path'] ?? ''));
$categoryImageAlt = trim((string)($category['image_alt'] ?? '')) ?: ($categoryName . ' printing');
$categoryDescription = trim((string)($category['description'] ?? ''));

include INCLUDE_PATH . '/partials/head.php';
include INCLUDE_PATH . '/partials/header.php';
?>

<main class="all-cat-page subcat-page">
  <section class="all-cat-hero-banner" aria-labelledby="categoryTitle">
    <div class="all-cat-hero-copy">
      <nav class="all-cat-crumb" aria-label="Breadcrumb">
        <a href="/">Home</a><span>›</span><a href="/categories">All Categories</a><span>›</span><span><?= htmlspecialchars($categoryName) ?></span>
      </nav>
      <h1 id="categoryTitle"><?= htmlspecialchars($categoryName) ?> Products</h1>
      <p><?= $categoryDescription !== '' ? htmlspecialchars($categoryDescription) : 'Premium quality printing products for every business need.' ?></p>
    </div>
  </section>

  <div class="container all-cat-content">
    <?php if (empty($products)): ?>
      <div class="cat-detail-empty">
        <div class="cat-detail-empty-icon">🖨️</div>
        <div class="cat-detail-empty-title">No products yet in <?= htmlspecialchars($categoryName) ?></div>
        <p>We're adding products soon. Contact us for custom requirements.</p>
        <div class="cat-detail-empty-actions">
          <a href="/categories" class="btn btn-outline">← Back to Categories</a>
          <a href="https://wa.me/<?= htmlspecialchars($bizWa) ?>" target="_blank" rel="noopener" class="btn btn-green">💬 WhatsApp Us</a>
        </div>
      </div>
    <?php else: ?>
      <section class="all-cat-results" aria-label="Browse <?= htmlspecialchars($categoryName) ?> products">
        <div class="all-cat-toolbar">
          <p>Showing 1–<?= (int)$productCount ?> of <?= (int)$productCount ?> products</p>
          <label>Sort by:
            <select aria-label="Sort <?= htmlspecialchars($categoryName) ?> products">
              <option>Popularity</option>
              <option>Newest</option>
              <option>Price Low to High</option>
            </select>
          </label>
        </div>

        <div class="all-cat-grid" id="catProductsGrid">
          <?php foreach ($products as $p):
            $img = trim((string)($p['primary_image'] ?? ''));
            $name = (string)($p['name'] ?? 'Product');
            $slug = (string)($p['slug'] ?? '');
            $desc = trim((string)($p['description'] ?? '')) ?: ('Premium ' . strtolower($name) . ' printing with custom sizes and finishing options.');
            $minP = (float)($p['min_price'] ?? 0);
          ?>
            <a class="all-cat-card all-cat-card-orange" href="/product/<?= htmlspecialchars($slug) ?>">
              <div class="all-cat-img">
                <?php if ($img !== ''): ?>
                  <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($name) ?>" loading="lazy" onerror="this.src='https://placehold.co/400x300/EEF3FD/1A56E8?text=<?= urlencode($name) ?>'">
                <?php else: ?>
                  <div class="shop-cat-fallback" aria-hidden="true">📦</div>
                <?php endif; ?>
              </div>
              <div class="all-cat-body">
                <span class="all-cat-icon" aria-hidden="true"><i class="fa-solid fa-print"></i></span>
                <h2><?= htmlspecialchars($name) ?></h2>
                <p><?= htmlspecialchars($desc) ?></p>
                <strong><?= $minP > 0 ? ('Starting from ₹' . number_format($minP)) : 'Price on request' ?></strong>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="all-cat-usp" aria-label="RCS Print benefits">
        <div class="why-print-panel">
          <article class="why-print-item"><div class="why-print-icon why-print-purple"><i class="fa-solid fa-truck-fast" aria-hidden="true"></i></div><div class="why-print-copy"><h3>Fast Delivery</h3><p>On-time delivery always guaranteed.</p></div></article>
          <article class="why-print-item"><div class="why-print-icon why-print-orange"><i class="fa-solid fa-pen-ruler" aria-hidden="true"></i></div><div class="why-print-copy"><h3>Free Design Support</h3><p>Professional design support at no extra cost.</p></div></article>
          <article class="why-print-item"><div class="why-print-icon why-print-green"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></div><div class="why-print-copy"><h3>Premium Quality</h3><p>Best quality materials and printing.</p></div></article>
          <article class="why-print-item"><div class="why-print-icon why-print-purple"><i class="fa-solid fa-tags" aria-hidden="true"></i></div><div class="why-print-copy"><h3>Affordable Pricing</h3><p>Low price with the best value.</p></div></article>
          <article class="why-print-item"><div class="why-print-icon why-print-orange"><i class="fa-solid fa-cube" aria-hidden="true"></i></div><div class="why-print-copy"><h3>Bulk Order Specialist</h3><p>Special prices for bulk requirements.</p></div></article>
        </div>
      </section>
    <?php endif; ?>
  </div>
</main>

<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
