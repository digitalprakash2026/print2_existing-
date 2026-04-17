<?php
$pageTitle = 'Add Product — RCS Admin';
$currentAdmPage = 'products-new';
include __DIR__ . '/layout.php';
$editId = (int)($_GET['id'] ?? 0);
?>
<div class="adm-pt"><?= $editId ? 'Edit Product' : 'Add Product' ?></div>

<div class="fsec" style="max-width:900px">
  <input type="hidden" id="ep-id" value="<?= $editId ?>">

  <div class="f2">
    <div class="fg"><label>Product Name *</label><input class="fi" id="ep-name"></div>
    <div class="fg"><label>Category *</label><select class="fi fi-sel" id="ep-cat"></select></div>
  </div>
  <div class="f2">
    <div class="fg"><label>Design Fee (₹)</label><input type="number" min="0" class="fi" id="ep-design-fee" value="0"></div>
    <div class="fg"><label>Status</label><select class="fi fi-sel" id="ep-active"><option value="1">Active</option><option value="0">Inactive</option></select></div>
  </div>
  <div class="fg"><label>Description</label><textarea class="fi" id="ep-desc" style="height:84px"></textarea></div>
  <div class="fg"><label>Specifications (Label: Value per line)</label><textarea class="fi" id="ep-specs" style="height:96px"></textarea></div>

  <div class="fg">
    <label>Product Image (jpg/png/webp, max 5MB)</label>
    <input type="file" class="fi" id="ep-image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
    <div id="imagePreview" style="margin-top:10px"></div>
  </div>

  <div class="fg" style="margin-top:8px">
    <label>Quantity Tier Pricing *</label>
    <div style="overflow:auto;border:1px solid var(--border);border-radius:10px">
      <table class="ptbl" style="margin:0;background:#fff;min-width:420px">
        <thead><tr><th style="width:40%">Quantity</th><th style="width:40%">Price (₹)</th><th style="width:20%">Remove</th></tr></thead>
        <tbody id="tierRows"></tbody>
      </table>
    </div>
    <div style="display:flex;gap:8px;margin-top:10px">
      <button type="button" class="btn btn-outline btn-sm" onclick="addTierRow()">+ Add Row</button>
      <button type="button" class="btn btn-outline btn-sm" onclick="sortTierRows()">Sort by Qty</button>
    </div>
    <div style="font-size:12px;color:var(--text3);margin-top:6px">No duplicate quantities allowed.</div>
  </div>

  <div style="display:flex;gap:9px;flex-wrap:wrap;margin-top:16px">
    <button class="btn btn-blue" onclick="saveProd()" style="padding:12px 26px;border-radius:10px">Save Product ✓</button>
    <a href="/admin/products" class="btn btn-outline" style="padding:12px 18px;border-radius:10px">Back to All Products</a>
  </div>
</div>

<script>
let allCats = [];
let currentImage = '';

function mkTierRow(quantity='', price='') {
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td><input type="number" min="1" class="fi tier-qty" value="${quantity}"></td>
    <td><input type="number" min="0.01" step="0.01" class="fi tier-price" value="${price}"></td>
    <td><button type="button" class="btn btn-outline btn-sm" onclick="this.closest('tr').remove()">✕</button></td>`;
  return tr;
}

function addTierRow(quantity='', price='') {
  document.getElementById('tierRows').appendChild(mkTierRow(quantity, price));
}

function sortTierRows() {
  const tbody = document.getElementById('tierRows');
  const rows = [...tbody.querySelectorAll('tr')];
  rows.sort((a,b) => (parseInt(a.querySelector('.tier-qty').value||'0',10)) - (parseInt(b.querySelector('.tier-qty').value||'0',10)));
  rows.forEach(r => tbody.appendChild(r));
}

function collectTiers() {
  const rows = [...document.querySelectorAll('#tierRows tr')];
  const tiers = rows.map(r => ({
    quantity: parseInt(r.querySelector('.tier-qty').value || '0', 10),
    price: parseFloat(r.querySelector('.tier-price').value || '0')
  }));

  if (!tiers.length) return {ok:false, msg:'Add at least one quantity tier'};
  const seen = new Set();
  for (const t of tiers) {
    if (!t.quantity || !t.price || t.quantity < 1 || t.price <= 0) return {ok:false, msg:'Quantity and price are required'};
    if (seen.has(t.quantity)) return {ok:false, msg:'Duplicate quantity: ' + t.quantity};
    seen.add(t.quantity);
  }
  tiers.sort((a,b) => a.quantity - b.quantity);
  return {ok:true, tiers};
}

async function boot() {
  const catsRes = await fetch('/admin/api/categories').then(r=>r.json());
  allCats = catsRes.categories || [];
  document.getElementById('ep-cat').innerHTML = allCats.map(c=>`<option value="${c.id}">${escH(c.name)}</option>`).join('');

  document.getElementById('ep-image').addEventListener('change', e => {
    const f = e.target.files?.[0];
    if (!f) return;
    const url = URL.createObjectURL(f);
    document.getElementById('imagePreview').innerHTML = `<img src="${url}" style="height:110px;border-radius:10px;border:1px solid var(--border)">`;
  });

  const id = parseInt(document.getElementById('ep-id').value || '0', 10);
  if (!id) { addTierRow(1000,''); addTierRow(2000,''); return; }

  const res = await fetch(`/admin/api/products/${id}`).then(r=>r.json());
  if (!res.ok || !res.product) return;
  const p = res.product;
  document.getElementById('ep-name').value = p.name || '';
  document.getElementById('ep-cat').value = p.category_id || '';
  document.getElementById('ep-design-fee').value = p.design_fee || 0;
  document.getElementById('ep-active').value = p.is_active ? '1' : '0';
  document.getElementById('ep-desc').value = p.description || '';
  document.getElementById('ep-specs').value = (p.specs||[]).map(s=>`${s.label}: ${s.value||''}`).join('\n');

  currentImage = p.image_path || p.primary_image || '';
  if (currentImage) {
    document.getElementById('imagePreview').innerHTML = `<img src="${escAttr(currentImage)}" style="height:110px;border-radius:10px;border:1px solid var(--border)">`;
  }

  const tiersRes = await fetch(`/admin/api/products/${id}/tiers`).then(r=>r.json());
  document.getElementById('tierRows').innerHTML = '';
  (tiersRes.tiers || []).forEach(t => addTierRow(t.quantity, t.price));
  if (!document.querySelector('#tierRows tr')) addTierRow(1000,'');
}

async function saveProd() {
  const id = parseInt(document.getElementById('ep-id').value || '0', 10);
  const name = document.getElementById('ep-name').value.trim();
  const catId = parseInt(document.getElementById('ep-cat').value || '0', 10);
  if (!name || !catId) { toast('Name and category required', 'error'); return; }

  const tierCheck = collectTiers();
  if (!tierCheck.ok) { toast(tierCheck.msg, 'error'); return; }

  const specsRaw = document.getElementById('ep-specs').value.trim().split('\n').filter(Boolean);
  const specs = specsRaw.map(s => {
    const [label, ...rest] = s.split(':');
    return { label: label.trim(), value: rest.join(':').trim() };
  }).filter(s=>s.label);

  const payload = {
    name,
    category_id: catId,
    description: document.getElementById('ep-desc').value.trim(),
    design_fee: parseFloat(document.getElementById('ep-design-fee').value || '0') || 0,
    is_active: parseInt(document.getElementById('ep-active').value || '1',10),
    specs,
    image_path: currentImage || null,
    quantity_tiers: tierCheck.tiers,
  };

  const url = id ? `/admin/api/products/${id}` : '/admin/api/products';
  const method = id ? 'PUT' : 'POST';
  const res = await fetch(url, {
    method,
    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'<?= htmlspecialchars($csrf??'') ?>'},
    body: JSON.stringify(payload)
  }).then(r=>r.json());

  if (!res.ok) { toast(res.msg || 'Failed', 'error'); return; }

  const prodId = res.id || id;

  // save tiers explicitly (for edit reliability)
  const tr = await fetch(`/admin/api/products/${prodId}/tiers`, {
    method:'POST',
    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'<?= htmlspecialchars($csrf??'') ?>'},
    body: JSON.stringify({tiers: tierCheck.tiers})
  }).then(r=>r.json());
  if (!tr.ok) { toast(tr.msg || 'Tier save failed', 'error'); return; }

  const file = document.getElementById('ep-image').files?.[0];
  if (file) {
    const fd = new FormData();
    fd.append('image', file);
    const up = await fetch(`/admin/api/products/${prodId}/image-upload`, {
      method: 'POST',
      headers: {'X-CSRF-TOKEN':'<?= htmlspecialchars($csrf??'') ?>'},
      body: fd
    }).then(r=>r.json());
    if (!up.ok) { toast(up.msg || 'Image upload failed', 'error'); return; }
  }

  toast(id ? 'Product updated' : 'Product created', 'success');
  setTimeout(()=>{ window.location.href = '/admin/products'; }, 450);
}

function escH(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\"/g,'&quot;'); }
function escAttr(s){return escH(s).replace(/'/g,'&#39;');}
function toast(msg, type='info') {
  const w=document.getElementById('tw'); const t=document.createElement('div');
  t.className='toast '+type; t.textContent=msg; w.appendChild(t);
  requestAnimationFrame(()=>requestAnimationFrame(()=>t.classList.add('show')));
  setTimeout(()=>{t.classList.remove('show');setTimeout(()=>t.remove(),300);},2800);
}

boot();
</script>
    </div></div></div>
</body></html>
