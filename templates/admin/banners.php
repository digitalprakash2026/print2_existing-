<?php
$pageTitle = 'Banner Slider — RCS Admin';
$currentAdmPage = 'banners';
include __DIR__ . '/layout.php';
?>
<div class="adm-pt">Banner Slider Manager</div>

<div class="fsec" style="max-width:1100px">
  <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:14px">
    <div style="font-size:13px;color:var(--text2)">Manage home page slider images, texts and CTA buttons.</div>
    <button class="btn btn-blue btn-sm" onclick="newBanner()">+ New Banner</button>
  </div>

  <div id="bnErr" style="display:none;padding:10px 12px;border:1px solid var(--red-mid);background:var(--red-bg);color:var(--red);border-radius:10px;font-size:12px;margin-bottom:10px"></div>

  <div id="bnList"></div>
  <hr style="border:none;border-top:1px solid var(--border);margin:18px 0">

  <div style="font-weight:700;margin-bottom:10px" id="bnFormTitle">Add Banner</div>
  <div class="f2">
    <div class="fg"><label>Eyebrow</label><input class="fi" id="bn-eyebrow" placeholder="New Arrivals"></div>
    <div class="fg"><label>Image Alt</label><input class="fi" id="bn-alt" placeholder="Premium Business Card Printing"></div>
  </div>
  <div class="fg"><label>Title (use &lt;br&gt; for line break)</label><input class="fi" id="bn-title" placeholder="Premium Business<br>Cards That Impress"></div>
  <div class="fg"><label>Subtitle (use &lt;br&gt; for line break)</label><textarea class="fi" id="bn-subtitle" style="height:72px"></textarea></div>
  <div class="f2">
    <div class="fg">
      <label>Banner Image</label>
      <input type="file" class="fi" id="bn-image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
      <div style="font-size:11px;color:var(--text3);margin-top:5px">Image select karo, Save Banner par auto upload ho jayegi.</div>
      <input type="hidden" id="bn-image-path">
      <div id="bn-img-preview-wrap" style="display:none;margin-top:8px">
        <img id="bn-img-preview" src="" alt="Banner preview" style="width:180px;height:96px;object-fit:cover;border:1px solid var(--border);border-radius:8px">
      </div>
    </div>
    <div class="fg" style="display:flex;align-items:flex-end">
      <div style="font-size:12px;color:var(--text2)">Image path ab auto-manage hota hai.</div>
    </div>
  </div>
  <div class="f2">
    <div class="fg"><label>Primary CTA Text</label><input class="fi" id="bn-ptext" value="View Products →"></div>
    <div class="fg"><label>Primary CTA URL</label><input class="fi" id="bn-purl" value="/products"></div>
  </div>
  <div class="f2">
    <div class="fg"><label>Secondary CTA Text</label><input class="fi" id="bn-stext" value="💬 WhatsApp"></div>
    <div class="fg">
      <label>Secondary CTA Type</label>
      <select class="fi fi-sel" id="bn-stype">
        <option value="whatsapp">WhatsApp</option>
        <option value="url">Custom URL</option>
      </select>
    </div>
  </div>
  <div class="f2">
    <div class="fg"><label>Secondary CTA URL (for type=url)</label><input class="fi" id="bn-surl" placeholder="https://example.com/offer"></div>
    <div class="fg"><label>Sort Order</label><input type="number" class="fi" id="bn-sort" value="0"></div>
  </div>
  <div class="fg">
    <label>Status</label>
    <select class="fi fi-sel" id="bn-active">
      <option value="1">Active</option>
      <option value="0">Inactive</option>
    </select>
  </div>

  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px">
    <button class="btn btn-blue btn-sm" onclick="saveBanner()" id="bnSaveBtn">Save Banner</button>
    <button class="btn btn-outline btn-sm" onclick="resetForm()">Reset</button>
  </div>
</div>

<script>
let banners = [];
let editId = 0;

function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
function resolveImagePath(path){
  const p = String(path || '').trim();
  if (!p) return '';
  if (/^https?:\/\//i.test(p)) return p;
  return '/' + p.replace(/^\/+/, '');
}
function toastMsg(msg,type='info'){ const w=document.getElementById('tw'); const t=document.createElement('div'); t.className='toast '+type; t.textContent=msg; w.appendChild(t); requestAnimationFrame(()=>requestAnimationFrame(()=>t.classList.add('show'))); setTimeout(()=>{t.classList.remove('show'); setTimeout(()=>t.remove(),300);},2600); }
function showErr(msg=''){ const e=document.getElementById('bnErr'); if(!e) return; if(!msg){e.style.display='none';return;} e.textContent=msg; e.style.display='block'; }
function setPreview(path){
  const wrap = document.getElementById('bn-img-preview-wrap');
  const img = document.getElementById('bn-img-preview');
  const p = resolveImagePath(path);
  if (!p) { wrap.style.display = 'none'; img.src=''; return; }
  img.src = p;
  wrap.style.display = 'block';
}

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
  box.innerHTML = banners.map((b,idx)=>`
    <div style="display:grid;grid-template-columns:100px 1fr auto;gap:10px;align-items:center;padding:10px;border:1px solid var(--border);border-radius:10px;margin-bottom:8px;background:#fff">
      <img src="${esc(resolveImagePath(b.image_path))}" style="width:100px;height:58px;object-fit:cover;border-radius:8px;border:1px solid var(--border)">
      <div>
        <div style="font-weight:700;font-size:13px">${esc(b.title || '(No title)')}</div>
        <div style="font-size:11px;color:var(--text2)">Order: ${Number(b.sort_order||0)} · ${b.is_active ? 'Active' : 'Inactive'}</div>
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
  document.getElementById('bnFormTitle').textContent = editId ? `Edit Banner #${editId}` : 'Add Banner';
  document.getElementById('bn-eyebrow').value = b.eyebrow || '';
  document.getElementById('bn-title').value = b.title || '';
  document.getElementById('bn-subtitle').value = b.subtitle || '';
  document.getElementById('bn-image-path').value = b.image_path || '';
  setPreview(b.image_path || '');
  document.getElementById('bn-alt').value = b.image_alt || '';
  document.getElementById('bn-ptext').value = b.cta_primary_text || 'View Products →';
  document.getElementById('bn-purl').value = b.cta_primary_url || '/products';
  document.getElementById('bn-stext').value = b.cta_secondary_text || '💬 WhatsApp';
  document.getElementById('bn-stype').value = b.cta_secondary_type || 'whatsapp';
  document.getElementById('bn-surl').value = b.cta_secondary_url || '';
  document.getElementById('bn-sort').value = Number(b.sort_order || 0);
  document.getElementById('bn-active').value = b.is_active ? '1' : '0';
}

function collectForm() {
  return {
    eyebrow: document.getElementById('bn-eyebrow').value.trim(),
    title: document.getElementById('bn-title').value.trim(),
    subtitle: document.getElementById('bn-subtitle').value.trim(),
    image_path: resolveImagePath(document.getElementById('bn-image-path').value),
    image_alt: document.getElementById('bn-alt').value.trim(),
    cta_primary_text: document.getElementById('bn-ptext').value.trim(),
    cta_primary_url: document.getElementById('bn-purl').value.trim(),
    cta_secondary_text: document.getElementById('bn-stext').value.trim(),
    cta_secondary_type: document.getElementById('bn-stype').value,
    cta_secondary_url: document.getElementById('bn-surl').value.trim(),
    sort_order: parseInt(document.getElementById('bn-sort').value || '0', 10) || 0,
    is_active: parseInt(document.getElementById('bn-active').value || '1', 10) || 0,
  };
}

function resetForm(){ editId = 0; fillForm({}); showErr(''); const fi=document.getElementById('bn-image'); if(fi) fi.value=''; }
function newBanner(){ resetForm(); window.scrollTo({top:document.body.scrollHeight, behavior:'smooth'}); }
function editBanner(id){ const b = banners.find(x=>Number(x.id)===Number(id)); if (!b) return; fillForm(b); window.scrollTo({top:document.body.scrollHeight, behavior:'smooth'}); }

async function ensureImagePathForSave(){
  const file = document.getElementById('bn-image').files?.[0];
  if (!file) return document.getElementById('bn-image-path').value.trim();
  const fd = new FormData();
  fd.append('image', file);
  const res = await fetch('/admin/api/banners/upload', {method:'POST', headers:{'X-CSRF-TOKEN':'<?= htmlspecialchars($csrf??'') ?>'}, body: fd}).then(r=>r.json());
  if (!res.ok) throw new Error(res.msg || 'Upload failed');
  const path = resolveImagePath(res.path || '');
  document.getElementById('bn-image-path').value = path;
  setPreview(path);
  return path;
}

async function saveBanner() {
  const payload = collectForm();
  const btn = document.getElementById('bnSaveBtn');
  btn.disabled = true;
  btn.textContent = 'Saving...';
  try {
    payload.image_path = await ensureImagePathForSave();
  } catch (e) {
    showErr(e?.message || 'Image upload failed');
    btn.disabled = false;
    btn.textContent = 'Save Banner';
    return;
  }
  if (!payload.title || !payload.image_path) { showErr('Title and image required.'); btn.disabled = false; btn.textContent = 'Save Banner'; return; }
  if (payload.cta_secondary_type === 'url' && !payload.cta_secondary_url) { showErr('Secondary CTA URL required for type=url'); btn.disabled = false; btn.textContent = 'Save Banner'; return; }
  showErr('');
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

resetForm();
loadBanners();
document.getElementById('bn-image').addEventListener('change', (e) => {
  const file = e.target.files?.[0];
  if (!file) return;
  setPreview(URL.createObjectURL(file));
});
</script>
    </div></div></div>
</body></html>
