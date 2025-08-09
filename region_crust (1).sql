-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 28, 2025 at 09:48 AM
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
-- Database: `region_crust`
--

-- --------------------------------------------------------

--
-- Table structure for table `reservation`
--

CREATE TABLE `reservation` (
  `reservation_id` int(11) NOT NULL,
  `idroom` int(11) NOT NULL,
  `idroom_cat` int(11) NOT NULL,
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservation`
--

INSERT INTO `reservation` (`reservation_id`, `idroom`, `idroom_cat`, `check_in_date`, `check_out_date`) VALUES
(12, 2, 2, '2025-05-28', '2025-05-30');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `idroom` int(30) NOT NULL,
  `room_no` int(11) DEFAULT NULL,
  `idroom_cat` int(100) NOT NULL,
  `status` tinyint(4) NOT NULL COMMENT '0=Available, 1= Unavailable'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`idroom`, `room_no`, `idroom_cat`, `status`) VALUES
(1, 121, 1, 0),
(2, 111, 2, 0);

-- --------------------------------------------------------

--
-- Table structure for table `room_cat`
--

CREATE TABLE `room_cat` (
  `idroom_cat` int(100) NOT NULL,
  `room_cat` varchar(100) NOT NULL,
  `booking_due` varchar(100) NOT NULL,
  `price_room` varchar(100) NOT NULL,
  `img_room` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_cat`
--

INSERT INTO `room_cat` (`idroom_cat`, `room_cat`, `booking_due`, `price_room`, `img_room`) VALUES
(1, 'Single Room', 'Per Day', '10,000', 'einzelzimmer-standard-insel-hotel-bonn-2524 (1).jpg'),
(2, 'Double Room', 'Per Day', '15000', '94d9fa_137f1def4fc6460db3b34751b3f5db4e~mv2.avif'),
(3, 'Triple Room', 'Per Day', '16000', '94d9fa_8343b0908d1b418e84e5caad655d10f8~mv2.avif');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`) VALUES
(5, 'MOHAMED SHIBLI MUHAMMADU FAYAS', 'fayasshibly7777@gmail.com', '$2y$10$uLFEAEZwkcbLD8AIDDBE..kAsMFmo8EsuxycEJ8/5sXq92nFwe9gC');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `fk_reservation_room` (`idroom`),
  ADD KEY `fk_reservation_room_cat` (`idroom_cat`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`idroom`),
  ADD KEY `idroom_cat_fk` (`idroom_cat`);

--
-- Indexes for table `room_cat`
--
ALTER TABLE `room_cat`
  ADD PRIMARY KEY (`idroom_cat`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `idroom` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `room_cat`
--
ALTER TABLE `room_cat`
  MODIFY `idroom_cat` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `fk_reservation_room` FOREIGN KEY (`idroom`) REFERENCES `rooms` (`idroom`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reservation_room_cat` FOREIGN KEY (`idroom_cat`) REFERENCES `room_cat` (`idroom_cat`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `  ` FOREIGN KEY (`idroom`) REFERENCES `room_cat` (`idroom_cat`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
