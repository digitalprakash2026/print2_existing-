<?php
// ═══════════════════════════════════════════════════════════════
//  RCS Graphic — config.php
//  Yeh file: rcs_app/config/config.php
// ═══════════════════════════════════════════════════════════════

declare(strict_types=1);

// ── App Root Path ─────────────────────────────────────────────
// config.php → rcs_app/config/  → dirname = rcs_app/
$appRoot = dirname(__DIR__);  // = /home/u123456789/rcs_app

define('APP_ROOT',    $appRoot);
define('BASE_PATH',   $appRoot);
define('PUBLIC_PATH', dirname(dirname($appRoot)) . '/domains/print.rcsgraphic.com/public_html');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');
define('SRC_PATH',    $appRoot . '/src');
define('TMPL_PATH',   $appRoot . '/templates');
define('CONFIG_PATH', $appRoot . '/config');

// ── .env Load Karo ────────────────────────────────────────────
// .env file: rcs_app/.env  (config ke saath same folder mein)
$envFile = $appRoot . '/.env';
if (!file_exists($envFile)) {
    // Fallback: ek level upar
    $envFile = dirname($appRoot) . '/.env';
}

if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$key, $val] = explode('=', $line, 2);
        $key = trim($key);
        $val = trim($val, " \t\n\r\0\x0B\"'");
        $_ENV[$key] = $val;
        putenv("{$key}={$val}");
    }
}

// ── env() Helper ─────────────────────────────────────────────
if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $v = $_ENV[$key] ?? getenv($key);
        if ($v === false || $v === null || $v === '') return $default;
        return $v;
    }
}

// ── Database Config ───────────────────────────────────────────
define('DB_HOST', env('DB_HOST', 'localhost'));  // Hostinger = localhost
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_NAME', env('DB_NAME', 'u330769761_printing'));
define('DB_USER', env('DB_USER', 'u330769761_printing'));
define('DB_PASS', env('DB_PASS', 'Printing@2026'));

// ── App Config ────────────────────────────────────────────────
define('APP_URL',    rtrim(env('APP_URL', 'https://print.rcsgraphic.com'), '/'));
define('APP_SECRET', env('APP_SECRET', 'change_me_now_32chars_minimum'));
define('APP_DEBUG',  env('APP_DEBUG', 'false') === 'true');

// ── Folders Banao ─────────────────────────────────────────────
foreach ([
    $appRoot . '/logs',
    UPLOAD_PATH . '/artwork',
] as $dir) {
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
}

// ── Error Handling ────────────────────────────────────────────
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', $appRoot . '/logs/error.log');
}

// ── Session Start ─────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400 * 30,
        'path'     => '/',
        'httponly' => true,
        'secure'   => isset($_SERVER['HTTPS']),
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ── CSRF Token ────────────────────────────────────────────────
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ── Class Autoloader ──────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $file = SRC_PATH . DIRECTORY_SEPARATOR
          . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    if (file_exists($file)) require_once $file;
});
