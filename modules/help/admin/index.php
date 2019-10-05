<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("ADMIN_FILE") || !is_admin_modul("help")) die("Illegal file access");

include("config/config_help.php");

function help_navi() {
	panel();
	$narg = func_get_args();
	$ops = array("help", "help&amp;status=1", "help_conf", "help_info");
	$lang = array(_HOME, _GLOSED, _PREFERENCES, _INFO);
	return navi_gen(_HELP, "help.png", "", $ops, $lang, "", "", $narg[0], $narg[1], $narg[2], $narg[3]);
}

function help() {
	global $prefix, $db, $admin_file, $confu, $confh;
	head();
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $confh['anum'];
	$offset = intval($offset);
	if ($_GET['status'] == 1) {
		$status = "1";
		$field = "op=help&amp;status=1&amp;";
		$refer = "&amp;refer=1";
		$cont = help_navi(0, 1, 0, 0);
	} else {
		$status = "0";
		$field = "op=help&amp;";
		$refer = "";
		$cont = help_navi(0, 0, 0, 0);
	}
	$result = $db->sql_query("SELECT s.sid, s.catid, s.title, s.time, s.comments, s.ip_sender, s.status, c.title, u.user_name FROM ".$prefix."_help AS s LEFT JOIN ".$prefix."_categories AS c ON (s.catid = c.id) LEFT JOIN ".$prefix."_users AS u ON (s.uid = u.user_id) WHERE s.pid = '0' AND s.status = '".$status."' ORDER BY s.time DESC LIMIT ".$offset.", ".$confh['anum']);
	if ($db->sql_numrows($result) > 0) {
		$cont .= tpl_eval("open");
		$cont .= "<table class=\"sl_table_list_sort\"><thead><tr><th>"._ID."</th><th>"._TITLE."</th><th>"._POSTEDBY."</th><th>".cutstr(_MESSAGES, 4, 1)."</th><th class=\"{sorter: false}\">"._STATUS."</th><th class=\"{sorter: false}\">"._FUNCTIONS."</th></tr></thead><tbody>";
		while (list($sid, $catid, $title, $time, $comments, $ip_sender, $stat, $ctitle, $user_name) = $db->sql_fetchrow($result)) {
			$ctitle = ($catid) ? $ctitle : _NO;
			$ip_sender = ($ip_sender) ? user_geo_ip($ip_sender, 4) : _NO;
			$post = ($user_name) ? user_info($user_name) : $confu['anonym'];
			$stat = ($stat) ? 0 : 1;
			$cont .= "<tr><td>".$sid."</td>"
			."<td>".title_tip(_CATEGORY.": ".$ctitle."<br>"._DATE.": ".format_time($time, _TIMESTRING)."<br>"._IP.": ".$ip_sender)."<span title=\"".$title."\" class=\"sl_note\">".cutstr($title, 60)."</span></td>"
			."<td>".$post."</td>"
			."<td>".$comments."</td>"
			."<td>".ad_status("", $stat)."</td>"
			."<td>".add_menu("<a href=\"".$admin_file.".php?op=help_view&amp;id=".$sid."\" title=\""._MVIEW."\">"._MVIEW."</a>||<a href=\"".$admin_file.".php?op=help_delete&amp;id=".$sid.$refer."\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$title."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>")."</td></tr>";
		}
		$cont .= "</tbody></table>";
		$cont .= num_article("pagenum", "", $confh['anum'], $field, "sid", "_help", "", "pid = '0' AND status = '".$status."'", $confh['anump']);
		$cont .= tpl_eval("close");
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function help_view() {
	global $prefix, $db, $admin_file, $confu;
	$id = intval($_GET['id']);
	$result = $db->sql_query("SELECT s.sid, s.pid, s.uid, s.aid, s.title, s.time, s.hometext, s.field, s.counter, s.score, s.ratings, c.title, c.description, u.user_name FROM ".$prefix."_help AS s LEFT JOIN ".$prefix."_categories AS c ON (s.catid = c.id) LEFT JOIN ".$prefix."_users AS u ON (s.aid = u.user_id) WHERE s.sid = '".$id."' OR s.pid = '".$id."' AND s.time <= now() ORDER BY s.time ASC");
	head();
	$cont = help_navi(0, 0, 0, 0);
	$cont .= tpl_eval("open");
	$a = 0;
	while (list($sid, $pid, $huid, $haid, $title, $time, $hometext, $field, $counter, $score, $ratings, $ctitle, $cdesc, $user_name) = $db->sql_fetchrow($result)) {
		$title = ($title) ? $title : _MESSAGE.": ".$a;
		$fields = fields_out($field, "help");
		$fields = ($fields) ? "<br><br>".$fields : "";
		$text = $hometext.$fields;
		$post = ($user_name) ? user_info($user_name) : $confu['anonym'];
		$post = "<span title=\""._POSTEDBY."\" class=\"sl_post\">".$post."</span>";
		$date = "<span title=\""._CHNGSTORY."\" class=\"sl_date\">".format_time($time, _TIMESTRING)."</span>";
		$comm = ($a) ? "<a href=\"#".$sid."\" title=\""._MESSAGE.": ".$a."\" class=\"sl_pnum\">".$a."</a>" : "";
		$arating = ($haid && $huid != $haid) ? ajax_rating(0, $sid, "help", $ratings, $score, "") : "";
		if (!$pid) {
			$cdesc = ($cdesc) ? $cdesc : $ctitle;
			$ctitle = ($ctitle) ? "<span title=\"".$cdesc."\" class=\"sl_cat\">".cutstr($ctitle, 15)."</span>" : "";
			$reads =  "<span title=\""._READS."\" class=\"sl_views\">".$counter."</span>";
		} else {
			$ctitle = "";
			$reads =  "";
		}
		$admin = add_menu("<a href=\"".$admin_file.".php?op=help_add&amp;id=".$sid."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=help_delete&amp;id=".$sid.$refer."\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$title."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>");
		$cont .= tpl_func("basic", $ctitle, $sid, $title, bb_decode($text, "help"), $post, $date, $reads, $comm, $arating, $admin);
		$a++;
	}
	$cont .= tpl_eval("close");
	$cont .= help_add_view($id);
	echo $cont;
	foot();
}

function help_add_view($id) {
	global $prefix, $db, $admin_file, $admin;
	$result = $db->sql_query("SELECT catid, uid, status FROM ".$prefix."_help WHERE sid = '".$id."'");
	list($catid, $uid, $status) = $db->sql_fetchrow($result);
	$cont .= tpl_eval("open");
	$cont .= "<form name=\"post\" action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_form\">"
	."<tr><td>"._POSTEDBY.":</td><td>".get_user_search("postname", $admin[1], "25", "sl_form", "1")."</td></tr>"
	."<tr><td>"._TEXT.":</td><td>".textarea("1", "hometext", $hometext, "help", "10", _TEXT, "1")."</td></tr>"
	."<tr><td>"._HELPGLOS."</td><td>".radio_form($status, "status")."</td></tr>"
	."<tr><td>"._MAIL_SENDE."</td><td>".radio_form("1", "umail")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"refer\" value=\"1\"><input type=\"hidden\" name=\"pid\" value=\"".$id."\"><input type=\"hidden\" name=\"cat\" value=\"".$catid."\"><input type=\"hidden\" name=\"uid\" value=\"".$uid."\"><input type=\"hidden\" name=\"posttype\" value=\"save\"><input type=\"hidden\" name=\"op\" value=\"help_save\"><input type=\"submit\" value=\""._SEND."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close");
	return $cont;
}

function help_add() {
	global $prefix, $db, $admin_file, $confu, $stop;
	if (isset($_REQUEST['id'])) {
		$sid = intval($_REQUEST['id']);
		$result = $db->sql_query("SELECT s.pid, s.catid, s.title, s.time, s.hometext, s.field, s.status, u.user_name FROM ".$prefix."_help AS s LEFT JOIN ".$prefix."_users AS u ON (s.aid = u.user_id) WHERE s.sid = '".$sid."'");
		list($pid, $cat, $subject, $time, $hometext, $field, $status, $user_name) = $db->sql_fetchrow($result);
		$postname = ($user_name) ? $user_name : $confu['anonym'];
	} else {
		$sid = $_POST['sid'];
		$pid = $_POST['pid'];
		$postname = $_POST['postname'];
		$subject = save_text($_POST['subject'], 1);
		$time = save_datetime(1, "time");
		$cat = $_POST['cat'];
		$hometext = save_text($_POST['hometext']);
		$field = fields_save($_POST['field']);
	}
	$status = ($_POST['status']) ? $_POST['status'] : $status;
	head();
	$cont = help_navi(0, 0, 0, 0);
	if ($stop) $cont .= tpl_warn("warn", $stop, "", "", "warn");
	if ($hometext) $cont .= preview($subject, $hometext, "", $field, "help");
	$cont .= tpl_eval("open");
	$cont .= "<form name=\"post\" action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_form\">"
	."<tr><td>"._POSTEDBY.":</td><td>".get_user_search("postname", $postname, "25", "sl_form", "1")."</td></tr>";
	if (!$pid) $cont .= "<tr><td>"._TITLE.":</td><td><input type=\"text\" name=\"subject\" value=\"".$subject."\" maxlength=\"100\" class=\"sl_form\" placeholder=\""._TITLE."\" required></td></tr>";
	$cont .= "<tr><td>"._CATEGORY.":</td><td>".getcat("help", $cat, "cat", "sl_form", "<option value=\"\">"._HOMECAT."</option>")."</td></tr>"
	."<tr><td>"._TEXT.":</td><td>".textarea("1", "hometext", $hometext, "help", "10", _TEXT, "1")."</td></tr>"
	.fields_in($field, "help")
	."<tr><td>"._CHNGSTORY.":</td><td>".datetime(1, "time", $time, 16, "sl_form")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"pid\" value=\"".$pid."\">".ad_save("sid", $sid, "help_save")."</td></tr></table></form>";
	$cont .= tpl_eval("close");
	echo $cont;
	foot();
}

function help_save() {
	global $prefix, $db, $admin_file, $admin, $conf, $stop;
	$sid = intval($_POST['sid']);
	$pid = intval($_POST['pid']);
	$uid = intval($_POST['uid']);
	$postname = $_POST['postname'];
	$subject = save_text($_POST['subject'], 1);
	$cat = $_POST['cat'];
	$hometext = save_text($_POST['hometext']);
	$field = fields_save($_POST['field']);
	$time = save_datetime(1, "time");
	$status = intval($_POST['status']);
	$umail = $_POST['umail'];
	$stop = array();
	if (!$subject && !$pid) $stop[] = _CERROR;
	if (!$hometext && !$pid) $stop[] = _CERROR1;
	if (!$postname && !$pid) $stop[] = _CERROR3;
	if (!$stop && $_POST['posttype'] == "save") {
		$postid = (is_user_id($postname)) ? is_user_id($postname) : "";
		if ($sid) {
			$db->sql_query("UPDATE ".$prefix."_help SET catid = '".$cat."', aid = '".$postid."', title = '".$subject."', time = '".$time."', hometext = '".$hometext."', field = '".$field."' WHERE sid = '".$sid."'");
			$hid = ($pid) ? $pid : $sid;
			header("Location: ".$admin_file.".php?op=help_view&id=".$hid);
		} else {
			$ip = getip();
			$db->sql_query("INSERT INTO ".$prefix."_help (sid, pid, catid, uid, aid, title, time, hometext, ip_sender, status) VALUES (NULL, '".$pid."', '".$cat."', '".$uid."', '".$postid."', '".$subject."', now(), '".$hometext."', '".$ip."', '0')");
			$db->sql_query("UPDATE ".$prefix."_help SET comments = comments+1, status = '".$status."'WHERE sid = '".$pid."'");
			
			if ($umail) {
				$result = $db->sql_query("SELECT user_email FROM ".$prefix."_users WHERE user_id = '".$uid."'");
				if ($db->sql_numrows($result) == 1) {
					list($user_email) = $db->sql_fetchrow($result);
					$finishlink = $conf['homeurl']."/index.php?name=help&amp;op=view&amp;id=".$pid;
					$link = "<a href=\"".$finishlink."\">".$finishlink."</a>";
					$subject = $conf['sitename']." - "._HELP;
					$message = str_replace("[text]", sprintf(_ADDMAILU, substr($admin[1], 0, 25), _HELP, $link), $conf['mtemp']);
					mail_send($user_email, $conf['adminmail'], $subject, $message, 0, 3);
				}
			}
			referer($admin_file.".php?op=help");
		}
	} elseif ($_POST['posttype'] == "delete") {
		help_delete($sid);
	} else {
		help_add();
	}
}

function help_delete() {
	global $prefix, $db, $admin_file, $id;
	$arg = func_get_args();
	$id = ($arg[0]) ? $arg[0] : $id;
	if ($id) {
		$db->sql_query("DELETE FROM ".$prefix."_favorites WHERE fid = '".$id."' AND modul = 'help'");
		$db->sql_query("DELETE FROM ".$prefix."_help WHERE sid = '".$id."' OR pid = '".$id."'");
	}
	referer($admin_file.".php?op=help");
}

function help_conf() {
	global $admin_file, $confh;
	head();
	$cont = help_navi(0, 2, 0, 0);
	$permtest = end_chmod("config/config_help.php", 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<form name=\"post\" action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_conf\">"
	."<tr><td>"._CDEFIS.":</td><td><input type=\"text\" name=\"defis\" value=\"".urldecode($confh['defis'])."\" maxlength=\"25\" class=\"sl_conf\" placeholder=\""._CDEFIS."\" required></td></tr>"
	."<tr><td>"._C_10.":</td><td><input type=\"number\" name=\"tabcol\" value='".$confh['tabcol']."' class=\"sl_conf\" placeholder=\""._C_10."\" required></td></tr>"
	."<tr><td>"._C_13.":</td><td><input type=\"number\" name=\"listnum\" value='".$confh['listnum']."' class=\"sl_conf\" placeholder=\""._C_13."\" required></td></tr>"
	."<tr><td>"._C_33.":</td><td><input type=\"number\" name=\"num\" value='".$confh['num']."' class=\"sl_conf\" placeholder=\""._C_33."\" required></td></tr>"
	."<tr><td>"._C_34.":</td><td><input type=\"number\" name=\"anum\" value=\"".$confh['anum']."\" class=\"sl_conf\" placeholder=\""._C_34."\" required></td></tr>"
	."<tr><td>"._C_35.":</td><td><input type=\"number\" name=\"nump\" value=\"".$confh['nump']."\" class=\"sl_conf\" placeholder=\""._C_35."\" required></td></tr>"
	."<tr><td>"._C_36.":</td><td><input type=\"number\" name=\"anump\" value=\"".$confh['anump']."\" class=\"sl_conf\" placeholder=\""._C_36."\" required></td></tr>"
	."<tr><td>"._C_32."</td><td>".radio_form($confh['catdesc'], "catdesc")."</td></tr>"
	."<tr><td>"._C_15."</td><td>".radio_form($confh['subcat'], "subcat")."</td></tr>"
	."<tr><td>"._ADDAMAIL."</td><td>".radio_form($confh['addmail'], "addmail")."</td></tr>"
	."<tr><td>"._HELPADD."</td><td>".radio_form($confh['add'], "add")."</td></tr>"
	."<tr><td>"._C_17."</td><td>".radio_form($confh['date'], "date")."</td></tr>"
	."<tr><td>"._C_18."</td><td>".radio_form($confh['read'], "read")."</td></tr>"
	."<tr><td>"._C_20."</td><td>".radio_form($confh['letter'], "letter")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"help_conf_save\"><input type=\"submit\" value='"._SAVECHANGES."' class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close");
	echo $cont;
	foot();
}

function help_conf_save() {
	global $admin_file;
	$xdefis = ($_POST['defis']) ? urlencode($_POST['defis']) : "%3E";
	$content = "\$confh = array();\n"
	."\$confh['defis'] = \"".$xdefis."\";\n"
	."\$confh['tabcol'] = \"".$_POST['tabcol']."\";\n"
	."\$confh['listnum'] = \"".$_POST['listnum']."\";\n"
	."\$confh['num'] = \"".$_POST['num']."\";\n"
	."\$confh['anum'] = \"".$_POST['anum']."\";\n"
	."\$confh['nump'] = \"".$_POST['nump']."\";\n"
	."\$confh['anump'] = \"".$_POST['anump']."\";\n"
	."\$confh['catdesc'] = \"".$_POST['catdesc']."\";\n"
	."\$confh['subcat'] = \"".$_POST['subcat']."\";\n"
	."\$confh['addmail'] = \"".$_POST['addmail']."\";\n"
	."\$confh['add'] = \"".$_POST['add']."\";\n"
	."\$confh['date'] = \"".$_POST['date']."\";\n"
	."\$confh['read'] = \"".$_POST['read']."\";\n"
	."\$confh['letter'] = \"".$_POST['letter']."\";\n";
	save_conf("config/config_help.php", $content);
	header("Location: ".$admin_file.".php?op=help_conf");
}

function help_info() {
	head();
	echo help_navi(0, 3, 0, 0)."<div id=\"repadm_info\">".adm_info(1, "help", 0)."</div>";
	foot();
}

switch($op) {
	case "help":
	help();
	break;
	
	case "help_view":
	help_view();
	break;
	
	case "help_add":
	help_add();
	break;
	
	case "help_save":
	help_save();
	break;
	
	case "help_delete":
	help_delete();
	break;
	
	case "help_conf":
	help_conf();
	break;
	
	case "help_conf_save":
	help_conf_save();
	break;
	
	case "help_info":
	help_info();
	break;
}
?>