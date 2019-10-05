# Copyright © 2005 - 2012 SLAED
# Website: http://www.slaed.net

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