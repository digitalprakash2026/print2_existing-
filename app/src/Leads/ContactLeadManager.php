<?php

declare(strict_types=1);

namespace Leads;

final class ContactLeadManager
{
    private static ?bool $schemaReady = null;

    public static function ensureSchema(): bool
    {
        if (self::$schemaReady !== null) return self::$schemaReady;
        try {
            \Database::query(
                "CREATE TABLE IF NOT EXISTS contact_leads (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(160) NOT NULL,
                    email VARCHAR(190) NULL,
                    phone VARCHAR(40) NULL,
                    subject VARCHAR(220) NOT NULL,
                    message TEXT NOT NULL,
                    status ENUM('new','contacted','quoted','converted','closed','spam') NOT NULL DEFAULT 'new',
                    priority ENUM('normal','high','urgent') NOT NULL DEFAULT 'normal',
                    source VARCHAR(80) NOT NULL DEFAULT 'contact_page',
                    admin_note TEXT NULL,
                    ip_address VARCHAR(64) NULL,
                    user_agent VARCHAR(255) NULL,
                    last_followup_at DATETIME NULL,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NULL,
                    KEY idx_contact_leads_status (status),
                    KEY idx_contact_leads_priority (priority),
                    KEY idx_contact_leads_created (created_at),
                    KEY idx_contact_leads_phone (phone),
                    KEY idx_contact_leads_email (email)
                 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
            self::$schemaReady = true;
        } catch (\Throwable $e) {
            error_log('Contact leads schema unavailable: ' . $e->getMessage());
            self::$schemaReady = false;
        }
        return self::$schemaReady;
    }

    public static function create(array $payload): array
    {
        if (!self::ensureSchema()) return ['ok' => false, 'msg' => 'Lead system is not ready.'];
        $honeypot = trim((string)($payload['website_url'] ?? ''));
        if ($honeypot !== '') return ['ok' => true, 'msg' => 'Thanks.'];

        $name = trim((string)($payload['name'] ?? ''));
        $email = strtolower(trim((string)($payload['email'] ?? '')));
        $phone = trim((string)($payload['phone'] ?? ''));
        $subject = trim((string)($payload['subject'] ?? ''));
        $message = trim((string)($payload['message'] ?? ''));
        if ($name === '' || $subject === '' || $message === '') return ['ok' => false, 'msg' => 'Name, subject and message are required.'];
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) return ['ok' => false, 'msg' => 'Please enter a valid email address.'];
        if (strlen($message) < 8) return ['ok' => false, 'msg' => 'Please write a little more detail.'];

        $ip = substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 64);
        $recent = \Database::row(
            "SELECT COUNT(*) AS c FROM contact_leads WHERE ip_address = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)",
            [$ip]
        );
        if ((int)($recent['c'] ?? 0) >= 5) return ['ok' => false, 'msg' => 'Too many enquiries. Please try again later.'];

        $priority = self::detectPriority($subject . ' ' . $message);
        $id = \Database::insert(
            "INSERT INTO contact_leads (name,email,phone,subject,message,status,priority,source,ip_address,user_agent,created_at)
             VALUES (?,?,?,?,?,'new',?,'contact_page',?,?,NOW())",
            [$name, $email ?: null, $phone ?: null, $subject, $message, $priority, $ip ?: null, substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255)]
        );
        return ['ok' => true, 'msg' => 'Thank you! Our team will contact you soon.', 'id' => (int)$id];
    }

    public static function adminList(): array
    {
        if (!self::ensureSchema()) return ['leads' => [], 'summary' => self::emptySummary()];
        $leads = \Database::rows("SELECT * FROM contact_leads ORDER BY FIELD(status,'new','contacted','quoted','converted','closed','spam'), created_at DESC LIMIT 500");
        $summary = self::emptySummary();
        foreach ($leads as &$lead) {
            $lead['matched_customer'] = null;
            if (!empty($lead['phone']) || !empty($lead['email'])) {
                $lead['matched_customer'] = \Database::row(
                    "SELECT id,name,email,phone FROM users WHERE (? <> '' AND phone = ?) OR (? <> '' AND email = ?) LIMIT 1",
                    [(string)($lead['phone'] ?? ''), (string)($lead['phone'] ?? ''), (string)($lead['email'] ?? ''), (string)($lead['email'] ?? '')]
                );
            }
            $summary['total']++;
            $status = (string)($lead['status'] ?? 'new');
            if (isset($summary[$status])) $summary[$status]++;
            if ((string)($lead['priority'] ?? 'normal') === 'urgent') $summary['urgent']++;
        }
        unset($lead);
        return ['leads' => $leads, 'summary' => $summary];
    }

    public static function update(int $id, array $payload): array
    {
        if (!self::ensureSchema()) return ['ok' => false, 'msg' => 'Lead system is not ready.'];
        $lead = \Database::row("SELECT id FROM contact_leads WHERE id = ?", [$id]);
        if (!$lead) return ['ok' => false, 'msg' => 'Lead not found.'];
        $status = trim((string)($payload['status'] ?? ''));
        $priority = trim((string)($payload['priority'] ?? ''));
        $note = trim((string)($payload['admin_note'] ?? ''));
        $sets = ['updated_at = NOW()'];
        $params = [];
        if ($status !== '') {
            if (!in_array($status, ['new','contacted','quoted','converted','closed','spam'], true)) return ['ok' => false, 'msg' => 'Invalid status.'];
            $sets[] = 'status = ?'; $params[] = $status;
            if (in_array($status, ['contacted','quoted','converted'], true)) $sets[] = 'last_followup_at = NOW()';
        }
        if ($priority !== '') {
            if (!in_array($priority, ['normal','high','urgent'], true)) return ['ok' => false, 'msg' => 'Invalid priority.'];
            $sets[] = 'priority = ?'; $params[] = $priority;
        }
        if (array_key_exists('admin_note', $payload)) { $sets[] = 'admin_note = ?'; $params[] = $note ?: null; }
        $params[] = $id;
        \Database::query("UPDATE contact_leads SET " . implode(', ', $sets) . " WHERE id = ?", $params);
        return ['ok' => true];
    }

    private static function detectPriority(string $text): string
    {
        $t = strtolower($text);
        if (str_contains($t, 'urgent') || str_contains($t, 'today') || str_contains($t, 'bulk') || str_contains($t, 'jaldi')) return 'urgent';
        if (str_contains($t, 'quote') || str_contains($t, 'price') || str_contains($t, '500') || str_contains($t, '1000')) return 'high';
        return 'normal';
    }

    private static function emptySummary(): array
    {
        return ['total'=>0,'new'=>0,'contacted'=>0,'quoted'=>0,'converted'=>0,'closed'=>0,'spam'=>0,'urgent'=>0];
    }
}
