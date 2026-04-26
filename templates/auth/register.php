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
    <div class="fg"><label>WhatsApp Number *</label><input type="tel" class="fi" id="r-ph" placeholder="+91 98765 43210"></div>
    <div class="fg"><label>City *</label><input class="fi" id="r-city" placeholder="Rajkot"></div>
    <div class="fg">
      <label>Password * (min 6 chars)</label>
      <div style="position:relative">
        <input type="password" class="fi" id="r-pw" placeholder="Create password" style="padding-right:44px">
        <button type="button" onclick="togglePass('r-pw', this)" aria-label="Show password" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);border:none;background:transparent;color:var(--text2);cursor:pointer;font-size:17px;line-height:1">👁️</button>
      </div>
    </div>
    <div class="fg">
      <label>Retype Password *</label>
      <div style="position:relative">
        <input type="password" class="fi" id="r-pw2" placeholder="Retype password" style="padding-right:44px">
        <button type="button" onclick="togglePass('r-pw2', this)" aria-label="Show password" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);border:none;background:transparent;color:var(--text2);cursor:pointer;font-size:17px;line-height:1">👁️</button>
      </div>
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
  const pw2 = document.getElementById('r-pw2').value;
  const city = document.getElementById('r-city').value.trim();
  if (!fn || !ln || !em || !ph || !city || !pw || !pw2) { showErr('Fill all required fields'); return; }
  if (pw !== pw2) { showErr('Password and retype password must match'); return; }
  const btn = document.querySelector('[onclick="doRegister()"]');
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
  btn.disabled = false; btn.textContent = 'Create Account →';
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
  btn.textContent = show ? '🙈' : '👁️';
}
</script>
</body></html>
