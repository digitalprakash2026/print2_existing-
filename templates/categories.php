<?php
/**
 * categories.php — All Categories Page
 * Shows every active category with its square category image.
 * Route: /categories
 */
$pageTitle = 'All Categories — RCS Graphic';
$pageDesc  = 'Browse all printing categories at RCS Graphic — visiting cards, brochures, flyers, posters and more.';

$activeCategories = array_values(array_filter($categories ?? [], static fn($cat) => (int)($cat['is_active'] ?? 1) === 1));
$bizWa = $settingsMap['biz_whatsapp'] ?? '919876543210';
$visibleCount = count($activeCategories);
$totalItems = array_sum(array_map(static fn($cat) => max(0, (int)($cat['product_count'] ?? 0)), $activeCategories));
$totalItems = $totalItems > 0 ? $totalItems : $visibleCount;
$categoryDescriptions = [
  'visiting-cards' => 'Make a lasting first impression with our premium quality business cards.',
  'business-cards' => 'Make a lasting first impression with our premium quality business cards.',
  'flyers' => 'Promote your business with eye-catching and professional flyers.',
  'brochures' => 'Showcase your business or services with high-quality brochures.',
  'posters' => 'High-impact posters for events, promotions and branding.',
  'diaries' => 'Custom diaries for your brand or personal use.',
  'calendars' => 'Stay ahead all year with our custom printed calendars.',
  'stationery-more' => 'Letterheads, envelopes, ID cards and more stationery items.',
  'stationery' => 'Letterheads, envelopes, ID cards and more stationery items.',
];
$categoryThemeClasses = ['purple', 'orange', 'orange', 'orange', 'purple', 'orange', 'purple', 'green'];

include INCLUDE_PATH . '/partials/head.php';
include INCLUDE_PATH . '/partials/header.php';
?>

<main class="all-cat-page">
  <section class="all-cat-hero-banner" aria-labelledby="allCatTitle">
    <div class="all-cat-hero-copy">
      <nav class="all-cat-crumb" aria-label="Breadcrumb">
        <a href="/">Home</a><span>›</span><span>All Categories</span>
      </nav>
      <h1 id="allCatTitle">All Categories</h1>
      <p>Premium Quality Printing for Every Need</p>
    </div>
    <div class="all-cat-hero-visual" aria-hidden="true">
      <img src="/assets/img/categories/all-categories-hero.svg" alt="" loading="eager">
    </div>
  </section>

  <div class="container all-cat-content">
    <?php if (empty($activeCategories)): ?>
      <div style="text-align:center;padding:80px 20px;background:#fff;border:1px solid var(--border);border-radius:18px">
        <div style="font-size:48px;margin-bottom:12px">🗂️</div>
        <div style="font-size:17px;font-weight:800;color:var(--ink);margin-bottom:6px">No categories yet</div>
        <p style="font-size:13px;color:var(--text2);margin-bottom:20px">Please check back soon or contact us on WhatsApp.</p>
        <a href="https://wa.me/<?= htmlspecialchars($bizWa) ?>" target="_blank" rel="noopener" class="btn btn-green">💬 WhatsApp Us</a>
      </div>
    <?php else: ?>
      <section class="all-cat-shop" aria-label="Browse all categories">
        <details class="all-cat-filter-panel" open>
          <summary><span>Categories &amp; Filters</span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary>
          <aside class="all-cat-sidebar" aria-label="Category filters">
          <div class="all-cat-side-box all-cat-side-categories">
            <h2>Categories</h2>
            <nav class="all-cat-side-list" aria-label="Category quick links">
              <?php foreach ($activeCategories as $idx => $cat): ?>
                <a href="/category/<?= htmlspecialchars($cat['slug'] ?? '') ?>" class="<?= $idx === 0 ? 'is-active' : '' ?>">
                  <?= htmlspecialchars($cat['name'] ?? 'Category') ?>
                </a>
              <?php endforeach; ?>
            </nav>
          </div>

          <div class="all-cat-side-box all-cat-filter-box">
            <h2>Filter By</h2>
            <div class="all-cat-filter-group">
              <h3>Product Type</h3>
              <label><input type="checkbox"> Standard</label>
              <label><input type="checkbox"> Premium</label>
              <label><input type="checkbox"> Luxury</label>
            </div>
            <div class="all-cat-filter-group">
              <h3>Paper Type</h3>
              <label><input type="checkbox"> Art Paper</label>
              <label><input type="checkbox"> Matte</label>
              <label><input type="checkbox"> Glossy</label>
              <label><input type="checkbox"> Textured</label>
            </div>
            <div class="all-cat-filter-group">
              <h3>Finishing</h3>
              <label><input type="checkbox"> Matt Lamination</label>
              <label><input type="checkbox"> Gloss Lamination</label>
              <label><input type="checkbox"> UV Coating</label>
              <label><input type="checkbox"> Spot UV</label>
              <label><input type="checkbox"> Foil Stamping</label>
            </div>
            <div class="all-cat-filter-group all-cat-price-filter">
              <h3>Price Range</h3>
              <div class="all-cat-price-line" aria-hidden="true"><span></span></div>
              <div class="all-cat-price-values"><span>₹0</span><span>₹5000+</span></div>
            </div>
            <button type="button" class="all-cat-apply-btn">Apply Filters <i class="fa-solid fa-sliders" aria-hidden="true"></i></button>
          </div>
          </aside>
        </details>

        <div class="all-cat-results">
          <div class="all-cat-toolbar">
            <p>Showing 1–<?= (int)$visibleCount ?> of <?= (int)$totalItems ?> products</p>
            <label>Sort by:
              <select aria-label="Sort categories">
                <option>Popularity</option>
                <option>Newest</option>
                <option>Name A-Z</option>
              </select>
            </label>
          </div>

          <div class="all-cat-grid" aria-label="All printing categories">
            <?php foreach ($activeCategories as $idx => $cat):
              $name = (string)($cat['name'] ?? 'Category');
              $slug = (string)($cat['slug'] ?? '');
              $image = trim((string)($cat['image_path'] ?? ''));
              $alt = trim((string)($cat['image_alt'] ?? '')) ?: ($name . ' category image');
              $count = (int)($cat['product_count'] ?? 0);
              $desc = $categoryDescriptions[$slug] ?? ('Explore premium quality ' . strtolower($name) . ' printing for your brand.');
              $theme = $categoryThemeClasses[$idx % count($categoryThemeClasses)];
            ?>
              <a class="all-cat-card all-cat-card-<?= htmlspecialchars($theme) ?>" href="/category/<?= htmlspecialchars($slug) ?>">
                <div class="all-cat-img">
                  <?php if ($image !== ''): ?>
                    <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($alt) ?>" loading="lazy">
                  <?php else: ?>
                    <div class="shop-cat-fallback" aria-hidden="true"><?= htmlspecialchars($cat['icon'] ?? '📦') ?></div>
                  <?php endif; ?>
                </div>
                <div class="all-cat-body">
                  <span class="all-cat-icon" aria-hidden="true"><i class="fa-solid fa-print"></i></span>
                  <h2><?= htmlspecialchars($name) ?></h2>
                  <p><?= htmlspecialchars($desc) ?></p>
                  <strong>(<?= $count ?> Item<?= $count === 1 ? '' : 's' ?>)</strong>
                </div>
              </a>
            <?php endforeach; ?>
          </div>

          <nav class="all-cat-pagination" aria-label="Category pagination">
            <span class="is-muted">←</span><strong>1</strong><span>2</span><span>3</span><span>4</span><span>→</span>
          </nav>
        </div>
      </section>

      <section class="all-cat-benefits" aria-label="RCS Print benefits">
        <div><span class="all-cat-benefit-icon is-purple"><i class="fa-solid fa-truck-fast"></i></span><strong>Fast Delivery</strong><p>On-time delivery always guaranteed.</p></div>
        <div><span class="all-cat-benefit-icon is-orange"><i class="fa-solid fa-pen-ruler"></i></span><strong>Free Design Support</strong><p>Professional design support at no extra cost.</p></div>
        <div><span class="all-cat-benefit-icon is-green"><i class="fa-solid fa-shield-halved"></i></span><strong>Premium Quality</strong><p>Best quality materials and printing.</p></div>
        <div><span class="all-cat-benefit-icon is-purple"><i class="fa-solid fa-tags"></i></span><strong>Affordable Pricing</strong><p>Low price with the best value.</p></div>
        <div><span class="all-cat-benefit-icon is-orange"><i class="fa-solid fa-cube"></i></span><strong>Bulk Order Specialist</strong><p>Special prices for bulk requirements.</p></div>
      </section>
    <?php endif; ?>
  </div>
</main>

<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
