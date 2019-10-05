<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("ADMIN_FILE") || !is_admin_god()) die("Illegal file access");

function security_navi() {
	global $admin_file;
	panel();
	$narg = func_get_args();
	$ops = array("security_show", "security_block", "security_pass", "security_conf", "security_info");
	$lang = array(_HOME, _BANNED, _SEC_PASS, _PREFERENCES, _INFO);
	$sops = array("", "");
	$slang = array(_BANNED_IP, _BANNED_USERS);
	return navi_gen(_SECURITY, "security.png", "", $ops, $lang, $sops, $slang, $narg[0], $narg[1], $narg[2], $narg[3], $narg[4]);
}

function security_show() {
	global $admin_file;
	head();
	$cont = security_navi(0, 0, 0, 0, "");
	$permtest = end_chmod("config/logs", 777);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<table class=\"sl_table_list_sort\"><thead><tr><th>"._TITLE."</th><th>"._SIZE."</th><th>"._DATE."</th><th class=\"{sorter: false}\">"._FUNCTIONS."</th></tr></thead><tbody>";
	$handle = opendir("config/logs");
	while (false !== ($file = readdir($handle))) {
		if (preg_match("#(.*)\.txt$#", $file)) {
			$langs = array(".txt" => "", "dump" => _SEC_STAT_DUM, "dump_log" => _SEC_STAT_DUML, "error" => _SEC_STAT_ERROR_D, "error_site" => _SEC_STAT_ERROR_S, "error_sql" => _SEC_STAT_ERROR_SQL, "hack" => _SEC_STAT_HACK, "log" => _SEC_STAT_LOG, "log_admin" => _SEC_STAT_A, "log_user" => _SEC_STAT_U, "warn" => _SEC_STAT_WARN);
			$title = strtr($file, $langs);
			$filesize = filesize("config/logs/".$file);
			$name = str_replace(".txt", "", $file);
			$cont .= "<tr><td>".title_tip(_FILE.": config/logs/".$file).$title."</td>"
			."<td>".files_size($filesize)."</td>"
			."<td>".date(_TIMESTRING, filemtime("config/logs/".$file))."</td>"
			."<td>".add_menu("<a href=\"".$admin_file.".php?op=security_file&amp;file=".$name."\" title=\""._INFO."\">"._INFO."</a>||<a href=\"".$admin_file.".php?op=security_down&amp;file=".$name."\" title=\""._DOWN."\">"._DOWN."</a>||<a href=\"".$admin_file.".php?op=security_del&amp;file=".$name."\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$title."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>")."</td></tr>";
		}
	}
	closedir($handle);
	$cont .= "</tbody></table>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function security_file() {
	global $admin_file;
	head();
	$cont = security_navi(0, 0, 0, 0, "");
	$fname = isset($_GET['file']) ? analyze($_GET['file']) : "";
	if ($fname) {
		$langs = array("dump" => _SEC_STAT_DUM, "dump_log" => _SEC_STAT_DUML, "error" => _SEC_STAT_ERROR_D, "error_site" => _SEC_STAT_ERROR_S, "error_sql" => _SEC_STAT_ERROR_SQL, "hack" => _SEC_STAT_HACK, "log" => _SEC_STAT_LOG, "log_admin" => _SEC_STAT_A, "log_user" => _SEC_STAT_U, "warn" => _SEC_STAT_WARN);
		$title = strtr($fname, $langs);
		$file = "config/logs/".$fname.".txt";
		$content = file_get_contents($file);
		$permtest = end_chmod($file, 666);
		if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
		$cont .= tpl_eval("open")."<table class=\"sl_table_edit\"><tr><td><h5>".$title."</h5></td></tr><tr><td>".textarea_code("code", "", "sl_form", "message/http", $content)."</td></tr></table>".tpl_eval("close", "");
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function security_block() {
	global $admin_file, $conf;
	head();
	$cont = security_navi(0, 1, 1, 0, "security");
	include("config/config_security.php");
	$permtest = end_chmod("config/config_security.php", 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	if (isset($_GET['send'])) $cont .= tpl_warn("warn", _MAIL_SEND, "", "", "info");
	$cont .= tpl_eval("open");
	$cont .= "<div id=\"tabcs0\" class=\"tabcont\">";
	$bip = explode("||", $confs['blocker_ip']);
	if ($confs['blocker_ip']) {
		$cont .= "<table class=\"sl_table_list_sort\"><thead><tr><th>"._IP."</th><th>"._IP_MASK."</th><th>"._HASH."</th><th>"._DATE."</th><th class=\"{sorter: false}\">"._FUNCTIONS."</th></tr></thead><tbody>";
		foreach ($bip as $val) {
			if ($val != "") {
				$binfo = explode("|", $val);
				if ($binfo[1] == 4) $mask = "255.255.255.255";
				if ($binfo[1] == 3) $mask = "255.255.255.***";
				if ($binfo[1] == 2) $mask = "255.255.***.***";
				if ($binfo[1] == 1) $mask = "255.***.***.***";
				$cont .= "<tr><td>".title_tip(_BANN_REAS.": ".$binfo[4]).user_geo_ip($binfo[0], 4)."</td>"
				."<td>".$mask."</td>"
				."<td>".$binfo[2]."</td>"
				."<td>".rest_time($binfo[3])."</td>"
				."<td>".add_menu("<a href=\"".$admin_file.".php?op=security_block_save&amp;ip=".$binfo[0]."&amp;ip_mask=".$binfo[1]."&amp;hash=".$binfo[2]."&amp;time=".$binfo[3]."&amp;id=1\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$binfo[0]."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>")."</td></tr>";
			}
		}
		$cont .= "</tbody></table><hr>";
	}
	$ip = isset($_GET['new_ip']) ? $_GET['new_ip'] : "";
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_form\">"
	."<tr><td>"._IP.":</td><td><input type=\"text\" name=\"ip\" value=\"".$ip."\" maxlength=\"255\" class=\"sl_form\" placeholder=\""._IP."\" required></td></tr>"
	."<tr><td>"._IP_MASK.":</td><td><select name=\"ip_mask\" class=\"sl_form\">"
	. "<option value=\"4\"";
	if ($ip_mask == 4) $cont .= " selected";
	$cont .= ">255.255.255.255</option>"
	."<option value=\"3\"";
	if ($ip_mask == 3) $cont .= " selected";
	$cont .= ">255.255.255.***</option>"
	."<option value=\"2\"";
	if ($ip_mask == 2) $cont .= " selected";
	$cont .= ">255.255.***.***</option>"
	."<option value=\"1\"";
	if ($ip_mask == 1) $cont .= " selected";
	$cont .= ">255.***.***.***</option>"
	."</select></td></tr>"
	."<tr><td>"._HASH.":</td><td><input type=\"text\" name=\"hash\" value=\"".$hash."\" maxlength=\"255\" class=\"sl_form\" placeholder=\""._HASH."\"></td></tr>"
	."<tr><td>"._TIME.":</td><td><input type=\"number\" name=\"time\" value=\"".$time."\" class=\"sl_form\" placeholder=\""._TIME."\" required></td></tr>"
	."<tr><td>"._BANN_REAS.":</td><td><textarea name=\"info\" cols=\"65\" rows=\"5\" class=\"sl_form\" placeholder=\""._BANN_REAS."\" required>".$info."</textarea></td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"security_block_save\"><input type=\"hidden\" name=\"id\" value=\"2\"><input type=\"submit\" value=\""._ADD."\" class=\"sl_but_blue\"></td></tr></table></form>"
	."</div>";
	$cont .= "<div id=\"tabcs1\" class=\"tabcont\">";
	$bip = explode("||", $confs['blocker_user']);
	if ($confs['blocker_user']) {
		$cont .= "<table class=\"sl_table_list_sort\"><thead><tr><th>"._NICKNAME."</th><th>"._BANN_REAS."</th><th>"._DATE."</th><th class=\"{sorter: false}\">"._FUNCTIONS."</th></tr></thead><tbody>";
		foreach ($bip as $val) {
			if ($val != "") {
				$binfo = explode("|", $val);
				$cont .= "<tr><td>".user_info($binfo[0])."</td>"
				."<td>".$binfo[2]."</td>"
				."<td>".rest_time($binfo[1])."</td>"
				."<td>".add_menu("<a href=\"".$admin_file.".php?op=security_block_save&amp;name=".$binfo[0]."&amp;time=".$binfo[1]."&amp;id=3\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$binfo[0]."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>")."</td></tr>";
			}
		}
		$cont .= "</tbody></table><hr>";
	}
	$name = isset($_GET['name']) ? $_GET['name'] : "";
	$check = ($_COOKIE['sl_close_9'] == "0") ? "" : " checked";
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_form\">"
	."<tr><td>"._NICKNAME.":</td><td>".get_user_search("name", $name, "25", "sl_form", "1")."</td></tr>"
	."<tr><td>"._TIME.":</td><td><input type=\"number\" name=\"time\" value=\"".$time."\" class=\"sl_form\" placeholder=\""._TIME."\" required></td></tr>"
	."<tr><td>"._BANN_REAS.":</td><td><textarea name=\"info\" cols=\"65\" rows=\"5\" class=\"sl_form\" placeholder=\""._BANN_REAS."\" required>".$info."</textarea></td></tr>"
	."<tr><td>"._MAIL_SENDE."</td><td><input type=\"checkbox\" name=\"mail\" value=\"1\" OnClick=\"CloseOpen('sl_close_9', 0);\"".$check."></td></tr>"
	."<tr><td colspan=\"2\"><div id=\"sl_close_9\"><table class=\"sl_table_form\"><tr><td>"._MAIL_TEXT.":<div class=\"sl_small\">"._MAIL_INFO."</div></td><td>".textarea("1", "mailtext", replace_break(str_replace("[text]", ""._BANN_INFO."\n\n"._BANN_TERM.": [time]\n"._BANN_REAS.": [info]", $conf['mtemp'])), "all", "10")."</td></tr></table></div></td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"security_block_save\"><input type=\"hidden\" name=\"id\" value=\"4\"><input type=\"submit\" value=\""._ADD."\" class=\"sl_but_blue\"></td></tr></table></form>"
	."</div>"
	."<script>
		var countries=new ddtabcontent(\"securitys\")
		countries.setpersist(true)
		countries.setselectedClassTarget(\"link\")
		countries.init()
	</script>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function security_block_save() {
	global $prefix, $db, $admin_file, $conf;
	$send = "";
	$id = $_REQUEST['id'];
	$ip = $_REQUEST['ip'];
	$name = $_REQUEST['name'];
	$mail = $_POST['mail'];
	include("config/config_security.php");
	if ($id == 1 && $ip) {
		$ip_mask = $_GET['ip_mask'];
		$hash = $_GET['hash'];
		$time = $_GET['time'];
		$blocker_ip = preg_replace("/".$ip."\|".$ip_mask."\|".$hash."\|".$time."\|(.*)\|\|/iU", "", $confs['blocker_ip']);
		$content = file_get_contents("config/config_security.php");
		$content = str_replace("\$confs['blocker_ip'] = \"".$confs['blocker_ip']."\";", "\$confs['blocker_ip'] = \"".$blocker_ip."\";", $content);
		$fp = fopen("config/config_security.php", "wb");
		fwrite($fp, $content);
		fclose($fp);
	} elseif ($id == 2 && $ip) {
		$ip_mask = $_POST['ip_mask'];
		$hash = ($_POST['hash']) ? $_POST['hash'] : "0";
		$time = (is_numeric($_POST['time'])) ? time() + ($_POST['time'] * 86400) : time() + 2592000;
		$info = (trim($_POST['info'])) ? text_filter($_POST['info']) : _BANN_INFO;
		$content = file_get_contents("config/config_security.php");
		$content = str_replace("\$confs['blocker_ip'] = \"".$confs['blocker_ip']."\";", "\$confs['blocker_ip'] = \"".$confs['blocker_ip'].$ip."|".$ip_mask."|".$hash."|".$time."|".$info."||\";", $content);
		$fp = fopen("config/config_security.php", "wb");
		fwrite($fp, $content);
		fclose($fp);
	} elseif ($id == 3 && $name) {
		$time = $_GET['time'];
		$blocker_user = preg_replace("/".$name."\|".$time."\|(.*)\|\|/iU", "", $confs['blocker_user']);
		$content = file_get_contents("config/config_security.php");
		$content = str_replace("\$confs['blocker_user'] = \"".$confs['blocker_user']."\";", "\$confs['blocker_user'] = \"".$blocker_user."\";", $content);
		$fp = fopen("config/config_security.php", "wb");
		fwrite($fp, $content);
		fclose($fp);
	} elseif ($id == 4 && $name) {
		$time = (is_numeric($_POST['time'])) ? time() + ($_POST['time'] * 86400) : time() + 2592000;
		$info = (trim($_POST['info'])) ? text_filter($_POST['info']) : _BANN_INFO;
		$content = file_get_contents("config/config_security.php");
		$content = str_replace("\$confs['blocker_user'] = \"".$confs['blocker_user']."\";", "\$confs['blocker_user'] = \"".$confs['blocker_user'].$name."|".$time."|".$info."||\";", $content);
		$fp = fopen("config/config_security.php", "wb");
		fwrite($fp, $content);
		fclose($fp);
		if ($mail) {
			list($mail) = $db->sql_fetchrow($db->sql_query("SELECT user_email FROM ".$prefix."_users WHERE user_name='".$name."'"));
			$subject = $conf['sitename']." - "._SECURITY;
			$msg = nl2br(bb_decode(str_replace("[time]", rest_time($time), str_replace("[info]", $_POST['info'], $_POST['mailtext'])), "all"), false);
			mail_send($mail, $conf['adminmail'], $subject, $msg, 0, 3);
			$send = "&send=1";
		}
	}
	header("Location: ".$admin_file.".php?op=security_block".$send);
}

function security_pass() {
	global $admin_file;
	head();
	$cont = security_navi(0, 2, 0, 0, "");
	include("config/config_secure.php");
	$permtest = end_chmod("config/config_secure.php", 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= (!$confsp['login'] || !$confsp['password']) ? tpl_warn("warn", _SEC_AUTH_INFO, "", "", "warn") : tpl_warn("warn", _SEC_AUTH_OK, "", "", "info");
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_conf\">"
	."<tr><td>"._SEC_ADMIN_MASK.":</td><td><select name=\"admin_mask\" class=\"sl_conf\">";
	$cont .= "<option value=\"4\"";
	if ($confsp['admin_mask'] == 4) $cont .= " selected";
	$cont .= ">255.255.255.255</option>"
	."<option value=\"3\"";
	if ($confsp['admin_mask'] == 3) $cont .= " selected";
	$cont .= ">255.255.255.***</option>"
	."<option value=\"2\"";
	if ($confsp['admin_mask'] == 2) $cont .= " selected";
	$cont .= ">255.255.***.***</option>"
	."<option value=\"1\"";
	if ($confsp['admin_mask'] == 1) $cont .= " selected";
	$cont .= ">255.***.***.***</option></select></td></tr>"
	."<tr><td>"._SEC_ADMIN_IP.":<div class=\"sl_small\">"._NOKOMA."</div></td><td><textarea name=\"admin_ip\" cols=\"65\" rows=\"5\" class=\"sl_conf\" placeholder=\""._SEC_ADMIN_IP."\">".$confsp['admin_ip']."</textarea></td></tr>";
	if (!$confsp['login'] || !$confsp['password']) {
		$cont .= "<tr><td>"._SEC_LOGIN.":</td><td><input type=\"text\" name=\"login\" value=\"".$confsp['login']."\" maxlength=\"255\" class=\"sl_conf\" placeholder=\""._SEC_LOGIN."\"></td></tr>"
		."<tr><td>"._SEC_PASSWORD.":</td><td><input type=\"text\" name=\"password\" value=\"".$confsp['password']."\" maxlength=\"255\" class=\"sl_conf\" placeholder=\""._SEC_PASSWORD."\"></td></tr>";
	} else {
		$cont .= "<input type=\"hidden\" name=\"login\" value=\"0\"><input type=\"hidden\" name=\"password\" value=\"0\">";
	}
	$cont .= "<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"security_pass_save\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function security_pass_save() {
	global $admin_file;
	include("config/config_secure.php");
	$protect = array("\n" => "", "\t" => "", "\r" => "", " " => "");
	$xadmin_ip = strtr($_POST['admin_ip'], $protect);
	$xlogin = ($_POST['login']) ? md5_salt($_POST['login']) : $confsp['login'];
	$xpassword = ($_POST['password']) ? md5_salt($_POST['password']) : $confsp['password'];
	$content = "\$confsp = array();\n"
	."\$confsp['admin_mask'] = \"".$_POST['admin_mask']."\";\n"
	."\$confsp['admin_ip'] = \"".$xadmin_ip."\";\n"
	."\$confsp['login'] = \"".$xlogin."\";\n"
	."\$confsp['password'] = \"".$xpassword."\";\n";
	save_conf("config/config_secure.php", $content, 1);
	header("Location: ".$admin_file.".php?op=security_pass");
}

function security_conf() {
	global $admin_file;
	head();
	$cont = security_navi(0, 3, 0, 0, "");
	include("config/config_security.php");
	$permtest = end_chmod("config/config_security.php", 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_conf\">"
	."<tr><td>"._SFLOOD.":</td><td><select name=\"flood\" class=\"sl_conf\">"
	. "<option value=\"0\"";
	if ($confs['flood'] == 0) $cont .= " selected";
	$cont .= ">"._NO."</option>"
	."<option value=\"1\"";
	if ($confs['flood'] == 1) $cont .= " selected";
	$cont .= ">"._SFLOOD_1."</option>"
	."<option value=\"2\"";
	if ($confs['flood'] == 2) $cont .= " selected";
	$cont .= ">"._SFLOOD_2."</option>"
	."<option value=\"3\"";
	if ($confs['flood'] == 3) $cont .= " selected";
	$cont .= ">"._SFLOOD_3."</option>"
	."</select></td></tr>"
	."<tr><td>"._SFLOD_T.":</td><td><input type=\"number\" name=\"flood_t\" value=\"".$confs['flood_t']."\" class=\"sl_conf\" placeholder=\""._SFLOD_T."\" required></td></tr>"
	."<tr><td>"._SEC_COOKIE.":</td><td><input type=\"text\" name=\"blocker_cookie\" value=\"".$confs['blocker_cookie']."\" maxlength=\"255\" class=\"sl_conf\" placeholder=\""._SEC_COOKIE."\" required></td></tr>"
	."<tr><td>"._SEC_LOG_SIZE.":</td><td><input type=\"number\" name=\"log_size\" value=\"".$confs['log_size']."\" class=\"sl_conf\" placeholder=\""._SEC_LOG_SIZE."\" required></td></tr>"
	."<tr><td>"._SEC_LOG_DS.":</td><td><input type=\"number\" name=\"sess_d\" value=\"".intval($confs['sess_d'] / 60)."\" class=\"sl_conf\" placeholder=\""._SEC_LOG_DS."\" required></td></tr>"
	."<tr><td>"._SEC_LOG_DB.":</td><td><input type=\"number\" name=\"sess_b\" value=\"".intval($confs['sess_b'] / 60)."\" class=\"sl_conf\" placeholder=\""._SEC_LOG_DB."\" required></td></tr>"
	."<tr><td>"._SEC_DB."</td><td>".radio_form($confs['log_b'], "log_b")."</td></tr>"
	."<tr><td>"._SEC_VIEW."</td><td>".radio_form($confs['error'], "error")."</td></tr>"
	."<tr><td>"._SEC_VIEW_JAVA."</td><td>".radio_form($confs['error_java'], "error_java")."</td></tr>"
	."<tr><td>"._SEC_STAT."</td><td>".radio_form($confs['error_log'], "error_log")."</td></tr>"
	."<tr><td>"._SEC_URL_GET."</td><td>".radio_form($confs['url_get'], "url_get")."</td></tr>"
	."<tr><td>"._SEC_URL_POST."</td><td>".radio_form($confs['url_post'], "url_post")."</td></tr>"
	."<tr><td>"._SEC_REF_POST."</td><td>".radio_form($confs['ref_post'], "ref_post")."</td></tr>"
	."<tr><td>"._SEC_MAIL_SEND."</td><td>".radio_form($confs['mail'], "mail")."</td></tr>"
	."<tr><td>"._SEC_MAIL_W_SEND."</td><td>".radio_form($confs['mail_w'], "mail_w")."</td></tr>"
	."<tr><td>"._SEC_MAIL_D_SEND."</td><td>".radio_form($confs['mail_d'], "mail_d")."</td></tr>"
	."<tr><td>"._SEC_HACK_STAT."</td><td>".radio_form($confs['write_h'], "write_h")."</td></tr>"
	."<tr><td>"._SEC_WARN_STAT."</td><td>".radio_form($confs['write_w'], "write_w")."</td></tr>"
	."<tr><td>"._SEC_LOG."</td><td>".radio_form($confs['log'], "log")."</td></tr>"
	."<tr><td>"._SEC_LOG_D."</td><td>".radio_form($confs['log_d'], "log_d")."</td></tr>"
	."<tr><td>"._SEC_LOG_A."</td><td>".radio_form($confs['log_a'], "log_a")."</td></tr>"
	."<tr><td>"._SEC_LOG_U."</td><td>".radio_form($confs['log_u'], "log_u")."</td></tr>"
	."<tr><td>"._SEC_WARN_BLOCK."</td><td>".radio_form($confs['block'], "block")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"security_conf_save\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function security_conf_save() {
	global $admin_file;
	include("config/config_security.php");
	$xflood_t = (!intval($_POST['flood_t'])) ? 1 : $_POST['flood_t'];
	$xlog_size = (!intval($_POST['log_size'])) ? 1048576 : $_POST['log_size'];
	$xsess_d = (!intval($_POST['sess_d'])) ? 86400 : $_POST['sess_d'] * 60;
	$xsess_b = (!intval($_POST['sess_b'])) ? 86400 : $_POST['sess_b'] * 60;
	$content = "\$confs = array();\n"
	."\$confs['flood'] = \"".$_POST['flood']."\";\n"
	."\$confs['flood_t'] = \"".$xflood_t."\";\n"
	."\$confs['blocker_cookie'] = \"".$_POST['blocker_cookie']."\";\n"
	."\$confs['log_size'] = \"".$xlog_size."\";\n"
	."\$confs['sess_d'] = \"".$xsess_d."\";\n"
	."\$confs['sess_b'] = \"".$xsess_b."\";\n"
	."\$confs['blocker_ip'] = \"".$confs['blocker_ip']."\";\n"
	."\$confs['blocker_user'] = \"".$confs['blocker_user']."\";\n"
	."\$confs['log_b'] = \"".$_POST['log_b']."\";\n"
	."\$confs['error'] = \"".$_POST['error']."\";\n"
	."\$confs['error_java'] = \"".$_POST['error_java']."\";\n"
	."\$confs['error_log'] = \"".$_POST['error_log']."\";\n"
	."\$confs['url_get'] = \"".$_POST['url_get']."\";\n"
	."\$confs['url_post'] = \"".$_POST['url_post']."\";\n"
	."\$confs['ref_post'] = \"".$_POST['ref_post']."\";\n"
	."\$confs['mail'] = \"".$_POST['mail']."\";\n"
	."\$confs['mail_w'] = \"".$_POST['mail_w']."\";\n"
	."\$confs['mail_d'] = \"".$_POST['mail_d']."\";\n"
	."\$confs['write_h'] = \"".$_POST['write_h']."\";\n"
	."\$confs['write_w'] = \"".$_POST['write_w']."\";\n"
	."\$confs['log'] = \"".$_POST['log']."\";\n"
	."\$confs['log_d'] = \"".$_POST['log_d']."\";\n"
	."\$confs['log_a'] = \"".$_POST['log_a']."\";\n"
	."\$confs['log_u'] = \"".$_POST['log_u']."\";\n"
	."\$confs['block'] = \"".$_POST['block']."\";\n";
	save_conf("config/config_security.php", $content);
	header("Location: ".$admin_file.".php?op=security_conf");
}

function security_info() {
	head();
	echo security_navi(0, 4, 0, 0, "")."<div id=\"repadm_info\">".adm_info(1, 0, "security")."</div>";
	foot();
}

switch($op) {
	case "security_show":
	security_show();
	break;
	
	case "security_file":
	security_file();
	break;
	
	case "security_down":
	$fname = isset($_GET['file']) ? analyze($_GET['file']) : "";
	if ($fname) {
		stream("config/logs/".$fname.".txt", date("d.m.Y")."_".$fname.".txt");
	} else {
		header("Location: ".$admin_file.".php?op=security_show");
	}
	break;
	
	case "security_del":
	$fname = isset($_GET['file']) ? analyze($_GET['file']) : "";
	if ($fname) unlink("config/logs/".$fname.".txt");
	header("Location: ".$admin_file.".php?op=security_show");
	break;
	
	case "security_block":
	security_block();
	break;
	
	case "security_block_save":
	security_block_save();
	break;
	
	case "security_pass":
	security_pass();
	break;
	
	case "security_pass_save":
	security_pass_save();
	break;
	
	case "security_conf":
	security_conf();
	break;
	
	case "security_conf_save":
	security_conf_save();
	break;
	
	case "security_info":
	security_info();
	break;
}
?>