<?php
$pageTitle = 'My Orders — RCS Graphic';
$currentPage = 'my-orders';
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/header.php';
// cart-drawer is included by header.php — do not include again

$statusLabels = ['received'=>'Received','processing'=>'Processing','printing'=>'Printing','ready'=>'Ready','delivered'=>'Delivered','cancelled'=>'Cancelled','whatsapp_pending'=>'Pending'];
$statusColors = ['received'=>'b-blue','processing'=>'b-amber','printing'=>'b-orange','ready'=>'b-green','delivered'=>'b-ink','cancelled'=>'b-red','whatsapp_pending'=>'b-amber'];
$tlSteps = ['received','processing','printing','ready','delivered'];
?>
<div class="myord-hdr">
  <div class="container">
    <div class="myord-hdr-t">My Orders</div>
    <div class="myord-hdr-s">Track your print orders in real time</div>
  </div>
</div>
<div class="container" style="padding-top:24px;padding-bottom:60px">
  <?php if (empty($orders)): ?>
  <div style="text-align:center;padding:60px 20px">
    <div style="font-size:48px;margin-bottom:12px">📦</div>
    <div style="font-size:18px;font-weight:700;margin-bottom:8px">No Orders Yet</div>
    <div style="font-size:14px;color:var(--text2);margin-bottom:20px">Your orders will appear here</div>
    <a href="/#prod-sec" class="btn btn-blue">Browse Products →</a>
  </div>
  <?php else: ?>
  <?php foreach ($orders as $order):
    $si = array_search($order['status'], $tlSteps);
    $si = $si === false ? -1 : $si;
    $items = $order['items'] ?? [];
    $itemDesc = implode(' + ', array_column($items, 'product_name'));
  ?>
  <div class="ord-card">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:12px">
      <div>
        <div class="ord-id">#<?= htmlspecialchars($order['order_id']) ?></div>
        <div class="ord-prod"><?= htmlspecialchars($itemDesc) ?></div>
        <div class="ord-meta">
          <?= date('d M Y', strtotime($order['created_at'])) ?> ·
          ₹<?= number_format($order['total_amount']) ?> ·
          <?= count($items) ?> item<?= count($items) > 1 ? 's' : '' ?>
        </div>
      </div>
      <span class="badge <?= $statusColors[$order['status']] ?? 'b-blue' ?>">
        <?= $statusLabels[$order['status']] ?? $order['status'] ?>
      </span>
    </div>

    <?php if ($order['status'] !== 'cancelled' && $order['status'] !== 'whatsapp_pending'): ?>
    <div class="ord-timeline">
      <?php foreach ($tlSteps as $i => $step): ?>
      <div class="tl-step <?= $i < $si ? 'done' : ($i === $si ? 'active' : '') ?>">
        <div class="tl-circle"><?= $i < $si ? '✓' : ($i + 1) ?></div>
        <div class="tl-label"><?= $statusLabels[$step] ?></div>
      </div>
      <?php if ($i < count($tlSteps) - 1): ?>
      <div class="tl-line <?= $i < $si ? 'done' : '' ?>"></div>
      <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div style="padding-top:8px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
      <div style="font-size:12px;color:var(--text2)">
        <?php foreach ($items as $item): ?>
        <?= htmlspecialchars($item['product_name']) ?>: <?= number_format($item['quantity']) ?> × <?= htmlspecialchars($item['quality_name']) ?> |
        <?php endforeach; ?>
      </div>
      <div style="display:flex;gap:7px">
        <?php if (in_array($order['payment_status'], ['paid'])): ?>
        <a href="/invoice/<?= htmlspecialchars($order['order_id']) ?>" class="btn btn-outline btn-xs" target="_blank">🧾 Invoice</a>
        <?php endif; ?>
        <?php if ($order['payment_id']): ?>
        <button class="btn btn-outline btn-xs" onclick="alert('Payment ID:\n<?= htmlspecialchars($order['payment_id']) ?>')">🆔 PID</button>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>
<script>const CSRF = '<?= $csrf ?>';</script>
<script src="/js/app.js"></script>
</body></html>
