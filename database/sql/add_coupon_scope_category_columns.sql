-- Adds coupon scope support:
-- - all products (scope_type='all')
-- - category-only (scope_type='category', category_id=<id>)

ALTER TABLE coupons
  ADD COLUMN IF NOT EXISTS scope_type VARCHAR(20) NOT NULL DEFAULT 'all' AFTER valid_until,
  ADD COLUMN IF NOT EXISTS category_id BIGINT NULL AFTER scope_type;

CREATE INDEX IF NOT EXISTS idx_coupons_scope_category ON coupons (scope_type, category_id);
