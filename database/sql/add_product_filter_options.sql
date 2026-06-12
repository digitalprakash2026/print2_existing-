-- DB-backed product filters for All Categories page.
-- Supports Paper Type, Lamination and Finishing selections per product.

CREATE TABLE IF NOT EXISTS product_filter_options (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  group_slug VARCHAR(64) NOT NULL,
  group_label VARCHAR(100) NOT NULL,
  option_slug VARCHAR(64) NOT NULL,
  label VARCHAR(100) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY uq_product_filter_option (group_slug, option_slug),
  KEY idx_product_filter_group (group_slug, is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS product_filter_map (
  product_id INT UNSIGNED NOT NULL,
  option_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (product_id, option_id),
  KEY idx_product_filter_map_option (option_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO product_filter_options (group_slug, group_label, option_slug, label, sort_order, is_active)
VALUES
('paper_type', 'Paper Type', 'non-tearable', 'Non Tearable', 10, 1),
('paper_type', 'Paper Type', 'glossy-paper', 'Glossy Paper', 20, 1),
('paper_type', 'Paper Type', 'matt-paper', 'Matt Paper', 30, 1),
('paper_type', 'Paper Type', 'texture-paper', 'Texture Paper', 40, 1),
('paper_type', 'Paper Type', 'craft-paper', 'Craft Paper', 50, 1),
('lamination', 'Lamination', 'glossy', 'Glossy', 10, 1),
('lamination', 'Lamination', 'matt', 'Matt', 20, 1),
('lamination', 'Lamination', 'velvet', 'Velvet', 30, 1),
('finishing', 'Finishing', 'spot-uv', 'Spot UV', 10, 1),
('finishing', 'Finishing', 'dripoff-uv', 'Dripoff UV', 20, 1),
('finishing', 'Finishing', 'foil-stamping', 'Foil Stamping', 30, 1),
('finishing', 'Finishing', 'die-cutting', 'Die Cutting', 40, 1)
ON DUPLICATE KEY UPDATE
  group_label = VALUES(group_label),
  label = VALUES(label),
  sort_order = VALUES(sort_order),
  is_active = VALUES(is_active);
