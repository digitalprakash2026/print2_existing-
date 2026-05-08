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
  <div class="topbar">
    <div class="header-container topbar-inner">
      <div class="topbar-left">
        <span>
          <i class="fa-solid fa-truck-fast" aria-hidden="true"></i>
          Free Delivery in Rajkot on All Orders Above ₹999
        </span>
      </div>

      <div class="topbar-right">
        <div class="topbar-links">
          <a href="/my-orders" aria-label="Track Order">Track Order</a>
          <span class="topbar-divider" aria-hidden="true"></span>
          <a href="/#contact-sec" aria-label="Help Center">Help Center</a>
        </div>

        <div class="social-links" aria-label="Social links">
          <a href="/#contact-sec" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
          <a href="/#contact-sec" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
          <a href="https://wa.me/<?= $navWa ?>" aria-label="WhatsApp" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i></a>
        </div>
      </div>
    </div>
  </div>

  <nav class="navbar" aria-label="Main navigation">
    <div class="header-container navbar-inner">
      <a href="/" class="brand" aria-label="<?= $navBizName ?> Home">
        <img src="/assets/images/rcs-graphic-logo.png"
             alt="<?= $navBizName ?> Logo"
             class="brand-img"
             loading="eager"
             decoding="async">
      </a>

      <div class="nav-center">
        <ul class="nav-menu">
          <li><a href="/" class="nav-link <?= $currentUri === '/' ? 'active' : '' ?>">Home</a></li>
          <li class="nav-dropdown" id="ddWrap">
            <button class="nav-link nav-link-button" id="ddBtn" type="button" aria-expanded="false" aria-haspopup="true">
              Products
              <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
            </button>
            <div class="dd-bridge"></div>
            <div class="dd-panel" id="ddPanel" role="menu">
              <?php if (!empty($navCategories)): ?>
                <div class="dd-cat-lbl">Browse by Category</div>
                <?php foreach ($navCategories as $cat): ?>
                  <a href="/category/<?= htmlspecialchars($cat['slug']) ?>" class="dd-item" role="menuitem">
                    <span class="dd-item-ic"><?= htmlspecialchars($cat['icon'] ?? '🖨️') ?></span>
                    <span><?= htmlspecialchars($cat['name']) ?></span>
                    <?php if ((int)($cat['product_count'] ?? 0) > 0): ?>
                      <span class="dd-count"><?= (int)$cat['product_count'] ?></span>
                    <?php endif; ?>
                  </a>
                <?php endforeach; ?>
                <div class="dd-divider"></div>
              <?php endif; ?>
              <?php if (!empty($navProducts)): ?>
                <div class="dd-cat-lbl">Products</div>
                <?php foreach (array_slice($navProducts, 0, 6) as $p): ?>
                  <a href="/product/<?= htmlspecialchars($p['slug']) ?>" class="dd-item" role="menuitem">
                    <span class="dd-item-ic"><?= $catIcons[$p['category_name']] ?? '🖨️' ?></span>
                    <span><?= htmlspecialchars($p['name']) ?></span>
                  </a>
                <?php endforeach; ?>
                <div class="dd-divider"></div>
              <?php endif; ?>
              <a href="/products" class="dd-item dd-item-all" role="menuitem">
                <span class="dd-item-ic">→</span>
                <span>See All Products</span>
              </a>
            </div>
          </li>
          <li><a href="/#why-sec" class="nav-link">About Us</a></li>
          <li><a href="/#contact-sec" class="nav-link">Contact Us</a></li>
          <li><a href="/products" class="nav-link">Blog</a></li>
          <li><a href="<?= ($user ?? null) ? '/profile' : '/login' ?>" class="nav-link">My Account</a></li>
        </ul>
      </div>

      <div class="navbar-actions">
        <a href="/products" class="action-btn" aria-label="Search">
          <i class="fa-solid fa-magnifying-glass"></i>
        </a>
        <a href="<?= ($user ?? null) ? '/profile' : '/login' ?>" class="action-btn" aria-label="My Account">
          <i class="fa-regular fa-user"></i>
        </a>
        <button class="action-btn cart-btn" onclick="toggleCart()" aria-label="Cart" type="button">
          <i class="fa-solid fa-cart-shopping"></i>
          <span class="cart-badge" id="cartCount">2</span>
        </button>
        <button class="menu-toggle" id="hamBtn" onclick="toggleDrawer()" aria-label="Toggle menu" aria-expanded="false" type="button">
          <i class="fa-solid fa-bars"></i>
        </button>
      </div>
    </div>
  </nav>
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
