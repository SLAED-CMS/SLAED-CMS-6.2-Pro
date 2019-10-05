<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("ADMIN_FILE") || !is_admin_modul("pages")) die("Illegal file access");

include("config/config_pages.php");

function page_navi() {
	panel();
	$narg = func_get_args();
	$ops = array("page", "page_add", "page&amp;status=1", "page_conf", "page_info");
	$lang = array(_HOME, _ADD, _NEW, _PREFERENCES, _INFO);
	return navi_gen(_PAGES, "pages.png", "", $ops, $lang, "", "", $narg[0], $narg[1], $narg[2], $narg[3]);
}

function page() {
	global $prefix, $db, $admin_file, $confp, $confu;
	head();
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $confp['anum'];
	$offset = intval($offset);
	if ($_GET['status'] == 1) {
		$status = "0";
		$field = "op=page&amp;status=1&amp;";
		$refer = "&amp;refer=1";
		$cont = page_navi(0, 2, 0, 0);
	} else {
		$status = "1";
		$field = "op=page&amp;";
		$refer = "";
		$cont = page_navi(0, 0, 0, 0);
	}
	$result = $db->sql_query("SELECT p.pid, p.catid, p.name, p.title, p.time, p.ip_sender, t.title, u.user_name FROM ".$prefix."_pages AS p LEFT JOIN ".$prefix."_categories AS t ON (p.catid = t.id) LEFT JOIN ".$prefix."_users AS u ON (p.uid = u.user_id) WHERE p.status = '".$status."' ORDER BY p.time DESC LIMIT ".$offset.", ".$confp['anum']);
	if ($db->sql_numrows($result) > 0) {
		$cont .= tpl_eval("open");
		$cont .= "<table class=\"sl_table_list_sort\"><thead><tr><th>"._ID."</th><th>"._TITLE."</th><th>"._POSTEDBY."</th><th class=\"{sorter: false}\">"._STATUS."</th><th class=\"{sorter: false}\">"._FUNCTIONS."</th></tr></thead><tbody>";
		while (list($pid, $catid, $uname, $title, $time, $ip_sender, $ctitle, $user_name) = $db->sql_fetchrow($result)) {
			$ctitle = ($catid) ? $ctitle : _NO;
			$ip_sender = ($ip_sender) ? user_geo_ip($ip_sender, 4) : _NO;
			$post = ($user_name) ? user_info($user_name) : (($uname) ? $uname : $confu['anonym']);
			if ($status && time() >= strtotime($time)) {
				$ad_view = "<a href=\"index.php?name=pages&amp;op=view&amp;id=".$pid."\" title=\""._MVIEW."\">"._MVIEW."</a>||";
				$active = "1";
			} else {
				$ad_view = "";
				$active = "0";
			}
			$cont .= "<tr><td>".$pid."</td>"
			."<td>".title_tip(_CATEGORY.": ".$ctitle."<br>"._DATE.": ".format_time($time, _TIMESTRING)."<br>"._IP.": ".$ip_sender)."<span title=\"".$title."\" class=\"sl_note\">".cutstr($title, 60)."</span></td>"
			."<td>".$post."</td>"
			."<td>".ad_status("", $active)."</td>"
			."<td>".add_menu($ad_view."<a href=\"".$admin_file.".php?op=page_add&amp;id=".$pid."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=page_delete&amp;id=".$pid.$refer."\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$title."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>")."</td></tr>";
		}
		$cont .= "</tbody></table>";
		$cont .= num_article("pagenum", "", $confp['anum'], $field, "pid", "_pages", "", "status = '".$status."'", $confp['anump']);
		$cont .= tpl_eval("close");
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function page_add() {
	global $prefix, $db, $admin_file, $confu, $stop;
	if (isset($_REQUEST['id'])) {
		$pid = intval($_REQUEST['id']);
		$result = $db->sql_query("SELECT p.catid, p.name, p.title, p.time, p.hometext, p.bodytext, p.acomm, u.user_name FROM ".$prefix."_pages AS p LEFT JOIN ".$prefix."_users AS u ON (p.uid = u.user_id) WHERE pid = '".$pid."'");
		list($cat, $uname, $subject, $time, $hometext, $bodytext, $acomm, $user_name) = $db->sql_fetchrow($result);
		$postname = ($user_name) ? $user_name : (($uname) ? $uname : $confu['anonym']);
	} else {
		$pid = $_POST['pid'];
		$postname = $_POST['postname'];
		$subject = save_text($_POST['subject'], 1);
		$cat = $_POST['cat'];
		$hometext = save_text($_POST['hometext']);
		$bodytext = save_text($_POST['bodytext']);
		$time = save_datetime(1, "time");
		$acomm = $_POST['acomm'];
	}
	head();
	$cont = page_navi(0, 1, 0, 0);
	if ($stop) $cont .= tpl_warn("warn", $stop, "", "", "warn");
	if ($hometext) $cont .= preview($subject, $hometext, $bodytext, "", "pages");
	$cont .= tpl_warn("warn", _PAGENOTE, "", "", "info");
	$cont .= tpl_eval("open");
	$cont .= "<form name=\"post\" action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_form\">"
	."<tr><td>"._POSTEDBY.":</td><td>".get_user_search("postname", $postname, "25", "sl_form", "1")."</td></tr>"
	."<tr><td>"._TITLE.":</td><td><input type=\"text\" name=\"subject\" value=\"".$subject."\" maxlength=\"100\" class=\"sl_form\" placeholder=\""._TITLE."\" required></td></tr>"
	."<tr><td>"._CATEGORY.":</td><td>".getcat("pages", $cat, "cat", "sl_form", "<option value=\"\">"._HOMECAT."</option>")."</td></tr>"
	."<tr><td>"._TEXT.":</td><td>".textarea("1", "hometext", $hometext, "pages", "5", _TEXT, "1")."</td></tr>"
	."<tr><td>"._ENDTEXT.":</td><td>".textarea("2", "bodytext", $bodytext, "pages", "15", _ENDTEXT, "0")."</td></tr>"
	."<tr><td>"._CHNGSTORY.":</td><td>".datetime(1, "time", $time, 16, "sl_form")."</td></tr>"
	."<tr><td>"._COMMENTS.":</td><td>".com_access("acomm", $acomm, "sl_form")."</td></tr>"
	."<tr><td>"._PUBHOME."</td><td>".radio_form($ihome, "ihome")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\">".ad_save("pid", $pid, "page_save")."</td></tr></table></form>";
	$cont .= tpl_eval("close");
	echo $cont;
	foot();
}

function page_save() {
	global $prefix, $db, $admin_file, $stop;
	$pid = intval($_POST['pid']);
	$postname = $_POST['postname'];
	$subject = save_text($_POST['subject'], 1);
	$cat = $_POST['cat'];
	$hometext = save_text($_POST['hometext']);
	$bodytext = save_text($_POST['bodytext']);
	$ihome = $_POST['ihome'];
	$acomm = $_POST['acomm'];
	$time = save_datetime(1, "time");
	$stop = array();
	if (!$subject) $stop[] = _CERROR;
	if (!$hometext) $stop[] = _CERROR1;
	if (!$postname) $stop[] = _CERROR3;
	if (!$stop && $_POST['posttype'] == "save") {
		$postid = (is_user_id($postname)) ? is_user_id($postname) : "";
		$postname = (!is_user_id($postname)) ? text_filter(substr($postname, 0, 25)) : "";
		if ($pid) {
			$db->sql_query("UPDATE ".$prefix."_pages SET catid = '".$cat."', uid = '".$postid."', name = '".$postname."', title = '".$subject."', time = '".$time."', hometext = '".$hometext."', bodytext = '".$bodytext."', ihome = '".$ihome."', acomm = '".$acomm."', status = '1' WHERE pid = '".$pid."'");
		} else {
			$ip = getip();
			$db->sql_query("INSERT INTO ".$prefix."_pages (pid, catid, uid, name, title, time, hometext, bodytext, comments, counter, ihome, acomm, score, ratings, ip_sender, status) VALUES (NULL, '".$cat."', '".$postid."', '".$postname."', '".$subject."', '".$time."', '".$hometext."', '".$bodytext."', '0', '0', '".$ihome."', '".$acomm."', '0', '0', '".$ip."', '1')");
		}
		header("Location: ".$admin_file.".php?op=page");
	} elseif ($_POST['posttype'] == "delete") {
		page_delete($pid);
	} else {
		page_add();
	}
}

function page_delete() {
	global $prefix, $db, $admin_file, $id;
	$arg = func_get_args();
	$id = ($arg[0]) ? $arg[0] : $id;
	if ($id) {
		$db->sql_query("DELETE FROM ".$prefix."_comment WHERE cid = '".$id."' AND modul = 'pages'");
		$db->sql_query("DELETE FROM ".$prefix."_favorites WHERE fid = '".$id."' AND modul = 'pages'");
		$db->sql_query("DELETE FROM ".$prefix."_pages WHERE pid = '".$id."'");
	}
	referer($admin_file.".php?op=page");
}

function page_conf() {
	global $admin_file, $confp;
	head();
	$cont = page_navi(0, 3, 0, 0);
	$permtest = end_chmod("config/config_pages.php", 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_conf\">"
	."<tr><td>"._CDEFIS.":</td><td><input type=\"text\" name=\"defis\" value=\"".urldecode($confp['defis'])."\" maxlength=\"25\" class=\"sl_conf\" placeholder=\""._CDEFIS."\" required></td></tr>"
	."<tr><td>"._C_10.":</td><td><input type=\"number\" name=\"tabcol\" value=\"".$confp['tabcol']."\" class=\"sl_conf\" placeholder=\""._C_10."\" required></td></tr>"
	."<tr><td>"._PAGELINKNUM.":</td><td><input type=\"number\" name=\"linknum\" value=\"".$confp['linknum']."\" class=\"sl_conf\" placeholder=\""._PAGELINKNUM."\" required></td></tr>"
	."<tr><td>"._C_13.":</td><td><input type=\"number\" name=\"listnum\" value=\"".$confp['listnum']."\" class=\"sl_conf\" placeholder=\""._C_13."\" required></td></tr>"
	."<tr><td>"._C_33.":</td><td><input type=\"number\" name=\"num\" value=\"".$confp['num']."\" class=\"sl_conf\" placeholder=\""._C_33."\" required></td></tr>"
	."<tr><td>"._C_34.":</td><td><input type=\"number\" name=\"anum\" value=\"".$confp['anum']."\" class=\"sl_conf\" placeholder=\""._C_34."\" required></td></tr>"
	."<tr><td>"._C_35.":</td><td><input type=\"number\" name=\"nump\" value=\"".$confp['nump']."\" class=\"sl_conf\" placeholder=\""._C_35."\" required></td></tr>"
	."<tr><td>"._C_36.":</td><td><input type=\"number\" name=\"anump\" value=\"".$confp['anump']."\" class=\"sl_conf\" placeholder=\""._C_36."\" required></td></tr>"
	."<tr><td>"._HOMCAT."</td><td>".radio_form($confp['homcat'], "homcat")."</td></tr>"
	."<tr><td>"._VIEWCAT."</td><td>".radio_form($confp['viewcat'], "viewcat")."</td></tr>"
	."<tr><td>"._C_32."</td><td>".radio_form($confp['catdesc'], "catdesc")."</td></tr>"
	."<tr><td>"._C_15."</td><td>".radio_form($confp['subcat'], "subcat")."</td></tr>"
	."<tr><td>"._ADDAMAIL."</td><td>".radio_form($confp['addmail'], "addmail")."</td></tr>"
	."<tr><td>"._C_39."</td><td>".radio_form($confp['add'], "add")."</td></tr>"
	."<tr><td>"._C_40."</td><td>".radio_form($confp['addquest'], "addquest")."</td></tr>"
	."<tr><td>"._C_37."</td><td>".radio_form($confp['autor'], "autor")."</td></tr>"
	."<tr><td>"._C_17."</td><td>".radio_form($confp['date'], "date")."</td></tr>"
	."<tr><td>"._C_18."</td><td>".radio_form($confp['read'], "read")."</td></tr>"
	."<tr><td>"._C_19."</td><td>".radio_form($confp['rate'], "rate")."</td></tr>"
	."<tr><td>"._C_20."</td><td>".radio_form($confp['letter'], "letter")."</td></tr>"
	."<tr><td>"._PAGELINK."</td><td>".radio_form($confp['link'], "link")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"page_conf_save\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close");
	echo $cont;
	foot();
}

function page_conf_save() {
	global $admin_file;
	$xdefis = ($_POST['defis']) ? urlencode($_POST['defis']) : "%3E";
	$content = "\$confp = array();\n"
	."\$confp['defis'] = \"".$xdefis."\";\n"
	."\$confp['tabcol'] = \"".$_POST['tabcol']."\";\n"
	."\$confp['linknum'] = \"".$_POST['linknum']."\";\n"
	."\$confp['listnum'] = \"".$_POST['listnum']."\";\n"
	."\$confp['num'] = \"".$_POST['num']."\";\n"
	."\$confp['anum'] = \"".$_POST['anum']."\";\n"
	."\$confp['nump'] = \"".$_POST['nump']."\";\n"
	."\$confp['anump'] = \"".$_POST['anump']."\";\n"
	."\$confp['homcat'] = \"".$_POST['homcat']."\";\n"
	."\$confp['viewcat'] = \"".$_POST['viewcat']."\";\n"
	."\$confp['catdesc'] = \"".$_POST['catdesc']."\";\n"
	."\$confp['subcat'] = \"".$_POST['subcat']."\";\n"
	."\$confp['addmail'] = \"".$_POST['addmail']."\";\n"
	."\$confp['add'] = \"".$_POST['add']."\";\n"
	."\$confp['addquest'] = \"".$_POST['addquest']."\";\n"
	."\$confp['autor'] = \"".$_POST['autor']."\";\n"
	."\$confp['date'] = \"".$_POST['date']."\";\n"
	."\$confp['read'] = \"".$_POST['read']."\";\n"
	."\$confp['rate'] = \"".$_POST['rate']."\";\n"
	."\$confp['letter'] = \"".$_POST['letter']."\";\n"
	."\$confp['link'] = \"".$_POST['link']."\";\n";
	save_conf("config/config_pages.php", $content);
	header("Location: ".$admin_file.".php?op=page_conf");
}

function page_info() {
	head();
	echo page_navi(0, 4, 0, 0)."<div id=\"repadm_info\">".adm_info(1, "pages", 0)."</div>";
	foot();
}

switch($op) {
	case "page":
	page();
	break;
	
	case "page_add":
	page_add();
	break;
	
	case "page_save":
	page_save();
	break;
	
	case "page_delete":
	page_delete();
	break;
	
	case "page_conf":
	page_conf();
	break;
	
	case "page_conf_save":
	page_conf_save();
	break;
	
	case "page_info":
	page_info();
	break;
}
?>