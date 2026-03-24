-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 24, 2026 at 02:08 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

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
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `aid_program`
--

INSERT INTO `aid_program` (`id`, `program_name`, `aid_type`, `date_scheduled`, `beneficiaries`, `status`) VALUES
(1, 'ear', 'asd', '2026-03-21', 123, 'Active');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registered_household`
--

INSERT INTO `registered_household` (`id`, `household_number`, `head_of_family_id`, `address`, `is_archived`, `created_at`) VALUES
(1, 'HH-00001', 8, 'Block 5, Abangan Norte', 1, '2026-03-21 05:05:33'),
(2, 'HH-00002', 8, 'Block 5, Abangan Norte', 1, '2026-03-21 05:07:00'),
(3, 'HH-00003', 19, 'Block 10, Abangan Norte', 1, '2026-03-21 05:10:05'),
(4, 'HH-00004', 8, 'Block 5, Abangan Norte', 0, '2026-03-21 05:21:26'),
(5, 'HH-00005', 15, 'Block 8, Abangan Norte', 1, '2026-03-21 05:22:50');

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

--
-- Dumping data for table `registered_resi`
--

INSERT INTO `registered_resi` (`id`, `household_id`, `first_name`, `middle_name`, `last_name`, `age`, `gender`, `civil_status`, `occupation`, `voters_registration_no`, `address`, `birthdate`, `contact`, `is_archived`, `version`, `created_at`) VALUES
(1, NULL, 'Lyka', 'Anne', 'Canillo', 21, 'Female', 'Single', 'Student', '123', 'Marilao', '2005-02-23', '09795583491', 1, 1, '2026-03-21 04:56:50'),
(2, NULL, 'Juan', 'Reyes', 'Dela Cruz', 40, 'Male', 'Married', 'Driver', 'VOT-1001', 'Block 1, Abangan Norte', '1985-06-15', '09171234561', 0, 1, '2026-03-21 05:04:16'),
(3, NULL, 'Maria', 'Santos', 'Clara', 37, 'Female', 'Married', 'Teacher', 'VOT-1002', 'Block 1, Abangan Norte', '1988-09-22', '09171234562', 0, 1, '2026-03-21 05:04:16'),
(4, NULL, 'Pedro', 'Gomez', 'Penduko', 24, 'Male', 'Single', 'Student', 'Not Registered', 'Block 2, Abangan Norte', '2001-11-05', '09181234563', 0, 1, '2026-03-21 05:04:16'),
(5, NULL, 'Ana', 'Bautista', 'Dimaculangan', 31, 'Female', 'Single', 'Nurse', 'VOT-1003', 'Block 3, Abangan Norte', '1995-02-14', '09191234564', 0, 1, '2026-03-21 05:04:16'),
(6, NULL, 'Jose', 'Rizal', 'Mercado', 65, 'Male', 'Widowed', 'Retired', 'VOT-1004', 'Block 4, Abangan Norte', '1960-12-30', 'N/A', 0, 1, '2026-03-21 05:04:16'),
(7, NULL, 'Luz', 'Mendoza', 'Valdez', 50, 'Female', 'Married', 'Vendor', 'VOT-1005', 'Block 2, Abangan Norte', '1975-08-08', '09201234565', 0, 1, '2026-03-21 05:04:16'),
(8, 4, 'Carlos', 'Garcia', 'Agoncillo', 34, 'Male', 'Married', 'Engineer', 'VOT-1006', 'Block 5, Abangan Norte', '1992-04-18', '09211234566', 0, 1, '2026-03-21 05:04:16'),
(9, NULL, 'Elena', 'Cruz', 'Tolentino', 31, 'Female', 'Married', 'Accountant', 'VOT-1007', 'Block 5, Abangan Norte', '1994-07-25', '09221234567', 0, 1, '2026-03-21 05:04:16'),
(10, NULL, 'Miguel', 'Roxas', 'Zobel', 21, 'Male', 'Single', 'Student', 'Not Registered', 'Block 6, Abangan Norte', '2005-01-10', '09231234568', 0, 1, '2026-03-21 05:04:16'),
(11, 4, 'Sofia', 'Luna', 'Andres', 18, 'Female', 'Single', 'Student', 'Not Registered', 'Block 6, Abangan Norte', '2008-05-20', '09241234569', 0, 1, '2026-03-21 05:04:16'),
(12, NULL, 'Ricardo', 'Dalisay', 'Probinsyano', 45, 'Male', 'Married', 'Police Officer', 'VOT-1008', 'Block 7, Abangan Norte', '1980-10-10', '09251234570', 0, 1, '2026-03-21 05:04:16'),
(13, NULL, 'Carmen', 'Soriano', 'Reyes', 43, 'Female', 'Married', 'Business Owner', 'VOT-1009', 'Block 7, Abangan Norte', '1982-12-12', '09261234571', 0, 1, '2026-03-21 05:04:16'),
(14, NULL, 'Ramon', 'Revilla', 'Bautista', 71, 'Male', 'Married', 'Retired', 'VOT-1010', 'Block 8, Abangan Norte', '1955-03-08', '09271234572', 0, 1, '2026-03-21 05:04:16'),
(15, NULL, 'Gloria', 'Macapagal', 'Arroyo', 68, 'Female', 'Married', 'None', 'VOT-1011', 'Block 8, Abangan Norte', '1958-04-05', '09281234573', 0, 1, '2026-03-21 05:04:16'),
(16, NULL, 'Ferdinand', 'Marcos', 'Romualdez', 27, 'Male', 'Single', 'IT Specialist', 'VOT-1012', 'Block 9, Abangan Norte', '1998-09-11', '09291234574', 0, 1, '2026-03-21 05:04:16'),
(17, NULL, 'Imelda', 'Romualdez', 'Marcos', 26, 'Female', 'Single', 'Designer', 'VOT-1013', 'Block 9, Abangan Norte', '1999-11-20', '09301234575', 0, 1, '2026-03-21 05:04:16'),
(18, NULL, 'Benigno', 'Aquino', 'Cojuangco', 37, 'Male', 'Married', 'Manager', 'VOT-1014', 'Block 10, Abangan Norte', '1989-02-08', '09311234576', 0, 1, '2026-03-21 05:04:16'),
(19, 4, 'Corazon', 'Cojuangco', 'Aquino', 35, 'Female', 'Married', 'HR Staff', 'VOT-1015', 'Block 10, Abangan Norte', '1991-01-25', '09321234577', 0, 1, '2026-03-21 05:04:16'),
(20, NULL, 'Rodrigo', 'Duterte', 'Roa', 56, 'Male', 'Widowed', 'Lawyer', 'VOT-1016', 'Block 11, Abangan Norte', '1970-03-28', '09331234578', 0, 1, '2026-03-21 05:04:16'),
(21, NULL, 'Leni', 'Robredo', 'Gerona', 51, 'Female', 'Widowed', 'Social Worker', 'VOT-1017', 'Block 11, Abangan Norte', '1975-04-23', '09341234579', 0, 1, '2026-03-21 05:04:16'),
(22, NULL, 'Manny', 'Pacquiao', 'Dapidran', 42, 'Male', 'Married', 'Athlete', 'VOT-1018', 'Block 12, Abangan Norte', '1983-12-17', '09351234580', 0, 1, '2026-03-21 05:04:16'),
(23, NULL, 'Jinkee', 'Jamora', 'Pacquiao', 41, 'Female', 'Married', 'None', 'VOT-1019', 'Block 12, Abangan Norte', '1985-01-12', '09361234581', 0, 1, '2026-03-21 05:04:16'),
(24, NULL, 'Coco', 'Martin', 'Nacianceno', 35, 'Male', 'Single', 'Actor', 'VOT-1020', 'Block 13, Abangan Norte', '1990-11-01', '09371234582', 0, 1, '2026-03-21 05:04:16'),
(25, NULL, 'Vice', 'Ganda', 'Viceral', 38, 'Other', 'Single', 'Comedian', 'VOT-1021', 'Block 13, Abangan Norte', '1988-03-31', '09381234583', 0, 1, '2026-03-21 05:04:16'),
(26, NULL, 'Kathryn', 'Bernardo', 'Bria', 24, 'Female', 'Single', 'Student', 'Not Registered', 'Block 14, Abangan Norte', '2002-03-26', '09391234584', 0, 1, '2026-03-21 05:04:16');

-- --------------------------------------------------------

--
-- Table structure for table `rfid_tags`
--

CREATE TABLE `rfid_tags` (
  `id` int(11) NOT NULL,
  `household_id` int(11) NOT NULL,
  `rfid_number` varchar(50) NOT NULL,
  `status` enum('Active','Disabled','Lost') NOT NULL DEFAULT 'Active',
  `issued_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rfid_tags`
--

INSERT INTO `rfid_tags` (`id`, `household_id`, `rfid_number`, `status`, `issued_date`) VALUES
(5, 4, '12312', 'Disabled', '2026-03-21 12:38:08'),
(6, 4, '33', 'Disabled', '2026-03-21 12:38:24');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `role`, `username`, `password`, `status`, `created_at`) VALUES
(1, 'System', 'Admin', 'admin', 'admin', '$2y$10$xWOJvB0rZ41G44wH2NTLouCLGRJaInuf6UW4uKD023DFrsqNSiTq6', 'Active', '2026-03-21 04:34:13'),
(2, 'earl', 'earl', 'admin', 'earl', '$2y$10$GtBqJKXt2hx6lSZH34jwiexv5Vtu0QPM6CJm2nPKPXTQ.PHJrPyY.', 'Active', '2026-03-21 04:48:43');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `registered_resi`
--
ALTER TABLE `registered_resi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `rfid_tags`
--
ALTER TABLE `rfid_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
