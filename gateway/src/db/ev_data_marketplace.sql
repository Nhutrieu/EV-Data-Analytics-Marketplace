-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Máy chủ: db
-- Thời gian đã tạo: Th10 11, 2025 lúc 05:47 AM
-- Phiên bản máy phục vụ: 8.0.44
-- Phiên bản PHP: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `ev_data_marketplace`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `datasets`
--

CREATE TABLE `datasets` (
  `id` int NOT NULL,
  `provider_id` int NOT NULL DEFAULT '1',
  `name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `format` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `price` int DEFAULT NULL,
  `price_unit` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `admin_status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `admin_note` text COLLATE utf8mb4_general_ci,
  `downloads` int DEFAULT '0',
  `tags` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_size` float DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `datasets`
--

INSERT INTO `datasets` (`id`, `provider_id`, `name`, `type`, `format`, `price`, `price_unit`, `description`, `status`, `admin_status`, `admin_note`, `downloads`, `tags`, `file_name`, `file_size`, `created_at`) VALUES
(4, 1, 'Energy Transactions TOU Pricing Sample', 'energy_tx', 'json', 900000, 'per_download', 'Dữ liệu giao dịch điện theo khung giờ (time-of-use), bao gồm đơn giá, kWh tiêu thụ, chi phí.', 'published', 'approved', 'Dùng cho demo pricing engine.', 8, 'energy,transaction,tou,pricing', 'energy_tx_tou_sample.json', 1024, '2025-11-10 09:30:34'),
(19, 1, 'haha', 'battery', 'raw', 400000, 'subscription', '', 'draft', 'pending', NULL, 0, NULL, NULL, NULL, '2025-11-10 11:19:01'),
(20, 1, 'banhmi', 'driving', 'analyzed', 500000, 'subscription', '', 'draft', 'pending', NULL, 0, '', '1762789211_066205009629-HuynhLeBao-BaiTap.xlsx', 13647, '2025-11-10 15:40:11'),
(22, 1, '\'Dữ liệu pin EV Model X', 'driving', 'raw', 499995, 'one-time', '', 'draft', 'pending', NULL, 0, NULL, NULL, NULL, '2025-11-10 17:08:30');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `pricing_policy`
--

CREATE TABLE `pricing_policy` (
  `provider_id` int NOT NULL,
  `model` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `price` int DEFAULT NULL,
  `currency` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `usage_rights` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `license_terms` text COLLATE utf8mb4_general_ci,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `pricing_policy`
--

INSERT INTO `pricing_policy` (`provider_id`, `model`, `price`, `currency`, `usage_rights`, `license_terms`, `updated_at`) VALUES
(1, 'per-download', 500000, 'VND', 'commercial', 'thương maij', '2025-11-10 17:43:12');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `privacy_settings`
--

CREATE TABLE `privacy_settings` (
  `provider_id` int NOT NULL,
  `anonymize` tinyint(1) DEFAULT NULL,
  `standard` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `retention_months` int DEFAULT NULL,
  `access_control` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `privacy_settings`
--

INSERT INTO `privacy_settings` (`provider_id`, `anonymize`, `standard`, `retention_months`, `access_control`, `updated_at`) VALUES
(1, 1, 'GDPR', 12, 'verified-buyers', '2025-11-10 18:17:46');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `providers`
--

CREATE TABLE `providers` (
  `id` int NOT NULL,
  `company_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_phone` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_person` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_general_ci,
  `description` text COLLATE utf8mb4_general_ci,
  `login_email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `providers`
--

INSERT INTO `providers` (`id`, `company_name`, `contact_email`, `contact_phone`, `contact_person`, `address`, `description`, `login_email`, `password_hash`, `created_at`, `updated_at`) VALUES
(1, 'EV Data Corp', 'contact@evdatacorp.com', '+84 123 456 789', 'Nguyễn Văn C', 'Hà Nội, Việt Nam', 'Cung cấp dữ liệu EV chất lượng cao cho thị trường', 'admin@evdatacorp.com', '$2y$10$.RZBsVbsB6ew4Q6xFnOkyu9gx8vft/wwWB4Sh9hjzmZpR6rCXt7kK', '2025-11-10 18:35:01', '2025-11-10 19:00:47');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `provider_settings`
--

CREATE TABLE `provider_settings` (
  `provider_id` int NOT NULL,
  `timezone` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '+7',
  `date_format` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'dd/mm/yyyy',
  `currency` varchar(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'VND',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `provider_settings`
--

INSERT INTO `provider_settings` (`provider_id`, `timezone`, `date_format`, `currency`, `updated_at`) VALUES
(1, '+0', 'mm/dd/yyyy', 'VND', '2025-11-10 19:00:54');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `transactions`
--

CREATE TABLE `transactions` (
  `id` int NOT NULL,
  `dataset_id` int DEFAULT NULL,
  `provider_id` int NOT NULL DEFAULT '1',
  `buyer` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `amount` int DEFAULT NULL,
  `method` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `transactions`
--

INSERT INTO `transactions` (`id`, `dataset_id`, `provider_id`, `buyer`, `amount`, `method`, `status`, `timestamp`) VALUES
(1, 19, 1, 'company_a', 400000, 'momo', 'completed', '2025-01-10 10:00:00'),
(2, 19, 1, 'company_b', 400000, 'vnpay', 'completed', '2025-01-11 15:30:00'),
(3, 4, 1, 'company_c', 900000, 'wallet', 'completed', '2025-02-01 09:45:00'),
(4, 19, 1, 'client_a', 400000, 'momo', 'completed', '2025-01-10 10:00:00'),
(5, 19, 1, 'client_b', 400000, 'vnpay', 'completed', '2025-01-11 15:30:00'),
(6, 4, 1, 'client_a', 900000, 'wallet', 'completed', '2025-02-01 09:45:00');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `datasets`
--
ALTER TABLE `datasets`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `pricing_policy`
--
ALTER TABLE `pricing_policy`
  ADD PRIMARY KEY (`provider_id`);

--
-- Chỉ mục cho bảng `privacy_settings`
--
ALTER TABLE `privacy_settings`
  ADD PRIMARY KEY (`provider_id`);

--
-- Chỉ mục cho bảng `providers`
--
ALTER TABLE `providers`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `provider_settings`
--
ALTER TABLE `provider_settings`
  ADD PRIMARY KEY (`provider_id`);

--
-- Chỉ mục cho bảng `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dataset_id` (`dataset_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `datasets`
--
ALTER TABLE `datasets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT cho bảng `providers`
--
ALTER TABLE `providers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Ràng buộc đối với các bảng kết xuất
--

--
-- Ràng buộc cho bảng `provider_settings`
--
ALTER TABLE `provider_settings`
  ADD CONSTRAINT `fk_provider_settings_provider` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`);

--
-- Ràng buộc cho bảng `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`dataset_id`) REFERENCES `datasets` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
