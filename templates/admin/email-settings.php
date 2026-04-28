<?php
$pageTitle = 'Email Setup — RCS Admin';
$currentAdmPage = 'email-settings';
include __DIR__ . '/layout.php';
$settingsMap = $settingsMap ?? [];
?>
<div class="adm-pt">Email Setup</div>
<p style="font-size:13px;color:var(--text2);margin-bottom:16px">Configure transactional emails (order confirmation, welcome, status updates) from one dedicated page.</p>

<form method="POST" action="/admin/settings/save">
  <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">

  <div class="fsec">
    <div class="fsec-t">📧 Email Communication Setup</div>
    <div class="f2">
      <div class="fg">
        <label>Email Enabled</label>
        <?php $emailEnabled = strtolower((string)($settingsMap['email_enabled'] ?? '1')); ?>
        <select name="email_enabled" class="fi fi-sel">
          <option value="1" <?= in_array($emailEnabled, ['1','true','yes','on'], true) ? 'selected' : '' ?>>Enabled</option>
          <option value="0" <?= !in_array($emailEnabled, ['1','true','yes','on'], true) ? 'selected' : '' ?>>Disabled</option>
        </select>
      </div>
      <div class="fg">
        <label>Email Provider</label>
        <?php $provider = strtolower((string)($settingsMap['email_provider'] ?? 'auto')); ?>
        <select name="email_provider" class="fi fi-sel">
          <option value="auto" <?= $provider === 'auto' ? 'selected' : '' ?>>Auto (Brevo → SMTP → Log)</option>
          <option value="brevo" <?= $provider === 'brevo' ? 'selected' : '' ?>>Brevo API</option>
          <option value="smtp" <?= $provider === 'smtp' ? 'selected' : '' ?>>SMTP Auth</option>
          <option value="log" <?= $provider === 'log' ? 'selected' : '' ?>>Log only (no send)</option>
        </select>
      </div>
    </div>
    <div class="f2">
      <div class="fg"><label>From Name</label><input name="smtp_from_name" class="fi" value="<?= htmlspecialchars($settingsMap['smtp_from_name'] ?? '') ?>" placeholder="RCS Graphic"></div>
      <div class="fg"><label>From Email</label><input name="smtp_from_email" class="fi" value="<?= htmlspecialchars($settingsMap['smtp_from_email'] ?? '') ?>" placeholder="noreply@yourdomain.com"></div>
    </div>
  </div>

  <div class="fsec">
    <div class="fsec-t">🟣 Brevo API (Optional)</div>
    <div class="f2">
      <div class="fg"><label>Brevo API Key</label><input type="password" name="brevo_api_key" class="fi" value="<?= htmlspecialchars($settingsMap['brevo_api_key'] ?? '') ?>" placeholder="xkeysib-..."></div>
      <div class="fg"><label>Brevo List ID (marketing)</label><input name="brevo_list_id" class="fi" value="<?= htmlspecialchars($settingsMap['brevo_list_id'] ?? '') ?>" placeholder="1"></div>
    </div>
  </div>

  <div class="fsec">
    <div class="fsec-t">📮 SMTP Auth Setup</div>
    <div class="f2">
      <div class="fg"><label>SMTP Host</label><input name="smtp_host" class="fi" value="<?= htmlspecialchars($settingsMap['smtp_host'] ?? '') ?>" placeholder="smtp.host.com"></div>
      <div class="fg"><label>SMTP Port</label><input name="smtp_port" class="fi" value="<?= htmlspecialchars($settingsMap['smtp_port'] ?? '587') ?>" placeholder="587"></div>
    </div>
    <div class="f2">
      <?php $smtpSecure = strtolower((string)($settingsMap['smtp_secure'] ?? 'tls')); ?>
      <div class="fg">
        <label>SMTP Security</label>
        <select name="smtp_secure" class="fi fi-sel">
          <option value="tls" <?= $smtpSecure === 'tls' ? 'selected' : '' ?>>TLS (STARTTLS)</option>
          <option value="ssl" <?= $smtpSecure === 'ssl' ? 'selected' : '' ?>>SSL</option>
          <option value="none" <?= $smtpSecure === 'none' ? 'selected' : '' ?>>None</option>
        </select>
      </div>
      <div class="fg"><label>SMTP Username</label><input name="smtp_user" class="fi" value="<?= htmlspecialchars($settingsMap['smtp_user'] ?? '') ?>" placeholder="username"></div>
    </div>
    <div class="fg"><label>SMTP Password</label><input type="password" name="smtp_pass" class="fi" value="<?= htmlspecialchars($settingsMap['smtp_pass'] ?? '') ?>" placeholder="password"></div>
  </div>

  <div class="fsec">
    <div class="fsec-t">🧪 Test Email</div>
    <div style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap">
      <div class="fg" style="margin:0;min-width:260px;flex:1">
        <label>Send test email to</label>
        <input id="test-email-to" class="fi" placeholder="you@example.com" value="<?= htmlspecialchars($settingsMap['smtp_from_email'] ?? '') ?>">
      </div>
      <button type="button" class="btn btn-outline btn-sm" onclick="sendTestEmail()">Send Test Email</button>
    </div>
    <div id="test-email-msg" style="display:none;font-size:12px;margin-top:8px"></div>
  </div>

  <button type="submit" class="btn btn-blue" style="padding:13px 28px;border-radius:10px">Save Email Settings ✓</button>
</form>

<script>
async function sendTestEmail() {
  const email = document.getElementById('test-email-to')?.value.trim() || '';
  const msg = document.getElementById('test-email-msg');
  if (!email) {
    msg.style.display = 'block';
    msg.style.color = 'var(--red)';
    msg.textContent = 'Please enter test email address.';
    return;
  }
  msg.style.display = 'block';
  msg.style.color = 'var(--text2)';
  msg.textContent = 'Sending test email...';
  const res = await fetch('/admin/api/email/test', {
    method: 'POST',
    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'<?= htmlspecialchars($csrf ?? '') ?>'},
    body: JSON.stringify({email})
  }).then(r=>r.json()).catch(()=>({ok:false,msg:'Request failed'}));
  msg.style.color = res.ok ? 'var(--green)' : 'var(--red)';
  msg.textContent = res.ok ? 'Test email sent successfully.' : (res.msg || 'Could not send test email.');
}
</script>
    </div></div></div>
</body></html>
