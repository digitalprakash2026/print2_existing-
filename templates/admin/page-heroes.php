<?php
$pageTitle = 'Page Heroes — Admin';
$currentAdmPage = 'page-heroes';
include __DIR__ . '/layout.php';
?>
<section class="adm-page-head page-heroes-head">
  <div>
    <p class="adm-kicker">Page Hero Manager</p>
    <h1>Page Heroes</h1>
    <p class="adm-muted">Manage the background images used in the common breadcrumb/title hero section.</p>
  </div>
</section>

<section class="card page-heroes-card">
  <div class="page-heroes-toolbar">
    <div>
      <h2>Page Backgrounds</h2>
      <p>Upload one premium banner image for each public page hero.</p>
    </div>
    <button class="btn" type="button" onclick="loadHeroes()">Refresh</button>
  </div>
  <div id="pageHeroList" class="page-heroes-grid" aria-live="polite"></div>
</section>

<script>
const pageHeroList = document.getElementById('pageHeroList');
const toast = (msg, ok = true) => window.showToast ? showToast(msg, ok ? 'success' : 'error') : alert(msg);
const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
async function api(url, opts={}){ const res=await fetch(url,{headers:{'Content-Type':'application/json'},...opts}); const data=await res.json().catch(()=>({})); if(!res.ok || data.ok===false) throw new Error(data.msg||'Request failed'); return data; }
async function uploadHero(file){ const fd=new FormData(); fd.append('image', file); const res=await fetch('/admin/api/page-heroes/upload',{method:'POST',body:fd}); const data=await res.json().catch(()=>({})); if(!res.ok || data.ok===false) throw new Error(data.msg||'Upload failed'); return data.path; }
function heroCard(row){
  const bg = row.background_image || row.fallback_image || '/assets/images/sample-products/brochures/brochures-2.svg';
  return `<article class="page-hero-admin-card" data-key="${esc(row.page_key)}">
    <div class="page-hero-admin-preview" style="background-image:url('${esc(bg)}')"><span>${esc(row.title)}</span></div>
    <div class="page-hero-admin-body">
      <h3>${esc(row.title)}</h3>
      <p>${esc(row.description || 'Common page hero background')}</p>
      <input class="hero-path" value="${esc(row.background_image || '')}" placeholder="Upload or paste image path">
      <div class="page-hero-admin-actions">
        <label class="btn">Upload Image<input class="hero-file" type="file" accept="image/png,image/jpeg,image/webp" hidden></label>
        <button class="btn primary hero-save" type="button">Save</button>
      </div>
    </div>
  </article>`;
}
async function loadHeroes(){
  pageHeroList.innerHTML = '<div class="adm-muted">Loading page heroes...</div>';
  try{
    const data = await api('/admin/api/page-heroes');
    pageHeroList.innerHTML = (data.heroes || []).map(heroCard).join('') || '<div class="adm-muted">No page heroes configured.</div>';
  }catch(err){ pageHeroList.innerHTML = '<div class="adm-error">'+esc(err.message)+'</div>'; }
}
pageHeroList.addEventListener('change', async (e) => {
  const input = e.target.closest('.hero-file');
  if(!input || !input.files[0]) return;
  const card = input.closest('.page-hero-admin-card');
  try{
    const path = await uploadHero(input.files[0]);
    card.querySelector('.hero-path').value = path;
    card.querySelector('.page-hero-admin-preview').style.backgroundImage = `url('${path}')`;
    toast('Image uploaded');
  }catch(err){ toast(err.message, false); }
});
pageHeroList.addEventListener('click', async (e) => {
  const btn = e.target.closest('.hero-save');
  if(!btn) return;
  const card = btn.closest('.page-hero-admin-card');
  const key = card.dataset.key;
  const background_image = card.querySelector('.hero-path').value.trim();
  try{
    await api('/admin/api/page-heroes/' + encodeURIComponent(key), {method:'PUT', body:JSON.stringify({background_image, is_active:true})});
    toast('Page hero saved');
  }catch(err){ toast(err.message, false); }
});
loadHeroes();
</script>
<?php include __DIR__ . '/layout-end.php'; ?>
