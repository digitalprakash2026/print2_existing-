<?php
$pageTitle = 'Portfolio Manager — Admin';
$currentAdmPage = 'portfolio';
include __DIR__ . '/layout.php';
?>
<section class="adm-page-head portfolio-admin-head portfolio-admin-head--pro">
  <div>
    <p class="adm-kicker">Portfolio CMS</p>
    <h1>Portfolio Manager</h1>
    <p class="adm-muted">Add portfolio images against product categories, manage visibility, and keep the public portfolio page fresh.</p>
  </div>
  <div class="adm-head-actions">
    <button class="btn primary" type="button" onclick="openPortfolioForm()">+ Add Portfolio</button>
    <a class="btn" href="/portfolio" target="_blank" rel="noopener">View Public Page</a>
  </div>
</section>

<div class="portfolio-admin-grid portfolio-admin-grid--single">
  <section class="card portfolio-admin-card portfolio-editor-card portfolio-inline-editor" id="portfolioEditor" hidden>
    <div class="card-head">
      <div><h2 id="portfolioFormTitle">Add Portfolio</h2><p>Select a product category, upload an image and save it instantly.</p></div>
      <button class="btn" type="button" onclick="closePortfolioForm()">Close</button>
    </div>
    <form id="portfolioForm" class="portfolio-editor-simple-grid">
      <input type="hidden" id="portfolioId" value="">
      <input type="hidden" id="main_image" required>
      <label>Product Category<select id="category_id" required><option value="">Loading categories...</option></select></label>
      <label>Portfolio Image<input id="imageFile" type="file" accept="image/png,image/jpeg,image/webp"></label>
      <label>Sort Order<input id="sort_order" type="number" value="0" min="0"></label>
      <label>Status<select id="is_active"><option value="1">Published</option><option value="0">Hidden</option></select></label>
      <label class="switch-row"><input id="is_featured" type="checkbox"> <span>Feature this work</span></label>
      <div class="portfolio-image-preview"><img id="imagePreview" src="/assets/images/sample-products/business-cards/business-cards-1.svg" alt="Portfolio preview"></div>
      <div class="form-actions stacked"><button class="btn primary portfolio-save-btn" type="submit">Save Portfolio</button><button class="btn" type="button" onclick="resetPortfolioForm()">Reset</button></div>
    </form>
  </section>

  <section class="card portfolio-admin-card portfolio-items-card">
    <div class="card-head">
      <div><h2>Portfolio Items</h2><p>Shown on the public portfolio page using product category order.</p></div>
      <div class="portfolio-card-toolbar"><button class="btn" type="button" onclick="loadPortfolio()">Refresh</button><button class="btn primary" type="button" onclick="openPortfolioForm()">+ Add Portfolio</button></div>
    </div>
    <div id="portfolioItems" class="portfolio-admin-list"><div class="adm-empty">Loading portfolio items...</div></div>
  </section>
</div>

<script>
const esc = (s) => String(s ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
const slugify = (s) => String(s || '').toLowerCase().trim().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');
const toast = (msg, ok = true) => window.showToast ? showToast(msg, ok ? 'success' : 'error') : alert(msg);
let PORTFOLIO_CATS = [];
let PORTFOLIO_ITEMS = [];
let currentPortfolioSlug = '';

async function api(url, opts = {}) {
  const res = await fetch(url, {headers:{'Content-Type':'application/json'}, credentials:'same-origin', ...opts});
  const data = await res.json().catch(() => ({}));
  if (!res.ok || data.ok === false) throw new Error(data.msg || 'Request failed');
  return data;
}
function selectedCat(){ return PORTFOLIO_CATS.find(c => String(c.id) === String(category_id.value)); }
function updatePreview(){ imagePreview.src = main_image.value || '/assets/images/sample-products/business-cards/business-cards-1.svg'; }
function openPortfolioForm(item = null){ portfolioEditor.hidden = false; portfolioEditor.scrollIntoView({behavior:'smooth', block:'start'}); if(item) fillPortfolioForm(item); else resetPortfolioForm(false); }
function closePortfolioForm(){ portfolioEditor.hidden = true; resetPortfolioForm(false); }
function resetPortfolioForm(clearImage = true){ portfolioForm.reset(); portfolioId.value=''; currentPortfolioSlug=''; portfolioFormTitle.textContent='Add Portfolio'; sort_order.value='0'; is_active.value='1'; if(clearImage){ main_image.value=''; imageFile.value=''; updatePreview(); } }
function fillPortfolioForm(item){ portfolioFormTitle.textContent='Edit Portfolio'; portfolioId.value=item.id||''; currentPortfolioSlug=item.slug||''; category_id.value=item.category_id||''; main_image.value=item.main_image||''; sort_order.value=item.sort_order||0; is_active.value=String(item.is_active ?? 1); is_featured.checked=Number(item.is_featured||0)===1; updatePreview(); }
async function loadCats(){ const data=await api('/admin/api/portfolio-categories'); PORTFOLIO_CATS=data.categories||[]; category_id.innerHTML='<option value="">Select product category</option>'+PORTFOLIO_CATS.map(c=>`<option value="${esc(c.id)}" data-name="${esc(c.name)}">${esc(c.name)}</option>`).join(''); }
async function uploadPortfolioFile(file){ const fd=new FormData(); fd.append('image',file); const res=await fetch('/admin/api/portfolio/upload',{method:'POST',body:fd,credentials:'same-origin'}); const data=await res.json(); if(!res.ok || data.ok===false) throw new Error(data.msg||'Upload failed'); return data.path; }
imageFile.addEventListener('change', async e => { const file=e.target.files[0]; if(!file) return; try{ main_image.value=await uploadPortfolioFile(file); updatePreview(); toast('Image uploaded'); }catch(err){ toast(err.message,false); } });
portfolioForm.addEventListener('submit', async e => {
  e.preventDefault();
  const cat = selectedCat();
  const editId = portfolioId.value;
  if(!cat) return toast('Please select product category', false);
  if(!main_image.value) return toast('Please upload portfolio image', false);
  const title = `${cat.name} Portfolio Image`;
  const body = {category_id:category_id.value,title,slug:editId ? currentPortfolioSlug : slugify(title + '-' + Date.now()),short_description:'',description:'',main_image:main_image.value.trim(),image_alt:title,client_name:'',project_type:cat.name,project_date:null,tags:cat.name,sort_order:Number(sort_order.value||0),is_featured:is_featured.checked,is_active:is_active.value==='1'};
  try{ await api('/admin/api/portfolio'+(editId?'/'+editId:''),{method:editId?'PUT':'POST',body:JSON.stringify(body)}); toast(editId?'Portfolio updated':'Portfolio added'); closePortfolioForm(); loadPortfolio(); }catch(err){ toast(err.message,false); }
});
async function loadPortfolio(){
  const box = document.getElementById('portfolioItems');
  box.innerHTML = '<div class="adm-empty">Loading portfolio items...</div>';
  try {
    const data = await api('/admin/api/portfolio');
    PORTFOLIO_ITEMS = data.items || [];
    box.innerHTML = PORTFOLIO_ITEMS.length ? PORTFOLIO_ITEMS.map(item => `
      <article class="portfolio-admin-item portfolio-admin-item--pro">
        <img src="${esc(item.main_image || '/assets/images/sample-products/business-cards/business-cards-1.svg')}" alt="${esc(item.image_alt || item.title)}" loading="lazy">
        <div class="portfolio-admin-item-body">
          <div class="portfolio-admin-title-row"><h3>${esc(item.title)}</h3><span class="badge ${item.is_active == 1 ? 'green' : ''}">${item.is_active == 1 ? 'Published' : 'Hidden'}</span></div>
          <p>${esc(item.category_name || 'Uncategorized')} portfolio image</p>
          <div class="portfolio-admin-meta"><span><i class="fa-regular fa-folder-open"></i> ${esc(item.category_name || 'Uncategorized')}</span><span>Sort: ${esc(item.sort_order)}</span>${item.is_featured == 1 ? '<span>Featured</span>' : ''}</div>
        </div>
        <div class="portfolio-admin-actions"><a class="btn sm" href="/portfolio?category=${esc(item.category_slug || '')}" target="_blank" rel="noopener">View</a><button class="btn sm primary" type="button" onclick='openPortfolioForm(${JSON.stringify(item).replace(/'/g,"&#39;")})'>Edit</button><button class="btn sm danger" onclick="deletePortfolio(${item.id})">Delete</button></div>
      </article>`).join('') : '<div class="adm-empty">No portfolio items yet. Click Add Portfolio to upload your first image.</div>';
  } catch (e) { box.innerHTML = `<div class="adm-empty">${esc(e.message)}</div>`; }
}
async function deletePortfolio(id){ if(!confirm('Delete this portfolio item?')) return; try{ await api('/admin/api/portfolio/'+id,{method:'DELETE'}); toast('Portfolio item deleted'); loadPortfolio(); }catch(e){ toast(e.message,false); } }
(async()=>{ try{ await loadCats(); await loadPortfolio(); }catch(e){ toast(e.message,false); } })();
</script>
<?php include __DIR__ . '/layout-end.php'; ?>
