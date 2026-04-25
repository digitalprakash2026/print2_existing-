-- Add persistent customer profile + default billing fields on users table.
-- Run this once before enabling profile billing update in production.

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS billing_legal_name VARCHAR(160) NULL AFTER company,
    ADD COLUMN IF NOT EXISTS gst_no VARCHAR(20) NULL AFTER billing_legal_name,
    ADD COLUMN IF NOT EXISTS billing_address_line1 VARCHAR(255) NULL AFTER gst_no,
    ADD COLUMN IF NOT EXISTS billing_address_line2 VARCHAR(255) NULL AFTER billing_address_line1,
    ADD COLUMN IF NOT EXISTS billing_city VARCHAR(120) NULL AFTER billing_address_line2,
    ADD COLUMN IF NOT EXISTS billing_state VARCHAR(120) NULL AFTER billing_city,
    ADD COLUMN IF NOT EXISTS billing_pincode VARCHAR(12) NULL AFTER billing_state,
    ADD COLUMN IF NOT EXISTS profile_updated_at DATETIME NULL AFTER billing_pincode;

CREATE INDEX idx_users_gst_no ON users (gst_no);
