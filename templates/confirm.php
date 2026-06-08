<?php
$pageTitle = 'Order Confirmed — RCS Graphic';
include INCLUDE_PATH . '/partials/head.php';
include INCLUDE_PATH . '/partials/header.php';
$bizWa = Database::setting('biz_whatsapp', env('BIZ_WHATSAPP', ''));
$bizName = Database::setting('biz_name', 'RCS Graphic');
?>
<div style="margin-top:calc(var(--site-hh, var(--hh)) + var(--post-header-gap,50px));min-height:calc(100vh - var(--site-hh, var(--hh)) - var(--post-header-gap,50px));display:flex;align-items:center;justify-content:center;padding:36px 18px;background:var(--bg)">
  <div class="confirm-card">
    <div class="confirm-ic">
      <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
    </div>
    <div style="font-family:var(--fd);font-size:25px;font-weight:700;text-align:center;margin-bottom:5px">Order Confirmed! 🎉</div>
    <div style="font-size:14px;color:var(--text2);text-align:center;margin-bottom:16px">We'll start your print job shortly.</div>
    <div class="confirm-oid">#<?= htmlspecialchars($order['order_id']) ?></div>
    <div class="confirm-rows">
      <?php foreach ($order['items'] as $item): ?>
      <div class="cr">
        <span class="cr-l"><?= htmlspecialchars($item['product_name']) ?></span>
        <span class="cr-v"><?= number_format($item['quantity']) ?> pcs · <?= htmlspecialchars($item['quality_name']) ?></span>
      </div>
      <div class="cr">
        <span class="cr-l">Design</span>
        <span class="cr-v"><?= $item['design_choice'] === 'rcs' ? '🎨 Design by RCS' : '📁 Customer Upload' ?></span>
      </div>
      <?php endforeach; ?>
      <?php if ($order['discount_amount'] > 0): ?>
      <div class="cr"><span class="cr-l">Discount</span><span class="cr-v" style="color:var(--green)">-₹<?= number_format($order['discount_amount']) ?></span></div>
      <?php endif; ?>
      <div class="cr"><span class="cr-l">GST (<?= $order['gst_percent'] ?>%)</span><span class="cr-v">₹<?= number_format($order['gst_amount']) ?></span></div>
      <div class="cr"><span class="cr-l">Total <?= $order['payment_status'] === 'paid' ? 'Paid' : '' ?></span><span class="cr-v" style="color:var(--blue);font-size:16px;font-family:var(--fd)">₹<?= number_format($order['total_amount']) ?></span></div>
      <?php if ($order['payment_id']): ?>
      <div class="cr"><span class="cr-l">Payment ID</span><span class="cr-v" style="font-family:monospace;font-size:11px"><?= htmlspecialchars($order['payment_id']) ?></span></div>
      <?php endif; ?>
    </div>
    <div style="display:flex;flex-direction:column;gap:9px">
      <button class="btn btn-blue btn-full" onclick="shareConfirm()">📲 Share Confirmation</button>
      <a href="/my-orders" class="btn btn-outline btn-full">📋 Track My Order</a>
      <?php if ($order['payment_status'] === 'paid'): ?>
      <a href="/invoice/<?= htmlspecialchars($order['order_id']) ?>" class="btn btn-outline btn-full" target="_blank">🧾 Download Invoice</a>
      <?php endif; ?>
      <a href="/" class="btn btn-ghost btn-full">← Continue Shopping</a>
    </div>
  </div>
</div>
<script>
const CSRF = '<?= $csrf ?>';
const BIZ_WA = '<?= htmlspecialchars($bizWa) ?>';
function shareConfirm() {
  const msg = `✅ Order Confirmed at <?= htmlspecialchars(addslashes($bizName)) ?>!\nOrder: #<?= htmlspecialchars($order['order_id']) ?>\nTotal: ₹<?= number_format($order['total_amount']) ?>\nThank you!`;
  window.open(`https://wa.me/?text=${encodeURIComponent(msg)}`, '_blank');
}
</script>
<script src="/assets/js/app.js"></script>
<?php include INCLUDE_PATH . '/partials/site-footer.php'; ?>
<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
