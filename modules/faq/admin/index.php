<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("ADMIN_FILE") || !is_admin_modul("faq")) die("Illegal file access");

include("config/config_faq.php");

function faq_navi() {
	panel();
	$narg = func_get_args();
	$ops = array("faq", "faq_add", "faq&amp;status=1", "faq_conf", "faq_info");
	$lang = array(_HOME, _ADD, _NEW, _PREFERENCES, _INFO);
	return navi_gen(_FAQ, "faq.png", "", $ops, $lang, "", "", $narg[0], $narg[1], $narg[2], $narg[3]);
}

function faq() {
	global $prefix, $db, $admin_file, $conf, $conffa, $confu;
	head();
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $conffa['anum'];
	$offset = intval($offset);
	if ($_GET['status'] == 1) {
		$status = "0";
		$field = "op=faq&amp;status=1&amp;";
		$refer = "&amp;refer=1";
		$cont = faq_navi(0, 2, 0, 0);
	} else {
		$status = "1";
		$field = "op=faq&amp;";
		$refer = "";
		$cont = faq_navi(0, 0, 0, 0);
	}
	$result = $db->sql_query("SELECT f.fid, f.catid, f.name, f.title, f.time, f.ip_sender, t.title, u.user_name FROM ".$prefix."_faq AS f LEFT JOIN ".$prefix."_categories AS t ON (f.catid = t.id) LEFT JOIN ".$prefix."_users AS u ON (f.uid = u.user_id) WHERE f.status = '".$status."' ORDER BY f.time DESC LIMIT ".$offset.", ".$conffa['anum']);
	if ($db->sql_numrows($result) > 0) {
		$cont .= tpl_eval("open");
		$cont .= "<table class=\"sl_table_list_sort\"><thead><tr><th>"._ID."</th><th>"._QUESTION."</th><th>"._POSTEDBY."</th><th class=\"{sorter: false}\">"._STATUS."</th><th class=\"{sorter: false}\">"._FUNCTIONS."</th></tr></thead><tbody>";
		while (list($fid, $catid, $uname, $title, $time, $ip_sender, $ctitle, $user_name) = $db->sql_fetchrow($result)) {
			$ctitle = ($catid) ? $ctitle : _NO;
			$ip_sender = ($ip_sender) ? user_geo_ip($ip_sender, 4) : _NO;
			$post = ($user_name) ? user_info($user_name) : (($uname) ? $uname : $confu['anonym']);
			if ($status && time() >= strtotime($time)) {
				$ad_view = "<a href=\"index.php?name=faq&amp;op=view&amp;id=".$fid."\" title=\""._MVIEW."\">"._MVIEW."</a>||";
				$active = "1";
			} else {
				$ad_view = "";
				$active = "0";
			}
			$cont .= "<tr><td>".$fid."</td>"
			."<td>".title_tip(_CATEGORY.": ".$ctitle."<br>"._DATE.": ".format_time($time, _TIMESTRING)."<br>"._IP.": ".$ip_sender)."<span title=\"".$title."\" class=\"sl_note\">".cutstr($title, 60)."</span></td>"
			."<td>".$post."</td>"
			."<td>".ad_status("", $active)."</td>"
			."<td>".add_menu($ad_view."<a href=\"".$admin_file.".php?op=faq_add&amp;id=".$fid."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=faq_delete&amp;id=".$fid.$refer."\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$title."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>")."</td></tr>";
		}
		$cont .= "</tbody></table>";
		$cont .= num_article("pagenum", "", $conffa['anum'], $field, "fid", "_faq", "", "status = '".$status."'", $conffa['anump']);
		$cont .= tpl_eval("close");
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function faq_add() {
	global $prefix, $db, $admin_file, $stop;
	if (isset($_REQUEST['id'])) {
		$fid = intval($_REQUEST['id']);
		$result = $db->sql_query("SELECT s.catid, s.name, s.title, s.time, s.hometext, s.ihome, s.acomm, u.user_name FROM ".$prefix."_faq AS s LEFT JOIN ".$prefix."_users AS u ON (s.uid = u.user_id) WHERE fid = '".$fid."'");
		list($cat, $uname, $subject, $time, $hometext, $ihome, $acomm, $user_name) = $db->sql_fetchrow($result);
		$postname = ($user_name) ? $user_name : (($uname) ? $uname : $confu['anonym']);
	} else {
		$fid = $_POST['fid'];
		$postname = $_POST['postname'];
		$subject = save_text($_POST['subject'], 1);
		$time = save_datetime(1, "time");
		$cat = $_POST['cat'];
		$hometext = save_text($_POST['hometext']);
		$ihome = $_POST['ihome'];
		$acomm = $_POST['acomm'];
	}
	head();
	$cont = faq_navi(0, 1, 0, 0);
	if ($stop) $cont .= tpl_warn("warn", $stop, "", "", "warn");
	$cont .= tpl_warn("warn", _PAGENOTE, "", "", "info");
	if ($hometext) $cont .= preview($subject, $hometext, "", "", "faq");
	$cont .= tpl_eval("open");
	$cont .= "<form name=\"post\" action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_form\">"
	."<tr><td>"._POSTEDBY.":</td><td>".get_user_search("postname", $postname, "25", "sl_form", "1")."</td></tr>"
	."<tr><td>"._TITLE." / "._QUESTION.":</td><td><input type=\"text\" name=\"subject\" value=\"".$subject."\" maxlength=\"100\" class=\"sl_form\" placeholder=\""._TITLE." / "._QUESTION."\" required></td></tr>"
	."<tr><td>"._CATEGORY.":</td><td>".getcat("faq", $cat, "cat", "sl_form", "<option value=\"\">"._HOMECAT."</option>")."</td></tr>"
	."<tr><td>"._ANSWER.":</td><td>".textarea("1", "hometext", $hometext, "faq", "10", _ANSWER, "1")."</td></tr>"
	."<tr><td>"._CHNGSTORY.":</td><td>".datetime(1, "time", $time, 16, "sl_form")."</td></tr>"
	."<tr><td>"._COMMENTS.":</td><td>".com_access("acomm", $acomm, "sl_form")."</td></tr>"
	."<tr><td>"._PUBHOME."</td><td>".radio_form($ihome, "ihome")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\">".ad_save("fid", $fid, "faq_save")."</td></tr></table></form>";
	$cont .= tpl_eval("close");
	echo $cont;
	foot();
}

function faq_save() {
	global $prefix, $db, $admin_file, $stop;
	$fid = intval($_POST['fid']);
	$postname = $_POST['postname'];
	$subject = save_text($_POST['subject'], 1);
	$cat = $_POST['cat'];
	$hometext = save_text($_POST['hometext']);
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
		if ($fid) {
			$db->sql_query("UPDATE ".$prefix."_faq SET catid = '".$cat."', uid = '".$postid."', name = '".$postname."', title = '".$subject."', time = '".$time."', hometext = '".$hometext."', ihome = '".$ihome."', acomm = '".$acomm."', status = '1' WHERE fid = '".$fid."'");
		} else {
			$ip = getip();
			$db->sql_query("INSERT INTO ".$prefix."_faq (fid, catid, uid, name, title, time, hometext, ihome, acomm, ip_sender, status) VALUES (NULL, '".$cat."', '".$postid."', '".$postname."', '".$subject."', '".$time."', '".$hometext."', '".$ihome."', '".$acomm."', '".$ip."', '1')");
		}
		header("Location: ".$admin_file.".php?op=faq");
	} elseif ($_POST['posttype'] == "delete") {
		faq_delete($fid);
	} else {
		faq_add();
	}
}

function faq_delete() {
	global $prefix, $db, $admin_file, $id;
	$arg = func_get_args();
	$id = ($arg[0]) ? $arg[0] : $id;
	if ($id) {
		$db->sql_query("DELETE FROM ".$prefix."_comment WHERE cid = '".$id."' AND modul = 'faq'");
		$db->sql_query("DELETE FROM ".$prefix."_favorites WHERE fid = '".$id."' AND modul = 'faq'");
		$db->sql_query("DELETE FROM ".$prefix."_faq WHERE fid = '".$id."'");
	}
	referer($admin_file.".php?op=faq");
}

function faq_conf() {
	global $admin_file, $conffa;
	head();
	$cont = faq_navi(0, 3, 0, 0);
	$permtest = end_chmod("config/config_faq.php", 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<form name=\"post\" action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_conf\">"
	."<tr><td>"._CDEFIS.":</td><td><input type=\"text\" name=\"defis\" value=\"".urldecode($conffa['defis'])."\" maxlength=\"25\" class=\"sl_conf\" placeholder=\""._CDEFIS."\" required></td></tr>"
	."<tr><td>"._C_10.":</td><td><input type=\"number\" name=\"tabcol\" value=\"".$conffa['tabcol']."\" class=\"sl_conf\" placeholder=\""._C_10."\" required></td></tr>"
	."<tr><td>"._PAGELINKNUM.":</td><td><input type=\"number\" name=\"linknum\" value=\"".$conffa['linknum']."\" class=\"sl_conf\" placeholder=\""._PAGELINKNUM."\" required></td></tr>"
	."<tr><td>"._C_13.":</td><td><input type=\"number\" name=\"listnum\" value=\"".$conffa['listnum']."\" class=\"sl_conf\" placeholder=\""._C_13."\" required></td></tr>"
	."<tr><td>"._C_33.":</td><td><input type=\"number\" name=\"num\" value=\"".$conffa['num']."\" class=\"sl_conf\" placeholder=\""._C_33."\" required></td></tr>"
	."<tr><td>"._C_34.":</td><td><input type=\"number\" name=\"anum\" value=\"".$conffa['anum']."\" class=\"sl_conf\" placeholder=\""._C_34."\" required></td></tr>"
	."<tr><td>"._C_35.":</td><td><input type=\"number\" name=\"nump\" value=\"".$conffa['nump']."\" class=\"sl_conf\" placeholder=\""._C_35."\" required></td></tr>"
	."<tr><td>"._C_36.":</td><td><input type=\"number\" name=\"anump\" value=\"".$conffa['anump']."\" class=\"sl_conf\" placeholder=\""._C_36."\" required></td></tr>"
	."<tr><td>"._HOMCAT."</td><td>".radio_form($conffa['homcat'], "homcat")."</td></tr>"
	."<tr><td>"._VIEWCAT."</td><td>".radio_form($conffa['viewcat'], "viewcat")."</td></tr>"
	."<tr><td>"._C_32."</td><td>".radio_form($conffa['catdesc'], "catdesc")."</td></tr>"
	."<tr><td>"._C_15."</td><td>".radio_form($conffa['subcat'], "subcat")."</td></tr>"
	."<tr><td>"._ADDAMAIL."</td><td>".radio_form($conffa['addmail'], "addmail")."</td></tr>"
	."<tr><td>"._C_39."</td><td>".radio_form($conffa['add'], "add")."</td></tr>"
	."<tr><td>"._C_40."</td><td>".radio_form($conffa['addquest'], "addquest")."</td></tr>"
	."<tr><td>"._C_37."</td><td>".radio_form($conffa['autor'], "autor")."</td></tr>"
	."<tr><td>"._C_17."</td><td>".radio_form($conffa['date'], "date")."</td></tr>"
	."<tr><td>"._C_18."</td><td>".radio_form($conffa['read'], "read")."</td></tr>"
	."<tr><td>"._C_19."</td><td>".radio_form($conffa['rate'], "rate")."</td></tr>"
	."<tr><td>"._C_20."</td><td>".radio_form($conffa['letter'], "letter")."</td></tr>"
	."<tr><td>"._PAGELINK."</td><td>".radio_form($conffa['link'], "link")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"faq_conf_save\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close");
	echo $cont;
	foot();
}

function faq_conf_save() {
	global $admin_file;
	$xdefis = ($_POST['defis']) ? urlencode($_POST['defis']) : "%3E";
	$content = "\$conffa = array();\n"
	."\$conffa['defis'] = \"".$xdefis."\";\n"
	."\$conffa['tabcol'] = \"".$_POST['tabcol']."\";\n"
	."\$conffa['linknum'] = \"".$_POST['linknum']."\";\n"
	."\$conffa['listnum'] = \"".$_POST['listnum']."\";\n"
	."\$conffa['num'] = \"".$_POST['num']."\";\n"
	."\$conffa['anum'] = \"".$_POST['anum']."\";\n"
	."\$conffa['nump'] = \"".$_POST['nump']."\";\n"
	."\$conffa['anump'] = \"".$_POST['anump']."\";\n"
	."\$conffa['homcat'] = \"".$_POST['homcat']."\";\n"
	."\$conffa['viewcat'] = \"".$_POST['viewcat']."\";\n"
	."\$conffa['catdesc'] = \"".$_POST['catdesc']."\";\n"
	."\$conffa['subcat'] = \"".$_POST['subcat']."\";\n"
	."\$conffa['addmail'] = \"".$_POST['addmail']."\";\n"
	."\$conffa['add'] = \"".$_POST['add']."\";\n"
	."\$conffa['addquest'] = \"".$_POST['addquest']."\";\n"
	."\$conffa['autor'] = \"".$_POST['autor']."\";\n"
	."\$conffa['date'] = \"".$_POST['date']."\";\n"
	."\$conffa['read'] = \"".$_POST['read']."\";\n"
	."\$conffa['rate'] = \"".$_POST['rate']."\";\n"
	."\$conffa['letter'] = \"".$_POST['letter']."\";\n"
	."\$conffa['link'] = \"".$_POST['link']."\";\n";
	save_conf("config/config_faq.php", $content);
	header("Location: ".$admin_file.".php?op=faq_conf");
}

function faq_info() {
	head();
	echo faq_navi(0, 4, 0, 0)."<div id=\"repadm_info\">".adm_info(1, "faq", 0)."</div>";
	foot();
}

switch($op) {
	case "faq":
	faq();
	break;
	
	case "faq_add":
	faq_add();
	break;
	
	case "faq_save":
	faq_save();
	break;
	
	case "faq_delete":
	faq_delete();
	break;
	
	case "faq_conf":
	faq_conf();
	break;
	
	case "faq_conf_save":
	faq_conf_save();
	break;
	
	case "faq_info":
	faq_info();
	break;
}
?>