<?php
$pageTitle = 'Admins — RCS Admin';
$currentAdmPage = 'admins';
include __DIR__ . '/layout.php';
?>
<div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:14px">
  <div>
    <div class="adm-pt" style="margin:0">Admins</div>
    <div style="font-size:12px;color:var(--text2);margin-top:4px">Create admin users and manage backend access.</div>
  </div>
  <span id="admCount" style="font-size:12px;color:var(--text2)"></span>
</div>

<div class="fsec" style="margin-bottom:14px">
  <div class="fsec-t">➕ Add New Admin</div>
  <div class="f2">
    <div class="fg"><label>Name *</label><input class="fi" id="adm-name" placeholder="Admin name"></div>
    <div class="fg"><label>Email *</label><input class="fi" id="adm-email" type="email" placeholder="name@example.com"></div>
  </div>
  <div class="f2">
    <div class="fg"><label>Mobile *</label><input class="fi" id="adm-mobile" placeholder="9876543210" maxlength="15"></div>
    <div class="fg" style="position:relative">
      <label>Password *</label>
      <input class="fi" id="adm-password" type="password" placeholder="Minimum 6 characters" style="padding-right:42px">
      <button type="button" onclick="togglePass('adm-password', this)" aria-label="Show password" style="position:absolute;right:8px;top:34px;border:none;background:transparent;color:var(--text2);cursor:pointer;font-size:16px">👁️</button>
    </div>
  </div>
  <button class="btn btn-blue" onclick="createAdmin()" style="padding:10px 16px;border-radius:9px">Create Admin</button>
</div>

<div class="fsec" style="padding:0;overflow:hidden">
  <div style="padding:14px 14px 10px;border-bottom:1px solid var(--border);font-weight:700">All Admin Users</div>
  <div id="adminsWrap"><div style="text-align:center;padding:30px;color:var(--text2)">Loading…</div></div>
</div>

<script>
const CSRF = '<?= htmlspecialchars($csrf ?? '') ?>';

function escH(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function fmtDate(s){ if(!s) return '—'; const d=new Date(s); return Number.isNaN(d.getTime()) ? '—' : d.toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric'}); }
function toast(msg,type='info'){const w=document.getElementById('tw');const t=document.createElement('div');t.className='toast '+type;t.textContent=msg;w.appendChild(t);requestAnimationFrame(()=>requestAnimationFrame(()=>t.classList.add('show')));setTimeout(()=>{t.classList.remove('show');setTimeout(()=>t.remove(),300);},2800);}
function togglePass(id, btn){ const inp=document.getElementById(id); if(!inp) return; const show=inp.type==='password'; inp.type=show?'text':'password'; btn.textContent=show?'🙈':'👁️'; }

async function loadAdmins() {
  const res = await fetch('/admin/api/admin-users', { credentials:'same-origin' }).then(r=>r.json());
  const admins = res.admins || [];
  document.getElementById('admCount').textContent = admins.length + ' admin users';
  if (!admins.length) {
    document.getElementById('adminsWrap').innerHTML = `<div style="text-align:center;padding:40px;color:var(--text2)"><div style="font-size:40px;margin-bottom:8px">🛡️</div><div>No admins found</div></div>`;
    return;
  }

  document.getElementById('adminsWrap').innerHTML = admins.map(a => `
    <div style="padding:12px 14px;border-bottom:1px solid var(--border)">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;flex-wrap:wrap">
        <div>
          <div style="font-size:14px;font-weight:700;color:var(--ink)">${escH(a.name || 'Admin')}</div>
          <div style="font-size:12px;color:var(--text2);margin-top:2px">${escH(a.email || '')}</div>
          <div style="font-size:12px;color:var(--text2)">${escH(a.mobile || '—')}</div>
        </div>
        <div style="text-align:right">
          <div style="font-size:11px;color:var(--text3)">${a.is_active == 1 ? 'Active' : 'Inactive'} · ${escH(a.role || 'admin')}</div>
          <div style="font-size:11px;color:var(--text3)">Created: ${fmtDate(a.created_at)}</div>
          <div style="font-size:11px;color:var(--text3)">Last login: ${fmtDate(a.last_login)}</div>
        </div>
      </div>
    </div>
  `).join('');
}

async function createAdmin() {
  const name = document.getElementById('adm-name').value.trim();
  const email = document.getElementById('adm-email').value.trim();
  const mobile = document.getElementById('adm-mobile').value.trim();
  const password = document.getElementById('adm-password').value;

  if (!name || !email || !mobile || !password) { toast('Please fill all required fields', 'error'); return; }
  const resp = await fetch('/admin/api/admin-users', {
    method:'POST',
    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
    credentials:'same-origin',
    body: JSON.stringify({ name, email, mobile, password, role: 'admin' })
  }).then(r=>r.json());

  if (!resp.ok) { toast(resp.msg || 'Could not create admin', 'error'); return; }
  toast('Admin user created successfully', 'success');
  ['adm-name','adm-email','adm-mobile','adm-password'].forEach(id => { const el = document.getElementById(id); if (el) el.value=''; });
  document.getElementById('adm-password').type = 'password';
  loadAdmins();
}

loadAdmins();
</script>
    </div></div></div>
</body></html>
