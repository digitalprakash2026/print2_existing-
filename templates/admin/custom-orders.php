<?php
$pageTitle = 'Custom Orders — RCS Admin';
$currentAdmPage = 'custom-orders';
$admMainClass = 'adm-main--orders';
include __DIR__ . '/layout.php';
?>
<div class="adm-orders-page custom-orders-page">
  <div class="custom-orders-toolbar">
    <a class="btn btn-primary btn-sm adm-orders-export" href="/admin/export/custom-orders">⬇ Export CSV</a>
  </div>

  <div id="customOrderStatusCards" class="custom-quote-status-grid"></div>
  <div id="customOrderList"><div style="text-align:center;padding:44px;color:var(--text2)"><div class="pay-spin" style="border-top-color:var(--blue);margin:0 auto 12px"></div>Loading custom orders…</div></div>
</div>
<script>
const CSRF = '<?= htmlspecialchars($csrf??'') ?>';
let quotes=[];
let counts={};
let activeFilter='all';
const statuses=['new','reviewing','sent_to_customer','customer_approved','payment_pending','converted_to_order','rejected'];
const statusCards=[
  {key:'all',label:'All Quote',icon:'📋'},
  {key:'new',label:'New Quote',icon:'🆕'},
  {key:'reviewing',label:'Reviewing',icon:'🔎'},
  {key:'sent_to_customer',label:'Send to Client',icon:'📨'},
  {key:'customer_approved',label:'Approved',icon:'✅'},
  {key:'payment_pending',label:'Payment Pending',icon:'💳'},
  {key:'converted_to_order',label:'Order Confirm',icon:'📦'},
  {key:'rejected',label:'Rejected',icon:'⛔'},
];
function esc(s){return String(s||'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));}
function attr(s){return esc(s).replace(/`/g,'&#96;');}
function statusLabel(s){return (statusCards.find(x=>x.key===s)?.label)||String(s||'new').replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase());}
function badgeForStatus(s){return ['customer_approved','paid','converted_to_order'].includes(s)?'b-green':(['rejected','closed'].includes(s)?'b-red':(['sent_to_customer','payment_pending','quoted'].includes(s)?'b-amber':'b-blue'));}
function waPhone(phone){let p=String(phone||'').replace(/\D+/g,''); if(p.length===10)p='91'+p; return p;}
async function loadCustomOrders(){const res=await fetch('/admin/api/custom-orders',{credentials:'same-origin'}).then(r=>r.json()).catch(()=>({ok:false,quotes:[],counts:{}})); quotes=res.quotes||[]; counts=res.counts||{}; renderStatusCards(); renderCustomOrders();}
async function customQuoteWhatsappMessage(id,type,paymentLink=''){const res=await fetch(`/admin/api/custom-orders/${id}/whatsapp-message`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},credentials:'same-origin',body:JSON.stringify({type,payment_link:paymentLink})}).then(r=>r.json()).catch(()=>({ok:false,msg:'Could not prepare WhatsApp message'})); if(!res.ok) throw new Error(res.msg||'Could not prepare WhatsApp message'); return res.message||'';}
function renderStatusCards(){const box=document.getElementById('customOrderStatusCards'); if(!box)return; box.innerHTML=statusCards.map(card=>`<button type="button" class="custom-quote-status-card custom-quote-status-card--${card.key} ${activeFilter===card.key?'active':''}" onclick="setCustomQuoteFilter('${card.key}')"><span>${card.icon}</span><strong>${esc(card.label)}</strong><b>${Number(counts[card.key]||0).toLocaleString('en-IN')}</b></button>`).join('');}
function setCustomQuoteFilter(key){activeFilter=key; renderStatusCards(); renderCustomOrders();}
function filteredQuotes(){return activeFilter==='all'?quotes:quotes.filter(q=>(q.status||'new')===activeFilter || (activeFilter==='converted_to_order' && q.status==='paid'));}
function detailValue(v,fallback='Not set'){return esc(v||fallback);}
function renderCustomOrders(){
  const box=document.getElementById('customOrderList');
  const list=filteredQuotes();
  if(!quotes.length){box.innerHTML='<div class="adm-empty">No custom quote requests yet.</div>';return;}
  if(!list.length){box.innerHTML='<div class="adm-empty">No quotes in this status.</div>';return;}
  box.innerHTML=`<div class="adm-order-card-list" aria-label="Custom quote requests list">${list.map(q=>{
    const id=Number(q.id||0); const amount=Number(q.quoted_amount||0); const status=q.status||'new'; const pay=q.payment_status||'not_required'; const linked=Number(q.user_id||0)>0;
    return `<article class="adm-order-card custom-quote-card custom-quote-card--${esc(status)}" id="cq-${id}" data-order-card data-quote-id="${id}">
      <div class="adm-order-card-head">
        <button class="adm-order-card-summary" type="button" aria-expanded="false" data-order-toggle onclick="toggleCustomQuoteCard(this)">
          <span class="adm-order-card-id"><strong>${esc(q.request_code||'#')}</strong><small>${esc(q.created_at||'')} · ${linked?'registered':'guest'}</small></span>
          <span class="adm-order-card-customer"><strong>${esc(q.customer_name)}</strong><small>${esc(q.phone)}${q.email?' · '+esc(q.email):''}</small></span>
          <span class="adm-order-card-meta"><b>₹${amount>0?amount.toLocaleString('en-IN'):'—'}</b><small>${esc(q.product_name||'Custom product')}</small></span>
          <span class="adm-order-card-badges"><span class="badge ${badgeForStatus(status)}">${statusLabel(status)}</span><span class="badge ${pay==='paid'?'b-green':(pay==='payment_pending'?'b-amber':'b-blue')}">${statusLabel(pay)}</span>${linked?'<span class="badge b-green">Account Linked</span>':'<span class="badge b-amber">Guest</span>'}</span>
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
              <input type="hidden" name="customer_name" value="${attr(q.customer_name)}"><input type="hidden" name="phone" value="${attr(q.phone)}"><input type="hidden" name="email" value="${attr(q.email)}">
              <input type="hidden" name="product_name" value="${attr(q.product_name)}"><input type="hidden" name="size_dimension" value="${attr(q.size_dimension)}"><input type="hidden" name="material_type" value="${attr(q.material_type)}"><input type="hidden" name="quantity" value="${attr(q.quantity)}">
              <input type="hidden" name="payment_status" value="${attr(pay)}"><input type="hidden" name="status" value="${attr(status)}">
              <div class="custom-quote-detail-strip"><div><span>Product</span><strong>${detailValue(q.product_name,'Custom product')}</strong></div><div><span>Size / Dimension</span><strong>${detailValue(q.size_dimension)}</strong></div><div><span>Material</span><strong>${detailValue(q.material_type)}</strong></div><div><span>Quantity</span><strong>${detailValue(q.quantity,'—')}</strong></div></div>
              <div class="custom-quote-instruction"><span>Customer Instructions</span><p>${esc(q.instructions||'No customer instructions provided.')}</p><input type="hidden" name="instructions" value="${attr(q.instructions)}"></div>
              <div class="custom-quote-price-row"><label>Quoted Amount<input class="fi" type="number" step="0.01" name="quoted_amount" value="${attr(q.quoted_amount)}" placeholder="Enter final price"></label></div>
              <label>Admin Quote Note / WhatsApp Details<textarea class="fi custom-quote-note-field" name="quote_note" placeholder="Add final custom order details, terms and approval note for customer...">${esc(q.quote_note)}</textarea></label>
              <div class="adm-order-action-strip custom-quote-actions"><div class="ord-action-strip-copy"><span class="ord-action-strip-label">Next flow</span><small>Save quote → create/link account → send quote → mark approved → payment link.</small></div><div class="ord-actions ord-actions--compact"><button class="aoc-btn aoc-btn--custom-save" type="submit"><span class="aoc-ico">💾</span><span>Save Quote</span></button><button class="aoc-btn aoc-btn--custom-account" type="button" onclick="createCustomerAccount(${id})"><span class="aoc-ico">👤</span><span>${linked?'Linked Account':'Create Account'}</span></button><button class="aoc-btn aoc-btn--custom-wa" type="button" onclick="sendQuoteWhatsApp(${id})"><span class="aoc-ico">💬</span><span>WhatsApp Quote</span></button><button class="aoc-btn aoc-btn--custom-approve" type="button" onclick="markApproved(${id})"><span class="aoc-ico">✅</span><span>Mark Approved</span></button><button class="aoc-btn aoc-btn--custom-payment" type="button" onclick="generatePaymentLink(${id})"><span class="aoc-ico">🔗</span><span>Payment Link</span></button></div></div>
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
async function markApproved(id){const res=await fetch(`/admin/api/custom-orders/${id}/status`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},credentials:'same-origin',body:JSON.stringify({status:'customer_approved'})}).then(r=>r.json()).catch(()=>({ok:false,msg:'Could not update status'})); if(!res.ok){alert(res.msg||'Could not update status');return;} await loadCustomOrders();}
async function sendQuoteWhatsApp(id){const q=quotes.find(x=>Number(x.id)===id); if(!q)return; let msg=''; try{msg=await customQuoteWhatsappMessage(id,'quote');}catch(e){alert(e.message);return;} window.open(`https://wa.me/${waPhone(q.phone)}?text=${encodeURIComponent(msg)}`,'_blank'); await fetch(`/admin/api/custom-orders/${id}/status`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},credentials:'same-origin',body:JSON.stringify({status:'sent_to_customer'})}).catch(()=>{}); setTimeout(loadCustomOrders,500);}
async function createCustomerAccount(id){const q=quotes.find(x=>Number(x.id)===id); if(!q)return; const res=await fetch(`/admin/api/custom-orders/${id}/customer-account`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},credentials:'same-origin',body:JSON.stringify({})}).then(r=>r.json()).catch(()=>({ok:false,msg:'Could not create/link account'})); if(!res.ok){alert(res.msg||'Could not create/link account');return;} alert((res.created?'Customer account created.':'Existing account linked.')+'\nEmail: '+(res.user?.email||q.email||'-')+'\nPassword: '+(res.login_password||'customer mobile number')+'\nThis customer will now appear in Customers page.'); await loadCustomOrders();}
async function generatePaymentLink(id){const q=quotes.find(x=>Number(x.id)===id); if(!q)return; const amount=Number(q.quoted_amount||0); if(amount<=0){alert('Please save quoted amount before generating payment link.');return;} const res=await fetch(`/admin/api/custom-orders/${id}/payment-link`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},credentials:'same-origin',body:JSON.stringify({})}).then(r=>r.json()).catch(()=>({ok:false,msg:'Could not generate payment link'})); if(!res.ok){alert(res.msg||'Could not generate payment link');return;} q.status='payment_pending'; q.payment_status='payment_pending'; let msg=''; try{msg=await customQuoteWhatsappMessage(id,'payment',res.link);}catch(e){alert(e.message);return;} window.open(`https://wa.me/${waPhone(q.phone)}?text=${encodeURIComponent(msg)}`,'_blank'); await loadCustomOrders();}
loadCustomOrders();
</script>
    </div></div></div>
</body></html>
