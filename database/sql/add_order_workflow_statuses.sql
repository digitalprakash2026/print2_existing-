ALTER TABLE orders
  MODIFY COLUMN status ENUM(
    'new_order',
    'received',
    'design_approved',
    'printing',
    'other_process',
    'processing',
    'ready',
    'delivered',
    'cancelled',
    'whatsapp_pending'
  ) NOT NULL DEFAULT 'new_order';

ALTER TABLE order_status_history
  MODIFY COLUMN status ENUM(
    'new_order',
    'received',
    'design_approved',
    'printing',
    'other_process',
    'processing',
    'ready',
    'delivered',
    'cancelled',
    'whatsapp_pending'
  ) NOT NULL;

CREATE TABLE IF NOT EXISTS order_design_approvals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  order_item_id INT NOT NULL,
  design_choice ENUM('upload','rcs') NOT NULL,
  status ENUM('pending_review','issue_found','proof_uploaded','revision_requested','approved') NOT NULL DEFAULT 'pending_review',
  customer_artwork_file_id INT NULL,
  proof_file_id INT NULL,
  admin_note TEXT NULL,
  customer_note TEXT NULL,
  approved_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NULL,
  UNIQUE KEY uniq_order_design_item (order_item_id),
  KEY idx_order_design_order (order_id),
  KEY idx_order_design_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


ALTER TABLE order_design_approvals
  MODIFY COLUMN status ENUM('pending_review','issue_found','proof_uploaded','revision_requested','approved') NOT NULL DEFAULT 'pending_review';
