<?php
$pageHero = is_array($pageHero ?? null) ? $pageHero : [];
$heroKey = trim((string)($pageHero['key'] ?? ''));
$heroTitle = trim((string)($pageHero['title'] ?? '')) ?: 'RCS PRINT';
$heroSubtitle = trim((string)($pageHero['subtitle'] ?? ''));
$heroEyebrow = trim((string)($pageHero['eyebrow'] ?? 'RCS PRINT'));
$heroBreadcrumbs = is_array($pageHero['breadcrumbs'] ?? null) ? $pageHero['breadcrumbs'] : [];
$heroFallbackImage = trim((string)($pageHero['fallback_image'] ?? '/assets/images/sample-products/brochures/brochures-2.svg'));
$heroBgImage = $heroFallbackImage;

if ($heroKey !== '') {
    try {
        $row = Database::row("SELECT background_image FROM page_heroes WHERE page_key=? AND is_active=1 LIMIT 1", [$heroKey]);
        $candidate = trim((string)($row['background_image'] ?? ''));
        if ($candidate !== '') {
            $heroBgImage = $candidate;
        }
    } catch (\Throwable) {
        // Keep fallback image when the admin-managed table is not available yet.
    }
}

if (empty($heroBreadcrumbs)) {
    $heroBreadcrumbs = [
        ['label' => 'Home', 'url' => '/'],
        ['label' => $heroTitle, 'url' => null],
    ];
}
?>
<section class="page-hero-banner" aria-labelledby="pageHeroTitle">
  <div class="page-hero-bg" style="background-image:url('<?= htmlspecialchars($heroBgImage, ENT_QUOTES, 'UTF-8') ?>')" aria-hidden="true"></div>
  <div class="page-hero-overlay" aria-hidden="true"></div>
  <div class="page-hero-inner">
    <nav class="page-hero-breadcrumb" aria-label="Breadcrumb">
      <?php foreach ($heroBreadcrumbs as $index => $crumb): ?>
        <?php
          $label = trim((string)($crumb['label'] ?? ''));
          $url = trim((string)($crumb['url'] ?? ''));
          if ($label === '') continue;
        ?>
        <?php if ($index > 0): ?><span class="page-hero-separator">›</span><?php endif; ?>
        <?php if ($url !== ''): ?>
          <a href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a>
        <?php else: ?>
          <span class="page-hero-current"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>
    <p class="page-hero-eyebrow"><?= htmlspecialchars($heroEyebrow, ENT_QUOTES, 'UTF-8') ?></p>
    <h1 id="pageHeroTitle"><?= htmlspecialchars($heroTitle, ENT_QUOTES, 'UTF-8') ?></h1>
    <?php if ($heroSubtitle !== ''): ?><p class="page-hero-subtitle"><?= htmlspecialchars($heroSubtitle, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
  </div>
</section>
