<?php
/**
 * admin/login.php — Admin Login Page
 * FIX: $csrf generated here if not already set (session may not have started via full view chain)
 */
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$csrf = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login — RCS Graphic</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/app.css">
<style>
  :root { --admin-login-purple:#4b148c; --admin-login-orange:#ff7a18; --admin-login-blue:#2563eb; }
  * { box-sizing:border-box; }
  body {
    min-height:100vh;
    margin:0;
    font-family:'Poppins',system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
    background:
      radial-gradient(circle at 16% 18%, rgba(255,122,24,.28), transparent 28%),
      radial-gradient(circle at 86% 14%, rgba(37,99,235,.24), transparent 30%),
      linear-gradient(135deg,#25084f 0%, #4b148c 46%, #111827 100%);
    color:#0f172a;
  }
  .admin-login-shell { min-height:100vh; display:grid; place-items:center; padding:42px 18px; position:relative; overflow:hidden; }
  .admin-login-shell::before,.admin-login-shell::after { content:''; position:absolute; border-radius:999px; pointer-events:none; }
  .admin-login-shell::before { width:360px; height:360px; left:-110px; bottom:-120px; background:rgba(255,122,24,.20); filter:blur(6px); }
  .admin-login-shell::after { width:270px; height:270px; right:-70px; top:-70px; background:rgba(255,255,255,.14); filter:blur(4px); }
  .admin-login-card {
    width:min(100%, 520px);
    position:relative;
    z-index:1;
    padding:42px;
    border:1px solid rgba(255,255,255,.34);
    border-radius:30px;
    background:linear-gradient(180deg,rgba(255,255,255,.98),rgba(255,255,255,.92));
    box-shadow:0 34px 90px rgba(5,9,25,.34);
  }
  .admin-login-brand { display:flex; align-items:center; gap:15px; margin-bottom:28px; }
  .admin-login-mark { width:62px; height:62px; display:grid; place-items:center; border-radius:20px; color:#fff; font-size:29px; font-weight:800; background:linear-gradient(135deg,var(--admin-login-purple),var(--admin-login-orange)); box-shadow:0 16px 34px rgba(75,20,140,.28); }
  .admin-login-brand small { display:block; color:#64748b; font-size:12px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }
  .admin-login-brand strong { display:block; margin-top:3px; color:#0f172a; font-size:24px; line-height:1.05; letter-spacing:-.04em; }
  .admin-login-title { margin:0 0 24px; color:#475569; font-size:14px; line-height:1.7; font-weight:600; }
  .admin-login-error { margin-bottom:18px; padding:13px 15px; border:1px solid #fecaca; border-radius:14px; background:#fff1f2; color:#b91c1c; font-size:13px; text-align:center; font-weight:800; }
  .admin-login-form { display:grid; gap:17px; }
  .admin-login-form .fg { margin:0; }
  .admin-login-form label { display:block; margin-bottom:7px; color:#0f172a; font-size:13px; font-weight:900; }
  .admin-login-form .fi { width:100%; min-height:54px; border:1px solid #dbe4f0; border-radius:16px; padding:0 16px; background:#f8fafc; font:inherit; font-size:14px; font-weight:700; color:#0f172a; transition:border-color .18s ease, box-shadow .18s ease, background .18s ease; }
  .admin-login-form .fi:focus { outline:none; border-color:var(--admin-login-purple); background:#fff; box-shadow:0 0 0 4px rgba(75,20,140,.12); }
  .admin-login-submit { min-height:56px; width:100%; border:0; border-radius:16px; color:#fff; background:linear-gradient(135deg,var(--admin-login-purple),var(--admin-login-blue)); font:inherit; font-size:15px; font-weight:900; cursor:pointer; box-shadow:0 18px 34px rgba(75,20,140,.24); }
  .admin-login-submit:hover { transform:translateY(-1px); box-shadow:0 22px 42px rgba(75,20,140,.30); }
  .admin-login-back { display:block; margin-top:22px; text-align:center; color:#64748b; font-size:12px; font-weight:800; text-decoration:none; }
  .admin-login-back:hover { color:var(--admin-login-purple); }
  @media(max-width:560px){ .admin-login-card{padding:30px 22px;border-radius:24px}.admin-login-brand strong{font-size:21px} }
</style>
</head>
<body>
<div class="admin-login-shell">
  <div class="admin-login-card">
    <div class="admin-login-brand">
      <div class="admin-login-mark">R</div>
      <div><small>RCS Graphic</small><strong>Admin Control Panel</strong></div>
    </div>
    <p class="admin-login-title">Sign in securely to manage orders, products, page heroes, portfolio work and website content.</p>

    <?php if (!empty($loginError)): ?>
    <div class="admin-login-error">⚠️ <?= htmlspecialchars($loginError) ?></div>
    <?php endif; ?>

    <form method="POST" action="/admin/login" autocomplete="on" class="admin-login-form">
      <div class="fg">
        <label>Email Address</label>
        <input type="email" name="email" class="fi"
               placeholder="admin@rcsgraphic.in"
               required autofocus autocomplete="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="fg">
        <label>Password</label>
        <input type="password" name="password" class="fi"
               placeholder="Enter admin password"
               required autocomplete="current-password">
      </div>
      <button type="submit" class="admin-login-submit">Sign In →</button>
    </form>

    <a class="admin-login-back" href="/">← Back to Store</a>
  </div>
</div>
</body>
</html>
