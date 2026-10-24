-- BIKeMo migration for EXISTING database. Back up bikemo before running.
-- Compatible with your XAMPP MariaDB; run in phpMyAdmin > bikemo > SQL.
-- The two new bike columns replace the separate bike_photos table.

ALTER TABLE bikes
    ADD COLUMN IF NOT EXISTS image_path VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS gallery_images TEXT NULL;

-- Your earlier MP1-MP5 update may already have these user columns.
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS photo VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 1;

CREATE TABLE IF NOT EXISTS bike_reviews (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    bike_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_bike (bike_id, user_id),
    INDEX (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- OPTIONAL: migrate paths from your OLD bike_photos table before dropping it.
-- The install guide explains the safe manual migration process.
-- DO NOT drop bike_photos or transactions until you verify all data is preserved.
