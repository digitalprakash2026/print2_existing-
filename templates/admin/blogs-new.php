<?php
$blogEditId = (int)($blogEditId ?? 0);
$pageTitle = $blogEditId > 0 ? 'Edit Blog — RCS Admin' : 'Add Blog — RCS Admin';
$currentAdmPage = 'blogs';
include __DIR__ . '/layout.php';
?>
<div class="adm-pt"><?= $blogEditId > 0 ? 'Edit Blog' : 'Add Blog' ?></div>

<div class="fsec" style="max-width:1180px">
  <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:14px">
    <div style="font-size:13px;color:var(--text2)"><?= $blogEditId > 0 ? 'Update blog content, SEO, image, and status.' : 'Create a rich blog post for the homepage and public blog detail pages.' ?></div>
    <a class="btn btn-outline btn-sm" href="/admin/blogs">← Back to Blogs</a>
  </div>

  <div id="blogErr" style="display:none;padding:10px 12px;border:1px solid var(--red-mid);background:var(--red-bg);color:var(--red);border-radius:10px;font-size:12px;margin-bottom:10px"></div>

  <div style="font-weight:700;margin-bottom:10px" id="blogFormTitle"><?= $blogEditId > 0 ? 'Edit Blog' : 'Add Blog' ?></div>
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
      <input type="file" class="fi" id="blog-image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" onchange="uploadBlogImage()">
      <div id="blogImageState" style="font-size:11px;color:var(--text3);margin-top:5px">Recommended ratio: 16:10 or 900×560. Choose image — upload starts automatically.</div>
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

  <div class="fg blog-composer-shell">
    <div class="blog-composer-head">
      <div>
        <label>Blog Content *</label>
        <p>Use image, video, CTA and tip blocks to create WordPress-style long-form posts.</p>
      </div>
      <div class="blog-live-stats"><span id="blogWordCount">0 words</span><span id="blogReadTime">1 min read</span></div>
    </div>
    <div class="blog-editor-toolbar blog-editor-toolbar-pro" aria-label="Rich text editor toolbar">
      <button type="button" onclick="editorBlock('p')">Paragraph</button>
      <button type="button" onclick="editorBlock('h2')">H2</button>
      <button type="button" onclick="editorBlock('h3')">H3</button>
      <button type="button" onclick="editorCmd('bold')"><strong>B</strong></button>
      <button type="button" onclick="editorCmd('italic')"><em>I</em></button>
      <button type="button" onclick="editorCmd('underline')"><u>U</u></button>
      <button type="button" onclick="editorCmd('insertUnorderedList')">• List</button>
      <button type="button" onclick="editorCmd('insertOrderedList')">1. List</button>
      <button type="button" onclick="editorBlock('blockquote')">Quote</button>
      <button type="button" onclick="editorLink()">Link</button>
      <button type="button" onclick="insertBlogImage()">Image</button>
      <button type="button" onclick="insertBlogVideo()">Video</button>
      <button type="button" onclick="insertCtaBlock()">CTA</button>
      <button type="button" onclick="insertTipBlock()">Tip Box</button>
      <button type="button" onclick="insertDivider()">Divider</button>
      <button type="button" onclick="editorCmd('undo')">Undo</button>
      <button type="button" onclick="editorCmd('removeFormat')">Clear</button>
    </div>
    <div id="blog-editor" class="blog-rich-editor blog-rich-editor-pro" contenteditable="true" aria-label="Blog content editor"></div>
  </div>

  <div class="blog-savebar">
    <div class="blog-savebar-left">
      <button class="btn btn-blue btn-sm" onclick="saveBlog()" id="blogSaveBtn"><?= $blogEditId > 0 ? 'Update Blog' : 'Save Blog' ?></button>
      <button class="btn btn-outline btn-sm" onclick="previewBlog()">Preview</button>
      <button class="btn btn-outline btn-sm" onclick="resetForm()">Reset</button>
    </div>
    <span id="blogImageSaveHint" style="align-self:center;font-size:11px;color:var(--text3);font-weight:800">Featured image uploads immediately after selection.</span>
  </div>
</div>

<input type="file" id="blog-inline-media" accept=".jpg,.jpeg,.png,.webp,.mp4,.webm,image/jpeg,image/png,image/webp,video/mp4,video/webm" style="display:none">
<div id="blogPreviewModal" class="blog-preview-modal" aria-hidden="true">
  <div class="blog-preview-card">
    <button type="button" class="blog-preview-close" onclick="closeBlogPreview()">×</button>
    <div class="blog-preview-meta" id="blogPreviewMeta"></div>
    <h1 id="blogPreviewTitle"></h1>
    <p id="blogPreviewExcerpt"></p>
    <div class="blog-detail-content" id="blogPreviewContent"></div>
  </div>
</div>

<script>
const BLOG_EDIT_ID = <?= (int)$blogEditId ?>;
let editId = BLOG_EDIT_ID;
let slugTouched = BLOG_EDIT_ID > 0;
const CSRF = '<?= htmlspecialchars($csrf??'') ?>';

function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
function toastMsg(msg,type='info'){ const w=document.getElementById('tw'); const t=document.createElement('div'); t.className='toast '+type; t.textContent=msg; w.appendChild(t); requestAnimationFrame(()=>requestAnimationFrame(()=>t.classList.add('show'))); setTimeout(()=>{t.classList.remove('show'); setTimeout(()=>t.remove(),300);},2600); }
function showErr(msg=''){ const e=document.getElementById('blogErr'); if(!e) return; if(!msg){e.style.display='none';return;} e.textContent=msg; e.style.display='block'; }
function slugify(s){ return String(s||'').toLowerCase().trim().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'') || 'blog-post'; }
function autoSlug(){ if(!editId && !slugTouched) document.getElementById('blog-slug').value = slugify(document.getElementById('blog-title').value); }
document.getElementById('blog-slug').addEventListener('input',()=>{ slugTouched = true; });

function editor(){ return document.getElementById('blog-editor'); }
function editorCmd(cmd){ document.execCommand(cmd, false, null); editor().focus(); updateBlogStats(); }
function editorBlock(tag){ document.execCommand('formatBlock', false, tag); editor().focus(); updateBlogStats(); }
function editorLink(){ const url = prompt('Enter URL'); if(url) document.execCommand('createLink', false, url); editor().focus(); updateBlogStats(); }
function insertHtmlAtCursor(html){ editor().focus(); document.execCommand('insertHTML', false, html); updateBlogStats(); }
function safeAttr(s){ return esc(s).replace(/`/g,'&#96;'); }

async function uploadBlogMedia(file){
  const fd = new FormData();
  fd.append('image', file);
  const res = await fetch('/admin/api/blogs/upload', {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}, body: fd}).then(r=>r.json());
  if (!res.ok) throw new Error(res.msg || 'Upload failed');
  return res;
}
function pickInlineMedia(){
  return new Promise((resolve) => {
    const input = document.getElementById('blog-inline-media');
    input.value = '';
    input.onchange = () => resolve(input.files?.[0] || null);
    input.click();
  });
}
async function insertBlogImage(){
  try {
    const file = await pickInlineMedia();
    if (!file) return;
    const res = await uploadBlogMedia(file);
    if (res.type === 'video') {
      insertHtmlAtCursor(`<figure class="blog-media-figure blog-video-figure"><video controls preload="metadata" src="${safeAttr(res.path)}"></video><figcaption>Video caption...</figcaption></figure><p><br></p>`);
      return;
    }
    const alt = prompt('Image alt text / caption', '') || '';
    insertHtmlAtCursor(`<figure class="blog-media-figure"><img src="${safeAttr(res.path)}" alt="${safeAttr(alt)}"><figcaption>${safeAttr(alt || 'Image caption...')}</figcaption></figure><p><br></p>`);
  } catch (e) { showErr(e.message || 'Could not insert media'); }
}
function youtubeEmbed(url){
  const value = String(url || '').trim();
  let id = '';
  const short = value.match(/youtu\.be\/([A-Za-z0-9_-]{6,})/);
  const watch = value.match(/[?&]v=([A-Za-z0-9_-]{6,})/);
  const embed = value.match(/youtube\.com\/embed\/([A-Za-z0-9_-]{6,})/);
  if (short) id = short[1];
  if (watch) id = watch[1];
  if (embed) id = embed[1];
  return id ? `https://www.youtube.com/embed/${id}` : '';
}
function insertBlogVideo(){
  const url = prompt('Paste YouTube video URL');
  const embed = youtubeEmbed(url);
  if (!embed) { showErr('Please paste a valid YouTube URL.'); return; }
  insertHtmlAtCursor(`<figure class="blog-media-figure blog-video-figure"><iframe src="${safeAttr(embed)}" title="Blog video" loading="lazy" allowfullscreen></iframe><figcaption>Video caption...</figcaption></figure><p><br></p>`);
  showErr('');
}
function insertCtaBlock(){
  const label = prompt('CTA button text', 'Get Quote on WhatsApp') || 'Get Quote on WhatsApp';
  const url = prompt('CTA link URL', '/contact') || '/contact';
  insertHtmlAtCursor(`<div class="blog-cta-block"><div><strong>Need premium printing support?</strong><p>Share your requirement with RCS Graphic and get quick guidance.</p></div><a href="${safeAttr(url)}">${safeAttr(label)}</a></div><p><br></p>`);
}
function insertTipBlock(){
  insertHtmlAtCursor('<div class="blog-tip-block"><strong>Pro Tip</strong><p>Write a practical print/design tip here...</p></div><p><br></p>');
}
function insertDivider(){ insertHtmlAtCursor('<hr class="blog-divider"><p><br></p>'); }
function updateBlogStats(){
  const text = editor().innerText || '';
  const words = (text.trim().match(/\S+/g) || []).length;
  const mins = Math.max(1, Math.ceil(words / 200));
  document.getElementById('blogWordCount').textContent = `${words} words`;
  document.getElementById('blogReadTime').textContent = `${mins} min read`;
}
function previewBlog(){
  const payload = collectForm();
  document.getElementById('blogPreviewTitle').textContent = payload.title || 'Untitled blog';
  document.getElementById('blogPreviewExcerpt').textContent = payload.excerpt || '';
  document.getElementById('blogPreviewMeta').textContent = `${payload.category || 'Print Tips'} • ${payload.author_name || 'RCS Print Team'}`;
  document.getElementById('blogPreviewContent').innerHTML = payload.content || '<p>No content yet.</p>';
  document.getElementById('blogPreviewModal').classList.add('open');
  document.getElementById('blogPreviewModal').setAttribute('aria-hidden','false');
}
function closeBlogPreview(){
  document.getElementById('blogPreviewModal').classList.remove('open');
  document.getElementById('blogPreviewModal').setAttribute('aria-hidden','true');
}

function toLocalInput(value){
  if(!value) return '';
  return String(value).replace(' ', 'T').slice(0,16);
}

function fillForm(b) {
  editId = Number(b.id || BLOG_EDIT_ID || 0);
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
  updateBlogStats();
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

async function loadBlogForEdit(){
  const res = await fetch(`/admin/api/blogs/${BLOG_EDIT_ID}`).then(r=>r.json());
  if (!res.ok) { showErr(res.msg || 'Could not load blog'); return; }
  showErr('');
  fillForm(res.blog || {});
}

async function resetForm(){
  if (BLOG_EDIT_ID > 0) {
    await loadBlogForEdit();
    return;
  }
  editId = 0;
  slugTouched = false;
  fillForm({category:'Print Tips', badge_theme:'purple', author_name:'RCS Print Team', is_featured:1, is_active:1, content:'<p>Write your blog content here...</p>'});
  slugTouched = false;
  showErr('');
  const imageState = document.getElementById('blogImageState');
  if (imageState) { imageState.textContent = 'Recommended ratio: 16:10 or 900×560. Choose image — upload starts automatically.'; imageState.style.color = 'var(--text3)'; }
}

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
    window.location.href = '/admin/blogs';
  } finally {
    btn.disabled = false;
    btn.textContent = BLOG_EDIT_ID > 0 ? 'Update Blog' : 'Save Blog';
  }
}

async function uploadBlogImage(){
  const input = document.getElementById('blog-image');
  const state = document.getElementById('blogImageState');
  const file = input?.files?.[0];
  if (!file) { showErr('Select image first.'); return; }
  if (input) input.disabled = true;
  if (state) { state.textContent = 'Uploading featured image…'; state.style.color = 'var(--blue)'; }
  const fd = new FormData();
  fd.append('image', file);
  try {
    const res = await fetch('/admin/api/blogs/upload', {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}, body: fd}).then(r=>r.json());
    if (!res.ok) {
      showErr(res.msg || 'Upload failed');
      if (state) { state.textContent = 'Upload failed. Choose image again.'; state.style.color = 'var(--red)'; }
      return;
    }
    document.getElementById('blog-image-path').value = res.path || '';
    showErr('');
    if (state) { state.textContent = 'Featured image uploaded automatically.'; state.style.color = 'var(--green)'; }
    toastMsg('Image uploaded', 'success');
  } finally {
    if (input) input.disabled = false;
  }
}

editor().addEventListener('input', updateBlogStats);
document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape') closeBlogPreview(); });
resetForm();
</script>
    </div></div></div>
</body></html>
