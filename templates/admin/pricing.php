<?php
$pageTitle = 'Pricing — RCS Admin';
$currentAdmPage = 'pricing';
include __DIR__ . '/layout.php';
?>
<div class="adm-pt">Pricing Rules</div>
<p style="font-size:13px;color:var(--text2);margin-bottom:20px">Manage quality options, per-quantity prices, and print attribute add-ons for each product.</p>

<div style="display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap">
  <button class="btn btn-blue btn-sm" onclick="openQualModal()">+ Add Quality Tier</button>
  <button class="btn btn-outline btn-sm" onclick="openAttrModal()">+ Add Attribute Group</button>
</div>

<div id="pricingList">
  <div style="text-align:center;padding:44px;color:var(--text2)"><div class="pay-spin" style="border-top-color:var(--blue);margin:0 auto 12px"></div>Loading…</div>
</div>

<!-- Add Quality Modal -->
<div class="modal-bg" id="m-qual">
  <div class="modal-box">
    <div class="modal-pill"></div>
    <div class="modal-hdr"><div class="modal-ttl">Add Quality Option</div><button class="modal-cls" onclick="closeM('m-qual')">✕</button></div>
    <div class="modal-bdy">
      <div class="fg"><label>Select Product *</label><select class="fi fi-sel" id="qm-prod"></select></div>
      <div class="fg"><label>Quality Name *</label><input class="fi" id="qm-name" placeholder="Premium (130 GSM)"></div>
      <div class="fg"><label>Description</label><input class="fi" id="qm-desc" placeholder="Thick premium paper"></div>
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text2);margin-bottom:9px">Price Per Quantity *</div>
      <table class="ptbl" style="background:var(--bg);border-radius:9px;overflow:hidden;margin-bottom:12px">
        <thead><tr><th>Quantity</th><th style="text-align:right">Price (₹)</th></tr></thead>
        <tbody id="qm-prices"></tbody>
      </table>
      <button class="btn btn-blue btn-full" onclick="saveQual()" style="border-radius:10px">Save Quality ✓</button>
    </div>
  </div>
</div>

<!-- Add Attribute Group Modal -->
<div class="modal-bg" id="m-attr">
  <div class="modal-box">
    <div class="modal-pill"></div>
    <div class="modal-hdr"><div class="modal-ttl">Add Attribute Group</div><button class="modal-cls" onclick="closeM('m-attr')">✕</button></div>
    <div class="modal-bdy">
      <div class="fg"><label>Select Product *</label><select class="fi fi-sel" id="am-prod"></select></div>
      <div class="fg">
        <label>Attribute Group Name *</label>
        <input class="fi" id="am-gname" placeholder="e.g. Sides, Lamination, UV Coating, Size…" list="attr-sugg">
        <datalist id="attr-sugg"><option value="Sides"><option value="Paper Type"><option value="Lamination"><option value="UV Coating"><option value="Size"><option value="Fold Type"><option value="Finish"><option value="Eyelets"></datalist>
      </div>
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text2);margin-bottom:9px">Options</div>
      <div id="am-opts"></div>
      <button class="btn btn-outline btn-sm" onclick="addAttrRow()" style="margin-bottom:12px">+ Add Option</button>
      <button class="btn btn-blue btn-full" onclick="saveAttr()" style="border-radius:10px">Save Attribute Group ✓</button>
    </div>
  </div>
</div>

<script>
const QTYS = [1000,2000,3000,4000,5000,6000,7000,8000,9000,10000];
let allProds = [], attrRows = [];
const CSRF = '<?= htmlspecialchars($csrf??'') ?>';

async function loadPricing() {
  const [pr, ar] = await Promise.all([
    fetch('/admin/api/products').then(r=>r.json()),
    fetch('/admin/api/attribute-groups').then(r=>r.json()),
  ]);
  allProds = pr.products || [];
  const groups = ar.groups || [];

  // Populate product dropdowns
  const opts = allProds.map(p=>`<option value="${p.id}">${escH(p.name)}</option>`).join('');
  ['qm-prod','am-prod'].forEach(id=>{const e=document.getElementById(id);if(e)e.innerHTML=opts;});

  // Render pricing cards per product
  const listEl = document.getElementById('pricingList');
  if (!allProds.length) { listEl.innerHTML = '<div style="text-align:center;padding:44px;color:var(--text2)"><div style="font-size:40px;margin-bottom:9px">💰</div><div>No products. Add products first.</div></div>'; return; }

  listEl.innerHTML = allProds.map(p => {
    // Fetch per-product data inline via fetch at page load already done via allProds
    return `<div class="fsec" id="prod-card-${p.id}">
      <div class="fsec-t">🖨️ ${escH(p.name)} <span style="font-size:11px;color:var(--text3);font-weight:400;font-family:var(--fn)">${escH(p.category_name||'')}</span>
        <span style="margin-left:auto;display:flex;gap:7px">
          <button class="btn btn-blue btn-sm" onclick="openQualModal(${p.id})">+ Quality</button>
          <button class="btn btn-outline btn-sm" onclick="openAttrModal(${p.id})">+ Attribute</button>
        </span>
      </div>
      <div id="prod-pricing-${p.id}">
        <div style="text-align:center;padding:20px;color:var(--text3);font-size:13px">Loading pricing…</div>
      </div>
    </div>`;
  }).join('');

  // Load per-product pricing details
  for (const p of allProds) {
    loadProductPricing(p.id);
  }
}

async function loadProductPricing(prodId) {
  const el = document.getElementById(`prod-pricing-${prodId}`);
  if (!el) return;

  const res = await fetch(`/admin/api/products/${prodId}`).then(r=>r.json());
  const prod = res.product;
  if (!prod) { el.innerHTML = '<div style="color:var(--text3);font-size:13px">Could not load</div>'; return; }

  const quals = prod.qualities || [];
  const attrs = prod.attr_groups || [];

  let html = '';

  // Quality slabs
  if (quals.length) {
    html += quals.map(q => `
      <div style="margin-bottom:13px;padding:13px;background:var(--bg);border-radius:10px;border:1px solid var(--border)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
          <div><div style="font-size:13px;font-weight:700">${escH(q.name)}</div><div style="font-size:11px;color:var(--text2)">${escH(q.description||'')}</div></div>
          <button class="ic-btn del" onclick="deleteQual(${prodId},${q.id})"><svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg></button>
        </div>
        <table class="ptbl" style="background:var(--white);border-radius:8px;overflow:hidden">
          <thead><tr><th>Quantity</th><th>Current</th><th style="text-align:right">New Price (₹)</th><th>Save</th></tr></thead>
          <tbody>
            ${QTYS.map(qty => {
              const slab = q.slabs?.find(s=>parseInt(s.quantity)===qty);
              return `<tr>
                <td style="font-weight:600">${qty.toLocaleString()}</td>
                <td style="color:var(--green);font-weight:600">${slab?'₹'+Number(slab.price).toLocaleString('en-IN'):'—'}</td>
                <td style="text-align:right"><input class="pi" type="number" id="pr_${prodId}_${q.id}_${qty}" value="${slab?.price||''}" placeholder="0" min="0"></td>
                <td><button class="ps-btn" onclick="saveSlab(${prodId},${q.id},${qty})">Save</button></td>
              </tr>`;
            }).join('')}
          </tbody>
        </table>
      </div>`).join('');
  } else {
    html += `<div style="font-size:13px;color:var(--text3);padding:8px 0;text-align:center">No quality options. <span style="color:var(--blue);cursor:pointer;font-weight:600" onclick="openQualModal(${prodId})">Add one →</span></div>`;
  }

  // Attribute groups
  if (attrs.length) {
    html += `<div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
      <div style="font-size:12px;font-weight:700;color:var(--text2);margin-bottom:8px">Print Attributes</div>
      ${attrs.map(ag=>`
        <div style="display:flex;align-items:center;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--border)">
          <div style="font-size:13px;font-weight:600">${escH(ag.name)}</div>
          <div style="display:flex;align-items:center;gap:8px">
            <div style="display:flex;gap:5px;flex-wrap:wrap">
              ${(ag.options||[]).map(o=>`<span style="font-size:11px;padding:3px 8px;border-radius:6px;background:${o.price_addon>0?'var(--orange-bg)':'var(--bg2)'};color:${o.price_addon>0?'var(--orange)':'var(--text2)'};font-weight:600">${escH(o.label)}${o.price_addon>0?' +₹'+Number(o.price_addon).toLocaleString('en-IN'):''}</span>`).join('')}
            </div>
            <button class="ic-btn del" onclick="removeAttrFromProd(${prodId},${ag.id})"><svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg></button>
          </div>
        </div>`).join('')}
    </div>`;
  }

  el.innerHTML = html || '<div style="color:var(--text3);font-size:13px;padding:8px 0">No pricing configured yet.</div>';
}

async function saveSlab(prodId, qualId, qty) {
  const val = parseFloat(document.getElementById(`pr_${prodId}_${qualId}_${qty}`)?.value) || 0;
  const res = await fetch(`/admin/api/qualities/${qualId}/slabs`, {
    method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
    body:JSON.stringify({product_id:prodId, slabs:{[qty]:val}})
  }).then(r=>r.json());
  if (res.ok) toast('✓ Saved','success'); else toast('Failed','error');
}

async function deleteQual(prodId, qualId) {
  if (!confirm('Remove this quality option and all its prices?')) return;
  await fetch(`/admin/api/qualities/${qualId}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF}});
  toast('Removed','info'); loadProductPricing(prodId);
}

function openQualModal(prodId) {
  document.getElementById('qm-prices').innerHTML = QTYS.map(q=>`<tr><td style="font-weight:600">${q.toLocaleString()} pcs</td><td style="text-align:right"><input class="pi" type="number" id="nq_${q}" placeholder="0" min="0"></td></tr>`).join('');
  if (prodId) document.getElementById('qm-prod').value = prodId;
  document.getElementById('qm-name').value = '';
  document.getElementById('qm-desc').value = '';
  openM('m-qual');
}

async function saveQual() {
  const prodId = parseInt(document.getElementById('qm-prod').value);
  const name = document.getElementById('qm-name').value.trim();
  if (!prodId || !name) { toast('Product and name required','error'); return; }
  const qRes = await fetch('/admin/api/qualities',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},body:JSON.stringify({name,description:document.getElementById('qm-desc').value.trim()})}).then(r=>r.json());
  if (!qRes.ok) { toast(qRes.msg||'Failed','error'); return; }
  const slabs = {};
  QTYS.forEach(q=>{ const v=parseFloat(document.getElementById('nq_'+q)?.value)||0; if(v>0)slabs[q]=v; });
  await fetch(`/admin/api/qualities/${qRes.id}/slabs`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},body:JSON.stringify({product_id:prodId,slabs})});
  closeM('m-qual'); toast('Quality added!','success'); loadProductPricing(prodId);
}

let attrRowIds = [];
function addAttrRow() {
  const rid = 'r'+Date.now();
  attrRowIds.push(rid);
  const div = document.createElement('div');
  div.id = rid;
  div.style.cssText = 'display:flex;gap:8px;margin-bottom:8px;align-items:center';
  div.innerHTML = `<input class="fi" id="al_${rid}" placeholder="Option label" style="flex:2"><input class="fi" type="number" id="aa_${rid}" placeholder="Add-on (₹)" style="flex:1;max-width:100px"><button onclick="removeAttrRow('${rid}')" style="color:var(--red);font-size:18px;background:none;border:none;cursor:pointer;flex-shrink:0">✕</button>`;
  document.getElementById('am-opts').appendChild(div);
}
function removeAttrRow(rid) {
  attrRowIds = attrRowIds.filter(r=>r!==rid);
  document.getElementById(rid)?.remove();
}

function openAttrModal(prodId) {
  attrRowIds = [];
  document.getElementById('am-opts').innerHTML = '';
  document.getElementById('am-gname').value = '';
  if (prodId) document.getElementById('am-prod').value = prodId;
  addAttrRow(); addAttrRow();
  openM('m-attr');
}

async function saveAttr() {
  const prodId = parseInt(document.getElementById('am-prod').value);
  const gname = document.getElementById('am-gname').value.trim();
  if (!prodId || !gname) { toast('Product and name required','error'); return; }
  const options = attrRowIds.map(rid=>({label:document.getElementById('al_'+rid)?.value?.trim(),price_addon:parseFloat(document.getElementById('aa_'+rid)?.value)||0})).filter(o=>o.label);
  if (!options.length) { toast('Add at least one option','error'); return; }
  const gRes = await fetch('/admin/api/attribute-groups',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},body:JSON.stringify({name:gname,options})}).then(r=>r.json());
  if (!gRes.ok) { toast(gRes.msg||'Failed','error'); return; }
  await fetch(`/admin/api/products/${prodId}/attribute-groups`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},body:JSON.stringify({group_id:gRes.id})});
  closeM('m-attr'); toast('Attribute added!','success'); loadProductPricing(prodId);
}

async function removeAttrFromProd(prodId, groupId) {
  if (!confirm('Remove this attribute group from product?')) return;
  await fetch(`/admin/api/products/${prodId}/attribute-groups/${groupId}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF}});
  toast('Removed','info'); loadProductPricing(prodId);
}

function openM(id){document.getElementById(id).classList.add('show');}
function closeM(id){document.getElementById(id).classList.remove('show');}
function escH(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function toast(msg,type='info'){const w=document.getElementById('tw');const t=document.createElement('div');t.className='toast '+type;t.textContent=msg;w.appendChild(t);requestAnimationFrame(()=>requestAnimationFrame(()=>t.classList.add('show')));setTimeout(()=>{t.classList.remove('show');setTimeout(()=>t.remove(),300);},2800);}

loadPricing();
</script>
    </div></div></div>
</body></html>
