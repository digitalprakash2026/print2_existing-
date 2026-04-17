<?php
/**
 * category.php — Category Page
 * Shows all products belonging to a single category
 * Route: /category/{slug}
 * Variables: $category (array), $products (array), $categories (array), $settingsMap (array)
 */
$pageTitle = htmlspecialchars($category['name']) . ' Printing — RCS Graphic';
$pageDesc  = 'Browse all ' . htmlspecialchars($category['name']) . ' products at RCS Graphic. Premium quality printing in Rajkot.';

$settingsMap = [];
try {
    $settings    = Database::rows("SELECT `key`, value FROM settings");
    $settingsMap = array_column($settings, 'value', 'key');
} catch (\Throwable) {}

$bizWa = $settingsMap['biz_whatsapp'] ?? '919876543210';

include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/header.php';
?>

<div style="margin-top:var(--hh); padding-bottom:80px; background:var(--bg); min-height:calc(100vh - var(--hh))">
  <div class="container">

    <!-- ── Breadcrumb ─────────────────────────────────────── -->
    <div class="breadcrumb" style="padding-top:22px">
      <a href="/">Home</a><span>/</span>
      <a href="/#prod-sec">Products</a><span>/</span>
      <span style="color:var(--ink);font-weight:600"><?= htmlspecialchars($category['name']) ?></span>
    </div>

    <!-- ── Category Header ───────────────────────────────── -->
    <div style="padding:28px 0 24px; border-bottom:1px solid var(--border); margin-bottom:30px">
      <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
        <div style="width:56px;height:56px;border-radius:14px;background:var(--blue-bg);
                    display:flex;align-items:center;justify-content:center;
                    font-size:28px;flex-shrink:0;border:1.5px solid var(--blue-mid)">
          <?= htmlspecialchars($category['icon'] ?? '📦') ?>
        </div>
        <div>
          <div class="sec-ey"><?= htmlspecialchars($category['name']) ?> Printing</div>
          <h1 style="font-family:var(--fd);font-size:clamp(22px,4vw,34px);font-weight:700;
                     color:var(--ink);line-height:1.15;margin:0">
            <?= htmlspecialchars($category['name']) ?> Products
          </h1>
          <?php if (!empty($category['description'])): ?>
          <p style="font-size:14px;color:var(--text2);margin-top:6px;line-height:1.6">
            <?= htmlspecialchars($category['description']) ?>
          </p>
          <?php endif; ?>
        </div>
        <div style="margin-left:auto;display:flex;gap:10px;flex-shrink:0">
          <span style="font-size:13px;color:var(--text2);align-self:center">
            <?= count($products) ?> product<?= count($products) !== 1 ? 's' : '' ?>
          </span>
          <a href="https://wa.me/<?= htmlspecialchars($bizWa) ?>?text=<?= urlencode('Hi! I need ' . $category['name'] . ' printing. Can you help?') ?>"
             target="_blank"
             class="btn btn-outline btn-sm"
             style="display:inline-flex;align-items:center;gap:6px">
            💬 WhatsApp
          </a>
        </div>
      </div>
    </div>

    <!-- ── Other Categories ──────────────────────────────── -->
    <?php if (!empty($categories)): ?>
    <div style="margin-bottom:24px">
      <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
        <span style="font-size:12px;font-weight:700;color:var(--text3);text-transform:uppercase;
                     letter-spacing:.07em;margin-right:4px">Browse:</span>
        <?php foreach ($categories as $cat):
          $isActive = ($cat['id'] == $category['id']);
        ?>
        <a href="/category/<?= htmlspecialchars($cat['slug']) ?>"
           class="chip <?= $isActive ? 'on' : '' ?>"
           style="<?= $isActive ? '' : '' ?>">
          <?= htmlspecialchars($cat['icon'] ?? '') ?> <?= htmlspecialchars($cat['name']) ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- ── Products Grid ─────────────────────────────────── -->
    <?php if (empty($products)): ?>
    <div style="text-align:center;padding:80px 20px;color:var(--text2)">
      <div style="font-size:52px;margin-bottom:14px">🖨️</div>
      <div style="font-size:18px;font-weight:700;color:var(--ink);margin-bottom:8px">
        No products yet in <?= htmlspecialchars($category['name']) ?>
      </div>
      <p style="font-size:14px;color:var(--text2);margin-bottom:24px">
        We're adding products soon. Contact us for custom requirements.
      </p>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
        <a href="/" class="btn btn-outline">← Back to Home</a>
        <a href="https://wa.me/<?= htmlspecialchars($bizWa) ?>"
           target="_blank" class="btn btn-green">
          💬 WhatsApp Us
        </a>
      </div>
    </div>

    <?php else: ?>

    <div class="cat-page-grid" style="grid-template-columns:repeat(3,1fr)">
      <?php foreach ($products as $p):
        $img  = $p['primary_image'] ?? '';
        $minP = (float)($p['min_price'] ?? 0);
      ?>
      <a href="/product/<?= htmlspecialchars($p['slug']) ?>" class="pc">
        <div class="pc-img">
          <img src="<?= htmlspecialchars($img) ?>"
               alt="<?= htmlspecialchars($p['name']) ?>"
               loading="lazy"
               onerror="this.src='https://placehold.co/400x300/EEF3FD/1A56E8?text=<?= urlencode($p['name']) ?>'">
          <div class="pc-img-badge">
            <span class="badge b-blue"><?= htmlspecialchars($p['category_name'] ?? '') ?></span>
          </div>
        </div>
        <div class="pc-body">
          <div class="pc-cat"><?= htmlspecialchars($p['category_name'] ?? '') ?></div>
          <div class="pc-name"><?= htmlspecialchars($p['name']) ?></div>
          <div class="pc-desc"><?= htmlspecialchars($p['description'] ?? '') ?></div>
          <div class="pc-foot">
            <div>
              <div class="pc-from">Starting from</div>
              <div class="pc-price">₹<?= $minP > 0 ? number_format($minP) : '—' ?></div>
            </div>
            <div class="pc-arr">
              <svg viewBox="0 0 24 24"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg>
            </div>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- WhatsApp CTA -->
    <div style="margin-top:44px;background:var(--white);border-radius:16px;
                border:1.5px solid var(--border);padding:28px 24px;
                display:flex;align-items:center;justify-content:space-between;
                flex-wrap:wrap;gap:16px">
      <div>
        <div style="font-family:var(--fd);font-size:18px;font-weight:700;color:var(--ink);margin-bottom:5px">
          Need a custom <?= htmlspecialchars($category['name']) ?> print?
        </div>
        <div style="font-size:13px;color:var(--text2)">
          Tell us your requirements — bulk pricing, custom sizes, special finishes.
        </div>
      </div>
      <a href="https://wa.me/<?= htmlspecialchars($bizWa) ?>?text=<?= urlencode('Hi! I need custom ' . $category['name'] . ' printing. Please share details.') ?>"
         target="_blank"
         class="btn btn-green"
         style="padding:13px 24px;font-size:14px;flex-shrink:0">
        💬 Chat on WhatsApp
      </a>
    </div>

    <?php endif; ?>

  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
