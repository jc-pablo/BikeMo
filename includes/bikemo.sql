CREATE TABLE IF NOT EXISTS `users` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(120) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    `photo` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `bikes` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `type` ENUM('Mountain', 'Road', 'City', 'Electric', 'Folding', 'BMX') NOT NULL,
    `color` VARCHAR(7) NOT NULL DEFAULT '#ff6a1a',
    `rate_per_hour` DECIMAL(10,2) NOT NULL,
    `rate_per_day` DECIMAL(10,2) NOT NULL,
    `gears` VARCHAR(40) DEFAULT NULL,
    `frame` VARCHAR(40) DEFAULT NULL,
    `weight` VARCHAR(20) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `status` ENUM('available', 'rented', 'maintenance') NOT NULL DEFAULT 'available',
    `image_path` VARCHAR(255) DEFAULT NULL,
    `gallery_images` TEXT DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `rentals` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(20) NOT NULL,
    `user_id` INT NOT NULL,
    `bike_id` INT NOT NULL,
    `start_time` DATETIME NOT NULL,
    `end_time` DATETIME NOT NULL,
    `duration_value` INT NOT NULL,
    `order_code` VARCHAR(20) NOT NULL,
    `unit` ENUM('hours', 'days', 'weeks') NOT NULL,
    `total` DECIMAL(10,2) NOT NULL,
    `late_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `payment` VARCHAR(40) NOT NULL DEFAULT 'Cash on pick-up',
    `status` ENUM('Ongoing', 'Completed') NOT NULL DEFAULT 'Ongoing',
    `returned_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_booking_code` (`code`),
    KEY `idx_rentals_user` (`user_id`),
    KEY `idx_rentals_bike` (`bike_id`),
    KEY `idx_rentals_order` (`order_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `bike_reviews` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `bike_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `rating` TINYINT NOT NULL,
    `comment` TEXT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_user_bike` (`bike_id`, `user_id`),
    KEY `idx_reviews_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `expenses` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `expense_month` DATE NOT NULL,
    `description` VARCHAR(160) NOT NULL,
    `category` VARCHAR(40) NOT NULL DEFAULT 'Other',
    `amount` DECIMAL(10,2) NOT NULL,
    `notes` VARCHAR(500) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_expenses_month` (`expense_month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;