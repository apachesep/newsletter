# Version 1.0.3b;
# Migration(upgrade).Uses only if UPDATE proccess executes!;
# Prev version 1.0.3a;

SET foreign_key_checks = 0;

CREATE TABLE `#__newsletter_automailings` (
  `automailing_id` INT(11) NOT NULL AUTO_INCREMENT,
  `automailing_name` VARCHAR(255) DEFAULT NULL,
  `automailing_type` ENUM('scheduled','eventbased') DEFAULT NULL,
  `automailing_event` ENUM('subscription') DEFAULT NULL,
  `automailing_state` INT(11) DEFAULT NULL,
  `params` TEXT,

  PRIMARY KEY (`automailing_id`)
) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

CREATE TABLE `#__newsletter_automailing_items` (
  `series_id` INT(11) NOT NULL AUTO_INCREMENT,
  `automailing_id` INT(11) DEFAULT NULL,
  `newsletter_id` INT(11) DEFAULT NULL,
  `time_start` TIMESTAMP NULL DEFAULT NULL,
  `time_offset` INT(11) DEFAULT NULL,
  `parent_id` INT(11) DEFAULT '0',

  PRIMARY KEY (`series_id`)
) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;


CREATE TABLE `#__newsletter_threads` (
  `thread_id` INT(11) NOT NULL AUTO_INCREMENT,
  `parent_id` INT(11) DEFAULT NULL,
  `type` ENUM ('send', 'automail', 'read') NOT NULL,
  `subtype` VARCHAR (255),
  `resource` VARCHAR (255) NOT NULL COMMENT "The target point of a process. email for 'send' and 'read'",
  `params` TEXT,

  PRIMARY KEY (`thread_id`)
) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

SET foreign_key_checks = 1;