<?php
$pageTitle = 'My Account — RCS Graphic';
$currentPage = 'profile';
include INCLUDE_PATH . '/partials/head.php';
include INCLUDE_PATH . '/partials/header.php';

$profile = $profile ?? [];
$billing = $profile['billing'] ?? [];
$shipping = $profile['shipping'] ?? [];
$orders = is_array($orders ?? null) ? $orders : [];

$h = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$name = trim((string)($profile['name'] ?? $user['name'] ?? 'RCS Customer'));
$email = trim((string)($profile['email'] ?? $user['email'] ?? ''));
$phone = trim((string)($profile['phone'] ?? $user['phone'] ?? ''));
$company = trim((string)($profile['company'] ?? $user['company'] ?? ''));
$city = trim((string)($shipping['city'] ?? $billing['city'] ?? 'Rajkot'));
$state = trim((string)($shipping['state'] ?? $billing['state'] ?? 'Gujarat'));
$pincode = trim((string)($shipping['pincode'] ?? $billing['pincode'] ?? ''));
$locationParts = array_filter([$city, $state, $pincode]);
$location = $locationParts ? implode(', ', $locationParts) : 'Add your default address';
$initials = strtoupper(substr(trim($name), 0, 1) ?: 'R');
$hasSavedAddress = trim((string)($shipping['address_line1'] ?? '')) !== '' || trim((string)($billing['address_line1'] ?? '')) !== '';
$savedAddressCount = $hasSavedAddress ? 1 : 0;

$statusLabels = [
    'received' => 'Received',
    'processing' => 'In Progress',
    'printing' => 'Printing',
    'ready' => 'Ready',
    'delivered' => 'Delivered',
    'cancelled' => 'Cancelled',
    'whatsapp_pending' => 'Pending',
];
$progressStatuses = ['received', 'processing', 'printing', 'ready', 'whatsapp_pending'];
$totalOrders = count($orders);
$progressOrders = count(array_filter($orders, static fn($order) => in_array((string)($order['status'] ?? ''), $progressStatuses, true)));
$completedOrders = count(array_filter($orders, static fn($order) => (string)($order['status'] ?? '') === 'delivered'));
$recentOrders = array_slice($orders, 0, 4);
$recentDesigns = [];
foreach ($orders as $order) {
    foreach (($order['items'] ?? []) as $item) {
        $recentDesigns[] = [
            'name' => trim((string)($item['product_name'] ?? 'Print Design')) ?: 'Print Design',
            'date' => $order['created_at'] ?? date('Y-m-d'),
            'design' => $item['design_choice'] ?? 'upload',
        ];
        if (count($recentDesigns) >= 4) break 2;
    }
}
$designFallbacks = [
    ['name' => 'Business Card', 'date' => date('Y-m-d'), 'design' => 'upload'],
    ['name' => 'Flyer Design', 'date' => date('Y-m-d', strtotime('-2 days')), 'design' => 'rcs'],
    ['name' => 'Brochure Design', 'date' => date('Y-m-d', strtotime('-5 days')), 'design' => 'upload'],
    ['name' => 'Poster Design', 'date' => date('Y-m-d', strtotime('-8 days')), 'design' => 'rcs'],
];
while (count($recentDesigns) < 4) {
    $recentDesigns[] = $designFallbacks[count($recentDesigns)];
}
$accountSettings = is_array($settingsMap ?? null) ? $settingsMap : [];
if ($accountSettings === []) {
    try {
        $settingsRows = Database::rows("SELECT `key`, value FROM settings");
        $accountSettings = array_column($settingsRows, 'value', 'key');
    } catch (\Throwable) {
        $accountSettings = [];
    }
}
$accountBizPhoneRaw = trim((string)($accountSettings['biz_phone'] ?? '+91 8980000023')) ?: '+91 8980000023';
$accountBizPhone = $h($accountBizPhoneRaw);
$accountBizPhoneHref = preg_replace('/\D+/', '', $accountBizPhoneRaw);
$accountBizWaRaw = trim((string)($accountSettings['biz_whatsapp'] ?? $accountBizPhoneRaw));
$accountBizWa = preg_replace('/\D+/', '', $accountBizWaRaw);
if ($accountBizWa === '') {
    $accountBizWa = $accountBizPhoneHref;
}

$renderOrders = static function (array $list, bool $compact = false) use ($h, $statusLabels): void {
    if (empty($list)) {
        ?>
        <div class="account-empty-state">
          <i class="fa-solid fa-box-open"></i>
          <strong>No orders yet</strong>
          <span>Your print orders will appear here after checkout.</span>
          <a href="/categories" class="btn btn-blue btn-sm">Browse Products</a>
        </div>
        <?php
        return;
    }
    ?>
    <div class="account-order-table" role="table" aria-label="<?= $compact ? 'Recent orders' : 'All orders' ?>">
      <div class="account-order-row account-order-head" role="row">
        <span>Order ID</span><span>Date</span><span>Products</span><span>Amount</span><span>Status</span><span>Action</span>
      </div>
      <?php foreach ($list as $order):
        $items = is_array($order['items'] ?? null) ? $order['items'] : [];
        $status = (string)($order['status'] ?? 'received');
        $statusClass = preg_replace('/[^a-z0-9_-]/i', '', $status);
        $productTitle = implode(', ', array_filter(array_map(static fn($item) => (string)($item['product_name'] ?? ''), $items)));
        $orderPublicId = (string)($order['order_id'] ?? $order['id'] ?? '');
        $isPaid = in_array((string)($order['payment_status'] ?? ''), ['paid'], true);
        $trackSteps = ['received', 'processing', 'printing', 'ready', 'delivered'];
        $trackIndex = array_search($status, $trackSteps, true);
        $trackIndex = $trackIndex === false ? -1 : (int)$trackIndex;
        $isCancelled = $status === 'cancelled';
        $isWhatsappPending = $status === 'whatsapp_pending';
      ?>
        <details class="account-order-detail">
          <summary class="account-order-row" role="row">
            <strong>#<?= $h($order['order_id'] ?? $order['id'] ?? '') ?></strong>
            <span><?= !empty($order['created_at']) ? date('d M, Y', strtotime((string)$order['created_at'])) : '—' ?></span>
            <span class="account-product-mini" title="<?= $h($productTitle) ?>">
              <?php foreach (array_slice($items, 0, 3) as $idx => $item): ?>
                <i style="--mini:<?= (int)$idx ?>"><?= $h(strtoupper(substr((string)($item['product_name'] ?? 'P'), 0, 1))) ?></i>
              <?php endforeach; ?>
              <?php if (count($items) > 3): ?><em>+<?= count($items) - 3 ?></em><?php endif; ?>
              <?php if (empty($items)): ?><em>0</em><?php endif; ?>
            </span>
            <b>₹<?= number_format((float)($order['total_amount'] ?? 0)) ?></b>
            <span class="account-status status-<?= $h($statusClass) ?>"><?= $h($statusLabels[$status] ?? ucfirst($status)) ?></span>
            <span class="account-mini-btn">Actions <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></span>
          </summary>
          <div class="account-order-expanded">
            <div>
              <strong>Products</strong>
              <?php if (empty($items)): ?>
                <p>No product items found for this order.</p>
              <?php else: ?>
                <ul>
                  <?php foreach ($items as $item): ?>
                    <li><?= $h($item['product_name'] ?? 'Product') ?> — <?= number_format((float)($item['quantity'] ?? 0)) ?> × <?= $h($item['quality_name'] ?? 'Standard') ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </div>
            <div>
              <strong>Payment</strong>
              <p><?= $h(ucfirst((string)($order['payment_status'] ?? 'pending'))) ?> · <?= $h(ucfirst((string)($order['payment_method'] ?? ''))) ?></p>
              <?php if (!empty($order['payment_id'])): ?><small>Payment ID: <?= $h($order['payment_id']) ?></small><?php endif; ?>
            </div>
            <div class="account-order-actions-list" aria-label="Order actions">
              <strong>Actions</strong>
              <button type="button" onclick="openAccountOrder(this)"><i class="fa-solid fa-truck-fast" aria-hidden="true"></i> Track Order</button>
              <?php if ($isPaid && $orderPublicId !== ''): ?>
                <a href="/invoice/<?= rawurlencode($orderPublicId) ?>" target="_blank" rel="noopener"><i class="fa-regular fa-file-lines" aria-hidden="true"></i> Download Invoice</a>
              <?php else: ?>
                <span class="account-order-action-disabled"><i class="fa-regular fa-file-lines" aria-hidden="true"></i> Invoice after payment</span>
              <?php endif; ?>
            </div>
            <div class="account-order-tracking" aria-label="Tracking detail">
              <div class="account-track-head">
                <strong>Tracking Detail</strong>
                <span><?= $h($statusLabels[$status] ?? ucfirst($status)) ?></span>
              </div>
              <?php if ($isCancelled): ?>
                <div class="account-track-alert is-cancelled"><i class="fa-solid fa-circle-xmark" aria-hidden="true"></i> This order has been cancelled.</div>
              <?php elseif ($isWhatsappPending): ?>
                <div class="account-track-alert is-pending"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> WhatsApp confirmation is pending. Our team will update this order after confirmation.</div>
              <?php else: ?>
                <div class="account-track-steps">
                  <?php foreach ($trackSteps as $stepIndex => $step):
                    $stepClass = $stepIndex < $trackIndex ? 'is-done' : ($stepIndex === $trackIndex ? 'is-active' : 'is-pending');
                  ?>
                    <div class="account-track-step <?= $stepClass ?>">
                      <span><?= $stepIndex < $trackIndex ? '✓' : ($stepIndex + 1) ?></span>
                      <strong><?= $h($statusLabels[$step] ?? ucfirst($step)) ?></strong>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </details>
      <?php endforeach; ?>
    </div>
    <?php
};
?>
<main class="account-page" data-design-target="account.page">
  <section class="account-hero" aria-labelledby="accountTitle">
    <div class="container account-hero-grid">
      <div class="account-title-block">
        <nav class="account-breadcrumb" aria-label="Breadcrumb">
          <a href="/">Home</a><span>›</span><span>My Account</span>
        </nav>
        <h1 id="accountTitle">My Account</h1>
        <p>Manage your profile, track orders and access exclusive print benefits.</p>
      </div>
      <a class="account-promo-card" href="/categories" aria-label="Order print products">
        <div>
          <strong>Design. Print. Grow.</strong>
          <span>Premium quality printing for your business success.</span>
          <em>Order Now</em>
        </div>
        <div class="account-promo-visual" aria-hidden="true">
          <span class="promo-sheet promo-sheet-one"></span>
          <span class="promo-sheet promo-sheet-two"></span>
          <span class="promo-box"></span>
        </div>
      </a>
    </div>
  </section>

  <section class="account-dashboard container" aria-label="Account dashboard">
    <aside class="account-sidebar" aria-label="My account menu">
      <button class="account-nav-item is-active" type="button" data-account-tab="dashboard"><i class="fa-solid fa-shapes"></i><span>Dashboard</span></button>
      <button class="account-nav-item" type="button" data-account-tab="orders"><i class="fa-regular fa-clipboard"></i><span>My Orders</span></button>
      <button class="account-nav-item" type="button" data-account-tab="designs"><i class="fa-regular fa-pen-to-square"></i><span>My Designs</span></button>
      <button class="account-nav-item" type="button" data-account-tab="addresses"><i class="fa-solid fa-location-dot"></i><span>Saved Addresses</span></button>
      <button class="account-nav-item" type="button" data-account-tab="details"><i class="fa-regular fa-user"></i><span>Account Details</span></button>
      <button class="account-nav-item" type="button" data-account-tab="security"><i class="fa-solid fa-lock"></i><span>Change Password</span></button>
      <a class="account-nav-item" href="/logout"><i class="fa-solid fa-arrow-right-from-bracket"></i><span>Logout</span></a>

    </aside>

    <div class="account-main">
      <section class="account-tab-panel is-active" data-account-panel="dashboard" aria-label="Account dashboard overview">
        <div class="account-stats-grid">
          <article class="account-stat-card stat-purple">
            <span class="account-stat-icon"><i class="fa-solid fa-bag-shopping"></i></span>
            <div><small>Total Orders</small><strong><?= number_format($totalOrders) ?></strong><button type="button" data-account-tab="orders">View Orders <i class="fa-solid fa-arrow-right"></i></button></div>
          </article>
          <article class="account-stat-card stat-orange">
            <span class="account-stat-icon"><i class="fa-regular fa-rectangle-list"></i></span>
            <div><small>Orders in Progress</small><strong><?= str_pad((string)$progressOrders, 2, '0', STR_PAD_LEFT) ?></strong><button type="button" data-account-tab="orders">Track Now <i class="fa-solid fa-arrow-right"></i></button></div>
          </article>
          <article class="account-stat-card stat-green">
            <span class="account-stat-icon"><i class="fa-solid fa-bag-shopping"></i></span>
            <div><small>Completed Orders</small><strong><?= str_pad((string)$completedOrders, 2, '0', STR_PAD_LEFT) ?></strong><button type="button" data-account-tab="orders">View History <i class="fa-solid fa-arrow-right"></i></button></div>
          </article>
          <article class="account-stat-card stat-wallet">
            <span class="account-stat-icon"><i class="fa-solid fa-location-dot"></i></span>
            <div><small>Saved Addresses</small><strong><?= str_pad((string)$savedAddressCount, 2, '0', STR_PAD_LEFT) ?></strong><button type="button" data-account-tab="addresses">Manage Address <i class="fa-solid fa-arrow-right"></i></button></div>
          </article>
        </div>

        <section class="account-card account-profile-card" aria-label="Profile summary">
          <div class="account-avatar-wrap">
            <div class="account-avatar" aria-hidden="true"><?= $h($initials) ?></div>
            <button type="button" data-account-tab="details" aria-label="Edit profile photo"><i class="fa-solid fa-camera"></i></button>
          </div>
          <div class="account-profile-copy">
            <h2><?= $h($name) ?></h2>
            <?php if ($company !== ''): ?><p><i class="fa-regular fa-building"></i><?= $h($company) ?></p><?php endif; ?>
            <p><i class="fa-regular fa-envelope"></i><?= $email !== '' ? $h($email) : 'Add email address' ?></p>
            <p><i class="fa-solid fa-phone"></i><?= $phone !== '' ? $h($phone) : 'Add phone number' ?></p>
            <p><i class="fa-solid fa-location-dot"></i><?= $h($location) ?></p>
          </div>
          <button class="account-edit-btn" type="button" data-account-tab="details">Edit Profile</button>
        </section>

        <section class="account-card account-orders-card" aria-labelledby="recentOrdersTitle">
          <div class="account-section-head">
            <h2 id="recentOrdersTitle">Recent Orders</h2>
            <button type="button" data-account-tab="orders">View All Orders <i class="fa-solid fa-arrow-right"></i></button>
          </div>
          <?php $renderOrders($recentOrders, true); ?>
        </section>

      </section>

      <section class="account-tab-panel" data-account-panel="orders" aria-labelledby="ordersPanelTitle">
        <section class="account-card account-orders-card">
          <div class="account-section-head">
            <div><h2 id="ordersPanelTitle">My Orders</h2><p>All your print orders and payment/status information in one place.</p></div>
            <a href="/categories">Place New Order <i class="fa-solid fa-arrow-right"></i></a>
          </div>
          <?php $renderOrders($orders, false); ?>
        </section>
      </section>

      <section class="account-tab-panel" data-account-panel="designs" aria-labelledby="designsPanelTitle">
        <section class="account-card account-designs-card">
          <div class="account-section-head">
            <div><h2 id="designsPanelTitle">My Designs</h2><p>Designs are based on your recent orders and uploaded artwork where available.</p></div>
            <a href="/categories">Upload New Design <i class="fa-solid fa-arrow-right"></i></a>
          </div>
          <div class="account-design-grid">
            <?php foreach ($recentDesigns as $idx => $design): ?>
            <article class="account-design-card">
              <div class="account-design-thumb design-thumb-<?= ($idx % 4) + 1 ?>">
                <span></span><b></b><em></em>
                <button type="button" aria-label="Design options"><i class="fa-solid fa-ellipsis-vertical"></i></button>
              </div>
              <strong><?= $h($design['name']) ?></strong>
              <small>Updated on <?= date('d M, Y', strtotime((string)$design['date'])) ?></small>
            </article>
            <?php endforeach; ?>
          </div>
        </section>
      </section>

      <section class="account-tab-panel" data-account-panel="addresses" aria-labelledby="addressesPanelTitle">
        <section class="account-card account-form-card">
          <div class="account-section-head"><div><h2 id="addressesPanelTitle">Saved Addresses</h2><p>Manage default delivery and billing addresses used during checkout.</p></div></div>
          <div class="account-form-block">
            <h3><i class="fa-solid fa-location-dot"></i> Default Delivery Address</h3>
            <div class="fg"><label>Address Line 1</label><input id="ps-add1" class="fi" value="<?= $h($shipping['address_line1'] ?? '') ?>"></div>
            <div class="fg"><label>Address Line 2</label><input id="ps-add2" class="fi" value="<?= $h($shipping['address_line2'] ?? '') ?>"></div>
            <div class="f2">
              <div class="fg"><label>City</label><input id="ps-city" class="fi" value="<?= $h($shipping['city'] ?? '') ?>"></div>
              <div class="fg"><label>State</label><input id="ps-state" class="fi" value="<?= $h($shipping['state'] ?? '') ?>"></div>
            </div>
            <div class="fg" style="margin-bottom:0"><label>Pincode</label><input id="ps-pin" class="fi" value="<?= $h($shipping['pincode'] ?? '') ?>"></div>
          </div>
          <div class="account-form-block">
            <h3><i class="fa-regular fa-file-lines"></i> Default Billing Details (GST Invoice)</h3>
            <?php if (!empty($profile['migration_required'])): ?>
            <div class="account-warning">Billing fields are not available yet. Please run the SQL migration shared in the implementation notes.</div>
            <?php endif; ?>
            <div class="fg"><label>Legal Business Name</label><input id="pb-legal" class="fi" value="<?= $h($billing['legal_name'] ?? '') ?>" placeholder="ABC Pvt Ltd"></div>
            <div class="fg"><label>GSTIN</label><input id="pb-gst" class="fi" value="<?= $h($billing['gst_no'] ?? '') ?>" placeholder="24ABCDE1234F1Z5" style="text-transform:uppercase" oninput="this.value=this.value.toUpperCase()"></div>
            <div class="fg"><label>Billing Address Line 1</label><input id="pb-add1" class="fi" value="<?= $h($billing['address_line1'] ?? '') ?>"></div>
            <div class="fg"><label>Billing Address Line 2</label><input id="pb-add2" class="fi" value="<?= $h($billing['address_line2'] ?? '') ?>"></div>
            <div class="f2">
              <div class="fg"><label>City</label><input id="pb-city" class="fi" value="<?= $h($billing['city'] ?? '') ?>"></div>
              <div class="fg"><label>State</label><input id="pb-state" class="fi" value="<?= $h($billing['state'] ?? '') ?>"></div>
            </div>
            <div class="fg" style="margin-bottom:0"><label>Pincode</label><input id="pb-pin" class="fi" value="<?= $h($billing['pincode'] ?? '') ?>"></div>
          </div>
          <div class="account-form-actions"><button class="btn btn-blue" onclick="saveProfile()"><i class="fa-solid fa-floppy-disk"></i> Save Addresses</button></div>
        </section>
      </section>



      <section class="account-tab-panel" data-account-panel="details" aria-labelledby="detailsPanelTitle">
        <section class="account-card account-form-card">
          <div class="account-section-head"><div><h2 id="detailsPanelTitle">Account Details</h2><p>Update your name, email, phone and company details.</p></div></div>
          <div class="account-form-block">
            <h3><i class="fa-regular fa-user"></i> Basic Details</h3>
            <div class="fg"><label>Full Name *</label><input id="p-name" class="fi" value="<?= $h($name) ?>"></div>
            <div class="f2">
              <div class="fg"><label>Email *</label><input id="p-email" type="email" class="fi" value="<?= $h($email) ?>"></div>
              <div class="fg"><label>Phone *</label><input id="p-phone" type="tel" class="fi" value="<?= $h($phone) ?>"></div>
            </div>
            <div class="fg" style="margin-bottom:0"><label>Company (optional)</label><input id="p-company" class="fi" value="<?= $h($company) ?>"></div>
          </div>
          <div id="profErr" class="account-alert is-error" style="display:none"></div>
          <div id="profOk" class="account-alert is-ok" style="display:none"></div>
          <div class="account-form-actions">
            <button class="btn btn-blue" onclick="saveProfile()"><i class="fa-solid fa-floppy-disk"></i> Save Profile</button>
            <button type="button" class="btn btn-outline" data-account-tab="security"><i class="fa-solid fa-lock"></i> Password Settings</button>
          </div>
        </section>
      </section>

      <section class="account-tab-panel" data-account-panel="security" aria-labelledby="securityPanelTitle">
        <section class="account-card account-form-card">
          <div class="account-section-head"><div><h2 id="securityPanelTitle">Change Password</h2><p>Set a new password without leaving My Account.</p></div></div>
          <div class="account-form-block">
            <h3><i class="fa-solid fa-lock"></i> Security</h3>
            <p class="account-muted">Existing passwords are stored securely in hashed form and cannot be shown in plain text.</p>
            <div class="fg"><label>Current Password *</label><div class="account-pass-wrap"><input id="pw-current" type="password" class="fi" placeholder="Enter current password"><button type="button" onclick="togglePassField('pw-current', this)">👁️</button></div></div>
            <div class="fg"><label>New Password *</label><div class="account-pass-wrap"><input id="pw-new" type="password" class="fi" placeholder="Minimum 6 characters"><button type="button" onclick="togglePassField('pw-new', this)">👁️</button></div></div>
            <div class="fg"><label>Confirm New Password *</label><div class="account-pass-wrap"><input id="pw-confirm" type="password" class="fi" placeholder="Retype new password"><button type="button" onclick="togglePassField('pw-confirm', this)">👁️</button></div></div>
            <div id="pwErr" class="account-alert is-error" style="display:none"></div>
            <div id="pwOk" class="account-alert is-ok" style="display:none"></div>
            <div class="account-form-actions"><button class="btn btn-blue" type="button" onclick="changePassword()">Update Password</button></div>
          </div>
        </section>
      </section>


    </div>
  </section>

  <section class="why-print-section account-why-section" aria-labelledby="accountWhyTitle" data-reveal>
    <div class="why-print-container">
      <h2 class="why-print-heading" id="accountWhyTitle">Why Choose <span>RCS PRINT?</span></h2>
      <div class="why-print-panel" aria-label="Why choose RCS Print">
        <article class="why-print-item">
          <div class="why-print-icon why-print-purple"><i class="fa-solid fa-truck-fast" aria-hidden="true"></i></div>
          <div class="why-print-copy"><h3>Fast Delivery</h3><p>On-time delivery always guaranteed.</p></div>
        </article>
        <article class="why-print-item">
          <div class="why-print-icon why-print-orange"><i class="fa-solid fa-pen-ruler" aria-hidden="true"></i></div>
          <div class="why-print-copy"><h3>Free Design Support</h3><p>Professional design support at no extra cost.</p></div>
        </article>
        <article class="why-print-item">
          <div class="why-print-icon why-print-green"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></div>
          <div class="why-print-copy"><h3>Premium Quality</h3><p>Best quality materials and printing.</p></div>
        </article>
        <article class="why-print-item">
          <div class="why-print-icon why-print-purple"><i class="fa-solid fa-tags" aria-hidden="true"></i></div>
          <div class="why-print-copy"><h3>Affordable Pricing</h3><p>Low price with the best value.</p></div>
        </article>
        <article class="why-print-item">
          <div class="why-print-icon why-print-orange"><i class="fa-solid fa-cube" aria-hidden="true"></i></div>
          <div class="why-print-copy"><h3>Bulk Order Specialist</h3><p>Special prices for bulk requirements.</p></div>
        </article>
      </div>
    </div>
  </section>

  <section class="quick-help-section account-quick-help-section" aria-label="Quick help and bulk order actions" data-reveal>
    <div class="quick-help-container">
      <div class="quick-help-bar">
        <a class="quick-help-item quick-help-call" href="tel:<?= $h($accountBizPhoneHref) ?>">
          <span class="quick-help-icon"><i class="fa-solid fa-phone-volume" aria-hidden="true"></i></span>
          <span class="quick-help-copy"><span>Need Help? Call Us</span><strong><?= $accountBizPhone ?></strong></span>
        </a>
        <button class="quick-help-item quick-help-whatsapp" type="button" onclick="window.open('https://wa.me/<?= $h($accountBizWa) ?>','_blank')">
          <span class="quick-help-icon"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></span>
          <span class="quick-help-copy"><strong>Chat with us on WhatsApp</strong><span>We are here to help!</span></span>
        </button>
        <a class="quick-help-item quick-help-download" href="/categories" aria-label="Download our brochure for all products">
          <span class="quick-help-icon"><i class="fa-solid fa-download" aria-hidden="true"></i></span>
          <span class="quick-help-copy"><strong>Download Our Brochure</strong><span>For All Products</span></span>
        </a>
      </div>
    </div>
  </section>
</main>

<script>
const ACCOUNT_TABS = ['dashboard','orders','designs','addresses','details','security'];

function setAccountTab(tab, pushHash = true, scrollToPanel = true) {
  const safeTab = ACCOUNT_TABS.includes(tab) ? tab : 'dashboard';
  document.querySelectorAll('[data-account-tab]').forEach(el => {
    const active = el.dataset.accountTab === safeTab;
    el.classList.toggle('is-active', active && el.classList.contains('account-nav-item'));
    if (el.classList.contains('account-nav-item')) el.setAttribute('aria-current', active ? 'page' : 'false');
  });
  document.querySelectorAll('[data-account-panel]').forEach(panel => {
    const active = panel.dataset.accountPanel === safeTab;
    panel.classList.toggle('is-active', active);
    panel.toggleAttribute('hidden', !active);
  });
  if (pushHash) history.replaceState(null, '', safeTab === 'dashboard' ? '/profile' : `/profile#${safeTab}`);
  if (scrollToPanel) document.querySelector('.account-main')?.scrollIntoView({behavior:'smooth', block:'start'});
}

document.querySelectorAll('[data-account-tab]').forEach(el => {
  el.addEventListener('click', event => {
    const tab = el.dataset.accountTab;
    if (!tab) return;
    event.preventDefault();
    setAccountTab(tab);
  });
});

window.addEventListener('hashchange', () => setAccountTab(location.hash.replace('#', ''), false));
setAccountTab(location.hash.replace('#', ''), false, false);


function openAccountOrder(trigger) {
  const detail = trigger?.closest('.account-order-detail');
  if (!detail) return;
  detail.open = true;
  const tracking = detail.querySelector('.account-order-tracking');
  const target = tracking || detail;
  tracking?.classList.remove('is-highlighted');
  target.scrollIntoView({behavior:'smooth', block:'center'});
  if (tracking) {
    window.setTimeout(() => tracking.classList.add('is-highlighted'), 220);
    window.setTimeout(() => tracking.classList.remove('is-highlighted'), 1800);
  }
}

async function saveProfile() {
  const err = document.getElementById('profErr');
  const ok = document.getElementById('profOk');
  if (err) err.style.display = 'none';
  if (ok) ok.style.display = 'none';

  const payload = {
    name: document.getElementById('p-name')?.value.trim() || '',
    email: document.getElementById('p-email')?.value.trim() || '',
    phone: document.getElementById('p-phone')?.value.trim() || '',
    company: document.getElementById('p-company')?.value.trim() || '',
    shipping: {
      address_line1: document.getElementById('ps-add1')?.value.trim() || '',
      address_line2: document.getElementById('ps-add2')?.value.trim() || '',
      city: document.getElementById('ps-city')?.value.trim() || '',
      state: document.getElementById('ps-state')?.value.trim() || '',
      pincode: document.getElementById('ps-pin')?.value.trim() || '',
    },
    billing: {
      legal_name: document.getElementById('pb-legal')?.value.trim() || '',
      gst_no: (document.getElementById('pb-gst')?.value || '').trim().toUpperCase(),
      address_line1: document.getElementById('pb-add1')?.value.trim() || '',
      address_line2: document.getElementById('pb-add2')?.value.trim() || '',
      city: document.getElementById('pb-city')?.value.trim() || '',
      state: document.getElementById('pb-state')?.value.trim() || '',
      pincode: document.getElementById('pb-pin')?.value.trim() || '',
    }
  };

  if (!payload.name || !payload.email || !payload.phone) {
    setAccountTab('details');
    const target = document.getElementById('profErr');
    target.textContent = 'Name, email and phone are required.';
    target.style.display = 'block';
    return;
  }

  try {
    const resp = await fetch('/api/profile', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': APP.csrfToken },
      credentials: 'same-origin',
      body: JSON.stringify(payload),
    });
    const data = await resp.json();
    if (!data.ok) {
      setAccountTab('details');
      const target = document.getElementById('profErr');
      target.textContent = data.msg || 'Could not update profile.';
      target.style.display = 'block';
      return;
    }
    const target = document.getElementById('profOk');
    target.textContent = data.migration_required
      ? 'Basic profile updated. Billing fields will work after DB migration is applied.'
      : 'Profile updated successfully.';
    setAccountTab('details');
    target.style.display = 'block';
  } catch (e) {
    setAccountTab('details');
    const target = document.getElementById('profErr');
    target.textContent = 'Could not update profile right now.';
    target.style.display = 'block';
  }
}

async function changePassword() {
  const err = document.getElementById('pwErr');
  const ok = document.getElementById('pwOk');
  err.style.display = 'none';
  ok.style.display = 'none';

  const current_password = document.getElementById('pw-current')?.value || '';
  const new_password = document.getElementById('pw-new')?.value || '';
  const confirm_password = document.getElementById('pw-confirm')?.value || '';

  if (!current_password || !new_password || !confirm_password) {
    err.textContent = 'Please fill all password fields.';
    err.style.display = 'block';
    return;
  }
  if (new_password !== confirm_password) {
    err.textContent = 'New password and confirm password must match.';
    err.style.display = 'block';
    return;
  }

  try {
    const resp = await fetch('/api/profile/password', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': APP.csrfToken },
      credentials: 'same-origin',
      body: JSON.stringify({ current_password, new_password, confirm_password }),
    });
    const data = await resp.json();
    if (!data.ok) {
      err.textContent = data.msg || 'Could not update password.';
      err.style.display = 'block';
      return;
    }
    ['pw-current','pw-new','pw-confirm'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
    ok.textContent = 'Password updated successfully.';
    ok.style.display = 'block';
  } catch (e) {
    err.textContent = 'Could not update password right now.';
    err.style.display = 'block';
  }
}

function togglePassField(id, btn) {
  const el = document.getElementById(id);
  if (!el) return;
  const show = el.type === 'password';
  el.type = show ? 'text' : 'password';
  btn.textContent = show ? '🙈' : '👁️';
}
</script>
<?php include INCLUDE_PATH . '/partials/site-footer.php'; ?>
<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
