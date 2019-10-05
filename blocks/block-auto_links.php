<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: http://www.slaed.net

if (!defined("BLOCK_FILE")) {
	header("Location: ../index.php");
	exit;
}

include("config/config_auto_links.php");
global $prefix, $db;
$content = "";
$result = $db->sql_query("SELECT id, sitename, description FROM ".$prefix."_auto_links WHERE hits != '0' ORDER BY hits DESC LIMIT 0,".intval($confal['limit'])."");
while(list($a_id, $a_site, $a_description) = $db->sql_fetchrow($result)) {
	$a_site = cutstr($a_site, $confal['strip']);
	$title = text_filter(cutstr(bb_decode($a_description, ""), 250), 1);
	$content .= "<table class=\"sl_table_block\"><tr><td><a href=\"index.php?name=auto_links&amp;op=view&amp;id=".$a_id."\" target=\"_blank\" title=\"".$title."\" >".$a_site."</a></td></tr></table>";
}
$content .= "<p class=\"sl_center\"><a href=\"index.php?name=auto_links&amp;op=add\" title=\""._A_LINKS."\" class=\"sl_but_blue\">"._ADD."</a></p>";
?>