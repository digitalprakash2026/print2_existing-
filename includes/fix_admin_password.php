<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}

require_once dirname(__DIR__) . '/config/config.php';

$providedKey = $argv[1] ?? '';
$expectedKey = (string) env('ADMIN_RESET_KEY', '');
if ($expectedKey === '' || !hash_equals($expectedKey, $providedKey)) {
    fwrite(STDERR, "Usage: php includes/fix_admin_password.php <ADMIN_RESET_KEY>\n");
    fwrite(STDERR, "Set ADMIN_RESET_KEY in .env before running this one-time tool.\n");
    exit(1);
}

$newPass = (string) env('ADMIN_RESET_PASSWORD', '');
if ($newPass === '') {
    $newPass = bin2hex(random_bytes(8));
}

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME),
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $newHash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 10]);

    $existing = $pdo->query('SELECT id FROM admin_users')->fetchAll(PDO::FETCH_ASSOC);
    if ($existing) {
        $stmt = $pdo->prepare('UPDATE admin_users SET password = ?, is_active = 1 WHERE id = ?');
        foreach ($existing as $admin) {
            $stmt->execute([$newHash, $admin['id']]);
        }
    } else {
        $stmt = $pdo->prepare('INSERT INTO admin_users (name, email, password, role, is_active, created_at) VALUES (?, ?, ?, ?, 1, NOW())');
        $stmt->execute(['Admin', 'admin@rcsgraphic.in', $newHash, 'super']);
    }

    echo "Admin password reset complete.\n";
    echo "Email: admin@rcsgraphic.in\n";
    echo "Password: {$newPass}\n";
    echo "Delete includes/fix_admin_password.php after use.\n";
} catch (PDOException $e) {
    fwrite(STDERR, 'DB Error: ' . $e->getMessage() . "\n");
    exit(1);
}
