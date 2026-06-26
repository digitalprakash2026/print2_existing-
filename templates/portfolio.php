<?php
$pageTitle = 'Portfolio — RCS Print';
$pageDesc = 'Explore RCS Print portfolio work across business cards, flyers, brochures, posters, stationery, packaging and custom print projects.';
$pageSchema = [[
  '@context' => 'https://schema.org',
  '@type' => 'CollectionPage',
  'name' => 'RCS Print Portfolio',
  'description' => $pageDesc,
  'url' => (defined('APP_URL') ? rtrim((string)APP_URL, '/') : '') . '/portfolio',
]];
include INCLUDE_PATH . '/partials/head.php';
include INCLUDE_PATH . '/partials/header.php';

$settingsMap = is_array($settingsMap ?? null) ? $settingsMap : [];
$bizPhoneRaw = trim((string)($settingsMap['biz_phone'] ?? '+91 98765 43210'));
$bizPhone = htmlspecialchars($bizPhoneRaw, ENT_QUOTES, 'UTF-8');
$bizPhoneHref = htmlspecialchars(preg_replace('/\D+/', '', $bizPhoneRaw), ENT_QUOTES, 'UTF-8');
$bizWa = htmlspecialchars(preg_replace('/\D+/', '', (string)($settingsMap['biz_whatsapp'] ?? $bizPhoneRaw)), ENT_QUOTES, 'UTF-8');
$waText = rawurlencode('Hello RCS Print, I want to discuss a portfolio-style print project.');

$fallbackPortfolioCategories = [
  ['label' => 'Business Cards', 'slug' => 'business-cards', 'icon' => 'fa-id-card-clip'],
  ['label' => 'Flyers', 'slug' => 'flyers', 'icon' => 'fa-file-image'],
  ['label' => 'Brochures', 'slug' => 'brochures', 'icon' => 'fa-images'],
  ['label' => 'Posters', 'slug' => 'posters', 'icon' => 'fa-newspaper'],
  ['label' => 'Stationery', 'slug' => 'stationery', 'icon' => 'fa-file-lines'],
  ['label' => 'Packaging', 'slug' => 'packaging', 'icon' => 'fa-cube'],
  ['label' => 'Others', 'slug' => 'others', 'icon' => 'fa-ellipsis'],
];
$fallbackPortfolioItems = [
  ['title' => 'Creative Business Card Design', 'category' => 'Business Cards', 'category_slug' => 'business-cards', 'image' => '/assets/images/sample-products/business-cards/business-cards-1.svg'],
  ['title' => 'Corporate Flyer Design', 'category' => 'Flyers', 'category_slug' => 'flyers', 'image' => '/assets/images/sample-products/flyers/flyers-1.svg'],
  ['title' => 'Company Brochure Design', 'category' => 'Brochures', 'category_slug' => 'brochures', 'image' => '/assets/images/sample-products/brochures/brochures-1.svg'],
  ['title' => 'Event Poster Design', 'category' => 'Posters', 'category_slug' => 'posters', 'image' => '/assets/images/sample-products/posters/posters-1.svg'],
  ['title' => 'Premium Diary Design', 'category' => 'Stationery', 'category_slug' => 'stationery', 'image' => '/assets/images/sample-products/stationery/stationery-1.svg'],
  ['title' => 'Custom Calendar Design', 'category' => 'Stationery', 'category_slug' => 'stationery', 'image' => '/assets/images/sample-products/stationery/stationery-2.svg'],
  ['title' => 'Corporate Stationery', 'category' => 'Stationery', 'category_slug' => 'stationery', 'image' => '/assets/images/sample-products/stationery/stationery-3.svg'],
  ['title' => 'Product Packaging Design', 'category' => 'Packaging', 'category_slug' => 'packaging', 'image' => '/assets/images/sample-products/business-cards/business-cards-3.svg'],
  ['title' => 'Shopping Bag Design', 'category' => 'Packaging', 'category_slug' => 'packaging', 'image' => '/assets/images/sample-products/banners/banners-3.svg'],
  ['title' => 'Restaurant Menu Design', 'category' => 'Others', 'category_slug' => 'others', 'image' => '/assets/images/sample-products/pamphlets/pamphlets-1.svg'],
  ['title' => 'Roll-Up Banner Design', 'category' => 'Others', 'category_slug' => 'others', 'image' => '/assets/images/sample-products/banners/banners-1.svg'],
  ['title' => 'Wedding Invitation Design', 'category' => 'Others', 'category_slug' => 'others', 'image' => '/assets/images/sample-products/brochures/brochures-4.svg'],
];
$portfolioCategories = is_array($portfolioCategories ?? null) ? $portfolioCategories : $fallbackPortfolioCategories;
$portfolioItems = is_array($portfolioItems ?? null) ? $portfolioItems : $fallbackPortfolioItems;
$portfolioCategory = trim((string)($portfolioCategory ?? ''));
$portfolioPage = max(1, (int)($portfolioPage ?? 1));
$portfolioTotalPages = max(1, (int)($portfolioTotalPages ?? 1));?>

<main class="portfolio-page">
  <section class="portfolio-hero" aria-labelledby="portfolioHeroTitle">
    <div class="portfolio-container portfolio-hero-grid">
      <div class="portfolio-hero-copy">
        <h1 id="portfolioHeroTitle">Our <span>Portfolio</span></h1>
        <p>Explore our work and see how we help businesses make a lasting impression.</p>
        <div class="portfolio-hero-points" aria-label="Portfolio highlights">
          <div><i class="fa-solid fa-camera-retro" aria-hidden="true"></i><strong>Premium<br>Quality</strong></div>
          <div><i class="fa-solid fa-truck-fast" aria-hidden="true"></i><strong>Creative<br>Designs</strong></div>
          <div><i class="fa-solid fa-pen-nib" aria-hidden="true"></i><strong>On-Time<br>Delivery</strong></div>
          <div><i class="fa-solid fa-phone" aria-hidden="true"></i><strong>100% Customer<br>Satisfaction</strong></div>
        </div>
      </div>
      <div class="portfolio-hero-art" aria-label="RCS Print portfolio product mockups">
        <div class="portfolio-art-card portfolio-art-box"><img src="/assets/images/sample-products/business-cards/business-cards-4.svg" alt="RCS printed box and card mockup"></div>
        <div class="portfolio-art-card portfolio-art-book"><img src="/assets/images/sample-products/brochures/brochures-2.svg" alt="Open brochure design mockup"></div>
        <div class="portfolio-art-card portfolio-art-purple"><img src="/assets/images/sample-products/banners/banners-2.svg" alt="Purple brand brochure mockup"></div>
        <div class="portfolio-art-plant" aria-hidden="true"><span></span></div>
      </div>
    </div>
  </section>

  <section class="portfolio-work" aria-labelledby="portfolioWorkTitle">
    <div class="portfolio-container">
      <div class="portfolio-section-head">
        <h2 id="portfolioWorkTitle">Our Work Speaks for <span>Itself</span></h2>
        <div class="portfolio-dots" aria-hidden="true"><span></span><span></span><span></span></div>
      </div>

      <div class="portfolio-filter-row" aria-label="Portfolio categories">
        <a href="/portfolio" class="portfolio-filter <?= $portfolioCategory === '' ? 'active' : '' ?>">
          <i class="fa-solid fa-border-all" aria-hidden="true"></i>
          <span>All Works</span>
        </a>
        <?php foreach ($portfolioCategories as $cat): ?>
          <?php
            $catLabel = (string)($cat['label'] ?? $cat['name'] ?? 'Category');
            $catSlug = (string)($cat['slug'] ?? '');
            $catIcon = (string)($cat['icon'] ?? 'fa-folder-open');
          ?>
          <a href="/portfolio/category/<?= rawurlencode($catSlug) ?>" class="portfolio-filter <?= $portfolioCategory === $catSlug ? 'active' : '' ?>">
            <i class="fa-solid <?= htmlspecialchars($catIcon, ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i>
            <span><?= htmlspecialchars($catLabel, ENT_QUOTES, 'UTF-8') ?></span>
          </a>
        <?php endforeach; ?>
      </div>

      <?php if ($portfolioItems): ?>
      <div class="portfolio-grid">
        <?php foreach ($portfolioItems as $item): ?>
          <?php
            $itemImage = (string)($item['image'] ?? $item['main_image'] ?? '/assets/images/sample-products/business-cards/business-cards-1.svg');
            $itemTitle = (string)($item['title'] ?? 'Portfolio Work');
            $itemAlt = (string)($item['image_alt'] ?? $itemTitle);
            $itemCategory = (string)($item['category'] ?? $item['category_name'] ?? 'Portfolio');
            $itemSlug = (string)($item['slug'] ?? '');
          ?>
          <?= $itemSlug !== '' ? '<a' : '<article' ?> class="portfolio-card <?= $itemSlug !== '' ? 'portfolio-linked-card' : '' ?>"<?= $itemSlug !== '' ? ' href="/portfolio/work/' . rawurlencode($itemSlug) . '"' : '' ?>>
            <div class="portfolio-card-img"><img src="<?= htmlspecialchars($itemImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($itemAlt, ENT_QUOTES, 'UTF-8') ?>" loading="lazy"></div>
            <div class="portfolio-card-body">
              <h3><?= htmlspecialchars($itemTitle, ENT_QUOTES, 'UTF-8') ?></h3>
              <p><i class="fa-regular fa-folder-open" aria-hidden="true"></i><?= htmlspecialchars($itemCategory, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
          <?= $itemSlug !== '' ? '</a>' : '</article>' ?>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
        <div class="portfolio-empty-state">No portfolio work found for this category yet. Please check all works or add new portfolio items from admin.</div>
      <?php endif; ?>

      <?php if ($portfolioPage < $portfolioTotalPages): ?>
        <div class="portfolio-load-wrap"><a class="portfolio-load-btn" href="/portfolio?<?= http_build_query(array_filter(['category' => $portfolioCategory ?: null, 'page' => $portfolioPage + 1])) ?>">Load More Works <i class="fa-solid fa-rotate-right" aria-hidden="true"></i></a></div>
      <?php endif; ?>
    </div>
  </section>

  <section class="portfolio-cta" aria-label="Portfolio project call to action">
    <div class="portfolio-container">
      <div class="portfolio-project-card">
        <div><h2>Have a Project in Mind?</h2><p>Let's create something amazing together!</p></div>
        <div class="portfolio-project-actions"><a href="/contact">Get Free Design</a><a href="/contact" class="outline">Contact Us</a></div>
        <span class="portfolio-gift" aria-hidden="true"><i class="fa-solid fa-gift"></i></span>
      </div>
    </div>
  </section>

  <section class="portfolio-usp" aria-label="RCS Print benefits">
    <div class="portfolio-container portfolio-usp-bar">
      <article><i class="fa-solid fa-lightbulb" aria-hidden="true"></i><div><h3>Creative Print Solutions</h3><p>Smart ideas for standout print results.</p></div></article>
      <article><i class="fa-solid fa-pen-ruler" aria-hidden="true"></i><div><h3>Free Design Support</h3><p>Professional design support at no extra cost.</p></div></article>
      <article><i class="fa-solid fa-bag-shopping" aria-hidden="true"></i><div><h3>Premium Quality</h3><p>Best quality materials and printing.</p></div></article>
      <article><i class="fa-solid fa-tags" aria-hidden="true"></i><div><h3>Affordable Pricing</h3><p>Low price with the best value.</p></div></article>
      <article><i class="fa-solid fa-cube" aria-hidden="true"></i><div><h3>Bulk Order Specialist</h3><p>Special prices for bulk requirements.</p></div></article>
    </div>
  </section>

  <section class="quick-help-section portfolio-quick-help" aria-label="Quick help and bulk order actions">
    <div class="quick-help-container">
      <div class="quick-help-bar">
        <a class="quick-help-item quick-help-call" href="tel:<?= $bizPhoneHref ?>"><span class="quick-help-icon"><i class="fa-solid fa-phone-volume" aria-hidden="true"></i></span><span class="quick-help-copy"><span>Need Help? Call Us</span><strong><?= $bizPhone ?></strong></span></a>
        <button class="quick-help-item quick-help-whatsapp" type="button" onclick="window.open('https://wa.me/<?= $bizWa ?>?text=<?= $waText ?>','_blank')"><span class="quick-help-icon"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></span><span class="quick-help-copy"><strong>Chat with us on WhatsApp</strong><span>We are here to help!</span></span></button>
        <a class="quick-help-item quick-help-download" href="/categories" aria-label="Download our brochure for all products"><span class="quick-help-icon"><i class="fa-solid fa-download" aria-hidden="true"></i></span><span class="quick-help-copy"><strong>Download Brochure</strong><span>For Bulk Orders</span></span></a>
      </div>
    </div>
  </section>
</main>

<?php include INCLUDE_PATH . '/partials/site-footer.php'; ?>
<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
