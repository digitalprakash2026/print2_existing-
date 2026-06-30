<?php

declare(strict_types=1);

namespace Backup;

final class BackupManager
{
    public static function tempDir(): string
    {
        $dir = APP_PATH . '/storage/backups/tmp';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        return $dir;
    }

    public static function create(string $type, bool $includeConfig = true): array
    {
        $type = in_array($type, ['full', 'database', 'files'], true) ? $type : 'full';
        $stamp = date('Ymd-His');
        $base = 'rcs-' . $type . '-backup-' . $stamp;
        if ($type === 'database') {
            $path = self::tempDir() . '/' . $base . '.sql';
            self::writeDatabaseSql($path);
            return ['path' => $path, 'name' => basename($path), 'mime' => 'application/sql'];
        }

        self::requireZip();
        $path = self::tempDir() . '/' . $base . '.zip';
        $zip = new \ZipArchive();
        if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Unable to create backup zip file.');
        }

        if ($type === 'full') {
            $sqlPath = self::tempDir() . '/' . $base . '.sql';
            self::writeDatabaseSql($sqlPath);
            $zip->addFile($sqlPath, 'database/' . basename($sqlPath));
            $zip->addFromString('backup-info.json', json_encode(self::meta($type, $includeConfig), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $zip->addFromString('backup-info.json', json_encode(self::meta($type, $includeConfig), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        self::addProjectFiles($zip, $includeConfig);
        $zip->close();
        if (isset($sqlPath) && is_file($sqlPath)) @unlink($sqlPath);
        return ['path' => $path, 'name' => basename($path), 'mime' => 'application/zip'];
    }

    public static function cleanupOld(int $olderThanSeconds = 86400): void
    {
        $dir = self::tempDir();
        foreach (glob($dir . '/*') ?: [] as $file) {
            if (is_file($file) && filemtime($file) !== false && filemtime($file) < time() - $olderThanSeconds) @unlink($file);
        }
    }

    private static function requireZip(): void
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('ZipArchive PHP extension is not enabled on this server.');
        }
    }

    private static function meta(string $type, bool $includeConfig): array
    {
        $admin = \Auth\Auth::admin() ?? [];
        return [
            'type' => $type,
            'created_at' => date('c'),
            'site' => defined('APP_URL') ? APP_URL : '',
            'database' => defined('DB_NAME') ? DB_NAME : '',
            'include_config' => $includeConfig,
            'generated_by' => [
                'id' => (int)($admin['id'] ?? 0),
                'name' => (string)($admin['name'] ?? ''),
                'email' => (string)($admin['email'] ?? ''),
            ],
        ];
    }

    private static function writeDatabaseSql(string $path): void
    {
        $pdo = \Database::get();
        $fh = fopen($path, 'wb');
        if (!$fh) throw new \RuntimeException('Unable to write database backup file.');
        fwrite($fh, "-- RCS Graphic database backup\n");
        fwrite($fh, "-- Created: " . date('c') . "\n");
        fwrite($fh, "-- Database: " . DB_NAME . "\n\n");
        fwrite($fh, "SET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\nSTART TRANSACTION;\n\n");

        $tables = $pdo->query('SHOW FULL TABLES WHERE Table_type = \'BASE TABLE\'')->fetchAll(\PDO::FETCH_NUM);
        foreach ($tables as $row) {
            $table = (string)$row[0];
            $quotedTable = '`' . str_replace('`', '``', $table) . '`';
            $create = $pdo->query('SHOW CREATE TABLE ' . $quotedTable)->fetch(\PDO::FETCH_ASSOC);
            $createSql = (string)($create['Create Table'] ?? '');
            fwrite($fh, "\n-- Table structure for {$quotedTable}\nDROP TABLE IF EXISTS {$quotedTable};\n{$createSql};\n\n");

            $stmt = $pdo->query('SELECT * FROM ' . $quotedTable);
            $columns = [];
            for ($i = 0; $i < $stmt->columnCount(); $i++) {
                $meta = $stmt->getColumnMeta($i);
                $columns[] = '`' . str_replace('`', '``', (string)($meta['name'] ?? 'col_' . $i)) . '`';
            }
            $prefix = 'INSERT INTO ' . $quotedTable . ' (' . implode(',', $columns) . ') VALUES ';
            $batch = [];
            while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $values = [];
                foreach ($data as $value) $values[] = $value === null ? 'NULL' : $pdo->quote((string)$value);
                $batch[] = '(' . implode(',', $values) . ')';
                if (count($batch) >= 100) {
                    fwrite($fh, $prefix . implode(",\n", $batch) . ";\n");
                    $batch = [];
                }
            }
            if ($batch) fwrite($fh, $prefix . implode(",\n", $batch) . ";\n");
        }
        fwrite($fh, "\nCOMMIT;\nSET FOREIGN_KEY_CHECKS=1;\n");
        fclose($fh);
    }

    private static function addProjectFiles(\ZipArchive $zip, bool $includeConfig): void
    {
        $root = realpath(APP_ROOT);
        if (!$root) throw new \RuntimeException('Project root is not readable.');
        $skipNames = ['.git', 'node_modules'];
        $skipPrefixes = [
            realpath(APP_PATH . '/storage/backups') ?: APP_PATH . '/storage/backups',
            realpath(APP_PATH . '/logs') ?: APP_PATH . '/logs',
        ];
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($it as $fileInfo) {
            $path = $fileInfo->getPathname();
            $real = realpath($path);
            if (!$real) continue;
            $relativeForSkip = str_replace('\\', '/', substr($real, strlen($root)));
            foreach ($skipNames as $skipName) {
                if ($relativeForSkip === '/' . $skipName || str_contains($relativeForSkip, '/' . $skipName . '/')) continue 2;
            }
            foreach ($skipPrefixes as $prefix) {
                if ($prefix && str_starts_with($real, $prefix)) continue 2;
            }
            if (!$includeConfig && (str_starts_with($real, realpath(CONFIG_PATH) ?: CONFIG_PATH) || basename($real) === '.env')) continue;
            if ($fileInfo->isDir()) continue;
            $local = ltrim(str_replace('\\', '/', substr($real, strlen($root))), '/');
            $zip->addFile($real, 'files/' . $local);
        }
    }
}
