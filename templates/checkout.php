<?php
$pageTitle = 'Checkout — RCS Graphic';
$loadRazorpay = true;
include INCLUDE_PATH . '/partials/head.php';
include INCLUDE_PATH . '/partials/header.php';
// cart-drawer is included by header.php — do not include again
$razKeyId = Database::setting('razorpay_key_id', env('RAZORPAY_KEY_ID', ''));
$bizWa = Database::setting('biz_whatsapp', env('BIZ_WHATSAPP', ''));
?>
<div style="margin-top:var(--hh);min-height:calc(100vh - var(--hh));background:var(--bg);padding:32px 0 80px">
  <div class="container" style="max-width:760px">
    <div style="font-family:var(--fd);font-size:24px;font-weight:700;margin-bottom:22px">Checkout</div>

    <?php if (empty($user['id'])): ?>
    <div style="background:var(--white);border-radius:12px;border:1.5px solid var(--border);padding:16px;margin-bottom:16px">
      <div style="font-size:12px;font-weight:700;color:var(--text2);margin-bottom:12px;text-transform:uppercase;letter-spacing:.06em">👤 Guest Details</div>
      <div class="fg"><label>Full Name *</label><input id="g-name" class="fi" placeholder="Your full name"></div>
      <div class="fg"><label>Email *</label><input id="g-email" type="email" class="fi" placeholder="email@example.com"></div>
      <div class="fg" style="margin-bottom:0"><label>Phone *</label><input id="g-phone" type="tel" class="fi" placeholder="+91 98765 43210"></div>
      <div style="font-size:12px;color:var(--text3);margin-top:8px">
        Already have account? <a href="/login" style="color:var(--blue);font-weight:600">Login here</a>
      </div>
      <div id="guestErr" style="display:none;font-size:12px;color:var(--red);margin-top:8px"></div>
    </div>
    <?php endif; ?>

    <!-- Order Summary -->
    <div class="fsec" style="background:var(--white);border-radius:12px;border:1.5px solid var(--border);padding:18px;margin-bottom:16px">
      <div style="font-family:var(--fd);font-size:16px;font-weight:700;margin-bottom:14px;color:var(--blue)">📋 Order Summary</div>
      <?php foreach ($cartItems as $item): ?>
      <div style="display:flex;gap:12px;padding:10px 0;border-bottom:1px solid var(--border)">
        <div style="width:50px;height:50px;border-radius:8px;background:var(--bg2);overflow:hidden;flex-shrink:0">
          <img src="<?= htmlspecialchars($item['product_image'] ?? '') ?>" style="width:100%;height:100%;object-fit:cover" onerror="this.style.display='none'">
        </div>
        <div style="flex:1">
          <div style="font-size:14px;font-weight:700"><?= htmlspecialchars($item['product_name']) ?></div>
          <div style="font-size:12px;color:var(--text2)"><?= number_format($item['quantity']) ?> pcs · <?= htmlspecialchars($item['quality_name']) ?></div>
          <?php if ($item['design_choice'] === 'rcs'): ?>
          <div style="font-size:11px;color:var(--blue)">🎨 Design by RCS Graphic</div>
          <?php endif; ?>
        </div>
        <div style="font-family:var(--fd);font-size:15px;font-weight:700;color:var(--blue)">₹<?= number_format($item['total_price']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Coupon -->
    <div style="background:var(--white);border-radius:12px;border:1.5px solid var(--border);padding:16px;margin-bottom:16px">
      <div style="font-size:12px;font-weight:700;color:var(--text2);margin-bottom:10px;text-transform:uppercase;letter-spacing:.06em">🎟️ Coupon Code</div>
      <div class="coupon-row">
        <input id="couponInp" placeholder="Enter coupon code" style="text-transform:uppercase" oninput="this.value=this.value.toUpperCase()">
        <button class="btn btn-outline btn-sm" onclick="applyCouponCheckout()">Apply</button>
      </div>
      <div id="couponMsg"></div>
    </div>

    <!-- Totals -->
    <div style="background:var(--white);border-radius:12px;border:1.5px solid var(--border);padding:16px;margin-bottom:20px" id="totalsBox">
      <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--text2)">Subtotal</span><span>₹<?= number_format($totals['subtotal']) ?></span></div>
      <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--text2)">GST (<?= $totals['gst_pct'] ?>%)</span><span>₹<?= number_format($totals['gst_amt']) ?></span></div>
      <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--text2)">Shipping</span><span><?= ($totals['shipping'] ?? 0) > 0 ? '₹' . number_format($totals['shipping']) : 'Free' ?></span></div>
      <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;padding-top:8px;border-top:1px solid var(--border)"><span>Total</span><span style="color:var(--blue);font-family:var(--fd)">₹<?= number_format($totals['total']) ?></span></div>
    </div>

    <!-- Payment Buttons -->
    <?php if ($razKeyId): ?>
    <button class="btn btn-blue btn-full" onclick="doCheckout()" style="padding:16px;font-size:16px;border-radius:12px;margin-bottom:10px">
      🔒 Pay Securely with Razorpay
    </button>
    <?php else: ?>
    <div style="background:var(--amber-bg);border:1px solid var(--amber-mid);border-radius:10px;padding:12px 16px;font-size:13px;color:var(--amber);margin-bottom:10px">
      ⚠️ Online payment not configured. Please use WhatsApp to confirm your order.
    </div>
    <?php endif; ?>
    <button class="btn btn-outline btn-full" onclick="doWhatsAppOrder()" style="padding:14px;font-size:14px;border-radius:12px">
      💬 Place Order via WhatsApp
    </button>
    <div style="text-align:center;margin-top:14px;font-size:12px;color:var(--text3)">
      🔒 Secured by Razorpay · GST Invoice included · Your data is safe
    </div>
  </div>
</div>

<script>
const CSRF = '<?= $csrf ?>';
const BIZ_WA = '<?= htmlspecialchars($bizWa) ?>';
let checkoutCoupon = null;

async function applyCouponCheckout() {
  const code = document.getElementById('couponInp').value.trim().toUpperCase();
  if (!code) return;
  const resp = await fetch('/api/coupon/validate', {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ code })
  });
  const data = await resp.json();
  const msg = document.getElementById('couponMsg');
  if (data.ok) {
    checkoutCoupon = code;
    msg.innerHTML = `<div class="coupon-applied" style="margin-top:8px">🎟️ ${code} applied! Discount: ₹${data.discount}</div>`;
    // Reload totals display
    const t = await fetch('/api/cart?coupon=' + code).then(r => r.json());
    if (t.totals) {
      const tot = t.totals;
      const fmt = n => '₹' + Number(n).toLocaleString('en-IN');
      document.getElementById('totalsBox').innerHTML = `
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--text2)">Subtotal</span><span>${fmt(tot.subtotal)}</span></div>
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;color:var(--green)"><span>Discount</span><span>-${fmt(tot.discount)}</span></div>
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--text2)">GST (${tot.gst_pct}%)</span><span>${fmt(tot.gst_amt)}</span></div>
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px"><span style="color:var(--text2)">Shipping</span><span>${(Number(tot.shipping||0) > 0) ? fmt(tot.shipping) : 'Free'}</span></div>
        <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;padding-top:8px;border-top:1px solid var(--border)"><span>Total</span><span style="color:var(--blue);font-family:var(--fd)">${fmt(tot.total)}</span></div>`;
    }
  } else {
    msg.innerHTML = `<div style="font-size:12px;color:var(--red);margin-top:6px">${data.msg}</div>`;
  }
}

function doCheckout() {
  const customer = getCheckoutCustomer();
  if (customer === false) return;
  initiateCheckout(checkoutCoupon, customer);
}

async function doWhatsAppOrder() {
  const customer = getCheckoutCustomer();
  if (customer === false) return;
  const notes = '';
  await placeWhatsappOrder(checkoutCoupon, notes, customer);
}

function getCheckoutCustomer() {
  <?php if (!empty($user['id'])): ?>
  return {};
  <?php else: ?>
  const name = document.getElementById('g-name')?.value.trim() || '';
  const email = document.getElementById('g-email')?.value.trim() || '';
  const phone = document.getElementById('g-phone')?.value.trim() || '';
  const err = document.getElementById('guestErr');
  const emailOk = /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/.test(email);
  if (!name || !email || !phone) {
    err.textContent = 'Please fill name, email and phone to continue checkout.';
    err.style.display = 'block';
    return false;
  }
  if (!emailOk) {
    err.textContent = 'Please enter a valid email address.';
    err.style.display = 'block';
    return false;
  }
  err.style.display = 'none';
  return { name, email, phone };
  <?php endif; ?>
}
</script>
<script src="/assets/js/app.js"></script>
</body></html>
