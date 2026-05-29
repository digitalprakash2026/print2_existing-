<?php
/**
 * product.php — Product Detail Page
 * FIX: class names aligned with CSS (pd-gallery, pd-thumbs)
 * FIX: pricing based only on quantity + design choice
 * FIX: design fee from admin settings
 * IMPROVEMENT: larger title, better spacing, related products with CTA
 */
$pageTitle = htmlspecialchars($product['name']) . ' — RCS Graphic';
$settingsMap = [];
try {
    $settings    = Database::rows("SELECT `key`, value FROM settings");
    $settingsMap = array_column($settings, 'value', 'key');
} catch (\Throwable) {}

// Design fee from admin settings (Admin → Settings → design_fee)
$designFee = (float)($product['design_fee'] ?? ($settingsMap['design_fee'] ?? 0));

include INCLUDE_PATH . '/partials/head.php';
include INCLUDE_PATH . '/partials/header.php';
// Note: cart-drawer is already included by header.php — do NOT include again

// Gallery
$imgs       = $product['images'] ?? [];
$primaryImg = '';
foreach ($imgs as $img) {
    $imgUrl = $img['image_path'] ?? ($img['url'] ?? '');
    if (!empty($img['is_primary']) && $imgUrl) { $primaryImg = $imgUrl; break; }
}
if (!$primaryImg && $imgs) $primaryImg = ($imgs[0]['image_path'] ?? ($imgs[0]['url'] ?? '')); 
if (!$primaryImg) $primaryImg = 'https://placehold.co/600x600/EEF3FD/1A56E8?text=' . urlencode($product['name']);

$specs      = $product['specs']      ?? [];
$qualities  = $product['qualities']  ?? [];
$attrGroups = []; // Attribute pricing retired from customer flow
$bizWa      = $settingsMap['biz_whatsapp'] ?? '919876543210';
$startingPrice = (float)($product['min_price'] ?? 0);
if ($startingPrice <= 0 && $qualities) {
    foreach ($qualities as $q) {
        $qMin = (float)($q['min_price'] ?? 0);
        if ($qMin > 0 && ($startingPrice <= 0 || $qMin < $startingPrice)) {
            $startingPrice = $qMin;
        }
    }
}
$comparePrice = $startingPrice > 0 ? ceil($startingPrice * 1.5) : 0;
$discountPct  = ($startingPrice > 0 && $comparePrice > $startingPrice)
    ? max(1, (int)round((($comparePrice - $startingPrice) / $comparePrice) * 100))
    : 0;
$productCode = trim((string)($product['product_code'] ?? ''));
$categoryName = trim((string)($product['category_name'] ?? 'Products'));
?>

<div class="pd-page-wrap">
<div class="container">

  <!-- Breadcrumb -->
  <div class="breadcrumb">
    <a href="/">Home</a><span>/</span>
    <a href="/products">Products</a><span>/</span>
    <span><?= htmlspecialchars($categoryName) ?></span><span>/</span>
    <span style="color:var(--ink);font-weight:600"><?= htmlspecialchars($product['name']) ?></span>
  </div>

  <!-- ══════════════════════════════════════════════════
       MAIN 2-COLUMN GRID
       Left: Gallery | Right: Info + Configurator
  ═══════════════════════════════════════════════════ -->
  <div class="pd-grid">

    <!-- ════ LEFT — GALLERY ════ -->
    <div class="pd-gallery" data-reveal>

      <div class="pd-main" id="pdMainWrap">
        <span class="pd-badge">🔥 Bestseller</span>
        <button class="pd-zoom-btn" type="button" onclick="window.open(document.getElementById('pdMainImg').src, '_blank')" aria-label="Open product image">
          <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        </button>
        <img id="pdMainImg"
             src="<?= htmlspecialchars($primaryImg) ?>"
             alt="<?= htmlspecialchars($product['name']) ?>"
             onerror="this.src='https://placehold.co/600x600/EEF3FD/1A56E8?text=<?= urlencode($product['name']) ?>'">
      </div>

      <?php if (count($imgs) > 1): ?>
      <div class="pd-thumbs-shell" aria-label="Product image gallery">
        <button type="button" class="pd-gallery-nav pd-gallery-prev" onclick="slideProductGallery(-1)" aria-label="Previous product image">‹</button>
        <div class="pd-thumbs" id="pdThumbs">
          <?php foreach ($imgs as $i => $img): ?>
          <div class="pd-th <?= $i === 0 ? 'act' : '' ?>"
               onclick="switchImg('<?= htmlspecialchars($img['image_path'] ?? ($img['url'] ?? '')) ?>',this)"
               title="<?= htmlspecialchars($img['alt_text'] ?: $product['name']) ?>">
            <img src="<?= htmlspecialchars($img['image_path'] ?? ($img['url'] ?? '')) ?>" loading="lazy"
                 alt="<?= htmlspecialchars($img['alt_text'] ?: $product['name']) ?>"
                 onerror="this.style.opacity=.3">
          </div>
          <?php endforeach; ?>
        </div>
        <button type="button" class="pd-gallery-nav pd-gallery-next" onclick="slideProductGallery(1)" aria-label="Next product image">›</button>
      </div>
      <?php endif; ?>

      <div class="pd-gallery-actions" aria-label="Product previews">
        <button type="button"><i class="fa-solid fa-rotate" aria-hidden="true"></i> 360° View</button>
        <button type="button"><i class="fa-regular fa-circle-play" aria-hidden="true"></i> Video Preview</button>
      </div>

    </div><!-- /pd-gallery -->

    <!-- ════ RIGHT — INFO + CONFIGURATOR ════ -->
    <div class="pd-info-col" data-reveal data-reveal-delay="80">

      <h1 class="pd-name"><?= htmlspecialchars($product['name']) ?></h1>
      <div class="pd-rating-row" aria-label="Product rating">
        <span class="pd-stars" aria-hidden="true">★★★★★</span>
        <strong>4.8</strong>
        <span>(124 Reviews)</span>
        <span class="pd-viewing-dot">•</span>
        <span>23 people are viewing this product</span>
      </div>


      <div class="pd-price-strip">
        <span class="pd-price-now" id="heroPrice"><?= $startingPrice > 0 ? '₹' . number_format($startingPrice) : '₹ —' ?></span>
        <span class="pd-price-label">Starting Price</span>
        <?php if ($comparePrice > 0): ?>
        <span class="pd-price-old">₹<?= number_format($comparePrice) ?></span>
        <?php endif; ?>
        <?php if ($discountPct > 0): ?>
        <span class="pd-discount">Save <?= $discountPct ?>%</span>
        <?php endif; ?>
      </div>

      <!-- ── SPECIFICATIONS ── -->
      <?php
      // Only show specs that have a value filled in
      $filledSpecs = array_filter($specs, fn($s) => !empty(trim($s['value'] ?? '')));
      ?>
      <?php if ($filledSpecs || $productCode): ?>
      <div class="pd-spec-table" aria-label="Product details">
        <?php if ($productCode): ?>
        <div class="pd-spec-row">
          <div class="pd-spec-label">Product Code</div>
          <div class="pd-spec-value"><?= htmlspecialchars($productCode) ?></div>
        </div>
        <?php endif; ?>
        <?php foreach ($filledSpecs as $spec): ?>
        <div class="pd-spec-row">
          <div class="pd-spec-label"><?= htmlspecialchars($spec['label']) ?></div>
          <div class="pd-spec-value"><?= htmlspecialchars($spec['value']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- ── QUALITY SELECTOR (shown only when multiple qualities exist) ── -->
      <?php if (count($qualities) > 1): ?>
      <div class="cfg pd-quality-panel" style="margin-bottom:10px">
        <div class="cfg-title">Paper / Quality</div>
        <?php foreach ($qualities as $qi => $q): ?>
        <div class="qual-opt <?= $qi === 0 ? 'sel' : '' ?>"
             onclick="selQual(<?= $qi ?>, <?= (int)$q['id'] ?>, this)"
             id="qual-<?= (int)$q['id'] ?>">
          <div class="qual-radio"><div class="qr-dot"></div></div>
          <div style="flex:1;min-width:0">
            <div style="font-size:13px;font-weight:700;color:var(--ink);margin-bottom:2px">
              <?= htmlspecialchars($q['name']) ?>
            </div>
            <?php if (!empty($q['description'])): ?>
            <div style="font-size:11px;color:var(--text2)"><?= htmlspecialchars($q['description']) ?></div>
            <?php endif; ?>
          </div>
          <div style="text-align:right;flex-shrink:0">
            <div style="font-size:10px;color:var(--text3);margin-bottom:1px">from</div>
            <div style="font-family:var(--fd);font-size:14px;font-weight:700;color:var(--blue)"
                 id="qprice-<?= (int)$q['id'] ?>">
              <?= $q['min_price'] > 0 ? '₹'.number_format((float)$q['min_price']) : '—' ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- ── QUANTITY ── -->
      <div class="pd-qty-row">
        <label class="pd-qty-label" for="pdQty">QUANTITY</label>
        <div class="pd-qty-control">
          <select class="fi fi-sel" id="pdQty" onchange="onQtyChange()">
            <option value="">Select Quantity</option>
            <!-- Populated by JS from API based on selected quality -->
          </select>
          <span>Price varies by quantity - more pieces = better rate per unit</span>
        </div>
      </div>


      <!-- Attribute groups hidden in customer flow -->

      <!-- ── DESIGN OPTION ── -->
      <div class="pd-design-section">
        <div class="pd-design-heading">Upload Your Design</div>
        <div class="pd-design-grid">

          <div class="design-opt sel" id="dopt-upload" onclick="selDesignOpt('upload')">
            <div id="panel-upload">
              <div class="upload-zone" id="uploadZone"
                   onclick="event.stopPropagation();document.getElementById('artworkFile').click()"
                   ondragover="event.preventDefault();this.classList.add('drag')"
                   ondragleave="this.classList.remove('drag')"
                   ondrop="handleFileDrop(event)">
                <input type="file" id="artworkFile"
                       accept=".pdf,.ai,.eps,.png,.jpg,.jpeg,.psd,.cdr,.svg,.tif,.tiff,.zip"
                       onchange="handleFileSelect(event)">
                <div class="design-opt-icon"><i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i></div>
                <div class="design-opt-title">Upload File</div>
                <div class="design-opt-copy">PDF, AI, PSD, PNG, JPG (Max 50MB)</div>
              </div>
              <div id="uploadPreview"></div>
            </div>
          </div>

          <div class="pd-design-or">OR</div>

          <div class="design-opt" id="dopt-rcs" onclick="selDesignOpt('rcs')">
            <div class="design-opt-icon"><i class="fa-regular fa-pen-to-square" aria-hidden="true"></i></div>
            <div class="design-opt-title">Get Free Design</div>
            <div class="design-opt-copy">Let our experts design for you</div>
            <?php if ($designFee > 0): ?>
            <div class="design-opt-note is-paid">+₹<?= number_format($designFee) ?> design fee</div>
            <?php else: ?>
            <div class="design-opt-note is-free">No upfront design charge</div>
            <?php endif; ?>
          </div>

        </div>
        <div id="panel-rcs" style="display:none"></div>
      </div>

      <!-- Notes area intentionally empty — kept for spacing -->

      <!-- ── PRICE PANEL ── -->
      <div class="price-panel" id="pricePanel">
        <div class="pp-row">
          <span class="pp-l">Base Price (Qty × Quality)</span>
          <span class="pp-v" id="ppBase">—</span>
        </div>
        <div class="pp-row" id="ppDesignRow" style="display:none">
          <span class="pp-l">Design Fee (RCS Graphic)</span>
          <span class="pp-v" id="ppDesignFee">₹0</span>
        </div>
        <div class="pp-row">
          <span class="pp-tl">Total Price</span>
          <span class="pp-tv" id="ppTotal">₹ —</span>
        </div>
      </div>

      <!-- ── ACTION BUTTONS ── -->
      <div class="pd-action-stack">
        <div class="pd-action-row">
          <button class="btn btn-blue btn-full" onclick="addToCart()" id="addCartBtn">
            <i class="fa-solid fa-cart-plus" aria-hidden="true"></i> ADD TO CART
          </button>
          <button class="btn btn-green btn-full" onclick="buyNow()">
            GET FREE DESIGN
          </button>
          <button class="btn btn-outline" onclick="waOrder()" title="Order via WHATSAPP SUPPORT">
            <svg viewBox="0 0 24 24" style="width:17px;height:17px;fill:currentColor" aria-hidden="true">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
            WHATSAPP SUPPORT
          </button>
        </div>
        <div class="pd-checkout-note pd-delivery-row">
          <span><i class="fa-solid fa-truck-fast" aria-hidden="true"></i> Delivery in 3 - 5 Working Days</span>
          <small><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Free Delivery on Orders Above ₹999</small>
          <em id="orderHint" class="pd-order-hint" aria-live="polite"></em>
        </div>
      </div>

    </div><!-- /pd-info-col -->
  </div><!-- /pd-grid -->

  <!-- PRODUCT DETAILS / REVIEWS SECTION -->
  <section class="pd-tabs-section" data-reveal data-reveal-delay="120" aria-label="Product information and customer reviews">
    <div class="pd-tabs-card">
      <div class="pd-tabs-nav" role="tablist" aria-label="Product detail tabs">
        <button type="button" class="pd-tab-btn is-active" id="pd-tab-description" role="tab" aria-selected="true" aria-controls="pd-panel-description" onclick="switchProductTab('description', this)">Description</button>
        <button type="button" class="pd-tab-btn" id="pd-tab-specifications" role="tab" aria-selected="false" aria-controls="pd-panel-specifications" onclick="switchProductTab('specifications', this)">Specifications</button>
        <button type="button" class="pd-tab-btn" id="pd-tab-reviews" role="tab" aria-selected="false" aria-controls="pd-panel-reviews" onclick="switchProductTab('reviews', this)">Reviews (124)</button>
        <button type="button" class="pd-tab-btn" id="pd-tab-faqs" role="tab" aria-selected="false" aria-controls="pd-panel-faqs" onclick="switchProductTab('faqs', this)">FAQs</button>
      </div>

      <div class="pd-tabs-content">
        <div class="pd-tabs-left">
          <div class="pd-tab-panel is-active" id="pd-panel-description" role="tabpanel" aria-labelledby="pd-tab-description" data-tab-panel="description">
            <h2>High Quality. Perfect Impression.</h2>
            <p>
              <?= !empty(trim((string)($product['description'] ?? '')))
                ? nl2br(htmlspecialchars((string)$product['description']))
                : 'Our ' . htmlspecialchars($product['name']) . ' are designed to leave a lasting impact. Printed on high-quality paper with professional finishing options, they reflect your brand identity with clarity and style.' ?>
            </p>
            <ul class="pd-check-list">
              <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i> Perfect for business branding and networking</li>
              <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i> High resolution printing with vibrant colors</li>
              <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i> Multiple paper types and finishing options</li>
              <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i> Fast turnaround and free delivery above ₹999</li>
            </ul>
          </div>

          <div class="pd-tab-panel" id="pd-panel-specifications" role="tabpanel" aria-labelledby="pd-tab-specifications" data-tab-panel="specifications" hidden>
            <h2>Specifications</h2>
            <?php if ($filledSpecs || $productCode): ?>
            <div class="pd-tab-spec-grid">
              <?php if ($productCode): ?>
              <div><span>Product Code</span><strong><?= htmlspecialchars($productCode) ?></strong></div>
              <?php endif; ?>
              <?php foreach ($filledSpecs as $spec): ?>
              <div><span><?= htmlspecialchars($spec['label']) ?></span><strong><?= htmlspecialchars($spec['value']) ?></strong></div>
              <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p>Specifications for this product will be confirmed by our print expert after your enquiry.</p>
            <?php endif; ?>
          </div>

          <div class="pd-tab-panel" id="pd-panel-reviews" role="tabpanel" aria-labelledby="pd-tab-reviews" data-tab-panel="reviews" hidden>
            <h2>Customer Reviews</h2>
            <p>Customers trust RCS Graphic for sharp printing, dependable finishing, and quick support from design to delivery.</p>
            <ul class="pd-check-list">
              <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i> 4.8 average customer rating</li>
              <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i> 124 verified customer reviews</li>
              <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i> Loved for print quality and fast communication</li>
            </ul>
          </div>

          <div class="pd-tab-panel" id="pd-panel-faqs" role="tabpanel" aria-labelledby="pd-tab-faqs" data-tab-panel="faqs" hidden>
            <h2>FAQs</h2>
            <div class="pd-faq-list">
              <details open>
                <summary>Can I upload my own design?</summary>
                <p>Yes, you can upload PDF, AI, PSD, PNG, JPG and other supported artwork files up to 50MB.</p>
              </details>
              <details>
                <summary>Can RCS Graphic create the design for me?</summary>
                <p>Yes, select the free design option and our team will connect with you for the design brief and confirmation.</p>
              </details>
              <details>
                <summary>How long does delivery take?</summary>
                <p>Standard delivery usually takes 3 - 5 working days after artwork and order confirmation.</p>
              </details>
            </div>
          </div>
        </div>

        <aside class="pd-reviews-panel" aria-label="What our customers say">
          <div class="pd-reviews-head">
            <h2>What Our Customers Say</h2>
            <a href="#pd-panel-reviews" onclick="switchProductTab('reviews', document.getElementById('pd-tab-reviews'))">View All Reviews <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
          </div>
          <div class="pd-review-cards">
            <article class="pd-review-card">
              <div class="pd-review-person">
                <span class="pd-review-avatar" aria-hidden="true">RM</span>
                <div><strong>Rakesh Mehta</strong><span>Business Owner</span></div>
              </div>
              <div class="pd-review-stars" aria-label="5 out of 5 stars">★★★★★</div>
              <p>Excellent quality and fast delivery. Highly recommended!</p>
            </article>
            <article class="pd-review-card">
              <div class="pd-review-person">
                <span class="pd-review-avatar" aria-hidden="true">KS</span>
                <div><strong>Khushbu Shah</strong><span>Marketing Head</span></div>
              </div>
              <div class="pd-review-stars" aria-label="5 out of 5 stars">★★★★★</div>
              <p>Very professional team and amazing print quality.</p>
            </article>
            <article class="pd-review-card">
              <div class="pd-review-person">
                <span class="pd-review-avatar" aria-hidden="true">JP</span>
                <div><strong>Jigar Patel</strong><span>Event Organizer</span></div>
              </div>
              <div class="pd-review-stars" aria-label="5 out of 5 stars">★★★★★</div>
              <p>Best experience for bulk printing. Great pricing and support.</p>
            </article>
          </div>
          <button type="button" class="pd-review-next" aria-label="Next review" onclick="document.querySelector('.pd-review-cards')?.scrollBy({left:220, behavior:'smooth'})">›</button>
        </aside>
      </div>
    </div>
  </section>

  <!-- RELATED PRODUCTS -->
  <?php if ($related): ?>
  <section class="ym-section">
    <div class="ym-head">
      <h2 class="ym-title">You May <span>Also Like</span></h2>
      <a href="/products" class="ym-view-all">View All Products</a>
    </div>

    <div class="ym-grid">
      <?php foreach (array_slice($related, 0, 5) as $rp):
        $rimg = $rp['primary_image'] ?? '';
        $rmin = (float)($rp['min_price'] ?? 0);
      ?>
      <article class="ym-card" data-reveal data-reveal-delay="<?= ((int)($rp['id'] ?? 0) % 3) * 60 ?>">
        <a class="ym-img" href="/product/<?= htmlspecialchars($rp['slug']) ?>">
          <img src="<?= htmlspecialchars($rimg) ?>" alt="<?= htmlspecialchars($rp['name']) ?>" loading="lazy"
               onerror="this.src='https://placehold.co/400x260/EEF3FD/1A56E8?text=<?= urlencode($rp['name']) ?>'">
        </a>
        <div class="ym-body">
          <div class="ym-cat"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><?= htmlspecialchars($rp['category_name'] ?? '') ?></div>
          <div class="ym-from">Starting from</div>
          <div class="ym-foot">
            <div class="ym-price">₹<?= $rmin > 0 ? number_format($rmin) : '—' ?></div>
            <a href="/product/<?= htmlspecialchars($rp['slug']) ?>" class="ym-order">ORDER NOW</a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

</div><!-- /container -->
</div><!-- /page-wrap -->

<!-- Sticky Price Bar -->
<div class="sticky-price show" id="stickyBar">
  <div>
    <div class="sp-sub">Total Price</div>
    <div class="sp-price" id="spTotal">₹ —</div>
  </div>
  <div class="sticky-price-actions" style="display:flex;gap:8px">
    <button class="btn btn-blue" onclick="addToCart()" style="padding:11px 18px">Add to Cart</button>
    <button class="btn btn-green" onclick="buyNow()" style="padding:11px 16px">Buy Now</button>
  </div>
</div>

<script>
// ── Data from PHP ──────────────────────────────────────────────
const PRODUCT_ID  = <?= (int)$product['id'] ?>;
const BIZ_WA      = '<?= htmlspecialchars($bizWa) ?>';
const CSRF        = '<?= htmlspecialchars($csrf ?? '') ?>';
const QUALITIES   = <?= json_encode($qualities) ?>;
const DESIGN_FEE  = <?= (float)$designFee ?>;

// ── State ──────────────────────────────────────────────────────
let selectedQualityId  = <?= $qualities ? (int)$qualities[0]['id'] : 1 ?>;
let selectedQualityIdx = 0;
// Ensure first quality card is visually selected on load
document.addEventListener('DOMContentLoaded', () => {
  const firstQual = document.querySelector('.qual-opt');
  if (firstQual && !firstQual.classList.contains('sel')) firstQual.classList.add('sel');
});
let selectedQty        = null;
let artworkId          = null;
let uploadedFileName   = null;
let designChoice       = 'upload';
let currentBasePrice   = 0;

function refreshOrderReadiness() {
  const hasQty = !!selectedQty;
  const addBtn = document.getElementById('addCartBtn');
  const stickyBtns = document.querySelectorAll('#stickyBar .btn.btn-blue, #stickyBar .btn.btn-green');
  const buyBtn = document.querySelector('.btn.btn-green.btn-full');
  const hint = document.getElementById('orderHint');

  if (addBtn) addBtn.disabled = !hasQty;
  if (buyBtn) buyBtn.disabled = !hasQty;
  stickyBtns.forEach(btn => btn.disabled = !hasQty);

  if (hint) {
    hint.textContent = hasQty
      ? 'Looks good. You can now add to cart or buy now.'
      : 'Select quantity to enable Add to Cart / Buy Now.';
  }
}

// Gallery
function switchImg(url, el) {
  const img = document.getElementById('pdMainImg');
  img.style.opacity = '0.6';
  setTimeout(() => { img.src = url; img.style.opacity = '1'; }, 150);
  document.querySelectorAll('.pd-th').forEach(t => t.classList.remove('act'));
  if (el) {
    el.classList.add('act');
    el.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
  }
}

function slideProductGallery(dir) {
  const thumbs = Array.from(document.querySelectorAll('.pd-th'));
  if (!thumbs.length) return;
  const activeIdx = Math.max(0, thumbs.findIndex(t => t.classList.contains('act')));
  const nextIdx = (activeIdx + dir + thumbs.length) % thumbs.length;
  const next = thumbs[nextIdx];
  const nextImg = next?.querySelector('img');
  if (!next || !nextImg) return;
  switchImg(nextImg.currentSrc || nextImg.src, next);
}

// Quality Selection
function selQual(idx, qualId, clickedEl) {
  selectedQualityIdx = idx;
  selectedQualityId  = qualId;
  document.querySelectorAll('.qual-opt').forEach(el => el.classList.remove('sel'));
  if (clickedEl) clickedEl.classList.add('sel');
  reloadQtySlabs();
  refreshOrderReadiness();
}

// Quantity Slabs
async function reloadQtySlabs() {
  if (!PRODUCT_ID) return;
  try {
    const resp = await fetch(`/api/products/${PRODUCT_ID}/pricing`);
    const data = await resp.json();
    const list = data.qualities || [];
    if (!list.length) return;
    const quality = list.find(q => q.id == selectedQualityId) || list[0];
    selectedQualityId = quality.id;

    const sel  = document.getElementById('pdQty');
    const prev = sel.value;
    sel.innerHTML = '<option value="">— Select Quantity —</option>';

    (quality.slabs || []).forEach(slab => {
      const opt = document.createElement('option');
      opt.value = slab.quantity;
      opt.textContent = Number(slab.quantity).toLocaleString('en-IN')
                      + ' pieces — ₹' + Number(slab.price).toLocaleString('en-IN');
      opt.dataset.price = slab.price;
      if (slab.quantity == prev) opt.selected = true;
      sel.appendChild(opt);
    });

    if (prev) {
      selectedQty = parseInt(prev) || null;
    }

    calcPrice();
  } catch (e) { /* silent */ }
}

function onQtyChange() {
  selectedQty = parseInt(document.getElementById('pdQty').value) || null;
  calcPrice();
  refreshOrderReadiness();
}

// Price Calculation
function calcPrice() {
  if (!selectedQualityId || !selectedQty) {
    document.getElementById('ppBase').textContent  = '—';
    document.getElementById('ppTotal').textContent = '₹ —';
    document.getElementById('spTotal').textContent = '₹ —';
    currentBasePrice = 0;
    refreshOrderReadiness();
    return;
  }

  const sel    = document.getElementById('pdQty');
  const selOpt = sel.options[sel.selectedIndex];
  const base   = selOpt ? parseFloat(selOpt.dataset.price || 0) : 0;
  currentBasePrice = base;

  const fee   = designChoice === 'rcs' ? DESIGN_FEE : 0;
  const total = base + fee;

  const fmt = n => '₹' + Number(n).toLocaleString('en-IN');
  document.getElementById('ppBase').textContent  = fmt(base);
  document.getElementById('ppTotal').textContent = fmt(total);
  document.getElementById('spTotal').textContent = fmt(total);
  const panel = document.getElementById('pricePanel');
  if (panel) {
    panel.classList.remove('flash');
    requestAnimationFrame(() => {
      panel.classList.add('flash');
      setTimeout(() => panel.classList.remove('flash'), 320);
    });
  }

  const feeRow = document.getElementById('ppDesignRow');
  if (feeRow) feeRow.style.display = fee > 0 ? '' : 'none';

  const feeEl = document.getElementById('ppDesignFee');
  if (feeEl) feeEl.textContent = fmt(fee);

  QUALITIES.forEach(q => {
    const badge = document.getElementById('qprice-' + q.id);
    if (!badge) return;
    const slab = (q.slabs || []).find(s => parseInt(s.quantity) === selectedQty);
    badge.textContent = slab ? '₹' + Number(slab.price).toLocaleString('en-IN') : '';
  });
  refreshOrderReadiness();
}

// Design Option
function selDesignOpt(choice) {
  designChoice = choice;
  document.getElementById('dopt-upload').classList.toggle('sel', choice === 'upload');
  document.getElementById('dopt-rcs').classList.toggle('sel', choice === 'rcs');
  document.getElementById('panel-upload').style.display = choice === 'upload' ? 'block' : 'none';
  document.getElementById('panel-rcs').style.display    = choice === 'rcs' ? 'block' : 'none';
  calcPrice();
  refreshOrderReadiness();
}

// File Upload
function handleFileSelect(e) {
  const f = e.target.files[0];
  if (f) processFile(f);
}

function handleFileDrop(e) {
  e.preventDefault();
  document.getElementById('uploadZone').classList.remove('drag');
  const f = e.dataTransfer.files[0];
  if (f) processFile(f);
}

async function processFile(file) {
  if (file.size > 52428800) {
    toast('File too large. Max 50MB', 'error');
    return;
  }

  const fd = new FormData();
  fd.append('artwork', file);
  toast('Uploading…', 'info');

  try {
    const resp = await fetch('/api/upload/artwork', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': CSRF },
      credentials: 'same-origin',
      body: fd
    });

    const data = await resp.json();

    if (data.ok) {
      artworkId        = data.artwork_id;
      uploadedFileName = data.filename;
      document.getElementById('uploadPreview').innerHTML = `
        <div class="upload-done">
          <div style="font-size:24px">📄</div>
          <div style="flex:1;min-width:0">
            <div style="font-size:13px;font-weight:700;color:var(--green)">${file.name}</div>
            <div style="font-size:11px;color:var(--text2);margin-top:2px">${(file.size/1024/1024).toFixed(2)} MB</div>
          </div>
          <button onclick="removeFile()" style="color:var(--red);font-size:18px;background:none;border:none;cursor:pointer">✕</button>
        </div>`;
      toast('Artwork uploaded!', 'success');
    } else {
      toast(data.msg || 'Upload failed', 'error');
    }
  } catch {
    toast('Upload failed. Please try again.', 'error');
  }
}

function removeFile() {
  artworkId = null;
  uploadedFileName = null;
  document.getElementById('uploadPreview').innerHTML = '';
  document.getElementById('artworkFile').value = '';
}

// Add to Cart
async function addToCart(opts = {}) {
  const v = validateOrder();
  if (!v.ok) {
    toast(v.msg, 'error');
    return false;
  }

  const btn = document.getElementById('addCartBtn');
  btn.disabled = true;
  btn.textContent = 'Adding…';

  try {
    const resp = await fetch('/api/cart/add', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': CSRF
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        product_id: PRODUCT_ID,
        quality_id: selectedQualityId,
        quantity: selectedQty,
        attribute_selections: {},
        design_choice: designChoice,
        design_brief: '',
        notes: '',
        artwork_id: artworkId
      })
    });

    const data = await resp.json();

    if (data.ok) {
      toast('Added to cart! 🛒', 'success');
      updateCartCount();
      if (!opts.silent) openCart();
      return true;
    }
    toast(data.msg || 'Could not add to cart', 'error');
    return false;
  } catch {
    toast('Error. Please try again.', 'error');
    return false;
  } finally {
    btn.disabled = false;
    btn.textContent = 'ADD TO CART';
  }
}

async function buyNow() {
  const v = validateOrder();
  if (!v.ok) {
    toast(v.msg, 'error');
    return false;
  }

  const ok = await addToCart({ silent: true });
  if (!ok) return;
  closeCart();
  location.href = '/checkout';
}

function validateOrder() {
  if (!selectedQty) {
    return { ok: false, msg: 'Please select a quantity' };
  }

  return { ok: true };
}

// WHATSAPP SUPPORT Quick Order
function waOrder() {
  const totalEl = document.getElementById('ppTotal').textContent || '₹ —';
  const qname   = QUALITIES[selectedQualityIdx]?.name || 'Standard';
  const qtyText = selectedQty ? Number(selectedQty).toLocaleString('en-IN') + ' pcs' : 'Not selected';
  const dOpt    = designChoice === 'rcs' ? 'Design by RCS Graphic' : 'Customer Upload';
  const now     = new Date().toLocaleString('en-IN');
  const pageUrl = window.location.href;

  const msg = [
    '🧾 *Product Enquiry*',
    `🕒 ${now}`,
    '',
    '*Product Details*',
    `• Product: <?= addslashes(htmlspecialchars($product['name'])) ?>`,
    `• Quantity: ${qtyText}`,
    `• Quality: ${qname}`,
    `• Design: ${dOpt}`,
    `• Estimated Total: ${totalEl}`,
    '',
    `Source: Product Page (${pageUrl})`,
    '',
    'Please confirm final costing and next steps.'
  ].join('\n');

  window.open(`https://wa.me/${BIZ_WA}?text=${encodeURIComponent(msg)}`, '_blank');
}

function switchProductTab(tab, btn) {
  document.querySelectorAll('.pd-tab-btn').forEach(el => {
    const active = el === btn;
    el.classList.toggle('is-active', active);
    el.setAttribute('aria-selected', active ? 'true' : 'false');
  });
  document.querySelectorAll('[data-tab-panel]').forEach(panel => {
    const active = panel.dataset.tabPanel === tab;
    panel.classList.toggle('is-active', active);
    panel.hidden = !active;
  });
}

// Init
reloadQtySlabs();
refreshOrderReadiness();
</script>

<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
