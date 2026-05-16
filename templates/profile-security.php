<?php
$pageTitle = 'Password Settings — RCS Graphic';
$currentPage = 'profile';
include INCLUDE_PATH . '/partials/head.php';
include INCLUDE_PATH . '/partials/header.php';
?>
<div style="margin-top:calc(var(--site-hh, var(--hh)) + var(--post-header-gap,50px));min-height:calc(100vh - var(--site-hh, var(--hh)) - var(--post-header-gap,50px));background:var(--bg);padding:32px 0 80px">
  <div class="container" style="max-width:760px">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:16px">
      <div>
        <div style="font-family:var(--fd);font-size:24px;font-weight:700">Password Settings</div>
        <div style="font-size:13px;color:var(--text2)">Update your account password from this dedicated security page.</div>
      </div>
      <a href="/profile" class="btn btn-outline btn-sm">← Back to Profile</a>
    </div>

    <div style="background:var(--white);border-radius:12px;border:1.5px solid var(--border);padding:16px;margin-bottom:16px">
      <div style="font-size:12px;font-weight:700;color:var(--text2);margin-bottom:12px;text-transform:uppercase;letter-spacing:.06em">🔐 Security</div>
      <div style="font-size:12px;color:var(--text2);margin-bottom:12px">
        Existing passwords are stored securely in hashed form and cannot be shown in plain text. You can set a new password below.
      </div>
      <div class="fg">
        <label>Current Password *</label>
        <div style="position:relative">
          <input id="pw-current" type="password" class="fi" style="padding-right:44px" placeholder="Enter current password">
          <button type="button" onclick="togglePassField('pw-current', this)" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);border:none;background:transparent;color:var(--text2);cursor:pointer">👁️</button>
        </div>
      </div>
      <div class="fg">
        <label>New Password *</label>
        <div style="position:relative">
          <input id="pw-new" type="password" class="fi" style="padding-right:44px" placeholder="Minimum 6 characters">
          <button type="button" onclick="togglePassField('pw-new', this)" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);border:none;background:transparent;color:var(--text2);cursor:pointer">👁️</button>
        </div>
      </div>
      <div class="fg" style="margin-bottom:10px">
        <label>Confirm New Password *</label>
        <div style="position:relative">
          <input id="pw-confirm" type="password" class="fi" style="padding-right:44px" placeholder="Retype new password">
          <button type="button" onclick="togglePassField('pw-confirm', this)" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);border:none;background:transparent;color:var(--text2);cursor:pointer">👁️</button>
        </div>
      </div>

      <div id="pwErr" style="display:none;font-size:12px;color:var(--red);margin-bottom:10px"></div>
      <div id="pwOk" style="display:none;font-size:12px;color:var(--green);margin-bottom:10px"></div>
      <button class="btn btn-blue" type="button" onclick="changePassword()">Update Password</button>
    </div>
  </div>
</div>

<script>
async function changePassword() {
  const err = document.getElementById('pwErr');
  const ok = document.getElementById('pwOk');
  err.style.display = 'none';
  ok.style.display = 'none';

  const current_password = document.getElementById('pw-current')?.value || '';
  const new_password = document.getElementById('pw-new')?.value || '';
  const confirm_password = document.getElementById('pw-confirm')?.value || '';

  if (!current_password || !new_password || !confirm_password) {
    err.textContent = 'Please fill all password fields.';
    err.style.display = 'block';
    return;
  }
  if (new_password !== confirm_password) {
    err.textContent = 'New password and confirm password must match.';
    err.style.display = 'block';
    return;
  }

  try {
    const resp = await fetch('/api/profile/password', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': APP.csrfToken },
      credentials: 'same-origin',
      body: JSON.stringify({ current_password, new_password, confirm_password }),
    });
    const data = await resp.json();
    if (!data.ok) {
      err.textContent = data.msg || 'Could not update password.';
      err.style.display = 'block';
      return;
    }
    ['pw-current','pw-new','pw-confirm'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
    ok.textContent = 'Password updated successfully.';
    ok.style.display = 'block';
  } catch (e) {
    err.textContent = 'Could not update password right now.';
    err.style.display = 'block';
  }
}

function togglePassField(id, btn) {
  const el = document.getElementById(id);
  if (!el) return;
  const show = el.type === 'password';
  el.type = show ? 'text' : 'password';
  btn.textContent = show ? '🙈' : '👁️';
}
</script>
<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
