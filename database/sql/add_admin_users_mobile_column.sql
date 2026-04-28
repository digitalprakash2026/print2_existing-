-- Adds mobile number support to admin users module.
-- Run this once on your database if `admin_users.mobile` does not exist.

ALTER TABLE admin_users
  ADD COLUMN IF NOT EXISTS mobile VARCHAR(20) NULL AFTER email;

-- Optional but recommended uniqueness guard for active admin records.
-- If your MariaDB/MySQL version does not support IF NOT EXISTS for indexes,
-- run manually after checking existing indexes.
CREATE UNIQUE INDEX IF NOT EXISTS ux_admin_users_mobile ON admin_users (mobile);
