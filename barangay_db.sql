-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 07, 2026 at 03:38 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `barangay_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `aid_program`
--

CREATE TABLE `aid_program` (
  `id` int(11) NOT NULL,
  `program_name` varchar(255) NOT NULL,
  `aid_type` varchar(100) NOT NULL,
  `date_scheduled` date NOT NULL,
  `beneficiaries` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `version` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `target_module` varchar(50) NOT NULL,
  `details` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `distribution_logs`
--

CREATE TABLE `distribution_logs` (
  `id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `household_id` int(11) NOT NULL,
  `rfid_tag_id` int(11) DEFAULT NULL,
  `rfid_snapshot` varchar(50) NOT NULL,
  `date_claimed` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registered_household`
--

CREATE TABLE `registered_household` (
  `id` int(11) NOT NULL,
  `household_number` varchar(50) NOT NULL,
  `head_of_family_id` int(11) DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `version` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registered_resi`
--

CREATE TABLE `registered_resi` (
  `id` int(11) NOT NULL,
  `household_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `civil_status` varchar(20) NOT NULL,
  `occupation` varchar(100) DEFAULT 'None',
  `voters_registration_no` varchar(50) DEFAULT 'Not Registered',
  `address` varchar(255) NOT NULL,
  `birthdate` date NOT NULL,
  `contact` varchar(20) DEFAULT 'N/A',
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `version` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rfid_tags`
--

CREATE TABLE `rfid_tags` (
  `id` int(11) NOT NULL,
  `household_id` int(11) NOT NULL,
  `rfid_number` varchar(50) NOT NULL,
  `status` enum('Active','Disabled','Lost') NOT NULL DEFAULT 'Active',
  `issued_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `version` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rfid_tags`
--

INSERT INTO `rfid_tags` (`id`, `household_id`, `rfid_number`, `status`, `issued_date`, `version`) VALUES
(5, 4, '12312', 'Disabled', '2026-03-21 12:38:08', 1),
(6, 4, '33', 'Disabled', '2026-03-21 12:38:24', 1),
(7, 4, '333', '', '2026-03-24 13:44:28', 2),
(9, 4, '3434', '', '2026-03-26 05:54:27', 1),
(10, 4, '1234', '', '2026-03-26 05:54:44', 2),
(11, 16, '123', 'Disabled', '2026-03-26 06:42:20', 11),
(13, 9, '4343', 'Active', '2026-03-28 02:40:23', 1),
(15, 16, '12323', 'Disabled', '2026-03-28 05:49:37', 1),
(16, 16, '11111232342', 'Disabled', '2026-03-28 06:13:36', 5),
(17, 20, 'C64A07011123', '', '2026-04-01 04:46:22', 8),
(18, 21, 'AAA', 'Active', '2026-04-01 04:56:45', 26),
(29, 22, '111', 'Active', '2026-04-07 11:01:01', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `role` enum('admin','staff') NOT NULL DEFAULT 'staff',
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `version` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `role`, `username`, `password`, `status`, `created_at`) VALUES
(1, 'System', 'Admin', 'admin', 'admin', '$2y$10$xWOJvB0rZ41G44wH2NTLouCLGRJaInuf6UW4uKD023DFrsqNSiTq6', 'Inactive', '2026-03-21 04:34:13'),
(2, 'earl', 'earl', 'admin', 'earl', '$2y$10$CNOdVmjP985uY0LzQrShTuYyGYy5R4hluoL8l8jGZQrPTvXBSS5mW', 'Inactive', '2026-03-21 04:48:43'),
(3, 'staff', 'staff', 'staff', 'staff', '$2y$10$c7VaVPwMJeOFry9bPn1hz.dn9zEHylP0GFJ9a2RglcwdSta2LZqtq', 'Active', '2026-04-03 12:11:53'),
(4, 'EARL', '43434aaa', 'admin', 'earl1', '$2y$10$MUptJTuzea/XWReiQBtrw.LHyedjV01xZ0eRAo5d1MyLEC0//4P4u', 'Active', '2026-04-03 12:21:11'),
(5, '123123', '123123', 'admin', 'earl123', '$2y$10$j9CsO9RjKWpMY4tS35hFheVVt585wik6nF8fhpL4/f2jf8OpnSnj.', 'Active', '2026-04-03 12:57:42'),
(6, 'EARL', '43434aaa', 'admin', '1234', '$2y$10$FO3zD6gKkkhA0/ZA2sABy.jOruooLm.t8hbK8x7I3vGdztB2kzCUW', 'Inactive', '2026-04-03 13:05:47'),
(7, 'EARL', '43434aaa', 'admin', '12345', '$2y$10$pb1riBWNK1a1OeCqK6JogOJXm3tMgYXh4Fr0XyCcmCGWqElwI3JBC', 'Active', '2026-04-06 10:19:42'),
(8, '1232', '123123', 'staff', '111', '$2y$10$XJvmgvXzGylUyWqpVfVpE.CD5Cmatw5ZGfW.mIFJEbSnPDEd15U6e', 'Inactive', '2026-04-06 11:19:32'),
(9, 'EARL', '43434', 'admin', 'asd', '$2y$10$Ee.SS4eG6QfMeafvBHWYSOILl.o80eWSb1PGjp30Q8C8BuGh5eF1K', 'Active', '2026-04-06 11:36:20'),
(10, '1232asd', '123123', 'admin', '123123', '$2y$10$bBt6ylYz0u2UcdU7RDsmIeyEx5uTYI62peV06cfOCW1udO86RLCua', 'Inactive', '2026-04-06 13:18:26'),
(11, 'TEST', 'TEST', 'admin', 'admin123', '$2y$10$wo2.Ox0ynmfosge7pQyVtO4eXlpV9dLc5n8yZ0Dv8KVKS9dxhboMm', 'Active', '2026-04-07 10:32:51'),
(12, 'TEST', 'TEST', 'admin', 'admin123123', '$2y$10$vKJHLdLrLTc2A.J40F9Ll.6uxCx3UPP1FCyfbQYVWE1FQ5SBI5BOS', 'Active', '2026-04-07 10:32:54'),
(13, 'TEST', 'TEST', 'admin', 'admin123123123', '$2y$10$TQ46iPnA9SbqxIB7j1WyZOaJs7eWQfakpw8MGCbT9YT/PLDqvaZ9y', 'Active', '2026-04-07 10:32:56'),
(14, 'asdas', 'asdas', 'admin', 'asdas', '$2y$10$kiMSnP54AvAjovo6vR3uJecqpveDb8F6HXHsZfQ9tbWQoS7QYvWDq', 'Active', '2026-04-07 10:33:33'),
(15, 'aqsdasdasd', 'asdasdasdasd', 'admin', 'asdasda', '$2y$10$mK1XIW7yfRLiXESvXfZj/uZFi4.xFMVPk2XrtIAwhiW6VAo5qQPBS', 'Active', '2026-04-07 10:34:57'),
(16, '123123', '123123', 'admin', 'ads', '$2y$10$wvkBak999FstnEkXNcrxD.4mNLxejpQIzt6612nrYDRlIX1yM9hyK', 'Active', '2026-04-07 10:36:21'),
(17, 'EARL', '43434aaa', 'admin', 'adminto', '$2y$10$bqpdoX/8cVT1wdDJnMByVO2SNrgg1cu4hVwLjwxP2b5.1bv8bQ33q', 'Active', '2026-04-07 10:40:01'),
(18, 'staff', 'staff', 'staff', 'staffto', '$2y$10$7csfEPkIJbctoJ.4qI/W7.Jky33GP6T9MEJOEUplncCLwYC/nqTcy', 'Active', '2026-04-07 10:42:04'),
(19, 'asdas', 'adasd', 'admin', 'asdaa', '$2y$10$XsgwiYei3tWLJm/vM3FV8eF6MnY3fNwnSbGzhaP3PV5cJmPKfGBka', 'Active', '2026-04-07 10:42:18'),
(20, 'asdas', 'asdasd', 'staff', 'aa', '$2y$10$77610CjN7g9QiJK441ZbUOIQDMdCCakzPtGH.UKCDsLfPBveQbdWe', 'Active', '2026-04-07 10:43:10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aid_program`
--
ALTER TABLE `aid_program`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `distribution_logs`
--
ALTER TABLE `distribution_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_claim` (`program_id`,`household_id`),
  ADD KEY `household_id` (`household_id`),
  ADD KEY `rfid_tag_id` (`rfid_tag_id`),
  ADD KEY `idx_program_id` (`program_id`);

--
-- Indexes for table `registered_household`
--
ALTER TABLE `registered_household`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `household_number` (`household_number`),
  ADD KEY `head_of_family_id` (`head_of_family_id`);

--
-- Indexes for table `registered_resi`
--
ALTER TABLE `registered_resi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_resident_household` (`household_id`);

--
-- Indexes for table `rfid_tags`
--
ALTER TABLE `rfid_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rfid_number` (`rfid_number`),
  ADD KEY `idx_household_id` (`household_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aid_program`
--
ALTER TABLE `aid_program`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `distribution_logs`
--
ALTER TABLE `distribution_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `registered_household`
--
ALTER TABLE `registered_household`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `registered_resi`
--
ALTER TABLE `registered_resi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rfid_tags`
--
ALTER TABLE `rfid_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `distribution_logs`
--
ALTER TABLE `distribution_logs`
  ADD CONSTRAINT `distribution_logs_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `aid_program` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `distribution_logs_ibfk_2` FOREIGN KEY (`household_id`) REFERENCES `registered_household` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `distribution_logs_ibfk_3` FOREIGN KEY (`rfid_tag_id`) REFERENCES `rfid_tags` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `registered_household`
--
ALTER TABLE `registered_household`
  ADD CONSTRAINT `registered_household_ibfk_1` FOREIGN KEY (`head_of_family_id`) REFERENCES `registered_resi` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `registered_resi`
--
ALTER TABLE `registered_resi`
  ADD CONSTRAINT `registered_resi_ibfk_1` FOREIGN KEY (`household_id`) REFERENCES `registered_household` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `rfid_tags`
--
ALTER TABLE `rfid_tags`
  ADD CONSTRAINT `rfid_tags_ibfk_1` FOREIGN KEY (`household_id`) REFERENCES `registered_household` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
