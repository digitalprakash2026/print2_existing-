<?php
$pageTitle = 'Media Library — RCS Admin';
$currentAdmPage = 'media';
include __DIR__ . '/layout.php';

$mediaCategories = [
    'all' => ['label' => 'All Media', 'icon' => '▦'],
    'products' => ['label' => 'Products', 'icon' => '🖼️', 'dir' => 'products'],
    'categories' => ['label' => 'Categories', 'icon' => '▣', 'dir' => 'categories'],
    'banners' => ['label' => 'Banners', 'icon' => '▤', 'dir' => 'banners'],
    'deals' => ['label' => 'Deals', 'icon' => '◆', 'dir' => 'deals'],
    'blogs' => ['label' => 'Blogs', 'icon' => '✍', 'dir' => 'blogs'],
    'artwork' => ['label' => 'Artwork', 'icon' => '📁', 'dir' => 'artwork'],
    'proofs' => ['label' => 'Proofs', 'icon' => '✓', 'dir' => 'artwork/proofs'],
    'revisions' => ['label' => 'Revisions', 'icon' => '↻', 'dir' => 'artwork/revisions'],
    'documents' => ['label' => 'Documents', 'icon' => '📄'],
    'videos' => ['label' => 'Videos', 'icon' => '▶'],
];
$selectedCategory = strtolower(trim((string)($_GET['category'] ?? 'all')));
if (!isset($mediaCategories[$selectedCategory])) $selectedCategory = 'all';
$search = strtolower(trim((string)($_GET['search'] ?? '')));

$uploadsRoot = rtrim((string)realpath(PUBLIC_PATH . '/uploads'), DIRECTORY_SEPARATOR);
$publicUploadsRoot = PUBLIC_PATH . '/uploads';
$imageExt = ['jpg','jpeg','png','gif','webp','svg'];
$docExt = ['pdf','ai','eps','psd','cdr','doc','docx','xls','xlsx','zip','tif','tiff'];
$videoExt = ['mp4','webm','mov'];
$mediaItems = [];
$mediaCounts = array_fill_keys(array_keys($mediaCategories), 0);

$formatBytes = static function (int $bytes): string {
    if ($bytes <= 0) return '—';
    $units = ['B','KB','MB','GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, $i === 0 ? 0 : 1) . ' ' . $units[$i];
};
$detectCategory = static function (string $relative, string $ext) use ($imageExt, $docExt, $videoExt): string {
    $relative = trim($relative, '/');
    if (str_starts_with($relative, 'artwork/proofs/')) return 'proofs';
    if (str_starts_with($relative, 'artwork/revisions/')) return 'revisions';
    foreach (['products','categories','banners','deals','blogs','artwork'] as $folder) {
        if (str_starts_with($relative, $folder . '/')) return $folder;
    }
    if (in_array($ext, $videoExt, true)) return 'videos';
    if (in_array($ext, $docExt, true)) return 'documents';
    if (in_array($ext, $imageExt, true)) return 'all';
    return 'documents';
};

if (is_dir($publicUploadsRoot)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($publicUploadsRoot, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $file) {
        if (!$file->isFile()) continue;
        $path = $file->getPathname();
        $relative = ltrim(str_replace('\\', '/', substr($path, strlen($publicUploadsRoot))), '/');
        if (str_starts_with($relative, '.trash/')) continue;
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $isImage = in_array($ext, $imageExt, true);
        $isVideo = in_array($ext, $videoExt, true);
        $category = $detectCategory($relative, $ext);
        if ($isVideo) $mediaCounts['videos']++;
        if (in_array($ext, $docExt, true)) $mediaCounts['documents']++;
        if (isset($mediaCounts[$category])) $mediaCounts[$category]++;
        $mediaCounts['all']++;

        $name = $file->getFilename();
        if ($selectedCategory !== 'all') {
            $categoryMatch = $selectedCategory === $category
                || ($selectedCategory === 'documents' && in_array($ext, $docExt, true))
                || ($selectedCategory === 'videos' && $isVideo);
            if (!$categoryMatch) continue;
        }
        if ($search !== '' && !str_contains(strtolower($name . ' ' . $relative), $search)) continue;

        $mediaItems[] = [
            'name' => $name,
            'url' => '/uploads/' . $relative,
            'relative' => $relative,
            'category' => $category,
            'type' => $isVideo ? 'video' : ($isImage ? 'image' : 'document'),
            'ext' => strtoupper($ext ?: 'FILE'),
            'size' => $formatBytes((int)$file->getSize()),
            'mtime' => (int)$file->getMTime(),
        ];
    }
}

usort($mediaItems, static fn(array $a, array $b): int => ($b['mtime'] <=> $a['mtime']) ?: strcmp($a['name'], $b['name']));
$mediaItems = array_slice($mediaItems, 0, 240);
$h = static fn($v): string => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>

<div class="adm-media-page">
  <section class="adm-media-hero">
    <div>
      <span>WordPress style library</span>
      <h1>Media Library</h1>
      <p>Manage uploaded product images, artwork, proofs, blog media and documents from one organized place.</p>
    </div>
    <strong><?= number_format((int)($mediaCounts['all'] ?? 0)) ?> files</strong>
  </section>

  <form class="adm-media-toolbar" method="GET">
    <label><span>🔎</span><input name="search" value="<?= $h($_GET['search'] ?? '') ?>" placeholder="Search file name or folder…"></label>
    <input type="hidden" name="category" value="<?= $h($selectedCategory) ?>">
    <button type="submit">Search</button>
    <?php if ($search !== ''): ?><a href="/admin/media?category=<?= $h($selectedCategory) ?>">Clear</a><?php endif; ?>
  </form>

  <nav class="adm-media-tabs" aria-label="Media categories">
    <?php foreach ($mediaCategories as $key => $cat): ?>
      <a class="<?= $selectedCategory === $key ? 'act' : '' ?>" href="/admin/media?category=<?= $h($key) ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>">
        <span><?= $h($cat['icon']) ?></span><b><?= $h($cat['label']) ?></b><em><?= number_format((int)($mediaCounts[$key] ?? 0)) ?></em>
      </a>
    <?php endforeach; ?>
  </nav>

  <?php if (!$mediaItems): ?>
    <div class="adm-media-empty">No media files found for this filter.</div>
  <?php else: ?>
    <section class="adm-media-grid">
      <?php foreach ($mediaItems as $item): ?>
        <article class="adm-media-card">
          <a class="adm-media-preview adm-media-preview--<?= $h($item['type']) ?>" href="<?= $h($item['url']) ?>" target="_blank" rel="noopener">
            <?php if ($item['type'] === 'image'): ?>
              <img src="<?= $h($item['url']) ?>" alt="<?= $h($item['name']) ?>" loading="lazy">
            <?php elseif ($item['type'] === 'video'): ?>
              <video src="<?= $h($item['url']) ?>" muted preload="metadata"></video><span>▶</span>
            <?php else: ?>
              <span><?= $h($item['ext']) ?></span>
            <?php endif; ?>
          </a>
          <div class="adm-media-info">
            <strong title="<?= $h($item['name']) ?>"><?= $h($item['name']) ?></strong>
            <small><?= $h($mediaCategories[$item['category']]['label'] ?? ucfirst($item['category'])) ?> · <?= $h($item['size']) ?> · <?= date('d M Y', $item['mtime']) ?></small>
            <code title="<?= $h($item['url']) ?>"><?= $h($item['url']) ?></code>
          </div>
          <div class="adm-media-actions">
            <a href="<?= $h($item['url']) ?>" target="_blank" rel="noopener">View</a>
            <a href="<?= $h($item['url']) ?>" download>Download</a>
            <button type="button" onclick='copyMediaUrl(<?= json_encode($item['url'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>Copy URL</button>
            <button class="adm-media-delete" type="button" onclick='deleteMediaFile(this, <?= json_encode($item['relative'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>, <?= json_encode($item['name'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>Delete</button>
          </div>
        </article>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>
</div>

<script>
function mediaToast(message, type = 'success') {
  const w = document.getElementById('tw');
  if (!w) { alert(message); return; }
  const t = document.createElement('div');
  t.className = `toast ${type}`;
  t.textContent = message;
  w.appendChild(t);
  requestAnimationFrame(() => requestAnimationFrame(() => t.classList.add('show')));
  setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 2600);
}
function copyMediaUrl(url) {
  navigator.clipboard?.writeText(url).then(() => mediaToast('Media URL copied'));
}
async function deleteMediaFile(button, relativePath, fileName) {
  const confirmed = window.confirm(`Delete ${fileName}?\n\nFor safety, the file will be moved to /uploads/.trash so it can be restored if needed.`);
  if (!confirmed) return;
  button.disabled = true;
  button.textContent = 'Deleting…';
  try {
    const resp = await fetch('/admin/api/media/delete', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({path: relativePath})
    });
    const data = await resp.json();
    if (!data.ok) throw new Error(data.msg || 'Could not delete media file');
    button.closest('.adm-media-card')?.remove();
    mediaToast(data.msg || 'Media file deleted');
  } catch (err) {
    button.disabled = false;
    button.textContent = 'Delete';
    mediaToast(err.message || 'Could not delete media file', 'error');
  }
}
</script>

    </div></div></div>
</body></html>
