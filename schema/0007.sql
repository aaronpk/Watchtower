ALTER TABLE feeds
ADD COLUMN `websub_hub` varchar(512) DEFAULT NULL AFTER `tier`,
ADD COLUMN `websub_topic` varchar(512) DEFAULT NULL AFTER `websub_hub`,
ADD COLUMN `websub_expiration` datetime DEFAULT NULL AFTER `websub_topic`,
ADD COLUMN `websub_active` tinyint(4) NOT NULL DEFAULT 0 AFTER `websub_expiration`,
ADD COLUMN `websub_last_ping_at` datetime DEFAULT NULL AFTER `websub_active`,
ADD COLUMN `websub_subscribed_at` datetime DEFAULT NULL AFTER `websub_last_ping_at`
;
