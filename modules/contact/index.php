<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("MODULE_FILE")) {
	header("Location: ../../index.php");
	exit;
}
get_lang($conf['name']);
include("config/config_contact.php");

function contact() {
	global $prefix, $db, $conf, $confco, $confu, $currentlang, $stop;
	if ($confco['info']) {
		$pagetitle = $conf['defis']." "._CONTACT;
		$title = _CONTACT;
		$form_block = bb_decode($confco['info'], $conf['name'])."<hr>";
	} else {
		$pagetitle = $conf['defis']." "._FEEDBACK;
		$title = _FEEDBACK;
		$form_block = "";
	}
	head($pagetitle, $form_block);
	$cont = tpl_eval("title", $title);
	if (is_user()) {
		$userinfo = getusrinfo();
		$sender_name = (isset($_POST['sender_name'])) ? text_filter($_POST['sender_name']) : $userinfo['user_name'];
		$sender_email = (isset($_POST['sender_email'])) ? text_filter($_POST['sender_email']) : $userinfo['user_email'];
	} else {
		$sender_name = (isset($_POST['sender_name'])) ? text_filter($_POST['sender_name']) : $confu['anonym'];
		$sender_email = (isset($_POST['sender_email'])) ? text_filter($_POST['sender_email']) : "";
	}
	if ($confco['admins']) {
		$wlang = ($conf['multilingual']) ? "AND (lang = '".$currentlang."' OR lang = '')" : "";
		$result = $db->sql_query("SELECT id, name, title FROM ".$prefix."_admins WHERE smail = '1' ".$wlang." ORDER BY id");
		$send_admin = "";
		if ($db->sql_numrows($result) > 0) {
			while (list($id, $admin_name, $admin_title) = $db->sql_fetchrow($result)) {
				$admin_name = substr($admin_name, 0, 25);
				$admin_title = substr($admin_title, 0, 50);
				$send_admin .= "<option value=\"".$id."\">".$admin_name." - ".$admin_title."</option>";
			}
		}
	}
	$form_block .= "<form action=\"index.php?name=".$conf['name']."\" method=\"post\">"
	."<table class=\"sl_table_form\">";
	$form_block .= ($send_admin) ? "<tr><td>"._TO.":</td><td><select name=\"id\" class=\"sl_field ".$conf['style']."\">".$send_admin."</select></td></tr>" : "";
	$form_block .= "<tr><td>"._YOURNAME.":</td><td><input type=\"text\" name=\"sender_name\" value=\"".$sender_name."\" class=\"sl_field ".$conf['style']."\" placeholder=\""._YOURNAME."\" required></td></tr>"
	."<tr><td>"._YOUREMAIL.":</td><td><input type=\"email\" name=\"sender_email\" value=\"".$sender_email."\" class=\"sl_field ".$conf['style']."\" placeholder=\""._YOUREMAIL."\" required></td></tr>"
	."<tr><td>"._MESSAGE.":</td><td><textarea name=\"message\" cols=\"65\" rows=\"10\" class=\"sl_field ".$conf['style']."\" placeholder=\""._MESSAGE."\" required>".$_POST['message']."</textarea></td></tr>"
	.captcha_random()
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"contact\"><input type=\"hidden\" name=\"opi\" value=\"ds\"><input type=\"submit\" value=\""._SEND."\" class=\"sl_but_blue\"></td></tr></table></form>";
	if ($_POST['opi'] != "ds") {
		$cont .= tpl_eval("open").$form_block.tpl_eval("close");
	} elseif ($_POST['opi'] == "ds") {
		$id = intval($_POST['id']);
		$sender_name = text_filter($_POST['sender_name']);
		$sender_email = text_filter($_POST['sender_email']);
		$message = nl2br(text_filter($_POST['message'], 2), false);
		$stop = array();
		if (!$sender_name) $stop[] = _CERROR3;
		if (!$message) $stop[] = _CERROR1;
		checkemail($sender_email);
		if (captcha_check(1)) $stop[] = _SECCODEINCOR;
		if (!$stop) {
			if ($confco['admins'] && $id) {
				list($adminmail) = $db->sql_fetchrow($db->sql_query("SELECT email FROM ".$prefix."_admins WHERE id = '".$id."' AND smail = '1'"));
				$to = $adminmail;
			} else {
				$to = $conf['adminmail'];
			}
			$subject = $conf['sitename']." - "._FEEDBACK;
			$msg = $conf['sitename']." - "._FEEDBACK."<br><br>"._SENDERNAME.": ".$sender_name."<br>"._SENDEREMAIL.": ".$sender_email."<br><br>"._MESSAGE.": ".$message;
			mail_send($to, $sender_email, $subject, $msg, 1, 1);
			update_points(5);
			$cont .= tpl_warn("warn", _FBMAILSENT, "", 5, "info");
		} else {
			$cont .= tpl_warn("warn", $stop, "", "", "warn");
			$cont .= tpl_eval("open").$form_block.tpl_eval("close");
		}
	}
	echo $cont;
	foot();
}

switch($op) {
	default:
	contact();
	break;
}
?>