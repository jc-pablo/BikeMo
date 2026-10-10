CREATE DATABASE IF NOT EXISTS bikemo CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE bikemo;

DROP TABLE IF EXISTS rentals;
DROP TABLE IF EXISTS bikes;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  email      VARCHAR(120) NOT NULL UNIQUE,
  phone      VARCHAR(20)  NOT NULL,
  password   VARCHAR(255) NOT NULL,              -- hashed with password_hash()
  role       ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE bikes (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100) NOT NULL,
  type          ENUM('Mountain','Road','City','Electric','Folding','BMX') NOT NULL,
  color         VARCHAR(7)   NOT NULL DEFAULT '#ff6a1a',
  rate_per_hour DECIMAL(10,2) NOT NULL,
  rate_per_day  DECIMAL(10,2) NOT NULL,
  gears         VARCHAR(40),
  frame         VARCHAR(40),
  weight        VARCHAR(20),
  description   TEXT,
  status        ENUM('available','rented','maintenance') NOT NULL DEFAULT 'available'
);

CREATE TABLE rentals (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  code         VARCHAR(20) NOT NULL UNIQUE,      -- reservation code e.g. BM-8K3X21
  user_id      INT NOT NULL,
  bike_id      INT NOT NULL,
  start_time   DATETIME NOT NULL,
  end_time     DATETIME NOT NULL,
  qty          INT NOT NULL,                     -- how many hours/days/weeks
  unit         ENUM('hours','days','weeks') NOT NULL,
  total        DECIMAL(10,2) NOT NULL,
  payment      VARCHAR(40) NOT NULL DEFAULT 'Cash on pick-up',
  status       ENUM('Ongoing','Completed') NOT NULL DEFAULT 'Ongoing',
  returned_at  DATETIME NULL,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (bike_id) REFERENCES bikes(id)
);

INSERT INTO users (name, email, phone, password, role) VALUES
('BikeMo Admin', 'admin@bikemo.com', '09000000000', '$2y$10$zFWKVJrP5fHJ.fRKikIo3uJOKhtsUh04G.L5vC2inDFO9C7CN3zfq', 'admin'),
('Demo Rider',   'user@bikemo.com',  '09171234567', '$2y$10$T6QCAu7Kopk4zJLyFcobi.Y6ZB9kDawGjMxoR.0K0F9lCnGY0NaD.', 'user');

INSERT INTO bikes (name, type, color, rate_per_hour, rate_per_day, gears, frame, weight, description, status) VALUES
('Trailblazer X1',  'Mountain', '#ff6a1a',  80, 450, '21-speed',     'Aluminum', '14.5 kg', 'Rugged suspension bike built for trails, hills and rough roads.', 'available'),
('Velocity Pro',    'Road',     '#e63946',  90, 500, '18-speed',     'Carbon',   '9.8 kg',  'Ultra-light road bike for speed lovers and long smooth rides.', 'available'),
('Urban Cruiser',   'City',     '#12b5a6',  60, 350, '7-speed',      'Steel',    '13 kg',   'Comfortable upright bike for everyday city rides and errands.', 'available'),
('Volt E-Rider',    'Electric', '#7b2ff7', 150, 900, 'Pedal-assist', 'Aluminum', '21 kg',   'Electric bike with up to 60 km range. Effortless on hills.', 'available'),
('Pocket Fold',     'Folding',  '#ffb703',  55, 320, '6-speed',      'Aluminum', '11 kg',   'Folds in seconds. Perfect for commuters and small spaces.', 'available'),
('Dirt Kid BMX',    'BMX',      '#06d6a0',  50, 300, 'Single-speed', 'Chromoly', '10 kg',   'Tough and playful BMX for tricks, parks and pump tracks.', 'available'),
('Ridge Runner 29', 'Mountain', '#3a86ff',  85, 480, '24-speed',     'Aluminum', '14 kg',   'Big 29-inch wheels roll over anything in your path.', 'available'),
('Coastal Breeze',  'City',     '#f72585',  65, 380, '3-speed',      'Steel',    '14 kg',   'Retro-style beach cruiser made for relaxed seaside rides.', 'available'),
('Thunder E-Cargo', 'Electric', '#1d3557', 140, 850, 'Pedal-assist', 'Alloy',    '23 kg',   'Powerful e-bike with strong torque for heavy-duty rides.', 'maintenance'),
('Sprint Aero',     'Road',     '#ffd60a',  95, 520, '22-speed',     'Carbon',   '9.2 kg',  'Aerodynamic racer for personal bests and group rides.', 'rented');
