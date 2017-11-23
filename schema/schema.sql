CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `feeds` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(512) DEFAULT NULL,
  `content_hash` varchar(255) DEFAULT NULL,
  `content_type` varchar(255) DEFAULT NULL,
  `content_length` int(11) DEFAULT NULL,
  `tier` int(11) DEFAULT NULL,
  `checks_since_last_change` int(11) NOT NULL DEFAULT '0',
  `http_last_modified` varchar(100) NOT NULL DEFAULT '',
  `http_etag` varchar(255) NOT NULL DEFAULT '',
  `last_checked_at` datetime DEFAULT NULL,
  `next_check_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `subscribers` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `feed_id` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `callback_url` varchar(512) DEFAULT NULL,
  `last_http_status` int(11) DEFAULT NULL,
  `error_count` int(11) NOT NULL DEFAULT '0',
  `last_notified_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

