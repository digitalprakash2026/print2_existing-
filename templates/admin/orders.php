<?php
$pageTitle = 'Orders — RCS Admin';
$currentAdmPage = 'orders';
$admMainClass = 'adm-main--orders';
include __DIR__ . '/layout.php';
$statusColors = ['new_order'=>'b-blue','received'=>'b-blue','design_approved'=>'b-green','processing'=>'b-amber','other_process'=>'b-amber','printing'=>'b-orange','ready'=>'b-green','delivered'=>'b-ink','cancelled'=>'b-red','whatsapp_pending'=>'b-amber'];
$statusLabels = ['new_order'=>'New Order','received'=>'Received','design_approved'=>'Design Approved','printing'=>'Printing','other_process'=>'Other Process','processing'=>'Other Process','ready'=>'Dispatched','delivered'=>'Delivered','cancelled'=>'Cancelled','whatsapp_pending'=>'WA Pending'];
$designApprovalLabels = ['pending_review'=>'Pending Review','issue_found'=>'Issue Found','proof_uploaded'=>'Proof Sent','revision_requested'=>'Revision Requested','approved'=>'Approved'];
$designApprovalColors = ['pending_review'=>'b-amber','issue_found'=>'b-red','proof_uploaded'=>'b-blue','revision_requested'=>'b-red','approved'=>'b-green'];
$designCustomerNoteMeta = static function (string $status): array {
    return match ($status) {
        'approved' => ['label' => '✅ Design Approved', 'title' => 'Design Approved By Customer', 'tone' => 'approved', 'hint' => 'Customer confirmation for this item'],
        'revision_requested' => ['label' => '💬 Revision Request', 'title' => 'Revision Request', 'tone' => 'revision', 'hint' => 'Customer message for this item'],
        default => ['label' => '💬 Customer Note', 'title' => 'Customer Note', 'tone' => 'note', 'hint' => 'Customer message for this item'],
    };
};
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
$shortFileName = static function (?string $name, string $fallback = 'File'): string {
    $name = trim((string)$name);
    if ($name === '') return $fallback;
    if (strlen($name) <= 24) return $name;
    $ext = pathinfo($name, PATHINFO_EXTENSION);
    $base = pathinfo($name, PATHINFO_FILENAME);
    $short = substr($base !== '' ? $base : $name, 0, 16);
    return $short . '…' . ($ext !== '' ? '.' . $ext : '');
};
$normalizeAssetPath = static function (?string $path): string {
    $path = trim((string)$path);
    if ($path === '') return '';
    return $path[0] === '/' ? $path : '/' . $path;
};
$isImageFile = static function (?string $mime, ?string $name, ?string $path = null): bool {
    $mime = strtolower(trim((string)$mime));
    $source = trim((string)($name ?: $path));
    $ext = strtolower(pathinfo($source, PATHINFO_EXTENSION));
    return str_starts_with($mime, 'image/') || in_array($ext, ['jpg','jpeg','png','gif','webp','svg'], true);
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
    $createdTs = app_timestamp((string)($o['created_at'] ?? ''));
    $isNewOrder = $orderStatus === 'new_order';
    $hasCustomerUpdate = !empty($o['customer_update_pending']);
    $customerUpdateLabel = match ((string)($o['customer_update_type'] ?? '')) {
        'customer_design_approved' => 'Design Approved by Customer',
        'customer_revision_requested' => 'Revision Requested',
        'customer_artwork_uploaded' => 'Customer Artwork Uploaded',
        'customer_artwork_reuploaded' => 'Artwork Reuploaded',
        default => 'Customer Update',
    };
    $hasDesignAttention = false;
    foreach (($o['items'] ?? []) as $attentionItem) {
        $attentionStatus = (string)($attentionItem['design_approval_status'] ?? '');
        if ($attentionStatus !== '' && $attentionStatus !== 'approved') { $hasDesignAttention = true; break; }
    }
    $ageSeconds = max(0, time() - $createdTs);
    $orderAge = $ageSeconds >= 86400 ? floor($ageSeconds / 86400) . 'd old' : floor($ageSeconds / 3600) . 'h old';
    $cardClasses = ['adm-order-card', 'adm-order-card--' . preg_replace('/[^a-z0-9_-]+/i', '-', $orderStatus)];
    if ($isNewOrder) $cardClasses[] = 'adm-order-card--new';
    if ($hasCustomerUpdate) $cardClasses[] = 'adm-order-card--customer-update';
    if ($hasDesignAttention) $cardClasses[] = 'adm-order-card--design-attention';
  ?>
  <article id="ord-<?= (int)$o['id'] ?>" class="<?= htmlspecialchars(implode(' ', $cardClasses)) ?>" data-order-card>
    <div class="adm-order-card-head">
      <button class="adm-order-card-summary" type="button" aria-expanded="false" data-order-toggle onclick="toggleOrderCard(this)">
        <span class="adm-order-card-id"><strong>#<?= htmlspecialchars($o['order_id']) ?></strong><small><?= htmlspecialchars(app_datetime((string)($o['created_at'] ?? ''), 'd M Y, H:i')) ?> · <?= htmlspecialchars($orderAge) ?></small></span>
        <span class="adm-order-card-customer"><strong><?= htmlspecialchars($o['customer_name']) ?></strong><small><?= htmlspecialchars($o['customer_phone']) ?><?= !empty($o['customer_email']) ? ' · ' . htmlspecialchars($o['customer_email']) : '' ?></small></span>
        <span class="adm-order-card-meta"><b>₹<?= number_format((float)$o['total_amount']) ?></b><small><?= count($o['items'] ?? []) ?> item(s)</small></span>
        <span class="adm-order-card-badges"><span class="badge <?= $statusColors[$orderStatus] ?? 'b-blue' ?>"><?= htmlspecialchars($statusLabels[$orderStatus] ?? $orderStatus) ?></span><span class="badge <?= $o['payment_status'] === 'paid' ? 'b-green' : 'b-amber' ?>"><?= ucfirst($o['payment_status']) ?></span><?php if ($hasCustomerUpdate): ?><span class="badge b-red adm-order-customer-update-badge"><?= htmlspecialchars($customerUpdateLabel) ?></span><?php elseif ($hasDesignAttention): ?><span class="badge b-red adm-order-customer-update-badge">Design Pending</span><?php endif; ?></span>
      </button>
      <div class="adm-order-card-quick" aria-label="Quick order actions">
        <select class="fi fi-sel" id="ord_status_<?= (int)$o['id'] ?>" aria-label="Update status for order <?= htmlspecialchars($o['order_id']) ?>"><?php foreach (['new_order','received','design_approved','printing','other_process','ready','delivered','cancelled'] as $s): ?><option value="<?= $s ?>" <?= $orderStatus === $s ? 'selected' : '' ?>><?= $statusLabels[$s] ?? ucfirst($s) ?></option><?php endforeach; ?></select>
        <button class="btn btn-outline btn-sm" type="button" onclick="updOrdFromSel(<?= (int)$o['id'] ?>)">Update</button>
        <?php if ($hasCustomerUpdate): ?><button class="btn btn-outline btn-sm adm-order-review-btn" type="button" onclick="clearCustomerUpdate(<?= (int)$o['id'] ?>, this)">Mark Reviewed</button><?php endif; ?>
      </div>
      <button class="adm-order-card-toggle" type="button" aria-label="Expand order <?= htmlspecialchars($o['order_id']) ?>" aria-expanded="false" data-order-toggle onclick="toggleOrderCard(this)">⌄</button>
    </div>
    <div class="adm-order-card-body" hidden>
      <div class="adm-order-card-grid">
        <section class="adm-order-card-section adm-order-card-section--full adm-order-card-section--flush">
          <?php foreach (($o['items'] ?? []) as $item): ?>
            <?php
              $approvalId = (int)($item['design_approval_id'] ?? 0);
              $approvalStatus = (string)($item['design_approval_status'] ?? 'pending_review');
              $designChoice = (string)($item['design_choice'] ?? 'upload');
              $isRcsDesign = $designChoice === 'rcs';
            ?>
            <?php
              $productImg = $normalizeAssetPath($item['product_image'] ?? '');
              $artworkName = (string)($item['artwork_original_name'] ?: $item['artwork_filename'] ?: '');
              $artworkPath = $normalizeAssetPath($item['artwork_file_path'] ?? '');
              $artworkIsImage = $isImageFile($item['artwork_mime_type'] ?? '', $artworkName, $artworkPath);
              $proofName = (string)($item['design_proof_original_name'] ?: $item['design_proof_filename'] ?: '');
              $proofPath = $normalizeAssetPath($item['design_proof_file_path'] ?? '');
              $proofIsImage = $isImageFile($item['design_proof_mime_type'] ?? '', $proofName, $proofPath);
            ?>
            <div class="ord-item-row ord-design-workflow ord-design-workflow--<?= htmlspecialchars($approvalStatus) ?> <?= !$isRcsDesign ? 'ord-design-workflow--customer-upload' : 'ord-design-workflow--rcs' ?>">
              <div class="ord-design-head">
                <div class="ord-item-product">
                  <span class="ord-item-thumb">
                    <?php if ($productImg !== ''): ?><img src="<?= htmlspecialchars($productImg) ?>" alt="<?= htmlspecialchars($item['product_name'] ?? 'Product') ?>" loading="lazy"><?php else: ?>📦<?php endif; ?>
                  </span>
                  <div><div class="ord-item-name"><?= htmlspecialchars($item['product_name']) ?> <span class="ord-design-inline-choice"><?= $isRcsDesign ? 'RCS Design' : 'Customer Upload' ?></span></div><div class="ord-meta"><?= number_format((float)$item['quantity']) ?> qty, <?= htmlspecialchars($item['quality_name']) ?> · <?= $isRcsDesign ? 'RCS will prepare proof' : 'Customer artwork approval required' ?></div></div>
                </div>
                <div class="ord-design-badges"><span class="badge <?= $isRcsDesign ? 'b-purple' : 'b-blue' ?>"><?= $isRcsDesign ? '🎨 RCS Design' : '📁 Customer Upload' ?></span><span class="badge <?= $designApprovalColors[$approvalStatus] ?? 'b-amber' ?>"><?= htmlspecialchars($designApprovalLabels[$approvalStatus] ?? $approvalStatus) ?></span></div>
              </div>
              <div class="ord-design-strip">
                <div class="ord-design-filebox">
                  <strong><?= $isRcsDesign ? 'Customer Brief / Assets' : 'Customer Artwork' ?></strong>
                  <?php if (!empty($item['artwork_file_id'])): ?>
                    <a class="ord-file-preview" href="/admin/artwork/<?= (int)$item['artwork_file_id'] ?>/view" target="_blank" rel="noopener" title="<?= htmlspecialchars($artworkName ?: 'Artwork File') ?>">
                      <span class="ord-file-thumb"><?= ($artworkIsImage && $artworkPath !== '') ? '<img src="' . htmlspecialchars($artworkPath) . '" alt="">' : '📄' ?></span>
                      <span class="ord-file-name"><?= htmlspecialchars($shortFileName($artworkName, 'Artwork File')) ?></span>
                    </a>
                    <span class="ord-artwork-actions"><a href="/admin/artwork/<?= (int)$item['artwork_file_id'] ?>/view" class="ord-artwork-link ord-artwork-link--view" target="_blank" rel="noopener">View</a><a href="/admin/artwork/<?= (int)$item['artwork_file_id'] ?>/download" class="ord-artwork-link ord-artwork-link--primary">Download</a></span>
                  <?php else: ?>
                    <span class="ord-artwork-empty"><?= $isRcsDesign ? 'Use WhatsApp/customer communication for brief and assets.' : 'No artwork uploaded' ?></span>
                  <?php endif; ?>
                </div>
                <div class="ord-design-filebox ord-design-filebox--proof">
                  <strong>Corrected File</strong>
                  <?php if (!empty($item['design_proof_file_id'])): ?>
                    <a class="ord-file-preview" href="/admin/artwork/<?= (int)$item['design_proof_file_id'] ?>/view" target="_blank" rel="noopener" title="<?= htmlspecialchars($proofName ?: 'Proof File') ?>">
                      <span class="ord-file-thumb"><?= ($proofIsImage && $proofPath !== '') ? '<img src="' . htmlspecialchars($proofPath) . '" alt="">' : '📄' ?></span>
                      <span class="ord-file-name"><?= htmlspecialchars($shortFileName($proofName, 'Proof File')) ?></span>
                    </a>
                    <?php
                      $proofOrderId = (string)($o['order_id'] ?? $o['id'] ?? '');
                      $proofProfileUrl = rtrim((defined('APP_URL') ? (string)APP_URL : ''), '/') . '/profile#orders-' . rawurlencode($proofOrderId);
                    ?>
                    <span class="ord-artwork-actions"><a href="/admin/artwork/<?= (int)$item['design_proof_file_id'] ?>/view" class="ord-artwork-link ord-artwork-link--view" target="_blank" rel="noopener">View</a><a href="/admin/artwork/<?= (int)$item['design_proof_file_id'] ?>/download" class="ord-artwork-link">Download</a><button class="ord-artwork-link ord-artwork-link--wa" type="button" onclick='waProofReady(<?= json_encode((string)($o['customer_name'] ?? 'Customer'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>, <?= json_encode((string)($o['customer_phone'] ?? ''), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>, <?= json_encode($proofOrderId, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>, <?= json_encode($proofProfileUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>💬 WhatsApp</button></span>
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
              <?php if (!empty($item['design_customer_note'])): ?>
                <?php $noteMeta = $designCustomerNoteMeta($approvalStatus); ?>
                <div class="ord-design-note ord-design-note--customer">
                  <button class="ord-customer-note-chip ord-customer-note-chip--<?= htmlspecialchars($noteMeta['tone']) ?>" type="button" onclick='openCustomerDesignNote(<?= json_encode($item['product_name'] ?? 'Product', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>, <?= json_encode($item['design_customer_note'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>, <?= json_encode($noteMeta['title'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>, <?= json_encode($noteMeta['hint'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>, <?= json_encode($noteMeta['tone'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'><?= htmlspecialchars($noteMeta['label']) ?></button>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </section>
        <section class="adm-order-card-section adm-order-card-section--full adm-order-action-strip"><span class="ord-action-strip-label">Quick actions</span><div class="ord-actions ord-actions--compact"><a href="tel:<?= htmlspecialchars(preg_replace('/\D+/', '', $o['customer_phone'] ?? '')) ?>" class="aoc-btn aoc-btn--call">📞 Call Customer</a><button class="aoc-btn aoc-btn--wa" onclick="waCustomer('<?= htmlspecialchars(addslashes($o['customer_name'])) ?>','<?= htmlspecialchars($o['customer_phone']) ?>','<?= htmlspecialchars($o['order_id']) ?>','<?= htmlspecialchars($orderStatus) ?>')">💬 WhatsApp</button><a href="/admin/invoice/<?= htmlspecialchars($o['order_id']) ?>" class="aoc-btn aoc-btn--invoice" target="_blank">🧾 Invoice</a><button class="aoc-btn aoc-btn--address" onclick='openAddrModal("<?= htmlspecialchars($o['order_id']) ?>", <?= json_encode($orderShipping, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>, <?= json_encode($orderBilling, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>)'>📍 Address</button></div></section>
      </div>
    </div>
  </article>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div id="revisionModal" class="ord-revision-modal" style="display:none">
  <div class="ord-revision-dialog">
    <div class="ord-revision-head"><div><strong id="revisionModalTitle">Revision Request</strong><small id="revisionModalHint">Customer message for this item</small></div><button type="button" onclick="closeRevisionNote()">×</button></div>
    <p id="revisionModalText"></p>
  </div>
</div>

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
const ADM_OPEN_ORDER_KEY = 'adm_open_order_cards';
function getOpenOrderCards() {
  try { return JSON.parse(sessionStorage.getItem(ADM_OPEN_ORDER_KEY) || '[]'); }
  catch (e) { return []; }
}
function saveOpenOrderCard(card, open) {
  if (!card?.id) return;
  const ids = new Set(getOpenOrderCards());
  if (open) ids.add(card.id);
  else ids.delete(card.id);
  sessionStorage.setItem(ADM_OPEN_ORDER_KEY, JSON.stringify([...ids]));
}
function setOrderCardOpen(card, open, persist = true) {
  const body = card?.querySelector('.adm-order-card-body');
  if (!card || !body) return;
  card.classList.toggle('open', open);
  body.hidden = !open;
  card.querySelectorAll('[data-order-toggle]').forEach((toggle) => {
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
  if (persist) saveOpenOrderCard(card, open);
}
function rememberCardForControl(control) {
  const card = control?.closest?.('[data-order-card]');
  if (card) saveOpenOrderCard(card, true);
}
function rememberCardForApproval(id) {
  const input = document.getElementById(`proof_${id}`);
  rememberCardForControl(input);
}
function restoreOpenOrderCards() {
  getOpenOrderCards().forEach((id) => {
    const card = document.getElementById(id);
    if (card) setOrderCardOpen(card, true, false);
  });
}
function toggleOrderCard(btn) {
  const card = btn.closest('[data-order-card]');
  if (!card) return;
  setOrderCardOpen(card, !card.classList.contains('open'));
}
function toast(msg, type='info') {
  const w = document.getElementById('tw');
  const t = document.createElement('div'); t.className = 'toast ' + type; t.textContent = msg; w.appendChild(t);
  requestAnimationFrame(() => requestAnimationFrame(() => t.classList.add('show')));
  setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 2800);
}

function openCustomerDesignNote(product, note, title='Customer Note', hint='Customer message for this item', tone='note') {
  const modal = document.getElementById('revisionModal');
  const dialog = modal?.querySelector('.ord-revision-dialog');
  if (dialog) dialog.dataset.tone = tone || 'note';
  document.getElementById('revisionModalTitle').textContent = `${title || 'Customer Note'} — ${product || 'Item'}`;
  document.getElementById('revisionModalHint').textContent = hint || 'Customer message for this item';
  document.getElementById('revisionModalText').textContent = note || 'No message provided.';
  if (modal) modal.style.display = 'flex';
}
function closeRevisionNote() {
  const modal = document.getElementById('revisionModal');
  if (modal) modal.style.display = 'none';
}

function updOrdFromSel(id) {
  const sel = document.getElementById(`ord_status_${id}`);
  if (!sel) return;
  updOrd(id, sel.value);
}

async function updOrd(id, status) {
  const statusSelect = document.getElementById(`ord_status_${id}`);
  rememberCardForControl(statusSelect);
  const resp = await fetch(`/admin/api/orders/${id}/status`, {
    method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'<?= htmlspecialchars($csrf ?? '') ?>'},
    body: JSON.stringify({ status })
  });
  const data = await resp.json();
  if (data.ok) { toast('Order updated to ' + status, 'success'); setTimeout(() => location.reload(), 500); }
  else toast('Update failed', 'error');
}

async function clearCustomerUpdate(id, btn) {
  rememberCardForControl(btn);
  if (btn) btn.disabled = true;
  const resp = await fetch(`/admin/api/orders/${id}/customer-update/clear`, {
    method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'<?= htmlspecialchars($csrf ?? '') ?>'}
  });
  const data = await resp.json();
  if (data.ok) { toast('Customer update marked reviewed', 'success'); setTimeout(() => location.reload(), 500); }
  else { toast(data.msg || 'Could not mark reviewed', 'error'); if (btn) btn.disabled = false; }
}

async function setDesignApproval(id, status) {
  const label = status === 'approved' ? 'Approve design?' : 'Describe the artwork/design issue for the customer';
  const note = window.prompt(label, status === 'approved' ? 'Design approved for printing.' : '');
  if (note === null) return;
  if (status === 'issue_found' && !note.trim()) {
    toast('Please add an issue note for the customer', 'error');
    return;
  }
  rememberCardForApproval(id);
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
  rememberCardForControl(input);
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
document.addEventListener('DOMContentLoaded', restoreOpenOrderCards);

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

function cleanWhatsAppPhone(phone) {
  let cleanPhone = String(phone || '').replace(/\D/g, '');
  if (cleanPhone.length === 10) cleanPhone = `91${cleanPhone}`;
  return cleanPhone;
}

function openWhatsAppMessage(phone, msg) {
  const cleanPhone = cleanWhatsAppPhone(phone);
  if (!cleanPhone) { toast('Customer WhatsApp number is missing', 'error'); return; }
  window.open(`https://wa.me/${cleanPhone}?text=${encodeURIComponent(msg)}`, '_blank');
}

function waCustomer(name, phone, ordId, status) {
  const msg = `Hi ${name}! 👋\nOrder ID: #${ordId}\nCurrent status: ${status.toUpperCase()}\nIf you need help, reply to this message.`;
  openWhatsAppMessage(phone, msg);
}

function waProofReady(name, phone, ordId, proofReviewUrl) {
  const reviewUrl = proofReviewUrl || `${location.origin}/profile#orders-${encodeURIComponent(ordId || '')}`;
  const msg = `Hello ${name || 'Customer'},\n\nYour corrected design proof for Order #${ordId} is ready for review.\n\nPlease login to your account and check My Orders to approve the design or request a revision:\n${reviewUrl}\n\nThank you,\nRCS Print\nFree Free To Call On the Number Given Below for Any query regarding Or Order\nMo: +91 8980000024`;
  openWhatsAppMessage(phone, msg);
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
