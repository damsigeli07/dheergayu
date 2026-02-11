-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 13, 2026 at 05:30 AM
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
-- Table structure for table `patient_products`
--

CREATE TABLE `patient_products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_products`
--

INSERT INTO `patient_products` (`product_id`, `name`, `price`, `description`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Paspanguwa Pack (Patient)', 850.00, 'A traditional herbal remedy for digestive health, cold relief, and overall wellness.', 'images/paspanguwa.jpeg', '2026-01-13 05:30:00', '2026-01-13 05:30:00'),
(2, 'Samahan Herbal Drink (Sachets)', 1200.00, 'An Ayurvedic herbal drink used to support immunity and relieve cold, cough, fever, and body aches.', 'images/Samhan.jpg', '2026-01-13 05:30:00', '2026-01-13 05:30:00'),
(3, 'Siddhalepa Herbal Balm (Patient)', 450.00, 'A popular Ayurvedic balm used for headaches, muscle pain, joint pain, and relief from cold symptoms.', 'images/siddhalepa.png', '2026-01-13 05:30:00', '2026-01-13 05:30:00'),
(4, 'Asamodagam Spirit (Patient)', 650.00, 'A traditional Ayurvedic herbal tonic used to support digestion and relieve stomach discomfort.', 'images/asamodagam.jpg', '2026-01-13 05:30:00', '2026-01-13 05:30:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `patient_products`
--
ALTER TABLE `patient_products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `patient_products`
--
ALTER TABLE `patient_products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
