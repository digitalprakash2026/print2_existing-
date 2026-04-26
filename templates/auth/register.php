<?php $pageTitle = 'Register — RCS Graphic'; include INCLUDE_PATH . '/partials/head.php'; include INCLUDE_PATH . '/partials/header.php'; ?>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-mark">R</div>
    <div class="auth-t">Create Account</div>
    <div class="auth-s">Join RCS Graphic for easy ordering</div>
    <div class="f2">
      <div class="fg"><label>First Name *</label><input class="fi" id="r-fn" placeholder="First"></div>
      <div class="fg"><label>Last Name *</label><input class="fi" id="r-ln" placeholder="Last"></div>
    </div>
    <div class="fg"><label>Email *</label><input type="email" class="fi" id="r-em" placeholder="email@example.com"></div>
    <div class="fg"><label>Phone *</label><input type="tel" class="fi" id="r-ph" placeholder="+91 98765 43210"></div>
    <div class="fg"><label>Address Line 1</label><input class="fi" id="r-add1" placeholder="House / Building / Street"></div>
    <div class="fg"><label>Address Line 2</label><input class="fi" id="r-add2" placeholder="Area / Landmark"></div>
    <div class="f2">
      <div class="fg"><label>City</label><input class="fi" id="r-city" placeholder="Rajkot"></div>
      <div class="fg"><label>State</label><input class="fi" id="r-state" placeholder="Gujarat"></div>
    </div>
    <div class="fg"><label>Pincode</label><input class="fi" id="r-pin" placeholder="360001"></div>
    <div class="fg"><label>Company (optional)</label><input class="fi" id="r-co" placeholder="Business Name"></div>
    <div class="fg"><label>Password * (min 6 chars)</label><input type="password" class="fi" id="r-pw" placeholder="Create password"></div>
    <div style="display:flex;align-items:flex-start;gap:8px;margin-bottom:14px;font-size:13px;color:var(--text2)">
      <input type="checkbox" id="r-consent" style="margin-top:2px;accent-color:var(--blue)">
      <label for="r-consent">I'd like to receive offers and updates via email/WhatsApp</label>
    </div>
    <div id="r-err" style="font-size:12px;color:var(--red);text-align:center;margin-bottom:10px;display:none"></div>
    <button class="btn btn-blue btn-full" onclick="doRegister()" style="padding:13px;border-radius:10px;font-size:15px">Create Account →</button>
    <div class="auth-sw">Already registered? <a href="/login">Sign in</a></div>
    <div class="auth-sw" style="margin-top:5px"><a href="/" style="color:var(--text3)">← Back to Home</a></div>
  </div>
</div>
<script>
const CSRF = '<?= $csrf ?>';
async function doRegister() {
  const fn = document.getElementById('r-fn').value.trim();
  const ln = document.getElementById('r-ln').value.trim();
  const em = document.getElementById('r-em').value.trim();
  const ph = document.getElementById('r-ph').value.trim();
  const pw = document.getElementById('r-pw').value;
  const co = document.getElementById('r-co').value.trim();
  const add1 = document.getElementById('r-add1').value.trim();
  const add2 = document.getElementById('r-add2').value.trim();
  const city = document.getElementById('r-city').value.trim();
  const state = document.getElementById('r-state').value.trim();
  const pin = document.getElementById('r-pin').value.trim();
  const consent = document.getElementById('r-consent').checked;
  if (!fn || !em || !ph || !pw) { showErr('Fill all required fields'); return; }
  const btn = document.querySelector('[onclick="doRegister()"]');
  btn.disabled = true; btn.textContent = 'Creating…';
  const resp = await fetch('/api/auth/register', {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      name: fn + ' ' + ln,
      email: em,
      phone: ph,
      password: pw,
      company: co,
      marketing_consent: consent ? 1 : 0,
      shipping: {
        address_line1: add1,
        address_line2: add2,
        city,
        state,
        pincode: pin,
      },
    })
  });
  const data = await resp.json();
  btn.disabled = false; btn.textContent = 'Create Account →';
  if (data.ok) { window.location.href = '/'; }
  else { showErr(data.msg || 'Registration failed'); }
}
function showErr(msg) {
  const e = document.getElementById('r-err');
  e.textContent = msg; e.style.display = 'block';
}
</script>
</body></html>
