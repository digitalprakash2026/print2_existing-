<?php
$pageTitle = 'Orders — RCS Admin';
$currentAdmPage = 'orders';
$admMainClass = 'adm-main--orders';
include __DIR__ . '/layout.php';
$statusColors = ['new_order'=>'b-blue','received'=>'b-blue','design_approved'=>'b-green','processing'=>'b-amber','other_process'=>'b-amber','printing'=>'b-orange','ready'=>'b-green','delivered'=>'b-ink','cancelled'=>'b-red','whatsapp_pending'=>'b-amber'];
$statusLabels = ['new_order'=>'New Order','received'=>'Received','design_approved'=>'Design Approved','printing'=>'Printing','other_process'=>'Other Process','processing'=>'Other Process','ready'=>'Dispatched','delivered'=>'Delivered','cancelled'=>'Cancelled','whatsapp_pending'=>'WA Pending'];
$designApprovalLabels = ['pending_review'=>'Pending Review','issue_found'=>'Issue Found','proof_uploaded'=>'Proof Uploaded','approved'=>'Approved'];
$designApprovalColors = ['pending_review'=>'b-amber','issue_found'=>'b-red','proof_uploaded'=>'b-blue','approved'=>'b-green'];
$orders = $orders ?? [];
$summaryCounts = $summaryCounts ?? [];
$statusCounts = $statusCounts ?? ['all' => 0];
$total = (int)($total ?? 0);
$page = (int)($page ?? 1);
$perPage = (int)($perPage ?? 12);
$search = $search ?? '';
$status = $status ?? 'all';
$paymentStatus = $paymentStatus ?? 'all';
$seen = $seen ?? 'all';
$sort = $sort ?? 'newest';
$dateFrom = $dateFrom ?? '';
$dateTo = $dateTo ?? '';
$hasSeen = !empty($hasSeen);
$orderUrl = static function (array $params = []): string {
    $params = array_filter($params, static fn($v) => $v !== '' && $v !== null);
    return '/admin/orders' . ($params ? ('?' . http_build_query($params)) : '');
};

$baseCardParams = [];
foreach (['search' => $search, 'payment_status' => $paymentStatus, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'sort' => $sort] as $k => $v) {
    if ($v !== '' && !in_array($v, ['all', 'newest'], true)) $baseCardParams[$k] = $v;
}
$orderStatusCards = [
    ['key'=>'all','label'=>'All Order','icon'=>'▦','class'=>'blue','params'=>[],'count'=>$statusCounts['all'] ?? 0],
    ['key'=>'new_order','label'=>'New Order','icon'=>'●','class'=>'pink','params'=>['status'=>'new_order'],'count'=>$statusCounts['new_order'] ?? 0],
    ['key'=>'received','label'=>'Received','icon'=>'▣','class'=>'cyan','params'=>['status'=>'received'],'count'=>$statusCounts['received'] ?? 0],
    ['key'=>'design_approved','label'=>'Design Approved','icon'=>'✓','class'=>'green','params'=>['status'=>'design_approved'],'count'=>$statusCounts['design_approved'] ?? 0],
    ['key'=>'printing','label'=>'Printing','icon'=>'▤','class'=>'purple','params'=>['status'=>'printing'],'count'=>$statusCounts['printing'] ?? 0],
    ['key'=>'other_process','label'=>'Other Process','icon'=>'⚙','class'=>'amber','params'=>['status'=>'other_process'],'count'=>$statusCounts['other_process'] ?? 0],
    ['key'=>'ready_dispatch','label'=>'Dispatched','icon'=>'▰','class'=>'lime','params'=>['status'=>'ready'],'count'=>$statusCounts['ready_dispatch'] ?? 0],
    ['key'=>'delivered','label'=>'Delivered','icon'=>'◆','class'=>'slate','params'=>['status'=>'delivered'],'count'=>$statusCounts['delivered'] ?? 0],
];
$isCardActive = static function (array $card) use ($status, $seen): bool {
    return match ($card['key']) {
        'all' => ($status === '' || $status === 'all') && $seen === 'all',
        'new_order' => $status === 'new_order' && $seen !== 'new',
        'ready_dispatch' => $status === 'ready' && $seen !== 'new',
        default => $status === $card['key'] && $seen !== 'new',
    };
};
?>

<div class="adm-orders-page">
<section class="adm-orders-command">
  <div class="adm-orders-command-bg" aria-hidden="true"></div>
  <div class="adm-orders-head adm-orders-head--compact">
    <div aria-hidden="true"></div>
    <a href="/admin/export/orders" class="btn btn-outline btn-sm adm-orders-export" target="_blank">⬇ Export CSV</a>
  </div>

  <div class="adm-orders-status-grid" aria-label="Order status summary filters">
  <?php foreach ($orderStatusCards as $card): ?>
    <?php
      $hrefParams = array_merge($baseCardParams, $card['params']);
      $href = $orderUrl($hrefParams);
      $active = $isCardActive($card);
    ?>
    <a class="adm-order-status-card adm-order-status-card--<?= htmlspecialchars($card['class']) ?> <?= $active ? 'act' : '' ?>" href="<?= htmlspecialchars($href) ?>">
      <span class="adm-order-status-icon"><?= htmlspecialchars($card['icon']) ?></span>
      <span class="adm-order-status-copy">
        <b><?= number_format((int)$card['count']) ?></b>
        <strong><?= htmlspecialchars($card['label']) ?></strong>
      </span>
      <em><?= $active ? 'Showing' : 'View' ?> →</em>
    </a>
  <?php endforeach; ?>
</div>
</section>

<div class="adm-orders-control-panel">
<form method="GET" class="adm-orders-filters">
  <input name="search" class="fi" placeholder="Search order ID, name, phone…" value="<?= htmlspecialchars($search) ?>">
  <select name="status" class="fi fi-sel" onchange="this.form.submit()">
    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Statuses</option>
    <?php foreach ($statusLabels as $k => $v): ?>
    <?php if ($k === 'processing' || $k === 'whatsapp_pending') continue; ?>
    <option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= $v ?></option>
    <?php endforeach; ?>
  </select>
  <select name="payment_status" class="fi fi-sel" onchange="this.form.submit()">
    <option value="all" <?= $paymentStatus === 'all' ? 'selected' : '' ?>>All Payments</option>
    <?php foreach (['paid'=>'Paid','pending'=>'Pending','failed'=>'Failed','refunded'=>'Refunded'] as $k => $v): ?>
    <option value="<?= $k ?>" <?= $paymentStatus === $k ? 'selected' : '' ?>><?= $v ?></option>
    <?php endforeach; ?>
  </select>
  <input type="date" name="date_from" class="fi" value="<?= htmlspecialchars($dateFrom) ?>" aria-label="Date from">
  <input type="date" name="date_to" class="fi" value="<?= htmlspecialchars($dateTo) ?>" aria-label="Date to">
  <select name="sort" class="fi fi-sel" onchange="this.form.submit()">
    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest first</option>
    <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest first</option>
    <option value="high_value" <?= $sort === 'high_value' ? 'selected' : '' ?>>High value first</option>
    <option value="urgent" <?= $sort === 'urgent' ? 'selected' : '' ?>>Urgent first</option>
  </select>
  <button class="btn btn-blue btn-sm" type="submit">Apply</button>
  <?php if ($search || $status !== 'all' || $paymentStatus !== 'all' || $seen !== 'all' || $sort !== 'newest' || $dateFrom || $dateTo): ?><a href="/admin/orders" class="btn btn-outline btn-sm">Clear</a><?php endif; ?>
</form>
</div>

<?php if (!$orders): ?>
<div style="text-align:center;padding:44px;color:var(--text2)"><div style="font-size:40px;margin-bottom:9px">📋</div><div>No orders found</div></div>
<?php else: ?>
<div class="adm-order-card-list" aria-label="Orders list">
  <?php foreach ($orders as $o): ?>
  <?php
    $orderNotesRaw = trim((string)($o['notes'] ?? ''));
    $orderNotesJson = $orderNotesRaw !== '' ? json_decode($orderNotesRaw, true) : null;
    $orderBilling = (is_array($orderNotesJson) && is_array($orderNotesJson['billing'] ?? null)) ? $orderNotesJson['billing'] : null;
    $orderShipping = (is_array($orderNotesJson) && is_array($orderNotesJson['shipping'] ?? null)) ? $orderNotesJson['shipping'] : null;
    $orderStatus = (string)($o['status'] ?? 'new_order');
    $createdTs = strtotime((string)($o['created_at'] ?? '')) ?: time();
    $isNewOrder = $orderStatus === 'new_order';
    $ageSeconds = max(0, time() - $createdTs);
    $orderAge = $ageSeconds >= 86400 ? floor($ageSeconds / 86400) . 'd old' : floor($ageSeconds / 3600) . 'h old';
    $cardClasses = ['adm-order-card', 'adm-order-card--' . preg_replace('/[^a-z0-9_-]+/i', '-', $orderStatus)];
    if ($isNewOrder) $cardClasses[] = 'adm-order-card--new';
  ?>
  <article id="ord-<?= (int)$o['id'] ?>" class="<?= htmlspecialchars(implode(' ', $cardClasses)) ?>" data-order-card>
    <div class="adm-order-card-head">
      <button class="adm-order-card-summary" type="button" aria-expanded="false" data-order-toggle onclick="toggleOrderCard(this)">
        <span class="adm-order-card-id"><strong>#<?= htmlspecialchars($o['order_id']) ?></strong><small><?= date('d M Y, H:i', strtotime($o['created_at'])) ?> · <?= htmlspecialchars($orderAge) ?></small></span>
        <span class="adm-order-card-customer"><strong><?= htmlspecialchars($o['customer_name']) ?></strong><small><?= htmlspecialchars($o['customer_phone']) ?><?= !empty($o['customer_email']) ? ' · ' . htmlspecialchars($o['customer_email']) : '' ?></small></span>
        <span class="adm-order-card-meta"><b>₹<?= number_format((float)$o['total_amount']) ?></b><small><?= count($o['items'] ?? []) ?> item(s)</small></span>
        <span class="adm-order-card-badges"><span class="badge <?= $statusColors[$orderStatus] ?? 'b-blue' ?>"><?= htmlspecialchars($statusLabels[$orderStatus] ?? $orderStatus) ?></span><span class="badge <?= $o['payment_status'] === 'paid' ? 'b-green' : 'b-amber' ?>"><?= ucfirst($o['payment_status']) ?></span></span>
      </button>
      <div class="adm-order-card-quick" aria-label="Quick order actions">
        <select class="fi fi-sel" id="ord_status_<?= (int)$o['id'] ?>" aria-label="Update status for order <?= htmlspecialchars($o['order_id']) ?>"><?php foreach (['new_order','received','design_approved','printing','other_process','ready','delivered','cancelled'] as $s): ?><option value="<?= $s ?>" <?= $orderStatus === $s ? 'selected' : '' ?>><?= $statusLabels[$s] ?? ucfirst($s) ?></option><?php endforeach; ?></select>
        <button class="btn btn-outline btn-sm" type="button" onclick="updOrdFromSel(<?= (int)$o['id'] ?>)">Update</button>
      </div>
      <button class="adm-order-card-toggle" type="button" aria-label="Expand order <?= htmlspecialchars($o['order_id']) ?>" aria-expanded="false" data-order-toggle onclick="toggleOrderCard(this)">⌄</button>
    </div>
    <div class="adm-order-card-body" hidden>
      <div class="adm-order-card-grid">
        <section class="adm-order-card-section adm-order-card-section--full">
          <h3>Items & Design Approval</h3>
          <div class="ord-items-hdr"><?= count($o['items'] ?? []) ?> item(s)</div>
          <?php foreach (($o['items'] ?? []) as $item): ?>
            <?php
              $approvalId = (int)($item['design_approval_id'] ?? 0);
              $approvalStatus = (string)($item['design_approval_status'] ?? 'pending_review');
              $designChoice = (string)($item['design_choice'] ?? 'upload');
              $isRcsDesign = $designChoice === 'rcs';
            ?>
            <div class="ord-item-row ord-design-workflow ord-design-workflow--<?= htmlspecialchars($approvalStatus) ?> <?= !$isRcsDesign ? 'ord-design-workflow--customer-upload' : 'ord-design-workflow--rcs' ?>">
              <div class="ord-design-head">
                <div><div class="ord-item-name"><?= htmlspecialchars($item['product_name']) ?> <span class="ord-design-inline-choice"><?= $isRcsDesign ? 'RCS Design' : 'Customer Upload' ?></span></div><div class="ord-meta"><?= number_format((float)$item['quantity']) ?> qty, <?= htmlspecialchars($item['quality_name']) ?> · <?= $isRcsDesign ? 'RCS will prepare proof' : 'Customer artwork approval required' ?></div></div>
                <div class="ord-design-badges"><span class="badge <?= $isRcsDesign ? 'b-purple' : 'b-blue' ?>"><?= $isRcsDesign ? '🎨 RCS Design' : '📁 Customer Upload' ?></span><span class="badge <?= $designApprovalColors[$approvalStatus] ?? 'b-amber' ?>"><?= htmlspecialchars($designApprovalLabels[$approvalStatus] ?? $approvalStatus) ?></span></div>
              </div>
              <div class="ord-design-strip">
                <div class="ord-design-filebox">
                  <strong><?= $isRcsDesign ? 'Customer Brief / Assets' : 'Customer Artwork' ?></strong>
                  <?php if (!empty($item['artwork_file_id'])): ?>
                    <span><?= htmlspecialchars($item['artwork_original_name'] ?: $item['artwork_filename'] ?: 'Artwork File') ?></span>
                    <a href="/admin/artwork/<?= (int)$item['artwork_file_id'] ?>/download" class="ord-artwork-link ord-artwork-link--primary">Download Customer File</a>
                  <?php else: ?>
                    <span class="ord-artwork-empty"><?= $isRcsDesign ? 'Use WhatsApp/customer communication for brief and assets.' : 'No artwork uploaded' ?></span>
                  <?php endif; ?>
                </div>
                <div class="ord-design-filebox ord-design-filebox--proof">
                  <strong>RCS Proof / Corrected File</strong>
                  <?php if (!empty($item['design_proof_file_id'])): ?>
                    <span><?= htmlspecialchars($item['design_proof_original_name'] ?: $item['design_proof_filename'] ?: 'Proof File') ?></span>
                    <a href="/admin/artwork/<?= (int)$item['design_proof_file_id'] ?>/download" class="ord-artwork-link">Download</a>
                  <?php else: ?>
                    <span class="ord-artwork-empty">No proof uploaded yet</span>
                  <?php endif; ?>
                </div>
                <?php if ($approvalId > 0): ?>
                <div class="ord-design-actions">
                  <input type="file" id="proof_<?= $approvalId ?>" class="ord-proof-input" accept=".pdf,.ai,.eps,.png,.jpg,.jpeg,.psd,.cdr,.svg,.tif,.tiff,.zip" onchange="uploadDesignProof(<?= $approvalId ?>)">
                  <button class="aoc-btn" type="button" onclick="chooseDesignProof(<?= $approvalId ?>)">Upload Proof</button>
                  <button class="aoc-btn aoc-btn--danger" type="button" onclick="setDesignApproval(<?= $approvalId ?>,'issue_found')">Mark Issue</button>
                  <button class="aoc-btn aoc-btn--approve" type="button" onclick="setDesignApproval(<?= $approvalId ?>,'approved')">Approve Design</button>
                </div>
                <?php endif; ?>
              </div>
              <?php if (!empty($item['design_admin_note'])): ?><div class="ord-design-note <?= $approvalStatus === 'issue_found' ? 'ord-design-note--issue' : '' ?>"><?= $approvalStatus === 'issue_found' ? '⚠ Issue for customer: ' : 'Note: ' ?><?= htmlspecialchars($item['design_admin_note']) ?></div><?php endif; ?>
            </div>
          <?php endforeach; ?>
        </section>
        <section class="adm-order-card-section adm-order-card-section--full adm-order-compact-tools"><div class="ord-ship-grid"><input class="fi" id="ship_provider_<?= (int)$o['id'] ?>" value="<?= htmlspecialchars($o['shipping_provider'] ?? '') ?>" placeholder="Shipping provider"><input class="fi" id="ship_track_<?= (int)$o['id'] ?>" value="<?= htmlspecialchars($o['tracking_code'] ?? '') ?>" placeholder="Tracking code"><input class="fi" id="ship_status_<?= (int)$o['id'] ?>" value="<?= htmlspecialchars($o['shipping_status'] ?? '') ?>" placeholder="Shipping status"><button class="btn btn-outline btn-sm" onclick="saveShipping(<?= (int)$o['id'] ?>)">Save Shipping</button></div><div class="ord-actions ord-actions--compact"><a href="tel:<?= htmlspecialchars(preg_replace('/\D+/', '', $o['customer_phone'] ?? '')) ?>" class="aoc-btn">📞 Call</a><button class="aoc-btn" onclick="waCustomer('<?= htmlspecialchars(addslashes($o['customer_name'])) ?>','<?= htmlspecialchars($o['customer_phone']) ?>','<?= htmlspecialchars($o['order_id']) ?>','<?= htmlspecialchars($orderStatus) ?>')">💬 WA</button><a href="/admin/invoice/<?= htmlspecialchars($o['order_id']) ?>" class="aoc-btn" target="_blank">🧾 Invoice</a><a href="/invoice/<?= htmlspecialchars($o['order_id']) ?>" class="aoc-btn" target="_blank">👁 View</a><button class="aoc-btn" onclick='openAddrModal("<?= htmlspecialchars($o['order_id']) ?>", <?= json_encode($orderShipping, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>, <?= json_encode($orderBilling, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>)'>📍 Addresses</button></div></section>
      </div>
    </div>
  </article>
  <?php endforeach; ?>
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
        <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px">
          <div style="font-size:12px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:.05em">Delivery Address</div>
          <button class="btn btn-outline btn-sm" type="button" onclick="copyAddress('shipping')">Copy</button>
        </div>
        <div id="addrShipBox" style="font-size:13px;line-height:1.6"></div>
      </div>
      <div style="border:1px solid var(--border);border-radius:10px;padding:12px">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px">
          <div style="font-size:12px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:.05em">Billing Address</div>
          <button class="btn btn-outline btn-sm" type="button" onclick="copyAddress('billing')">Copy</button>
        </div>
        <div id="addrBillBox" style="font-size:13px;line-height:1.6"></div>
      </div>
    </div>
  </div>
</div>

<?php if ($total > $perPage): ?>
<div style="display:flex;gap:8px;justify-content:center;margin-top:20px;flex-wrap:wrap">
  <?php for ($i = 1; $i <= ceil($total / $perPage); $i++): ?>
  <?php $pageHref = $orderUrl(['page'=>$i,'status'=>$status,'search'=>$search,'payment_status'=>$paymentStatus,'seen'=>$seen,'sort'=>$sort,'date_from'=>$dateFrom,'date_to'=>$dateTo]); ?>
  <a href="<?= htmlspecialchars($pageHref) ?>" class="btn <?= $page === $i ? 'btn-blue' : 'btn-outline' ?> btn-sm"><?= $i ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>

<script>
function toggleOrderCard(btn) {
  const card = btn.closest('[data-order-card]');
  const body = card?.querySelector('.adm-order-card-body');
  if (!card || !body) return;
  const open = card.classList.toggle('open');
  body.hidden = !open;
  card.querySelectorAll('[data-order-toggle]').forEach((toggle) => {
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
}
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

async function setDesignApproval(id, status) {
  const label = status === 'approved' ? 'Approve design?' : 'Describe the artwork/design issue for the customer';
  const note = window.prompt(label, status === 'approved' ? 'Design approved for printing.' : '');
  if (note === null) return;
  if (status === 'issue_found' && !note.trim()) {
    toast('Please add an issue note for the customer', 'error');
    return;
  }
  const resp = await fetch(`/admin/api/design-approvals/${id}`, {
    method: 'POST',
    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'<?= htmlspecialchars($csrf ?? '') ?>'},
    body: JSON.stringify({ status, admin_note: note })
  });
  const data = await resp.json();
  if (data.ok) { toast('Design approval updated', 'success'); setTimeout(() => location.reload(), 500); }
  else toast(data.msg || 'Could not update design approval', 'error');
}

function chooseDesignProof(id) {
  const input = document.getElementById(`proof_${id}`);
  if (input) input.click();
}

async function uploadDesignProof(id) {
  const input = document.getElementById(`proof_${id}`);
  if (!input || !input.files.length) { toast('Please choose a proof file first', 'error'); return; }
  const note = window.prompt('Optional proof note for customer/admin', 'Proof uploaded for review.') ?? '';
  const fd = new FormData();
  fd.append('proof', input.files[0]);
  fd.append('admin_note', note);
  const resp = await fetch(`/admin/api/design-approvals/${id}/proof`, {
    method: 'POST',
    headers: {'X-CSRF-TOKEN':'<?= htmlspecialchars($csrf ?? '') ?>'},
    body: fd
  });
  const data = await resp.json();
  if (data.ok) { toast('Proof uploaded', 'success'); setTimeout(() => location.reload(), 500); }
  else toast(data.msg || 'Proof upload failed', 'error');
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
      ${a.phone ? `<div>Phone: ${a.phone}</div>` : ''}
      ${a.email ? `<div>Email: ${a.email}</div>` : ''}
      <div>${a.address_line1 || ''}${a.address_line2 ? ', ' + a.address_line2 : ''}</div>
      <div>${a.city || ''}, ${a.state || ''} - ${a.pincode || ''}</div>`;
  }
  return `<div>${a.address_line1 || ''}${a.address_line2 ? ', ' + a.address_line2 : ''}</div>
    <div>${a.city || ''}, ${a.state || ''} - ${a.pincode || ''}</div>`;
}

function addrText(a, kind = 'shipping') {
  if (!a || typeof a !== 'object') return '';
  if (kind === 'billing') {
    return [
      a.legal_name || '',
      a.gst_no ? `GSTIN: ${a.gst_no}` : '',
      a.phone ? `Phone: ${a.phone}` : '',
      a.email ? `Email: ${a.email}` : '',
      [a.address_line1 || '', a.address_line2 || ''].filter(Boolean).join(', '),
      [a.city || '', a.state || ''].filter(Boolean).join(', ') + ((a.pincode || '') ? ` - ${a.pincode}` : ''),
    ].filter(Boolean).join('\n');
  }
  return [
    [a.address_line1 || '', a.address_line2 || ''].filter(Boolean).join(', '),
    [a.city || '', a.state || ''].filter(Boolean).join(', ') + ((a.pincode || '') ? ` - ${a.pincode}` : ''),
  ].filter(Boolean).join('\n');
}

function openAddrModal(orderId, shipping, billing) {
  document.getElementById('addrOrdLabel').textContent = `Order #${orderId}`;
  document.getElementById('addrShipBox').innerHTML = fmtAddr(shipping, 'shipping');
  document.getElementById('addrBillBox').innerHTML = fmtAddr(billing, 'billing');
  const modal = document.getElementById('addrModal');
  if (modal) {
    modal.dataset.shipAddr = addrText(shipping, 'shipping');
    modal.dataset.billAddr = addrText(billing, 'billing');
  }
  if (!modal) return;
  modal.style.display = 'flex';
}

async function copyAddress(kind = 'shipping') {
  const modal = document.getElementById('addrModal');
  if (!modal) return;
  const text = kind === 'billing' ? (modal.dataset.billAddr || '') : (modal.dataset.shipAddr || '');
  if (!text) {
    toast('Address not available to copy', 'error');
    return;
  }
  try {
    await navigator.clipboard.writeText(text);
    toast('Address copied to clipboard', 'success');
  } catch (e) {
    toast('Could not copy address', 'error');
  }
}

function closeAddrModal() {
  const modal = document.getElementById('addrModal');
  if (modal) modal.style.display = 'none';
}
</script>
</div>
    </div></div></div>
</body></html>
