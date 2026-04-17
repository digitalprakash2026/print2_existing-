<?php
$pageTitle = 'Dashboard — RCS Admin';
$currentAdmPage = 'dashboard';
include __DIR__ . '/layout.php';
?>
<div class="adm-pt">Dashboard</div>

<div class="adm-stats" id="dashStats">
  <div class="ast"><div class="ast-v" style="color:var(--blue)" id="ds-orders">—</div><div class="ast-l">Total Orders</div></div>
  <div class="ast"><div class="ast-v" style="color:var(--green)" id="ds-rev">—</div><div class="ast-l">Total Revenue</div></div>
  <div class="ast"><div class="ast-v" style="color:var(--orange)" id="ds-today">—</div><div class="ast-l">Today Orders</div></div>
  <div class="ast"><div class="ast-v" style="color:var(--amber)" id="ds-pending">—</div><div class="ast-l">In Progress</div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:13px;margin-bottom:13px" id="dash-charts">
  <div class="anl-card">
    <div class="anl-t">Revenue (6 months)</div>
    <div class="rev-chart" id="revChart"><div style="color:var(--text3);font-size:12px;margin:auto">Loading…</div></div>
  </div>
  <div class="anl-card">
    <div class="anl-t">Top Products</div>
    <div id="topProds"><div style="color:var(--text3);font-size:12px">Loading…</div></div>
  </div>
</div>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
  <div style="font-family:'Fraunces',serif;font-size:16px;font-weight:700">Recent Orders</div>
  <a href="/admin/orders" class="btn btn-outline btn-sm">View All →</a>
</div>
<div id="recentOrders"><div style="text-align:center;padding:28px;color:var(--text3)">Loading…</div></div>

<script>
const STATUS_COLORS = {received:'b-blue',processing:'b-amber',printing:'b-orange',ready:'b-green',delivered:'b-ink',cancelled:'b-red',whatsapp_pending:'b-amber'};
const STATUS_LABELS = {received:'Received',processing:'Processing',printing:'Printing',ready:'Ready',delivered:'Delivered',cancelled:'Cancelled',whatsapp_pending:'WA Pending'};

async function loadDash() {
  const res = await fetch('/admin/api/dashboard').then(r=>r.json());
  if (!res.ok) return;

  const s = res.stats;
  document.getElementById('ds-orders').textContent = s.total_orders;
  document.getElementById('ds-rev').textContent = '₹'+Number(s.total_revenue).toLocaleString('en-IN');
  document.getElementById('ds-today').textContent = s.today_orders;
  document.getElementById('ds-pending').textContent = s.pending_orders;

  // Revenue chart
  const monthly = res.monthly || [];
  const maxRev = Math.max(...monthly.map(m=>parseFloat(m.revenue)||0), 1);
  document.getElementById('revChart').innerHTML = monthly.length
    ? monthly.map(m => `<div class="rv-bar-wrap"><div class="rv-val">${parseFloat(m.revenue)>0?'₹'+Math.round(m.revenue/1000)+'k':''}</div><div class="rv-bar ${parseFloat(m.revenue)===maxRev?'hi':''}" style="height:${Math.max(3,(parseFloat(m.revenue)/maxRev)*80)}px"></div><div class="rv-lbl">${escH(m.month)}</div></div>`).join('')
    : '<div style="color:var(--text3);font-size:12px;margin:auto">No data yet</div>';

  // Top products
  document.getElementById('topProds').innerHTML = res.top_products?.length
    ? res.top_products.map(p=>`<div class="anl-row"><span>${escH(p.product_name)}</span><span class="anl-v">${p.count} orders</span></div>`).join('')
    : '<div style="color:var(--text3);font-size:12px">No data yet</div>';

  // Recent orders
  document.getElementById('recentOrders').innerHTML = res.recent_orders?.length
    ? res.recent_orders.map(o=>`
    <div class="aoc">
      <div class="aoc-top">
        <div>
          <div class="aoc-id">#${escH(o.order_id)} · ${new Date(o.created_at).toLocaleDateString('en-IN',{day:'numeric',month:'short'})}</div>
          <div class="aoc-name">${escH(o.customer_name)}</div>
          <div class="aoc-contact">${escH(o.customer_phone)}</div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:5px">
          <span class="badge ${STATUS_COLORS[o.status]||'b-blue'}">${STATUS_LABELS[o.status]||o.status}</span>
          <span style="font-size:12px;font-weight:700;color:var(--blue)">₹${Number(o.total_amount).toLocaleString('en-IN')}</span>
        </div>
      </div>
      <div style="display:flex;gap:5px;padding-top:8px;border-top:1px solid var(--border)">
        <a href="/admin/orders" class="aoc-btn">View</a>
        <a href="/invoice/${escH(o.order_id)}" class="aoc-btn" target="_blank">🧾 Invoice</a>
      </div>
    </div>`).join('')
    : '<div style="text-align:center;padding:44px;color:var(--text2)"><div style="font-size:40px;margin-bottom:9px">📋</div><div>No orders yet</div></div>';
}

function escH(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function toast(msg,type='info'){const w=document.getElementById('tw');const t=document.createElement('div');t.className='toast '+type;t.textContent=msg;w.appendChild(t);requestAnimationFrame(()=>requestAnimationFrame(()=>t.classList.add('show')));setTimeout(()=>{t.classList.remove('show');setTimeout(()=>t.remove(),300);},2800);}

loadDash();
</script>
    </div></div></div>
</body></html>
