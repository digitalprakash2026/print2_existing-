-- MariaDB-safe migration for current dump (server 11.x)
-- Purpose:
-- 1) Fix admin login by normalizing admin password hash
-- 2) Keep product image compatibility (url + image_path)
-- 3) Add sample categories/products/tiers/images safely

START TRANSACTION;

-- -----------------------------------------------------
-- A) ADMIN LOGIN FIX
-- -----------------------------------------------------
-- Default admin credential after running this SQL:
-- email: admin@rcsgraphic.in
-- password: admin@123
UPDATE admin_users
SET password = '$2y$10$icDaIkokaMPjih/Dfq23uOZZSv4BBv0g9zyzQ5Kcw26xEndqUIM52',
    is_active = 1
WHERE email = 'admin@rcsgraphic.in';

INSERT INTO admin_users (name, email, password, role, is_active, created_at)
SELECT 'Admin', 'admin@rcsgraphic.in', '$2y$10$icDaIkokaMPjih/Dfq23uOZZSv4BBv0g9zyzQ5Kcw26xEndqUIM52', 'super', 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM admin_users WHERE email='admin@rcsgraphic.in');

-- -----------------------------------------------------
-- B) SCHEMA COMPAT FOR IMAGE PATH
-- -----------------------------------------------------
ALTER TABLE product_images ADD COLUMN IF NOT EXISTS image_path VARCHAR(500) NULL AFTER product_id;
UPDATE product_images SET image_path = COALESCE(image_path, url) WHERE image_path IS NULL OR image_path='';

-- -----------------------------------------------------
-- C) SAMPLE CATEGORIES (adds if missing)
-- -----------------------------------------------------
INSERT INTO categories (name, slug, description, icon, sort_order, is_active, created_at)
SELECT 'Visiting Cards', 'visiting-cards', 'Business visiting card printing', '💳', 8, 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug='visiting-cards');

-- -----------------------------------------------------
-- D) SAMPLE PRODUCTS (6)
-- -----------------------------------------------------
INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Premium Visiting Card', 'premium-visiting-card', 'Premium quality visiting cards', 'Premium Visiting Card', 500, '/uploads/products/sample_premium_visiting_card.jpg', 1, 10, NOW()
FROM categories c WHERE c.slug='visiting-cards'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug='premium-visiting-card');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Matte Visiting Card', 'matte-visiting-card', 'Matte finish visiting cards', 'Matte Visiting Card', 500, '/uploads/products/sample_matte_visiting_card.jpg', 1, 11, NOW()
FROM categories c WHERE c.slug='visiting-cards'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug='matte-visiting-card');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Tri-fold Brochure', 'tri-fold-brochure', 'Tri-fold brochure printing', 'Tri-fold Brochure', 1000, '/uploads/products/sample_trifold_brochure.jpg', 1, 12, NOW()
FROM categories c WHERE c.slug='brochures'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug='tri-fold-brochure');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'A4 Flyer', 'a4-flyer', 'A4 flyer printing service', 'A4 Flyer', 500, '/uploads/products/sample_a4_flyer.jpg', 1, 13, NOW()
FROM categories c WHERE c.slug='flyers'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug='a4-flyer');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'A3 Poster', 'a3-poster', 'A3 poster printing', 'A3 Poster', 1000, '/uploads/products/sample_a3_poster.jpg', 1, 14, NOW()
FROM categories c WHERE c.slug='posters'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug='a3-poster');

INSERT INTO products (category_id, name, slug, description, meta_title, design_fee, image_path, is_active, sort_order, created_at)
SELECT c.id, 'Business Flyer', 'business-flyer', 'Business promotional flyers', 'Business Flyer', 500, '/uploads/products/sample_business_flyer.jpg', 1, 15, NOW()
FROM categories c WHERE c.slug='flyers'
AND NOT EXISTS (SELECT 1 FROM products WHERE slug='business-flyer');

-- -----------------------------------------------------
-- E) SAMPLE QUANTITY TIERS (1000..10000)
-- -----------------------------------------------------
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
WHERE p.slug IN ('premium-visiting-card','matte-visiting-card','tri-fold-brochure','a4-flyer','a3-poster','business-flyer')
ON DUPLICATE KEY UPDATE price=VALUES(price);

-- -----------------------------------------------------
-- F) SAMPLE GALLERY IMAGES (2 per sample product)
-- -----------------------------------------------------
INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order, created_at)
SELECT p.id, CONCAT('/uploads/products/', p.slug, '_1.jpg'), CONCAT('/uploads/products/', p.slug, '_1.jpg'), CONCAT(p.name, ' main image'), 1, 0, NOW()
FROM products p
WHERE p.slug IN ('premium-visiting-card','matte-visiting-card','tri-fold-brochure','a4-flyer','a3-poster','business-flyer')
AND NOT EXISTS (
  SELECT 1 FROM product_images pi WHERE pi.product_id=p.id AND pi.sort_order=0
);

INSERT INTO product_images (product_id, image_path, url, alt_text, is_primary, sort_order, created_at)
SELECT p.id, CONCAT('/uploads/products/', p.slug, '_2.jpg'), CONCAT('/uploads/products/', p.slug, '_2.jpg'), CONCAT(p.name, ' gallery image'), 0, 1, NOW()
FROM products p
WHERE p.slug IN ('premium-visiting-card','matte-visiting-card','tri-fold-brochure','a4-flyer','a3-poster','business-flyer')
AND NOT EXISTS (
  SELECT 1 FROM product_images pi WHERE pi.product_id=p.id AND pi.sort_order=1
);

UPDATE products p
JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
SET p.image_path = COALESCE(pi.image_path, pi.url)
WHERE p.slug IN ('premium-visiting-card','matte-visiting-card','tri-fold-brochure','a4-flyer','a3-poster','business-flyer');

COMMIT;
