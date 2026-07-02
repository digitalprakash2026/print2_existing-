<?php
$pageTitle = 'Backup — RCS Admin';
$currentAdmPage = 'backup';
include __DIR__ . '/layout.php';
$error = trim((string)($_GET['error'] ?? ''));
$zipReady = class_exists(ZipArchive::class);
?>

<div class="adm-pt">Website Backup</div>
<?php if ($error !== ''): ?>
  <div class="adm-backup-alert adm-backup-alert--error">⚠️ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<section class="adm-backup-hero">
  <div>
    <span>Super Admin only</span>
    <h1>Download Website & Database Backup</h1>
    <p>Create a database SQL export, website files ZIP, or full backup package for safe offline storage and disaster recovery.</p>
  </div>
  <strong><?= $zipReady ? 'ZIP Ready' : 'ZIP Extension Missing' ?></strong>
</section>

<section class="adm-backup-warning">
  <strong>Important security note</strong>
  <p>Backups may contain customer orders, uploaded artwork/proofs, admin information and database records. Download only on trusted devices and store securely.</p>
</section>

<div class="adm-backup-grid">
  <form class="adm-backup-card" method="POST" action="/admin/backup/download">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
    <input type="hidden" name="backup_type" value="full">
    <span class="adm-backup-icon">🧰</span>
    <h2>Full Backup</h2>
    <p>Creates one ZIP containing website files, uploads, database SQL and backup metadata.</p>
    <label class="adm-backup-check"><input type="checkbox" name="include_config" value="1" checked> Include config/secrets for restore</label>
    <button class="btn btn-blue" type="submit" <?= $zipReady ? '' : 'disabled' ?>>Download Full Backup</button>
  </form>

  <form class="adm-backup-card" method="POST" action="/admin/backup/download">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
    <input type="hidden" name="backup_type" value="database">
    <span class="adm-backup-icon">🗄️</span>
    <h2>Database SQL</h2>
    <p>Exports all database tables and data as a restore-ready SQL file.</p>
    <button class="btn btn-blue" type="submit">Download SQL Backup</button>
  </form>

  <form class="adm-backup-card" method="POST" action="/admin/backup/download">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? '') ?>">
    <input type="hidden" name="backup_type" value="files">
    <span class="adm-backup-icon">📦</span>
    <h2>Files ZIP</h2>
    <p>Creates a ZIP of website files and uploads. Database is not included in this option.</p>
    <label class="adm-backup-check"><input type="checkbox" name="include_config" value="1" checked> Include config/secrets</label>
    <button class="btn btn-outline" type="submit" <?= $zipReady ? '' : 'disabled' ?>>Download Files ZIP</button>
  </form>
</div>

<section class="adm-backup-tips">
  <h3>Recommended backup practice</h3>
  <ul>
    <li>Take a full backup before large content, product, theme or code changes.</li>
    <li>Keep at least one recent backup outside the hosting server.</li>
    <li>If ZIP is not available on hosting, use the Database SQL option and ask hosting support to enable PHP ZipArchive.</li>
  </ul>
</section>

</div></div></div>
</body></html>
