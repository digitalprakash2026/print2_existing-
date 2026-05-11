<?php
/**
 * blogs.php — Public Blogs Listing
 * Route: /blogs
 */
$pageTitle = 'Blogs — RCS Graphic';
$pageDesc  = 'Read printing tips, design ideas and bulk order guides from RCS Print.';

$blogBadgeClass = static function ($theme): string {
  $theme = strtolower(trim((string)$theme));
  return match ($theme) {
    'orange' => ' blog-badge-orange',
    'green' => ' blog-badge-green',
    default => '',
  };
};
$formatBlogDate = static function ($value): string {
  $time = strtotime((string)$value);
  return $time ? date('d M, Y', $time) : date('d M, Y');
};

include INCLUDE_PATH . '/partials/head.php';
include INCLUDE_PATH . '/partials/header.php';
?>

<main class="all-blogs-page" style="margin-top:var(--hh);padding:26px 0 84px;background:var(--bg);min-height:calc(100vh - var(--hh))">
  <div class="container">
    <div class="breadcrumb" style="padding-top:4px">
      <a href="/">Home</a><span>/</span>
      <span style="color:var(--ink);font-weight:600">Blogs</span>
    </div>

    <section style="padding:26px 0 22px;border-bottom:1px solid var(--border);margin-bottom:28px">
      <div class="sec-ey">RCS Print Blog</div>
      <h1 style="font-family:var(--fd);font-size:clamp(26px,4.5vw,42px);font-weight:800;color:var(--ink);line-height:1.1;margin:0 0 8px">
        Printing Tips, Ideas &amp; Guides
      </h1>
      <p style="font-size:14px;color:var(--text2);margin:0;max-width:720px;line-height:1.7">
        Explore practical print, design and bulk-order advice to make your next project look better.
      </p>
    </section>

    <?php if (empty($blogs ?? [])): ?>
      <div style="text-align:center;padding:80px 20px;background:#fff;border:1px solid var(--border);border-radius:18px">
        <div style="font-size:48px;margin-bottom:12px">📝</div>
        <div style="font-size:17px;font-weight:800;color:var(--ink);margin-bottom:6px">No blogs published yet</div>
        <p style="font-size:13px;color:var(--text2);margin:0">Please check back soon for new printing tips.</p>
      </div>
    <?php else: ?>
      <div class="all-blogs-grid" role="list">
        <?php foreach ($blogs as $blog):
          $titleRaw = trim((string)($blog['title'] ?? 'Blog'));
          $slugRaw = trim((string)($blog['slug'] ?? ''));
          $url = $slugRaw !== '' ? '/blog/' . rawurlencode($slugRaw) : '/blogs';
          $imageRaw = trim((string)($blog['featured_image'] ?? ''));
          $altRaw = trim((string)($blog['image_alt'] ?? '')) ?: $titleRaw;
          $categoryRaw = trim((string)($blog['category'] ?? 'Print Tips')) ?: 'Print Tips';
          $excerptRaw = trim((string)($blog['excerpt'] ?? ''));
          $badgeClass = $blogBadgeClass($blog['badge_theme'] ?? 'purple');
        ?>
          <article class="blog-card" role="listitem">
            <a href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>" class="blog-card-link" aria-label="Read blog: <?= htmlspecialchars($titleRaw, ENT_QUOTES, 'UTF-8') ?>">
              <div class="blog-image">
                <?php if ($imageRaw !== ''): ?>
                  <img src="<?= htmlspecialchars($imageRaw, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($altRaw, ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                <?php endif; ?>
                <span class="blog-badge<?= $badgeClass ?>"><?= htmlspecialchars($categoryRaw, ENT_QUOTES, 'UTF-8') ?></span>
              </div>
              <div class="blog-content">
                <div class="blog-meta"><i class="fa-regular fa-calendar" aria-hidden="true"></i> <?= htmlspecialchars($formatBlogDate($blog['published_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                <h2><?= htmlspecialchars($titleRaw, ENT_QUOTES, 'UTF-8') ?></h2>
                <?php if ($excerptRaw !== ''): ?><p><?= htmlspecialchars($excerptRaw, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                <span class="blog-read-more">Read More <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
              </div>
            </a>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
