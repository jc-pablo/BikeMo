-- Run once in phpMyAdmin, database bikemo. BACK UP FIRST.
-- Keep the existing bikes, rentals, transactions and users data.
ALTER TABLE users
    ADD COLUMN photo VARCHAR(255) NULL,
    ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1;

CREATE TABLE bike_photos (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    bike_id INT NOT NULL,
    photo_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (bike_id)
);

CREATE TABLE bike_reviews (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    bike_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_bike (bike_id, user_id),
    INDEX (user_id)
);
