<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("BLOCK_FILE")) {
	header("Location: ../index.php");
	exit;
}

if (file_exists("forum/conf_global.php")) {
	global $db;
	include_once("forum/conf_global.php");
	$prefix_ipb = $INFO['sql_tbl_prefix'];
	$buffer = "";
	$result = $db->sql_query("SELECT tid, posts, title, description, starter_name, last_poster_name, views FROM ".$prefix_ipb."topics ORDER BY last_post DESC LIMIT 0, 15");
	while (list($tid, $posts, $title, $description, $starter_name, $last_poster_name, $views) = $db->sql_fetchrow($result)) {
		$post_text = ($description) ? $title." - ".$description : $title;
		$buffer .= "<tr><td><a href=\"forum/index.php?showtopic=".$tid."&amp;view=getnewpost\" title=\"".$post_text."\">".cutstr($title, 50)."</a></td>"
		."<td style=\"text-align: center;\">".user_info($starter_name)."</td>"
		."<td style=\"text-align: center;\">".$views."</td><td style=\"text-align: center;\">".$posts."</td>"
		."<td style=\"text-align: center;\">".user_info($last_poster_name)."</td></tr>";
	}
	$content .= "<table class=\"sl_table_list\"><thead><tr><th>"._NEWTOPICS."</th><th>"._POSTER."</th><th>"._VIEWS."</th><th>"._REPLIES."</th><th>"._LASTPOSTER."</th></tr></thead><tbody>".$buffer."</tbody></table>";
} else {
	$content = "";
}
?>