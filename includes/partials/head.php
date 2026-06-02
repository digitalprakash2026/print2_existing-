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
  if (event.origin !== window.location.origin || !event.data) return;
  if (event.data.type === 'RCS_THEME_PREVIEW') {
    var style = document.getElementById('rcs-theme-vars');
    if (!style) {
      style = document.createElement('style');
      style.id = 'rcs-theme-vars';
      document.head.appendChild(style);
    }
    if (typeof event.data.css === 'string') style.textContent = event.data.css;
  }
  if (event.data.type === 'RCS_THEME_ENABLE_INSPECTOR') {
    window.RCS_THEME_INSPECTOR_ELEMENTS = event.data.elements || {};
    if (window.RCS_THEME_INSPECTOR_READY) return;
    window.RCS_THEME_INSPECTOR_READY = true;
    var inspectorStyle = document.createElement('style');
    inspectorStyle.textContent = '.rcs-inspector-hover{outline:2px dashed #2563eb!important;outline-offset:3px!important;cursor:crosshair!important}.rcs-inspector-selected{outline:3px solid #ea580c!important;outline-offset:4px!important}';
    document.head.appendChild(inspectorStyle);
    var selected = null;
    var findTarget = function (node) {
      var map = window.RCS_THEME_INSPECTOR_ELEMENTS || {};
      while (node && node !== document.body) {
        for (var key in map) {
          if (map[key] && map[key].selector && node.matches && node.matches(map[key].selector)) {
            return {key:key, label:map[key].label || key, el:node};
          }
        }
        node = node.parentElement;
      }
      return null;
    };
    document.addEventListener('mouseover', function (e) {
      var found = findTarget(e.target);
      if (found && found.el !== selected) found.el.classList.add('rcs-inspector-hover');
    }, true);
    document.addEventListener('mouseout', function (e) {
      var found = findTarget(e.target);
      if (found) found.el.classList.remove('rcs-inspector-hover');
    }, true);
    document.addEventListener('click', function (e) {
      var found = findTarget(e.target);
      if (!found) return;
      e.preventDefault();
      e.stopPropagation();
      if (selected) selected.classList.remove('rcs-inspector-selected');
      selected = found.el;
      selected.classList.remove('rcs-inspector-hover');
      selected.classList.add('rcs-inspector-selected');
      window.parent.postMessage({type:'RCS_THEME_ELEMENT_SELECTED', target:found.key, label:found.label}, window.location.origin);
    }, true);
  }
});
</script>
</head>
<body>
