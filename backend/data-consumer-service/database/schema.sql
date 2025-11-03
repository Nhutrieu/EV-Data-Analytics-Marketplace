-- =====================================================
-- DATABASE: ev_data_consumer
-- =====================================================

CREATE DATABASE IF NOT EXISTS ev_data_consumer;
USE ev_data_consumer;

-- ===================== TABLE: users =====================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('consumer','admin') DEFAULT 'consumer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE datasets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,
    region VARCHAR(100) NOT NULL,
    price DECIMAL(15,2) NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    rent_monthly DECIMAL(15,2),
    rent_yearly DECIMAL(15,2)
);
INSERT INTO  datasets (id, name, type, region, price, active, rent_monthly, rent_yearly)
VALUES
(1, 'Hiệu suất pin', 'battery', 'HCM', 2500000.00, 1, 125000.00, 750000.00),
(2, 'Hành vi lái xe', 'driver', 'HN', 3750000.00, 1, 187500.00, 1125000.00),
(3, 'Sử dụng trạm sạc', 'charging', 'Đà Nẵng', 3000000.00, 1, 150000.00, 900000.00);

CREATE TABLE purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    dataset_id INT NOT NULL,
    type ENUM('buy','rent_month','rent_year') NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (dataset_id) REFERENCES datasets(id)
);



CREATE TABLE user_cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    package_id INT NOT NULL,
    selected_type VARCHAR(50) NOT NULL,
    price DECIMAL(15,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (package_id) REFERENCES packages(id)
);
INSERT INTO users (id, name, email, password, created_at)
VALUES
(1, 'Nguyễn Văn A', 'user1@example.com', 'hashed_password1', NOW()),
(2, 'Trần Thị B', 'user2@example.com', 'hashed_password2', NOW()),
(3, 'Lê Thị C', 'user3@example.com', 'hashed_password3', NOW());