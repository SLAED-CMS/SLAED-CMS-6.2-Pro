<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("MODULE_FILE")) {
	header("Location: ../../index.php");
	exit;
}
get_lang($conf['name']);

function recommend() {
	global $prefix, $db, $conf, $stop;
	$unkey = md5_salt($conf['sitekey']);
	if (is_user()) {
		$userinfo = getusrinfo();
		$yname = (isset($_POST[$unkey])) ? text_filter($_POST[$unkey]) : $userinfo['user_name'];
		$ymail = (isset($_POST['ymail'])) ? text_filter($_POST['ymail']) : $userinfo['user_email'];
	} else {
		$yname = (isset($_POST[$unkey])) ? text_filter($_POST[$unkey]) : "";
		$ymail = (isset($_POST['ymail'])) ? text_filter($_POST['ymail']) : "";
	}
	$fname = (isset($_POST['fname'])) ? text_filter($_POST['fname']) : "";
	$fmail = (isset($_POST['fmail'])) ? text_filter($_POST['fmail']) : "";
	head($conf['defis']." "._RECOMMEND);
	$cont = tpl_eval("title", _RECOMMTITLE);
	if ($stop) $cont .= tpl_warn("warn", $stop, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"index.php?name=".$conf['name']."\" method=\"post\"><table class=\"sl_table_form\">"
	."<tr><td>"._YOURNAME.":</td><td><input type=\"text\" name=\"".$unkey."\" value=\"".$yname."\" class=\"sl_field ".$conf['style']."\" placeholder=\""._YOURNAME."\" required></td></tr>"
	."<tr><td>"._YOUREMAIL.":</td><td><input type=\"email\" name=\"ymail\" value=\"".$ymail."\" class=\"sl_field ".$conf['style']."\" placeholder=\""._YOUREMAIL."\" required></td></tr>"
	."<tr><td>"._FFRIENDNAME.":</td><td><input type=\"text\" name=\"fname\" value=\"".$fname."\" class=\"sl_field ".$conf['style']."\" placeholder=\""._FFRIENDNAME."\" required></td></tr>"
	."<tr><td>"._FFRIENDEMAIL.":</td><td><input type=\"email\" name=\"fmail\" value=\"".$fmail."\" class=\"sl_field ".$conf['style']."\" placeholder=\""._FFRIENDEMAIL."\" required></td></tr>"
	.captcha_random(2)
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"recommend_send\"><input type=\"submit\" value=\""._SEND."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close");
	echo $cont;
	foot();
}

function recommend_send() {
	global $conf, $stop;
	$unkey = md5_salt($conf['sitekey']);
	$yname = text_filter(substr($_POST[$unkey], 0, 25));
	$fname = text_filter(substr($_POST['fname'], 0, 25));
	$ymail = text_filter($_POST['ymail']);
	$fmail = text_filter($_POST['fmail']);
	$stop = array();
	if (!$yname || !$fname) $stop[] = _ERROR_ALL;
	checkemail($ymail);
	checkemail($fmail);
	if (captcha_check(2)) $stop[] = _SECCODEINCOR;
	if (!$stop) {
		$subject = $conf['sitename']." - "._INTSITE;
		$message = _HELLO." ".$fname."!<br><br>"._YOURFRIEND." ".$yname." "._OURSITE." ".$conf['sitename']." "._INTSENT."<br><br>"._SITENAME.": ".$conf['sitename']." ".urldecode($conf['defis'])." ".$conf['slogan']."<br>"._SITEURL.": <a href=\"".$conf['homeurl']."\" target=\"_blank\" title=\"".$conf['sitename']."\">".$conf['homeurl']."</a>";
		mail_send($fmail, $ymail, $subject, $message, 0, 3);
		update_points(38);
		head($conf['defis']." "._RECOMMEND, _FREFERENCE);
		echo tpl_eval("title", _RECOMMTITLE).tpl_warn("warn", _FREFERENCE." ".$fname.".<br>"._THANKSREC, "", 5, "info");
		foot();
	} else {
		recommend();
	}
}

switch($op) {
	default:
	recommend();
	break;

	case "recommend_send":
	recommend_send();
	break;
}
?>