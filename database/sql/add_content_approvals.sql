-- Adds approval workflow columns for admin-managed content.
-- Normal Admin submissions should stay inactive until Super Admin approval.

ALTER TABLE products
  ADD COLUMN approval_status ENUM('draft','pending','approved','rejected') NOT NULL DEFAULT 'approved' AFTER is_active,
  ADD COLUMN submitted_by INT NULL AFTER approval_status,
  ADD COLUMN submitted_at DATETIME NULL AFTER submitted_by,
  ADD COLUMN approved_by INT NULL AFTER submitted_at,
  ADD COLUMN approved_at DATETIME NULL AFTER approved_by,
  ADD COLUMN rejected_by INT NULL AFTER approved_at,
  ADD COLUMN rejected_at DATETIME NULL AFTER rejected_by,
  ADD COLUMN approval_note TEXT NULL AFTER rejected_at;

ALTER TABLE categories
  ADD COLUMN approval_status ENUM('draft','pending','approved','rejected') NOT NULL DEFAULT 'approved' AFTER is_active,
  ADD COLUMN submitted_by INT NULL AFTER approval_status,
  ADD COLUMN submitted_at DATETIME NULL AFTER submitted_by,
  ADD COLUMN approved_by INT NULL AFTER submitted_at,
  ADD COLUMN approved_at DATETIME NULL AFTER approved_by,
  ADD COLUMN rejected_by INT NULL AFTER approved_at,
  ADD COLUMN rejected_at DATETIME NULL AFTER rejected_by,
  ADD COLUMN approval_note TEXT NULL AFTER rejected_at;

ALTER TABLE coupons
  ADD COLUMN approval_status ENUM('draft','pending','approved','rejected') NOT NULL DEFAULT 'approved' AFTER is_active,
  ADD COLUMN submitted_by INT NULL AFTER approval_status,
  ADD COLUMN submitted_at DATETIME NULL AFTER submitted_by,
  ADD COLUMN approved_by INT NULL AFTER submitted_at,
  ADD COLUMN approved_at DATETIME NULL AFTER approved_by,
  ADD COLUMN rejected_by INT NULL AFTER approved_at,
  ADD COLUMN rejected_at DATETIME NULL AFTER rejected_by,
  ADD COLUMN approval_note TEXT NULL AFTER rejected_at;

ALTER TABLE home_deals
  ADD COLUMN approval_status ENUM('draft','pending','approved','rejected') NOT NULL DEFAULT 'approved' AFTER is_active,
  ADD COLUMN submitted_by INT NULL AFTER approval_status,
  ADD COLUMN submitted_at DATETIME NULL AFTER submitted_by,
  ADD COLUMN approved_by INT NULL AFTER submitted_at,
  ADD COLUMN approved_at DATETIME NULL AFTER approved_by,
  ADD COLUMN rejected_by INT NULL AFTER approved_at,
  ADD COLUMN rejected_at DATETIME NULL AFTER rejected_by,
  ADD COLUMN approval_note TEXT NULL AFTER rejected_at;
