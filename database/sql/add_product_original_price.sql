-- Adds admin-managed original/MRP price for dynamic product discount badges.
-- Run once before using the Original Price / MRP field in product admin.

ALTER TABLE products
  ADD COLUMN IF NOT EXISTS original_price DECIMAL(10,2) NULL DEFAULT NULL AFTER design_fee;
