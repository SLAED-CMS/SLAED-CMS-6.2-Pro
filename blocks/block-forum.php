<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("BLOCK_FILE")) {
	header("Location: ../index.php");
	exit;
}

global $prefix, $db;

# Количество сообщений в блоке
$blimit = "3";
# Закрытые форумы, сообщения которых не будут показаны в блоке
$bclos = "97, 98";

$bwhere = ($bclos) ? "catid NOT IN (".$bclos.") AND" : "";
$ordern = (is_moder("forum")) ? "" : "AND time <= now() AND status > '1'";
$buffer = "";
$result = $db->sql_query("SELECT id, title, time, l_uid, l_name, l_id, l_time, status FROM ".$prefix."_forum WHERE ".$bwhere." pid = '0' ".$ordern." ORDER BY l_time DESC LIMIT 0, ".$blimit);
while (list($id, $title, $time, $l_uid, $l_name, $l_id, $l_time, $status) = $db->sql_fetchrow($result)) {
	$lposter = ($l_uid) ? user_info($l_name) : $l_name;
	$class = ($status <= 1 || $time > date("Y-m-d H:i:s")) ? " class=\"sl_hidden\"" : "";
	$buffer .= "<li".$class."><a href=\"index.php?name=forum&amp;op=view&amp;id=".$id."&amp;last#".$l_id."\" title=\"".$title."\">".cutstr($title, 50)."</a><ul><li title=\""._POSTEDBY."\" class=\"sl_post\">".$lposter."</li><li title=\""._DATE.": ".format_time($l_time, _TIMESTRING)."\" class=\"ico i_date\">".format_time($l_time)."</li></ul></li>";
}
$content = "<div class=\"grid\"><p title=\""._FORUM."\" class=\"font f_title\">"._FORUM."</p><ul class=\"list-item\">".$buffer."</ul></div>";
?>