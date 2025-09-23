-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 23, 2025 at 04:18 AM
-- Server version: 8.0.42-0ubuntu0.22.04.2
-- PHP Version: 8.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `deploysmart`
--

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci NOT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci,
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci DEFAULT NULL,
  `directory_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `address`, `phone`, `directory_id`) VALUES
(1, 'Admin Company', 'Administrator street 123', '+46000000000', 'ds_68ac0df2e04f8');

-- --------------------------------------------------------

--
-- Table structure for table `deployments`
--

CREATE TABLE `deployments` (
  `id` int NOT NULL,
  `company_id` int NOT NULL,
  `method` enum('iso','powershell') COLLATE utf8mb4_general_ci NOT NULL,
  `timestamp` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_history`
--

CREATE TABLE `login_history` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `timestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mfa_resets`
--

CREATE TABLE `mfa_resets` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `admin_id` int NOT NULL,
  `reset_time` datetime NOT NULL,
  `reason` text COLLATE utf8mb4_general_ci,
  `delivery_status` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profile_changes`
--

CREATE TABLE `profile_changes` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `change_type` enum('email','password') COLLATE utf8mb4_general_ci NOT NULL,
  `old_value` text COLLATE utf8mb4_general_ci,
  `new_value` text COLLATE utf8mb4_general_ci,
  `timestamp` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_swedish_ci DEFAULT NULL,
  `surname` varchar(100) COLLATE utf8mb4_swedish_ci DEFAULT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci NOT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci DEFAULT NULL,
  `company_id` int DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT '0',
  `totp_enabled` tinyint(1) DEFAULT '0',
  `totp_secret` varchar(64) COLLATE utf8mb4_swedish_ci DEFAULT NULL,
  `mfa_reset_token` varchar(64) COLLATE utf8mb4_swedish_ci DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `name`, `surname`, `password_hash`, `phone`, `company_id`, `is_admin`, `totp_enabled`, `totp_secret`, `mfa_reset_token`, `reset_token_expiry`) VALUES
(1, 'admin@example.com', 'admin', '', '$2y$12$FE7iAJD1PzTbnurktSRGoe6iqgKNHiCcCfo4pK5F.nDzBR8TxyTH2', '+46765711370', 1, 1, 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_companies`
--

CREATE TABLE `user_companies` (
  `user_id` int NOT NULL,
  `company_id` int NOT NULL,
  `role` enum('owner','editor','viewer') COLLATE utf8mb4_general_ci DEFAULT 'owner'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `directory_id` (`directory_id`);

--
-- Indexes for table `deployments`
--
ALTER TABLE `deployments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `login_history`
--
ALTER TABLE `login_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `mfa_resets`
--
ALTER TABLE `mfa_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `profile_changes`
--
ALTER TABLE `profile_changes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `user_companies`
--
ALTER TABLE `user_companies`
  ADD PRIMARY KEY (`user_id`,`company_id`),
  ADD KEY `company_id` (`company_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `deployments`
--
ALTER TABLE `deployments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `login_history`
--
ALTER TABLE `login_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `mfa_resets`
--
ALTER TABLE `mfa_resets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `profile_changes`
--
ALTER TABLE `profile_changes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `deployments`
--
ALTER TABLE `deployments`
  ADD CONSTRAINT `deployments_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`);

--
-- Constraints for table `login_history`
--
ALTER TABLE `login_history`
  ADD CONSTRAINT `login_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mfa_resets`
--
ALTER TABLE `mfa_resets`
  ADD CONSTRAINT `mfa_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `mfa_resets_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `profile_changes`
--
ALTER TABLE `profile_changes`
  ADD CONSTRAINT `profile_changes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`);

--
-- Constraints for table `user_companies`
--
ALTER TABLE `user_companies`
  ADD CONSTRAINT `user_companies_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_companies_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
