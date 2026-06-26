<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($pageTitle ?? 'Admin — RCS Graphic') ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<?php
$adminAppCssVersion = @filemtime(PUBLIC_PATH . '/assets/css/app.css') ?: time();
$adminCssVersion = @filemtime(PUBLIC_PATH . '/assets/css/admin.css') ?: time();
$adminPageClass = 'admin-page-' . preg_replace('/[^a-z0-9-]+/i', '-', (string)($currentAdmPage ?? 'dashboard'));
$admin = \Auth\Auth::admin();
$isSuperAdmin = \Auth\Auth::isSuperAdmin();
$adminRoleLabel = $isSuperAdmin ? 'Super Admin' : 'Admin';
?>
<link rel="stylesheet" href="/assets/css/app.css?v=<?= (int)$adminAppCssVersion ?>">
<link rel="stylesheet" href="/assets/css/admin.css?v=<?= (int)$adminCssVersion ?>">
</head>
<body class="admin-shell <?= htmlspecialchars($adminPageClass) ?> <?= ($currentAdmPage ?? '') === 'dashboard' ? 'admin-dashboard-shell' : '' ?>" style="background:var(--bg)">
<div class="toast-wrap" id="tw"></div>

<!-- Admin Header -->
<header class="header adm-header adm-header-pro" style="z-index:950">
  <div class="adm-hdr-left">
    <button class="adm-mob-toggle" id="admMobToggle" type="button" aria-label="Open admin menu" aria-expanded="false" onclick="document.body.classList.toggle('adm-sb-open');this.setAttribute('aria-expanded', document.body.classList.contains('adm-sb-open') ? 'true' : 'false');">
      <span></span><span></span><span></span>
    </button>
  </div>
  <div class="adm-hdr-center"></div>
  <div class="adm-hdr-right">
    <div class="adm-hdr-actions">
      <button class="adm-icon-btn" type="button" aria-label="Fullscreen" onclick="document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen?.();">⛶</button>
      <span class="adm-hdr-divider"></span>
    </div>
    <div class="adm-user-menu" id="admUserMenu">
      <button class="adm-user-btn adm-user-btn-pro" id="admUserBtn" type="button" aria-expanded="false" aria-label="Admin account menu">
        <span><?= strtoupper(substr((string)($admin['name'] ?? 'A'), 0, 1)) ?></span><b>⌄</b>
      </button>
      <div class="adm-user-panel adm-user-panel-pro" id="admUserPanel">
        <div class="adm-user-name"><strong><?= htmlspecialchars($admin['name'] ?? 'Admin') ?></strong><small><?= htmlspecialchars($adminRoleLabel) ?></small></div>
        <a href="/admin/logout" class="adm-user-link adm-user-logout">🚪 Logout</a>
      </div>
    </div>
  </div>
</header>

<div style="margin-top:var(--hh)">
  <div class="adm-lay">
    <!-- Sidebar -->
    <div class="adm-sb" id="admSidebar">
      <div class="adm-sb-logo adm-sb-logo-img">
        <img src="/assets/images/RCS%20PRINT%20LOGO-white.png" alt="RCS Print Logo" loading="eager" decoding="async">
        <div class="adm-sb-s">Admin Panel</div>
      </div>
      <div class="adm-nl">Main</div>
      <?php $cur = $currentAdmPage ?? ''; ?>
      <a href="/admin/dashboard" class="adm-ni <?= $cur === 'dashboard' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>Dashboard</a>
      <a href="/admin/orders"   class="adm-ni <?= $cur === 'orders' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>Orders</a>
      <div class="adm-nl">Catalog</div>
      <a href="/admin/products" class="adm-ni <?= $cur === 'products' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M20.5 3l-.16.03L15 5.1 9 3 3.36 4.9c-.21.07-.36.25-.36.48V20.5c0 .28.22.5.5.5l.16-.03L9 18.9l6 2.1 5.64-1.9c.21-.07.36-.25.36-.48V3.5c0-.28-.22-.5-.5-.5zM15 19l-6-2.11V5l6 2.11V19z"/></svg>Products</a>
      <a href="/admin/categories" class="adm-ni <?= $cur === 'categories' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M10 4H4v6h6V4zm10 0h-8v6h8V4zM10 14H4v6h6v-6zm10 0h-8v6h8v-6z"/></svg>Categories</a>
      <a href="/admin/media" class="adm-ni <?= $cur === 'media' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM8.5 9.5c-.83 0-1.5-.67-1.5-1.5S7.67 6.5 8.5 6.5 10 7.17 10 8s-.67 1.5-1.5 1.5zM19 18H5l3.5-4.5 2.5 3.01L14.5 12 19 18z"/></svg>Media</a>
      <a href="/admin/portfolio" class="adm-ni <?= $cur === 'portfolio' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M4 5h16c1.1 0 2 .9 2 2v10c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V7c0-1.1.9-2 2-2zm0 2v10h16V7H4zm2 8 3.2-4.2 2.3 2.8 2.1-2.6L18 15H6zm10-5.5c-.83 0-1.5-.67-1.5-1.5S15.17 6.5 16 6.5s1.5.67 1.5 1.5S16.83 9.5 16 9.5z"/></svg>Portfolio</a>
      <a href="/admin/banners" class="adm-ni <?= $cur === 'banners' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M3 5h18v14H3V5zm2 2v10h14V7H5zm2 2h10v2H7V9zm0 4h7v2H7v-2z"/></svg>Banner Slider</a>
      <a href="/admin/deals" class="adm-ni <?= $cur === 'deals' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M20 6h-2.18c.11-.31.18-.65.18-1 0-1.66-1.34-3-3-3-1.12 0-2.1.61-2.62 1.52L12 4.17l-.38-.65C11.1 2.61 10.12 2 9 2 7.34 2 6 3.34 6 5c0 .35.07.69.18 1H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-5-2c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM9 4c.55 0 1 .45 1 1S9.55 6 9 6 8 5.55 8 5s.45-1 1-1zm11 15H4v-2h16v2zm0-5H4V8h5.08L7 10.83 8.62 12 12 7.4l3.38 4.6L17 10.83 14.92 8H20v6z"/></svg>Best Deals</a>
      <a href="/admin/blogs" class="adm-ni <?= $cur === 'blogs' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-1 16H6v-2h12v2zm0-4H6v-2h12v2zm0-4H6V5h12v6z"/></svg>Blogs</a>
      <div class="adm-nl">Tools</div>
      <a href="/admin/coupons"  class="adm-ni <?= $cur === 'coupons' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.42l9 9c.36.36.86.58 1.41.58.55 0 1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41 0-.55-.23-1.06-.59-1.42zM5.5 7C4.67 7 4 6.33 4 5.5S4.67 4 5.5 4 7 4.67 7 5.5 6.33 7 5.5 7z"/></svg>Coupons</a>
      <a href="/admin/reviews" class="adm-ni <?= $cur === 'reviews' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27z"/></svg>Reviews</a>
      <a href="/admin/faqs" class="adm-ni <?= $cur === 'faqs' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M11 18h2v-2h-2v2zm1-16C6.48 2 2 6.03 2 11c0 2.39 1.05 4.57 2.76 6.2L4 22l4.78-1.44c1 .29 2.08.44 3.22.44 5.52 0 10-4.03 10-9S17.52 2 12 2zm0 17c-.98 0-1.9-.14-2.76-.42l-.42-.13-2.12.64.34-2.08-.36-.34C5.01 15.31 4 13.23 4 11c0-3.86 3.58-7 8-7s8 3.14 8 7-3.58 8-8 8zm0-13c-2.21 0-4 1.46-4 3.25h2c0-.69.9-1.25 2-1.25s2 .56 2 1.25c0 .71-.46 1.04-1.42 1.58-1.02.58-1.58 1.31-1.58 2.67V14h2v-.5c0-.61.24-.85 1.1-1.34.94-.53 1.9-1.3 1.9-2.91C16 7.46 14.21 6 12 6z"/></svg>Add FAQ</a>
      <a href="/admin/customers" class="adm-ni <?= $cur === 'customers' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>Customers</a>
      <a href="/admin/leads" class="adm-ni <?= $cur === 'leads' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg>Leads</a>
      <?php if ($isSuperAdmin): ?>
      <a href="/admin/approvals" class="adm-ni <?= $cur === 'approvals' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M9 16.17 4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>Approvals</a>
      <a href="/admin/admins" class="adm-ni <?= $cur === 'admins' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 3-1.57 3-3.5S17.66 4 16 4s-3 1.57-3 3.5 1.34 3.5 3 3.5zm-8 0c1.66 0 3-1.57 3-3.5S9.66 4 8 4 5 5.57 5 7.5 6.34 11 8 11zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4zm8 0c-.34 0-.71.02-1.1.06C16.22 13.98 17 15.33 17 17v2h7v-2c0-2.66-5.33-4-8-4z"/></svg>Admins</a>
      <?php endif; ?>
      <div class="adm-nl">Config</div>
      <a href="/admin/design" class="adm-ni <?= $cur === 'design' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M12 3c-4.97 0-9 3.58-9 8 0 3.31 2.69 6 6 6h1.5c.83 0 1.5.67 1.5 1.5S12.67 20 13.5 20H15c3.31 0 6-2.69 6-6.5C21 7.7 16.97 3 12 3zM6.5 11C5.67 11 5 10.33 5 9.5S5.67 8 6.5 8 8 8.67 8 9.5 7.33 11 6.5 11zm3-3C8.67 8 8 7.33 8 6.5S8.67 5 9.5 5 11 5.67 11 6.5 10.33 8 9.5 8zm5 0c-.83 0-1.5-.67-1.5-1.5S13.67 5 14.5 5 16 5.67 16 6.5 15.33 8 14.5 8zm3 3c-.83 0-1.5-.67-1.5-1.5S16.67 8 17.5 8 19 8.67 19 9.5 18.33 11 17.5 11z"/></svg>Design Studio</a>
      <a href="/admin/settings" class="adm-ni <?= $cur === 'settings' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>Settings</a>
      <a href="/admin/integrations" class="adm-ni <?= $cur === 'integrations' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M12 2a5 5 0 0 1 5 5v2h-2V7a3 3 0 1 0-6 0v2H7V7a5 5 0 0 1 5-5zm-7 9h14v9a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-9zm4 3v2h2v-2H9zm4 0v2h2v-2h-2z"/></svg>Integrations</a>
      <a href="/admin/audit-logs" class="adm-ni <?= $cur === 'audit' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.89 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm-1 7V3.5L18.5 9H13zM6 20V4h5v7h7v9H6z"/></svg>Audit Log</a>
      <a href="/admin/export/orders" class="adm-ni" target="_blank"><svg viewBox="0 0 24 24"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/></svg>Export CSV</a>
      <div class="adm-help-card">
        <span>🎧</span>
        <div><strong>Need Help?</strong><small>+91 98765 43210</small></div>
      </div>
    </div>
    <button class="adm-sb-backdrop" id="admSidebarBack" type="button" aria-label="Close admin menu" onclick="document.body.classList.remove('adm-sb-open');document.getElementById('admMobToggle')?.setAttribute('aria-expanded','false');"></button>

    <script>
    (function(){
      const sb = document.getElementById('admSidebar');
      const t = document.getElementById('admMobToggle');
      if (sb && t) {
        sb.addEventListener('click', function (e) {
          if (window.innerWidth <= 900 && e.target.closest('.adm-ni')) {
            document.body.classList.remove('adm-sb-open');
            t.setAttribute('aria-expanded', 'false');
          }
        });
        document.addEventListener('keydown', function (e) {
          if (e.key === 'Escape') {
            document.body.classList.remove('adm-sb-open');
            t.setAttribute('aria-expanded', 'false');
          }
        });
        window.addEventListener('resize', function () {
          if (window.innerWidth > 900) {
            document.body.classList.remove('adm-sb-open');
            t.setAttribute('aria-expanded', 'false');
          }
        });
      } else {
        document.addEventListener('keydown', function (e) {
          if (e.key === 'Escape') {
            document.body.classList.remove('adm-sb-open');
          }
        });
        window.addEventListener('resize', function () {
          if (window.innerWidth > 900) {
            document.body.classList.remove('adm-sb-open');
          }
        });
      }

      const userBtn = document.getElementById('admUserBtn');
      const userMenu = document.getElementById('admUserMenu');
      if (userBtn && userMenu) {
        userBtn.addEventListener('click', function (e) {
          e.stopPropagation();
          const open = userMenu.classList.toggle('open');
          userBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        document.addEventListener('click', function (e) {
          if (!userMenu.contains(e.target)) {
            userMenu.classList.remove('open');
            userBtn.setAttribute('aria-expanded', 'false');
          }
        });
      }
    })();
    </script>

    <!-- Main content -->
    <div class="adm-main<?= !empty($admMainClass) ? ' ' . htmlspecialchars((string)$admMainClass) : '' ?>">
