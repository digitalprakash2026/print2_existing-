<?php
$blogEditId = (int)($blogEditId ?? 0);
$pageTitle = $blogEditId > 0 ? 'Edit Blog — RCS Admin' : 'Add Blog — RCS Admin';
$currentAdmPage = 'blogs';
include __DIR__ . '/layout.php';
?>
<div class="adm-pt"><?= $blogEditId > 0 ? 'Edit Blog' : 'Add Blog' ?></div>

<div class="fsec blog-publisher-page">
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

  <div class="fg blog-composer-shell blog-composer-shell-pro">
    <div class="blog-composer-head">
      <div>
        <label>Blog Content *</label>
        <p>Place the cursor anywhere in the article, then use the side block panel to insert media, CTA and layout elements.</p>
      </div>
      <div class="blog-live-stats"><span id="blogWordCount">0 words</span><span id="blogReadTime">1 min read</span></div>
    </div>
    <div class="blog-workbench">
      <main class="blog-editor-canvas">
        <div class="blog-editor-mini-toolbar" aria-label="Quick formatting toolbar">
          <button type="button" onclick="editorCmd('bold')"><strong>B</strong></button>
          <button type="button" onclick="editorCmd('italic')"><em>I</em></button>
          <button type="button" onclick="editorCmd('underline')"><u>U</u></button>
          <button type="button" onclick="editorLink()"><i class="fa-solid fa-link"></i> Link</button>
          <button type="button" onclick="editorCmd('undo')"><i class="fa-solid fa-rotate-left"></i> Undo</button>
        </div>
        <div id="blog-editor" class="blog-rich-editor blog-rich-editor-pro" contenteditable="true" aria-label="Blog content editor"></div>
      </main>
      <aside class="blog-block-panel" aria-label="Blog block insert panel">
        <div class="blog-block-panel-head">
          <strong><i class="fa-solid fa-wand-magic-sparkles"></i> Insert Blocks</strong>
          <span>Cursor based</span>
        </div>
        <div class="blog-tool-group">
          <p>Text Blocks</p>
          <div class="blog-tool-grid blog-tool-grid--text">
            <button type="button" onclick="editorBlock('p')">Paragraph</button>
            <button type="button" onclick="editorBlock('h1')">H1</button>
            <button type="button" onclick="editorBlock('h2')">H2</button>
            <button type="button" onclick="editorBlock('h3')">H3</button>
            <button type="button" onclick="editorCmd('insertUnorderedList')">• List</button>
            <button type="button" onclick="editorCmd('insertOrderedList')">1. List</button>
            <button type="button" onclick="editorBlock('blockquote')">Quote</button>
          </div>
        </div>
        <div class="blog-tool-group">
          <p>Media & Conversion</p>
          <div class="blog-tool-grid">
            <button class="blog-tool-card blog-tool-card--image" type="button" onclick="insertBlogImage()"><i class="fa-regular fa-image"></i><span>Image</span><small>Upload into cursor</small></button>
            <button class="blog-tool-card blog-tool-card--video" type="button" onclick="insertBlogVideo()"><i class="fa-solid fa-play"></i><span>Video</span><small>YouTube embed</small></button>
            <button class="blog-tool-card blog-tool-card--cta" type="button" onclick="insertCtaBlock()"><i class="fa-solid fa-bullhorn"></i><span>CTA</span><small>Lead action block</small></button>
            <button class="blog-tool-card blog-tool-card--tip" type="button" onclick="insertTipBlock()"><i class="fa-regular fa-lightbulb"></i><span>Tip Box</span><small>Helpful note</small></button>
            <button class="blog-tool-card blog-tool-card--divider" type="button" onclick="insertDivider()"><i class="fa-solid fa-grip-lines"></i><span>Divider</span><small>Section break</small></button>
          </div>
        </div>
        <div class="blog-tool-group">
          <p>Spacing & Cleanup</p>
          <label class="blog-editor-control">Line
            <select onchange="restoreEditorSelection(); applyEditorStyle('lineHeight', this.value); this.value='';">
              <option value="">Spacing</option>
              <option value="1.2">Tight 1.2</option>
              <option value="1.5">Normal 1.5</option>
              <option value="1.75">Relaxed 1.75</option>
              <option value="2">Large 2.0</option>
            </select>
          </label>
          <label class="blog-editor-control">Letter
            <select onchange="restoreEditorSelection(); applyEditorStyle('letterSpacing', this.value); this.value='';">
              <option value="">Spacing</option>
              <option value="normal">Normal</option>
              <option value="-0.25px">-0.25px</option>
              <option value="0.5px">0.5px</option>
              <option value="1px">1px</option>
              <option value="1.5px">1.5px</option>
            </select>
          </label>
          <div class="blog-tool-grid blog-tool-grid--text">
            <button type="button" onclick="applySpacingPreset('compact')">Compact Space</button>
            <button type="button" onclick="applySpacingPreset('normal')">Normal Space</button>
            <button type="button" onclick="editorCmd('removeFormat')">Clear Format</button>
            <button type="button" onclick="cleanCurrentContent()">Clean Content</button>
          </div>
        </div>
      </aside>
    </div>
  </div>

  <div class="blog-savebar">
    <div class="blog-savebar-left">
      <button class="btn btn-blue btn-sm" onclick="saveBlog()" id="blogSaveBtn"><?= $blogEditId > 0 ? 'Update Blog' : 'Save Blog' ?></button>
      <button class="btn btn-outline btn-sm" onclick="previewBlog()">Preview</button>
      <button class="btn btn-outline btn-sm" onclick="resetForm()">Reset</button>
    </div>
    <span id="blogImageSaveHint" style="align-self:center;font-size:11px;color:var(--text3);font-weight:700">Featured image uploads immediately after selection.</span>
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
let savedEditorRange = null;
function saveEditorSelection(){
  const sel = window.getSelection();
  const ed = editor();
  if (sel && sel.rangeCount && ed.contains(sel.anchorNode)) savedEditorRange = sel.getRangeAt(0).cloneRange();
}
function restoreEditorSelection(){
  const ed = editor();
  ed.focus();
  if (!savedEditorRange) return;
  const sel = window.getSelection();
  sel.removeAllRanges();
  sel.addRange(savedEditorRange);
}
function editorCmd(cmd){ restoreEditorSelection(); document.execCommand(cmd, false, null); saveEditorSelection(); updateBlogStats(); }
function editorBlock(tag){ restoreEditorSelection(); document.execCommand('formatBlock', false, tag); saveEditorSelection(); updateBlogStats(); }
function editorLink(){ restoreEditorSelection(); const url = prompt('Enter URL'); if(url) document.execCommand('createLink', false, url); saveEditorSelection(); updateBlogStats(); }
function insertHtmlAtCursor(html){ restoreEditorSelection(); document.execCommand('insertHTML', false, html); saveEditorSelection(); updateBlogStats(); }
function safeAttr(s){ return esc(s).replace(/`/g,'&#96;'); }

function selectedEditableBlocks(){
  const ed = editor();
  const sel = window.getSelection();
  const fallback = () => {
    const node = sel?.anchorNode;
    const el = node?.nodeType === 1 ? node : node?.parentElement;
    const block = el?.closest?.('h1,h2,h3,h4,p,li,blockquote,div');
    return block && ed.contains(block) ? [block] : [];
  };
  if (!sel || !sel.rangeCount || sel.isCollapsed) return fallback();
  const range = sel.getRangeAt(0);
  const blocks = Array.from(ed.querySelectorAll('h1,h2,h3,h4,p,li,blockquote,div')).filter((el) => {
    try { return range.intersectsNode(el); } catch (_) { return false; }
  });
  return blocks.length ? blocks : fallback();
}
function wrapSelectionWithStyle(prop, value){
  const sel = window.getSelection();
  if (!sel || !sel.rangeCount || sel.isCollapsed) return false;
  const span = document.createElement('span');
  span.style[prop] = value;
  try {
    const range = sel.getRangeAt(0);
    range.surroundContents(span);
    sel.removeAllRanges();
    return true;
  } catch (_) {
    document.execCommand('insertHTML', false, `<span style="${prop.replace(/[A-Z]/g, m => '-' + m.toLowerCase())}:${safeAttr(value)}">${esc(sel.toString())}</span>`);
    return true;
  }
}
function applyEditorStyle(prop, value){
  if (!value) return;
  const blocks = selectedEditableBlocks();
  if (blocks.length) {
    blocks.forEach((el) => { el.style[prop] = value; });
  } else {
    wrapSelectionWithStyle(prop, value);
  }
  editor().focus();
  updateBlogStats();
}
function applySpacingPreset(type){
  const ed = editor();
  const map = {
    compact: {p:'8px', h:'14px', li:'4px', line:'1.45'},
    normal: {p:'14px', h:'20px', li:'7px', line:'1.65'}
  };
  const preset = map[type] || map.normal;
  ed.querySelectorAll('p').forEach(el => { el.style.marginTop = '0'; el.style.marginBottom = preset.p; el.style.lineHeight = preset.line; });
  ed.querySelectorAll('h1,h2,h3,h4').forEach(el => { el.style.marginTop = preset.h; el.style.marginBottom = '8px'; el.style.lineHeight = '1.2'; });
  ed.querySelectorAll('li').forEach(el => { el.style.marginBottom = preset.li; el.style.lineHeight = preset.line; });
  toastMsg(type === 'compact' ? 'Compact spacing applied' : 'Normal spacing applied', 'success');
  updateBlogStats();
}

function stripPasteNoise(root){
  root.querySelectorAll('script,style,meta,link').forEach(n => n.remove());
  root.querySelectorAll('*').forEach((el) => {
    [...el.attributes].forEach((attr) => {
      const name = attr.name.toLowerCase();
      if (name === 'class') {
        const safeClasses = String(attr.value || '').split(/\s+/).filter(c => /^blog-(cta-block|tip-block|media-figure|video-figure|divider)$/.test(c));
        safeClasses.length ? el.setAttribute('class', safeClasses.join(' ')) : el.removeAttribute('class');
      }
      if (name.startsWith('on') || name === 'id' || name.startsWith('data-') || name === 'width' || name === 'height') el.removeAttribute(attr.name);
      if (name === 'style') {
        const allowed = [];
        const style = el.getAttribute('style') || '';
        const line = style.match(/line-height\s*:\s*([^;]+)/i);
        const letter = style.match(/letter-spacing\s*:\s*([^;]+)/i);
        if (line) allowed.push(`line-height:${line[1].trim()}`);
        if (letter) allowed.push(`letter-spacing:${letter[1].trim()}`);
        allowed.length ? el.setAttribute('style', allowed.join(';')) : el.removeAttribute('style');
      }
    });
  });
}
function classifyPlainLine(line, index){
  const clean = line.trim();
  if (!clean) return '';
  if (/^[-*•]\s+/.test(clean)) return `<li>${esc(clean.replace(/^[-*•]\s+/, ''))}</li>`;
  if (/^\d+[.)]\s+/.test(clean)) return `<li>${esc(clean.replace(/^\d+[.)]\s+/, ''))}</li>`;
  if (index === 0 && clean.length <= 90) return `<h1>${esc(clean)}</h1>`;
  if (clean.length <= 70 && !/[.!?]$/.test(clean)) return `<h2>${esc(clean)}</h2>`;
  return `<p>${esc(clean)}</p>`;
}
function smartPlainTextToHtml(text){
  const lines = String(text || '').replace(/\r/g, '').split('\n').map(l => l.trim()).filter(Boolean);
  const html = [];
  let list = [];
  const flushList = () => { if (list.length) { html.push(`<ul>${list.join('')}</ul>`); list = []; } };
  lines.forEach((line, index) => {
    const node = classifyPlainLine(line, index);
    if (node.startsWith('<li>')) { list.push(node); return; }
    flushList();
    html.push(node);
  });
  flushList();
  return html.join('');
}
function normalizePastedHtml(html, text){
  const source = String(html || '').trim();
  if (!source) return smartPlainTextToHtml(text || '');
  const box = document.createElement('div');
  box.innerHTML = source;
  stripPasteNoise(box);
  box.querySelectorAll('div').forEach((div) => {
    if (Array.from(div.classList || []).some(c => /^blog-/.test(c))) return;
    if (!div.querySelector('figure,img,iframe,video,ul,ol,h1,h2,h3,h4,blockquote') && div.textContent.trim()) {
      const p = document.createElement('p');
      p.innerHTML = div.innerHTML;
      div.replaceWith(p);
    }
  });
  box.querySelectorAll('p,li,h1,h2,h3,h4,blockquote').forEach((el) => {
    el.innerHTML = el.innerHTML.replace(/(&nbsp;|\s)+$/g, '');
    if (!el.textContent.trim() && !el.querySelector('img,iframe,video')) el.remove();
  });
  const firstTextBlock = box.querySelector('p,h1,h2,h3,h4');
  if (firstTextBlock && firstTextBlock.tagName === 'P' && firstTextBlock.textContent.trim().length <= 90) {
    const h = document.createElement('h1');
    h.innerHTML = firstTextBlock.innerHTML;
    firstTextBlock.replaceWith(h);
  }
  box.querySelectorAll('b').forEach(el => { const strong = document.createElement('strong'); strong.innerHTML = el.innerHTML; el.replaceWith(strong); });
  box.querySelectorAll('i').forEach(el => { const em = document.createElement('em'); em.innerHTML = el.innerHTML; el.replaceWith(em); });
  return box.innerHTML || smartPlainTextToHtml(text || '');
}
function handleEditorPaste(e){
  const data = e.clipboardData;
  if (!data) return;
  e.preventDefault();
  const html = data.getData('text/html');
  const text = data.getData('text/plain');
  insertHtmlAtCursor(normalizePastedHtml(html, text));
  applySpacingPreset('normal');
  toastMsg('Pasted content cleaned and formatted', 'success');
}
function cleanCurrentContent(){
  const ed = editor();
  ed.innerHTML = normalizePastedHtml(ed.innerHTML, ed.innerText || '');
  applySpacingPreset('normal');
  toastMsg('Blog content cleaned', 'success');
}

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

editor().addEventListener('input', () => { saveEditorSelection(); updateBlogStats(); });
editor().addEventListener('paste', handleEditorPaste);
editor().addEventListener('keyup', saveEditorSelection);
editor().addEventListener('mouseup', saveEditorSelection);
document.querySelectorAll('.blog-block-panel button,.blog-editor-mini-toolbar button').forEach((control) => {
  control.addEventListener('mousedown', (event) => { event.preventDefault(); restoreEditorSelection(); });
});
document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape') closeBlogPreview(); });
resetForm();
</script>
    </div></div></div>
</body></html>
