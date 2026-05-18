-- Optional: make existing committee events visible to students (run once in phpMyAdmin)
USE `fkscems`;

-- Students can browse anything except Draft / Cancelled / Closed / Rejected
UPDATE `event`
SET `eventStatus` = 'Active'
WHERE LOWER(`eventStatus`) IN ('pending', 'pending_approval', 'approved', 'open');
