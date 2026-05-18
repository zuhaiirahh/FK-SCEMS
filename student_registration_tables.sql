-- Run in phpMyAdmin if you already have fkscems DB (adds registration + waiting list tables)
USE `fkscems`;

CREATE TABLE IF NOT EXISTS `event_registration` (
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

CREATE TABLE IF NOT EXISTS `waiting_list` (
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
