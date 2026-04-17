<?php
$pageTitle = 'Orders — RCS Admin';
$currentAdmPage = 'orders';
include __DIR__ . '/layout.php';
$statusColors = ['received'=>'b-blue','processing'=>'b-amber','printing'=>'b-orange','ready'=>'b-green','delivered'=>'b-ink','cancelled'=>'b-red','whatsapp_pending'=>'b-amber'];
$statusLabels = ['received'=>'Received','processing'=>'Processing','printing'=>'Printing','ready'=>'Ready','delivered'=>'Delivered','cancelled'=>'Cancelled','whatsapp_pending'=>'WA Pending'];
$bizWa = Database::setting('biz_whatsapp', env('BIZ_WHATSAPP', '919876543210'));
?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
  <div class="adm-pt" style="margin:0">All Orders (<?= $total ?>)</div>
  <a href="/admin/export/orders" class="btn btn-outline btn-sm" target="_blank">⬇ Export CSV</a>
</div>

<!-- Search & Filter -->
<form method="GET" style="display:flex;gap:9px;margin-bottom:14px;flex-wrap:wrap">
  <input name="search" class="fi" style="flex:1;min-width:180px" placeholder="Search order ID, name, phone…" value="<?= htmlspecialchars($search) ?>">
  <select name="status" class="fi fi-sel" style="width:160px" onchange="this.form.submit()">
    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Statuses</option>
    <?php foreach ($statusLabels as $k => $v): ?>
    <option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= $v ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-blue btn-sm" type="submit">Search</button>
  <?php if ($search || $status !== 'all'): ?>
  <a href="/admin/orders" class="btn btn-outline btn-sm">Clear</a>
  <?php endif; ?>
</form>

<?php if (!$orders): ?>
<div style="text-align:center;padding:44px;color:var(--text2)"><div style="font-size:40px;margin-bottom:9px">📋</div><div>No orders found</div></div>
<?php endif; ?>

<?php foreach ($orders as $o): ?>
<div class="aoc" id="ord-<?= $o['id'] ?>">
  <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:9px">
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
      🖨️ <?= htmlspecialchars($item['product_name']) ?> × <?= number_format($item['quantity']) ?> — <?= htmlspecialchars($item['quality_name']) ?>
      <?= $item['design_choice'] === 'rcs' ? ' · 🎨 RCS Design' : '' ?>
    </div>
    <?php endforeach; ?>
    <div style="font-size:11px;color:var(--blue);font-weight:700;background:var(--blue-bg);padding:3px 8px;border-radius:100px">💰 ₹<?= number_format($o['total_amount']) ?></div>
    <?php if ($o['coupon_code']): ?>
    <div style="font-size:11px;color:var(--green);background:var(--green-bg);padding:3px 8px;border-radius:100px">🎟️ <?= htmlspecialchars($o['coupon_code']) ?></div>
    <?php endif; ?>
  </div>
  <?php
  // Check for artwork
  $artworks = Database::rows("SELECT af.* FROM artwork_files af JOIN order_items oi ON af.order_item_id = oi.id WHERE oi.order_id = ?", [$o['id']]);
  if ($artworks):
  ?>
  <div style="margin-bottom:8px">
    <?php foreach ($artworks as $af): ?>
    <a href="/admin/artwork/<?= $af['id'] ?>/download" class="aoc-artwork" style="display:inline-flex;align-items:center;gap:6px;background:var(--blue-bg);border:1px solid var(--blue-mid);border-radius:7px;padding:6px 11px;font-size:12px;font-weight:600;color:var(--blue);text-decoration:none;margin-right:6px;margin-bottom:4px" target="_blank">
      📁 <?= htmlspecialchars($af['original_name']) ?>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <?php
  // Design briefs
  $briefs = array_filter(array_column($o['items'], 'design_brief'));
  if ($briefs):
  ?>
  <div style="background:var(--blue-bg);border:1px solid var(--blue-mid);border-radius:7px;padding:7px 11px;font-size:12px;color:var(--blue);margin-bottom:8px">
    📋 Design Brief: <?= htmlspecialchars(implode(' | ', $briefs)) ?>
  </div>
  <?php endif; ?>
  <?php
  $notes = array_filter(array_column($o['items'], 'notes'));
  if ($notes):
  ?>
  <div style="background:var(--amber-bg);border:1px solid var(--amber-mid);border-radius:7px;padding:7px 11px;font-size:12px;color:var(--amber);margin-bottom:8px">
    📝 <?= htmlspecialchars(implode(' · ', $notes)) ?>
  </div>
  <?php endif; ?>
  <!-- Status Actions -->
  <div style="display:flex;gap:5px;padding-top:9px;border-top:1px solid var(--border);flex-wrap:wrap">
    <?php foreach (['received','processing','printing','ready','delivered'] as $s): ?>
    <button class="aoc-btn" onclick="updOrd(<?= $o['id'] ?>,'<?= $s ?>')"
      <?= $o['status'] === $s || in_array($o['status'], ['delivered','cancelled']) ? 'disabled' : '' ?>>
      <?= $statusLabels[$s] ?>
    </button>
    <?php endforeach; ?>
    <button class="aoc-btn" onclick="updOrd(<?= $o['id'] ?>,'cancelled')"
      style="color:var(--red)" <?= $o['status'] === 'cancelled' ? 'disabled' : '' ?>>✕ Cancel</button>
    <button class="aoc-btn" onclick="waNotify(<?= $o['id'] ?>, '<?= htmlspecialchars(addslashes($o['customer_name'])) ?>', '<?= htmlspecialchars($o['customer_phone']) ?>', '<?= htmlspecialchars($o['order_id']) ?>', '<?= $o['status'] ?>')"
      style="background:var(--green-bg);border-color:var(--green-mid);color:var(--green)">📲 Notify</button>
    <a href="/admin/invoice/<?= htmlspecialchars($o['order_id']) ?>" class="aoc-btn" target="_blank">🧾 Invoice</a>
  </div>
</div>
<?php endforeach; ?>

<!-- Pagination -->
<?php if ($total > $perPage): ?>
<div style="display:flex;gap:8px;justify-content:center;margin-top:20px">
  <?php for ($i = 1; $i <= ceil($total / $perPage); $i++): ?>
  <a href="?page=<?= $i ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>"
     class="btn <?= $page === $i ? 'btn-blue' : 'btn-outline' ?> btn-sm"><?= $i ?></a>
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
  const resp = await fetch(`/admin/orders/${id}/status`, {
    method: 'POST', headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ status })
  });
  const data = await resp.json();
  if (data.ok) { toast('Order updated to ' + status + '!', 'success'); setTimeout(() => location.reload(), 800); }
  else toast('Update failed', 'error');
}
function waNotify(id, name, phone, ordId, status) {
  const statusMsg = {
    received: 'Your order has been received! We\'ll start processing it shortly.',
    processing: 'Your order is being processed by our team.',
    printing: 'Great news! Your order is now being printed. 🖨️',
    ready: 'Your order is ready! Please contact us to arrange delivery/pickup.',
    delivered: 'Your order has been delivered. Thank you for choosing us!',
    cancelled: 'Your order has been cancelled. Please contact us for queries.'
  };
  const msg = `Hi ${name}! 👋\n\n${statusMsg[status] || 'Your order status has been updated.'}\n\nOrder ID: #${ordId}\nStatus: ${status.toUpperCase()}\n\nFor queries: <?= htmlspecialchars($bizWa) ?>`;
  window.open(`https://wa.me/${phone.replace(/\D/g,'')}?text=${encodeURIComponent(msg)}`, '_blank');
}
</script>
    </div></div></div>
</body></html>
