<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("ADMIN_FILE")) die("Illegal file access");

global $path;

include($path."config/config_global.php");
include($path."config/config_secure.php");
include($path."language/lang-".$conf['language'].".php");

# HTTP Authentication
function authenticate() {
	global $confsp;
	header("WWW-Authenticate: Basic realm=\"SLAED\"");
	header("HTTP/1.0 401 Unauthorized");
	get_exit(_LOGININCOR, 0);
}

# Crypted md5 and salt
function md5_salt($pass) {
	global $conf;
	$crypt = md5(md5($conf['lic_f']).md5($pass));
	return $crypt;
}

# Get IP
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

# Format exit info
function get_exit($msg, $typ) {
	global $conf, $path;
	$cont = "<!DOCTYPE html>\n"
	."<html>\n"
	."<head>\n"
	."<meta charset=\""._CHARSET."\">\n"
	."<title>".$conf['sitename']." ".urldecode($conf['defis'])." ".$conf['slogan']."</title>\n"
	."<meta name=\"author\" content=\"".$conf['sitename']."\">\n"
	."<meta name=\"generator\" content=\"SLAED CMS ".$conf['version']."\">\n";
	$cont .= ($typ) ? "<meta http-equiv=\"refresh\" content=\"5; url=".$conf['homeurl']."/index.php\">\n" : "";
	$cont .= "</head>\n"
	."<body>\n"
	."<div style=\"margin: 25%;\">\n"
	."<div style=\"text-align: center;\"><img src=\"".$path."templates/".$conf['theme']."/images/logos/".$conf['site_logo']."\" alt=\"".$conf['sitename']."\" title=\"".$conf['sitename']."\"></div>\n"
	."<div style=\"margin-top: 50px; font: 18px Arial, Tahoma, sans-serif, Verdana; color: #1a4674; font-weight: bold; text-align: center;\">".$msg."</div>\n"
	."</div>\n"
	."</body>\n"
	."</html>";
	die($cont);
}

if ($confsp['admin_ip'] != "") {
	$admin_ip = explode(",", $confsp['admin_ip']);
	foreach ($admin_ip as $val) {
		$temp_ip = getip();
		$admin_ip = $val;
		if ($confsp['admin_mask'] <= 3) {
			$temp_ip = substr($temp_ip, 0, strrpos($temp_ip, "."));
			$admin_ip = substr($admin_ip, 0, strrpos($admin_ip, "."));
		}
		if ($confsp['admin_mask'] <= 2) {
			$temp_ip = substr($temp_ip, 0, strrpos($temp_ip, "."));
			$admin_ip = substr($admin_ip, 0, strrpos($admin_ip, "."));
		}
		if ($confsp['admin_mask'] == 1) {
			$temp_ip = substr($temp_ip, 0, strrpos($temp_ip, "."));
			$admin_ip = substr($admin_ip, 0, strrpos($admin_ip, "."));
		}
		if ($admin_ip == $temp_ip) {
			$ip_check = true;
			break;
		} else {
			$ip_check = false;
		}
	}
	if (!$ip_check) get_exit(_AUTH_ERROR_IP, 0);
}

if ($confsp['login'] != "" && $confsp['password'] != "") {
	if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) authenticate();
	if (!((md5_salt($_SERVER['PHP_AUTH_USER']) == $confsp['login']) && (md5_salt($_SERVER['PHP_AUTH_PW']) == $confsp['password']))) authenticate();
} else {
	get_exit(_AUTH_ERROR, 0);
}

unset($conf);
unset($confsp);
unset($path);
?>