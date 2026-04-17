<?php
// ═══════════════════════════════════════════════════════════════
//  RCS Graphic — Hostinger Debug Tool
//  Yeh file: public_html/debug.php
//  Visit: print.rcsgraphic.com/debug.php?key=rcsdebug
//  IMPORTANT: Kaam hone ke baad DELETE kar dena!
// ═══════════════════════════════════════════════════════════════

if (($_GET['key'] ?? '') !== 'rcsdebug') {
    die('<h2 style="font-family:sans-serif;padding:30px">URL mein ?key=rcsdebug add karo</h2>');
}

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Path calculate karo
$publicHtml = __DIR__;  // ye file public_html/ mein hai
// Hostinger subdomain structure:
// /home/u123456789/domains/print.rcsgraphic.com/public_html/  ← yahan hai yeh file
// /home/u123456789/rcs_app/  ← yahan hona chahiye app

// 3 levels upar jao: public_html → print.rcsgraphic.com → domains → u123456789
$homeDir = dirname(dirname(dirname($publicHtml)));
$appRoot = $homeDir . '/rcs_app';

// Backup paths try karte hain
$possibleAppRoots = [
    $homeDir . '/rcs_app',
    dirname($homeDir) . '/rcs_app',
    dirname($publicHtml) . '/rcs_app',
    dirname(dirname($publicHtml)) . '/rcs_app',
];

$foundAppRoot = null;
foreach ($possibleAppRoots as $path) {
    if (is_dir($path . '/config')) {
        $foundAppRoot = $path;
        break;
    }
}

$ok   = '<span style="color:#22c55e;font-weight:700">✅ OK</span>';
$fail = '<span style="color:#ef4444;font-weight:700">❌ ERROR</span>';
$warn = '<span style="color:#f59e0b;font-weight:700">⚠️ WARN</span>';
?>
<!DOCTYPE html>
<html><head>
<meta charset="UTF-8">
<title>RCS Debug — Hostinger</title>
<style>
* { box-sizing: border-box; }
body { font-family: 'Courier New', monospace; background: #0f172a; color: #e2e8f0; padding: 20px; font-size: 13px; margin: 0; }
h1 { color: #38bdf8; font-size: 20px; margin-bottom: 4px; }
h2 { color: #64748b; font-size: 13px; font-weight: 400; margin-bottom: 20px; }
.card { background: #1e293b; border-radius: 10px; padding: 16px; margin-bottom: 14px; }
.card h3 { color: #38bdf8; font-size: 11px; text-transform: uppercase; letter-spacing: .07em; margin: 0 0 12px; }
.row { display: flex; justify-content: space-between; align-items: flex-start; padding: 6px 0; border-bottom: 1px solid #334155; gap: 12px; }
.row:last-child { border: none; }
.lbl { color: #94a3b8; flex-shrink: 0; }
.val { text-align: right; word-break: break-all; }
.fix { background: #1e1b4b; border: 1px solid #4338ca; border-radius: 6px; padding: 12px; margin-top: 10px; color: #a5b4fc; line-height: 1.7; }
.fix code { background: #312e81; padding: 2px 6px; border-radius: 4px; color: #c7d2fe; }
pre { background: #020617; border-radius: 6px; padding: 12px; overflow-x: auto; color: #86efac; font-size: 12px; line-height: 1.6; border: 1px solid #1e3a5f; }
.big { font-size: 18px; font-weight: 700; color: #38bdf8; }
.success-box { background: #052e16; border: 1px solid #166534; border-radius: 8px; padding: 14px; margin-bottom: 14px; color: #86efac; }
.error-box { background: #1c0a0a; border: 1px solid #991b1b; border-radius: 8px; padding: 14px; margin-bottom: 14px; color: #fca5a5; }
</style>
</head>
<body>

<h1>🔧 RCS Graphic — Hostinger Debug</h1>
<h2>print.rcsgraphic.com — Har cheez check karo, phir delete karo yeh file!</h2>

<!-- PATH INFO -->
<div class="card">
<h3>📁 Path Information</h3>
<div class="row"><span class="lbl">Yeh file ka path</span><span class="val"><?= $publicHtml ?></span></div>
<div class="row"><span class="lbl">Home directory</span><span class="val"><?= $homeDir ?></span></div>
<div class="row"><span class="lbl">rcs_app folder expected at</span><span class="val"><?= $homeDir ?>/rcs_app</span></div>
<div class="row"><span class="lbl">rcs_app FOUND at</span><span class="val"><?= $foundAppRoot ? ($ok . ' ' . $foundAppRoot) : ($fail . ' Koi bhi path mein nahi mila!') ?></span></div>
</div>

<?php if (!$foundAppRoot): ?>
<div class="error-box">
<strong>❌ rcs_app folder nahi mila!</strong><br><br>
Aapko yeh karna hai:<br>
1. Hostinger File Manager mein jaao<br>
2. <code><?= $homeDir ?>/</code> folder mein jaao (domains ke saath same level pe)<br>
3. Wahan <strong>rcs_app</strong> naam ka folder banao<br>
4. Usme saari files upload karo (config, src, templates, api, admin, .env)
</div>
<?php else: ?>

<!-- PHP VERSION -->
<div class="card">
<h3>🐘 PHP</h3>
<div class="row"><span class="lbl">PHP Version</span><span class="val"><?= PHP_VERSION ?> <?= version_compare(PHP_VERSION, '8.0', '>=') ? $ok : $fail . ' — 8.0+ chahiye' ?></span></div>
<?php foreach (['pdo','pdo_mysql','mbstring','curl','json','fileinfo','openssl'] as $ext): ?>
<div class="row"><span class="lbl">ext-<?= $ext ?></span><span class="val"><?= extension_loaded($ext) ? $ok : $fail ?></span></div>
<?php endforeach; ?>
</div>

<!-- FILES CHECK -->
<div class="card">
<h3>📄 Files Check</h3>
<?php
$checkFiles = [
    'config/config.php'      => $foundAppRoot . '/config/config.php',
    'src/Database.php'       => $foundAppRoot . '/src/Database.php',
    'src/Auth/Auth.php'      => $foundAppRoot . '/src/Auth/Auth.php',
    'src/Cart/Cart.php'      => $foundAppRoot . '/src/Cart/Cart.php',
    'src/Cart/Pricing.php'   => $foundAppRoot . '/src/Cart/Pricing.php',
    'src/Catalog/ProductCatalog.php' => $foundAppRoot . '/src/Catalog/ProductCatalog.php',
    'src/Orders/OrderManager.php'   => $foundAppRoot . '/src/Orders/OrderManager.php',
    'src/Payment/Razorpay.php'      => $foundAppRoot . '/src/Payment/Razorpay.php',
    'src/Email/Mailer.php'           => $foundAppRoot . '/src/Email/Mailer.php',
    'src/Sheets/SheetsSync.php'      => $foundAppRoot . '/src/Sheets/SheetsSync.php',
    'api/router.php'         => $foundAppRoot . '/api/router.php',
    'admin/router.php'       => $foundAppRoot . '/admin/router.php',
    'templates/home.php'     => $foundAppRoot . '/templates/home.php',
    'templates/product.php'  => $foundAppRoot . '/templates/product.php',
    '.env file'              => $foundAppRoot . '/.env',
    'public_html/.htaccess'  => $publicHtml . '/.htaccess',
    'public_html/index.php'  => $publicHtml . '/index.php',
    'public_html/css/app.css'=> $publicHtml . '/css/app.css',
    'public_html/js/app.js'  => $publicHtml . '/js/app.js',
];
foreach ($checkFiles as $label => $path):
    $exists = file_exists($path);
?>
<div class="row">
  <span class="lbl"><?= $label ?></span>
  <span class="val"><?= $exists ? $ok : ($fail . ' — NOT FOUND') ?></span>
</div>
<?php endforeach; ?>
</div>

<!-- ENV FILE -->
<div class="card">
<h3>⚙️ .env File</h3>
<?php
$envFile = $foundAppRoot . '/.env';
if (!file_exists($envFile)): ?>
<div class="fix">
❌ <strong>.env file nahi mila!</strong><br><br>
<code><?= $foundAppRoot ?>/.env</code> pe create karo yeh content ke saath:<br><br>
<pre>DB_HOST=localhost
DB_NAME=u123456789_rcsgraphic
DB_USER=u123456789_rcsuser
DB_PASS=AapkaPassword
APP_URL=https://print.rcsgraphic.com
APP_DEBUG=false
APP_SECRET=random_64_char_string_here</pre>
</div>
<?php else:
    $envVars = [];
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (!$line || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $envVars[trim($k)] = trim($v, " \t\"'");
    }
    foreach (['DB_HOST','DB_NAME','DB_USER','DB_PASS','APP_URL','APP_SECRET'] as $key):
        $val = $envVars[$key] ?? '';
        $present = $val !== '';
        $isSensitive = in_array($key, ['DB_PASS','APP_SECRET']);
        $display = $isSensitive ? str_repeat('*', min(8, strlen($val))) : htmlspecialchars(substr($val, 0, 50));
?>
<div class="row">
  <span class="lbl"><?= $key ?></span>
  <span class="val"><?= $present ? ($ok . ' ' . $display) : ($fail . ' — .env mein nahi hai!') ?></span>
</div>
<?php endforeach;
    if (($envVars['DB_HOST'] ?? '') === '127.0.0.1'):?>
<div class="fix">⚠️ <strong>DB_HOST galat hai!</strong> Hostinger mein <code>DB_HOST=localhost</code> hona chahiye, <code>127.0.0.1</code> nahi.</div>
<?php endif; ?>
<?php endif; ?>
</div>

<!-- DATABASE TEST -->
<div class="card">
<h3>🗄️ Database Connection Test</h3>
<?php
if (file_exists($envFile)) {
    $envVars2 = [];
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (!$line || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $envVars2[trim($k)] = trim($v, " \t\"'");
    }
    $dbH = $envVars2['DB_HOST'] ?? 'localhost';
    $dbN = $envVars2['DB_NAME'] ?? '';
    $dbU = $envVars2['DB_USER'] ?? '';
    $dbP = $envVars2['DB_PASS'] ?? '';

    echo '<div class="row"><span class="lbl">Host</span><span class="val">' . htmlspecialchars($dbH) . '</span></div>';
    echo '<div class="row"><span class="lbl">Database</span><span class="val">' . htmlspecialchars($dbN) . '</span></div>';
    echo '<div class="row"><span class="lbl">User</span><span class="val">' . htmlspecialchars($dbU) . '</span></div>';

    try {
        $pdo = new PDO(
            "mysql:host={$dbH};dbname={$dbN};charset=utf8mb4",
            $dbU, $dbP,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]
        );
        echo '<div class="row"><span class="lbl">Connection</span><span class="val">' . $ok . ' Connected!</span></div>';

        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        foreach (['users','products','orders','settings','categories','coupons'] as $t) {
            echo '<div class="row"><span class="lbl">Table: ' . $t . '</span><span class="val">' . (in_array($t, $tables) ? $ok : $fail . ' — database.sql import karo!') . '</span></div>';
        }
        $count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
        echo '<div class="row"><span class="lbl">Products count</span><span class="val">' . ($count > 0 ? $ok . ' ' . $count . ' products' : $warn . ' 0 products — seed data check karo') . '</span></div>';
    } catch (\PDOException $e) {
        echo '<div class="row"><span class="lbl">Connection</span><span class="val">' . $fail . '</span></div>';
        echo '<div class="fix">
        ❌ <strong>DB Error:</strong> <code>' . htmlspecialchars($e->getMessage()) . '</code><br><br>
        <strong>Fix karo:</strong><br>
        • <code>DB_HOST=localhost</code> hona chahiye<br>
        • hPanel → Databases → MySQL mein database aur user ka exact naam check karo<br>
        • Database user ko database ka access diya hai? (MySQL Users → Assign to Database)<br>
        • Password sahi hai?
        </div>';
    }
} else {
    echo '<div class="fix">❌ .env file nahi mili, database test nahi ho sakta</div>';
}
?>
</div>

<!-- PERMISSIONS -->
<div class="card">
<h3>🔒 Folder Permissions</h3>
<?php
$checkDirs = [
    'public_html/uploads'          => $publicHtml . '/uploads',
    'public_html/uploads/artwork'  => $publicHtml . '/uploads/artwork',
    'rcs_app/logs'                 => $foundAppRoot . '/logs',
];
foreach ($checkDirs as $label => $path):
    if (!is_dir($path)) @mkdir($path, 0755, true);
?>
<div class="row">
  <span class="lbl"><?= $label ?></span>
  <span class="val"><?= is_dir($path) ? ($ok . ' Exists') : ($warn . ' Creating...') ?>  <?= is_writable($path) ? '· Writable' . $ok : '· NOT Writable ' . $fail ?></span>
</div>
<?php endforeach; ?>
</div>

<?php endif; ?>

<!-- FINAL CHECKLIST -->
<div class="card" style="border: 2px solid #2563eb">
<h3>✅ Final Checklist — Yeh sab hona chahiye</h3>
<pre style="color:#e2e8f0">
HOSTINGER FOLDER STRUCTURE:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━
/home/u123456789/
│
├── rcs_app/                    ← Yahan upload karo (bahar!)
│   ├── .env                    ← DB credentials
│   ├── config/
│   │   └── config.php
│   ├── src/
│   │   ├── Database.php
│   │   ├── Auth/Auth.php
│   │   ├── Cart/Cart.php
│   │   ├── Cart/Pricing.php
│   │   ├── Catalog/ProductCatalog.php
│   │   ├── Orders/OrderManager.php
│   │   ├── Orders/AdminAudit.php
│   │   ├── Payment/Razorpay.php
│   │   ├── Email/Mailer.php
│   │   ├── Sheets/SheetsSync.php
│   │   └── Invoice/InvoiceGenerator.php
│   ├── templates/              ← sabhi .php templates
│   ├── api/router.php
│   └── admin/router.php
│
└── domains/
    └── print.rcsgraphic.com/
        └── public_html/        ← Yahan upload karo (web root)
            ├── index.php       ← ✅ REPLACED version
            ├── .htaccess       ← ✅ REPLACED version
            ├── css/app.css
            ├── js/app.js
            └── uploads/
                └── artwork/
</pre>
</div>

<p style="color:#475569;font-size:11px;margin-top:16px">⚠️ Yeh file DELETE karo jab sab kuch kaam karne lage! Security risk hai.</p>
</body></html>
