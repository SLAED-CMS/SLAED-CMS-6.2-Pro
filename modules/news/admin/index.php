<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("ADMIN_FILE") || !is_admin_modul("news")) die("Illegal file access");

include("config/config_news.php");

function news_navi() {
	panel();
	$narg = func_get_args();
	$ops = array("news", "news_add", "news&amp;status=1", "news_conf", "news_info");
	$lang = array(_HOME, _ADD, _NEW, _PREFERENCES, _INFO);
	return navi_gen(_NEWS, "news.png", "", $ops, $lang, "", "", $narg[0], $narg[1], $narg[2], $narg[3]);
}

function news() {
	global $prefix, $db, $admin_file, $confn, $confu;
	head();
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $confn['anum'];
	$offset = intval($offset);
	if (isset($_GET['status']) == 1) {
		$status = "0";
		$field = "op=news&amp;status=1&amp;";
		$refer = "&amp;refer=1";
		$cont = news_navi(0, 2, 0, 0);
	} else {
		$status = "1";
		$field = "op=news&amp;";
		$refer = "";
		$cont = news_navi(0, 0, 0, 0);
	}
	$result = $db->sql_query("SELECT s.sid, s.catid, s.name, s.title, s.time, s.vote, s.ip_sender, c.title, u.user_name FROM ".$prefix."_news AS s LEFT JOIN ".$prefix."_categories AS c ON (s.catid = c.id) LEFT JOIN ".$prefix."_users AS u ON (s.uid = u.user_id) WHERE s.status = '".$status."' ORDER BY s.fix DESC, s.time DESC LIMIT ".$offset.", ".$confn['anum']);
	if ($db->sql_numrows($result) > 0) {
		$cont .= tpl_eval("open");
		$cont .= "<form name=\"post\" action=\"".$admin_file.".php\" method=\"post\">"
		."<table class=\"sl_table_list_sort\"><thead><tr><th>"._ID."</th><th>"._TITLE."</th><th>"._POSTEDBY."</th><th class=\"{sorter: false}\">"._STATUS."</th><th class=\"{sorter: false}\">"._FUNCTIONS."</th><th class=\"{sorter: false}\"><input type=\"checkbox\" name=\"markcheck\" id=\"markcheck\" title=\""._CHECKALL."\" OnClick=\"CheckBox('#markcheck', '.sl_check')\"></th></tr></thead><tbody>";
		while (list($sid, $catid, $uname, $title, $time, $vote, $ip_sender, $ctitle, $user_name) = $db->sql_fetchrow($result)) {
			$ctitle = ($catid) ? $ctitle : _NO;
			$ip_sender = ($ip_sender) ? user_geo_ip($ip_sender, 4) : _NO;
			$post = ($user_name) ? user_info($user_name) : (($uname) ? $uname : $confu['anonym']);
			if ($status && time() >= strtotime($time)) {
				$ad_view = "<a href=\"index.php?name=news&amp;op=view&amp;id=".$sid."\" title=\""._MVIEW."\">"._MVIEW."</a>||";
				$active = "1";
			} else {
				$ad_view = "";
				$active = "0";
			}
			$ad_vote = ($vote) ? "<a href=\"".$admin_file.".php?op=voting_add&amp;id=".$vote."\" title=\""._EDITVOTE."\">"._EDITVOTE."</a>||" : "";
			$cont .= "<tr><td>".$sid."</td>"
			."<td>".title_tip(_CATEGORY.": ".$ctitle."<br>"._DATE.": ".format_time($time, _TIMESTRING)."<br>"._IP.": ".$ip_sender)."<span title=\"".$title."\" class=\"sl_note\">".cutstr($title, 60)."</span></td>"
			."<td>".$post."</td>"
			."<td>".ad_status("", $active)."</td>"
			."<td>".add_menu($ad_view.$ad_vote."<a href=\"".$admin_file.".php?op=news_add&amp;id=".$sid."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=news_admin&amp;typ=d&amp;id=".$sid.$refer."\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$title."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>")."</td>"
			."<td><input type=\"checkbox\" name=\"id[]\" class=\"sl_check\" value=\"".$sid."\"></td></tr>";
		}
		$cont .= "</tbody></table>";
		$selms = _CHECKOP.": ".edit_list("news", "typ", "")." <input type=\"hidden\" name=\"op\" value=\"news_admin\"><input type=\"hidden\" name=\"refer\" value=\"1\"> <input type=\"submit\" value=\""._OK."\" class=\"sl_but_blue\">";
		$numpt = num_article("pagenum", "", $confn['anum'], $field, "sid", "_news", "", "status = '".$status."'", $confn['anump']);
		$cont .= tpl_eval("list-bottom", $numpt, $selms);
		$cont .= tpl_eval("close", "</form>");
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function news_add() {
	global $prefix, $db, $admin_file, $confu, $stop;
	if (isset($_REQUEST['id'])) {
		$sid = intval($_REQUEST['id']);
		$result = $db->sql_query("SELECT s.catid, s.name, s.title, s.time, s.hometext, s.bodytext, s.field, s.vote, s.ihome, s.acomm, s.associated, s.fix, u.user_name FROM ".$prefix."_news AS s LEFT JOIN ".$prefix."_users AS u ON (s.uid = u.user_id) WHERE sid = '".$sid."'");
		list($cat, $uname, $subject, $time, $hometext, $bodytext, $field, $vote, $ihome, $acomm, $associated, $fix, $user_name) = $db->sql_fetchrow($result);
		$associated = explode(",", $associated);
		$postname = ($user_name) ? $user_name : (($uname) ? $uname : $confu['anonym']);
	} else {
		$sid = $_POST['sid'];
		$postname = $_POST['postname'];
		$subject = save_text($_POST['subject'], 1);
		$associated = $_POST['associated'];
		$cat = $_POST['cat'];
		$hometext = save_text($_POST['hometext']);
		$bodytext = save_text($_POST['bodytext']);
		$field = fields_save($_POST['field']);
		$vote = $_POST['vote'];
		$time = save_datetime(1, "time");
		$ihome = $_POST['ihome'];
		$acomm = $_POST['acomm'];
		$fix = $_POST['fix'];
	}
	head();
	$cont = news_navi(0, 1, 0, 0);
	if ($stop) $cont .= tpl_warn("warn", $stop, "", "", "warn");
	$homepre = ($vote) ? "<div id=\"repnews\">".avoting_view($vote, "news")."</div><hr>".$hometext : $hometext;
	if ($homepre) $cont .= preview($subject, $homepre, $bodytext, $field, "news");
	$cont .= tpl_warn("warn", _PAGENOTE, "", "", "info");
	$cont .= tpl_eval("open");
	$cont .= "<form name=\"post\" action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_form\">"
	."<tr><td>"._POSTEDBY.":</td><td>".get_user_search("postname", $postname, "25", "sl_form", "1")."</td></tr>"
	."<tr><td>"._TITLE.":</td><td><input type=\"text\" name=\"subject\" value=\"".$subject."\" maxlength=\"100\" class=\"sl_form\" placeholder=\""._TITLE."\" required></td></tr>"
	."<tr><td>"._CATEGORY.":</td><td>".getcat("news", $cat, "cat", "sl_form", "<option value=\"\">"._HOMECAT."</option>")."</td></tr>";
	$result2 = $db->sql_query("SELECT id, title FROM ".$prefix."_categories WHERE modul = 'news' ORDER BY parentid, title");
	if ($db->sql_numrows($result2) > 0) {
		$cont .= "<tr><td>"._ASSOTOPIC.":<div class=\"sl_small\">"._ASSOTOPICI."</div></td><td><table class=\"sl_form\"><tr>";
		while (list($cid, $ctitle) = $db->sql_fetchrow($result2)) {
			if ($a == 2) {
				$cont .= "</tr><tr>";
				$a = 0;
			}
			$check = "";
			if ($associated) foreach ($associated as $val) if ($val == $cid) $check = " checked";
			$cont .= "<td><input type=\"checkbox\" name=\"associated[]\" value=\"".$cid."\"".$check."> ".$ctitle."</td>";
			$a++;
		}
		$cont .= "</tr></table></td></tr>";
	}
	$cont .= "<tr><td>"._TEXT.":</td><td>".textarea("1", "hometext", $hometext, "news", "5", _TEXT, "1")."</td></tr>"
	."<tr><td>"._ENDTEXT.":</td><td>".textarea("2", "bodytext", $bodytext, "news", "15", _ENDTEXT, "0")."</td></tr>"
	.fields_in($field, "news")
	."<tr><td>"._CHNGSTORY.":</td><td>".datetime(1, "time", $time, 16, "sl_form")."</td></tr>"
	."<tr><td>"._VOTING.":</td><td>".add_voting("news", "vote", $vote, "sl_form")."</td></tr>"
	."<tr><td>"._COMMENTS.":</td><td>".com_access("acomm", $acomm, "sl_form")."</td></tr>"
	."<tr><td>"._PUBHOME."</td><td>".radio_form($ihome, "ihome")."</td></tr>"
	."<tr><td>"._FIXED."?</td><td>".radio_form($fix, "fix")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\">".ad_save("sid", $sid, "news_save")."</td></tr></table></form>";
	$cont .= tpl_eval("close");
	echo $cont;
	foot();
}

function news_save() {
	global $prefix, $db, $admin_file, $stop;
	$sid = intval($_POST['sid']);
	$postname = $_POST['postname'];
	$subject = save_text($_POST['subject'], 1);
	$associated = (isset($_POST['associated'])) ? implode(",", $_POST['associated']) : "";
	$cat = $_POST['cat'];
	$hometext = save_text($_POST['hometext']);
	$bodytext = save_text($_POST['bodytext']);
	$field = fields_save($_POST['field']);
	$vote = intval($_POST['vote']);
	$ihome = $_POST['ihome'];
	$acomm = $_POST['acomm'];
	$time = save_datetime(1, "time");
	$fix = intval($_POST['fix']);
	$stop = array();
	if (!$subject) $stop[] = _CERROR;
	if (!$hometext) $stop[] = _CERROR1;
	if (!$postname) $stop[] = _CERROR3;
	if (!$stop && $_POST['posttype'] == "save") {
		$postid = (is_user_id($postname)) ? is_user_id($postname) : "";
		$postname = (!is_user_id($postname)) ? text_filter(substr($postname, 0, 25)) : "";
		if ($sid) {
			$db->sql_query("UPDATE ".$prefix."_news SET catid = '".$cat."', uid = '".$postid."', name = '".$postname."', title = '".$subject."', time = '".$time."', hometext = '".$hometext."', bodytext = '".$bodytext."', field = '".$field."', vote = '".$vote."', ihome = '".$ihome."', acomm = '".$acomm."', associated = '".$associated."', fix = '".$fix."', status = '1' WHERE sid = '".$sid."'");
		} else {
			$ip = getip();
			$db->sql_query("INSERT INTO ".$prefix."_news (sid, catid, uid, name, title, time, hometext, bodytext, field, vote, comments, counter, ihome, acomm, score, ratings, associated, ip_sender, fix, status) VALUES (NULL, '".$cat."', '".$postid."', '".$postname."', '".$subject."', '".$time."', '".$hometext."', '".$bodytext."', '".$field."', '".$vote."', '0', '0', '".$ihome."', '".$acomm."', '0', '0', '".$associated."', '".$ip."', '".$fix."', '1')");
		}
		header("Location: ".$admin_file.".php?op=news");
	} elseif ($_POST['posttype'] == "delete") {
		news_admin($sid, "d");
	} else {
		news_add();
	}
}

function news_admin() {
	global $prefix, $db, $admin_file, $id;
	$arg = func_get_args();
	$id = ($arg[0]) ? $arg[0] : $id;
	$id = (is_array($id)) ? implode(",", $id) : intval($id);
	$vtyp = (isset($_POST['typ'])) ? analyze($_POST['typ']) : ((isset($_GET['typ'])) ? analyze($_GET['typ']) : $arg[1]);
	$typ = (is_numeric($vtyp[0])) ? intval($vtyp) : intval(substr($vtyp, 1));
	if ($id) {
		if ($vtyp[0] == "a") {
			$db->sql_query("UPDATE ".$prefix."_news SET status = '".$typ."' WHERE sid IN (".$id.")");
		} elseif ($vtyp[0] == "f") {
			$db->sql_query("UPDATE ".$prefix."_news SET fix = '".$typ."' WHERE sid IN (".$id.")");
		} elseif ($vtyp[0] == "h") {
			$db->sql_query("UPDATE ".$prefix."_news SET ihome = '".$typ."' WHERE sid IN (".$id.")");
		} elseif ($vtyp[0] == "t") {
			$db->sql_query("UPDATE ".$prefix."_news SET time = now() WHERE sid IN (".$id.")");
		} elseif ($vtyp[0] == "c") {
			$db->sql_query("UPDATE ".$prefix."_news SET acomm = '".$typ."' WHERE sid IN (".$id.")");
		} elseif ($vtyp[0] == "d") {
			$db->sql_query("DELETE FROM ".$prefix."_comment WHERE cid IN (".$id.") AND modul = 'news'");
			$db->sql_query("DELETE FROM ".$prefix."_favorites WHERE fid IN (".$id.") AND modul = 'news'");
			$db->sql_query("DELETE FROM ".$prefix."_news WHERE sid IN (".$id.")");
		} elseif (is_numeric($vtyp[0])) {
			$db->sql_query("UPDATE ".$prefix."_news SET catid = '".$typ."' WHERE sid IN (".$id.")");
		}
	}
	referer($admin_file.".php?op=news");
}

function news_conf() {
	global $admin_file, $confn;
	head();
	$cont = news_navi(0, 3, 0, 0);
	$permtest = end_chmod("config/config_news.php", 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<form name=\"post\" action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_conf\">"
	."<tr><td>"._CDEFIS.":</td><td><input type=\"text\" name=\"defis\" value=\"".urldecode($confn['defis'])."\" maxlength=\"25\" class=\"sl_conf\" placeholder=\""._CDEFIS."\" required></td></tr>"
	."<tr><td>"._BASCOL.":</td><td><input type=\"number\" name=\"bascol\" value=\"".$confn['bascol']."\" class=\"sl_conf\" placeholder=\""._BASCOL."\" required></td></tr>"
	."<tr><td>"._C_10.":</td><td><input type=\"number\" name=\"tabcol\" value=\"".$confn['tabcol']."\" class=\"sl_conf\" placeholder=\""._C_10."\" required></td></tr>"
	."<tr><td>"._C_11.":</td><td><input type=\"number\" name=\"asocnum\" value=\"".$confn['asocnum']."\" class=\"sl_conf\" placeholder=\""._C_11."\" required></td></tr>"
	."<tr><td>"._C_13.":</td><td><input type=\"number\" name=\"listnum\" value=\"".$confn['listnum']."\" class=\"sl_conf\" placeholder=\""._C_13."\" required></td></tr>"
	."<tr><td>"._C_33.":</td><td><input type=\"number\" name=\"num\" value=\"".$confn['num']."\" class=\"sl_conf\" placeholder=\""._C_33."\" required></td></tr>"
	."<tr><td>"._C_34.":</td><td><input type=\"number\" name=\"anum\" value=\"".$confn['anum']."\" class=\"sl_conf\" placeholder=\""._C_34."\" required></td></tr>"
	."<tr><td>"._C_35.":</td><td><input type=\"number\" name=\"nump\" value=\"".$confn['nump']."\" class=\"sl_conf\" placeholder=\""._C_35."\" required></td></tr>"
	."<tr><td>"._C_36.":</td><td><input type=\"number\" name=\"anump\" value=\"".$confn['anump']."\" class=\"sl_conf\" placeholder=\""._C_36."\" required></td></tr>"
	."<tr><td>"._HOMCAT."</td><td>".radio_form($confn['homcat'], "homcat")."</td></tr>"
	."<tr><td>"._VIEWCAT."</td><td>".radio_form($confn['viewcat'], "viewcat")."</td></tr>"
	."<tr><td>"._C_32."</td><td>".radio_form($confn['catdesc'], "catdesc")."</td></tr>"
	."<tr><td>"._C_15."</td><td>".radio_form($confn['subcat'], "subcat")."</td></tr>"
	."<tr><td>"._ADDAMAIL."</td><td>".radio_form($confn['addmail'], "addmail")."</td></tr>"
	."<tr><td>"._C_39."</td><td>".radio_form($confn['add'], "add")."</td></tr>"
	."<tr><td>"._C_40."</td><td>".radio_form($confn['addquest'], "addquest")."</td></tr>"
	."<tr><td>"._C_37."</td><td>".radio_form($confn['autor'], "autor")."</td></tr>"
	."<tr><td>"._C_17."</td><td>".radio_form($confn['date'], "date")."</td></tr>"
	."<tr><td>"._C_18."</td><td>".radio_form($confn['read'], "read")."</td></tr>"
	."<tr><td>"._C_19."</td><td>".radio_form($confn['rate'], "rate")."</td></tr>"
	."<tr><td>"._C_20."</td><td>".radio_form($confn['letter'], "letter")."</td></tr>"
	."<tr><td>"._C_23."</td><td>".radio_form($confn['assoc'], "assoc")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"news_conf_save\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close");
	echo $cont;
	foot();
}

function news_conf_save() {
	global $admin_file;
	$xdefis = ($_POST['defis']) ? urlencode($_POST['defis']) : "%3E";
	$xbascol = (!intval($_POST['bascol'])) ? "1" : $_POST['bascol'];
	$content = "\$confn = array();\n"
	."\$confn['defis'] = \"".$xdefis."\";\n"
	."\$confn['bascol'] = \"".$xbascol."\";\n"
	."\$confn['tabcol'] = \"".$_POST['tabcol']."\";\n"
	."\$confn['asocnum'] = \"".$_POST['asocnum']."\";\n"
	."\$confn['listnum'] = \"".$_POST['listnum']."\";\n"
	."\$confn['num'] = \"".$_POST['num']."\";\n"
	."\$confn['anum'] = \"".$_POST['anum']."\";\n"
	."\$confn['nump'] = \"".$_POST['nump']."\";\n"
	."\$confn['anump'] = \"".$_POST['anump']."\";\n"
	."\$confn['homcat'] = \"".$_POST['homcat']."\";\n"
	."\$confn['viewcat'] = \"".$_POST['viewcat']."\";\n"
	."\$confn['catdesc'] = \"".$_POST['catdesc']."\";\n"
	."\$confn['subcat'] = \"".$_POST['subcat']."\";\n"
	."\$confn['addmail'] = \"".$_POST['addmail']."\";\n"
	."\$confn['add'] = \"".$_POST['add']."\";\n"
	."\$confn['addquest'] = \"".$_POST['addquest']."\";\n"
	."\$confn['autor'] = \"".$_POST['autor']."\";\n"
	."\$confn['date'] = \"".$_POST['date']."\";\n"
	."\$confn['read'] = \"".$_POST['read']."\";\n"
	."\$confn['rate'] = \"".$_POST['rate']."\";\n"
	."\$confn['letter'] = \"".$_POST['letter']."\";\n"
	."\$confn['assoc'] = \"".$_POST['assoc']."\";\n";
	save_conf("config/config_news.php", $content);
	header("Location: ".$admin_file.".php?op=news_conf");
}

function news_info() {
	head();
	echo news_navi(0, 4, 0, 0)."<div id=\"repadm_info\">".adm_info(1, "news", 0)."</div>";
	foot();
}

switch($op) {
	case "news":
	news();
	break;
	
	case "news_add":
	news_add();
	break;
	
	case "news_save":
	news_save();
	break;
	
	case "news_admin":
	news_admin();
	break;
	
	case "news_conf":
	news_conf();
	break;
	
	case "news_conf_save":
	news_conf_save();
	break;
	
	case "news_info":
	news_info();
	break;
}
?>