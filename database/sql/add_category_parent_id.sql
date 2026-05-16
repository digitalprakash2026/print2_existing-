-- Add parent/child category support.
-- Run this before using the Parent Category dropdown in admin.

ALTER TABLE categories
    ADD COLUMN IF NOT EXISTS parent_id INT NULL AFTER id;

ALTER TABLE categories
    ADD INDEX IF NOT EXISTS idx_categories_parent_id (parent_id);

-- Add the foreign key only when it does not already exist.
SET @fk_exists := (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 'categories'
      AND CONSTRAINT_NAME = 'fk_categories_parent_id'
      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);

SET @sql := IF(
    @fk_exists = 0,
    'ALTER TABLE categories ADD CONSTRAINT fk_categories_parent_id FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT ''fk_categories_parent_id already exists'' AS info'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
