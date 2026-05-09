<?php
$pageTitle = ($blog['meta_title'] ?? $blog['title'] ?? 'Blog') . ' — RCS Print';
$pageDesc = $blog['meta_description'] ?? $blog['excerpt'] ?? 'RCS Print blog article.';
include INCLUDE_PATH . '/partials/head.php';
include INCLUDE_PATH . '/partials/header.php';

$bizName  = htmlspecialchars($settingsMap['biz_name'] ?? 'RCS Print', ENT_QUOTES, 'UTF-8');
$bizPhone = htmlspecialchars($settingsMap['biz_phone'] ?? '+91 98765 43210', ENT_QUOTES, 'UTF-8');
$bizWa    = htmlspecialchars($settingsMap['biz_whatsapp'] ?? '919876543210', ENT_QUOTES, 'UTF-8');
$bizEmail = htmlspecialchars($settingsMap['biz_email'] ?? 'hello@rcsgraphic.in', ENT_QUOTES, 'UTF-8');

$title = htmlspecialchars((string)($blog['title'] ?? ''), ENT_QUOTES, 'UTF-8');
$excerpt = htmlspecialchars((string)($blog['excerpt'] ?? ''), ENT_QUOTES, 'UTF-8');
$category = htmlspecialchars((string)($blog['category'] ?? 'Print Tips'), ENT_QUOTES, 'UTF-8');
$author = htmlspecialchars((string)($blog['author_name'] ?? 'RCS Print Team'), ENT_QUOTES, 'UTF-8');
$image = htmlspecialchars((string)($blog['featured_image'] ?? ''), ENT_QUOTES, 'UTF-8');
$imageAlt = htmlspecialchars((string)(($blog['image_alt'] ?? '') ?: ($blog['title'] ?? 'Blog image')), ENT_QUOTES, 'UTF-8');
$publishedAt = strtotime((string)($blog['published_at'] ?? '')) ?: time();
$published = date('d M, Y', $publishedAt);
$sanitizeBlogHtml = static function (string $html): string {
  $allowed = '<p><br><strong><b><em><i><u><h2><h3><h4><ul><ol><li><a><blockquote><img><figure><figcaption>';
  $clean = strip_tags($html, $allowed);
  $clean = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean) ?? $clean;
  $clean = preg_replace('/(href|src)\s*=\s*("|\')\s*javascript:[^"\']*("|\')/i', '$1="#"', $clean) ?? $clean;
  return $clean;
};
$content = $sanitizeBlogHtml((string)($blog['content'] ?? ''));
?>

<main class="blog-detail-page">
  <section class="blog-detail-hero">
    <div class="blog-detail-container">
      <a href="/#blogs-sec" class="blog-back-link">← Back to Blogs</a>
      <div class="blog-detail-meta-row">
        <span class="blog-detail-badge"><?= $category ?></span>
        <span><i class="fa-regular fa-calendar" aria-hidden="true"></i> <?= htmlspecialchars($published, ENT_QUOTES, 'UTF-8') ?></span>
        <span><i class="fa-regular fa-user" aria-hidden="true"></i> <?= $author ?></span>
      </div>
      <h1><?= $title ?></h1>
      <?php if ($excerpt !== ''): ?><p class="blog-detail-excerpt"><?= $excerpt ?></p><?php endif; ?>
    </div>
  </section>

  <section class="blog-detail-body-section">
    <div class="blog-detail-container blog-detail-layout">
      <article class="blog-detail-article">
        <?php if ($image !== ''): ?>
          <figure class="blog-detail-image">
            <img src="<?= $image ?>" alt="<?= $imageAlt ?>" loading="eager">
          </figure>
        <?php endif; ?>
        <div class="blog-detail-content"><?= $content ?></div>
      </article>

      <aside class="blog-detail-sidebar">
        <div class="blog-side-card">
          <h2>Need Printing Help?</h2>
          <p>Talk to our team for design support, paper selection and bulk order guidance.</p>
          <button type="button" onclick="window.open('https://wa.me/<?= $bizWa ?>','_blank')">Chat on WhatsApp</button>
        </div>
        <?php if (!empty($relatedBlogs ?? [])): ?>
          <div class="blog-side-card">
            <h2>More Blogs</h2>
            <?php foreach ($relatedBlogs as $rb): ?>
              <a class="blog-related-link" href="/blog/<?= htmlspecialchars((string)$rb['slug'], ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars((string)$rb['title'], ENT_QUOTES, 'UTF-8') ?>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </aside>
    </div>
  </section>
</main>

<footer class="footer" aria-label="Site footer">
  <div class="footer-container">
    <div class="footer-bottom" style="border-top:0">
      <div class="footer-copy">© <?= date('Y') ?> <?= $bizName ?>. All Rights Reserved.</div>
      <div class="footer-payments" aria-label="Accepted payments"><span>VISA</span><span>UPI</span><span>Paytm</span></div>
      <div class="footer-developed">Developed By Prakash Karena</div>
    </div>
  </div>
</footer>

<div class="mob-quick-cta" role="navigation" aria-label="Quick actions">
  <a href="tel:<?= preg_replace('/\D+/', '', $bizPhone) ?>" class="mq-btn">Call</a>
  <button type="button" class="mq-btn mq-btn-wa" onclick="window.open('https://wa.me/<?= $bizWa ?>','_blank')">WhatsApp</button>
  <a href="/products" class="mq-btn mq-btn-primary">Start Order</a>
</div>

<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
