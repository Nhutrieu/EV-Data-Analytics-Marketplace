-- Tạo database
CREATE DATABASE IF NOT EXISTS ev_data_marketplace;
USE ev_data_marketplace;

-- Bảng users (nhà cung cấp dữ liệu)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    contact_person VARCHAR(255),
    address TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng datasets
CREATE TABLE IF NOT EXISTS datasets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    type ENUM('Battery', 'Journey', 'Charging', 'Transaction', 'Other') NOT NULL,
    format ENUM('CSV', 'JSON', 'XML', 'Parquet') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    price_unit ENUM('download', 'volume', 'subscription') NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT,
    tags TEXT,
    downloads INT DEFAULT 0,
    status ENUM('active', 'paused', 'pending', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng pricing_policies
CREATE TABLE IF NOT EXISTS pricing_policies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    pricing_model ENUM('per_download', 'volume_based', 'subscription') NOT NULL,
    base_price DECIMAL(10,2) NOT NULL,
    currency ENUM('VND', 'USD', 'EUR') DEFAULT 'VND',
    usage_rights ENUM('research_only', 'commercial', 'extended') NOT NULL,
    license_terms TEXT,
    anonymize_data BOOLEAN DEFAULT TRUE,
    privacy_standard ENUM('GDPR', 'CCPA', 'both') DEFAULT 'GDPR',
    retention_period INT DEFAULT 24,
    data_encryption BOOLEAN DEFAULT TRUE,
    access_logs BOOLEAN DEFAULT TRUE,
    data_masking BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng transactions
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dataset_id INT,
    buyer_name VARCHAR(255) NOT NULL,
    buyer_email VARCHAR(255),
    amount DECIMAL(10,2) NOT NULL,
    currency ENUM('VND', 'USD', 'EUR') DEFAULT 'VND',
    payment_method ENUM('transfer', 'credit_card', 'paypal') NOT NULL,
    status ENUM('completed', 'pending', 'failed') DEFAULT 'pending',
    download_count INT DEFAULT 0,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dataset_id) REFERENCES datasets(id) ON DELETE SET NULL
);

-- Chèn dữ liệu mẫu
INSERT INTO users (company_name, email, password, phone, contact_person, address, description) VALUES
('EV Data Solutions', 'contact@evdata.com', '$2b$10$ExampleHashedPassword', '+84 28 3823 4567', 'Nguyễn Văn A', '123 Lê Lợi, Q.1, TP.HCM', 'Nhà cung cấp dữ liệu xe điện hàng đầu Việt Nam');

INSERT INTO pricing_policies (user_id, pricing_model, base_price, currency, usage_rights, license_terms) VALUES
(1, 'per_download', 199.99, 'VND', 'research_only', 'Giấy phép sử dụng dữ liệu trong vòng 12 tháng. Dữ liệu chỉ được sử dụng cho mục đích nghiên cứu, không được phân phối lại cho bên thứ ba.');

INSERT INTO datasets (user_id, name, description, type, format, price, price_unit, file_name, file_path, file_size, tags, downloads, status) VALUES
(1, 'Dữ liệu pin xe điện 2024', 'Dữ liệu hiệu suất pin từ 1000+ xe điện', 'Battery', 'CSV', 199999, 'download', 'battery_data_2024.csv', '/uploads/battery_data_2024.csv', 5242880, 'pin,xe điện,hiệu suất', 45, 'active'),
(1, 'Hành trình giao hàng TPHCM', 'Dữ liệu hành trình giao hàng trong nội thành', 'Journey', 'JSON', 299999, 'subscription', 'delivery_journeys.json', '/uploads/delivery_journeys.json', 3145728, 'hành trình,giao hàng,TPHCM', 23, 'active');

INSERT INTO transactions (dataset_id, buyer_name, buyer_email, amount, currency, payment_method, status, download_count) VALUES
(1, 'Công ty Nghiên cứu Xe điện', 'research@evcompany.com', 199999, 'VND', 'transfer', 'completed', 1),
(2, 'Đại học Bách Khoa', 'contact@hcmut.edu.vn', 299999, 'VND', 'credit_card', 'completed', 1);