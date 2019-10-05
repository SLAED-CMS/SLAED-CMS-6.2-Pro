<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("ADMIN_FILE") || !is_admin_modul("jokes")) die("Illegal file access");

include("config/config_jokes.php");

function jokes_navi() {
	panel();
	$narg = func_get_args();
	$ops = array("jokes", "jokes_add", "jokes&amp;status=1", "jokes_conf", "jokes_info");
	$lang = array(_HOME, _ADD, _NEW, _PREFERENCES, _INFO);
	return navi_gen(_JOKES, "jokes.png", "", $ops, $lang, "", "", $narg[0], $narg[1], $narg[2], $narg[3]);
}

function jokes() {
	global $prefix, $db, $admin_file, $conf, $confj, $confu;
	head();
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $confj['anum'];
	$offset = intval($offset);
	if ($_GET['status'] == 1) {
		$status = "0";
		$field = "op=jokes&amp;status=1&amp;";
		$refer = "&amp;refer=1";
		$cont = jokes_navi(0, 2, 0, 0);
	} else {
		$status = "1";
		$field = "op=jokes&amp;";
		$refer = "";
		$cont = jokes_navi(0, 0, 0, 0);
	}
	$result = $db->sql_query("SELECT j.jokeid, j.name, j.date, j.title, j.cat, j.ip_sender, c.title, u.user_name FROM ".$prefix."_jokes AS j LEFT JOIN ".$prefix."_categories AS c ON (j.cat = c.id) LEFT JOIN ".$prefix."_users AS u ON (j.uid = u.user_id) WHERE j.status = '".$status."' ORDER BY j.date DESC LIMIT ".$offset.", ".$confj['anum']);
	if ($db->sql_numrows($result) > 0) {
		$cont .= tpl_eval("open");
		$cont .= "<table class=\"sl_table_list_sort\"><thead><tr><th>"._ID."</th><th>"._TITLE."</th><th>"._POSTEDBY."</th><th class=\"{sorter: false}\">"._STATUS."</th><th class=\"{sorter: false}\">"._FUNCTIONS."</th></tr></thead><tbody>";
		while (list($jokeid, $uname, $date, $title, $cat, $ip_sender, $ctitle, $user_name) = $db->sql_fetchrow($result)) {
			$ctitle = ($cat) ? $ctitle : _NO;
			$ip_sender = ($ip_sender) ? user_geo_ip($ip_sender, 4) : _NO;
			$post = ($user_name) ? user_info($user_name) : (($uname) ? $uname : $confu['anonym']);
			if ($status && time() >= strtotime($date)) {
				$ad_view = "<a href=\"index.php?name=jokes&amp;cat=".$cat."#".$jokeid."\" title=\""._MVIEW."\">"._MVIEW."</a>||";
				$active = "1";
			} else {
				$ad_view = "";
				$active = "0";
			}
			$cont .= "<tr><td>".$jokeid."</td>"
			."<td>".title_tip(_CATEGORY.": ".$ctitle."<br>"._DATE.": ".format_time($date, _TIMESTRING)."<br>"._IP.": ".$ip_sender)."<span title=\"".$title."\" class=\"sl_note\">".cutstr($title, 60)."</span></td>"
			."<td>".$post."</td>"
			."<td>".ad_status("", $active)."</td>"
			."<td>".add_menu($ad_view."<a href=\"".$admin_file.".php?op=jokes_add&amp;id=".$jokeid."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=jokes_delete&amp;id=".$jokeid.$refer."\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$title."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>")."</td></tr>";
		}
		$cont .= "</tbody></table>";
		$cont .= num_article("pagenum", "", $confj['anum'], $field, "jokeid", "_jokes", "", "status = '".$status."'", $confj['anump']);
		$cont .= tpl_eval("close");
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function jokes_add() {
	global $prefix, $db, $admin_file, $confu, $stop;
	if (isset($_REQUEST['id'])) {
		$jokeid = intval($_REQUEST['id']);
		$result = $db->sql_query("SELECT j.jokeid, j.name, j.date, j.title, j.cat, j.joke, u.user_name FROM ".$prefix."_jokes AS j LEFT JOIN ".$prefix."_users AS u ON (j.uid = u.user_id) WHERE jokeid = '".$jokeid."'");
		list($jokeid, $uname, $date, $title, $cat, $joke, $user_name) = $db->sql_fetchrow($result);
		$postname = ($user_name) ? $user_name : (($uname) ? $uname : $confu['anonym']);
	} else {
		$jokeid = $_POST['jokeid'];
		$postname = $_POST['postname'];
		$date = save_datetime(1, "date");
		$title = save_text($_POST['title'], 1);
		$cat = $_POST['cat'];
		$joke = save_text($_POST['joke']);
	}
	head();
	$cont = jokes_navi(0, 1, 0, 0);
	if ($stop) $cont .= tpl_warn("warn", $stop, "", "", "warn");
	if ($joke) $cont .= preview($title, $joke, "", "", "all");
	$cont .= tpl_eval("open");
	$cont .= "<form name=\"post\" action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_form\">"
	."<tr><td>"._POSTEDBY.":</td><td>".get_user_search("postname", $postname, "25", "sl_form", "1")."</td></tr>"
	."<tr><td>"._TITLE.":</td><td><input type=\"text\" name=\"title\" value=\"".$title."\" maxlength=\"100\" class=\"sl_form\" placeholder=\""._TITLE."\" required></td></tr>"
	."<tr><td>"._CATEGORY.":</td><td>".getcat("jokes", $cat, "cat", "sl_form", "<option value=\"\">"._HOMECAT."</option>")."</td></tr>"
	."<tr><td>"._JOKE.":</td><td>".textarea("1", "joke", $joke, "jokes", "10", _JOKE, "1")."</td></tr>"
	."<tr><td>"._CHNGSTORY.":</td><td>".datetime(1, "date", $date, 16, "sl_form")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\">".ad_save("jokeid", $jokeid, "jokes_save")."</td></tr></table></form>";
	$cont .= tpl_eval("close");
	echo $cont;
	foot();
}

function jokes_save() {
	global $prefix, $db, $admin_file, $stop;
	$jokeid = intval($_POST['jokeid']);
	$postname = $_POST['postname'];
	$date = save_datetime(1, "date");
	$title = save_text($_POST['title'], 1);
	$cat = $_POST['cat'];
	$joke = save_text($_POST['joke']);
	$stop = array();
	if (!$title) $stop[] = _CERROR;
	if (!$joke) $stop[] = _CERROR1;
	if (!$postname) $stop[] = _CERROR3;
	if (!$jokeid && $db->sql_numrows($db->sql_query("SELECT title FROM ".$prefix."_jokes WHERE title = '".$title."'")) > 0) $stop[] = _JOKEEXIST;
	if (!$stop && $_POST['posttype'] == "save") {
		$postid = (is_user_id($postname)) ? is_user_id($postname) : "";
		$postname = (!is_user_id($postname)) ? text_filter(substr($postname, 0, 25)) : "";
		if ($jokeid) {
			$db->sql_query("UPDATE ".$prefix."_jokes SET uid = '".$postid."', name = '".$postname."', date = '".$date."', title = '".$title."', cat = '".$cat."', joke = '".$joke."', status = '1' WHERE jokeid = '".$jokeid."'");
		} else {
			$ip = getip();
			$db->sql_query("INSERT INTO ".$prefix."_jokes (jokeid, uid, name, date, title, cat, joke, ip_sender, status) VALUES (NULL, '".$postid."', '".$postname."', '".$date."', '".$title."', '".$cat."', '".$joke."', '".$ip."', '1')");
		}
		header("Location: ".$admin_file.".php?op=jokes");
	} elseif ($_POST['posttype'] == "delete") {
		jokes_delete($jokeid);
	} else {
		jokes_add();
	}
}

function jokes_delete() {
	global $prefix, $db, $admin_file, $id;
	$arg = func_get_args();
	$id = ($arg[0]) ? $arg[0] : $id;
	if ($id) {
		$db->sql_query("DELETE FROM ".$prefix."_favorites WHERE fid = '".$id."' AND modul = 'jokes'");
		$db->sql_query("DELETE FROM ".$prefix."_jokes WHERE jokeid = '".$id."'");
	}
	referer($admin_file.".php?op=jokes");
}

function jokes_conf() {
	global $admin_file, $confj;
	head();
	$cont = jokes_navi(0, 3, 0, 0);
	$permtest = end_chmod("config/config_jokes.php", 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_conf\">"
	."<tr><td>"._CDEFIS.":</td><td><input type=\"text\" name=\"defis\" value=\"".urldecode($confj['defis'])."\" maxlength=\"25\" class=\"sl_conf\" placeholder=\""._CDEFIS."\" required></td></tr>"
	."<tr><td>"._C_10.":</td><td><input type=\"number\" name=\"tabcol\" value=\"".$confj['tabcol']."\" class=\"sl_conf\" placeholder=\""._C_10."\" required></td></tr>"
	."<tr><td>"._C_33.":</td><td><input type=\"number\" name=\"num\" value=\"".$confj['num']."\" class=\"sl_conf\" placeholder=\""._C_33."\" required></td></tr>"
	."<tr><td>"._C_34.":</td><td><input type=\"number\" name=\"anum\" value=\"".$confj['anum']."\" class=\"sl_conf\" placeholder=\""._C_34."\" required></td></tr>"
	."<tr><td>"._C_35.":</td><td><input type=\"number\" name=\"nump\" value=\"".$confj['nump']."\" class=\"sl_conf\" placeholder=\""._C_35."\" required></td></tr>"
	."<tr><td>"._C_36.":</td><td><input type=\"number\" name=\"anump\" value=\"".$confj['anump']."\" class=\"sl_conf\" placeholder=\""._C_36."\" required></td></tr>"
	."<tr><td>"._HOMCAT."</td><td>".radio_form($confj['homcat'], "homcat")."</td></tr>"
	."<tr><td>"._C_32."</td><td>".radio_form($confj['catdesc'], "catdesc")."</td></tr>"
	."<tr><td>"._C_15."</td><td>".radio_form($confj['subcat'], "subcat")."</td></tr>"
	."<tr><td>"._ADDAMAIL."</td><td>".radio_form($confj['addmail'], "addmail")."</td></tr>"
	."<tr><td>"._J_1."</td><td>".radio_form($confj['add'], "add")."</td></tr>"
	."<tr><td>"._J_2."</td><td>".radio_form($confj['addquest'], "addquest")."</td></tr>"
	."<tr><td>"._C_17."</td><td>".radio_form($confj['date'], "date")."</td></tr>"
	."<tr><td>"._C_19."</td><td>".radio_form($confj['rate'], "rate")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"jokes_conf_save\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close");
	echo $cont;
	foot();
}

function jokes_conf_save() {
	global $admin_file;
	$xdefis = ($_POST['defis']) ? urlencode($_POST['defis']) : "%3E";
	$content = "\$confj = array();\n"
	."\$confj['defis'] = \"".$xdefis."\";\n"
	."\$confj['tabcol'] = \"".$_POST['tabcol']."\";\n"
	."\$confj['num'] = \"".$_POST['num']."\";\n"
	."\$confj['anum'] = \"".$_POST['anum']."\";\n"
	."\$confj['nump'] = \"".$_POST['nump']."\";\n"
	."\$confj['anump'] = \"".$_POST['anump']."\";\n"
	."\$confj['homcat'] = \"".$_POST['homcat']."\";\n"
	."\$confj['catdesc'] = \"".$_POST['catdesc']."\";\n"
	."\$confj['subcat'] = \"".$_POST['subcat']."\";\n"
	."\$confj['addmail'] = \"".$_POST['addmail']."\";\n"
	."\$confj['add'] = \"".$_POST['add']."\";\n"
	."\$confj['addquest'] = \"".$_POST['addquest']."\";\n"
	."\$confj['date'] = \"".$_POST['date']."\";\n"
	."\$confj['rate'] = \"".$_POST['rate']."\";\n";
	save_conf("config/config_jokes.php", $content);
	header("Location: ".$admin_file.".php?op=jokes_conf");
}

function jokes_info() {
	head();
	echo jokes_navi(0, 4, 0, 0)."<div id=\"repadm_info\">".adm_info(1, "jokes", 0)."</div>";
	foot();
}

switch ($op) {
	case "jokes":
	jokes();
	break;
	
	case "jokes_add":
	jokes_add();
	break;
	
	case "jokes_save":
	jokes_save();
	break;
	
	case "jokes_delete":
	jokes_delete();
	break;
	
	case "jokes_conf":
	jokes_conf();
	break;
	
	case "jokes_conf_save":
	jokes_conf_save();
	break;
	
	case "jokes_info":
	jokes_info();
	break;
}
?>