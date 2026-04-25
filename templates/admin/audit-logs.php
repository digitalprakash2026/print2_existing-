<?php
$pageTitle = 'Audit Logs — RCS Admin';
$currentAdmPage = 'audit-logs';
include __DIR__ . '/layout.php';
?>
<div class="adm-pt">Audit Logs</div>
<p style="font-size:13px;color:var(--text2);margin-bottom:16px">Admin action history — last 200 entries.</p>
<div id="logList"><div style="text-align:center;padding:44px;color:var(--text2)"><div class="pay-spin" style="border-top-color:var(--blue);margin:0 auto 12px"></div>Loading…</div></div>
<script>
async function loadLogs() {
  const res = await fetch('/admin/api/audit-logs').then(r=>r.json());
  const logs = res.logs || [];
  if (!logs.length) {
    document.getElementById('logList').innerHTML = '<div style="text-align:center;padding:44px;color:var(--text2)"><div style="font-size:40px;margin-bottom:9px">📋</div><div>No audit logs yet</div></div>';
    return;
  }
  document.getElementById('logList').innerHTML = `
    <table class="ptbl" style="background:var(--white);border-radius:12px;overflow:hidden">
      <thead><tr><th>Time</th><th>Admin</th><th>Action</th><th>Description</th><th>IP</th></tr></thead>
      <tbody>${logs.map(l=>`<tr>
        <td style="font-size:11px;white-space:nowrap;color:var(--text3)">${new Date(l.created_at).toLocaleString('en-IN')}</td>
        <td style="font-weight:600">${escH(l.admin_name||'system')}</td>
        <td><span style="font-size:11px;padding:3px 8px;border-radius:6px;background:var(--blue-bg);color:var(--blue);font-weight:600">${escH(l.action)}</span></td>
        <td style="font-size:12px;color:var(--text2)">${escH(l.description||'—')}</td>
        <td style="font-size:11px;color:var(--text3)">${escH(l.ip_address||'—')}</td>
      </tr>`).join('')}</tbody>
    </table>`;
}
function escH(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
loadLogs();
</script>
    </div></div></div>
</body></html>
