-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 19, 2025 at 04:06 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ev-data-analytics-marketplace`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','provider','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'truong', 'truong@gmail.com', '$2y$10$gnsRfHLK.0jFHoWJrmGV1uqWiDPcaaqjJiMCAbpov8pePSJp/x7oi', 'user', '2025-11-13 05:51:42'),
(2, 'Hoa', 'hoa@gmail.com', '$2y$10$EIq3Yn9pZjzm2ly9Be1mi.ZyOb71BRY/aakJ6RyMaOMzFu7lZCnoC', 'provider', '2025-11-13 05:56:04'),
(3, 'mai', 'mai@gmail.com', '$2y$10$xmpM6xJgLiM1PjxADNGvA.8dbu29uy1sfT7eRsp/vP3UNMKL7x0/a', 'provider', '2025-11-13 06:01:05'),
(4, 'cam', 'cam@gmail.com', '$2y$10$wd1O4hPQFQm.vyJgKOd5e.7Pp5.AZgs8EmU1qoa9lDTBo3u0nuPca', 'user', '2025-11-13 06:17:45'),
(5, 'kai', 'kai@gmail.com', '$2y$10$sMbQ6MaWcGqqNa.6OJLXyO6.KORzKJZPwQrTwR7iIDbiACl2lWNpq', 'user', '2025-11-13 07:20:47'),
(6, 'hong', 'hong@gmail.com', '$2y$10$kL0JYm90yhH7BYsVYRIfR.RT.J1KhH0hqoh0ZhYfhNuCGGNd8gwCy', 'user', '2025-11-13 09:59:19'),
(7, 'dnl', 'linh@gmail.com', '$2y$10$MJA.0fYQGhf8EiLUxESi9OFTf62Ih.oj7KrpyGFpqs0LeDwK43u6y', 'user', '2025-11-13 10:00:53'),
(8, 'van', 'van@gmail.com', '$2y$10$t63vGkqUD1PE55XkUAs9qOGKs/SlnyVUplPrUavEUJEiG8hJPBhGm', 'user', '2025-11-13 10:01:59'),
(9, 'seo', 'seo@gmail.com', '$2y$10$lknerSNsn0WrTi1Qpu59Re.Oytqt3eCfbDONZ5fPV8jrVEOXb8NBy', 'user', '2025-11-13 15:17:08'),
(10, 'truongnv2561@ut.edu.vn', 'miahd2@gmail.com', '$2y$10$J0Gc3P1IgrTK0LlQm5NhRuK.uIskBJGPXNp6Xna0HyxxgbQzzqq6W', 'user', '2025-11-13 15:17:42'),
(11, 'maio', 'maio@gmail.com', '$2y$10$mBp7Qn/fLkroG.aW9OpcJ.ntD5.ZGG1CbzFmYgs0VqSw0E2mOL18G', 'user', '2025-11-13 15:24:42'),
(12, 'Test User Triều', 'test.trieu@example.com', '$2y$10$MRemp.59QRIh3tLyrFqsPeYaqF374vsipthpXvM7papQIhMgYZVn.', 'user', '2025-11-19 14:59:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
