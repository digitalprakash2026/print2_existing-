-- =====================================================
-- Product Management / Quantity Tier / Images SQL
-- Safe for shared hosting MySQL 5.7+ / 8+
-- =====================================================

-- 1) Required schema updates (run once)
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

ALTER TABLE products
  ADD COLUMN IF NOT EXISTS image_path VARCHAR(255) NULL AFTER design_fee;

-- Backfill legacy URL column into image_path if needed
UPDATE product_images SET image_path = COALESCE(image_path, url) WHERE (image_path IS NULL OR image_path = '') AND url IS NOT NULL;

-- 2) Sample categories
INSERT INTO categories (name, slug, sort_order, is_active)
VALUES
('Visiting Cards', 'visiting-cards', 1, 1),
('Brochures', 'brochures', 2, 1),
('Flyers', 'flyers', 3, 1),
('Posters', 'posters', 4, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name), sort_order=VALUES(sort_order), is_active=VALUES(is_active);

-- 3) Sample products (8 records)
INSERT INTO products (name, slug, category_id, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT 'Premium Visiting Card', 'premium-visiting-card', c.id,
       'Premium quality visiting card with smooth finish.', 'Premium Visiting Card', 500,
       '/uploads/products/sample_premium_visiting_card.jpg', 1, 1, NOW()
FROM categories c WHERE c.slug='visiting-cards'
ON DUPLICATE KEY UPDATE description=VALUES(description), design_fee=VALUES(design_fee), image_path=VALUES(image_path), is_active=1;

INSERT INTO products (name, slug, category_id, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT 'Matte Visiting Card', 'matte-visiting-card', c.id,
       'Elegant matte laminated visiting card.', 'Matte Visiting Card', 500,
       '/uploads/products/sample_matte_visiting_card.jpg', 1, 2, NOW()
FROM categories c WHERE c.slug='visiting-cards'
ON DUPLICATE KEY UPDATE description=VALUES(description), design_fee=VALUES(design_fee), image_path=VALUES(image_path), is_active=1;

INSERT INTO products (name, slug, category_id, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT 'Tri-fold Brochure', 'tri-fold-brochure', c.id,
       'Professional tri-fold brochure printing.', 'Tri-fold Brochure', 1000,
       '/uploads/products/sample_trifold_brochure.jpg', 1, 1, NOW()
FROM categories c WHERE c.slug='brochures'
ON DUPLICATE KEY UPDATE description=VALUES(description), design_fee=VALUES(design_fee), image_path=VALUES(image_path), is_active=1;

INSERT INTO products (name, slug, category_id, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT 'Bi-fold Brochure', 'bi-fold-brochure', c.id,
       'High-quality bi-fold brochure for marketing.', 'Bi-fold Brochure', 1000,
       '/uploads/products/sample_bifold_brochure.jpg', 1, 2, NOW()
FROM categories c WHERE c.slug='brochures'
ON DUPLICATE KEY UPDATE description=VALUES(description), design_fee=VALUES(design_fee), image_path=VALUES(image_path), is_active=1;

INSERT INTO products (name, slug, category_id, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT 'A4 Flyer', 'a4-flyer', c.id,
       'Single-page A4 flyer printing.', 'A4 Flyer', 500,
       '/uploads/products/sample_a4_flyer.jpg', 1, 1, NOW()
FROM categories c WHERE c.slug='flyers'
ON DUPLICATE KEY UPDATE description=VALUES(description), design_fee=VALUES(design_fee), image_path=VALUES(image_path), is_active=1;

INSERT INTO products (name, slug, category_id, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT 'Business Flyer', 'business-flyer', c.id,
       'Promotional business flyer with vibrant colors.', 'Business Flyer', 500,
       '/uploads/products/sample_business_flyer.jpg', 1, 2, NOW()
FROM categories c WHERE c.slug='flyers'
ON DUPLICATE KEY UPDATE description=VALUES(description), design_fee=VALUES(design_fee), image_path=VALUES(image_path), is_active=1;

INSERT INTO products (name, slug, category_id, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT 'A3 Poster', 'a3-poster', c.id,
       'A3 size poster printing for events and promotions.', 'A3 Poster', 1000,
       '/uploads/products/sample_a3_poster.jpg', 1, 1, NOW()
FROM categories c WHERE c.slug='posters'
ON DUPLICATE KEY UPDATE description=VALUES(description), design_fee=VALUES(design_fee), image_path=VALUES(image_path), is_active=1;

INSERT INTO products (name, slug, category_id, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT 'Event Poster', 'event-poster', c.id,
       'Event announcement poster with rich colors.', 'Event Poster', 1000,
       '/uploads/products/sample_event_poster.jpg', 1, 2, NOW()
FROM categories c WHERE c.slug='posters'
ON DUPLICATE KEY UPDATE description=VALUES(description), design_fee=VALUES(design_fee), image_path=VALUES(image_path), is_active=1;

-- 4) Sample tier pricing for each sample product
INSERT INTO product_quantity_tiers (product_id, quantity, price, created_at)
SELECT p.id, q.qty, q.price, NOW()
FROM products p
JOIN (
  SELECT 1000 AS qty, 2000.00 AS price
  UNION ALL SELECT 2000, 3500.00
  UNION ALL SELECT 3000, 4800.00
  UNION ALL SELECT 4000, 5900.00
  UNION ALL SELECT 5000, 7000.00
  UNION ALL SELECT 6000, 8200.00
  UNION ALL SELECT 7000, 9300.00
  UNION ALL SELECT 8000, 10400.00
  UNION ALL SELECT 9000, 11500.00
  UNION ALL SELECT 10000, 12600.00
) q
WHERE p.slug IN (
  'premium-visiting-card','matte-visiting-card','tri-fold-brochure','bi-fold-brochure',
  'a4-flyer','business-flyer','a3-poster','event-poster'
)
ON DUPLICATE KEY UPDATE price=VALUES(price);

-- 5) Sample gallery images (2 per product)
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, CONCAT('/uploads/products/', p.slug, '_1.jpg'), CONCAT('/uploads/products/', p.slug, '_1.jpg'), CONCAT(p.name, ' main image'), 1, 0
FROM products p
WHERE p.slug IN (
  'premium-visiting-card','matte-visiting-card','tri-fold-brochure','bi-fold-brochure',
  'a4-flyer','business-flyer','a3-poster','event-poster'
)
ON DUPLICATE KEY UPDATE image_path=VALUES(image_path), url=VALUES(url), alt_text=VALUES(alt_text), is_primary=VALUES(is_primary);

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order)
SELECT p.id, CONCAT('/uploads/products/', p.slug, '_2.jpg'), CONCAT('/uploads/products/', p.slug, '_2.jpg'), CONCAT(p.name, ' gallery image'), 0, 1
FROM products p
WHERE p.slug IN (
  'premium-visiting-card','matte-visiting-card','tri-fold-brochure','bi-fold-brochure',
  'a4-flyer','business-flyer','a3-poster','event-poster'
)
ON DUPLICATE KEY UPDATE image_path=VALUES(image_path), url=VALUES(url), alt_text=VALUES(alt_text), is_primary=VALUES(is_primary);

-- Keep products.image_path in sync with primary image
UPDATE products p
JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
SET p.image_path = pi.image_path;
