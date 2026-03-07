-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 07, 2026 at 03:30 AM
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
  `program_name` varchar(255) DEFAULT NULL,
  `aid_type` varchar(100) DEFAULT NULL,
  `date_scheduled` date DEFAULT NULL,
  `beneficiaries` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `aid_program`
--

INSERT INTO `aid_program` (`id`, `program_name`, `aid_type`, `date_scheduled`, `beneficiaries`, `status`) VALUES
(1, '4234', '423', '2026-01-02', 456, 'Active'),
(2, '423423423423', '4234234', '2025-06-10', 4234, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `distribution_logs`
--

CREATE TABLE `distribution_logs` (
  `id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `household_number` varchar(50) NOT NULL,
  `rfid_number` varchar(50) NOT NULL,
  `date_claimed` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registered_household`
--

CREATE TABLE `registered_household` (
  `id` int(11) NOT NULL,
  `household_number` varchar(50) NOT NULL,
  `head_of_family` varchar(100) DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `household_members` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `rfid` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registered_household`
--

INSERT INTO `registered_household` (`id`, `household_number`, `head_of_family`, `address`, `household_members`, `created_at`, `rfid`) VALUES
(8, 'HH-00002', 'EARL RUZZLE CRUZ', '43', 'sample sample sample', '2026-03-07 02:05:04', NULL),
(11, 'HH-00003', 'EARL RUZZLE CRUZ', '43', 'sample sample sample', '2026-03-07 02:09:48', 'A1400701');

-- --------------------------------------------------------

--
-- Table structure for table `registered_resi`
--

CREATE TABLE `registered_resi` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `civil_status` varchar(20) NOT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `voters_registration_no` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registered_resi`
--

INSERT INTO `registered_resi` (`id`, `first_name`, `middle_name`, `last_name`, `age`, `gender`, `civil_status`, `occupation`, `voters_registration_no`, `address`, `birthdate`, `contact`, `created_at`) VALUES
(6, 'EARL', 'RUZZLE', 'CRUZ', 20, 'Female', 'Single', 'tAE', 'Not Registered', '43', '2005-12-19', '09785594819', '2026-03-07 01:47:41'),
(7, 'sample', 'sample', 'sample', 20, 'Male', 'Married', 'sample', '424234', 'sample', '2005-12-10', '09692283123', '2026-03-07 02:02:15');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `role` varchar(20) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `role`, `username`, `password`) VALUES
(1, 'Earl Ruzzle', 'cruz', 'admin', 'earl', '$2y$10$xWOJvB0rZ41G44wH2NTLouCLGRJaInuf6UW4uKD023DFrsqNSiTq6');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aid_program`
--
ALTER TABLE `aid_program`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `distribution_logs`
--
ALTER TABLE `distribution_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `registered_household`
--
ALTER TABLE `registered_household`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rfid` (`rfid`);

--
-- Indexes for table `registered_resi`
--
ALTER TABLE `registered_resi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aid_program`
--
ALTER TABLE `aid_program`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `distribution_logs`
--
ALTER TABLE `distribution_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `registered_household`
--
ALTER TABLE `registered_household`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `registered_resi`
--
ALTER TABLE `registered_resi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
