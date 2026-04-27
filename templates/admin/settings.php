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
    <div class="fsec-t">📲 Fast2SMS (Order Confirmation SMS)</div>
    <div class="f2">
      <div class="fg">
        <label>Enable Fast2SMS</label>
        <?php $f2 = strtolower((string)($settingsMap['fast2sms_enabled'] ?? '0')); ?>
        <select name="fast2sms_enabled" class="fi fi-sel">
          <option value="1" <?= in_array($f2, ['1','true','yes','on'], true) ? 'selected' : '' ?>>Enabled</option>
          <option value="0" <?= !in_array($f2, ['1','true','yes','on'], true) ? 'selected' : '' ?>>Disabled</option>
        </select>
      </div>
      <div class="fg"><label>Fast2SMS API Key</label><input type="password" name="fast2sms_api_key" class="fi" value="<?= htmlspecialchars($settingsMap['fast2sms_api_key'] ?? '') ?>" placeholder="Enter Fast2SMS API key"></div>
    </div>
    <div class="fg">
      <label>Order Confirmation SMS Template</label>
      <textarea name="fast2sms_order_confirmation_template" class="fi" style="height:78px" placeholder="Hi {name}, your order {order_id} is confirmed. Amount: Rs {amount}."><?= htmlspecialchars($settingsMap['fast2sms_order_confirmation_template'] ?? 'Hi {name}, your order {order_id} is confirmed. Amount: Rs {amount}. Thank you for choosing RCS Graphic.') ?></textarea>
      <div style="font-size:11px;color:var(--text3);margin-top:6px">
        Available placeholders: <code>{name}</code>, <code>{order_id}</code>, <code>{amount}</code>
      </div>
    </div>
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
    </div></div></div>
</body></html>
