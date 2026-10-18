-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 18, 2026 at 10:11 AM
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
-- Database: `medibook_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(10) UNSIGNED NOT NULL,
  `patient_id` int(10) UNSIGNED NOT NULL,
  `doctor_id` int(10) UNSIGNED NOT NULL,
  `appointment_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `appointment_date`, `start_time`, `end_time`, `reason`, `status`, `created_at`, `updated_at`) VALUES
(1, 6, 1, '2026-10-20', '09:00:00', '09:30:00', 'Routine heart health consultation', 'confirmed', '2026-10-18 07:20:52', '2026-10-18 07:20:52'),
(2, 7, 3, '2026-10-20', '14:00:00', '14:30:00', 'Joint pain consultation', 'pending', '2026-10-18 07:20:52', '2026-10-18 07:20:52'),
(3, 8, 2, '2026-10-21', '10:00:00', '10:30:00', 'Neurological consultation', 'confirmed', '2026-10-18 07:20:52', '2026-10-18 07:20:52'),
(4, 6, 4, '2026-10-21', '14:00:00', '14:30:00', 'Follow-up consultation', 'pending', '2026-10-18 07:20:52', '2026-10-18 07:20:52');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','resolved') NOT NULL DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `is_active`, `created_at`) VALUES
(1, 'Cardiology', 'Diagnosis and treatment of heart conditions', 1, '2026-10-18 07:20:52'),
(2, 'Neurology', 'Diagnosis and treatment of nervous system conditions', 1, '2026-10-18 07:20:52'),
(3, 'Orthopedics', 'Treatment of bones, joints and musculoskeletal conditions', 1, '2026-10-18 07:20:52');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `department_id` int(10) UNSIGNED NOT NULL,
  `specialization` varchar(150) NOT NULL,
  `qualification` varchar(200) NOT NULL,
  `experience_years` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `consultation_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `bio` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `user_id`, `department_id`, `specialization`, `qualification`, `experience_years`, `consultation_fee`, `bio`, `is_active`, `created_at`) VALUES
(1, 2, 1, 'Cardiologist', 'MBBS, MD Cardiology', 10, 800.00, 'Specializes in heart health and cardiovascular care.', 1, '2026-10-18 07:20:52'),
(2, 3, 2, 'Neurologist', 'MBBS, MD Neurology', 8, 700.00, 'Specializes in neurological assessment and care.', 1, '2026-10-18 07:20:52'),
(3, 4, 3, 'Orthopedic Surgeon', 'MBBS, MS Orthopedics', 12, 900.00, 'Specializes in bone and joint conditions.', 1, '2026-10-18 07:20:52'),
(4, 5, 1, 'Cardiologist', 'MBBS, DM Cardiology', 6, 600.00, 'Provides cardiovascular consultations and follow-up care.', 1, '2026-10-18 07:20:52');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `doctor_id` int(10) UNSIGNED NOT NULL,
  `day_of_week` tinyint(3) UNSIGNED NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `slot_duration_minutes` smallint(5) UNSIGNED NOT NULL DEFAULT 30,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `doctor_id`, `day_of_week`, `start_time`, `end_time`, `slot_duration_minutes`, `is_available`, `created_at`) VALUES
(1, 1, 2, '09:00:00', '12:00:00', 30, 1, '2026-10-18 07:20:52'),
(2, 1, 4, '09:00:00', '12:00:00', 30, 1, '2026-10-18 07:20:52'),
(3, 2, 3, '10:00:00', '13:00:00', 30, 1, '2026-10-18 07:20:52'),
(4, 2, 5, '10:00:00', '13:00:00', 30, 1, '2026-10-18 07:20:52'),
(5, 3, 2, '14:00:00', '17:00:00', 30, 1, '2026-10-18 07:20:52'),
(6, 3, 4, '14:00:00', '17:00:00', 30, 1, '2026-10-18 07:20:52'),
(7, 4, 3, '14:00:00', '17:00:00', 30, 1, '2026-10-18 07:20:52'),
(8, 4, 6, '09:00:00', '12:00:00', 30, 1, '2026-10-18 07:20:52');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('patient','doctor','admin') NOT NULL DEFAULT 'patient',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `phone`, `role`, `is_active`, `created_at`) VALUES
(1, 'MediBook Admin', 'admin@medibook.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9000000001', 'admin', 1, '2026-10-18 07:20:52'),
(2, 'Dr. Aarav Sharma', 'aarav@medibook.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9000000002', 'doctor', 1, '2026-10-18 07:20:52'),
(3, 'Dr. Priya Patel', 'priya@medibook.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9000000003', 'doctor', 1, '2026-10-18 07:20:52'),
(4, 'Dr. Rohan Verma', 'rohan@medibook.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9000000004', 'doctor', 1, '2026-10-18 07:20:52'),
(5, 'Dr. Neha Deshmukh', 'neha@medibook.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9000000005', 'doctor', 1, '2026-10-18 07:20:52'),
(6, 'Rahul Joshi', 'rahul@example.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9000000006', 'patient', 1, '2026-10-18 07:20:52'),
(7, 'Sneha Kulkarni', 'sneha@example.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9000000007', 'patient', 1, '2026-10-18 07:20:52'),
(8, 'Arjun Mehta', 'arjun@example.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9000000008', 'patient', 1, '2026-10-18 07:20:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_doctor_appointment_slot` (`doctor_id`,`appointment_date`,`start_time`),
  ADD KEY `idx_patient_appointments` (`patient_id`,`appointment_date`),
  ADD KEY `idx_doctor_appointments` (`doctor_id`,`appointment_date`,`status`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `fk_doctors_department` (`department_id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_doctor_schedule` (`doctor_id`,`day_of_week`,`start_time`,`end_time`);

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
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_appointments_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_appointments_patient` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `fk_doctors_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_doctors_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `fk_schedules_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
