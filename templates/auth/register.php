<?php
$pageTitle = 'Create Account — RCS Graphic';
$pageDesc = 'Create your RCS Graphic account to track orders, save addresses and reorder print products faster.';
include INCLUDE_PATH . '/partials/head.php';
include INCLUDE_PATH . '/partials/header.php';

$settingsMap = [];
try {
    $settings = Database::rows("SELECT `key`, value FROM settings");
    $settingsMap = array_column($settings, 'value', 'key');
} catch (\Throwable) {}

$supportPhone = preg_replace('/\D+/', '', (string)($settingsMap['biz_whatsapp'] ?? $settingsMap['biz_phone'] ?? '919876543210'));
$supportPhoneDisplay = trim((string)($settingsMap['biz_phone'] ?? $settingsMap['biz_whatsapp'] ?? '+91 98765 43210'));
if ($supportPhoneDisplay === '') {
  $supportPhoneDisplay = '+91 98765 43210';
}
?>
<main class="register-page">
  <section class="register-hero">
    <div class="register-container register-hero-grid">
      <div class="register-hero-copy">
        <h1>Create Your Account</h1>
        <p>Join <strong>RCS PRINT</strong> and start printing excellence today.</p>
      </div>
      <div class="register-hero-art" aria-hidden="true">
        <img class="reg-art reg-art-main" src="/assets/images/sample-products/business-cards/business-cards-1.svg" alt="" loading="eager" decoding="async">
        <img class="reg-art reg-art-bag" src="/assets/images/sample-products/brochures/brochures-1.svg" alt="" loading="lazy" decoding="async">
        <img class="reg-art reg-art-card" src="/assets/images/sample-products/flyers/flyers-1.svg" alt="" loading="lazy" decoding="async">
        <span class="reg-art-dot reg-art-dot-one"></span>
        <span class="reg-art-dot reg-art-dot-two"></span>
      </div>
    </div>
  </section>

  <section class="register-main-section">
    <div class="register-container register-layout">
      <article class="register-form-card">
        <div class="register-form-head">
          <h2>Sign Up</h2>
          <p>Already have an account? <a href="/login">Login</a></p>
        </div>

        <div class="register-form-grid register-name-grid">
          <label class="register-field">First Name *
            <span class="register-input-wrap"><i class="fa-regular fa-user" aria-hidden="true"></i><input id="r-fn" type="text" autocomplete="given-name" placeholder="Enter first name"></span>
          </label>
          <label class="register-field">Last Name *
            <span class="register-input-wrap"><i class="fa-regular fa-user" aria-hidden="true"></i><input id="r-ln" type="text" autocomplete="family-name" placeholder="Enter last name"></span>
          </label>
        </div>

        <label class="register-field">Email Address *
          <span class="register-input-wrap"><i class="fa-regular fa-envelope" aria-hidden="true"></i><input id="r-em" type="email" autocomplete="email" placeholder="Enter your email address"></span>
        </label>

        <label class="register-field">WhatsApp Number *
          <span class="register-phone-wrap">
            <span class="register-country"><span aria-hidden="true">🇮🇳</span><strong>+91</strong></span>
            <span class="register-input-wrap"><i class="fa-solid fa-phone" aria-hidden="true"></i><input id="r-ph" type="tel" autocomplete="tel" placeholder="Enter your mobile number"></span>
          </span>
        </label>

        <label class="register-field">City *
          <span class="register-input-wrap"><i class="fa-solid fa-location-dot" aria-hidden="true"></i><input id="r-city" type="text" autocomplete="address-level2" placeholder="Rajkot"></span>
        </label>

        <label class="register-field">Password *
          <span class="register-input-wrap"><i class="fa-solid fa-lock" aria-hidden="true"></i><input id="r-pw" type="password" autocomplete="new-password" placeholder="Create a password"><button type="button" onclick="togglePass('r-pw', this)" aria-label="Show password"><i class="fa-regular fa-eye" aria-hidden="true"></i></button></span>
          <small>At least 6 characters with letters and numbers.</small>
        </label>

        <label class="register-field">Retype Password *
          <span class="register-input-wrap"><i class="fa-solid fa-lock" aria-hidden="true"></i><input id="r-pw2" type="password" autocomplete="new-password" placeholder="Retype your password"><button type="button" onclick="togglePass('r-pw2', this)" aria-label="Show password"><i class="fa-regular fa-eye" aria-hidden="true"></i></button></span>
        </label>

        <label class="register-agree"><input id="r-agree" type="checkbox"> <span>I agree to the <a href="/terms-and-conditions">Terms &amp; Conditions</a> and <a href="/privacy-policy">Privacy Policy</a></span></label>

        <div id="r-err" class="register-error" role="alert"></div>
        <button id="r-submit" class="register-submit" type="button" onclick="doRegister()">Create Account</button>

        <div class="register-benefit-strip">
          <span class="register-benefit-icon"><i class="fa-solid fa-stopwatch" aria-hidden="true"></i></span>
          <div>
            <strong>By creating an account, you will be able to:</strong>
            <ul>
              <li><i class="fa-solid fa-check" aria-hidden="true"></i>Track your orders</li>
              <li><i class="fa-solid fa-check" aria-hidden="true"></i>Save your addresses</li>
              <li><i class="fa-solid fa-check" aria-hidden="true"></i>Get exclusive offers</li>
              <li><i class="fa-solid fa-check" aria-hidden="true"></i>Reorder quickly</li>
            </ul>
          </div>
        </div>
      </article>

      <aside class="register-side-panel" aria-label="Account benefits">
        <div class="register-why-card">
          <h2>Why Create an <span>Account?</span></h2>
          <div class="register-why-list">
            <div class="register-why-item"><span class="register-why-icon purple"><i class="fa-solid fa-cube" aria-hidden="true"></i></span><div><h3>Easy Order Tracking</h3><p>Track your orders in real-time from start to finish.</p></div></div>
            <div class="register-why-item"><span class="register-why-icon orange"><i class="fa-solid fa-wallet" aria-hidden="true"></i></span><div><h3>Faster Checkout</h3><p>Save your details and checkout in just a click.</p></div></div>
            <div class="register-why-item"><span class="register-why-icon green"><i class="fa-solid fa-tag" aria-hidden="true"></i></span><div><h3>Exclusive Offers</h3><p>Get access to exclusive deals and member-only discounts.</p></div></div>
            <div class="register-why-item"><span class="register-why-icon purple"><i class="fa-regular fa-heart" aria-hidden="true"></i></span><div><h3>Wishlist &amp; Save</h3><p>Save your favorite products and order when ready.</p></div></div>
            <div class="register-why-item"><span class="register-why-icon orange"><i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i></span><div><h3>Order History</h3><p>View and reorder your previous orders easily.</p></div></div>
          </div>
        </div>

        <div class="register-help-card">
          <span><i class="fa-solid fa-phone-volume" aria-hidden="true"></i></span>
          <div>
            <h3>Need Help?</h3>
            <p>We're here to assist you.</p>
            <a href="tel:+<?= htmlspecialchars($supportPhone) ?>"><?= htmlspecialchars($supportPhoneDisplay) ?></a>
            <small>Mon - Sat: 10:00 AM - 7:00 PM</small>
          </div>
        </div>
      </aside>
    </div>
  </section>

  <section class="register-trust-section" aria-label="RCS benefits">
    <div class="register-container register-trust-grid">
      <div class="register-trust-item"><span class="green"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></span><div><strong>Premium Quality</strong><p>Best quality materials and printing.</p></div></div>
      <div class="register-trust-item"><span class="orange"><i class="fa-regular fa-thumbs-up" aria-hidden="true"></i></span><div><strong>100% Satisfaction</strong><p>Your happiness matters.</p></div></div>
      <div class="register-trust-item"><span class="purple"><i class="fa-solid fa-pen-ruler" aria-hidden="true"></i></span><div><strong>Free Design Support</strong><p>Professional design support at no extra cost.</p></div></div>
      <div class="register-trust-item"><span class="purple"><i class="fa-solid fa-tags" aria-hidden="true"></i></span><div><strong>Affordable Pricing</strong><p>Low price with the best value.</p></div></div>
      <div class="register-trust-item"><span class="orange"><i class="fa-solid fa-cube" aria-hidden="true"></i></span><div><strong>Bulk Order Specialist</strong><p>Special prices for bulk requirements.</p></div></div>
    </div>
  </section>
</main>
<script>
const CSRF = '<?= $csrf ?>';
async function doRegister() {
  const fn = document.getElementById('r-fn').value.trim();
  const ln = document.getElementById('r-ln').value.trim();
  const em = document.getElementById('r-em').value.trim();
  const ph = document.getElementById('r-ph').value.trim();
  const pw = document.getElementById('r-pw').value;
  const pw2 = document.getElementById('r-pw2').value;
  const city = document.getElementById('r-city').value.trim();
  const agree = document.getElementById('r-agree');
  if (!fn || !ln || !em || !ph || !city || !pw || !pw2) { showErr('Fill all required fields'); return; }
  if (pw !== pw2) { showErr('Password and retype password must match'); return; }
  if (agree && !agree.checked) { showErr('Please accept Terms & Conditions and Privacy Policy'); return; }
  const btn = document.getElementById('r-submit');
  btn.disabled = true; btn.textContent = 'Creating…';
  const resp = await fetch('/api/auth/register', {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      name: fn + ' ' + ln,
      email: em,
      phone: ph,
      password: pw,
      shipping: {
        city,
      },
    })
  });
  const data = await resp.json();
  btn.disabled = false; btn.textContent = 'Create Account';
  if (data.ok) { window.location.href = '/'; }
  else { showErr(data.msg || 'Registration failed'); }
}
function showErr(msg) {
  const e = document.getElementById('r-err');
  e.textContent = msg; e.style.display = 'block';
}

function togglePass(inputId, btn) {
  const inp = document.getElementById(inputId);
  if (!inp) return;
  const show = inp.type === 'password';
  inp.type = show ? 'text' : 'password';
  btn.innerHTML = show ? '<i class="fa-regular fa-eye-slash" aria-hidden="true"></i>' : '<i class="fa-regular fa-eye" aria-hidden="true"></i>';
  btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
}
</script>
<?php include INCLUDE_PATH . '/partials/site-footer.php'; ?>
<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
