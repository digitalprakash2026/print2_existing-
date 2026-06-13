<?php
$pageTitle = 'All Products — RCS Admin';
$currentAdmPage = 'products';
include __DIR__ . '/layout.php';
?>
<div class="adm-pt">All Products</div>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;gap:10px;flex-wrap:wrap">
  <div id="prodCount" style="font-size:13px;color:var(--text2)">Loading…</div>
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <a href="/admin/categories" class="btn btn-outline btn-sm">Manage Categories</a>
    <a href="/admin/import/sample/products" class="btn btn-outline btn-sm">Sample CSV</a>
    <a href="/admin/export/products" class="btn btn-outline btn-sm">Export Products</a>
    <a href="/admin/products/new" class="btn btn-blue btn-sm">+ Add Product</a>
  </div>
</div>

<div class="fsec" style="margin-bottom:14px">
  <div style="display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap">
    <div style="flex:1;min-width:220px">
      <div style="font-size:12px;font-weight:800;color:var(--ink);margin-bottom:6px">Import Products CSV</div>
      <input id="prodImportFile" type="file" class="fi" accept=".csv,text/csv">
    </div>
    <div style="min-width:170px">
      <div style="font-size:12px;font-weight:800;color:var(--ink);margin-bottom:6px">Import Mode</div>
      <select id="prodImportMode" class="fi fi-sel">
        <option value="create_update">Create + Update</option>
        <option value="create">Create only</option>
        <option value="update">Update only</option>
      </select>
    </div>
    <button class="btn btn-blue btn-sm" type="button" onclick="importProductsCsv()">Import Products</button>
  </div>
  <div id="prodImportResult" style="display:none;margin-top:10px;font-size:12px;color:var(--text2)"></div>
</div>

<div class="chip-row" style="margin-bottom:14px" id="prodCatFilter">
  <div class="chip on" data-cat="all" onclick="filterProds('all',this)">All</div>
</div>

<div id="prodList">
  <div style="text-align:center;padding:44px;color:var(--text2)"><div class="pay-spin" style="border-top-color:var(--blue);margin:0 auto 12px"></div>Loading products…</div>
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

async function importProductsCsv() {
  const fileInput = document.getElementById('prodImportFile');
  const resultEl = document.getElementById('prodImportResult');
  const file = fileInput?.files?.[0];
  if (!file) { toast('Please choose a products CSV file', 'error'); return; }
  const fd = new FormData();
  fd.append('file', file);
  fd.append('mode', document.getElementById('prodImportMode')?.value || 'create_update');
  resultEl.style.display = 'block';
  resultEl.textContent = 'Importing products...';
  try {
    const res = await fetch('/admin/api/import/products', { method:'POST', body:fd, headers:{'X-CSRF-TOKEN':'<?= htmlspecialchars($csrf??'') ?>'} }).then(r=>r.json());
    if (!res.ok) throw new Error(res.msg || 'Import failed');
    const errors = (res.errors || []).slice(0, 5).map(e => `Row ${e.row}: ${escH(e.message)}`).join('<br>');
    resultEl.innerHTML = `Total ${res.total || 0} rows · Created ${res.created || 0} · Updated ${res.updated || 0} · Skipped ${res.skipped || 0} · Failed ${res.failed || 0}${errors ? '<br><strong>Errors:</strong><br>' + errors : ''}`;
    toast('Products import completed', (res.failed || 0) > 0 ? 'info' : 'success');
    fileInput.value = '';
    loadProds();
  } catch (err) {
    resultEl.textContent = err.message || 'Import failed';
    toast(resultEl.textContent, 'error');
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
