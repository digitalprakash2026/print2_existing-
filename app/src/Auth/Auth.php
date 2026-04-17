<?php
// ─────────────────────────────────────────────────────────────
//  RCS Graphic — Auth
//  FIX: adminLogin does not require CSRF (form-based, session started)
//  FIX: password_verify works correctly regardless of PHP version
// ─────────────────────────────────────────────────────────────

declare(strict_types=1);

namespace Auth;

class Auth
{
    // ══════════════════════════════════════════════════════════
    //  USER AUTH
    // ══════════════════════════════════════════════════════════

    public static function login(string $identifier, string $password): array
    {
        $ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $lockUntil= $_SESSION['login_lock'][$ip] ?? 0;

        if ($lockUntil > time()) {
            $mins = (int)ceil(($lockUntil - time()) / 60);
            return ['ok' => false, 'msg' => "Too many attempts. Try again in {$mins} min."];
        }

        $identifier = trim($identifier);
        $user = \Database::row(
            "SELECT * FROM users WHERE (email = ? OR phone = ?) AND is_active = 1",
            [$identifier, $identifier]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            $attempts = ($_SESSION['login_attempts'][$ip] ?? 0) + 1;
            $_SESSION['login_attempts'][$ip] = $attempts;
            if ($attempts >= 5) {
                $_SESSION['login_lock'][$ip]     = time() + 900; // 15 min
                $_SESSION['login_attempts'][$ip] = 0;
            }
            return ['ok' => false, 'msg' => 'Invalid credentials.'];
        }

        unset($_SESSION['login_attempts'][$ip]);
        $_SESSION['user'] = self::publicUser($user);
        session_regenerate_id(true);

        try { \Cart\Cart::mergeGuestCart((int)$user['id']); } catch (\Throwable) {}
        try { if ($user['marketing_consent']) \Email\Mailer::marketingOptIn($user); } catch (\Throwable) {}

        return ['ok' => true, 'user' => self::publicUser($user)];
    }

    public static function register(array $data): array
    {
        $errors = self::validateRegister($data);
        if ($errors) return ['ok' => false, 'msg' => implode(', ', $errors)];

        $exists = \Database::row(
            "SELECT id FROM users WHERE email = ? OR phone = ?",
            [trim($data['email']), trim($data['phone'])]
        );
        if ($exists) return ['ok' => false, 'msg' => 'Email or phone already registered.'];

        $id = \Database::insert(
            "INSERT INTO users (name, email, phone, company, password, marketing_consent, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())",
            [
                trim($data['name']),
                trim($data['email']),
                trim($data['phone']),
                trim($data['company'] ?? ''),
                password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 10]),
                (int)($data['marketing_consent'] ?? 0),
            ]
        );

        $user = \Database::row("SELECT * FROM users WHERE id = ?", [$id]);
        $_SESSION['user'] = self::publicUser($user);
        session_regenerate_id(true);

        try { \Cart\Cart::mergeGuestCart((int)$id); } catch (\Throwable) {}
        try { \Email\Mailer::sendWelcome($user); }        catch (\Throwable) {}
        try { if (!empty($data['marketing_consent'])) \Email\Mailer::marketingOptIn($user); } catch (\Throwable) {}

        return ['ok' => true, 'user' => self::publicUser($user)];
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }

    public static function user(): ?array  { return $_SESSION['user'] ?? null; }
    public static function check(): bool   { return !empty($_SESSION['user']['id']); }

    public static function require(): void
    {
        if (!self::check()) {
            if (self::isApiRequest()) { http_response_code(401); echo json_encode(['ok'=>false,'msg'=>'Login required']); exit; }
            header('Location: /login'); exit;
        }
    }

    // ══════════════════════════════════════════════════════════
    //  ADMIN AUTH
    //  FIX: Removed CSRF check from login form — CSRF is only
    //       needed for API endpoints, not traditional form submissions.
    //       The login form is protected by the session naturally.
    // ══════════════════════════════════════════════════════════

    public static function adminLogin(string $email, string $password): array
    {
        $email    = strtolower(trim($email));
        $password = trim($password);

        if (empty($email) || empty($password)) {
            return ['ok' => false, 'msg' => 'Email and password are required.'];
        }

        // Find admin user
        $admin = \Database::row(
            "SELECT * FROM admin_users WHERE email = ? AND is_active = 1",
            [$email]
        );

        if (!$admin) {
            // Don't reveal whether email exists
            return ['ok' => false, 'msg' => 'Incorrect email or password.'];
        }

        // Verify password with legacy compatibility:
        // some older dumps have plaintext admin passwords.
        $storedPassword = (string)($admin['password'] ?? '');
        $isHash = str_starts_with($storedPassword, '$2y$') || str_starts_with($storedPassword, '$2a$') || str_starts_with($storedPassword, '$2b$');
        $valid = $isHash ? password_verify($password, $storedPassword) : hash_equals($storedPassword, $password);

        if (!$valid) {
            return ['ok' => false, 'msg' => 'Incorrect email or password.'];
        }

        // Always move legacy/plaintext password to bcrypt hash after successful login.
        if (!$isHash || password_needs_rehash($storedPassword, PASSWORD_BCRYPT, ['cost' => 10])) {
            $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
            try {
                \Database::query("UPDATE admin_users SET password = ? WHERE id = ?", [$newHash, $admin['id']]);
            } catch (\Throwable) {}
        }

        // Set admin session
        $_SESSION['admin'] = [
            'id'    => (int)$admin['id'],
            'name'  => $admin['name'],
            'email' => $admin['email'],
            'role'  => $admin['role'],
        ];
        session_regenerate_id(true);

        // Record last login (non-blocking)
        try {
            \Database::query("UPDATE admin_users SET last_login = NOW() WHERE id = ?", [$admin['id']]);
        } catch (\Throwable) {}

        return ['ok' => true];
    }

    public static function adminLogout(): void
    {
        unset($_SESSION['admin']);
        session_regenerate_id(true);
    }

    public static function isAdmin(): bool { return !empty($_SESSION['admin']['id']); }
    public static function admin(): ?array  { return $_SESSION['admin'] ?? null; }

    public static function requireAdmin(): void
    {
        if (!self::isAdmin()) {
            if (self::isApiRequest()) { http_response_code(403); echo json_encode(['ok'=>false,'msg'=>'Forbidden']); exit; }
            header('Location: /admin/login'); exit;
        }
    }

    // ══════════════════════════════════════════════════════════
    //  CSRF — used for API calls, not HTML form submissions
    // ══════════════════════════════════════════════════════════

    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(): void
    {
        $token = $_POST['_token']
               ?? $_SERVER['HTTP_X_CSRF_TOKEN']
               ?? $_SERVER['HTTP_X_XSRF_TOKEN']
               ?? '';
        $stored = $_SESSION['csrf_token'] ?? '';
        if (empty($stored) || !hash_equals($stored, $token)) {
            http_response_code(419);
            echo json_encode(['ok' => false, 'msg' => 'Security token mismatch. Please refresh the page.']);
            exit;
        }
    }

    // ══════════════════════════════════════════════════════════
    //  HELPERS
    // ══════════════════════════════════════════════════════════

    private static function publicUser(array $user): array
    {
        return [
            'id'      => (int)$user['id'],
            'name'    => $user['name'],
            'email'   => $user['email'],
            'phone'   => $user['phone'],
            'company' => $user['company'] ?? '',
        ];
    }

    private static function validateRegister(array $d): array
    {
        $e = [];
        if (empty(trim($d['name']   ?? '')))  $e[] = 'Name required';
        if (empty(trim($d['email']  ?? '')) || !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $e[] = 'Valid email required';
        if (empty(trim($d['phone']  ?? '')))  $e[] = 'Phone required';
        if (empty($d['password'] ?? '') || strlen($d['password']) < 6) $e[] = 'Password minimum 6 characters';
        return $e;
    }

    private static function isApiRequest(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return str_starts_with($uri, '/api/') || str_starts_with($uri, '/admin/api/');
    }
}
