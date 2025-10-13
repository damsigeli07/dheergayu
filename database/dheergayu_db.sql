-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 13, 2025 at 08:56 AM
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
-- Database: `dheergayu_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `dob` date NOT NULL,
  `nic` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `first_name`, `last_name`, `dob`, `nic`, `email`, `password`, `created_at`) VALUES
(3, 'Amashi', 'Vithanage', '1999-01-12', '200371911688', 'patient1@gmail.com', '$2y$10$Y07M.6ETDBYe.mA2kR6fzeCiK./YNkoyE5Id/YFif5frwPAFi.sAa', '2025-10-12 10:48:51'),
(4, 'Amal', 'Perera', '1946-12-30', '200372911684', 'patient2@gmail.com', '$2y$10$r7RXC3ULfSJZP1X9a9UFLOQBGISmi24ipzTXkmQotu0mDRN.pbZ1O', '2025-10-13 06:41:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `role` enum('pharmacist','doctor','staff','admin') NOT NULL,
  `certification_verified` tinyint(1) DEFAULT 0,
  `must_change_password` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'Active',
  `reg_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `phone`, `role`, `certification_verified`, `must_change_password`, `created_at`, `status`, `reg_date`) VALUES
(1, 'Nimal', 'Perera', 'doctor1@gmail.com', '$2y$10$x9M5bKin0BcQ5exCArlkpuT.QL844j9/M6lAXLjqaaD//8W6R9WOm', '0742440377', 'doctor', 0, 1, '2025-10-12 10:33:29', 'Active', '2025-10-11 18:30:00'),
(2, 'Kamal', 'Silva', 'staff1@gmail.com', '$2y$10$FtQljuIrL1HT/gAcQo3ho.3jfYhSx6SF6BGeEKvk9QVVlwLqswtQe', '0742440321', 'staff', 0, 1, '2025-10-12 10:42:53', 'Active', '2025-10-11 18:30:00'),
(3, 'Sarah', 'Perera', 'pharmacist1@gmail.com', '$2y$10$Za2bXz8s0aTweo1JH.A5BeBCYMwfMJyRyNJEu4BmeOYCJxujPsL7W', '0742420377', 'pharmacist', 0, 1, '2025-10-12 10:44:33', 'Active', '2025-10-11 18:30:00'),
(4, 'Nuwan', 'Vithana', 'admindheergayu@gmail.com', '$2y$10$Ev0xZtWqbHixuuNBCBY1x.V2NgOrWE7cVIpZxUyYy8QlIqkC/Eo5a', '0732440377', 'admin', 0, 1, '2025-10-12 10:45:34', 'Active', '2025-10-11 18:30:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nic` (`nic`),
  ADD UNIQUE KEY `email` (`email`);

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
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
