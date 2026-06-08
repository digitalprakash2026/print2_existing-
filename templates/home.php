<?php
/**
 * home.php — Home page
 * Includes: head.php (<!DOCTYPE + <head>), then header.php (full header + drawer + cart)
 * Note: Do NOT re-include cart-drawer.php here — header.php already does it.
 */
$pageTitle = ($settingsMap['biz_name'] ?? 'RCS Graphic') . ' — Premium Print Ordering';
$pageDesc  = 'Professional printing services in Rajkot — business cards, brochures, banners and more. Fast delivery, GST invoice, secure payment.';
include INCLUDE_PATH . '/partials/head.php';    // outputs <!DOCTYPE><html><head>...</head><body>
include INCLUDE_PATH . '/partials/header.php';  // outputs header + cart drawer + global JS

$bizName  = htmlspecialchars($settingsMap['biz_name']    ?? 'RCS Graphic');
$bizPhone = htmlspecialchars($settingsMap['biz_phone']   ?? '+91 98765 43210');
$bizWa    = htmlspecialchars($settingsMap['biz_whatsapp']?? '919876543210');
$bizEmail = htmlspecialchars($settingsMap['biz_email']   ?? 'hello@rcsgraphic.in');
$bizAddr  = htmlspecialchars($settingsMap['biz_address'] ?? 'Rajkot, Gujarat');
?>

<!-- ═══════════════════════════════════════════════════════════
     BANNER SLIDER
     ─────────────────────────────────────────────────────────
     Admin-managed, image-first banner slider. Optional text/CTA fields
     render only when filled, so a designed clickable banner image can
     stand on its own across desktop and mobile.
═══════════════════════════════════════════════════════════════ -->
<div class="banner-slider" id="bannerSlider" data-design-target="home.banner">
  <?php
  $fallbackBanners = [
    [
      'eyebrow' => 'Premium Print Studio',
      'title' => 'Print That Grows<br>Your Business',
      'subtitle' => 'Business cards, flyers, brochures, posters and more with fast Rajkot delivery.',
      'image_path' => 'https://images.unsplash.com/photo-1586953208448-b95a79798f07?w=1400&q=85&fit=crop',
      'image_alt' => 'Premium Business Card Printing',
      'cta_primary_text' => 'Order Now',
      'cta_primary_url' => '/categories',
      'cta_secondary_text' => 'Get Free Design',
      'cta_secondary_type' => 'url',
      'cta_secondary_url' => '/#quick-help-sec',
    ],
  ];
  $bannerSlides = array_values(array_filter(!empty($homeBanners ?? []) ? $homeBanners : $fallbackBanners, static function ($slide) {
    return trim((string)($slide['image_path'] ?? '')) !== '';
  }));
  $formatBannerHtml = static function ($value): string {
    $safe = htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
    return preg_replace('/&lt;br\s*\/?&gt;/i', '<br>', $safe) ?? $safe;
  };
  foreach ($bannerSlides as $i => $slide):
    $rawImg = trim((string)($slide['image_path'] ?? ''));
    $img = htmlspecialchars($rawImg, ENT_QUOTES, 'UTF-8');
    $altText = trim((string)($slide['image_alt'] ?? '')) ?: ('RCS Graphic banner ' . ($i + 1));
    $alt = htmlspecialchars($altText, ENT_QUOTES, 'UTF-8');
    $eyebrow = trim((string)($slide['eyebrow'] ?? ''));
    $title = $formatBannerHtml($slide['title'] ?? '');
    $subtitle = $formatBannerHtml($slide['subtitle'] ?? '');
    $ctaPrimaryTextRaw = trim((string)($slide['cta_primary_text'] ?? ''));
    $ctaPrimaryUrlRaw = trim((string)($slide['cta_primary_url'] ?? ''));
    $ctaPrimaryText = htmlspecialchars($ctaPrimaryTextRaw, ENT_QUOTES, 'UTF-8');
    $ctaPrimaryUrl = htmlspecialchars($ctaPrimaryUrlRaw, ENT_QUOTES, 'UTF-8');
    $ctaSecondaryTextRaw = trim((string)($slide['cta_secondary_text'] ?? ''));
    $ctaSecondaryText = htmlspecialchars($ctaSecondaryTextRaw, ENT_QUOTES, 'UTF-8');
    $ctaSecondaryType = strtolower(trim((string)($slide['cta_secondary_type'] ?? 'whatsapp')));
    $ctaSecondaryUrlRaw = trim((string)($slide['cta_secondary_url'] ?? ''));
    $ctaSecondaryUrl = htmlspecialchars($ctaSecondaryUrlRaw, ENT_QUOTES, 'UTF-8');
    $hasPrimaryCta = $ctaPrimaryTextRaw !== '' && $ctaPrimaryUrlRaw !== '' && $ctaPrimaryUrlRaw !== '#';
    $hasSecondaryCta = $ctaSecondaryTextRaw !== '' && ($ctaSecondaryType !== 'url' || ($ctaSecondaryUrlRaw !== '' && $ctaSecondaryUrlRaw !== '#'));
    $hasContent = $eyebrow !== '' || $title !== '' || $subtitle !== '' || $hasPrimaryCta || $hasSecondaryCta;
    $slideClickUrl = $hasPrimaryCta ? $ctaPrimaryUrl : '';
  ?>
  <div class="bs-slide <?= $hasContent ? 'has-content' : 'image-only' ?>">
    <?php if ($slideClickUrl !== ''): ?>
      <a class="bs-image-link" href="<?= $slideClickUrl ?>" aria-label="<?= $alt ?>">
        <img src="<?= $img ?>" alt="<?= $alt ?>" loading="<?= $i === 0 ? 'eager' : 'lazy' ?>" decoding="async">
      </a>
    <?php else: ?>
      <img src="<?= $img ?>" alt="<?= $alt ?>" loading="<?= $i === 0 ? 'eager' : 'lazy' ?>" decoding="async">
    <?php endif; ?>
    <?php if ($hasContent): ?>
      <div class="bs-overlay" aria-hidden="true"></div>
      <div class="bs-content">
        <?php if ($eyebrow !== ''): ?><div class="bs-eyebrow"><?= htmlspecialchars($eyebrow, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if ($title !== ''): ?><div class="bs-title" data-design-target="home.banner.title"><?= $title ?></div><?php endif; ?>
        <?php if ($subtitle !== ''): ?><div class="bs-sub" data-design-target="home.banner.subtitle"><?= $subtitle ?></div><?php endif; ?>
        <?php if ($hasPrimaryCta || $hasSecondaryCta): ?>
          <div class="bs-actions">
            <?php if ($hasPrimaryCta): ?><a href="<?= $ctaPrimaryUrl ?>" class="bs-cta-primary" data-design-target="home.banner.buttons"><?= $ctaPrimaryText ?></a><?php endif; ?>
            <?php if ($hasSecondaryCta): ?>
              <?php if ($ctaSecondaryType === 'url'): ?>
                <a href="<?= $ctaSecondaryUrl ?>" class="bs-cta-wa" data-design-target="home.banner.buttons"><?= $ctaSecondaryText ?></a>
              <?php else: ?>
                <button class="bs-cta-wa" data-design-target="home.banner.buttons" onclick="window.open('https://wa.me/<?= $bizWa ?>','_blank')" type="button"><?= $ctaSecondaryText ?></button>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>

  <?php if (count($bannerSlides) > 1): ?>
    <!-- Prev / Next arrows -->
    <button class="bs-prev" onclick="document.getElementById('bannerSlider')._sliderPrev()" aria-label="Previous slide">
      <svg viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
    </button>
    <button class="bs-next" onclick="document.getElementById('bannerSlider')._sliderNext()" aria-label="Next slide">
      <svg viewBox="0 0 24 24"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
    </button>

    <!-- Dot indicators -->
    <div class="bs-dots">
      <?php foreach ($bannerSlides as $i => $_): ?>
        <button class="bs-dot" onclick="document.getElementById('bannerSlider')._sliderGoTo(<?= (int)$i ?>)" aria-label="Slide <?= (int)$i + 1 ?>"></button>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>


<?php
$catSpot = [];
foreach ($categories as $cat) {
  $cid = (int)($cat['id'] ?? 0);
  if ($cid <= 0 || (int)($cat['is_active'] ?? 1) !== 1) continue;
  $catProducts = array_values(array_filter($products, fn($p) => (int)($p['category_id'] ?? 0) === $cid));
  usort($catProducts, fn($a, $b) => ((float)($a['min_price'] ?? 0) <=> (float)($b['min_price'] ?? 0)));
  $first = $catProducts[0] ?? [];
  $catImage = trim((string)($cat['image_path'] ?? ''));
  if ($catImage === '') {
    $catImage = trim((string)($first['primary_image'] ?? ''));
  }
  $catSpot[] = [
    'name' => $cat['name'] ?? 'Category',
    'slug' => $cat['slug'] ?? '',
    'icon' => $cat['icon'] ?? '📦',
    'image' => $catImage,
    'image_alt' => trim((string)($cat['image_alt'] ?? '')) ?: ($cat['name'] ?? 'Category'),
    'start' => (float)($first['min_price'] ?? 0),
    'count' => count($catProducts),
  ];
}
?>

<?php if (!empty($catSpot)): ?>
<section class="shop-cat-section" data-design-target="home.categories.section" aria-labelledby="shopCatTitle" data-reveal>
  <div class="shop-cat-container">
    <div class="shop-cat-head">
      <h2 class="shop-cat-title" id="shopCatTitle" data-design-target="home.categories.title">Shop By <span>Category</span></h2>
      <a href="/categories" class="shop-cat-all">View All Categories</a>
    </div>

    <div class="shop-cat-track" id="shopCatTrack" aria-label="Product categories" data-auto-slide="true">
      <?php foreach ($catSpot as $i => $c): ?>
        <article class="shop-cat-card">
          <a href="/category/<?= htmlspecialchars($c['slug']) ?>" class="shop-cat-link" data-design-target="home.category.card">
            <div class="shop-cat-img">
              <?php if (!empty($c['image'])): ?>
                <img src="<?= htmlspecialchars($c['image']) ?>" alt="<?= htmlspecialchars($c['image_alt'] ?? $c['name']) ?>" loading="lazy">
              <?php else: ?>
                <div class="shop-cat-fallback" aria-hidden="true"><?= htmlspecialchars($c['icon']) ?></div>
              <?php endif; ?>
            </div>
            <div class="shop-cat-body">
              <span class="shop-cat-icon" aria-hidden="true"><?= htmlspecialchars($c['icon']) ?></span>
              <span class="shop-cat-name" data-design-target="home.category.name"><?= htmlspecialchars($c['name']) ?></span>
            </div>
          </a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>


<!-- WHY CHOOSE -->
<section class="why-print-section" id="why-sec" data-reveal>
  <div class="why-print-container">
    <h2 class="why-print-heading">Why Choose <span>RCS PRINT?</span></h2>

    <div class="why-print-panel" aria-label="Why choose RCS Print">
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
  </div>
</section>

<!-- BEST DEALS -->
<section class="best-deals-section" aria-labelledby="bestDealsTitle" data-reveal>
  <div class="best-deals-container">
    <h2 class="best-deals-heading" id="bestDealsTitle">Our <span>Best Deals</span></h2>

    <?php
    $fallbackDeals = [
      [
        'deal_type' => 'deal',
        'title' => '500 Visiting Cards',
        'subtitle' => 'Starting from',
        'price_text' => '₹199',
        'image_path' => 'https://images.unsplash.com/photo-1586953208448-b95a79798f07?w=700&q=85&fit=crop',
        'image_alt' => '500 visiting cards printing deal',
        'cta_text' => 'Order Now',
        'cta_url' => '/categories',
        'color_theme' => 'green',
      ],
      [
        'deal_type' => 'deal',
        'title' => '1000 Flyers',
        'subtitle' => 'Starting from',
        'price_text' => '₹499',
        'image_path' => 'https://images.unsplash.com/photo-1541746972996-4e0b0f43e02a?w=700&q=85&fit=crop',
        'image_alt' => '1000 flyers printing deal',
        'cta_text' => 'Order Now',
        'cta_url' => '/categories',
        'color_theme' => 'orange',
      ],
      [
        'deal_type' => 'deal',
        'title' => 'Brochure (A4)',
        'subtitle' => 'Starting from',
        'price_text' => '₹799',
        'image_path' => 'https://images.unsplash.com/photo-1600172454284-934feca24de6?w=700&q=85&fit=crop',
        'image_alt' => 'A4 brochure printing deal',
        'cta_text' => 'Order Now',
        'cta_url' => '/categories',
        'color_theme' => 'purple',
      ],
      [
        'deal_type' => 'promo',
        'title' => 'Get',
        'highlight_text' => 'FREE Design',
        'subtitle' => 'on Your First Order!',
        'image_path' => '',
        'image_alt' => 'Free design offer',
        'cta_text' => 'Get Free Design',
        'cta_url' => '/#quick-help-sec',
        'color_theme' => 'purple',
      ],
    ];
    $bestDeals = !empty($homeDeals ?? []) ? $homeDeals : $fallbackDeals;
    $dealThemes = ['green', 'orange', 'purple'];
    $formatDealText = static function ($value): string {
      $safe = htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
      return preg_replace('/&lt;br\s*\/?&gt;/i', '<br>', $safe) ?? $safe;
    };
    ?>

    <div class="best-deals-grid">
      <?php foreach ($bestDeals as $deal):
        $dealType = strtolower(trim((string)($deal['deal_type'] ?? 'deal')));
        $themeRaw = strtolower(trim((string)($deal['color_theme'] ?? 'green')));
        $theme = in_array($themeRaw, $dealThemes, true) ? $themeRaw : 'green';
        $titleRaw = trim((string)($deal['title'] ?? ''));
        $highlightRaw = trim((string)($deal['highlight_text'] ?? ''));
        $subtitleRaw = trim((string)($deal['subtitle'] ?? ''));
        $priceRaw = trim((string)($deal['price_text'] ?? ''));
        $imageRaw = trim((string)($deal['image_path'] ?? ''));
        $imageAltRaw = trim((string)($deal['image_alt'] ?? '')) ?: ($titleRaw !== '' ? $titleRaw : 'Best deal');
        $ctaTextRaw = trim((string)($deal['cta_text'] ?? '')) ?: ($dealType === 'promo' ? 'Get Offer' : 'Order Now');
        $ctaUrlRaw = trim((string)($deal['cta_url'] ?? '')) ?: '/categories';
        $title = $formatDealText($titleRaw);
        $highlight = htmlspecialchars($highlightRaw, ENT_QUOTES, 'UTF-8');
        $subtitle = $formatDealText($subtitleRaw);
        $price = htmlspecialchars($priceRaw, ENT_QUOTES, 'UTF-8');
        $image = htmlspecialchars($imageRaw, ENT_QUOTES, 'UTF-8');
        $imageAlt = htmlspecialchars($imageAltRaw, ENT_QUOTES, 'UTF-8');
        $ctaText = htmlspecialchars($ctaTextRaw, ENT_QUOTES, 'UTF-8');
        $ctaUrl = htmlspecialchars($ctaUrlRaw, ENT_QUOTES, 'UTF-8');
      ?>
        <?php if ($dealType === 'promo'): ?>
          <article class="deal-promo-card" data-design-target="home.deal.card">
            <div class="deal-confetti" aria-hidden="true"></div>
            <div class="deal-promo-copy">
              <h3>
                <?= $title ?><?php if ($highlight !== ''): ?> <span><?= $highlight ?></span><?php endif; ?>
                <?php if ($subtitle !== ''): ?><br><?= $subtitle ?><?php endif; ?>
              </h3>
              <a href="<?= $ctaUrl ?>" class="deal-promo-btn"><?= $ctaText ?></a>
            </div>
            <?php if ($imageRaw !== ''): ?>
              <div class="deal-gift deal-gift-image">
                <img src="<?= $image ?>" alt="<?= $imageAlt ?>" loading="lazy">
              </div>
            <?php else: ?>
              <div class="deal-gift" aria-hidden="true">
                <div class="deal-gift-bow"></div>
                <div class="deal-gift-box"></div>
              </div>
            <?php endif; ?>
          </article>
        <?php else: ?>
          <article class="deal-card deal-<?= htmlspecialchars($theme, ENT_QUOTES, 'UTF-8') ?>">
            <a href="<?= $ctaUrl ?>" class="deal-card-link" data-design-target="home.deal.card">
              <div class="deal-card-img">
                <?php if ($imageRaw !== ''): ?>
                  <img src="<?= $image ?>" alt="<?= $imageAlt ?>" loading="lazy">
                <?php endif; ?>
              </div>
              <div class="deal-card-band">
                <div class="deal-copy">
                  <h3><?= $title ?></h3>
                  <?php if ($subtitle !== ''): ?><p><?= $subtitle ?></p><?php endif; ?>
                  <?php if ($price !== ''): ?><strong><?= $price ?></strong><?php endif; ?>
                </div>
                <span class="deal-order-btn"><?= $ctaText ?></span>
              </div>
            </a>
          </article>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="how-works-section" aria-labelledby="howWorksTitle" data-reveal>
  <div class="how-works-container">
    <h2 class="how-works-title" id="howWorksTitle">How It <span>Works</span></h2>
    <div class="how-works-panel">
      <div class="how-works-track" role="list">
        <article class="how-step" role="listitem">
          <div class="how-icon how-icon-white"><i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i></div>
          <div class="how-copy">
            <span>01</span>
            <h3>Upload or Request<br> Your Design</h3>
          </div>
        </article>

        <div class="how-arrow" aria-hidden="true">→</div>

        <article class="how-step" role="listitem">
          <div class="how-icon how-icon-orange"><i class="fa-solid fa-pencil" aria-hidden="true"></i></div>
          <div class="how-copy">
            <span>02</span>
            <h3>Approve<br> Your Design</h3>
          </div>
        </article>

        <div class="how-arrow" aria-hidden="true">→</div>

        <article class="how-step" role="listitem">
          <div class="how-icon how-icon-green"><i class="fa-solid fa-print" aria-hidden="true"></i></div>
          <div class="how-copy">
            <span>03</span>
            <h3>We Print<br> Your Order</h3>
          </div>
        </article>

        <div class="how-arrow" aria-hidden="true">→</div>

        <article class="how-step" role="listitem">
          <div class="how-icon how-icon-white"><i class="fa-solid fa-truck-fast" aria-hidden="true"></i></div>
          <div class="how-copy">
            <span>04</span>
            <h3>We Deliver<br> At Your Doorstep</h3>
          </div>
        </article>
      </div>
    </div>
  </div>
</section>


<!-- CUSTOMER TESTIMONIALS -->
<section class="customer-say-section" aria-labelledby="customerSayTitle" data-reveal>
  <div class="customer-say-container">
    <h2 class="customer-say-title" id="customerSayTitle">What Our <span>Customers</span> Say</h2>

    <div class="customer-say-shell">
      <div class="customer-say-track" id="customerSayTrack" role="list" data-auto-slide="true">
        <article class="customer-card" role="listitem">
          <i class="fa-solid fa-quote-left customer-quote" aria-hidden="true"></i>
          <p class="customer-text">Excellent quality printing and super fast service. Highly recommended!</p>
          <div class="customer-stars" aria-label="5 out of 5 stars">
            <i class="fa-solid fa-star" aria-hidden="true"></i>
            <i class="fa-solid fa-star" aria-hidden="true"></i>
            <i class="fa-solid fa-star" aria-hidden="true"></i>
            <i class="fa-solid fa-star" aria-hidden="true"></i>
            <i class="fa-solid fa-star" aria-hidden="true"></i>
          </div>
          <div class="customer-profile">
            <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=160&q=80&fit=crop&crop=faces" alt="Rakesh Mehta" loading="lazy">
            <div>
              <h3>– Rakesh Mehta</h3>
              <span>Business Owner</span>
            </div>
          </div>
        </article>

        <article class="customer-card" role="listitem">
          <i class="fa-solid fa-quote-left customer-quote" aria-hidden="true"></i>
          <p class="customer-text">Very professional design support and premium quality prints.</p>
          <div class="customer-stars" aria-label="5 out of 5 stars">
            <i class="fa-solid fa-star" aria-hidden="true"></i>
            <i class="fa-solid fa-star" aria-hidden="true"></i>
            <i class="fa-solid fa-star" aria-hidden="true"></i>
            <i class="fa-solid fa-star" aria-hidden="true"></i>
            <i class="fa-solid fa-star" aria-hidden="true"></i>
          </div>
          <div class="customer-profile">
            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=160&q=80&fit=crop&crop=faces" alt="Khushbu Shah" loading="lazy">
            <div>
              <h3>– Khushbu Shah</h3>
              <span>Marketing Head</span>
            </div>
          </div>
        </article>

        <article class="customer-card" role="listitem">
          <i class="fa-solid fa-quote-left customer-quote" aria-hidden="true"></i>
          <p class="customer-text">Best experience for bulk printing. Great price and on-time delivery.</p>
          <div class="customer-stars" aria-label="5 out of 5 stars">
            <i class="fa-solid fa-star" aria-hidden="true"></i>
            <i class="fa-solid fa-star" aria-hidden="true"></i>
            <i class="fa-solid fa-star" aria-hidden="true"></i>
            <i class="fa-solid fa-star" aria-hidden="true"></i>
            <i class="fa-solid fa-star" aria-hidden="true"></i>
          </div>
          <div class="customer-profile">
            <img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=160&q=80&fit=crop&crop=faces" alt="Jigar Patel" loading="lazy">
            <div>
              <h3>– Jigar Patel</h3>
              <span>Event Organizer</span>
            </div>
          </div>
        </article>
      </div>

    </div>

    <div class="customer-dots" aria-label="Testimonials pagination">
      <span class="customer-dot customer-dot-green"></span>
      <span class="customer-dot customer-dot-active"></span>
      <span class="customer-dot customer-dot-green"></span>
    </div>
  </div>
</section>


<!-- FROM OUR BLOGS -->
<section class="blog-section" id="blogs-sec" aria-labelledby="blogTitle" data-reveal>
  <div class="blog-container">
    <div class="blog-head">
      <h2 class="blog-title" id="blogTitle">From Our <span>Blogs</span></h2>
      <a class="blog-view-all" href="/blogs">View All</a>
    </div>

    <?php
    $fallbackBlogs = [
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
    $blogCards = !empty($homeBlogs ?? []) ? $homeBlogs : $fallbackBlogs;
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
    ?>

    <div class="blog-grid" id="blogGrid" role="list" data-auto-slide="true">
      <?php foreach ($blogCards as $blog):
        $blogTitleRaw = trim((string)($blog['title'] ?? 'Blog'));
        $blogSlugRaw = trim((string)($blog['slug'] ?? ''));
        $blogUrl = $blogSlugRaw !== '' ? '/blog/' . rawurlencode($blogSlugRaw) : '/#blogs-sec';
        $blogImageRaw = trim((string)($blog['featured_image'] ?? ''));
        $blogAltRaw = trim((string)($blog['image_alt'] ?? '')) ?: $blogTitleRaw;
        $blogCategoryRaw = trim((string)($blog['category'] ?? 'Print Tips')) ?: 'Print Tips';
        $blogExcerptRaw = trim((string)($blog['excerpt'] ?? ''));
        $blogTitle = htmlspecialchars($blogTitleRaw, ENT_QUOTES, 'UTF-8');
        $blogImage = htmlspecialchars($blogImageRaw, ENT_QUOTES, 'UTF-8');
        $blogAlt = htmlspecialchars($blogAltRaw, ENT_QUOTES, 'UTF-8');
        $blogCategory = htmlspecialchars($blogCategoryRaw, ENT_QUOTES, 'UTF-8');
        $blogExcerpt = htmlspecialchars($blogExcerptRaw, ENT_QUOTES, 'UTF-8');
        $blogDate = htmlspecialchars($formatBlogDate($blog['published_at'] ?? ''), ENT_QUOTES, 'UTF-8');
        $badgeClass = $blogBadgeClass($blog['badge_theme'] ?? 'purple');
      ?>
      <article class="blog-card" role="listitem">
        <a href="<?= htmlspecialchars($blogUrl, ENT_QUOTES, 'UTF-8') ?>" class="blog-card-link" data-design-target="home.blog.card" aria-label="Read blog: <?= $blogTitle ?>">
          <div class="blog-image">
            <?php if ($blogImageRaw !== ''): ?>
              <img src="<?= $blogImage ?>" alt="<?= $blogAlt ?>" loading="lazy">
            <?php endif; ?>
            <span class="blog-badge<?= $badgeClass ?>"><?= $blogCategory ?></span>
          </div>
          <div class="blog-content">
            <div class="blog-meta"><i class="fa-regular fa-calendar" aria-hidden="true"></i> <?= $blogDate ?></div>
            <h3><?= $blogTitle ?></h3>
            <?php if ($blogExcerpt !== ''): ?><p><?= $blogExcerpt ?></p><?php endif; ?>
            <span class="blog-read-more">Read More <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
          </div>
        </a>
      </article>
      <?php endforeach; ?>
    </div>

    <?php if (count($blogCards) > 1): ?>
    <div class="blog-dots" aria-label="Blog pagination">
      <?php foreach ($blogCards as $idx => $_blog): ?>
        <span class="blog-dot <?= $idx === 0 ? 'blog-dot-active' : '' ?>"></span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>


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
<footer class="footer" aria-label="Site footer" data-design-target="footer.section">
  <div class="footer-container">
    <div class="footer-main">
      <div class="footer-brand-col">
        <a href="/" class="footer-logo" aria-label="RCS Print home">
          <span class="footer-logo-main">RCS</span>
          <span class="footer-logo-sub">PRINT</span>
        </a>
        <p class="footer-desc" data-design-target="footer.links">Your one-stop solution for all your printing needs. Quality prints that represent your brand perfectly.</p>
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
  const track = document.getElementById('shopCatTrack');
  if (!track) return;
  let timer = null;
  const getStep = () => {
    const card = track.querySelector('.shop-cat-card');
    if (!card) return Math.max(180, Math.round(track.clientWidth * 0.7));
    const gap = parseFloat(getComputedStyle(track).gap || '0');
    return Math.max(120, card.getBoundingClientRect().width + gap);
  };
  const slideNext = () => {
    const maxScroll = track.scrollWidth - track.clientWidth;
    if (maxScroll <= 4) return;
    if (track.scrollLeft >= maxScroll - 8) {
      track.scrollTo({ left: 0, behavior: 'smooth' });
      return;
    }
    track.scrollBy({ left: getStep(), behavior: 'smooth' });
  };
  const start = () => {
    stop();
    timer = window.setInterval(slideNext, 3500);
  };
  const stop = () => {
    if (timer) window.clearInterval(timer);
    timer = null;
  };
  track.addEventListener('mouseenter', stop);
  track.addEventListener('mouseleave', start);
  track.addEventListener('focusin', stop);
  track.addEventListener('focusout', start);
  document.addEventListener('visibilitychange', () => document.hidden ? stop() : start());
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  start();
})();

(() => {
  const track = document.getElementById('blogGrid');
  if (!track) return;
  let timer = null;
  const getStep = () => {
    const card = track.querySelector('.blog-card');
    if (!card) return Math.max(220, Math.round(track.clientWidth * 0.7));
    const gap = parseFloat(getComputedStyle(track).gap || '0');
    return Math.max(160, card.getBoundingClientRect().width + gap);
  };
  const slideNext = () => {
    const maxScroll = track.scrollWidth - track.clientWidth;
    if (maxScroll <= 4) return;
    if (track.scrollLeft >= maxScroll - 8) {
      track.scrollTo({ left: 0, behavior: 'smooth' });
      return;
    }
    track.scrollBy({ left: getStep(), behavior: 'smooth' });
  };
  const start = () => {
    stop();
    timer = window.setInterval(slideNext, 3600);
  };
  const stop = () => {
    if (timer) window.clearInterval(timer);
    timer = null;
  };
  track.addEventListener('mouseenter', stop);
  track.addEventListener('mouseleave', start);
  track.addEventListener('focusin', stop);
  track.addEventListener('focusout', start);
  document.addEventListener('visibilitychange', () => document.hidden ? stop() : start());
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  start();
})();

(() => {
  const track = document.getElementById('customerSayTrack');
  if (!track) return;
  let timer = null;
  const getStep = () => {
    const card = track.querySelector('.customer-card');
    if (!card) return Math.max(220, Math.round(track.clientWidth * 0.7));
    const gap = parseFloat(getComputedStyle(track).gap || '0');
    return Math.max(160, card.getBoundingClientRect().width + gap);
  };
  const slideNext = () => {
    const maxScroll = track.scrollWidth - track.clientWidth;
    if (maxScroll <= 4) return;
    if (track.scrollLeft >= maxScroll - 8) {
      track.scrollTo({ left: 0, behavior: 'smooth' });
      return;
    }
    track.scrollBy({ left: getStep(), behavior: 'smooth' });
  };
  const start = () => {
    stop();
    timer = window.setInterval(slideNext, 3000);
  };
  const stop = () => {
    if (timer) window.clearInterval(timer);
    timer = null;
  };
  track.addEventListener('mouseenter', stop);
  track.addEventListener('mouseleave', start);
  track.addEventListener('focusin', stop);
  track.addEventListener('focusout', start);
  document.addEventListener('visibilitychange', () => document.hidden ? stop() : start());
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  start();
})();

</script>

<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
