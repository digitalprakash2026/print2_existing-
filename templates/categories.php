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
      <div class="all-cat-grid" aria-label="All printing categories">
        <?php foreach ($activeCategories as $cat):
          $name = (string)($cat['name'] ?? 'Category');
          $image = trim((string)($cat['image_path'] ?? ''));
          $alt = trim((string)($cat['image_alt'] ?? '')) ?: ($name . ' category image');
          $count = (int)($cat['product_count'] ?? 0);
        ?>
          <a class="all-cat-card" href="/category/<?= htmlspecialchars($cat['slug'] ?? '') ?>">
            <div class="all-cat-img">
              <?php if ($image !== ''): ?>
                <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($alt) ?>" loading="lazy">
              <?php else: ?>
                <div class="shop-cat-fallback" aria-hidden="true"><?= htmlspecialchars($cat['icon'] ?? '📦') ?></div>
              <?php endif; ?>
            </div>
            <div class="all-cat-body">
              <span class="all-cat-icon" aria-hidden="true"><?= htmlspecialchars($cat['icon'] ?? '📦') ?></span>
              <div>
                <h2><?= htmlspecialchars($name) ?></h2>
                <p><?= $count ?> product<?= $count === 1 ? '' : 's' ?> available</p>
              </div>
              <span class="all-cat-arrow" aria-hidden="true">→</span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
