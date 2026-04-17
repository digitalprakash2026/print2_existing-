<?php
declare(strict_types=1);

namespace Orders;

class AdminAudit
{
    public static function log(string $action, string $description = '', ?int $entityId = null): void
    {
        try {
            $admin = \Auth\Auth::admin();
            \Database::insert(
                "INSERT INTO admin_audit_logs (admin_id, admin_name, action, description, entity_id, ip_address, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())",
                [
                    $admin['id'] ?? 0,
                    $admin['name'] ?? 'system',
                    $action,
                    $description,
                    $entityId,
                    $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                ]
            );
        } catch (\Throwable) {} // Never break on audit fail
    }
}
