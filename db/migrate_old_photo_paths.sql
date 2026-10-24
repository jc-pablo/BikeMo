-- OPTIONAL. Run ONLY if bike_photos currently exists and contains records.
-- First run upgrade_images_no_table.sql; back up database before this migration.

UPDATE bikes AS b
SET b.image_path = (
    SELECT p.photo_path
    FROM bike_photos AS p
    WHERE p.bike_id = b.id
    ORDER BY p.id ASC
    LIMIT 1
)
WHERE (b.image_path IS NULL OR b.image_path = '')
  AND EXISTS (SELECT 1 FROM bike_photos AS p WHERE p.bike_id = b.id);

-- MariaDB JSON_ARRAYAGG collects every previously uploaded image path.
UPDATE bikes AS b
SET b.gallery_images = (
    SELECT JSON_ARRAYAGG(p.photo_path)
    FROM bike_photos AS p
    WHERE p.bike_id = b.id
)
WHERE EXISTS (SELECT 1 FROM bike_photos AS p WHERE p.bike_id = b.id)
  AND (b.gallery_images IS NULL OR b.gallery_images = '' OR b.gallery_images = '[]');

-- Then edit one bike in Admin and check its photos are visible.
-- DROP TABLE bike_photos;    <-- MANUAL, only after checking all bikes.
