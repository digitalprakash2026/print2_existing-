<?php
$pageTitle = 'Custom Orders — RCS Admin';
$currentAdmPage = 'custom-orders';
$admMainClass = 'adm-main--orders';
include __DIR__ . '/layout.php';
?>
<div class="adm-orders-page custom-orders-page">
  <section class="adm-orders-command">
    <div class="adm-orders-command-bg" aria-hidden="true"></div>
    <div class="adm-orders-head adm-orders-head--compact">
      <div><div class="adm-pt" style="margin:0">Custom Orders</div><p style="margin:5px 0 0;color:var(--text2);font-size:12px;font-weight:700">Review, edit and send custom quotation requests for customer approval.</p></div>
      <button class="btn btn-outline btn-sm adm-orders-export" type="button" onclick="loadCustomOrders()">↻ Refresh</button>
    </div>
  </section>

  <div id="customOrderList"><div style="text-align:center;padding:44px;color:var(--text2)"><div class="pay-spin" style="border-top-color:var(--blue);margin:0 auto 12px"></div>Loading custom orders…</div></div>
</div>
<script>
const CSRF = '<?= htmlspecialchars($csrf??'') ?>';
let quotes=[];
const statuses=['new','reviewing','quoted','sent_to_customer','customer_approved','payment_pending','paid','converted_to_order','rejected','closed'];
function esc(s){return String(s||'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));}
function attr(s){return esc(s).replace(/`/g,'&#96;');}
function statusLabel(s){return String(s||'new').replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase());}
function waPhone(phone){let p=String(phone||'').replace(/\D+/g,''); if(p.length===10)p='91'+p; return p;}
async function loadCustomOrders(){const res=await fetch('/admin/api/custom-orders',{credentials:'same-origin'}).then(r=>r.json()).catch(()=>({ok:false,quotes:[]})); quotes=res.quotes||[]; renderCustomOrders();}
function quoteMessage(q){return `Hello ${q.customer_name||'Customer'}, 👋\n\nThank you for your custom quotation request ${q.request_code||''}.\n\nProduct: ${q.product_name||'-'}\nSize: ${q.size_dimension||'-'}\nMaterial: ${q.material_type||'-'}\nQuantity: ${q.quantity||'-'}\nQuoted Amount: ₹${Number(q.quoted_amount||0).toLocaleString('en-IN')}\nDelivery: ${q.estimated_delivery||'-'}\n\n${q.quote_note||'Please reply APPROVE to confirm this custom order. Payment link will be shared after approval.'}\n\nThank you,\nRCS Print`;}
function renderCustomOrders(){
  const box=document.getElementById('customOrderList');
  if(!quotes.length){box.innerHTML='<div class="adm-empty">No custom quote requests yet.</div>';return;}
  box.innerHTML=quotes.map(q=>{
    const id=Number(q.id||0); const amount=Number(q.quoted_amount||0);
    return `<article class="adm-order-card custom-quote-card" id="cq-${id}" data-quote-id="${id}">
      <div class="adm-order-card-summary">
        <span class="adm-order-card-id"><strong>${esc(q.request_code||'#')}</strong><small>${esc(q.created_at||'')} · ${esc(q.customer_type||'guest')}</small></span>
        <span class="adm-order-card-customer"><strong>${esc(q.customer_name)}</strong><small>${esc(q.phone)}${q.email?' · '+esc(q.email):''}</small></span>
        <span class="adm-order-card-items"><strong>${esc(q.product_name)}</strong><small>${esc(q.size_dimension||'Size not set')} · Qty: ${esc(q.quantity||'—')}</small></span>
        <span class="custom-status custom-status-${esc(q.status||'new')}">${statusLabel(q.status)}</span>
        <span class="ord-amt">₹${amount>0?amount.toLocaleString('en-IN'):'—'}</span>
        <button class="adm-order-card-toggle" type="button" aria-expanded="false" onclick="toggleCustomQuoteCard(this)">⌄</button>
      </div>
      <div class="adm-order-card-panel custom-quote-panel" hidden>
        <form class="custom-quote-edit" onsubmit="saveCustomQuote(event,${id})">
          <div class="custom-quote-edit-grid">
            <label>Name<input class="fi" name="customer_name" value="${attr(q.customer_name)}" required></label>
            <label>Phone<input class="fi" name="phone" value="${attr(q.phone)}" required></label>
            <label>Email<input class="fi" name="email" value="${attr(q.email)}"></label>
            <label>Product<input class="fi" name="product_name" value="${attr(q.product_name)}" required></label>
            <label>Size / Dimension<input class="fi" name="size_dimension" value="${attr(q.size_dimension)}"></label>
            <label>Material<input class="fi" name="material_type" value="${attr(q.material_type)}"></label>
            <label>Quantity<input class="fi" name="quantity" value="${attr(q.quantity)}"></label>
            <label>Quoted Amount<input class="fi" type="number" step="0.01" name="quoted_amount" value="${attr(q.quoted_amount)}"></label>
            <label>Status<select class="fi fi-sel" name="status">${statuses.map(s=>`<option value="${s}" ${s===(q.status||'new')?'selected':''}>${statusLabel(s)}</option>`).join('')}</select></label>
            <label>Payment Status<select class="fi fi-sel" name="payment_status">${['not_required','payment_pending','paid','failed','refunded'].map(s=>`<option value="${s}" ${s===(q.payment_status||'not_required')?'selected':''}>${statusLabel(s)}</option>`).join('')}</select></label>
            <label>Estimated Delivery<input class="fi" name="estimated_delivery" value="${attr(q.estimated_delivery)}" placeholder="Eg: 4-5 working days"></label>
          </div>
          <label>Customer Instructions<textarea class="fi" name="instructions">${esc(q.instructions)}</textarea></label>
          <label>Admin Quote Note / WhatsApp Terms<textarea class="fi" name="quote_note">${esc(q.quote_note)}</textarea></label>
          <label>Internal Admin Notes<textarea class="fi" name="admin_notes">${esc(q.admin_notes)}</textarea></label>
          <div class="custom-quote-actions"><button class="btn btn-blue btn-sm" type="submit">Save Changes</button><button class="btn btn-green btn-sm" type="button" onclick="sendQuoteWhatsApp(${id})">Send WhatsApp Quote</button><button class="btn btn-outline btn-sm" type="button" onclick="markApproved(${id})">Mark Approved</button><button class="btn btn-outline btn-sm" type="button" disabled title="Next phase: generate secure payment link and convert after payment">Generate Payment Link</button></div>
        </form>
      </div>
    </article>`;
  }).join('');
}
function toggleCustomQuoteCard(btn){const card=btn.closest('.custom-quote-card'); const panel=card.querySelector('.custom-quote-panel'); const open=panel.hidden; panel.hidden=!open; btn.setAttribute('aria-expanded',open?'true':'false'); card.classList.toggle('open',open);}
function formPayload(form){return Object.fromEntries(new FormData(form).entries());}
async function saveCustomQuote(e,id){e.preventDefault(); const payload=formPayload(e.currentTarget); const res=await fetch(`/admin/api/custom-orders/${id}`,{method:'PUT',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},credentials:'same-origin',body:JSON.stringify(payload)}).then(r=>r.json()); if(!res.ok){alert(res.msg||'Could not save custom order');return;} await loadCustomOrders();}
async function markApproved(id){const q=quotes.find(x=>Number(x.id)===id); if(!q)return; q.status='customer_approved'; const res=await fetch(`/admin/api/custom-orders/${id}/status`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},credentials:'same-origin',body:JSON.stringify({status:'customer_approved'})}).then(r=>r.json()); if(!res.ok){alert(res.msg||'Could not update status');return;} await loadCustomOrders();}
async function sendQuoteWhatsApp(id){const q=quotes.find(x=>Number(x.id)===id); if(!q)return; window.open(`https://wa.me/${waPhone(q.phone)}?text=${encodeURIComponent(quoteMessage(q))}`,'_blank'); await fetch(`/admin/api/custom-orders/${id}/status`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},credentials:'same-origin',body:JSON.stringify({status:'sent_to_customer'})}).catch(()=>{}); setTimeout(loadCustomOrders,500);}
loadCustomOrders();
</script>
    </div></div></div>
</body></html>
