<?php
$pageTitle = 'Add Product — RCS Admin';
$currentAdmPage = 'products-new';
include __DIR__ . '/layout.php';
$editId = (int)($_GET['id'] ?? 0);
?>
<div class="adm-pt"><?= $editId ? 'Edit Product' : 'Add Product' ?></div>

<div class="fsec" style="max-width:960px">
  <input type="hidden" id="ep-id" value="<?= $editId ?>">

  <div class="f2">
    <div class="fg"><label>Product Name *</label><input class="fi" id="ep-name"></div>
    <div class="fg"><label>Category *</label><select class="fi fi-sel" id="ep-cat"></select></div>
  </div>
  <div class="f2">
    <div class="fg"><label>Product Code</label><input class="fi" id="ep-code" placeholder="Auto: PREFIX-001"></div>
    <div class="fg"><label>Code Prefix (from category)</label><input class="fi" id="ep-prefix" disabled></div>
  </div>
  <div id="ep-code-help" style="font-size:12px;color:var(--text2);margin-top:-6px;margin-bottom:10px">
    Leave Product Code empty to auto-generate from selected category prefix.
  </div>
  <div class="f2">
    <div class="fg"><label>Design Fee (₹)</label><input type="number" min="0" class="fi" id="ep-design-fee" value="0"></div>
    <div class="fg"><label>Status</label><select class="fi fi-sel" id="ep-active"><option value="1">Active</option><option value="0">Inactive</option></select></div>
  </div>
  <div class="fg"><label>Description</label><textarea class="fi" id="ep-desc" style="height:84px"></textarea></div>
  <div class="fg"><label>Specifications (Label: Value per line)</label><textarea class="fi" id="ep-specs" style="height:96px"></textarea></div>

  <div class="fg">
    <label>Product Images (multiple allowed, jpg/png/webp, max 5MB each)</label>
    <input type="file" class="fi" id="ep-images" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple>
    <div id="imagePreview" style="margin-top:10px;display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:10px"></div>
  </div>

  <div class="fg" style="margin-top:8px">
    <label>Quantity Tier Pricing (Fixed: 1000 → 10000) *</label>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:10px">
      <?php for ($q = 1000; $q <= 10000; $q += 1000): ?>
      <div style="border:1px solid var(--border);border-radius:10px;padding:10px;background:#fff">
        <div style="font-size:12px;color:var(--text2);margin-bottom:6px;font-weight:700"><?= number_format($q) ?> pcs</div>
        <input type="number" min="0" step="0.01" class="fi tier-fixed" data-qty="<?= $q ?>" placeholder="Price for <?= number_format($q) ?>">
      </div>
      <?php endfor; ?>
    </div>
    <div style="font-size:12px;color:var(--text3);margin-top:6px">Leave blank to skip a quantity.</div>
  </div>

  <div style="display:flex;gap:9px;flex-wrap:wrap;margin-top:16px">
    <button class="btn btn-blue" id="saveBtn" onclick="saveProd()" style="padding:12px 26px;border-radius:10px">Save Product ✓</button>
    <a href="/admin/products" class="btn btn-outline" style="padding:12px 18px;border-radius:10px">Back to All Products</a>
  </div>
</div>

<script>
let allCats = [];
let currentImages = [];
let pendingImages = [];
let autoCodePreview = '';

function renderPreview(images = currentImages, newImages = pendingImages) {
  const box = document.getElementById('imagePreview');
  box.innerHTML = '';
  const allImages = [
    ...images.map((img, i) => ({...normalizeImage(img), index: i, isNew: false})),
    ...newImages.map((img, i) => ({...normalizeImage(img), index: i, isNew: true}))
  ].filter(img => img.path);

  if (!allImages.length) {
    box.innerHTML = '<div style="grid-column:1/-1;padding:14px;border:1px dashed var(--border);border-radius:10px;color:var(--text2);font-size:12px;background:#fff">No product images yet. Upload images and save the product.</div>';
    return;
  }

  allImages.forEach((img) => {
    const div = document.createElement('div');
    div.style.cssText = 'border:1px solid var(--border);border-radius:10px;padding:8px;background:#fff;position:relative;display:grid;gap:7px';
    const badge = img.isNew ? 'New image' : (img.isPrimary ? 'Primary image' : 'Gallery image');
    const deleteButton = (!img.isNew && img.id > 0)
      ? `<button type="button" class="btn btn-red btn-xs" onclick="deleteProductImage(${img.id})" style="width:100%;justify-content:center">🗑️ Delete image</button>`
      : (img.isNew
        ? '<div style="font-size:11px;color:var(--green);font-weight:800">Will upload on save</div>'
        : '<button type="button" class="btn btn-red btn-xs" onclick="deleteLegacyProductImage()" style="width:100%;justify-content:center">🗑️ Clear main image</button>');
    div.innerHTML = `
      <img src="${escAttr(img.path)}" style="width:100%;height:88px;object-fit:cover;border-radius:8px;border:1px solid var(--border)" onerror="this.style.display='none'">
      <div style="font-size:11px;color:${img.isPrimary ? 'var(--blue)' : 'var(--text2)'};font-weight:800">${badge}</div>
      ${deleteButton}
    `;
    box.appendChild(div);
  });
}

function normalizeImage(img) {
  if (typeof img === 'string') return {id: 0, path: img, isPrimary: false};
  return {
    id: parseInt(img?.id || '0', 10),
    path: img?.image_path || img?.url || '',
    isPrimary: Number(img?.is_primary || 0) === 1,
  };
}

function collectFixedTiers() {
  const tiers = [];
  document.querySelectorAll('.tier-fixed').forEach(input => {
    const qty = parseInt(input.dataset.qty || '0', 10);
    const price = parseFloat(input.value || '0');
    if (price > 0) tiers.push({quantity: qty, price});
  });
  if (!tiers.length) return {ok:false, msg:'Add at least one quantity price'};
  return {ok:true, tiers};
}

async function boot() {
  const catsRes = await fetch('/admin/api/categories').then(r=>r.json());
  allCats = catsRes.categories || [];
  document.getElementById('ep-cat').innerHTML = allCats.map(c=>`<option value="${c.id}">${escH(c.name)}</option>`).join('');
  document.getElementById('ep-cat').addEventListener('change', updateCatPrefixHint);
  document.getElementById('ep-code').addEventListener('input', updateCodeHelp);
  await updateCatPrefixHint();

  document.getElementById('ep-images').addEventListener('change', e => {
    const files = [...(e.target.files || [])];
    if (!files.length) return;
    pendingImages.forEach(img => { if (img.image_path?.startsWith('blob:')) URL.revokeObjectURL(img.image_path); });
    pendingImages = files.map(f => ({image_path: URL.createObjectURL(f)}));
    renderPreview();
  });

  const id = parseInt(document.getElementById('ep-id').value || '0', 10);
  if (!id) return;

  const res = await fetch(`/admin/api/products/${id}`).then(r=>r.json());
  if (!res.ok || !res.product) return;
  const p = res.product;
  document.getElementById('ep-name').value = p.name || '';
  document.getElementById('ep-cat').value = p.category_id || '';
  document.getElementById('ep-code').value = p.product_code || '';
  document.getElementById('ep-design-fee').value = p.design_fee || 0;
  document.getElementById('ep-active').value = p.is_active ? '1' : '0';
  document.getElementById('ep-desc').value = p.description || '';
  document.getElementById('ep-specs').value = (p.specs||[]).map(s=>`${s.label}: ${s.value||''}`).join('\n');

  currentImages = p.images || (p.image_path ? [{image_path:p.image_path, is_primary: 1}] : []);
  pendingImages = [];
  renderPreview();

  const tiersRes = await fetch(`/admin/api/products/${id}/tiers`).then(r=>r.json());
  const map = {};
  (tiersRes.tiers || []).forEach(t => map[parseInt(t.quantity, 10)] = t.price);
  document.querySelectorAll('.tier-fixed').forEach(input => {
    const qty = parseInt(input.dataset.qty || '0', 10);
    input.value = map[qty] ? String(map[qty]) : '';
  });
  await updateCatPrefixHint();
}

async function updateCatPrefixHint() {
  const catId = parseInt(document.getElementById('ep-cat')?.value || '0', 10);
  const cat = allCats.find(c => Number(c.id) === catId);
  const prefix = (cat?.code_prefix || '').toUpperCase();
  const box = document.getElementById('ep-prefix');
  if (box) box.value = prefix || 'Not set';

  autoCodePreview = '';
  if (catId > 0) {
    try {
      const editId = parseInt(document.getElementById('ep-id')?.value || '0', 10);
      const q = editId > 0 ? `?edit_id=${editId}` : '';
      const res = await fetch(`/admin/api/categories/${catId}/next-product-code${q}`).then(r=>r.json());
      if (res?.ok && res.code) autoCodePreview = String(res.code).toUpperCase();
    } catch (e) {
      console.warn('Could not fetch auto code preview', e);
    }
  }
  updateCodeHelp();
}

function updateCodeHelp() {
  const codeInput = document.getElementById('ep-code');
  const help = document.getElementById('ep-code-help');
  if (!codeInput || !help) return;
  const typed = codeInput.value.trim().toUpperCase();
  if (typed) {
    help.textContent = 'Manual Product Code will be used as-is.';
    help.style.color = 'var(--blue)';
    return;
  }
  if (autoCodePreview) {
    help.textContent = `Auto code on save: ${autoCodePreview}`;
    help.style.color = 'var(--green)';
    return;
  }
  help.textContent = 'Auto code unavailable (check category prefix / DB migration).';
  help.style.color = 'var(--red)';
}

async function deleteProductImage(imageId) {
  const id = parseInt(document.getElementById('ep-id').value || '0', 10);
  imageId = parseInt(imageId || '0', 10);
  if (!id || !imageId) { toast('Save the product before deleting images', 'error'); return; }
  const img = currentImages.find(item => Number(item.id || 0) === imageId);
  const label = img && Number(img.is_primary || 0) === 1 ? 'primary product image' : 'product image';
  if (!confirm(`Delete this ${label}? This cannot be undone.`)) return;

  try {
    const res = await fetch(`/admin/api/products/${id}/images/${imageId}`, {
      method: 'DELETE',
      headers: {'X-CSRF-TOKEN':'<?= htmlspecialchars($csrf??'') ?>'},
      credentials: 'same-origin'
    }).then(r => r.json());

    if (!res.ok) { toast(res.msg || 'Could not delete image', 'error'); return; }
    currentImages = (res.images || []).length ? res.images : currentImages.filter(item => Number(item.id || 0) !== imageId);
    renderPreview();
    toast('Product image deleted', 'success');
  } catch (err) {
    console.error(err);
    toast('Unexpected error while deleting image', 'error');
  }
}

async function deleteLegacyProductImage() {
  const id = parseInt(document.getElementById('ep-id').value || '0', 10);
  if (!id) { toast('Save the product before deleting images', 'error'); return; }
  if (!confirm('Clear this legacy main product image? This cannot be undone.')) return;

  try {
    const res = await fetch(`/admin/api/products/${id}/image-path`, {
      method: 'DELETE',
      headers: {'X-CSRF-TOKEN':'<?= htmlspecialchars($csrf??'') ?>'},
      credentials: 'same-origin'
    }).then(r => r.json());

    if (!res.ok) { toast(res.msg || 'Could not clear main image', 'error'); return; }
    currentImages = res.images || [];
    renderPreview();
    toast('Main product image cleared', 'success');
  } catch (err) {
    console.error(err);
    toast('Unexpected error while clearing image', 'error');
  }
}

async function saveProd() {
  const id = parseInt(document.getElementById('ep-id').value || '0', 10);
  const name = document.getElementById('ep-name').value.trim();
  const catId = parseInt(document.getElementById('ep-cat').value || '0', 10);
  if (!name || !catId) { toast('Name and category required', 'error'); return; }

  const tierCheck = collectFixedTiers();
  if (!tierCheck.ok) { toast(tierCheck.msg, 'error'); return; }

  const specsRaw = document.getElementById('ep-specs').value.trim().split('\n').filter(Boolean);
  const specs = specsRaw.map(s => {
    const [label, ...rest] = s.split(':');
    return { label: label.trim(), value: rest.join(':').trim() };
  }).filter(s=>s.label);

  const payload = {
    name,
    category_id: catId,
    product_code: document.getElementById('ep-code').value.trim().toUpperCase(),
    description: document.getElementById('ep-desc').value.trim(),
    design_fee: parseFloat(document.getElementById('ep-design-fee').value || '0') || 0,
    is_active: parseInt(document.getElementById('ep-active').value || '1',10),
    specs,
    quantity_tiers: tierCheck.tiers,
  };

  const saveBtn = document.getElementById('saveBtn');
  saveBtn.disabled = true;
  const oldText = saveBtn.textContent;
  saveBtn.textContent = 'Saving...';

  try {
    const url = id ? `/admin/api/products/${id}` : '/admin/api/products';
    const method = id ? 'PUT' : 'POST';
    const res = await fetch(url, {
      method,
      headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'<?= htmlspecialchars($csrf??'') ?>'},
      body: JSON.stringify(payload)
    }).then(r=>r.json());

    if (!res.ok) {
      toast(res.msg || 'Failed to save product', 'error');
      console.error('Save product failed response:', res);
      return;
    }

    const prodId = res.id || id;

    const tr = await fetch(`/admin/api/products/${prodId}/tiers`, {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'<?= htmlspecialchars($csrf??'') ?>'},
      body: JSON.stringify({tiers: tierCheck.tiers})
    }).then(r=>r.json());
    if (!tr.ok) { toast(tr.msg || 'Tier save failed', 'error'); return; }

    const files = [...(document.getElementById('ep-images').files || [])];
    if (files.length) {
      const fd = new FormData();
      files.forEach(f => fd.append('images[]', f));
      const up = await fetch(`/admin/api/products/${prodId}/images-upload`, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN':'<?= htmlspecialchars($csrf??'') ?>'},
        body: fd
      }).then(r=>r.json());
      if (!up.ok) { toast(up.msg || 'Image upload failed', 'error'); return; }
    }

    toast(id ? 'Product updated successfully' : 'Product created successfully', 'success');
    setTimeout(()=>{ window.location.href = '/admin/products'; }, 700);
  } catch (err) {
    console.error(err);
    toast('Unexpected error while saving. Check logs.', 'error');
  } finally {
    saveBtn.disabled = false;
    saveBtn.textContent = oldText;
  }
}

function escH(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\"/g,'&quot;'); }
function escAttr(s){return escH(s).replace(/'/g,'&#39;');}
function toast(msg, type='info') {
  const w=document.getElementById('tw'); const t=document.createElement('div');
  t.className='toast '+type; t.textContent=msg; w.appendChild(t);
  requestAnimationFrame(()=>requestAnimationFrame(()=>t.classList.add('show')));
  setTimeout(()=>{t.classList.remove('show');setTimeout(()=>t.remove(),300);},3000);
}

boot();
</script>
    </div></div></div>
</body></html>
