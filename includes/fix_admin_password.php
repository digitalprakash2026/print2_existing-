<?php
/**
 * One-time admin password reset tool.
 * Visit: /includes/fix_admin_password.php?run=yes
 * Delete this file after use.
 */
if (($_GET['key'] ?? '') !== 'rcsfix2024' && ($_GET['run'] ?? '') !== 'yes') {
    die('<h2 style="font-family:sans-serif;padding:30px">Add ?run=yes to reset admin password, or ?key=rcsfix2024</h2>');
}

error_reporting(E_ALL);
ini_set('display_errors', '1');

$rootPath = dirname(__DIR__);
$envFile  = $rootPath . '/.env';
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
<style>body{font-family:sans-serif;background:#f9fafb;padding:30px;max-width:600px;margin:auto}.card{background:white;border-radius:12px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,.08);margin-bottom:16px}h2{color:#1A56E8;margin-top:0}.err{color:#DC2626;font-weight:700}.btn{display:inline-block;background:#1A56E8;color:white;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;margin-top:8px}</style>
</head><body>';

try {
    $pdo = new PDO("mysql:host={$dbH};dbname={$dbN};charset=utf8mb4", $dbU, $dbP, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $newPass = 'admin@123';
    $newHash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 10]);

    $existing = $pdo->query("SELECT id FROM admin_users")->fetchAll(PDO::FETCH_ASSOC);
    if ($existing) {
        foreach ($existing as $a) {
            $pdo->prepare("UPDATE admin_users SET password=?, is_active=1 WHERE id=?")->execute([$newHash, $a['id']]);
        }
    } else {
        $pdo->prepare("INSERT INTO admin_users (name,email,password,role,is_active,created_at) VALUES (?,?,?,?,1,NOW())")
            ->execute(['Admin', 'admin@rcsgraphic.in', $newHash, 'super']);
    }

    echo '<div class="card"><h2>✅ Admin reset complete</h2><p>Email: <code>admin@rcsgraphic.in</code><br>Password: <code>admin@123</code></p><a href="/admin/login" class="btn">Go to Admin Login</a></div>';
} catch (PDOException $e) {
    echo '<div class="card"><h2 class="err">DB Error</h2><p>' . htmlspecialchars($e->getMessage()) . '</p><p>Check <code>/.env</code> credentials in project root.</p></div>';
}

echo '<div class="card" style="border:2px solid #DC2626"><h2 style="color:#DC2626">Delete this file now</h2></div></body></html>';
