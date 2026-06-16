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
