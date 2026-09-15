<?php
$pageTitle = 'Business Sectors — RCS Admin';
$currentAdmPage = 'business-needs';
include __DIR__ . '/layout.php';
?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;gap:10px;flex-wrap:wrap">
  <div><div class="adm-pt" style="margin:0">Business Sectors</div><div style="font-size:13px;color:var(--text2);margin-top:4px">Manage industry collections shown on the website. Product assignment is handled from Add/Edit Product.</div></div>
  <a class="btn btn-blue btn-sm" href="/admin/business-needs/new">+ Add Business Sector</a>
</div>
<div id="needErr" class="adm-inline-error" style="display:none"></div>
<div id="needList" class="business-needs-list"><div style="text-align:center;padding:44px;color:var(--text2)"><div class="pay-spin" style="border-top-color:var(--blue);margin:0 auto 12px"></div>Loading sectors…</div></div>
<script>
const CSRF = '<?= htmlspecialchars($csrf??'') ?>';
let needs = [];
function esc(s){return String(s||'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));}
function showErr(msg=''){const e=document.getElementById('needErr'); if(!e)return; e.style.display=msg?'block':'none'; e.textContent=msg;}
async function loadNeeds(){
  try { const res = await fetch('/admin/api/business-needs').then(r=>r.json()); needs = res.needs || []; renderNeeds(); }
  catch(e){ showErr('Could not load business sectors.'); }
}
function renderNeeds(){
  const box=document.getElementById('needList');
  if(!needs.length){ box.innerHTML='<div class="adm-empty">No business sectors added yet.</div>'; return; }
  box.innerHTML=needs.map(n=>{
    const img=String(n.image_path||'');
    return `<article class="business-need-row business-sector-row"><div class="business-need-icon sector-thumb">${img?`<img src="${esc(img)}" alt="${esc(n.name)}" loading="lazy">`:esc(n.icon||'🏢')}</div><div><strong>${esc(n.name)}</strong><small>${esc(n.description||'No description yet.')}<br>Slug: /business/${esc(n.slug||'')}</small></div><span>${Number(n.is_active)?'Active':'Hidden'} · ${Number(n.product_count||0)} Products · Order ${Number(n.sort_order||0)}</span><div><a class="btn btn-outline btn-sm" href="/admin/business-needs/edit/${Number(n.id)}">Edit</a><button class="btn btn-outline btn-sm" onclick="deleteNeed(${Number(n.id)})">Delete</button></div></article>`;
  }).join('');
}
async function deleteNeed(id){ if(!confirm('Delete this business sector? Product assignments will be removed.'))return; const res=await fetch(`/admin/api/business-needs/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF}}).then(r=>r.json()); if(!res.ok){showErr(res.msg||'Delete failed');return;} await loadNeeds();}
loadNeeds();
</script>
    </div></div></div>
</body></html>
