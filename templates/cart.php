<?php
$pageTitle = 'Your Cart — RCS Graphic';
include INCLUDE_PATH . '/partials/head.php';
include INCLUDE_PATH . '/partials/header.php';
$bizWa = Database::setting('biz_whatsapp', env('BIZ_WHATSAPP', ''));
$itemCount = count($cartItems ?? []);
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
        <a href="/products" class="btn btn-blue">Browse Products</a>
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
          $linePrice = (float)($item['unit_price'] ?? $lineTotal);
          $quality = trim((string)($item['quality_name'] ?? ''));
          $designChoice = (string)($item['design_choice'] ?? 'upload');
          $qtyOptions = range(1000, 10000, 1000);
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
                <small>Design by RCS Graphic</small>
              <?php else: ?>
                <small>Customer artwork upload</small>
              <?php endif; ?>
            </div>
          </div>
          <div class="cartp-price" data-label="Price">₹<?= number_format($linePrice) ?></div>
          <div class="cartp-qty" data-label="Quantity">
            <select class="cartp-qty-select" onchange="updateCartQty('<?= htmlspecialchars($itemId, ENT_QUOTES) ?>', this.value, this)" aria-label="Select quantity for <?= htmlspecialchars($item['product_name'] ?? '', ENT_QUOTES) ?>">
              <?php foreach ($qtyOptions as $qty): ?>
                <option value="<?= (int)$qty ?>" <?= $qty === $itemQty ? 'selected' : '' ?>><?= number_format((int)$qty) ?> pcs</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="cartp-total" data-label="Total">₹<?= number_format($lineTotal) ?></div>
          <button class="cartp-del" onclick="removeCartItem('<?= htmlspecialchars($itemId, ENT_QUOTES) ?>')" aria-label="Remove <?= htmlspecialchars($item['product_name'] ?? 'item', ENT_QUOTES) ?>">
            <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
          </button>
        </article>
        <?php endforeach; ?>

        <div class="cartp-actions">
          <a href="/products" class="cartp-continue"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Continue Shopping</a>
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
