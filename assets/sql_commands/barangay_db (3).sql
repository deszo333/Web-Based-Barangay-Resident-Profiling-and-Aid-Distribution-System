-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 06, 2026 at 01:48 PM
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
  `status` varchar(50) NOT NULL,
  `version` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `aid_program`
--

INSERT INTO `aid_program` (`id`, `program_name`, `aid_type`, `date_scheduled`, `beneficiaries`, `status`, `version`) VALUES
(1, 'ear', 'asd', '2026-03-21', 123, 'Active', 1),
(2, '12323', 'Food', '2026-03-24', 123, 'Active', 4),
(3, '31231', '13123', '2026-04-01', 123, 'Active', 3),
(4, 'Ayuda 2025', 'Food packss', '2026-03-29', 126, 'Inactive', 4),
(5, '1223', '12321', '2026-04-01', 123, 'Active', 3),
(7, 'Ayuda 2025', 'Food', '2026-04-07', 123, 'Ongoing', 1),
(8, '12123', '12123', '2026-04-17', 4, 'Ongoing', 1);

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

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action_type`, `target_module`, `details`, `timestamp`) VALUES
(1, 2, 'Create', 'Household Management', 'Created household: HH-00006', '2026-03-26 06:42:20'),
(2, 2, 'Create', 'Household Management', 'Created household: HH-00007', '2026-03-26 06:45:33'),
(3, 2, 'Create', 'Household Management', 'Created household: HH-00008', '2026-03-26 06:46:25'),
(4, 2, 'Update', 'Resident Profiling', 'Updated profile for Resident ID: 26', '2026-03-26 06:54:28'),
(5, 2, 'Update', 'RFID Management', 'Updated RFID Tag 323 | Assigned to Household: HH-00006', '2026-03-26 07:00:45'),
(6, 2, 'Update', 'RFID Management', 'Updated RFID Tag 3231 | Assigned to Household: HH-00006', '2026-03-26 07:00:54'),
(7, 2, 'Update', 'Resident Profiling', 'Updated profile: Kathryn Brianaa (Kathryn, Bernardods1 Brianaa)', '2026-03-26 07:01:03'),
(8, 2, 'Update', 'Resident Profiling', 'Updated profile: Kathryn Brianaa3 (Kathryn, Bernardods1 Brianaa3)', '2026-03-26 07:01:06'),
(9, 2, 'Update', 'Resident Profiling', '{\"action_summary\":\"Resident Profile Updated\",\"resident_name\":\"Kathryn Brianaa32\",\"resident_id\":26,\"fields_modified\":\"Multiple profile fields\"}', '2026-03-26 07:05:49'),
(10, 2, 'Update', 'Household Management', '{\"action_summary\":\"Household Profile Updated\",\"household_number\":\"HH-00008\",\"head_of_family\":\"Corazon Aquino\",\"address\":\"Block 10, Abangan Nort11\"}', '2026-03-26 07:06:57'),
(11, 2, 'Update', 'Resident Profiling', '{\"action_summary\":\"Resident Profile Updated\",\"resident_name\":\"Kathryn Brianaa32123\",\"resident_id\":26,\"fields_modified\":\"Multiple profile fields\"}', '2026-03-26 07:07:02'),
(12, 2, 'Update', 'RFID Management', '{\"action_summary\":\"RFID Tag Reassigned\",\"rfid_number\":\"32311\",\"rfid_id\":\"11\",\"household_number\":\"HH-00006\"}', '2026-03-26 07:07:13'),
(13, 2, 'Update', 'RFID Management', '{\"action_summary\":\"RFID Tag Reassigned\",\"rfid_number\":\"123\",\"rfid_id\":\"11\",\"household_number\":\"HH-00006\"}', '2026-03-26 07:07:24'),
(14, 2, 'Update', 'RFID Management', '{\"action_summary\":\"RFID Tag Reassigned\",\"rfid_number\":\"52345345\",\"rfid_id\":\"11\",\"household_number\":\"HH-00006\"}', '2026-03-26 07:07:31'),
(15, 2, 'Update', 'Aid Programs', '{\"action_summary\":\"Aid Program Updated\",\"program_name\":\"12323\",\"program_id\":\"2\",\"aid_type\":\"Food\",\"date_scheduled\":\"2026-03-24\",\"beneficiaries\":\"123\",\"status\":\"Active\"}', '2026-03-26 07:09:47'),
(16, 2, 'Update', 'RFID Management', '{\"action_summary\":\"RFID Tag Reassigned\",\"rfid_number\":\"52345345\",\"rfid_id\":\"11\",\"household_number\":\"HH-00006\"}', '2026-03-26 07:10:30'),
(17, 2, 'Update', 'RFID Management', '{\"action_summary\":\"RFID Tag Reassigned\",\"rfid_number\":\"52345345\",\"rfid_id\":\"11\",\"household_number\":\"HH-00006\"}', '2026-03-26 08:54:12'),
(18, 2, 'Update', 'RFID Management', '{\"action_summary\":\"RFID Tag Reassigned\",\"rfid_number\":\"5234534534\",\"rfid_id\":\"11\",\"household_number\":\"HH-00006\"}', '2026-03-26 08:54:16'),
(19, 2, 'Update', 'RFID Management', '{\"action_summary\":\"RFID Tag Reassigned\",\"rfid_number\":\"123\",\"rfid_id\":\"11\",\"household_number\":\"HH-00006\"}', '2026-03-26 08:54:19'),
(20, 2, 'Create', 'Aid Program Setup', '{\"action_summary\":\"New Aid Program Created\",\"program_name\":\"31231\",\"program_id\":3,\"aid_type\":\"13123\",\"date_scheduled\":\"2026-03-27\",\"beneficiaries\":\"123\",\"status\":\"Active\"}', '2026-03-27 12:34:53'),
(21, 2, 'Create', 'Resident Profiling', '{\"action_summary\":\"New Resident Profile Created\",\"resident_name\":\"4234 23423 4234234\",\"resident_id\":27,\"address\":\"4234234\",\"birthdate\":\"0323-02-12\"}', '2026-03-28 02:31:16'),
(22, 2, 'Create', 'Household Management', '{\"action_summary\":\"New Household Created\",\"household_number\":\"HH-00009\",\"head_of_family_id\":\"8\",\"address\":\"Block 5, Abangan Norte\"}', '2026-03-28 02:40:23'),
(23, 2, 'Create', 'Household Management', '{\"action_summary\":\"New Household Created\",\"household_number\":\"HH-00010\",\"head_of_family_id\":\"8\",\"address\":\"Block 5, Abangan Norte\"}', '2026-03-28 02:56:58'),
(24, 2, 'Create', 'Household Management', '{\"action_summary\":\"New Household Created\",\"household_number\":\"HH-00011\",\"head_of_family_id\":\"8\",\"address\":\"Block 5, Abangan Norte\"}', '2026-03-28 02:59:38'),
(25, 2, 'Create', 'Household Management', '{\"action_summary\":\"New Household Created\",\"household_number\":\"HH-00012\",\"head_of_family_id\":\"19\",\"address\":\"Block 10, Abangan Norte\"}', '2026-03-28 02:59:47'),
(26, 2, 'Create', 'Household Management', '{\"action_summary\":\"New Household Created\",\"household_number\":\"HH-00013\",\"head_of_family_id\":\"8\",\"address\":\"Block 5, Abangan Norte\"}', '2026-03-28 03:02:05'),
(27, 2, 'Create', 'Household Management', '{\"action_summary\":\"New Household Created\",\"household_number\":\"HH-00014\",\"head_of_family_id\":\"15\",\"address\":\"Block 8, Abangan Norte\"}', '2026-03-28 03:02:16'),
(28, 2, 'Create', 'Household Management', '{\"action_summary\":\"New Household Created\",\"household_number\":\"HH-00015\",\"head_of_family_id\":\"11\",\"address\":\"Block 6, Abangan Norte\"}', '2026-03-28 03:02:39'),
(29, 2, 'Create', 'Household Management', '{\"action_summary\":\"New Household Created\",\"household_number\":\"HH-00016\",\"head_of_family_id\":\"8\",\"address\":\"Block 5, Abangan Norte\"}', '2026-03-28 03:04:09'),
(30, 2, 'Create', 'Household Management', '{\"action_summary\":\"New Household Created\",\"household_number\":\"HH-00017\",\"head_of_family_id\":\"19\",\"address\":\"Block 10, Abangan Norte\"}', '2026-03-28 03:04:25'),
(31, 2, 'Create', 'Household Management', '{\"action_summary\":\"New Household Created\",\"household_number\":\"HH-00018\",\"head_of_family_id\":\"14\",\"address\":\"Block 8, Abangan Norte\"}', '2026-03-28 03:05:31'),
(32, 2, 'Create', 'Household Management', '{\"action_summary\":\"New Household Created\",\"household_number\":\"HH-00019\",\"head_of_family_id\":\"27\",\"address\":\"4234234\"}', '2026-03-28 03:32:16'),
(33, 2, 'Create', 'Aid Program Setup', '{\"action_summary\":\"New Aid Program Created\",\"program_name\":\"Ayuda 2025\",\"program_id\":4,\"aid_type\":\"Food packs\",\"date_scheduled\":\"2026-03-28\",\"beneficiaries\":\"123\",\"status\":\"Active\"}', '2026-03-28 05:39:43'),
(34, 2, 'Update', 'Household Management', '{\"action_summary\":\"Household Profile Updated\",\"household_number\":\"HH-00019\",\"head_of_family\":\"4234 4234234\",\"address\":\"4234234\"}', '2026-03-28 05:40:04'),
(35, 2, 'Update', 'RFID Management', '{\"action_summary\":\"RFID Tag Reassigned\",\"rfid_number\":\"123\",\"rfid_id\":\"11\",\"household_number\":\"HH-00016\"}', '2026-03-28 05:49:02'),
(36, 2, 'Create', 'RFID Tag Issuance', '{\"action_summary\":\"RFID Tag Issued\",\"rfid_number\":\"12323\",\"rfid_id\":15,\"household_number\":\"HH-00016\",\"status\":\"Active\"}', '2026-03-28 05:49:37'),
(37, 2, 'Create', 'RFID Tag Issuance', '{\"action_summary\":\"RFID Tag Issued\",\"rfid_number\":\"75FF0801\",\"rfid_id\":16,\"household_number\":\"HH-00016\",\"status\":\"Active\"}', '2026-03-28 06:13:36'),
(38, 2, 'Update', 'Aid Programs', '{\"action_summary\":\"Aid Program Updated\",\"program_name\":\"Ayuda 2025\",\"program_id\":\"4\",\"aid_type\":\"Food packs\",\"date_scheduled\":\"2026-03-29\",\"beneficiaries\":\"123\",\"status\":\"Active\"}', '2026-03-29 02:36:00'),
(39, 2, 'Update', 'RFID Management', '{\"action_summary\":\"RFID Tag Reassigned\",\"rfid_number\":\"111\",\"rfid_id\":\"16\",\"household_number\":\"HH-00016\"}', '2026-03-29 02:39:47'),
(40, 2, 'Update', 'Aid Programs', '{\"action_summary\":\"Aid Program Updated\",\"program_name\":\"Ayuda 2025\",\"program_id\":\"4\",\"aid_type\":\"Food packss\",\"date_scheduled\":\"2026-03-29\",\"beneficiaries\":\"123\",\"status\":\"Active\"}', '2026-03-29 02:52:01'),
(41, 2, 'Update', 'RFID Management', '{\"action_summary\":\"RFID Tag Reassigned\",\"rfid_number\":\"1111\",\"rfid_id\":\"16\",\"household_number\":\"HH-00016\"}', '2026-03-29 02:52:12'),
(42, 2, 'Update', 'Household Management', '{\"action_summary\":\"Household Profile Updated\",\"household_number\":\"HH-00019\",\"head_of_family\":\"4234 4234234\",\"address\":\"4234234\"}', '2026-03-29 02:52:23'),
(43, 2, 'Create', 'Resident Profiling', '{\"action_summary\":\"New Resident Profile Created\",\"resident_name\":\"Sample Sample Sample\",\"resident_id\":28,\"address\":\"Sample\",\"birthdate\":\"2005-02-10\"}', '2026-04-01 04:14:16'),
(44, 2, 'Create', 'Household Management', '{\"action_summary\":\"New Household Created\",\"household_number\":\"HH-00020\",\"head_of_family_id\":\"28\",\"address\":\"Sample\"}', '2026-04-01 04:14:59'),
(45, 2, 'Update', 'Household Management', '{\"action_summary\":\"Household Profile Updated\",\"household_number\":\"HH-00019\",\"head_of_family\":\"4234 4234234\",\"address\":\"4234234\"}', '2026-04-01 04:15:13'),
(46, 2, 'Update', 'Aid Programs', '{\"action_summary\":\"Aid Program Updated\",\"program_name\":\"Ayuda 2025\",\"program_id\":\"4\",\"aid_type\":\"Food packss\",\"date_scheduled\":\"2026-03-29\",\"beneficiaries\":\"126\",\"status\":\"Inactive\"}', '2026-04-01 04:19:39'),
(47, 2, 'Create', 'Aid Program Setup', '{\"action_summary\":\"New Aid Program Created\",\"program_name\":\"1223\",\"program_id\":5,\"aid_type\":\"123\",\"date_scheduled\":\"2026-04-01\",\"beneficiaries\":\"123\",\"status\":\"Active\"}', '2026-04-01 04:25:14'),
(48, 2, 'Update', 'Resident Profiling', '{\"action_summary\":\"Resident Profile Updated\",\"resident_name\":\"Sample Sample\",\"resident_id\":28,\"fields_modified\":\"Multiple profile fields\"}', '2026-04-01 04:25:24'),
(49, 2, 'Update', 'Household Management', '{\"action_summary\":\"Household Profile Updated\",\"household_number\":\"HH-00020\",\"head_of_family\":\"Benigno Cojuangco\",\"address\":\"Block 10, Abangan Norte\"}', '2026-04-01 04:25:31'),
(50, 2, 'Update', 'Household Management', '{\"action_summary\":\"Household Profile Updated\",\"household_number\":\"HH-00020\",\"head_of_family\":\"Benigno Cojuangco\",\"address\":\"Block 10, Abangan Norte\"}', '2026-04-01 04:46:22'),
(51, 2, 'Create', 'Resident Profiling', '{\"action_summary\":\"New Resident Profile Created\",\"resident_name\":\"Consety Baho Cruz\",\"resident_id\":29,\"address\":\"09 J. Francisco\",\"birthdate\":\"2005-12-10\"}', '2026-04-01 04:55:47'),
(52, 2, 'Update', 'Resident Profiling', '{\"action_summary\":\"Resident Profile Updated\",\"resident_name\":\"Consety11 Cruz\",\"resident_id\":29,\"fields_modified\":\"Multiple profile fields\"}', '2026-04-01 04:56:10'),
(53, 2, 'Create', 'Household Management', '{\"action_summary\":\"New Household Created\",\"household_number\":\"HH-00021\",\"head_of_family_id\":\"29\",\"address\":\"09 J. Francisco\"}', '2026-04-01 04:56:45'),
(54, 2, 'Create', 'Aid Program Setup', '{\"action_summary\":\"New Aid Program Created\",\"program_name\":\"Ayuda 2026\",\"program_id\":6,\"aid_type\":\"Food Packs\",\"date_scheduled\":\"2026-04-01\",\"beneficiaries\":\"3\",\"status\":\"Active\"}', '2026-04-01 04:57:14'),
(55, 2, 'Update', 'Resident Profiling', '{\"action_summary\":\"Resident Profile Updated\",\"resident_name\":\"Consety11 Cruz\",\"resident_id\":29,\"fields_modified\":\"Multiple profile fields\"}', '2026-04-01 04:59:59'),
(56, 2, 'Update', 'Resident Profiling', '{\"action_summary\":\"Resident Profile Updated\",\"resident_name\":\"Consety11 Cruz1\",\"resident_id\":29,\"fields_modified\":\"Multiple profile fields\"}', '2026-04-01 05:00:06'),
(57, 2, 'Update', 'Household Management', '{\"action_summary\":\"Household Profile Updated\",\"household_number\":\"HH-00021\",\"head_of_family\":\"Consety11 Cruz1\",\"address\":\"09 J. Francisco11\"}', '2026-04-01 05:00:17'),
(58, 2, 'Update', 'Household Management', '{\"action_summary\":\"Household Profile Updated\",\"household_number\":\"HH-00021\",\"head_of_family\":\"Consety11 Cruz1\",\"address\":\"09 J. Francisco4343\"}', '2026-04-01 05:00:19'),
(59, 2, 'Update', 'Household Management', '{\"action_summary\":\"Household Profile Updated\",\"household_number\":\"HH-00021\",\"head_of_family\":\"Consety11 Cruz1\",\"address\":\"09 J. Francisco115345345\"}', '2026-04-01 05:00:29'),
(60, 2, 'Update', 'Household Management', '{\"action_summary\":\"Household Profile Updated\",\"household_number\":\"HH-00021\",\"head_of_family\":\"Consety11 Cruz1\",\"address\":\"09 J. Francisco11123\"}', '2026-04-01 05:00:32'),
(61, 2, 'Update', 'Household Management', '{\"action_summary\":\"Household Profile Updated\",\"household_number\":\"HH-00021\",\"head_of_family\":\"Consety11 Cruz1\",\"address\":\"09 J. Francisco11534534523\"}', '2026-04-01 05:04:41'),
(62, 2, 'Update', 'Aid Programs', '{\"action_summary\":\"Aid Program Updated\",\"program_name\":\"Ayuda 2026\",\"program_id\":\"6\",\"aid_type\":\"Food Pa1cks\",\"date_scheduled\":\"2026-04-01\",\"beneficiaries\":\"3\",\"status\":\"Active\"}', '2026-04-01 05:04:50'),
(63, 2, 'Update', 'Aid Programs', '{\"action_summary\":\"Aid Program Updated\",\"program_name\":\"Ayuda 2026\",\"program_id\":\"6\",\"aid_type\":\"Food Pa1cks44\",\"date_scheduled\":\"2026-04-01\",\"beneficiaries\":\"3\",\"status\":\"Active\"}', '2026-04-01 05:04:58'),
(64, 2, 'Update', 'RFID Management', '{\"action_summary\":\"RFID Tag Reassigned\",\"rfid_number\":\"C64A070111\",\"rfid_id\":\"17\",\"household_number\":\"HH-00020\"}', '2026-04-01 05:05:28'),
(65, 2, 'Update', 'RFID Management', '{\"action_summary\":\"RFID Tag Reassigned\",\"rfid_number\":\"C64A07011123\",\"rfid_id\":\"17\",\"household_number\":\"HH-00020\"}', '2026-04-01 05:05:34'),
(66, 2, 'Update', 'RFID Management', '{\"action_summary\":\"RFID Tag Reassigned\",\"rfid_number\":\"1111123\",\"rfid_id\":\"16\",\"household_number\":\"HH-00016\"}', '2026-04-01 05:05:38'),
(67, 2, 'Update', 'RFID Management', '{\"action_summary\":\"RFID Tag Reassigned\",\"rfid_number\":\"11111232342\",\"rfid_id\":\"16\",\"household_number\":\"HH-00016\"}', '2026-04-01 05:05:44'),
(68, 4, 'Update', 'Resident Profiling', '{\"action_summary\":\"Resident Profile Updated\",\"resident_name\":\"Consety11 Cruz1\",\"resident_id\":29,\"fields_modified\":\"Multiple profile fields\"}', '2026-04-03 12:27:52'),
(69, 6, 'Update', 'Household Management', '{\"action_summary\":\"Household Profile Updated\",\"household_number\":\"HH-00021\",\"head_of_family\":\"Consety11 Cruz1\",\"address\":\"09 J. Francisco11534534523\"}', '2026-04-03 13:12:48'),
(70, 7, 'Create', 'Aid Program Setup', '{\"action_summary\":\"New Aid Program Created\",\"program_name\":\"Ayuda 2025\",\"program_id\":7,\"aid_type\":\"Food\",\"date_scheduled\":\"2026-04-07\",\"beneficiaries\":\"123\",\"status\":\"Scheduled\"}', '2026-04-06 10:56:22'),
(71, 7, 'Create', 'Aid Program Setup', '{\"action_summary\":\"New Aid Program Created\",\"program_name\":\"12123\",\"program_id\":8,\"aid_type\":\"12123\",\"date_scheduled\":\"2026-04-17\",\"beneficiaries\":\"4\",\"status\":\"Ongoing\"}', '2026-04-06 10:56:46'),
(72, 7, 'UPDATE', 'aid_program', 'Changed program status to Ongoing (Program ID: 7)', '2026-04-06 11:08:56'),
(73, 7, 'UPDATE', 'aid_program', 'Changed program status to Paused (Program ID: 8)', '2026-04-06 11:11:10'),
(74, 7, 'UPDATE', 'aid_program', 'Changed program status to Ongoing (Program ID: 8)', '2026-04-06 11:11:11'),
(75, 7, 'UPDATE', 'aid_program', 'Changed program status to Paused (Program ID: 8)', '2026-04-06 11:13:58'),
(76, 7, 'UPDATE', 'aid_program', 'Changed program status to Ongoing (Program ID: 8)', '2026-04-06 11:14:08'),
(77, 7, 'Update', 'RFID Management', '{\"action_summary\":\"RFID Tag Reassigned\",\"rfid_number\":\"12345\",\"rfid_id\":\"18\",\"household_number\":\"HH-00021\"}', '2026-04-06 11:16:01'),
(78, 7, 'CREATE', 'users', 'Created new user account: 111 (Role: staff)', '2026-04-06 11:19:32');

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

--
-- Dumping data for table `distribution_logs`
--

INSERT INTO `distribution_logs` (`id`, `program_id`, `household_id`, `rfid_tag_id`, `rfid_snapshot`, `date_claimed`) VALUES
(1, 4, 16, 16, '75FF0801', '2026-03-28 06:13:44'),
(2, 3, 20, 17, 'C64A0701', '2026-04-01 04:47:03'),
(4, 7, 21, 18, '12345', '2026-04-06 11:19:10');

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

--
-- Dumping data for table `registered_household`
--

INSERT INTO `registered_household` (`id`, `household_number`, `head_of_family_id`, `address`, `is_archived`, `created_at`, `version`) VALUES
(1, 'HH-00001', 8, 'Block 5, Abangan Norte', 1, '2026-03-21 05:05:33', 1),
(2, 'HH-00002', 8, 'Block 5, Abangan Norte', 1, '2026-03-21 05:07:00', 1),
(3, 'HH-00003', 19, 'Block 10, Abangan Norte', 1, '2026-03-21 05:10:05', 1),
(4, 'HH-00004', 8, 'Block 5, Abangan Norte', 1, '2026-03-21 05:21:26', 2),
(5, 'HH-00005', 15, 'Block 8, Abangan Norte', 1, '2026-03-21 05:22:50', 1),
(6, 'HH-00006', 19, 'Block 10, Abangan Norte', 1, '2026-03-26 06:42:20', 2),
(7, 'HH-00007', 19, 'Block 10, Abangan Norte', 1, '2026-03-26 06:45:33', 2),
(8, 'HH-00008', 19, 'Block 10, Abangan Nort11', 1, '2026-03-26 06:46:25', 3),
(9, 'HH-00009', 8, 'Block 5, Abangan Norte', 1, '2026-03-28 02:40:23', 1),
(10, 'HH-00010', 8, 'Block 5, Abangan Norte', 1, '2026-03-28 02:56:58', 1),
(11, 'HH-00011', 8, 'Block 5, Abangan Norte', 1, '2026-03-28 02:59:38', 1),
(12, 'HH-00012', 19, 'Block 10, Abangan Norte', 1, '2026-03-28 02:59:47', 1),
(13, 'HH-00013', 8, 'Block 5, Abangan Norte', 1, '2026-03-28 03:02:05', 1),
(14, 'HH-00014', 15, 'Block 8, Abangan Norte', 1, '2026-03-28 03:02:16', 1),
(15, 'HH-00015', 11, 'Block 6, Abangan Norte', 1, '2026-03-28 03:02:39', 1),
(16, 'HH-00016', 8, 'Block 5, Abangan Norte', 0, '2026-03-28 03:04:09', 1),
(17, 'HH-00017', 19, 'Block 10, Abangan Norte', 0, '2026-03-28 03:04:25', 1),
(18, 'HH-00018', 14, 'Block 8, Abangan Norte', 0, '2026-03-28 03:05:31', 1),
(19, 'HH-00019', 27, '4234234', 0, '2026-03-28 03:32:16', 4),
(20, 'HH-00020', 18, 'Block 10, Abangan Norte', 0, '2026-04-01 04:14:59', 3),
(21, 'HH-00021', 29, '09 J. Francisco11534534523', 0, '2026-04-01 04:56:45', 5);

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
(3, 20, 'Maria', 'Santos', 'Clara', 37, 'Female', 'Married', 'Teacher', 'VOT-1002', 'Block 1, Abangan Norte', '1988-09-22', '09171234562', 0, 1, '2026-03-21 05:04:16'),
(4, NULL, 'Pedro', 'Gomez', 'Penduko', 24, 'Male', 'Single', 'Student', 'Not Registered', 'Block 2, Abangan Norte', '2001-11-05', '09181234563', 0, 1, '2026-03-21 05:04:16'),
(5, NULL, 'Ana', 'Bautista', 'Dimaculangan', 31, 'Female', 'Single', 'Nurse', 'VOT-1003', 'Block 3, Abangan Norte', '1995-02-14', '09191234564', 0, 1, '2026-03-21 05:04:16'),
(6, NULL, 'Jose', 'Rizal', 'Mercado', 65, 'Male', 'Widowed', 'Retired', 'VOT-1004', 'Block 4, Abangan Norte', '1960-12-30', 'N/A', 0, 1, '2026-03-21 05:04:16'),
(7, NULL, 'Luz', 'Mendoza', 'Valdez', 50, 'Female', 'Married', 'Vendor', 'VOT-1005', 'Block 2, Abangan Norte', '1975-08-08', '09201234565', 0, 1, '2026-03-21 05:04:16'),
(8, NULL, 'Carlos', 'Garcia', 'Agoncillo', 34, 'Male', 'Married', 'Engineer', 'VOT-1006', 'Block 5, Abangan Norte', '1992-04-18', '09211234566', 0, 1, '2026-03-21 05:04:16'),
(9, NULL, 'Elena', 'Cruz', 'Tolentino', 31, 'Female', 'Married', 'Accountant', 'VOT-1007', 'Block 5, Abangan Norte', '1994-07-25', '09221234567', 0, 1, '2026-03-21 05:04:16'),
(10, NULL, 'Miguel', 'Roxas', 'Zobel', 21, 'Male', 'Single', 'Student', 'Not Registered', 'Block 6, Abangan Norte', '2005-01-10', '09231234568', 0, 1, '2026-03-21 05:04:16'),
(11, 21, 'Sofia', 'Luna', 'Andres', 18, 'Female', 'Single', 'Student', 'Not Registered', 'Block 6, Abangan Norte', '2008-05-20', '09241234569', 0, 1, '2026-03-21 05:04:16'),
(12, NULL, 'Ricardo', 'Dalisay', 'Probinsyano', 45, 'Male', 'Married', 'Police Officer', 'VOT-1008', 'Block 7, Abangan Norte', '1980-10-10', '09251234570', 0, 1, '2026-03-21 05:04:16'),
(13, 21, 'Carmen', 'Soriano', 'Reyes', 43, 'Female', 'Married', 'Business Owner', 'VOT-1009', 'Block 7, Abangan Norte', '1982-12-12', '09261234571', 0, 1, '2026-03-21 05:04:16'),
(14, 18, 'Ramon', 'Revilla', 'Bautista', 71, 'Male', 'Married', 'Retired', 'VOT-1010', 'Block 8, Abangan Norte', '1955-03-08', '09271234572', 0, 1, '2026-03-21 05:04:16'),
(15, 19, 'Gloria', 'Macapagal', 'Arroyo', 68, 'Female', 'Married', 'None', 'VOT-1011', 'Block 8, Abangan Norte', '1958-04-05', '09281234573', 0, 1, '2026-03-21 05:04:16'),
(16, 21, 'Ferdinand', 'Marcos', 'Romualdez', 27, 'Male', 'Single', 'IT Specialist', 'VOT-1012', 'Block 9, Abangan Norte', '1998-09-11', '09291234574', 0, 1, '2026-03-21 05:04:16'),
(17, NULL, 'Imelda', 'Romualdez', 'Marcos', 26, 'Female', 'Single', 'Designer', 'VOT-1013', 'Block 9, Abangan Norte', '1999-11-20', '09301234575', 0, 1, '2026-03-21 05:04:16'),
(18, 20, 'Benigno', 'Aquino', 'Cojuangco', 37, 'Male', 'Married', 'Manager', 'VOT-1014', 'Block 10, Abangan Norte', '1989-02-08', '09311234576', 0, 1, '2026-03-21 05:04:16'),
(19, 17, 'Corazon', 'Cojuangco', 'Aquino', 35, 'Female', 'Married', 'HR Staff', 'VOT-1015', 'Block 10, Abangan Norte', '1991-01-25', '09321234577', 0, 1, '2026-03-21 05:04:16'),
(20, 21, 'Rodrigo', 'Duterte', 'Roa', 56, 'Male', 'Widowed', 'Lawyer', 'VOT-1016', 'Block 11, Abangan Norte', '1970-03-28', '09331234578', 0, 1, '2026-03-21 05:04:16'),
(21, NULL, 'Leni', 'Robredo', 'Gerona', 51, 'Female', 'Widowed', 'Social Worker', 'VOT-1017', 'Block 11, Abangan Norte', '1975-04-23', '09341234579', 0, 1, '2026-03-21 05:04:16'),
(22, NULL, 'Manny', 'Pacquiao', 'Dapidran', 42, 'Male', 'Married', 'Athlete', 'VOT-1018', 'Block 12, Abangan Norte', '1983-12-17', '09351234580', 0, 1, '2026-03-21 05:04:16'),
(23, NULL, 'Jinkee', 'Jamora', 'Pacquiao', 41, 'Female', 'Married', 'None', 'VOT-1019', 'Block 12, Abangan Norte', '1985-01-12', '09361234581', 0, 1, '2026-03-21 05:04:16'),
(24, NULL, 'Coco', 'Martin', 'Nacianceno', 35, 'Male', 'Single', 'Actor', 'VOT-1020', 'Block 13, Abangan Norte', '1990-11-01', '09371234582', 0, 1, '2026-03-21 05:04:16'),
(25, NULL, 'Vice', 'Ganda', 'Viceral', 38, 'Other', 'Single', 'Comedian', 'VOT-1021', 'Block 13, Abangan Norte', '1988-03-31', '09381234583', 0, 1, '2026-03-21 05:04:16'),
(26, 20, 'Kathryn', 'Bernardods1', 'Brianaa32123', 24, 'Female', 'Single', 'Student', 'Not Registered', 'Block 14, Abangan Norte', '2002-03-26', '09391234584', 0, 10, '2026-03-21 05:04:16'),
(27, 19, '4234', '23423', '4234234', 1703, 'Male', 'Single', '23432', '1232', '4234234', '0323-02-12', '09312312312', 0, 1, '2026-03-28 02:31:16'),
(28, 20, 'Sample', 'Sample1', 'Sample', 21, 'Male', 'Single', 'Student', 'Not Registered', 'Sample', '2005-02-10', '09585583958', 0, 2, '2026-04-01 04:14:16'),
(29, 21, 'Consety11', 'Baho2', 'Cruz1', 20, 'Male', 'Single', 'Student', '123', '09 J. Francisco', '2005-12-10', '09785594819', 0, 5, '2026-04-01 04:55:47');

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
(16, 16, '11111232342', '', '2026-03-28 06:13:36', 5),
(17, 20, 'C64A07011123', '', '2026-04-01 04:46:22', 3),
(18, 21, '12345', 'Active', '2026-04-01 04:56:45', 2);

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
(1, 'System', 'Admin', 'admin', 'admin', '$2y$10$xWOJvB0rZ41G44wH2NTLouCLGRJaInuf6UW4uKD023DFrsqNSiTq6', 'Inactive', '2026-03-21 04:34:13'),
(2, 'earl', 'earl', 'admin', 'earl', '$2y$10$CNOdVmjP985uY0LzQrShTuYyGYy5R4hluoL8l8jGZQrPTvXBSS5mW', 'Inactive', '2026-03-21 04:48:43'),
(3, 'staff', 'staff', 'staff', 'staff', '$2y$10$c7VaVPwMJeOFry9bPn1hz.dn9zEHylP0GFJ9a2RglcwdSta2LZqtq', 'Active', '2026-04-03 12:11:53'),
(4, 'EARL', '43434aaa', 'admin', 'earl1', '$2y$10$MUptJTuzea/XWReiQBtrw.LHyedjV01xZ0eRAo5d1MyLEC0//4P4u', 'Active', '2026-04-03 12:21:11'),
(5, '123123', '123123', 'admin', 'earl123', '$2y$10$j9CsO9RjKWpMY4tS35hFheVVt585wik6nF8fhpL4/f2jf8OpnSnj.', 'Active', '2026-04-03 12:57:42'),
(6, 'EARL', '43434aaa', 'admin', '1234', '$2y$10$FO3zD6gKkkhA0/ZA2sABy.jOruooLm.t8hbK8x7I3vGdztB2kzCUW', 'Active', '2026-04-03 13:05:47'),
(7, 'EARL', '43434aaa', 'admin', '12345', '$2y$10$pb1riBWNK1a1OeCqK6JogOJXm3tMgYXh4Fr0XyCcmCGWqElwI3JBC', 'Active', '2026-04-06 10:19:42'),
(8, '1232', '123123', 'staff', '111', '$2y$10$XJvmgvXzGylUyWqpVfVpE.CD5Cmatw5ZGfW.mIFJEbSnPDEd15U6e', 'Active', '2026-04-06 11:19:32'),
(9, 'EARL', '43434', 'admin', 'asd', '$2y$10$Ee.SS4eG6QfMeafvBHWYSOILl.o80eWSb1PGjp30Q8C8BuGh5eF1K', 'Active', '2026-04-06 11:36:20');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `distribution_logs`
--
ALTER TABLE `distribution_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `registered_household`
--
ALTER TABLE `registered_household`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `registered_resi`
--
ALTER TABLE `registered_resi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `rfid_tags`
--
ALTER TABLE `rfid_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
