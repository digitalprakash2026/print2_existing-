ALTER TABLE orders
  ADD COLUMN IF NOT EXISTS is_seen TINYINT(1) NOT NULL DEFAULT 0 AFTER status;

CREATE INDEX IF NOT EXISTS idx_orders_is_seen ON orders (is_seen, created_at);
