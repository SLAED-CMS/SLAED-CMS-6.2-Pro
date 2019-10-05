# Copyright © 2005 - 2012 SLAED
# Website: http://www.slaed.net

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