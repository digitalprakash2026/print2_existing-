<?php
$sitePages = require APP_PATH . '/data/site_pages.php';
$page = $sitePages['terms-and-conditions'] ?? [];
try {
    $settings = Database::rows("SELECT `key`, value FROM settings");
    $settingsMap = array_column($settings, 'value', 'key');
} catch (\Throwable) {
    $settingsMap = [];
}
include TMPL_PATH . '/info-page.php';
