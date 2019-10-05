ALTER TABLE `{pref}_users` CHANGE `user_block` `user_block` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `{pref}_users` DROP `user_icq`, DROP `user_aim`, DROP `user_yim`, DROP `user_msnm`;
ALTER TABLE `{pref}_users` ADD `user_network` VARCHAR(255) NOT NULL AFTER `user_agent`;