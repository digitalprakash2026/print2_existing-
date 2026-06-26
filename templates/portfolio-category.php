<?php
$settingsMap = is_array($settingsMap ?? null) ? $settingsMap : [];
$category = is_array($category ?? null) ? $category : [];
$portfolioItems = is_array($portfolioItems ?? null) ? $portfolioItems : [];
$portfolioCategory = (string)($category['slug'] ?? ($portfolioCategory ?? ''));
$portfolioPage = max(1, (int)($portfolioPage ?? 1));
$portfolioTotalPages = max(1, (int)($portfolioTotalPages ?? 1));
$catName = (string)($category['name'] ?? 'Portfolio Category');
$pageTitle = ($category['meta_title'] ?? '') ?: ($catName . ' Portfolio — RCS Print');
$pageDesc = ($category['meta_description'] ?? '') ?: (($category['description'] ?? '') ?: 'Explore selected RCS Print portfolio work in ' . $catName . '.');
$pageSchema = [[
  '@context' => 'https://schema.org',
  '@type' => 'CollectionPage',
  'name' => $catName . ' Portfolio',
  'description' => $pageDesc,
  'url' => (defined('APP_URL') ? rtrim((string)APP_URL, '/') : '') . '/portfolio/category/' . $portfolioCategory,
]];
include INCLUDE_PATH . '/partials/head.php';
include INCLUDE_PATH . '/partials/header.php';
$heroImage = trim((string)($category['hero_image'] ?? ''));
?>
<main class="portfolio-page portfolio-category-page">
  <section class="portfolio-detail-hero">
    <div class="portfolio-container portfolio-detail-hero-grid">
      <div>
        <p class="portfolio-breadcrumb"><a href="/">Home</a> <span>›</span> <a href="/portfolio">Portfolio</a> <span>›</span> <?= htmlspecialchars($catName, ENT_QUOTES, 'UTF-8') ?></p>
        <h1><?= htmlspecialchars($catName, ENT_QUOTES, 'UTF-8') ?> <span>Portfolio</span></h1>
        <p><?= htmlspecialchars((string)($category['description'] ?? 'Explore premium print projects crafted by RCS Print for this category.'), ENT_QUOTES, 'UTF-8') ?></p>
      </div>
      <div class="portfolio-detail-hero-image"><img src="<?= htmlspecialchars($heroImage ?: '/assets/images/sample-products/brochures/brochures-1.svg', ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($catName, ENT_QUOTES, 'UTF-8') ?> portfolio"></div>
    </div>
  </section>

  <section class="portfolio-work">
    <div class="portfolio-container">
      <div class="portfolio-section-head"><h2>All <span><?= htmlspecialchars($catName, ENT_QUOTES, 'UTF-8') ?></span> Works</h2><div class="portfolio-dots" aria-hidden="true"><span></span><span></span><span></span></div></div>
      <?php if ($portfolioItems): ?>
      <div class="portfolio-grid portfolio-category-grid">
        <?php foreach ($portfolioItems as $item): ?>
          <?php $itemImage = (string)($item['main_image'] ?: '/assets/images/sample-products/business-cards/business-cards-1.svg'); ?>
          <a class="portfolio-card portfolio-linked-card" href="/portfolio/work/<?= rawurlencode((string)$item['slug']) ?>">
            <div class="portfolio-card-img"><img src="<?= htmlspecialchars($itemImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($item['image_alt'] ?: $item['title']), ENT_QUOTES, 'UTF-8') ?>" loading="lazy"></div>
            <div class="portfolio-card-body"><h3><?= htmlspecialchars((string)$item['title'], ENT_QUOTES, 'UTF-8') ?></h3><p><i class="fa-regular fa-folder-open"></i><?= htmlspecialchars($catName, ENT_QUOTES, 'UTF-8') ?></p></div>
          </a>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
        <div class="portfolio-empty-state">No works found in this category yet.</div>
      <?php endif; ?>
      <?php if ($portfolioPage < $portfolioTotalPages): ?>
        <div class="portfolio-load-wrap"><a class="portfolio-load-btn" href="/portfolio/category/<?= rawurlencode($portfolioCategory) ?>?page=<?= $portfolioPage + 1 ?>">Load More Works <i class="fa-solid fa-rotate-right"></i></a></div>
      <?php endif; ?>
    </div>
  </section>
</main>
<?php include INCLUDE_PATH . '/partials/site-footer.php'; ?>
<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
