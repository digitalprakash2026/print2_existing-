<?php
$settingsMap = is_array($settingsMap ?? null) ? $settingsMap : [];
$item = is_array($portfolioItem ?? null) ? $portfolioItem : [];
$images = is_array($portfolioImages ?? null) ? $portfolioImages : [];
$related = is_array($relatedPortfolioItems ?? null) ? $relatedPortfolioItems : [];
$title = (string)($item['title'] ?? 'Portfolio Work');
$mainImage = (string)($item['main_image'] ?: '/assets/images/sample-products/business-cards/business-cards-1.svg');
if (!$images && $mainImage !== '') {
    $images[] = ['image_path' => $mainImage, 'image_alt' => ($item['image_alt'] ?? $title), 'caption' => $title];
}
$pageTitle = $title . ' — Portfolio — RCS Print';
$pageDesc = (string)($item['short_description'] ?: 'View this premium RCS Print portfolio project with detailed images and print information.');
$pagePreloadImage = $mainImage;
$pageSchema = [[
  '@context' => 'https://schema.org',
  '@type' => 'CreativeWork',
  'name' => $title,
  'description' => $pageDesc,
  'image' => $mainImage,
  'url' => (defined('APP_URL') ? rtrim((string)APP_URL, '/') : '') . '/portfolio/work/' . ($item['slug'] ?? ''),
]];
include INCLUDE_PATH . '/partials/head.php';
include INCLUDE_PATH . '/partials/header.php';
?>
<main class="portfolio-page portfolio-detail-page">
  <section class="portfolio-detail-hero">
    <div class="portfolio-container portfolio-detail-hero-grid">
      <div>
        <p class="portfolio-breadcrumb"><a href="/">Home</a> <span>›</span> <a href="/portfolio">Portfolio</a><?php if (!empty($item['category_slug'])): ?> <span>›</span> <a href="/portfolio/category/<?= rawurlencode((string)$item['category_slug']) ?>"><?= htmlspecialchars((string)$item['category_name'], ENT_QUOTES, 'UTF-8') ?></a><?php endif; ?></p>
        <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($pageDesc, ENT_QUOTES, 'UTF-8') ?></p>
        <div class="portfolio-detail-meta">
          <?php if (!empty($item['category_name'])): ?><span><i class="fa-regular fa-folder-open"></i><?= htmlspecialchars((string)$item['category_name'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
          <?php if (!empty($item['client_name'])): ?><span><i class="fa-regular fa-user"></i><?= htmlspecialchars((string)$item['client_name'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
          <?php if (!empty($item['project_type'])): ?><span><i class="fa-solid fa-print"></i><?= htmlspecialchars((string)$item['project_type'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
          <?php if (!empty($item['project_date'])): ?><span><i class="fa-regular fa-calendar"></i><?= htmlspecialchars(date('M d, Y', strtotime((string)$item['project_date'])), ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
        </div>
      </div>
      <div class="portfolio-detail-hero-image"><img src="<?= htmlspecialchars($mainImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($item['image_alt'] ?: $title), ENT_QUOTES, 'UTF-8') ?>"></div>
    </div>
  </section>

  <section class="portfolio-detail-content">
    <div class="portfolio-container portfolio-detail-layout">
      <article class="portfolio-detail-copy">
        <h2>Project Overview</h2>
        <p><?= nl2br(htmlspecialchars((string)($item['description'] ?: $item['short_description'] ?: 'A premium print project crafted with attention to material quality, brand consistency and final presentation.'), ENT_QUOTES, 'UTF-8')) ?></p>
        <?php if (!empty($item['tags'])): ?><div class="portfolio-tags"><?php foreach (array_filter(array_map('trim', explode(',', (string)$item['tags']))) as $tag): ?><span><?= htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; ?></div><?php endif; ?>
      </article>
      <aside class="portfolio-detail-card"><h3>Need similar work?</h3><p>Share your requirement and our team will help with design, print material and finish selection.</p><a href="/contact">Get Free Design</a></aside>
    </div>
  </section>

  <section class="portfolio-gallery-section">
    <div class="portfolio-container">
      <div class="portfolio-section-head"><h2>Project <span>Gallery</span></h2><div class="portfolio-dots" aria-hidden="true"><span></span><span></span><span></span></div></div>
      <div class="portfolio-gallery-grid">
        <?php foreach ($images as $img): ?>
          <figure><img src="<?= htmlspecialchars((string)$img['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($img['image_alt'] ?: $title), ENT_QUOTES, 'UTF-8') ?>" loading="lazy"><?php if (!empty($img['caption'])): ?><figcaption><?= htmlspecialchars((string)$img['caption'], ENT_QUOTES, 'UTF-8') ?></figcaption><?php endif; ?></figure>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php if ($related): ?>
  <section class="portfolio-work portfolio-related-work"><div class="portfolio-container"><div class="portfolio-section-head"><h2>Related <span>Works</span></h2></div><div class="portfolio-grid">
    <?php foreach ($related as $rel): ?><a class="portfolio-card portfolio-linked-card" href="/portfolio/work/<?= rawurlencode((string)$rel['slug']) ?>"><div class="portfolio-card-img"><img src="<?= htmlspecialchars((string)($rel['main_image'] ?: '/assets/images/sample-products/business-cards/business-cards-1.svg'), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)$rel['title'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy"></div><div class="portfolio-card-body"><h3><?= htmlspecialchars((string)$rel['title'], ENT_QUOTES, 'UTF-8') ?></h3><p><i class="fa-regular fa-folder-open"></i><?= htmlspecialchars((string)($rel['category_name'] ?? 'Portfolio'), ENT_QUOTES, 'UTF-8') ?></p></div></a><?php endforeach; ?>
  </div></div></section>
  <?php endif; ?>
</main>
<?php include INCLUDE_PATH . '/partials/site-footer.php'; ?>
<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
