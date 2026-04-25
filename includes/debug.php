<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}

require_once dirname(__DIR__) . '/config/config.php';

$root = dirname(__DIR__);
$ok = static fn(bool $v): string => $v ? 'OK' : 'MISSING';
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

echo "RCS Unified Structure Debug\n";
echo "Root: {$root}\n\n";

foreach ($checks as $label => $path) {
    $exists = file_exists($path) || is_dir($path);
    echo str_pad($label, 28) . ' ' . $ok($exists) . "\n";
}

echo "\nThis script is CLI-only. Remove after troubleshooting.\n";
