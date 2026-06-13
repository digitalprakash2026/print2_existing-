<?php
$pageTitle = 'Coupons — RCS Admin';
$currentAdmPage = 'coupons';
include __DIR__ . '/layout.php';
?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
  <div class="adm-pt" style="margin:0">Coupon Codes</div>
  <button class="btn btn-blue btn-sm" onclick="openCouponModal()">+ Add Coupon</button>
</div>
<div id="couponList"><div style="text-align:center;padding:44px;color:var(--text2)"><div class="pay-spin" style="border-top-color:var(--blue);margin:0 auto 12px"></div>Loading…</div></div>

<div class="modal-bg" id="m-coupon">
  <div class="modal-box">
    <div class="modal-pill"></div>
    <div class="modal-hdr"><div class="modal-ttl" id="couponModalTitle">Add Coupon</div><button class="modal-cls" onclick="closeM('m-coupon')">✕</button></div>
    <div class="modal-bdy">
      <div class="fg"><label>Coupon Code *</label><input class="fi" id="ec-code" placeholder="FIRST10" oninput="this.value=this.value.toUpperCase()" style="text-transform:uppercase;font-family:monospace;font-weight:700;letter-spacing:1px"></div>
      <div class="fg"><label>Description</label><input class="fi" id="ec-desc" placeholder="10% off on first order"></div>
      <div class="f2">
        <div class="fg"><label>Discount Type</label><select class="fi fi-sel" id="ec-type"><option value="percent">Percentage (%)</option><option value="flat">Flat Amount (₹)</option></select></div>
        <div class="fg"><label>Discount Value *</label><input type="number" class="fi" id="ec-val" placeholder="10"></div>
      </div>
      <div class="f2">
        <div class="fg"><label>Min Order (₹)</label><input type="number" class="fi" id="ec-min" placeholder="0"></div>
        <div class="fg"><label>Max Uses (0 = unlimited)</label><input type="number" class="fi" id="ec-uses" placeholder="0"></div>
      </div>
      <div class="f2">
        <div class="fg">
          <label>Coupon Scope</label>
          <select class="fi fi-sel" id="ec-scope" onchange="toggleCouponScope(this.value)">
            <option value="all">All Products</option>
            <option value="category">Specific Category</option>
          </select>
        </div>
        <div class="fg" id="ec-cat-wrap" style="display:none">
          <label>Category *</label>
          <select class="fi fi-sel" id="ec-category"><option value="">Select category</option></select>
        </div>
      </div>
      <div class="f2">
        <div class="fg"><label>Valid From</label><input type="date" class="fi" id="ec-from"></div>
        <div class="fg"><label>Valid Until</label><input type="date" class="fi" id="ec-to"></div>
      </div>
      <button class="btn btn-blue btn-full" id="couponSaveBtn" onclick="saveCoupon()" style="border-radius:10px;margin-top:4px">Save Coupon ✓</button>
    </div>
  </div>
</div>

<script>
const CSRF = '<?= htmlspecialchars($csrf??'') ?>';
let coupons = [];
let editCouponId = 0;

async function loadCoupons() {
  const res = await fetch('/admin/api/coupons').then(r=>r.json());
  coupons = res.coupons || [];
  if (!coupons.length) {
    document.getElementById('couponList').innerHTML = `<div style="text-align:center;padding:60px;color:var(--text2)"><div style="font-size:48px;margin-bottom:12px">🎟️</div><div style="font-size:15px;font-weight:600;margin-bottom:4px">No Coupons Yet</div><div style="font-size:13px">Create coupon codes to give discounts to customers</div></div>`;
    return;
  }
  document.getElementById('couponList').innerHTML = coupons.map(c => `
    <div class="coupon-item">
      <div class="coupon-code">${escH(c.code)}</div>
      <div class="coupon-info">
        <div class="coupon-name">${escH(c.description||'—')}</div>
        <div class="coupon-meta">
          ${c.discount_type==='percent'?c.discount_value+'% off':'₹'+Number(c.discount_value).toLocaleString('en-IN')+' off'}
          ${(c.scope_type||'all')==='category' ? ` · Category: ${escH(c.category_name || 'Selected')}` : ' · All products'}
          ${c.min_order_amount>0?' · Min ₹'+Number(c.min_order_amount).toLocaleString('en-IN'):''}
          · Used ${c.used_count||0}/${c.max_uses||'∞'} times
          ${c.valid_until?' · Expires '+c.valid_until:''}
        </div>
      </div>
      <button class="ic-btn" onclick="editCoupon(${c.id})" title="Edit coupon" aria-label="Edit coupon">
        <svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zm17.71-10.04c.39-.39.39-1.02 0-1.41l-2.51-2.51a.9959.9959 0 0 0-1.41 0l-1.96 1.96 3.75 3.75 2.13-1.79z"/></svg>
      </button>
      <div class="tog ${c.is_active?'on':''}" onclick="toggleCoupon(${c.id},this)" title="${c.is_active?'Deactivate':'Activate'}"><div class="tog-k"></div></div>
      <button class="ic-btn del" onclick="deleteCoupon(${c.id},'${escH(c.code)}')">
        <svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
      </button>
    </div>`).join('');
}

function openCouponModal() {
  editCouponId = 0;
  document.getElementById('couponModalTitle').textContent = 'Add Coupon';
  document.getElementById('couponSaveBtn').textContent = 'Save Coupon ✓';
  ['ec-code','ec-desc','ec-val','ec-min','ec-uses','ec-from','ec-to'].forEach(id=>{const e=document.getElementById(id);if(e)e.value='';});
  document.getElementById('ec-type').value='percent';
  document.getElementById('ec-scope').value='all';
  document.getElementById('ec-category').value='';
  toggleCouponScope('all');
  openM('m-coupon');
}

function editCoupon(id) {
  const coupon = coupons.find(c => Number(c.id) === Number(id));
  if (!coupon) { toast('Coupon not found','error'); return; }
  editCouponId = Number(coupon.id);
  document.getElementById('couponModalTitle').textContent = `Edit Coupon: ${coupon.code || ''}`;
  document.getElementById('couponSaveBtn').textContent = 'Update Coupon ✓';
  document.getElementById('ec-code').value = coupon.code || '';
  document.getElementById('ec-desc').value = coupon.description || '';
  document.getElementById('ec-type').value = coupon.discount_type || 'percent';
  document.getElementById('ec-val').value = Number(coupon.discount_value || 0);
  document.getElementById('ec-min').value = Number(coupon.min_order_amount || 0);
  document.getElementById('ec-uses').value = Number(coupon.max_uses || 0);
  const scopeType = (coupon.scope_type || 'all') === 'category' ? 'category' : 'all';
  document.getElementById('ec-scope').value = scopeType;
  document.getElementById('ec-category').value = coupon.category_id || '';
  toggleCouponScope(scopeType);
  document.getElementById('ec-from').value = coupon.valid_from ? String(coupon.valid_from).slice(0,10) : '';
  document.getElementById('ec-to').value = coupon.valid_until ? String(coupon.valid_until).slice(0,10) : '';
  openM('m-coupon');
}

async function loadCouponCategories() {
  const res = await fetch('/admin/api/categories').then(r=>r.json());
  const cats = res.categories || [];
  document.getElementById('ec-category').innerHTML = `<option value="">Select category</option>` + cats.map(c =>
    `<option value="${c.id}">${escH(c.name)}</option>`
  ).join('');
}

function toggleCouponScope(scope) {
  document.getElementById('ec-cat-wrap').style.display = scope === 'category' ? '' : 'none';
}

async function saveCoupon() {
  const code = document.getElementById('ec-code').value.trim().toUpperCase();
  const val  = parseFloat(document.getElementById('ec-val').value);
  const scopeType = document.getElementById('ec-scope').value;
  const categoryId = parseInt(document.getElementById('ec-category').value || '0', 10);
  if (!code || !val) { toast('Code and value required','error'); return; }
  if (scopeType === 'category' && !categoryId) { toast('Select coupon category','error'); return; }
  const payload = {
    code, description:document.getElementById('ec-desc').value.trim(),
    discount_type:document.getElementById('ec-type').value, discount_value:val,
    min_order_amount:parseFloat(document.getElementById('ec-min').value)||0,
    max_uses:parseInt(document.getElementById('ec-uses').value)||0,
    scope_type:scopeType,
    category_id: scopeType === 'category' ? categoryId : null,
    valid_from:document.getElementById('ec-from').value||null,
    valid_until:document.getElementById('ec-to').value||null,
  };
  const url = editCouponId ? `/admin/api/coupons/${editCouponId}` : '/admin/api/coupons';
  const method = editCouponId ? 'PUT' : 'POST';
  const res = await fetch(url,{
    method, headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
    body:JSON.stringify(payload)
  }).then(r=>r.json());
  if (res.ok) { closeM('m-coupon'); toast(editCouponId ? 'Coupon updated!' : 'Coupon created!','success'); loadCoupons(); }
  else toast(res.msg||'Failed','error');
}

async function toggleCoupon(id,togEl) {
  await fetch(`/admin/api/coupons/${id}/toggle`,{method:'POST',headers:{'X-CSRF-TOKEN':CSRF}});
  togEl.classList.toggle('on');
  toast('Status updated','success');
}

async function deleteCoupon(id, code) {
  if (!confirm(`Delete coupon "${code}"?`)) return;
  await fetch(`/admin/api/coupons/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF}});
  toast('Deleted','info'); loadCoupons();
}

function openM(id){document.getElementById(id).classList.add('show');}
function closeM(id){document.getElementById(id).classList.remove('show');}
function escH(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function toast(msg,type='info'){const w=document.getElementById('tw');const t=document.createElement('div');t.className='toast '+type;t.textContent=msg;w.appendChild(t);requestAnimationFrame(()=>requestAnimationFrame(()=>t.classList.add('show')));setTimeout(()=>{t.classList.remove('show');setTimeout(()=>t.remove(),300);},2800);}

loadCoupons();
loadCouponCategories();
</script>
    </div></div></div>
</body></html>
