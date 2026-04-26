<?php
$pageTitle = 'All Products — RCS Admin';
$currentAdmPage = 'products';
include __DIR__ . '/layout.php';
?>
<div class="adm-pt">All Products</div>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;gap:10px;flex-wrap:wrap">
  <div id="prodCount" style="font-size:13px;color:var(--text2)">Loading…</div>
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <button class="btn btn-outline btn-sm" type="button" onclick="openCatModal()">+ Add Category</button>
    <a href="/admin/products/new" class="btn btn-blue btn-sm">+ Add Product</a>
  </div>
</div>

<div class="chip-row" style="margin-bottom:14px" id="prodCatFilter">
  <div class="chip on" data-cat="all" onclick="filterProds('all',this)">All</div>
</div>

<div id="prodList">
  <div style="text-align:center;padding:44px;color:var(--text2)"><div class="pay-spin" style="border-top-color:var(--blue);margin:0 auto 12px"></div>Loading products…</div>
</div>

<div id="catModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:1300;align-items:center;justify-content:center;padding:18px">
  <div style="width:min(520px,100%);background:var(--white);border:1px solid var(--border);border-radius:12px;padding:16px">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
      <div style="font-family:var(--fd);font-size:16px;font-weight:700">Add Category</div>
      <button class="btn btn-outline btn-sm" type="button" onclick="closeCatModal()">Close ✕</button>
    </div>
    <div class="fg"><label>Category Name *</label><input id="cat-name" class="fi" placeholder="Visiting Cards"></div>
    <div class="f2">
      <div class="fg"><label>Code Prefix</label><input id="cat-prefix" class="fi" placeholder="RCSVC"></div>
      <div class="fg"><label>Icon</label><input id="cat-icon" class="fi" placeholder="💳"></div>
    </div>
    <div class="fg"><label>Sort Order</label><input id="cat-sort" type="number" class="fi" value="0"></div>
    <div id="catErr" style="display:none;font-size:12px;color:var(--red);margin-bottom:10px"></div>
    <button class="btn btn-blue btn-sm" type="button" onclick="createCategory()">Save Category</button>
  </div>
</div>

<script>
let allProds = [];

async function loadProds() {
  try {
    const res = await fetch('/admin/api/products', {credentials:'same-origin'}).then(r=>r.json());
    if (!res.ok && !Array.isArray(res.products)) {
      throw new Error(res.msg || 'Failed to load products');
    }
    allProds = res.products || [];

    const fc = document.getElementById('prodCatFilter');
    fc.querySelectorAll('.chip:not([data-cat="all"])').forEach(el=>el.remove());
    const cats = [...new Set(allProds.map(p=>p.category_name).filter(Boolean))];
    cats.forEach(c => {
      const btn = document.createElement('div');
      btn.className = 'chip'; btn.dataset.cat = c; btn.textContent = c;
      btn.onclick = () => filterProds(c, btn);
      fc.appendChild(btn);
    });

    renderProds(allProds);
  } catch (err) {
    console.error(err);
    document.getElementById('prodCount').textContent = '0 products';
    document.getElementById('prodList').innerHTML = `<div style="text-align:center;padding:44px;color:var(--red)"><div style="font-size:36px;margin-bottom:9px">⚠️</div><div>Could not load products.</div><div style="font-size:12px;color:var(--text2);margin-top:8px">Please check DB schema and try again.</div></div>`;
  }
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
        ${p.product_code ? `<div style="font-size:11px;color:var(--blue);font-weight:700;margin-top:2px">Code: ${escH(p.product_code)}</div>` : ''}
        <div style="font-size:11px;color:var(--text3);margin-top:2px">Min price: ₹${Number(p.min_price||0).toLocaleString('en-IN')} · Design fee: ₹${Number(p.design_fee||0).toLocaleString('en-IN')}</div>
      </div>
      <div class="aprod-acts">
        <a class="ic-btn" href="/admin/products/new?id=${p.id}" title="Edit">
          <svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
        </a>
        <div class="tog ${p.is_active?'on':''}" onclick="toggleProd(${p.id},this)" title="${p.is_active?'Deactivate':'Activate'}"><div class="tog-k"></div></div>
        <button class="ic-btn del" onclick="deleteProd(${p.id},'${escH(p.name)}')" title="Delete">
          <svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
        </button>
      </div>
    </div>
  `).join('');
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

function openCatModal() {
  const m = document.getElementById('catModal');
  if (m) m.style.display = 'flex';
}
function closeCatModal() {
  const m = document.getElementById('catModal');
  if (m) m.style.display = 'none';
}
async function createCategory() {
  const name = document.getElementById('cat-name')?.value.trim() || '';
  const code_prefix = document.getElementById('cat-prefix')?.value.trim().toUpperCase() || '';
  const icon = document.getElementById('cat-icon')?.value.trim() || '🖨️';
  const sort_order = parseInt(document.getElementById('cat-sort')?.value || '0', 10) || 0;
  const err = document.getElementById('catErr');
  if (!name) {
    if (err) { err.textContent = 'Category name is required.'; err.style.display = 'block'; }
    return;
  }
  if (err) err.style.display = 'none';
  const res = await fetch('/admin/api/categories', {
    method: 'POST',
    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'<?= htmlspecialchars($csrf??'') ?>'},
    body: JSON.stringify({name, code_prefix, icon, sort_order})
  }).then(r=>r.json());
  if (!res.ok) {
    if (err) { err.textContent = res.msg || 'Could not create category.'; err.style.display = 'block'; }
    return;
  }
  closeCatModal();
  ['cat-name','cat-prefix','cat-icon'].forEach(id=>{ const el=document.getElementById(id); if (el) el.value=''; });
  const s = document.getElementById('cat-sort'); if (s) s.value='0';
  toast('Category created', 'success');
  loadProds();
}

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
