-- Adds verified customer reviews for products.
-- Customers can review products from delivered orders; admins can approve, reject and feature reviews.

CREATE TABLE IF NOT EXISTS product_reviews (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  order_id INT UNSIGNED NULL,
  order_item_id INT UNSIGNED NULL,
  rating TINYINT UNSIGNED NOT NULL,
  comment TEXT NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  admin_note VARCHAR(255) NULL,
  approved_by INT UNSIGNED NULL,
  approved_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_product_reviews_product_user (product_id, user_id),
  KEY idx_product_reviews_product_status (product_id, status),
  KEY idx_product_reviews_featured_status (is_featured, status),
  KEY idx_product_reviews_user_created (user_id, created_at),
  KEY idx_product_reviews_order_item (order_item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Compatibility for databases where product_reviews was created before moderation metadata was added.
ALTER TABLE product_reviews ADD COLUMN IF NOT EXISTS status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending' AFTER comment;
ALTER TABLE product_reviews ADD COLUMN IF NOT EXISTS is_featured TINYINT(1) NOT NULL DEFAULT 0 AFTER status;
ALTER TABLE product_reviews ADD COLUMN IF NOT EXISTS admin_note VARCHAR(255) NULL AFTER is_featured;
ALTER TABLE product_reviews ADD COLUMN IF NOT EXISTS approved_by INT UNSIGNED NULL AFTER admin_note;
ALTER TABLE product_reviews ADD COLUMN IF NOT EXISTS approved_at DATETIME NULL AFTER approved_by;
ALTER TABLE product_reviews ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;
