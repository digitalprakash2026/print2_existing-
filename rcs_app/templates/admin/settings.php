<?php
$pageTitle = 'Settings — RCS Admin';
$currentAdmPage = 'settings';
include __DIR__ . '/layout.php';
$saved = isset($_GET['saved']);
?>
<?php if ($saved): ?>
<div style="background:var(--green-bg);border:1px solid var(--green-mid);border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:var(--green);font-weight:600">
  ✅ Settings saved successfully!
</div>
<?php endif; ?>
<div class="adm-pt">Settings</div>
<form method="POST" action="/admin/settings/save">
  <input type="hidden" name="_token" value="<?= $csrf ?>">

  <div class="fsec">
    <div class="fsec-t">🏢 Business Info</div>
    <div class="f2">
      <div class="fg"><label>Business Name</label><input name="biz_name" class="fi" value="<?= htmlspecialchars($settingsMap['biz_name'] ?? 'RCS Graphic') ?>"></div>
      <div class="fg"><label>Tagline</label><input name="biz_tagline" class="fi" value="<?= htmlspecialchars($settingsMap['biz_tagline'] ?? '') ?>"></div>
    </div>
    <div class="f2">
      <div class="fg"><label>Phone</label><input name="biz_phone" class="fi" value="<?= htmlspecialchars($settingsMap['biz_phone'] ?? '') ?>"></div>
      <div class="fg"><label>WhatsApp (with country code, no +)</label><input name="biz_whatsapp" class="fi" value="<?= htmlspecialchars($settingsMap['biz_whatsapp'] ?? '') ?>" placeholder="919876543210"><div class="f-hint">e.g. 919876543210</div></div>
    </div>
    <div class="f2">
      <div class="fg"><label>Email</label><input name="biz_email" class="fi" value="<?= htmlspecialchars($settingsMap['biz_email'] ?? '') ?>"></div>
      <div class="fg"><label>GSTIN</label><input name="biz_gst_no" class="fi" value="<?= htmlspecialchars($settingsMap['biz_gst_no'] ?? '') ?>" placeholder="24XXXXX0000X1ZX"></div>
    </div>
    <div class="fg"><label>Address</label><input name="biz_address" class="fi" value="<?= htmlspecialchars($settingsMap['biz_address'] ?? '') ?>"></div>
    <div class="fg"><label>GST Rate (%)</label><input type="number" name="gst_percent" class="fi" value="<?= htmlspecialchars($settingsMap['gst_percent'] ?? '18') ?>" style="max-width:120px"></div>
    <div class="fg">
      <label>Design Fee (₹) — Charged when customer selects "Design by RCS Graphic"</label>
      <input type="number" name="design_fee" class="fi" value="<?= htmlspecialchars($settingsMap['design_fee'] ?? '0') ?>" placeholder="0" style="max-width:160px" min="0" step="1">
      <div class="f-hint">Set to 0 to not charge a fixed fee (admin will confirm manually). This fee is automatically added to the order total when the customer selects RCS design option on the product page.</div>
    </div>
  </div>

  <div class="fsec">
    <div class="fsec-t">💳 Razorpay Payment Gateway</div>
    <div class="fg"><label>Razorpay Key ID</label><input name="razorpay_key_id" class="fi" value="<?= htmlspecialchars($settingsMap['razorpay_key_id'] ?? '') ?>" placeholder="rzp_live_XXXXXXXXXX or rzp_test_XXXXXXXXXX"></div>
    <div class="fg"><label>Razorpay Key Secret</label><input type="password" name="razorpay_key_secret" class="fi" value="<?= htmlspecialchars($settingsMap['razorpay_key_secret'] ?? '') ?>" placeholder="Your secret key"></div>
    <div style="background:var(--blue-bg);border:1px solid var(--blue-mid);border-radius:9px;padding:11px 14px;font-size:12px;color:var(--blue);line-height:1.6;margin-top:6px">
      💡 <strong>Test mode:</strong> Use <code>rzp_test_</code> key to test payments without real money. Switch to <code>rzp_live_</code> when going live.<br>
      <strong>Important:</strong> Payment signature is verified server-side — never trust client-only payment status.
    </div>
  </div>

  <div class="fsec">
    <div class="fsec-t">📊 Google Sheets Integration</div>
    <div class="fg">
      <label>Apps Script Web App URL</label>
      <input name="sheets_webhook_url" class="fi" value="<?= htmlspecialchars($settingsMap['sheets_webhook_url'] ?? '') ?>" placeholder="https://script.google.com/macros/s/…/exec">
      <div class="f-hint">Every new order and status update will automatically sync to your Google Sheet.</div>
    </div>
    <div style="font-size:12px;font-weight:600;color:<?= !empty($settingsMap['sheets_webhook_url']) ? 'var(--green)' : 'var(--text3)' ?>">
      <?= !empty($settingsMap['sheets_webhook_url']) ? '✅ Connected — orders sync to Google Sheets' : '⚠️ Not configured — orders saved locally only' ?>
    </div>
    <button type="button" onclick="testSheets()" class="btn btn-outline btn-sm" style="margin-top:10px">🧪 Test Connection</button>
    <button type="button" onclick="downloadScript()" class="btn btn-outline btn-sm" style="margin-left:6px">⬇ Download Apps Script</button>
    <div style="margin-top:12px;background:var(--bg2);border-radius:9px;padding:12px 14px;font-size:12px;color:var(--text2);line-height:1.7">
      <strong>Setup:</strong> Open Google Sheets → Extensions → Apps Script → paste the downloaded script → Save → Deploy as Web App (Anyone can access) → Copy URL → paste above.
    </div>
  </div>

  <div class="fsec">
    <div class="fsec-t">📧 Email Configuration (SMTP/Brevo)</div>
    <div class="f-hint" style="margin-bottom:12px">Configure in <code>.env</code> file: SMTP_HOST, SMTP_USER, SMTP_PASS, BREVO_API_KEY</div>
    <div style="background:var(--bg2);border-radius:9px;padding:12px 14px;font-size:12px;color:var(--text2);line-height:1.7">
      Transactional emails (order confirmation, payment success, status updates) are sent via SMTP or Brevo configured in your <code>.env</code> file. Marketing opt-in uses Brevo API.
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

<script>
function toast(msg, type='info') {
  const w = document.getElementById('tw');
  const t = document.createElement('div'); t.className = 'toast ' + type; t.textContent = msg; w.appendChild(t);
  requestAnimationFrame(() => requestAnimationFrame(() => t.classList.add('show')));
  setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 2800);
}

async function testSheets() {
  const url = document.querySelector('[name="sheets_webhook_url"]').value.trim();
  if (!url) { toast('Enter the Apps Script URL first', 'warn'); return; }
  toast('Testing connection…', 'info');
  try {
    await fetch(url, {
      method: 'POST', mode: 'no-cors',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ action: 'addOrder', orderId: 'TEST-' + Date.now(), customerName: 'Connection Test', total: 0, status: 'test' })
    });
    toast('Test ping sent! Check your Google Sheet for a test row.', 'success');
  } catch { toast('Could not reach URL', 'error'); }
}

function downloadScript() {
  const code = `// RCS Graphic Google Apps Script
// Paste in Extensions → Apps Script → Deploy as Web App (Anyone)

function doPost(e) {
  try {
    const data = JSON.parse(e.postData.contents);
    const sheet = SpreadsheetApp.getActiveSpreadsheet()
      .getSheetByName('RCS Orders') || SpreadsheetApp.getActiveSpreadsheet().insertSheet('RCS Orders');
    
    if (sheet.getLastRow() === 0) {
      const headers = ['Order ID','Date','Time','Customer','Phone','Email','Product','Qty','Quality',
        'Options','Design','Brief','Item Price','Coupon','Discount','GST','Total','Payment ID','Status'];
      sheet.appendRow(headers);
      sheet.getRange(1,1,1,headers.length).setFontWeight('bold').setBackground('#1A56E8').setFontColor('#FFFFFF');
    }
    
    if (data.action === 'addOrder') {
      sheet.appendRow([data.orderId,data.date,data.time,data.customerName,data.customerPhone,
        data.customerEmail,data.product,data.quantity,data.quality,data.options,data.designOption,
        data.designBrief,data.itemPrice,data.coupon,data.discount,data.gst,data.total,data.paymentId,data.status]);
    }
    if (data.action === 'updateStatus') {
      const lr = sheet.getLastRow();
      for (let i = 2; i <= lr; i++) {
        if (sheet.getRange(i,1).getValue() === data.orderId) {
          sheet.getRange(i,19).setValue(data.status);
          break;
        }
      }
    }
    return ContentService.createTextOutput(JSON.stringify({success:true})).setMimeType(ContentService.MimeType.JSON);
  } catch(err) {
    return ContentService.createTextOutput(JSON.stringify({error:err.message})).setMimeType(ContentService.MimeType.JSON);
  }
}
function doGet(e) {
  return ContentService.createTextOutput(JSON.stringify({status:'RCS Script Active'})).setMimeType(ContentService.MimeType.JSON);
}`;
  const a = document.createElement('a');
  a.href = 'data:text/plain;charset=utf-8,' + encodeURIComponent(code);
  a.download = 'RCS_Graphic_AppsScript.gs';
  a.click();
  toast('Apps Script downloaded!', 'success');
}
</script>
    </div></div></div>
</body></html>
