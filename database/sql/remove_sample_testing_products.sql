-- =====================================================
-- Remove RCS sample testing products
-- Safe cleanup for database/sql/add_sample_testing_products.sql
-- This removes only products whose slugs start with rcs-sample-.
-- Categories and real products are intentionally left untouched.
-- =====================================================

START TRANSACTION;

DELETE FROM product_images
WHERE product_id IN (
  SELECT id FROM products WHERE slug LIKE 'rcs-sample-%'
);

DELETE FROM product_quantity_tiers
WHERE product_id IN (
  SELECT id FROM products WHERE slug LIKE 'rcs-sample-%'
);

DELETE FROM products
WHERE slug LIKE 'rcs-sample-%';

COMMIT;
