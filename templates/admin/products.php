<?php
$pageTitle = 'Products — RCS Admin';
$currentAdmPage = 'products';
include __DIR__ . '/layout.php';
?>
<div class="adm-pt">Products</div>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
  <div id="prodCount" style="font-size:13px;color:var(--text2)">Loading…</div>
  <button class="btn btn-blue btn-sm" onclick="showAddProductModal()">+ Add Product</button>
</div>

<!-- Category filter -->
<div class="chip-row" style="margin-bottom:14px" id="prodCatFilter">
  <div class="chip on" data-cat="all" onclick="filterProds('all',this)">All</div>
</div>

<div id="prodList">
  <div style="text-align:center;padding:44px;color:var(--text2)"><div class="pay-spin" style="border-top-color:var(--blue);margin:0 auto 12px"></div>Loading products…</div>
</div>

<!-- Add/Edit Product Modal -->
<div class="modal-bg" id="m-prod">
  <div class="modal-box" style="max-width:640px">
    <div class="modal-pill"></div>
    <div class="modal-hdr">
      <div class="modal-ttl" id="m-prod-ttl">Add Product</div>
      <button class="modal-cls" onclick="closeM('m-prod')">✕</button>
    </div>
    <div class="modal-bdy">
      <input type="hidden" id="ep-id">
      <div class="f2">
        <div class="fg"><label>Product Name *</label><input class="fi" id="ep-name" placeholder="Business Card Printing"></div>
        <div class="fg">
          <label>Category *</label>
          <select class="fi fi-sel" id="ep-cat"></select>
        </div>
      </div>
      <div class="fg"><label>Description</label><textarea class="fi" id="ep-desc" style="height:76px" placeholder="Describe the product…"></textarea></div>
      <div class="fg"><label>Specs (one per line — Label: Value)</label><textarea class="fi" id="ep-specs" style="height:76px" placeholder="Size: A4&#10;Min Qty: 1000&#10;Delivery: 2–3 Days"></textarea></div>
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text2);margin-bottom:9px;margin-top:4px">Product Images (URLs)</div>
      <div class="fg"><label>Main Image URL *</label><input class="fi" id="ep-i1" placeholder="https://images.unsplash.com/…" oninput="previewImg('ep-i1','pv1')"><div id="pv1" style="margin-top:7px"></div></div>
      <div class="f2">
        <div class="fg"><label>Image 2</label><input class="fi" id="ep-i2" oninput="previewImg('ep-i2','pv2')"><div id="pv2" style="margin-top:5px"></div></div>
        <div class="fg"><label>Image 3</label><input class="fi" id="ep-i3" oninput="previewImg('ep-i3','pv3')"><div id="pv3" style="margin-top:5px"></div></div>
      </div>
      <div class="fg"><label>Image 4</label><input class="fi" id="ep-i4" oninput="previewImg('ep-i4','pv4')"><div id="pv4" style="margin-top:5px"></div></div>
      <div style="font-size:12px;color:var(--text2);background:var(--blue-bg);border-radius:8px;padding:10px 13px;margin-bottom:14px">
        💡 After saving, go to <strong>Pricing</strong> to add quality options and set prices per quantity.
      </div>
      <div style="display:flex;gap:9px">
        <button class="btn btn-blue" onclick="saveProd()" style="padding:12px 26px;border-radius:10px">Save Product ✓</button>
        <button class="btn btn-outline" onclick="closeM('m-prod')" style="padding:12px 18px;border-radius:10px">Cancel</button>
      </div>
    </div>
  </div>
</div>

<script>
let allProds = [], allCats = [];

async function loadProds() {
  const [pr, cr] = await Promise.all([
    fetch('/admin/api/products').then(r=>r.json()),
    fetch('/admin/api/categories').then(r=>r.json()),
  ]);
  allProds = pr.products || [];
  allCats  = cr.categories || [];

  // Populate category filter
  const fc = document.getElementById('prodCatFilter');
  const cats = [...new Set(allProds.map(p=>p.category_name).filter(Boolean))];
  cats.forEach(c => {
    const btn = document.createElement('div');
    btn.className = 'chip'; btn.dataset.cat = c; btn.textContent = c;
    btn.onclick = () => filterProds(c, btn);
    fc.appendChild(btn);
  });

  // Populate category dropdown in modal
  const sel = document.getElementById('ep-cat');
  sel.innerHTML = allCats.map(c=>`<option value="${c.id}">${escH(c.name)}</option>`).join('');

  renderProds(allProds);
}

function filterProds(cat, btn) {
  document.querySelectorAll('#prodCatFilter .chip').forEach(c=>c.classList.remove('on'));
  btn.classList.add('on');
  renderProds(cat === 'all' ? allProds : allProds.filter(p=>p.category_name===cat));
}

function renderProds(prods) {
  document.getElementById('prodCount').textContent = prods.length + ' products';
  if (!prods.length) {
    document.getElementById('prodList').innerHTML = `<div style="text-align:center;padding:44px;color:var(--text2)"><div style="font-size:40px;margin-bottom:9px">📦</div><div>No products found</div></div>`;
    return;
  }
  document.getElementById('prodList').innerHTML = prods.map(p => `
    <div class="aprod">
      <div class="aprod-img"><img src="${escH(p.primary_image||'')}" onerror="this.style.opacity=.3" loading="lazy"></div>
      <div class="aprod-info">
        <div class="aprod-name">${escH(p.name)}</div>
        <div class="aprod-meta">${escH(p.category_name||'')} · ${p.is_active?'<span style="color:var(--green)">Active</span>':'<span style="color:var(--red)">Inactive</span>'}</div>
        <div style="font-size:11px;color:var(--text3);margin-top:2px">Min price: ₹${Number(p.min_price||0).toLocaleString('en-IN')}</div>
      </div>
      <div class="aprod-acts">
        <button class="ic-btn" onclick="editProd(${p.id})" title="Edit">
          <svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
        </button>
        <div class="tog ${p.is_active?'on':''}" onclick="toggleProd(${p.id},this)" title="${p.is_active?'Deactivate':'Activate'}"><div class="tog-k"></div></div>
        <button class="ic-btn del" onclick="deleteProd(${p.id},'${escH(p.name)}')" title="Delete">
          <svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
        </button>
      </div>
    </div>
  `).join('');
}

function showAddProductModal() {
  document.getElementById('m-prod-ttl').textContent = 'Add Product';
  document.getElementById('ep-id').value = '';
  ['ep-name','ep-desc','ep-specs','ep-i1','ep-i2','ep-i3','ep-i4'].forEach(id=>{document.getElementById(id).value='';});
  ['pv1','pv2','pv3','pv4'].forEach(id=>{const e=document.getElementById(id);if(e)e.innerHTML='';});
  openM('m-prod');
}

function editProd(id) {
  const p = allProds.find(x=>x.id===id);
  if (!p) return;
  document.getElementById('m-prod-ttl').textContent = 'Edit Product';
  document.getElementById('ep-id').value = id;
  document.getElementById('ep-name').value = p.name || '';
  document.getElementById('ep-cat').value = p.category_id || '';
  document.getElementById('ep-desc').value = p.description || '';
  // Specs
  fetch(`/admin/api/products/${id}`).then(r=>r.json()).then(res => {
    if (res.ok && res.product) {
      const specs = (res.product.specs||[]).map(s=>s.label+': '+s.value).join('\n');
      document.getElementById('ep-specs').value = specs;
      const imgs = res.product.images || [];
      ['ep-i1','ep-i2','ep-i3','ep-i4'].forEach((fid,i) => {
        const val = imgs[i]?.url || '';
        document.getElementById(fid).value = val;
        previewImg(fid, 'pv'+(i+1));
      });
    }
  });
  openM('m-prod');
}

async function saveProd() {
  const id = document.getElementById('ep-id').value;
  const name = document.getElementById('ep-name').value.trim();
  const catId = document.getElementById('ep-cat').value;
  if (!name || !catId) { toast('Name and category required', 'error'); return; }

  const specsRaw = document.getElementById('ep-specs').value.trim().split('\n').filter(Boolean);
  const specs = specsRaw.map(s => {
    const [label, ...rest] = s.split(':');
    return { label: label.trim(), value: rest.join(':').trim() };
  }).filter(s=>s.label);

  const imgs = ['ep-i1','ep-i2','ep-i3','ep-i4'].map(id=>document.getElementById(id).value.trim()).filter(Boolean);

  const data = { name, category_id: parseInt(catId), description: document.getElementById('ep-desc').value.trim(), specs, is_active: 1 };

  const url = id ? `/admin/api/products/${id}` : '/admin/api/products';
  const method = id ? 'PUT' : 'POST';
  const res = await fetch(url, { method, headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'<?= htmlspecialchars($csrf??'') ?>'}, body: JSON.stringify(data) }).then(r=>r.json());

  if (res.ok) {
    const prodId = res.id;
    // Save images
    if (imgs.length) {
      await fetch(`/admin/api/products/${prodId}/images`, { method:'DELETE', headers: {'X-CSRF-TOKEN':'<?= htmlspecialchars($csrf??'') ?>'} }).catch(()=>{});
      for (let i = 0; i < imgs.length; i++) {
        await fetch(`/admin/api/products/${prodId}/images`, {
          method: 'POST',
          headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'<?= htmlspecialchars($csrf??'') ?>'},
          body: JSON.stringify({ url: imgs[i], is_primary: i===0?1:0, sort_order: i })
        });
      }
    }
    closeM('m-prod');
    toast(id ? 'Product updated!' : 'Product added!', 'success');
    loadProds();
  } else {
    toast(res.msg || 'Failed', 'error');
  }
}

async function toggleProd(id, togEl) {
  await fetch(`/admin/api/products/${id}/toggle`, { method:'POST', headers:{'X-CSRF-TOKEN':'<?= htmlspecialchars($csrf??'') ?>'} });
  togEl.classList.toggle('on');
  toast('Status updated', 'success');
}

async function deleteProd(id, name) {
  if (!confirm(`Delete "${name}"? This cannot be undone.`)) return;
  const res = await fetch(`/admin/api/products/${id}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':'<?= htmlspecialchars($csrf??'') ?>'} }).then(r=>r.json());
  if (res.ok) { toast('Product deleted', 'info'); loadProds(); }
  else toast(res.msg || 'Failed', 'error');
}

function previewImg(inputId, previewId) {
  const url = document.getElementById(inputId)?.value?.trim();
  const p = document.getElementById(previewId);
  if (!p) return;
  p.innerHTML = url ? `<img src="${url}" style="height:${inputId==='ep-i1'?'80':'54'}px;border-radius:8px;border:1px solid var(--border)" onerror="this.style.opacity=.3">` : '';
}

function openM(id) { document.getElementById(id).classList.add('show'); }
function closeM(id) { document.getElementById(id).classList.remove('show'); }
function escH(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function toast(msg, type='info') {
  const w=document.getElementById('tw'); const t=document.createElement('div');
  t.className='toast '+type; t.textContent=msg; w.appendChild(t);
  requestAnimationFrame(()=>requestAnimationFrame(()=>t.classList.add('show')));
  setTimeout(()=>{t.classList.remove('show');setTimeout(()=>t.remove(),300);},2800);
}

loadProds();
</script>
    </div></div></div>
</body></html>
