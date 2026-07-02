<?php
$pageTitle = 'Portfolio Manager — Admin';
$currentAdmPage = 'portfolio';
include __DIR__ . '/layout.php';
?>
<section class="adm-page-head portfolio-admin-head portfolio-admin-head--pro portfolio-admin-hero">
  <div>
    <p class="adm-kicker">Portfolio CMS</p>
    <h1>Portfolio Manager</h1>
  </div>
  <div class="adm-head-actions portfolio-head-actions">
    <a class="btn portfolio-action portfolio-action--add" href="/admin/portfolio/new"><i class="fa-solid fa-plus"></i> Add Portfolio</a>
    <a class="btn portfolio-action portfolio-action--view" href="/portfolio" target="_blank" rel="noopener"><i class="fa-regular fa-eye"></i> View Public Page</a>
    <button class="btn portfolio-action portfolio-action--refresh" type="button" onclick="loadPortfolio()"><i class="fa-solid fa-rotate"></i> Refresh</button>
  </div>
</section>

<div class="portfolio-admin-grid portfolio-admin-grid--single">
  <section class="card portfolio-admin-card portfolio-items-card portfolio-showcase-card">
    <div class="card-head portfolio-list-head">
      <div>
        <h2>Portfolio Items</h2>
        <p>Manage public work samples by product category and display order.</p>
      </div>
      <span class="portfolio-count-pill" id="portfolioCountPill">0 items</span>
    </div>
    <div id="portfolioItems" class="portfolio-admin-list"><div class="adm-empty">Loading portfolio items...</div></div>
  </section>
</div>

<script>
const esc = (s) => String(s ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
const toast = (msg, ok = true) => window.showToast ? showToast(msg, ok ? 'success' : 'error') : alert(msg);
let PORTFOLIO_ITEMS = [];

async function api(url, opts = {}) {
  const res = await fetch(url, {headers:{'Content-Type':'application/json'}, credentials:'same-origin', ...opts});
  const data = await res.json().catch(() => ({}));
  if (!res.ok || data.ok === false) throw new Error(data.msg || 'Request failed');
  return data;
}

function portfolioStatusBadge(item) {
  return item.is_active == 1 ? '<span class="badge green">Published</span>' : '<span class="badge">Hidden</span>';
}

async function loadPortfolio(){
  const box = document.getElementById('portfolioItems');
  const count = document.getElementById('portfolioCountPill');
  box.innerHTML = '<div class="adm-empty">Loading portfolio items...</div>';
  try {
    const data = await api('/admin/api/portfolio');
    PORTFOLIO_ITEMS = data.items || [];
    count.textContent = `${PORTFOLIO_ITEMS.length} item${PORTFOLIO_ITEMS.length === 1 ? '' : 's'}`;
    box.innerHTML = PORTFOLIO_ITEMS.length ? PORTFOLIO_ITEMS.map(item => `
      <article class="portfolio-admin-item portfolio-admin-item--pro portfolio-polished-card">
        <img src="${esc(item.main_image || '/assets/images/sample-products/business-cards/business-cards-1.svg')}" alt="${esc(item.image_alt || item.title)}" loading="lazy">
        <div class="portfolio-admin-item-body">
          <div class="portfolio-admin-title-row"><h3>${esc(item.title)}</h3>${portfolioStatusBadge(item)}</div>
          <p>${esc(item.category_name || 'Uncategorized')} portfolio image</p>
          <div class="portfolio-admin-meta"><span><i class="fa-regular fa-folder-open"></i> ${esc(item.category_name || 'Uncategorized')}</span><span><i class="fa-solid fa-arrow-down-1-9"></i> Sort ${esc(item.sort_order)}</span>${item.is_featured == 1 ? '<span><i class="fa-solid fa-star"></i> Featured</span>' : ''}</div>
        </div>
        <div class="portfolio-admin-actions portfolio-row-actions">
          <a class="btn sm portfolio-row-btn portfolio-row-btn--view" href="/portfolio?category=${esc(item.category_slug || '')}" target="_blank" rel="noopener"><i class="fa-regular fa-eye"></i> View</a>
          <a class="btn sm portfolio-row-btn portfolio-row-btn--edit" href="/admin/portfolio/edit/${esc(item.id)}"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
          <button class="btn sm portfolio-row-btn portfolio-row-btn--delete" onclick="deletePortfolio(${item.id})"><i class="fa-regular fa-trash-can"></i> Delete</button>
        </div>
      </article>`).join('') : '<div class="adm-empty portfolio-empty"><strong>No portfolio items yet.</strong><br><a class="btn primary" href="/admin/portfolio/new">Add your first portfolio image</a></div>';
  } catch (e) {
    count.textContent = '0 items';
    box.innerHTML = `<div class="adm-empty">${esc(e.message)}</div>`;
  }
}
async function deletePortfolio(id){ if(!confirm('Delete this portfolio item?')) return; try{ await api('/admin/api/portfolio/'+id,{method:'DELETE'}); toast('Portfolio item deleted'); loadPortfolio(); }catch(e){ toast(e.message,false); } }
loadPortfolio();
</script>
<?php include __DIR__ . '/layout-end.php'; ?>
