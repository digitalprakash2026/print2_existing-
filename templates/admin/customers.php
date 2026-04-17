<?php
$pageTitle = 'Customers — RCS Admin';
$currentAdmPage = 'customers';
include __DIR__ . '/layout.php';
?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
  <div class="adm-pt" style="margin:0">Customers</div>
  <span id="custCount" style="font-size:13px;color:var(--text2)"></span>
</div>
<div id="custList"><div style="text-align:center;padding:44px;color:var(--text2)"><div class="pay-spin" style="border-top-color:var(--blue);margin:0 auto 12px"></div>Loading…</div></div>

<script>
const WA = '<?= htmlspecialchars($bizSettings['biz_whatsapp'] ?? '') ?>';

async function loadCustomers() {
  const res = await fetch('/admin/api/customers').then(r=>r.json());
  const custs = res.customers || [];
  document.getElementById('custCount').textContent = custs.length + ' customers';
  if (!custs.length) {
    document.getElementById('custList').innerHTML = `<div style="text-align:center;padding:60px;color:var(--text2)"><div style="font-size:48px;margin-bottom:12px">👥</div><div style="font-size:15px;font-weight:600">No registered customers yet</div></div>`;
    return;
  }
  const colors = ['#1A56E8','#059669','#D97706','#DC2626','#7C3AED','#0891B2'];
  function avColor(name) { let h=0; for(let i=0;i<name.length;i++)h=name.charCodeAt(i)+((h<<5)-h); return colors[Math.abs(h)%colors.length]; }
  function initials(name) { return (name||'?').split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2); }

  document.getElementById('custList').innerHTML = custs.map(c => `
    <div style="background:var(--white);border-radius:12px;border:1.5px solid var(--border);padding:13px;margin-bottom:8px;display:flex;align-items:center;gap:11px;transition:var(--tr)" onmouseover="this.style.borderColor='var(--blue-mid)'" onmouseout="this.style.borderColor='var(--border)'">
      <div style="width:40px;height:40px;border-radius:50%;background:${avColor(c.name)};display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0">${initials(c.name)}</div>
      <div style="flex:1;min-width:0">
        <div style="font-size:14px;font-weight:700">${escH(c.name)}${c.company?` <span style="font-size:12px;font-weight:400;color:var(--text2)">· ${escH(c.company)}</span>`:''}</div>
        <div style="font-size:12px;color:var(--text2);margin-top:2px">${escH(c.email)} · ${escH(c.phone)}</div>
        <div style="font-size:11px;color:var(--text3);margin-top:2px">Joined: ${new Date(c.created_at).toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric'})}</div>
      </div>
      <div style="text-align:right;flex-shrink:0">
        <div style="font-size:20px;font-weight:700;color:var(--blue);font-family:'Fraunces',serif">${c.order_count}</div>
        <div style="font-size:10px;color:var(--text3)">orders</div>
        <div style="font-size:13px;font-weight:700;color:var(--green)">₹${Number(c.total_spent||0).toLocaleString('en-IN')}</div>
      </div>
      <button onclick="window.open('https://wa.me/${escH(c.phone.replace(/\D/g,''))}','_blank')" style="width:34px;height:34px;border-radius:8px;background:var(--bg2);border:1.5px solid var(--border);cursor:pointer;font-size:15px;display:flex;align-items:center;justify-content:center;flex-shrink:0" title="WhatsApp">📲</button>
    </div>`).join('');
}

function escH(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}

loadCustomers();
</script>
    </div></div></div>
</body></html>
