ALTER TABLE `{pref}_session` CHANGE `module` `module` VARCHAR(100) NOT NULL;
ALTER TABLE `{pref}_users` CHANGE `user_password` `user_password` VARCHAR(32) NOT NULL;
ALTER TABLE `{pref}_users_temp` CHANGE `user_password` `user_password` VARCHAR(25) NOT NULL;
ALTER TABLE `{pref}_admins` CHANGE `pwd` `pwd` VARCHAR(32) NOT NULL;
ALTER TABLE `{pref}_admins` CHANGE `ip` `ip` VARCHAR(15) NOT NULL;
ALTER TABLE `{pref}_comment` CHANGE `host_name` `host_name` VARCHAR(15) NOT NULL;
ALTER TABLE `{pref}_faq` CHANGE `ip_sender` `ip_sender` VARCHAR(15) NOT NULL;
ALTER TABLE `{pref}_files` CHANGE `ip_sender` `ip_sender` VARCHAR(15) NOT NULL;
ALTER TABLE `{pref}_forum` CHANGE `ip_send` `ip_send` VARCHAR(15) NOT NULL;
ALTER TABLE `{pref}_forum` CHANGE `e_ip_send` `e_ip_send` VARCHAR(15) NOT NULL;
ALTER TABLE `{pref}_help` CHANGE `ip_sender` `ip_sender` VARCHAR(15) NOT NULL;
ALTER TABLE `{pref}_jokes` CHANGE `ip_sender` `ip_sender` VARCHAR(15) NOT NULL;
ALTER TABLE `{pref}_links` CHANGE `ip_sender` `ip_sender` VARCHAR(15) NOT NULL;
ALTER TABLE `{pref}_media` CHANGE `ip_sender` `ip_sender` VARCHAR(15) NOT NULL;
ALTER TABLE `{pref}_page` CHANGE `ip_sender` `ip_sender` VARCHAR(15) NOT NULL;
ALTER TABLE `{pref}_rating` CHANGE `host` `host` VARCHAR(15) NOT NULL;
ALTER TABLE `{pref}_session` CHANGE `host_addr` `host_addr` VARCHAR(15) NOT NULL;
ALTER TABLE `{pref}_stories` CHANGE `ip_sender` `ip_sender` VARCHAR(15) NOT NULL;
ALTER TABLE `{pref}_users` CHANGE `user_last_ip` `user_last_ip` VARCHAR(15) NOT NULL;
ALTER TABLE `{pref}_products` ADD `ihome` INT(1) NOT NULL DEFAULT '0' AFTER `product_assoc`;
ALTER TABLE `{pref}_products` ADD `acomm` INT(1) NOT NULL DEFAULT '0' AFTER `ihome`;
ALTER TABLE `{pref}_products` CHANGE `product_active` `product_active` INT(1) NOT NULL DEFAULT '0';
UPDATE `{pref}_products` SET `ihome` = '1';
UPDATE `{pref}_products` SET `acomm` = '1';
UPDATE `{pref}_stories` SET `ihome` = '2' WHERE `ihome` = '0';
UPDATE `{pref}_stories` SET `ihome` = '0' WHERE `ihome` = '1';
UPDATE `{pref}_stories` SET `ihome` = '1' WHERE `ihome` = '2';
UPDATE `{pref}_stories` SET `acomm` = '2' WHERE `acomm` = '0';
UPDATE `{pref}_stories` SET `acomm` = '0' WHERE `acomm` = '1';
UPDATE `{pref}_stories` SET `acomm` = '1' WHERE `acomm` = '2';
ALTER TABLE `{pref}_files` ADD `counter` INT(11) NOT NULL DEFAULT '0' AFTER `ip_sender`;
ALTER TABLE `{pref}_files` ADD `ihome` INT(1) NOT NULL DEFAULT '0' AFTER `counter`;
ALTER TABLE `{pref}_files` ADD `acomm` INT(1) NOT NULL DEFAULT '0' AFTER `ihome`;
UPDATE `{pref}_files` SET `ihome` = '1';
UPDATE `{pref}_files` SET `acomm` = '1';
ALTER TABLE `{pref}_faq` ADD `ihome` INT(1) NOT NULL DEFAULT '0' AFTER `counter`;
UPDATE `{pref}_faq` SET `ihome` = '1';

CREATE TABLE `{pref}_favorites` (
  `id` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL default '0',
  `fid` int(11) NOT NULL default '0',
  `modul` varchar(50) NOT NULL default '',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `fid` (`fid`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_privat` (
  `id` int(11) NOT NULL auto_increment,
  `uidin` int(11) NOT NULL default '0',
  `uidout` int(11) NOT NULL default '0',
  `title` varchar(100) NOT NULL default '',
  `content` text NOT NULL,
  `date` datetime default NULL,
  `ip_sender` varchar(15) NOT NULL,
  `status` int(1) NOT NULL default '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

ALTER TABLE `{pref}_help` ADD `score` INT(11) NOT NULL DEFAULT '0' AFTER `counter`;
ALTER TABLE `{pref}_help` ADD `ratings` INT(11) NOT NULL DEFAULT '0' AFTER `score`;
ALTER TABLE `{pref}_users` ADD `user_fsmail` INT(1) NOT NULL DEFAULT '1' AFTER `user_newsletter`;
ALTER TABLE `{pref}_users` ADD `user_psmail` INT(1) NOT NULL DEFAULT '1' AFTER `user_fsmail`;
ALTER TABLE `{pref}_modules` DROP `custom_title`;
ALTER TABLE `{pref}_links` ADD `counter` INT(11) NOT NULL DEFAULT '0' AFTER `ip_sender`;
ALTER TABLE `{pref}_links` ADD `ihome` INT(1) NOT NULL DEFAULT '0' AFTER `counter`;
ALTER TABLE `{pref}_links` ADD `acomm` INT(1) NOT NULL DEFAULT '0' AFTER `ihome`;
UPDATE `{pref}_links` SET `ihome` = '1';
UPDATE `{pref}_links` SET `acomm` = '1';
ALTER TABLE `{pref}_media` ADD `ihome` INT(1) NOT NULL DEFAULT '0' AFTER `date`;
ALTER TABLE `{pref}_media` ADD `acomm` INT(1) NOT NULL DEFAULT '0' AFTER `ihome`;
UPDATE `{pref}_media` SET `ihome` = '1';
UPDATE `{pref}_media` SET `acomm` = '1';
ALTER TABLE `{pref}_page` ADD `ihome` INT(1) NOT NULL DEFAULT '0' AFTER `counter`;
UPDATE `{pref}_page` SET `ihome` = '1';
RENAME TABLE `{pref}_survey` TO `{pref}_voting`;
RENAME TABLE `{pref}_stories` TO `{pref}_news`;
RENAME TABLE `{pref}_page` TO `{pref}_pages`;
UPDATE `{pref}_voting` SET `acomm` = '1';