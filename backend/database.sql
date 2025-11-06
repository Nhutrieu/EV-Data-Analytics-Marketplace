-- Tạo database
CREATE DATABASE IF NOT EXISTS ev_data_provider;
USE ev_data_provider;

-- Bảng dữ liệu
CREATE TABLE datasets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255),
  type VARCHAR(50),
  format VARCHAR(50),
  price INT,
  price_unit VARCHAR(50),
  description TEXT,
  status VARCHAR(50),
  admin_status VARCHAR(50),
  admin_note TEXT,
  downloads INT DEFAULT 0,
  tags VARCHAR(255),
  file_name VARCHAR(255),
  file_size FLOAT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng giao dịch
CREATE TABLE transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dataset_id INT,
  buyer VARCHAR(255),
  amount INT,
  method VARCHAR(50),
  status VARCHAR(50),
  timestamp DATETIME,
  FOREIGN KEY (dataset_id) REFERENCES datasets(id)
);

-- Bảng chính sách giá
CREATE TABLE pricing_policy (
  id INT PRIMARY KEY,
  model VARCHAR(50),
  price INT,
  currency VARCHAR(10),
  usage_rights VARCHAR(50),
  license_terms TEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng cài đặt bảo mật
CREATE TABLE privacy_settings (
  id INT PRIMARY KEY,
  anonymize BOOLEAN,
  standard VARCHAR(50),
  retention_months INT,
  access_control VARCHAR(50),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng thông tin công ty
CREATE TABLE company_profile (
  id INT PRIMARY KEY,
  name VARCHAR(255),
  email VARCHAR(255),
  phone VARCHAR(50),
  contact_person VARCHAR(255),
  address TEXT,
  description TEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng cài đặt hệ thống
CREATE TABLE system_settings (
  id INT PRIMARY KEY,
  language VARCHAR(50),
  timezone VARCHAR(50),
  date_format VARCHAR(20),
  currency VARCHAR(10),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng cài đặt thông báo
CREATE TABLE notification_settings (
  id INT PRIMARY KEY,
  email_notifications BOOLEAN,
  security_notifications BOOLEAN,
  weekly_reports BOOLEAN,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng hoạt động gần đây (tuỳ chọn)
CREATE TABLE recent_activity (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(50),
  actor VARCHAR(255),
  title VARCHAR(255),
  amount INT,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
);
