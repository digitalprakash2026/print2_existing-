<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($pageTitle ?? 'Admin — RCS Graphic') ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:ital,opsz,wght@0,9..144,600;0,9..144,700;1,9..144,500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/app.css">
</head>
<body style="background:var(--bg)">
<div class="toast-wrap" id="tw"></div>

<!-- Admin Header -->
<header class="header" style="z-index:950">
  <div class="hdr-logo" onclick="location.href='/admin'">
    <div class="hdr-logo-box">R</div>
    <div><div class="hdr-logo-name">RCS Admin</div><div class="hdr-logo-sub">Print Order System</div></div>
  </div>
  <div class="hdr-space"></div>
  <div style="display:flex;align-items:center;gap:10px">
    <?php $admin = \Auth\Auth::admin(); ?>
    <span style="font-size:13px;color:var(--text2)"><?= htmlspecialchars($admin['name'] ?? '') ?></span>
    <a href="/" class="btn-auth btn-auth-ghost" style="font-size:12px">🌐 Site</a>
    <a href="/admin/logout" class="btn-auth btn-auth-ghost" style="font-size:12px">Logout</a>
  </div>
</header>

<div style="margin-top:var(--hh)">
  <div class="adm-lay">
    <!-- Sidebar -->
    <div class="adm-sb">
      <div class="adm-sb-logo">
        <div class="adm-sb-t">RCS Graphic</div>
        <div class="adm-sb-s">Admin Panel</div>
      </div>
      <div class="adm-nl">Main</div>
      <?php $cur = $currentAdmPage ?? ''; ?>
      <a href="/admin/dashboard" class="adm-ni <?= $cur === 'dashboard' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>Dashboard</a>
      <a href="/admin/orders"   class="adm-ni <?= $cur === 'orders' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>Orders</a>
      <a href="/admin/analytics" class="adm-ni <?= $cur === 'analytics' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>Analytics</a>
      <div class="adm-nl">Catalog</div>
      <a href="/admin/products" class="adm-ni <?= $cur === 'products' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M20.5 3l-.16.03L15 5.1 9 3 3.36 4.9c-.21.07-.36.25-.36.48V20.5c0 .28.22.5.5.5l.16-.03L9 18.9l6 2.1 5.64-1.9c.21-.07.36-.25.36-.48V3.5c0-.28-.22-.5-.5-.5zM15 19l-6-2.11V5l6 2.11V19z"/></svg>Products</a>
      <a href="/admin/products/new" class="adm-ni <?= $cur === 'products-new' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>Add Product</a>
      <a href="/admin/banners" class="adm-ni <?= $cur === 'banners' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M3 5h18v14H3V5zm2 2v10h14V7H5zm2 2h10v2H7V9zm0 4h7v2H7v-2z"/></svg>Banner Slider</a>
      <a href="/admin/pricing"  class="adm-ni <?= $cur === 'pricing' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg>Pricing</a>
      <div class="adm-nl">Tools</div>
      <a href="/admin/coupons"  class="adm-ni <?= $cur === 'coupons' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.42l9 9c.36.36.86.58 1.41.58.55 0 1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41 0-.55-.23-1.06-.59-1.42zM5.5 7C4.67 7 4 6.33 4 5.5S4.67 4 5.5 4 7 4.67 7 5.5 6.33 7 5.5 7z"/></svg>Coupons</a>
      <a href="/admin/customers" class="adm-ni <?= $cur === 'customers' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>Customers</a>
      <div class="adm-nl">Config</div>
      <a href="/admin/settings" class="adm-ni <?= $cur === 'settings' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>Settings</a>
      <a href="/admin/integrations" class="adm-ni <?= $cur === 'integrations' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M12 2a5 5 0 0 1 5 5v2h-2V7a3 3 0 1 0-6 0v2H7V7a5 5 0 0 1 5-5zm-7 9h14v9a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-9zm4 3v2h2v-2H9zm4 0v2h2v-2h-2z"/></svg>Integrations</a>
      <a href="/admin/audit-logs" class="adm-ni <?= $cur === 'audit' ? 'act' : '' ?>"><svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.89 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm-1 7V3.5L18.5 9H13zM6 20V4h5v7h7v9H6z"/></svg>Audit Log</a>
      <a href="/admin/export/orders" class="adm-ni" target="_blank"><svg viewBox="0 0 24 24"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/></svg>Export CSV</a>
      <div class="adm-sb-logout">
        <a href="/admin/logout" style="display:block;padding:9px;border-radius:8px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.09);color:rgba(255,255,255,.5);font-size:13px;cursor:pointer;text-align:center;text-decoration:none">← Logout</a>
      </div>
    </div>

    <!-- Main content -->
    <div class="adm-main<?= !empty($admMainClass) ? ' ' . htmlspecialchars((string)$admMainClass) : '' ?>">
