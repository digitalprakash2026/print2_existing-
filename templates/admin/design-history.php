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
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
$events = [];
$eventTypes = [];
try {
    $events = Database::rows(
        "SELECT e.*, o.order_id AS public_order_id, o.customer_name, o.customer_email, o.customer_phone,
                o.status AS order_status, oi.product_name, oi.quantity, oi.design_choice,
                oda.status AS current_design_status
           FROM order_design_events e
           LEFT JOIN orders o ON o.id = e.order_id
           LEFT JOIN order_items oi ON oi.id = e.order_item_id
           LEFT JOIN order_design_approvals oda ON oda.id = e.design_approval_id
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
$buildQuery = static function (array $extra = []) use ($q, $eventType, $dateFrom, $dateTo): string {
    $params = array_filter(['q' => $q, 'event_type' => $eventType !== 'all' ? $eventType : '', 'date_from' => $dateFrom, 'date_to' => $dateTo] + $extra, static fn($v) => $v !== '' && $v !== null);
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
    <button type="submit">Filter</button>
    <?php if ($q !== '' || $eventType !== 'all' || $dateFrom !== '' || $dateTo !== ''): ?><a href="/admin/design-history">Clear</a><?php endif; ?>
  </form>

  <?php if (!$events): ?>
    <div class="design-history-empty">No design history events found yet. New uploads, proofs, issues and approvals will appear here automatically.</div>
  <?php else: ?>
    <section class="design-history-list">
      <?php foreach ($events as $event):
        $label = $eventLabels[(string)$event['event_type']] ?? ucwords(str_replace('_', ' ', (string)$event['event_type']));
        $orderLink = !empty($event['order_id']) ? '/admin/orders?q=' . urlencode((string)($event['public_order_id'] ?? $event['order_id'])) : '/admin/orders';
        $filePath = trim((string)($event['file_path'] ?? ''));
      ?>
        <article class="design-event-card">
          <div class="design-event-time">
            <strong><?= $h(date('d M Y', strtotime((string)$event['created_at']))) ?></strong><br>
            <span><?= $h(date('h:i A', strtotime((string)$event['created_at']))) ?></span>
          </div>
          <div class="design-event-main">
            <div class="design-event-title">
              <strong><?= $h($label) ?></strong>
              <em><?= $h($actorIcon((string)$event['actor_type']) . ' ' . ucfirst((string)$event['actor_type'])) ?></em>
              <?php if (!empty($event['status_after'])): ?><em><?= $h((string)$event['status_after']) ?></em><?php endif; ?>
            </div>
            <div class="design-event-meta">
              <span>Order: <b><?= $h($event['public_order_id'] ?? ('#' . $event['order_id'])) ?></b></span>
              <?php if (!empty($event['customer_name'])): ?><span>Customer: <?= $h($event['customer_name']) ?></span><?php endif; ?>
              <?php if (!empty($event['product_name'])): ?><span>Product: <?= $h($event['product_name']) ?></span><?php endif; ?>
              <?php if (!empty($event['actor_name'])): ?><span>By: <?= $h($event['actor_name']) ?></span><?php endif; ?>
            </div>
            <?php if (!empty($event['note'])): ?><p class="design-event-note"><?= $h($event['note']) ?></p><?php endif; ?>
            <?php if ($filePath !== ''): ?><a class="design-event-file" href="<?= $h($filePath) ?>" target="_blank" rel="noopener">📎 <?= $h($event['file_name'] ?: basename($filePath)) ?></a><?php endif; ?>
          </div>
          <div class="design-event-side"><a href="<?= $h($orderLink) ?>">View Order</a></div>
        </article>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>
</div>

    </div></div></div>
</body></html>
