ALTER TABLE feeds
ADD COLUMN `domain` varchar(255) DEFAULT NULL AFTER `url`;

ALTER TABLE `feeds` ADD INDEX `domain` (`domain`);
ALTER TABLE `feeds` ADD INDEX `tier` (`tier`);
