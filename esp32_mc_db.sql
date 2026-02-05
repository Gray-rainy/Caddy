-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 05, 2026 at 02:50 AM
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
-- Database: `esp32_mc_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Present','Absent') DEFAULT 'Present'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `student_id`, `class_id`, `timestamp`, `status`) VALUES
(1, 1, 1, '2026-01-06 14:47:00', 'Present'),
(2, 1, 1, '2026-01-06 14:47:01', 'Absent'),
(3, 1, 1, '2026-01-06 14:47:04', 'Absent'),
(4, 1, 1, '2026-01-06 14:54:28', 'Absent'),
(5, 1, 1, '2026-01-06 14:54:29', 'Absent'),
(6, 1, 1, '2026-01-06 14:54:29', 'Absent'),
(7, 1, 1, '2026-01-06 16:17:01', 'Absent'),
(8, 1, 1, '2026-01-06 16:17:02', 'Absent'),
(9, 1, 1, '2026-01-06 16:17:03', 'Absent'),
(10, 1, 1, '2026-01-06 16:21:14', 'Absent'),
(11, 18, 1, '2026-01-06 16:21:42', 'Present'),
(12, 13, 1, '2026-01-06 16:21:43', 'Present'),
(13, 13, 1, '2026-01-06 16:21:44', 'Absent'),
(14, 2, 1, '2026-01-06 16:25:38', 'Present'),
(15, 10, 1, '2026-01-06 16:25:39', 'Present'),
(16, 15, 1, '2026-01-06 16:25:40', 'Present'),
(17, 2, 1, '2026-01-06 16:25:40', 'Absent'),
(18, 10, 1, '2026-01-06 16:25:41', 'Absent');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `class_name` varchar(50) DEFAULT NULL,
  `start_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `class_name`, `start_time`) VALUES
(1, 'Math', '08:00:00'),
(2, 'Science', '09:00:00'),
(3, 'English', '10:00:00'),
(4, 'History', '11:00:00'),
(5, 'Computer', '12:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_uid` varchar(50) DEFAULT NULL,
  `student_name` varchar(100) DEFAULT NULL,
  `confirmed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_uid`, `student_name`, `confirmed`) VALUES
(1, '04A1BC9F23', 'John Smith', 0),
(2, 'UID1001', 'Alex Johnson', 1),
(3, 'UID1002', 'Mia Rodriguez', 1),
(4, 'UID1003', 'Ethan Brown', 1),
(5, 'UID1004', 'Sophia Martinez', 1),
(6, 'UID1005', 'Noah Wilson', 1),
(7, 'UID1006', 'Emma Davis', 1),
(8, 'UID1007', 'Liam Anderson', 1),
(9, 'UID1008', 'Olivia Thomas', 1),
(10, 'UID1009', 'Aiden Moore', 1),
(11, 'UID1010', 'Isabella Taylor', 1),
(12, 'UID1011', 'Lucas Harris', 1),
(13, 'UID1012', 'Charlotte Clark', 1),
(14, 'UID1013', 'Benjamin Lewis', 1),
(15, 'UID1014', 'Amelia Walker', 1),
(16, 'UID1015', 'Henry Hall', 1),
(17, 'UID1016', 'Harper Allen', 1),
(18, 'UID1017', 'Daniel Young', 1),
(19, 'UID1018', 'Evelyn King', 1),
(20, 'UID1019', 'Matthew Wright', 1),
(21, 'UID1020', 'Abigail Lopez', 1);

-- --------------------------------------------------------

--
-- Table structure for table `student_class`
--

CREATE TABLE `student_class` (
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `present_override` tinyint(1) DEFAULT NULL,
  `confirmed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_classes`
--

CREATE TABLE `student_classes` (
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `seat_row` int(11) DEFAULT 1,
  `seat_col` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `student_classes`
--

INSERT INTO `student_classes` (`student_id`, `class_id`, `seat_row`, `seat_col`) VALUES
(2, 1, 2, 2),
(3, 1, 1, 1),
(4, 1, 1, 1),
(5, 1, 1, 1),
(6, 1, 1, 1),
(7, 1, 1, 1),
(8, 1, 1, 1),
(9, 1, 1, 1),
(10, 1, 1, 1),
(11, 1, 1, 1),
(12, 1, 1, 1),
(13, 1, 1, 1),
(14, 1, 1, 1),
(15, 1, 1, 1),
(16, 1, 1, 1),
(17, 1, 1, 1),
(18, 1, 1, 1),
(19, 1, 1, 1),
(20, 1, 1, 1),
(21, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('teacher','admin') DEFAULT 'teacher'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `username`, `password`, `role`) VALUES
(3, 'john', '$2y$10$fy8j.1BHteu5wBrKX60QLu69vC7x5NctImhh1ucJ/ox7ttMc6w6ZC', 'teacher'),
(4, 'smith', '$2y$10$3/fvd.EOjsEGGEaUzzsE5u1GzhOOR0C.BIKqdP/3fMtG108Os1ZLu', 'admin'),
(6, 'adams', '$2y$10$GqForO6Ln7EnLQ.dLRyBJ.TjkcoZOSFGU3FrW.EbVaPW0BcNhtzey', 'teacher'),
(7, 'admin', '$2y$10$RJKK6m7td7nAaG6eTrIKa.YnAc.TphJXiUlRbOp5PkvYtSg5tDCDO', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_uid` (`student_uid`);

--
-- Indexes for table `student_class`
--
ALTER TABLE `student_class`
  ADD PRIMARY KEY (`student_id`,`class_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `student_classes`
--
ALTER TABLE `student_classes`
  ADD PRIMARY KEY (`student_id`,`class_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`);

--
-- Constraints for table `student_class`
--
ALTER TABLE `student_class`
  ADD CONSTRAINT `student_class_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `student_class_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `student_class_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Constraints for table `student_classes`
--
ALTER TABLE `student_classes`
  ADD CONSTRAINT `student_classes_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_classes_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
