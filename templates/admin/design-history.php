<?php
$pageTitle = 'Design History — RCS Admin';
$currentAdmPage = 'design-history';
include __DIR__ . '/layout.php';

\Orders\OrderManager::ensureDesignEventSchema();
$h = static fn($v): string => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$q = trim((string)($_GET['q'] ?? ''));
$eventType = trim((string)($_GET['event_type'] ?? 'all'));
$dateFrom = trim((string)($_GET['date_from'] ?? ''));
$dateTo = trim((string)($_GET['date_to'] ?? ''));
$actionFilter = trim((string)($_GET['action'] ?? 'all'));
$where = [];
$params = [];
if ($q !== '') {
    $where[] = '(o.order_id LIKE ? OR o.customer_name LIKE ? OR o.customer_email LIKE ? OR o.customer_phone LIKE ? OR oi.product_name LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like, $like);
}
if ($eventType !== '' && $eventType !== 'all') {
    $where[] = 'e.event_type = ?';
    $params[] = $eventType;
}
if ($dateFrom !== '') { $where[] = 'DATE(e.created_at) >= ?'; $params[] = $dateFrom; }
if ($dateTo !== '') { $where[] = 'DATE(e.created_at) <= ?'; $params[] = $dateTo; }
if ($actionFilter === 'needs_action') {
    $where[] = "oda.status IN ('pending_review','issue_found','proof_uploaded','revision_requested')";
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
$events = [];
$eventTypes = [];
try {
    $events = Database::rows(
        "SELECT e.*, o.order_id AS public_order_id, o.customer_name, o.customer_email, o.customer_phone,
                o.status AS order_status, oi.product_name, oi.quantity, oi.design_choice,
                oda.status AS current_design_status,
                caf.file_path AS current_artwork_path, caf.original_name AS current_artwork_name, caf.filename AS current_artwork_filename,
                pf.file_path AS current_proof_path, pf.original_name AS current_proof_name, pf.filename AS current_proof_filename
           FROM order_design_events e
           LEFT JOIN orders o ON o.id = e.order_id
           LEFT JOIN order_items oi ON oi.id = e.order_item_id
           LEFT JOIN order_design_approvals oda ON oda.id = e.design_approval_id
           LEFT JOIN artwork_files caf ON caf.id = oda.customer_artwork_file_id
           LEFT JOIN artwork_files pf ON pf.id = oda.proof_file_id
           $whereSql
          ORDER BY e.created_at DESC
          LIMIT 250",
        $params
    );
    $eventTypes = Database::rows("SELECT event_type, COUNT(*) AS count FROM order_design_events GROUP BY event_type ORDER BY event_type ASC");
} catch (Throwable $e) {
    error_log('Design history page failed: ' . $e->getMessage());
}
$eventLabels = [
    'design_approval_created' => 'Workflow Started',
    'design_approval_synced' => 'Workflow Synced',
    'admin_issue_marked' => 'Issue Marked',
    'admin_proof_uploaded' => 'Proof Uploaded',
    'admin_design_approved' => 'Admin Approved',
    'customer_design_approved' => 'Customer Approved',
    'customer_revision_requested' => 'Revision Requested',
    'customer_artwork_uploaded' => 'Artwork Uploaded',
    'customer_artwork_reuploaded' => 'Artwork Reuploaded',
    'media_archived' => 'Media Archived',
    'design_status_changed' => 'Status Changed',
];
$actorIcon = static fn(string $type): string => match ($type) { 'customer' => '👤', 'admin' => '🛡️', default => '⚙️' };
$eventTone = static function (string $type, string $status): string {
    if (str_contains($type, 'issue') || str_contains($type, 'revision') || in_array($status, ['issue_found','revision_requested'], true)) return 'danger';
    if (str_contains($type, 'approved') || $status === 'approved') return 'success';
    if (str_contains($type, 'proof') || str_contains($type, 'uploaded')) return 'info';
    return 'neutral';
};
$needsActionCount = count(array_filter($events, static fn($event): bool => in_array((string)($event['current_design_status'] ?? ''), ['pending_review','issue_found','proof_uploaded','revision_requested'], true)));
$approvedCount = count(array_filter($events, static fn($event): bool => (string)($event['current_design_status'] ?? '') === 'approved'));
$issueCount = count(array_filter($events, static fn($event): bool => in_array((string)($event['current_design_status'] ?? ''), ['issue_found','revision_requested'], true)));
$buildQuery = static function (array $extra = []) use ($q, $eventType, $dateFrom, $dateTo, $actionFilter): string {
    $base = ['q' => $q, 'event_type' => $eventType !== 'all' ? $eventType : '', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'action' => $actionFilter !== 'all' ? $actionFilter : ''];
    $params = array_filter(array_merge($base, $extra), static fn($v) => $v !== '' && $v !== null);
    return $params ? ('?' . http_build_query($params)) : '';
};
?>

<div class="design-history-page">
  <section class="design-history-hero">
    <div>
      <span>Audit trail</span>
      <h1>Order Design History</h1>
      <p>Track customer artwork, admin proofs, issue notes, revision requests and approvals in one reversible timeline.</p>
    </div>
    <strong><?= number_format(count($events)) ?> events</strong>
  </section>

  <section class="design-history-stats" aria-label="Design history summary">
    <a class="design-history-stat <?= $actionFilter === 'needs_action' ? 'act' : '' ?>" href="/admin/design-history<?= $h($buildQuery(['action' => 'needs_action'])) ?>"><span>Needs Action</span><strong><?= number_format($needsActionCount) ?></strong></a>
    <div class="design-history-stat"><span>Approved</span><strong><?= number_format($approvedCount) ?></strong></div>
    <div class="design-history-stat"><span>Issues / Revisions</span><strong><?= number_format($issueCount) ?></strong></div>
    <a class="design-history-stat" href="/admin/design-history"><span>Reset View</span><strong>All</strong></a>
  </section>

  <form class="design-history-filters" method="GET">
    <input type="search" name="q" value="<?= $h($q) ?>" placeholder="Search order, customer, product…">
    <select name="event_type">
      <option value="all">All events</option>
      <?php foreach ($eventTypes as $type): $key = (string)($type['event_type'] ?? ''); ?>
        <option value="<?= $h($key) ?>" <?= $eventType === $key ? 'selected' : '' ?>><?= $h($eventLabels[$key] ?? ucwords(str_replace('_', ' ', $key))) ?> (<?= number_format((int)($type['count'] ?? 0)) ?>)</option>
      <?php endforeach; ?>
    </select>
    <input type="date" name="date_from" value="<?= $h($dateFrom) ?>">
    <input type="date" name="date_to" value="<?= $h($dateTo) ?>">
    <select name="action">
      <option value="all" <?= $actionFilter === 'all' ? 'selected' : '' ?>>All action states</option>
      <option value="needs_action" <?= $actionFilter === 'needs_action' ? 'selected' : '' ?>>Needs action only</option>
    </select>
    <button type="submit">Filter</button>
    <?php if ($q !== '' || $eventType !== 'all' || $dateFrom !== '' || $dateTo !== '' || $actionFilter !== 'all'): ?><a href="/admin/design-history">Clear</a><?php endif; ?>
  </form>

  <?php if (!$events): ?>
    <div class="design-history-empty">No design history events found yet. New uploads, proofs, issues and approvals will appear here automatically.</div>
  <?php else: ?>
    <section class="design-history-list">
      <?php foreach ($events as $event):
        $label = $eventLabels[(string)$event['event_type']] ?? ucwords(str_replace('_', ' ', (string)$event['event_type']));
        $orderLink = !empty($event['order_id']) ? '/admin/orders?q=' . urlencode((string)($event['public_order_id'] ?? $event['order_id'])) : '/admin/orders';
        $filePath = trim((string)($event['file_path'] ?? ''));
        $fileName = trim((string)($event['file_name'] ?? ''));
        $fallbackProofPath = trim((string)($event['current_proof_path'] ?? ''));
        $fallbackArtworkPath = trim((string)($event['current_artwork_path'] ?? ''));
        if ($filePath === '' && in_array((string)$event['event_type'], ['admin_design_approved','customer_design_approved'], true)) {
            $filePath = $fallbackProofPath !== '' ? $fallbackProofPath : $fallbackArtworkPath;
            $fileName = $fallbackProofPath !== ''
                ? trim((string)(($event['current_proof_name'] ?? '') ?: ($event['current_proof_filename'] ?? '')))
                : trim((string)(($event['current_artwork_name'] ?? '') ?: ($event['current_artwork_filename'] ?? '')));
        }
        $tone = $eventTone((string)$event['event_type'], (string)($event['status_after'] ?? ''));
        $statusBefore = trim((string)($event['status_before'] ?? ''));
        $statusAfter = trim((string)($event['status_after'] ?? ''));
      ?>
        <article class="design-event-card design-event-card--<?= $h($tone) ?>">
          <div class="design-event-time">
            <strong><?= $h(date('d M Y', strtotime((string)$event['created_at']))) ?></strong><br>
            <span><?= $h(date('h:i A', strtotime((string)$event['created_at']))) ?></span>
          </div>
          <div class="design-event-main">
            <div class="design-event-title">
              <strong><?= $h($label) ?></strong>
              <em class="design-event-chip"><?= $h($actorIcon((string)$event['actor_type']) . ' ' . ucfirst((string)$event['actor_type'])) ?></em>
              <?php if ($statusAfter !== ''): ?><em class="design-event-chip design-event-chip--<?= $h($tone) ?>"><?= $h($statusAfter) ?></em><?php endif; ?>
            </div>
            <div class="design-event-meta">
              <span>Order: <b><?= $h($event['public_order_id'] ?? ('#' . $event['order_id'])) ?></b></span>
              <?php if (!empty($event['customer_name'])): ?><span>Customer: <?= $h($event['customer_name']) ?></span><?php endif; ?>
              <?php if (!empty($event['product_name'])): ?><span>Product: <?= $h($event['product_name']) ?></span><?php endif; ?>
              <?php if (!empty($event['actor_name'])): ?><span>By: <?= $h($event['actor_name']) ?></span><?php endif; ?>
              <?php if (!empty($event['design_choice'])): ?><span>Design: <?= $h(ucfirst((string)$event['design_choice'])) ?></span><?php endif; ?>
              <?php if (!empty($event['current_design_status'])): ?><span>Current: <b><?= $h((string)$event['current_design_status']) ?></b></span><?php endif; ?>
            </div>
            <?php if ($statusBefore !== '' || $statusAfter !== ''): ?>
              <div class="design-event-status-flow"><span><?= $h($statusBefore !== '' ? $statusBefore : 'new') ?></span><i>→</i><strong><?= $h($statusAfter !== '' ? $statusAfter : 'updated') ?></strong></div>
            <?php endif; ?>
            <?php if (!empty($event['note'])): ?><p class="design-event-note"><?= $h($event['note']) ?></p><?php endif; ?>
            <?php if ($filePath !== ''): ?>
              <div class="design-event-file-row">
                <a class="design-event-file" href="<?= $h($filePath) ?>" target="_blank" rel="noopener">📎 <?= $h($fileName !== '' ? $fileName : basename($filePath)) ?></a>
                <a class="design-event-file-action" href="<?= $h($filePath) ?>" target="_blank" rel="noopener">View</a>
                <a class="design-event-file-action" href="<?= $h($filePath) ?>" download>Download</a>
              </div>
            <?php endif; ?>
          </div>
          <div class="design-event-side"><a href="<?= $h($orderLink) ?>">View Order</a></div>
        </article>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>
</div>

    </div></div></div>
</body></html>
