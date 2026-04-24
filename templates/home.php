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

  <!-- ── SLIDE 1 ─────────────────────────────────────────── -->
  <div class="bs-slide">
    <!-- CHANGE IMAGE: replace src below -->
    <img src="https://images.unsplash.com/photo-1586953208448-b95a79798f07?w=1400&q=85&fit=crop"
         alt="Premium Business Card Printing">
    <div class="bs-overlay"></div>
    <div class="bs-content">
      <div class="bs-eyebrow">New Arrivals</div>
      <div class="bs-title">Premium Business<br>Cards That Impress</div>
      <div class="bs-sub">400 GSM thick stock, UV coating, matte finish.<br>Make every handshake count.</div>
      <div class="bs-actions">
        <!-- CHANGE LINK: update href to your product URL -->
        <a href="/category/cards" class="bs-cta-primary">View Products →</a>
        <button class="bs-cta-wa" onclick="window.open('https://wa.me/<?= $bizWa ?>','_blank')">💬 WhatsApp</button>
      </div>
    </div>
  </div>

  <!-- ── SLIDE 2 ─────────────────────────────────────────── -->
  <div class="bs-slide">
    <img src="https://images.unsplash.com/photo-1541746972996-4e0b0f43e02a?w=1400&q=85&fit=crop"
         alt="Brochure Printing">
    <div class="bs-overlay"></div>
    <div class="bs-content">
      <div class="bs-eyebrow">Best Seller</div>
      <div class="bs-title">Brochures That<br>Tell Your Story</div>
      <div class="bs-sub">Tri-fold, bi-fold, A4 or custom sizes.<br>Full colour, gloss or matte finish.</div>
      <div class="bs-actions">
        <a href="/category/brochures" class="bs-cta-primary">View Products →</a>
        <button class="bs-cta-wa" onclick="window.open('https://wa.me/<?= $bizWa ?>','_blank')">💬 WhatsApp</button>
      </div>
    </div>
  </div>

  <!-- ── SLIDE 3 ─────────────────────────────────────────── -->
  <div class="bs-slide">
    <img src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1400&q=85&fit=crop"
         alt="Banner Printing">
    <div class="bs-overlay"></div>
    <div class="bs-content">
      <div class="bs-eyebrow">Large Format</div>
      <div class="bs-title">Banners &amp; Posters<br>That Grab Attention</div>
      <div class="bs-sub">Weather-resistant flex banners, standees,<br>hoarding prints — any size.</div>
      <div class="bs-actions">
        <a href="/category/banners" class="bs-cta-primary">View Products →</a>
        <button class="bs-cta-wa" onclick="window.open('https://wa.me/<?= $bizWa ?>','_blank')">💬 WhatsApp</button>
      </div>
    </div>
  </div>

  <!-- ── SLIDE 4 ─────────────────────────────────────────── -->
  <div class="bs-slide">
    <img src="https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=1400&q=85&fit=crop"
         alt="Flyer Printing">
    <div class="bs-overlay"></div>
    <div class="bs-content">
      <div class="bs-eyebrow">Fast Turnaround</div>
      <div class="bs-title">Flyers &amp; Pamphlets<br>Delivered in 24 hrs</div>
      <div class="bs-sub">High-volume offset printing, vibrant colours,<br>bulk discounts available.</div>
      <div class="bs-actions">
        <a href="/category/flyers" class="bs-cta-primary">View Products →</a>
        <button class="bs-cta-wa" onclick="window.open('https://wa.me/<?= $bizWa ?>','_blank')">💬 WhatsApp</button>
      </div>
    </div>
  </div>

  <!-- Prev / Next arrows -->
  <button class="bs-prev" onclick="document.getElementById('bannerSlider')._sliderPrev()" aria-label="Previous slide">
    <svg viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
  </button>
  <button class="bs-next" onclick="document.getElementById('bannerSlider')._sliderNext()" aria-label="Next slide">
    <svg viewBox="0 0 24 24"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
  </button>

  <!-- Dot indicators -->
  <div class="bs-dots">
    <button class="bs-dot" onclick="document.getElementById('bannerSlider')._sliderGoTo(0)" aria-label="Slide 1"></button>
    <button class="bs-dot" onclick="document.getElementById('bannerSlider')._sliderGoTo(1)" aria-label="Slide 2"></button>
    <button class="bs-dot" onclick="document.getElementById('bannerSlider')._sliderGoTo(2)" aria-label="Slide 3"></button>
    <button class="bs-dot" onclick="document.getElementById('bannerSlider')._sliderGoTo(3)" aria-label="Slide 4"></button>
  </div>
</div>


<!-- ═══════════════════════════════════════════════════════════
     TRUST BAR
══════════════════════════════════════════════════════════════ -->
<div class="trust">
  <div class="container">
    <div class="trust-inner">
      <div class="trust-i"><div class="trust-ic" style="background:#ECFDF5">✅</div>GST Invoice Included</div>
      <div class="trust-i"><div class="trust-ic" style="background:#EEF3FD">🔒</div>Secure Razorpay Payment</div>
      <div class="trust-i"><div class="trust-ic" style="background:#FFF4ED">⚡</div>24–48hr Fast Delivery</div>
      <div class="trust-i"><div class="trust-ic" style="background:#FEF9C3">🎨</div>Free Design Support</div>
      <div class="trust-i"><div class="trust-ic" style="background:#F0FDF4">💯</div>Quality Guaranteed</div>
    </div>
  </div>
</div>


<!-- ═══════════════════════════════════════════════════════════
     PRODUCTS SECTION — Category-based boxes with 4 products each
══════════════════════════════════════════════════════════════ -->
<section class="sec" id="prod-sec" style="background:var(--bg)">
  <div class="container">

    <div class="sec-hdr" style="margin-bottom:32px">
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
      <div class="cat-box cat-box-square">

        <!-- Category header -->
        <div class="cat-box-hdr">
          <div class="cat-box-title">
            <div class="cat-box-icon"><?= $catIcon ?></div>
            <?= $catName ?>
          </div>
          <a href="/category/<?= $catSlug ?>" class="cat-box-see">
            View More →
          </a>
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
<section class="sec" id="why-sec" style="background:var(--white)">
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

      <div class="why-c">
        <div class="why-ic" style="background:#EEF3FD">🖨️</div>
        <div class="why-t">Premium Print Quality</div>
        <div class="why-d">State-of-the-art printing technology ensuring sharp, vibrant and consistent results every time.</div>
      </div>

      <div class="why-c">
        <div class="why-ic" style="background:#ECFDF5">⚡</div>
        <div class="why-t">Fast Turnaround</div>
        <div class="why-d">Same-day and next-day options available. We know deadlines matter for your business.</div>
      </div>

      <div class="why-c">
        <div class="why-ic" style="background:#FFF4ED">💰</div>
        <div class="why-t">Best Bulk Prices</div>
        <div class="why-d">Competitive pricing with bulk discounts. The more you print, the more you save.</div>
      </div>

      <div class="why-c">
        <div class="why-ic" style="background:#FEF9C3">🎨</div>
        <div class="why-t">Free Design Help</div>
        <div class="why-d">Our creative team helps you get the perfect design ready for print at no extra cost.</div>
      </div>

      <div class="why-c">
        <div class="why-ic" style="background:#F0F9FF">📦</div>
        <div class="why-t">Safe Packaging</div>
        <div class="why-d">Every order is carefully packed to ensure your prints arrive in perfect condition.</div>
      </div>

      <div class="why-c">
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
    <div class="cta">
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
<section class="sec" id="contact-sec" style="background:var(--white)">
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
      <div>
        <div class="f-brand"><?= $bizName ?></div>
        <div class="f-desc">Premium printing services for businesses. Quality that speaks for itself.</div>
        <div style="margin-top:13px">
          <button onclick="window.open('https://wa.me/<?= $bizWa ?>','_blank')"
                  style="padding:7px 13px;border-radius:8px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.65);font-size:12px;cursor:pointer;font-family:var(--fn)">💬 WhatsApp</button>
        </div>
      </div>
      <div>
        <div class="f-lbl">Products</div>
        <?php foreach (array_slice($products, 0, 7) as $p): ?>
        <a class="f-link" href="/product/<?= htmlspecialchars($p['slug']) ?>"><?= htmlspecialchars($p['name']) ?></a>
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
        <div class="f-link"><?= $bizPhone ?></div>
        <div class="f-link"><?= $bizEmail ?></div>
        <div class="f-link" onclick="window.open('https://wa.me/<?= $bizWa ?>','_blank')" style="cursor:pointer">WhatsApp Chat</div>
      </div>
    </div>
    <div class="f-bot">
      <div>© <?= date('Y') ?> <?= $bizName ?>. All rights reserved.</div>
      <div>Made with ❤️ in Rajkot, Gujarat</div>
    </div>
  </div>
</footer>

<!-- No carousel/filter JS needed with new category layout -->

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
