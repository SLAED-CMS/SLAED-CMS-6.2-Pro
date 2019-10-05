<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("BLOCK_FILE")) {
	header("Location: ../index.php");
	exit;
}

global $prefix, $db;
$strip = 20;
$result = $db->sql_query("SELECT jokeid, title FROM ".$prefix."_jokes WHERE date <= now() AND status != '0' ORDER BY date DESC LIMIT 5");
while(list($jokeid, $title) = $db->sql_fetchrow($result)) {
	$linkstrip = cutstr($title, $strip);
	$content .= "<table class=\"sl_table_block\"><tr><td><a href=\"index.php?name=jokes#".$jokeid."\" title=\"".$title."\">".$linkstrip."</a></td></tr></table>";
}
?>