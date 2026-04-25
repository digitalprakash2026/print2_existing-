<?php
$pageTitle = 'Integrations — RCS Admin';
$currentAdmPage = 'integrations';
include __DIR__ . '/layout.php';
$settingsMap = $settingsMap ?? [];
?>
<div class="adm-pt">Integrations</div>
<p style="font-size:13px;color:var(--text2);margin-bottom:16px">Manage external integrations in one place.</p>

<form method="POST" action="/admin/settings/save">
  <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
  <div class="fsec">
    <div class="fsec-t">📊 Google Sheets</div>
    <div class="fg">
      <label>Apps Script Web App URL</label>
      <input name="sheets_webhook_url" class="fi" value="<?= htmlspecialchars($settingsMap['sheets_webhook_url'] ?? '') ?>" placeholder="https://script.google.com/macros/s/.../exec">
      <div class="f-hint">New orders + status updates will sync to this sheet URL.</div>
    </div>
    <div style="font-size:12px;font-weight:600;color:<?= !empty($settingsMap['sheets_webhook_url']) ? 'var(--green)' : 'var(--text3)' ?>">
      <?= !empty($settingsMap['sheets_webhook_url']) ? '✅ Connected' : '⚠️ Not configured' ?>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px">
      <button type="button" onclick="testSheets()" class="btn btn-outline btn-sm">🧪 Test Connection</button>
      <button type="button" onclick="downloadScript()" class="btn btn-outline btn-sm">⬇ Download Apps Script</button>
    </div>
  </div>
  <button type="submit" class="btn btn-blue" style="padding:12px 22px;border-radius:10px">Save Integrations ✓</button>
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
  try {
    await fetch(url, {
      method: 'POST', mode: 'no-cors',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ action: 'addOrder', orderId: 'TEST-' + Date.now(), customerName: 'Connection Test', total: 0, status: 'test' })
    });
    toast('Test ping sent. Check your Google Sheet.', 'success');
  } catch {
    toast('Could not reach URL', 'error');
  }
}

function downloadScript() {
  const code = `function doPost(e){try{const data=JSON.parse(e.postData.contents);const ss=SpreadsheetApp.getActiveSpreadsheet();const sh=ss.getSheetByName('RCS Orders')||ss.insertSheet('RCS Orders');if(sh.getLastRow()===0){sh.appendRow(['Order ID','Date','Customer','Phone','Email','Total','Status']);}
if(data.action==='addOrder'){sh.appendRow([data.orderId,new Date(),data.customerName||'',data.customerPhone||'',data.customerEmail||'',data.total||'',data.status||'']);}
if(data.action==='updateStatus'){const lr=sh.getLastRow();for(let i=2;i<=lr;i++){if(sh.getRange(i,1).getValue()===data.orderId){sh.getRange(i,7).setValue(data.status);break;}}}
return ContentService.createTextOutput(JSON.stringify({success:true})).setMimeType(ContentService.MimeType.JSON);}catch(err){return ContentService.createTextOutput(JSON.stringify({error:err.message})).setMimeType(ContentService.MimeType.JSON);}}
function doGet(){return ContentService.createTextOutput('ok');}`;
  const a = document.createElement('a');
  a.href = 'data:text/plain;charset=utf-8,' + encodeURIComponent(code);
  a.download = 'RCS_Graphic_AppsScript.gs';
  a.click();
  toast('Apps Script downloaded', 'success');
}
</script>
    </div></div></div>
</body></html>
