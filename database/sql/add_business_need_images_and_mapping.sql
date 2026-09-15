ALTER TABLE business_needs
  ADD COLUMN IF NOT EXISTS image_path VARCHAR(255) NULL AFTER product_ids;

CREATE TABLE IF NOT EXISTS product_business_needs (
  product_id INT UNSIGNED NOT NULL,
  business_need_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (product_id, business_need_id),
  KEY idx_pbn_need (business_need_id),
  KEY idx_pbn_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
