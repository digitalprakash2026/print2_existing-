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
    if (typeof event.data.css === 'string') {
      style.textContent = event.data.css;
      if (window.parent && window.parent !== window) {
        window.parent.postMessage({type:'RCS_THEME_PREVIEW_APPLIED', cssLength:style.textContent.length}, window.location.origin);
      }
    }
  }
  if (event.data.type === 'RCS_THEME_ENABLE_INSPECTOR') {
    window.RCS_THEME_INSPECTOR_ELEMENTS = event.data.elements || {};
    if (window.RCS_THEME_INSPECTOR_READY) return;
    window.RCS_THEME_INSPECTOR_READY = true;
    var inspectorStyle = document.createElement('style');
    inspectorStyle.textContent = '.rcs-inspector-hover{outline:2px dashed #2563eb!important;outline-offset:3px!important;cursor:crosshair!important}.rcs-inspector-selected{outline:3px solid #ea580c!important;outline-offset:4px!important}';
    document.head.appendChild(inspectorStyle);
    var selected = null;
    var rgbToHex = function (value) {
      if (!value || value === 'transparent') return '';
      if (value.charAt(0) === '#') return value.toUpperCase();
      var match = value.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([0-9.]+))?\)/i);
      if (!match || (match[4] !== undefined && Number(match[4]) === 0)) return '';
      return '#' + [match[1], match[2], match[3]].map(function (part) {
        var hex = Math.max(0, Math.min(255, Number(part))).toString(16);
        return hex.length === 1 ? '0' + hex : hex;
      }).join('').toUpperCase();
    };
    var cleanPx = function (value) {
      var num = parseFloat(value || '');
      return Number.isFinite(num) ? (Math.round(num * 100) / 100) + 'px' : '';
    };
    var normalizeLineHeight = function (value, fontSize) {
      var num = parseFloat(value || '');
      var font = parseFloat(fontSize || '');
      if (!Number.isFinite(num)) return '';
      if (Number.isFinite(font) && font > 0 && String(value).indexOf('px') !== -1) {
        return String(Math.round((num / font) * 100) / 100);
      }
      return String(Math.round(num * 100) / 100);
    };
    var normalizeWeight = function (value) {
      value = String(value || '').toLowerCase().trim();
      if (value === 'normal') return '400';
      if (value === 'bold') return '700';
      return value;
    };
    var computedStyles = function (el) {
      var cs = window.getComputedStyle(el);
      return {
        fontFamily: (cs.fontFamily || '').split(',')[0].replace(/["']/g, '').trim(),
        fontSize: cleanPx(cs.fontSize),
        fontWeight: normalizeWeight(cs.fontWeight),
        lineHeight: normalizeLineHeight(cs.lineHeight, cs.fontSize),
        color: rgbToHex(cs.color),
        backgroundColor: rgbToHex(cs.backgroundColor),
        borderColor: rgbToHex(cs.borderColor),
        borderRadius: cleanPx(cs.borderRadius),
        paddingTop: cleanPx(cs.paddingTop),
        paddingBottom: cleanPx(cs.paddingBottom),
        paddingLeft: cleanPx(cs.paddingLeft),
        paddingRight: cleanPx(cs.paddingRight),
        marginBottom: cleanPx(cs.marginBottom),
        boxShadow: (cs.boxShadow && cs.boxShadow !== 'none') ? 'premium' : 'none'
      };
    };
    var findTarget = function (node) {
      var map = window.RCS_THEME_INSPECTOR_ELEMENTS || {};
      while (node && node !== document.body) {
        var explicit = node.getAttribute && node.getAttribute('data-design-target');
        if (explicit && map[explicit]) {
          return {key:explicit, label:map[explicit].label || explicit, el:node};
        }
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
      window.parent.postMessage({type:'RCS_THEME_ELEMENT_SELECTED', target:found.key, label:found.label, computed:computedStyles(selected)}, window.location.origin);
    }, true);
  }
});
</script>
</head>
<body>
