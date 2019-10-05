<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("BLOCK_FILE")) {
	header("Location: ../index.php");
	exit;
}

global $prefix, $db;
$strip = 40;
$content = "<table class=\"sl_table_list\"><thead class=\"sl_table_list_head\"><tr><th style=\"width: 50%;\">Новые сайты</th><th>Лучшие сайты</th></tr></thead><tbody class=\"sl_table_list_body\">";

// Last added links
$content .="<tr><td>";
$result = $db->sql_query("SELECT lid, title, description FROM ".$prefix."_links WHERE status != '0' ORDER BY date DESC LIMIT 0,10");
while(list($l_lid, $l_title, $l_description) = $db->sql_fetchrow($result)) {
	$content .= "<table><tr><td><a href=\"index.php?name=links&amp;op=view&amp;id=".$l_lid."\" title=\"".text_filter(cutstr(bb_decode($l_description, "links"), 250), 1)."\">".cutstr($l_title, $strip)."</a></td></tr></table>";
}

// Last best links
$content .="</td><td>";
$result = $db->sql_query("SELECT lid, title, description FROM ".$prefix."_links WHERE status != '0' ORDER BY totalvotes DESC LIMIT 0,10");
while(list($l_lid, $l_title, $l_description) = $db->sql_fetchrow($result)) {
	$content .= "<table><tr><td><a href=\"index.php?name=links&amp;op=view&amp;id=".$l_lid."\" title=\"".text_filter(cutstr(bb_decode($l_description, "links"), 250), 1)."\">".cutstr($l_title, $strip)."</a></td></tr></table>";
}
$content .= "</td></tr></tbody></table>";
?>