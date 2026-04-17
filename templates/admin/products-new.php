<?php
$pageTitle = 'Add Product — RCS Admin';
$currentAdmPage = 'products-new';
include __DIR__ . '/layout.php';
$editId = (int)($_GET['id'] ?? 0);
?>
<div class="adm-pt"><?= $editId ? 'Edit Product' : 'Add Product' ?></div>

<div class="fsec" style="max-width:860px">
  <input type="hidden" id="ep-id" value="<?= $editId ?>">
  <div class="f2">
    <div class="fg"><label>Product Name *</label><input class="fi" id="ep-name" placeholder="Business Card Printing"></div>
    <div class="fg"><label>Category *</label><select class="fi fi-sel" id="ep-cat"></select></div>
  </div>
  <div class="f2">
    <div class="fg"><label>Design Fee (₹)</label><input type="number" min="0" class="fi" id="ep-design-fee" placeholder="0"></div>
    <div class="fg"><label>Status</label><select class="fi fi-sel" id="ep-active"><option value="1">Active</option><option value="0">Inactive</option></select></div>
  </div>
  <div class="fg"><label>Description</label><textarea class="fi" id="ep-desc" style="height:84px" placeholder="Describe the product…"></textarea></div>
  <div class="fg"><label>Specifications (Label: Value per line)</label><textarea class="fi" id="ep-specs" style="height:96px" placeholder="Size: A4&#10;Paper: 300 GSM&#10;Finishing: Matte"></textarea></div>

  <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text2);margin-bottom:9px;margin-top:4px">Product Images (URLs)</div>
  <div class="fg"><label>Main Image URL *</label><input class="fi" id="ep-i1" placeholder="https://..." oninput="previewImg('ep-i1','pv1')"><div id="pv1" style="margin-top:7px"></div></div>
  <div class="f2">
    <div class="fg"><label>Image 2</label><input class="fi" id="ep-i2" oninput="previewImg('ep-i2','pv2')"><div id="pv2" style="margin-top:5px"></div></div>
    <div class="fg"><label>Image 3</label><input class="fi" id="ep-i3" oninput="previewImg('ep-i3','pv3')"><div id="pv3" style="margin-top:5px"></div></div>
  </div>
  <div class="fg"><label>Image 4</label><input class="fi" id="ep-i4" oninput="previewImg('ep-i4','pv4')"><div id="pv4" style="margin-top:5px"></div></div>

  <div style="display:flex;gap:9px;flex-wrap:wrap">
    <button class="btn btn-blue" onclick="saveProd()" style="padding:12px 26px;border-radius:10px">Save Product ✓</button>
    <a href="/admin/products" class="btn btn-outline" style="padding:12px 18px;border-radius:10px">Back to All Products</a>
    <a href="/admin/pricing" class="btn btn-outline" style="padding:12px 18px;border-radius:10px">Go to Pricing</a>
  </div>
</div>

<script>
let allCats = [];

async function boot() {
  const catsRes = await fetch('/admin/api/categories').then(r=>r.json());
  allCats = catsRes.categories || [];
  document.getElementById('ep-cat').innerHTML = allCats.map(c=>`<option value="${c.id}">${escH(c.name)}</option>`).join('');

  const id = parseInt(document.getElementById('ep-id').value || '0', 10);
  if (!id) return;

  const res = await fetch(`/admin/api/products/${id}`).then(r=>r.json());
  if (!res.ok || !res.product) return;
  const p = res.product;
  document.getElementById('ep-name').value = p.name || '';
  document.getElementById('ep-cat').value = p.category_id || '';
  document.getElementById('ep-design-fee').value = p.design_fee || 0;
  document.getElementById('ep-active').value = p.is_active ? '1' : '0';
  document.getElementById('ep-desc').value = p.description || '';
  document.getElementById('ep-specs').value = (p.specs||[]).map(s=>`${s.label}: ${s.value||''}`).join('\n');

  const imgs = p.images || [];
  ['ep-i1','ep-i2','ep-i3','ep-i4'].forEach((fid,i) => {
    const val = imgs[i]?.url || '';
    document.getElementById(fid).value = val;
    previewImg(fid, 'pv'+(i+1));
  });
}

async function saveProd() {
  const id = parseInt(document.getElementById('ep-id').value || '0', 10);
  const name = document.getElementById('ep-name').value.trim();
  const catId = parseInt(document.getElementById('ep-cat').value || '0', 10);
  if (!name || !catId) { toast('Name and category required', 'error'); return; }

  const specsRaw = document.getElementById('ep-specs').value.trim().split('\n').filter(Boolean);
  const specs = specsRaw.map(s => {
    const [label, ...rest] = s.split(':');
    return { label: label.trim(), value: rest.join(':').trim() };
  }).filter(s=>s.label);

  const imgs = ['ep-i1','ep-i2','ep-i3','ep-i4'].map(id=>document.getElementById(id).value.trim()).filter(Boolean);

  const data = {
    name,
    category_id: catId,
    description: document.getElementById('ep-desc').value.trim(),
    design_fee: parseFloat(document.getElementById('ep-design-fee').value || '0') || 0,
    is_active: parseInt(document.getElementById('ep-active').value || '1',10),
    specs
  };

  const url = id ? `/admin/api/products/${id}` : '/admin/api/products';
  const method = id ? 'PUT' : 'POST';
  const res = await fetch(url, {
    method,
    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'<?= htmlspecialchars($csrf??'') ?>'},
    body: JSON.stringify(data)
  }).then(r=>r.json());

  if (!res.ok) { toast(res.msg || 'Failed', 'error'); return; }

  const prodId = res.id || id;
  await fetch(`/admin/api/products/${prodId}/images`, { method:'DELETE', headers:{'X-CSRF-TOKEN':'<?= htmlspecialchars($csrf??'') ?>'} });
  for (let i = 0; i < imgs.length; i++) {
    await fetch(`/admin/api/products/${prodId}/images`, {
      method: 'POST',
      headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'<?= htmlspecialchars($csrf??'') ?>'},
      body: JSON.stringify({ url: imgs[i], is_primary: i===0?1:0, sort_order: i })
    });
  }

  toast(id ? 'Product updated' : 'Product created', 'success');
  setTimeout(()=>{ window.location.href = '/admin/products'; }, 400);
}

function previewImg(inputId, previewId) {
  const url = document.getElementById(inputId)?.value?.trim();
  const p = document.getElementById(previewId);
  if (!p) return;
  p.innerHTML = url ? `<img src="${url}" style="height:${inputId==='ep-i1'?'80':'54'}px;border-radius:8px;border:1px solid var(--border)" onerror="this.style.opacity=.3">` : '';
}

function escH(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\"/g,'&quot;'); }
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
