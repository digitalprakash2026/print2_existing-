<?php
$pageTitle = 'Custom Orders — RCS Admin';
$currentAdmPage = 'custom-orders';
include __DIR__ . '/layout.php';
?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;gap:10px;flex-wrap:wrap">
  <div><div class="adm-pt" style="margin:0">Custom Orders</div><div style="font-size:13px;color:var(--text2);margin-top:4px">Customer custom quote requests. Next phases can send WhatsApp quotes, approvals, payments and convert to orders.</div></div>
  <button class="btn btn-outline btn-sm" type="button" onclick="loadCustomOrders()">↻ Refresh</button>
</div>
<div id="customOrderList"><div style="text-align:center;padding:44px;color:var(--text2)"><div class="pay-spin" style="border-top-color:var(--blue);margin:0 auto 12px"></div>Loading custom orders…</div></div>
<script>
const CSRF = '<?= htmlspecialchars($csrf??'') ?>';
let quotes=[];
function esc(s){return String(s||'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));}
function statusLabel(s){return String(s||'new').replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase());}
async function loadCustomOrders(){
  const res=await fetch('/admin/api/custom-orders',{credentials:'same-origin'}).then(r=>r.json()).catch(()=>({ok:false,quotes:[]}));
  quotes=res.quotes||[]; renderCustomOrders();
}
function renderCustomOrders(){
  const box=document.getElementById('customOrderList');
  if(!quotes.length){box.innerHTML='<div class="adm-empty">No custom quote requests yet.</div>';return;}
  box.innerHTML=`<div class="custom-orders-list">${quotes.map(q=>{
    const wa=String(q.phone||'').replace(/\D+/g,'');
    const msg=encodeURIComponent(`Hello ${q.customer_name||''}, thank you for your custom quotation request ${q.request_code||''}. Our team is reviewing your requirement for ${q.product_name||''}.`);
    return `<article class="custom-order-row"><div><strong>${esc(q.request_code||'#')}</strong><small>${esc(q.created_at||'')}</small></div><div><b>${esc(q.customer_name)}</b><small>${esc(q.phone)} ${q.email?` · ${esc(q.email)}`:''}</small></div><div><b>${esc(q.product_name)}</b><small>${esc(q.size_dimension||'Size not set')} · ${esc(q.material_type||'Material not set')} · Qty: ${esc(q.quantity||'—')}</small><em>${esc(q.instructions||'No instructions')}</em></div><span class="custom-status custom-status-${esc(q.status||'new')}">${statusLabel(q.status)}</span><div class="custom-order-actions"><a class="btn btn-green btn-sm" target="_blank" rel="noopener" href="https://wa.me/${wa}?text=${msg}">WhatsApp</a><select class="fi fi-sel" onchange="updateStatus(${Number(q.id)},this.value)">${['new','reviewing','quoted','customer_approved','converted_to_order','rejected','closed'].map(s=>`<option value="${s}" ${s===(q.status||'new')?'selected':''}>${statusLabel(s)}</option>`).join('')}</select></div></article>`;
  }).join('')}</div>`;
}
async function updateStatus(id,status){
  const res=await fetch(`/admin/api/custom-orders/${id}/status`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},credentials:'same-origin',body:JSON.stringify({status})}).then(r=>r.json());
  if(!res.ok){alert(res.msg||'Could not update status');return;} loadCustomOrders();
}
loadCustomOrders();
</script>
    </div></div></div>
</body></html>
