ALTER TABLE feeds
ADD COLUMN http_last_modified VARCHAR(100) NOT NULL DEFAULT '' AFTER checks_since_last_change,
ADD COLUMN http_etag VARCHAR(255) NOT NULL DEFAULT '' AFTER http_last_modified,
ADD COLUMN content_length INT(11) DEFAULT NULL AFTER content_type;

