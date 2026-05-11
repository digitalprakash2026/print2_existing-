<?php $pageTitle = 'Page Not Found — RCS Graphic'; include INCLUDE_PATH . '/partials/head.php'; include INCLUDE_PATH . '/partials/header.php'; ?>
<div style="margin-top:var(--hh);min-height:calc(100vh - var(--hh));display:flex;align-items:center;justify-content:center;padding:36px 18px">
  <div style="text-align:center;max-width:420px">
    <div style="font-family:var(--fd);font-size:80px;font-weight:700;color:var(--blue);line-height:1;margin-bottom:12px">404</div>
    <div style="font-family:var(--fd);font-size:22px;font-weight:700;margin-bottom:8px">Page Not Found</div>
    <div style="font-size:14px;color:var(--text2);margin-bottom:24px;line-height:1.6">The page you're looking for doesn't exist or has been moved.</div>
    <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
      <a href="/" class="btn btn-blue">← Back to Home</a>
      <a href="/products" class="btn btn-outline">Browse Products</a>
    </div>
  </div>
</div>
<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
