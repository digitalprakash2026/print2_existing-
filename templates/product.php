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
    if (!empty($img['is_primary'])) { $primaryImg = $img['url']; break; }
}
if (!$primaryImg && $imgs) $primaryImg = $imgs[0]['url'];
if (!$primaryImg) $primaryImg = 'https://placehold.co/600x600/EEF3FD/1A56E8?text=' . urlencode($product['name']);

$specs      = $product['specs']      ?? [];
$qualities  = $product['qualities']  ?? [];
$attrGroups = []; // Attribute pricing retired from customer flow
$bizWa      = $settingsMap['biz_whatsapp'] ?? '919876543210';
?>

<div style="margin-top:var(--hh);padding-bottom:120px">
<div class="container">

  <!-- Breadcrumb -->
  <div class="breadcrumb">
    <a href="/">Home</a><span>/</span>
    <a href="/#prod-sec">Products</a><span>/</span>
    <span style="color:var(--ink);font-weight:600"><?= htmlspecialchars($product['name']) ?></span>
  </div>

  <!-- ══════════════════════════════════════════════════
       MAIN 2-COLUMN GRID
       Left: Gallery | Right: Info + Configurator
  ═══════════════════════════════════════════════════ -->
  <div class="pd-grid">

    <!-- ════ LEFT — GALLERY ════ -->
    <div class="pd-gallery">

      <!-- Square main image -->
      <div class="pd-main" id="pdMainWrap">
        <img id="pdMainImg"
             src="<?= htmlspecialchars($primaryImg) ?>"
             alt="<?= htmlspecialchars($product['name']) ?>"
             onerror="this.src='https://placehold.co/600x600/EEF3FD/1A56E8?text=<?= urlencode($product['name']) ?>'">
      </div>

      <?php if (count($imgs) > 1): ?>
      <div class="pd-thumbs" id="pdThumbs">
        <?php foreach ($imgs as $i => $img): ?>
        <div class="pd-th <?= $i === 0 ? 'act' : '' ?>"
             onclick="switchImg('<?= htmlspecialchars($img['url']) ?>',this)"
             title="<?= htmlspecialchars($img['alt_text'] ?: $product['name']) ?>">
          <img src="<?= htmlspecialchars($img['url']) ?>" loading="lazy"
               alt="<?= htmlspecialchars($img['alt_text'] ?: $product['name']) ?>"
               onerror="this.style.opacity=.3">
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

    </div><!-- /pd-gallery -->

    <!-- ════ RIGHT — INFO + CONFIGURATOR ════ -->
    <div class="pd-info-col">

      <!-- Category tag -->
      <div class="pd-cat"><?= htmlspecialchars($product['category_name'] ?? '') ?></div>

      <h1 class="pd-name"><?= htmlspecialchars($product['name']) ?></h1>
      <p class="pd-desc"><?= htmlspecialchars($product['description'] ?? '') ?></p>

      <!-- ── SPECIFICATIONS ── -->
      <?php
      // Only show specs that have a value filled in
      $filledSpecs = array_filter($specs, fn($s) => !empty(trim($s['value'] ?? '')));
      ?>
      <?php if ($filledSpecs): ?>
      <div class="cfg" style="margin-bottom:20px">
        <div class="cfg-title">📋 Specifications</div>
        <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px">
          <?php foreach ($filledSpecs as $spec): ?>
          <li style="display:flex;align-items:flex-start;gap:10px;font-size:13px;
                     padding:7px 0;border-bottom:1px solid var(--border)">
            <span style="color:var(--blue);flex-shrink:0;margin-top:2px;font-size:11px;font-weight:700">▸</span>
            <span style="flex:1">
              <strong style="color:var(--text2);font-size:11px;text-transform:uppercase;
                             letter-spacing:.05em;font-weight:700">
                <?= htmlspecialchars($spec['label']) ?>
              </strong>
              <span style="display:block;color:var(--ink);font-weight:600;margin-top:2px">
                <?= htmlspecialchars($spec['value']) ?>
              </span>
            </span>
          </li>
          <?php endforeach; ?>
        </ul>
        <div style="margin-top:10px;font-size:11px;color:var(--text3);display:flex;align-items:center;gap:5px">
          <span style="color:var(--amber)">ℹ</span>
          Specifications are fixed for this product and cannot be changed.
        </div>
      </div>
      <?php endif; ?>

      <!-- ── QUALITY SELECTOR (shown only when multiple qualities exist) ── -->
      <?php if (count($qualities) > 1): ?>
      <div class="cfg" style="margin-bottom:14px">
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
      <div class="cfg">
        <div class="cfg-title">Quantity</div>
        <select class="fi fi-sel" id="pdQty" onchange="onQtyChange()" style="font-size:15px;font-weight:600">
          <option value="">— Select Quantity —</option>
          <!-- Populated by JS from API based on selected quality -->
        </select>
        <div style="margin-top:7px;font-size:11px;color:var(--text3)">
          Price varies by quantity — more pieces = better rate per unit.
        </div>
      </div>


      <!-- Attribute groups hidden in customer flow -->

      <!-- ── DESIGN OPTION ── -->
      <div class="cfg">
        <div class="cfg-title">Design Option</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px">

          <div class="design-opt sel" id="dopt-upload" onclick="selDesignOpt('upload')">
            <div style="font-size:26px;margin-bottom:7px">📁</div>
            <div style="font-size:13px;font-weight:700;color:var(--ink);margin-bottom:3px">I'll Upload My Design</div>
            <div style="font-size:11px;color:var(--text2);line-height:1.4">PDF, AI, PNG, JPG etc.</div>
            <div style="font-size:11px;color:var(--green);margin-top:6px;font-weight:600">No extra charge</div>
          </div>

          <div class="design-opt" id="dopt-rcs" onclick="selDesignOpt('rcs')">
            <div style="font-size:26px;margin-bottom:7px">🎨</div>
            <div style="font-size:13px;font-weight:700;color:var(--ink);margin-bottom:3px">Design by RCS Graphic</div>
            <div style="font-size:11px;color:var(--text2);line-height:1.4">We'll create your design</div>
            <?php if ($designFee > 0): ?>
            <div style="font-size:11px;color:var(--amber);margin-top:6px;font-weight:600">
              +₹<?= number_format($designFee) ?> design fee
            </div>
            <?php else: ?>
            <div style="font-size:11px;color:var(--text3);margin-top:6px">Fee confirmed on enquiry</div>
            <?php endif; ?>
          </div>

        </div>

        <!-- Upload panel -->
        <div id="panel-upload">
          <div class="upload-zone" id="uploadZone"
               onclick="document.getElementById('artworkFile').click()"
               ondragover="event.preventDefault();this.classList.add('drag')"
               ondragleave="this.classList.remove('drag')"
               ondrop="handleFileDrop(event)">
            <input type="file" id="artworkFile"
                   accept=".pdf,.ai,.eps,.png,.jpg,.jpeg,.psd,.cdr,.svg,.tif,.tiff,.zip"
                   onchange="handleFileSelect(event)">
            <div style="font-size:26px;margin-bottom:7px">📤</div>
            <div style="font-size:13px;font-weight:600;color:var(--text2);margin-bottom:3px">Click to upload or drag & drop</div>
            <div style="font-size:11px;color:var(--text3)">PDF, AI, EPS, PNG, JPG, PSD, CDR — Max 50MB</div>
          </div>
          <div id="uploadPreview"></div>
        </div>

        <!-- RCS Design panel kept empty intentionally -->
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
      <div style="display:flex;flex-direction:column;gap:10px">
        <button class="btn btn-blue btn-full" onclick="addToCart()" id="addCartBtn"
                style="padding:15px;font-size:15px;border-radius:12px">
          🛒 Add to Cart
        </button>
        <div style="display:flex;gap:9px">
          <button class="btn btn-green btn-full" onclick="buyNow()" style="padding:13px">
            ⚡ Buy Now
          </button>
          <button class="btn btn-outline" onclick="waOrder()" style="padding:13px;flex-shrink:0"
                  title="Order via WhatsApp">
            <svg viewBox="0 0 24 24" style="width:17px;height:17px;fill:currentColor">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
            WhatsApp
          </button>
        </div>
      </div>

    </div><!-- /pd-info-col -->
  </div><!-- /pd-grid -->

  <!-- RELATED PRODUCTS -->
  <?php if ($related): ?>
  <div style="margin-top:52px;padding-top:36px;border-top:1px solid var(--border)">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:10px">
      <div>
        <div class="sec-ey">More From Us</div>
        <div style="font-family:var(--fd);font-size:22px;font-weight:700;color:var(--ink)">You May Also Like</div>
      </div>
      <a href="/#prod-sec" class="btn btn-outline btn-sm">View All Products →</a>
    </div>

    <div class="prod-grid" style="grid-template-columns:repeat(<?= min(count($related), 3) ?>,1fr)">
      <?php foreach (array_slice($related, 0, 3) as $rp):
        $rimg = $rp['primary_image'] ?? '';
        $rmin = (float)($rp['min_price'] ?? 0);
      ?>
      <div class="pc" style="cursor:default">
        <a href="/product/<?= htmlspecialchars($rp['slug']) ?>" style="display:contents;text-decoration:none">
          <div class="pc-img">
            <img src="<?= htmlspecialchars($rimg) ?>" alt="<?= htmlspecialchars($rp['name']) ?>" loading="lazy"
                 onerror="this.src='https://placehold.co/400x300/EEF3FD/1A56E8?text=<?= urlencode($rp['name']) ?>'">
            <div class="pc-img-badge"><span class="badge b-blue"><?= htmlspecialchars($rp['category_name'] ?? '') ?></span></div>
          </div>
        </a>
        <div class="pc-body">
          <div class="pc-cat"><?= htmlspecialchars($rp['category_name'] ?? '') ?></div>
          <div class="pc-name"><?= htmlspecialchars($rp['name']) ?></div>
          <div class="pc-desc"><?= htmlspecialchars($rp['description'] ?? '') ?></div>
          <div class="pc-foot" style="flex-direction:column;align-items:stretch;gap:8px">
            <div style="display:flex;align-items:center;justify-content:space-between">
              <div>
                <div class="pc-from">Starting from</div>
                <div class="pc-price">₹<?= $rmin > 0 ? number_format($rmin) : '—' ?></div>
              </div>
            </div>
            <div style="display:flex;gap:7px">
              <a href="/product/<?= htmlspecialchars($rp['slug']) ?>"
                 class="btn btn-blue btn-sm btn-full" style="font-size:12px">
                Order Now →
              </a>
              <button onclick="window.open('https://wa.me/<?= htmlspecialchars($bizWa) ?>?text=<?= urlencode('Hi! I\'m interested in ' . $rp['name']) ?>','_blank')"
                      class="btn btn-outline btn-sm" style="font-size:12px;padding:7px 10px" title="WhatsApp">
                💬
              </button>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</div><!-- /container -->
</div><!-- /page-wrap -->

<!-- Sticky Price Bar -->
<div class="sticky-price show" id="stickyBar">
  <div>
    <div class="sp-sub">Total Price</div>
    <div class="sp-price" id="spTotal">₹ —</div>
  </div>
  <div style="display:flex;gap:8px">
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
let selectedQualityId  = <?= $qualities ? (int)$qualities[0]['id'] : 0 ?>;
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

// Gallery
function switchImg(url, el) {
  const img = document.getElementById('pdMainImg');
  img.style.opacity = '0.6';
  setTimeout(() => { img.src = url; img.style.opacity = '1'; }, 150);
  document.querySelectorAll('.pd-th').forEach(t => t.classList.remove('act'));
  el.classList.add('act');
}

// Quality Selection
function selQual(idx, qualId, clickedEl) {
  selectedQualityIdx = idx;
  selectedQualityId  = qualId;
  document.querySelectorAll('.qual-opt').forEach(el => el.classList.remove('sel'));
  if (clickedEl) clickedEl.classList.add('sel');
  reloadQtySlabs();
}

// Quantity Slabs
async function reloadQtySlabs() {
  if (!selectedQualityId) return;
  try {
    const resp = await fetch(`/api/products/${PRODUCT_ID}/pricing`);
    const data = await resp.json();
    const quality = (data.qualities || []).find(q => q.id == selectedQualityId);
    if (!quality) return;

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
}

// Price Calculation
function calcPrice() {
  if (!selectedQualityId || !selectedQty) {
    document.getElementById('ppBase').textContent  = '—';
    document.getElementById('ppTotal').textContent = '₹ —';
    document.getElementById('spTotal').textContent = '₹ —';
    currentBasePrice = 0;
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
}

// Design Option
function selDesignOpt(choice) {
  designChoice = choice;
  document.getElementById('dopt-upload').classList.toggle('sel', choice === 'upload');
  document.getElementById('dopt-rcs').classList.toggle('sel', choice === 'rcs');
  document.getElementById('panel-upload').style.display = choice === 'upload' ? 'block' : 'none';
  document.getElementById('panel-rcs').style.display    = choice === 'rcs' ? 'block' : 'none';
  calcPrice();
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
    return;
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
    } else {
      toast(data.msg || 'Could not add to cart', 'error');
    }
  } catch {
    toast('Error. Please try again.', 'error');
  } finally {
    btn.disabled = false;
    btn.textContent = '🛒 Add to Cart';
  }
}

async function buyNow() {
  const v = validateOrder();
  if (!v.ok) {
    toast(v.msg, 'error');
    return;
  }

  await addToCart({ silent: true });
  closeCart();
  location.href = '/checkout';
}

function validateOrder() {
  if (!selectedQty) {
    return { ok: false, msg: 'Please select a quantity' };
  }

  if (!selectedQualityId) {
    return { ok: false, msg: 'Please select a quality option' };
  }

  return { ok: true };
}

// WhatsApp Quick Order
function waOrder() {
  const totalEl = document.getElementById('ppTotal').textContent;
  const qname   = QUALITIES[selectedQualityIdx]?.name || '—';
  const qtyText = selectedQty ? Number(selectedQty).toLocaleString('en-IN') + ' pcs' : '—';
  const dOpt    = designChoice === 'rcs' ? 'Design by RCS Graphic' : 'Customer Upload';

  window.open(
    `https://wa.me/${BIZ_WA}?text=${encodeURIComponent(
      `Hi! I'd like to order:\n🖨️ <?= addslashes(htmlspecialchars($product['name'])) ?>\n📦 ${qtyText}\n⭐ ${qname}\n🎨 Design: ${dOpt}\n💰 ${totalEl}\nPlease confirm.`
    )}`,
    '_blank'
  );
}

// Init
reloadQtySlabs();
</script>

<?php include INCLUDE_PATH . '/partials/footer.php'; ?>