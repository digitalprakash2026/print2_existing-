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
function badgeForStatus(s){return ['customer_approved','paid','converted_to_order'].includes(s)?'b-green':(['rejected','closed'].includes(s)?'b-red':(['sent_to_customer','payment_pending','quoted'].includes(s)?'b-amber':'b-blue'));}
function waPhone(phone){let p=String(phone||'').replace(/\D+/g,''); if(p.length===10)p='91'+p; return p;}
async function loadCustomOrders(){const res=await fetch('/admin/api/custom-orders',{credentials:'same-origin'}).then(r=>r.json()).catch(()=>({ok:false,quotes:[]})); quotes=res.quotes||[]; renderCustomOrders();}
function quoteMessage(q){return `Hello ${q.customer_name||'Customer'}, 👋\n\nThank you for your custom quotation request ${q.request_code||''}.\n\nProduct: ${q.product_name||'-'}\nSize: ${q.size_dimension||'-'}\nMaterial: ${q.material_type||'-'}\nQuantity: ${q.quantity||'-'}\nQuoted Amount: ₹${Number(q.quoted_amount||0).toLocaleString('en-IN')}\nDelivery: ${q.estimated_delivery||'-'}\n\n${q.quote_note||'Please reply APPROVE to confirm this custom order. Payment link will be shared after approval.'}\n\nThank you,\nRCS Print`;}
function paymentMessage(q,link){return `Hello ${q.customer_name||'Customer'}, 👋\n\nYour custom quote ${q.request_code||''} is ready for checkout.\n\nProduct: ${q.product_name||'-'}\nAmount: ₹${Number(q.quoted_amount||0).toLocaleString('en-IN')}\nDelivery: ${q.estimated_delivery||'-'}\n\nPlease open this secure link to add it to your cart and complete payment:\n${link}\n\nThank you,\nRCS Print`;}
function renderCustomOrders(){
  const box=document.getElementById('customOrderList');
  if(!quotes.length){box.innerHTML='<div class="adm-empty">No custom quote requests yet.</div>';return;}
  box.innerHTML=`<div class="adm-order-card-list" aria-label="Custom quote requests list">${quotes.map(q=>{
    const id=Number(q.id||0); const amount=Number(q.quoted_amount||0); const status=q.status||'new'; const pay=q.payment_status||'not_required';
    return `<article class="adm-order-card custom-quote-card custom-quote-card--${esc(status)}" id="cq-${id}" data-order-card data-quote-id="${id}">
      <div class="adm-order-card-head">
        <button class="adm-order-card-summary" type="button" aria-expanded="false" data-order-toggle onclick="toggleCustomQuoteCard(this)">
          <span class="adm-order-card-id"><strong>${esc(q.request_code||'#')}</strong><small>${esc(q.created_at||'')} · ${esc(q.customer_type||'guest')}</small></span>
          <span class="adm-order-card-customer"><strong>${esc(q.customer_name)}</strong><small>${esc(q.phone)}${q.email?' · '+esc(q.email):''}</small></span>
          <span class="adm-order-card-meta"><b>₹${amount>0?amount.toLocaleString('en-IN'):'—'}</b><small>${esc(q.product_name||'Custom product')}</small></span>
          <span class="adm-order-card-badges"><span class="badge ${badgeForStatus(status)}">${statusLabel(status)}</span><span class="badge ${pay==='paid'?'b-green':(pay==='payment_pending'?'b-amber':'b-blue')}">${statusLabel(pay)}</span></span>
        </button>
        <div class="adm-order-card-quick" aria-label="Quick custom quote actions">
          <select class="fi fi-sel" aria-label="Update custom quote status" onchange="quickStatus(${id},this.value)">${statuses.map(s=>`<option value="${s}" ${s===status?'selected':''}>${statusLabel(s)}</option>`).join('')}</select>
          <button class="btn btn-outline btn-sm" type="button" onclick="sendQuoteWhatsApp(${id})">WhatsApp</button>
        </div>
        <button class="adm-order-card-toggle" type="button" aria-label="Expand custom quote ${esc(q.request_code||'')}" aria-expanded="false" data-order-toggle onclick="toggleCustomQuoteCard(this)">⌄</button>
      </div>
      <div class="adm-order-card-body" hidden>
        <div class="adm-order-card-grid">
          <section class="adm-order-card-section adm-order-card-section--full">
            <h3>Quote Details</h3>
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
                <label>Status<select class="fi fi-sel" name="status">${statuses.map(s=>`<option value="${s}" ${s===status?'selected':''}>${statusLabel(s)}</option>`).join('')}</select></label>
                <label>Payment Status<select class="fi fi-sel" name="payment_status">${['not_required','payment_pending','paid','failed','refunded'].map(s=>`<option value="${s}" ${s===pay?'selected':''}>${statusLabel(s)}</option>`).join('')}</select></label>
                <label>Estimated Delivery<input class="fi" name="estimated_delivery" value="${attr(q.estimated_delivery)}" placeholder="Eg: 4-5 working days"></label>
              </div>
              <label>Customer Instructions<textarea class="fi" name="instructions">${esc(q.instructions)}</textarea></label>
              <label>Admin Quote Note / WhatsApp Terms<textarea class="fi" name="quote_note">${esc(q.quote_note)}</textarea></label>
              <label>Internal Admin Notes<textarea class="fi" name="admin_notes">${esc(q.admin_notes)}</textarea></label>
              <div class="adm-order-action-strip custom-quote-actions"><div class="ord-action-strip-copy"><span class="ord-action-strip-label">Next flow</span><small>Save quote → send WhatsApp → mark approved → generate payment/cart link.</small></div><div class="ord-actions ord-actions--compact"><button class="aoc-btn aoc-btn--invoice-upload" type="submit"><span class="aoc-ico">💾</span><span>Save Changes</span></button><button class="aoc-btn aoc-btn--wa" type="button" onclick="sendQuoteWhatsApp(${id})"><span class="aoc-ico">💬</span><span>WhatsApp Quote</span></button><button class="aoc-btn aoc-btn--confirm" type="button" onclick="markApproved(${id})"><span class="aoc-ico">✅</span><span>Mark Approved</span></button><button class="aoc-btn aoc-btn--invoice" type="button" onclick="generatePaymentLink(${id})" title="Generate secure cart/payment link and send on WhatsApp"><span class="aoc-ico">🔗</span><span>Payment Link</span></button></div></div>
            </form>
          </section>
        </div>
      </div>
    </article>`;
  }).join('')}</div>`;
}
function toggleCustomQuoteCard(btn){const card=btn.closest('[data-order-card]'); const body=card?.querySelector('.adm-order-card-body'); if(!card||!body)return; const open=body.hidden; body.hidden=!open; card.classList.toggle('open',open); card.querySelectorAll('[data-order-toggle]').forEach(t=>t.setAttribute('aria-expanded',open?'true':'false'));}
function formPayload(form){return Object.fromEntries(new FormData(form).entries());}
async function saveCustomQuote(e,id){e.preventDefault(); const payload=formPayload(e.currentTarget); const res=await fetch(`/admin/api/custom-orders/${id}`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},credentials:'same-origin',body:JSON.stringify(payload)}).then(r=>r.json()).catch(()=>({ok:false,msg:'Could not save custom order'})); if(!res.ok){alert(res.msg||'Could not save custom order');return;} await loadCustomOrders();}
async function quickStatus(id,status){const res=await fetch(`/admin/api/custom-orders/${id}/status`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},credentials:'same-origin',body:JSON.stringify({status})}).then(r=>r.json()).catch(()=>({ok:false,msg:'Could not update status'})); if(!res.ok){alert(res.msg||'Could not update status');return;} await loadCustomOrders();}
async function markApproved(id){const q=quotes.find(x=>Number(x.id)===id); if(!q)return; q.status='customer_approved'; const res=await fetch(`/admin/api/custom-orders/${id}/status`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},credentials:'same-origin',body:JSON.stringify({status:'customer_approved'})}).then(r=>r.json()); if(!res.ok){alert(res.msg||'Could not update status');return;} await loadCustomOrders();}
async function sendQuoteWhatsApp(id){const q=quotes.find(x=>Number(x.id)===id); if(!q)return; window.open(`https://wa.me/${waPhone(q.phone)}?text=${encodeURIComponent(quoteMessage(q))}`,'_blank'); await fetch(`/admin/api/custom-orders/${id}/status`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},credentials:'same-origin',body:JSON.stringify({status:'sent_to_customer'})}).catch(()=>{}); setTimeout(loadCustomOrders,500);}
async function generatePaymentLink(id){const q=quotes.find(x=>Number(x.id)===id); if(!q)return; const amount=Number(q.quoted_amount||0); if(amount<=0){alert('Please save quoted amount before generating payment link.');return;} const res=await fetch(`/admin/api/custom-orders/${id}/payment-link`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},credentials:'same-origin',body:JSON.stringify({})}).then(r=>r.json()).catch(()=>({ok:false,msg:'Could not generate payment link'})); if(!res.ok){alert(res.msg||'Could not generate payment link');return;} q.status='payment_pending'; q.payment_status='payment_pending'; const msg=paymentMessage(q,res.link); window.open(`https://wa.me/${waPhone(q.phone)}?text=${encodeURIComponent(msg)}`,'_blank'); await loadCustomOrders();}
loadCustomOrders();
</script>
    </div></div></div>
</body></html>
