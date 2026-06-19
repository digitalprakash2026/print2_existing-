<?php
$pageTitle = 'Banner Slider — RCS Admin';
$currentAdmPage = 'banners';
include __DIR__ . '/layout.php';
?>
<div class="adm-pt">Banner Slider Manager</div>

<div class="fsec" style="max-width:1100px">
  <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:14px">
    <div style="font-size:13px;color:var(--text2)">Upload banner images and optionally make each banner clickable with a link.</div>
    <button class="btn btn-blue btn-sm" onclick="newBanner()">+ New Banner</button>
  </div>

  <div id="bnErr" style="display:none;padding:10px 12px;border:1px solid var(--red-mid);background:var(--red-bg);color:var(--red);border-radius:10px;font-size:12px;margin-bottom:10px"></div>

  <div id="bnList"></div>
  <hr style="border:none;border-top:1px solid var(--border);margin:18px 0">

  <div style="font-weight:800;margin-bottom:10px" id="bnFormTitle">Add Banner</div>
  <div class="f2">
    <div class="fg">
      <label>Banner Image *</label>
      <input type="file" class="fi" id="bn-image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
      <div style="font-size:11px;color:var(--text3);margin-top:5px">Recommended ratio: 1024×384 or similar wide banner.</div>
    </div>
    <div class="fg"><label>Image Path *</label><input class="fi" id="bn-image-path" placeholder="/uploads/banners/..."></div>
  </div>
  <div class="fg"><label>Clickable Banner Link <span style="color:var(--text3);font-weight:500">(optional)</span></label><input class="fi" id="bn-click-url" placeholder="/categories or https://example.com/offer"></div>

  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px">
    <button class="btn btn-outline btn-sm" onclick="uploadBannerImage()">Upload Image</button>
    <button class="btn btn-blue btn-sm" onclick="saveBanner()" id="bnSaveBtn">Save Banner</button>
    <button class="btn btn-outline btn-sm" onclick="resetForm()">Reset</button>
  </div>
</div>

<script>
let banners = [];
let editId = 0;
let currentSort = 0;
let currentActive = 1;

function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
function toastMsg(msg,type='info'){ const w=document.getElementById('tw'); const t=document.createElement('div'); t.className='toast '+type; t.textContent=msg; w.appendChild(t); requestAnimationFrame(()=>requestAnimationFrame(()=>t.classList.add('show'))); setTimeout(()=>{t.classList.remove('show'); setTimeout(()=>t.remove(),300);},2600); }
function showErr(msg=''){ const e=document.getElementById('bnErr'); if(!e) return; if(!msg){e.style.display='none';return;} e.textContent=msg; e.style.display='block'; }

async function loadBanners() {
  const res = await fetch('/admin/api/banners').then(r=>r.json());
  if (!res.ok) { showErr(res.msg || 'Could not load banners'); return; }
  showErr('');
  banners = res.banners || [];
  renderBanners();
}

function renderBanners() {
  const box = document.getElementById('bnList');
  if (!banners.length) {
    box.innerHTML = `<div style="padding:18px;border:1px dashed var(--border);border-radius:10px;color:var(--text2);font-size:13px">No banners added yet.</div>`;
    return;
  }
  box.innerHTML = banners.map((b)=>`
    <div style="display:grid;grid-template-columns:120px 1fr auto;gap:12px;align-items:center;padding:10px;border:1px solid var(--border);border-radius:12px;margin-bottom:9px;background:#fff">
      <img src="${esc(b.image_path)}" style="width:120px;height:68px;object-fit:cover;border-radius:10px;border:1px solid var(--border)">
      <div>
        <div style="font-weight:800;font-size:13px">Image Banner</div>
        <div style="font-size:11px;color:var(--text2)">Order: ${Number(b.sort_order||0)} · ${b.is_active ? 'Active' : 'Inactive'} · Link: ${b.image_click_url ? esc(b.image_click_url) : 'Not clickable'}</div>
      </div>
      <div style="display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end">
        <button class="btn btn-outline btn-sm" onclick="shiftOrder(${Number(b.id)}, -1)">↑</button>
        <button class="btn btn-outline btn-sm" onclick="shiftOrder(${Number(b.id)}, 1)">↓</button>
        <button class="btn btn-outline btn-sm" onclick="editBanner(${Number(b.id)})">Edit</button>
        <button class="btn btn-outline btn-sm" onclick="delBanner(${Number(b.id)})">Delete</button>
      </div>
    </div>
  `).join('');
}

function fillForm(b) {
  editId = Number(b.id || 0);
  currentSort = Number(b.sort_order || 0);
  currentActive = Number(b.is_active ?? 1) ? 1 : 0;
  document.getElementById('bnFormTitle').textContent = editId ? `Edit Banner #${editId}` : 'Add Banner';
  document.getElementById('bn-image-path').value = b.image_path || '';
  document.getElementById('bn-click-url').value = b.image_click_url || '';
}

function collectForm() {
  const clickUrl = document.getElementById('bn-click-url').value.trim();
  return {
    eyebrow: '', title: '', subtitle: '', image_alt: '',
    cta_primary_text: '', cta_primary_url: '', cta_secondary_text: '', cta_secondary_type: 'url', cta_secondary_url: '',
    image_path: document.getElementById('bn-image-path').value.trim(),
    image_click_enabled: clickUrl ? 1 : 0,
    image_click_url: clickUrl,
    sort_order: currentSort,
    is_active: currentActive,
  };
}

function resetForm(){ editId = 0; currentSort = banners.length; currentActive = 1; fillForm({sort_order:currentSort,is_active:1}); showErr(''); }
function newBanner(){ resetForm(); window.scrollTo({top:document.body.scrollHeight, behavior:'smooth'}); }
function editBanner(id){ const b = banners.find(x=>Number(x.id)===Number(id)); if (!b) return; fillForm(b); window.scrollTo({top:document.body.scrollHeight, behavior:'smooth'}); }

async function saveBanner() {
  const payload = collectForm();
  if (!payload.image_path) { showErr('Banner image path required.'); return; }
  showErr('');
  const btn = document.getElementById('bnSaveBtn');
  btn.disabled = true;
  btn.textContent = 'Saving...';
  try {
    const url = editId ? `/admin/api/banners/${editId}` : '/admin/api/banners';
    const method = editId ? 'PUT' : 'POST';
    const res = await fetch(url, {method, headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'<?= htmlspecialchars($csrf??'') ?>'}, body: JSON.stringify(payload)}).then(r=>r.json());
    if (!res.ok) { showErr(res.msg || 'Save failed'); return; }
    toastMsg(editId ? 'Banner updated' : 'Banner created', 'success');
    resetForm();
    await loadBanners();
  } finally {
    btn.disabled = false;
    btn.textContent = 'Save Banner';
  }
}

async function delBanner(id) {
  if (!confirm('Delete this banner?')) return;
  const res = await fetch(`/admin/api/banners/${id}`, {method:'DELETE', headers:{'X-CSRF-TOKEN':'<?= htmlspecialchars($csrf??'') ?>'}}).then(r=>r.json());
  if (!res.ok) { showErr(res.msg || 'Delete failed'); return; }
  toastMsg('Banner deleted', 'info');
  await loadBanners();
}

async function shiftOrder(id, delta){
  const sorted = [...banners].sort((a,b)=>Number(a.sort_order||0)-Number(b.sort_order||0));
  const idx = sorted.findIndex(x=>Number(x.id)===Number(id));
  if (idx < 0) return;
  const to = idx + delta;
  if (to < 0 || to >= sorted.length) return;
  const tmp = sorted[idx].sort_order;
  sorted[idx].sort_order = sorted[to].sort_order;
  sorted[to].sort_order = tmp;
  const items = sorted.map((x,i)=>({id:x.id, sort_order:i}));
  const res = await fetch('/admin/api/banners/reorder', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'<?= htmlspecialchars($csrf??'') ?>'}, body: JSON.stringify({items})}).then(r=>r.json());
  if (!res.ok) { showErr(res.msg || 'Reorder failed'); return; }
  await loadBanners();
}

async function uploadBannerImage(){
  const file = document.getElementById('bn-image').files?.[0];
  if (!file) { showErr('Select image first.'); return; }
  const fd = new FormData();
  fd.append('image', file);
  const res = await fetch('/admin/api/banners/upload', {method:'POST', headers:{'X-CSRF-TOKEN':'<?= htmlspecialchars($csrf??'') ?>'}, body: fd}).then(r=>r.json());
  if (!res.ok) { showErr(res.msg || 'Upload failed'); return; }
  document.getElementById('bn-image-path').value = res.path || '';
  showErr('');
  toastMsg('Image uploaded', 'success');
}

resetForm();
loadBanners();
</script>
    </div></div></div>
</body></html>
