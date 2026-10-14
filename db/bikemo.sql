-- Schema reference only: existing BikeMo demo still stores data in browser localStorage.
CREATE DATABASE IF NOT EXISTS bikemo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bikemo;
CREATE TABLE IF NOT EXISTS users (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE, phone VARCHAR(30) NOT NULL,
 password_hash VARCHAR(255) NOT NULL, role ENUM('user','admin') NOT NULL DEFAULT 'user',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS bikes (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL,
 type ENUM('Mountain','Road','City','Electric','Folding','BMX') NOT NULL,
 color CHAR(7) NOT NULL DEFAULT '#ff6a1a', rate_per_hour DECIMAL(10,2) NOT NULL,
 rate_per_day DECIMAL(10,2) NOT NULL, gears VARCHAR(80) NOT NULL DEFAULT '—',
 frame VARCHAR(80) NOT NULL DEFAULT '—', weight VARCHAR(40) NOT NULL DEFAULT '—',
 description TEXT, status ENUM('available','rented','maintenance') NOT NULL DEFAULT 'available'
);
CREATE TABLE IF NOT EXISTS transactions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, booking_code VARCHAR(40) NOT NULL UNIQUE,
 user_id BIGINT UNSIGNED NOT NULL, bike_id BIGINT UNSIGNED NOT NULL,
 start_at DATETIME NOT NULL, end_at DATETIME NOT NULL, quantity INT UNSIGNED NOT NULL,
 unit ENUM('hours','days','weeks') NOT NULL, total DECIMAL(10,2) NOT NULL,
 payment_method VARCHAR(80) NOT NULL, status ENUM('Ongoing','Completed') NOT NULL DEFAULT 'Ongoing',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, returned_at DATETIME NULL,
 FOREIGN KEY (user_id) REFERENCES users(id), FOREIGN KEY (bike_id) REFERENCES bikes(id)
);
