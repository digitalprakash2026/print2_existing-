<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';

$debugKey = (string) env('DEBUG_ACCESS_KEY', 'rcsdebug');
$providedKey = (string) ($_GET['key'] ?? '');

if (!APP_DEBUG) {
    http_response_code(404);
    exit;
}

if ($providedKey === '' || !hash_equals($debugKey, $providedKey)) {
    die('<h2 style="font-family:sans-serif;padding:30px">Use a valid debug key.</h2>');
}

$root = dirname(__DIR__);
$ok = static fn(bool $v): string => $v ? '✅' : '❌';
$checks = [
    'index.php' => $root . '/index.php',
    'config/config.php' => $root . '/config/config.php',
    'app/src/Database.php' => $root . '/app/src/Database.php',
    'app/api/router.php' => $root . '/app/api/router.php',
    'admin/router.php' => $root . '/admin/router.php',
    'templates/home.php' => $root . '/templates/home.php',
    'includes/partials/head.php' => $root . '/includes/partials/head.php',
    'assets/css/app.css' => $root . '/assets/css/app.css',
    'assets/js/app.js' => $root . '/assets/js/app.js',
    'uploads/' => $root . '/uploads',
    '.env (optional)' => $root . '/.env',
];
?><!doctype html><html><head><meta charset="utf-8"><title>RCS Debug</title>
<style>body{font-family:system-ui;background:#0b1020;color:#e5e7eb;padding:24px}table{width:100%;border-collapse:collapse}td,th{padding:8px;border-bottom:1px solid #243044}.p{font-family:monospace;word-break:break-all}</style></head><body>
<h2>RCS Unified Structure Debug</h2>
<p>Root: <span class="p"><?= htmlspecialchars($root) ?></span></p>
<table><tr><th align="left">Item</th><th align="left">Status</th><th align="left">Path</th></tr>
<?php foreach ($checks as $label => $path): $exists = file_exists($path) || is_dir($path); ?>
<tr><td><?= htmlspecialchars($label) ?></td><td><?= $ok($exists) ?></td><td class="p"><?= htmlspecialchars($path) ?></td></tr>
<?php endforeach; ?></table>
<p style="margin-top:18px;color:#fca5a5">Delete this debug file after verification.</p>
</body></html>
