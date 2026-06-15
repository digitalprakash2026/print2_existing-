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

loadBlogs();
</script>
    </div></div></div>
</body></html>
