<?php
$page = is_array($page ?? null) ? $page : [];
$pageTitle = $page['meta_title'] ?? (($page['title'] ?? 'Information') . ' — RCS Graphic');
$pageDesc = $page['meta_description'] ?? 'RCS Graphic information page.';
include INCLUDE_PATH . '/partials/head.php';
include INCLUDE_PATH . '/partials/header.php';

$bizWa = htmlspecialchars(($settingsMap['biz_whatsapp'] ?? '919876543210'), ENT_QUOTES, 'UTF-8');
$title = htmlspecialchars((string)($page['title'] ?? 'Information'), ENT_QUOTES, 'UTF-8');
$eyebrow = htmlspecialchars((string)($page['eyebrow'] ?? 'RCS Graphic'), ENT_QUOTES, 'UTF-8');
$heading = htmlspecialchars((string)($page['heading'] ?? $title), ENT_QUOTES, 'UTF-8');
$intro = htmlspecialchars((string)($page['intro'] ?? ''), ENT_QUOTES, 'UTF-8');
$breadcrumb = htmlspecialchars((string)($page['breadcrumb'] ?? $title), ENT_QUOTES, 'UTF-8');
$badges = is_array($page['hero_badges'] ?? null) ? $page['hero_badges'] : [];
$highlights = is_array($page['highlights'] ?? null) ? $page['highlights'] : [];
$sections = is_array($page['sections'] ?? null) ? $page['sections'] : [];
$steps = is_array($page['steps'] ?? null) ? $page['steps'] : [];
$ctaTitle = htmlspecialchars((string)($page['cta_title'] ?? 'Need help?'), ENT_QUOTES, 'UTF-8');
$ctaText = htmlspecialchars((string)($page['cta_text'] ?? 'Contact our team for guidance.'), ENT_QUOTES, 'UTF-8');
$visualTitle = htmlspecialchars((string)($page['visual_title'] ?? 'Premium print support'), ENT_QUOTES, 'UTF-8');
$visualText = htmlspecialchars((string)($page['visual_text'] ?? 'Professional print guidance for your business.'), ENT_QUOTES, 'UTF-8');
$mockups = is_array($page['mockups'] ?? null) ? array_values($page['mockups']) : [];
if (empty($mockups)) {
    $mockups = [(string)($page['visual_image'] ?? '/assets/img/categories/print-category.svg')];
}
?>
<main class="info-page">
  <section class="info-hero">
    <div class="info-container">
      <nav class="info-breadcrumb" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span><span><?= $breadcrumb ?></span></nav>
      <div class="info-hero-grid">
        <div>
          <div class="sec-ey"><?= $eyebrow ?></div>
          <h1><?= $heading ?></h1>
          <?php if ($intro !== ''): ?><p><?= $intro ?></p><?php endif; ?>
          <?php if (!empty($badges)): ?>
            <div class="info-badges">
              <?php foreach ($badges as $badge): ?><span><?= htmlspecialchars((string)$badge, ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        <aside class="info-visual-card" aria-label="Page visual">
          <div class="info-print-stack" aria-hidden="true">
            <?php foreach ($mockups as $idx => $mockup): ?>
              <img class="info-mockup info-mockup-<?= $idx + 1 ?>" src="<?= htmlspecialchars((string)$mockup, ENT_QUOTES, 'UTF-8') ?>" alt="" loading="<?= $idx === 0 ? 'eager' : 'lazy' ?>" decoding="async">
            <?php endforeach; ?>
          </div>
          <div class="info-visual-caption">
            <strong><?= $visualTitle ?></strong>
            <p><?= $visualText ?></p>
          </div>
          <div class="info-actions">
            <a href="/categories" class="btn btn-blue">Explore Products</a>
            <a href="https://wa.me/<?= $bizWa ?>" class="btn btn-outline" target="_blank" rel="noopener">WhatsApp Us</a>
          </div>
        </aside>
      </div>
    </div>
  </section>

  <?php if (!empty($highlights)): ?>
  <section class="info-section info-section-wide">
    <div class="info-container info-card-grid">
      <?php foreach ($highlights as $item): ?>
        <article class="info-feature-card">
          <div class="info-feature-icon"><?= htmlspecialchars((string)($item['icon'] ?? '•'), ENT_QUOTES, 'UTF-8') ?></div>
          <h2><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
          <p><?= htmlspecialchars((string)($item['text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if (!empty($sections)): ?>
  <section class="info-section info-section-soft info-section-wide">
    <div class="info-container info-content-layout">
      <div class="info-content-aside">
        <div class="sec-ey">Page guide</div>
        <h2><?= $title ?></h2>
        <p><?= $intro ?></p>
      </div>
      <div class="info-content-wrap">
      <?php foreach ($sections as $section): ?>
        <article class="info-content-card">
          <h2><?= htmlspecialchars((string)($section['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
          <?php foreach ((array)($section['body'] ?? []) as $paragraph): ?>
            <p><?= htmlspecialchars((string)$paragraph, ENT_QUOTES, 'UTF-8') ?></p>
          <?php endforeach; ?>
          <?php if (!empty($section['bullets']) && is_array($section['bullets'])): ?>
            <ul class="info-content-list">
              <?php foreach ($section['bullets'] as $bullet): ?>
                <li><?= htmlspecialchars((string)$bullet, ENT_QUOTES, 'UTF-8') ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php if (!empty($steps)): ?>
  <section class="info-section">
    <div class="info-container">
      <div class="info-section-head">
        <div class="sec-ey">How it works</div>
        <h2>Simple process, clear communication</h2>
      </div>
      <div class="info-steps">
        <?php foreach ($steps as $idx => $step): ?>
          <article class="info-step">
            <span><?= str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT) ?></span>
            <h3><?= htmlspecialchars((string)($step['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars((string)($step['text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <section class="info-section info-cta-section">
    <div class="info-container">
      <div class="info-cta-card">
        <div>
          <h2><?= $ctaTitle ?></h2>
          <p><?= $ctaText ?></p>
        </div>
        <div class="info-actions">
          <a href="/contact" class="btn btn-blue">Contact Us</a>
          <a href="/categories" class="btn btn-outline">View Products</a>
        </div>
      </div>
    </div>
  </section>
</main>
<?php include INCLUDE_PATH . '/partials/site-footer.php'; ?>
<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
