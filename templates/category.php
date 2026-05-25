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
$themeClasses = ['purple', 'orange', 'green', 'purple', 'orange', 'green'];

include INCLUDE_PATH . '/partials/head.php';
include INCLUDE_PATH . '/partials/header.php';
?>

<main class="all-cat-page cat-detail-page">
  <section class="all-cat-hero-banner cat-detail-hero" aria-labelledby="categoryTitle">
    <div class="all-cat-hero-copy">
      <nav class="all-cat-crumb" aria-label="Breadcrumb">
        <a href="/">Home</a><span>›</span><a href="/categories">All Categories</a><span>›</span><span><?= htmlspecialchars($categoryName) ?></span>
      </nav>
      <h1 id="categoryTitle"><?= htmlspecialchars($categoryName) ?> Products</h1>
      <p><?= $categoryDescription !== '' ? htmlspecialchars($categoryDescription) : 'Premium quality printing products for every business need.' ?></p>
    </div>
    <div class="all-cat-hero-visual cat-detail-hero-visual" aria-hidden="true">
      <?php if ($categoryImage !== ''): ?>
        <img src="<?= htmlspecialchars($categoryImage) ?>" alt="<?= htmlspecialchars($categoryImageAlt) ?>" loading="eager">
      <?php else: ?>
        <img src="/assets/img/categories/all-categories-hero.svg" alt="" loading="eager">
      <?php endif; ?>
    </div>
  </section>

  <div class="container all-cat-content cat-detail-content">
    <?php if (!empty($categories)): ?>
      <nav class="cat-detail-browse" aria-label="Browse categories">
        <span>Browse:</span>
        <?php foreach ($categories as $cat):
          $isActive = ((int)($cat['id'] ?? 0) === (int)($category['id'] ?? 0));
        ?>
          <a href="/category/<?= htmlspecialchars($cat['slug'] ?? '') ?>" class="chip <?= $isActive ? 'on' : '' ?>">
            <?= htmlspecialchars($cat['icon'] ?? '') ?> <?= htmlspecialchars($cat['name'] ?? 'Category') ?>
          </a>
        <?php endforeach; ?>
      </nav>
    <?php endif; ?>

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
      <section class="all-cat-results cat-detail-results" aria-label="<?= htmlspecialchars($categoryName) ?> products">
        <div class="all-cat-toolbar">
          <p>Showing <?= (int)$productCount ?> products in <?= htmlspecialchars($categoryName) ?></p>
        </div>

        <div class="prod-grid" id="catProductsGrid">
          <?php foreach ($products as $p):
            $img = trim((string)($p['primary_image'] ?? ''));
            $name = (string)($p['name'] ?? 'Product');
            $slug = (string)($p['slug'] ?? '');
            $desc = trim((string)($p['description'] ?? '')) ?: ('Premium ' . strtolower($name) . ' printing with custom sizes and finishing options.');
            $minP = (float)($p['min_price'] ?? 0);
          ?>
            <a href="/product/<?= htmlspecialchars($slug) ?>" class="pc">
              <div class="pc-img">
                <?php if ($img !== ''): ?>
                  <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($name) ?>" loading="lazy" onerror="this.src='https://placehold.co/400x300/EEF3FD/1A56E8?text=<?= urlencode($name) ?>'">
                <?php else: ?>
                  <div class="shop-cat-fallback" aria-hidden="true"><?= htmlspecialchars($category['icon'] ?? '📦') ?></div>
                <?php endif; ?>
                <div class="pc-img-badge">
                  <span class="badge b-blue"><?= htmlspecialchars($categoryName) ?></span>
                </div>
              </div>
              <div class="pc-body">
                <div class="pc-cat"><?= htmlspecialchars($categoryName) ?></div>
                <div class="pc-name"><?= htmlspecialchars($name) ?></div>
                <div class="pc-desc"><?= htmlspecialchars($desc) ?></div>
                <div class="pc-foot">
                  <div>
                    <div class="pc-from">Starting from</div>
                    <div class="pc-price"><?= $minP > 0 ? ('₹' . number_format($minP)) : 'Price on request' ?></div>
                  </div>
                  <div class="pc-arr">
                    <svg viewBox="0 0 24 24"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg>
                  </div>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="cat-detail-whatsapp" aria-label="Custom print help">
        <div>
          <h2>Need a custom <?= htmlspecialchars($categoryName) ?> print?</h2>
          <p>Tell us your requirements — bulk pricing, custom sizes, special finishes.</p>
        </div>
        <a href="https://wa.me/<?= htmlspecialchars($bizWa) ?>?text=<?= urlencode('Hi! I need custom ' . $categoryName . ' printing. Please share details.') ?>" target="_blank" rel="noopener" class="btn btn-green">
          💬 Chat on WhatsApp
        </a>
      </section>
    <?php endif; ?>
  </div>
</main>

<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
