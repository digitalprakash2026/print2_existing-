<?php

declare(strict_types=1);

namespace Approvals;

final class ContentApprovalManager
{
    private const TABLES = ['products','categories','coupons','home_deals'];
    private const LABELS = ['products'=>'Product','categories'=>'Category','coupons'=>'Coupon','home_deals'=>'Deal'];
    private static array $ready = [];

    public static function ensureSchema(?string $table = null): void
    {
        $tables = $table ? [$table] : self::TABLES;
        foreach ($tables as $t) {
            if (!in_array($t, self::TABLES, true) || isset(self::$ready[$t])) continue;
            try {
                $cols = [];
                foreach (\Database::rows("SHOW COLUMNS FROM {$t}") as $row) $cols[strtolower((string)$row['Field'])] = true;
                $defs = [
                    'approval_status' => "ALTER TABLE {$t} ADD COLUMN approval_status ENUM('draft','pending','approved','rejected') NOT NULL DEFAULT 'approved' AFTER is_active",
                    'submitted_by' => "ALTER TABLE {$t} ADD COLUMN submitted_by INT NULL AFTER approval_status",
                    'submitted_at' => "ALTER TABLE {$t} ADD COLUMN submitted_at DATETIME NULL AFTER submitted_by",
                    'approved_by' => "ALTER TABLE {$t} ADD COLUMN approved_by INT NULL AFTER submitted_at",
                    'approved_at' => "ALTER TABLE {$t} ADD COLUMN approved_at DATETIME NULL AFTER approved_by",
                    'rejected_by' => "ALTER TABLE {$t} ADD COLUMN rejected_by INT NULL AFTER approved_at",
                    'rejected_at' => "ALTER TABLE {$t} ADD COLUMN rejected_at DATETIME NULL AFTER rejected_by",
                    'approval_note' => "ALTER TABLE {$t} ADD COLUMN approval_note TEXT NULL AFTER rejected_at",
                ];
                foreach ($defs as $name => $sql) if (!isset($cols[$name])) \Database::query($sql);
                self::$ready[$t] = true;
            } catch (\Throwable $e) {
                error_log("Approval schema unavailable for {$t}: " . $e->getMessage());
            }
        }
    }

    public static function isSuperAdmin(?array $admin = null): bool
    {
        $role = strtolower(trim((string)(($admin ?? \Auth\Auth::admin())['role'] ?? '')));
        $normalizedRole = trim((string)preg_replace('/[^a-z0-9]+/', '_', $role), '_');
        return in_array($normalizedRole, ['super','superadmin','super_admin','owner'], true);
    }

    public static function requireSuperAdmin(): void
    {
        if (!self::isSuperAdmin()) {
            http_response_code(403);
            echo json_encode(['ok'=>false,'msg'=>'Only Super Admin can perform this action.']);
            exit;
        }
    }

    public static function applySaveState(string $table, int $id, ?array $admin = null, string $note = ''): void
    {
        if ($id <= 0 || !in_array($table, self::TABLES, true)) return;
        self::ensureSchema($table);
        $admin = $admin ?? \Auth\Auth::admin();
        $adminId = (int)($admin['id'] ?? 0);
        try {
            if (self::isSuperAdmin($admin)) {
                \Database::query(
                    "UPDATE {$table} SET approval_status='approved', approved_by=?, approved_at=NOW(), rejected_by=NULL, rejected_at=NULL, approval_note=NULL WHERE id=?",
                    [$adminId ?: null, $id]
                );
                return;
            }
            \Database::query(
                "UPDATE {$table} SET is_active=0, approval_status='pending', submitted_by=?, submitted_at=NOW(), approved_by=NULL, approved_at=NULL, rejected_by=NULL, rejected_at=NULL, approval_note=? WHERE id=?",
                [$adminId ?: null, $note ?: null, $id]
            );
        } catch (\Throwable $e) {
            error_log("Approval state update failed for {$table} #{$id}: " . $e->getMessage());
        }
    }

    public static function listPending(): array
    {
        self::ensureSchema();
        $items = [];
        foreach (self::TABLES as $table) {
            try {
                $nameExpr = $table === 'coupons' ? 'code' : 'title';
                if (in_array($table, ['products','categories'], true)) $nameExpr = 'name';
                $rows = \Database::rows(
                    "SELECT id, {$nameExpr} AS title, is_active, approval_status, submitted_by, submitted_at, approval_note
                     FROM {$table}
                     WHERE approval_status IN ('pending','rejected')
                     ORDER BY FIELD(approval_status,'pending','rejected'), submitted_at DESC, id DESC
                     LIMIT 100"
                );
                foreach ($rows as $row) {
                    $row['type'] = $table;
                    $row['type_label'] = self::LABELS[$table];
                    $row['submitted_by_name'] = self::adminName((int)($row['submitted_by'] ?? 0));
                    $items[] = $row;
                }
            } catch (\Throwable $e) {
                error_log("Approval list skipped {$table}: " . $e->getMessage());
            }
        }
        return $items;
    }

    public static function decide(string $table, int $id, string $decision, string $note = ''): array
    {
        self::requireSuperAdmin();
        if (!in_array($table, self::TABLES, true)) return ['ok'=>false,'msg'=>'Invalid approval type.'];
        self::ensureSchema($table);
        $adminId = (int)(\Auth\Auth::admin()['id'] ?? 0);
        try {
            if ($decision === 'approve') {
                \Database::query("UPDATE {$table} SET is_active=1, approval_status='approved', approved_by=?, approved_at=NOW(), rejected_by=NULL, rejected_at=NULL, approval_note=NULL WHERE id=?", [$adminId ?: null, $id]);
                \Orders\AdminAudit::log('content_approved', self::LABELS[$table] . " #{$id} approved");
                return ['ok'=>true];
            }
            if ($decision === 'reject') {
                if (trim($note) === '') return ['ok'=>false,'msg'=>'Rejection reason is required.'];
                \Database::query("UPDATE {$table} SET is_active=0, approval_status='rejected', rejected_by=?, rejected_at=NOW(), approval_note=? WHERE id=?", [$adminId ?: null, trim($note), $id]);
                \Orders\AdminAudit::log('content_rejected', self::LABELS[$table] . " #{$id} rejected");
                return ['ok'=>true];
            }
        } catch (\Throwable $e) {
            error_log("Approval decision failed: " . $e->getMessage());
            return ['ok'=>false,'msg'=>'Could not update approval.'];
        }
        return ['ok'=>false,'msg'=>'Invalid decision.'];
    }

    private static function adminName(int $id): string
    {
        if ($id <= 0) return 'Admin';
        try { $row = \Database::row("SELECT name,email FROM admin_users WHERE id=?", [$id]); return (string)($row['name'] ?? $row['email'] ?? 'Admin'); } catch (\Throwable) { return 'Admin'; }
    }
}
