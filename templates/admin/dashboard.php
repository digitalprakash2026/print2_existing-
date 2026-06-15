<?php
$pageTitle = 'Dashboard — RCS Admin';
$currentAdmPage = 'dashboard';
include __DIR__ . '/layout.php';
?>
<div class="adm-dash-page">
  <section class="adm-dash-hero">
    <div>
      <div class="adm-orders-kicker">Admin overview</div>
      <div class="adm-pt" style="margin:0">Dashboard</div>
      <div class="adm-orders-sub">Monitor new orders, production flow, revenue, and quick actions from one clean workspace.</div>
    </div>
    <div class="adm-dash-actions">
      <a href="/admin/products/new" class="btn btn-blue btn-sm">+ Add Product</a>
      <a href="/admin/orders" class="btn btn-outline btn-sm">View Orders</a>
      <a href="/admin/coupons" class="btn btn-outline btn-sm">Create Coupon</a>
      <a href="/admin/export/orders" class="btn btn-outline btn-sm" target="_blank">Export Orders</a>
    </div>
  </section>

  <div class="adm-kpi-grid" id="dashKpis">
    <div class="adm-kpi-card is-blue"><span>🆕</span><b id="ds-new">—</b><small>New Orders</small></div>
    <div class="adm-kpi-card is-amber"><span>⚠️</span><b id="ds-pending">—</b><small>Pending Orders</small></div>
    <div class="adm-kpi-card is-orange"><span>⚙️</span><b id="ds-production">—</b><small>Processing / Printing</small></div>
    <div class="adm-kpi-card is-green"><span>✅</span><b id="ds-ready">—</b><small>Ready Orders</small></div>
    <div class="adm-kpi-card is-slate"><span>📦</span><b id="ds-delivered">—</b><small>Delivered</small></div>
    <div class="adm-kpi-card is-blue"><span>💰</span><b id="ds-rev">—</b><small>Total Revenue</small></div>
    <div class="adm-kpi-card is-green"><span>📈</span><b id="ds-today-rev">—</b><small>Today Revenue</small></div>
    <div class="adm-kpi-card is-red"><span>💳</span><b id="ds-payments">—</b><small>Pending Payments</small></div>
  </div>

  <div class="adm-dash-grid-main">
    <section class="adm-panel-card adm-panel-card--wide">
      <div class="adm-panel-head"><div><strong>Recent New Orders</strong><small>Unseen / fresh orders that need admin review.</small></div><a href="/admin/orders?seen=new" class="btn btn-outline btn-sm">Review all</a></div>
      <div id="newOrdersList" class="adm-new-orders-list"><div class="adm-empty-state">Loading…</div></div>
    </section>
    <section class="adm-panel-card">
      <div class="adm-panel-head"><div><strong>Production Queue</strong><small>Print workflow snapshot.</small></div></div>
      <div id="prodQueue" class="adm-queue-list"><div class="adm-empty-state">Loading…</div></div>
    </section>
  </div>

  <div class="adm-dash-charts adm-dash-charts--premium" id="dash-charts">
    <div class="anl-card adm-panel-card">
      <div class="adm-panel-head"><div><strong>Revenue (6 months)</strong><small>Paid order revenue trend.</small></div></div>
      <div class="rev-chart" id="revChart"><div class="adm-empty-state">Loading…</div></div>
    </div>
    <div class="anl-card adm-panel-card">
      <div class="adm-panel-head"><div><strong>Top Products</strong><small>Most ordered printing products.</small></div></div>
      <div id="topProds"><div class="adm-empty-state">Loading…</div></div>
    </div>
  </div>

  <div class="adm-panel-card">
    <div class="adm-panel-head"><div><strong>Recent Orders</strong><small>Latest activity across all statuses.</small></div><a href="/admin/orders" class="btn btn-outline btn-sm">View All →</a></div>
    <div id="recentOrders"><div class="adm-empty-state">Loading…</div></div>
  </div>
</div>

<script>
const STATUS_COLORS = {received:'b-blue',processing:'b-amber',printing:'b-orange',ready:'b-green',delivered:'b-ink',cancelled:'b-red',whatsapp_pending:'b-amber'};
const STATUS_LABELS = {received:'Received',processing:'Processing',printing:'Printing',ready:'Ready',delivered:'Delivered',cancelled:'Cancelled',whatsapp_pending:'WA Pending'};
const money = n => '₹'+Number(n||0).toLocaleString('en-IN');

async function loadDash() {
  const res = await fetch('/admin/api/dashboard').then(r=>r.json());
  if (!res.ok) return;

  const s = res.stats || {};
  document.getElementById('ds-new').textContent = Number(s.new_orders||0).toLocaleString('en-IN');
  document.getElementById('ds-pending').textContent = Number(s.pending_orders||0).toLocaleString('en-IN');
  document.getElementById('ds-production').textContent = Number(s.production_orders||0).toLocaleString('en-IN');
  document.getElementById('ds-ready').textContent = Number(s.ready_orders||0).toLocaleString('en-IN');
  document.getElementById('ds-delivered').textContent = Number(s.delivered_orders||0).toLocaleString('en-IN');
  document.getElementById('ds-rev').textContent = money(s.total_revenue);
  document.getElementById('ds-today-rev').textContent = money(s.today_revenue);
  document.getElementById('ds-payments').textContent = Number(s.pending_payments||0).toLocaleString('en-IN');

  const newOrders = res.recent_new_orders || [];
  document.getElementById('newOrdersList').innerHTML = newOrders.length ? newOrders.map(o => orderMini(o, true)).join('') : '<div class="adm-empty-state">🎉 No new orders pending review.</div>';

  const q = res.queue || {};
  const queueItems = [
    ['Design Pending', q.design_pending || 0, '🎨'], ['Customer Approval', q.approval_pending || 0, '💬'],
    ['Printing', q.printing || 0, '🖨️'], ['Packing', q.packing || 0, '📦'], ['Ready for Delivery', q.ready_delivery || 0, '✅']
  ];
  const maxQ = Math.max(...queueItems.map(i=>Number(i[1])||0), 1);
  document.getElementById('prodQueue').innerHTML = queueItems.map(([label,count,icon]) => `<div class="adm-queue-row"><span>${icon}</span><div><strong>${label}</strong><em style="width:${Math.max(6,(count/maxQ)*100)}%"></em></div><b>${count}</b></div>`).join('');

  const monthly = res.monthly || [];
  const maxRev = Math.max(...monthly.map(m=>parseFloat(m.revenue)||0), 1);
  document.getElementById('revChart').innerHTML = monthly.length
    ? monthly.map(m => `<div class="rv-bar-wrap"><div class="rv-val">${parseFloat(m.revenue)>0?'₹'+Math.round(m.revenue/1000)+'k':''}</div><div class="rv-bar ${parseFloat(m.revenue)===maxRev?'hi':''}" style="height:${Math.max(8,(parseFloat(m.revenue)/maxRev)*92)}px"></div><div class="rv-lbl">${escH(m.month)}</div></div>`).join('')
    : '<div class="adm-empty-state">No revenue data yet.</div>';

  const top = res.top_products || [];
  const maxTop = Math.max(...top.map(p=>Number(p.count)||0), 1);
  document.getElementById('topProds').innerHTML = top.length
    ? top.map(p=>`<div class="adm-product-progress"><div><span>${escH(p.product_name)}</span><b>${Number(p.count||0)} orders</b></div><em><i style="width:${Math.max(6,(Number(p.count||0)/maxTop)*100)}%"></i></em></div>`).join('')
    : '<div class="adm-empty-state">No product data yet.</div>';

  document.getElementById('recentOrders').innerHTML = res.recent_orders?.length
    ? res.recent_orders.map(o=>orderMini(o, false)).join('')
    : '<div class="adm-empty-state">📋 No orders yet.</div>';
}

function orderMini(o, isNew) {
  return `<div class="adm-order-mini ${isNew ? 'is-new' : ''}">
    <div><strong>#${escH(o.order_id)}</strong>${isNew ? '<span>NEW</span>' : ''}<small>${escH(o.customer_name)} · ${escH(o.customer_phone || '')}</small></div>
    <div><b>${money(o.total_amount)}</b><small class="badge ${STATUS_COLORS[o.status]||'b-blue'}">${STATUS_LABELS[o.status]||o.status}</small></div>
    <a href="/admin/orders?search=${encodeURIComponent(o.order_id)}" class="btn btn-outline btn-sm">Open</a>
  </div>`;
}
function escH(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function toast(msg,type='info'){const w=document.getElementById('tw');const t=document.createElement('div');t.className='toast '+type;t.textContent=msg;w.appendChild(t);requestAnimationFrame(()=>requestAnimationFrame(()=>t.classList.add('show')));setTimeout(()=>{t.classList.remove('show');setTimeout(()=>t.remove(),300);},2800);}

loadDash();
</script>
    </div></div></div>
</body></html>
