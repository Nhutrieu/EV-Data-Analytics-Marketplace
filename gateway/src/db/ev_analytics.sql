-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 09, 2025 lúc 11:35 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `ev_analytics`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `analytics`
--
-- Error reading structure for table ev_analytics.analytics: #1932 - Table &#039;ev_analytics.analytics&#039; doesn&#039;t exist in engine
-- Error reading data for table ev_analytics.analytics: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `ev_analytics`.`analytics`&#039; at line 1

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `analytics_data`
--

CREATE TABLE `analytics_data` (
  `id` int(11) NOT NULL,
  `analytics_id` int(11) NOT NULL,
  `dataset_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `soc` text DEFAULT NULL,
  `soh` text DEFAULT NULL,
  `range` text DEFAULT NULL,
  `consumption` text DEFAULT NULL,
  `vehicle_type` text DEFAULT NULL,
  `co2_saved` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `analytics_data`
--

INSERT INTO `analytics_data` (`id`, `analytics_id`, `dataset_id`, `created_at`, `soc`, `soh`, `range`, `consumption`, `vehicle_type`, `co2_saved`) VALUES
(1, 1, 1, '2025-10-19 17:00:00', '[80,75,90,60,70]', '[95,93,97,90,92]', '[120,150,100,130,110]', '[15,18,12,17,16]', '{\"EV\":60,\"Hybrid\":40}', '[5,8,6,7,4]'),
(2, 1, 1, '2025-10-20 17:00:00', '[82,78,88,63,72]', '[96,94,98,91,93]', '[125,155,105,135,115]', '[16,19,13,18,17]', '{\"EV\":65,\"Hybrid\":35}', '[6,9,7,8,5]'),
(3, 1, 1, '2025-10-21 17:00:00', '[85,80,92,65,75]', '[97,95,99,92,94]', '[130,160,110,140,120]', '[17,20,14,19,18]', '{\"EV\":70,\"Hybrid\":30}', '[7,10,8,9,6]'),
(4, 1, 1, '2025-10-22 17:00:00', '[84,80,76,75,76]', '[99,96,97,97,98]', '[111,165,122,96,108]', '[20,15,12,16,16]', '{\"EV\":73,\"Hybrid\":24}', '[5,7,7,7,6]'),
(5, 1, 1, '2025-10-23 17:00:00', '[85,68,82,71,85]', '[98,94,98,98,98]', '[108,161,131,101,160]', '[16,22,13,19,16]', '{\"EV\":76,\"Hybrid\":33}', '[9,8,6,7,5]'),
(6, 1, 1, '2025-11-01 17:00:00', '[72,80,78,73,79]', '[97,98,99,98,97]', '[110,134,177,150,160]', '[12,15,14,19,14]', '{\"EV\":65,\"Hybrid\":24}', '[6,9,8,7,7]'),
(7, 1, 1, '2025-11-02 17:00:00', '[75,86,83,79,87]', '[99,95,98,98,97]', '[158,143,139,143,100]', '[20,22,18,19,17]', '{\"EV\":67,\"Hybrid\":35}', '[9,7,8,7,6]'),
(8, 1, 1, '2025-11-03 17:00:00', '[71,88,75,60,88]', '[95,96,96,98,99]', '[151,120,162,101,153]', '[17,21,14,17,20]', '{\"EV\":72,\"Hybrid\":26}', '[10,9,5,7,7]'),
(9, 1, 1, '2025-11-04 17:00:00', '[71,85,84,76,70]', '[99,96,96,94,97]', '[121,128,167,103,136]', '[12,19,13,16,20]', '{\"EV\":78,\"Hybrid\":32}', '[5,7,6,8,8]'),
(10, 1, 1, '2025-11-05 17:00:00', '[78,83,87,84,89]', '[98,97,96,96,95]', '[159,155,146,132,135]', '[16,19,11,17,17]', '{\"EV\":71,\"Hybrid\":23}', '[8,8,8,7,7]'),
(11, 1, 1, '2025-11-06 17:00:00', '[72,81,82,84,70]', '[99,96,99,98,98]', '[111,119,144,97,158]', '[16,18,11,15,19]', '{\"EV\":78,\"Hybrid\":33}', '[8,9,6,8,6]'),
(12, 1, 1, '2025-11-07 17:00:00', '[88,69,83,85,86]', '[97,94,97,96,98]', '[136,151,138,98,126]', '[13,13,12,19,16]', '{\"EV\":61,\"Hybrid\":27}', '[9,7,4,9,6]');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `analytics_monthly_summary`
--

CREATE TABLE `analytics_monthly_summary` (
  `id` int(11) NOT NULL,
  `month_year` varchar(7) DEFAULT NULL,
  `avg_soc` decimal(5,2) DEFAULT NULL,
  `avg_soh` decimal(5,2) DEFAULT NULL,
  `avg_range` decimal(6,2) DEFAULT NULL,
  `avg_consumption` decimal(6,2) DEFAULT NULL,
  `co2_saved_total` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `analytics_monthly_summary`
--

INSERT INTO `analytics_monthly_summary` (`id`, `month_year`, `avg_soc`, `avg_soh`, `avg_range`, `avg_consumption`, `co2_saved_total`, `created_at`) VALUES
(1, '2025-10', 83.20, 97.00, 118.80, 16.80, 32.00, '2025-11-01 10:26:12');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `analytics_packages`
--

CREATE TABLE `analytics_packages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `analytics_packages`
--

INSERT INTO `analytics_packages` (`id`, `name`, `description`, `price`, `features`, `active`, `created_at`, `updated_at`) VALUES
(1, 'SoC & SoH Analysis', 'Phân tích mức sạc và tình trạng pin', 50.00, '{\"metrics\":[\"SoC\",\"SoH\"]}', 1, '2025-11-01 09:34:42', '2025-11-01 09:34:42'),
(2, 'Charging Behavior Analysis', 'Phân tích tần suất sạc và thời gian sạc', 70.00, '{\"metrics\":[\"charge_frequency\",\"duration\"]}', 1, '2025-11-01 09:34:42', '2025-11-01 09:34:42'),
(3, 'SoC & SoH Analysis', 'Phân tích mức sạc và tình trạng pin', 50.00, '{\"metrics\":[\"soc\",\"soh\"]}', 1, '2025-11-01 09:53:42', '2025-11-01 09:53:42');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `api_keys`
--

CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `api_key` varchar(64) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `api_keys`
--

INSERT INTO `api_keys` (`id`, `user_id`, `api_key`, `status`, `created_at`) VALUES
(10, 1, '9f96564ec8d755368a154f8344d92ba29f4a0438d0e9c210e9ecaf803b6ca44d', 'active', '2025-11-08 11:07:15');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `datasets`
--

CREATE TABLE `datasets` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `rent_monthly` decimal(12,2) DEFAULT 0.00,
  `rent_yearly` decimal(12,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `datasets`
--

INSERT INTO `datasets` (`id`, `name`, `type`, `region`, `price`, `active`, `rent_monthly`, `rent_yearly`) VALUES
(1, 'Hiệu suất pin', 'battery', 'HCM', 2000.00, 1, 125000.00, 750000.00),
(2, 'Hành vi lái xe', 'driver', 'HN', 3750000.00, 1, 187500.00, 1125000.00),
(3, 'Sử dụng trạm sạc', 'charging', 'Đà Nẵng', 3000000.00, 1, 150000.00, 900000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` enum('pending','success','failed') DEFAULT 'pending',
  `note` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `purchases`
--

CREATE TABLE `purchases` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dataset_id` int(11) NOT NULL,
  `order_code` varchar(50) NOT NULL,
  `type` enum('buy','rent') NOT NULL DEFAULT 'buy',
  `price` decimal(12,2) NOT NULL,
  `purchased_at` datetime DEFAULT NULL,
  `expiry_date` datetime DEFAULT NULL COMMENT 'Ngày hết hạn nếu là thuê',
  `created_at` datetime DEFAULT current_timestamp(),
  `status` enum('pending','paid','failed') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `purchases`
--

INSERT INTO `purchases` (`id`, `user_id`, `dataset_id`, `order_code`, `type`, `price`, `purchased_at`, `expiry_date`, `created_at`, `status`) VALUES
(38, 4, 1, '260453691', '', 2000.00, '2025-11-08 17:59:09', NULL, '2025-11-08 17:58:50', 'paid');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('consumer','admin') DEFAULT 'consumer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Nguyễn Văn A', 'user1@example.com', 'hashed_password1', 'consumer', '2025-11-02 10:03:00'),
(2, 'Trần Thị B', 'user2@example.com', 'hashed_password2', 'consumer', '2025-11-02 10:03:00'),
(3, 'Lê Thị C', 'user3@example.com', 'hashed_password3', 'consumer', '2025-11-02 10:03:00'),
(4, 'Test User Triều', 'test.trieu@example.com', 'test_password', 'consumer', '2025-11-04 08:37:07');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_cart`
--

CREATE TABLE `user_cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `selected_type` varchar(50) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `analytics_data`
--
ALTER TABLE `analytics_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `analytics_id` (`analytics_id`),
  ADD KEY `dataset_id` (`dataset_id`);

--
-- Chỉ mục cho bảng `analytics_monthly_summary`
--
ALTER TABLE `analytics_monthly_summary`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `analytics_packages`
--
ALTER TABLE `analytics_packages`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `api_keys`
--
ALTER TABLE `api_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `api_key` (`api_key`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `datasets`
--
ALTER TABLE `datasets`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `dataset_id` (`dataset_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `user_cart`
--
ALTER TABLE `user_cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_item` (`user_id`,`package_id`,`selected_type`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `analytics_data`
--
ALTER TABLE `analytics_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `analytics_monthly_summary`
--
ALTER TABLE `analytics_monthly_summary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `analytics_packages`
--
ALTER TABLE `analytics_packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `api_keys`
--
ALTER TABLE `api_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `datasets`
--
ALTER TABLE `datasets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `user_cart`
--
ALTER TABLE `user_cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `analytics_data`
--
ALTER TABLE `analytics_data`
  ADD CONSTRAINT `analytics_data_ibfk_1` FOREIGN KEY (`analytics_id`) REFERENCES `analytics_packages` (`id`),
  ADD CONSTRAINT `analytics_data_ibfk_2` FOREIGN KEY (`dataset_id`) REFERENCES `datasets` (`id`);

--
-- Các ràng buộc cho bảng `api_keys`
--
ALTER TABLE `api_keys`
  ADD CONSTRAINT `api_keys_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`dataset_id`) REFERENCES `datasets` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
