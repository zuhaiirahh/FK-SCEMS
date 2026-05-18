-- Fix event_registration when you see: Duplicate entry '0' for key 'PRIMARY'
-- Run in phpMyAdmin (fkscems database). The app also auto-repairs on register.

USE `fkscems`;

-- Fix invalid ID 0 (skip if already fixed)
UPDATE `event_registration` SET `registrationID` = 1 WHERE `registrationID` = 0;

ALTER TABLE `event_registration`
  MODIFY `registrationID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `event_registration` DROP FOREIGN KEY `event_registration_ibfk_1`;
ALTER TABLE `event_registration` DROP FOREIGN KEY `event_registration_ibfk_2`;

ALTER TABLE `event_registration` DROP INDEX `userID`;
ALTER TABLE `event_registration` DROP INDEX `eventID`;

ALTER TABLE `event_registration` ADD KEY `idx_reg_user` (`userID`);
ALTER TABLE `event_registration` ADD KEY `idx_reg_event` (`eventID`);
ALTER TABLE `event_registration` ADD UNIQUE KEY `uniq_event_user` (`eventID`, `userID`);

ALTER TABLE `event_registration`
  ADD CONSTRAINT `event_registration_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `event` (`eventID`);
ALTER TABLE `event_registration`
  ADD CONSTRAINT `event_registration_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`);

ALTER TABLE `waiting_list`
  MODIFY `waitingID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `waiting_list`
  ADD UNIQUE KEY `uniq_wait_event_user` (`eventID`, `userID`);
