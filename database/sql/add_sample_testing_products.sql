-- =====================================================
-- RCS sample product catalog for testing
-- Adds 7 categories, 35 sample products, 4 gallery images
-- per product, and simple quantity tiers.
--
-- Maintain/remove later:
--   DELETE FROM product_images WHERE product_id IN (SELECT id FROM products WHERE slug LIKE 'rcs-sample-%');
--   DELETE FROM product_quantity_tiers WHERE product_id IN (SELECT id FROM products WHERE slug LIKE 'rcs-sample-%');
--   DELETE FROM products WHERE slug LIKE 'rcs-sample-%';
-- Categories are left untouched so real data is not disturbed.
-- =====================================================

START TRANSACTION;

CREATE TABLE IF NOT EXISTS product_quantity_tiers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  quantity INT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_product_qty (product_id, quantity),
  KEY idx_tiers_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS product_images (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  url VARCHAR(255) NULL,
  alt_text VARCHAR(255) NULL,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  KEY idx_product_images_product (product_id),
  KEY idx_product_images_primary (product_id, is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Categories: inserted only when a matching slug is missing.
INSERT INTO categories (name, slug, description, icon, sort_order, is_active, created_at)
SELECT 'Business Cards', 'business-cards', 'Business card and visiting card samples', '💳', 10, 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug = 'business-cards');

INSERT INTO categories (name, slug, description, icon, sort_order, is_active, created_at)
SELECT 'Brochures', 'brochures', 'Folded brochure printing samples', '📋', 20, 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug = 'brochures');

INSERT INTO categories (name, slug, description, icon, sort_order, is_active, created_at)
SELECT 'Flyers', 'flyers', 'Marketing flyer printing samples', '📄', 30, 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug = 'flyers');

INSERT INTO categories (name, slug, description, icon, sort_order, is_active, created_at)
SELECT 'Pamphlets', 'pamphlets', 'Pamphlet and handout printing samples', '📰', 40, 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug = 'pamphlets');

INSERT INTO categories (name, slug, description, icon, sort_order, is_active, created_at)
SELECT 'Stationery', 'stationery', 'Office stationery printing samples', '📝', 50, 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug = 'stationery');

INSERT INTO categories (name, slug, description, icon, sort_order, is_active, created_at)
SELECT 'Banners', 'banners', 'Flex and vinyl banner printing samples', '🏳️', 60, 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug = 'banners');

INSERT INTO categories (name, slug, description, icon, sort_order, is_active, created_at)
SELECT 'Posters', 'posters', 'Poster printing samples', '🖼️', 70, 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug = 'posters');

-- Products: all slugs are prefixed with rcs-sample- so they are easy to find/remove.
INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Premium Business Card', 'rcs-sample-premium-business-card', 'Thick premium business cards with sharp color output and smooth finish.', 'Premium Business Card', 350, '/assets/images/sample-products/business-cards/business-cards-1.svg', 1, 1, NOW()
FROM categories c WHERE c.slug = 'business-cards'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-premium-business-card');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Matte Laminated Business Card', 'rcs-sample-matte-business-card', 'Elegant matte laminated cards for a modern professional identity.', 'Matte Laminated Business Card', 450, '/assets/images/sample-products/business-cards/business-cards-1.svg', 1, 2, NOW()
FROM categories c WHERE c.slug = 'business-cards'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-matte-business-card');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Gloss Business Card', 'rcs-sample-gloss-business-card', 'Gloss coated business cards with vibrant brand colors.', 'Gloss Business Card', 400, '/assets/images/sample-products/business-cards/business-cards-1.svg', 1, 3, NOW()
FROM categories c WHERE c.slug = 'business-cards'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-gloss-business-card');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Spot UV Business Card', 'rcs-sample-spot-uv-business-card', 'Premium cards with highlighted spot UV logo and details.', 'Spot UV Business Card', 700, '/assets/images/sample-products/business-cards/business-cards-1.svg', 1, 4, NOW()
FROM categories c WHERE c.slug = 'business-cards'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-spot-uv-business-card');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Square Business Card', 'rcs-sample-square-business-card', 'Creative square cards for boutiques, creators, and premium brands.', 'Square Business Card', 500, '/assets/images/sample-products/business-cards/business-cards-1.svg', 1, 5, NOW()
FROM categories c WHERE c.slug = 'business-cards'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-square-business-card');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Tri-fold Brochure', 'rcs-sample-trifold-brochure', 'Classic tri-fold brochure for services, menus, real estate, and offers.', 'Tri-fold Brochure', 1200, '/assets/images/sample-products/brochures/brochures-1.svg', 1, 1, NOW()
FROM categories c WHERE c.slug = 'brochures'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-trifold-brochure');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Bi-fold Brochure', 'rcs-sample-bifold-brochure', 'Clean bi-fold brochure for product/service presentation.', 'Bi-fold Brochure', 1100, '/assets/images/sample-products/brochures/brochures-1.svg', 1, 2, NOW()
FROM categories c WHERE c.slug = 'brochures'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-bifold-brochure');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Corporate Brochure', 'rcs-sample-corporate-brochure', 'Premium corporate brochure for company profiles and proposals.', 'Corporate Brochure', 1800, '/assets/images/sample-products/brochures/brochures-1.svg', 1, 3, NOW()
FROM categories c WHERE c.slug = 'brochures'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-corporate-brochure');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Product Catalog Brochure', 'rcs-sample-product-catalog-brochure', 'Multi-panel catalog-style brochure for product showcases.', 'Product Catalog Brochure', 2200, '/assets/images/sample-products/brochures/brochures-1.svg', 1, 4, NOW()
FROM categories c WHERE c.slug = 'brochures'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-product-catalog-brochure');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Real Estate Brochure', 'rcs-sample-real-estate-brochure', 'Property brochure with image-led layouts and feature highlights.', 'Real Estate Brochure', 1600, '/assets/images/sample-products/brochures/brochures-1.svg', 1, 5, NOW()
FROM categories c WHERE c.slug = 'brochures'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-real-estate-brochure');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'A4 Promotional Flyer', 'rcs-sample-a4-promotional-flyer', 'Full-color A4 flyer for promotions, launches, and announcements.', 'A4 Promotional Flyer', 900, '/assets/images/sample-products/flyers/flyers-1.svg', 1, 1, NOW()
FROM categories c WHERE c.slug = 'flyers'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-a4-promotional-flyer');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'A5 Event Flyer', 'rcs-sample-a5-event-flyer', 'Compact event flyer for local promotions and hand distribution.', 'A5 Event Flyer', 650, '/assets/images/sample-products/flyers/flyers-1.svg', 1, 2, NOW()
FROM categories c WHERE c.slug = 'flyers'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-a5-event-flyer');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Retail Sale Flyer', 'rcs-sample-sale-flyer', 'Vibrant flyer layout for discounts, offers, and seasonal campaigns.', 'Retail Sale Flyer', 750, '/assets/images/sample-products/flyers/flyers-1.svg', 1, 3, NOW()
FROM categories c WHERE c.slug = 'flyers'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-sale-flyer');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Restaurant Flyer', 'rcs-sample-restaurant-flyer', 'Food menu and restaurant offer flyer with rich color print.', 'Restaurant Flyer', 850, '/assets/images/sample-products/flyers/flyers-1.svg', 1, 4, NOW()
FROM categories c WHERE c.slug = 'flyers'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-restaurant-flyer');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Real Estate Flyer', 'rcs-sample-real-estate-flyer', 'Property flyer for site visits, launches, and project highlights.', 'Real Estate Flyer', 950, '/assets/images/sample-products/flyers/flyers-1.svg', 1, 5, NOW()
FROM categories c WHERE c.slug = 'flyers'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-real-estate-flyer');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'A5 Pamphlet', 'rcs-sample-a5-pamphlet', 'Budget-friendly pamphlet for local marketing and awareness campaigns.', 'A5 Pamphlet', 520, '/assets/images/sample-products/pamphlets/pamphlets-1.svg', 1, 1, NOW()
FROM categories c WHERE c.slug = 'pamphlets'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-a5-pamphlet');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Healthcare Pamphlet', 'rcs-sample-healthcare-pamphlet', 'Informational pamphlet for clinics, health camps, and services.', 'Healthcare Pamphlet', 620, '/assets/images/sample-products/pamphlets/pamphlets-1.svg', 1, 2, NOW()
FROM categories c WHERE c.slug = 'pamphlets'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-healthcare-pamphlet');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Education Pamphlet', 'rcs-sample-education-pamphlet', 'Institute and course promotion pamphlet with clear details.', 'Education Pamphlet', 580, '/assets/images/sample-products/pamphlets/pamphlets-1.svg', 1, 3, NOW()
FROM categories c WHERE c.slug = 'pamphlets'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-education-pamphlet');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Campaign Pamphlet', 'rcs-sample-political-pamphlet', 'High-volume pamphlet for campaigns, notices, and public outreach.', 'Campaign Pamphlet', 700, '/assets/images/sample-products/pamphlets/pamphlets-1.svg', 1, 4, NOW()
FROM categories c WHERE c.slug = 'pamphlets'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-political-pamphlet');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Menu Pamphlet', 'rcs-sample-menu-pamphlet', 'Compact menu pamphlet for cafés, restaurants, and takeaways.', 'Menu Pamphlet', 680, '/assets/images/sample-products/pamphlets/pamphlets-1.svg', 1, 5, NOW()
FROM categories c WHERE c.slug = 'pamphlets'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-menu-pamphlet');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Corporate Letterhead', 'rcs-sample-letterhead', 'Professional letterhead for invoices, quotations, and official letters.', 'Corporate Letterhead', 800, '/assets/images/sample-products/stationery/stationery-1.svg', 1, 1, NOW()
FROM categories c WHERE c.slug = 'stationery'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-letterhead');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Printed Envelope', 'rcs-sample-envelope', 'Branded envelopes for business communication and dispatch.', 'Printed Envelope', 900, '/assets/images/sample-products/stationery/stationery-1.svg', 1, 2, NOW()
FROM categories c WHERE c.slug = 'stationery'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-envelope');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'ID Card', 'rcs-sample-id-card', 'Durable ID cards for staff, students, and events.', 'ID Card', 600, '/assets/images/sample-products/stationery/stationery-1.svg', 1, 3, NOW()
FROM categories c WHERE c.slug = 'stationery'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-id-card');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Invoice Book', 'rcs-sample-invoice-book', 'Custom invoice and bill books with business branding.', 'Invoice Book', 1200, '/assets/images/sample-products/stationery/stationery-1.svg', 1, 4, NOW()
FROM categories c WHERE c.slug = 'stationery'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-invoice-book');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Complete Stationery Kit', 'rcs-sample-stationery-kit', 'Matching letterhead, envelope, ID card, and business card set.', 'Complete Stationery Kit', 1800, '/assets/images/sample-products/stationery/stationery-1.svg', 1, 5, NOW()
FROM categories c WHERE c.slug = 'stationery'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-stationery-kit');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Shop Flex Banner', 'rcs-sample-shop-flex-banner', 'Outdoor flex banner for shops, launches, and local visibility.', 'Shop Flex Banner', 1500, '/assets/images/sample-products/banners/banners-1.svg', 1, 1, NOW()
FROM categories c WHERE c.slug = 'banners'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-shop-flex-banner');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Event Backdrop Banner', 'rcs-sample-event-backdrop-banner', 'Large event backdrop banner for stages, counters, and branding.', 'Event Backdrop Banner', 2500, '/assets/images/sample-products/banners/banners-1.svg', 1, 2, NOW()
FROM categories c WHERE c.slug = 'banners'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-event-backdrop-banner');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Vinyl Banner', 'rcs-sample-vinyl-banner', 'Durable vinyl banner for indoor and outdoor campaigns.', 'Vinyl Banner', 2200, '/assets/images/sample-products/banners/banners-1.svg', 1, 3, NOW()
FROM categories c WHERE c.slug = 'banners'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-vinyl-banner');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Roll-up Standee', 'rcs-sample-roll-up-standee', 'Portable roll-up standee for exhibitions, stores, and offices.', 'Roll-up Standee', 1800, '/assets/images/sample-products/banners/banners-1.svg', 1, 4, NOW()
FROM categories c WHERE c.slug = 'banners'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-roll-up-standee');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Retail Sale Banner', 'rcs-sample-sale-banner', 'High-impact sale banner for discounts and seasonal offers.', 'Retail Sale Banner', 1300, '/assets/images/sample-products/banners/banners-1.svg', 1, 5, NOW()
FROM categories c WHERE c.slug = 'banners'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-sale-banner');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'A3 Poster', 'rcs-sample-a3-poster', 'Sharp A3 poster for events, offers, and announcements.', 'A3 Poster', 850, '/assets/images/sample-products/posters/posters-1.svg', 1, 1, NOW()
FROM categories c WHERE c.slug = 'posters'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-a3-poster');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Movie Style Poster', 'rcs-sample-movie-style-poster', 'Premium poster style print for events and creative campaigns.', 'Movie Style Poster', 1100, '/assets/images/sample-products/posters/posters-1.svg', 1, 2, NOW()
FROM categories c WHERE c.slug = 'posters'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-movie-style-poster');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Wall Poster', 'rcs-sample-wall-poster', 'Decorative wall poster with high-quality color output.', 'Wall Poster', 950, '/assets/images/sample-products/posters/posters-1.svg', 1, 3, NOW()
FROM categories c WHERE c.slug = 'posters'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-wall-poster');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Product Poster', 'rcs-sample-product-poster', 'Product advertising poster for retail and showroom display.', 'Product Poster', 1050, '/assets/images/sample-products/posters/posters-1.svg', 1, 4, NOW()
FROM categories c WHERE c.slug = 'posters'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-product-poster');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Event Poster', 'rcs-sample-event-poster', 'Bold event poster for concerts, workshops, and social events.', 'Event Poster', 900, '/assets/images/sample-products/posters/posters-1.svg', 1, 5, NOW()
FROM categories c WHERE c.slug = 'posters'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug = 'rcs-sample-event-poster');

-- Rebuild managed sample galleries/tiers each time so the seed stays deterministic.
DELETE FROM product_images WHERE product_id IN (SELECT id FROM products WHERE slug IN (
  'rcs-sample-premium-business-card',
  'rcs-sample-matte-business-card',
  'rcs-sample-gloss-business-card',
  'rcs-sample-spot-uv-business-card',
  'rcs-sample-square-business-card',
  'rcs-sample-trifold-brochure',
  'rcs-sample-bifold-brochure',
  'rcs-sample-corporate-brochure',
  'rcs-sample-product-catalog-brochure',
  'rcs-sample-real-estate-brochure',
  'rcs-sample-a4-promotional-flyer',
  'rcs-sample-a5-event-flyer',
  'rcs-sample-sale-flyer',
  'rcs-sample-restaurant-flyer',
  'rcs-sample-real-estate-flyer',
  'rcs-sample-a5-pamphlet',
  'rcs-sample-healthcare-pamphlet',
  'rcs-sample-education-pamphlet',
  'rcs-sample-political-pamphlet',
  'rcs-sample-menu-pamphlet',
  'rcs-sample-letterhead',
  'rcs-sample-envelope',
  'rcs-sample-id-card',
  'rcs-sample-invoice-book',
  'rcs-sample-stationery-kit',
  'rcs-sample-shop-flex-banner',
  'rcs-sample-event-backdrop-banner',
  'rcs-sample-vinyl-banner',
  'rcs-sample-roll-up-standee',
  'rcs-sample-sale-banner',
  'rcs-sample-a3-poster',
  'rcs-sample-movie-style-poster',
  'rcs-sample-wall-poster',
  'rcs-sample-product-poster',
  'rcs-sample-event-poster'
));
DELETE FROM product_quantity_tiers WHERE product_id IN (SELECT id FROM products WHERE slug IN (
  'rcs-sample-premium-business-card',
  'rcs-sample-matte-business-card',
  'rcs-sample-gloss-business-card',
  'rcs-sample-spot-uv-business-card',
  'rcs-sample-square-business-card',
  'rcs-sample-trifold-brochure',
  'rcs-sample-bifold-brochure',
  'rcs-sample-corporate-brochure',
  'rcs-sample-product-catalog-brochure',
  'rcs-sample-real-estate-brochure',
  'rcs-sample-a4-promotional-flyer',
  'rcs-sample-a5-event-flyer',
  'rcs-sample-sale-flyer',
  'rcs-sample-restaurant-flyer',
  'rcs-sample-real-estate-flyer',
  'rcs-sample-a5-pamphlet',
  'rcs-sample-healthcare-pamphlet',
  'rcs-sample-education-pamphlet',
  'rcs-sample-political-pamphlet',
  'rcs-sample-menu-pamphlet',
  'rcs-sample-letterhead',
  'rcs-sample-envelope',
  'rcs-sample-id-card',
  'rcs-sample-invoice-book',
  'rcs-sample-stationery-kit',
  'rcs-sample-shop-flex-banner',
  'rcs-sample-event-backdrop-banner',
  'rcs-sample-vinyl-banner',
  'rcs-sample-roll-up-standee',
  'rcs-sample-sale-banner',
  'rcs-sample-a3-poster',
  'rcs-sample-movie-style-poster',
  'rcs-sample-wall-poster',
  'rcs-sample-product-poster',
  'rcs-sample-event-poster'
));

-- Quantity tiers for checkout/pricing tests.
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 384.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-premium-business-card';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 545.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-premium-business-card';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1075.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-premium-business-card';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3000.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-premium-business-card';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 10250.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-premium-business-card';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 484.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-matte-business-card';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 645.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-matte-business-card';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1175.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-matte-business-card';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3100.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-matte-business-card';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 10350.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-matte-business-card';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 434.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-gloss-business-card';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 595.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-gloss-business-card';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1125.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-gloss-business-card';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3050.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-gloss-business-card';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 10300.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-gloss-business-card';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 734.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-spot-uv-business-card';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 895.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-spot-uv-business-card';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1425.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-spot-uv-business-card';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3350.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-spot-uv-business-card';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 10600.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-spot-uv-business-card';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 534.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-square-business-card';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 695.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-square-business-card';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1225.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-square-business-card';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3150.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-square-business-card';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 10400.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-square-business-card';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 1234.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-trifold-brochure';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 1395.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-trifold-brochure';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1925.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-trifold-brochure';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3850.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-trifold-brochure';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 11100.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-trifold-brochure';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 1134.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-bifold-brochure';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 1295.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-bifold-brochure';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1825.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-bifold-brochure';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3750.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-bifold-brochure';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 11000.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-bifold-brochure';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 1834.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-corporate-brochure';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 1995.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-corporate-brochure';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 2525.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-corporate-brochure';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 4450.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-corporate-brochure';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 11700.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-corporate-brochure';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 2234.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-product-catalog-brochure';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 2395.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-product-catalog-brochure';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 2925.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-product-catalog-brochure';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 4850.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-product-catalog-brochure';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 12100.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-product-catalog-brochure';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 1634.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-real-estate-brochure';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 1795.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-real-estate-brochure';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 2325.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-real-estate-brochure';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 4250.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-real-estate-brochure';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 11500.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-real-estate-brochure';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 934.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-a4-promotional-flyer';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 1095.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-a4-promotional-flyer';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1625.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-a4-promotional-flyer';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3550.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-a4-promotional-flyer';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 10800.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-a4-promotional-flyer';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 684.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-a5-event-flyer';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 845.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-a5-event-flyer';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1375.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-a5-event-flyer';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3300.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-a5-event-flyer';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 10550.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-a5-event-flyer';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 784.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-sale-flyer';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 945.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-sale-flyer';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1475.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-sale-flyer';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3400.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-sale-flyer';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 10650.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-sale-flyer';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 884.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-restaurant-flyer';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 1045.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-restaurant-flyer';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1575.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-restaurant-flyer';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3500.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-restaurant-flyer';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 10750.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-restaurant-flyer';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 984.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-real-estate-flyer';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 1145.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-real-estate-flyer';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1675.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-real-estate-flyer';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3600.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-real-estate-flyer';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 10850.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-real-estate-flyer';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 554.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-a5-pamphlet';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 715.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-a5-pamphlet';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1245.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-a5-pamphlet';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3170.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-a5-pamphlet';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 10420.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-a5-pamphlet';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 654.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-healthcare-pamphlet';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 815.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-healthcare-pamphlet';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1345.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-healthcare-pamphlet';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3270.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-healthcare-pamphlet';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 10520.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-healthcare-pamphlet';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 614.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-education-pamphlet';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 775.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-education-pamphlet';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1305.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-education-pamphlet';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3230.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-education-pamphlet';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 10480.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-education-pamphlet';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 734.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-political-pamphlet';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 895.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-political-pamphlet';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1425.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-political-pamphlet';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3350.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-political-pamphlet';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 10600.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-political-pamphlet';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 714.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-menu-pamphlet';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 875.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-menu-pamphlet';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1405.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-menu-pamphlet';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3330.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-menu-pamphlet';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 10580.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-menu-pamphlet';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 834.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-letterhead';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 995.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-letterhead';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1525.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-letterhead';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3450.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-letterhead';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 10700.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-letterhead';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 934.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-envelope';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 1095.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-envelope';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1625.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-envelope';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3550.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-envelope';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 10800.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-envelope';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 634.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-id-card';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 795.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-id-card';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1325.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-id-card';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3250.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-id-card';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 10500.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-id-card';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 1234.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-invoice-book';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 1395.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-invoice-book';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1925.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-invoice-book';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3850.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-invoice-book';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 11100.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-invoice-book';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 1834.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-stationery-kit';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 1995.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-stationery-kit';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 2525.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-stationery-kit';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 4450.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-stationery-kit';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 11700.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-stationery-kit';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 1534.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-shop-flex-banner';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 1695.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-shop-flex-banner';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 2225.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-shop-flex-banner';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 4150.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-shop-flex-banner';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 11400.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-shop-flex-banner';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 2534.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-event-backdrop-banner';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 2695.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-event-backdrop-banner';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 3225.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-event-backdrop-banner';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 5150.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-event-backdrop-banner';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 12400.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-event-backdrop-banner';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 2234.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-vinyl-banner';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 2395.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-vinyl-banner';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 2925.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-vinyl-banner';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 4850.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-vinyl-banner';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 12100.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-vinyl-banner';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 1834.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-roll-up-standee';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 1995.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-roll-up-standee';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 2525.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-roll-up-standee';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 4450.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-roll-up-standee';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 11700.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-roll-up-standee';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 1334.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-sale-banner';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 1495.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-sale-banner';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 2025.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-sale-banner';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3950.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-sale-banner';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 11200.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-sale-banner';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 884.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-a3-poster';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 1045.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-a3-poster';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1575.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-a3-poster';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3500.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-a3-poster';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 10750.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-a3-poster';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 1134.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-movie-style-poster';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 1295.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-movie-style-poster';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1825.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-movie-style-poster';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3750.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-movie-style-poster';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 11000.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-movie-style-poster';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 984.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-wall-poster';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 1145.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-wall-poster';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1675.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-wall-poster';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3600.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-wall-poster';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 10850.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-wall-poster';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 1084.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-product-poster';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 1245.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-product-poster';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1775.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-product-poster';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3700.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-product-poster';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 10950.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-product-poster';

INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 100, 934.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-event-poster';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 250, 1095.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-event-poster';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 500, 1625.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-event-poster';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 1000, 3550.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-event-poster';
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, 2000, 10800.00, NOW() FROM products p WHERE p.slug = 'rcs-sample-event-poster';

-- Four realistic reusable SVG mockups per product category.
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/business-cards/business-cards-1.svg', '/assets/images/sample-products/business-cards/business-cards-1.svg', 'Premium Business Card sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-premium-business-card';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/business-cards/business-cards-2.svg', '/assets/images/sample-products/business-cards/business-cards-2.svg', 'Premium Business Card sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-premium-business-card';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/business-cards/business-cards-3.svg', '/assets/images/sample-products/business-cards/business-cards-3.svg', 'Premium Business Card sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-premium-business-card';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/business-cards/business-cards-4.svg', '/assets/images/sample-products/business-cards/business-cards-4.svg', 'Premium Business Card sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-premium-business-card';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/business-cards/business-cards-1.svg', '/assets/images/sample-products/business-cards/business-cards-1.svg', 'Matte Laminated Business Card sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-matte-business-card';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/business-cards/business-cards-2.svg', '/assets/images/sample-products/business-cards/business-cards-2.svg', 'Matte Laminated Business Card sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-matte-business-card';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/business-cards/business-cards-3.svg', '/assets/images/sample-products/business-cards/business-cards-3.svg', 'Matte Laminated Business Card sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-matte-business-card';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/business-cards/business-cards-4.svg', '/assets/images/sample-products/business-cards/business-cards-4.svg', 'Matte Laminated Business Card sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-matte-business-card';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/business-cards/business-cards-1.svg', '/assets/images/sample-products/business-cards/business-cards-1.svg', 'Gloss Business Card sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-gloss-business-card';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/business-cards/business-cards-2.svg', '/assets/images/sample-products/business-cards/business-cards-2.svg', 'Gloss Business Card sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-gloss-business-card';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/business-cards/business-cards-3.svg', '/assets/images/sample-products/business-cards/business-cards-3.svg', 'Gloss Business Card sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-gloss-business-card';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/business-cards/business-cards-4.svg', '/assets/images/sample-products/business-cards/business-cards-4.svg', 'Gloss Business Card sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-gloss-business-card';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/business-cards/business-cards-1.svg', '/assets/images/sample-products/business-cards/business-cards-1.svg', 'Spot UV Business Card sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-spot-uv-business-card';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/business-cards/business-cards-2.svg', '/assets/images/sample-products/business-cards/business-cards-2.svg', 'Spot UV Business Card sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-spot-uv-business-card';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/business-cards/business-cards-3.svg', '/assets/images/sample-products/business-cards/business-cards-3.svg', 'Spot UV Business Card sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-spot-uv-business-card';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/business-cards/business-cards-4.svg', '/assets/images/sample-products/business-cards/business-cards-4.svg', 'Spot UV Business Card sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-spot-uv-business-card';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/business-cards/business-cards-1.svg', '/assets/images/sample-products/business-cards/business-cards-1.svg', 'Square Business Card sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-square-business-card';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/business-cards/business-cards-2.svg', '/assets/images/sample-products/business-cards/business-cards-2.svg', 'Square Business Card sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-square-business-card';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/business-cards/business-cards-3.svg', '/assets/images/sample-products/business-cards/business-cards-3.svg', 'Square Business Card sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-square-business-card';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/business-cards/business-cards-4.svg', '/assets/images/sample-products/business-cards/business-cards-4.svg', 'Square Business Card sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-square-business-card';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/brochures/brochures-1.svg', '/assets/images/sample-products/brochures/brochures-1.svg', 'Tri-fold Brochure sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-trifold-brochure';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/brochures/brochures-2.svg', '/assets/images/sample-products/brochures/brochures-2.svg', 'Tri-fold Brochure sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-trifold-brochure';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/brochures/brochures-3.svg', '/assets/images/sample-products/brochures/brochures-3.svg', 'Tri-fold Brochure sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-trifold-brochure';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/brochures/brochures-4.svg', '/assets/images/sample-products/brochures/brochures-4.svg', 'Tri-fold Brochure sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-trifold-brochure';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/brochures/brochures-1.svg', '/assets/images/sample-products/brochures/brochures-1.svg', 'Bi-fold Brochure sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-bifold-brochure';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/brochures/brochures-2.svg', '/assets/images/sample-products/brochures/brochures-2.svg', 'Bi-fold Brochure sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-bifold-brochure';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/brochures/brochures-3.svg', '/assets/images/sample-products/brochures/brochures-3.svg', 'Bi-fold Brochure sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-bifold-brochure';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/brochures/brochures-4.svg', '/assets/images/sample-products/brochures/brochures-4.svg', 'Bi-fold Brochure sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-bifold-brochure';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/brochures/brochures-1.svg', '/assets/images/sample-products/brochures/brochures-1.svg', 'Corporate Brochure sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-corporate-brochure';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/brochures/brochures-2.svg', '/assets/images/sample-products/brochures/brochures-2.svg', 'Corporate Brochure sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-corporate-brochure';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/brochures/brochures-3.svg', '/assets/images/sample-products/brochures/brochures-3.svg', 'Corporate Brochure sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-corporate-brochure';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/brochures/brochures-4.svg', '/assets/images/sample-products/brochures/brochures-4.svg', 'Corporate Brochure sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-corporate-brochure';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/brochures/brochures-1.svg', '/assets/images/sample-products/brochures/brochures-1.svg', 'Product Catalog Brochure sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-product-catalog-brochure';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/brochures/brochures-2.svg', '/assets/images/sample-products/brochures/brochures-2.svg', 'Product Catalog Brochure sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-product-catalog-brochure';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/brochures/brochures-3.svg', '/assets/images/sample-products/brochures/brochures-3.svg', 'Product Catalog Brochure sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-product-catalog-brochure';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/brochures/brochures-4.svg', '/assets/images/sample-products/brochures/brochures-4.svg', 'Product Catalog Brochure sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-product-catalog-brochure';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/brochures/brochures-1.svg', '/assets/images/sample-products/brochures/brochures-1.svg', 'Real Estate Brochure sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-real-estate-brochure';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/brochures/brochures-2.svg', '/assets/images/sample-products/brochures/brochures-2.svg', 'Real Estate Brochure sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-real-estate-brochure';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/brochures/brochures-3.svg', '/assets/images/sample-products/brochures/brochures-3.svg', 'Real Estate Brochure sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-real-estate-brochure';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/brochures/brochures-4.svg', '/assets/images/sample-products/brochures/brochures-4.svg', 'Real Estate Brochure sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-real-estate-brochure';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/flyers/flyers-1.svg', '/assets/images/sample-products/flyers/flyers-1.svg', 'A4 Promotional Flyer sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-a4-promotional-flyer';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/flyers/flyers-2.svg', '/assets/images/sample-products/flyers/flyers-2.svg', 'A4 Promotional Flyer sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-a4-promotional-flyer';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/flyers/flyers-3.svg', '/assets/images/sample-products/flyers/flyers-3.svg', 'A4 Promotional Flyer sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-a4-promotional-flyer';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/flyers/flyers-4.svg', '/assets/images/sample-products/flyers/flyers-4.svg', 'A4 Promotional Flyer sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-a4-promotional-flyer';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/flyers/flyers-1.svg', '/assets/images/sample-products/flyers/flyers-1.svg', 'A5 Event Flyer sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-a5-event-flyer';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/flyers/flyers-2.svg', '/assets/images/sample-products/flyers/flyers-2.svg', 'A5 Event Flyer sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-a5-event-flyer';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/flyers/flyers-3.svg', '/assets/images/sample-products/flyers/flyers-3.svg', 'A5 Event Flyer sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-a5-event-flyer';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/flyers/flyers-4.svg', '/assets/images/sample-products/flyers/flyers-4.svg', 'A5 Event Flyer sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-a5-event-flyer';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/flyers/flyers-1.svg', '/assets/images/sample-products/flyers/flyers-1.svg', 'Retail Sale Flyer sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-sale-flyer';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/flyers/flyers-2.svg', '/assets/images/sample-products/flyers/flyers-2.svg', 'Retail Sale Flyer sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-sale-flyer';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/flyers/flyers-3.svg', '/assets/images/sample-products/flyers/flyers-3.svg', 'Retail Sale Flyer sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-sale-flyer';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/flyers/flyers-4.svg', '/assets/images/sample-products/flyers/flyers-4.svg', 'Retail Sale Flyer sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-sale-flyer';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/flyers/flyers-1.svg', '/assets/images/sample-products/flyers/flyers-1.svg', 'Restaurant Flyer sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-restaurant-flyer';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/flyers/flyers-2.svg', '/assets/images/sample-products/flyers/flyers-2.svg', 'Restaurant Flyer sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-restaurant-flyer';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/flyers/flyers-3.svg', '/assets/images/sample-products/flyers/flyers-3.svg', 'Restaurant Flyer sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-restaurant-flyer';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/flyers/flyers-4.svg', '/assets/images/sample-products/flyers/flyers-4.svg', 'Restaurant Flyer sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-restaurant-flyer';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/flyers/flyers-1.svg', '/assets/images/sample-products/flyers/flyers-1.svg', 'Real Estate Flyer sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-real-estate-flyer';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/flyers/flyers-2.svg', '/assets/images/sample-products/flyers/flyers-2.svg', 'Real Estate Flyer sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-real-estate-flyer';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/flyers/flyers-3.svg', '/assets/images/sample-products/flyers/flyers-3.svg', 'Real Estate Flyer sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-real-estate-flyer';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/flyers/flyers-4.svg', '/assets/images/sample-products/flyers/flyers-4.svg', 'Real Estate Flyer sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-real-estate-flyer';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/pamphlets/pamphlets-1.svg', '/assets/images/sample-products/pamphlets/pamphlets-1.svg', 'A5 Pamphlet sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-a5-pamphlet';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/pamphlets/pamphlets-2.svg', '/assets/images/sample-products/pamphlets/pamphlets-2.svg', 'A5 Pamphlet sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-a5-pamphlet';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/pamphlets/pamphlets-3.svg', '/assets/images/sample-products/pamphlets/pamphlets-3.svg', 'A5 Pamphlet sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-a5-pamphlet';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/pamphlets/pamphlets-4.svg', '/assets/images/sample-products/pamphlets/pamphlets-4.svg', 'A5 Pamphlet sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-a5-pamphlet';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/pamphlets/pamphlets-1.svg', '/assets/images/sample-products/pamphlets/pamphlets-1.svg', 'Healthcare Pamphlet sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-healthcare-pamphlet';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/pamphlets/pamphlets-2.svg', '/assets/images/sample-products/pamphlets/pamphlets-2.svg', 'Healthcare Pamphlet sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-healthcare-pamphlet';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/pamphlets/pamphlets-3.svg', '/assets/images/sample-products/pamphlets/pamphlets-3.svg', 'Healthcare Pamphlet sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-healthcare-pamphlet';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/pamphlets/pamphlets-4.svg', '/assets/images/sample-products/pamphlets/pamphlets-4.svg', 'Healthcare Pamphlet sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-healthcare-pamphlet';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/pamphlets/pamphlets-1.svg', '/assets/images/sample-products/pamphlets/pamphlets-1.svg', 'Education Pamphlet sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-education-pamphlet';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/pamphlets/pamphlets-2.svg', '/assets/images/sample-products/pamphlets/pamphlets-2.svg', 'Education Pamphlet sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-education-pamphlet';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/pamphlets/pamphlets-3.svg', '/assets/images/sample-products/pamphlets/pamphlets-3.svg', 'Education Pamphlet sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-education-pamphlet';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/pamphlets/pamphlets-4.svg', '/assets/images/sample-products/pamphlets/pamphlets-4.svg', 'Education Pamphlet sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-education-pamphlet';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/pamphlets/pamphlets-1.svg', '/assets/images/sample-products/pamphlets/pamphlets-1.svg', 'Campaign Pamphlet sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-political-pamphlet';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/pamphlets/pamphlets-2.svg', '/assets/images/sample-products/pamphlets/pamphlets-2.svg', 'Campaign Pamphlet sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-political-pamphlet';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/pamphlets/pamphlets-3.svg', '/assets/images/sample-products/pamphlets/pamphlets-3.svg', 'Campaign Pamphlet sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-political-pamphlet';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/pamphlets/pamphlets-4.svg', '/assets/images/sample-products/pamphlets/pamphlets-4.svg', 'Campaign Pamphlet sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-political-pamphlet';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/pamphlets/pamphlets-1.svg', '/assets/images/sample-products/pamphlets/pamphlets-1.svg', 'Menu Pamphlet sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-menu-pamphlet';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/pamphlets/pamphlets-2.svg', '/assets/images/sample-products/pamphlets/pamphlets-2.svg', 'Menu Pamphlet sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-menu-pamphlet';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/pamphlets/pamphlets-3.svg', '/assets/images/sample-products/pamphlets/pamphlets-3.svg', 'Menu Pamphlet sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-menu-pamphlet';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/pamphlets/pamphlets-4.svg', '/assets/images/sample-products/pamphlets/pamphlets-4.svg', 'Menu Pamphlet sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-menu-pamphlet';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/stationery/stationery-1.svg', '/assets/images/sample-products/stationery/stationery-1.svg', 'Corporate Letterhead sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-letterhead';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/stationery/stationery-2.svg', '/assets/images/sample-products/stationery/stationery-2.svg', 'Corporate Letterhead sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-letterhead';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/stationery/stationery-3.svg', '/assets/images/sample-products/stationery/stationery-3.svg', 'Corporate Letterhead sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-letterhead';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/stationery/stationery-4.svg', '/assets/images/sample-products/stationery/stationery-4.svg', 'Corporate Letterhead sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-letterhead';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/stationery/stationery-1.svg', '/assets/images/sample-products/stationery/stationery-1.svg', 'Printed Envelope sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-envelope';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/stationery/stationery-2.svg', '/assets/images/sample-products/stationery/stationery-2.svg', 'Printed Envelope sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-envelope';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/stationery/stationery-3.svg', '/assets/images/sample-products/stationery/stationery-3.svg', 'Printed Envelope sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-envelope';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/stationery/stationery-4.svg', '/assets/images/sample-products/stationery/stationery-4.svg', 'Printed Envelope sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-envelope';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/stationery/stationery-1.svg', '/assets/images/sample-products/stationery/stationery-1.svg', 'ID Card sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-id-card';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/stationery/stationery-2.svg', '/assets/images/sample-products/stationery/stationery-2.svg', 'ID Card sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-id-card';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/stationery/stationery-3.svg', '/assets/images/sample-products/stationery/stationery-3.svg', 'ID Card sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-id-card';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/stationery/stationery-4.svg', '/assets/images/sample-products/stationery/stationery-4.svg', 'ID Card sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-id-card';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/stationery/stationery-1.svg', '/assets/images/sample-products/stationery/stationery-1.svg', 'Invoice Book sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-invoice-book';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/stationery/stationery-2.svg', '/assets/images/sample-products/stationery/stationery-2.svg', 'Invoice Book sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-invoice-book';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/stationery/stationery-3.svg', '/assets/images/sample-products/stationery/stationery-3.svg', 'Invoice Book sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-invoice-book';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/stationery/stationery-4.svg', '/assets/images/sample-products/stationery/stationery-4.svg', 'Invoice Book sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-invoice-book';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/stationery/stationery-1.svg', '/assets/images/sample-products/stationery/stationery-1.svg', 'Complete Stationery Kit sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-stationery-kit';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/stationery/stationery-2.svg', '/assets/images/sample-products/stationery/stationery-2.svg', 'Complete Stationery Kit sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-stationery-kit';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/stationery/stationery-3.svg', '/assets/images/sample-products/stationery/stationery-3.svg', 'Complete Stationery Kit sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-stationery-kit';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/stationery/stationery-4.svg', '/assets/images/sample-products/stationery/stationery-4.svg', 'Complete Stationery Kit sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-stationery-kit';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/banners/banners-1.svg', '/assets/images/sample-products/banners/banners-1.svg', 'Shop Flex Banner sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-shop-flex-banner';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/banners/banners-2.svg', '/assets/images/sample-products/banners/banners-2.svg', 'Shop Flex Banner sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-shop-flex-banner';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/banners/banners-3.svg', '/assets/images/sample-products/banners/banners-3.svg', 'Shop Flex Banner sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-shop-flex-banner';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/banners/banners-4.svg', '/assets/images/sample-products/banners/banners-4.svg', 'Shop Flex Banner sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-shop-flex-banner';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/banners/banners-1.svg', '/assets/images/sample-products/banners/banners-1.svg', 'Event Backdrop Banner sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-event-backdrop-banner';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/banners/banners-2.svg', '/assets/images/sample-products/banners/banners-2.svg', 'Event Backdrop Banner sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-event-backdrop-banner';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/banners/banners-3.svg', '/assets/images/sample-products/banners/banners-3.svg', 'Event Backdrop Banner sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-event-backdrop-banner';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/banners/banners-4.svg', '/assets/images/sample-products/banners/banners-4.svg', 'Event Backdrop Banner sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-event-backdrop-banner';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/banners/banners-1.svg', '/assets/images/sample-products/banners/banners-1.svg', 'Vinyl Banner sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-vinyl-banner';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/banners/banners-2.svg', '/assets/images/sample-products/banners/banners-2.svg', 'Vinyl Banner sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-vinyl-banner';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/banners/banners-3.svg', '/assets/images/sample-products/banners/banners-3.svg', 'Vinyl Banner sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-vinyl-banner';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/banners/banners-4.svg', '/assets/images/sample-products/banners/banners-4.svg', 'Vinyl Banner sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-vinyl-banner';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/banners/banners-1.svg', '/assets/images/sample-products/banners/banners-1.svg', 'Roll-up Standee sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-roll-up-standee';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/banners/banners-2.svg', '/assets/images/sample-products/banners/banners-2.svg', 'Roll-up Standee sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-roll-up-standee';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/banners/banners-3.svg', '/assets/images/sample-products/banners/banners-3.svg', 'Roll-up Standee sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-roll-up-standee';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/banners/banners-4.svg', '/assets/images/sample-products/banners/banners-4.svg', 'Roll-up Standee sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-roll-up-standee';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/banners/banners-1.svg', '/assets/images/sample-products/banners/banners-1.svg', 'Retail Sale Banner sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-sale-banner';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/banners/banners-2.svg', '/assets/images/sample-products/banners/banners-2.svg', 'Retail Sale Banner sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-sale-banner';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/banners/banners-3.svg', '/assets/images/sample-products/banners/banners-3.svg', 'Retail Sale Banner sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-sale-banner';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/banners/banners-4.svg', '/assets/images/sample-products/banners/banners-4.svg', 'Retail Sale Banner sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-sale-banner';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/posters/posters-1.svg', '/assets/images/sample-products/posters/posters-1.svg', 'A3 Poster sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-a3-poster';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/posters/posters-2.svg', '/assets/images/sample-products/posters/posters-2.svg', 'A3 Poster sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-a3-poster';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/posters/posters-3.svg', '/assets/images/sample-products/posters/posters-3.svg', 'A3 Poster sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-a3-poster';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/posters/posters-4.svg', '/assets/images/sample-products/posters/posters-4.svg', 'A3 Poster sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-a3-poster';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/posters/posters-1.svg', '/assets/images/sample-products/posters/posters-1.svg', 'Movie Style Poster sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-movie-style-poster';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/posters/posters-2.svg', '/assets/images/sample-products/posters/posters-2.svg', 'Movie Style Poster sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-movie-style-poster';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/posters/posters-3.svg', '/assets/images/sample-products/posters/posters-3.svg', 'Movie Style Poster sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-movie-style-poster';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/posters/posters-4.svg', '/assets/images/sample-products/posters/posters-4.svg', 'Movie Style Poster sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-movie-style-poster';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/posters/posters-1.svg', '/assets/images/sample-products/posters/posters-1.svg', 'Wall Poster sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-wall-poster';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/posters/posters-2.svg', '/assets/images/sample-products/posters/posters-2.svg', 'Wall Poster sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-wall-poster';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/posters/posters-3.svg', '/assets/images/sample-products/posters/posters-3.svg', 'Wall Poster sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-wall-poster';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/posters/posters-4.svg', '/assets/images/sample-products/posters/posters-4.svg', 'Wall Poster sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-wall-poster';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/posters/posters-1.svg', '/assets/images/sample-products/posters/posters-1.svg', 'Product Poster sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-product-poster';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/posters/posters-2.svg', '/assets/images/sample-products/posters/posters-2.svg', 'Product Poster sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-product-poster';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/posters/posters-3.svg', '/assets/images/sample-products/posters/posters-3.svg', 'Product Poster sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-product-poster';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/posters/posters-4.svg', '/assets/images/sample-products/posters/posters-4.svg', 'Product Poster sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-product-poster';

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/posters/posters-1.svg', '/assets/images/sample-products/posters/posters-1.svg', 'Event Poster sample image 1', 1, 0 FROM products p WHERE p.slug = 'rcs-sample-event-poster';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/posters/posters-2.svg', '/assets/images/sample-products/posters/posters-2.svg', 'Event Poster sample image 2', 0, 1 FROM products p WHERE p.slug = 'rcs-sample-event-poster';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/posters/posters-3.svg', '/assets/images/sample-products/posters/posters-3.svg', 'Event Poster sample image 3', 0, 2 FROM products p WHERE p.slug = 'rcs-sample-event-poster';
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, '/assets/images/sample-products/posters/posters-4.svg', '/assets/images/sample-products/posters/posters-4.svg', 'Event Poster sample image 4', 0, 3 FROM products p WHERE p.slug = 'rcs-sample-event-poster';

-- Keep product card/listing thumbnail aligned to the primary sample image.
UPDATE products p
JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
SET p.image_path = pi.image_path
WHERE p.slug IN (
  'rcs-sample-premium-business-card',
  'rcs-sample-matte-business-card',
  'rcs-sample-gloss-business-card',
  'rcs-sample-spot-uv-business-card',
  'rcs-sample-square-business-card',
  'rcs-sample-trifold-brochure',
  'rcs-sample-bifold-brochure',
  'rcs-sample-corporate-brochure',
  'rcs-sample-product-catalog-brochure',
  'rcs-sample-real-estate-brochure',
  'rcs-sample-a4-promotional-flyer',
  'rcs-sample-a5-event-flyer',
  'rcs-sample-sale-flyer',
  'rcs-sample-restaurant-flyer',
  'rcs-sample-real-estate-flyer',
  'rcs-sample-a5-pamphlet',
  'rcs-sample-healthcare-pamphlet',
  'rcs-sample-education-pamphlet',
  'rcs-sample-political-pamphlet',
  'rcs-sample-menu-pamphlet',
  'rcs-sample-letterhead',
  'rcs-sample-envelope',
  'rcs-sample-id-card',
  'rcs-sample-invoice-book',
  'rcs-sample-stationery-kit',
  'rcs-sample-shop-flex-banner',
  'rcs-sample-event-backdrop-banner',
  'rcs-sample-vinyl-banner',
  'rcs-sample-roll-up-standee',
  'rcs-sample-sale-banner',
  'rcs-sample-a3-poster',
  'rcs-sample-movie-style-poster',
  'rcs-sample-wall-poster',
  'rcs-sample-product-poster',
  'rcs-sample-event-poster'
);

COMMIT;
