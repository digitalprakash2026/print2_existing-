<?php
$pageTitle = ($blog['meta_title'] ?? $blog['title'] ?? 'Blog') . ' — RCS Print';
$pageDesc = $blog['meta_description'] ?? $blog['excerpt'] ?? 'RCS Print blog article.';
$pageOgType = 'article';
$pageImage = trim((string)($blog['featured_image'] ?? '')) ?: '/assets/images/rcs-graphic-logo.png';
$blogBaseUrl = defined('APP_URL') ? rtrim((string)APP_URL, '/') : '';
$blogSchemaImage = preg_match('#^https?://#i', $pageImage) ? $pageImage : ($blogBaseUrl . '/' . ltrim($pageImage, '/'));
$blogPublishedRaw = (string)($blog['published_at'] ?? $blog['created_at'] ?? 'now');
$blogModifiedRaw = (string)($blog['updated_at'] ?? $blog['published_at'] ?? $blogPublishedRaw);
$pageSchema = [[
  '@context' => 'https://schema.org',
  '@type' => 'BlogPosting',
  'headline' => (string)($blog['title'] ?? 'RCS Print Blog'),
  'description' => strip_tags((string)$pageDesc),
  'image' => $blogSchemaImage,
  'datePublished' => date('c', strtotime($blogPublishedRaw) ?: time()),
  'dateModified' => date('c', strtotime($blogModifiedRaw) ?: time()),
  'author' => ['@type' => 'Person', 'name' => (string)($blog['author_name'] ?? 'RCS Print Team')],
  'publisher' => [
    '@type' => 'Organization',
    'name' => (string)($settingsMap['biz_name'] ?? 'RCS Print'),
    'logo' => ['@type' => 'ImageObject', 'url' => $blogBaseUrl . '/assets/images/rcs-graphic-logo.png'],
  ],
  'mainEntityOfPage' => $blogBaseUrl . '/blog/' . rawurlencode((string)($blog['slug'] ?? '')),
]];
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
$sidebarBannerImageRaw = trim((string)($settingsMap['blog_sidebar_banner_image'] ?? ''));
$sidebarBannerUrlRaw = trim((string)($settingsMap['blog_sidebar_banner_url'] ?? ''));
$sidebarBannerAltRaw = trim((string)($settingsMap['blog_sidebar_banner_alt'] ?? ''));
$sidebarBannerActive = (int)($settingsMap['blog_sidebar_banner_active'] ?? 0) === 1 && $sidebarBannerImageRaw !== '';
$sidebarBannerNewTab = (int)($settingsMap['blog_sidebar_banner_new_tab'] ?? 0) === 1;
$sidebarBannerImage = htmlspecialchars($sidebarBannerImageRaw, ENT_QUOTES, 'UTF-8');
$sidebarBannerUrl = htmlspecialchars($sidebarBannerUrlRaw !== '' ? $sidebarBannerUrlRaw : '#', ENT_QUOTES, 'UTF-8');
$sidebarBannerAlt = htmlspecialchars($sidebarBannerAltRaw !== '' ? $sidebarBannerAltRaw : 'RCS Print banner', ENT_QUOTES, 'UTF-8');
$publishedAt = strtotime((string)($blog['published_at'] ?? '')) ?: time();
$published = date('d M, Y', $publishedAt);
$sanitizeBlogHtml = static function (string $html): string {
  $allowed = '<p><br><strong><b><em><i><u><h2><h3><h4><ul><ol><li><a><blockquote><img><figure><figcaption><div><span><hr><iframe><video><source>';
  $clean = strip_tags($html, $allowed);
  $clean = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean) ?? $clean;
  $clean = preg_replace('/(href|src)\s*=\s*("|\')\s*javascript:[^"\']*("|\')/i', '$1="#"', $clean) ?? $clean;
  $clean = preg_replace_callback('/<iframe\b([^>]*)>/i', static function (array $m): string {
    $attrs = $m[1] ?? '';
    if (!preg_match('/src\s*=\s*("|\')([^"\']+)\1/i', $attrs, $srcMatch)) {
      return '';
    }
    $src = $srcMatch[2];
    if (!preg_match('#^https://(www\.)?(youtube\.com/embed/|player\.vimeo\.com/video/)#i', $src)) {
      return '';
    }
    return '<iframe src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" loading="lazy" allowfullscreen></iframe>';
  }, $clean) ?? $clean;
  return $clean;
};
$content = $sanitizeBlogHtml((string)($blog['content'] ?? ''));
$suggestedBlogs = array_slice(is_array($relatedBlogs ?? null) ? $relatedBlogs : [], 0, 4);
$blogThumb = static function (array $item): string {
  $src = trim((string)($item['featured_image'] ?? ''));
  return $src !== '' ? $src : 'https://placehold.co/420x280/EEF3FD/4A148C?text=RCS+Print';
};
?>

<main class="blog-detail-page">
  <section class="blog-detail-hero">
    <div class="blog-detail-container">
      <a href="/blogs" class="blog-back-link">← Back to Blogs</a>
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
        <?php if (!empty($suggestedBlogs)): ?>
          <section class="blog-bottom-suggestions" aria-labelledby="blogMoreGuidesTitle">
            <div class="blog-bottom-head">
              <span>Keep learning</span>
              <h2 id="blogMoreGuidesTitle">Related Printing Guides</h2>
              <p>Handpicked articles to help you plan better designs, materials and print orders.</p>
            </div>
            <div class="blog-suggestion-grid">
              <?php foreach ($suggestedBlogs as $sb): ?>
                <?php
                  $sbTitle = (string)($sb['title'] ?? 'Blog article');
                  $sbUrl = '/blog/' . rawurlencode((string)($sb['slug'] ?? ''));
                  $sbImg = htmlspecialchars($blogThumb($sb), ENT_QUOTES, 'UTF-8');
                  $sbAlt = htmlspecialchars((string)(($sb['image_alt'] ?? '') ?: $sbTitle), ENT_QUOTES, 'UTF-8');
                  $sbExcerpt = trim(strip_tags((string)($sb['excerpt'] ?? '')));
                  if (function_exists('mb_substr')) $sbExcerpt = mb_substr($sbExcerpt, 0, 96); else $sbExcerpt = substr($sbExcerpt, 0, 96);
                ?>
                <a class="blog-suggestion-card" href="<?= htmlspecialchars($sbUrl, ENT_QUOTES, 'UTF-8') ?>">
                  <span class="blog-suggestion-img"><img src="<?= $sbImg ?>" alt="<?= $sbAlt ?>" loading="lazy"></span>
                  <span class="blog-suggestion-copy">
                    <small><?= htmlspecialchars((string)($sb['category'] ?? 'Print Tips'), ENT_QUOTES, 'UTF-8') ?></small>
                    <strong><?= htmlspecialchars($sbTitle, ENT_QUOTES, 'UTF-8') ?></strong>
                    <?php if ($sbExcerpt !== ''): ?><em><?= htmlspecialchars($sbExcerpt, ENT_QUOTES, 'UTF-8') ?>...</em><?php endif; ?>
                  </span>
                </a>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endif; ?>
      </article>

      <aside class="blog-detail-sidebar">
        <div class="blog-side-card">
          <h2>Need Printing Help?</h2>
          <p>Talk to our team for design support, paper selection and bulk order guidance.</p>
          <button type="button" onclick="window.open('https://wa.me/<?= $bizWa ?>','_blank')">Chat on WhatsApp</button>
        </div>
        <?php if (!empty($relatedBlogs ?? [])): ?>
          <div class="blog-side-card blog-side-more-card">
            <h2>More Blogs</h2>
            <div class="blog-related-list">
              <?php foreach ($relatedBlogs as $rb): ?>
                <?php
                  $rbTitle = (string)($rb['title'] ?? 'Blog article');
                  $rbUrl = '/blog/' . rawurlencode((string)($rb['slug'] ?? ''));
                  $rbImg = htmlspecialchars($blogThumb($rb), ENT_QUOTES, 'UTF-8');
                  $rbAlt = htmlspecialchars((string)(($rb['image_alt'] ?? '') ?: $rbTitle), ENT_QUOTES, 'UTF-8');
                  $rbDate = !empty($rb['published_at']) ? date('M d, Y', strtotime((string)$rb['published_at'])) : '';
                ?>
                <a class="blog-related-link" href="<?= htmlspecialchars($rbUrl, ENT_QUOTES, 'UTF-8') ?>">
                  <span class="blog-related-thumb"><img src="<?= $rbImg ?>" alt="<?= $rbAlt ?>" loading="lazy"></span>
                  <span class="blog-related-copy">
                    <strong><?= htmlspecialchars($rbTitle, ENT_QUOTES, 'UTF-8') ?></strong>
                    <small><?= htmlspecialchars((string)($rb['category'] ?? 'Print Tips'), ENT_QUOTES, 'UTF-8') ?><?= $rbDate !== '' ? ' · ' . htmlspecialchars($rbDate, ENT_QUOTES, 'UTF-8') : '' ?></small>
                  </span>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
        <?php if ($sidebarBannerActive): ?>
          <a class="blog-sidebar-banner" href="<?= $sidebarBannerUrl ?>" <?= $sidebarBannerNewTab ? 'target="_blank" rel="noopener noreferrer"' : '' ?> aria-label="<?= $sidebarBannerAlt ?>">
            <img src="<?= $sidebarBannerImage ?>" alt="<?= $sidebarBannerAlt ?>" loading="lazy">
          </a>
        <?php endif; ?>
      </aside>
    </div>
  </section>
</main>

<?php include INCLUDE_PATH . '/partials/site-footer.php'; ?>
<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
