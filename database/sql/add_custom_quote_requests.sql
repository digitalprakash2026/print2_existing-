CREATE TABLE IF NOT EXISTS custom_quote_requests (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  request_code VARCHAR(40) NOT NULL UNIQUE,
  user_id INT UNSIGNED NULL,
  customer_name VARCHAR(160) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  email VARCHAR(180) NULL,
  product_name VARCHAR(180) NOT NULL,
  size_dimension VARCHAR(160) NULL,
  material_type VARCHAR(160) NULL,
  quantity VARCHAR(80) NULL,
  instructions TEXT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'new',
  admin_notes TEXT NULL,
  quoted_amount DECIMAL(12,2) NULL,
  currency VARCHAR(10) NOT NULL DEFAULT 'INR',
  source_page VARCHAR(255) NULL,
  ip_address VARCHAR(64) NULL,
  user_agent VARCHAR(255) NULL,
  order_id INT UNSIGNED NULL,
  customer_type VARCHAR(30) NOT NULL DEFAULT 'guest',
  quote_token VARCHAR(80) NULL,
  quote_note TEXT NULL,
  payment_status VARCHAR(40) NOT NULL DEFAULT 'not_required',
  sent_at DATETIME NULL,
  payment_link_generated_at DATETIME NULL,
  approved_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_custom_quote_status (status, created_at),
  KEY idx_custom_quote_phone (phone),
  KEY idx_custom_quote_user (user_id),
  KEY idx_custom_quote_token (quote_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE custom_quote_requests ADD COLUMN IF NOT EXISTS customer_type VARCHAR(30) NOT NULL DEFAULT 'guest' AFTER order_id;
ALTER TABLE custom_quote_requests ADD COLUMN IF NOT EXISTS quote_token VARCHAR(80) NULL AFTER customer_type;
ALTER TABLE custom_quote_requests ADD COLUMN IF NOT EXISTS quote_note TEXT NULL AFTER quote_token;
ALTER TABLE custom_quote_requests ADD COLUMN IF NOT EXISTS payment_status VARCHAR(40) NOT NULL DEFAULT 'not_required' AFTER quote_note;
ALTER TABLE custom_quote_requests ADD COLUMN IF NOT EXISTS sent_at DATETIME NULL AFTER payment_status;
ALTER TABLE custom_quote_requests ADD COLUMN IF NOT EXISTS admin_notes TEXT NULL AFTER status;
ALTER TABLE custom_quote_requests ADD COLUMN IF NOT EXISTS quoted_amount DECIMAL(12,2) NULL AFTER admin_notes;
ALTER TABLE custom_quote_requests ADD COLUMN IF NOT EXISTS currency VARCHAR(10) NOT NULL DEFAULT 'INR' AFTER quoted_amount;
ALTER TABLE custom_quote_requests ADD COLUMN IF NOT EXISTS order_id INT UNSIGNED NULL AFTER user_agent;
ALTER TABLE custom_quote_requests ADD COLUMN IF NOT EXISTS approved_at DATETIME NULL AFTER sent_at;
ALTER TABLE custom_quote_requests ADD COLUMN IF NOT EXISTS payment_link_generated_at DATETIME NULL AFTER sent_at;

-- Estimated delivery is no longer part of the custom quote workflow.
ALTER TABLE custom_quote_requests DROP COLUMN IF EXISTS estimated_delivery;
