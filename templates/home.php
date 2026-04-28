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
     HOW TO CHANGE SLIDES:
       • Change background images: edit the <img src="..."> in each .bs-slide
       • Change slide text: edit .bs-eyebrow, .bs-title, .bs-sub
       • Change CTA buttons: edit href on .bs-cta-primary / .bs-cta-wa
       • Add/remove slides: copy a .bs-slide div and add a matching .bs-dot
       • Auto-advance interval: edit `10000` in app.js → initBannerSlider()
═══════════════════════════════════════════════════════════════ -->
<div class="banner-slider" id="bannerSlider">
  <?php
  $fallbackBanners = [
    [
      'eyebrow' => 'New Arrivals',
      'title' => 'Premium Business<br>Cards That Impress',
      'subtitle' => '400 GSM thick stock, UV coating, matte finish.<br>Make every handshake count.',
      'image_path' => 'https://images.unsplash.com/photo-1586953208448-b95a79798f07?w=1400&q=85&fit=crop',
      'image_alt' => 'Premium Business Card Printing',
      'cta_primary_text' => 'View Products →',
      'cta_primary_url' => '/category/cards',
      'cta_secondary_text' => '💬 WhatsApp',
      'cta_secondary_type' => 'whatsapp',
      'cta_secondary_url' => '',
    ],
    [
      'eyebrow' => 'Best Seller',
      'title' => 'Brochures That<br>Tell Your Story',
      'subtitle' => 'Tri-fold, bi-fold, A4 or custom sizes.<br>Full colour, gloss or matte finish.',
      'image_path' => 'https://images.unsplash.com/photo-1541746972996-4e0b0f43e02a?w=1400&q=85&fit=crop',
      'image_alt' => 'Brochure Printing',
      'cta_primary_text' => 'View Products →',
      'cta_primary_url' => '/category/brochures',
      'cta_secondary_text' => '💬 WhatsApp',
      'cta_secondary_type' => 'whatsapp',
      'cta_secondary_url' => '',
    ],
    [
      'eyebrow' => 'Large Format',
      'title' => 'Banners &amp; Posters<br>That Grab Attention',
      'subtitle' => 'Weather-resistant flex banners, standees,<br>hoarding prints — any size.',
      'image_path' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1400&q=85&fit=crop',
      'image_alt' => 'Banner Printing',
      'cta_primary_text' => 'View Products →',
      'cta_primary_url' => '/category/banners',
      'cta_secondary_text' => '💬 WhatsApp',
      'cta_secondary_type' => 'whatsapp',
      'cta_secondary_url' => '',
    ],
    [
      'eyebrow' => 'Fast Turnaround',
      'title' => 'Flyers &amp; Pamphlets<br>Delivered in 24 hrs',
      'subtitle' => 'High-volume offset printing, vibrant colours,<br>bulk discounts available.',
      'image_path' => 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=1400&q=85&fit=crop',
      'image_alt' => 'Flyer Printing',
      'cta_primary_text' => 'View Products →',
      'cta_primary_url' => '/category/flyers',
      'cta_secondary_text' => '💬 WhatsApp',
      'cta_secondary_type' => 'whatsapp',
      'cta_secondary_url' => '',
    ],
  ];
  $bannerSlides = !empty($homeBanners ?? []) ? $homeBanners : $fallbackBanners;
  foreach ($bannerSlides as $i => $slide):
    $img = htmlspecialchars((string)($slide['image_path'] ?? ''));
    $alt = htmlspecialchars((string)($slide['image_alt'] ?? ('Slide ' . ($i + 1))));
    $eyebrow = (string)($slide['eyebrow'] ?? '');
    $title = strip_tags((string)($slide['title'] ?? ''), '<br><br/>');
    $subtitle = strip_tags((string)($slide['subtitle'] ?? ''), '<br><br/>');
    $ctaPrimaryText = htmlspecialchars((string)($slide['cta_primary_text'] ?? 'View Products →'));
    $ctaPrimaryUrl = htmlspecialchars((string)($slide['cta_primary_url'] ?? '#'));
    $ctaSecondaryText = htmlspecialchars((string)($slide['cta_secondary_text'] ?? '💬 WhatsApp'));
    $ctaSecondaryType = strtolower(trim((string)($slide['cta_secondary_type'] ?? 'whatsapp')));
    $ctaSecondaryUrl = trim((string)($slide['cta_secondary_url'] ?? ''));
  ?>
  <div class="bs-slide">
    <img src="<?= $img ?>" alt="<?= $alt ?>">
    <div class="bs-overlay"></div>
    <div class="bs-content">
      <div class="bs-eyebrow"><?= htmlspecialchars($eyebrow) ?></div>
      <div class="bs-title"><?= $title ?></div>
      <div class="bs-sub"><?= $subtitle ?></div>
      <div class="bs-actions">
        <a href="<?= $ctaPrimaryUrl ?>" class="bs-cta-primary"><?= $ctaPrimaryText ?></a>
        <?php if ($ctaSecondaryType === 'url' && $ctaSecondaryUrl !== ''): ?>
          <a href="<?= htmlspecialchars($ctaSecondaryUrl) ?>" class="bs-cta-wa"><?= $ctaSecondaryText ?></a>
        <?php else: ?>
          <button class="bs-cta-wa" onclick="window.open('https://wa.me/<?= $bizWa ?>','_blank')"><?= $ctaSecondaryText ?></button>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>

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
</div>


<!-- ═══════════════════════════════════════════════════════════
     TRUST BAR
══════════════════════════════════════════════════════════════ -->
<div class="trust" data-reveal>
  <div class="container">
    <div class="trust-inner">
      <div class="trust-i" data-reveal data-reveal-delay="40"><div class="trust-ic" style="background:#FEF9C3">🎨</div>Free Design Support</div>
      <div class="trust-i" data-reveal data-reveal-delay="80"><div class="trust-ic" style="background:#ECFDF5">🖨️</div>Best Print Quality</div>
      <div class="trust-i" data-reveal data-reveal-delay="120"><div class="trust-ic" style="background:#EEF3FD">💸</div>Affordable Pricing</div>
      <div class="trust-i" data-reveal data-reveal-delay="160"><div class="trust-ic" style="background:#FFF4ED">⏱️</div>On-Time Delivery</div>
      <div class="trust-i" data-reveal data-reveal-delay="200"><div class="trust-ic" style="background:#F0FDF4">💯</div>100% Satisfaction</div>
    </div>
  </div>
</div>


<!-- ═══════════════════════════════════════════════════════════
     PRODUCTS SECTION — Category-based boxes with 4 products each
══════════════════════════════════════════════════════════════ -->
<section class="sec home-prod-sec" id="prod-sec" style="background:var(--bg)">
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
<section class="sec" id="why-sec" style="background:var(--white)" data-reveal>
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
<section class="sec" id="contact-sec" style="background:var(--white)" data-reveal>
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
