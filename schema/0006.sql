CREATE TABLE `stats` (
  `key` varchar(30) NOT NULL,
  `value` int(11) DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `stats` (`key`, `value`) VALUES ("fetches", 0);
