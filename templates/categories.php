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

<main class="all-cat-page" style="margin-top:var(--hh);padding:26px 0 84px;background:var(--bg);min-height:calc(100vh - var(--hh))">
  <div class="container">
    <div class="breadcrumb" style="padding-top:4px">
      <a href="/">Home</a><span>/</span>
      <span style="color:var(--ink);font-weight:600">All Categories</span>
    </div>

    <section class="all-cat-hero" style="padding:26px 0 22px;border-bottom:1px solid var(--border);margin-bottom:28px">
      <div class="sec-ey">Browse Categories</div>
      <h1 style="font-family:var(--fd);font-size:clamp(26px,4.5vw,42px);font-weight:800;color:var(--ink);line-height:1.1;margin:0 0 8px">
        Shop By Printing Category
      </h1>
      <p style="font-size:14px;color:var(--text2);margin:0;max-width:720px;line-height:1.7">
        Choose a category to explore products, pricing options and custom print solutions from RCS Print.
      </p>
    </section>

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
