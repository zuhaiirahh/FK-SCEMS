-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Generation Time: May 17, 2026 at 10:33 AM
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
-- Table structure for table `administrator`
--

CREATE TABLE `administrator` (
  `userID` int(11) NOT NULL,
  `staffID` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `club`
--

CREATE TABLE `club` (
  `clubID` int(11) NOT NULL,
  `clubName` varchar(100) NOT NULL,
  `clubCategory` varchar(50) NOT NULL,
  `clubDescription` text NOT NULL,
  `clubStatus` varchar(20) NOT NULL,
  `clubCreatedDate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `club_committee`
--

CREATE TABLE `club_committee` (
  `committeeID` int(11) NOT NULL,
  `clubID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `rolePosition` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `club_membership`
--

CREATE TABLE `club_membership` (
  `membershipID` int(11) NOT NULL,
  `clubID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `membershipJoinDate` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `eventID` int(11) NOT NULL,
  `clubID` int(11) NOT NULL,
  `eventTitle` varchar(150) NOT NULL,
  `eventDescription` text NOT NULL,
  `eventDate` date NOT NULL,
  `eventTime` time NOT NULL,
  `eventVenue` varchar(150) NOT NULL,
  `maxParticipations` int(11) NOT NULL,
  `eventStatus` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_registration`
--

CREATE TABLE `event_registration` (
  `registrationID` int(11) NOT NULL,
  `eventID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `registrationDate` date NOT NULL,
  `registrationStatus` varchar(100) NOT NULL,
  `confirmationStatus` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `userID` int(11) NOT NULL,
  `studentID` varchar(20) NOT NULL,
  `totalPoints` int(11) NOT NULL,
  `studentType` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

  `userID` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phoneNumber` varchar(20) NOT NULL,
  `profilePhoto` varchar(255) NOT NULL,
  `userType` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`userID`, `username`, `password`, `name`, `email`, `phoneNumber`, `profilePhoto`, `userType`) VALUES
(3, 'CB24024', '$2y$10$WluVzAg/XmwCXro69fIlj.adcmoYBrjSvH/3/q.mxNixHaONeJ38m', 'Nur Zuhairah', 'cb24024@adab.umpsa.edu.my', '1170208096', '', 'student'),
(7, 'CB24085', '$2y$10$FOuOAVfTLPwqUCx3ro3/5O84rLdKcOs2OkQEUSvJNzWcRAGius3ZC', 'Izatul Sorfina', 'cb24085@adab.umpsa.edu.my', '1110640352', '', 'admin'),
(10, 'CB24051', '$2y$10$jP2NSE45pRPWm.Sz.e9tKueCeAAFS0AYC91A9Nid5oeBSdeuiugaK', 'Fatehah Syuhadah', 'cb24051@adab.umpsa.edu.my', '01128089177', '', 'committee');

-- --------------------------------------------------------

--
-- Table structure for table `waiting_list`
--

CREATE TABLE `waiting_list` (
  `waitingID` int(11) NOT NULL,
  `eventID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `requestDate` date NOT NULL,
  `waitingStatus` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrator`
--
ALTER TABLE `administrator`
  ADD PRIMARY KEY (`userID`);

--
-- Indexes for table `club`
--
ALTER TABLE `club`
  ADD PRIMARY KEY (`clubID`);

--
-- Indexes for table `club_committee`
--
ALTER TABLE `club_committee`
  ADD PRIMARY KEY (`committeeID`),
  ADD KEY `clubID` (`clubID`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `club_membership`
--
ALTER TABLE `club_membership`
  ADD PRIMARY KEY (`membershipID`),
  ADD UNIQUE KEY `clubID` (`clubID`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`eventID`),
  ADD UNIQUE KEY `clubID` (`clubID`);

--
-- Indexes for table `event_registration`
--
ALTER TABLE `event_registration`
  ADD PRIMARY KEY (`registrationID`),
  ADD UNIQUE KEY `userID` (`userID`),
  ADD UNIQUE KEY `eventID` (`eventID`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `studentID` (`studentID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `waiting_list`
--
ALTER TABLE `waiting_list`
  ADD PRIMARY KEY (`waitingID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `administrator`
--
ALTER TABLE `administrator`
  ADD CONSTRAINT `administrator_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`);

--
-- Constraints for table `club_committee`
--
ALTER TABLE `club_committee`
  ADD CONSTRAINT `club_committee_ibfk_1` FOREIGN KEY (`clubID`) REFERENCES `club` (`clubID`),
  ADD CONSTRAINT `club_committee_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`);

--
-- Constraints for table `club_membership`
--
ALTER TABLE `club_membership`
  ADD CONSTRAINT `club_membership_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`),
  ADD CONSTRAINT `club_membership_ibfk_2` FOREIGN KEY (`clubID`) REFERENCES `club` (`clubID`);

--
-- Constraints for table `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `event_ibfk_1` FOREIGN KEY (`clubID`) REFERENCES `club` (`clubID`);

--
-- Constraints for table `event_registration`
--
ALTER TABLE `event_registration`
  ADD CONSTRAINT `event_registration_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `event` (`eventID`),
  ADD CONSTRAINT `event_registration_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`);

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
