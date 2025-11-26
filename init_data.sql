-- init_data.sql

CREATE DATABASE IF NOT EXISTS `ev-data-analytics-marketplace` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ev-data-analytics-marketplace`;

-- Bảng users
CREATE TABLE IF NOT EXISTS `users` (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  password VARCHAR(255),
  role ENUM('consumer','provider') DEFAULT 'consumer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng admins
CREATE TABLE IF NOT EXISTS `admins` (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  password VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng providers
CREATE TABLE IF NOT EXISTS `providers` (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  password VARCHAR(255),
  company VARCHAR(150),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Dữ liệu mẫu
INSERT INTO users (name,email,password,role) VALUES
('Nguyen Van A','a@example.com','$2y$10$e0NRp1e/jv7GkWQ1gqk1ROl1qY6uZx8s9K/3l6I5RbDqF8hQaMjkO','consumer');

INSERT INTO admins (name,email,password) VALUES
('Admin1','admin1@example.com','$2y$10$e0NRp1e/jv7GkWQ1gqk1ROl1qY6uZx8s9K/3l6I5RbDqF8hQaMjkO');

INSERT INTO providers (name,email,password,company) VALUES
('Tran Van C','c@example.com','$2y$10$e0NRp1e/jv7GkWQ1gqk1ROl1qY6uZx8s9K/3l6I5RbDqF8hQaMjkO','EV Fleet Co.');
