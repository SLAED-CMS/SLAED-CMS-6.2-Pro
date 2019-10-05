RENAME TABLE `{pref}_auto_links_ip` TO `{pref}_referer`;
ALTER TABLE `{pref}_referer` CHANGE `referer_id` `lid` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `{pref}_referer` ADD `uid` INT(11) NOT NULL AFTER `id`;
ALTER TABLE `{pref}_referer` ADD `name` VARCHAR(25) NOT NULL AFTER `uid`;
ALTER TABLE `{pref}_referer` ADD `link` VARCHAR(255) NOT NULL AFTER `referer`;
ALTER TABLE `{pref}_admins` ADD `editor` TINYINT(1) NULL AFTER `super`;
ALTER TABLE `{pref}_admins` ADD `smail` TINYINT(1) NULL AFTER `editor`;