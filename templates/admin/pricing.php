<?php
$pageTitle = 'Pricing — RCS Admin';
$currentAdmPage = 'pricing';
include __DIR__ . '/layout.php';
?>
<div class="adm-pt">Pricing Management</div>
<p style="font-size:13px;color:var(--text2);margin-bottom:20px">Only quantity-tier pricing and per-product design fee are used for order totals.</p>

<div style="display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap">
  <button class="btn btn-blue btn-sm" onclick="openTierModal()">+ Add Quantity Tier Set</button>
</div>

<div id="pricingList">
  <div style="text-align:center;padding:44px;color:var(--text2)"><div class="pay-spin" style="border-top-color:var(--blue);margin:0 auto 12px"></div>Loading…</div>
</div>

<div class="modal-bg" id="m-tier">
  <div class="modal-box">
    <div class="modal-pill"></div>
    <div class="modal-hdr"><div class="modal-ttl">Add Quantity Tier Pricing</div><button class="modal-cls" onclick="closeM('m-tier')">✕</button></div>
    <div class="modal-bdy">
      <div class="fg"><label>Select Product *</label><select class="fi fi-sel" id="tm-prod"></select></div>
      <div class="fg"><label>Tier Name *</label><input class="fi" id="tm-name" placeholder="Standard / Premium / Gloss"></div>
      <div class="fg"><label>Description</label><input class="fi" id="tm-desc" placeholder="Optional"></div>
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text2);margin-bottom:9px">Price Per Quantity *</div>
      <table class="ptbl" style="background:var(--bg);border-radius:9px;overflow:hidden;margin-bottom:12px">
        <thead><tr><th>Quantity</th><th style="text-align:right">Price (₹)</th></tr></thead>
        <tbody id="tm-prices"></tbody>
      </table>
      <button class="btn btn-blue btn-full" onclick="saveTier()" style="border-radius:10px">Save Tier ✓</button>
    </div>
  </div>
</div>

<script>
const QTYS = [100,250,500,1000,2000,3000,4000,5000,10000];
let allProds = [];
const CSRF = '<?= htmlspecialchars($csrf??'') ?>';

async function loadPricing() {
  const pr = await fetch('/admin/api/products').then(r=>r.json());
  allProds = pr.products || [];

  const opts = allProds.map(p=>`<option value="${p.id}">${escH(p.name)}</option>`).join('');
  document.getElementById('tm-prod').innerHTML = opts;

  const listEl = document.getElementById('pricingList');
  if (!allProds.length) {
    listEl.innerHTML = '<div style="text-align:center;padding:44px;color:var(--text2)"><div style="font-size:40px;margin-bottom:9px">💰</div><div>No products. Add products first.</div></div>';
    return;
  }

  listEl.innerHTML = allProds.map(p => `
    <div class="fsec" id="prod-card-${p.id}">
      <div class="fsec-t">🖨️ ${escH(p.name)}
        <span style="font-size:11px;color:var(--text3);font-weight:400;font-family:var(--fn)">${escH(p.category_name||'')}</span>
        <span style="margin-left:auto;display:flex;gap:7px"><button class="btn btn-blue btn-sm" onclick="openTierModal(${p.id})">+ Tier</button></span>
      </div>
      <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:10px">
        <label style="font-size:12px;color:var(--text2)">Design Fee (₹)</label>
        <input class="fi" type="number" min="0" id="design_fee_${p.id}" value="${Number(p.design_fee||0)}" style="max-width:140px">
        <button class="btn btn-outline btn-sm" onclick="saveDesignFee(${p.id})">Save Design Fee</button>
      </div>
      <div id="prod-pricing-${p.id}"><div style="text-align:center;padding:20px;color:var(--text3);font-size:13px">Loading quantity tiers…</div></div>
    </div>
  `).join('');

  for (const p of allProds) loadProductPricing(p.id);
}

async function loadProductPricing(prodId) {
  const el = document.getElementById(`prod-pricing-${prodId}`);
  const res = await fetch(`/admin/api/products/${prodId}`).then(r=>r.json());
  const prod = res.product;
  if (!prod) { el.innerHTML = '<div style="color:var(--text3);font-size:13px">Could not load</div>'; return; }

  const quals = prod.qualities || [];
  if (!quals.length) {
    el.innerHTML = `<div style="font-size:13px;color:var(--text3);padding:8px 0;text-align:center">No quantity tiers configured yet. <span style="color:var(--blue);cursor:pointer;font-weight:600" onclick="openTierModal(${prodId})">Add one →</span></div>`;
    return;
  }

  let html = '';
  quals.forEach(q => {
    html += `
      <div style="margin-bottom:13px;padding:13px;background:var(--bg);border-radius:10px;border:1px solid var(--border)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
          <div><div style="font-size:13px;font-weight:700">${escH(q.name)}</div><div style="font-size:11px;color:var(--text2)">${escH(q.description||'')}</div></div>
          <button class="ic-btn del" onclick="deleteTier(${prodId},${q.id})"><svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg></button>
        </div>
        <table class="ptbl" style="background:var(--white);border-radius:8px;overflow:hidden">
          <thead><tr><th>Quantity</th><th>Current</th><th style="text-align:right">New Price (₹)</th><th>Save</th></tr></thead>
          <tbody>
            ${QTYS.map(qty => {
              const slab = (q.slabs||[]).find(s=>parseInt(s.quantity,10)===qty);
              return `<tr>
                <td style="font-weight:600">${qty.toLocaleString()}</td>
                <td style="color:var(--green);font-weight:600">${slab?'₹'+Number(slab.price).toLocaleString('en-IN'):'—'}</td>
                <td style="text-align:right"><input class="pi" type="number" id="pr_${prodId}_${q.id}_${qty}" value="${slab?.price||''}" placeholder="0" min="0"></td>
                <td><button class="ps-btn" onclick="saveSlab(${prodId},${q.id},${qty})">Save</button></td>
              </tr>`;
            }).join('')}
          </tbody>
        </table>
      </div>`;
  });

  el.innerHTML = html;
}

async function saveDesignFee(prodId) {
  const fee = parseFloat(document.getElementById(`design_fee_${prodId}`).value || '0') || 0;
  const existing = await fetch(`/admin/api/products/${prodId}`).then(r=>r.json());
  if (!existing.ok || !existing.product) { toast('Could not load product', 'error'); return; }
  const p = existing.product;
  const payload = {
    name: p.name,
    category_id: p.category_id,
    description: p.description || '',
    meta_title: p.meta_title || p.name,
    is_active: p.is_active ? 1 : 0,
    sort_order: p.sort_order || 0,
    specs: p.specs || [],
    design_fee: fee
  };
  const res = await fetch(`/admin/api/products/${prodId}`, {
    method:'PUT',
    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
    body: JSON.stringify(payload)
  }).then(r=>r.json());
  if (res.ok) toast('Design fee saved', 'success'); else toast(res.msg || 'Failed', 'error');
}

async function saveSlab(prodId, tierId, qty) {
  const val = parseFloat(document.getElementById(`pr_${prodId}_${tierId}_${qty}`)?.value) || 0;
  const res = await fetch(`/admin/api/qualities/${tierId}/slabs`, {
    method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
    body:JSON.stringify({product_id:prodId, slabs:{[qty]:val}})
  }).then(r=>r.json());
  if (res.ok) toast('Saved', 'success'); else toast('Failed', 'error');
}

async function deleteTier(prodId, tierId) {
  if (!confirm('Remove this quantity tier and all slab prices?')) return;
  await fetch(`/admin/api/qualities/${tierId}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF}});
  toast('Removed','info');
  loadProductPricing(prodId);
}

function openTierModal(prodId) {
  document.getElementById('tm-prices').innerHTML = QTYS.map(q=>`<tr><td style="font-weight:600">${q.toLocaleString()} pcs</td><td style="text-align:right"><input class="pi" type="number" id="nq_${q}" placeholder="0" min="0"></td></tr>`).join('');
  document.getElementById('tm-name').value = '';
  document.getElementById('tm-desc').value = '';
  if (prodId) document.getElementById('tm-prod').value = prodId;
  openM('m-tier');
}

async function saveTier() {
  const prodId = parseInt(document.getElementById('tm-prod').value || '0', 10);
  const name = document.getElementById('tm-name').value.trim();
  if (!prodId || !name) { toast('Product and tier name required','error'); return; }

  const qRes = await fetch('/admin/api/qualities',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},body:JSON.stringify({name,description:document.getElementById('tm-desc').value.trim()})}).then(r=>r.json());
  if (!qRes.ok) { toast(qRes.msg||'Failed','error'); return; }

  const slabs = {};
  QTYS.forEach(q=>{ const v=parseFloat(document.getElementById('nq_'+q)?.value)||0; if(v>0) slabs[q]=v; });
  await fetch(`/admin/api/qualities/${qRes.id}/slabs`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},body:JSON.stringify({product_id:prodId,slabs})});

  closeM('m-tier');
  toast('Quantity tier saved','success');
  loadProductPricing(prodId);
}

function openM(id){document.getElementById(id).classList.add('show');}
function closeM(id){document.getElementById(id).classList.remove('show');}
function escH(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function toast(msg,type='info'){const w=document.getElementById('tw');const t=document.createElement('div');t.className='toast '+type;t.textContent=msg;w.appendChild(t);requestAnimationFrame(()=>requestAnimationFrame(()=>t.classList.add('show')));setTimeout(()=>{t.classList.remove('show');setTimeout(()=>t.remove(),300);},2800);}

loadPricing();
</script>
    </div></div></div>
</body></html>
