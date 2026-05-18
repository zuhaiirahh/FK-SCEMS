-- Run this in phpMyAdmin to set up the database and a sample login account.
-- Database: fkscems

CREATE DATABASE IF NOT EXISTS `fkscems` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `fkscems`;

DROP TABLE IF EXISTS `waiting_list`;
DROP TABLE IF EXISTS `event_registration`;
DROP TABLE IF EXISTS `event`;
DROP TABLE IF EXISTS `user`;

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

-- Password: password123
INSERT INTO `user` (`username`, `password`, `name`, `email`, `phoneNumber`, `profilePhoto`, `userType`) VALUES
('fkscems01', '$2y$10$OBiPNAADSCRYDI3/8LYw.etWF5ZoJNynStJGsmol8lP1Ddp1LA0GG', 'Ahmad bin Abdullah', 'ahmad.abdullah@umpsa.edu.my', '0123456789', NULL, 'committee'),
('student01', '$2y$10$OBiPNAADSCRYDI3/8LYw.etWF5ZoJNynStJGsmol8lP1Ddp1LA0GG', 'Nur Zuhairah', 'nur.zuhairah@student.umpsa.edu.my', '0198765432', NULL, 'student');

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

CREATE TABLE `event_registration` (
  `registrationID` int(11) NOT NULL AUTO_INCREMENT,
  `eventID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `registrationDate` datetime NOT NULL DEFAULT current_timestamp(),
  `registrationStatus` varchar(100) NOT NULL DEFAULT 'registered',
  `confirmationStatus` varchar(100) NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`registrationID`),
  UNIQUE KEY `uniq_event_user` (`eventID`, `userID`),
  KEY `idx_reg_event` (`eventID`),
  KEY `idx_reg_user` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `waiting_list` (
  `waitingID` int(11) NOT NULL AUTO_INCREMENT,
  `eventID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `requestDate` datetime NOT NULL DEFAULT current_timestamp(),
  `waitingStatus` varchar(20) NOT NULL DEFAULT 'waiting',
  PRIMARY KEY (`waitingID`),
  UNIQUE KEY `uniq_wait_event_user` (`eventID`, `userID`),
  KEY `idx_wait_event` (`eventID`),
  KEY `idx_wait_user` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
