<?php
$pageTitle = 'Business Needs — RCS Admin';
$currentAdmPage = 'business-needs';
include __DIR__ . '/layout.php';
?>
<div class="adm-pt">Shop by Business Needs</div>

<div class="fsec business-needs-admin" style="max-width:1180px">
  <div class="business-needs-admin-head">
    <div>
      <h2>Business / Sector Collections</h2>
      <p>Create homepage collections like Education, Healthcare, Retail or Events and attach selected products to each sector.</p>
    </div>
    <button class="btn btn-blue btn-sm" type="button" onclick="resetNeedForm();document.getElementById('needFormCard').scrollIntoView({behavior:'smooth',block:'start'});">+ Add Business Need</button>
  </div>
  <div id="needErr" class="adm-inline-error" style="display:none"></div>
  <div id="needList" class="business-needs-list"></div>
</div>

<div class="fsec business-needs-form-card" id="needFormCard" style="max-width:1180px;margin-top:16px">
  <div class="business-needs-admin-head compact"><div><h2 id="needFormTitle">Add Business Need</h2><p>Select products in the exact order customers should see them.</p></div></div>
  <div class="grid2">
    <div class="fg"><label>Name</label><input class="fi" id="needName" placeholder="Education"></div>
    <div class="fg"><label>Slug</label><input class="fi" id="needSlug" placeholder="education"></div>
    <div class="fg"><label>Icon</label><input class="fi" id="needIcon" placeholder="🎓"></div>
    <div class="fg"><label>Sort Order</label><input class="fi" id="needSort" type="number" value="0"></div>
  </div>
  <div class="fg"><label>Description</label><input class="fi" id="needDesc" placeholder="Brochures, calendars and stationery for schools and institutes."></div>
  <div class="grid2">
    <div class="fg"><label>Status</label><select class="fi fi-sel" id="needActive"><option value="1">Active</option><option value="0">Hidden</option></select></div>
    <div class="fg"><label>Quick product search</label><input class="fi" id="productSearch" placeholder="Search products..." oninput="renderProductPicker()"></div>
  </div>
  <div class="business-product-picker" id="productPicker"></div>
  <div class="form-actions" style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap">
    <button class="btn btn-blue" id="needSaveBtn" type="button" onclick="saveNeed()">Save Business Need</button>
    <button class="btn btn-outline" type="button" onclick="resetNeedForm()">Reset</button>
  </div>
</div>

<script>
const CSRF = '<?= htmlspecialchars($csrf??'') ?>';
let needs = [], products = [], editId = 0, selectedProducts = new Set();
function esc(s){return String(s||'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));}
function slugify(s){return String(s||'').toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');}
function showErr(msg=''){const e=document.getElementById('needErr'); if(!e)return; e.style.display=msg?'block':'none'; e.textContent=msg;}
async function loadAll(){
  const [n,p] = await Promise.all([fetch('/admin/api/business-needs').then(r=>r.json()), fetch('/admin/api/products').then(r=>r.json())]);
  needs = n.needs || []; products = p.products || []; renderNeeds(); renderProductPicker();
}
function renderNeeds(){
  const box=document.getElementById('needList');
  if(!needs.length){ box.innerHTML='<div class="adm-empty">No business needs added yet.</div>'; return; }
  box.innerHTML=needs.map(n=>{
    const ids=String(n.product_ids||'').split(',').filter(Boolean).map(Number);
    const names=ids.map(id=>(products.find(p=>Number(p.id)===id)||{}).name).filter(Boolean).slice(0,4).join(', ');
    return `<article class="business-need-row"><div class="business-need-icon">${esc(n.icon||'🏢')}</div><div><strong>${esc(n.name)}</strong><small>${esc(n.description||'')} ${names?`<br>Products: ${esc(names)}${ids.length>4?' +' +(ids.length-4)+' more':''}`:''}</small></div><span>${Number(n.is_active)?'Active':'Hidden'} · Order ${Number(n.sort_order||0)}</span><div><button class="btn btn-outline btn-sm" onclick="editNeed(${Number(n.id)})">Edit</button><button class="btn btn-outline btn-sm" onclick="deleteNeed(${Number(n.id)})">Delete</button></div></article>`;
  }).join('');
}
function renderProductPicker(){
  const q=String(document.getElementById('productSearch')?.value||'').toLowerCase();
  const filtered=products.filter(p=>!q || String(p.name||'').toLowerCase().includes(q) || String(p.category_name||'').toLowerCase().includes(q));
  document.getElementById('productPicker').innerHTML=filtered.map(p=>`<label class="business-product-choice"><input type="checkbox" value="${Number(p.id)}" ${selectedProducts.has(Number(p.id))?'checked':''} onchange="toggleProduct(${Number(p.id)},this.checked)"><span><b>${esc(p.name)}</b><small>${esc(p.category_name||'Product')} · ₹${Number(p.min_price||0).toLocaleString('en-IN')}</small></span></label>`).join('');
}
function toggleProduct(id,on){on?selectedProducts.add(id):selectedProducts.delete(id);}
function resetNeedForm(){editId=0; selectedProducts=new Set(); ['needName','needSlug','needIcon','needDesc'].forEach(id=>document.getElementById(id).value=''); document.getElementById('needSort').value='0'; document.getElementById('needActive').value='1'; document.getElementById('needFormTitle').textContent='Add Business Need'; renderProductPicker();}
function editNeed(id){ const n=needs.find(x=>Number(x.id)===id); if(!n)return; editId=id; document.getElementById('needName').value=n.name||''; document.getElementById('needSlug').value=n.slug||''; document.getElementById('needIcon').value=n.icon||''; document.getElementById('needDesc').value=n.description||''; document.getElementById('needSort').value=n.sort_order||0; document.getElementById('needActive').value=Number(n.is_active)?'1':'0'; selectedProducts=new Set(String(n.product_ids||'').split(',').filter(Boolean).map(Number)); document.getElementById('needFormTitle').textContent='Edit Business Need'; renderProductPicker(); document.getElementById('needFormCard').scrollIntoView({behavior:'smooth',block:'start'});}
async function saveNeed(){
  const payload={name:needName.value.trim(), slug:needSlug.value.trim()||slugify(needName.value), icon:needIcon.value.trim()||'🏢', description:needDesc.value.trim(), product_ids:[...selectedProducts], sort_order:Number(needSort.value||0), is_active:Number(needActive.value||1)};
  const url=editId?`/admin/api/business-needs/${editId}`:'/admin/api/business-needs';
  const res=await fetch(url,{method:editId?'PUT':'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},body:JSON.stringify(payload)}).then(r=>r.json());
  if(!res.ok){showErr(res.msg||'Save failed');return;} showErr(''); resetNeedForm(); await loadAll();
}
async function deleteNeed(id){ if(!confirm('Delete this business need?'))return; const res=await fetch(`/admin/api/business-needs/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF}}).then(r=>r.json()); if(!res.ok){showErr(res.msg||'Delete failed');return;} await loadAll();}
needName.addEventListener('input',()=>{ if(!editId && !needSlug.value.trim()) needSlug.value=slugify(needName.value); });
loadAll();
</script>
