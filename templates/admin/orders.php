<?php
$pageTitle = 'Orders — RCS Admin';
$currentAdmPage = 'orders';
$admMainClass = 'adm-main--orders';
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

<div class="adm-orders-page">
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
    <colgroup>
      <col style="width:14%">
      <col style="width:17%">
      <col style="width:29%">
      <col style="width:10%">
      <col style="width:10%">
      <col style="width:20%">
    </colgroup>
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
      <?php
        $orderNotesRaw = trim((string)($o['notes'] ?? ''));
        $orderNotesJson = $orderNotesRaw !== '' ? json_decode($orderNotesRaw, true) : null;
        $orderBilling = (is_array($orderNotesJson) && is_array($orderNotesJson['billing'] ?? null))
            ? $orderNotesJson['billing']
            : null;
        $orderShipping = (is_array($orderNotesJson) && is_array($orderNotesJson['shipping'] ?? null))
            ? $orderNotesJson['shipping']
            : null;
      ?>
      <tr id="ord-<?= (int)$o['id'] ?>">
        <td>
          <div class="ord-id">#<?= htmlspecialchars($o['order_id']) ?></div>
          <div class="ord-date"><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></div>
          <div class="ord-meta">Internal ID: <?= (int)$o['id'] ?></div>
        </td>
        <td>
          <div class="ord-customer"><?= htmlspecialchars($o['customer_name']) ?></div>
          <div class="ord-meta"><?= htmlspecialchars($o['customer_phone']) ?></div>
          <?php if (!empty($o['customer_email'])): ?><div class="ord-meta"><?= htmlspecialchars($o['customer_email']) ?></div><?php endif; ?>
          <?php if ($orderBilling): ?>
          <div class="ord-meta" style="margin-top:6px;padding-top:6px;border-top:1px dashed var(--border)">
            <div style="font-weight:700;color:var(--text)">🧾 Billing</div>
            <div><?= htmlspecialchars($orderBilling['legal_name'] ?? '-') ?></div>
            <div>GSTIN: <?= htmlspecialchars($orderBilling['gst_no'] ?? '-') ?></div>
            <div>
              <?= htmlspecialchars($orderBilling['address_line1'] ?? '') ?>
              <?php if (!empty($orderBilling['address_line2'])): ?>, <?= htmlspecialchars($orderBilling['address_line2']) ?><?php endif; ?>
            </div>
            <div>
              <?= htmlspecialchars($orderBilling['city'] ?? '') ?>, <?= htmlspecialchars($orderBilling['state'] ?? '') ?>
              - <?= htmlspecialchars($orderBilling['pincode'] ?? '') ?>
            </div>
          </div>
          <?php endif; ?>
          <div class="ord-cust-actions">
            <a href="tel:<?= htmlspecialchars(preg_replace('/\D+/', '', $o['customer_phone'] ?? '')) ?>" class="aoc-btn">📞 Call</a>
            <button class="aoc-btn" onclick="waCustomer('<?= htmlspecialchars(addslashes($o['customer_name'])) ?>','<?= htmlspecialchars($o['customer_phone']) ?>','<?= htmlspecialchars($o['order_id']) ?>','<?= htmlspecialchars($o['status']) ?>')">💬 WA</button>
          </div>
        </td>
        <td>
          <div class="ord-items-hdr"><?= count($o['items'] ?? []) ?> item(s)</div>
          <?php foreach (($o['items'] ?? []) as $item): ?>
          <div class="ord-item-row">
            <div class="ord-item-name"><?= htmlspecialchars($item['product_name']) ?></div>
            <div class="ord-meta">
              <?= number_format((float)$item['quantity']) ?> qty, <?= htmlspecialchars($item['quality_name']) ?>
              <?= $item['design_choice'] === 'rcs' ? ' · 🎨 RCS Design' : ' · 📁 Upload' ?>
            </div>
            <?php if (!empty($item['artwork_file_id'])): ?>
            <div class="ord-artwork">
              <span>📎 <?= htmlspecialchars($item['artwork_original_name'] ?: $item['artwork_filename'] ?: 'Artwork File') ?></span>
              <a href="/admin/artwork/<?= (int)$item['artwork_file_id'] ?>/download" class="ord-artwork-link">Download</a>
            </div>
            <?php elseif (($item['design_choice'] ?? '') !== 'rcs'): ?>
            <div class="ord-artwork ord-artwork-empty">No artwork uploaded</div>
            <?php endif; ?>
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
            <a href="/admin/invoice/<?= htmlspecialchars($o['order_id']) ?>" class="aoc-btn" target="_blank">🧾 Invoice</a>
            <a href="/invoice/<?= htmlspecialchars($o['order_id']) ?>" class="aoc-btn" target="_blank">👁 View</a>
            <button
              class="aoc-btn"
              onclick='openAddrModal("<?= htmlspecialchars($o['order_id']) ?>", <?= json_encode($orderShipping, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>, <?= json_encode($orderBilling, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>)'
            >📍 Addresses</button>
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

<div id="addrModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:1200;align-items:center;justify-content:center;padding:18px">
  <div style="width:min(620px,100%);max-height:86vh;overflow:auto;background:var(--white);border-radius:12px;border:1px solid var(--border);box-shadow:var(--sh-lg);padding:18px">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px">
      <div>
        <div style="font-family:var(--fd);font-size:16px;font-weight:700">Address Details</div>
        <div id="addrOrdLabel" style="font-size:12px;color:var(--text2)"></div>
      </div>
      <button class="btn btn-outline btn-sm" onclick="closeAddrModal()">Close ✕</button>
    </div>
    <div class="f2" style="grid-template-columns:1fr 1fr;gap:12px">
      <div style="border:1px solid var(--border);border-radius:10px;padding:12px">
        <div style="font-size:12px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">Delivery Address</div>
        <div id="addrShipBox" style="font-size:13px;line-height:1.6"></div>
      </div>
      <div style="border:1px solid var(--border);border-radius:10px;padding:12px">
        <div style="font-size:12px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">Billing Address</div>
        <div id="addrBillBox" style="font-size:13px;line-height:1.6"></div>
      </div>
    </div>
  </div>
</div>

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

function fmtAddr(a, kind = 'shipping') {
  if (!a || typeof a !== 'object') return '<span style="color:var(--text3)">Not available</span>';
  if (kind === 'billing') {
    return `<div><b>${a.legal_name || '-'}</b></div>
      <div>GSTIN: ${a.gst_no || '-'}</div>
      <div>${a.address_line1 || ''}${a.address_line2 ? ', ' + a.address_line2 : ''}</div>
      <div>${a.city || ''}, ${a.state || ''} - ${a.pincode || ''}</div>`;
  }
  return `<div>${a.address_line1 || ''}${a.address_line2 ? ', ' + a.address_line2 : ''}</div>
    <div>${a.city || ''}, ${a.state || ''} - ${a.pincode || ''}</div>`;
}

function openAddrModal(orderId, shipping, billing) {
  document.getElementById('addrOrdLabel').textContent = `Order #${orderId}`;
  document.getElementById('addrShipBox').innerHTML = fmtAddr(shipping, 'shipping');
  document.getElementById('addrBillBox').innerHTML = fmtAddr(billing, 'billing');
  const modal = document.getElementById('addrModal');
  if (modal) modal.style.display = 'flex';
}

function closeAddrModal() {
  const modal = document.getElementById('addrModal');
  if (modal) modal.style.display = 'none';
}
</script>
</div>
    </div></div></div>
</body></html>
