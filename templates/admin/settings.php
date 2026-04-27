<?php
$pageTitle = 'Settings — RCS Admin';
$currentAdmPage = 'settings';
include __DIR__ . '/layout.php';
$saved = isset($_GET['saved']) && $_GET['saved'] === '1';
?>
<?php if ($saved): ?>
<div style="background:var(--green-bg);border:1px solid var(--green-mid);border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:var(--green);font-weight:600">
  ✅ Settings saved successfully!
</div>
<?php endif; ?>
<div class="adm-pt">Settings</div>
<form method="POST" action="/admin/settings/save">
  <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">

  <div class="fsec">
    <div class="fsec-t">🏢 Business Info</div>
    <div class="f2">
      <div class="fg"><label>Business Name</label><input name="biz_name" class="fi" value="<?= htmlspecialchars($settingsMap['biz_name'] ?? 'RCS Graphic') ?>"></div>
      <div class="fg"><label>Tagline</label><input name="biz_tagline" class="fi" value="<?= htmlspecialchars($settingsMap['biz_tagline'] ?? '') ?>"></div>
    </div>
    <div class="f2">
      <div class="fg"><label>Phone</label><input name="biz_phone" class="fi" value="<?= htmlspecialchars($settingsMap['biz_phone'] ?? '') ?>"></div>
      <div class="fg"><label>WhatsApp (country code, no +)</label><input name="biz_whatsapp" class="fi" value="<?= htmlspecialchars($settingsMap['biz_whatsapp'] ?? '') ?>" placeholder="919876543210"></div>
    </div>
    <div class="f2">
      <div class="fg"><label>Email</label><input name="biz_email" class="fi" value="<?= htmlspecialchars($settingsMap['biz_email'] ?? '') ?>"></div>
      <div class="fg"><label>GSTIN</label><input name="biz_gst_no" class="fi" value="<?= htmlspecialchars($settingsMap['biz_gst_no'] ?? '') ?>" placeholder="24XXXXX0000X1ZX"></div>
    </div>
    <div class="fg"><label>Address</label><input name="biz_address" class="fi" value="<?= htmlspecialchars($settingsMap['biz_address'] ?? '') ?>"></div>
    <div class="f2">
      <div class="fg"><label>GST Rate (%)</label><input type="number" name="gst_percent" class="fi" value="<?= htmlspecialchars($settingsMap['gst_percent'] ?? '18') ?>" style="max-width:120px"></div>
      <div class="fg"><label>Default Design Fee (₹)</label><input type="number" name="design_fee" class="fi" value="<?= htmlspecialchars($settingsMap['design_fee'] ?? '0') ?>" style="max-width:160px" min="0"></div>
    </div>
  </div>

  <div class="fsec">
    <div class="fsec-t">🚚 Shipping Settings</div>
    <div class="f2">
      <div class="fg">
        <label>Shipping Mode</label>
        <select name="shipping_mode" class="fi fi-sel">
          <?php $sm = $settingsMap['shipping_mode'] ?? 'flat'; ?>
          <option value="free" <?= $sm === 'free' ? 'selected' : '' ?>>Free Shipping</option>
          <option value="flat" <?= $sm === 'flat' ? 'selected' : '' ?>>Flat Shipping</option>
          <option value="threshold" <?= $sm === 'threshold' ? 'selected' : '' ?>>Free Above Order Amount</option>
        </select>
      </div>
      <div class="fg"><label>Flat Shipping Fee (₹)</label><input type="number" name="shipping_flat_fee" class="fi" value="<?= htmlspecialchars($settingsMap['shipping_flat_fee'] ?? '0') ?>" min="0"></div>
    </div>
    <div class="f2">
      <div class="fg"><label>Free Shipping Above (₹)</label><input type="number" name="shipping_free_above" class="fi" value="<?= htmlspecialchars($settingsMap['shipping_free_above'] ?? '0') ?>" min="0"></div>
      <div class="fg"><label>Shipping Label / Note</label><input name="shipping_note" class="fi" value="<?= htmlspecialchars($settingsMap['shipping_note'] ?? 'Delivery in 2-4 days') ?>"></div>
    </div>
  </div>

  <div class="fsec">
    <div class="fsec-t">💳 Razorpay Payment Gateway</div>
    <div class="fg"><label>Razorpay Key ID</label><input name="razorpay_key_id" class="fi" value="<?= htmlspecialchars($settingsMap['razorpay_key_id'] ?? '') ?>" placeholder="rzp_live_XXXXXXXXXX"></div>
    <div class="fg"><label>Razorpay Key Secret</label><input type="password" name="razorpay_key_secret" class="fi" value="<?= htmlspecialchars($settingsMap['razorpay_key_secret'] ?? '') ?>" placeholder="Your secret key"></div>
  </div>

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
          <option value="smtp" <?= $provider === 'smtp' ? 'selected' : '' ?>>SMTP (PHP mail fallback)</option>
          <option value="log" <?= $provider === 'log' ? 'selected' : '' ?>>Log only (no send)</option>
        </select>
      </div>
    </div>
    <div class="f2">
      <div class="fg"><label>From Name</label><input name="smtp_from_name" class="fi" value="<?= htmlspecialchars($settingsMap['smtp_from_name'] ?? '') ?>" placeholder="RCS Graphic"></div>
      <div class="fg"><label>From Email</label><input name="smtp_from_email" class="fi" value="<?= htmlspecialchars($settingsMap['smtp_from_email'] ?? '') ?>" placeholder="noreply@yourdomain.com"></div>
    </div>
    <div class="f2">
      <div class="fg"><label>Brevo API Key</label><input type="password" name="brevo_api_key" class="fi" value="<?= htmlspecialchars($settingsMap['brevo_api_key'] ?? '') ?>" placeholder="xkeysib-..."></div>
      <div class="fg"><label>Brevo List ID (marketing)</label><input name="brevo_list_id" class="fi" value="<?= htmlspecialchars($settingsMap['brevo_list_id'] ?? '') ?>" placeholder="1"></div>
    </div>
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
    <div style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap;margin-top:8px">
      <div class="fg" style="margin:0;min-width:260px;flex:1">
        <label>Test Email</label>
        <input id="test-email-to" class="fi" placeholder="you@example.com" value="<?= htmlspecialchars($settingsMap['smtp_from_email'] ?? '') ?>">
      </div>
      <button type="button" class="btn btn-outline btn-sm" onclick="sendTestEmail()">Send Test Email</button>
    </div>
    <div id="test-email-msg" style="display:none;font-size:12px;margin-top:8px"></div>
  </div>

  <div class="fsec">
    <div class="fsec-t">📁 File Upload Settings</div>
    <div class="f2">
      <div class="fg"><label>Max Upload Size (MB)</label><input type="number" name="upload_max_mb" class="fi" value="<?= htmlspecialchars($settingsMap['upload_max_mb'] ?? '50') ?>"></div>
      <div class="fg"><label>Allowed Extensions</label><input name="upload_allowed_ext" class="fi" value="<?= htmlspecialchars($settingsMap['upload_allowed_ext'] ?? 'pdf,ai,eps,png,jpg,jpeg,psd,cdr') ?>"></div>
    </div>
  </div>

  <div class="fsec">
    <div class="fsec-t">🔐 Admin Security</div>
    <div class="fg"><label>New Admin Password (leave blank to keep current)</label><input type="password" name="new_admin_password" class="fi" placeholder="Enter new password (min 6 chars)"></div>
  </div>

  <button type="submit" class="btn btn-blue" style="padding:13px 28px;border-radius:10px">Save All Settings ✓</button>
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
