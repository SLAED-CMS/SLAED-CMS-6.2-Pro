# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

CREATE TABLE `{pref}_admins` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(25) NOT NULL,
  `title` varchar(50) default NULL,
  `url` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `pwd` varchar(40) default NULL,
  `super` tinyint(1) default NULL,
  `editor` tinyint(1) default NULL,
  `smail` tinyint(1) default NULL,
  `modules` varchar(255) NOT NULL,
  `lang` varchar(30) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `regdate` datetime NOT NULL,
  `lastvisit` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_auto_links` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `sitename` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `link` varchar(100) NOT NULL,
  `mail` varchar(100) NOT NULL,
  `hits` int(11) NOT NULL default '0',
  `outs` int(11) NOT NULL default '0',
  `added` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_blocks` (
  `bid` int(11) NOT NULL auto_increment,
  `bkey` varchar(15) NOT NULL,
  `title` varchar(60) NOT NULL,
  `content` text NOT NULL,
  `url` varchar(200) NOT NULL,
  `bposition` char(1) NOT NULL,
  `weight` int(10) NOT NULL default '1',
  `active` int(1) NOT NULL default '1',
  `refresh` int(10) NOT NULL default '0',
  `time` varchar(14) NOT NULL default '0',
  `blanguage` varchar(30) NOT NULL,
  `blockfile` varchar(255) NOT NULL,
  `view` int(1) NOT NULL default '0',
  `expire` varchar(14) NOT NULL default '0',
  `action` char(1) NOT NULL,
  `which` text NOT NULL,
  PRIMARY KEY (`bid`),
  KEY `title` (`title`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_categories` (
  `id` int(11) NOT NULL auto_increment,
  `modul` varchar(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `img` varchar(100) NOT NULL,
  `language` varchar(30) NOT NULL,
  `parentid` int(11) NOT NULL default '0',
  `cstatus` int(1) NOT NULL default '0',
  `ordern` int(11) NOT NULL default '0',
  `topics` int(11) NOT NULL default '0',
  `posts` int(11) NOT NULL default '0',
  `lpost_id` int(11) NOT NULL default '0',
  `auth_view` varchar(100) NOT NULL,
  `auth_read` varchar(100) NOT NULL,
  `auth_post` varchar(100) NOT NULL,
  `auth_reply` varchar(100) NOT NULL,
  `auth_edit` varchar(100) NOT NULL,
  `auth_delete` varchar(100) NOT NULL,
  `auth_mod` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `modul` (`modul`),
  KEY `parentid` (`parentid`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_clients` (
  `id` int(11) NOT NULL auto_increment,
  `id_user` int(11) NOT NULL default '0',
  `id_product` int(11) NOT NULL default '0',
  `id_partner` int(11) NOT NULL default '0',
  `partner_proz` int(3) NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  `adres` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `website` varchar(255) NOT NULL,
  `regdate` int(10) NOT NULL default '0',
  `enddate` int(10) NOT NULL default '0',
  `info` varchar(255) NOT NULL,
  `active` tinyint(1) NULL default '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_comment` (
  `id` int(11) NOT NULL auto_increment,
  `cid` int(11) NOT NULL default '0',
  `modul` varchar(60) NOT NULL,
  `date` datetime default NULL,
  `uid` int(11) NOT NULL default '0',
  `name` varchar(25) NOT NULL,
  `host_name` varchar(15) NOT NULL,
  `comment` text NOT NULL,
  `status` int(1) NOT NULL default '0',
  PRIMARY KEY (`id`),
  KEY `cid` (`cid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_content` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(100) default NULL,
  `text` mediumtext NOT NULL,
  `field` text NOT NULL,
  `url` varchar(200) NOT NULL,
  `time` datetime default NULL,
  `refresh` int(10) NOT NULL default '0',
  `counter` int(11) NOT NULL default '0',
  PRIMARY KEY (`id`),
  KEY `counter` (`counter`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_faq` (
  `fid` int(11) NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `name` varchar(25) NOT NULL,
  `title` varchar(100) NOT NULL,
  `time` datetime default NULL,
  `hometext` text,
  `comments` int(11) NOT NULL default '0',
  `counter` int(11) NOT NULL default '0',
  `ihome` int(1) NOT NULL default '0',
  `acomm` int(1) NOT NULL default '0',
  `score` int(11) NOT NULL default '0',
  `ratings` int(11) NOT NULL default '0',
  `ip_sender` varchar(15) NOT NULL,
  `status` int(1) NOT NULL default '0',
  PRIMARY KEY (`fid`),
  KEY `catid` (`catid`),
  KEY `counter` (`counter`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_favorites` (
  `id` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL default '0',
  `fid` int(11) NOT NULL default '0',
  `modul` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `fid` (`fid`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_files` (
  `lid` int(11) NOT NULL auto_increment,
  `cid` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `name` varchar(25) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `bodytext` text NOT NULL,
  `url` varchar(100) NOT NULL,
  `date` datetime default NULL,
  `filesize` int(11) NOT NULL default '0',
  `version` varchar(10) NOT NULL,
  `email` varchar(100) NOT NULL,
  `homepage` varchar(200) NOT NULL,
  `ip_sender` varchar(15) NOT NULL,
  `counter` int(11) NOT NULL default '0',
  `ihome` int(1) NOT NULL default '0',
  `acomm` int(1) NOT NULL default '0',
  `votes` int(11) NOT NULL default '0',
  `totalvotes` int(11) NOT NULL default '0',
  `totalcomments` int(11) NOT NULL default '0',
  `hits` int(11) NOT NULL default '0',
  `status` int(1) NOT NULL default '0',
  PRIMARY KEY (`lid`),
  KEY `cid` (`cid`),
  KEY `title` (`title`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_forum` (
  `id` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `catid` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `name` varchar(25) NOT NULL,
  `title` varchar(100) NOT NULL,
  `time` datetime default NULL,
  `hometext` text,
  `field` text NOT NULL,
  `comments` int(11) default '0',
  `counter` int(11) NOT NULL default '0',
  `score` int(11) NOT NULL default '0',
  `ratings` int(11) NOT NULL default '0',
  `ip_send` varchar(15) NOT NULL,
  `l_uid` int(11) NOT NULL default '0',
  `l_name` varchar(25) NOT NULL,
  `l_id` int(11) NOT NULL default '0',
  `l_time` datetime default NULL,
  `e_uid` int(11) NOT NULL default '0',
  `e_ip_send` varchar(15) NOT NULL,
  `e_time` datetime default NULL,
  `status` int(1) NOT NULL default '0',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `catid` (`catid`),
  KEY `counter` (`counter`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_groups` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `points` int(10) NOT NULL default '0',
  `extra` int(1) NOT NULL default '0',
  `rank` varchar(255) NOT NULL,
  `color` varchar(7) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_help` (
  `sid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `catid` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `aid` int(11) NOT NULL default '0',
  `title` varchar(100) NOT NULL,
  `time` datetime default NULL,
  `hometext` text,
  `field` text NOT NULL,
  `comments` int(11) default '0',
  `counter` int(11) NOT NULL default '0',
  `score` int(11) NOT NULL default '0',
  `ratings` int(11) NOT NULL default '0',
  `ip_sender` varchar(15) NOT NULL,
  `status` int(1) NOT NULL default '0',
  PRIMARY KEY (`sid`),
  KEY `pid` (`pid`),
  KEY `catid` (`catid`),
  KEY `counter` (`counter`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_jokes` (
  `jokeid` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL default '0',
  `name` varchar(25) NOT NULL,
  `date` datetime default NULL,
  `title` varchar(100) NOT NULL,
  `cat` int(11) NOT NULL default '0',
  `joke` text NOT NULL,
  `rating` varchar(100) NOT NULL default '0',
  `ratingtot` varchar(100) NOT NULL default '0',
  `ip_sender` varchar(15) NOT NULL,
  `status` int(1) NOT NULL default '0',
  PRIMARY KEY (`jokeid`),
  KEY `cat` (`cat`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_links` (
  `lid` int(11) NOT NULL auto_increment,
  `cid` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `name` varchar(25) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `bodytext` text NOT NULL,
  `url` varchar(100) NOT NULL,
  `date` datetime default NULL,
  `email` varchar(100) NOT NULL,
  `ip_sender` varchar(15) NOT NULL,
  `counter` int(11) NOT NULL default '0',
  `ihome` int(1) NOT NULL default '0',
  `acomm` int(1) NOT NULL default '0',
  `votes` int(11) NOT NULL default '0',
  `totalvotes` int(11) NOT NULL default '0',
  `totalcomments` int(11) NOT NULL default '0',
  `hits` int(11) NOT NULL default '0',
  `status` int(1) NOT NULL default '0',
  PRIMARY KEY (`lid`),
  KEY `cid` (`cid`),
  KEY `title` (`title`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_media` (
  `id` int(11) NOT NULL auto_increment,
  `cid` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `name` varchar(25) NOT NULL,
  `title` varchar(100) NOT NULL,
  `subtitle` varchar(100) NOT NULL,
  `year` int(11) NOT NULL default '0',
  `director` varchar(100) NOT NULL,
  `roles` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `createdby` varchar(100) NOT NULL,
  `duration` varchar(100) NOT NULL,
  `lang` varchar(100) NOT NULL,
  `note` text NOT NULL,
  `format` varchar(100) NOT NULL,
  `quality` varchar(100) NOT NULL,
  `size` varchar(100) NOT NULL,
  `released` varchar(100) NOT NULL,
  `links` text NOT NULL,
  `date` datetime default NULL,
  `ihome` int(1) NOT NULL default '0',
  `acomm` int(1) NOT NULL default '0',
  `votes` int(11) NOT NULL default '0',
  `totalvotes` int(11) NOT NULL default '0',
  `totalcom` int(11) NOT NULL default '0',
  `hits` int(11) NOT NULL default '0',
  `ip_sender` varchar(15) NOT NULL,
  `status` int(1) NOT NULL default '0',
  PRIMARY KEY (`id`),
  KEY `cid` (`cid`),
  KEY `title` (`title`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_message` (
  `mid` int(11) NOT NULL auto_increment,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `expire` int(7) NOT NULL default '0',
  `active` int(1) NOT NULL default '1',
  `view` int(1) NOT NULL default '1',
  `mlanguage` varchar(30) NOT NULL,
  PRIMARY KEY (`mid`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_modules` (
  `mid` int(11) NOT NULL auto_increment,
  `title` varchar(50) NOT NULL,
  `active` int(1) NOT NULL default '0',
  `view` int(1) NOT NULL default '0',
  `inmenu` tinyint(1) NOT NULL default '1',
  `mod_group` int(10) default '0',
  `blocks` int(1) NOT NULL default '0',
  `blocks_c` int(1) NOT NULL default '0',
  PRIMARY KEY (`mid`),
  KEY `title` (`title`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_pages` (
  `pid` int(11) NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `name` varchar(25) NOT NULL,
  `title` varchar(100) NOT NULL,
  `time` datetime default NULL,
  `hometext` text,
  `bodytext` mediumtext NOT NULL,
  `comments` int(11) NOT NULL default '0',
  `counter` int(11) NOT NULL default '0',
  `ihome` int(1) NOT NULL default '0',
  `acomm` int(1) NOT NULL default '0',
  `score` int(11) NOT NULL default '0',
  `ratings` int(11) NOT NULL default '0',
  `ip_sender` varchar(15) NOT NULL,
  `status` int(1) NOT NULL default '0',
  PRIMARY KEY (`pid`),
  KEY `catid` (`catid`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_partners` (
  `id` int(11) NOT NULL auto_increment,
  `id_user` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  `adres` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `website` varchar(255) NOT NULL,
  `webmoney` varchar(255) NOT NULL,
  `paypal` varchar(255) NOT NULL,
  `regdate` int(10) NOT NULL default '0',
  `rest` int(10) NOT NULL default '0',
  `bek` int(10) NOT NULL default '0',
  `active` tinyint(1) NULL default '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_products` (
  `id` int(11) NOT NULL auto_increment,
  `cid` int(11) NOT NULL default '0',
  `time` datetime default NULL,
  `title` varchar(100) NOT NULL,
  `text` text NOT NULL,
  `bodytext` text NOT NULL,
  `preis` int(11) NOT NULL default '0',
  `vote` int(11) NOT NULL default '0',
  `assoc` text NOT NULL,
  `ihome` int(1) NOT NULL default '0',
  `acomm` int(1) NOT NULL default '0',
  `com` int(11) NOT NULL default '0',
  `count` int(11) NOT NULL default '0',
  `votes` int(11) NOT NULL default '0',
  `totalvotes` int(11) NOT NULL default '0',
  `fix` int(1) NOT NULL default '0',
  `active` int(1) NULL default '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_privat` (
  `id` int(11) NOT NULL auto_increment,
  `uidin` int(11) NOT NULL default '0',
  `uidout` int(11) NOT NULL default '0',
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `date` datetime default NULL,
  `ip_sender` varchar(15) NOT NULL,
  `status` int(1) NOT NULL default '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_rating` (
  `id` int(11) NOT NULL auto_increment,
  `mid` int(11) NOT NULL default '0',
  `modul` varchar(50) NOT NULL,
  `time` varchar(14) NOT NULL,
  `uid` int(11) NOT NULL default '0',
  `host` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mid` (`mid`),
  KEY `modul` (`modul`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_referer` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `uid` int(11) NOT NULL,
  `name` varchar(25) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `referer` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `lid` int(11) NOT NULL default '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

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

CREATE TABLE `{pref}_news` (
  `sid` int(11) NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `name` varchar(25) NOT NULL,
  `title` varchar(100) NOT NULL,
  `time` datetime default NULL,
  `hometext` text,
  `bodytext` text NOT NULL,
  `field` text NOT NULL,
  `vote` int(11) NOT NULL default '0',
  `comments` int(11) default '0',
  `counter` int(11) NOT NULL default '0',
  `ihome` int(1) NOT NULL default '0',
  `acomm` int(1) NOT NULL default '0',
  `score` int(11) NOT NULL default '0',
  `ratings` int(11) NOT NULL default '0',
  `associated` text NOT NULL,
  `ip_sender` varchar(15) NOT NULL,
  `fix` int(1) NOT NULL default '0',
  `status` int(1) NOT NULL default '0',
  PRIMARY KEY (`sid`),
  KEY `catid` (`catid`),
  KEY `counter` (`counter`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_newsletter` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(50) NOT NULL,
  `content` text,
  `mails` mediumtext,
  `send` int(10) NOT NULL default '0',
  `time` datetime default NULL,
  `endtime` datetime default NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_order` (
  `id` int(11) NOT NULL auto_increment,
  `mail` varchar(255) NOT NULL,
  `info` text NOT NULL,
  `com` text NOT NULL,
  `ip` varchar(15) NOT NULL,
  `agent` varchar(255) NOT NULL,
  `date` datetime default NULL,
  `status` int(1) NOT NULL default '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

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

CREATE TABLE `{pref}_users` (
  `user_id` int(11) NOT NULL auto_increment,
  `user_name` varchar(25) NOT NULL,
  `user_rank` varchar(25) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_website` varchar(255) NOT NULL,
  `user_avatar` varchar(255) NOT NULL,
  `user_regdate` datetime NOT NULL,
  `user_occ` varchar(100) default NULL,
  `user_from` varchar(100) default NULL,
  `user_interests` varchar(150) NOT NULL,
  `user_sig` varchar(255) default NULL,
  `user_viewemail` tinyint(1) default NULL,
  `user_password` varchar(32) NOT NULL,
  `user_storynum` tinyint(4) NOT NULL default '10',
  `user_blockon` tinyint(1) NOT NULL default '0',
  `user_block` text NOT NULL,
  `user_theme` varchar(255) NOT NULL,
  `user_newsletter` int(1) NOT NULL default '1',
  `user_fsmail` int(1) NOT NULL default '1',
  `user_psmail` int(1) NOT NULL default '1',
  `user_lastvisit` datetime NOT NULL,
  `user_lang` varchar(255) NOT NULL default 'russian',
  `user_points` int(10) default '0',
  `user_last_ip` varchar(15) NOT NULL,
  `user_warnings` text NOT NULL,
  `user_acess` int(1) NOT NULL default '0',
  `user_group` int(1) NOT NULL default '0',
  `user_birthday` date default NULL,
  `user_gender` int(1) NOT NULL default '0',
  `user_votes` int(11) NOT NULL default '0',
  `user_totalvotes` int(11) NOT NULL default '0',
  `user_field` text NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `user_network` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM;

CREATE TABLE `{pref}_users_temp` (
  `user_id` int(11) NOT NULL auto_increment,
  `user_name` varchar(25) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_password` varchar(25) NOT NULL,
  `user_regdate` datetime NOT NULL,
  `check_num` varchar(50) NOT NULL,
  `time` varchar(14) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM;