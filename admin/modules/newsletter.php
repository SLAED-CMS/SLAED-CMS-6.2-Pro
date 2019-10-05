<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined('ADMIN_FILE') || !is_admin_god()) die('Illegal file access');

function newsletter_navi() {
	panel();
	$narg = func_get_args();
	$ops = array('anewsletter', 'newsletter_add', 'newsletter_info');
	$lang = array(_HOME, _ADD, _INFO);
	return navi_gen(_NEWSLETTER, 'newsletter.png', '', $ops, $lang, '', '', $narg[0], $narg[1], $narg[2], $narg[3]);
}

function anewsletter() {
	global $prefix, $db, $admin_file, $conf;
	head();
	$cont = newsletter_navi(0, 0, 0, 0);
	$result = $db->sql_query("SELECT id, title, content, mails, send, time, endtime FROM ".$prefix."_newsletter ORDER BY id");
	if ($db->sql_numrows($result) > 0) {
		$cont .= tpl_eval('open');
		$cont .= "<table class=\"sl_table_list_sort\"><thead><tr><th>"._ID."</th><th>"._TITLE."</th><th>"._NLEND."</th><th class=\"{sorter: false}\">"._STATUS."</th><th class=\"{sorter: false}\">"._FUNCTIONS."</th></tr></thead><tbody>";
		while (list($id, $title, $content, $mails, $sended, $time, $endtime) = $db->sql_fetchrow($result)) {
			$sendtime = ($endtime > $time) ? strtotime($endtime) - strtotime($time) : 0;
			$active = ($mails && $sended && $conf['newsletter']) ? 1 : 0;
			$cont .= "<tr>"
			."<td>".$id."</td>"
			."<td>".title_tip(_DATE.": ".format_time($time, _TIMESTRING)."<br>"._TIMENL.": ".display_time($sendtime)).$title."</td>"
			."<td>".$sended." "._NLUSER."</td>"
			."<td>".ad_status("", $active)."</td>"
			."<td>".add_menu("<a href=\"".$admin_file.".php?op=newsletter_add&amp;id=".$id."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=newsletter_delete&amp;id=".$id."\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$title."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>")."</td></tr>";
		}
		$cont .= "</tbody></table>";
		$cont .= tpl_eval("close", "");
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function newsletter_add() {
	global $prefix, $db, $admin_file, $conf, $stop;
	if (isset($_REQUEST['id'])) {
		$nid = intval($_REQUEST['id']);
		$result = $db->sql_query("SELECT title, content, mails FROM ".$prefix."_newsletter WHERE id = '".$nid."'");
		list($title, $content, $mails) = $db->sql_fetchrow($result);
	} else {
		$nid = isset($_POST['nid']) ? $_POST['nid'] : "";
		$title = isset($_POST['title']) ? save_text($_POST['title'], 1) : "";
		$content = (isset($_POST['content'])) ? save_text($_POST['content']) : $conf['mtemp'];
		$mails = isset($_POST['mails']) ? $_POST['mails'] : "";
	}
	$count = isset($_POST['count']) ? $_POST['count'] : "";
	$send = isset($_POST['send']) ? $_POST['send'] : "";
	head();
	$cont = newsletter_navi(0, 1, 0, 0);
	if ($stop) $cont .= tpl_warn("warn", $stop, "", "", "warn");
	if ($content) $cont .= preview($title, $content, "", "", "all");
	list($num) = $db->sql_fetchrow($db->sql_query("SELECT Count(user_id) FROM ".$prefix."_users"));
	$sel = ($mails == 1) ? "selected" : "";
	$option = "<option value=\"1\" ".$sel.">"._MASSMAIL." - ".$num."</option>";
	list($num2) = $db->sql_fetchrow($db->sql_query("SELECT Count(user_id) FROM ".$prefix."_users WHERE user_newsletter = '1'"));
	$sel = ($mails == 2) ? "selected" : "";
	$option .= "<option value=\"2\" ".$sel.">"._ANEWSLETTER." - ".$num2."</option>";
	$result3 = $db->sql_query("SELECT id, name, points FROM ".$prefix."_groups WHERE extra = '1' ORDER BY id");
	if ($db->sql_numrows($result3) > 0) {
		while (list($grid, $grname, $points) = $db->sql_fetchrow($result3)) {
			$result4 = $db->sql_query("SELECT user_email FROM ".$prefix."_users WHERE user_group = '".$grid."'");
			$email3 = "";
			$num3 = 0;
			while (list($user_email) = $db->sql_fetchrow($result4)) {
				$email3 .= $user_email.",";
				$num3++;
			}
			$sel = ($email3 == $mails) ? "selected" : "";
			$option .= "<option value=\"".$email3."\" ".$sel.">"._SPEC_GROUP." \"".$grname."\" - ".$num3."</option>";
		}
	}
	$result5 = $db->sql_query("SELECT id, name, points FROM ".$prefix."_groups WHERE extra != '1' ORDER BY id");
	if ($db->sql_numrows($result5) > 0) {
		while (list($grid, $grname, $points) = $db->sql_fetchrow($result5)) {
			$result6 = $db->sql_query("SELECT user_email FROM ".$prefix."_users WHERE user_points >= '".$points."'");
			$email4 = "";
			$num4 = 0;
			while (list($user_email) = $db->sql_fetchrow($result6)) {
				$email4 .= $user_email.",";
				$num4++;
			}
			$sel = ($email4 == $mails) ? "selected" : "";
			$option .= "<option value=\"".$email4."\" ".$sel.">"._GROUP." \"".$grname."\" - ".$num4."</option>";
		}
	}
	if (is_active('money')) {
		$result7 = $db->sql_query("SELECT mail FROM ".$prefix."_money WHERE status = '1'");
		if ($db->sql_numrows($result7) > 0) {
			$aemail = array();
			while (list($user_email) = $db->sql_fetchrow($result7)) $aemail[] = $user_email;
			$aemail = array_unique($aemail);
			$email5 = "";
			$num5 = 0;
			foreach ($aemail as $val) {
				if ($val != "") {
					$email5 .= $val.",";
					$num5++;
				}
			}
			$sel = ($email5 == $mails) ? "selected" : "";
			$option .= "<option value=\"".$email5."\" ".$sel.">"._CLIENTSM." \""._MONEY."\" - ".$num5."</option>";
		}
	}
	if (is_active('order')) {
		$result8 = $db->sql_query("SELECT mail FROM ".$prefix."_order WHERE status = '1'");
		if ($db->sql_numrows($result8) > 0) {
			$aemail = array();
			while (list($user_email) = $db->sql_fetchrow($result8)) $aemail[] = $user_email;
			$aemail = array_unique($aemail);
			$email6 = "";
			$num6 = 0;
			foreach ($aemail as $val) {
				if ($val != "") {
					$email6 .= $val.",";
					$num6++;
				}
			}
			$sel = ($email6 == $mails) ? "selected" : "";
			$option .= "<option value=\"".$email6."\" ".$sel.">"._CLIENTSM." \""._ORDER."\" - ".$num6."</option>";
		}
	}
	if (is_active('shop')) {
		$result9 = $db->sql_query("SELECT email FROM ".$prefix."_clients");
		if ($db->sql_numrows($result9) > 0) {
			$aemail = array();
			while (list($user_email) = $db->sql_fetchrow($result9)) $aemail[] = $user_email;
			$aemail = array_unique($aemail);
			$email7 = "";
			$num7 = 0;
			foreach ($aemail as $val) {
				if ($val != "") {
					$email7 .= $val.",";
					$num7++;
				}
			}
			$sel = ($email7 == $mails) ? "selected" : "";
			$option .= "<option value=\"".$email7."\" ".$sel.">"._CLIENTSM." \""._SHOP."\" ("._ALL.") - ".$num7."</option>";
		}
		$result10 = $db->sql_query("SELECT email FROM ".$prefix."_clients WHERE active = '1'");
		if ($db->sql_numrows($result10) > 0) {
			$aemail = array();
			while (list($user_email) = $db->sql_fetchrow($result10)) $aemail[] = $user_email;
			$aemail = array_unique($aemail);
			$email8 = "";
			$num8 = 0;
			foreach ($aemail as $val) {
				if ($val != "") {
					$email8 .= $val.",";
					$num8++;
				}
			}
			$sel = ($email8 == $mails) ? "selected" : "";
			$option .= "<option value=\"".$email8."\" ".$sel.">"._CLIENTSM." \""._SHOP."\" ("._AKTIVE.") - ".$num8."</option>";
		}
		$result11 = $db->sql_query("SELECT email FROM ".$prefix."_clients WHERE active = '0'");
		if ($db->sql_numrows($result11) > 0) {
			$aemail = array();
			while (list($user_email) = $db->sql_fetchrow($result11)) $aemail[] = $user_email;
			$aemail = array_unique($aemail);
			$email9 = "";
			$num9 = 0;
			foreach ($aemail as $val) {
				if ($val != "") {
					$email9 .= $val.",";
					$num9++;
				}
			}
			$sel = ($email9 == $mails) ? "selected" : "";
			$option .= "<option value=\"".$email9."\" ".$sel.">"._CLIENTSM." \""._SHOP."\" ("._DEAKTIVE.") - ".$num9."</option>";
		}
	}
	$cont .= tpl_eval("open");
	$cont .= "<form name=\"post\" method=\"post\" action=\"".$admin_file.".php\"><table class=\"sl_table_form\">"
	."<tr><td>"._TITLE.":</td><td><input type=\"text\" name=\"title\" value=\"".$title."\" maxlength=\"50\" class=\"sl_form\" placeholder=\""._TITLE."\" required></td></tr>"
	."<tr><td>"._TEXT.":</td><td>".textarea("1", "content", $content, "all", "10", _TEXT, "1")."</td></tr>"
	."<tr><td>"._NLWHERE.":</td><td><select name=\"mails\" class=\"sl_form\">".$option."</select></td></tr>"
	."<tr><td>"._NLCOUNT.":</td><td><select name=\"count\" class=\"sl_form\">";
	$xusnum = 1;
	while ($xusnum <= 25) {
		$sel = ($xusnum == $count) ? " selected" : "";
		$cont .= "<option value=\"".$xusnum."\"".$sel.">".$xusnum."</option>";
		$xusnum++;
	}
	$cont .= "</select></td></tr>";
	$cont .= "<tr><td>"._NLSEND."</td><td>".radio_form($send, "send")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\">".ad_save("nid", $nid, "newsletter_save")."</td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function newsletter_save() {
	global $prefix, $db, $admin_file, $conf, $stop;
	$id = isset($_POST['nid']) ? intval($_POST['nid']) : "";
	$title = save_text($_POST['title'], 1);
	$content = save_text($_POST['content']);
	$mails = $_POST['mails'];
	$count = $_POST['count'];
	$send = intval($_POST['send']);
	if (!$title) $stop[] = _CERROR;
	if (!$content) $stop[] = _CERROR1;
	if (!$stop && $_POST['posttype'] == "save") {
		if ($mails == 1) {
			$emails = "";
			$result = $db->sql_query("SELECT user_email FROM ".$prefix."_users");
			while (list($user_email) = $db->sql_fetchrow($result)) $emails[] = $user_email;
			$emails = implode(",", array_unique($emails));
		} elseif ($mails == 2) {
			$emails = "";
			$result = $db->sql_query("SELECT user_email FROM ".$prefix."_users WHERE user_newsletter = '1'");
			while (list($user_email) = $db->sql_fetchrow($result)) $emails[] = $user_email;
			$emails = implode(",", array_unique($emails));
		} else {
			$emails = $mails;
		}
		$emails = ($send) ? $emails : "";
		if ($id) {
			$db->sql_query("UPDATE ".$prefix."_newsletter SET title = '".$title."', content = '".$content."', mails = '".$emails."', send = '0', time = now(), endtime = '0' WHERE id = '".$id."'");
		} else {
			$db->sql_query("INSERT INTO ".$prefix."_newsletter VALUES (NULL, '".$title."', '".$content."', '".$emails."', '', now(), '')");
		}
		$content = file_get_contents("config/config_global.php");
		$content = str_replace("\$conf['newsletter'] = \"".$conf['newsletter']."\";", "\$conf['newsletter'] = \"".$send."\";", $content);
		$content = str_replace("\$conf['newslettercount'] = \"".$conf['newslettercount']."\";", "\$conf['newslettercount'] = \"".$count."\";", $content);
		$fp = fopen("config/config_global.php", "wb");
		fwrite($fp, $content);
		fclose($fp);
		header("Location: ".$admin_file.".php?op=anewsletter");
	} else {
		newsletter_add();
	}
}

function newsletter_info() {
	head();
	echo newsletter_navi(0, 2, 0, 0)."<div id=\"repadm_info\">".adm_info(1, 0, "newsletter")."</div>";
	foot();
}

switch ($op) {
	case "anewsletter":
	anewsletter();
	break;
	
	case "newsletter_add":
	newsletter_add();
	break;
	
	case "newsletter_save":
	newsletter_save();
	break;
	
	case "newsletter_delete":
	$db->sql_query("DELETE FROM ".$prefix."_newsletter WHERE id = '".$id."'");
	header("Location: ".$admin_file.".php?op=anewsletter");
	break;
	
	case "newsletter_info":
	newsletter_info();
	break;
}
?>