<?php
/**
 * fix_admin_password.php — One-time admin password reset tool
 * Upload to public/ → visit URL → THEN DELETE THIS FILE.
 * Visit: yourdomain.com/fix_admin_password.php?run=yes
 */
if (($_GET['key'] ?? '') !== 'rcsfix2024' && ($_GET['run'] ?? '') !== 'yes') {
    die('<h2 style="font-family:sans-serif;padding:30px">Add ?run=yes to reset admin password, or ?key=rcsfix2024</h2>');
}

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Find app root and load config
$publicHtml = __DIR__;
foreach ([
    dirname(dirname(dirname($publicHtml))) . '/rcs_app',
    dirname($publicHtml) . '/rcs_app',
    dirname(dirname($publicHtml)) . '/rcs_app',
    $publicHtml . '/../../rcs_app',
] as $path) {
    if (is_dir($path . '/config')) { $appRoot = $path; break; }
}

// Load .env manually if found
$envFile = ($appRoot ?? dirname($publicHtml)) . '/.env';
$env = [];
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (!$line || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim($v, " \t\"'");
    }
}

$dbH = $env['DB_HOST'] ?? 'localhost';
$dbN = $env['DB_NAME'] ?? '';
$dbU = $env['DB_USER'] ?? '';
$dbP = $env['DB_PASS'] ?? '';

echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Admin Fix</title>
<style>body{font-family:sans-serif;background:#f9fafb;padding:30px;max-width:600px;margin:auto}
.card{background:white;border-radius:12px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,.08);margin-bottom:16px}
h2{color:#1A56E8;margin-top:0}.ok{color:#059669;font-weight:700}.err{color:#DC2626;font-weight:700}
pre{background:#f3f4f6;padding:12px;border-radius:6px;font-size:13px}
.btn{display:inline-block;background:#1A56E8;color:white;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;margin-top:8px}</style>
</head><body>';

try {
    $pdo = new PDO("mysql:host={$dbH};dbname={$dbN};charset=utf8mb4", $dbU, $dbP,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    echo '<div class="card"><h2>✅ Database Connected</h2></div>';

    // Generate fresh hash — works on all PHP 7.4+ versions
    $newPass  = 'admin@123';
    $newHash  = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 10]);
    $verified = password_verify($newPass, $newHash);

    if (!$verified) {
        echo '<div class="card"><h2 class="err">❌ Hash verification failed on this server</h2>
        <p>PHP version: ' . PHP_VERSION . '</p></div>';
    } else {
        $existing = $pdo->query("SELECT id, email FROM admin_users")->fetchAll(PDO::FETCH_ASSOC);

        if ($existing) {
            foreach ($existing as $a) {
                $pdo->prepare("UPDATE admin_users SET password=?, is_active=1 WHERE id=?")
                    ->execute([$newHash, $a['id']]);
            }
            echo '<div class="card"><h2>✅ Admin Password Reset</h2>
            <p>Updated ' . count($existing) . ' admin account(s).</p></div>';
        } else {
            $pdo->prepare("INSERT INTO admin_users (name,email,password,role,is_active,created_at) VALUES (?,?,?,?,1,NOW())")
                ->execute(['Admin', 'admin@rcsgraphic.in', $newHash, 'super']);
            echo '<div class="card"><h2>✅ Admin Account Created</h2></div>';
        }

        // Ensure design_fee setting exists
        $pdo->prepare("INSERT IGNORE INTO settings (`key`,value) VALUES ('design_fee','0')")->execute();

        echo '<div class="card" style="border:2px solid #059669">
        <h2 style="color:#059669">🎉 Done — New Login Details</h2>
        <table style="width:100%;border-collapse:collapse;font-size:14px">
        <tr style="background:#f0fdf4"><th style="padding:9px;text-align:left">Email</th><th style="padding:9px;text-align:left">Password</th></tr>
        <tr><td style="padding:9px;font-family:monospace">admin@rcsgraphic.in</td><td style="padding:9px;font-family:monospace;color:#1A56E8;font-weight:700">admin@123</td></tr>
        </table>
        <br><a href="/admin/login" class="btn">→ Go to Admin Login</a>
        </div>';
    }
} catch (PDOException $e) {
    echo '<div class="card"><h2 class="err">❌ DB Error</h2><pre>' . htmlspecialchars($e->getMessage()) . '</pre>
    <p>Check rcs_app/.env — DB_HOST should be <strong>localhost</strong> on Hostinger.</p></div>';
}

echo '<div class="card" style="border:2px solid #DC2626">
<h2 style="color:#DC2626">⚠️ DELETE THIS FILE NOW</h2>
<p>Remove <code>fix_admin_password.php</code> from your server immediately.</p>
</div></body></html>';
