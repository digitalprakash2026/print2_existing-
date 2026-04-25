<?php
$pageTitle = 'Orders — RCS Admin';
$currentAdmPage = 'orders';
include __DIR__ . '/layout.php';
$statusColors = ['received'=>'b-blue','processing'=>'b-amber','printing'=>'b-orange','ready'=>'b-green','delivered'=>'b-ink','cancelled'=>'b-red','whatsapp_pending'=>'b-amber'];
$statusLabels = ['received'=>'Received','processing'=>'Processing','printing'=>'Printing','ready'=>'Ready','delivered'=>'Delivered','cancelled'=>'Cancelled','whatsapp_pending'=>'WA Pending'];
$orders = $orders ?? [];
$total = (int)($total ?? 0);
$page = (int)($page ?? 1);
$perPage = (int)($perPage ?? 12);
$search = $search ?? '';
$status = $status ?? 'all';
?>

<div class="adm-orders-head">
  <div>
    <div class="adm-pt" style="margin:0">Order Management</div>
    <div class="adm-orders-sub">Track, update status, and manage shipping from one place.</div>
  </div>
  <a href="/admin/export/orders" class="btn btn-outline btn-sm" target="_blank">⬇ Export CSV</a>
</div>

<form method="GET" class="adm-orders-filters">
  <input name="search" class="fi" placeholder="Search order ID, name, phone…" value="<?= htmlspecialchars($search) ?>">
  <select name="status" class="fi fi-sel" onchange="this.form.submit()">
    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Statuses</option>
    <?php foreach ($statusLabels as $k => $v): ?>
    <option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= $v ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-blue btn-sm" type="submit">Apply</button>
  <?php if ($search || $status !== 'all'): ?><a href="/admin/orders" class="btn btn-outline btn-sm">Clear</a><?php endif; ?>
</form>

<?php if (!$orders): ?>
<div style="text-align:center;padding:44px;color:var(--text2)"><div style="font-size:40px;margin-bottom:9px">📋</div><div>No orders found</div></div>
<?php else: ?>
<div class="adm-orders-wrap">
  <table class="adm-orders-table">
    <thead>
      <tr>
        <th>Order</th>
        <th>Customer</th>
        <th>Items & Specifications</th>
        <th>Value</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($orders as $o): ?>
      <tr id="ord-<?= (int)$o['id'] ?>">
        <td>
          <div class="ord-id">#<?= htmlspecialchars($o['order_id']) ?></div>
          <div class="ord-date"><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></div>
        </td>
        <td>
          <div class="ord-customer"><?= htmlspecialchars($o['customer_name']) ?></div>
          <div class="ord-meta"><?= htmlspecialchars($o['customer_phone']) ?></div>
          <?php if (!empty($o['customer_email'])): ?><div class="ord-meta"><?= htmlspecialchars($o['customer_email']) ?></div><?php endif; ?>
        </td>
        <td>
          <?php foreach ($o['items'] as $item): ?>
          <div class="ord-item-row">
            <div class="ord-item-name"><?= htmlspecialchars($item['product_name']) ?></div>
            <div class="ord-meta">
              <?= number_format((float)$item['quantity']) ?> qty, <?= htmlspecialchars($item['quality_name']) ?>
              <?= $item['design_choice'] === 'rcs' ? ' · 🎨 RCS Design' : ' · 📁 Upload' ?>
            </div>
          </div>
          <?php endforeach; ?>
        </td>
        <td>
          <div class="ord-amt">₹<?= number_format((float)$o['total_amount']) ?></div>
          <?php if (!empty($o['coupon_code'])): ?><div class="ord-meta" style="color:var(--green)">Coupon: <?= htmlspecialchars($o['coupon_code']) ?></div><?php endif; ?>
        </td>
        <td>
          <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-start">
            <span class="badge <?= $statusColors[$o['status']] ?? 'b-blue' ?>"><?= $statusLabels[$o['status']] ?? $o['status'] ?></span>
            <span class="badge <?= $o['payment_status'] === 'paid' ? 'b-green' : 'b-amber' ?>"><?= ucfirst($o['payment_status']) ?></span>
          </div>
        </td>
        <td>
          <div class="ord-actions">
            <a href="tel:<?= htmlspecialchars(preg_replace('/\D+/', '', $o['customer_phone'] ?? '')) ?>" class="aoc-btn">📞 Call</a>
            <button class="aoc-btn" onclick="waCustomer('<?= htmlspecialchars(addslashes($o['customer_name'])) ?>','<?= htmlspecialchars($o['customer_phone']) ?>','<?= htmlspecialchars($o['order_id']) ?>','<?= htmlspecialchars($o['status']) ?>')">💬 WA</button>
            <a href="/admin/invoice/<?= htmlspecialchars($o['order_id']) ?>" class="aoc-btn" target="_blank">🧾 Invoice</a>
          </div>

          <div class="ord-status-row">
            <select class="fi fi-sel" id="ord_status_<?= (int)$o['id'] ?>">
              <?php foreach (['received','processing','printing','ready','delivered','cancelled'] as $s): ?>
              <option value="<?= $s ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= $statusLabels[$s] ?? ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-outline btn-sm" onclick="updOrdFromSel(<?= (int)$o['id'] ?>)">Update</button>
          </div>

          <details class="ord-ship">
            <summary>Shipping details</summary>
            <div class="ord-ship-grid">
              <input class="fi" id="ship_provider_<?= (int)$o['id'] ?>" value="<?= htmlspecialchars($o['shipping_provider'] ?? '') ?>" placeholder="Provider">
              <input class="fi" id="ship_track_<?= (int)$o['id'] ?>" value="<?= htmlspecialchars($o['tracking_code'] ?? '') ?>" placeholder="Tracking code">
              <input class="fi" id="ship_status_<?= (int)$o['id'] ?>" value="<?= htmlspecialchars($o['shipping_status'] ?? '') ?>" placeholder="Shipping status">
              <button class="btn btn-outline btn-sm" onclick="saveShipping(<?= (int)$o['id'] ?>)">Save</button>
            </div>
            <textarea class="fi" id="ship_notes_<?= (int)$o['id'] ?>" style="height:56px" placeholder="Shipping notes"><?= htmlspecialchars($o['shipping_notes'] ?? '') ?></textarea>
          </details>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?php if ($total > $perPage): ?>
<div style="display:flex;gap:8px;justify-content:center;margin-top:20px;flex-wrap:wrap">
  <?php for ($i = 1; $i <= ceil($total / $perPage); $i++): ?>
  <a href="?page=<?= $i ?>&status=<?= urlencode($status) ?>&search=<?= urlencode($search) ?>" class="btn <?= $page === $i ? 'btn-blue' : 'btn-outline' ?> btn-sm"><?= $i ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>

<script>
function toast(msg, type='info') {
  const w = document.getElementById('tw');
  const t = document.createElement('div'); t.className = 'toast ' + type; t.textContent = msg; w.appendChild(t);
  requestAnimationFrame(() => requestAnimationFrame(() => t.classList.add('show')));
  setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 2800);
}

function updOrdFromSel(id) {
  const sel = document.getElementById(`ord_status_${id}`);
  if (!sel) return;
  updOrd(id, sel.value);
}

async function updOrd(id, status) {
  const resp = await fetch(`/admin/api/orders/${id}/status`, {
    method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'<?= htmlspecialchars($csrf ?? '') ?>'},
    body: JSON.stringify({ status })
  });
  const data = await resp.json();
  if (data.ok) { toast('Order updated to ' + status, 'success'); setTimeout(() => location.reload(), 500); }
  else toast('Update failed', 'error');
}

async function saveShipping(id) {
  const payload = {
    shipping_provider: document.getElementById(`ship_provider_${id}`).value.trim(),
    tracking_code: document.getElementById(`ship_track_${id}`).value.trim(),
    shipping_status: document.getElementById(`ship_status_${id}`).value.trim(),
    shipping_notes: document.getElementById(`ship_notes_${id}`).value.trim(),
  };
  const resp = await fetch(`/admin/api/orders/${id}/shipping`, {
    method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'<?= htmlspecialchars($csrf ?? '') ?>'},
    body: JSON.stringify(payload)
  });
  const data = await resp.json();
  if (data.ok) toast('Shipping details saved', 'success'); else toast('Could not save shipping', 'error');
}

function waCustomer(name, phone, ordId, status) {
  const msg = `Hi ${name}! 👋\nOrder ID: #${ordId}\nCurrent status: ${status.toUpperCase()}\nIf you need help, reply to this message.`;
  const cleanPhone = (phone || '').replace(/\D/g,'');
  window.open(`https://wa.me/${cleanPhone}?text=${encodeURIComponent(msg)}`, '_blank');
}
</script>
    </div></div></div>
</body></html>
