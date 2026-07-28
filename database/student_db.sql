-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 28, 2026 at 08:26 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `student_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('Present','Absent') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `student_id`, `attendance_date`, `status`) VALUES
(2, 3, '2026-07-21', 'Present'),
(3, 2, '2026-07-21', 'Present'),
(4, 4, '2026-07-21', 'Present');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

CREATE TABLE `attendance_records` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `status` enum('Present','Absent') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_records`
--

INSERT INTO `attendance_records` (`id`, `session_id`, `student_id`, `status`, `created_at`) VALUES
(1, 1, 11, 'Absent', '2026-07-27 17:55:12'),
(2, 1, 4, 'Absent', '2026-07-27 17:55:12'),
(7, 1, 3, 'Present', '2026-07-27 17:56:31'),
(8, 1, 2, 'Present', '2026-07-27 17:56:31');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_sessions`
--

CREATE TABLE `attendance_sessions` (
  `id` int(11) NOT NULL,
  `timetable_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `department` varchar(30) NOT NULL,
  `section` varchar(10) NOT NULL,
  `year` varchar(20) NOT NULL,
  `semester` int(11) NOT NULL,
  `subject_code` varchar(50) NOT NULL,
  `subject_name` varchar(150) DEFAULT NULL,
  `start_period` int(11) NOT NULL,
  `end_period` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_sessions`
--

INSERT INTO `attendance_sessions` (`id`, `timetable_id`, `attendance_date`, `department`, `section`, `year`, `semester`, `subject_code`, `subject_name`, `start_period`, `end_period`, `created_at`) VALUES
(1, 119, '2026-07-27', 'CSBS', 'A', '3rd Year', 5, 'DA', 'Data Analytics', 1, 2, '2026-07-27 17:55:12');

-- --------------------------------------------------------

--
-- Table structure for table `marks`
--

CREATE TABLE `marks` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `internal_marks` int(11) NOT NULL,
  `external_marks` int(11) NOT NULL,
  `total_marks` int(11) NOT NULL,
  `grade` varchar(5) DEFAULT NULL,
  `result` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marks`
--

INSERT INTO `marks` (`id`, `student_id`, `subject`, `internal_marks`, `external_marks`, `total_marks`, `grade`, `result`) VALUES
(1, 2, 'dbms', 30, 69, 99, 'O', 'Pass'),
(2, 3, 'dbms', 29, 69, 98, 'O', 'Pass'),
(3, 3, 'CN LAB', 29, 68, 97, 'O', 'Pass'),
(4, 2, 'CN LAB', 29, 69, 98, 'O', 'Pass'),
(5, 12, 'CN LAB', 27, 68, 95, 'O', 'Pass'),
(6, 4, 'CN LAB', 28, 67, 95, 'O', 'Pass');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `roll_no` varchar(50) NOT NULL,
  `registration_no` varchar(50) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `department` varchar(100) NOT NULL,
  `section` varchar(10) DEFAULT NULL,
  `year` varchar(20) DEFAULT NULL,
  `semester` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `address` text DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `verification_token` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `name`, `roll_no`, `registration_no`, `dob`, `gender`, `department`, `section`, `year`, `semester`, `email`, `phone`, `address`, `username`, `password`, `verification_token`, `email_verified`) VALUES
(2, 'chandhini', '10', '24B91A5710', '2006-12-15', 'Female', 'CSBS', 'A', '3rd Year', '5', 'chandhinichikkala@gmail.com', '6302613800', 'dghtrhgiythfuytyg', NULL, NULL, NULL, 0),
(3, 'Praveena Adabala', '1', '24B91A5701', '2006-07-05', 'Female', 'CSBS', 'A', '3rd Year', '5', 'praveenaadabala@gmail.com', '9866609400', 'dfgghhfjgdvbn', '24B91A5701', '$2y$10$gJQy6wpiWm/0QdJ4v/A4kee6qsRb552JGWm9MCGCKJpGTUENquHme', NULL, 1),
(4, 'rubeena', '57', '24B91A5757', '2006-07-02', 'Female', 'CSBS', 'A', '3rd Year', '5', 'rubeena@gmail.com', '9302613889', 'rftyuikjhgdsdxcvbhj', NULL, NULL, NULL, 0),
(5, 'ramya', '14', '24B91A5501', '2005-06-06', 'Female', 'CSE', NULL, '3rd Year', '5', 'ramya@gmail.com', '8885412323', 'pathapatnam , srikakulam', '24B91A5501', '$2y$10$njzlxmK.rKKc10tomVAE.uJo1gsdGXvFuee5wK8Wk1Vd3mZvaszFG', NULL, 0),
(12, 'ram', '25', '24B91A5725', '2006-06-05', 'Male', 'CSBS', 'A', '3rd Year', '5', 'llllresug@gmail.com', '9356775465', 'dfghbjkl;cfghjkl', '24B91A5725', '$2y$10$Sy.XIq/ZG0LyDcjNxQZ39.VFej/.ZvKk2up4oEDi2Boz6DyFVzd1W', '96ebf7dc32adfcd7e78547d36a9716679153e32eff4d0a12777557a8cd13342e', 0);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `subject_code` varchar(30) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `department` varchar(50) NOT NULL,
  `year` varchar(20) NOT NULL,
  `semester` int(11) NOT NULL,
  `credits` int(11) NOT NULL DEFAULT 3,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject_code`, `subject_name`, `department`, `year`, `semester`, `credits`, `created_at`) VALUES
(1, 'CSBS501', 'Web Technologies', 'CSBS', '3rd Year', 5, 4, '2026-07-21 17:13:29'),
(2, 'CSBS502', 'Business Intelligence', 'CSBS', '3rd Year', 5, 4, '2026-07-21 17:13:29'),
(3, 'CSBS503', 'Computer Networks', 'CSBS', '3rd Year', 5, 4, '2026-07-21 17:13:29'),
(4, 'CSBS504', 'Machine Learning', 'CSBS', '3rd Year', 5, 4, '2026-07-21 17:13:29'),
(5, 'CSBS505', 'Software Engineering', 'CSBS', '3rd Year', 5, 3, '2026-07-21 17:13:29'),
(6, '323CV3102', 'computer networking', 'CSBS', '3rd Year', 5, 3, '2026-07-21 17:17:02');

-- --------------------------------------------------------

--
-- Table structure for table `timetable`
--

CREATE TABLE `timetable` (
  `id` int(11) NOT NULL,
  `department` varchar(30) NOT NULL,
  `section` varchar(10) NOT NULL,
  `year` varchar(20) NOT NULL,
  `semester` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `subject_code` varchar(50) NOT NULL,
  `subject_name` varchar(150) DEFAULT NULL,
  `start_period` int(11) NOT NULL,
  `end_period` int(11) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `session_type` enum('Theory','Lab','Activity') DEFAULT 'Theory'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `timetable`
--

INSERT INTO `timetable` (`id`, `department`, `section`, `year`, `semester`, `day_of_week`, `subject_code`, `subject_name`, `start_period`, `end_period`, `start_time`, `end_time`, `session_type`) VALUES
(4, 'AI&DS', 'A', '3rd Year', 5, 'Monday', 'CN', 'Computer Networks', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(5, 'AI&DS', 'A', '3rd Year', 5, 'Monday', 'OS', 'Operating Systems', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(6, 'AI&DS', 'A', '3rd Year', 5, 'Monday', 'FLUTTER', 'Tinkering Lab - Flutter', 5, 6, '13:30:00', '15:00:00', 'Lab'),
(7, 'AI&DS', 'A', '3rd Year', 5, 'Monday', 'COUNSELING', 'Counseling', 7, 7, '15:00:00', '15:45:00', 'Activity'),
(8, 'AI&DS', 'A', '3rd Year', 5, 'Monday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(9, 'AI&DS', 'A', '3rd Year', 5, 'Tuesday', 'FDM', 'Foundations of Data Mining', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(10, 'AI&DS', 'A', '3rd Year', 5, 'Tuesday', 'CN', 'Computer Networks', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(11, 'AI&DS', 'A', '3rd Year', 5, 'Tuesday', 'DV LAB', 'Data Visualization Lab', 5, 8, '13:30:00', '16:30:00', 'Lab'),
(12, 'AI&DS', 'A', '3rd Year', 5, 'Tuesday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(13, 'AI&DS', 'A', '3rd Year', 5, 'Wednesday', 'OE-I', 'Open Elective I', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(14, 'AI&DS', 'A', '3rd Year', 5, 'Wednesday', 'HON/MIN', 'Honors / Minors', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(15, 'AI&DS', 'A', '3rd Year', 5, 'Wednesday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(16, 'AI&DS', 'A', '3rd Year', 5, 'Thursday', 'PML', 'Predictive Machine Learning', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(17, 'AI&DS', 'A', '3rd Year', 5, 'Thursday', 'OS', 'Operating Systems', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(18, 'AI&DS', 'A', '3rd Year', 5, 'Thursday', 'DM ML LAB', 'Data Mining & Machine Learning Lab', 5, 8, '13:30:00', '16:30:00', 'Lab'),
(19, 'AI&DS', 'A', '3rd Year', 5, 'Thursday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(20, 'AI&DS', 'A', '3rd Year', 5, 'Friday', 'FDM', 'Foundations of Data Mining', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(21, 'AI&DS', 'A', '3rd Year', 5, 'Friday', 'SS', 'Soft Skills', 3, 4, '10:30:00', '12:00:00', 'Activity'),
(22, 'AI&DS', 'A', '3rd Year', 5, 'Friday', 'OE-I', 'Open Elective I', 5, 6, '13:30:00', '15:00:00', 'Theory'),
(23, 'AI&DS', 'A', '3rd Year', 5, 'Friday', 'HON/MIN', 'Honors / Minors', 7, 8, '15:00:00', '16:30:00', 'Theory'),
(24, 'AI&DS', 'A', '3rd Year', 5, 'Friday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(25, 'AI&DS', 'A', '3rd Year', 5, 'Saturday', 'PML', 'Predictive Machine Learning', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(26, 'AI&DS', 'A', '3rd Year', 5, 'Saturday', 'QA', 'Quantitative Aptitude', 3, 4, '10:30:00', '12:00:00', 'Activity'),
(27, 'AI&DS', 'B', '3rd Year', 5, 'Monday', 'DM ML LAB', 'Data Mining & Machine Learning Lab', 1, 4, '09:00:00', '12:00:00', 'Lab'),
(28, 'AI&DS', 'B', '3rd Year', 5, 'Monday', 'FDM', 'Foundations of Data Mining', 5, 6, '13:30:00', '15:00:00', 'Theory'),
(29, 'AI&DS', 'B', '3rd Year', 5, 'Monday', 'COUNSELING', 'Counseling', 7, 7, '15:00:00', '15:45:00', 'Activity'),
(30, 'AI&DS', 'B', '3rd Year', 5, 'Monday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(31, 'AI&DS', 'B', '3rd Year', 5, 'Tuesday', 'PML', 'Predictive Machine Learning', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(32, 'AI&DS', 'B', '3rd Year', 5, 'Tuesday', 'OS', 'Operating Systems', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(33, 'AI&DS', 'B', '3rd Year', 5, 'Tuesday', 'FLUTTER', 'Tinkering Lab - Flutter', 5, 6, '13:30:00', '15:00:00', 'Lab'),
(34, 'AI&DS', 'B', '3rd Year', 5, 'Tuesday', 'SS', 'Soft Skills', 7, 8, '15:00:00', '16:30:00', 'Activity'),
(35, 'AI&DS', 'B', '3rd Year', 5, 'Tuesday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(36, 'AI&DS', 'B', '3rd Year', 5, 'Wednesday', 'OE-I', 'Open Elective I', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(37, 'AI&DS', 'B', '3rd Year', 5, 'Wednesday', 'HON/MIN', 'Honors / Minors', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(38, 'AI&DS', 'B', '3rd Year', 5, 'Wednesday', 'DV LAB', 'Data Visualization Lab', 5, 8, '13:30:00', '16:30:00', 'Lab'),
(39, 'AI&DS', 'B', '3rd Year', 5, 'Wednesday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(40, 'AI&DS', 'B', '3rd Year', 5, 'Thursday', 'OS', 'Operating Systems', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(41, 'AI&DS', 'B', '3rd Year', 5, 'Thursday', 'QA', 'Quantitative Aptitude', 3, 4, '10:30:00', '12:00:00', 'Activity'),
(42, 'AI&DS', 'B', '3rd Year', 5, 'Thursday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(43, 'AI&DS', 'B', '3rd Year', 5, 'Friday', 'CN', 'Computer Networks', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(44, 'AI&DS', 'B', '3rd Year', 5, 'Friday', 'FDM', 'Foundations of Data Mining', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(45, 'AI&DS', 'B', '3rd Year', 5, 'Friday', 'OE-I', 'Open Elective I', 5, 6, '13:30:00', '15:00:00', 'Theory'),
(46, 'AI&DS', 'B', '3rd Year', 5, 'Friday', 'HON/MIN', 'Honors / Minors', 7, 8, '15:00:00', '16:30:00', 'Theory'),
(47, 'AI&DS', 'B', '3rd Year', 5, 'Friday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(48, 'AI&DS', 'B', '3rd Year', 5, 'Saturday', 'CN', 'Computer Networks', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(49, 'AI&DS', 'B', '3rd Year', 5, 'Saturday', 'PML', 'Predictive Machine Learning', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(73, 'AI&DS', 'C', '3rd Year', 5, 'Monday', 'OS', 'Operating Systems', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(74, 'AI&DS', 'C', '3rd Year', 5, 'Monday', 'CN', 'Computer Networks', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(75, 'AI&DS', 'C', '3rd Year', 5, 'Monday', 'PML', 'Predictive Machine Learning', 5, 6, '13:30:00', '15:00:00', 'Theory'),
(76, 'AI&DS', 'C', '3rd Year', 5, 'Monday', 'COUNSELING', 'Counseling', 7, 7, '15:00:00', '15:45:00', 'Activity'),
(77, 'AI&DS', 'C', '3rd Year', 5, 'Monday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(78, 'AI&DS', 'C', '3rd Year', 5, 'Tuesday', 'OS', 'Operating Systems', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(79, 'AI&DS', 'C', '3rd Year', 5, 'Tuesday', 'FDM', 'Foundations of Data Mining', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(80, 'AI&DS', 'C', '3rd Year', 5, 'Tuesday', 'DM ML LAB', 'Data Mining & Machine Learning Lab', 5, 8, '13:30:00', '16:30:00', 'Lab'),
(81, 'AI&DS', 'C', '3rd Year', 5, 'Tuesday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(82, 'AI&DS', 'C', '3rd Year', 5, 'Wednesday', 'OE-I', 'Open Elective I', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(83, 'AI&DS', 'C', '3rd Year', 5, 'Wednesday', 'HON/MIN', 'Honors / Minors', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(84, 'AI&DS', 'C', '3rd Year', 5, 'Wednesday', 'QA', 'Quantitative Aptitude', 5, 6, '13:30:00', '15:00:00', 'Activity'),
(85, 'AI&DS', 'C', '3rd Year', 5, 'Wednesday', 'FLUTTER', 'Tinkering Lab - Flutter', 7, 8, '15:00:00', '16:30:00', 'Lab'),
(86, 'AI&DS', 'C', '3rd Year', 5, 'Wednesday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(87, 'AI&DS', 'C', '3rd Year', 5, 'Thursday', 'DV LAB', 'Data Visualization Lab', 1, 4, '09:00:00', '12:00:00', 'Lab'),
(88, 'AI&DS', 'C', '3rd Year', 5, 'Thursday', 'SS', 'Soft Skills', 5, 6, '13:30:00', '15:00:00', 'Activity'),
(89, 'AI&DS', 'C', '3rd Year', 5, 'Thursday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(90, 'AI&DS', 'C', '3rd Year', 5, 'Friday', 'CN', 'Computer Networks', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(91, 'AI&DS', 'C', '3rd Year', 5, 'Friday', 'PML', 'Predictive Machine Learning', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(92, 'AI&DS', 'C', '3rd Year', 5, 'Friday', 'OE-I', 'Open Elective I', 5, 6, '13:30:00', '15:00:00', 'Theory'),
(93, 'AI&DS', 'C', '3rd Year', 5, 'Friday', 'HON/MIN', 'Honors / Minors', 7, 8, '15:00:00', '16:30:00', 'Theory'),
(94, 'AI&DS', 'C', '3rd Year', 5, 'Friday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(95, 'AI&DS', 'C', '3rd Year', 5, 'Saturday', 'FDM', 'Foundations of Data Mining', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(96, 'AI&DS', 'D', '3rd Year', 5, 'Monday', 'OS', 'Operating Systems', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(97, 'AI&DS', 'D', '3rd Year', 5, 'Monday', 'PML', 'Predictive Machine Learning', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(98, 'AI&DS', 'D', '3rd Year', 5, 'Monday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(99, 'AI&DS', 'D', '3rd Year', 5, 'Tuesday', 'QA', 'Quantitative Aptitude', 1, 2, '09:00:00', '10:30:00', 'Activity'),
(100, 'AI&DS', 'D', '3rd Year', 5, 'Tuesday', 'SS', 'Soft Skills', 3, 4, '10:30:00', '12:00:00', 'Activity'),
(101, 'AI&DS', 'D', '3rd Year', 5, 'Tuesday', 'DM ML LAB', 'Data Mining & Machine Learning Lab', 5, 8, '13:30:00', '16:30:00', 'Lab'),
(102, 'AI&DS', 'D', '3rd Year', 5, 'Tuesday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(103, 'AI&DS', 'D', '3rd Year', 5, 'Wednesday', 'OE-I', 'Open Elective I', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(104, 'AI&DS', 'D', '3rd Year', 5, 'Wednesday', 'HON/MIN', 'Honors / Minors', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(105, 'AI&DS', 'D', '3rd Year', 5, 'Wednesday', 'CN', 'Computer Networks', 5, 6, '13:30:00', '15:00:00', 'Theory'),
(106, 'AI&DS', 'D', '3rd Year', 5, 'Wednesday', 'COUNSELING', 'Counseling', 7, 7, '15:00:00', '15:45:00', 'Activity'),
(107, 'AI&DS', 'D', '3rd Year', 5, 'Wednesday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(108, 'AI&DS', 'D', '3rd Year', 5, 'Thursday', 'FDM', 'Foundations of Data Mining', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(109, 'AI&DS', 'D', '3rd Year', 5, 'Thursday', 'CN', 'Computer Networks', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(110, 'AI&DS', 'D', '3rd Year', 5, 'Thursday', 'PML', 'Predictive Machine Learning', 5, 6, '13:30:00', '15:00:00', 'Theory'),
(111, 'AI&DS', 'D', '3rd Year', 5, 'Thursday', 'FLUTTER', 'Tinkering Lab - Flutter', 7, 8, '15:00:00', '16:30:00', 'Lab'),
(112, 'AI&DS', 'D', '3rd Year', 5, 'Thursday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(113, 'AI&DS', 'D', '3rd Year', 5, 'Friday', 'DV LAB', 'Data Visualization Lab', 1, 4, '09:00:00', '12:00:00', 'Lab'),
(114, 'AI&DS', 'D', '3rd Year', 5, 'Friday', 'OE-I', 'Open Elective I', 5, 6, '13:30:00', '15:00:00', 'Theory'),
(115, 'AI&DS', 'D', '3rd Year', 5, 'Friday', 'HON/MIN', 'Honors / Minors', 7, 8, '15:00:00', '16:30:00', 'Theory'),
(116, 'AI&DS', 'D', '3rd Year', 5, 'Friday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(117, 'AI&DS', 'D', '3rd Year', 5, 'Saturday', 'OS', 'Operating Systems', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(118, 'AI&DS', 'D', '3rd Year', 5, 'Saturday', 'FDM', 'Foundations of Data Mining', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(119, 'CSBS', 'A', '3rd Year', 5, 'Monday', 'DA', 'Data Analytics', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(120, 'CSBS', 'A', '3rd Year', 5, 'Monday', 'BI', 'Business Intelligence', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(121, 'CSBS', 'A', '3rd Year', 5, 'Monday', 'AI', 'Artificial Intelligence', 5, 6, '13:30:00', '15:00:00', 'Theory'),
(122, 'CSBS', 'A', '3rd Year', 5, 'Monday', 'COUNSELING', 'Counseling', 7, 7, '15:00:00', '15:45:00', 'Activity'),
(123, 'CSBS', 'A', '3rd Year', 5, 'Monday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(124, 'CSBS', 'A', '3rd Year', 5, 'Tuesday', 'BI', 'Business Intelligence', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(125, 'CSBS', 'A', '3rd Year', 5, 'Tuesday', 'DA', 'Data Analytics', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(126, 'CSBS', 'A', '3rd Year', 5, 'Tuesday', 'CN', 'Computer Networks', 5, 6, '13:30:00', '15:00:00', 'Theory'),
(127, 'CSBS', 'A', '3rd Year', 5, 'Tuesday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(128, 'CSBS', 'A', '3rd Year', 5, 'Wednesday', 'OE-I', 'Open Elective I', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(129, 'CSBS', 'A', '3rd Year', 5, 'Wednesday', 'VA', 'Verbal Aptitude', 3, 4, '10:30:00', '12:00:00', 'Activity'),
(130, 'CSBS', 'A', '3rd Year', 5, 'Wednesday', 'DA LAB', 'Data Analytics Lab', 5, 8, '13:30:00', '16:30:00', 'Lab'),
(131, 'CSBS', 'A', '3rd Year', 5, 'Wednesday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(132, 'CSBS', 'A', '3rd Year', 5, 'Thursday', 'CN LAB', 'Computer Networks Lab', 1, 4, '09:00:00', '12:00:00', 'Lab'),
(133, 'CSBS', 'A', '3rd Year', 5, 'Thursday', 'FLUTTER', 'Tinkering Lab - Flutter', 5, 6, '13:30:00', '15:00:00', 'Lab'),
(134, 'CSBS', 'A', '3rd Year', 5, 'Thursday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(135, 'CSBS', 'A', '3rd Year', 5, 'Friday', 'FSD-II', 'Full Stack Development II', 1, 4, '09:00:00', '12:00:00', 'Lab'),
(136, 'CSBS', 'A', '3rd Year', 5, 'Friday', 'OE-I', 'Open Elective I', 5, 6, '13:30:00', '15:00:00', 'Theory'),
(137, 'CSBS', 'A', '3rd Year', 5, 'Friday', 'QA', 'Quantitative Aptitude', 7, 8, '15:00:00', '16:30:00', 'Activity'),
(138, 'CSBS', 'A', '3rd Year', 5, 'Friday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(139, 'CSBS', 'A', '3rd Year', 5, 'Saturday', 'AI', 'Artificial Intelligence', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(140, 'CSBS', 'A', '3rd Year', 5, 'Saturday', 'CN', 'Computer Networks', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(141, 'IT', 'A', '3rd Year', 5, 'Monday', 'A JAVA', 'Advanced Java', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(142, 'IT', 'A', '3rd Year', 5, 'Monday', 'ATCD', 'Automata Theory & Compiler Design', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(143, 'IT', 'A', '3rd Year', 5, 'Monday', 'VA', 'Verbal Aptitude', 5, 6, '13:30:00', '15:00:00', 'Activity'),
(144, 'IT', 'A', '3rd Year', 5, 'Monday', 'COUNSELING', 'Counseling', 7, 7, '15:00:00', '15:45:00', 'Activity'),
(145, 'IT', 'A', '3rd Year', 5, 'Monday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(146, 'IT', 'A', '3rd Year', 5, 'Tuesday', 'CN', 'Computer Networks', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(147, 'IT', 'A', '3rd Year', 5, 'Tuesday', 'AI', 'Artificial Intelligence', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(148, 'IT', 'A', '3rd Year', 5, 'Tuesday', 'FSD-I', 'Full Stack Development I', 5, 8, '13:30:00', '16:30:00', 'Lab'),
(149, 'IT', 'A', '3rd Year', 5, 'Tuesday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(150, 'IT', 'A', '3rd Year', 5, 'Wednesday', 'OE-I', 'Open Elective I', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(151, 'IT', 'A', '3rd Year', 5, 'Wednesday', 'HON/MIN', 'Honors / Minors', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(152, 'IT', 'A', '3rd Year', 5, 'Wednesday', 'CN LAB', 'Computer Networks Lab', 5, 8, '13:30:00', '16:30:00', 'Lab'),
(153, 'IT', 'A', '3rd Year', 5, 'Wednesday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(154, 'IT', 'A', '3rd Year', 5, 'Thursday', 'A JAVA', 'Advanced Java', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(155, 'IT', 'A', '3rd Year', 5, 'Thursday', 'FLUTTER', 'Tinkering Lab - Flutter', 3, 4, '10:30:00', '12:00:00', 'Lab'),
(156, 'IT', 'A', '3rd Year', 5, 'Thursday', 'A JAVA LAB', 'Advanced Java Lab', 5, 8, '13:30:00', '16:30:00', 'Lab'),
(157, 'IT', 'A', '3rd Year', 5, 'Thursday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(158, 'IT', 'A', '3rd Year', 5, 'Friday', 'ATCD', 'Automata Theory & Compiler Design', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(159, 'IT', 'A', '3rd Year', 5, 'Friday', 'CN', 'Computer Networks', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(160, 'IT', 'A', '3rd Year', 5, 'Friday', 'OE-I', 'Open Elective I', 5, 6, '13:30:00', '15:00:00', 'Theory'),
(161, 'IT', 'A', '3rd Year', 5, 'Friday', 'HON/MIN', 'Honors / Minors', 7, 8, '15:00:00', '16:30:00', 'Theory'),
(162, 'IT', 'A', '3rd Year', 5, 'Friday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(163, 'IT', 'A', '3rd Year', 5, 'Saturday', 'QA', 'Quantitative Aptitude', 1, 2, '09:00:00', '10:30:00', 'Activity'),
(164, 'IT', 'A', '3rd Year', 5, 'Saturday', 'AI', 'Artificial Intelligence', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(165, 'IT', 'B', '3rd Year', 5, 'Monday', 'CN', 'Computer Networks', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(166, 'IT', 'B', '3rd Year', 5, 'Monday', 'AI', 'Artificial Intelligence', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(167, 'IT', 'B', '3rd Year', 5, 'Monday', 'CN LAB', 'Computer Networks Lab', 5, 8, '13:30:00', '16:30:00', 'Lab'),
(168, 'IT', 'B', '3rd Year', 5, 'Monday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(169, 'IT', 'B', '3rd Year', 5, 'Tuesday', 'ATCD', 'Automata Theory & Compiler Design', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(170, 'IT', 'B', '3rd Year', 5, 'Tuesday', 'A JAVA', 'Advanced Java', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(171, 'IT', 'B', '3rd Year', 5, 'Tuesday', 'QA', 'Quantitative Aptitude', 5, 6, '13:30:00', '15:00:00', 'Activity'),
(172, 'IT', 'B', '3rd Year', 5, 'Tuesday', 'FLUTTER', 'Tinkering Lab - Flutter', 7, 8, '15:00:00', '16:30:00', 'Lab'),
(173, 'IT', 'B', '3rd Year', 5, 'Tuesday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(174, 'IT', 'B', '3rd Year', 5, 'Wednesday', 'OE-I', 'Open Elective I', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(175, 'IT', 'B', '3rd Year', 5, 'Wednesday', 'HON/MIN', 'Honors / Minors', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(176, 'IT', 'B', '3rd Year', 5, 'Wednesday', 'FSD-I', 'Full Stack Development I', 5, 8, '13:30:00', '16:30:00', 'Lab'),
(177, 'IT', 'B', '3rd Year', 5, 'Wednesday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(178, 'IT', 'B', '3rd Year', 5, 'Thursday', 'AI', 'Artificial Intelligence', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(179, 'IT', 'B', '3rd Year', 5, 'Thursday', 'CN', 'Computer Networks', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(180, 'IT', 'B', '3rd Year', 5, 'Thursday', 'VA', 'Verbal Aptitude', 5, 6, '13:30:00', '15:00:00', 'Activity'),
(181, 'IT', 'B', '3rd Year', 5, 'Thursday', 'COUNSELING', 'Counseling', 7, 7, '15:00:00', '15:45:00', 'Activity'),
(182, 'IT', 'B', '3rd Year', 5, 'Thursday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(183, 'IT', 'B', '3rd Year', 5, 'Friday', 'A JAVA LAB', 'Advanced Java Lab', 1, 4, '09:00:00', '12:00:00', 'Lab'),
(184, 'IT', 'B', '3rd Year', 5, 'Friday', 'OE-I', 'Open Elective I', 5, 6, '13:30:00', '15:00:00', 'Theory'),
(185, 'IT', 'B', '3rd Year', 5, 'Friday', 'HON/MIN', 'Honors / Minors', 7, 8, '15:00:00', '16:30:00', 'Theory'),
(186, 'IT', 'B', '3rd Year', 5, 'Friday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(187, 'IT', 'B', '3rd Year', 5, 'Saturday', 'A JAVA', 'Advanced Java', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(188, 'IT', 'B', '3rd Year', 5, 'Saturday', 'ATCD', 'Automata Theory & Compiler Design', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(189, 'IT', 'C', '3rd Year', 5, 'Monday', 'A JAVA', 'Advanced Java', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(190, 'IT', 'C', '3rd Year', 5, 'Monday', 'ATCD', 'Automata Theory & Compiler Design', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(191, 'IT', 'C', '3rd Year', 5, 'Monday', 'FSD-I', 'Full Stack Development I', 5, 8, '13:30:00', '16:30:00', 'Lab'),
(192, 'IT', 'C', '3rd Year', 5, 'Monday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(193, 'IT', 'C', '3rd Year', 5, 'Tuesday', 'CN', 'Computer Networks', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(194, 'IT', 'C', '3rd Year', 5, 'Tuesday', 'AI', 'Artificial Intelligence', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(195, 'IT', 'C', '3rd Year', 5, 'Tuesday', 'ATCD', 'Automata Theory & Compiler Design', 5, 6, '13:30:00', '15:00:00', 'Theory'),
(196, 'IT', 'C', '3rd Year', 5, 'Tuesday', 'COUNSELING', 'Counseling', 7, 7, '15:00:00', '15:45:00', 'Activity'),
(197, 'IT', 'C', '3rd Year', 5, 'Tuesday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(198, 'IT', 'C', '3rd Year', 5, 'Wednesday', 'OE-I', 'Open Elective I', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(199, 'IT', 'C', '3rd Year', 5, 'Wednesday', 'HON/MIN', 'Honors / Minors', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(200, 'IT', 'C', '3rd Year', 5, 'Wednesday', 'FLUTTER', 'Tinkering Lab - Flutter', 5, 6, '13:30:00', '15:00:00', 'Lab'),
(201, 'IT', 'C', '3rd Year', 5, 'Wednesday', 'VA', 'Verbal Aptitude', 7, 8, '15:00:00', '16:30:00', 'Activity'),
(202, 'IT', 'C', '3rd Year', 5, 'Wednesday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(203, 'IT', 'C', '3rd Year', 5, 'Thursday', 'A JAVA', 'Advanced Java', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(204, 'IT', 'C', '3rd Year', 5, 'Thursday', 'AI', 'Artificial Intelligence', 3, 4, '10:30:00', '12:00:00', 'Theory'),
(205, 'IT', 'C', '3rd Year', 5, 'Thursday', 'CN LAB', 'Computer Networks Lab', 5, 8, '13:30:00', '16:30:00', 'Lab'),
(206, 'IT', 'C', '3rd Year', 5, 'Thursday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(207, 'IT', 'C', '3rd Year', 5, 'Friday', 'CN', 'Computer Networks', 1, 2, '09:00:00', '10:30:00', 'Theory'),
(208, 'IT', 'C', '3rd Year', 5, 'Friday', 'QA', 'Quantitative Aptitude', 3, 4, '10:30:00', '12:00:00', 'Activity'),
(209, 'IT', 'C', '3rd Year', 5, 'Friday', 'OE-I', 'Open Elective I', 5, 6, '13:30:00', '15:00:00', 'Theory'),
(210, 'IT', 'C', '3rd Year', 5, 'Friday', 'HON/MIN', 'Honors / Minors', 7, 8, '15:00:00', '16:30:00', 'Theory'),
(211, 'IT', 'C', '3rd Year', 5, 'Friday', 'SPORTS', 'Sports', 9, 9, '16:30:00', '17:15:00', 'Activity'),
(212, 'IT', 'C', '3rd Year', 5, 'Saturday', 'A JAVA LAB', 'Advanced Java Lab', 1, 4, '09:00:00', '12:00:00', 'Lab');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_session` (`session_id`,`student_id`);

--
-- Indexes for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance_session` (`timetable_id`,`attendance_date`);

--
-- Indexes for table `marks`
--
ALTER TABLE `marks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subject_code` (`subject_code`);

--
-- Indexes for table `timetable`
--
ALTER TABLE `timetable`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_timetable_session` (`department`,`section`,`semester`,`day_of_week`,`start_period`,`end_period`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `attendance_records`
--
ALTER TABLE `attendance_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `marks`
--
ALTER TABLE `marks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `timetable`
--
ALTER TABLE `timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=213;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `marks`
--
ALTER TABLE `marks`
  ADD CONSTRAINT `marks_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
