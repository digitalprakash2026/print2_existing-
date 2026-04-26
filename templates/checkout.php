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

    <div style="background:var(--white);border-radius:12px;border:1.5px solid var(--border);padding:16px;margin-bottom:16px">
      <div style="font-size:12px;font-weight:700;color:var(--text2);margin-bottom:12px;text-transform:uppercase;letter-spacing:.06em">📦 Delivery Address</div>
      <div class="fg"><label>Address Line 1 *</label><input id="s-add1" class="fi" placeholder="House / Building / Street"></div>
      <div class="fg"><label>Address Line 2 (optional)</label><input id="s-add2" class="fi" placeholder="Area / Landmark"></div>
      <div class="f2">
        <div class="fg"><label>City *</label><input id="s-city" class="fi" placeholder="Rajkot"></div>
        <div class="fg"><label>State *</label><input id="s-state" class="fi" placeholder="Gujarat"></div>
      </div>
      <div class="fg" style="margin-bottom:0"><label>Pincode *</label><input id="s-pin" class="fi" placeholder="360001"></div>
      <?php if (!empty($user['id'])): ?>
      <label id="ship-save-wrap" style="display:none;align-items:center;gap:8px;font-size:12px;color:var(--text2);margin-top:10px">
        <input type="checkbox" id="ship-save-default" style="accent-color:var(--blue)">
        Save this as my default delivery address
      </label>
      <?php endif; ?>
      <div id="shipErr" style="display:none;font-size:12px;color:var(--red);margin-top:8px"></div>
    </div>

    <div style="background:var(--white);border-radius:12px;border:1.5px solid var(--border);padding:16px;margin-bottom:16px">
      <div style="font-size:12px;font-weight:700;color:var(--text2);margin-bottom:12px;text-transform:uppercase;letter-spacing:.06em">🧾 Billing Details (Tax Invoice)</div>
      <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text2);margin-bottom:12px">
        <input type="checkbox" id="bill-same-ship" style="accent-color:var(--blue)" onchange="syncBillingFromShipping()">
        Same as Delivery Address
      </label>
      <div id="billingFields">
        <div class="fg"><label>Legal Business Name *</label><input id="b-legal" class="fi" placeholder="ABC Pvt Ltd"></div>
        <div class="fg"><label>GSTIN *</label><input id="b-gst" class="fi" placeholder="24ABCDE1234F1Z5" style="text-transform:uppercase" oninput="this.value=this.value.toUpperCase()"></div>
        <div class="fg"><label>Billing Address Line 1 *</label><input id="b-add1" class="fi" placeholder="Street / Building"></div>
        <div class="fg"><label>Billing Address Line 2 (optional)</label><input id="b-add2" class="fi" placeholder="Area / Landmark"></div>
        <div class="f2">
          <div class="fg"><label>City *</label><input id="b-city" class="fi" placeholder="Rajkot"></div>
          <div class="fg"><label>State *</label><input id="b-state" class="fi" placeholder="Gujarat"></div>
        </div>
        <div class="fg" style="margin-bottom:0"><label>Pincode *</label><input id="b-pin" class="fi" placeholder="360001"></div>
      </div>
      <div id="billErr" style="display:none;font-size:12px;color:var(--red);margin-top:8px"></div>
    </div>

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
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px">
          <div style="font-family:var(--fd);font-size:15px;font-weight:700;color:var(--blue)">₹<?= number_format($item['total_price']) ?></div>
          <?php if (!empty($item['id'])): ?>
          <button type="button" class="btn btn-outline btn-sm" onclick="removeCheckoutItem('<?= htmlspecialchars((string)$item['id'], ENT_QUOTES) ?>')" style="padding:5px 10px;font-size:11px">✕ Remove</button>
          <?php endif; ?>
        </div>
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
      <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;padding-top:8px;border-top:1px solid var(--border)"><span>Total</span><span style="color:var(--blue);font-family:var(--fd)">₹<?= number_format($totals['total']) ?></span></div>
    </div>

    <div style="background:var(--white);border-radius:12px;border:1.5px solid var(--border);padding:14px 16px;margin-bottom:14px">
      <label style="display:flex;align-items:flex-start;gap:10px;font-size:13px;color:var(--text2);line-height:1.55">
        <input type="checkbox" id="ship-consent" style="accent-color:var(--blue);margin-top:2px">
        <span>Shipping charges <strong>will</strong> apply based on total package weight and delivery location. Final shipping details will be shared with you via call or message before dispatch.</span>
      </label>
      <div id="shipConsentErr" style="display:none;font-size:12px;color:var(--red);margin-top:8px"></div>
    </div>
    <div style="background:var(--white);border-radius:12px;border:1.5px solid var(--border);padding:14px 16px;margin-bottom:14px">
      <label style="display:flex;align-items:flex-start;gap:10px;font-size:13px;color:var(--text2);line-height:1.55">
        <input type="checkbox" id="terms-consent" style="accent-color:var(--blue);margin-top:2px">
        <span>I have read and agree to the <a href="/terms-and-conditions" target="_blank" style="color:var(--blue);font-weight:700">Terms &amp; Conditions</a>.</span>
      </label>
      <div id="termsConsentErr" style="display:none;font-size:12px;color:var(--red);margin-top:8px"></div>
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
      💬 Share Order on WhatsApp
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
let checkoutProfile = { shipping: null, billing: null };

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
        <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;padding-top:8px;border-top:1px solid var(--border)"><span>Total</span><span style="color:var(--blue);font-family:var(--fd)">${fmt(tot.total)}</span></div>`;
    }
  } else {
    msg.innerHTML = `<div style="font-size:12px;color:var(--red);margin-top:6px">${data.msg}</div>`;
  }
}

function doCheckout() {
  const consentErr = document.getElementById('shipConsentErr');
  if (!document.getElementById('ship-consent')?.checked) {
    if (consentErr) {
      consentErr.textContent = 'Please confirm shipping charge acknowledgement to continue.';
      consentErr.style.display = 'block';
    }
    return;
  }
  if (consentErr) consentErr.style.display = 'none';
  const termsErr = document.getElementById('termsConsentErr');
  if (!document.getElementById('terms-consent')?.checked) {
    if (termsErr) {
      termsErr.textContent = 'Please accept Terms & Conditions to continue.';
      termsErr.style.display = 'block';
    }
    return;
  }
  if (termsErr) termsErr.style.display = 'none';
  const customer = getCheckoutCustomer();
  if (customer === false) return;
  const shipping = getCheckoutShipping();
  if (shipping === false) return;
  const billing = getCheckoutBilling();
  if (billing === false) return;
  initiateCheckout(checkoutCoupon, customer, billing, shipping);
}

async function doWhatsAppOrder() {
  const consentErr = document.getElementById('shipConsentErr');
  if (!document.getElementById('ship-consent')?.checked) {
    if (consentErr) {
      consentErr.textContent = 'Please confirm shipping charge acknowledgement to continue.';
      consentErr.style.display = 'block';
    }
    return;
  }
  if (consentErr) consentErr.style.display = 'none';
  const termsErr = document.getElementById('termsConsentErr');
  if (!document.getElementById('terms-consent')?.checked) {
    if (termsErr) {
      termsErr.textContent = 'Please accept Terms & Conditions to continue.';
      termsErr.style.display = 'block';
    }
    return;
  }
  if (termsErr) termsErr.style.display = 'none';
  const customer = getCheckoutCustomer();
  if (customer === false) return;
  const shipping = getCheckoutShipping();
  if (shipping === false) return;
  const billing = getCheckoutBilling();
  if (billing === false) return;
  const notes = '';
  await placeWhatsappOrder(checkoutCoupon, notes, customer, billing, shipping);
}

async function removeCheckoutItem(itemId) {
  if (!itemId) return;
  try {
    const resp = await fetch(`/api/cart/remove/${encodeURIComponent(itemId)}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': CSRF },
      credentials: 'same-origin'
    });
    const data = await resp.json();
    if (!data.ok) {
      alert(data.msg || 'Could not remove item.');
      return;
    }
    window.location.reload();
  } catch (e) {
    alert('Could not remove item right now.');
  }
}

function toggleBillingFields() {
  const err = document.getElementById('billErr');
  if (err) err.style.display = 'none';
}

function toggleShippingFields() {
  const err = document.getElementById('shipErr');
  const saveWrap = document.getElementById('ship-save-wrap');
  if (saveWrap) saveWrap.style.display = 'flex';
  if (err) err.style.display = 'none';
}

function syncBillingFromShipping() {
  const same = !!document.getElementById('bill-same-ship')?.checked;
  if (!same) return;
  setField('b-add1', document.getElementById('s-add1')?.value || '');
  setField('b-add2', document.getElementById('s-add2')?.value || '');
  setField('b-city', document.getElementById('s-city')?.value || '');
  setField('b-state', document.getElementById('s-state')?.value || '');
  setField('b-pin', document.getElementById('s-pin')?.value || '');
}

function getCheckoutCustomer() {
  <?php if (!empty($user['id'])): ?>
  return {};
  <?php else: ?>
  const name = document.getElementById('g-name')?.value.trim() || '';
  const email = document.getElementById('g-email')?.value.trim() || '';
  const phone = document.getElementById('g-phone')?.value.trim() || '';
  const err = document.getElementById('guestErr');
  const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
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

function getCheckoutBilling() {
  const legal = document.getElementById('b-legal')?.value.trim() || '';
  const gst = (document.getElementById('b-gst')?.value || '').trim().toUpperCase();
  const add1 = document.getElementById('b-add1')?.value.trim() || '';
  const add2 = document.getElementById('b-add2')?.value.trim() || '';
  const city = document.getElementById('b-city')?.value.trim() || '';
  const state = document.getElementById('b-state')?.value.trim() || '';
  const pin = document.getElementById('b-pin')?.value.trim() || '';

  const err = document.getElementById('billErr');
  const gstOk = /^[0-9]{2}[A-Z0-9]{10}[0-9A-Z]{3}$/.test(gst);
  const pinOk = /^[1-9][0-9]{5}$/.test(pin);

  if (!legal || !gst || !add1 || !city || !state || !pin) {
    if (err) { err.textContent = 'Please fill all required billing fields for GST invoice.'; err.style.display = 'block'; }
    return false;
  }
  if (!gstOk) {
    if (err) { err.textContent = 'Please enter a valid GSTIN.'; err.style.display = 'block'; }
    return false;
  }
  if (!pinOk) {
    if (err) { err.textContent = 'Please enter a valid 6-digit pincode.'; err.style.display = 'block'; }
    return false;
  }
  if (err) err.style.display = 'none';

  return {
    required: true,
    legal_name: legal,
    gst_no: gst,
    address_line1: add1,
    address_line2: add2,
    city,
    state,
    pincode: pin,
  };
}

function getCheckoutShipping() {
  const add1 = document.getElementById('s-add1')?.value.trim() || '';
  const add2 = document.getElementById('s-add2')?.value.trim() || '';
  const city = document.getElementById('s-city')?.value.trim() || '';
  const state = document.getElementById('s-state')?.value.trim() || '';
  const pin = document.getElementById('s-pin')?.value.trim() || '';
  const err = document.getElementById('shipErr');
  const pinOk = /^[1-9][0-9]{5}$/.test(pin);

  if (!add1 || !city || !state || !pin) {
    if (err) { err.textContent = 'Please fill delivery address details to continue.'; err.style.display = 'block'; }
    return false;
  }
  if (!pinOk) {
    if (err) { err.textContent = 'Please enter a valid 6-digit delivery pincode.'; err.style.display = 'block'; }
    return false;
  }
  if (err) err.style.display = 'none';

  return {
    address_line1: add1,
    address_line2: add2,
    city,
    state,
    pincode: pin,
    save_as_default: !!document.getElementById('ship-save-default')?.checked,
  };
}

function setField(id, val = '') {
  const el = document.getElementById(id);
  if (el) el.value = val || '';
}

async function prefillCheckoutFromProfile() {
  <?php if (empty($user['id'])): ?>
  return;
  <?php else: ?>
  try {
    const resp = await fetch('/api/profile', { credentials: 'same-origin' });
    const data = await resp.json();
    if (!data.ok || !data.profile) return;

    checkoutProfile.shipping = data.profile.shipping || null;
    checkoutProfile.billing = data.profile.billing || null;

    if (checkoutProfile.shipping) {
      setField('s-add1', checkoutProfile.shipping.address_line1);
      setField('s-add2', checkoutProfile.shipping.address_line2);
      setField('s-city', checkoutProfile.shipping.city);
      setField('s-state', checkoutProfile.shipping.state);
      setField('s-pin', checkoutProfile.shipping.pincode);
    }

    if (checkoutProfile.billing) {
      setField('b-legal', checkoutProfile.billing.legal_name);
      setField('b-gst', checkoutProfile.billing.gst_no);
      setField('b-add1', checkoutProfile.billing.address_line1);
      setField('b-add2', checkoutProfile.billing.address_line2);
      setField('b-city', checkoutProfile.billing.city);
      setField('b-state', checkoutProfile.billing.state);
      setField('b-pin', checkoutProfile.billing.pincode);
      toggleBillingFields();
    }

    toggleShippingFields();
  } catch (e) { /* ignore prefill failures */ }
  <?php endif; ?>
}

document.addEventListener('DOMContentLoaded', () => {
  ['s-add1','s-add2','s-city','s-state','s-pin'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', syncBillingFromShipping);
  });
  prefillCheckoutFromProfile();
});
</script>
<script src="/assets/js/app.js"></script>
<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
