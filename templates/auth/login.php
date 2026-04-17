<?php $pageTitle = 'Login — RCS Graphic'; include INCLUDE_PATH . '/partials/head.php'; include INCLUDE_PATH . '/partials/header.php'; ?>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-mark">R</div>
    <div class="auth-t">Welcome Back</div>
    <div class="auth-s">Sign in to your RCS Graphic account</div>
    <?php if (!empty($error)): ?>
    <div style="color:var(--red);font-size:12px;text-align:center;margin-bottom:10px"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <div class="fg"><label>Email or Phone</label><input class="fi" id="l-id" placeholder="email@example.com or 9876543210"></div>
    <div class="fg"><label>Password</label><input type="password" class="fi" id="l-pw" placeholder="Your password" onkeyup="if(event.key==='Enter')doLogin()"></div>
    <div id="l-err" style="font-size:12px;color:var(--red);text-align:center;margin-bottom:10px;display:none"></div>
    <button class="btn btn-blue btn-full" onclick="doLogin()" style="padding:13px;border-radius:10px;font-size:15px">Sign In →</button>
    <div class="auth-sw">No account? <a href="/register">Register free</a></div>
    <div class="auth-sw" style="margin-top:6px"><a href="/" style="color:var(--text3)">← Back to Home</a></div>
  </div>
</div>
<script>
const CSRF = '<?= $csrf ?>';
async function doLogin() {
  const id = document.getElementById('l-id').value.trim();
  const pw = document.getElementById('l-pw').value;
  if (!id || !pw) { showErr('Fill all fields'); return; }
  const btn = document.querySelector('[onclick="doLogin()"]');
  btn.disabled = true; btn.textContent = 'Signing in…';
  const resp = await fetch('/api/auth/login', {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ identifier: id, password: pw })
  });
  const data = await resp.json();
  btn.disabled = false; btn.textContent = 'Sign In →';
  if (data.ok) { window.location.href = '/'; }
  else showErr(data.msg || 'Invalid credentials');
}
function showErr(msg) {
  const e = document.getElementById('l-err');
  e.textContent = msg; e.style.display = 'block';
}
</script>
</body></html>
