<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("ADMIN_FILE") || !is_admin_modul("forum")) die("Illegal file access");

include("config/config_forum.php");

function forum_navi() {
	panel();
	$narg = func_get_args();
	$ops = array("forum_synch", "forum_conf", "forum_info");
	$lang = array(_SYNCH, _PREFERENCES, _INFO);
	return navi_gen(_FORUM, "forum.png", "", $ops, $lang, "", "", $narg[0], $narg[1], $narg[2], $narg[3]);
}

function forum_synch() {
	global $prefix, $db;
	$db->sql_query("UPDATE ".$prefix."_categories SET topics = '0', posts = '0', lpost_id = '0' WHERE modul = 'forum'");
	$result = $db->sql_query("SELECT id, parentid FROM ".$prefix."_categories WHERE modul = 'forum' ORDER BY ordern");
	while (list($id, $parentid) = $db->sql_fetchrow($result)) $massiv[$id] = array($parentid);
	foreach ($massiv as $key => $val) {
		list($topics) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_forum WHERE pid = '0' AND catid = '".$key."'"));
		list($posts) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_forum WHERE pid != '0' AND catid = '".$key."'"));
		list($id, $pid) = $db->sql_fetchrow($db->sql_query("SELECT id, pid FROM ".$prefix."_forum WHERE catid = '".$key."' AND ((pid != '0' && status = '1') || (pid = '0' && status > '1')) ORDER BY id DESC LIMIT 1"));
		$lid = ($pid) ? $pid : $id;
		$db->sql_query("UPDATE ".$prefix."_categories SET topics = '".$topics."', posts = '".$posts."', lpost_id = '".$lid."' WHERE id = '".$key."' AND modul = 'forum'");
		$flag = $val[0];
		while ($flag != 0) {
			$db->sql_query("UPDATE ".$prefix."_categories SET topics = topics+".$topics.", posts = posts+".$posts.", lpost_id = '".$lid."' WHERE id = '".$flag."' AND modul = 'forum'");
			$flag = intval($massiv[$flag][0]);
		}
	}
	head();
	$cont = forum_navi(0, 0, 0, 0);
	$cont .= tpl_warn("warn", _SYNCHIN, "", "", "info");
	$cont .= tpl_eval("open");
	$cont .= "<table class=\"sl_table_list_sort\"><thead><tr><th>"._ID."</th><th>"._FORUM."</th><th>"._NEWTOPICS."</th><th>"._MESSAGES."</th><th class=\"{sorter: false}\">"._STATUS."</th></tr></thead><tbody>";
	$result = $db->sql_query("SELECT id, title, description, cstatus, topics, posts FROM ".$prefix."_categories WHERE modul = 'forum' ORDER BY ordern");
	while (list($id, $title, $description, $cstatus, $topics, $posts) = $db->sql_fetchrow($result)) {
		$descript = ($description) ? $description : _NO;
		$ltitle = title_tip(_DESCRIPTION.": ".$descript)."<a href=\"index.php?name=forum&amp;cat=".$id."\" target=\"_blank\" title=\"".$title."\" class=\"sl_note\">".cutstr($title, 60)."</a>";
		$cont .= "<tr><td>".$id."</td><td>".$ltitle."</td><td>".$topics."</td><td>".$posts."</td><td>".ad_status("", $cstatus)."</td></tr>";
	}
	$cont .= "</tbody></table>";
	$cont .= tpl_eval("close");
	echo $cont;
	foot();
}

function forum_conf() {
	global $admin_file, $conffo;
	head();
	$cont = forum_navi(0, 1, 0, 0);
	$cont .= tpl_warn("warn", _SYNCHINF, "", "", "info");
	$permtest = end_chmod("config/config_forum.php", 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_conf\">"
	."<tr><td>"._CDEFIS.":</td><td><input type=\"text\" name=\"defis\" value=\"".urldecode($conffo['defis'])."\" maxlength=\"25\" class=\"sl_conf\" placeholder=\""._CDEFIS."\" required></td></tr>"
	."<tr><td>"._FO_1.":</td><td><input type=\"number\" name=\"listnum\" value=\"".$conffo['listnum']."\" class=\"sl_conf\" placeholder=\""._FO_1."\" required></td></tr>"
	."<tr><td>"._FO_2.":</td><td><input type=\"number\" name=\"pop\" value=\"".$conffo['pop']."\" class=\"sl_conf\" placeholder=\""._FO_2."\" required></td></tr>"
	."<tr><td>"._COMLETTER.":</td><td><input type=\"number\" name=\"letter\" value=\"".$conffo['letter']."\" class=\"sl_conf\" placeholder=\""._COMLETTER."\" required></td></tr>"
	."<tr><td>"._C_33.":</td><td><input type=\"number\" name=\"num\" value=\"".$conffo['num']."\" class=\"sl_conf\" placeholder=\""._C_33."\" required></td></tr>"
	."<tr><td>"._C_35.":</td><td><input type=\"number\" name=\"pnum\" value=\"".$conffo['pnum']."\" class=\"sl_conf\" placeholder=\""._C_35."\" required></td></tr>"
	."<tr><td>"._FO_5.":</td><td>".getcat("forum", $conffo['recycle'], "recycle", "sl_conf", "<option value=\"0\">"._NO."</option>")."</td></tr>"
	."<tr><td>"._SORT.":</td><td><select name=\"sort\" class=\"sl_conf\">"
	."<option value=\"1\"";
	if ($conffo['sort'] == "1") $cont .= " selected";
	$cont .= ">"._ASC."</option>"
	."<option value=\"0\"";
	if ($conffo['sort'] == "0") $cont .= " selected";
	$cont .= ">"._DESC."</option>"
	."</select></td></tr>"
	."<tr><td>"._ALLOWANONPOST."<div class=\"sl_small\">"._FO_6."</div></td><td><select name=\"anonpost\" class=\"sl_conf\">"
	."<option value=\"0\"";
	if ($conffo['anonpost'] == "0") $cont .= " selected";
	$cont .= ">"._APOSTMOD."</option>"
	."<option value=\"1\"";
	if ($conffo['anonpost'] == "1") $cont .= " selected";
	$cont .= ">"._APOSTNOMOD."</option>"
	."</select></td></tr>"
	."<tr><td>"._FO_7."</td><td>".radio_form($conffo['add'], "add")."</td></tr>"
	."<tr><td>"._FO_8."</td><td>".radio_form($conffo['qreply'], "qreply")."</td></tr>"
	."<tr><td>"._FO_9."</td><td>".radio_form($conffo['ledit'], "ledit")."</td></tr>"
	."<tr><td>"._FO_10."</td><td>".radio_form($conffo['addmail'], "addmail")."</td></tr>"
	."<tr><td>"._VPRIVAT."</td><td>".radio_form($conffo['privat'], "privat")."</td></tr>"
	."<tr><td>"._VPROFIL."</td><td>".radio_form($conffo['profil'], "profil")."</td></tr>"
	."<tr><td>"._VWEB."</td><td>".radio_form($conffo['web'], "web")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"forum_conf_save\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close");
	echo $cont;
	foot();
}

function forum_conf_save() {
	global $admin_file;
	$xdefis = ($_POST['defis']) ? urlencode($_POST['defis']) : "%3E";
	$content = "\$conffo = array();\n"
	."\$conffo['defis'] = \"".$xdefis."\";\n"
	."\$conffo['listnum'] = \"".$_POST['listnum']."\";\n"
	."\$conffo['pop'] = \"".$_POST['pop']."\";\n"
	."\$conffo['letter'] = \"".$_POST['letter']."\";\n"
	."\$conffo['num'] = \"".$_POST['num']."\";\n"
	."\$conffo['pnum'] = \"".$_POST['pnum']."\";\n"
	."\$conffo['recycle'] = \"".$_POST['recycle']."\";\n"
	."\$conffo['sort'] = \"".$_POST['sort']."\";\n"
	."\$conffo['anonpost'] = \"".$_POST['anonpost']."\";\n"
	."\$conffo['add'] = \"".$_POST['add']."\";\n"
	."\$conffo['qreply'] = \"".$_POST['qreply']."\";\n"
	."\$conffo['ledit'] = \"".$_POST['ledit']."\";\n"
	."\$conffo['addmail'] = \"".$_POST['addmail']."\";\n"
	."\$conffo['privat'] = \"".$_POST['privat']."\";\n"
	."\$conffo['profil'] = \"".$_POST['profil']."\";\n"
	."\$conffo['web'] = \"".$_POST['web']."\";\n";
	save_conf("config/config_forum.php", $content);
	header("Location: ".$admin_file.".php?op=forum_conf");
}

function forum_info() {
	head();
	echo forum_navi(0, 2, 0, 0)."<div id=\"repadm_info\">".adm_info(1, "forum", 0)."</div>";
	foot();
}

switch($op) {
	case "forum_synch":
	forum_synch();
	break;
	
	case "forum_conf":
	forum_conf();
	break;
	
	case "forum_conf_save":
	forum_conf_save();
	break;
	
	case "forum_info":
	forum_info();
	break;
}
?>