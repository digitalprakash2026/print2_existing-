<?php
$pageTitle = 'Portfolio Manager — Admin';
$currentAdmPage = 'portfolio';
include __DIR__ . '/layout.php';
?>
<section class="adm-page-head portfolio-admin-head">
  <div>
    <p class="adm-kicker">Portfolio CMS</p>
    <h1>Portfolio Manager</h1>
    <p class="adm-muted">Manage premium portfolio work, categories, ordering, featured status and public visibility.</p>
  </div>
  <div class="adm-head-actions">
    <a class="btn primary" href="/admin/portfolio/new">+ New Portfolio Item</a>
    <a class="btn" href="/portfolio" target="_blank" rel="noopener">View Public Page</a>
  </div>
</section>

<div class="portfolio-admin-grid">
  <section class="card portfolio-admin-card portfolio-items-card">
    <div class="card-head">
      <div><h2>Portfolio Items</h2><p>Images are shown on the public portfolio page in this order.</p></div>
      <button class="btn" type="button" onclick="loadPortfolio()">Refresh</button>
    </div>
    <div id="portfolioItems" class="portfolio-admin-list"><div class="adm-empty">Loading portfolio items...</div></div>
  </section>

  <aside class="card portfolio-admin-card portfolio-cats-card">
    <div class="card-head"><div><h2>Categories</h2><p>Used as public filter tabs.</p></div></div>
    <form id="catForm" class="portfolio-cat-form">
      <input type="hidden" id="catId" value="">
      <label>Category Name<input id="catName" required placeholder="Business Cards"></label>
      <label>Slug<input id="catSlug" placeholder="business-cards"></label>
      <label>FontAwesome Icon<input id="catIcon" placeholder="fa-id-card-clip"></label>
      <div class="form-row two">
        <label>Sort Order<input id="catSort" type="number" value="10"></label>
        <label>Status<select id="catActive"><option value="1">Active</option><option value="0">Hidden</option></select></label>
      </div>
      <div class="form-actions"><button class="btn primary" type="submit">Save Category</button><button class="btn" type="button" onclick="resetCatForm()">Reset</button></div>
    </form>
    <div id="portfolioCats" class="portfolio-cat-list"><div class="adm-empty">Loading categories...</div></div>
  </aside>
</div>

<script>
const esc = (s) => String(s ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
const slugify = (s) => String(s || '').toLowerCase().trim().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');
const toast = (msg, ok = true) => window.showToast ? showToast(msg, ok ? 'success' : 'error') : alert(msg);

async function api(url, opts = {}) {
  const res = await fetch(url, {headers:{'Content-Type':'application/json'}, ...opts});
  const data = await res.json().catch(() => ({}));
  if (!res.ok || data.ok === false) throw new Error(data.msg || 'Request failed');
  return data;
}

async function loadPortfolio(){
  const box = document.getElementById('portfolioItems');
  box.innerHTML = '<div class="adm-empty">Loading portfolio items...</div>';
  try {
    const data = await api('/admin/api/portfolio');
    const items = data.items || [];
    box.innerHTML = items.length ? items.map(item => `
      <article class="portfolio-admin-item">
        <img src="${esc(item.main_image || '/assets/images/sample-products/business-cards/business-cards-1.svg')}" alt="${esc(item.image_alt || item.title)}" loading="lazy">
        <div class="portfolio-admin-item-body">
          <div class="portfolio-admin-title-row"><h3>${esc(item.title)}</h3><span class="badge ${item.is_active == 1 ? 'green' : ''}">${item.is_active == 1 ? 'Published' : 'Hidden'}</span></div>
          <p>${esc(item.short_description || 'No short description added yet.')}</p>
          <div class="portfolio-admin-meta"><span><i class="fa-regular fa-folder-open"></i> ${esc(item.category_name || 'Uncategorized')}</span><span>Sort: ${esc(item.sort_order)}</span>${item.is_featured == 1 ? '<span>Featured</span>' : ''}</div>
        </div>
        <div class="portfolio-admin-actions"><a class="btn sm" href="/admin/portfolio/edit/${item.id}">Edit</a><button class="btn sm danger" onclick="deletePortfolio(${item.id})">Delete</button></div>
      </article>`).join('') : '<div class="adm-empty">No portfolio items yet. Add your first work.</div>';
  } catch (e) { box.innerHTML = `<div class="adm-empty">${esc(e.message)}</div>`; }
}

async function loadCats(){
  const box = document.getElementById('portfolioCats');
  try {
    const data = await api('/admin/api/portfolio-categories');
    const cats = data.categories || [];
    box.innerHTML = cats.length ? cats.map(cat => `
      <div class="portfolio-cat-row">
        <span><i class="fa-solid ${esc(cat.icon || 'fa-folder')}"></i></span>
        <div><strong>${esc(cat.name)}</strong><small>${esc(cat.slug)} · Sort ${esc(cat.sort_order)} · ${cat.is_active == 1 ? 'Active' : 'Hidden'}</small></div>
        <button class="btn sm" type="button" onclick='editCat(${JSON.stringify(cat).replace(/'/g,'&#39;')})'>Edit</button>
        <button class="btn sm danger" type="button" onclick="deleteCat(${cat.id})">Delete</button>
      </div>`).join('') : '<div class="adm-empty">No categories yet.</div>';
  } catch (e) { box.innerHTML = `<div class="adm-empty">${esc(e.message)}</div>`; }
}

function resetCatForm(){ document.getElementById('catForm').reset(); document.getElementById('catId').value=''; document.getElementById('catSort').value='10'; document.getElementById('catActive').value='1'; }
function editCat(cat){ cat = typeof cat === 'string' ? JSON.parse(cat) : cat; document.getElementById('catId').value=cat.id; document.getElementById('catName').value=cat.name||''; document.getElementById('catSlug').value=cat.slug||''; document.getElementById('catIcon').value=cat.icon||''; document.getElementById('catSort').value=cat.sort_order||10; document.getElementById('catActive').value=cat.is_active == 1 ? '1':'0'; }
async function deleteCat(id){ if(!confirm('Delete this category? Items must be reassigned first.')) return; try{ await api('/admin/api/portfolio-categories/'+id,{method:'DELETE'}); toast('Category deleted'); loadCats(); loadPortfolio(); }catch(e){ toast(e.message,false); } }
async function deletePortfolio(id){ if(!confirm('Delete this portfolio item?')) return; try{ await api('/admin/api/portfolio/'+id,{method:'DELETE'}); toast('Portfolio item deleted'); loadPortfolio(); }catch(e){ toast(e.message,false); } }

document.getElementById('catName').addEventListener('input', e => { if(!document.getElementById('catSlug').dataset.touched) document.getElementById('catSlug').value = slugify(e.target.value); });
document.getElementById('catSlug').addEventListener('input', e => { e.target.dataset.touched = '1'; e.target.value = slugify(e.target.value); });
document.getElementById('catForm').addEventListener('submit', async e => {
  e.preventDefault();
  const id = document.getElementById('catId').value;
  const body = {name:catName.value.trim(), slug:catSlug.value.trim(), icon:catIcon.value.trim(), sort_order:Number(catSort.value||0), is_active:catActive.value === '1'};
  try{ await api('/admin/api/portfolio-categories'+(id?'/'+id:''), {method:id?'PUT':'POST', body:JSON.stringify(body)}); toast('Category saved'); resetCatForm(); loadCats(); }catch(err){ toast(err.message,false); }
});

loadCats();
loadPortfolio();
</script>
<?php include __DIR__ . '/layout-end.php'; ?>
