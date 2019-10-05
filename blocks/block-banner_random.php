<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined('BLOCK_FILE')) {
	header('Location: ../index.php');
	exit;
}

$path = img_find('banners/random');
$dir = opendir($path);
while (false !== ($file = readdir($dir))) {
	if ($file != '.' && $file != '..' && $file != 'index.html' && !is_dir($path.'/'.$file)) $ban[] = $file;
}
closedir($dir);
$i = mt_rand(0, count($ban) - 1);
$url = preg_split('#-#', $ban[$i]); 
$content = '<a href="https://'.str_replace(array('_', '+'), array('/', '?'), $url[0]).'" target="_blank" title="SLAED CMS"><img src="'.img_find('banners/random/'.$ban[$i]).'" style="width: 468px; height: 60px; border: 0;" alt="SLAED CMS"></a>';
?>