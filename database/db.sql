-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 11, 2026 at 07:37 AM
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
-- Database: `record_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `log_id` int(11) NOT NULL,
  `admin_username` varchar(100) NOT NULL DEFAULT 'system',
  `admin_fullname` varchar(255) NOT NULL DEFAULT 'System',
  `action` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(45) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`log_id`, `admin_username`, `admin_fullname`, `action`, `module`, `description`, `ip_address`, `created_at`) VALUES
(1, 'admin', 'System Administrator', 'UPDATE_SYMPTOM', 'Symptoms', 'Symptom \'Back Pain\' disabled', '::1', '2026-03-26 05:45:54'),
(2, 'admin', 'System Administrator', 'UPDATE_SYMPTOM', 'Symptoms', 'Symptom \'Back Pain\' enabled', '::1', '2026-03-26 05:45:56'),
(3, 'admin', 'System Administrator', 'LOGOUT', 'Auth', 'Admin \'admin\' logged out', '::1', '2026-03-26 05:46:24'),
(4, 'admin', 'System Administrator', 'LOGIN', 'Auth', 'Admin \'admin\' logged in successfully', '::1', '2026-03-26 05:47:30'),
(5, 'admin', 'System Administrator', 'LOGOUT', 'Auth', 'Admin \'admin\' logged out', '::1', '2026-03-26 05:51:18'),
(6, 'admin', 'System Administrator', 'LOGIN', 'Auth', 'Admin \'admin\' logged in successfully', '::1', '2026-03-26 05:52:35'),
(7, 'admin', 'System Administrator', 'LOGIN', 'Auth', 'Admin \'admin\' logged in successfully', '::1', '2026-03-27 00:54:15'),
(8, 'admin', 'System Administrator', 'UPDATE_STOCK', 'Medicine', 'Stock update on medicine ID 5: add 12 unit(s)', '::1', '2026-03-27 00:56:24'),
(9, 'admin', 'System Administrator', 'UPDATE_SYMPTOM', 'Symptoms', 'Symptom \'Anxiety / Stress\' disabled', '::1', '2026-03-27 01:10:09'),
(10, 'admin', 'System Administrator', 'UPDATE_SYMPTOM', 'Symptoms', 'Symptom \'Anxiety / Stress\' enabled', '::1', '2026-03-27 01:10:10'),
(11, 'admin', 'System Administrator', 'UPDATE_STOCK', 'Medicine', 'Stock update on medicine ID 13: subtract 45 unit(s)', '::1', '2026-03-27 01:12:10'),
(12, 'admin', 'System Administrator', 'UPDATE_STOCK', 'Medicine', 'Stock update on medicine ID 13: subtract 90 unit(s)', '::1', '2026-03-27 01:12:36'),
(13, 'admin', 'System Administrator', 'UPDATE_STOCK', 'Medicine', 'Stock update on medicine ID 13: add 180 unit(s)', '::1', '2026-03-27 01:12:54'),
(14, 'admin', 'admin', 'LOGIN_FAILED', 'Auth', 'Failed login attempt for username \'admin\'', '::1', '2026-04-30 00:35:31'),
(15, 'admin', 'System Administrator', 'LOGIN', 'Auth', 'Admin \'admin\' logged in successfully', '::1', '2026-04-30 00:35:41'),
(16, 'admin', 'System Administrator', 'UPDATE_MEDICINE', 'Medicine', 'Updated medicine ID 13 — name: \'Amoxicillin\'', '::1', '2026-04-30 00:36:23'),
(17, 'admin', 'System Administrator', 'UPDATE_MEDICINE', 'Medicine', 'Updated medicine ID 13 — name: \'Amoxicillin\'', '::1', '2026-04-30 00:36:41'),
(18, 'admin', 'System Administrator', 'UPDATE_MEDICINE', 'Medicine', 'Updated medicine ID 13 — name: \'Amoxicillin\'', '::1', '2026-04-30 00:37:03'),
(19, 'admin', 'System Administrator', 'UPDATE_STOCK', 'Medicine', 'Stock update on medicine ID 13: add 90 unit(s)', '::1', '2026-04-30 00:37:16'),
(20, 'admin', 'System Administrator', 'DELETE_SYMPTOM', 'Symptoms', 'Deleted symptom ID 17 (\'Diarrhea\')', '::1', '2026-04-30 00:44:34'),
(21, 'admin', 'System Administrator', 'ADD_RECORD', 'Patient Records', 'Added patient record for \'Franlie Balot Villasan23\' (medicine: Amoxicillin, qty: 2)', '::1', '2026-04-30 00:46:20'),
(22, 'admin', 'System Administrator', 'UPDATE_MEDICINE', 'Medicine', 'Updated medicine ID 3 — name: \'Bioflu\'', '::1', '2026-04-30 00:46:45'),
(23, 'admin', 'System Administrator', 'UPDATE_MEDICINE', 'Medicine', 'Updated medicine ID 3 — name: \'Bioflu\'', '::1', '2026-04-30 00:46:56'),
(24, 'admin', 'System Administrator', 'UPDATE_MEDICINE', 'Medicine', 'Updated medicine ID 13 — name: \'Amoxicillin\'', '::1', '2026-04-30 00:47:03'),
(25, 'admin', 'System Administrator', 'UPDATE_RECORD', 'Patient Records', 'Updated patient record ID 14 for \'Franlie Balot Villasan23\' (medicine: Loperamide, qty: 2)', '::1', '2026-04-30 00:48:53'),
(26, 'admin', 'System Administrator', 'DELETE_RECORD', 'Patient Records', 'Deleted patient record ID 14 (medicine ID: 15, qty restored: 2)', '::1', '2026-04-30 00:48:57'),
(27, 'admin', 'System Administrator', 'UPDATE_STOCK', 'Medicine', 'Stock update on medicine ID 13: subtract 5 unit(s)', '::1', '2026-04-30 00:50:54'),
(28, 'admin', 'System Administrator', 'UPDATE_RECORD', 'Patient Records', 'Updated patient record ID 12 for \'Franlie Balot Villasan\' (medicine: Biogesic, qty: 6)', '::1', '2026-04-30 00:52:30'),
(29, 'admin', 'System Administrator', 'UPDATE_STOCK', 'Medicine', 'Stock update on medicine ID 13: add 5 unit(s)', '::1', '2026-04-30 00:53:37'),
(30, 'admin', 'System Administrator', 'ADD_RECORD', 'Patient Records', 'Added patient record for \'Franlie Balot Villasan2323\' (medicine: Loperamide, qty: 2)', '::1', '2026-04-30 00:54:47');

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `middlename` varchar(255) NOT NULL,
  `name_extension` varchar(255) NOT NULL,
  `contact_no` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `dateCreated` timestamp NOT NULL DEFAULT current_timestamp(),
  `dateDeleted` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faculty_details`
--

CREATE TABLE `faculty_details` (
  `faculty_id` int(255) NOT NULL,
  `patient_id` int(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `dateCreated` timestamp NOT NULL DEFAULT current_timestamp(),
  `dateDeleted` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medicine`
--

CREATE TABLE `medicine` (
  `medicine_id` int(255) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `milligrams` varchar(50) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `manufactured_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `current_stock` int(255) NOT NULL,
  `dateCreated` timestamp NOT NULL DEFAULT current_timestamp(),
  `dateDeleted` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicine`
--

INSERT INTO `medicine` (`medicine_id`, `medicine_name`, `milligrams`, `description`, `manufactured_date`, `expiry_date`, `current_stock`, `dateCreated`, `dateDeleted`) VALUES
(1, 'Biogesic', NULL, '', NULL, NULL, 0, '2026-02-12 08:00:46', '2026-02-12 08:00:46'),
(2, 'Biogesic', NULL, '', NULL, NULL, 0, '2026-02-12 08:23:06', '2026-02-12 08:23:06'),
(3, 'Bioflu', '500', '', NULL, NULL, 20, '2026-02-12 08:31:18', NULL),
(4, 'Biogesic', NULL, 'For Head Ache', NULL, NULL, 3, '2026-02-16 01:07:30', '2026-03-09 02:37:21'),
(5, 'Neozep', NULL, 'Cold', NULL, NULL, 16, '2026-02-16 01:26:50', NULL),
(11, 'Biogesic', NULL, 'Pain reliever', NULL, NULL, 34, '2026-03-09 02:31:28', NULL),
(12, 'Paracetamol', NULL, 'Fever reducer', NULL, NULL, 80, '2026-03-09 02:31:28', NULL),
(13, 'Amoxicillin', 'five hundred ', 'Antibiotic', '2026-04-30', '2026-04-01', 465, '2026-03-09 02:31:28', NULL),
(15, 'Loperamide', NULL, '', NULL, NULL, 18, '2026-03-09 02:37:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `medicine_stock`
--

CREATE TABLE `medicine_stock` (
  `stock_log` int(255) NOT NULL,
  `admin_id` int(255) NOT NULL,
  `medicine_id` int(255) NOT NULL,
  `stock_date` datetime(6) NOT NULL,
  `quantity_added` int(255) NOT NULL,
  `expiration_date` date NOT NULL,
  `dateCreated` timestamp NOT NULL DEFAULT current_timestamp(),
  `dateDeleted` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patient`
--

CREATE TABLE `patient` (
  `patient_id` int(255) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `patient_type` varchar(100) NOT NULL,
  `contact_no` varchar(255) NOT NULL,
  `dateCreated` timestamp NOT NULL DEFAULT current_timestamp(),
  `dateDeleted` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient`
--

INSERT INTO `patient` (`patient_id`, `fullname`, `patient_type`, `contact_no`, `dateCreated`, `dateDeleted`) VALUES
(1, 'Franlie Balot Villasan', 'student', '09615496134', '2026-02-12 08:00:46', '2026-02-12 08:00:46'),
(2, 'Test Patient', 'student', '09123456789', '2026-02-12 08:20:15', '2026-02-12 08:20:15'),
(3, 'John Larielle', 'student', '09615496134', '2026-02-12 08:23:06', '2026-02-12 08:23:06'),
(4, 'John Larielle Lunod', 'student', '09615496134', '2026-02-12 08:31:18', NULL),
(6, 'Ace Jerome Alcantara', 'student', '09612345678', '2026-02-16 02:02:19', NULL),
(7, 'Franlie Balot Villasan', 'student', '096123456789', '2026-02-16 03:47:09', NULL),
(8, 'Franlie Balot Villasan', 'Staff', '09615496134', '2026-02-16 03:47:25', NULL),
(9, 'Franlie Balot Villasan23', 'Student', '09615496134', '2026-04-30 00:46:20', NULL),
(10, 'Franlie Balot Villasan2323', 'Visitor', '09615496134', '2026-04-30 00:54:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `patient_record`
--

CREATE TABLE `patient_record` (
  `record_id` int(255) NOT NULL,
  `patient_id` int(255) NOT NULL,
  `medicine_id` int(255) NOT NULL,
  `date_given` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `quantity` int(255) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `created_by` varchar(255) DEFAULT NULL,
  `dateCreated` timestamp NOT NULL DEFAULT current_timestamp(),
  `dateDeleted` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_record`
--

INSERT INTO `patient_record` (`record_id`, `patient_id`, `medicine_id`, `date_given`, `quantity`, `reason`, `created_by`, `dateCreated`, `dateDeleted`) VALUES
(1, 1, 1, '2026-02-11 16:00:00', 1, 'Head Ache', NULL, '2026-02-12 08:00:46', '2026-02-12 08:00:46'),
(2, 2, 1, '2026-02-12 08:20:15', 1, 'Test Headache', NULL, '2026-02-12 08:20:15', '2026-02-12 08:20:15'),
(3, 2, 1, '2026-02-12 08:21:43', 1, 'Test Headache', NULL, '2026-02-12 08:21:43', '2026-02-12 08:21:43'),
(4, 2, 1, '2026-02-12 08:21:46', 1, 'Test Headache', NULL, '2026-02-12 08:21:46', '2026-02-12 08:21:46'),
(5, 3, 2, '2026-02-11 16:00:00', 2, 'Head Ache', NULL, '2026-02-12 08:23:06', '2026-02-12 08:23:06'),
(6, 2, 1, '2026-02-12 08:25:54', 1, 'Test Headache', NULL, '2026-02-12 08:25:54', '2026-02-12 08:25:54'),
(7, 2, 1, '2026-02-12 08:27:31', 1, 'Test Headache', NULL, '2026-02-12 08:27:31', '2026-02-12 08:27:31'),
(8, 2, 1, '2026-02-12 08:31:54', 1, 'Test Headache', NULL, '2026-02-12 08:30:22', '2026-02-12 08:31:54'),
(9, 4, 3, '2026-03-09 02:32:01', 1, 'Head Ache', NULL, '2026-02-12 08:31:18', '2026-03-09 02:32:01'),
(10, 6, 3, '2026-02-15 16:00:00', 2, 'Head Ache', NULL, '2026-02-16 02:02:19', NULL),
(11, 7, 4, '2026-02-15 16:00:00', 1, 'awAW', NULL, '2026-02-16 03:47:09', NULL),
(12, 8, 11, '2026-02-15 16:00:00', 6, 'Awaw', NULL, '2026-02-16 03:47:25', NULL),
(13, 8, 4, '2026-03-09 02:31:51', 1, '123', NULL, '2026-02-16 03:51:58', '2026-03-09 02:31:51'),
(14, 9, 15, '2026-04-30 00:48:57', 2, 'Constipation', NULL, '2026-04-30 00:46:20', '2026-04-30 00:48:57'),
(15, 10, 15, '2026-04-29 16:00:00', 2, 'Constipation', NULL, '2026-04-30 00:54:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_details`
--

CREATE TABLE `student_details` (
  `student_id` int(255) NOT NULL,
  `patient_id` int(255) NOT NULL,
  `grade_lvl` enum('11','12') NOT NULL,
  `strand` enum('TVL','GAS','STEM','HUMSS','ABM','ALS') NOT NULL,
  `section` varchar(255) NOT NULL,
  `dateCreated` timestamp NOT NULL DEFAULT current_timestamp(),
  `dateDeleted` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `symptoms`
--

CREATE TABLE `symptoms` (
  `symptom_id` int(11) NOT NULL,
  `symptom_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'General',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `dateCreated` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `symptoms`
--

INSERT INTO `symptoms` (`symptom_id`, `symptom_name`, `category`, `is_active`, `dateCreated`) VALUES
(1, 'Headache', 'Pain', 1, '2026-03-26 05:44:50'),
(2, 'Toothache', 'Pain', 1, '2026-03-26 05:44:50'),
(3, 'Stomach Pain', 'Pain', 1, '2026-03-26 05:44:50'),
(4, 'Chest Pain', 'Pain', 1, '2026-03-26 05:44:50'),
(5, 'Back Pain', 'Pain', 1, '2026-03-26 05:44:50'),
(6, 'Muscle Pain', 'Pain', 1, '2026-03-26 05:44:50'),
(7, 'Earache', 'Pain', 1, '2026-03-26 05:44:50'),
(8, 'Cough', 'Respiratory', 1, '2026-03-26 05:44:50'),
(9, 'Colds / Runny Nose', 'Respiratory', 1, '2026-03-26 05:44:50'),
(10, 'Sore Throat', 'Respiratory', 1, '2026-03-26 05:44:50'),
(11, 'Difficulty Breathing', 'Respiratory', 1, '2026-03-26 05:44:50'),
(12, 'Sneezing', 'Respiratory', 1, '2026-03-26 05:44:50'),
(13, 'Fever', 'Fever & Temperature', 1, '2026-03-26 05:44:50'),
(14, 'Chills', 'Fever & Temperature', 1, '2026-03-26 05:44:50'),
(15, 'Night Sweats', 'Fever & Temperature', 1, '2026-03-26 05:44:50'),
(16, 'Vomiting', 'Digestive', 1, '2026-03-26 05:44:50'),
(18, 'Nausea', 'Digestive', 1, '2026-03-26 05:44:50'),
(19, 'Loss of Appetite', 'Digestive', 1, '2026-03-26 05:44:50'),
(20, 'Constipation', 'Digestive', 1, '2026-03-26 05:44:50'),
(21, 'Dizziness', 'Neurological', 1, '2026-03-26 05:44:50'),
(22, 'Fainting', 'Neurological', 1, '2026-03-26 05:44:50'),
(23, 'Blurred Vision', 'Neurological', 1, '2026-03-26 05:44:50'),
(24, 'Rash / Skin Irritation', 'Skin', 1, '2026-03-26 05:44:50'),
(25, 'Wound / Laceration', 'Skin', 1, '2026-03-26 05:44:50'),
(26, 'Allergic Reaction', 'Skin', 1, '2026-03-26 05:44:50'),
(27, 'Insect Bite', 'Skin', 1, '2026-03-26 05:44:50'),
(28, 'Menstrual Pain', 'Menstrual', 1, '2026-03-26 05:44:50'),
(29, 'Irregular Period', 'Menstrual', 1, '2026-03-26 05:44:50'),
(30, 'High Blood Pressure', 'Other', 1, '2026-03-26 05:44:50'),
(31, 'Low Blood Pressure', 'Other', 1, '2026-03-26 05:44:50'),
(32, 'Anxiety / Stress', 'Other', 1, '2026-03-26 05:44:50'),
(33, 'Fatigue / Weakness', 'Other', 1, '2026-03-26 05:44:50'),
(34, 'Eye Irritation', 'Other', 1, '2026-03-26 05:44:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_module` (`module`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_username` (`admin_username`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `faculty_details`
--
ALTER TABLE `faculty_details`
  ADD PRIMARY KEY (`faculty_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `medicine`
--
ALTER TABLE `medicine`
  ADD PRIMARY KEY (`medicine_id`);

--
-- Indexes for table `medicine_stock`
--
ALTER TABLE `medicine_stock`
  ADD PRIMARY KEY (`stock_log`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `medicine_id` (`medicine_id`);

--
-- Indexes for table `patient`
--
ALTER TABLE `patient`
  ADD PRIMARY KEY (`patient_id`);

--
-- Indexes for table `patient_record`
--
ALTER TABLE `patient_record`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `medicine_id` (`medicine_id`);

--
-- Indexes for table `student_details`
--
ALTER TABLE `student_details`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `symptoms`
--
ALTER TABLE `symptoms`
  ADD PRIMARY KEY (`symptom_id`),
  ADD UNIQUE KEY `unique_symptom` (`symptom_name`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faculty_details`
--
ALTER TABLE `faculty_details`
  MODIFY `faculty_id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medicine`
--
ALTER TABLE `medicine`
  MODIFY `medicine_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `medicine_stock`
--
ALTER TABLE `medicine_stock`
  MODIFY `stock_log` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patient`
--
ALTER TABLE `patient`
  MODIFY `patient_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `patient_record`
--
ALTER TABLE `patient_record`
  MODIFY `record_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `student_details`
--
ALTER TABLE `student_details`
  MODIFY `student_id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `symptoms`
--
ALTER TABLE `symptoms`
  MODIFY `symptom_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `faculty_details`
--
ALTER TABLE `faculty_details`
  ADD CONSTRAINT `faculty_details_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`patient_id`);

--
-- Constraints for table `medicine_stock`
--
ALTER TABLE `medicine_stock`
  ADD CONSTRAINT `medicine_stock_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`),
  ADD CONSTRAINT `medicine_stock_ibfk_2` FOREIGN KEY (`medicine_id`) REFERENCES `medicine` (`medicine_id`);

--
-- Constraints for table `patient_record`
--
ALTER TABLE `patient_record`
  ADD CONSTRAINT `patient_record_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`patient_id`),
  ADD CONSTRAINT `patient_record_ibfk_2` FOREIGN KEY (`medicine_id`) REFERENCES `medicine` (`medicine_id`);

--
-- Constraints for table `student_details`
--
ALTER TABLE `student_details`
  ADD CONSTRAINT `student_details_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`patient_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
