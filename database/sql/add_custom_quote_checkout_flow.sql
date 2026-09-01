-- Custom quote checkout/cart/order flow support.
ALTER TABLE custom_quote_requests ADD COLUMN IF NOT EXISTS payment_link_generated_at DATETIME NULL AFTER sent_at;

ALTER TABLE cart_items MODIFY COLUMN product_id INT UNSIGNED NULL;
ALTER TABLE cart_items ADD COLUMN IF NOT EXISTS item_type VARCHAR(30) NOT NULL DEFAULT 'product' AFTER cart_id;
ALTER TABLE cart_items ADD COLUMN IF NOT EXISTS custom_quote_id INT UNSIGNED NULL AFTER item_type;
ALTER TABLE cart_items ADD COLUMN IF NOT EXISTS custom_quote_token VARCHAR(80) NULL AFTER custom_quote_id;
CREATE INDEX IF NOT EXISTS idx_cart_items_custom_quote ON cart_items (custom_quote_id);

ALTER TABLE orders ADD COLUMN IF NOT EXISTS order_type VARCHAR(30) NOT NULL DEFAULT 'normal' AFTER order_id;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS custom_quote_id INT UNSIGNED NULL AFTER order_type;
CREATE INDEX IF NOT EXISTS idx_orders_order_type ON orders (order_type);
CREATE INDEX IF NOT EXISTS idx_orders_custom_quote ON orders (custom_quote_id);

ALTER TABLE order_items MODIFY COLUMN product_id INT UNSIGNED NULL;
ALTER TABLE order_items ADD COLUMN IF NOT EXISTS item_type VARCHAR(30) NOT NULL DEFAULT 'product' AFTER order_id;
ALTER TABLE order_items ADD COLUMN IF NOT EXISTS custom_quote_id INT UNSIGNED NULL AFTER item_type;
CREATE INDEX IF NOT EXISTS idx_order_items_custom_quote ON order_items (custom_quote_id);
