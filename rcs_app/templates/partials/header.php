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
<header class="header" id="siteHeader">

  <!-- Logo -->
  <a href="/" class="hdr-logo">
    <div class="hdr-logo-box">R</div>
    <div>
      <div class="hdr-logo-name"><?= $navBizName ?></div>
      <div class="hdr-logo-sub">Print Studio · Rajkot</div>
    </div>
  </a>

  <!-- Desktop Nav -->
  <nav class="hdr-nav" id="desktopNav">
    <a href="/"           class="hn <?= $currentUri === '/'          ? 'act' : '' ?>">Home</a>

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
        <a href="/products" class="dd-item" role="menuitem"
           style="color:var(--blue);font-weight:700">
          <div class="dd-item-ic" style="background:var(--blue);color:#fff">→</div>
          See All Products
        </a>
      </div>
    </div>

    <a href="/#why-sec"     class="hn">Why Us</a>
    <a href="/#contact-sec" class="hn">Contact</a>
    <?php if ($user ?? null): ?>
      <a href="/my-orders" class="hn">My Orders</a>
    <?php endif; ?>
  </nav>

  <div class="hdr-space"></div>

 

  <!-- Right actions -->
  <div class="hdr-right">
    <!-- Cart -->
    <button class="cart-btn" onclick="toggleCart()" title="Shopping Cart" aria-label="Open cart">
      <svg viewBox="0 0 24 24"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96C5 16.1 6.9 18 9 18h12v-2H9.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63H19c.75 0 1.41-.41 1.75-1.03l3.58-6.49A1 1 0 0 0 23.46 5H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
      <span class="cart-count hidden" id="cartCount">0</span>
    </button>

    <!-- Auth -->
    <?php if ($user ?? null): ?>
      <span class="hdr-user-name"><?= htmlspecialchars(explode(' ', $user['name'])[0]) ?></span>
      <a href="/logout" class="btn-auth btn-auth-ghost">Logout</a>
    <?php else: ?>
      <a href="/login" class="btn-auth btn-auth-ghost">Login</a>
    <?php endif; ?>

    

    <!-- Hamburger — fixed: calls toggleDrawer() which exists in app.js -->
    <button class="ham" onclick="toggleDrawer()" id="hamBtn" aria-label="Open menu" aria-expanded="false">
      <svg viewBox="0 0 24 24"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
    </button>
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

    <a href="/#why-sec"     class="md-item" onclick="closeDrawer()">⭐ Why Us</a>
    <a href="/#contact-sec" class="md-item" onclick="closeDrawer()">📞 Contact</a>

    <?php if ($user ?? null): ?>
      <a href="/my-orders" class="md-item">📋 My Orders</a>
      <a href="/logout"    class="md-item">👤 <?= htmlspecialchars($user['name']) ?> (Logout)</a>
    <?php else: ?>
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
<script src="/js/app.js"></script>
