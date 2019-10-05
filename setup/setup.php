<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("SETUP_FILE")) die("Illegal File Access");
define("FUNC_FILE", true);

error_reporting(0);
include("config/config_global.php");

$is_safe_mode = (ini_get("safe_mode") == "1") ? 1 : 0;
if (!$is_safe_mode && function_exists("set_time_limit")) set_time_limit(1800);
$host = (getenv("HTTP_HOST")) ? getenv("HTTP_HOST") : getenv("SERVER_NAME");
$clang = (isset($_COOKIE["lang"])) ? analyze($_COOKIE["lang"]) : "";
$op = (isset($_REQUEST['op'])) ? analyze($_REQUEST['op']) : "";

if (!empty($clang)) {
	include_once("language/lang-".$clang.".php");
	include_once("setup/language/lang-".$clang.".php");
} else {
	include_once("language/lang-english.php");
	include_once("setup/language/lang-english.php");
}

if ($conf['lic_h'] != "UG93ZXJlZCBieSA8YSBocmVmPSJodHRwOi8vd3d3LnNsYWVkLm5ldCIgdGFyZ2V0PSJfYmxhbmsiIHRpdGxlPSJTTEFFRCBDTVMiPlNMQUVEIENNUzwvYT4gJmNvcHk7IDIwMDUt" || $conf['lic_f'] != "IFNMQUVELiBBbGwgcmlnaHRzIHJlc2VydmVkLg==") get_exit(_NO_LICENSE, 0);
$copyright = base64_decode($conf['lic_h']).date("Y").base64_decode($conf['lic_f']);

function deflang($con) {
	$langs = array("english" => _ENGLISH, "french" => _FRENCH, "german" => _GERMAN, "polish" => _POLISH, "russian" => _RUSSIAN, "ukrainian" => _UKRAINIAN);
	$out = strtr($con, $langs);
	return $out;
}

function head() {
	global $title, $conf;
	echo "<!DOCTYPE html>\n"
	."<html>\n"
	."<head>\n"
	."<meta charset=\""._CHARSET."\">\n"
	."<title>"._SETUP_SLAED." - ".$title."</title>\n"
	."<meta name=\"resource-type\" content=\"document\">\n"
	."<meta name=\"document-state\" content=\"dynamic\">\n"
	."<meta name=\"distribution\" content=\"global\">\n"
	."<meta name=\"author\" content=\"".$conf['sitename']."\">\n"
	."<meta name=\"generator\" content=\"SLAED CMS ".$conf['version']."\">\n"
	."<link rel=\"stylesheet\" href=\"setup/templates/style.css\">\n"
	."</head>\n"
	."<body id=\"page_bg\">\n"
	."<div id=\"wrapper\">"
	."<div id=\"header\">"
	."<div id=\"header-left\">"
	."<div id=\"header-right\">"
	."<div id=\"logo\">"
	."<img src=\"setup/templates/images/logotype.png\" alt=\"".$title."\">"
	."</div>"
	."</div>"
	."</div>"
	."</div>"
	."<div id=\"shadow-l\">"
	."<div id=\"shadow-r\">"
	."<div id=\"container\">"
	."<h3 class=\"btitle\">".$title."</h3>";
}

function foot() {
	global $conf, $copyright;
	echo "</div>"
	."</div>"
	."</div>"
	."<div id=\"footer\">"
	."<div id=\"footer-r\">"
	."<div id=\"footer-l\">"
	."<div id=\"copyright\">".$copyright."</div>"
	."</div>"
	."</div>"
	."</div>"
	."</div>\n"
	."</body>\n"
	."</html>";
}

function text_info($table, $id) {
	$text = "<tr><td>"._TABLE.":</td><td>".$table." ".(($id) ? "</td><td><span class=\"sl_green\">"._OK."</span></td>" : "<td><span class=\"sl_red\">"._ERROR."</span></td>")."</tr>";
	return $text;
}

function language() {
	global $title;
	$title = (PHP_VERSION < "4.3.0") ? _PHPSETUP : _LANG;
	head();
	$cont = "<table class=\"sl_table\">";
	$handle = opendir('setup/language');
	while (false !== ($file = readdir($handle))) {
		if (preg_match("/^lang\-(.+)\.php/", $file, $matches)) $langlist[] = $matches[1];
	}
	closedir($handle);
	sort($langlist);
	$a = 3;
	$i = 1;
	$tdwidth = intval(100/$a);
	foreach ($langlist as $key => $val) {
		$altlang = deflang($langlist[$key]);
		if (($i - 1) % $a == 0) $cont .= "<tr>";
		$cont .= "<td style=\"width: ".$tdwidth."%;\" class=\"sl_center\"><a href=\"setup.php?op=lang&amp;id=".$langlist[$key]."\" title=\"".$altlang."\"><img src=\"setup/templates/images/".$langlist[$key].".png\" alt=\"".$altlang."\"><br><b>".$altlang."</b></a></td>";
		if ($i % $a == 0) $cont .= "</tr>\n";
		$i++;
	}
	if (isset($_GET["lang"]) && $_GET["lang"] == 1) {
		$cont .= "<tr><td colspan=\"".$a."\" class=\"sl_center\"><form action=\"setup.php\" method=\"post\"><input type=\"hidden\" name=\"op\" value=\"config\"><input type=\"submit\" value=\""._NEXT_SE."\" class=\"sl_but_blue\"></form></td></tr>";
	}
	$cont .= "</table>";
	echo $cont;
	foot();
}

function lang() {
	$lang = (preg_match("#[^a-zA-Z0-9_]#", $_GET["id"])) ? "english" : $_GET["id"];
	setcookie ("lang", $lang, time() + 3600);
	header("Location: setup.php?op=language&lang=1");
}

function get_exit($msg, $typ) {
	global $conf;
	$cont = "<!DOCTYPE html>\n"
	."<html>\n"
	."<head>\n"
	."<meta charset=\""._CHARSET."\">\n"
	."<title>"._SETUP_SLAED."</title>\n"
	."<meta name=\"author\" content=\"".$conf['sitename']."\">\n"
	."<meta name=\"generator\" content=\"SLAED CMS ".$conf['version']."\">\n";
	$cont .= ($typ) ? "<meta http-equiv=\"refresh\" content=\"5; url=".$conf['homeurl']."/index.php\">\n" : "";
	$cont .= "</head>\n"
	."<body>\n"
	."<div style=\"margin: 25%;\">\n"
	."<div style=\"text-align: center;\"><img src=\"setup/templates/images/logotype.png\" alt=\"".$conf['sitename']."\" title=\"".$conf['sitename']."\"></div>\n"
	."<div style=\"margin-top: 50px; font: 18px Arial, Tahoma, sans-serif, Verdana; color: #1a4674; font-weight: bold; text-align: center;\">".$msg."</div>\n"
	."</div>\n"
	."</body>\n"
	."</html>";
	die($cont);
}

function analyze($var) {
	$var = (preg_match("#[^a-zA-Z0-9_]#", $var)) ? "" : $var;
	return $var;
}

function gen_pass($m) {
	$m = intval($m);
	$pass = "";
	for ($i = 0; $i < $m; $i++) {
		$te = mt_rand(48, 122);
		if (($te > 57 && $te < 65) || ($te > 90 && $te < 97)) $te = $te - 9;
		$pass .= chr($te);
	}
	return $pass;
}

function md5_salt($pass) {
	global $conf;
	$crypt = md5("0a958d066ab41444be55359c31702bcf".$pass);
	return $crypt;
}

function getip() {
	if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) {
		$ip = getenv("REMOTE_ADDR");
	} elseif (!empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
		$ip = $_SERVER['REMOTE_ADDR'];
	} else {
		$ip = "0.0.0.0";
	}
	return $ip;
}

function config() {
	global $title;
	$title = _CONFIG;
	if (file_exists("config/config.php")) {
		chmod("config/config.php", 0666);
		$permsdir = decoct(fileperms("config/config.php"));
		$perms = substr($permsdir, -3);
		if ($perms != "666") {
			$title = _FILE." config/config.php "._SERRORPERM." CHMOD - 666";
			head();
			foot();
			exit;
		}
	}
	if (file_exists("config/config_global.php")) {
		chmod("config/config_global.php", 0666);
		$permsdir = decoct(fileperms("config/config_global.php"));
		$perms = substr($permsdir, -3);
		if ($perms != "666") {
			$title = _FILE." config/config_global.php "._SERRORPERM." CHMOD - 666";
			head();
			foot();
			exit;
		}
	}
	include("config/config.php");
	$xdbhost = ($confdb['host']) ? $confdb['host'] : "localhost";
	$xdbuname = ($confdb['uname']) ? $confdb['uname'] : "";
	$xdbpass = ($confdb['pass']) ? "" : gen_pass("10");
	$xdbname = ($confdb['name']) ? $confdb['name'] : "";
	$ftype = (PHP_VERSION < "5.5.0") ? array("mysqli", "mysql", "sqlite", "postgres", "mssql", "oracle", "msaccess", "mssql-odbc") : array("mysqli", "sqlite", "postgres", "mssql", "oracle", "msaccess", "mssql-odbc");
	$xdbtype = "";
	foreach ($ftype as $val2) {
		if ($val2 != "") {
			$sel = ($val2 == $confdb['type']) ? " selected" : "";
			$xdbtype .= "<option value=\"".$val2."\"".$sel.">".$val2."</option>";
		}
	}
		
	$ctype = ($confdb['code']) ? array("utf8", "cp1251", "latin1", $confdb['code']) : array("utf8", "cp1251", "latin1");
	$ctype = array_unique($ctype);
	$xdbcode = "";
	foreach ($ctype as $val) {
		if ($val != "") {
			$sel = ($val == $confdb['code']) ? " selected" : "";
			$xdbcode .= "<option value=\"".$val."\"".$sel.">".$val."</option>";
		}
	}
	$xprefix = ($prefix) ? $prefix : gen_pass("10");
	$xadmin_file = ($admin_file) ? $admin_file : strtolower(gen_pass("10"));
	$info = sprintf(_CONF_5_INFO, $xadmin_file);
	head();
	echo "<form action=\"setup.php\" method=\"post\">"
	."<table class=\"sl_table\">"
	."<tr><td>"._SETUP_NEW.":</td><td><input type=\"radio\" name=\"setup\" value=\"new\" checked></td></tr>"
	."<tr><td>"._SUPDATE." 4.0 Pro > 4.1 Pro:</td><td><input type=\"radio\" name=\"setup\" value=\"update4_1\"></td></tr>"
	."<tr><td>"._SUPDATE." 4.1 Pro > 4.2 Pro:</td><td><input type=\"radio\" name=\"setup\" value=\"update4_2\"></td></tr>"
	."<tr><td>"._SUPDATE." 4.2 Pro > 4.3 Pro:</td><td><input type=\"radio\" name=\"setup\" value=\"update4_3\"></td></tr>"
	."<tr><td>"._SUPDATE." 4.3 Pro > 5.0 Pro:</td><td><input type=\"radio\" name=\"setup\" value=\"update5_0\"></td></tr>"
	."<tr><td>"._SUPDATE." 5.0 Pro > 5.1 Pro:</td><td><input type=\"radio\" name=\"setup\" value=\"update5_1\"></td></tr>"
	."<tr><td>"._SUPDATE." 5.3 Pro > 6.1 Pro:</td><td><input type=\"radio\" name=\"setup\" value=\"update6_0\"></td></tr>"
	."<tr><td>"._SUPDATE." 6.1 Pro > 6.2 Pro:</td><td><input type=\"radio\" name=\"setup\" value=\"update6_2\"></td></tr>"
	."<tr><td colspan=\"2\"><hr></td></tr>"
	."<tr><td>"._CONF_1.":</td><td><input type=\"text\" name=\"xdbhost\" value=\"".$xdbhost."\" class=\"sl_cinput\" placeholder=\""._CONF_1."\" required></td></tr>"
	."<tr><td>"._CONF_2.":</td><td><input type=\"text\" name=\"xdbuname\" value=\"".$xdbuname."\" class=\"sl_cinput\" placeholder=\""._CONF_2."\" required></td></tr>"
	."<tr><td>"._CONF_3.":</td><td><input type=\"text\" name=\"xdbpass\" value=\"".$xdbpass."\" class=\"sl_cinput\" placeholder=\""._CONF_3."\"></td></tr>"
	."<tr><td>"._CONF_4.":</td><td><input type=\"text\" name=\"xdbname\" value=\"".$xdbname."\" class=\"sl_cinput\" placeholder=\""._CONF_4."\" required></td></tr>"
	."<tr><td colspan=\"2\"><hr></td></tr>"
	."<tr><td>"._CONF_5.":<div class=\"sl_small\">"._CDEFAULT."</div></td><td><select name=\"xdbtype\" class=\"sl_cinput\">".$xdbtype."</select></td></tr>"
	."<tr><td>"._CONF_6.":<div class=\"sl_small\">"._CDEFAULT."</div></td><td><select name=\"xdbcode\" class=\"sl_cinput\">".$xdbcode."</select></td></tr>"
	."<tr><td>"._CONF_7.":</td><td><input type=\"text\" name=\"xprefix\" value=\"".$xprefix."\" class=\"sl_cinput\" placeholder=\""._CONF_7."\" required></td></tr>"
	."<tr><td>"._CONF_8.":<div class=\"sl_small\">".$info."</div></td><td><input type=\"text\" name=\"xadmin_file\" value=\"".$xadmin_file."\" class=\"sl_cinput\" placeholder=\""._CONF_8."\" required></td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\">"._GOBACK." <input type=\"hidden\" name=\"op\" value=\"save\"><input type=\"submit\" value=\""._NEXT_SE."\" class=\"sl_but_blue\"></td></tr>"
	."</table></form>";
	foot();
}

function save() {
	global $title, $clang, $conf, $host;
	$setup = (isset($_POST['setup'])) ? $_POST['setup'] : "";
	$xdbhost = (isset($_POST['xdbhost'])) ? $_POST['xdbhost'] : "";
	$xdbuname = (isset($_POST['xdbuname'])) ? $_POST['xdbuname'] : "";
	$xdbpass = (isset($_POST['xdbpass'])) ? $_POST['xdbpass'] : "";
	$xdbname = (isset($_POST['xdbname'])) ? $_POST['xdbname'] : "";
	$xdbtype = (isset($_POST['xdbtype'])) ? $_POST['xdbtype'] : "mysqli";
	$xdbcode = (isset($_POST['xdbcode'])) ? $_POST['xdbcode'] : "utf8";
	$xprefix = (isset($_POST['xprefix'])) ? $_POST['xprefix'] : "slaed";
	$xadmin_file = (isset($_POST['xadmin_file'])) ? $_POST['xadmin_file'] : "admin";

	include("config/config.php");
	$temp_file = (isset($admin_file)) ? $admin_file : "admin";
	rename($temp_file.".php", $xadmin_file.".php");
	$xadmin_file = (file_exists($xadmin_file.".php")) ? $xadmin_file : $temp_file;
	
	$content = "<?php\n"
	."if (!defined(\"FUNC_FILE\")) die(\"Illegal File Access\");\n"
	."\n"
	."\$confdb = array();\n"
	."\$confdb['host'] = \"".$xdbhost."\";\n"
	."\$confdb['uname'] = \"".$xdbuname."\";\n"
	."\$confdb['pass'] = \"".$xdbpass."\";\n"
	."\$confdb['name'] = \"".$xdbname."\";\n"
	."\$confdb['type'] = \"".$xdbtype."\";\n"
	."\$confdb['code'] = \"".$xdbcode."\";\n"
	."\n"
	."\$prefix = \"".$xprefix."\";\n"
	."\$admin_file = \"".$xadmin_file."\";\n"
	."\n"
	."?>";
	$fp = fopen("config/config.php", "wb");
	fwrite($fp, $content);
	fclose($fp);
	
	include("config/config.php");
	include("function/db.php");
	$bodytext = "";
	if ($setup == "new") {
		$title = _SAVE_NEW;
		$filename = file_get_contents("setup/sql/table.sql");
		$stringdump = explode(";", $filename);
		for ($i = 0; $i < count($stringdump); $i++) {
			$string = str_replace("{pref}", $xprefix, $stringdump[$i]);
			$id = $db->sql_query($string);
			if (preg_match("/CREATE|ALTER|DELETE|DROP|RENAME|UPDATE/i", $string)) {
				$table = explode("`", $string);
				$bodytext .= text_info($table[1], $id);
			}
		}
		$filename = file_get_contents("setup/sql/insert.sql");
		$stringdump = explode(";", $filename);
		for ($i = 0; $i < count($stringdump); $i++) {
			$string = str_replace("{pref}", $xprefix, $stringdump[$i]);
			$id = $db->sql_query($string);
			if (preg_match("/CREATE|ALTER|DELETE|DROP|RENAME|UPDATE/i", $string)) {
				$table = explode("`", $string);
				$bodytext .= text_info($table[1], $id);
			}
		}
	} elseif ($setup == "update4_1") {
		$title = _SAVE_UPDATE;
		$filename = file_get_contents("setup/sql/table_update4_1.sql");
		$stringdump = explode(";", $filename);
		for ($i = 0; $i < count($stringdump); $i++) {
			$string = str_replace("{pref}", $xprefix, $stringdump[$i]);
			$id = $db->sql_query($string);
			if (preg_match("/CREATE|ALTER|DELETE|DROP|RENAME|UPDATE/i", $string)) {
				$table = explode("`", $string);
				$bodytext .= text_info($table[1], $id);
			}
		}
	} elseif ($setup == "update4_2") {
		$title = _SAVE_UPDATE;
		$filename = file_get_contents("setup/sql/table_update4_2.sql");
		$stringdump = explode(";", $filename);
		for ($i = 0; $i < count($stringdump); $i++) {
			$string = str_replace("{pref}", $xprefix, $stringdump[$i]);
			$id = $db->sql_query($string);
			if (preg_match("/CREATE|ALTER|DELETE|DROP|RENAME|UPDATE/i", $string)) {
				$table = explode("`", $string);
				$bodytext .= text_info($table[1], $id);
			}
		}
	} elseif ($setup == "update4_3") {
		$title = _SAVE_UPDATE;
		$filename = file_get_contents("setup/sql/table_update4_3.sql");
		$stringdump = explode(";", $filename);
		for ($i = 0; $i < count($stringdump); $i++) {
			$string = str_replace("{pref}", $xprefix, $stringdump[$i]);
			$id = $db->sql_query($string);
			if (preg_match("/CREATE|ALTER|DELETE|DROP|RENAME|UPDATE/i", $string)) {
				$table = explode("`", $string);
				$bodytext .= text_info($table[1], $id);
			}
		}
	} elseif ($setup == "update5_0") {
		$title = _SAVE_UPDATE;
		$result = $db->sql_query("SELECT id, pwd FROM ".$xprefix."_admins");
		while (list($a_id, $a_pwd) = $db->sql_fetchrow($result)) {
			$db->sql_query("UPDATE ".$xprefix."_admins SET pwd = '".md5_salt($a_pwd)."' WHERE id = '".$a_id."'");
		}
		$bodytext .= text_info($xprefix."_admins", $result);
		$result = $db->sql_query("SELECT user_id, user_password FROM ".$xprefix."_users");
		while (list($user_id, $user_password) = $db->sql_fetchrow($result)) {
			$db->sql_query("UPDATE ".$xprefix."_users SET user_password = '".md5_salt($user_password)."' WHERE user_id = '".$user_id."'");
		}
		$bodytext .= text_info($xprefix."_users", $result);
		$filename = file_get_contents("setup/sql/table_update5_0.sql");
		$stringdump = explode(";", $filename);
		for ($i = 0; $i < count($stringdump); $i++) {
			$string = str_replace("{pref}", $xprefix, $stringdump[$i]);
			$id = $db->sql_query($string);
			if (preg_match("/CREATE|ALTER|DELETE|DROP|RENAME|UPDATE/i", $string)) {
				$table = explode("`", $string);
				$bodytext .= text_info($table[1], $id);
			}
		}
	} elseif ($setup == "update5_1") {
		$title = _SAVE_UPDATE;
		$filename = file_get_contents("setup/sql/table_update5_1.sql");
		$stringdump = explode(";", $filename);
		for ($i = 0; $i < count($stringdump); $i++) {
			$string = str_replace("{pref}", $xprefix, $stringdump[$i]);
			$id = $db->sql_query($string);
			if (preg_match("/CREATE|ALTER|DELETE|DROP|RENAME|UPDATE/i", $string)) {
				$table = explode("`", $string);
				$bodytext .= text_info($table[1], $id);
			}
		}
		
		$result = $db->sql_query("SELECT poll_id, poll_date, poll_title, poll_questions, poll_answer_1, poll_answer_2, poll_answer_3, poll_answer_4, poll_answer_5, poll_answer_6, poll_answer_7, poll_answer_8, poll_answer_9, poll_answer_10, poll_answer_11, poll_answer_12, pool_comments, planguage, acomm FROM ".$xprefix."_voting_temp");
		while (list($poll_id, $poll_date, $poll_title, $poll_questions, $poll_answer_1, $poll_answer_2, $poll_answer_3, $poll_answer_4, $poll_answer_5, $poll_answer_6, $poll_answer_7, $poll_answer_8, $poll_answer_9, $poll_answer_10, $poll_answer_11, $poll_answer_12, $pool_comments, $planguage, $acomm) = $db->sql_fetchrow($result)) {
			$questions = substr($poll_questions, 0, -1);
			$array_answ = array($poll_answer_1, $poll_answer_2, $poll_answer_3, $poll_answer_4, $poll_answer_5, $poll_answer_6, $poll_answer_7, $poll_answer_8, $poll_answer_9, $poll_answer_10, $poll_answer_11, $poll_answer_12);
			$answ = array();
			foreach ($array_answ as $val) if (!empty($val)) $answ[] = trim($val);
			$answ = implode("|", $answ);
			$ip = getip();
			$db->sql_query("INSERT INTO ".$xprefix."_voting (id, modul, title, questions, answer, date, enddate, multi, comments, language, acomm, ip, typ, status) VALUES ('".$poll_id."', '', '".$poll_title."', '".$questions."', '".$answ."', '".$poll_date."', '2020-05-23 20:58:00', '0', '".$pool_comments."', '".$planguage."', '".$acomm."', '".$ip."', '1', '1')");
			$db->sql_query("DROP TABLE ".$xprefix."_voting_temp");
		}
		$bodytext .= text_info($xprefix."_voting", $result);
		
		$result = $db->sql_query("SELECT sid, associated FROM ".$xprefix."_news");
		while (list($id, $associated) = $db->sql_fetchrow($result)) {
			$associated = explode("-", $associated);
			if (is_array($associated)) {
				$assoc = array();
				foreach ($associated as $val) {
					if (!empty($val)) $assoc[] = trim($val);
				}
				$assoc = implode(",", $assoc);
			} else {
				$assoc = "";
			}
			$db->sql_query("UPDATE ".$xprefix."_news SET associated = '".$assoc."' WHERE sid = '".$id."'");
		}
		$bodytext .= text_info($xprefix."_news", $result);
		
		$result = $db->sql_query("SELECT id, assoc FROM ".$xprefix."_products");
		while (list($id, $associated) = $db->sql_fetchrow($result)) {
			$associated = explode("-", $associated);
			if (is_array($associated)) {
				$assoc = array();
				foreach ($associated as $val) {
					if (!empty($val)) $assoc[] = trim($val);
				}
				$assoc = implode(",", $assoc);
			} else {
				$assoc = "";
			}
			$db->sql_query("UPDATE ".$xprefix."_products SET assoc = '".$assoc."' WHERE id = '".$id."'");
		}
		$bodytext .= text_info($xprefix."_products", $result);
	} elseif ($setup == "update6_0") {
		$title = _SAVE_UPDATE;
		$filename = file_get_contents("setup/sql/table_update6_0.sql");
		$stringdump = explode(";", $filename);
		for ($i = 0; $i < count($stringdump); $i++) {
			$string = str_replace("{pref}", $xprefix, $stringdump[$i]);
			$id = $db->sql_query($string);
			if (preg_match("/CREATE|ALTER|DELETE|DROP|RENAME|UPDATE/i", $string)) {
				$table = explode("`", $string);
				$bodytext .= text_info($table[1], $id);
			}
		}
	} elseif ($setup == "update6_2") {
		$title = _SAVE_UPDATE;
		$filename = file_get_contents("setup/sql/table_update6_2.sql");
		$stringdump = explode(";", $filename);
		for ($i = 0; $i < count($stringdump); $i++) {
			$string = str_replace("{pref}", $xprefix, $stringdump[$i]);
			$id = $db->sql_query($string);
			if (preg_match("/CREATE|ALTER|DELETE|DROP|RENAME|UPDATE/i", $string)) {
				$table = explode("`", $string);
				$bodytext .= text_info($table[1], $id);
			}
		}
	}
	
	include("config/config_global.php");
	$content = file_get_contents("config/config_global.php");
	if (isset($clang)) $content = str_replace("\$conf['language'] = \"".$conf['language']."\";", "\$conf['language'] = \"".$clang."\";", $content);
	$content = str_replace("\$conf['homeurl'] = \"".$conf['homeurl']."\";", "\$conf['homeurl'] = \"http://".$host."\";", $content);
	$fp = fopen("config/config_global.php", "wb");
	fwrite($fp, $content);
	fclose($fp);
	
	$bodytext .= "<tr><td colspan=\"2\">"._CONF_FILE.":</td><td><span class=\"sl_green\">"._OK."</span></td></tr>";
	
	head();
	echo "<table class=\"sl_table\">".$bodytext."</table>"
	."<div class=\"sl_center\"><form action=\"".$admin_file.".php\" method=\"post\">"._GOBACK." <input type=\"submit\" value=\""._ADMIN_SE."\" class=\"sl_but_blue\"></form></div>";
	foot();
}

switch($op) {
	default:
	language();
	break;
	
	case "lang":
	lang();
	break;
	
	case "config":
	config();
	break;
	
	case "save":
	save();
	break;
}
?>