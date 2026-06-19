<?php
// ─────────────────────────────────────────────────────────────
//  RCS Graphic — Unified config
//  Root structure compatible with shared hosting
// ─────────────────────────────────────────────────────────────

declare(strict_types=1);

$rootPath = dirname(__DIR__); // project root

define('APP_ROOT',     $rootPath);
define('BASE_PATH',    $rootPath);
define('APP_PATH',     $rootPath . '/app');
define('PUBLIC_PATH',  $rootPath);
define('UPLOAD_PATH',  $rootPath . '/uploads');
define('SRC_PATH',     APP_PATH . '/src');
define('TMPL_PATH',    $rootPath . '/templates');
define('CONFIG_PATH',  $rootPath . '/config');
define('INCLUDE_PATH', $rootPath . '/includes');
define('ADMIN_PATH',   $rootPath . '/admin');
define('LOG_PATH',     APP_PATH . '/logs');

// ── .env Load ────────────────────────────────────────────────
$envFile = $rootPath . '/.env';
if (!file_exists($envFile)) {
    $envFile = dirname($rootPath) . '/.env';
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

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $v = $_ENV[$key] ?? getenv($key);
        if ($v === false || $v === null || $v === '') return $default;
        return $v;
    }
}

// ── Database Config ───────────────────────────────────────────
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_NAME', env('DB_NAME', 'u330769761_printing'));
define('DB_USER', env('DB_USER', 'u330769761_printing'));
define('DB_PASS', env('DB_PASS', 'Printing@2026'));

// ── App Config ────────────────────────────────────────────────
define('APP_URL',    rtrim(env('APP_URL', 'https://print.rcsgraphic.com'), '/'));
define('APP_SECRET', env('APP_SECRET', 'change_me_now_32chars_minimum'));
define('APP_DEBUG',  env('APP_DEBUG', 'false') === 'true');
define('APP_TIMEZONE', env('APP_TIMEZONE', 'Asia/Kolkata'));
define('DB_TIMEZONE', env('DB_TIMEZONE', '+05:30'));

try {
    date_default_timezone_set(APP_TIMEZONE);
} catch (Throwable) {
    date_default_timezone_set('Asia/Kolkata');
}

if (!function_exists('app_datetime')) {
    function app_datetime(string|null $value, string $format = 'd M Y, H:i'): string
    {
        $raw = trim((string)$value);
        if ($raw === '') return '';
        try {
            return (new DateTimeImmutable($raw, new DateTimeZone(APP_TIMEZONE)))->format($format);
        } catch (Throwable) {
            $ts = strtotime($raw);
            return $ts ? date($format, $ts) : '';
        }
    }
}

if (!function_exists('app_timestamp')) {
    function app_timestamp(string|null $value): int
    {
        $raw = trim((string)$value);
        if ($raw === '') return time();
        try {
            return (new DateTimeImmutable($raw, new DateTimeZone(APP_TIMEZONE)))->getTimestamp();
        } catch (Throwable) {
            return strtotime($raw) ?: time();
        }
    }
}

// ── Required directories ──────────────────────────────────────
foreach ([
    LOG_PATH,
    UPLOAD_PATH,
    UPLOAD_PATH . '/artwork',
] as $dir) {
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
}

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', LOG_PATH . '/error.log');
}

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

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

spl_autoload_register(function (string $class): void {
    $file = SRC_PATH . DIRECTORY_SEPARATOR
          . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    if (file_exists($file)) require_once $file;
});
