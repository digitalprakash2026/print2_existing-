<?php
$pageTitle = 'Coupons — RCS Admin';
$currentAdmPage = 'coupons';
include __DIR__ . '/layout.php';
?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;gap:10px;flex-wrap:wrap">
  <div class="adm-pt" style="margin:0">Coupon Codes</div>
  <a class="btn btn-blue btn-sm" href="/admin/coupons/new">+ Add Coupon</a>
</div>
<div id="couponList"><div style="text-align:center;padding:44px;color:var(--text2)"><div class="pay-spin" style="border-top-color:var(--blue);margin:0 auto 12px"></div>Loading…</div></div>
<script>
const CSRF = '<?= htmlspecialchars($csrf??'') ?>';
let coupons = [];
async function loadCoupons() {
  const res = await fetch('/admin/api/coupons').then(r=>r.json());
  coupons = res.coupons || [];
  if (!coupons.length) {
    document.getElementById('couponList').innerHTML = `<div style="text-align:center;padding:60px;color:var(--text2)"><div style="font-size:48px;margin-bottom:12px">🎟️</div><div style="font-size:15px;font-weight:600;margin-bottom:4px">No Coupons Yet</div><div style="font-size:13px">Create coupon codes to give discounts to customers</div></div>`;
    return;
  }
  document.getElementById('couponList').innerHTML = coupons.map(c => `<div class="coupon-item"><div class="coupon-code">${escH(c.code)}</div><div class="coupon-info"><div class="coupon-name">${escH(c.description||'—')}</div><div class="coupon-meta">${c.discount_type==='percent'?c.discount_value+'% off':'₹'+Number(c.discount_value).toLocaleString('en-IN')+' off'} ${(c.scope_type||'all')==='category' ? ` · Category: ${escH(c.category_name || 'Selected')}` : ' · All products'} ${c.min_order_amount>0?' · Min ₹'+Number(c.min_order_amount).toLocaleString('en-IN'):''} · Used ${c.used_count||0}/${c.max_uses||'∞'} times ${c.valid_until?' · Expires '+c.valid_until:''}</div></div><a class="ic-btn" href="/admin/coupons/edit/${Number(c.id)}" title="Edit coupon" aria-label="Edit coupon"><svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zm17.71-10.04c.39-.39.39-1.02 0-1.41l-2.51-2.51a.9959.9959 0 0 0-1.41 0l-1.96 1.96 3.75 3.75 2.13-1.79z"/></svg></a><div class="tog ${c.is_active?'on':''}" onclick="toggleCoupon(${c.id},this)" title="${c.is_active?'Deactivate':'Activate'}"><div class="tog-k"></div></div><button class="ic-btn del" onclick="deleteCoupon(${c.id},'${escH(c.code)}')"><svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg></button></div>`).join('');
}
async function toggleCoupon(id,togEl) { await fetch(`/admin/api/coupons/${id}/toggle`,{method:'POST',headers:{'X-CSRF-TOKEN':CSRF}}); togEl.classList.toggle('on'); toast('Status updated','success'); }
async function deleteCoupon(id, code) { if (!confirm(`Delete coupon "${code}"?`)) return; await fetch(`/admin/api/coupons/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF}}); toast('Deleted','info'); loadCoupons(); }
function escH(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function toast(msg,type='info'){const w=document.getElementById('tw');const t=document.createElement('div');t.className='toast '+type;t.textContent=msg;w.appendChild(t);requestAnimationFrame(()=>requestAnimationFrame(()=>t.classList.add('show')));setTimeout(()=>{t.classList.remove('show');setTimeout(()=>t.remove(),300);},2800);}
loadCoupons();
</script>
    </div></div></div>
</body></html>
