<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("MODULE_FILE")) {
	header("Location: ../../index.php");
	exit;
}

include("config/config_content.php");

function content() {
	global $prefix, $db, $admin_file, $conf, $confcn;
	head($conf['defis']." "._CONTENT);
	$cont = tpl_eval("title", _CONTENT);
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $confcn['num'];
	$result = $db->sql_query("SELECT id, title, time, counter FROM ".$prefix."_content WHERE time <= now() ORDER BY time DESC LIMIT ".$offset.", ".$confcn['num']);
	if ($db->sql_numrows($result) > 0) {
		$cont .= tpl_eval("open");
		$cont .= "<table class=\"sl_table_list_sort\"><thead class=\"sl_table_list_head\"><tr><th>"._ID."</th><th>"._TITLE."</th><th>"._FUNCTIONS."</th></tr></thead><tbody class=\"sl_table_list_body\">";
		while (list($id, $title, $time, $counter)= $db->sql_fetchrow($result)) {
			$moder = (is_moder($conf['name'])) ? "<a href=\"".$admin_file.".php?op=content_add&amp;id=".$id."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=content_delete&amp;id=".$id."&amp;refer=1\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$title."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>||" : "";
			$edit = add_menu($moder."<a href=\"index.php?name=content&amp;op=view&amp;id=".$id."\" title=\""._SHOW."\">"._SHOW."</a>");
			$cont .= "<tr id=\"".$id."\">"
			."<td><a href=\"#".$id."\" title=\"".$id."\" class=\"sl_pnum\">".$id."</a></td>"
			."<td>".title_tip(_DATE.": ".format_time($time, _TIMESTRING)."<br>"._READS.": ".$counter)."<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."\" title=\"".$title."\">".$title."</a> ".new_graphic($time)."</td>"
			."<td>".$edit."</td></tr>";
		}
		$cont .= "</tbody></table>";
		$cont .= num_article("pagenum", $conf['name'], $confcn['num'], "op=content&amp;", "id", "_content", "", "", $confcn['nump']);
		$cont .= tpl_eval("close");
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function view() {
	global $db, $prefix, $conf, $confn, $admin_file;
	$id = (isset($_GET['id'])) ? intval($_GET['id']) : 0;
	$pag = (isset($_GET['pag'])) ? intval($_GET['pag']) : 0;
	$word = (isset($_GET['word'])) ? text_filter($_GET['word']) : "";
	$result = $db->sql_query("SELECT id, title, text, field, url, time, refresh FROM ".$prefix."_content WHERE id = '".$id."' AND time <= now()");
	if ($db->sql_numrows($result) == 1) {
		$db->sql_query("UPDATE ".$prefix."_content SET counter = counter+1 WHERE id = '".$id."'");
		list($id, $title, $text, $field, $url, $time, $refresh) = $db->sql_fetchrow($result);
		if ($url) {
			$past = time() - $refresh;
			if (strtotime($time) < $past) {
				$content = rss_read($url, 1);
				$db->sql_query("UPDATE ".$prefix."_content SET text = '".$content."', time = now() WHERE id = '".$id."'");
			}
		}
		$fields = fields_out($field, $conf['name']);
		$fields = ($fields) ? "<br><br>".$fields : "";
		$hometext = $text.$fields;
		head($conf['defis']." "._CONTENT." ".$conf['defis']." ".$title, $hometext);
		echo tpl_eval("title", $title).tpl_eval("open").search_color(bb_decode($hometext, $conf['name']), $word).tpl_eval("close");
		foot();
	} else {
		header("Location: index.php?name=".$conf['name']);
	}
}

switch($op) {
	default:
	content();
	break;
	
	case "view":
	view();
	break;
}
?>