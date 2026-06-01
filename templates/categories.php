<?php
/**
 * categories.php — All Categories Page
 * Shows every active category with its square category image.
 * Route: /categories
 */
$pageTitle = 'All Categories — RCS Graphic';
$pageDesc  = 'Browse all printing categories at RCS Graphic — visiting cards, brochures, flyers, posters and more.';

$activeCategories = array_values(array_filter($categories ?? [], static fn($cat) => (int)($cat['is_active'] ?? 1) === 1));
$bizName  = htmlspecialchars($settingsMap['biz_name']    ?? 'RCS Graphic');
$bizPhone = htmlspecialchars($settingsMap['biz_phone']   ?? '+91 98765 43210');
$bizWa    = htmlspecialchars($settingsMap['biz_whatsapp']?? '919876543210');
$bizEmail = htmlspecialchars($settingsMap['biz_email']   ?? 'hello@rcsgraphic.in');
$bizAddr  = htmlspecialchars($settingsMap['biz_address'] ?? 'Rajkot, Gujarat');
$visibleCount = count($activeCategories);
$totalItems = array_sum(array_map(static fn($cat) => max(0, (int)($cat['product_count'] ?? 0)), $activeCategories));
$totalItems = $totalItems > 0 ? $totalItems : $visibleCount;
$categoryDescriptions = [
  'visiting-cards' => 'Make a lasting first impression with our premium quality business cards.',
  'business-cards' => 'Make a lasting first impression with our premium quality business cards.',
  'flyers' => 'Promote your business with eye-catching and professional flyers.',
  'brochures' => 'Showcase your business or services with high-quality brochures.',
  'posters' => 'High-impact posters for events, promotions and branding.',
  'diaries' => 'Custom diaries for your brand or personal use.',
  'calendars' => 'Stay ahead all year with our custom printed calendars.',
  'stationery-more' => 'Letterheads, envelopes, ID cards and more stationery items.',
  'stationery' => 'Letterheads, envelopes, ID cards and more stationery items.',
];
$categoryThemeClasses = ['purple', 'orange', 'orange', 'orange', 'purple', 'orange', 'purple', 'green'];

include INCLUDE_PATH . '/partials/head.php';
include INCLUDE_PATH . '/partials/header.php';
?>

<main class="all-cat-page">
  <section class="all-cat-hero-banner" aria-labelledby="allCatTitle">
    <div class="all-cat-hero-copy">
      <nav class="all-cat-crumb" aria-label="Breadcrumb">
        <a href="/">Home</a><span>›</span><span>All Categories</span>
      </nav>
      <h1 id="allCatTitle">All Categories</h1>
      <p>Premium Quality Printing for Every Need</p>
    </div>
    <div class="all-cat-hero-visual" aria-hidden="true">
      <img src="/assets/img/categories/all-categories-hero.svg" alt="" loading="eager">
    </div>
  </section>

  <div class="container all-cat-content">
    <?php if (empty($activeCategories)): ?>
      <div style="text-align:center;padding:80px 20px;background:#fff;border:1px solid var(--border);border-radius:18px">
        <div style="font-size:48px;margin-bottom:12px">🗂️</div>
        <div style="font-size:17px;font-weight:800;color:var(--ink);margin-bottom:6px">No categories yet</div>
        <p style="font-size:13px;color:var(--text2);margin-bottom:20px">Please check back soon or contact us on WhatsApp.</p>
        <a href="https://wa.me/<?= htmlspecialchars($bizWa) ?>" target="_blank" rel="noopener" class="btn btn-green">💬 WhatsApp Us</a>
      </div>
    <?php else: ?>
      <section class="all-cat-shop" aria-label="Browse all categories">
        <details class="all-cat-filter-panel" open>
          <summary><span>Categories &amp; Filters</span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary>
          <aside class="all-cat-sidebar" aria-label="Category filters">
          <div class="all-cat-side-box all-cat-side-categories">
            <h2>Categories</h2>
            <nav class="all-cat-side-list" aria-label="Category quick links">
              <?php foreach ($activeCategories as $idx => $cat): ?>
                <a href="/category/<?= htmlspecialchars($cat['slug'] ?? '') ?>" class="<?= $idx === 0 ? 'is-active' : '' ?>">
                  <?= htmlspecialchars($cat['name'] ?? 'Category') ?>
                </a>
              <?php endforeach; ?>
            </nav>
          </div>

          <div class="all-cat-side-box all-cat-filter-box">
            <h2>Filter By</h2>
            <div class="all-cat-filter-group">
              <h3>Product Type</h3>
              <label><input type="checkbox"> Standard</label>
              <label><input type="checkbox"> Premium</label>
              <label><input type="checkbox"> Luxury</label>
            </div>
            <div class="all-cat-filter-group">
              <h3>Paper Type</h3>
              <label><input type="checkbox"> Art Paper</label>
              <label><input type="checkbox"> Matte</label>
              <label><input type="checkbox"> Glossy</label>
              <label><input type="checkbox"> Textured</label>
            </div>
            <div class="all-cat-filter-group">
              <h3>Finishing</h3>
              <label><input type="checkbox"> Matt Lamination</label>
              <label><input type="checkbox"> Gloss Lamination</label>
              <label><input type="checkbox"> UV Coating</label>
              <label><input type="checkbox"> Spot UV</label>
              <label><input type="checkbox"> Foil Stamping</label>
            </div>
            <div class="all-cat-filter-group all-cat-price-filter">
              <h3>Price Range</h3>
              <div class="all-cat-price-line" aria-hidden="true"><span></span></div>
              <div class="all-cat-price-values"><span>₹0</span><span>₹5000+</span></div>
            </div>
            <button type="button" class="all-cat-apply-btn">Apply Filters <i class="fa-solid fa-sliders" aria-hidden="true"></i></button>
          </div>
          </aside>
        </details>

        <div class="all-cat-results">
          <div class="all-cat-toolbar">
            <p>Showing 1–<?= (int)$visibleCount ?> of <?= (int)$totalItems ?> products</p>
            <label>Sort by:
              <select aria-label="Sort categories">
                <option>Popularity</option>
                <option>Newest</option>
                <option>Name A-Z</option>
              </select>
            </label>
          </div>

          <div class="all-cat-grid" aria-label="All printing categories">
            <?php foreach ($activeCategories as $idx => $cat):
              $name = (string)($cat['name'] ?? 'Category');
              $slug = (string)($cat['slug'] ?? '');
              $image = trim((string)($cat['image_path'] ?? ''));
              $alt = trim((string)($cat['image_alt'] ?? '')) ?: ($name . ' category image');
              $count = (int)($cat['product_count'] ?? 0);
              $desc = $categoryDescriptions[$slug] ?? ('Explore premium quality ' . strtolower($name) . ' printing for your brand.');
              $theme = $categoryThemeClasses[$idx % count($categoryThemeClasses)];
            ?>
              <a class="all-cat-card all-cat-card-<?= htmlspecialchars($theme) ?>" href="/category/<?= htmlspecialchars($slug) ?>">
                <div class="all-cat-img">
                  <?php if ($image !== ''): ?>
                    <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($alt) ?>" loading="lazy">
                  <?php else: ?>
                    <div class="shop-cat-fallback" aria-hidden="true"><?= htmlspecialchars($cat['icon'] ?? '📦') ?></div>
                  <?php endif; ?>
                </div>
                <div class="all-cat-body">
                  <span class="all-cat-icon" aria-hidden="true"><i class="fa-solid fa-print"></i></span>
                  <h2><?= htmlspecialchars($name) ?></h2>
                  <p><?= htmlspecialchars($desc) ?></p>
                  <strong>(<?= $count ?> Item<?= $count === 1 ? '' : 's' ?>)</strong>
                </div>
              </a>
            <?php endforeach; ?>
          </div>

          <nav class="all-cat-pagination" aria-label="Category pagination">
            <span class="is-muted">←</span><strong>1</strong><span>2</span><span>3</span><span>4</span><span>→</span>
          </nav>
        </div>
      </section>

      <section class="all-cat-usp" aria-label="RCS Print benefits">
        <div class="why-print-panel">
          <article class="why-print-item">
            <div class="why-print-icon why-print-purple"><i class="fa-solid fa-truck-fast" aria-hidden="true"></i></div>
            <div class="why-print-copy">
              <h3>Fast Delivery</h3>
              <p>On-time delivery always guaranteed.</p>
            </div>
          </article>

          <article class="why-print-item">
            <div class="why-print-icon why-print-orange"><i class="fa-solid fa-pen-ruler" aria-hidden="true"></i></div>
            <div class="why-print-copy">
              <h3>Free Design Support</h3>
              <p>Professional design support at no extra cost.</p>
            </div>
          </article>

          <article class="why-print-item">
            <div class="why-print-icon why-print-green"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></div>
            <div class="why-print-copy">
              <h3>Premium Quality</h3>
              <p>Best quality materials and printing.</p>
            </div>
          </article>

          <article class="why-print-item">
            <div class="why-print-icon why-print-purple"><i class="fa-solid fa-tags" aria-hidden="true"></i></div>
            <div class="why-print-copy">
              <h3>Affordable Pricing</h3>
              <p>Low price with the best value.</p>
            </div>
          </article>

          <article class="why-print-item">
            <div class="why-print-icon why-print-orange"><i class="fa-solid fa-cube" aria-hidden="true"></i></div>
            <div class="why-print-copy">
              <h3>Bulk Order Specialist</h3>
              <p>Special prices for bulk requirements.</p>
            </div>
          </article>
        </div>
      </section>
    <?php endif; ?>
  </div>
</main>

<!-- QUICK HELP STRIP -->
<section class="quick-help-section" id="quick-help-sec" aria-label="Quick help and bulk order actions" data-reveal>
  <div class="quick-help-container">
    <div class="quick-help-bar">
      <a class="quick-help-item quick-help-call" href="tel:<?= preg_replace('/\D+/', '', $bizPhone) ?>">
        <span class="quick-help-icon"><i class="fa-solid fa-phone-volume" aria-hidden="true"></i></span>
        <span class="quick-help-copy">
          <span>Need Help? Call Us</span>
          <strong><?= $bizPhone ?></strong>
        </span>
      </a>

      <button class="quick-help-item quick-help-whatsapp" type="button" onclick="window.open('https://wa.me/<?= $bizWa ?>','_blank')">
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
          <a href="https://wa.me/<?= $bizWa ?>" aria-label="WhatsApp" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></a>
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
          <span><?= $bizAddr ?></span>
        </div>
        <a class="footer-contact-item" href="tel:<?= preg_replace('/\D+/', '', $bizPhone) ?>">
          <i class="fa-solid fa-phone" aria-hidden="true"></i>
          <span><?= $bizPhone ?></span>
        </a>
        <a class="footer-contact-item" href="mailto:<?= $bizEmail ?>">
          <i class="fa-regular fa-envelope" aria-hidden="true"></i>
          <span><?= $bizEmail ?></span>
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
          <label class="sr-only" for="footerEmail">Enter your email</label>
          <input id="footerEmail" name="email" type="email" placeholder="Enter your email" autocomplete="email">
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

<script>
(() => {
  const panel = document.querySelector('.all-cat-filter-panel');
  if (!panel) return;
  const mobileQuery = window.matchMedia('(max-width: 820px)');
  const syncFilterPanel = (event) => {
    if (mobileQuery.matches) {
      if (!event) panel.open = false;
    } else {
      panel.open = true;
    }
  };
  syncFilterPanel();
  if (typeof mobileQuery.addEventListener === 'function') {
    mobileQuery.addEventListener('change', syncFilterPanel);
  } else if (typeof mobileQuery.addListener === 'function') {
    mobileQuery.addListener(syncFilterPanel);
  }
})();
</script>

<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
