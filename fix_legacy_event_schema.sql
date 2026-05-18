-- Run once in phpMyAdmin (database: fkscems) to fix duplicate PRIMARY KEY '0' errors.

USE `fkscems`;

-- Remove invalid rows that block AUTO_INCREMENT
DELETE FROM `event` WHERE `eventID` = 0;
DELETE FROM `club` WHERE `clubID` = 0;

-- Club: auto-increment primary key
ALTER TABLE `club`
  MODIFY `clubID` int(11) NOT NULL AUTO_INCREMENT;

-- Event: allow many events per club (drop mistaken UNIQUE on clubID)
ALTER TABLE `event` DROP FOREIGN KEY `event_ibfk_1`;
ALTER TABLE `event` DROP INDEX `clubID`;
ALTER TABLE `event` ADD KEY `idx_event_clubID` (`clubID`);
ALTER TABLE `event` ADD CONSTRAINT `event_ibfk_1` FOREIGN KEY (`clubID`) REFERENCES `club` (`clubID`);

ALTER TABLE `event`
  MODIFY `eventID` int(11) NOT NULL AUTO_INCREMENT;
