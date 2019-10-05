<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("BLOCK_FILE")) {
	header("Location: ../index.php");
	exit;
}

global $prefix, $db;
$strip = 40;
$content = "<table class=\"sl_table_list\"><thead class=\"sl_table_list_head\"><tr><th style=\"width: 50%;\">"._JOKES."</th><th>"._FAQ."</th></tr></thead><tbody class=\"sl_table_list_body\">";

// Last added jokes
$content .="<tr><td>";
$result = $db->sql_query("SELECT jokeid, title FROM ".$prefix."_jokes WHERE date <= now() AND status != '0' ORDER BY date DESC LIMIT 5");
while(list($jokeid, $title) = $db->sql_fetchrow($result)) {
	$linkstrip = cutstr($title, $strip);
	$content .= "<table><tr><td><a href=\"index.php?name=jokes#".$jokeid."\" title=\"".$title."\">".$linkstrip."</a></td></tr></table>";
}

// Last added faq
$content .="</td><td>";
$result = $db->sql_query("SELECT fid, title FROM ".$prefix."_faq WHERE time <= now() AND status != '0' ORDER BY time DESC LIMIT 5");
while(list($fid, $title) = $db->sql_fetchrow($result)) {
	$linkstrip = cutstr($title, $strip);
	$content .= "<table><tr><td><a href=\"index.php?name=faq&amp;op=view&amp;id=".$fid."\" title=\"".$title."\">".$linkstrip."</a></td></tr></table>";
}
$content .= "</td></tr></tbody></table>";
?>