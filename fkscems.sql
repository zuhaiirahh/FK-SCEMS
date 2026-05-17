-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Generation Time: May 10, 2026 at 05:14 PM
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
-- Database: `fkscems`
--

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `userID` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phoneNumber` varchar(20) DEFAULT NULL,
  `profilePhoto` varchar(255) DEFAULT NULL,
  `userType` enum('student','committee','admin') NOT NULL DEFAULT 'student',
  PRIMARY KEY (`userID`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Sample club organiser account (password: password123)
--

INSERT INTO `user` (`username`, `password`, `name`, `email`, `phoneNumber`, `profilePhoto`, `userType`) VALUES
('fkscems01', '$2y$10$OBiPNAADSCRYDI3/8LYw.etWF5ZoJNynStJGsmol8lP1Ddp1LA0GG', 'Ahmad bin Abdullah', 'ahmad.abdullah@umpsa.edu.my', '0123456789', NULL, 'committee');

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `eventID` int(11) NOT NULL AUTO_INCREMENT,
  `createdBy` int(11) NOT NULL,
  `eventTitle` varchar(255) NOT NULL,
  `eventCategory` varchar(100) NOT NULL,
  `eventDate` date NOT NULL,
  `startTime` time NOT NULL,
  `endTime` time NOT NULL,
  `venue` varchar(255) NOT NULL,
  `locationRoom` varchar(255) DEFAULT NULL,
  `eventDescription` text NOT NULL,
  `registrationRequired` tinyint(1) NOT NULL DEFAULT 1,
  `registrationStartDate` date DEFAULT NULL,
  `registrationEndDate` date DEFAULT NULL,
  `approvalRequired` tinyint(1) NOT NULL DEFAULT 1,
  `eventVisibility` varchar(50) NOT NULL DEFAULT 'club_members',
  `eventPoster` varchar(255) DEFAULT NULL,
  `estimatedCost` decimal(10,2) DEFAULT NULL,
  `maxParticipants` int(11) DEFAULT NULL,
  `contactPerson` varchar(150) DEFAULT NULL,
  `contactEmail` varchar(100) DEFAULT NULL,
  `contactPhone` varchar(20) DEFAULT NULL,
  `status` enum('draft','pending_approval','approved','rejected','cancelled') NOT NULL DEFAULT 'draft',
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`eventID`),
  KEY `idx_event_createdBy` (`createdBy`),
  KEY `idx_event_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
