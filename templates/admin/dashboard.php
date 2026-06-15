<?php
$pageTitle = 'Dashboard — RCS Admin';
$currentAdmPage = 'dashboard';
include __DIR__ . '/layout.php';
?>
<div class="adm-dash-pro">
  <section class="dash-pro-head">
    <div>
      <h1>Dashboard</h1>
      <p>Welcome back! Here’s what’s happening with your printing business today.</p>
    </div>
    <button class="dash-date-pill" type="button">📅 <span><?= date('d M Y') ?> - <?= date('d M Y') ?></span>⌄</button>
  </section>

  <section class="dash-kpi-grid dash-kpi-grid--top" aria-label="Order summary">
    <article class="dash-kpi-card kpi-blue"><div class="kpi-icon">🛍️</div><div><strong id="ds-new">—</strong><span>New Orders</span><small class="up">↑ Live queue</small></div></article>
    <article class="dash-kpi-card kpi-amber"><div class="kpi-icon">⏳</div><div><strong id="ds-pending">—</strong><span>Pending Orders</span><small class="up">↑ Needs review</small></div></article>
    <article class="dash-kpi-card kpi-purple"><div class="kpi-icon">🖨️</div><div><strong id="ds-production">—</strong><span>Printing Orders</span><small>↗ Production flow</small></div></article>
    <article class="dash-kpi-card kpi-green"><div class="kpi-icon">📦</div><div><strong id="ds-ready">—</strong><span>Ready Orders</span><small class="down">↓ Dispatch queue</small></div></article>
    <article class="dash-kpi-card kpi-mint"><div class="kpi-icon">🚚</div><div><strong id="ds-delivered">—</strong><span>Delivered Orders</span><small class="up">↑ Completed</small></div></article>
    <article class="dash-kpi-card kpi-pink"><div class="kpi-icon">₹</div><div><strong id="ds-rev">—</strong><span>Total Revenue</span><small class="up">↑ Paid orders</small></div></article>
  </section>

  <section class="dash-kpi-grid dash-kpi-grid--sub" aria-label="Revenue and customer summary">
    <article class="dash-kpi-card kpi-lime"><div class="kpi-icon">🛡️</div><div><strong id="ds-today-rev">—</strong><span>Today’s Revenue</span><small class="up">↑ Today</small></div></article>
    <article class="dash-kpi-card kpi-teal"><div class="kpi-icon">📈</div><div><strong id="ds-month-rev">—</strong><span>This Month Revenue</span><small class="up">↑ This month</small></div></article>
    <article class="dash-kpi-card kpi-orange"><div class="kpi-icon">💳</div><div><strong id="ds-payments">—</strong><span>Pending Payments</span><small class="down">↑ Follow up</small></div></article>
    <article class="dash-kpi-card kpi-indigo"><div class="kpi-icon">📊</div><div><strong id="ds-aov">—</strong><span>Average Order Value</span><small class="up">↑ Paid orders</small></div></article>
    <article class="dash-kpi-card kpi-violet"><div class="kpi-icon">👥</div><div><strong id="ds-customers">—</strong><span>Total Customers</span><small class="up">↑ Customer base</small></div></article>
  </section>

  <section class="dash-mid-grid">
    <article class="dash-card dash-revenue-card">
      <div class="dash-card-head"><div><h2>Revenue Overview</h2><p>Paid order revenue over the last six months.</p></div><button type="button" class="dash-mini-select">Last 6 Months⌄</button></div>
      <div class="dash-line-chart" id="revChart"><div class="adm-empty-state">Loading…</div></div>
    </article>

    <article class="dash-card dash-queue-card">
      <div class="dash-card-head"><div><h2>Production Queue</h2><p>Track design, approval, printing and dispatch.</p></div><a href="/admin/orders?status=attention" class="dash-view-link">View All</a></div>
      <div class="dash-queue-list" id="prodQueue"><div class="adm-empty-state">Loading…</div></div>
    </article>

    <article class="dash-card dash-products-card">
      <div class="dash-card-head"><div><h2>Top Products</h2><p>Highest ordered catalog items.</p></div><a href="/admin/products" class="dash-view-link">View All</a></div>
      <div class="dash-products-list" id="topProds"><div class="adm-empty-state">Loading…</div></div>
    </article>
  </section>

  <section class="dash-bottom-grid">
    <article class="dash-card dash-orders-card">
      <div class="dash-card-head"><div><h2>Recent New Orders</h2><p>Unseen orders highlighted for fast follow-up.</p></div><a href="/admin/orders?seen=new" class="dash-view-link">View All Orders</a></div>
      <div class="dash-orders-table-wrap">
        <table class="dash-orders-table">
          <thead><tr><th>Order ID</th><th>Customer</th><th>Product</th><th>Amount</th><th>Status</th><th>Time</th><th></th></tr></thead>
          <tbody id="newOrdersList"><tr><td colspan="7"><div class="adm-empty-state">Loading…</div></td></tr></tbody>
        </table>
      </div>
    </article>

    <article class="dash-card dash-actions-card">
      <div class="dash-card-head"><div><h2>Quick Actions</h2><p>Common admin shortcuts.</p></div></div>
      <div class="dash-actions-grid">
        <a href="/admin/products/new"><span class="qa-blue">📦</span><b>Add Product</b></a>
        <a href="/admin/orders"><span class="qa-green">📋</span><b>View Orders</b></a>
        <a href="/admin/coupons"><span class="qa-purple">🏷️</span><b>Create Coupon</b></a>
        <a href="/admin/export/orders" target="_blank"><span class="qa-orange">⬇️</span><b>Export Orders</b></a>
        <a href="/admin/customers"><span class="qa-pink">👤</span><b>Manage Users</b></a>
        <a href="/admin/design"><span class="qa-cyan">✏️</span><b>Design Studio</b></a>
        <a href="/admin/analytics"><span class="qa-indigo">📊</span><b>Reports</b></a>
        <a href="/admin/settings"><span class="qa-slate">⚙️</span><b>Settings</b></a>
      </div>
    </article>
  </section>
</div>

<script>
const STATUS_COLORS = {received:'b-blue',processing:'b-amber',printing:'b-orange',ready:'b-green',delivered:'b-ink',cancelled:'b-red',whatsapp_pending:'b-amber'};
const STATUS_LABELS = {received:'Received',processing:'Processing',printing:'Printing',ready:'Ready',delivered:'Delivered',cancelled:'Cancelled',whatsapp_pending:'WA Pending'};
const money = n => '₹'+Number(n||0).toLocaleString('en-IN');
function escH(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function pct(part,total){return Math.max(4, Math.round((Number(part||0) / Math.max(Number(total||0), 1)) * 100));}

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
  document.getElementById('ds-month-rev').textContent = money(s.month_revenue || 0);
  document.getElementById('ds-payments').textContent = money(s.pending_payments || 0);
  document.getElementById('ds-aov').textContent = money(s.avg_order_value || 0);
  document.getElementById('ds-customers').textContent = Number(s.total_customers||0).toLocaleString('en-IN');

  renderLineChart(res.monthly || []);
  renderQueue(res.queue || {});
  renderTopProducts(res.top_products || []);
  renderNewOrders(res.recent_new_orders || []);
}

function renderLineChart(monthly) {
  const chart = document.getElementById('revChart');
  if (!monthly.length) { chart.innerHTML = '<div class="adm-empty-state">No revenue data yet.</div>'; return; }
  const values = monthly.map(m => Number(m.revenue || 0));
  const max = Math.max(...values, 1);
  const w = 640, h = 250, pad = 34;
  const step = (w - pad * 2) / Math.max(monthly.length - 1, 1);
  const pts = monthly.map((m,i) => [pad + (i * step), h - pad - ((Number(m.revenue || 0) / max) * (h - pad * 2))]);
  const path = pts.map((p,i)=>`${i?'L':'M'}${p[0].toFixed(1)} ${p[1].toFixed(1)}`).join(' ');
  const area = `${path} L${pts[pts.length-1][0].toFixed(1)} ${h-pad} L${pad} ${h-pad} Z`;
  const yLabels = [max, max*.75, max*.5, max*.25, 0];
  chart.innerHTML = `<svg viewBox="0 0 ${w} ${h}" role="img" aria-label="Revenue overview chart">
    <defs><linearGradient id="revFill" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#2563eb" stop-opacity=".18"/><stop offset="1" stop-color="#2563eb" stop-opacity=".02"/></linearGradient></defs>
    ${yLabels.map((v,i)=>`<g><line x1="${pad}" y1="${pad+i*((h-pad*2)/4)}" x2="${w-pad}" y2="${pad+i*((h-pad*2)/4)}"/><text x="0" y="${pad+i*((h-pad*2)/4)+4}">${money(v).replace('.00','')}</text></g>`).join('')}
    <path d="${area}" fill="url(#revFill)"></path><path d="${path}" fill="none" stroke="#2563eb" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"></path>
    ${pts.map((p,i)=>`<circle cx="${p[0]}" cy="${p[1]}" r="5"/><text class="x" x="${p[0]}" y="${h-8}" text-anchor="middle">${escH(monthly[i].month)}</text>`).join('')}
  </svg>`;
}

function renderQueue(q) {
  const items = [
    ['Design Pending','Waiting for design work', q.design_pending || 0, '✂️','purple'],
    ['Customer Approval','Waiting for customer approval', q.approval_pending || 0, '⌘','orange'],
    ['Printing','In printing process', q.printing || 0, '🖨️','blue'],
    ['Packing','Ready for packaging', q.packing || 0, '▣','cyan'],
    ['Ready for Delivery','Ready to dispatch', q.ready_delivery || 0, '🚚','green']
  ];
  document.getElementById('prodQueue').innerHTML = items.map(([title,sub,count,icon,tone]) => `<div class="dash-queue-row"><span class="${tone}">${icon}</span><div><strong>${title}</strong><small>${sub}</small></div><b class="${tone}">${count}</b></div>`).join('');
}

function renderTopProducts(products) {
  const wrap = document.getElementById('topProds');
  if (!products.length) { wrap.innerHTML = '<div class="adm-empty-state">No product data yet.</div>'; return; }
  const total = products.reduce((sum,p)=>sum + Number(p.count || 0), 0);
  wrap.innerHTML = products.slice(0,8).map(p => `<div class="dash-product-row"><div><strong>${escH(p.product_name)}</strong><small>${Number(p.count||0)} orders</small></div><em><i style="width:${pct(p.count,total)}%"></i></em><b>${pct(p.count,total)}%</b></div>`).join('');
}

function renderNewOrders(orders) {
  const body = document.getElementById('newOrdersList');
  if (!orders.length) { body.innerHTML = '<tr><td colspan="7"><div class="adm-empty-state">🎉 No new orders pending review.</div></td></tr>'; return; }
  body.innerHTML = orders.map(o => `<tr>
    <td><a href="/admin/orders?search=${encodeURIComponent(o.order_id)}">#${escH(o.order_id)}</a></td>
    <td>${escH(o.customer_name || '-')}</td>
    <td>${escH(o.product_summary || (Number(o.item_count||0) + ' item(s)'))}</td>
    <td>${money(o.total_amount)}</td>
    <td><span class="dash-status ${STATUS_COLORS[o.status]||'b-blue'}">${STATUS_LABELS[o.status]||escH(o.status)}</span></td>
    <td>${formatDate(o.created_at)}</td>
    <td><span class="dash-new-badge">NEW</span></td>
  </tr>`).join('');
}
function formatDate(d){const dt = new Date(String(d).replace(' ', 'T')); return Number.isNaN(dt.getTime()) ? escH(d) : dt.toLocaleDateString('en-IN',{day:'2-digit',month:'short'})+', '+dt.toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit'});}
loadDash();
</script>
    </div></div></div>
</body></html>
