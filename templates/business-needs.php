<?php
$businessNeeds = is_array($businessNeeds ?? null) ? $businessNeeds : [];
$settingsMap = is_array($settingsMap ?? null) ? $settingsMap : [];
$pageTitle = 'All Sectors — RCS Graphic';
$pageDesc = 'Browse printing solutions by business sector and find products matched to your industry needs.';
include INCLUDE_PATH . '/partials/head.php';
include INCLUDE_PATH . '/partials/header.php';
?>
<main class="business-page all-business-page">
  <?php
  $pageHero = [
    'key' => 'business_sectors',
    'title' => 'All Sectors',
    'subtitle' => 'Choose your industry and discover print products curated for your daily business needs.',
    'eyebrow' => 'Business Printing Solutions',
    'breadcrumbs' => [
      ['label' => 'Home', 'url' => '/'],
      ['label' => 'Business Sectors', 'url' => null],
    ],
    'fallback_image' => '/assets/img/categories/print-category.svg',
  ];
  include INCLUDE_PATH . '/partials/page-hero.php';
  ?>
  <section class="shop-cat-section business-needs-section all-business-section" aria-labelledby="allBusinessTitle">
    <div class="shop-cat-container">
      <div class="shop-cat-head"><h2 class="shop-cat-title" id="allBusinessTitle">All <span>Sectors</span></h2><a href="/products" class="shop-cat-all">View All Products</a></div>
      <?php if (!$businessNeeds): ?>
        <div class="business-empty-state"><strong>No business sectors added yet</strong><span>Please check back soon for curated print collections.</span></div>
      <?php else: ?>
        <div class="business-sector-grid">
          <?php foreach ($businessNeeds as $idx => $need):
            $name = trim((string)($need['name'] ?? 'Business Sector'));
            $slug = trim((string)($need['slug'] ?? ''));
            $icon = trim((string)($need['icon'] ?? '🏢')) ?: '🏢';
            $img = trim((string)($need['image_path'] ?? '')) ?: '/assets/img/categories/print-category.svg';
            $href = $slug !== '' ? '/business/' . rawurlencode($slug) : '/business';
          ?>
          <article class="shop-cat-card business-need-card" data-reveal data-reveal-delay="<?= ($idx % 4) * 50 ?>">
            <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>" class="shop-cat-link business-need-link">
              <div class="shop-cat-img business-need-img"><img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" loading="lazy" onerror="this.src='/assets/img/categories/print-category.svg'"></div>
              <div class="shop-cat-body business-need-body compact"><span class="shop-cat-icon" aria-hidden="true"><?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?></span><span class="shop-cat-name"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></span></div>
            </a>
          </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>
<?php include INCLUDE_PATH . '/partials/site-footer.php'; ?>
<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
