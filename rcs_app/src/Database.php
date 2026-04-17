<?php
// ─────────────────────────────────────────────────────────────
//  RCS Graphic — Database (PDO Singleton)
// ─────────────────────────────────────────────────────────────

declare(strict_types=1);

class Database
{
    private static ?PDO $instance = null;

    public static function get(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                DB_HOST, DB_PORT, DB_NAME
            );
            self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$instance;
    }

    // Convenience wrappers
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function row(string $sql, array $params = []): ?array
    {
        $row = self::query($sql, $params)->fetch();
        return $row ?: null;
    }

    public static function rows(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function insert(string $sql, array $params = []): string
    {
        self::query($sql, $params);
        return self::get()->lastInsertId();
    }

    public static function setting(string $key, mixed $default = null): mixed
    {
        try {
            $row = self::row("SELECT value FROM settings WHERE `key` = ?", [$key]);
            return $row ? $row['value'] : $default;
        } catch (\Throwable) {
            return $default;
        }
    }

    public static function setSetting(string $key, mixed $value): void
    {
        self::query(
            "INSERT INTO settings (`key`, value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE value = VALUES(value)",
            [$key, $value]
        );
    }
}
