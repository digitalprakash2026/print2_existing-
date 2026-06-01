<?php
$pageTitle = 'Best Deals — RCS Admin';
$currentAdmPage = 'deals';
include __DIR__ . '/layout.php';
?>
<div class="adm-pt">Best Deals Manager</div>

<div class="fsec" style="max-width:1120px">
  <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:14px">
    <div style="font-size:13px;color:var(--text2)">Manage the homepage Our Best Deals cards. Normal deal cards and the promo offer card are both dynamic.</div>
    <button class="btn btn-blue btn-sm" onclick="newDeal()">+ New Deal</button>
  </div>

  <div id="dealErr" style="display:none;padding:10px 12px;border:1px solid var(--red-mid);background:var(--red-bg);color:var(--red);border-radius:10px;font-size:12px;margin-bottom:10px"></div>

  <div id="dealList"></div>
  <hr style="border:none;border-top:1px solid var(--border);margin:18px 0">

  <div style="font-weight:700;margin-bottom:10px" id="dealFormTitle">Add Deal</div>
  <div class="f2">
    <div class="fg">
      <label>Card Type</label>
      <select class="fi fi-sel" id="deal-type" onchange="toggleDealHelp()">
        <option value="deal">Normal Deal Card</option>
        <option value="promo">Promo / Offer Card</option>
      </select>
    </div>
    <div class="fg">
      <label>Color Theme</label>
      <select class="fi fi-sel" id="deal-theme">
        <option value="green">Green</option>
        <option value="orange">Orange</option>
        <option value="purple">Purple</option>
      </select>
    </div>
  </div>

  <div class="f2">
    <div class="fg"><label>Title *</label><input class="fi" id="deal-title" placeholder="500 Visiting Cards"></div>
    <div class="fg"><label>Highlight Text <span style="color:var(--text3);font-weight:500">(promo optional)</span></label><input class="fi" id="deal-highlight" placeholder="FREE Design"></div>
  </div>

  <div class="f2">
    <div class="fg"><label>Subtitle</label><input class="fi" id="deal-subtitle" placeholder="Starting from / on Your First Order!"></div>
    <div class="fg"><label>Price Text <span style="color:var(--text3);font-weight:500">(required for normal deal)</span></label><input class="fi" id="deal-price" placeholder="₹199"></div>
  </div>

  <div class="fg"><label>Description <span style="color:var(--text3);font-weight:500">(optional, admin note)</span></label><textarea class="fi" id="deal-description" style="height:64px"></textarea></div>

  <div class="f2">
    <div class="fg">
      <label>Deal Image</label>
      <input type="file" class="fi" id="deal-image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
      <div id="dealHelp" style="font-size:11px;color:var(--text3);margin-top:5px">Normal deal cards require an image. Promo cards can use the default gift art or an uploaded image.</div>
    </div>
    <div class="fg"><label>Image Path <span style="color:var(--text3);font-weight:500">(required for normal deal)</span></label><input class="fi" id="deal-image-path" placeholder="/uploads/deals/..."></div>
  </div>

  <div class="f2">
    <div class="fg"><label>Image Alt</label><input class="fi" id="deal-alt" placeholder="500 visiting cards printing deal"></div>
    <div class="fg"><label>Sort Order</label><input type="number" class="fi" id="deal-sort" value="0"></div>
  </div>

  <div class="f2">
    <div class="fg"><label>CTA Text</label><input class="fi" id="deal-cta-text" placeholder="Order Now"></div>
    <div class="fg"><label>CTA URL</label><input class="fi" id="deal-cta-url" placeholder="/categories"></div>
  </div>

  <div class="fg">
    <label>Status</label>
    <select class="fi fi-sel" id="deal-active">
      <option value="1">Active</option>
      <option value="0">Inactive</option>
    </select>
  </div>

  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px">
    <button class="btn btn-blue btn-sm" onclick="saveDeal()" id="dealSaveBtn">Save Deal</button>
    <button class="btn btn-outline btn-sm" onclick="resetForm()">Reset</button>
    <button class="btn btn-outline btn-sm" onclick="uploadDealImage()">Upload Image</button>
  </div>
</div>

<script>
let deals = [];
let editId = 0;
const CSRF = '<?= htmlspecialchars($csrf??'') ?>';

function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
function toastMsg(msg,type='info'){ const w=document.getElementById('tw'); const t=document.createElement('div'); t.className='toast '+type; t.textContent=msg; w.appendChild(t); requestAnimationFrame(()=>requestAnimationFrame(()=>t.classList.add('show'))); setTimeout(()=>{t.classList.remove('show'); setTimeout(()=>t.remove(),300);},2600); }
function showErr(msg=''){ const e=document.getElementById('dealErr'); if(!e) return; if(!msg){e.style.display='none';return;} e.textContent=msg; e.style.display='block'; }

async function loadDeals() {
  const res = await fetch('/admin/api/deals').then(r=>r.json());
  if (!res.ok) { showErr(res.msg || 'Could not load deals'); return; }
  showErr('');
  deals = res.deals || [];
  renderDeals();
}

function renderDeals() {
  const box = document.getElementById('dealList');
  if (!deals.length) {
    box.innerHTML = `<div style="padding:18px;border:1px dashed var(--border);border-radius:10px;color:var(--text2);font-size:13px">No deals added yet. Run the migration to seed the 3 default deals and 1 promo card.</div>`;
    return;
  }
  box.innerHTML = deals.map((d) => {
    const type = d.deal_type === 'promo' ? 'Promo' : 'Deal';
    const img = d.image_path ? `<img src="${esc(d.image_path)}" style="width:94px;height:62px;object-fit:cover;border-radius:8px;border:1px solid var(--border)">` : `<div style="width:94px;height:62px;border-radius:8px;border:1px dashed var(--border);display:flex;align-items:center;justify-content:center;color:var(--text3);font-size:11px">Gift Art</div>`;
    return `
      <div style="display:grid;grid-template-columns:94px 1fr auto;gap:12px;align-items:center;padding:10px;border:1px solid var(--border);border-radius:10px;margin-bottom:8px;background:#fff">
        ${img}
        <div>
          <div style="display:flex;gap:7px;align-items:center;flex-wrap:wrap;margin-bottom:3px">
            <span style="font-size:10px;font-weight:800;color:#fff;background:${d.deal_type === 'promo' ? '#6d28d9' : '#0f766e'};border-radius:999px;padding:3px 8px">${type}</span>
            <span style="font-size:11px;color:var(--text2)">${esc(d.color_theme || 'green')}</span>
          </div>
          <div style="font-weight:800;font-size:13px">${esc(d.title)} ${d.highlight_text ? `<span style="color:var(--orange)">${esc(d.highlight_text)}</span>` : ''}</div>
          <div style="font-size:11px;color:var(--text2)">${esc(d.subtitle || '')}${d.price_text ? ` · ${esc(d.price_text)}` : ''} · Order: ${Number(d.sort_order||0)} · ${Number(d.is_active) ? 'Active' : 'Inactive'}</div>
        </div>
        <div style="display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end">
          <button class="btn btn-outline btn-sm" onclick="shiftOrder(${Number(d.id)}, -1)">↑</button>
          <button class="btn btn-outline btn-sm" onclick="shiftOrder(${Number(d.id)}, 1)">↓</button>
          <button class="btn btn-outline btn-sm" onclick="editDeal(${Number(d.id)})">Edit</button>
          <button class="btn btn-outline btn-sm" onclick="delDeal(${Number(d.id)})">Delete</button>
        </div>
      </div>`;
  }).join('');
}

function toggleDealHelp(){
  const type = document.getElementById('deal-type').value;
  document.getElementById('dealHelp').textContent = type === 'promo'
    ? 'Promo cards can use the default gift art. Upload an image only if you want to replace it.'
    : 'Normal deal cards require an image and price text.';
}

function fillForm(d) {
  editId = Number(d.id || 0);
  document.getElementById('dealFormTitle').textContent = editId ? `Edit Deal #${editId}` : 'Add Deal';
  document.getElementById('deal-type').value = d.deal_type || 'deal';
  document.getElementById('deal-theme').value = d.color_theme || 'green';
  document.getElementById('deal-title').value = d.title || '';
  document.getElementById('deal-highlight').value = d.highlight_text || '';
  document.getElementById('deal-subtitle').value = d.subtitle || '';
  document.getElementById('deal-price').value = d.price_text || '';
  document.getElementById('deal-description').value = d.description || '';
  document.getElementById('deal-image-path').value = d.image_path || '';
  document.getElementById('deal-alt').value = d.image_alt || '';
  document.getElementById('deal-cta-text').value = d.cta_text || '';
  document.getElementById('deal-cta-url').value = d.cta_url || '';
  document.getElementById('deal-sort').value = Number(d.sort_order || 0);
  document.getElementById('deal-active').value = Number(d.is_active ?? 1) ? '1' : '0';
  toggleDealHelp();
}

function collectForm() {
  return {
    deal_type: document.getElementById('deal-type').value,
    color_theme: document.getElementById('deal-theme').value,
    title: document.getElementById('deal-title').value.trim(),
    highlight_text: document.getElementById('deal-highlight').value.trim(),
    subtitle: document.getElementById('deal-subtitle').value.trim(),
    price_text: document.getElementById('deal-price').value.trim(),
    description: document.getElementById('deal-description').value.trim(),
    image_path: document.getElementById('deal-image-path').value.trim(),
    image_alt: document.getElementById('deal-alt').value.trim(),
    cta_text: document.getElementById('deal-cta-text').value.trim(),
    cta_url: document.getElementById('deal-cta-url').value.trim(),
    sort_order: parseInt(document.getElementById('deal-sort').value || '0', 10) || 0,
    is_active: parseInt(document.getElementById('deal-active').value || '1', 10) || 0,
  };
}

function resetForm(){ editId = 0; fillForm({deal_type:'deal', color_theme:'green', subtitle:'Starting from', cta_text:'Order Now', cta_url:'/categories', is_active:1}); showErr(''); }
function newDeal(){ resetForm(); window.scrollTo({top:document.body.scrollHeight, behavior:'smooth'}); }
function editDeal(id){ const d = deals.find(x=>Number(x.id)===Number(id)); if (!d) return; fillForm(d); window.scrollTo({top:document.body.scrollHeight, behavior:'smooth'}); }

async function saveDeal() {
  const payload = collectForm();
  if (!payload.title) { showErr('Deal title is required.'); return; }
  if (payload.deal_type === 'deal' && !payload.image_path) { showErr('Normal deal image is required.'); return; }
  if (payload.deal_type === 'deal' && !payload.price_text) { showErr('Normal deal price is required.'); return; }
  showErr('');
  const btn = document.getElementById('dealSaveBtn');
  btn.disabled = true;
  btn.textContent = 'Saving...';
  try {
    const url = editId ? `/admin/api/deals/${editId}` : '/admin/api/deals';
    const method = editId ? 'PUT' : 'POST';
    const res = await fetch(url, {method, headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF}, body: JSON.stringify(payload)}).then(r=>r.json());
    if (!res.ok) { showErr(res.msg || 'Save failed'); return; }
    toastMsg(editId ? 'Deal updated' : 'Deal created', 'success');
    resetForm();
    await loadDeals();
  } finally {
    btn.disabled = false;
    btn.textContent = 'Save Deal';
  }
}

async function delDeal(id) {
  if (!confirm('Delete this deal?')) return;
  const res = await fetch(`/admin/api/deals/${id}`, {method:'DELETE', headers:{'X-CSRF-TOKEN':CSRF}}).then(r=>r.json());
  if (!res.ok) { showErr(res.msg || 'Delete failed'); return; }
  toastMsg('Deal deleted', 'info');
  await loadDeals();
}

async function shiftOrder(id, delta){
  const sorted = [...deals].sort((a,b)=>Number(a.sort_order||0)-Number(b.sort_order||0));
  const idx = sorted.findIndex(x=>Number(x.id)===Number(id));
  if (idx < 0) return;
  const to = idx + delta;
  if (to < 0 || to >= sorted.length) return;
  const tmp = sorted[idx].sort_order;
  sorted[idx].sort_order = sorted[to].sort_order;
  sorted[to].sort_order = tmp;
  const items = sorted.map((x,i)=>({id:x.id, sort_order:i}));
  const res = await fetch('/admin/api/deals/reorder', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF}, body: JSON.stringify({items})}).then(r=>r.json());
  if (!res.ok) { showErr(res.msg || 'Reorder failed'); return; }
  await loadDeals();
}

async function uploadDealImage(){
  const file = document.getElementById('deal-image').files?.[0];
  if (!file) { showErr('Select image first.'); return; }
  const fd = new FormData();
  fd.append('image', file);
  const res = await fetch('/admin/api/deals/upload', {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}, body: fd}).then(r=>r.json());
  if (!res.ok) { showErr(res.msg || 'Upload failed'); return; }
  document.getElementById('deal-image-path').value = res.path || '';
  showErr('');
  toastMsg('Image uploaded', 'success');
}

resetForm();
loadDeals();
</script>
    </div></div></div>
</body></html>
