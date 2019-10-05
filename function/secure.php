<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("ADMIN_FILE")) die("Illegal file access");

include("config/config_secure.php");

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
}

function authenticate() {
	global $confsp;
	header("WWW-Authenticate: Basic realm=\"SLAED\"");
	header("HTTP/1.0 401 Unauthorized");
	get_exit(_LOGININCOR, 0);
}

unset($confsp);
?>