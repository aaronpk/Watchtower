CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(2048) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `feeds` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(2048) DEFAULT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `content_hash` varchar(255) DEFAULT NULL,
  `content_type` varchar(255) DEFAULT NULL,
  `content_length` int(11) DEFAULT NULL,
  `tier` int(11) DEFAULT NULL,
  `websub_hub` varchar(512) DEFAULT NULL,
  `websub_topic` varchar(512) DEFAULT NULL,
  `websub_expiration` datetime DEFAULT NULL,
  `websub_active` tinyint(4) NOT NULL DEFAULT '0',
  `websub_last_ping_at` datetime DEFAULT NULL,
  `websub_subscribed_at` datetime DEFAULT NULL,
  `checks_since_last_change` int(11) NOT NULL DEFAULT '0',
  `http_last_modified` varchar(100) NOT NULL DEFAULT '',
  `http_etag` varchar(255) NOT NULL DEFAULT '',
  `last_checked_at` datetime DEFAULT NULL,
  `next_check_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `domain` (`domain`),
  KEY `tier` (`tier`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `subscribers` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `feed_id` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `callback_url` varchar(2048) DEFAULT NULL,
  `last_http_status` int(11) DEFAULT NULL,
  `error_count` int(11) NOT NULL DEFAULT '0',
  `last_notified_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `stats` (
  `key` varchar(30) NOT NULL,
  `value` int(11) DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `stats` (`key`, `value`) VALUES ("fetches", 0);
