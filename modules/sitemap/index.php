<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined('MODULE_FILE')) {
	header('Location: ../../index.php');
	exit;
}

function sitemap() {
	global $conf;
	head($conf['defis']." "._SITEMAP);
	$cont = tpl_eval("title", _SITEMAP);
	if (file_exists('config/sitemap/sitemap.txt')) {
		$cont .= tpl_eval('open').file_get_contents('config/sitemap/sitemap.txt').tpl_eval('close');
	} else {
		$cont .= tpl_warn('warn', _NO_INFO, '', '', 'info');
	}
	echo $cont;
	foot();
}

switch($op) {
	default:
	sitemap();
	break;
}
?>