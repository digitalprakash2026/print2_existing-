<?php
$pageTitle = 'Login — RCS Graphic';
$pageDesc = 'Sign in to your RCS Graphic account to track orders, reorder products and manage saved details.';
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
<main class="login-page register-page">
  <section class="register-hero login-hero">
    <div class="register-container register-hero-grid login-hero-grid">
      <div class="register-hero-copy">
        <h1>Welcome Back</h1>
        <p>Sign in to <strong>RCS PRINT</strong> and continue managing your print orders.</p>
      </div>
      <div class="register-hero-art login-hero-art" aria-hidden="true">
        <img class="reg-art reg-art-main" src="/assets/images/sample-products/business-cards/business-cards-2.svg" alt="" loading="eager" decoding="async">
        <img class="reg-art reg-art-bag" src="/assets/images/sample-products/stationery/stationery-1.svg" alt="" loading="lazy" decoding="async">
        <img class="reg-art reg-art-card" src="/assets/images/sample-products/brochures/brochures-2.svg" alt="" loading="lazy" decoding="async">
        <span class="reg-art-dot reg-art-dot-one"></span>
        <span class="reg-art-dot reg-art-dot-two"></span>
      </div>
    </div>
  </section>

  <section class="register-main-section login-main-section">
    <div class="register-container register-layout login-layout">
      <article class="register-form-card login-form-card">
        <div class="register-form-head">
          <h2>Sign In</h2>
          <p>New customer? <a href="/register">Create Account</a></p>
        </div>

        <?php if (!empty($error)): ?>
          <div class="register-error login-server-error" style="display:block"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <label class="register-field">Email or Phone
          <span class="register-input-wrap"><i class="fa-regular fa-user" aria-hidden="true"></i><input class="login-input" id="l-id" autocomplete="username" placeholder="email@example.com or 9876543210"></span>
        </label>

        <label class="register-field">Password
          <span class="register-input-wrap"><i class="fa-solid fa-lock" aria-hidden="true"></i><input type="password" class="login-input" id="l-pw" autocomplete="current-password" placeholder="Your password" onkeyup="if(event.key==='Enter')doLogin()"><button type="button" onclick="toggleLoginPass('l-pw', this)" aria-label="Show password"><i class="fa-regular fa-eye" aria-hidden="true"></i></button></span>
        </label>

        <div class="login-form-row">
          <label class="login-remember"><input type="checkbox" id="l-remember"> <span>Remember me</span></label>
          <a href="/#quick-help-sec">Need help?</a>
        </div>

        <div id="l-err" class="register-error" role="alert"></div>
        <button id="l-submit" class="register-submit login-submit" type="button" onclick="doLogin()">Sign In</button>

        <div class="register-benefit-strip login-benefit-strip">
          <span class="register-benefit-icon"><i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i></span>
          <div>
            <strong>After login, you can quickly:</strong>
            <ul>
              <li><i class="fa-solid fa-check" aria-hidden="true"></i>Track current orders</li>
              <li><i class="fa-solid fa-check" aria-hidden="true"></i>Reorder saved products</li>
              <li><i class="fa-solid fa-check" aria-hidden="true"></i>Manage addresses</li>
              <li><i class="fa-solid fa-check" aria-hidden="true"></i>Submit product reviews</li>
            </ul>
          </div>
        </div>
      </article>

      <aside class="register-side-panel login-side-panel" aria-label="Account benefits">
        <div class="register-why-card login-why-card">
          <h2>Your Account <span>Dashboard</span></h2>
          <div class="register-why-list">
            <div class="register-why-item"><span class="register-why-icon purple"><i class="fa-solid fa-box-open" aria-hidden="true"></i></span><div><h3>Order Tracking</h3><p>Check production, dispatch and delivery updates in one place.</p></div></div>
            <div class="register-why-item"><span class="register-why-icon orange"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i></span><div><h3>Reorder Faster</h3><p>Repeat previous print orders without filling details again.</p></div></div>
            <div class="register-why-item"><span class="register-why-icon green"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></span><div><h3>Saved Details</h3><p>Keep profile and address details ready for quicker checkout.</p></div></div>
            <div class="register-why-item"><span class="register-why-icon purple"><i class="fa-solid fa-star" aria-hidden="true"></i></span><div><h3>Review Products</h3><p>Share feedback for delivered products from your account.</p></div></div>
          </div>
        </div>

        <div class="register-help-card login-help-card">
          <span><i class="fa-solid fa-phone-volume" aria-hidden="true"></i></span>
          <div>
            <h3>Need Login Help?</h3>
            <p>Our team can help with account access.</p>
            <a href="tel:+<?= htmlspecialchars($supportPhone) ?>"><?= htmlspecialchars($supportPhoneDisplay) ?></a>
            <small>Mon - Sat: 10:00 AM - 7:00 PM</small>
          </div>
        </div>
      </aside>
    </div>
  </section>

  <section class="register-trust-section login-trust-section" aria-label="RCS benefits">
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
const LOGIN_NEXT = new URLSearchParams(window.location.search).get('next') || '/';
async function doLogin() {
  const id = document.getElementById('l-id').value.trim();
  const pw = document.getElementById('l-pw').value;
  if (!id || !pw) { showErr('Fill all fields'); return; }
  const btn = document.getElementById('l-submit');
  btn.disabled = true; btn.textContent = 'Signing in…';
  const resp = await fetch('/api/auth/login', {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ identifier: id, password: pw })
  });
  const data = await resp.json();
  btn.disabled = false; btn.textContent = 'Sign In';
  if (data.ok) { window.location.href = LOGIN_NEXT; }
  else showErr(data.msg || 'Invalid credentials');
}
function showErr(msg) {
  const e = document.getElementById('l-err');
  e.textContent = msg; e.style.display = 'block';
}
function toggleLoginPass(inputId, btn) {
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
