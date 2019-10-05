<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("BLOCK_FILE")) {
	header("Location: ../index.php");
	exit;
}

global $prefix, $db;
$strip = 20;
$result = $db->sql_query("SELECT pid, title FROM ".$prefix."_pages WHERE time <= now() AND status != '0' ORDER BY time DESC LIMIT 5");
while(list($pid, $title) = $db->sql_fetchrow($result)) {
	$linkstrip = cutstr($title, $strip);
	$content .= "<table class=\"sl_table_block\"><tr><td><a href=\"index.php?name=pages&amp;op=view&amp;id=".$pid."\" title=\"".$title."\">".$linkstrip."</a></td></tr></table>";
}
?>