-- Home best-deals management table for admin panel.
-- Run this migration before using /admin/deals.

CREATE TABLE IF NOT EXISTS home_deals (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    deal_key VARCHAR(80) NULL,
    deal_type VARCHAR(20) NOT NULL DEFAULT 'deal',
    title VARCHAR(180) NOT NULL,
    highlight_text VARCHAR(120) NOT NULL DEFAULT '',
    subtitle VARCHAR(180) NOT NULL DEFAULT 'Starting from',
    price_text VARCHAR(80) NOT NULL DEFAULT '',
    description TEXT NULL,
    image_path VARCHAR(255) NULL,
    image_alt VARCHAR(180) NULL,
    cta_text VARCHAR(120) NOT NULL DEFAULT 'Order Now',
    cta_url VARCHAR(255) NOT NULL DEFAULT '/products',
    color_theme VARCHAR(20) NOT NULL DEFAULT 'green',
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_home_deals_key (deal_key),
    KEY idx_home_deals_active_sort (is_active, sort_order),
    KEY idx_home_deals_sort (sort_order),
    KEY idx_home_deals_type (deal_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed the homepage with the current 3 deal cards + 1 promo/offer card.
INSERT INTO home_deals
    (deal_key, deal_type, title, highlight_text, subtitle, price_text, description, image_path, image_alt, cta_text, cta_url, color_theme, sort_order, is_active, created_at, updated_at)
SELECT
    'visiting-cards-500', 'deal', '500 Visiting Cards', '', 'Starting from', '₹199', '',
    'https://images.unsplash.com/photo-1586953208448-b95a79798f07?w=700&q=85&fit=crop',
    '500 visiting cards printing deal', 'Order Now', '/products', 'green', 0, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM home_deals WHERE deal_key = 'visiting-cards-500');

INSERT INTO home_deals
    (deal_key, deal_type, title, highlight_text, subtitle, price_text, description, image_path, image_alt, cta_text, cta_url, color_theme, sort_order, is_active, created_at, updated_at)
SELECT
    'flyers-1000', 'deal', '1000 Flyers', '', 'Starting from', '₹499', '',
    'https://images.unsplash.com/photo-1541746972996-4e0b0f43e02a?w=700&q=85&fit=crop',
    '1000 flyers printing deal', 'Order Now', '/products', 'orange', 1, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM home_deals WHERE deal_key = 'flyers-1000');

INSERT INTO home_deals
    (deal_key, deal_type, title, highlight_text, subtitle, price_text, description, image_path, image_alt, cta_text, cta_url, color_theme, sort_order, is_active, created_at, updated_at)
SELECT
    'brochure-a4', 'deal', 'Brochure (A4)', '', 'Starting from', '₹799', '',
    'https://images.unsplash.com/photo-1600172454284-934feca24de6?w=700&q=85&fit=crop',
    'A4 brochure printing deal', 'Order Now', '/products', 'purple', 2, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM home_deals WHERE deal_key = 'brochure-a4');

INSERT INTO home_deals
    (deal_key, deal_type, title, highlight_text, subtitle, price_text, description, image_path, image_alt, cta_text, cta_url, color_theme, sort_order, is_active, created_at, updated_at)
SELECT
    'free-design-first-order', 'promo', 'Get', 'FREE Design', 'on Your First Order!', '', '',
    'https://images.unsplash.com/photo-1513201099705-a9746e1e201f?w=420&q=85&fit=crop', 'Free design offer gift card', 'Get Free Design', '/#quick-help-sec', 'purple', 3, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM home_deals WHERE deal_key = 'free-design-first-order');
