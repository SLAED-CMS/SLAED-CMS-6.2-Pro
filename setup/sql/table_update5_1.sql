DROP TABLE `{pref}_session`;

CREATE TABLE `{pref}_session` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `uname` varchar(25) NOT NULL,
  `time` int(10) NOT NULL,
  `host_addr` varchar(15) NOT NULL,
  `guest` int(1) NOT NULL default '0',
  `module` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uname` (`uname`),
  KEY `time` (`time`)
) ENGINE=MyISAM;

RENAME TABLE `{pref}_voting` TO `{pref}_voting_temp`;

CREATE TABLE `{pref}_voting` (
  `id` int(11) NOT NULL auto_increment,
  `modul` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `questions` text NOT NULL,
  `answer` text NOT NULL,
  `date` datetime default NULL,
  `enddate` datetime default NULL,
  `multi` int(1) NOT NULL default '0',
  `comments` int(11) NOT NULL default '0',
  `language` varchar(30) NOT NULL,
  `acomm` int(1) NOT NULL default '0',
  `ip` varchar(15) NOT NULL,
  `typ` int(1) NOT NULL default '0',
  `status` int(1) NOT NULL default '0',
  PRIMARY KEY (`id`),
  KEY `modul` (`modul`)
) ENGINE=MyISAM;

ALTER TABLE `{pref}_news` ADD `vote` INT(11) NOT NULL DEFAULT '0' AFTER `field`;
ALTER TABLE `{pref}_news` ADD `fix` INT(1) NOT NULL DEFAULT '0' AFTER `ip_sender`;
ALTER TABLE `{pref}_products` ADD `vote` INT(1) NOT NULL DEFAULT '0' AFTER `product_preis`;
ALTER TABLE `{pref}_products` ADD `fix` INT(1) NOT NULL DEFAULT '0' AFTER `product_totalvotes`;

ALTER TABLE `{pref}_products` CHANGE `product_id` `id` INT(11) NOT NULL AUTO_INCREMENT, CHANGE `product_cid` `cid` INT(11) NOT NULL DEFAULT '0', CHANGE `product_time` `time` DATETIME NULL DEFAULT NULL, CHANGE `product_title` `title` VARCHAR(100) NULL DEFAULT NULL, CHANGE `product_text` `text` TEXT NOT NULL, CHANGE `product_desc` `bodytext` TEXT NOT NULL, CHANGE `product_preis` `preis` INT(10) NOT NULL DEFAULT '0', CHANGE `product_assoc` `assoc` TEXT NOT NULL, CHANGE `ihome` `ihome` INT(1) NOT NULL DEFAULT '0', CHANGE `acomm` `acomm` INT(1) NOT NULL DEFAULT '0', CHANGE `product_com` `com` INT(11) NOT NULL DEFAULT '0', CHANGE `product_count` `count` INT(11) NOT NULL DEFAULT '0', CHANGE `product_votes` `votes` INT(11) NOT NULL DEFAULT '0', CHANGE `product_totalvotes` `totalvotes` INT(11) NOT NULL DEFAULT '0', CHANGE `product_active` `active` INT( 1 ) NOT NULL DEFAULT '0';

ALTER TABLE `{pref}_partners` CHANGE `partner_id` `id` INT(11) NOT NULL AUTO_INCREMENT, CHANGE `partner_id_user` `id_user` INT(11) NOT NULL DEFAULT '0', CHANGE `partner_name` `name` VARCHAR(255) NOT NULL DEFAULT '', CHANGE `partner_adres` `adres` VARCHAR(255) NOT NULL DEFAULT '', CHANGE `partner_phone` `phone` VARCHAR(255) NOT NULL DEFAULT '', CHANGE `partner_email` `email` VARCHAR(255) NOT NULL DEFAULT '', CHANGE `partner_website` `website` VARCHAR(255) NOT NULL DEFAULT '', CHANGE `partner_webmoney` `webmoney` VARCHAR(255) NOT NULL DEFAULT '', CHANGE `partner_paypal` `paypal` VARCHAR(255) NOT NULL DEFAULT '', CHANGE `partner_regdate` `regdate` INT( 10 ) NOT NULL DEFAULT '0', CHANGE `partner_rest` `rest` INT( 10 ) NOT NULL DEFAULT '0', CHANGE `partner_bek` `bek` INT( 10 ) NOT NULL DEFAULT '0', CHANGE `partner_active` `active` TINYINT( 1 ) NULL DEFAULT '0';

ALTER TABLE `{pref}_clients` CHANGE `client_id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT , CHANGE `client_id_user` `id_user` INT( 11 ) NOT NULL DEFAULT '0', CHANGE `client_id_product` `id_product` INT( 11 ) NOT NULL DEFAULT '0', CHANGE `client_id_partner` `id_partner` INT( 11 ) NOT NULL DEFAULT '0', CHANGE `client_partner_proz` `partner_proz` INT( 3 ) NOT NULL DEFAULT '0', CHANGE `client_name` `name` VARCHAR( 255 ) NOT NULL DEFAULT '', CHANGE `client_adres` `adres` VARCHAR( 255 ) NOT NULL DEFAULT '', CHANGE `client_phone` `phone` VARCHAR( 255 ) NOT NULL DEFAULT '', CHANGE `client_email` `email` VARCHAR( 255 ) NOT NULL DEFAULT '', CHANGE `client_website` `website` VARCHAR( 255 ) NOT NULL DEFAULT '', CHANGE `client_regdate` `regdate` INT( 10 ) NOT NULL DEFAULT '0', CHANGE `client_enddate` `enddate` INT( 10 ) NOT NULL DEFAULT '0', CHANGE `client_key` `info` VARCHAR( 20 ) NOT NULL DEFAULT '', CHANGE `client_active` `active` TINYINT( 1 ) NULL DEFAULT '0';