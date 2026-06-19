<?php
$pageTitle = 'Leads — RCS Admin';
$currentAdmPage = 'leads';
$admMainClass = 'adm-main--leads';
include __DIR__ . '/layout.php';
?>
<div class="leads-page">
  <section class="leads-hero">
    <div><span>Sales Pipeline</span><h1>Contact Leads</h1><p>Capture contact form enquiries, follow up on WhatsApp, and move each lead from new to converted.</p></div>
    <button class="leads-export" type="button" onclick="exportLeadsCsv()">⬇ Export CSV</button>
  </section>
  <section class="leads-stats" id="leadStats">
    <article><span>New</span><strong>--</strong></article><article><span>Contacted</span><strong>--</strong></article><article><span>Quoted</span><strong>--</strong></article><article><span>Converted</span><strong>--</strong></article><article><span>Urgent</span><strong>--</strong></article>
  </section>
  <section class="leads-toolbar">
    <label><span>🔎</span><input id="leadSearch" type="search" placeholder="Search name, phone, email, subject..."></label>
    <div class="leads-tabs">
      <button class="act" data-filter="all">All</button><button data-filter="new">New</button><button data-filter="contacted">Contacted</button><button data-filter="quoted">Quoted</button><button data-filter="converted">Converted</button><button data-filter="closed">Closed</button><button data-filter="spam">Spam</button>
    </div>
  </section>
  <div id="leadList" class="leads-grid"><div class="leads-empty">Loading leads…</div></div>
</div>
<aside class="lead-drawer" id="leadDrawer" aria-hidden="true"><div class="lead-drawer-panel"><button type="button" onclick="closeLeadDrawer()" class="lead-close">×</button><div id="leadDrawerBody"></div></div></aside>
<script>
let LEADS = [];
let LEAD_FILTER = 'all';
const escLead = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
const leadDate = s => s ? new Date(String(s).replace(' ', 'T')).toLocaleString('en-IN',{day:'numeric',month:'short',hour:'2-digit',minute:'2-digit'}) : '-';
const leadDigits = s => String(s || '').replace(/\D/g,'');
async function loadLeads(){ const res = await fetch('/admin/api/leads',{credentials:'same-origin'}).then(r=>r.json()); LEADS = res.leads || []; renderLeadStats(res.summary || {}); renderLeads(); }
function renderLeadStats(s){ const vals=[s.new||0,s.contacted||0,s.quoted||0,s.converted||0,s.urgent||0]; document.querySelectorAll('#leadStats strong').forEach((el,i)=>el.textContent=Number(vals[i]).toLocaleString('en-IN')); }
function filteredLeads(){ const q=document.getElementById('leadSearch').value.trim().toLowerCase(); return LEADS.filter(l=>{ const hay=`${l.name||''} ${l.phone||''} ${l.email||''} ${l.subject||''} ${l.message||''}`.toLowerCase(); return (!q || hay.includes(q)) && (LEAD_FILTER==='all' || l.status===LEAD_FILTER); }); }
function priorityLabel(p){ return p === 'urgent' ? '🔥 Urgent' : p === 'high' ? '⚡ High' : 'Normal'; }
function renderLeads(){ const list=filteredLeads(); const wrap=document.getElementById('leadList'); if(!list.length){wrap.innerHTML='<div class="leads-empty">No leads found.</div>';return;} wrap.innerHTML=list.map(l=>`<article class="lead-card lead-card--${escLead(l.status)}"><button type="button" onclick="openLead(${Number(l.id)})"><span class="lead-priority lead-priority--${escLead(l.priority)}">${priorityLabel(l.priority)}</span><strong>${escLead(l.name)}</strong><small>${escLead(l.phone||'-')} · ${escLead(l.email||'-')}</small><b>${escLead(l.subject)}</b><p>${escLead(l.message).slice(0,150)}</p></button><div class="lead-card-foot"><span>${escLead(l.status)}</span><em>${leadDate(l.created_at)}</em></div><div class="lead-actions"><button onclick="waLead(${Number(l.id)})">💬 WA</button><a href="tel:${leadDigits(l.phone)}">📞 Call</a><button onclick="quickStatus(${Number(l.id)},'contacted')">✓ Contacted</button></div></article>`).join(''); }
function openLead(id){ const l=LEADS.find(x=>Number(x.id)===Number(id)); if(!l)return; document.getElementById('leadDrawerBody').innerHTML=`<div class="lead-drawer-head"><span class="lead-priority lead-priority--${escLead(l.priority)}">${priorityLabel(l.priority)}</span><h2>${escLead(l.name)}</h2><p>${escLead(l.subject)}</p></div><section><h3>Message</h3><p>${escLead(l.message)}</p></section><section><h3>Contact</h3><p>${escLead(l.phone||'-')}<br>${escLead(l.email||'-')}</p><div class="lead-drawer-actions"><button onclick="waLead(${Number(l.id)})">WhatsApp</button><a href="tel:${leadDigits(l.phone)}">Call</a><a href="mailto:${escLead(l.email)}">Email</a></div></section><section><h3>Pipeline</h3><div class="lead-status-grid">${['new','contacted','quoted','converted','closed','spam'].map(st=>`<button class="${l.status===st?'act':''}" onclick="quickStatus(${Number(l.id)},'${st}')">${st}</button>`).join('')}</div></section><section><h3>Admin Note</h3><textarea id="leadNote">${escLead(l.admin_note||'')}</textarea><button class="lead-save-note" onclick="saveLeadNote(${Number(l.id)})">Save Note</button></section>${l.matched_customer?`<section><h3>Matched Customer</h3><p>${escLead(l.matched_customer.name)}<br>${escLead(l.matched_customer.phone||l.matched_customer.email||'')}</p><a href="/admin/customers?search=${encodeURIComponent(l.matched_customer.phone||l.matched_customer.email||'')}">Open Customer CRM</a></section>`:''}`; document.getElementById('leadDrawer').classList.add('open'); document.getElementById('leadDrawer').setAttribute('aria-hidden','false'); }
function closeLeadDrawer(){ document.getElementById('leadDrawer').classList.remove('open'); document.getElementById('leadDrawer').setAttribute('aria-hidden','true'); }
async function updateLead(id,payload){ const res=await fetch(`/admin/api/leads/${id}`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'<?= htmlspecialchars($csrf ?? '') ?>'},credentials:'same-origin',body:JSON.stringify(payload)}).then(r=>r.json()); if(!res.ok) return alert(res.msg||'Could not update lead'); await loadLeads(); const drawer=document.getElementById('leadDrawer'); if(drawer.classList.contains('open')) openLead(id); }
function quickStatus(id,status){ updateLead(id,{status}); }
function saveLeadNote(id){ updateLead(id,{admin_note:document.getElementById('leadNote')?.value||''}); }
function waLead(id){ const l=LEADS.find(x=>Number(x.id)===Number(id)); if(!l)return; const phone=leadDigits(l.phone); if(!phone)return alert('Phone number not available'); const first=String(l.name||'Customer').split(' ')[0]; const msg=`Hi ${first} ji, thanks for contacting RCS Graphic. We received your enquiry: ${l.subject}. Please share any artwork/details so we can guide you quickly.`; window.open(`https://wa.me/${phone}?text=${encodeURIComponent(msg)}`,'_blank'); }
function exportLeadsCsv(){ const rows=[['Name','Phone','Email','Subject','Status','Priority','Created']].concat(filteredLeads().map(l=>[l.name,l.phone,l.email,l.subject,l.status,l.priority,l.created_at])); const csv=rows.map(r=>r.map(v=>'"'+String(v??'').replace(/"/g,'""')+'"').join(',')).join('\n'); const a=document.createElement('a'); a.href=URL.createObjectURL(new Blob([csv],{type:'text/csv'})); a.download='contact-leads.csv'; a.click(); URL.revokeObjectURL(a.href); }
document.querySelectorAll('.leads-tabs button').forEach(btn=>btn.addEventListener('click',()=>{document.querySelectorAll('.leads-tabs button').forEach(b=>b.classList.remove('act'));btn.classList.add('act');LEAD_FILTER=btn.dataset.filter||'all';renderLeads();}));
document.getElementById('leadSearch').addEventListener('input',renderLeads);
loadLeads();
</script>
