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

<form id="portfolioForm" class="card portfolio-editor-card">
  <div class="portfolio-editor-grid">
    <div class="portfolio-editor-main">
      <label>Title<input id="title" required placeholder="Creative Business Card Design"></label>
      <label>Slug<input id="slug" placeholder="creative-business-card-design"></label>
      <label>Short Description<textarea id="short_description" rows="3" placeholder="Premium brand identity print work for a local business."></textarea></label>
      <label>Full Description<textarea id="description" rows="8" placeholder="Project notes, material details, print finish, client requirement..."></textarea></label>
      <div class="form-row two">
        <label>Client Name<input id="client_name" placeholder="RCS Client"></label>
        <label>Project Type<input id="project_type" placeholder="Offset / Digital / Packaging"></label>
      </div>
      <div class="form-row two">
        <label>Project Date<input id="project_date" type="date"></label>
        <label>Tags<input id="tags" placeholder="branding, premium, print"></label>
      </div>
    </div>
    <aside class="portfolio-editor-side">
      <label>Category<select id="category_id"><option value="">Loading...</option></select></label>
      <label>Main Image<input id="imageFile" type="file" accept="image/png,image/jpeg,image/webp"></label>
      <input id="main_image" placeholder="/uploads/portfolio/image.webp">
      <div class="portfolio-image-preview"><img id="imagePreview" src="/assets/images/sample-products/business-cards/business-cards-1.svg" alt="Portfolio image preview"></div>
      <label>Image Alt Text<input id="image_alt" placeholder="Creative business card design"></label>
      <div class="form-row two">
        <label>Sort Order<input id="sort_order" type="number" value="10"></label>
        <label>Status<select id="is_active"><option value="1">Published</option><option value="0">Hidden</option></select></label>
      </div>
      <label class="switch-row"><input id="is_featured" type="checkbox"> <span>Feature this item first</span></label>
      <div class="form-actions stacked"><button class="btn primary portfolio-save-btn" type="submit">Save Portfolio Item</button><button class="btn portfolio-save-publish" type="button" onclick="publishAndSave()">Publish & Save</button><a class="btn" href="/portfolio" target="_blank" rel="noopener">View Public Portfolio</a></div>
    </aside>
  </div>
  <?php if ($portfolioEditId): ?>
  <section class="portfolio-gallery-admin">
    <div class="portfolio-gallery-admin-head"><div><h2>Detail Page Gallery</h2><p>Add unlimited large images for the portfolio detail page.</p></div><label class="btn primary gallery-upload-btn">+ Upload Gallery Images<input id="galleryFiles" type="file" accept="image/png,image/jpeg,image/webp" multiple hidden></label></div>
    <div id="galleryList" class="portfolio-gallery-admin-list"><div class="adm-empty">Loading gallery...</div></div>
  </section>
  <?php else: ?>
  <section class="portfolio-gallery-admin portfolio-gallery-disabled"><h2>Detail Page Gallery</h2><p>Save the portfolio item first, then edit it to add unlimited gallery images.</p></section>
  <?php endif; ?>
</form>

<script>
const editId = <?= (int)$portfolioEditId ?>;
const esc = (s) => String(s ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
const slugify = (s) => String(s || '').toLowerCase().trim().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');
const toast = (msg, ok = true) => window.showToast ? showToast(msg, ok ? 'success' : 'error') : alert(msg);
async function api(url, opts={}){ const res=await fetch(url,{headers:{'Content-Type':'application/json'},...opts}); const data=await res.json().catch(()=>({})); if(!res.ok || data.ok===false) throw new Error(data.msg||'Request failed'); return data; }
function setVal(id,val){ const el=document.getElementById(id); if(el) el.value = val ?? ''; }
function updatePreview(){ document.getElementById('imagePreview').src = document.getElementById('main_image').value || '/assets/images/sample-products/business-cards/business-cards-1.svg'; }
async function loadCategories(){ const data=await api('/admin/api/portfolio-categories'); category_id.innerHTML='<option value="">Uncategorized</option>'+(data.categories||[]).map(c=>`<option value="${c.id}">${esc(c.name)}</option>`).join(''); }
async function loadItem(){ if(!editId) return; const data=await api('/admin/api/portfolio/'+editId); const item=data.item||{}; ['title','slug','short_description','description','main_image','image_alt','client_name','project_type','project_date','tags','sort_order'].forEach(k=>setVal(k,item[k])); category_id.value=item.category_id||''; is_active.value=item.is_active == 1 ? '1':'0'; is_featured.checked=item.is_featured == 1; updatePreview(); }

title.addEventListener('input', e => { if(!slug.dataset.touched) slug.value=slugify(e.target.value); if(!image_alt.value) image_alt.value=e.target.value; });
slug.addEventListener('input', e => { e.target.dataset.touched='1'; e.target.value=slugify(e.target.value); });
main_image.addEventListener('input', updatePreview);
imageFile.addEventListener('change', async e => {
  const file=e.target.files[0]; if(!file) return;
  try{ main_image.value=await uploadPortfolioFile(file); updatePreview(); toast('Image uploaded'); }catch(err){ toast(err.message,false); }
});
function portfolioPayload(){ return {title:title.value.trim(),slug:slug.value.trim(),category_id:category_id.value||null,short_description:short_description.value.trim(),description:description.value.trim(),main_image:main_image.value.trim(),image_alt:image_alt.value.trim(),client_name:client_name.value.trim(),project_type:project_type.value.trim(),project_date:project_date.value||null,tags:tags.value.trim(),sort_order:Number(sort_order.value||0),is_featured:is_featured.checked,is_active:is_active.value==='1'}; }
async function savePortfolio(){ const data=await api('/admin/api/portfolio'+(editId?'/'+editId:''),{method:editId?'PUT':'POST',body:JSON.stringify(portfolioPayload())}); toast('Portfolio item saved'); if(!editId && data.id) location.href='/admin/portfolio/edit/'+data.id; return data; }
function publishAndSave(){ is_active.value='1'; savePortfolio().catch(err=>toast(err.message,false)); }
portfolioForm.addEventListener('submit', async e => { e.preventDefault(); try{ await savePortfolio(); }catch(err){ toast(err.message,false); } });

async function uploadPortfolioFile(file){ const fd=new FormData(); fd.append('image',file); const res=await fetch('/admin/api/portfolio/upload',{method:'POST',body:fd}); const data=await res.json(); if(!res.ok || data.ok===false) throw new Error(data.msg||'Upload failed'); return data.path; }
async function loadGallery(){ if(!editId || !document.getElementById('galleryList')) return; const data=await api('/admin/api/portfolio/'+editId+'/images'); const box=document.getElementById('galleryList'); const images=data.images||[]; box.innerHTML=images.length?images.map(img=>`<article class="portfolio-gallery-admin-item"><img src="${esc(img.image_path)}" alt="${esc(img.image_alt||'Gallery image')}"><div><input value="${esc(img.image_alt||'')}" placeholder="Alt text" data-field="image_alt"><input value="${esc(img.caption||'')}" placeholder="Caption" data-field="caption"><input type="number" value="${esc(img.sort_order||0)}" data-field="sort_order"></div><div class="portfolio-gallery-actions"><button class="btn sm" type="button" onclick="saveGalleryImage(${img.id}, this)">Save</button><button class="btn sm danger" type="button" onclick="deleteGalleryImage(${img.id})">Delete</button></div><input type="hidden" data-field="image_path" value="${esc(img.image_path)}"><input type="hidden" data-field="is_active" value="${esc(img.is_active)}"></article>`).join(''):'<div class="adm-empty">No gallery images yet. Upload multiple images to build the detail page.</div>'; }
async function saveGalleryImage(id, btn){ const row=btn.closest('.portfolio-gallery-admin-item'); const val=f=>row.querySelector(`[data-field="${f}"]`)?.value||''; await api('/admin/api/portfolio/images/'+id,{method:'PUT',body:JSON.stringify({image_path:val('image_path'),image_alt:val('image_alt'),caption:val('caption'),sort_order:Number(val('sort_order')||0),is_active:val('is_active')==='0'?0:1})}); toast('Gallery image saved'); }
async function deleteGalleryImage(id){ if(!confirm('Delete this gallery image?')) return; await api('/admin/api/portfolio/images/'+id,{method:'DELETE'}); toast('Gallery image deleted'); loadGallery(); }
document.getElementById('galleryFiles')?.addEventListener('change', async e=>{ const files=[...e.target.files]; if(!files.length) return; try{ for(const file of files){ const path=await uploadPortfolioFile(file); await api('/admin/api/portfolio/'+editId+'/images',{method:'POST',body:JSON.stringify({image_path:path,image_alt:title.value,caption:title.value,sort_order:0,is_active:1})}); } toast('Gallery images uploaded'); e.target.value=''; loadGallery(); }catch(err){ toast(err.message,false); } });
(async()=>{ try{ await loadCategories(); await loadItem(); await loadGallery(); }catch(e){ toast(e.message,false); } })();
</script>
<?php include __DIR__ . '/layout-end.php'; ?>
