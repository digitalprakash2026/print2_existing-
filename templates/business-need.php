<?php
/** Business/Sector product listing page. */
$businessNeed = is_array($businessNeed ?? null) ? $businessNeed : [];
$businessProducts = is_array($businessProducts ?? null) ? $businessProducts : [];
$settingsMap = is_array($settingsMap ?? null) ? $settingsMap : [];
$needName = trim((string)($businessNeed['name'] ?? 'Business Need')) ?: 'Business Need';
$needIcon = trim((string)($businessNeed['icon'] ?? '🏢')) ?: '🏢';
$pageTitle = $needName . ' Products — RCS Graphic';
$pageDesc = trim((string)($businessNeed['description'] ?? 'Browse selected printing products for this business sector.'));
$bizPhone = htmlspecialchars($settingsMap['biz_phone'] ?? '+91 98765 43210');
$bizWa = htmlspecialchars($settingsMap['biz_whatsapp'] ?? '919876543210');
include INCLUDE_PATH . '/partials/head.php';
include INCLUDE_PATH . '/partials/header.php';
?>
<main class="business-page">
  <?php
  $pageHero = [
    'key' => 'business-' . preg_replace('/[^a-z0-9-]+/i', '-', (string)($businessNeed['slug'] ?? 'sector')),
    'title' => $needIcon . ' ' . $needName,
    'subtitle' => $pageDesc !== '' ? $pageDesc : 'Curated print products for this sector.',
    'eyebrow' => 'Shop by Business Need',
    'breadcrumbs' => [
      ['label' => 'Home', 'url' => '/'],
      ['label' => 'Business Needs', 'url' => '/#businessNeedsTitle'],
      ['label' => $needName, 'url' => null],
    ],
    'fallback_image' => '/assets/img/categories/all-categories-hero.svg',
  ];
  include INCLUDE_PATH . '/partials/page-hero.php';
  ?>
  <section class="ym-section business-need-products-page">
    <div class="ym-head">
      <h2 class="ym-title"><?= htmlspecialchars($needName) ?> <span>Products</span></h2>
      <a href="/categories" class="ym-view-all">Browse Categories</a>
    </div>
    <?php if (empty($businessProducts)): ?>
      <div class="business-empty-state"><strong>No products assigned yet</strong><span>Please check back soon or contact us for a custom quote.</span><a href="https://wa.me/<?= htmlspecialchars($bizWa) ?>" class="btn btn-green" target="_blank" rel="noopener">WhatsApp Us</a></div>
    <?php else: ?>
      <div class="ym-grid ym-product-grid">
        <?php foreach ($businessProducts as $idx => $product):
          $name = (string)($product['name'] ?? 'Print Product');
          $slug = (string)($product['slug'] ?? '');
          $href = $slug !== '' ? '/product/' . rawurlencode($slug) : '/categories';
          $img = trim((string)($product['primary_image'] ?? ($product['image_path'] ?? '')));
          $category = trim((string)($product['category_name'] ?? 'Print Product'));
          $price = (float)($product['min_price'] ?? 0);
        ?>
        <article class="ym-card ym-product-card" data-reveal data-reveal-delay="<?= ($idx % 3) * 60 ?>">
          <a class="ym-img ym-product-img" href="<?= htmlspecialchars($href) ?>">
            <?php if ($img !== ''): ?><img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($name) ?>" loading="lazy"><span class="ym-product-fallback" hidden><i class="fa-solid fa-print" aria-hidden="true"></i></span><?php else: ?><span class="ym-product-fallback"><i class="fa-solid fa-print" aria-hidden="true"></i></span><?php endif; ?>
          </a>
          <div class="ym-body"><div class="ym-cat"><i class="fa-solid fa-layer-group" aria-hidden="true"></i><?= htmlspecialchars($category) ?></div><h3 class="ym-name"><?= htmlspecialchars($name) ?></h3><div class="ym-foot"><div><div class="ym-from">Starting from</div><div class="ym-price">₹<?= $price > 0 ? number_format($price) : '—' ?></div></div><a href="<?= htmlspecialchars($href) ?>" class="ym-order">VIEW</a></div></div>
        </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</main>
<section class="quick-help-section" id="quick-help-sec" aria-label="Quick help and bulk order actions" data-reveal>
  <div class="quick-help-container"><div class="quick-help-bar"><a class="quick-help-item quick-help-call" href="tel:<?= preg_replace('/\D+/', '', $bizPhone) ?>"><span class="quick-help-icon"><i class="fa-solid fa-phone-volume" aria-hidden="true"></i></span><span class="quick-help-copy"><span>Need Help? Call Us</span><strong><?= $bizPhone ?></strong></span></a><button class="quick-help-item quick-help-whatsapp" type="button" onclick="window.open('https://wa.me/<?= $bizWa ?>','_blank')"><span class="quick-help-icon"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></span><span class="quick-help-copy"><strong>Chat with us on WhatsApp</strong><span>We are here to help!</span></span></button></div></div>
</section>
<?php include INCLUDE_PATH . '/partials/site-footer.php'; ?>
<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
