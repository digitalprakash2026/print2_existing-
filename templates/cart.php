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

    <div class="cartp-grid">
      <section class="cartp-main">
        <div class="cartp-table-head">
          <span>Product</span><span>Price</span><span>Quantity</span><span>Total</span>
        </div>
        <?php foreach ($cartItems as $item): ?>
        <article class="cartp-row">
          <div class="cartp-prod">
            <div class="cartp-img"><img src="<?= htmlspecialchars($item['product_image'] ?? '') ?>" alt="<?= htmlspecialchars($item['product_name'] ?? '') ?>" onerror="this.style.display='none'"></div>
            <div>
              <h3><?= htmlspecialchars($item['product_name'] ?? '') ?></h3>
              <p><?= number_format((int)$item['quantity']) ?> pcs, <?= htmlspecialchars($item['quality_name'] ?? '') ?></p>
              <?php if (($item['design_choice'] ?? '') === 'rcs'): ?><small>Design by RCS Graphic</small><?php endif; ?>
            </div>
          </div>
          <div class="cartp-price">₹<?= number_format((float)$item['unit_price']) ?></div>
          <div class="cartp-qty"><button disabled>−</button><span><?= (int)$item['quantity'] ?></span><button disabled>+</button></div>
          <div class="cartp-total">₹<?= number_format((float)$item['total_price']) ?></div>
          <button class="cartp-del" onclick="removeCartItem('<?= htmlspecialchars((string)$item['id'], ENT_QUOTES) ?>')">🗑</button>
        </article>
        <?php endforeach; ?>

        <div class="cartp-actions">
          <a href="/products" class="btn btn-outline">← Continue Shopping</a>
          <button class="btn btn-ghost" onclick="clearCartPage()">🗑 Clear Cart</button>
        </div>
      </section>

      <aside class="cartp-side">
        <div class="cartp-card">
          <h2>Order Summary</h2>
          <div class="r"><span>Subtotal (<?= (int)$itemCount ?> Items)</span><strong>₹<?= number_format((float)$totals['subtotal']) ?></strong></div>
          <div class="r"><span>Discount</span><button class="link" onclick="document.getElementById('promoInp').focus()">APPLY</button></div>
          <div class="r"><span>Shipping</span><strong style="color:#16a34a">Free</strong></div>
          <div class="rt"><span>Total</span><strong>₹<?= number_format((float)$totals['total']) ?></strong></div>
          <a href="/checkout" class="btn btn-blue btn-full">Proceed to Checkout →</a>
        </div>

        <div class="cartp-card">
          <h3>Have a Promo Code?</h3>
          <p>Enter code and get exciting discounts!</p>
          <div class="coupon-row"><input id="promoInp" placeholder="Enter promo code"><button class="btn btn-outline btn-sm" onclick="applyPromoOnCartPage()">APPLY</button></div>
          <div id="promoMsg"></div>
        </div>
      </aside>
    </div>
  </div>
</div>
<script>
const CSRF='<?= $csrf ?>';
async function removeCartItem(id){ if(!id) return; await fetch(`/api/cart/remove/${encodeURIComponent(id)}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF},credentials:'same-origin'}); location.reload(); }
async function clearCartPage(){ await fetch('/api/cart/clear',{method:'POST',headers:{'X-CSRF-TOKEN':CSRF},credentials:'same-origin'}); location.reload(); }
async function applyPromoOnCartPage(){ const code=document.getElementById('promoInp').value.trim().toUpperCase(); if(!code) return; const r=await fetch('/api/coupon/validate',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({code})}); const d=await r.json(); document.getElementById('promoMsg').innerHTML=d.ok?`<div class="coupon-applied">Applied: ${code}</div>`:`<div style="font-size:12px;color:var(--red)">${d.msg||'Invalid'}</div>`; }
</script>
<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
