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

<?php if (!empty($catSpot)): ?>
<section class="shop-cat-section" aria-labelledby="shopCatTitle" data-reveal>
  <div class="shop-cat-container">
    <div class="shop-cat-head">
      <h2 class="shop-cat-title" id="shopCatTitle">Shop By <span>Category</span></h2>
      <a href="/products" class="shop-cat-all">View All Products</a>
    </div>

    <div class="shop-cat-track" aria-label="Product categories">
      <?php foreach ($catSpot as $i => $c): ?>
        <article class="shop-cat-card">
          <a href="/category/<?= htmlspecialchars($c['slug']) ?>" class="shop-cat-link">
            <div class="shop-cat-img">
              <?php if (!empty($c['image'])): ?>
                <img src="<?= htmlspecialchars($c['image']) ?>" alt="<?= htmlspecialchars($c['name']) ?>" loading="lazy">
              <?php else: ?>
                <div class="shop-cat-fallback" aria-hidden="true"><?= htmlspecialchars($c['icon']) ?></div>
              <?php endif; ?>
            </div>
            <div class="shop-cat-body">
              <span class="shop-cat-icon" aria-hidden="true"><?= htmlspecialchars($c['icon']) ?></span>
              <span class="shop-cat-name"><?= htmlspecialchars($c['name']) ?></span>
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

<!-- CTA BAND -->
<section class="sec">
  <div class="container">
    <div class="cta" data-reveal>
      <div style="position:relative;z-index:1">
        <div class="cta-h">Ready to Print Something Amazing?</div>
        <div class="cta-s">Place your order in minutes. We'll take care of the rest.</div>
      </div>
      <div class="cta-btns">
        <a href="/products" class="btn" style="background:#fff;color:var(--blue);font-weight:700">Order Now →</a>
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
  <a href="/products" class="mq-btn mq-btn-primary">Start Order</a>
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
