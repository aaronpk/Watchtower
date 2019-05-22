ALTER TABLE feeds
ADD COLUMN `pending` tinyint(4) NOT NULL DEFAULT '0' AFTER `next_check_at`;
