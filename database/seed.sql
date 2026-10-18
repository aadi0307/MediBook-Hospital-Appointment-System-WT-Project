-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 18, 2026 at 10:25 AM
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

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `appointment_date`, `start_time`, `end_time`, `reason`, `status`, `created_at`, `updated_at`) VALUES
(1, 6, 1, '2026-10-20', '09:00:00', '09:30:00', 'Routine heart health consultation', 'confirmed', '2026-10-18 07:20:52', '2026-10-18 07:20:52'),
(2, 7, 3, '2026-10-20', '14:00:00', '14:30:00', 'Joint pain consultation', 'pending', '2026-10-18 07:20:52', '2026-10-18 07:20:52'),
(3, 8, 2, '2026-10-21', '10:00:00', '10:30:00', 'Neurological consultation', 'confirmed', '2026-10-18 07:20:52', '2026-10-18 07:20:52'),
(4, 6, 4, '2026-10-21', '14:00:00', '14:30:00', 'Follow-up consultation', 'pending', '2026-10-18 07:20:52', '2026-10-18 07:20:52');

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `is_active`, `created_at`) VALUES
(1, 'Cardiology', 'Diagnosis and treatment of heart conditions', 1, '2026-10-18 07:20:52'),
(2, 'Neurology', 'Diagnosis and treatment of nervous system conditions', 1, '2026-10-18 07:20:52'),
(3, 'Orthopedics', 'Treatment of bones, joints and musculoskeletal conditions', 1, '2026-10-18 07:20:52');

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `user_id`, `department_id`, `specialization`, `qualification`, `experience_years`, `consultation_fee`, `bio`, `is_active`, `created_at`) VALUES
(1, 2, 1, 'Cardiologist', 'MBBS, MD Cardiology', 10, 800.00, 'Specializes in heart health and cardiovascular care.', 1, '2026-10-18 07:20:52'),
(2, 3, 2, 'Neurologist', 'MBBS, MD Neurology', 8, 700.00, 'Specializes in neurological assessment and care.', 1, '2026-10-18 07:20:52'),
(3, 4, 3, 'Orthopedic Surgeon', 'MBBS, MS Orthopedics', 12, 900.00, 'Specializes in bone and joint conditions.', 1, '2026-10-18 07:20:52'),
(4, 5, 1, 'Cardiologist', 'MBBS, DM Cardiology', 6, 600.00, 'Provides cardiovascular consultations and follow-up care.', 1, '2026-10-18 07:20:52');

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
