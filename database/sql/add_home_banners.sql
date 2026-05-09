-- Home banner slider management table for admin panel.
-- Run this migration before using /admin/banners.

CREATE TABLE IF NOT EXISTS home_banners (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    eyebrow VARCHAR(120) NULL,
    title VARCHAR(255) NULL,
    subtitle TEXT NULL,
    image_path VARCHAR(255) NOT NULL,
    image_alt VARCHAR(180) NULL,
    cta_primary_text VARCHAR(120) NOT NULL DEFAULT '',
    cta_primary_url VARCHAR(255) NOT NULL DEFAULT '',
    cta_secondary_text VARCHAR(120) NOT NULL DEFAULT '',
    cta_secondary_type VARCHAR(20) NOT NULL DEFAULT 'whatsapp',
    cta_secondary_url VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_home_banners_active_sort (is_active, sort_order),
    KEY idx_home_banners_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Existing installs: make copy/CTA columns optional for image-only banners.
ALTER TABLE home_banners MODIFY title VARCHAR(255) NULL;
ALTER TABLE home_banners MODIFY cta_primary_text VARCHAR(120) NOT NULL DEFAULT '';
ALTER TABLE home_banners MODIFY cta_primary_url VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE home_banners MODIFY cta_secondary_text VARCHAR(120) NOT NULL DEFAULT '';
