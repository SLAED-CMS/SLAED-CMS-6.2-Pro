<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined('FUNC_FILE')) die('Illegal file access');

# Global config file include
include('config/config_global.php');

# Users config file include
include('config/config_users.php');

# Murder variables
unset($name, $file, $admin, $user, $admintrue, $godtrue, $usertrue, $aid, $uname, $guest, $userinfo, $stop);

# Set the default timezone to use. Available since PHP 5.1
if (PHP_VERSION >= '5.1.0') date_default_timezone_set($conf['gtime']);

# Language on
get_lang();

# SQL class file include
include('function/db.php');

# Security config file include
include('config/config_security.php');

# Error all reporting
# error_reporting(E_ALL);

# Error reporting
if ($confs['error']) {
	error_reporting(E_ALL ^ E_NOTICE);
} else {
	error_reporting(0);
}

# GZip
if ($conf['gzip']) {
	if (strstr($_SERVER['HTTP_USER_AGENT'], 'compatible') || strstr($_SERVER['HTTP_USER_AGENT'], 'Gecko')) {
		if (extension_loaded('zlib')) ob_start('ob_gzhandler');
	} else {
		if (strstr($HTTP_SERVER_VARS['HTTP_ACCEPT_ENCODING'], 'gzip')) {
			if (extension_loaded('zlib')) {
				$do_gzip_compress = true;
				ob_start();
				ob_implicit_flush(0);
				header('Content-Encoding: gzip');
			}
		}
	}
}

# Security magic quotes gpc
if (!get_magic_quotes_gpc()) {
	function add_slashes($val) {
		if (is_array($val)) {
			$val = array_map('add_slashes', $val);
		} elseif (!empty($val) && is_string($val)) {
			$val = addslashes($val);
		}
		return $val;
	}
	$_GET = add_slashes($_GET);
	$_POST = add_slashes($_POST);
	$_COOKIE = add_slashes($_COOKIE);
	$_REQUEST = add_slashes($_REQUEST);
}

# Output buffering on
ob_start();

# Session start
session_start();

# Flood Protection
if (!defined('ADMIN_FILE') && $confs['flood']) {
	$ctime = time();
	$ftime = $ctime - intval($confs['flood_t']);
	$flood = (isset($_SESSION['flood']) && $_SESSION['flood'] > $ftime) ? 1 : 0;
	if ($confs['flood'] == 3 && $flood) warn_report('Flood attack');
	if ($confs['flood'] == 2 && isset($_GET) && $flood) warn_report('Flood in GET - '.print_r($_GET, true));
	if (isset($_POST) && $flood) warn_report('Flood in POST - '.print_r($_POST, true));
	unset($_SESSION['flood']);
	$_SESSION['flood'] = $ctime;
}

# Format admin variable
$admin = isset($_SESSION[$conf['admin_c']]) ? explode(':', addslashes(base64_decode($_SESSION[$conf['admin_c']]))) : false;

# Format user variable
$user = isset($_COOKIE[$conf['user_c']]) ? explode(':', addslashes(base64_decode($_COOKIE[$conf['user_c']]))) : false;

# Analyzer of variables
function variable() {
	$cont = '';
	if ($_POST) $cont .= 'POST: '.print_r($_POST, true);
	if ($_GET) $cont .= 'GET: '.print_r($_GET, true);
	if ($_COOKIE) $cont .= 'COOKIE: '.print_r($_COOKIE, true);
	if ($_FILES) $cont .= 'FILES: '.print_r($_FILES, true);
	if ($_SESSION) $cont .= 'SESSION: '.print_r($_SESSION, true);
	# if ($_SERVER) $cont .= 'SERVER: '.print_r($_SERVER, true);
	return $cont;
}

# Log report
function log_report() {
	global $user, $confu, $confs;
	$ip = getip();
	$agent = getagent();
	$url = text_filter(getenv('REQUEST_URI'));
	$refer = get_referer();
	$ref = ($refer) ? "\n"._REFERER.": ".$refer : "";
	$luser = ($user) ? substr($user[1], 0, 25) : substr($confu['anonym'], 0, 25);
	$path = 'config/logs/log.txt';
	if ($fhandle = @fopen($path, 'ab')) {
		if (filesize($path) > $confs['log_size']) {
			zip_compress($path, 'config/logs/log_'.date('Y-m-d_H-i').'.txt');
			@unlink($path);
		}
		fwrite($fhandle, variable()._IP.": ".$ip."\n"._USER.": ".$luser."\n"._URL.": ".$url.$ref."\n"._BROWSER.": ".$agent."\n"._DATE.": ".date(_TIMESTRING)."\n---\n");
		fclose($fhandle);
	}
}

if ($confs['log']) log_report();

# Security cookies blocker or ip blocker and member blocker
$bcookie = isset($_COOKIE[$confs['blocker_cookie']]) ? $_COOKIE[$confs['blocker_cookie']] : '';
if ($bcookie == 'block') {
	get_exit(_BANN_INFO, 0);
} else {
	$bip = explode('||', $confs['blocker_ip']);
	if ($bip) {
		foreach ($bip as $val) {
			if ($val != '') {
				$binfo = explode('|', $val);
				if (time() <= $binfo[3]) {
					$ipt = getip();
					$ipb = $binfo[0];
					$uagt = md5(getagent());
					if ($binfo[1] <= 3) {
						$ipt = substr($ipt, 0, strrpos($ipt, '.'));
						$ipb = substr($ipb, 0, strrpos($ipb, '.'));
					}
					if ($binfo[1] <= 2) {
						$ipt = substr($ipt, 0, strrpos($ipt, '.'));
						$ipb = substr($ipb, 0, strrpos($ipb, '.'));
					}
					if ($binfo[1] == 1) {
						$ipt = substr($ipt, 0, strrpos($ipt, '.'));
						$ipb = substr($ipb, 0, strrpos($ipb, '.'));
					}
					if ((!$binfo[2] && $ipt == $ipb) || ($binfo[2] && $ipt == $ipb && $uagt == $binfo[2])) {
						setcookie($confs['blocker_cookie'], 'block', $binfo[3]);
						$btext = _BANN_INFO.'<br>'._BANN_TERM.': '.rest_time($binfo[3]).'<br>'._BANN_REAS.': '.$binfo[4];
						get_exit($btext, 0);
					}
				}
			}
		}
	}
	$bus = explode('||', $confs['blocker_user']);
	if ($bus) {
		foreach ($bus as $val) {
			if ($val != '') {
				$tus = substr($user[1], 0, 25);
				$uinfo = explode('|', $val);
				if (time() <= $uinfo[1]) {
					if ($tus == $uinfo[0]) {
						setcookie($confs['blocker_cookie'], 'block', $uinfo[1]);
						$utext = _BANN_INFO.'<br>'._BANN_TERM.': '.rest_time($uinfo[1]).'<br>'._BANN_REAS.': '.$uinfo[2];
						get_exit($utext, 0);
					}
				}
			}
		}
	}
}

# Error reporting log
if ($confs['error_log']) {
	
	# HTTP error reporting log
	if (isset($_GET['error'])) {
		$error = intval($_GET['error']);
		unset($error_log, $http);
		static $http = array (
			100 => 'HTTP/1.1 100 Continue',
			101 => 'HTTP/1.1 101 Switching Protocols',
			200 => 'HTTP/1.1 200 OK',
			201 => 'HTTP/1.1 201 Created',
			202 => 'HTTP/1.1 202 Accepted',
			203 => 'HTTP/1.1 203 Non-Authoritative Information',
			204 => 'HTTP/1.1 204 No Content',
			205 => 'HTTP/1.1 205 Reset Content',
			206 => 'HTTP/1.1 206 Partial Content',
			300 => 'HTTP/1.1 300 Multiple Choices',
			301 => 'HTTP/1.1 301 Moved Permanently',
			302 => 'HTTP/1.1 302 Found',
			303 => 'HTTP/1.1 303 See Other',
			304 => 'HTTP/1.1 304 Not Modified',
			305 => 'HTTP/1.1 305 Use Proxy',
			307 => 'HTTP/1.1 307 Temporary Redirect',
			400 => 'HTTP/1.1 400 Bad Request',
			401 => 'HTTP/1.1 401 Unauthorized',
			402 => 'HTTP/1.1 402 Payment Required',
			403 => 'HTTP/1.1 403 Forbidden',
			404 => 'HTTP/1.1 404 Not Found',
			405 => 'HTTP/1.1 405 Method Not Allowed',
			406 => 'HTTP/1.1 406 Not Acceptable',
			407 => 'HTTP/1.1 407 Proxy Authentication Required',
			408 => 'HTTP/1.1 408 Request Time-out',
			409 => 'HTTP/1.1 409 Conflict',
			410 => 'HTTP/1.1 410 Gone',
			411 => 'HTTP/1.1 411 Length Required',
			412 => 'HTTP/1.1 412 Precondition Failed',
			413 => 'HTTP/1.1 413 Request Entity Too Large',
			414 => 'HTTP/1.1 414 Request-URI Too Large',
			415 => 'HTTP/1.1 415 Unsupported Media Type',
			416 => 'HTTP/1.1 416 Requested range not satisfiable',
			417 => 'HTTP/1.1 417 Expectation Failed',
			500 => 'HTTP/1.1 500 Internal Server Error',
			501 => 'HTTP/1.1 501 Not Implemented',
			502 => 'HTTP/1.1 502 Bad Gateway',
			503 => 'HTTP/1.1 503 Service Unavailable',
			504 => 'HTTP/1.1 504 Gateway Time-out'
		);
		$error_log = $http[$error];
		if ($error_log) {
			$ip = getip();
			$agent = getagent();
			$url = text_filter(getenv('REQUEST_URI'));
			$refer = get_referer();
			$ref = ($refer) ? "\n"._REFERER.": ".$refer : "";
			$path = 'config/logs/error_site.txt';
			if ($fhandle = @fopen($path, 'ab')) {
				if (filesize($path) > $confs['log_size']) {
					zip_compress($path, 'config/logs/error_site_'.date('Y-m-d_H-i').'.txt');
					@unlink($path);
				}
				fwrite($fhandle, variable()._ERROR.": ".$error_log."\n"._IP.": ".$ip."\n"._URL.": ".$url.$ref."\n"._BROWSER.": ".$agent."\n"._DATE.": ".date(_TIMESTRING)."\n---\n");
				fclose($fhandle);
			}
		}
		unset($error_log, $http);
	}
	
	# PHP error reporting log
	function error_reporting_log($error_num, $error_var, $error_file, $error_line) {
		global $confs;
		$error_write = false;
		switch ($error_num) {
			case 1:
			$error_desc = 'ERROR';
			$error_write = true;
			break;
			case 2:
			$error_desc = 'WARNING';
			$error_write = true;
			break;
			case 4:
			$error_desc = 'PARSE';
			$error_write = true;
			break;
			case 8:
			$error_desc = 'NOTICE';
			$error_write = false;
			break;
			case 2048:
			$error_desc = 'STRICT';
			$error_write = true;
			break;
			case 8192:
			$error_desc = 'DEPRECATED';
			$error_write = true;
			break;
		}
		if ($error_write) {
			$ip = getip();
			$agent = getagent();
			$url = text_filter(getenv('REQUEST_URI'));
			$refer = get_referer();
			$ref = ($refer) ? "\n"._REFERER.": ".$refer : "";
			$path = 'config/logs/error.txt';
			if ($fhandle = @fopen($path, 'ab')) {
				if (filesize($path) > $confs['log_size']) {
					zip_compress($path, 'config/logs/error_'.date('Y-m-d_H-i').'.txt');
					@unlink($path);
				}
				fwrite($fhandle, variable()._ERROR.": ".$error_desc.": ".$error_var." Line: ".$error_line." in file ".$error_file."\n"._IP.": ".$ip."\n"._URL.": ".$url.$ref."\n"._BROWSER.": ".$agent."\n"._DATE.": ".date(_TIMESTRING)."\n---\n");
				fclose($fhandle);
			}
		}
	}
	set_error_handler('error_reporting_log');
	
	# SQL error reporting log
	function error_sql_log($errno, $error, $log) {
		global $confs;
		$ip = getip();
		$agent = getagent();
		$url = text_filter(getenv('REQUEST_URI'));
		$refer = get_referer();
		$ref = ($refer) ? "\n"._REFERER.": ".$refer : "";
		$log = text_filter(trim($log));
		$path = 'config/logs/error_sql.txt';
		if ($fhandle = @fopen($path, 'ab')) {
			if (filesize($path) > $confs['log_size']) {
				zip_compress($path, 'config/logs/error_sql_'.date('Y-m-d_H-i').'.txt');
				@unlink($path);
			}
			fwrite($fhandle, variable()._ERROR.": ".$errno." - ".$error."\nSQL: ".$log."\n"._IP.": ".$ip."\n"._URL.": ".$url.$ref."\n"._BROWSER.": ".$agent."\n"._DATE.": ".date(_TIMESTRING)."\n---\n");
			fclose($fhandle);
		}
	}
}

# Security GET, POST, COOKIE, FILES
if (!is_admin_god()) {
	
	# Security GET
	function check_get($name, $val) {
		global $prefix, $confs;
		$links = '#^(http\:\/\/|https\:\/\/|ftp\:\/\/|php\:\/\/|\/\/)#i';
		$script = '#<.*?(script|body|object|iframe|applet|meta|form|style|img).*?>#i';
		$char = '#\([^>]*\"?[^)]*\)#';
		$quote = '#\"|\'|\.\.\/|\*#';
		$string = '#ALTER|DROP|INSERT|OUTFILE|SELECT|TRUNCATE|UNION|'.$prefix.'_admins|'.$prefix.'_users|admins_show|admins_add|admins_save|admins_del#i';
		$decode = base64_decode($val);
		$slash = preg_replace('#\/\*.*?\*\/#', '', $val);
		if ($confs['url_get']) if (preg_match($links, $val)) warn_report('URL in GET - '.$name.' = '. $val);
		if (preg_match($script, urldecode($val)) || preg_match($char, $val)) warn_report('HTML in GET - '.$name.' = '. $val);
		if (preg_match($quote, $val)) hack_report('Hack in GET - '.$name.' = '. $val);
		if (preg_match($string, $val)) hack_report('XSS in GET - '.$name.' = '. $val);
		if (preg_match($string, $decode)) hack_report('XSS base64 in GET - '.$name.' = '. $val);
		if (preg_match($string, $slash)) hack_report('XSS slash in GET - '.$name.' = '. $val);
	}
	function parse_get($val) {
		if (is_array($val)) {
			$val = array_map('parse_get', $val);
		} elseif (!empty($val) && is_string($val)) {
			$in = array('\"', '\'', '\\');
			$out = array('', '', '');
			$val = str_replace($in, $out, $val);
		}
		return $val;
	}
	$_GET = parse_get($_GET);
	foreach ($_GET as $var => $val) {
		if (is_array($val)) {
			foreach ($val as $var_a => $val_a) check_get($var_a, $val_a);
		} else {
			check_get($var, $val);
		}
	}
	
	# Security POST
	function check_post($name, $val) {
		global $prefix, $confs, $conf, $admin;
		#$val = is_array($val) ? fields_save($val) : $val;
		$editor = intval(substr($admin[3], 0, 1));
		$links = '#^(http\:\/\/|https\:\/\/|ftp\:\/\/|php\:\/\/|\/\/)#i';
		$script = '#<.*?(script|body|object|iframe|applet|meta|form).*?>#i';
		$string = '#'.$prefix.'_admins|'.$prefix.'_users#i';
		$decode = base64_decode($val);
		$slash = preg_replace('#\/\*.*?\*\/#', '', $val);
		if ($confs['ref_post'] && isset($_FILES['Filedata']['size'])) if (!intval($_FILES['Filedata']['size']) && !stristr(getenv('HTTP_REFERER'), get_host())) warn_report('POST from referer - '.$name.' = '. $val);
		if ($confs['url_post']) if (preg_match($links, $val)) warn_report('URL in POST - '.$name.' = '. $val);
		if (((defined('ADMIN_FILE') && $editor != 1) || (!defined('ADMIN_FILE') && $conf['redaktor'] != 1)) && preg_match($script, urldecode($val))) warn_report('HTML in POST - '.$name.' = '. $val);
		if (preg_match($string, $val)) hack_report('XSS in POST - '.$name.' = '. $val);
		if (preg_match($string, $decode)) hack_report('XSS base64 in POST - '.$name.' = '. $val);
		if (preg_match($string, $slash)) hack_report('XSS slash in POST - '.$name.' = '. $val);
	}
	function parse_post($val) {
		if (is_array($val)) {
			$val = array_map('parse_post', $val);
		} elseif (!empty($val) && is_string($val)) {
			$in = array('#javascript:#si', '#vbscript:#si', '#script:#si', '#about:#si', '#applet:#si', '#activex:#si', '#chrome:#si');
			$out = array('Java Script', 'VB Script', 'Script', 'About', 'Applet', 'ActiveX', 'Chrome');
			$val = preg_replace($in, $out, $val);
		}
		return $val;
	}
	$_POST = parse_post($_POST);
	foreach ($_POST as $var => $val) {
		if (is_array($val)) {
			foreach ($val as $var_a => $val_a) check_post($var_a, $val_a);
		} else {
			check_post($var, $val);
		}
	}
}

# Security COOKIE
function check_cookie($name, $val) {
	global $prefix;
	$links = '#^(http\:\/\/|https\:\/\/|ftp\:\/\/|php\:\/\/|\/\/)#i';
	$script = '#<.*?(script|body|object|iframe|applet|meta|form|style|img).*?>#i';
	$string = '#ALTER|DROP|INSERT|OUTFILE|SELECT|TRUNCATE|UNION|'.$prefix.'_admins|'.$prefix.'_users|admins_show|admins_add|admins_save|admins_del#i';
	$decode = base64_decode($val);
	$slash = preg_replace('#\/\*.*?\*\/#', '', $val);
	if (preg_match($links, $val)) hack_report('URL in COOKIE - '.$name.' = '. $val);
	if (preg_match($script, $val)) hack_report('HTML in COOKIE - '.$name.' = '. $val);
	if (preg_match($string, $val)) hack_report('XSS in COOKIE - '.$name.' = '. $val);
	if (preg_match($string, $decode)) hack_report('XSS base64 in COOKIE - '.$name.' = '. $val);
	if (preg_match($string, $slash)) hack_report('XSS slash in COOKIE - '.$name.' = '. $val);
}
foreach ($_COOKIE as $var => $val) {
	if (is_array($val)) {
		foreach ($val as $var_a => $val_a) check_cookie($var_a, $val_a);
	} else {
		check_cookie($var, $val);
	}
}

# Security FILES
function check_files($name, $val) {
	$type = '#php.*|js|htm|html|phtml|cgi|pl|perl|asp#i';
	if (isset($_FILES['userfile'])) {
		$val = strtolower(substr(strrchr($_FILES['userfile']['name'], '.'), 1));
	} elseif (isset($_FILES['Filedata'])) {
		$val = strtolower(substr(strrchr($_FILES['Filedata']['name'], '.'), 1));
	} else {
		$val = strtolower(substr(strrchr($_FILES[$name]['name'], '.'), 1));
	}
	if (preg_match($type, $val)) hack_report('Hack in FILES - '.$name.' = '. $val);
}
foreach ($_FILES as $var => $val) {
	if (is_array($val)) {
		foreach ($val as $var_a => $val_a) check_files($var_a, $val_a);
	} else {
		check_files($var, $val);
	}
}

# Reset all variables
reset($_GET);
reset($_POST);
reset($_COOKIE);
reset($_FILES);

# Check super admin
function is_admin_god() {
	global $prefix, $db, $admin;
	static $godtrue;
	if (!empty($admin)) {
		if (!isset($godtrue)) {
			$id = intval(substr($admin[0], 0, 11));
			$name = htmlspecialchars(substr($admin[1], 0, 25));
			$pwd = htmlspecialchars(substr($admin[2], 0, 40));
			$ip = getip();
			if ($id && $name && $pwd && $ip) {
				list($aname, $apwd, $aip) = $db->sql_fetchrow($db->sql_query("SELECT name, pwd, ip FROM ".$prefix."_admins WHERE id = '".$id."' AND super = '1'"));
				if ($aname == $name && $aname != '' && $apwd == $pwd && $apwd != '' && $aip == $ip && $aip != '') {
					$godtrue = 1;
					return $godtrue;
				}
			}
			$godtrue = 0;
			return $godtrue;
		} else {
			return $godtrue;
		}
	} else {
		return 0;
	}
}

# Get IP
function getip() {
	if (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
		$ip = getenv('REMOTE_ADDR');
	} elseif (!empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
		$ip = $_SERVER['REMOTE_ADDR'];
	} else {
		$ip = '0.0.0.0';
	}
	return $ip;
}

# Get user agent
function getagent() {
	if (getenv('HTTP_USER_AGENT') && strcasecmp(getenv('HTTP_USER_AGENT'), 'unknown')) {
		$agent = text_filter(getenv('HTTP_USER_AGENT'));
	} elseif (!empty($_SERVER['HTTP_USER_AGENT']) && strcasecmp($_SERVER['HTTP_USER_AGENT'], 'unknown')) {
		$agent = text_filter($_SERVER['HTTP_USER_AGENT']);
	} else {
		$agent = 'unknown';
	}
	return $agent;
}

# Get host
function get_host() {
	$host = (getenv('HTTP_HOST')) ? getenv('HTTP_HOST') : getenv('SERVER_NAME');
	return $host;
}

# Get referer
function get_referer() {
	$referer = text_filter(getenv('HTTP_REFERER'));
	if (!empty($referer) && $referer != '' && !preg_match('#^unknown#i', $referer) && !preg_match('#^bookmark#i', $referer) && !stristr($referer, get_host())) {
		$refer = $referer;
	} else {
		$refer = '';
	}
	return $refer;
}

# Format language
function get_lang($module='') {
	global $currentlang, $conf;
	$rlang = analyze(getVar('req', 'newlang'));
	$clang = isset($_COOKIE['sl_lang']) ? analyze($_COOKIE['sl_lang']) : '';
	if ($rlang && $conf['multilingual'] == '1') {
		if (file_exists('language/lang-'.$rlang.'.php')) {
			setcookie('sl_lang', $rlang, time() + intval($conf['user_c_t']));
			include_once('language/lang-'.$rlang.'.php');
			$currentlang = $rlang;
		} else {
			setcookie('sl_lang', $conf['language'], time() + intval($conf['user_c_t']));
			include_once('language/lang-'.$conf['language'].'.php');
			$currentlang = $conf['language'];
		}
	} elseif ($clang && $conf['multilingual'] == '1') {
		if (file_exists('language/lang-'.$clang.'.php')) {
			include_once('language/lang-'.$clang.'.php');
			$currentlang = $clang;
		} else {
			include_once('language/lang-'.$conf['language'].'.php');
			$currentlang = $conf['language'];
		}
	} else {
		setcookie('sl_lang', $conf['language'], time() + intval($conf['user_c_t']));
		include_once('language/lang-'.$conf['language'].'.php');
		$currentlang = $conf['language'];
	}
	if ($module != '') {
		if (file_exists('modules/'.$module.'/language/lang-'.$currentlang.'.php')) {
			if ($module == 'admin') {
				include_once('admin/language/lang-'.$currentlang.'.php');
			} else {
				include_once('modules/'.$module.'/language/lang-'.$currentlang.'.php');
			}
		} else {
			if ($module == 'admin') {
				include_once('admin/language/lang-'.$currentlang.'.php');
			} else {
				include_once('modules/'.$module.'/language/lang-'.$conf['language'].'.php');
			}
		}
	}
}

# Zip check
function zip_check() {
	if (function_exists('gzopen')) {
		return 2;
	} elseif (function_exists('bzopen')) {
		return 1;
	} else {
		return 0;
	}
}

# Zip compress
function zip_compress($src, $dst) {
	$check = zip_check();
	if ($check) {
		$fp = @fopen($src, 'rb');
		$data = fread($fp, filesize($src));
		fclose($fp);
		if ($check == 2) {
			$zp = gzopen($dst.'.gz', 'wb5');
			gzwrite($zp, $data);
			gzclose($zp);
		} else {
			$zp = bzopen($dst.'.bz2', 'w');
			bzwrite($zp, $data);
			bzclose($zp);
		}
	}
}

# Format exit info
function get_exit($msg, $typ) {
	global $conf;
	$cont = "<!doctype html>\n"
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
	."<div style=\"text-align: center;\"><img src=\"".img_find("logos/".$conf['site_logo'])."\" alt=\"".$conf['sitename']."\" title=\"".$conf['sitename']."\"></div>\n"
	."<div style=\"margin-top: 50px; font: 18px Arial, Tahoma, sans-serif, Verdana; color: #1a4674; font-weight: bold; text-align: center;\">".$msg."</div>\n"
	."</div>\n"
	."</body>\n"
	."</html>";
	die($cont);
}

# Get variables
function getVar($var, $val, $typ='', $obj='') {
	if ($var == 'post') {
		if ($typ == 'num') {
			$out = isset($_POST[$val]) ? num_filter($_POST[$val]) : (empty($obj) ? false : num_filter($obj));
		} elseif ($typ == 'let') {
			$out = isset($_POST[$val]) ? mb_substr($_POST[$val], 0, 1, 'utf-8') : (empty($obj) ? false : mb_substr($obj, 0, 1, 'utf-8'));
		} elseif ($typ == 'word') {
			$out = isset($_POST[$val]) ? text_filter($_POST[$val]) : (empty($obj) ? false : text_filter($obj));
		} elseif ($typ == 'name') {
			$out = isset($_POST[$val]) ? text_filter(substr($_POST[$val], 0, 25)) : (empty($obj) ? false : text_filter(substr($obj, 0, 25)));
		} elseif ($typ == 'title') {
			$out = isset($_POST[$val]) ? save_text($_POST[$val], 1) : (empty($obj) ? false : save_text($obj, 1));
		} elseif ($typ == 'text') {
			$out = isset($_POST[$val]) ? save_text($_POST[$val]) : (empty($obj) ? false : save_text($obj));
		} elseif ($typ == 'field') {
			$out = isset($_POST[$val]) ? fields_save($_POST[$val]) : (empty($obj) ? false : fields_save($obj));
		} elseif ($typ == 'url') {
			$out = isset($_POST[$val]) ? url_filter($_POST[$val]) : (empty($obj) ? false : $obj);
		} else {
			$out = isset($_POST[$val]) ? $_POST[$val] : (empty($obj) ? false : $obj);
		}
	} elseif ($var == 'get') {
		if ($typ == 'num') {
			$out = isset($_GET[$val]) ? num_filter($_GET[$val]) : (empty($obj) ? false : num_filter($obj));
		} elseif ($typ == 'let') {
			$out = isset($_GET[$val]) ? mb_substr($_GET[$val], 0, 1, 'utf-8') : (empty($obj) ? false : mb_substr($obj, 0, 1, 'utf-8'));
		} elseif ($typ == 'word') {
			$out = isset($_GET[$val]) ? text_filter($_GET[$val]) : (empty($obj) ? false : text_filter($obj));
		} elseif ($typ == 'name') {
			$out = isset($_GET[$val]) ? text_filter(substr($_GET[$val], 0, 25)) : (empty($obj) ? false : text_filter(substr($obj, 0, 25)));
		} elseif ($typ == 'title') {
			$out = isset($_GET[$val]) ? save_text($_GET[$val], 1) : (empty($obj) ? false : save_text($obj, 1));
		} elseif ($typ == 'text') {
			$out = isset($_GET[$val]) ? save_text($_GET[$val]) : (empty($obj) ? false : save_text($obj));
		} elseif ($typ == 'field') {
			$out = isset($_GET[$val]) ? fields_save($_GET[$val]) : (empty($obj) ? false : fields_save($obj));
		} elseif ($typ == 'url') {
			$out = isset($_GET[$val]) ? url_filter($_GET[$val]) : (empty($obj) ? false : $obj);
		} else {
			$out = isset($_GET[$val]) ? $_GET[$val] : (empty($obj) ? false : $obj);
		}
	} elseif ($var == 'req') {
		if ($typ == 'num') {
			$out = isset($_POST[$val]) ? num_filter($_POST[$val]) : (isset($_GET[$val]) ? num_filter($_GET[$val]) : (empty($obj) ? false : num_filter($obj)));
		} elseif ($typ == 'let') {
			$out = isset($_POST[$val]) ? mb_substr($_POST[$val], 0, 1, 'utf-8') : (isset($_GET[$val]) ? mb_substr($_GET[$val], 0, 1, 'utf-8') : (empty($obj) ? false : mb_substr($obj, 0, 1, 'utf-8')));
		} elseif ($typ == 'word') {
			$out = isset($_POST[$val]) ? text_filter($_POST[$val]) : (isset($_GET[$val]) ? text_filter($_GET[$val]) : (empty($obj) ? false : text_filter($obj)));
		} elseif ($typ == 'name') {
			$out = isset($_POST[$val]) ? text_filter(substr($_POST[$val], 0, 25)) : (isset($_GET[$val]) ? text_filter(substr($_GET[$val], 0, 25)) : (empty($obj) ? false : text_filter(substr($obj, 0, 25))));
		} elseif ($typ == 'title') {
			$out = isset($_POST[$val]) ? save_text($_POST[$val], 1) : (isset($_GET[$val]) ? save_text($_GET[$val], 1) : (empty($obj) ? false : save_text($obj, 1)));
		} elseif ($typ == 'text') {
			$out = isset($_POST[$val]) ? save_text($_POST[$val]) : (isset($_GET[$val]) ? save_text($_GET[$val]) : (empty($obj) ? false : save_text($obj)));
		} elseif ($typ == 'field') {
			$out = isset($_POST[$val]) ? fields_save($_POST[$val]) : (isset($_GET[$val]) ? fields_save($_GET[$val]) : (empty($obj) ? false : fields_save($obj)));
		} elseif ($typ == 'url') {
			$out = isset($_POST[$val]) ? url_filter($_POST[$val]) : (isset($_GET[$val]) ? url_filter($_GET[$val]) : (empty($obj) ? false : $obj));
		} else {
			$out = isset($_POST[$val]) ? $_POST[$val] : (isset($_GET[$val]) ? $_GET[$val] : (empty($obj) ? false : $obj));
		}
	}
	return empty($out) ? false : $out;
}

# Strict variable analyzer
function analyze($var) {
	$var = (preg_match('#[^a-zA-Z0-9_\-]#', $var)) ? '' : $var;
	return $var;
}

# URL filter
function url_filter($url) {
	$url = strtolower($url);
	$url = (preg_match('#http\:\/\/|https\:\/\/#i', $url)) ? $url : 'http://'.$url;
	$url = ($url == 'http://') ? '' : text_filter($url);
	return $url;
}

# Number filter
function num_filter($var) {
	$con = preg_replace('#[^0-9]#', '', $var);
	return $con;
}

# Variables filter
function var_filter($var) {
	$con = preg_replace('#[^\pL0-9\s%&/|.:;&_+\-=]#siu', '', $var);
	return $con;
}

# HTML and word filter
function text_filter($message, $type='') {
	global $conf;
	if (!is_admin()) while (preg_match('#\[(usehtml|/usehtml)\]|\[(usephp|/usephp)\]#si', $message)) $message = preg_replace('#\[(usehtml|/usehtml)\]|\[(usephp|/usephp)\]#si', '', $message);
	$message = is_array($message) ? fields_save($message) : $message;
	if (intval($type) == 2) {
		$message = htmlspecialchars(trim($message), ENT_QUOTES);
	} else {
		$message = strip_tags(urldecode($message));
		$message = htmlspecialchars(trim($message), ENT_QUOTES);
	}
	if ($conf['censor'] && intval($type != 1)) {
		$censor_l = explode(',', $conf['censor_l']);
		foreach ($censor_l as $val) $message = preg_replace('#'.$val.'#i', $conf['censor_r'], $message);
	}
	return $message;
}

# Length center filter
function cutstrc($linkstrip, $strip) {
	if (strlen($linkstrip) > $strip) $linkstrip = substr($linkstrip, 0, $strip - 19).'…'.substr($linkstrip, -16);
	return $linkstrip;
}

# Format ed2k links
function ed2k_link($m) {
	$href = 'url='.$m[2];
	$fname = rawurldecode($m[3]);
	$fname = str_replace(array('&#038;', '&amp;'), '&', $fname);
	$size = files_size($m[4]);
	$cont = ' eMule/eDonkey: ['.$href.']'.cutstrc($fname, 50).'[/url] - '._SIZE.': '.$size;
	return $cont;
}

# Make clickable url
function url_clickable($text) {
	if (!preg_match("#\[php\](.*)\[/php\]|\[code\](.*)\[/code\]#si", $text)) {
		$ret = preg_replace_callback("#([\n ])(?<=[^\w\"'])(ed2k://\|file\|([^\\/\|:<>\*\?\"]+?)\|(\d+?)\|([a-f0-9]{32})\|(.*?)/?)(?![\"'])(?=([,\.]*?[\s<\[])|[,\.]*?$)#i", "ed2k_link", " ".$text);
		$ret = preg_replace("#([\n ])(?<=[^\w\"'])(ed2k://\|server\|([\d\.]+?)\|(\d+?)\|/?)#i", "ed2k Server: [url=\\2]\\3[/url] - Port: \\4", $ret);
		$ret = preg_replace("#([\n ])(?<=[^\w\"'])(ed2k://\|friend\|([^\\/\|:<>\*\?\"]+?)\|([\d\.]+?)\|(\d+?)\|/?)#i", "Friend: [url=\\2]\\3[/url]", $ret);
		$ret = preg_replace("#([\n ])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1[url=\\2]\\2[/url]", $ret);
		$ret = preg_replace("#([\n ])((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1[url=http://\\2]\\2[/url]", $ret);
		$ret = preg_replace("#([\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1[mail=\\2@\\3]\\2@\\3[/mail]", $ret);
		$ret = substr($ret, 1);
	} else {
		if (preg_match('#(.*)\[php\](.*)\[/php\](.*)#si', $text, $matches)) {
			$ret = url_clickable($matches[1]).'[php]'.$matches[2].'[/php]'.url_clickable($matches[3]);
		} elseif (preg_match('#(.*)\[code(.*)\](.*)\[/code\](.*)#si', $text, $matches)) {
			$ret = url_clickable($matches[1]).'[code'.$matches[2].']'.$matches[3].'[/code]'.url_clickable($matches[4]);
		}
	}
	return $ret;
}

# Save text
function save_text($text, $id='') {
	global $admin, $conf;
	if ($text) {
		$editor = intval(substr($admin[3], 0, 1));
		if ((defined('ADMIN_FILE') && $editor == 1) || (!defined('ADMIN_FILE') && $conf['redaktor'] == 1)) {
			$text = ($conf['clickable'] && $id != 1) ? url_clickable($text) : $text;
			$out = nl2br(str_replace(array('$', '\\'), array('&#036;', '&#092;'), stripslashes(text_filter($text, 2))), false);
		} else {
			$out = str_replace(array('"', '$', '\'', '\\'), array('&#034;', '&#036;', '&#039;', '&#092;'), stripslashes($text));
		}
		return $out;
	}
}

# Fields save
function fields_save($field) {
	if (isArray($field)) {
		$fields = stripslashes(text_filter(implode('|', $field), 2));
		return $fields;
	}
}

# Display Time filter
function display_time($sec) {
	$min = floor($sec / 60);
	$hours = floor($min / 60);
	$seconds = $sec % 60;
	$minutes = $min % 60;
	$cont = ($hours == 0) ? (($min == 0) ? $seconds.' '._SEC.'.' : $min.' '._MIN.'. '.$seconds.' '._SEC.'.') : $hours.' '._HOUR.'. '.$minutes.' '._MIN.'. '.$seconds.' '._SEC.'.';
	return $cont;
}

# Rest time
function rest_time($time) {
	$end = date(_DATESTRING, $time);
	$expire = $time - time();
	$days = round($expire / 86400, 3).' '._DAYS;
	$date = (time() < $time) ? '<span title="'.display_time($expire).'" class="sl_green sl_note">'.$days.' - '.$end.'</span>' : '<span class="sl_red">'.$end.' - '._END.'</span>';
	return $date;
}

# Mail send
function mail_send($email, $smail, $subject, $message, $id='', $pr='') {
	global $conf;
	$email = text_filter($email);
	$smail = text_filter($smail);
	$subject = text_filter($subject);
	$id = intval($id);
	$pr = (!$pr) ? '3' : intval($pr);
	$message = (!$id) ? $message : $message.'<br><br>'._IP.': '.getip().'<br>'._BROWSER.': '.getagent().'<br>'._HASH.': '.md5(getagent());
	$mheader = "MIME-Version: 1.0\n"
	."Content-Type: text/html; charset="._CHARSET."\n"
	."Content-Transfer-Encoding: 8bit\n"
	."Reply-To: \"".$smail."\" <".$smail.">\n"
	."From: \"".$smail."\" <".$smail.">\n"
	."Return-Path: <".$smail.">\n"
	."X-Priority: ".$pr."\n"
	."X-Mailer: SLAED CMS ".$conf['version']." Mailer\n";
	mail($email, $subject, $message, $mheader);
}

# Hack report
function hack_report($msg) {
	global $user, $conf, $confu, $confs;
	$msg = text_filter(substr($msg, 0, 500));
	$url = text_filter(getenv('REQUEST_URI'));
	$refer = get_referer();
	$ref = ($refer) ? "\n"._REFERER.": ".$refer : "";
	$ip = getip();
	$agent = getagent();
	$date_time = date(_TIMESTRING);
	$user = ($user) ? substr($user[1], 0, 25) : substr($confu['anonym'], 0, 25);
	if ($confs['block']) {
		$btime = time() + 86400;
		$cont = file_get_contents('config/config_security.php');
		$cont = str_replace("\$confs['blocker_ip'] = \"".$confs['blocker_ip']."\";", "\$confs['blocker_ip'] = \"".$confs['blocker_ip'].$ip."|4|".md5($agent)."|".$btime."|"._HACK."||\";", $cont);
		$fp = @fopen("config/config_security.php", "wb");
		fwrite($fp, $cont);
		fclose($fp);
		setcookie($confs['blocker_cookie'], 'block', $btime);
	}
	if ($confs['mail']) {
		$subject = $conf['sitename'].' - '._SECURITY;
		$mmsg = $conf['sitename'].' - '._SECURITY.'<br><br>'._HACK.': '.$msg.'<br>'._IP.': '.$ip.'<br>'._USER.': '.$user.'<br>'._URL.': '.$url.$ref.'<br>'._BROWSER.': '.$agent.'<br>'._DATE.': '.$date_time;
		mail_send($conf['adminmail'], $conf['adminmail'], $subject, $mmsg, 0, 1);
	}
	if ($confs['write_h']) {
		$path = 'config/logs/hack.txt';
		if ($fhandle = @fopen($path, 'ab')) {
			if (filesize($path) > $confs['log_size']) {
				zip_compress($path, 'config/logs/hack_'.date('Y-m-d_H-i').'.txt');
				@unlink($path);
			}
			fwrite($fhandle, _HACK.": ".$msg."\n"._IP.": ".$ip."\n"._USER.": ".$user."\n"._URL.": ".$url.$ref."\n"._BROWSER.": ".$agent."\n"._DATE.": ".$date_time."\n---\n");
			fclose($fhandle);
		}
	}
	setcookie($conf['user_c'], false);
	get_exit(_HACK.'!', 1);
}

# Warn report
function warn_report($msg) {
	global $user, $conf, $confu, $confs;
	$msg = text_filter(substr($msg, 0, 500));
	$url = text_filter(getenv('REQUEST_URI'));
	$refer = get_referer();
	$ref = ($refer) ? "\n"._REFERER.": ".$refer : "";
	$ip = getip();
	$agent = getagent();
	$date_time = date(_TIMESTRING);
	$user = ($user) ? substr($user[1], 0, 25) : substr($confu['anonym'], 0, 25);
	if ($confs['mail_w']) {
		$subject = $conf['sitename'].' - '._SECURITY;
		$mmsg = $conf['sitename'].' - '._SECURITY.'<br><br>'._WARN.': '.$msg.'<br>'._IP.': '.$ip.'<br>'._USER.': '.$user.'<br>'._URL.': '.$url.$ref.'<br>'._BROWSER.': '.$agent.'<br>'._DATE.': '.$date_time;
		mail_send($conf['adminmail'], $conf['adminmail'], $subject, $mmsg, 0, 1);
	}
	if ($confs['write_w']) {
		$path = 'config/logs/warn.txt';
		if ($fhandle = @fopen($path, 'ab')) {
			if (filesize($path) > $confs['log_size']) {
				zip_compress($path, 'config/logs/warn_'.date('Y-m-d_H-i').'.txt');
				@unlink($path);
			}
			fwrite($fhandle, _WARN.": ".$msg."\n"._IP.": ".$ip."\n"._USER.": ".$user."\n"._URL.": ".$url.$ref."\n"._BROWSER.": ".$agent."\n"._DATE.": ".$date_time."\n---\n");
			fclose($fhandle);
		}
	}
	get_exit(_WARN.'!', 1);
}
?>