<?php
$pageTitle = 'Dashboard — RCS Admin';
$currentAdmPage = 'dashboard';
include __DIR__ . '/layout.php';
?>
<div class="dash-ref-page">
  <div class="dash-ref-titlebar">
    <div>
      <h1>Dashboard</h1>
      <p>Welcome back! Here’s what’s happening with your business today.</p>
    </div>
    <button class="dash-ref-date" type="button">📅 <span><?= date('d M Y') ?> - <?= date('d M Y') ?></span>⌄</button>
  </div>

  <section class="dash-ref-kpis dash-ref-kpis-top" aria-label="Primary dashboard metrics">
    <article class="dash-ref-kpi kpi-bag"><span>🛍️</span><div><b id="ds-new">—</b><strong>New Orders</strong><small class="pos">↑ Live orders</small></div></article>
    <article class="dash-ref-kpi kpi-hour"><span>⌛</span><div><b id="ds-pending">—</b><strong>Pending Orders</strong><small class="pos">↑ Needs review</small></div></article>
    <article class="dash-ref-kpi kpi-print"><span>🖨️</span><div><b id="ds-production">—</b><strong>Printing Orders</strong><small>↗ In production</small></div></article>
    <article class="dash-ref-kpi kpi-box"><span>📦</span><div><b id="ds-ready">—</b><strong>Ready Orders</strong><small class="neg">↓ Ready queue</small></div></article>
    <article class="dash-ref-kpi kpi-truck"><span>🚚</span><div><b id="ds-delivered">—</b><strong>Delivered Orders</strong><small class="pos">↑ Completed</small></div></article>
    <article class="dash-ref-kpi kpi-rupee"><span>₹</span><div><b id="ds-rev">—</b><strong>Total Revenue</strong><small class="pos">↑ Paid orders</small></div></article>
  </section>

  <section class="dash-ref-kpis dash-ref-kpis-sub" aria-label="Secondary dashboard metrics">
    <article class="dash-ref-kpi kpi-shield"><span>🛡️</span><div><b id="ds-today-rev">—</b><strong>Today’s Revenue</strong><small class="pos">↑ Today</small></div></article>
    <article class="dash-ref-kpi kpi-chart"><span>📈</span><div><b id="ds-month-rev">—</b><strong>This Month Revenue</strong><small class="pos">↑ This month</small></div></article>
    <article class="dash-ref-kpi kpi-wallet"><span>💳</span><div><b id="ds-payments">—</b><strong>Pending Payments</strong><small class="neg">↑ Follow up</small></div></article>
    <article class="dash-ref-kpi kpi-aov"><span>📊</span><div><b id="ds-aov">—</b><strong>Average Order Value</strong><small class="pos">↑ Paid orders</small></div></article>
    <article class="dash-ref-kpi kpi-users"><span>👥</span><div><b id="ds-customers">—</b><strong>Total Customers</strong><small class="pos">↑ Customer base</small></div></article>
  </section>

  <section class="dash-ref-main-row">
    <article class="dash-ref-card dash-ref-revenue">
      <div class="dash-ref-card-head"><div><h2>Revenue Overview</h2></div><button class="dash-ref-select" type="button">Last 6 Months⌄</button></div>
      <div class="dash-ref-chart" id="revChart"><div class="dash-ref-empty">Loading…</div></div>
    </article>

    <article class="dash-ref-card dash-ref-queue">
      <div class="dash-ref-card-head"><div><h2>Production Queue</h2></div><a href="/admin/orders?status=attention">View All</a></div>
      <div class="dash-ref-queue-list" id="prodQueue"><div class="dash-ref-empty">Loading…</div></div>
    </article>

    <article class="dash-ref-card dash-ref-products">
      <div class="dash-ref-card-head"><div><h2>Top Products</h2></div><a href="/admin/products">View All</a></div>
      <div class="dash-ref-product-list" id="topProds"><div class="dash-ref-empty">Loading…</div></div>
    </article>
  </section>

  <section class="dash-ref-bottom-row">
    <article class="dash-ref-card dash-ref-orders">
      <div class="dash-ref-card-head"><div><h2>Recent New Orders</h2></div><a href="/admin/orders?seen=new">View All Orders</a></div>
      <div class="dash-ref-table-wrap">
        <table class="dash-ref-table">
          <thead><tr><th>Order ID</th><th>Customer</th><th>Product</th><th>Amount</th><th>Status</th><th>Time</th><th></th></tr></thead>
          <tbody id="newOrdersList"><tr><td colspan="7"><div class="dash-ref-empty">Loading…</div></td></tr></tbody>
        </table>
      </div>
    </article>

    <article class="dash-ref-card dash-ref-actions">
      <div class="dash-ref-card-head"><div><h2>Quick Actions</h2></div></div>
      <div class="dash-ref-actions-grid">
        <a href="/admin/products/new"><span class="c-blue">📦</span><b>Add Product</b></a>
        <a href="/admin/orders"><span class="c-green">📋</span><b>View Orders</b></a>
        <a href="/admin/coupons"><span class="c-purple">🏷️</span><b>Create Coupon</b></a>
        <a href="/admin/export/orders" target="_blank"><span class="c-orange">⬇️</span><b>Export Orders</b></a>
        <a href="/admin/customers"><span class="c-pink">👤</span><b>Manage Users</b></a>
        <a href="/admin/design"><span class="c-cyan">✎</span><b>Design Studio</b></a>
        <a href="/admin/analytics"><span class="c-indigo">▮</span><b>Reports</b></a>
        <a href="/admin/settings"><span class="c-slate">⚙️</span><b>Settings</b></a>
      </div>
    </article>
  </section>
</div>

<script>
const STATUS_COLORS = {received:'st-blue',processing:'st-amber',printing:'st-orange',ready:'st-green',delivered:'st-ink',cancelled:'st-red',whatsapp_pending:'st-amber'};
const STATUS_LABELS = {received:'Received',processing:'Processing',printing:'Printing',ready:'Ready',delivered:'Delivered',cancelled:'Cancelled',whatsapp_pending:'WA Pending'};
const money = n => '₹'+Number(n||0).toLocaleString('en-IN');
const escH = s => String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
const pct = (part,total) => Math.max(3, Math.round((Number(part||0) / Math.max(Number(total||0), 1)) * 100));

async function loadDash(){
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
  document.getElementById('ds-month-rev').textContent = money(s.month_revenue || 0);
  document.getElementById('ds-payments').textContent = money(s.pending_payments || 0);
  document.getElementById('ds-aov').textContent = money(s.avg_order_value || 0);
  document.getElementById('ds-customers').textContent = Number(s.total_customers||0).toLocaleString('en-IN');
  drawRevenue(res.monthly || []);
  drawQueue(res.queue || {});
  drawProducts(res.top_products || []);
  drawOrders(res.recent_new_orders || []);
}
function drawRevenue(monthly){
  const el = document.getElementById('revChart');
  if (!monthly.length) { el.innerHTML = '<div class="dash-ref-empty">No revenue data yet.</div>'; return; }
  const w=720,h=318,padL=66,padR=22,padT=24,padB=44;
  const vals = monthly.map(m=>Number(m.revenue||0));
  const max = Math.max(...vals, 1);
  const plotW = w-padL-padR, plotH = h-padT-padB;
  const pts = monthly.map((m,i)=>[padL + (i*(plotW/Math.max(monthly.length-1,1))), padT + plotH - ((Number(m.revenue||0)/max)*plotH)]);
  const path = pts.map((p,i)=>`${i?'L':'M'}${p[0].toFixed(1)} ${p[1].toFixed(1)}`).join(' ');
  const area = `${path} L${pts.at(-1)[0].toFixed(1)} ${h-padB} L${padL} ${h-padB} Z`;
  const labels = [max, max*.75, max*.5, max*.25, 0];
  el.innerHTML = `<svg viewBox="0 0 ${w} ${h}" aria-label="Revenue overview" role="img">
    <defs><linearGradient id="dashRefRev" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#2563eb" stop-opacity=".18"/><stop offset="1" stop-color="#2563eb" stop-opacity=".03"/></linearGradient></defs>
    ${labels.map((v,i)=>{const y=padT+i*(plotH/4);return `<line x1="${padL}" y1="${y}" x2="${w-padR}" y2="${y}"/><text x="${padL-12}" y="${y+4}" text-anchor="end">${money(Math.round(v))}</text>`}).join('')}
    <path d="${area}" fill="url(#dashRefRev)"></path><path d="${path}" fill="none" stroke="#2563eb" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"></path>
    ${pts.map((p,i)=>`<circle cx="${p[0]}" cy="${p[1]}" r="5"/><text class="month" x="${p[0]}" y="${h-10}" text-anchor="middle">${escH(monthly[i].month)}</text>`).join('')}
  </svg>`;
}
function drawQueue(q){
  const rows = [
    ['Design Pending','Waiting for design work', q.design_pending || 0, '✂','purple'],
    ['Customer Approval','Waiting for customer approval', q.approval_pending || 0, '⌘','orange'],
    ['Printing','In printing process', q.printing || 0, '▣','blue'],
    ['Packing','Ready for packaging', q.packing || 0, '▤','cyan'],
    ['Ready for Delivery','Ready to dispatch', q.ready_delivery || 0, '▰','green']
  ];
  document.getElementById('prodQueue').innerHTML = rows.map(r=>`<div class="dash-ref-queue-row"><span class="${r[4]}">${r[3]}</span><div><strong>${r[0]}</strong><small>${r[1]}</small></div><b class="${r[4]}">${r[2]}</b></div>`).join('');
}
function drawProducts(products){
  const el = document.getElementById('topProds');
  if (!products.length) { el.innerHTML = '<div class="dash-ref-empty">No product data yet.</div>'; return; }
  const total = products.reduce((sum,p)=>sum+Number(p.count||0),0);
  el.innerHTML = products.slice(0,8).map(p=>`<div class="dash-ref-product-row"><div><strong>${escH(p.product_name)}</strong><small>${Number(p.count||0)} orders</small></div><em><i style="width:${pct(p.count,total)}%"></i></em><b>${pct(p.count,total)}%</b></div>`).join('');
}
function drawOrders(orders){
  const el = document.getElementById('newOrdersList');
  if (!orders.length) { el.innerHTML = '<tr><td colspan="7"><div class="dash-ref-empty">No new orders pending review.</div></td></tr>'; return; }
  el.innerHTML = orders.map(o=>`<tr><td><a href="/admin/orders?search=${encodeURIComponent(o.order_id)}">#${escH(o.order_id)}</a></td><td>${escH(o.customer_name||'-')}</td><td>${escH(o.product_summary || (Number(o.item_count||0)+' item(s)'))}</td><td>${money(o.total_amount)}</td><td><span class="dash-ref-status ${STATUS_COLORS[o.status]||'st-blue'}">${STATUS_LABELS[o.status]||escH(o.status)}</span></td><td>${fmt(o.created_at)}</td><td><span class="dash-ref-new">NEW</span></td></tr>`).join('');
}
function fmt(d){const dt=new Date(String(d).replace(' ','T'));return Number.isNaN(dt.getTime())?escH(d):dt.toLocaleDateString('en-IN',{day:'2-digit',month:'short'})+', '+dt.toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit'});}
loadDash();
</script>
    </div></div></div>
</body></html>
