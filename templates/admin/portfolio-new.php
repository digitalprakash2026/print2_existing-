<?php
$pageTitle = !empty($portfolioEditId) ? 'Edit Portfolio Item — Admin' : 'Add Portfolio Item — Admin';
$currentAdmPage = 'portfolio';
$portfolioEditId = (int)($portfolioEditId ?? 0);
include __DIR__ . '/layout.php';
?>
<section class="adm-page-head portfolio-admin-head portfolio-editor-hero">
  <div>
    <p class="adm-kicker">Portfolio CMS</p>
    <h1><?= $portfolioEditId ? 'Edit Portfolio Item' : 'Add Portfolio Item' ?></h1>
    <p class="adm-muted">Choose a product category, upload a polished portfolio image, and control exactly how it appears on the public portfolio page.</p>
  </div>
  <div class="adm-head-actions portfolio-head-actions">
    <a class="btn portfolio-action portfolio-action--back" href="/admin/portfolio"><i class="fa-solid fa-arrow-left"></i> Back to Portfolio</a>
    <a class="btn portfolio-action portfolio-action--view" href="/portfolio" target="_blank" rel="noopener"><i class="fa-regular fa-eye"></i> View Public Page</a>
    <button class="btn portfolio-action portfolio-action--add" type="submit" form="portfolioForm"><i class="fa-solid fa-floppy-disk"></i> Save Portfolio</button>
  </div>
</section>

<form id="portfolioForm" class="portfolio-editor-pro">
  <section class="card portfolio-editor-card portfolio-editor-main-card">
    <div class="card-head">
      <div>
        <h2>Portfolio Details</h2>
        <p>Category selection automatically creates a clear portfolio title and SEO-friendly image alt text.</p>
      </div>
      <span class="badge green" id="portfolioModeBadge"><?= $portfolioEditId ? 'Editing' : 'New' ?></span>
    </div>

    <div class="portfolio-editor-form-grid">
      <label>Product Category
        <select id="category_id" required><option value="">Loading categories...</option></select>
      </label>
      <label>Sort Order
        <input id="sort_order" type="number" value="0" min="0" inputmode="numeric">
      </label>
      <label>Status
        <select id="is_active"><option value="1">Published</option><option value="0">Hidden</option></select>
      </label>
      <label class="switch-row portfolio-feature-switch"><input id="is_featured" type="checkbox"> <span>Feature this portfolio item</span></label>
      <input id="main_image" type="hidden" required>
    </div>

    <div class="portfolio-editor-actions">
      <button class="btn portfolio-action portfolio-action--add" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Portfolio</button>
      <a class="btn portfolio-action portfolio-action--back" href="/admin/portfolio"><i class="fa-solid fa-xmark"></i> Cancel</a>
    </div>
  </section>

  <aside class="card portfolio-editor-card portfolio-upload-card">
    <div class="portfolio-upload-drop" onclick="imageFile.click()" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();imageFile.click();}" tabindex="0" role="button" aria-label="Upload portfolio image">
      <input id="imageFile" type="file" accept="image/png,image/jpeg,image/webp" hidden>
      <div class="portfolio-image-preview portfolio-image-preview--large"><img id="imagePreview" src="/assets/images/sample-products/business-cards/business-cards-1.svg" alt="Portfolio image preview"></div>
      <div class="portfolio-upload-copy">
        <strong><i class="fa-solid fa-cloud-arrow-up"></i> Upload Portfolio Image</strong>
        <span>PNG, JPG or WebP. Click this card to choose an image.</span>
      </div>
    </div>
    <div class="portfolio-upload-state" id="uploadState">No image uploaded yet.</div>
  </aside>
</form>

<script>
const editId = <?= (int)$portfolioEditId ?>;
let currentSlug = '';
let currentItem = null;
const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
const toast = (msg, ok = true) => window.showToast ? showToast(msg, ok ? 'success' : 'error') : alert(msg);
const slugify = (s) => String(s || '').toLowerCase().trim().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');
async function api(url, opts={}){ const res=await fetch(url,{headers:{'Content-Type':'application/json'}, credentials:'same-origin', ...opts}); const data=await res.json().catch(()=>({})); if(!res.ok || data.ok===false) throw new Error(data.msg||'Request failed'); return data; }
function selectedCategoryName(){ const opt = category_id.selectedOptions[0]; return opt?.dataset.name || opt?.textContent || 'Portfolio'; }
function updatePreview(){ document.getElementById('imagePreview').src = document.getElementById('main_image').value || '/assets/images/sample-products/business-cards/business-cards-1.svg'; }
function setUploadState(message, ok = true){ uploadState.textContent = message; uploadState.classList.toggle('is-ok', ok); }
function isOtherCategory(cat){ return String(cat?.slug || '').toLowerCase() === 'others' || String(cat?.name || '').trim().toLowerCase() === 'others'; }
async function loadCategories(){ const data=await api('/admin/api/portfolio-categories'); const cats=(data.categories||[]).filter(cat => !isOtherCategory(cat)); category_id.innerHTML='<option value="">Select product category</option>'+cats.map(c=>`<option value="${esc(c.id)}" data-name="${esc(c.name)}">${esc(c.name)}</option>`).join(''); }
async function loadItem(){ if(!editId) return; const data=await api('/admin/api/portfolio/'+editId); const item=data.item||{}; currentItem=item; currentSlug=item.slug||''; category_id.value=item.category_id||''; main_image.value=item.main_image||''; sort_order.value=item.sort_order||0; is_active.value=String(item.is_active ?? 1); is_featured.checked=Number(item.is_featured||0)===1; updatePreview(); if(item.main_image) setUploadState('Current image loaded. Upload a new file to replace it.'); }
async function uploadPortfolioFile(file){ const fd=new FormData(); fd.append('image',file); setUploadState('Uploading image...', true); const res=await fetch('/admin/api/portfolio/upload',{method:'POST',body:fd,credentials:'same-origin'}); const data=await res.json(); if(!res.ok || data.ok===false) throw new Error(data.msg||'Upload failed'); return data.path; }
imageFile.addEventListener('change', async e => { const file=e.target.files[0]; if(!file) return; try{ main_image.value=await uploadPortfolioFile(file); updatePreview(); setUploadState('Image uploaded successfully. Ready to save.'); toast('Image uploaded'); }catch(err){ setUploadState(err.message || 'Upload failed', false); toast(err.message,false); } });
portfolioForm.addEventListener('submit', async e => {
  e.preventDefault();
  const categoryName = selectedCategoryName();
  const title = categoryName + ' Portfolio Image';
  const body = {category_id:category_id.value||null,title,slug:(editId && currentSlug ? currentSlug : slugify(title + '-' + Date.now())),short_description:'',description:'',main_image:main_image.value.trim(),image_alt:title,client_name:'',project_type:categoryName,project_date:null,tags:categoryName,sort_order:Number(sort_order.value||0),is_featured:is_featured.checked,is_active:is_active.value==='1'};
  if(!body.category_id) return toast('Please select category', false);
  if(!body.main_image) return toast('Please upload main image', false);
  try{ const data=await api('/admin/api/portfolio'+(editId?'/'+editId:''),{method:editId?'PUT':'POST',body:JSON.stringify(body)}); toast(editId?'Portfolio image updated':'Portfolio image saved'); location.href='/admin/portfolio'; }catch(err){ toast(err.message,false); }
});
(async()=>{ try{ await loadCategories(); await loadItem(); }catch(e){ toast(e.message,false); } })();
</script>
<?php include __DIR__ . '/layout-end.php'; ?>
