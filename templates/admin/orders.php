<?php
$pageTitle = 'Orders — RCS Admin';
$currentAdmPage = 'orders';
include __DIR__ . '/layout.php';
$statusColors = ['received'=>'b-blue','processing'=>'b-amber','printing'=>'b-orange','ready'=>'b-green','delivered'=>'b-ink','cancelled'=>'b-red','whatsapp_pending'=>'b-amber'];
$statusLabels = ['received'=>'Received','processing'=>'Processing','printing'=>'Printing','ready'=>'Ready','delivered'=>'Delivered','cancelled'=>'Cancelled','whatsapp_pending'=>'WA Pending'];
$bizWa = Database::setting('biz_whatsapp', env('BIZ_WHATSAPP', '919876543210'));
$orders = $orders ?? [];
$total = (int)($total ?? 0);
$page = (int)($page ?? 1);
$perPage = (int)($perPage ?? 12);
$search = $search ?? '';
$status = $status ?? 'all';
?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
  <div class="adm-pt" style="margin:0">All Orders (<?= $total ?>)</div>
  <a href="/admin/export/orders" class="btn btn-outline btn-sm" target="_blank">⬇ Export CSV</a>
</div>

<form method="GET" style="display:flex;gap:9px;margin-bottom:14px;flex-wrap:wrap">
  <input name="search" class="fi" style="flex:1;min-width:180px" placeholder="Search order ID, name, phone…" value="<?= htmlspecialchars($search) ?>">
  <select name="status" class="fi fi-sel" style="width:160px" onchange="this.form.submit()">
    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Statuses</option>
    <?php foreach ($statusLabels as $k => $v): ?>
    <option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= $v ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-blue btn-sm" type="submit">Search</button>
  <?php if ($search || $status !== 'all'): ?><a href="/admin/orders" class="btn btn-outline btn-sm">Clear</a><?php endif; ?>
</form>

<?php if (!$orders): ?>
<div style="text-align:center;padding:44px;color:var(--text2)"><div style="font-size:40px;margin-bottom:9px">📋</div><div>No orders found</div></div>
<?php endif; ?>

<?php foreach ($orders as $o): ?>
<div class="aoc" id="ord-<?= (int)$o['id'] ?>">
  <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:9px;flex-wrap:wrap">
    <div>
      <div class="aoc-id">#<?= htmlspecialchars($o['order_id']) ?> · <?= date('d M Y, H:i', strtotime($o['created_at'])) ?></div>
      <div class="aoc-name"><?= htmlspecialchars($o['customer_name']) ?></div>
      <div style="font-size:12px;color:var(--text2)"><?= htmlspecialchars($o['customer_phone']) ?><?= $o['customer_email'] ? ' · ' . htmlspecialchars($o['customer_email']) : '' ?></div>
    </div>
    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:5px">
      <span class="badge <?= $statusColors[$o['status']] ?? 'b-blue' ?>"><?= $statusLabels[$o['status']] ?? $o['status'] ?></span>
      <span class="badge <?= $o['payment_status'] === 'paid' ? 'b-green' : 'b-amber' ?>"><?= ucfirst($o['payment_status']) ?></span>
    </div>
  </div>

  <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px">
    <?php foreach ($o['items'] as $item): ?>
    <div style="font-size:11px;color:var(--text2);background:var(--bg2);padding:3px 8px;border-radius:100px;border:1px solid var(--border)">
      🖨️ <?= htmlspecialchars($item['product_name']) ?> × <?= number_format((float)$item['quantity']) ?> — <?= htmlspecialchars($item['quality_name']) ?>
      <?= $item['design_choice'] === 'rcs' ? ' · 🎨 RCS Design' : '' ?>
    </div>
    <?php endforeach; ?>
    <div style="font-size:11px;color:var(--blue);font-weight:700;background:var(--blue-bg);padding:3px 8px;border-radius:100px">💰 ₹<?= number_format((float)$o['total_amount']) ?></div>
    <?php if (!empty($o['coupon_code'])): ?><div style="font-size:11px;color:var(--green);background:var(--green-bg);padding:3px 8px;border-radius:100px">🎟️ <?= htmlspecialchars($o['coupon_code']) ?></div><?php endif; ?>
  </div>

  <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;margin-bottom:8px">
    <a href="tel:<?= htmlspecialchars(preg_replace('/\D+/', '', $o['customer_phone'] ?? '')) ?>" class="aoc-btn">📞 Call</a>
    <button class="aoc-btn" onclick="waCustomer('<?= htmlspecialchars(addslashes($o['customer_name'])) ?>','<?= htmlspecialchars($o['customer_phone']) ?>','<?= htmlspecialchars($o['order_id']) ?>','<?= htmlspecialchars($o['status']) ?>')">💬 WhatsApp</button>
    <a href="/admin/invoice/<?= htmlspecialchars($o['order_id']) ?>" class="aoc-btn" target="_blank">🧾 Invoice</a>
  </div>

  <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;margin-bottom:8px">
    <input class="fi" id="ship_provider_<?= (int)$o['id'] ?>" value="<?= htmlspecialchars($o['shipping_provider'] ?? '') ?>" placeholder="Shipping provider">
    <input class="fi" id="ship_track_<?= (int)$o['id'] ?>" value="<?= htmlspecialchars($o['tracking_code'] ?? '') ?>" placeholder="Tracking code">
    <input class="fi" id="ship_status_<?= (int)$o['id'] ?>" value="<?= htmlspecialchars($o['shipping_status'] ?? '') ?>" placeholder="Shipping status">
    <button class="btn btn-outline btn-sm" onclick="saveShipping(<?= (int)$o['id'] ?>)">Save Shipping</button>
  </div>
  <textarea class="fi" id="ship_notes_<?= (int)$o['id'] ?>" style="height:54px;margin-bottom:8px" placeholder="Shipping notes"><?= htmlspecialchars($o['shipping_notes'] ?? '') ?></textarea>

  <div style="display:flex;gap:5px;padding-top:9px;border-top:1px solid var(--border);flex-wrap:wrap">
    <?php foreach (['received','processing','printing','ready','delivered'] as $s): ?>
    <button class="aoc-btn" onclick="updOrd(<?= (int)$o['id'] ?>,'<?= $s ?>')" <?= $o['status'] === $s || in_array($o['status'], ['delivered','cancelled']) ? 'disabled' : '' ?>><?= $statusLabels[$s] ?></button>
    <?php endforeach; ?>
    <button class="aoc-btn" onclick="updOrd(<?= (int)$o['id'] ?>,'cancelled')" style="color:var(--red)" <?= $o['status'] === 'cancelled' ? 'disabled' : '' ?>>✕ Cancel</button>
  </div>
</div>
<?php endforeach; ?>

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
