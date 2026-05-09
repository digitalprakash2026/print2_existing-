-- Add square category thumbnails for homepage/admin category cards.
-- Run this before using the category image upload fields in admin.

ALTER TABLE categories
    ADD COLUMN IF NOT EXISTS image_path VARCHAR(255) NULL AFTER icon,
    ADD COLUMN IF NOT EXISTS image_alt VARCHAR(160) NULL AFTER image_path;

-- Give existing categories a relevant square default so the homepage layout looks polished
-- immediately after the migration. Admins can replace any of these from Manage Categories.
UPDATE categories
SET image_path = CASE
        WHEN LOWER(CONCAT_WS(' ', slug, name)) LIKE '%visiting%' OR LOWER(CONCAT_WS(' ', slug, name)) LIKE '%business-card%' THEN '/assets/img/categories/visiting-cards.svg'
        WHEN LOWER(CONCAT_WS(' ', slug, name)) LIKE '%brochure%' THEN '/assets/img/categories/brochures.svg'
        WHEN LOWER(CONCAT_WS(' ', slug, name)) LIKE '%flyer%' THEN '/assets/img/categories/flyers.svg'
        WHEN LOWER(CONCAT_WS(' ', slug, name)) LIKE '%poster%' THEN '/assets/img/categories/posters.svg'
        WHEN LOWER(CONCAT_WS(' ', slug, name)) LIKE '%calendar%' THEN '/assets/img/categories/calendars.svg'
        WHEN LOWER(CONCAT_WS(' ', slug, name)) LIKE '%sticker%' OR LOWER(CONCAT_WS(' ', slug, name)) LIKE '%label%' THEN '/assets/img/categories/stickers.svg'
        WHEN LOWER(CONCAT_WS(' ', slug, name)) LIKE '%banner%' OR LOWER(CONCAT_WS(' ', slug, name)) LIKE '%standee%' THEN '/assets/img/categories/banners.svg'
        WHEN LOWER(CONCAT_WS(' ', slug, name)) LIKE '%letter%' OR LOWER(CONCAT_WS(' ', slug, name)) LIKE '%stationery%' THEN '/assets/img/categories/letterheads.svg'
        ELSE '/assets/img/categories/print-category.svg'
    END,
    image_alt = COALESCE(NULLIF(image_alt, ''), CONCAT(name, ' category image'))
WHERE image_path IS NULL OR image_path = '';
