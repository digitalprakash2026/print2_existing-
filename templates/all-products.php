<?php
/**
 * all-products.php — All Products Page
 * Shows every active product with category filter tabs
 * Route: /products
 */
$pageTitle = 'All Products — RCS Graphic';
$pageDesc  = 'Browse all printing products at RCS Graphic — business cards, brochures, banners, flyers and more.';

$bizWa = $settingsMap['biz_whatsapp'] ?? '919876543210';

include INCLUDE_PATH . '/partials/head.php';
include INCLUDE_PATH . '/partials/header.php';
?>

<div style="margin-top:calc(var(--site-hh, var(--hh)) + var(--post-header-gap,50px));padding-bottom:80px;background:var(--bg);min-height:calc(100vh - var(--site-hh, var(--hh)) - var(--post-header-gap,50px))">
  <div class="container">

    <!-- Breadcrumb -->
    <div class="breadcrumb" style="padding-top:22px">
      <a href="/">Home</a><span>/</span>
      <span style="color:var(--ink);font-weight:600">All Products</span>
    </div>

    <!-- Page header -->
    <div style="padding:24px 0 22px;border-bottom:1px solid var(--border);margin-bottom:28px">
      <div class="sec-ey">Browse</div>
      <h1 style="font-family:var(--fd);font-size:clamp(24px,4vw,36px);font-weight:700;
                 color:var(--ink);line-height:1.15;margin:0 0 6px">
        All Print Products
      </h1>
      <p style="font-size:14px;color:var(--text2);margin:0">
        <?= count($products) ?> products available · Premium quality · Fast delivery from Rajkot
      </p>
    </div>

    <!-- Category filter pills -->
    <?php if (!empty($categories)): ?>
    <div style="margin-bottom:24px">
      <div class="chip-row">
        <div class="chip on" data-cat="all" onclick="apFilter('all',this)">All Products</div>
        <?php foreach ($categories as $cat): ?>
        <div class="chip" data-cat="<?= htmlspecialchars($cat['slug']) ?>"
             onclick="apFilter('<?= htmlspecialchars($cat['slug']) ?>',this)">
          <?= htmlspecialchars($cat['icon'] ?? '') ?> <?= htmlspecialchars($cat['name']) ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Product count label -->
    <div style="font-size:13px;color:var(--text2);margin-bottom:18px" id="apCountLbl">
      Showing <?= count($products) ?> products
    </div>

    <!-- Products grid -->
    <?php if (empty($products)): ?>
    <div style="text-align:center;padding:80px 20px">
      <div style="font-size:48px;margin-bottom:12px">🖨️</div>
      <div style="font-size:16px;font-weight:700;color:var(--ink);margin-bottom:6px">No products yet</div>
      <p style="font-size:13px;color:var(--text2);margin-bottom:20px">Check back soon or contact us on WhatsApp</p>
      <a href="https://wa.me/<?= htmlspecialchars($bizWa) ?>" target="_blank" class="btn btn-green">
        💬 WhatsApp Us
      </a>
    </div>
    <?php else: ?>

    <div class="prod-grid" id="apGrid">
      <?php foreach ($products as $p):
        $img     = $p['primary_image'] ?? '';
        $minP    = (float)($p['min_price'] ?? 0);
        $catSlug = '';
        foreach ($categories as $cat) {
            if ($cat['id'] == $p['category_id']) { $catSlug = $cat['slug']; break; }
        }
      ?>
      <a href="/product/<?= htmlspecialchars($p['slug']) ?>"
         class="pc" data-design-target="product.card"
         data-cat="<?= htmlspecialchars($catSlug) ?>">
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
          <div class="pc-name" data-design-target="product.card.title"><?= htmlspecialchars($p['name']) ?></div>
          <div class="pc-desc" data-design-target="product.card.description"><?= htmlspecialchars($p['description'] ?? '') ?></div>
          <div class="pc-foot">
            <div>
              <div class="pc-from">Starting from</div>
              <div class="pc-price" data-design-target="product.card.price">₹<?= $minP > 0 ? number_format($minP) : '—' ?></div>
            </div>
            <div class="pc-arr">
              <svg viewBox="0 0 24 24"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg>
            </div>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- No results message (shown by JS) -->
    <div id="apNoResults" style="display:none;text-align:center;padding:60px 20px;color:var(--text2)">
      <div style="font-size:40px;margin-bottom:10px">🔍</div>
      <div style="font-size:15px;font-weight:600;margin-bottom:4px">No products in this category yet</div>
      <div style="font-size:13px">Try another category or contact us for custom requirements</div>
    </div>

    <?php endif; ?>

  </div>
</div>

<script>
function apFilter(slug, btn) {
  // Update active chip
  document.querySelectorAll('.chip-row .chip').forEach(c => c.classList.remove('on'));
  btn.classList.add('on');

  // Filter cards
  let visible = 0;
  document.querySelectorAll('#apGrid .pc').forEach(card => {
    const show = slug === 'all' || (card.dataset.cat || '') === slug;
    card.style.display = show ? '' : 'none';
    if (show) visible++;
  });

  // Update count label
  const lbl = document.getElementById('apCountLbl');
  if (lbl) lbl.textContent = 'Showing ' + visible + ' product' + (visible !== 1 ? 's' : '');

  // Show/hide no-results
  const nr = document.getElementById('apNoResults');
  if (nr) nr.style.display = visible === 0 ? 'block' : 'none';
}
</script>

<?php include INCLUDE_PATH . '/partials/footer.php'; ?>
