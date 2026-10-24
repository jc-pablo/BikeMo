-- BikeMo reference schema for a brand-new install.
-- If you already have users/bikes/rentals in phpMyAdmin, DO NOT re-import this.
-- Run db/upgrade_images_no_table.sql instead (after backup).

CREATE DATABASE IF NOT EXISTS bikemo CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE bikemo;

CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    photo VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bikes (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('Mountain', 'Road', 'City', 'Electric', 'Folding', 'BMX') NOT NULL,
    color VARCHAR(7) NOT NULL DEFAULT '#ff6a1a',
    rate_per_hour DECIMAL(10,2) NOT NULL,
    rate_per_day DECIMAL(10,2) NOT NULL,
    gears VARCHAR(40) DEFAULT NULL,
    frame VARCHAR(40) DEFAULT NULL,
    weight VARCHAR(20) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    status ENUM('available', 'rented', 'maintenance') NOT NULL DEFAULT 'available',
    image_path VARCHAR(255) DEFAULT NULL,
    gallery_images TEXT DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS rentals (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    bike_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    qty INT NOT NULL,
    unit ENUM('hours', 'days', 'weeks') NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    payment VARCHAR(40) NOT NULL DEFAULT 'Cash on pick-up',
    status ENUM('Ongoing', 'Completed') NOT NULL DEFAULT 'Ongoing',
    returned_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX(user_id), INDEX(bike_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bike_reviews (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    bike_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_bike (bike_id, user_id)
) ENGINE=InnoDB;
