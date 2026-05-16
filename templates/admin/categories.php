<?php
$pageTitle = 'Categories — RCS Admin';
$currentAdmPage = 'categories';
include __DIR__ . '/layout.php';
?>
<div class="adm-pt">Categories</div>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;gap:10px;flex-wrap:wrap">
  <div id="catCount" style="font-size:13px;color:var(--text2)">Loading…</div>
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <a href="/admin/products" class="btn btn-outline btn-sm">← Back to Products</a>
    <button class="btn btn-blue btn-sm" type="button" onclick="openCatModal()">+ Add Category</button>
  </div>
</div>

<div id="catList">
  <div style="text-align:center;padding:44px;color:var(--text2)"><div class="pay-spin" style="border-top-color:var(--blue);margin:0 auto 12px"></div>Loading categories…</div>
</div>

<div id="catModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:1300;align-items:center;justify-content:center;padding:18px;overflow:auto">
  <div style="width:min(720px,100%);max-height:92vh;overflow:auto;background:var(--white);border:1px solid var(--border);border-radius:12px;padding:16px">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
      <div id="catModalTitle" style="font-family:var(--fd);font-size:16px;font-weight:700">Add Category</div>
      <button class="btn btn-outline btn-sm" type="button" onclick="closeCatModal()">Close ✕</button>
    </div>
    <div class="f2">
      <div class="fg"><label>Category Name *</label><input id="cat-name" class="fi" placeholder="Visiting Cards"></div>
      <div class="fg"><label>Slug</label><input id="cat-slug" class="fi" placeholder="visiting-cards"></div>
    </div>
    <div class="f2">
      <div class="fg"><label>Code Prefix</label><input id="cat-prefix" class="fi" placeholder="RCSVC"></div>
      <div class="fg"><label>Icon</label><input id="cat-icon" class="fi" placeholder="💳"></div>
    </div>
    <div class="f2">
      <div class="fg"><label>Sort Order</label><input id="cat-sort" type="number" class="fi" value="0"></div>
      <div class="fg"><label>Status</label>
        <select id="cat-active" class="fi fi-sel">
          <option value="1">Active</option>
          <option value="0">Inactive</option>
        </select>
      </div>
    </div>

    <div style="border:1px solid var(--border);border-radius:12px;padding:12px;margin:4px 0 14px;background:#f8fafc">
      <div style="display:flex;gap:14px;align-items:flex-start;flex-wrap:wrap">
        <div id="catImagePreview" style="width:118px;height:118px;border-radius:16px;border:1px solid var(--border);background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;color:var(--text3);font-size:12px;text-align:center;padding:8px">No image</div>
        <div style="flex:1;min-width:240px">
          <div class="fg" style="margin-bottom:8px"><label>Category Square Image</label><input id="cat-image-file" type="file" class="fi" accept="image/jpeg,image/png,image/webp"></div>
          <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:8px">
            <button class="btn btn-outline btn-sm" type="button" onclick="uploadCategoryImage()">Upload Image</button>
            <button class="btn btn-outline btn-sm" type="button" onclick="clearCategoryImage()">Remove</button>
          </div>
          <div style="font-size:11px;color:var(--text3);line-height:1.45">Recommended: square 600×600 or 800×800 JPG/PNG/WebP. This image is used in the home page “Shop By Category” cards.</div>
        </div>
      </div>
      <div class="f2" style="margin-top:10px">
        <div class="fg"><label>Image Path</label><input id="cat-image-path" class="fi" placeholder="/uploads/categories/..." oninput="updateCategoryPreview()"></div>
        <div class="fg"><label>Image Alt Text</label><input id="cat-image-alt" class="fi" placeholder="Visiting Cards category image"></div>
      </div>
    </div>

    <div id="catErr" style="display:none;font-size:12px;color:var(--red);margin-bottom:10px"></div>
    <button class="btn btn-blue btn-sm" type="button" onclick="saveCategory()">Save Category</button>
  </div>
</div>

<script>
const CSRF = '<?= htmlspecialchars($csrf??'') ?>';
let allCats = [];
let editId = 0;

async function loadCategories() {
  const res = await fetch('/admin/api/categories').then(r=>r.json());
  allCats = res.categories || [];
  document.getElementById('catCount').textContent = `${allCats.length} categories`;
  if (!allCats.length) {
    document.getElementById('catList').innerHTML = `<div style="text-align:center;padding:54px;color:var(--text2)"><div style="font-size:42px;margin-bottom:8px">🗂️</div><div style="font-size:15px;font-weight:600">No categories yet</div></div>`;
    return;
  }

  document.getElementById('catList').innerHTML = allCats.map(c => {
    const imagePath = c.image_path || '';
    const media = imagePath
      ? `<img src="${escAttr(imagePath)}" alt="${escAttr(c.image_alt || c.name || 'Category image')}" style="width:100%;height:100%;object-fit:cover">`
      : escH(c.icon || '🖨️');
    return `
      <div class="aprod">
        <div class="aprod-img" style="display:flex;align-items:center;justify-content:center;font-size:28px;overflow:hidden">${media}</div>
        <div class="aprod-info">
          <div class="aprod-name">${escH(c.name || '')}</div>
          <div class="aprod-meta">Slug: ${escH(c.slug || '')} · Prefix: <span style="color:var(--blue);font-weight:700">${escH((c.code_prefix || '').toUpperCase() || '—')}</span></div>
          <div style="font-size:11px;color:var(--text3);margin-top:3px">Products: ${Number(c.product_count||0)} · Sort: ${Number(c.sort_order||0)} · ${c.is_active ? 'Active' : 'Inactive'} · ${imagePath ? 'Image set' : 'No image'}</div>
        </div>
        <div class="aprod-acts">
          <button class="ic-btn" type="button" onclick="openCatModal(${Number(c.id)})" title="Edit">
            <svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
          </button>
          <div class="tog ${c.is_active?'on':''}" onclick="toggleCategory(${Number(c.id)},this)" title="${c.is_active ? 'Deactivate' : 'Activate'}"><div class="tog-k"></div></div>
          <button class="ic-btn del" type="button" onclick="deleteCategory(${Number(c.id)},'${escJS(c.name||'')}')" title="Delete">
            <svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
          </button>
        </div>
      </div>
    `;
  }).join('');
}

function openCatModal(id = 0) {
  editId = Number(id || 0);
  const m = document.getElementById('catModal');
  const title = document.getElementById('catModalTitle');
  const err = document.getElementById('catErr');
  if (err) err.style.display = 'none';
  setVal('cat-image-file', '');
  if (editId > 0) {
    const c = allCats.find(x => Number(x.id) === editId);
    if (!c) return;
    title.textContent = 'Edit Category';
    setVal('cat-name', c.name || '');
    setVal('cat-slug', c.slug || '');
    setVal('cat-prefix', (c.code_prefix || '').toUpperCase());
    setVal('cat-icon', c.icon || '🖨️');
    setVal('cat-sort', Number(c.sort_order || 0));
    setVal('cat-active', Number(c.is_active || 0) ? '1' : '0');
    setVal('cat-image-path', c.image_path || '');
    setVal('cat-image-alt', c.image_alt || `${c.name || 'Category'} category image`);
  } else {
    title.textContent = 'Add Category';
    setVal('cat-name', '');
    setVal('cat-slug', '');
    setVal('cat-prefix', '');
    setVal('cat-icon', '🖨️');
    setVal('cat-sort', 0);
    setVal('cat-active', '1');
    setVal('cat-image-path', '');
    setVal('cat-image-alt', '');
  }
  updateCategoryPreview();
  if (m) m.style.display = 'flex';
}

function closeCatModal() {
  const m = document.getElementById('catModal');
  if (m) m.style.display = 'none';
}

async function saveCategory() {
  const name = getVal('cat-name').trim();
  const slug = getVal('cat-slug').trim().toLowerCase();
  const code_prefix = getVal('cat-prefix').trim().toUpperCase();
  const icon = getVal('cat-icon').trim() || '🖨️';
  const image_path = getVal('cat-image-path').trim();
  const image_alt = getVal('cat-image-alt').trim() || (name ? `${name} category image` : 'Category image');
  const sort_order = parseInt(getVal('cat-sort') || '0', 10) || 0;
  const is_active = parseInt(getVal('cat-active') || '1', 10) ? 1 : 0;
  const err = document.getElementById('catErr');
  if (!name) {
    if (err) { err.textContent = 'Category name is required.'; err.style.display = 'block'; }
    return;
  }

  const payload = { name, slug, code_prefix, icon, image_path, image_alt, sort_order, is_active };
  const url = editId > 0 ? `/admin/api/categories/${editId}` : '/admin/api/categories';
  const method = editId > 0 ? 'PUT' : 'POST';
  const res = await fetch(url, {
    method,
    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
    body: JSON.stringify(payload)
  }).then(r=>r.json());

  if (!res.ok) {
    if (err) { err.textContent = res.msg || 'Could not save category.'; err.style.display = 'block'; }
    return;
  }
  closeCatModal();
  toast(editId > 0 ? 'Category updated' : 'Category created', 'success');
  loadCategories();
}

async function uploadCategoryImage() {
  const input = document.getElementById('cat-image-file');
  const err = document.getElementById('catErr');
  if (err) err.style.display = 'none';
  if (!input || !input.files || !input.files[0]) {
    if (err) { err.textContent = 'Please choose an image first.'; err.style.display = 'block'; }
    return;
  }
  const fd = new FormData();
  fd.append('image', input.files[0]);
  const res = await fetch('/admin/api/categories/upload', {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}, body: fd}).then(r=>r.json());
  if (!res.ok) {
    if (err) { err.textContent = res.msg || 'Upload failed.'; err.style.display = 'block'; }
    return;
  }
  setVal('cat-image-path', res.path || '');
  const name = getVal('cat-name').trim();
  if (!getVal('cat-image-alt').trim() && name) setVal('cat-image-alt', `${name} category image`);
  updateCategoryPreview();
  toast('Category image uploaded', 'success');
}

function clearCategoryImage() {
  setVal('cat-image-path', '');
  setVal('cat-image-file', '');
  updateCategoryPreview();
}

function updateCategoryPreview() {
  const preview = document.getElementById('catImagePreview');
  if (!preview) return;
  const path = getVal('cat-image-path').trim();
  if (!path) {
    preview.innerHTML = 'No image';
    return;
  }
  preview.innerHTML = `<img src="${escAttr(path)}" alt="Category image preview" style="width:100%;height:100%;object-fit:cover">`;
}

async function toggleCategory(id, el) {
  const res = await fetch(`/admin/api/categories/${id}/toggle`, {
    method:'POST',
    headers:{'X-CSRF-TOKEN':CSRF}
  }).then(r=>r.json());
  if (!res.ok) { toast(res.msg || 'Failed to update', 'error'); return; }
  el.classList.toggle('on');
  toast('Status updated', 'success');
}

async function deleteCategory(id, name) {
  if (!confirm(`Delete category "${name}"?`)) return;
  const res = await fetch(`/admin/api/categories/${id}`, {
    method:'DELETE',
    headers:{'X-CSRF-TOKEN':CSRF}
  }).then(r=>r.json());
  if (!res.ok) { toast(res.msg || 'Delete failed', 'error'); return; }
  toast('Category deleted', 'info');
  loadCategories();
}

function setVal(id, value) { const el = document.getElementById(id); if (el) el.value = value; }
function getVal(id) { const el = document.getElementById(id); return el ? String(el.value ?? '') : ''; }
function escH(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
function escAttr(s) { return escH(s); }
function escJS(s) { return String(s||'').replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/\n/g,' '); }
function toast(msg, type='info') {
  const w=document.getElementById('tw'); const t=document.createElement('div');
  t.className='toast '+type; t.textContent=msg; w.appendChild(t);
  requestAnimationFrame(()=>requestAnimationFrame(()=>t.classList.add('show')));
  setTimeout(()=>{t.classList.remove('show');setTimeout(()=>t.remove(),300);},2800);
}

loadCategories();
</script>
    </div></div></div>
</body></html>
