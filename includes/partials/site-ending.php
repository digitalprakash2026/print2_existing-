<?php
/**
 * Shared public ending sections.
 * Renders the home page's final conversion blocks (Blogs, Quick Help, Footer)
 * on customer-facing pages. Set $siteEndingShowBlogs / QuickHelp / Footer to
 * false before including to render only the missing pieces for pages that
 * already have a local section.
 */
$siteEndingShowBlogs = $siteEndingShowBlogs ?? true;
$siteEndingShowQuickHelp = $siteEndingShowQuickHelp ?? true;
$siteEndingShowFooter = $siteEndingShowFooter ?? true;

$siteEndingSettingsMap = $settingsMap ?? [];
if (empty($siteEndingSettingsMap)) {
    try {
        $siteEndingSettings = Database::rows("SELECT `key`, value FROM settings");
        $siteEndingSettingsMap = array_column($siteEndingSettings, 'value', 'key');
    } catch (\Throwable) {
        $siteEndingSettingsMap = [];
    }
}

$siteEndingBizName = htmlspecialchars($siteEndingSettingsMap['biz_name'] ?? 'RCS Graphic', ENT_QUOTES, 'UTF-8');
$siteEndingBizPhone = htmlspecialchars($siteEndingSettingsMap['biz_phone'] ?? '+91 8980000023', ENT_QUOTES, 'UTF-8');
$siteEndingBizWaRaw = $siteEndingSettingsMap['biz_whatsapp'] ?? '918980000023';
$siteEndingBizWa = htmlspecialchars(preg_replace('/\D+/', '', (string)$siteEndingBizWaRaw), ENT_QUOTES, 'UTF-8');
$siteEndingBizEmail = htmlspecialchars($siteEndingSettingsMap['biz_email'] ?? 'hello@rcsgraphic.in', ENT_QUOTES, 'UTF-8');
$siteEndingBizAddr = htmlspecialchars($siteEndingSettingsMap['biz_address'] ?? 'Rajkot, Gujarat', ENT_QUOTES, 'UTF-8');

$siteEndingFallbackBlogs = [
    [
        'title' => 'How to Choose the Perfect Business Card Finish',
        'slug' => 'how-to-choose-the-perfect-business-card-finish',
        'excerpt' => 'Learn when to pick matte, gloss, textured or premium laminated cards for a stronger first impression.',
        'featured_image' => 'https://images.unsplash.com/photo-1586953208448-b95a79798f07?w=900&q=85&fit=crop',
        'image_alt' => 'Premium printed business cards arranged on a desk',
        'category' => 'Print Tips',
        'badge_theme' => 'purple',
        'published_at' => '2026-05-09 10:00:00',
    ],
    [
        'title' => '5 Flyer Design Ideas That Get More Customers',
        'slug' => '5-flyer-design-ideas-that-get-more-customers',
        'excerpt' => 'Simple layout, color and copy tips to make your next flyer campaign clear, attractive and conversion focused.',
        'featured_image' => 'https://images.unsplash.com/photo-1541746972996-4e0b0f43e02a?w=900&q=85&fit=crop',
        'image_alt' => 'Creative flyer and brochure design samples',
        'category' => 'Design Ideas',
        'badge_theme' => 'orange',
        'published_at' => '2026-05-05 10:00:00',
    ],
    [
        'title' => 'Bulk Printing Checklist for Events and Shops',
        'slug' => 'bulk-printing-checklist-for-events-and-shops',
        'excerpt' => 'Plan quantities, paper type, delivery timing and finishing options before placing your next large print order.',
        'featured_image' => 'https://images.unsplash.com/photo-1600172454284-934feca24de6?w=900&q=85&fit=crop',
        'image_alt' => 'Stacks of brochures and colorful printed material',
        'category' => 'Bulk Orders',
        'badge_theme' => 'green',
        'published_at' => '2026-05-02 10:00:00',
    ],
    [
        'title' => 'How Square Category Images Improve Product Browsing',
        'slug' => 'how-square-category-images-improve-product-browsing',
        'excerpt' => 'See why clean square thumbnails make product discovery faster and help customers compare print categories easily.',
        'featured_image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=900&q=85&fit=crop',
        'image_alt' => 'Designer arranging print category thumbnails on a screen',
        'category' => 'Product Guide',
        'badge_theme' => 'purple',
        'published_at' => '2026-04-28 10:00:00',
    ],
];

$siteEndingBlogs = $homeBlogs ?? [];
if ($siteEndingShowBlogs && empty($siteEndingBlogs)) {
    try {
        $siteEndingBlogs = Database::rows(
            "SELECT id,title,slug,excerpt,featured_image,image_alt,category,badge_theme,published_at
             FROM blogs
             WHERE is_active=1 AND is_featured=1
             ORDER BY sort_order ASC, published_at DESC, id DESC
             LIMIT 4"
        );
    } catch (\Throwable) {
        $siteEndingBlogs = [];
    }
}
$siteEndingBlogs = !empty($siteEndingBlogs) ? $siteEndingBlogs : $siteEndingFallbackBlogs;
$siteEndingBlogBadgeClass = static function ($theme): string {
    $theme = strtolower(trim((string)$theme));
    return match ($theme) {
        'orange' => ' blog-badge-orange',
        'green' => ' blog-badge-green',
        default => '',
    };
};
$siteEndingFormatBlogDate = static function ($value): string {
    $time = strtotime((string)$value);
    return $time ? date('d M, Y', $time) : date('d M, Y');
};
?>

<?php if ($siteEndingShowBlogs): ?>
<!-- FROM OUR BLOGS -->
<section class="blog-section" id="blogs-sec" aria-labelledby="blogTitle">
  <div class="blog-container">
    <div class="blog-head">
      <h2 class="blog-title" id="blogTitle">From Our <span>Blogs</span></h2>
      <a class="blog-view-all" href="/blogs">View All</a>
    </div>

    <div class="blog-grid" role="list" data-auto-slide="true">
      <?php foreach ($siteEndingBlogs as $blog):
        $siteEndingBlogTitleRaw = trim((string)($blog['title'] ?? 'Blog'));
        $siteEndingBlogSlugRaw = trim((string)($blog['slug'] ?? ''));
        $siteEndingBlogUrl = $siteEndingBlogSlugRaw !== '' ? '/blog/' . rawurlencode($siteEndingBlogSlugRaw) : '/blogs';
        $siteEndingBlogImageRaw = trim((string)($blog['featured_image'] ?? ''));
        $siteEndingBlogAltRaw = trim((string)($blog['image_alt'] ?? '')) ?: $siteEndingBlogTitleRaw;
        $siteEndingBlogCategoryRaw = trim((string)($blog['category'] ?? 'Print Tips')) ?: 'Print Tips';
        $siteEndingBlogExcerptRaw = trim((string)($blog['excerpt'] ?? ''));
        $siteEndingBlogTitle = htmlspecialchars($siteEndingBlogTitleRaw, ENT_QUOTES, 'UTF-8');
        $siteEndingBlogImage = htmlspecialchars($siteEndingBlogImageRaw, ENT_QUOTES, 'UTF-8');
        $siteEndingBlogAlt = htmlspecialchars($siteEndingBlogAltRaw, ENT_QUOTES, 'UTF-8');
        $siteEndingBlogCategory = htmlspecialchars($siteEndingBlogCategoryRaw, ENT_QUOTES, 'UTF-8');
        $siteEndingBlogExcerpt = htmlspecialchars($siteEndingBlogExcerptRaw, ENT_QUOTES, 'UTF-8');
        $siteEndingBlogDate = htmlspecialchars($siteEndingFormatBlogDate($blog['published_at'] ?? ''), ENT_QUOTES, 'UTF-8');
        $siteEndingBadgeClass = $siteEndingBlogBadgeClass($blog['badge_theme'] ?? 'purple');
      ?>
      <article class="blog-card" role="listitem">
        <a href="<?= htmlspecialchars($siteEndingBlogUrl, ENT_QUOTES, 'UTF-8') ?>" class="blog-card-link" aria-label="Read blog: <?= $siteEndingBlogTitle ?>">
          <div class="blog-image">
            <?php if ($siteEndingBlogImageRaw !== ''): ?>
              <img src="<?= $siteEndingBlogImage ?>" alt="<?= $siteEndingBlogAlt ?>" loading="lazy">
            <?php endif; ?>
            <span class="blog-badge<?= $siteEndingBadgeClass ?>"><?= $siteEndingBlogCategory ?></span>
          </div>
          <div class="blog-content">
            <div class="blog-meta"><i class="fa-regular fa-calendar" aria-hidden="true"></i> <?= $siteEndingBlogDate ?></div>
            <h3><?= $siteEndingBlogTitle ?></h3>
            <?php if ($siteEndingBlogExcerpt !== ''): ?><p><?= $siteEndingBlogExcerpt ?></p><?php endif; ?>
            <span class="blog-read-more">Read More <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
          </div>
        </a>
      </article>
      <?php endforeach; ?>
    </div>

    <?php if (count($siteEndingBlogs) > 1): ?>
    <div class="blog-dots" aria-label="Blog pagination">
      <?php foreach ($siteEndingBlogs as $idx => $_blog): ?>
        <span class="blog-dot <?= $idx === 0 ? 'blog-dot-active' : '' ?>"></span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($siteEndingShowQuickHelp): ?>
<!-- QUICK HELP STRIP -->
<section class="quick-help-section" id="quick-help-sec" aria-label="Quick help and bulk order actions">
  <div class="quick-help-container">
    <div class="quick-help-bar">
      <a class="quick-help-item quick-help-call" href="tel:<?= preg_replace('/\D+/', '', $siteEndingBizPhone) ?>">
        <span class="quick-help-icon"><i class="fa-solid fa-phone-volume" aria-hidden="true"></i></span>
        <span class="quick-help-copy">
          <span>Need Help? Call Us</span>
          <strong><?= $siteEndingBizPhone ?></strong>
        </span>
      </a>

      <button class="quick-help-item quick-help-whatsapp" type="button" onclick="window.open('https://wa.me/<?= $siteEndingBizWa ?>','_blank')">
        <span class="quick-help-icon"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></span>
        <span class="quick-help-copy">
          <strong>Chat with us on WhatsApp</strong>
          <span>We are here to help!</span>
        </span>
      </button>

      <a class="quick-help-item quick-help-download" href="/categories" aria-label="Download our brochure for all products">
        <span class="quick-help-icon"><i class="fa-solid fa-download" aria-hidden="true"></i></span>
        <span class="quick-help-copy">
          <strong>Download Our Brochure</strong>
          <span>For All Products</span>
        </span>
      </a>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($siteEndingShowFooter): ?>
<!-- FOOTER -->
<footer class="footer" aria-label="Site footer">
  <div class="footer-container">
    <div class="footer-main">
      <div class="footer-brand-col">
        <a href="/" class="footer-logo" aria-label="RCS Print home">
          <span class="footer-logo-main">RCS</span>
          <span class="footer-logo-sub">PRINT</span>
        </a>
        <p class="footer-desc">Your one-stop solution for all your printing needs. Quality prints that represent your brand perfectly.</p>
        <div class="footer-social" aria-label="Social links">
          <a href="/#quick-help-sec" aria-label="Facebook"><i class="fa-brands fa-facebook-f" aria-hidden="true"></i></a>
          <a href="/#quick-help-sec" aria-label="Instagram"><i class="fa-brands fa-instagram" aria-hidden="true"></i></a>
          <a href="https://wa.me/<?= $siteEndingBizWa ?>" aria-label="WhatsApp" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></a>
          <a href="/#quick-help-sec" aria-label="YouTube"><i class="fa-brands fa-youtube" aria-hidden="true"></i></a>
        </div>
      </div>

      <nav class="footer-col" aria-label="Quick links">
        <h3>Quick Links</h3>
        <a href="/">Home</a>
        <a href="/#why-sec">About Us</a>
        <a href="/categories">Products</a>
        <a href="<?= ($user ?? null) ? '/profile' : '/login' ?>">My Account</a>
        <a href="/#quick-help-sec">Contact Us</a>
      </nav>

      <nav class="footer-col" aria-label="Products">
        <h3>Products</h3>
        <a href="/categories">Business Cards</a>
        <a href="/categories">Flyers</a>
        <a href="/categories">Brochures</a>
        <a href="/categories">Posters</a>
        <a href="/categories">Diaries</a>
        <a href="/categories">Calendars</a>
        <a href="/categories">Stationery &amp; More</a>
      </nav>

      <nav class="footer-col" aria-label="Customer service">
        <h3>Customer Service</h3>
        <a href="<?= ($user ?? null) ? '/profile' : '/login' ?>">My Account</a>
        <a href="/my-orders">Track Order</a>
        <a href="/categories">Shipping Policy</a>
        <a href="/categories">Refund &amp; Return</a>
        <a href="/terms-and-conditions">Terms &amp; Conditions</a>
        <a href="/terms-and-conditions">Privacy Policy</a>
      </nav>

      <div class="footer-col footer-contact-col">
        <h3>Contact Us</h3>
        <div class="footer-contact-item">
          <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
          <span><?= $siteEndingBizAddr ?></span>
        </div>
        <a class="footer-contact-item" href="tel:<?= preg_replace('/\D+/', '', $siteEndingBizPhone) ?>">
          <i class="fa-solid fa-phone" aria-hidden="true"></i>
          <span><?= $siteEndingBizPhone ?></span>
        </a>
        <a class="footer-contact-item" href="mailto:<?= $siteEndingBizEmail ?>">
          <i class="fa-regular fa-envelope" aria-hidden="true"></i>
          <span><?= $siteEndingBizEmail ?></span>
        </a>
        <div class="footer-contact-item">
          <i class="fa-regular fa-clock" aria-hidden="true"></i>
          <span>Mon - Sat: 10:00 AM - 7:00 PM</span>
        </div>
      </div>

      <div class="footer-col footer-newsletter-col">
        <h3>Newsletter</h3>
        <p>Subscribe to get special offers, free giveaways, and once-in-a-lifetime deals.</p>
        <form class="footer-newsletter" action="/categories" method="get">
          <label class="sr-only" for="siteEndingFooterEmail">Enter your email</label>
          <input id="siteEndingFooterEmail" name="email" type="email" placeholder="Enter your email" autocomplete="email">
          <button type="submit">Subscribe</button>
        </form>
      </div>
    </div>

    <div class="footer-bottom">
      <div class="footer-copy">© <?= date('Y') ?> RCS PRINT. All Rights Reserved.</div>
      <div class="footer-developed">Developed By Prakash Karena</div>
    </div>
  </div>
</footer>
<?php endif; ?>
