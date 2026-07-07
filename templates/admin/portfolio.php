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
    <div class="portfolio-tabs-wrap" id="portfolioTabsWrap" aria-label="Portfolio category filters"><div id="portfolioTabs" class="portfolio-tabs"><button class="portfolio-tab is-active" type="button">All</button></div></div>
    <div id="portfolioItems" class="portfolio-admin-list"><div class="adm-empty">Loading portfolio items...</div></div>
  </section>
</div>

<script>
const esc = (s) => String(s ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
const toast = (msg, ok = true) => window.showToast ? showToast(msg, ok ? 'success' : 'error') : alert(msg);
let PORTFOLIO_ITEMS = [];
let PORTFOLIO_CATS = [];
let activePortfolioCategory = 'all';

async function api(url, opts = {}) {
  const res = await fetch(url, {headers:{'Content-Type':'application/json'}, credentials:'same-origin', ...opts});
  const data = await res.json().catch(() => ({}));
  if (!res.ok || data.ok === false) throw new Error(data.msg || 'Request failed');
  return data;
}

function portfolioStatusBadge(item) {
  return item.is_active == 1 ? '<span class="badge green">Published</span>' : '<span class="badge">Hidden</span>';
}
function isOtherCategory(cat){ return String(cat?.slug || '').toLowerCase() === 'others' || String(cat?.name || '').trim().toLowerCase() === 'others'; }
function visiblePortfolioItems(){ return activePortfolioCategory === 'all' ? PORTFOLIO_ITEMS : PORTFOLIO_ITEMS.filter(item => String(item.category_id || '') === String(activePortfolioCategory)); }
function renderPortfolioTabs(){
  const tabs = document.getElementById('portfolioTabs');
  const counts = PORTFOLIO_ITEMS.reduce((acc, item) => { const key = String(item.category_id || ''); acc[key] = (acc[key] || 0) + 1; return acc; }, {});
  tabs.innerHTML = [`<button class="portfolio-tab ${activePortfolioCategory === 'all' ? 'is-active' : ''}" type="button" data-category="all">All <span>${PORTFOLIO_ITEMS.length}</span></button>`]
    .concat(PORTFOLIO_CATS.map(cat => `<button class="portfolio-tab ${String(activePortfolioCategory) === String(cat.id) ? 'is-active' : ''}" type="button" data-category="${esc(cat.id)}">${esc(cat.name)} <span>${counts[String(cat.id)] || 0}</span></button>`)).join('');
  tabs.querySelectorAll('button').forEach(btn => btn.addEventListener('click', () => { activePortfolioCategory = btn.dataset.category || 'all'; renderPortfolioTabs(); renderPortfolioItems(); }));
}
function portfolioCard(item){ return `
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
  </article>`; }
function renderPortfolioItems(){
  const box = document.getElementById('portfolioItems');
  const count = document.getElementById('portfolioCountPill');
  const items = visiblePortfolioItems();
  count.textContent = `${items.length} item${items.length === 1 ? '' : 's'}`;
  box.innerHTML = items.length ? items.map(portfolioCard).join('') : `<div class="adm-empty portfolio-empty"><strong>No portfolio items in this category.</strong><br><a class="btn primary" href="/admin/portfolio/new">Add portfolio image</a></div>`;
}
async function loadPortfolio(){
  const box = document.getElementById('portfolioItems');
  const count = document.getElementById('portfolioCountPill');
  box.innerHTML = '<div class="adm-empty">Loading portfolio items...</div>';
  try {
    const [catData, itemData] = await Promise.all([api('/admin/api/portfolio-categories'), api('/admin/api/portfolio')]);
    PORTFOLIO_CATS = (catData.categories || []).filter(cat => !isOtherCategory(cat));
    PORTFOLIO_ITEMS = (itemData.items || []).filter(item => String(item.category_slug || '').toLowerCase() !== 'others' && String(item.category_name || '').trim().toLowerCase() !== 'others');
    if (activePortfolioCategory !== 'all' && !PORTFOLIO_CATS.some(cat => String(cat.id) === String(activePortfolioCategory))) activePortfolioCategory = 'all';
    renderPortfolioTabs();
    renderPortfolioItems();
  } catch (e) {
    count.textContent = '0 items';
    box.innerHTML = `<div class="adm-empty">${esc(e.message)}</div>`;
  }
}
async function deletePortfolio(id){ if(!confirm('Delete this portfolio item?')) return; try{ await api('/admin/api/portfolio/'+id,{method:'DELETE'}); toast('Portfolio item deleted'); loadPortfolio(); }catch(e){ toast(e.message,false); } }
loadPortfolio();
</script>
<?php include __DIR__ . '/layout-end.php'; ?>
