-- Design Studio element-level style storage.
-- Safe to run multiple times.
CREATE TABLE IF NOT EXISTS theme_element_styles (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  target_key VARCHAR(191) NOT NULL,
  styles_json LONGTEXT NOT NULL,
  generated_css LONGTEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_theme_element_target (target_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Keep the legacy settings JSON key safe for large backups/fallbacks.
ALTER TABLE settings MODIFY `value` LONGTEXT NULL;
