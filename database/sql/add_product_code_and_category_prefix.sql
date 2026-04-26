-- Add product code support and category code prefix support.
-- Run before enabling category-wise product code generation in admin.

ALTER TABLE categories
    ADD COLUMN IF NOT EXISTS code_prefix VARCHAR(24) NULL AFTER slug;

ALTER TABLE products
    ADD COLUMN IF NOT EXISTS product_code VARCHAR(64) NULL AFTER category_id;

CREATE UNIQUE INDEX idx_products_product_code ON products (product_code);
