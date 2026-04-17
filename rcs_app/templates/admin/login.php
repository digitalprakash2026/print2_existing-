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
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:ital,opsz,wght@0,9..144,600;0,9..144,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/app.css">
<style>
  body { background:linear-gradient(135deg,#EEF3FD,#F9FAFB,#FFF4ED); min-height:100vh; margin:0; }
</style>
</head>
<body>
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:36px 18px">
  <div class="auth-card">

    <div class="auth-mark">R</div>
    <div class="auth-t">Admin Login</div>
    <div class="auth-s">RCS Graphic Control Panel</div>

    <?php if (!empty($loginError)): ?>
    <div style="background:var(--red-bg);border:1px solid var(--red-mid);border-radius:8px;padding:11px 14px;
                font-size:13px;color:var(--red);text-align:center;margin-bottom:14px;font-weight:600">
      ⚠️ <?= htmlspecialchars($loginError) ?>
    </div>
    <?php endif; ?>

    <!--
      FIX: Form posts to /admin/login. No CSRF check on this endpoint —
      the session-based admin auth is the security mechanism here.
      CSRF tokens are used for state-changing API calls, not login forms.
    -->
    <form method="POST" action="/admin/login" autocomplete="on">
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
               placeholder="Your admin password"
               required autocomplete="current-password">
      </div>
      <button type="submit" class="btn btn-blue btn-full"
              style="padding:13px;border-radius:10px;font-size:15px;margin-top:4px">
        Sign In →
      </button>
    </form>

    <div style="margin-top:20px;padding:12px;background:var(--bg2);border-radius:8px;
                font-size:12px;color:var(--text3);text-align:center;line-height:1.7">
      Default: <strong style="color:var(--text2)">admin@rcsgraphic.in</strong> /
      <strong style="color:var(--text2)">admin123</strong><br>
      <em>Change password in Admin → Settings after first login</em>
    </div>

    <div style="text-align:center;margin-top:14px">
      <a href="/" style="color:var(--text3);font-size:12px;text-decoration:none">← Back to Store</a>
    </div>

  </div>
</div>
</body>
</html>
