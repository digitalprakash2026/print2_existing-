<?php
$pageTitle = 'Your Cart — RCS Graphic';
include INCLUDE_PATH . '/partials/head.php';
include INCLUDE_PATH . '/partials/header.php';
$settingsMap = $settingsMap ?? [];
$bizPhone = htmlspecialchars($settingsMap['biz_phone'] ?? Database::setting('biz_phone', '+91 8980000023'));
$bizWaRaw = $settingsMap['biz_whatsapp'] ?? Database::setting('biz_whatsapp', env('BIZ_WHATSAPP', '918980000023'));
$bizWa = htmlspecialchars(preg_replace('/\D+/', '', (string)$bizWaRaw));
$itemCount = count($cartItems ?? []);
$cartRecommendations = $cartRecommendations ?? [];
?>
<div class="cartp-wrap">
  <div class="container cartp-page">
    <div class="cartp-head">
      <h1>Your Cart <span><?= (int)$itemCount ?> Items</span></h1>
      <p>Review your items and proceed to checkout.</p>
    </div>

    <?php if (empty($cartItems)): ?>
      <section class="cartp-empty">
        <div class="cartp-empty-icon">🛒</div>
        <h2>Your cart is empty</h2>
        <p>Browse our products and add your print items to continue.</p>
        <a href="/categories" class="btn btn-blue">Browse Products</a>
      </section>
    <?php else: ?>
    <div class="cartp-grid">
      <section class="cartp-main" aria-label="Shopping cart items">
        <div class="cartp-table-head">
          <span>Product</span><span>Price</span><span>Quantity</span><span>Total</span><span></span>
        </div>
        <?php foreach ($cartItems as $item):
          $itemId = (string)($item['id'] ?? '');
          $itemQty = (int)($item['quantity'] ?? 0);
          $lineTotal = (float)($item['total_price'] ?? 0);
          $breakdownRaw = $item['price_breakdown'] ?? '{}';
          $priceBreakdown = is_array($breakdownRaw) ? $breakdownRaw : (json_decode((string)$breakdownRaw, true) ?: []);
          $basePrice = (float)($priceBreakdown['base_price'] ?? $lineTotal);
          $designFee = (float)($priceBreakdown['design_fee'] ?? 0);
          $linePrice = $basePrice;
          $quality = trim((string)($item['quality_name'] ?? ($priceBreakdown['quality_name'] ?? '')));
          $designChoice = (string)($item['design_choice'] ?? ($priceBreakdown['design_choice'] ?? 'upload'));
          try {
              $pricingData = \Cart\Pricing::productPricingData((int)($item['product_id'] ?? 0));
              $qtyOptions = array_values(array_unique(array_map('intval', array_column($pricingData['tiers'] ?? [], 'quantity'))));
              sort($qtyOptions);
          } catch (\Throwable) {
              $qtyOptions = [];
          }
          if (!$qtyOptions) $qtyOptions = range(1000, 10000, 1000);
          if ($itemQty > 0 && !in_array($itemQty, $qtyOptions, true)) {
              $qtyOptions[] = $itemQty;
              sort($qtyOptions);
          }
        ?>
        <article class="cartp-row">
          <div class="cartp-prod" data-label="Product">
            <a class="cartp-img" href="/product/<?= htmlspecialchars($item['slug'] ?? '') ?>">
              <img src="<?= htmlspecialchars($item['product_image'] ?? '') ?>" alt="<?= htmlspecialchars($item['product_name'] ?? '') ?>" onerror="this.style.display='none'">
            </a>
            <div class="cartp-prod-copy">
              <h3><?= htmlspecialchars($item['product_name'] ?? '') ?></h3>
              <p><?= number_format($itemQty) ?> pcs<?= $quality !== '' ? ', ' . htmlspecialchars($quality) : '' ?></p>
              <?php if ($designChoice === 'rcs'): ?>
                <small>Design by RCS Graphic<?= $designFee > 0 ? ' (+₹' . number_format($designFee) . ')' : '' ?></small>
              <?php else: ?>
                <small>Customer artwork upload (No design fee)</small>
              <?php endif; ?>
            </div>
          </div>
          <div class="cartp-price" data-label="Price">
            <strong>₹<?= number_format($linePrice) ?></strong>
            <small>Base price</small>
          </div>
          <div class="cartp-qty" data-label="Quantity">
            <select class="cartp-qty-select" onchange="updateCartQty('<?= htmlspecialchars($itemId, ENT_QUOTES) ?>', this.value, this)" aria-label="Select quantity for <?= htmlspecialchars($item['product_name'] ?? '', ENT_QUOTES) ?>">
              <?php foreach ($qtyOptions as $qty): ?>
                <option value="<?= (int)$qty ?>" <?= $qty === $itemQty ? 'selected' : '' ?>><?= number_format((int)$qty) ?> pcs</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="cartp-total" data-label="Total">
            <strong>₹<?= number_format($lineTotal) ?></strong>
            <?php if ($designFee > 0): ?><small>Includes ₹<?= number_format($designFee) ?> design fee</small><?php endif; ?>
          </div>
          <button class="cartp-del" onclick="removeCartItem('<?= htmlspecialchars($itemId, ENT_QUOTES) ?>')" aria-label="Remove <?= htmlspecialchars($item['product_name'] ?? 'item', ENT_QUOTES) ?>">
            <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
          </button>
        </article>
        <?php endforeach; ?>

        <div class="cartp-actions">
          <a href="/categories" class="cartp-continue"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Continue Shopping</a>
          <button class="cartp-clear" onclick="clearCartPage()"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Clear Cart</button>
        </div>
      </section>

      <aside class="cartp-side" aria-label="Order summary">
        <div class="cartp-card cartp-summary-card">
          <h2>Order Summary</h2>
          <div class="r"><span>Subtotal (<?= (int)$itemCount ?> Items)</span><strong>₹<?= number_format((float)$totals['subtotal']) ?></strong></div>
          <div class="r"><span>Discount</span><button class="link" onclick="document.getElementById('promoInp').focus()">APPLY</button></div>
          <div class="r"><span>Shipping</span><strong class="is-free">Free</strong></div>
          <div class="rt"><span>Total</span><strong>₹<?= number_format((float)$totals['total']) ?></strong></div>
          <a href="/checkout" class="cartp-checkout">Proceed to Checkout <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
          <div class="cartp-secure"><i class="fa-solid fa-lock" aria-hidden="true"></i> Secure Checkout</div>
        </div>

        <div class="cartp-card cartp-promo-card">
          <div class="cartp-promo-head">
            <span><i class="fa-solid fa-ticket" aria-hidden="true"></i></span>
            <div><h3>Have a Promo Code?</h3><p>Enter code and get exciting discounts!</p></div>
          </div>
          <div class="coupon-row"><input id="promoInp" placeholder="Enter promo code"><button class="btn btn-outline btn-sm" onclick="applyPromoOnCartPage()">APPLY</button></div>
          <div id="promoMsg"></div>
        </div>

        <div class="cartp-why-card">
          <h3>Why Shop With <span>RCS PRINT?</span></h3>
          <div class="cartp-why-list">
            <div><i class="fa-solid fa-pen-ruler" aria-hidden="true"></i><p><strong>Premium Quality Prints</strong><span>Top-notch materials and printing.</span></p></div>
            <div><i class="fa-solid fa-truck-fast" aria-hidden="true"></i><p><strong>Fast & Free Delivery</strong><span>On orders above ₹999 in Rajkot.</span></p></div>
            <div><i class="fa-solid fa-shield-halved" aria-hidden="true"></i><p><strong>100% Secure Checkout</strong><span>Your payment information is safe.</span></p></div>
            <div><i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i><p><strong>Easy Returns</strong><span>Hassle-free returns & refunds.</span></p></div>
          </div>
          <div class="cartp-gift" aria-hidden="true">🎁</div>
        </div>
      </aside>
    </div>
    <?php endif; ?>

    <?php if (!empty($cartRecommendations)): ?>
    <section class="ym-section cartp-recommendations" aria-labelledby="cartRecommendationsTitle">
      <div class="ym-head">
        <h2 class="ym-title" id="cartRecommendationsTitle">You May <span>Also Like</span></h2>
        <a href="/categories" class="ym-view-all">View All Products</a>
      </div>

      <div class="ym-grid">
        <?php foreach ($cartRecommendations as $rp):
          $rimg = trim((string)($rp['primary_image'] ?? ''));
          $rname = trim((string)($rp['name'] ?? 'Product'));
          $rslug = trim((string)($rp['slug'] ?? ''));
          $rmin = (float)($rp['min_price'] ?? 0);
        ?>
        <article class="ym-card">
          <a class="ym-img" href="/product/<?= htmlspecialchars($rslug) ?>">
            <img src="<?= htmlspecialchars($rimg) ?>" alt="<?= htmlspecialchars($rname) ?>" loading="lazy"
                 onerror="this.src='https://placehold.co/400x260/EEF3FD/1A56E8?text=<?= urlencode($rname) ?>'">
          </a>
          <div class="ym-body">
            <div class="ym-cat"><i class="fa-solid fa-layer-group" aria-hidden="true"></i><?= htmlspecialchars($rname) ?></div>
            <div class="ym-from">Starting from</div>
            <div class="ym-foot">
              <div class="ym-price">₹<?= $rmin > 0 ? number_format($rmin) : '—' ?></div>
              <a href="/product/<?= htmlspecialchars($rslug) ?>" class="ym-order">ORDER NOW</a>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <section class="quick-help-section cartp-help-section" aria-label="Quick help and bulk order actions">
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

  </div>
</div>
<script>
const CSRF='<?= $csrf ?>';
async function removeCartItem(id){
  if(!id) return;
  await fetch(`/api/cart/remove/${encodeURIComponent(id)}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF},credentials:'same-origin'});
  location.reload();
}
async function updateCartQty(id, quantity, el){
  if(!id || !quantity) return;
  const previous = el?.dataset?.previous || '';
  if (el) el.disabled = true;
  try {
    const resp = await fetch(`/api/cart/update/${encodeURIComponent(id)}`,{
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
      credentials:'same-origin',
      body:JSON.stringify({quantity:parseInt(quantity,10)})
    });
    const data = await resp.json();
    if(!data.ok){
      if (previous && el) el.value = previous;
      alert(data.msg || 'Could not update quantity');
      return;
    }
    location.reload();
  } catch(e) {
    if (previous && el) el.value = previous;
    alert('Could not update quantity. Please try again.');
  } finally {
    if (el) el.disabled = false;
  }
}
document.querySelectorAll('.cartp-qty-select').forEach(sel => { sel.dataset.previous = sel.value; });
async function clearCartPage(){ await fetch('/api/cart/clear',{method:'POST',headers:{'X-CSRF-TOKEN':CSRF},credentials:'same-origin'}); location.reload(); }
async function applyPromoOnCartPage(){ const code=document.getElementById('promoInp').value.trim().toUpperCase(); if(!code) return; const r=await fetch('/api/coupon/validate',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},body:JSON.stringify({code})}); const d=await r.json(); document.getElementById('promoMsg').innerHTML=d.ok?`<div class="coupon-applied">Applied: ${code}</div>`:`<div style="font-size:12px;color:var(--red)">${d.msg||'Invalid'}</div>`; }
</script>
<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
