<?php
$pageTitle = 'Blogs — RCS Admin';
$currentAdmPage = 'blogs';
include __DIR__ . '/layout.php';
?>
<div class="adm-pt">Blog Manager</div>

<div class="fsec" style="max-width:1180px">
  <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:14px">
    <div style="font-size:13px;color:var(--text2)">Manage rich blog posts for the homepage and public blog detail pages.</div>
    <a class="btn btn-blue btn-sm" href="/admin/blogs/new">+ New Blog</a>
  </div>

  <div id="blogErr" style="display:none;padding:10px 12px;border:1px solid var(--red-mid);background:var(--red-bg);color:var(--red);border-radius:10px;font-size:12px;margin-bottom:10px"></div>
  <div id="blogList"></div>
</div>

<div class="fsec blog-banner-admin" style="max-width:1180px;margin-top:16px">
  <div class="blog-banner-admin-head">
    <div>
      <div style="font-weight:900;color:var(--text);font-size:15px">Blog Sidebar Banner</div>
      <div style="font-size:12px;color:var(--text2);margin-top:3px">This clickable image appears below “Need Printing Help?” and “More Blogs” on blog article pages.</div>
    </div>
    <span id="blogBannerStatus" class="blog-banner-status">Loading…</span>
  </div>
  <div class="blog-banner-admin-grid">
    <div class="blog-banner-preview" id="blogBannerPreview">
      <span>No banner selected</span>
    </div>
    <div class="blog-banner-fields">
      <div class="f2">
        <div class="fg">
          <label>Banner Image</label>
          <input type="file" class="fi" id="blog-banner-file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" onchange="uploadBlogBanner()">
          <div style="font-size:11px;color:var(--text3);margin-top:5px">Choose image — upload starts automatically.</div>
        </div>
        <div class="fg">
          <label>Image Path</label>
          <input class="fi" id="blog-banner-image" placeholder="/uploads/blogs/...">
        </div>
      </div>
      <div class="f2">
        <div class="fg">
          <label>Click URL</label>
          <input class="fi" id="blog-banner-url" placeholder="/contact or /product/visiting-card">
        </div>
        <div class="fg">
          <label>Alt Text</label>
          <input class="fi" id="blog-banner-alt" placeholder="RCS Print offer banner">
        </div>
      </div>
      <div class="f2">
        <div class="fg">
          <label>Status</label>
          <select class="fi fi-sel" id="blog-banner-active"><option value="1">Active</option><option value="0">Inactive</option></select>
        </div>
        <div class="fg">
          <label>Open Link</label>
          <select class="fi fi-sel" id="blog-banner-new-tab"><option value="0">Same tab</option><option value="1">New tab</option></select>
        </div>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
        <button class="btn btn-blue btn-sm" type="button" id="blogBannerSaveBtn" onclick="saveBlogBanner()">Save Sidebar Banner</button>
        <button class="btn btn-outline btn-sm" type="button" onclick="clearBlogBanner()">Clear</button>
      </div>
    </div>
  </div>
</div>

<script>
let blogs = [];
const CSRF = '<?= htmlspecialchars($csrf??'') ?>';

function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
function toastMsg(msg,type='info'){ const w=document.getElementById('tw'); const t=document.createElement('div'); t.className='toast '+type; t.textContent=msg; w.appendChild(t); requestAnimationFrame(()=>requestAnimationFrame(()=>t.classList.add('show'))); setTimeout(()=>{t.classList.remove('show'); setTimeout(()=>t.remove(),300);},2600); }
function showErr(msg=''){ const e=document.getElementById('blogErr'); if(!e) return; if(!msg){e.style.display='none';return;} e.textContent=msg; e.style.display='block'; }

async function loadBlogs() {
  const res = await fetch('/admin/api/blogs').then(r=>r.json());
  if (!res.ok) { showErr(res.msg || 'Could not load blogs'); return; }
  showErr('');
  blogs = res.blogs || [];
  renderBlogs();
}

function renderBlogs() {
  const box = document.getElementById('blogList');
  if (!blogs.length) {
    box.innerHTML = `<div style="padding:18px;border:1px dashed var(--border);border-radius:10px;color:var(--text2);font-size:13px">No blogs added yet. Use the New Blog button to create your first post.</div>`;
    return;
  }
  box.innerHTML = blogs.map((b) => {
    const img = b.featured_image ? `<img src="${esc(b.featured_image)}" style="width:98px;height:64px;object-fit:cover;border-radius:8px;border:1px solid var(--border)">` : `<div style="width:98px;height:64px;border-radius:8px;border:1px dashed var(--border);display:flex;align-items:center;justify-content:center;color:var(--text3);font-size:11px">No Image</div>`;
    const id = Number(b.id || 0);
    return `
      <div style="display:grid;grid-template-columns:98px 1fr auto;gap:12px;align-items:center;padding:10px;border:1px solid var(--border);border-radius:10px;margin-bottom:8px;background:#fff">
        ${img}
        <div>
          <div style="font-weight:800;font-size:13px;margin-bottom:2px">${esc(b.title)}</div>
          <div style="font-size:11px;color:var(--text2)">/${esc(b.slug)} · ${esc(b.category || 'Blog')} · ${Number(b.is_featured) ? 'Featured' : 'Hidden from home'} · ${Number(b.is_active) ? 'Published' : 'Draft'}</div>
          <div style="font-size:11px;color:var(--text3);margin-top:3px">${esc(b.excerpt || '').slice(0,120)}</div>
        </div>
        <div style="display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end">
          <a class="btn btn-outline btn-sm" href="/blog/${esc(b.slug)}" target="_blank">View</a>
          <a class="btn btn-outline btn-sm" href="/admin/blogs/edit/${id}">Edit</a>
          <button class="btn btn-outline btn-sm" onclick="delBlog(${id})">Delete</button>
        </div>
      </div>`;
  }).join('');
}

async function delBlog(id) {
  if (!confirm('Delete this blog?')) return;
  const res = await fetch(`/admin/api/blogs/${id}`, {method:'DELETE', headers:{'X-CSRF-TOKEN':CSRF}}).then(r=>r.json());
  if (!res.ok) { showErr(res.msg || 'Delete failed'); return; }
  toastMsg('Blog deleted', 'info');
  await loadBlogs();
}

function setBlogBannerStatus(msg, type='info') {
  const el = document.getElementById('blogBannerStatus');
  if (!el) return;
  el.textContent = msg;
  el.dataset.type = type;
}
function renderBlogBannerPreview() {
  const img = document.getElementById('blog-banner-image')?.value.trim() || '';
  const alt = document.getElementById('blog-banner-alt')?.value.trim() || 'Blog sidebar banner';
  const box = document.getElementById('blogBannerPreview');
  if (!box) return;
  box.innerHTML = img ? `<img src="${esc(img)}" alt="${esc(alt)}">` : '<span>No banner selected</span>';
}
async function loadBlogBanner() {
  try {
    const res = await fetch('/admin/api/settings', {credentials:'same-origin'}).then(r=>r.json());
    const s = res.settings || {};
    document.getElementById('blog-banner-image').value = s.blog_sidebar_banner_image || '';
    document.getElementById('blog-banner-url').value = s.blog_sidebar_banner_url || '';
    document.getElementById('blog-banner-alt').value = s.blog_sidebar_banner_alt || '';
    document.getElementById('blog-banner-active').value = String(Number(s.blog_sidebar_banner_active ?? 0) ? 1 : 0);
    document.getElementById('blog-banner-new-tab').value = String(Number(s.blog_sidebar_banner_new_tab ?? 0) ? 1 : 0);
    renderBlogBannerPreview();
    setBlogBannerStatus('Ready', 'success');
  } catch (e) {
    setBlogBannerStatus('Could not load banner settings', 'error');
  }
}
async function uploadBlogBanner() {
  const input = document.getElementById('blog-banner-file');
  const file = input?.files?.[0];
  if (!file) return;
  input.disabled = true;
  setBlogBannerStatus('Uploading…', 'info');
  const fd = new FormData();
  fd.append('image', file);
  try {
    const res = await fetch('/admin/api/blogs/upload', {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}, body:fd, credentials:'same-origin'}).then(r=>r.json());
    if (!res.ok) {
      setBlogBannerStatus(res.msg || 'Upload failed', 'error');
      return;
    }
    document.getElementById('blog-banner-image').value = res.path || '';
    renderBlogBannerPreview();
    setBlogBannerStatus('Image uploaded automatically', 'success');
    toastMsg('Banner image uploaded', 'success');
  } finally {
    input.disabled = false;
  }
}
async function saveBlogBanner() {
  const btn = document.getElementById('blogBannerSaveBtn');
  btn.disabled = true;
  btn.textContent = 'Saving...';
  const payload = {
    blog_sidebar_banner_image: document.getElementById('blog-banner-image').value.trim(),
    blog_sidebar_banner_url: document.getElementById('blog-banner-url').value.trim(),
    blog_sidebar_banner_alt: document.getElementById('blog-banner-alt').value.trim(),
    blog_sidebar_banner_active: document.getElementById('blog-banner-active').value,
    blog_sidebar_banner_new_tab: document.getElementById('blog-banner-new-tab').value,
  };
  try {
    const res = await fetch('/admin/api/settings', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF}, body:JSON.stringify(payload), credentials:'same-origin'}).then(r=>r.json());
    if (!res.ok) {
      setBlogBannerStatus(res.msg || 'Save failed', 'error');
      return;
    }
    setBlogBannerStatus('Saved', 'success');
    toastMsg('Sidebar banner saved', 'success');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Save Sidebar Banner';
  }
}
function clearBlogBanner() {
  document.getElementById('blog-banner-image').value = '';
  document.getElementById('blog-banner-url').value = '';
  document.getElementById('blog-banner-alt').value = '';
  document.getElementById('blog-banner-active').value = '0';
  renderBlogBannerPreview();
}
['blog-banner-image','blog-banner-alt'].forEach(id => document.getElementById(id)?.addEventListener('input', renderBlogBannerPreview));
loadBlogs();
loadBlogBanner();
</script>
    </div></div></div>
</body></html>
