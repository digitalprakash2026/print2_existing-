<?php
$pageTitle = 'Blogs — RCS Admin';
$currentAdmPage = 'blogs';
include __DIR__ . '/layout.php';
?>
<div class="adm-pt">Blog Manager</div>

<div class="fsec" style="max-width:1180px">
  <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:14px">
    <div style="font-size:13px;color:var(--text2)">Create rich blog posts for the homepage and public blog detail pages.</div>
    <button class="btn btn-blue btn-sm" onclick="newBlog()">+ New Blog</button>
  </div>

  <div id="blogErr" style="display:none;padding:10px 12px;border:1px solid var(--red-mid);background:var(--red-bg);color:var(--red);border-radius:10px;font-size:12px;margin-bottom:10px"></div>

  <div id="blogList"></div>
  <hr style="border:none;border-top:1px solid var(--border);margin:18px 0">

  <div style="font-weight:700;margin-bottom:10px" id="blogFormTitle">Add Blog</div>
  <div class="f2">
    <div class="fg"><label>Title *</label><input class="fi" id="blog-title" placeholder="How to Choose the Perfect Business Card Finish" oninput="autoSlug()"></div>
    <div class="fg"><label>Slug <span style="color:var(--text3);font-weight:500">(auto URL)</span></label><input class="fi" id="blog-slug" placeholder="how-to-choose-the-perfect-business-card-finish"></div>
  </div>

  <div class="fg"><label>Short Description / Excerpt</label><textarea class="fi" id="blog-excerpt" style="height:70px" placeholder="Short text shown on homepage blog card"></textarea></div>

  <div class="f2">
    <div class="fg"><label>Category</label><input class="fi" id="blog-category" placeholder="Print Tips"></div>
    <div class="fg">
      <label>Badge Theme</label>
      <select class="fi fi-sel" id="blog-theme">
        <option value="purple">Purple</option>
        <option value="orange">Orange</option>
        <option value="green">Green</option>
      </select>
    </div>
  </div>

  <div class="f2">
    <div class="fg"><label>Author</label><input class="fi" id="blog-author" placeholder="RCS Print Team"></div>
    <div class="fg"><label>Published At</label><input class="fi" id="blog-published" type="datetime-local"></div>
  </div>

  <div class="f2">
    <div class="fg">
      <label>Featured Image</label>
      <input type="file" class="fi" id="blog-image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
      <div style="font-size:11px;color:var(--text3);margin-top:5px">Recommended ratio: 16:10 or 900×560.</div>
    </div>
    <div class="fg"><label>Image Path</label><input class="fi" id="blog-image-path" placeholder="/uploads/blogs/..."></div>
  </div>

  <div class="f2">
    <div class="fg"><label>Image Alt</label><input class="fi" id="blog-alt" placeholder="Premium printed business cards"></div>
    <div class="fg"><label>Sort Order</label><input type="number" class="fi" id="blog-sort" value="0"></div>
  </div>

  <div class="f2">
    <div class="fg"><label>Meta Title</label><input class="fi" id="blog-meta-title" placeholder="SEO title"></div>
    <div class="fg"><label>Meta Description</label><input class="fi" id="blog-meta-desc" placeholder="SEO description"></div>
  </div>

  <div class="f2">
    <div class="fg">
      <label>Show on Homepage?</label>
      <select class="fi fi-sel" id="blog-featured"><option value="1">Yes</option><option value="0">No</option></select>
    </div>
    <div class="fg">
      <label>Status</label>
      <select class="fi fi-sel" id="blog-active"><option value="1">Published</option><option value="0">Draft / Inactive</option></select>
    </div>
  </div>

  <div class="fg">
    <label>Blog Content *</label>
    <div class="blog-editor-toolbar" aria-label="Rich text editor toolbar">
      <button type="button" onclick="editorCmd('bold')"><strong>B</strong></button>
      <button type="button" onclick="editorCmd('italic')"><em>I</em></button>
      <button type="button" onclick="editorCmd('underline')"><u>U</u></button>
      <button type="button" onclick="editorBlock('h2')">H2</button>
      <button type="button" onclick="editorBlock('h3')">H3</button>
      <button type="button" onclick="editorCmd('insertUnorderedList')">• List</button>
      <button type="button" onclick="editorCmd('insertOrderedList')">1. List</button>
      <button type="button" onclick="editorBlock('blockquote')">Quote</button>
      <button type="button" onclick="editorLink()">Link</button>
      <button type="button" onclick="editorCmd('removeFormat')">Clear</button>
    </div>
    <div id="blog-editor" class="blog-rich-editor" contenteditable="true" aria-label="Blog content editor"></div>
  </div>

  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px">
    <button class="btn btn-blue btn-sm" onclick="saveBlog()" id="blogSaveBtn">Save Blog</button>
    <button class="btn btn-outline btn-sm" onclick="resetForm()">Reset</button>
    <button class="btn btn-outline btn-sm" onclick="uploadBlogImage()">Upload Image</button>
  </div>
</div>

<script>
let blogs = [];
let editId = 0;
let slugTouched = false;
const CSRF = '<?= htmlspecialchars($csrf??'') ?>';

function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
function toastMsg(msg,type='info'){ const w=document.getElementById('tw'); const t=document.createElement('div'); t.className='toast '+type; t.textContent=msg; w.appendChild(t); requestAnimationFrame(()=>requestAnimationFrame(()=>t.classList.add('show'))); setTimeout(()=>{t.classList.remove('show'); setTimeout(()=>t.remove(),300);},2600); }
function showErr(msg=''){ const e=document.getElementById('blogErr'); if(!e) return; if(!msg){e.style.display='none';return;} e.textContent=msg; e.style.display='block'; }
function slugify(s){ return String(s||'').toLowerCase().trim().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'') || 'blog-post'; }
function autoSlug(){ if(!editId && !slugTouched) document.getElementById('blog-slug').value = slugify(document.getElementById('blog-title').value); }
document.getElementById('blog-slug').addEventListener('input',()=>{ slugTouched = true; });

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
    box.innerHTML = `<div style="padding:18px;border:1px dashed var(--border);border-radius:10px;color:var(--text2);font-size:13px">No blogs added yet. Run the migration to seed default blogs.</div>`;
    return;
  }
  box.innerHTML = blogs.map((b) => {
    const img = b.featured_image ? `<img src="${esc(b.featured_image)}" style="width:98px;height:64px;object-fit:cover;border-radius:8px;border:1px solid var(--border)">` : `<div style="width:98px;height:64px;border-radius:8px;border:1px dashed var(--border);display:flex;align-items:center;justify-content:center;color:var(--text3);font-size:11px">No Image</div>`;
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
          <button class="btn btn-outline btn-sm" onclick="editBlog(${Number(b.id)})">Edit</button>
          <button class="btn btn-outline btn-sm" onclick="delBlog(${Number(b.id)})">Delete</button>
        </div>
      </div>`;
  }).join('');
}

function editorCmd(cmd){ document.execCommand(cmd, false, null); document.getElementById('blog-editor').focus(); }
function editorBlock(tag){ document.execCommand('formatBlock', false, tag); document.getElementById('blog-editor').focus(); }
function editorLink(){ const url = prompt('Enter URL'); if(url) document.execCommand('createLink', false, url); document.getElementById('blog-editor').focus(); }

function toLocalInput(value){
  if(!value) return '';
  return String(value).replace(' ', 'T').slice(0,16);
}

function fillForm(b) {
  editId = Number(b.id || 0);
  slugTouched = !!editId;
  document.getElementById('blogFormTitle').textContent = editId ? `Edit Blog #${editId}` : 'Add Blog';
  document.getElementById('blog-title').value = b.title || '';
  document.getElementById('blog-slug').value = b.slug || '';
  document.getElementById('blog-excerpt').value = b.excerpt || '';
  document.getElementById('blog-category').value = b.category || 'Print Tips';
  document.getElementById('blog-theme').value = b.badge_theme || 'purple';
  document.getElementById('blog-author').value = b.author_name || 'RCS Print Team';
  document.getElementById('blog-published').value = toLocalInput(b.published_at || new Date().toISOString());
  document.getElementById('blog-image-path').value = b.featured_image || '';
  document.getElementById('blog-alt').value = b.image_alt || '';
  document.getElementById('blog-sort').value = Number(b.sort_order || 0);
  document.getElementById('blog-meta-title').value = b.meta_title || '';
  document.getElementById('blog-meta-desc').value = b.meta_description || '';
  document.getElementById('blog-featured').value = Number(b.is_featured ?? 1) ? '1' : '0';
  document.getElementById('blog-active').value = Number(b.is_active ?? 1) ? '1' : '0';
  document.getElementById('blog-editor').innerHTML = b.content || '';
}

function collectForm() {
  return {
    title: document.getElementById('blog-title').value.trim(),
    slug: document.getElementById('blog-slug').value.trim(),
    excerpt: document.getElementById('blog-excerpt').value.trim(),
    category: document.getElementById('blog-category').value.trim(),
    badge_theme: document.getElementById('blog-theme').value,
    author_name: document.getElementById('blog-author').value.trim(),
    published_at: document.getElementById('blog-published').value ? document.getElementById('blog-published').value.replace('T',' ') + ':00' : '',
    featured_image: document.getElementById('blog-image-path').value.trim(),
    image_alt: document.getElementById('blog-alt').value.trim(),
    sort_order: parseInt(document.getElementById('blog-sort').value || '0', 10) || 0,
    meta_title: document.getElementById('blog-meta-title').value.trim(),
    meta_description: document.getElementById('blog-meta-desc').value.trim(),
    is_featured: parseInt(document.getElementById('blog-featured').value || '1', 10) || 0,
    is_active: parseInt(document.getElementById('blog-active').value || '1', 10) || 0,
    content: document.getElementById('blog-editor').innerHTML.trim(),
  };
}

function resetForm(){
  editId = 0;
  slugTouched = false;
  fillForm({category:'Print Tips', badge_theme:'purple', author_name:'RCS Print Team', is_featured:1, is_active:1, content:'<p>Write your blog content here...</p>'});
  slugTouched = false;
  showErr('');
}
function newBlog(){ resetForm(); window.scrollTo({top:document.body.scrollHeight, behavior:'smooth'}); }
function editBlog(id){ const b = blogs.find(x=>Number(x.id)===Number(id)); if (!b) return; fillForm(b); window.scrollTo({top:document.body.scrollHeight, behavior:'smooth'}); }

async function saveBlog() {
  const payload = collectForm();
  if (!payload.title) { showErr('Blog title is required.'); return; }
  if (!payload.content || payload.content === '<br>') { showErr('Blog content is required.'); return; }
  showErr('');
  const btn = document.getElementById('blogSaveBtn');
  btn.disabled = true;
  btn.textContent = 'Saving...';
  try {
    const url = editId ? `/admin/api/blogs/${editId}` : '/admin/api/blogs';
    const method = editId ? 'PUT' : 'POST';
    const res = await fetch(url, {method, headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF}, body: JSON.stringify(payload)}).then(r=>r.json());
    if (!res.ok) { showErr(res.msg || 'Save failed'); return; }
    toastMsg(editId ? 'Blog updated' : 'Blog created', 'success');
    resetForm();
    await loadBlogs();
  } finally {
    btn.disabled = false;
    btn.textContent = 'Save Blog';
  }
}

async function delBlog(id) {
  if (!confirm('Delete this blog?')) return;
  const res = await fetch(`/admin/api/blogs/${id}`, {method:'DELETE', headers:{'X-CSRF-TOKEN':CSRF}}).then(r=>r.json());
  if (!res.ok) { showErr(res.msg || 'Delete failed'); return; }
  toastMsg('Blog deleted', 'info');
  await loadBlogs();
}

async function uploadBlogImage(){
  const file = document.getElementById('blog-image').files?.[0];
  if (!file) { showErr('Select image first.'); return; }
  const fd = new FormData();
  fd.append('image', file);
  const res = await fetch('/admin/api/blogs/upload', {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}, body: fd}).then(r=>r.json());
  if (!res.ok) { showErr(res.msg || 'Upload failed'); return; }
  document.getElementById('blog-image-path').value = res.path || '';
  showErr('');
  toastMsg('Image uploaded', 'success');
}

resetForm();
loadBlogs();
</script>
    </div></div></div>
</body></html>
