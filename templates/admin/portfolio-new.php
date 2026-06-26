<?php
$pageTitle = !empty($portfolioEditId) ? 'Edit Portfolio Item — Admin' : 'New Portfolio Item — Admin';
$currentAdmPage = 'portfolio';
$portfolioEditId = (int)($portfolioEditId ?? 0);
include __DIR__ . '/layout.php';
?>
<section class="adm-page-head portfolio-admin-head">
  <div>
    <p class="adm-kicker">Portfolio CMS</p>
    <h1><?= $portfolioEditId ? 'Edit Portfolio Item' : 'Add Portfolio Item' ?></h1>
    <p class="adm-muted">Upload a strong visual, choose a category and control how this work appears publicly.</p>
  </div>
  <div class="adm-head-actions"><a class="btn" href="/admin/portfolio">← Back to Portfolio</a><button class="btn primary" type="submit" form="portfolioForm">Save Changes</button></div>
</section>

<form id="portfolioForm" class="card portfolio-editor-card portfolio-editor-simple">
  <div class="portfolio-editor-simple-grid">
    <label>Category<select id="category_id" required><option value="">Loading...</option></select></label>
    <label>Main Image<input id="imageFile" type="file" accept="image/png,image/jpeg,image/webp"></label>
    <input id="main_image" type="hidden" required>
    <div class="portfolio-image-preview"><img id="imagePreview" src="/assets/images/sample-products/business-cards/business-cards-1.svg" alt="Portfolio image preview"></div>
    <div class="form-actions stacked"><button class="btn primary portfolio-save-btn" type="submit">Save Portfolio Image</button><a class="btn" href="/admin/portfolio">Back to Portfolio</a></div>
  </div>
</form>

<script>
const editId = <?= (int)$portfolioEditId ?>;
let currentSlug = '';
const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;',"'":'&#039;'}[m]));
const toast = (msg, ok = true) => window.showToast ? showToast(msg, ok ? 'success' : 'error') : alert(msg);
const slugify = (s) => String(s || '').toLowerCase().trim().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');
async function api(url, opts={}){ const res=await fetch(url,{headers:{'Content-Type':'application/json'},...opts}); const data=await res.json().catch(()=>({})); if(!res.ok || data.ok===false) throw new Error(data.msg||'Request failed'); return data; }
function updatePreview(){ document.getElementById('imagePreview').src = document.getElementById('main_image').value || '/assets/images/sample-products/business-cards/business-cards-1.svg'; }
async function loadCategories(){ const data=await api('/admin/api/portfolio-categories'); category_id.innerHTML='<option value="">Select category</option>'+(data.categories||[]).map(c=>`<option value="${esc(c.id)}" data-name="${esc(c.name)}">${esc(c.name)}</option>`).join(''); }
async function loadItem(){ if(!editId) return; const data=await api('/admin/api/portfolio/'+editId); const item=data.item||{}; currentSlug=item.slug||''; category_id.value=item.category_id||''; main_image.value=item.main_image||''; updatePreview(); }
async function uploadPortfolioFile(file){ const fd=new FormData(); fd.append('image',file); const res=await fetch('/admin/api/portfolio/upload',{method:'POST',body:fd}); const data=await res.json(); if(!res.ok || data.ok===false) throw new Error(data.msg||'Upload failed'); return data.path; }
imageFile.addEventListener('change', async e => { const file=e.target.files[0]; if(!file) return; try{ main_image.value=await uploadPortfolioFile(file); updatePreview(); toast('Image uploaded'); }catch(err){ toast(err.message,false); } });
portfolioForm.addEventListener('submit', async e => {
  e.preventDefault();
  const opt = category_id.selectedOptions[0];
  const categoryName = opt?.dataset.name || opt?.textContent || 'Portfolio';
  const title = categoryName + ' Portfolio Image';
  const body = {category_id:category_id.value||null,title,slug:(editId && currentSlug ? currentSlug : slugify(title + '-' + Date.now())),short_description:'',description:'',main_image:main_image.value.trim(),image_alt:title,client_name:'',project_type:'',project_date:null,tags:'',sort_order:0,is_featured:false,is_active:true};
  if(!body.category_id) return toast('Please select category', false);
  if(!body.main_image) return toast('Please upload main image', false);
  try{ const data=await api('/admin/api/portfolio'+(editId?'/'+editId:''),{method:editId?'PUT':'POST',body:JSON.stringify(body)}); toast('Portfolio image saved'); if(!editId && data.id) location.href='/admin/portfolio/edit/'+data.id; }catch(err){ toast(err.message,false); }
});
(async()=>{ try{ await loadCategories(); await loadItem(); }catch(e){ toast(e.message,false); } })();
</script>
<?php include __DIR__ . '/layout-end.php'; ?>
