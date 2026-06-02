<?php
/**
 * head.php — HTML <head> partial (does NOT open <body>)
 * Included by home.php, product.php, etc. BEFORE header.php
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($pageTitle ?? 'RCS Graphic — Premium Print Ordering') ?></title>
<meta name="description" content="<?= htmlspecialchars($pageDesc ?? 'Professional printing services — business cards, brochures, banners and more. Fast delivery, GST invoice, secure Razorpay payment.') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Merriweather:wght@600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
<link rel="stylesheet" href="/assets/css/app.css?v=<?= file_exists(PUBLIC_PATH . '/assets/css/app.css') ? filemtime(PUBLIC_PATH . '/assets/css/app.css') : time() ?>">
<?php $siteTheme = \Theme\SiteTheme::load(); ?>
<style id="rcs-theme-vars"><?= \Theme\SiteTheme::css($siteTheme) ?></style>
<meta name="csrf-token" content="<?= htmlspecialchars($csrf ?? '') ?>">
<script>
window.RCS_THEME_DEFAULTS = <?= json_encode(\Theme\SiteTheme::defaults(), JSON_UNESCAPED_SLASHES) ?>;
window.addEventListener('message', function (event) {
  if (event.origin !== window.location.origin || !event.data || event.data.type !== 'RCS_THEME_PREVIEW') return;
  var style = document.getElementById('rcs-theme-vars');
  if (!style) {
    style = document.createElement('style');
    style.id = 'rcs-theme-vars';
    document.head.appendChild(style);
  }
  if (typeof event.data.css === 'string') style.textContent = event.data.css;
});
</script>
</head>
<body>
