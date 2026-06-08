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
$helpPhone = $phone !== '' ? $phone : '+91 98765 43210';
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
      <a class="account-nav-item is-active" href="/profile"><i class="fa-solid fa-shapes"></i><span>Dashboard</span></a>
      <a class="account-nav-item" href="/my-orders"><i class="fa-regular fa-clipboard"></i><span>My Orders</span></a>
      <a class="account-nav-item" href="#myDesigns"><i class="fa-regular fa-pen-to-square"></i><span>My Designs</span></a>
      <a class="account-nav-item" href="#savedAddresses"><i class="fa-solid fa-location-dot"></i><span>Saved Addresses</span></a>
      <a class="account-nav-item" href="#wishlist"><i class="fa-regular fa-heart"></i><span>My Wishlist</span></a>
      <a class="account-nav-item" href="#wallet"><i class="fa-regular fa-wallet"></i><span>My Wallet</span></a>
      <a class="account-nav-item" href="#accountDetails"><i class="fa-regular fa-user"></i><span>Account Details</span></a>
      <a class="account-nav-item" href="/profile/security"><i class="fa-solid fa-lock"></i><span>Change Password</span></a>
      <a class="account-nav-item" href="#notifications"><i class="fa-regular fa-bell"></i><span>Notifications</span></a>
      <a class="account-nav-item" href="/contact"><i class="fa-regular fa-handshake"></i><span>Refer &amp; Earn</span></a>
      <a class="account-nav-item" href="/logout"><i class="fa-solid fa-arrow-right-from-bracket"></i><span>Logout</span></a>

      <div class="account-help-card">
        <strong>Need Help?</strong>
        <span>We are here to help you!</span>
        <a href="tel:<?= $h(preg_replace('/\D+/', '', $helpPhone)) ?>"><i class="fa-solid fa-phone"></i><?= $h($helpPhone) ?></a>
        <small>Mon - Sat: 10:00 AM - 7:00 PM</small>
      </div>
    </aside>

    <div class="account-main">
      <div class="account-stats-grid" id="wallet">
        <article class="account-stat-card stat-purple">
          <span class="account-stat-icon"><i class="fa-solid fa-bag-shopping"></i></span>
          <div><small>Total Orders</small><strong><?= number_format($totalOrders) ?></strong><a href="/my-orders">View Orders <i class="fa-solid fa-arrow-right"></i></a></div>
        </article>
        <article class="account-stat-card stat-orange">
          <span class="account-stat-icon"><i class="fa-regular fa-rectangle-list"></i></span>
          <div><small>Orders in Progress</small><strong><?= str_pad((string)$progressOrders, 2, '0', STR_PAD_LEFT) ?></strong><a href="/my-orders">Track Now <i class="fa-solid fa-arrow-right"></i></a></div>
        </article>
        <article class="account-stat-card stat-green">
          <span class="account-stat-icon"><i class="fa-solid fa-bag-shopping"></i></span>
          <div><small>Completed Orders</small><strong><?= str_pad((string)$completedOrders, 2, '0', STR_PAD_LEFT) ?></strong><a href="/my-orders">View History <i class="fa-solid fa-arrow-right"></i></a></div>
        </article>
        <article class="account-stat-card stat-wallet">
          <span class="account-stat-icon"><i class="fa-regular fa-wallet"></i></span>
          <div><small>Wallet Balance</small><strong>₹0.00</strong><a href="/contact">Add Money <i class="fa-solid fa-arrow-right"></i></a></div>
        </article>
      </div>

      <section class="account-card account-profile-card" aria-label="Profile summary">
        <div class="account-avatar-wrap">
          <div class="account-avatar" aria-hidden="true"><?= $h($initials) ?></div>
          <button type="button" onclick="document.getElementById('p-name')?.focus()" aria-label="Edit profile photo"><i class="fa-solid fa-camera"></i></button>
        </div>
        <div class="account-profile-copy">
          <h2><?= $h($name) ?></h2>
          <?php if ($company !== ''): ?><p><i class="fa-regular fa-building"></i><?= $h($company) ?></p><?php endif; ?>
          <p><i class="fa-regular fa-envelope"></i><?= $email !== '' ? $h($email) : 'Add email address' ?></p>
          <p><i class="fa-solid fa-phone"></i><?= $phone !== '' ? $h($phone) : 'Add phone number' ?></p>
          <p><i class="fa-solid fa-location-dot"></i><?= $h($location) ?></p>
        </div>
        <a class="account-edit-btn" href="#accountDetails">Edit Profile</a>
      </section>

      <section class="account-card account-orders-card" aria-labelledby="recentOrdersTitle">
        <div class="account-section-head">
          <h2 id="recentOrdersTitle">Recent Orders</h2>
          <a href="/my-orders">View All Orders <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <?php if (empty($recentOrders)): ?>
          <div class="account-empty-state">
            <i class="fa-solid fa-box-open"></i>
            <strong>No orders yet</strong>
            <span>Your recent print orders will appear here.</span>
            <a href="/categories" class="btn btn-blue btn-sm">Browse Products</a>
          </div>
        <?php else: ?>
          <div class="account-order-table" role="table" aria-label="Recent orders">
            <div class="account-order-row account-order-head" role="row">
              <span>Order ID</span><span>Date</span><span>Products</span><span>Amount</span><span>Status</span><span>Action</span>
            </div>
            <?php foreach ($recentOrders as $order):
              $items = $order['items'] ?? [];
              $status = (string)($order['status'] ?? 'received');
              $statusClass = preg_replace('/[^a-z0-9_-]/i', '', $status);
            ?>
              <div class="account-order-row" role="row">
                <strong>#<?= $h($order['order_id'] ?? $order['id'] ?? '') ?></strong>
                <span><?= !empty($order['created_at']) ? date('d M, Y', strtotime((string)$order['created_at'])) : '—' ?></span>
                <span class="account-product-mini" title="<?= $h(implode(', ', array_filter(array_column($items, 'product_name')))) ?>">
                  <?php foreach (array_slice($items, 0, 3) as $idx => $item): ?>
                    <i style="--mini:<?= (int)$idx ?>"><?= strtoupper(substr((string)($item['product_name'] ?? 'P'), 0, 1)) ?></i>
                  <?php endforeach; ?>
                  <?php if (count($items) > 3): ?><em>+<?= count($items) - 3 ?></em><?php endif; ?>
                </span>
                <b>₹<?= number_format((float)($order['total_amount'] ?? 0)) ?></b>
                <span class="account-status status-<?= $h($statusClass) ?>"><?= $h($statusLabels[$status] ?? ucfirst($status)) ?></span>
                <a class="account-mini-btn" href="/my-orders">View Details</a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <section class="account-card account-designs-card" id="myDesigns" aria-labelledby="myDesignsTitle">
        <div class="account-section-head">
          <h2 id="myDesignsTitle">My Designs</h2>
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

      <section class="account-card account-actions-card" aria-labelledby="quickActionsTitle">
        <h2 id="quickActionsTitle">Quick Actions</h2>
        <div class="account-action-grid">
          <a href="/categories"><i class="fa-solid fa-repeat"></i><strong>Reorder</strong><span>Quickly</span></a>
          <a href="/categories"><i class="fa-solid fa-cloud-arrow-up"></i><strong>Upload</strong><span>New Design</span></a>
          <a href="/my-orders"><i class="fa-regular fa-file-lines"></i><strong>Download</strong><span>Invoice</span></a>
          <a href="/my-orders"><i class="fa-solid fa-truck-fast"></i><strong>Track</strong><span>Order</span></a>
          <a href="/contact"><i class="fa-solid fa-gift"></i><strong>Refer &amp;</strong><span>Earn</span></a>
          <a href="/contact"><i class="fa-solid fa-headset"></i><strong>Help</strong><span>Center</span></a>
        </div>
      </section>

      <section class="account-card account-form-card" id="accountDetails" aria-labelledby="accountDetailsTitle">
        <div class="account-section-head">
          <div>
            <h2 id="accountDetailsTitle">Account Details</h2>
            <p>Update your profile, saved address and default billing details.</p>
          </div>
          <a href="/profile/security">Change Password</a>
        </div>

        <div class="account-form-block">
          <h3><i class="fa-regular fa-user"></i> Basic Details</h3>
          <div class="fg"><label>Full Name *</label><input id="p-name" class="fi" value="<?= $h($name) ?>"></div>
          <div class="f2">
            <div class="fg"><label>Email *</label><input id="p-email" type="email" class="fi" value="<?= $h($email) ?>"></div>
            <div class="fg"><label>Phone *</label><input id="p-phone" type="tel" class="fi" value="<?= $h($phone) ?>"></div>
          </div>
          <div class="fg" style="margin-bottom:0"><label>Company (optional)</label><input id="p-company" class="fi" value="<?= $h($company) ?>"></div>
        </div>

        <div class="account-form-block" id="savedAddresses">
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

        <div id="profErr" class="account-alert is-error" style="display:none"></div>
        <div id="profOk" class="account-alert is-ok" style="display:none"></div>
        <div class="account-form-actions">
          <button class="btn btn-blue" onclick="saveProfile()"><i class="fa-solid fa-floppy-disk"></i> Save Profile</button>
          <a href="/profile/security" class="btn btn-outline"><i class="fa-solid fa-lock"></i> Password Settings</a>
        </div>
      </section>
    </div>
  </section>

  <section class="account-benefits container" id="wishlist" aria-label="Account benefits">
    <div><i class="fa-solid fa-crown"></i><strong>Premium Quality</strong><span>Best quality materials and printing.</span></div>
    <div><i class="fa-solid fa-bag-shopping"></i><strong>Affordable Pricing</strong><span>Low price with the best value.</span></div>
    <div><i class="fa-solid fa-truck-fast"></i><strong>Fast Delivery</strong><span>On-time delivery with guarantees.</span></div>
    <div><i class="fa-solid fa-tags"></i><strong>Bulk Order Specialist</strong><span>Special prices for bulk requirements.</span></div>
    <div><i class="fa-solid fa-cube"></i><strong>Design Support</strong><span>Professional artwork guidance.</span></div>
  </section>

  <section class="account-contact-strip container" id="notifications" aria-label="Contact support">
    <div><strong>Have Questions?</strong><span>We're here to help!</span></div>
    <a href="tel:<?= $h(preg_replace('/\D+/', '', $helpPhone)) ?>"><i class="fa-solid fa-phone-volume"></i><strong><?= $h($helpPhone) ?></strong><span>Mon - Sat: 10:00 AM - 7:00 PM</span></a>
    <a href="https://wa.me/<?= $h(preg_replace('/\D+/', '', $helpPhone)) ?>" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i><strong>Chat with us on WhatsApp</strong><span>We are here to help!</span></a>
    <a class="account-download" href="/contact"><i class="fa-solid fa-download"></i><strong>Download Brochure</strong><span>For Bulk Orders</span></a>
  </section>
</main>

<script>
async function saveProfile() {
  const err = document.getElementById('profErr');
  const ok = document.getElementById('profOk');
  err.style.display = 'none';
  ok.style.display = 'none';

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
    err.textContent = 'Name, email and phone are required.';
    err.style.display = 'block';
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
      err.textContent = data.msg || 'Could not update profile.';
      err.style.display = 'block';
      return;
    }
    ok.textContent = data.migration_required
      ? 'Basic profile updated. Billing fields will work after DB migration is applied.'
      : 'Profile updated successfully.';
    ok.style.display = 'block';
  } catch (e) {
    err.textContent = 'Could not update profile right now.';
    err.style.display = 'block';
  }
}
</script>
<?php include INCLUDE_PATH . '/partials/site-footer.php'; ?>
<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
