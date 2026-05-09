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
<div class="banner-slider" id="bannerSlider">
  <?php
  $fallbackBanners = [
    [
      'eyebrow' => 'Premium Print Studio',
      'title' => 'Print That Grows<br>Your Business',
      'subtitle' => 'Business cards, flyers, brochures, posters and more with fast Rajkot delivery.',
      'image_path' => 'https://images.unsplash.com/photo-1586953208448-b95a79798f07?w=1400&q=85&fit=crop',
      'image_alt' => 'Premium Business Card Printing',
      'cta_primary_text' => 'Order Now',
      'cta_primary_url' => '/products',
      'cta_secondary_text' => 'Get Free Design',
      'cta_secondary_type' => 'url',
      'cta_secondary_url' => '/#contact-sec',
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
        <?php if ($title !== ''): ?><div class="bs-title"><?= $title ?></div><?php endif; ?>
        <?php if ($subtitle !== ''): ?><div class="bs-sub"><?= $subtitle ?></div><?php endif; ?>
        <?php if ($hasPrimaryCta || $hasSecondaryCta): ?>
          <div class="bs-actions">
            <?php if ($hasPrimaryCta): ?><a href="<?= $ctaPrimaryUrl ?>" class="bs-cta-primary"><?= $ctaPrimaryText ?></a><?php endif; ?>
            <?php if ($hasSecondaryCta): ?>
              <?php if ($ctaSecondaryType === 'url'): ?>
                <a href="<?= $ctaSecondaryUrl ?>" class="bs-cta-wa"><?= $ctaSecondaryText ?></a>
              <?php else: ?>
                <button class="bs-cta-wa" onclick="window.open('https://wa.me/<?= $bizWa ?>','_blank')" type="button"><?= $ctaSecondaryText ?></button>
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

<section class="hp-proof" aria-label="Customer trust highlights">
  <div class="container">
    <div class="hp-proof-row">
      <div class="hp-proof-item"><strong>4.8/5</strong><span>Customer Rating</span></div>
      <div class="hp-proof-item"><strong>5000+</strong><span>Orders Delivered</span></div>
      <div class="hp-proof-item"><strong>24-48 hrs</strong><span>Fast Turnaround</span></div>
      <div class="hp-proof-item"><strong>GST</strong><span>Invoice Available</span></div>
    </div>
  </div>
</section>

<?php
$catSpot = [];
foreach ($categories as $cat) {
  $cid = (int)($cat['id'] ?? 0);
  if ($cid <= 0) continue;
  $catProducts = array_values(array_filter($products, fn($p) => (int)($p['category_id'] ?? 0) === $cid));
  if (!$catProducts) continue;
  usort($catProducts, fn($a, $b) => ((float)($a['min_price'] ?? 0) <=> (float)($b['min_price'] ?? 0)));
  $first = $catProducts[0];
  $catSpot[] = [
    'name' => $cat['name'] ?? 'Category',
    'slug' => $cat['slug'] ?? '',
    'icon' => $cat['icon'] ?? '📦',
    'image' => $first['primary_image'] ?? '',
    'start' => (float)($first['min_price'] ?? 0),
    'count' => count($catProducts),
  ];
}
?>
<!-- ═══════════════════════════════════════════════════════════
     TRUST BAR
══════════════════════════════════════════════════════════════ -->
<div class="trust" data-reveal>
  <div class="container">
    <div class="trust-inner">
      <div class="trust-i" data-reveal data-reveal-delay="40"><div class="trust-ic trust-ic-svg" style="background:#FEF9C3"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.1 6.3 7 .9-5 4.8 1.2 6.9L12 17.8 5.7 21l1.2-6.9-5-4.8 7-.9z"/></svg></div>Free Design Support</div>
      <div class="trust-i" data-reveal data-reveal-delay="80"><div class="trust-ic trust-ic-svg" style="background:#ECFDF5"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16v9H4zm2 2v5h12V8zM2 17h20v2H2z"/></svg></div>Best Print Quality</div>
      <div class="trust-i" data-reveal data-reveal-delay="120"><div class="trust-ic trust-ic-svg" style="background:#EEF3FD"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12h18v2H3zM12 3l4 4h-3v5h-2V7H8zM12 21l-4-4h3v-5h2v5h3z"/></svg></div>Affordable Pricing</div>
      <div class="trust-i" data-reveal data-reveal-delay="160"><div class="trust-ic trust-ic-svg" style="background:#FFF4ED"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4a8 8 0 1 0 8 8h-2a6 6 0 1 1-6-6zm-1 2h2v7h-2zm1 11a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z"/></svg></div>On-Time Delivery</div>
      <div class="trust-i" data-reveal data-reveal-delay="200"><div class="trust-ic trust-ic-svg" style="background:#F0FDF4"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l8 4v6c0 5-3.4 9.7-8 11-4.6-1.3-8-6-8-11V6zm-1 14 6-6-1.4-1.4-4.6 4.6-2.6-2.6L7 12z"/></svg></div>100% Satisfaction</div>
    </div>
  </div>
</div>


<!-- ═══════════════════════════════════════════════════════════
     PRODUCTS SECTION — Category-based boxes with 4 products each
══════════════════════════════════════════════════════════════ -->
<section class="sec home-prod-sec sec-tint-blue" id="prod-sec">
  <div class="home-prod-wrap">

    <div class="sec-hdr" style="margin-bottom:32px" data-reveal>
      <div>
        <div class="sec-ey">Our Products</div>
        <div class="sec-t">Everything You Need to Print</div>
      </div>
      <a href="/products" class="btn btn-outline btn-sm" style="font-size:13px">
        See All Products →
      </a>
    </div>

    <?php
    // Group products by category
    $byCategory = [];
    foreach ($products as $p) {
        $catId = $p['category_id'] ?? 0;
        if (!isset($byCategory[$catId])) {
            $byCategory[$catId] = [
                'name'     => $p['category_name'] ?? 'Other',
                'slug'     => strtolower(preg_replace('/[^a-z0-9]+/', '-', $p['category_name'] ?? 'other')),
                'icon'     => '',
                'products' => [],
            ];
        }
        $byCategory[$catId]['products'][] = $p;
    }
    // Merge icon from $categories array
    foreach ($categories as $cat) {
        if (isset($byCategory[$cat['id']])) {
            $byCategory[$cat['id']]['icon'] = $cat['icon'] ?? '';
            $byCategory[$cat['id']]['slug'] = $cat['slug'] ?? $byCategory[$cat['id']]['slug'];
        }
    }
    ?>

    <?php if (empty($products)): ?>
    <div style="text-align:center;padding:60px 20px;color:var(--text2)">
      <div style="font-size:44px;margin-bottom:12px">🖨️</div>
      <div style="font-size:16px;font-weight:600;margin-bottom:6px">Products coming soon</div>
      <div style="font-size:13px">Check back or contact us via WhatsApp</div>
    </div>
    <?php else: ?>

    <div class="home-cat-grid">
      <?php foreach ($byCategory as $catId => $catData):
        $catProducts = array_slice($catData['products'], 0, 4);
        $catSlug     = htmlspecialchars($catData['slug']);
        $catName     = htmlspecialchars($catData['name']);
        $catIcon     = htmlspecialchars($catData['icon'] ?? '📦');
      ?>
      <div class="cat-box cat-box-square" data-reveal data-reveal-delay="<?= ((int)$catId % 5) * 40 ?>">

        <!-- Category header -->
        <div class="cat-box-hdr">
          <div class="cat-box-title">
            <div class="cat-box-icon"><?= $catIcon ?></div>
            <?= $catName ?>
          </div>
        </div>

        <!-- Products mini grid (4 items preview) -->
        <div class="cat-box-products">
          <?php foreach ($catProducts as $p):
            $img  = $p['primary_image'] ?? '';
            $minP = (float)($p['min_price'] ?? 0);
          ?>
          <a href="/product/<?= htmlspecialchars($p['slug']) ?>" class="cat-mini-prod">
            <div class="cat-mini-img">
              <img src="<?= htmlspecialchars($img) ?>"
                   alt="<?= htmlspecialchars($p['name']) ?>"
                   loading="lazy"
                   onerror="this.src='https://placehold.co/300x300/EEF3FD/1A56E8?text=<?= urlencode($p['name']) ?>'">
            </div>
            <div class="cat-mini-name"><?= htmlspecialchars($p['name']) ?></div>
            <div class="cat-mini-from">Starting from</div>
            <div class="cat-mini-price">
              <?= $minP > 0 ? '₹' . number_format($minP) : '—' ?>
            </div>
          </a>
          <?php endforeach; ?>
        </div>

        <div class="cat-box-ft">
          <a href="/category/<?= $catSlug ?>" class="cat-box-see">
            See all products
          </a>
        </div>

      </div>
      <?php endforeach; ?>
    </div>

    <!-- See all products CTA -->
    <div style="text-align:center;margin-top:36px">
      <a href="/products" class="btn btn-blue" style="padding:14px 36px;font-size:15px">
        🖨️ See All Products
      </a>
    </div>

    <?php endif; ?>

  </div>
</section>


<!-- WHY US -->
<section class="sec sec-tint-warm" id="why-sec" data-reveal>
  <div class="container">
    <div class="sec-hdr" style="margin-bottom:28px">
      <div>
        <div class="sec-ey">Why RCS Graphic</div>
        <div class="sec-t">Quality You Can Count On</div>
      </div>
      <a href="https://wa.me/<?= $bizWa ?>" target="_blank"
         class="btn btn-outline btn-sm" style="font-size:13px">
        💬 Talk to Us
      </a>
    </div>
    <div class="why-grid">

      <div class="why-c" data-reveal data-reveal-delay="30">
        <div class="why-ic" style="background:#EEF3FD">🖨️</div>
        <div class="why-t">Premium Print Quality</div>
        <div class="why-d">State-of-the-art printing technology ensuring sharp, vibrant and consistent results every time.</div>
      </div>

      <div class="why-c" data-reveal data-reveal-delay="60">
        <div class="why-ic" style="background:#ECFDF5">⚡</div>
        <div class="why-t">Fast Turnaround</div>
        <div class="why-d">Same-day and next-day options available. We know deadlines matter for your business.</div>
      </div>

      <div class="why-c" data-reveal data-reveal-delay="90">
        <div class="why-ic" style="background:#FFF4ED">💰</div>
        <div class="why-t">Best Bulk Prices</div>
        <div class="why-d">Competitive pricing with bulk discounts. The more you print, the more you save.</div>
      </div>

      <div class="why-c" data-reveal data-reveal-delay="120">
        <div class="why-ic" style="background:#FEF9C3">🎨</div>
        <div class="why-t">Free Design Support</div>
        <div class="why-d">Our creative team helps you get the perfect design ready for print for Your Bulk Orders.</div>
      </div>

      <div class="why-c" data-reveal data-reveal-delay="150">
        <div class="why-ic" style="background:#F0F9FF">📦</div>
        <div class="why-t">Safe Packaging</div>
        <div class="why-d">Every order is carefully packed to ensure your prints arrive in perfect condition.</div>
      </div>

      <div class="why-c" data-reveal data-reveal-delay="180">
        <div class="why-ic" style="background:#FDF4FF">🤝</div>
        <div class="why-t">Dedicated Support</div>
        <div class="why-d">Personal support via WhatsApp and phone. We're here at every step of your order.</div>
      </div>

    </div>
  </div>
</section>

<?php if (!empty($catSpot)): ?>
<section class="cs-section sec-tint-blue cs-full" data-reveal>
  <div class="container">
    <div class="sec-hdr" style="margin-bottom:18px">
      <div>
        <div class="sec-ey">Explore Print Categories</div>
        <div class="sec-t">Pick Your Perfect Category</div>
      </div>
    </div>
    <div class="cs-wrap" id="csWrap">
      <button class="cs-nav prev" type="button" aria-label="Previous category" onclick="csPrev()">‹</button>
      <div class="cs-track" id="csTrack">
        <?php foreach ($catSpot as $i => $c): ?>
        <article class="cs-card<?= $i === 0 ? ' is-active' : '' ?>">
          <a href="/category/<?= htmlspecialchars($c['slug']) ?>" class="cs-link">
            <div class="cs-img">
              <img src="<?= htmlspecialchars($c['image']) ?>" alt="<?= htmlspecialchars($c['name']) ?>" loading="lazy">
            </div>
            <div class="cs-body">
              <div class="cs-title"><?= htmlspecialchars($c['icon']) ?> <?= htmlspecialchars($c['name']) ?></div>
              <div class="cs-meta"><?= (int)$c['count'] ?> products · Starting from ₹<?= $c['start'] > 0 ? number_format($c['start']) : '—' ?></div>
              <span class="cs-cta">View Category →</span>
            </div>
          </a>
        </article>
        <?php endforeach; ?>
      </div>
      <button class="cs-nav next" type="button" aria-label="Next category" onclick="csNext()">›</button>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- CTA BAND -->
<section class="sec">
  <div class="container">
    <div class="cta" data-reveal>
      <div style="position:relative;z-index:1">
        <div class="cta-h">Ready to Print Something Amazing?</div>
        <div class="cta-s">Place your order in minutes. We'll take care of the rest.</div>
      </div>
      <div class="cta-btns">
        <a href="#prod-sec" class="btn" style="background:#fff;color:var(--blue);font-weight:700">Order Now →</a>
        <button class="btn" style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3)"
                onclick="window.open('https://wa.me/<?= $bizWa ?>','_blank')">💬 WhatsApp</button>
      </div>
    </div>
  </div>
</section>

<!-- CONTACT -->
<section class="sec sec-tint-blue" id="contact-sec" data-reveal>
  <div class="container">
    <div class="contact-grid">
      <div>
        <div class="sec-ey">Get In Touch</div>
        <div class="sec-t" style="font-size:26px;margin-bottom:14px">Let's Talk About Your Project</div>
        <p style="font-size:14px;color:var(--text2);line-height:1.7;margin-bottom:22px">Custom requirements? Bulk pricing? Just say hello!</p>
        <div class="cinfo-card">
          <div class="ci-item"><div class="ci-ic">📞</div><div><div class="ci-lbl">Phone</div><div class="ci-v"><?= $bizPhone ?></div></div></div>
          <div class="ci-item"><div class="ci-ic">📧</div><div><div class="ci-lbl">Email</div><div class="ci-v"><?= $bizEmail ?></div></div></div>
          <div class="ci-item"><div class="ci-ic">📍</div><div><div class="ci-lbl">Address</div><div class="ci-v"><?= $bizAddr ?></div></div></div>
          <div class="ci-item" style="border:none"><div class="ci-ic">🕐</div><div><div class="ci-lbl">Hours</div><div class="ci-v">Mon–Sat · 9AM–7PM</div></div></div>
        </div>
      </div>
      <div>
        <div class="fg"><label>Your Name</label><input class="fi" id="ct-name" placeholder="Full Name"></div>
        <div class="fg"><label>Phone / Email</label><input class="fi" id="ct-contact" placeholder="+91 98765 43210"></div>
        <div class="fg"><label>Message</label><textarea class="fi" id="ct-msg" style="height:96px" placeholder="Tell us about your print requirement…"></textarea></div>
        <button class="btn btn-blue btn-full" onclick="sendEnquiry()" style="padding:14px">Send Enquiry →</button>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div class="f-col-brand">
        <div class="f-brand"><?= $bizName ?></div>
        <div class="f-desc">Premium printing services for businesses. Quality that speaks for itself.</div>
        <div class="f-brand-cta">
          <button class="f-wa-btn" onclick="window.open('https://wa.me/<?= $bizWa ?>','_blank')">💬 WhatsApp</button>
        </div>
      </div>
      <div>
        <div class="f-lbl">Products</div>
        <?php foreach (array_slice($categories ?? [], 0, 6) as $c): ?>
        <a class="f-link" href="/category/<?= htmlspecialchars($c['slug']) ?>"><?= htmlspecialchars($c['name']) ?></a>
        <?php endforeach; ?>
      </div>
      <div>
        <div class="f-lbl">Company</div>
        <a class="f-link" href="/">Home</a>
        <a class="f-link" href="/#why-sec">Why Us</a>
        <a class="f-link" href="/login">Login / Register</a>
        <a class="f-link" href="/admin">Admin Panel</a>
      </div>
      <div>
        <div class="f-lbl">Contact</div>
        <div class="f-contact">
          <div class="f-link"><?= $bizPhone ?></div>
          <div class="f-link"><?= $bizEmail ?></div>
          <div class="f-link" onclick="window.open('https://wa.me/<?= $bizWa ?>','_blank')" style="cursor:pointer">WhatsApp Chat</div>
        </div>
      </div>
    </div>
    <div class="f-bot">
      <div>© <?= date('Y') ?> <?= $bizName ?>. All rights reserved.</div>
      <div>Developed By Prakash Karena</div>
    </div>
  </div>
</footer>

<div class="mob-quick-cta" role="navigation" aria-label="Quick actions">
  <a href="tel:<?= preg_replace('/\D+/', '', $bizPhone) ?>" class="mq-btn">Call</a>
  <button type="button" class="mq-btn mq-btn-wa" onclick="window.open('https://wa.me/<?= $bizWa ?>','_blank')">WhatsApp</button>
  <a href="#prod-sec" class="mq-btn mq-btn-primary">Start Order</a>
</div>

<!-- No carousel/filter JS needed with new category layout -->

<script>
// Enquiry (WhatsApp)
function sendEnquiry() {
  const n = document.getElementById('ct-name').value.trim();
  const c = document.getElementById('ct-contact').value.trim();
  const m = document.getElementById('ct-msg').value.trim();
  if (!n || !c) { toast('Fill name and contact', 'error'); return; }
  window.open(`https://wa.me/<?= $bizWa ?>?text=${encodeURIComponent('📩 Enquiry:\nName: ' + n + '\nContact: ' + c + '\nMessage: ' + (m || '—'))}`, '_blank');
  toast('Enquiry sent!', 'success');
  document.getElementById('ct-name').value = '';
  document.getElementById('ct-contact').value = '';
  document.getElementById('ct-msg').value = '';
}
</script>

<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
