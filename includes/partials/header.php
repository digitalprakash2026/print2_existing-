<?php
/**
 * header.php — Site header, mobile drawer, cart drawer, global JS config
 * NOTE: Does NOT output <!DOCTYPE> / <html> / <head> — head.php does that.
 * Cart-drawer is included here once; templates must NOT re-include it.
 */

// ── Navigation data ──────────────────────────────────────────
try {
    $navCategories = \Catalog\ProductCatalog::categories();
    $navProducts   = \Catalog\ProductCatalog::all();
    $bizSettings   = [];
    $settingRows   = Database::rows(
        "SELECT `key`, value FROM settings
         WHERE `key` IN ('biz_name','biz_tagline','biz_whatsapp','biz_phone','razorpay_key_id','gst_percent','design_fee')"
    );
    foreach ($settingRows as $r) $bizSettings[$r['key']] = $r['value'];
} catch (\Throwable) {
    $navCategories = []; $navProducts = []; $bizSettings = [];
}

$navBizName  = htmlspecialchars($bizSettings['biz_name']         ?? 'RCS Graphic');
$navWa       = htmlspecialchars($bizSettings['biz_whatsapp']     ?? '919876543210');
$navRazKey   = htmlspecialchars($bizSettings['razorpay_key_id']  ?? '');
$navGst      = (int)($bizSettings['gst_percent'] ?? 18);
$navDesignFee= (float)($bizSettings['design_fee'] ?? 0);
$catIcons    = ['Cards'=>'💳','Brochures'=>'📋','Flyers'=>'📄','Pamphlets'=>'📰','Stationery'=>'📝','Banners'=>'🏳️','Posters'=>'🖼️'];
$currentUri  = $uri ?? '/';
?>

<!-- ── Overlays (toast, payment, cart backdrop) ────────────── -->
<div class="toast-wrap" id="tw"></div>
<div class="pay-ov" id="payOv">
  <div class="pay-spin"></div>
  <div class="pay-txt" id="payTxt">Processing…</div>
  <div class="pay-sub">Please don't close this window</div>
</div>
<div class="cart-backdrop" id="cartBack" onclick="closeCart()"></div>

<!-- ═══════════════════════════════════════════════
     SITE HEADER
══════════════════════════════════════════════════ -->
<header class="site-header" id="siteHeader">

  <!-- Top announcement bar -->
  <div class="top-bar hdr-topbar">
    <div class="header-container top-bar-inner hdr-topbar-inner">
      <div class="top-bar-left hdr-topbar-left">
        <span class="hdr-delivery-ic" aria-hidden="true">🚚</span>
        <span>Free Delivery in Rajkot on All Orders Above ₹999</span>
      </div>
      <div class="top-bar-right hdr-topbar-right" aria-label="Header quick links">
        <a href="/my-orders" class="hdr-top-link">Track Order</a>
        <span class="hdr-top-sep" aria-hidden="true">|</span>
        <a href="/#contact-sec" class="hdr-top-link">Help Center</a>
        <span class="hdr-top-sep" aria-hidden="true">|</span>
        <a href="/#contact-sec" class="hdr-social" aria-label="Facebook">f</a>
        <a href="/#contact-sec" class="hdr-social" aria-label="Instagram">◎</a>
        <a href="https://wa.me/<?= $navWa ?>" class="hdr-social" aria-label="WhatsApp" target="_blank" rel="noopener">◔</a>
      </div>
    </div>
  </div>

  <!-- Main white navigation row -->
  <div class="main-header hdr-mainbar">
    <div class="header-container main-header-inner hdr-mainbar-inner">
      <!-- Logo -->
      <a href="/" class="logo hdr-logo">
        <img src="/assets/images/rcs-graphic-logo.png"
             alt="<?= $navBizName ?> Logo"
             class="hdr-logo-img"
             loading="eager"
             decoding="async">
      </a>

      <!-- Desktop Nav -->
      <nav class="main-nav hdr-nav" id="desktopNav">
        <a href="/" class="hn <?= $currentUri === '/' ? 'act' : '' ?>">Home</a>

        <!-- Products dropdown — hover bridge prevents flicker -->
        <div class="dd-wrap" id="ddWrap">
          <button class="dd-btn" id="ddBtn" aria-expanded="false" aria-haspopup="true">
            Products
            <svg viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>
          </button>
          <!-- Invisible bridge fills gap between button and panel -->
          <div class="dd-bridge"></div>
          <div class="dd-panel" id="ddPanel" role="menu">
            <?php if (!empty($navCategories)): ?>
              <div class="dd-cat-lbl">Browse by Category</div>
              <?php foreach ($navCategories as $cat): ?>
              <a href="/category/<?= htmlspecialchars($cat['slug']) ?>" class="dd-item" role="menuitem">
                <div class="dd-item-ic"><?= htmlspecialchars($cat['icon'] ?? '🖨️') ?></div>
                <span><?= htmlspecialchars($cat['name']) ?></span>
                <?php if ((int)($cat['product_count'] ?? 0) > 0): ?>
                <span style="margin-left:auto;font-size:10px;color:var(--text3);font-weight:600">
                  <?= (int)$cat['product_count'] ?>
                </span>
                <?php endif; ?>
              </a>
              <?php endforeach; ?>
              <div class="dd-divider"></div>
            <?php endif; ?>
            <?php if (!empty($navProducts)): ?>
              <div class="dd-cat-lbl">Products</div>
              <?php foreach (array_slice($navProducts, 0, 6) as $p): ?>
              <a href="/product/<?= htmlspecialchars($p['slug']) ?>" class="dd-item" role="menuitem">
                <div class="dd-item-ic"><?= $catIcons[$p['category_name']] ?? '🖨️' ?></div>
                <?= htmlspecialchars($p['name']) ?>
              </a>
              <?php endforeach; ?>
              <div class="dd-divider"></div>
            <?php endif; ?>
            <a href="/products" class="dd-item dd-item-all" role="menuitem">
              <div class="dd-item-ic">→</div>
              See All Products
            </a>
          </div>
        </div>

        <a href="/#why-sec" class="hn">About Us</a>
        <a href="/#contact-sec" class="hn">Contact Us</a>
        <a href="/products" class="hn">Blog</a>
        <a href="<?= ($user ?? null) ? '/profile' : '/login' ?>" class="hn">My Account</a>
      </nav>

      <!-- Right actions -->
      <div class="header-actions hdr-right">
        <a href="/products" class="hdr-icon-btn hdr-search-btn" title="Search products" aria-label="Search products">
          <svg viewBox="0 0 24 24"><path d="M9.5 3a6.5 6.5 0 0 1 5.16 10.45l4.44 4.45-1.4 1.4-4.45-4.44A6.5 6.5 0 1 1 9.5 3zm0 2a4.5 4.5 0 1 0 0 9 4.5 4.5 0 0 0 0-9z"/></svg>
        </a>

        <?php if ($user ?? null): ?>
          <a href="/profile" class="hdr-icon-btn hdr-account-btn" title="My account" aria-label="My account">
            <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
          </a>
        <?php else: ?>
          <a href="/login" class="hdr-icon-btn hdr-account-btn" title="Login" aria-label="Login">
            <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
          </a>
        <?php endif; ?>

        <!-- Cart -->
        <button class="cart-btn" onclick="toggleCart()" title="Shopping Cart" aria-label="Open cart">
          <svg viewBox="0 0 24 24"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96C5 16.1 6.9 18 9 18h12v-2H9.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63H19c.75 0 1.41-.41 1.75-1.03l3.58-6.49A1 1 0 0 0 23.46 5H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
          <span class="cart-count cart-badge" id="cartCount">2</span>
        </button>

        <!-- Hamburger — fixed: calls toggleDrawer() which exists in app.js -->
        <button class="mobile-menu-toggle ham" onclick="toggleDrawer()" id="hamBtn" aria-label="Open menu" aria-expanded="false">
          <svg viewBox="0 0 24 24"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
        </button>
      </div>
    </div>
  </div>

</header>

<!-- ═══════════════════════════════════════════════
     MOBILE DRAWER
══════════════════════════════════════════════════ -->
<div class="mob-drawer" id="mobDrawer">
  <div class="mob-drawer-inner">
    <a href="/"            class="md-item md-home">🏠 Home</a>

    <!-- Products accordion -->
    <div class="md-item md-acc" onclick="toggleMobProds()" id="mobProdToggle">
      <span>📦 Products</span>
      <svg class="md-acc-arrow" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>
    </div>
    <div id="mobProdList" class="md-sub-list" style="display:none">
      <?php foreach ($navCategories as $cat): ?>
      <a href="/category/<?= htmlspecialchars($cat['slug']) ?>" class="md-item md-sub"
         onclick="closeDrawer()" style="font-weight:600;color:var(--blue)">
        <?= htmlspecialchars($cat['icon'] ?? '') ?> <?= htmlspecialchars($cat['name']) ?>
      </a>
      <?php endforeach; ?>
      <?php foreach ($navProducts as $p): ?>
      <a href="/product/<?= htmlspecialchars($p['slug']) ?>" class="md-item md-sub"
         onclick="closeDrawer()">
        <?= htmlspecialchars($p['name']) ?>
      </a>
      <?php endforeach; ?>
      <a href="/products" class="md-item md-sub" onclick="closeDrawer()"
         style="font-weight:700;color:var(--blue)">
        → See All Products
      </a>
    </div>

    <a href="/#why-sec"     class="md-item" onclick="closeDrawer()">⭐ About Us</a>
    <a href="/#contact-sec" class="md-item" onclick="closeDrawer()">📞 Contact Us</a>
    <a href="/products"      class="md-item" onclick="closeDrawer()">📝 Blog</a>
    <a href="/products"      class="md-item" onclick="closeDrawer()">🔎 Search Products</a>
    <button type="button" class="md-item md-action" onclick="toggleCart();closeDrawer()">
      🛒 Cart <span class="md-cart-badge">2</span>
    </button>

    <?php if ($user ?? null): ?>
      <a href="/my-orders" class="md-item">📋 My Orders</a>
      <a href="/profile" class="md-item">👤 My Profile</a>
      <a href="/logout"    class="md-item">👤 <?= htmlspecialchars($user['name']) ?> (Logout)</a>
    <?php else: ?>
      <a href="/register"  class="md-item md-start" onclick="closeDrawer()">✨ New Customer? Start Here</a>
      <a href="/login"     class="md-item">👤 Login / Register</a>
    <?php endif; ?>

    <a href="/admin" class="md-item">🔐 Admin Panel</a>

    <!-- WhatsApp quick action in drawer -->
    <div style="padding:14px 18px;border-top:1px solid var(--border);margin-top:4px">
      <button onclick="window.open('https://wa.me/<?= $navWa ?>','_blank');closeDrawer()"
              class="btn btn-green btn-full" style="border-radius:10px">
        💬 WhatsApp Us
      </button>
    </div>
  </div>
</div>
<!-- Drawer backdrop -->
<div class="mob-backdrop" id="mobBack" onclick="closeDrawer()"></div>

<!-- ═══════════════════════════════════════════════
     CART DRAWER (included once here only)
══════════════════════════════════════════════════ -->
<?php include __DIR__ . '/cart-drawer.php'; ?>

<!-- ── Global JS Config ──────────────────────────────────────── -->
<script>
/* Global app config — available to all page scripts */
const APP = {
  csrfToken:  '<?= htmlspecialchars($csrf ?? '') ?>',
  razorpayKey:'<?= $navRazKey ?>',
  whatsapp:   '<?= $navWa ?>',
  gstPercent: <?= $navGst ?>,
  designFee:  <?= $navDesignFee ?>,   // RCS design charge from admin settings
  user:       <?= json_encode($user ?? null) ?>,
  apiBase:    ''
};
</script>
<script src="/assets/js/app.js"></script>
