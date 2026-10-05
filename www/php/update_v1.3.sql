-- update groups table
ALTER TABLE `groups` ADD COLUMN IF NOT EXISTS `variable` varchar(255) NOT NULL DEFAULT '';

-- update version in global;
UPDATE `global` SET `value` = '1.3.0' WHERE `global`.`key` = 'version';
