<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("BLOCK_FILE")) {
	header("Location: ../index.php");
	exit;
}

$content = '<img src="index.php?stat=1&amp;img=2" style="width: 80px; height: 25px; border: 0; vertical-align: bottom;" alt="Счетчик посещений страниц и уникальных посетителей в сутки" title="Счетчик посещений страниц и уникальных посетителей в сутки"> <a href="http://www.slaed.net" target="_blank" title="SLAED CMS - Content Management System"><img src="'.img_find("banners/slaed_3_2.gif").'" style="width: 80px; height: 25px; border: 0; vertical-align: bottom;" alt="SLAED CMS - Content Management System"></a> <a href="index.php?go=rss&amp;num=50" target="_blank" title="Экспорт новостей в формате RSS"><img src="'.img_find("banners/rss_2.gif").'" style="width: 80px; height: 25px; border: 0; vertical-align: bottom;" alt="Экспорт новостей в формате RSS"></a>';
?>