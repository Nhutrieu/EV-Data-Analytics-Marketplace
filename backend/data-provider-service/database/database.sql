-- Tạo database
CREATE DATABASE IF NOT EXISTS ev_data_marketplace;
USE ev_data_marketplace;

-- Bảng users (data providers)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    description TEXT,
    avatar_color VARCHAR(7) DEFAULT '#3B82F6',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng datasets
CREATE TABLE datasets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    data_type ENUM('battery', 'driving', 'charging', 'v2g') NOT NULL,
    data_format ENUM('raw', 'analyzed') NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    price_unit ENUM('per-download', 'subscription', 'one-time') DEFAULT 'per-download',
    file_name VARCHAR(500),
    file_size DECIMAL(10,2),
    tags TEXT,
    status ENUM('active', 'inactive', 'pending') DEFAULT 'pending',
    admin_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_note TEXT,
    download_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng downloads
CREATE TABLE downloads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    dataset_id INT,
    buyer_company VARCHAR(255) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dataset_id) REFERENCES datasets(id) ON DELETE CASCADE
);

-- Bảng activities
CREATE TABLE activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    activity_type ENUM('download', 'upload', 'user_join', 'purchase') NOT NULL,
    description TEXT NOT NULL,
    amount DECIMAL(12,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng pricing_policies
CREATE TABLE pricing_policies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    default_model ENUM('per-download', 'subscription', 'capacity', 'api') DEFAULT 'per-download',
    default_price DECIMAL(12,2) DEFAULT 500000.00,
    default_currency ENUM('VND', 'USD', 'EUR') DEFAULT 'VND',
    default_usage_rights ENUM('research', 'commercial', 'internal', 'extended') DEFAULT 'commercial',
    default_license TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample data
INSERT INTO users (company_name, email, password_hash, contact_person, phone, address, description) VALUES 
('EV Data Corp', 'admin@evdatacorp.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn A', '+84 123 456 789', 'Hà Nội, Việt Nam', 'Cung cấp dữ liệu EV chất lượng cao cho thị trường');

INSERT INTO pricing_policies (user_id, default_model, default_price, default_currency, default_usage_rights, default_license) VALUES 
(1, 'per-download', 500000.00, 'VND', 'commercial', 'Dữ liệu được cung cấp bởi EV Data Analytics Marketplace...');

INSERT INTO datasets (user_id, name, description, data_type, data_format, price, price_unit, status, admin_status, download_count) VALUES 
(1, 'Dữ liệu pin EV Model X', 'Dữ liệu hiệu suất pin từ 1000+ xe EV Model X', 'battery', 'raw', 500000.00, 'per-download', 'active', 'approved', 45),
(1, 'Hành vi lái xe Hà Nội', 'Phân tích hành vi lái xe trong khu vực Hà Nội', 'driving', 'analyzed', 1200000.00, 'subscription', 'active', 'approved', 78),
(1, 'Sử dụng trạm sạc TP.HCM', 'Dữ liệu sử dụng trạm sạc tại TP.HCM Q3/2024', 'charging', 'raw', 750000.00, 'per-download', 'pending', 'pending', 0),
(1, 'Dữ liệu V2G 2024', 'Dữ liệu giao dịch V2G năm 2024', 'v2g', 'raw', 900000.00, 'per-download', 'pending', 'rejected', 0);

INSERT INTO downloads (dataset_id, buyer_company, amount) VALUES 
(1, 'Công ty ABC', 500000.00),
(1, 'Startup XYZ', 500000.00),
(2, 'Công ty DEF', 1200000.00);

INSERT INTO activities (user_id, activity_type, description, amount) VALUES 
(1, 'download', 'Tải xuống dữ liệu pin - Công ty ABC', 500000.00),
(1, 'user_join', 'Người dùng mới - Startup XYZ', 2000000.00),
(1, 'upload', 'Tải lên dữ liệu mới - Hành vi lái xe Q4', 0);