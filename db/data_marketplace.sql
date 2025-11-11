-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 06, 2025 lúc 07:03 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `data_marketplace`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admin_stats`
--

CREATE TABLE `admin_stats` (
  `id` int(11) NOT NULL,
  `total_users` int(11) NOT NULL DEFAULT 0,
  `total_datasets` int(11) NOT NULL DEFAULT 0,
  `total_revenue` decimal(10,2) NOT NULL DEFAULT 0.00,
  `top_dataset` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `admin_stats`
--

INSERT INTO `admin_stats` (`id`, `total_users`, `total_datasets`, `total_revenue`, `top_dataset`, `updated_at`) VALUES
(1, 25, 15, 636.88, 'EV Charging Stations Data', '2025-11-05 02:57:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `datasets`
--

CREATE TABLE `datasets` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `datasets`
--

INSERT INTO `datasets` (`id`, `title`, `provider_id`, `status`, `price`, `created_at`) VALUES
(1, 'EV Charging Stations Data', 1, 'approved', 99.99, '2025-11-05 02:57:00'),
(2, 'Battery Performance Data', 2, 'approved', 49.99, '2025-11-05 02:57:00'),
(3, 'EV Sales 2024', 3, 'approved', 79.99, '2025-11-05 02:57:00'),
(4, 'Vehicle Speed Logs', 4, 'pending', 29.99, '2025-11-05 02:57:00'),
(5, 'Route Optimization Data', 5, 'approved', 59.99, '2025-11-05 02:57:00'),
(6, 'Battery Health Report', 6, 'pending', 39.99, '2025-11-05 02:57:00'),
(7, 'EV Maintenance Records', 7, 'approved', 89.99, '2025-11-05 02:57:00'),
(8, 'EV Range Statistics', 8, 'approved', 69.99, '2025-11-05 02:57:00'),
(9, 'EV Charger Usage', 9, 'approved', 24.99, '2025-11-05 02:57:00'),
(10, 'EV Battery Temperature', 10, 'pending', 34.99, '2025-11-05 02:57:00'),
(11, 'EV Fleet Management', 1, 'approved', 99.99, '2025-11-05 02:57:00'),
(12, 'EV Charging Cost', 2, 'approved', 44.99, '2025-11-05 02:57:00'),
(13, 'EV Market Share', 3, 'approved', 54.99, '2025-11-05 02:57:00'),
(14, 'EV Traffic Patterns', 4, 'pending', 19.99, '2025-11-05 02:57:00'),
(15, 'EV Energy Consumption', 5, 'approved', 74.99, '2025-11-05 02:57:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `logs`
--

INSERT INTO `logs` (`id`, `admin_id`, `action`, `created_at`) VALUES
(1, 1, 'Approved dataset ID 1', '2025-11-05 02:57:00'),
(2, 2, 'Rejected dataset ID 4', '2025-11-05 02:57:00'),
(3, 1, 'Approved dataset ID 2', '2025-11-05 02:57:00'),
(4, 3, 'Processed transaction ID 3', '2025-11-05 02:57:00'),
(5, 4, 'Updated stats table', '2025-11-05 02:57:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `revenue_share`
--

CREATE TABLE `revenue_share` (
  `id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `share_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `revenue_share`
--

INSERT INTO `revenue_share` (`id`, `provider_id`, `transaction_id`, `share_amount`, `created_at`) VALUES
(1, 1, 1, 49.99, '2025-11-05 02:57:00'),
(2, 2, 2, 24.99, '2025-11-05 02:57:00'),
(3, 3, 3, 39.99, '2025-11-05 02:57:00'),
(4, 5, 4, 29.99, '2025-11-05 02:57:00'),
(5, 7, 5, 44.99, '2025-11-05 02:57:00'),
(6, 8, 6, 34.99, '2025-11-05 02:57:00'),
(7, 9, 7, 12.49, '2025-11-05 02:57:00'),
(8, 1, 8, 49.99, '2025-11-05 02:57:00'),
(9, 2, 9, 22.49, '2025-11-05 02:57:00'),
(10, 3, 10, 27.49, '2025-11-05 02:57:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `dataset_id` int(11) NOT NULL,
  `consumer_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `transactions`
--

INSERT INTO `transactions` (`id`, `dataset_id`, `consumer_id`, `amount`, `created_at`) VALUES
(1, 1, 1, 99.99, '2025-11-05 02:57:00'),
(2, 2, 2, 49.99, '2025-11-05 02:57:00'),
(3, 3, 3, 79.99, '2025-11-05 02:57:00'),
(4, 5, 4, 59.99, '2025-11-05 02:57:00'),
(5, 7, 5, 89.99, '2025-11-05 02:57:00'),
(6, 8, 6, 69.99, '2025-11-05 02:57:00'),
(7, 9, 7, 24.99, '2025-11-05 02:57:00'),
(8, 11, 8, 99.99, '2025-11-05 02:57:00'),
(9, 12, 9, 44.99, '2025-11-05 02:57:00'),
(10, 13, 10, 54.99, '2025-11-05 02:57:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','provider','consumer','partner') NOT NULL DEFAULT 'consumer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin One', 'admin1@example.com', 'adminpass1', 'admin', '2025-11-05 02:57:00'),
(2, 'Admin Two', 'admin2@example.com', 'adminpass2', 'admin', '2025-11-05 02:57:00'),
(3, 'Admin Three', 'admin3@example.com', 'adminpass3', 'admin', '2025-11-05 02:57:00'),
(4, 'Admin Four', 'admin4@example.com', 'adminpass4', 'admin', '2025-11-05 02:57:00'),
(5, 'Admin Five', 'admin5@example.com', 'adminpass5', 'admin', '2025-11-05 02:57:00'),
(6, 'Provider One', 'prov1@example.com', 'provpass1', 'provider', '2025-11-05 02:57:00'),
(7, 'Provider Two', 'prov2@example.com', 'provpass2', 'provider', '2025-11-05 02:57:00'),
(8, 'Provider Three', 'prov3@example.com', 'provpass3', 'provider', '2025-11-05 02:57:00'),
(9, 'Provider Four', 'prov4@example.com', 'provpass4', 'provider', '2025-11-05 02:57:00'),
(10, 'Provider Five', 'prov5@example.com', 'provpass5', 'provider', '2025-11-05 02:57:00'),
(11, 'Provider Six', 'prov6@example.com', 'provpass6', 'provider', '2025-11-05 02:57:00'),
(12, 'Provider Seven', 'prov7@example.com', 'provpass7', 'provider', '2025-11-05 02:57:00'),
(13, 'Provider Eight', 'prov8@example.com', 'provpass8', 'provider', '2025-11-05 02:57:00'),
(14, 'Provider Nine', 'prov9@example.com', 'provpass9', 'provider', '2025-11-05 02:57:00'),
(15, 'Provider Ten', 'prov10@example.com', 'provpass10', 'provider', '2025-11-05 02:57:00'),
(16, 'Consumer One', 'cons1@example.com', 'conspass1', 'consumer', '2025-11-05 02:57:00'),
(17, 'Consumer Two', 'cons2@example.com', 'conspass2', 'consumer', '2025-11-05 02:57:00'),
(18, 'Consumer Three', 'cons3@example.com', 'conspass3', 'consumer', '2025-11-05 02:57:00'),
(19, 'Consumer Four', 'cons4@example.com', 'conspass4', 'consumer', '2025-11-05 02:57:00'),
(20, 'Consumer Five', 'cons5@example.com', 'conspass5', 'consumer', '2025-11-05 02:57:00'),
(21, 'Consumer Six', 'cons6@example.com', 'conspass6', 'consumer', '2025-11-05 02:57:00'),
(22, 'Consumer Seven', 'cons7@example.com', 'conspass7', 'consumer', '2025-11-05 02:57:00'),
(23, 'Consumer Eight', 'cons8@example.com', 'conspass8', 'consumer', '2025-11-05 02:57:00'),
(24, 'Consumer Nine', 'cons9@example.com', 'conspass9', 'consumer', '2025-11-05 02:57:00'),
(25, 'Consumer Ten', 'cons10@example.com', 'conspass10', 'consumer', '2025-11-05 02:57:00');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `admin_stats`
--
ALTER TABLE `admin_stats`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `datasets`
--
ALTER TABLE `datasets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `provider_id` (`provider_id`);

--
-- Chỉ mục cho bảng `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Chỉ mục cho bảng `revenue_share`
--
ALTER TABLE `revenue_share`
  ADD PRIMARY KEY (`id`),
  ADD KEY `provider_id` (`provider_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Chỉ mục cho bảng `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dataset_id` (`dataset_id`),
  ADD KEY `consumer_id` (`consumer_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `admin_stats`
--
ALTER TABLE `admin_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `datasets`
--
ALTER TABLE `datasets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT cho bảng `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `revenue_share`
--
ALTER TABLE `revenue_share`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `datasets`
--
ALTER TABLE `datasets`
  ADD CONSTRAINT `datasets_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `revenue_share`
--
ALTER TABLE `revenue_share`
  ADD CONSTRAINT `revenue_share_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `revenue_share_ibfk_2` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`dataset_id`) REFERENCES `datasets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`consumer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
