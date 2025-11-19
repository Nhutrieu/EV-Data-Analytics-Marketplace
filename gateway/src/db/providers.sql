-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 19, 2025 at 04:05 PM
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
-- Table structure for table `providers`
--

CREATE TABLE `providers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `providers`
--

INSERT INTO `providers` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'lan', 'lan@gmail.com', '$2y$10$5OWtVa6d7JeqCUyOi598/uUjRoXq7GcVkHCPv6pYVg0OMkpweMYhi', '2025-11-13 06:07:08'),
(2, 'lan', 'tan@gmail.com', '$2y$10$PdNsmorZ18Gx.h6TyFdrJebA/nOAQC0LR0kKj/eNr3oCKog5UMvxS', '2025-11-13 06:07:48'),
(3, 'hai', 'hai@gmail.com', '$2y$10$H6oU0CiL9fjNB1FC2XmPaeb5iy12TbBXw9vEY2BVRoJdFptudDvh2', '2025-11-13 06:11:56'),
(4, 'tai', 'tai@gmail.com', '$2y$10$BaIIAdDsa/WZ5sEf4j1KqO2LFG9mphHAQoxS8OpZyQ1UbtI2wHaGi', '2025-11-13 06:13:52'),
(5, 'hong', 'hong@gmail.com', '$2y$10$LtMLwFdOwFYDYeKtWfzD1.WnLnbun8KNcxupgWnsBFMaRtz5VB9bq', '2025-11-13 09:59:10'),
(6, 'hai', 'kais@gmail.com', '$2y$10$3W0cVV3z/FDrwlI3z8tpg.rSPxsBCbN74wwEzE2eaVYljqVrdYlta', '2025-11-13 15:12:09'),
(7, 'mais', 'maia@gmail.com', '$2y$10$Ldnhhp.ZtGHZIZ.YJX8IU.Z93Y8.qMmhwweLg8tHEhZcce2W4T5iS', '2025-11-13 15:15:24'),
(8, 'seo', 'seo@gmail.com', '$2y$10$9bESzwkkvSryf.gZXHtRO.QtqxzggnWmyVl42TYsZoQ0zMTYFhw86', '2025-11-13 15:16:54'),
(9, 'tamtai', 'tamtai@gmail.com', '$2y$10$Ys7n9KSNg.AYaHG7BPA7auF6.c4/o.Ev1i7Sf6ZTZJrkhrWo9ICaW', '2025-11-13 15:23:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `providers`
--
ALTER TABLE `providers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `providers`
--
ALTER TABLE `providers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
