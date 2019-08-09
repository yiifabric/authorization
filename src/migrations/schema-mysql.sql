SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `auth_rule`;
CREATE TABLE `auth_rule`
(
  `name`       varchar(64) NOT NULL,
  `data`       blob,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=`utf8mb4` COLLATE=`utf8mb4_unicode_ci`;

DROP TABLE IF EXISTS `auth_item`;
CREATE TABLE `auth_item`
(
  `name`        varchar(64) NOT NULL,
  `type`        tinyint(1)  NOT NULL,
  `description` varchar(192) DEFAULT NULL,
  `external_id` int(11)      DEFAULT NULL,
  `attached_id` int(11)      DEFAULT NULL,
  `rule_name`   varchar(64)  DEFAULT NULL,
  `data`        blob,
  `created_at`  int(11)      DEFAULT NULL,
  `updated_at`  int(11)      DEFAULT NULL,
  PRIMARY KEY (`name`),
  KEY `idx_auth_item_type` (`type`),
  FOREIGN KEY `fk_auth_item_rule_name` (`rule_name`) REFERENCES `auth_rule` (`name`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=`utf8mb4` COLLATE=`utf8mb4_unicode_ci`;

DROP TABLE IF EXISTS `auth_item_child`;
CREATE TABLE `auth_item_child`
(
  `parent` varchar(64) NOT NULL,
  `child`  varchar(64) NOT NULL,
  PRIMARY KEY (`parent`, `child`),
  FOREIGN KEY (`parent`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY `fk_auth_item_child_child` (`child`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=`utf8mb4` COLLATE=`utf8mb4_unicode_ci`;

DROP TABLE IF EXISTS `auth_assignment`;
CREATE TABLE `auth_assignment`
(
  `item_name`  varchar(64) NOT NULL,
  `user_id`    varchar(64) NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`item_name`, `user_id`),
  KEY `idx_auth_assignment_user_id` (`user_id`),
  FOREIGN KEY (`item_name`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=`utf8mb4` COLLATE=`utf8mb4_unicode_ci`;


SET FOREIGN_KEY_CHECKS=1;
