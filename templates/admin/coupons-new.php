<?php
$couponEditId = (int)($couponEditId ?? 0);
$pageTitle = $couponEditId > 0 ? 'Edit Coupon — RCS Admin' : 'Add Coupon — RCS Admin';
$currentAdmPage = 'coupons';
include __DIR__ . '/layout.php';
?>
<div class="adm-pt"><?= $couponEditId > 0 ? 'Edit Coupon' : 'Add Coupon' ?></div>
<div class="fsec" style="max-width:820px">
  <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:14px"><div style="font-size:13px;color:var(--text2)">Create or update discount coupons on a dedicated page.</div><a class="btn btn-outline btn-sm" href="/admin/coupons">← Back to Coupons</a></div>
  <div class="fg"><label>Coupon Code *</label><input class="fi" id="ec-code" placeholder="FIRST10" oninput="this.value=this.value.toUpperCase()" style="text-transform:uppercase;font-family:monospace;font-weight:700;letter-spacing:1px"></div>
  <div class="fg"><label>Description</label><input class="fi" id="ec-desc" placeholder="10% off on first order"></div>
  <div class="f2"><div class="fg"><label>Discount Type</label><select class="fi fi-sel" id="ec-type"><option value="percent">Percentage (%)</option><option value="flat">Flat Amount (₹)</option></select></div><div class="fg"><label>Discount Value *</label><input type="number" class="fi" id="ec-val" placeholder="10"></div></div>
  <div class="f2"><div class="fg"><label>Min Order (₹)</label><input type="number" class="fi" id="ec-min" placeholder="0"></div><div class="fg"><label>Max Uses (0 = unlimited)</label><input type="number" class="fi" id="ec-uses" placeholder="0"></div></div>
  <div class="f2"><div class="fg"><label>Coupon Scope</label><select class="fi fi-sel" id="ec-scope" onchange="toggleCouponScope(this.value)"><option value="all">All Products</option><option value="category">Specific Category</option></select></div><div class="fg" id="ec-cat-wrap" style="display:none"><label>Category *</label><select class="fi fi-sel" id="ec-category"><option value="">Select category</option></select></div></div>
  <div class="f2"><div class="fg"><label>Valid From</label><input type="date" class="fi" id="ec-from"></div><div class="fg"><label>Valid Until</label><input type="date" class="fi" id="ec-to"></div></div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px"><button class="btn btn-blue btn-sm" id="couponSaveBtn" onclick="saveCoupon()"><?= $couponEditId > 0 ? 'Update Coupon ✓' : 'Save Coupon ✓' ?></button><a class="btn btn-outline btn-sm" href="/admin/coupons">Cancel</a></div>
</div>
<script>
const CSRF = '<?= htmlspecialchars($csrf??'') ?>';
const COUPON_EDIT_ID = <?= (int)$couponEditId ?>;
let editCouponId = COUPON_EDIT_ID;
function escH(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function toast(msg,type='info'){const w=document.getElementById('tw');const t=document.createElement('div');t.className='toast '+type;t.textContent=msg;w.appendChild(t);requestAnimationFrame(()=>requestAnimationFrame(()=>t.classList.add('show')));setTimeout(()=>{t.classList.remove('show');setTimeout(()=>t.remove(),300);},2800);}
async function loadCouponCategories() { const res = await fetch('/admin/api/categories').then(r=>r.json()); const cats = res.categories || []; document.getElementById('ec-category').innerHTML = `<option value="">Select category</option>` + cats.map(c => `<option value="${c.id}">${escH(c.name)}</option>`).join(''); }
function toggleCouponScope(scope) { document.getElementById('ec-cat-wrap').style.display = scope === 'category' ? '' : 'none'; }
function fillCoupon(c){ editCouponId=Number(c.id||COUPON_EDIT_ID||0); document.getElementById('ec-code').value=c.code||''; document.getElementById('ec-desc').value=c.description||''; document.getElementById('ec-type').value=c.discount_type||'percent'; document.getElementById('ec-val').value=Number(c.discount_value||0)||''; document.getElementById('ec-min').value=Number(c.min_order_amount||0)||''; document.getElementById('ec-uses').value=Number(c.max_uses||0)||''; const scope=(c.scope_type||'all')==='category'?'category':'all'; document.getElementById('ec-scope').value=scope; document.getElementById('ec-category').value=c.category_id||''; toggleCouponScope(scope); document.getElementById('ec-from').value=c.valid_from?String(c.valid_from).slice(0,10):''; document.getElementById('ec-to').value=c.valid_until?String(c.valid_until).slice(0,10):''; }
async function loadForEdit(){ await loadCouponCategories(); if(!COUPON_EDIT_ID){ fillCoupon({}); return; } const res=await fetch('/admin/api/coupons').then(r=>r.json()); const coupon=(res.coupons||[]).find(c=>Number(c.id)===COUPON_EDIT_ID); if(!coupon){ toast('Coupon not found','error'); return; } fillCoupon(coupon); }
async function saveCoupon() { const code=document.getElementById('ec-code').value.trim().toUpperCase(); const val=parseFloat(document.getElementById('ec-val').value); const scopeType=document.getElementById('ec-scope').value; const categoryId=parseInt(document.getElementById('ec-category').value||'0',10); if(!code||!val){toast('Code and value required','error');return;} if(scopeType==='category'&&!categoryId){toast('Select coupon category','error');return;} const payload={code,description:document.getElementById('ec-desc').value.trim(),discount_type:document.getElementById('ec-type').value,discount_value:val,min_order_amount:parseFloat(document.getElementById('ec-min').value)||0,max_uses:parseInt(document.getElementById('ec-uses').value)||0,scope_type:scopeType,category_id:scopeType==='category'?categoryId:null,valid_from:document.getElementById('ec-from').value||null,valid_until:document.getElementById('ec-to').value||null}; const url=editCouponId?`/admin/api/coupons/${editCouponId}`:'/admin/api/coupons'; const method=editCouponId?'PUT':'POST'; const btn=document.getElementById('couponSaveBtn'); btn.disabled=true; btn.textContent='Saving...'; try{ const res=await fetch(url,{method,headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},body:JSON.stringify(payload)}).then(r=>r.json()); if(res.ok){toast(editCouponId?'Coupon updated!':'Coupon created!','success'); window.location.href='/admin/coupons';} else toast(res.msg||'Failed','error'); } finally{ btn.disabled=false; btn.textContent=COUPON_EDIT_ID?'Update Coupon ✓':'Save Coupon ✓'; } }
loadForEdit();
</script>
    </div></div></div>
</body></html>
