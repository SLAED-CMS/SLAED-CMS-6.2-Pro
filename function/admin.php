<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined('ADMIN_FILE')) die('Illegal file access');

function admininfo() {
	global $prefix, $db, $admin, $admin_file, $conf, $confr, $panel;
	if (is_admin()) {
		$panel = isset($_GET['panel']) ? $_GET['panel'] : $panel;
		$ablocks = '';
		if ($panel) {
			$n_cont = "<table class=\"sl_tab_bl\">";
			if (is_active("account") && is_admin_modul("account")) {
				$num = $db->sql_numrows($db->sql_query("SELECT user_id FROM ".$prefix."_users_temp"));
				$num = (is_numeric($num)) ? (($num >= 1) ? "<span class=\"sl_red\">".$num."</span>" : "<span class=\"sl_green\">".$num."</span>") : "-";
				$n_cont .= "<tr><td><a href=\"".$admin_file.".php?op=users_new\" title=\""._NEW_USER."\">"._USERS."</a>:</td><td>".$num."</td></tr>";
			}
			if (is_active("album") && is_admin_modul("album")) {
				#$num = $db->sql_numrows($db->sql_query("SELECT pid FROM ".$prefix."_album_pictures_newpicture"));
				#$num = (is_numeric($num)) ? (($num >= 1) ? "<span class=\"sl_red\">".$num."</span>" : "<span class=\"sl_green\">".$num."</span>") : "-";
				#$n_cont .= "<tr><td><a href=\"".$admin_file.".php?op=album&amp;do=validnew&amp;type=checknew\" title=\""._ALBUM."\">"._ALBUM."</a>:</td><td>".$num."</td></tr>";
			}
			if (is_active("faq") && is_admin_modul("faq")) {
				$num = $db->sql_numrows($db->sql_query("SELECT fid FROM ".$prefix."_faq WHERE status = '0'"));
				$num = (is_numeric($num)) ? (($num >= 1) ? "<span class=\"sl_red\">".$num."</span>" : "<span class=\"sl_green\">".$num."</span>") : "-";
				$n_cont .= "<tr><td><a href=\"".$admin_file.".php?op=faq&amp;status=1\" title=\""._FAQ."\">"._FAQ."</a>:</td><td>".$num."</td></tr>";
			}
			if (is_active("files") && is_admin_modul("files")) {
				$num = $db->sql_numrows($db->sql_query("SELECT lid FROM ".$prefix."_files WHERE status = '0'"));
				$num = (is_numeric($num)) ? (($num >= 1) ? "<span class=\"sl_red\">".$num."</span>" : "<span class=\"sl_green\">".$num."</span>") : "-";
				$n_cont .= "<tr><td><a href=\"".$admin_file.".php?op=files&amp;status=1\" title=\""._FILES."\">"._FILES."</a>:</td><td>".$num."</td></tr>";
				$num = $db->sql_numrows($db->sql_query("SELECT lid FROM ".$prefix."_files WHERE status = '2'"));
				$num = (is_numeric($num)) ? (($num >= 1) ? "<span class=\"sl_red\">".$num."</span>" : "<span class=\"sl_green\">".$num."</span>") : "-";
				$n_cont .= "<tr><td><a href=\"".$admin_file.".php?op=files&amp;status=2\" title=\""._BROCFILES."\">"._BROCFILES."</a>:</td><td>".$num."</td></tr>";
			}
			if (is_active("help") && is_admin_modul("help")) {
				$num = $db->sql_numrows($db->sql_query("SELECT sid FROM ".$prefix."_help WHERE pid = '0' AND status = '0'"));
				$num = (is_numeric($num)) ? (($num >= 1) ? "<span class=\"sl_red\">".$num."</span>" : "<span class=\"sl_green\">".$num."</span>") : "-";
				$n_cont .= "<tr><td><a href=\"".$admin_file.".php?op=help\" title=\""._HELP."\">"._HELP."</a>:</td><td>".$num."</td></tr>";
			}
			if (is_active("jokes") && is_admin_modul("jokes")) {
				$num = $db->sql_numrows($db->sql_query("SELECT jokeid FROM ".$prefix."_jokes WHERE status = '0'"));
				$num = (is_numeric($num)) ? (($num >= 1) ? "<span class=\"sl_red\">".$num."</span>" : "<span class=\"sl_green\">".$num."</span>") : "-";
				$n_cont .= "<tr><td><a href=\"".$admin_file.".php?op=jokes&amp;status=1\" title=\""._JOKES."\">"._JOKES."</a>:</td><td>".$num."</td></tr>";
			}
			if (is_active("links") && is_admin_modul("links")) {
				$num = $db->sql_numrows($db->sql_query("SELECT lid FROM ".$prefix."_links WHERE status = '0'"));
				$num = (is_numeric($num)) ? (($num >= 1) ? "<span class=\"sl_red\">".$num."</span>" : "<span class=\"sl_green\">".$num."</span>") : "-";
				$n_cont .= "<tr><td><a href=\"".$admin_file.".php?op=links&amp;status=1\" title=\""._LINKS."\">"._LINKS."</a>:</td><td>".$num."</td></tr>";
				$num = $db->sql_numrows($db->sql_query("SELECT lid FROM ".$prefix."_links WHERE status = '2'"));
				$num = (is_numeric($num)) ? (($num >= 1) ? "<span class=\"sl_red\">".$num."</span>" : "<span class=\"sl_green\">".$num."</span>") : "-";
				$n_cont .= "<tr><td><a href=\"".$admin_file.".php?op=links&amp;status=2\" title=\""._BROCLINKS."\">"._BROCLINKS."</a>:</td><td>".$num."</td></tr>";
			}
			if (is_active("media") && is_admin_modul("media")) {
				$num = $db->sql_numrows($db->sql_query("SELECT id FROM ".$prefix."_media WHERE status = '0'"));
				$num = (is_numeric($num)) ? (($num >= 1) ? "<span class=\"sl_red\">".$num."</span>" : "<span class=\"sl_green\">".$num."</span>") : "-";
				$n_cont .= "<tr><td><a href=\"".$admin_file.".php?op=media&amp;status=1\" title=\""._MEDIA."\">"._MEDIA."</a>:</td><td>".$num."</td></tr>";
				$num = $db->sql_numrows($db->sql_query("SELECT id FROM ".$prefix."_media WHERE status = '2'"));
				$num = (is_numeric($num)) ? (($num >= 1) ? "<span class=\"sl_red\">".$num."</span>" : "<span class=\"sl_green\">".$num."</span>") : "-";
				$n_cont .= "<tr><td><a href=\"".$admin_file.".php?op=media&amp;status=2\" title=\""._BROCMFILES."\">"._BROCMFILES."</a>:</td><td>".$num."</td></tr>";
			}
			if (is_active("news") && is_admin_modul("news")) {
				$num = $db->sql_numrows($db->sql_query("SELECT sid FROM ".$prefix."_news WHERE status = '0'"));
				$num = (is_numeric($num)) ? (($num >= 1) ? "<span class=\"sl_red\">".$num."</span>" : "<span class=\"sl_green\">".$num."</span>") : "-";
				$n_cont .= "<tr><td><a href=\"".$admin_file.".php?op=news&amp;status=1\" title=\""._NEWS."\">"._NEWS."</a>:</td><td>".$num."</td></tr>";
			}
			if (is_active("pages") && is_admin_modul("pages")) {
				$num = $db->sql_numrows($db->sql_query("SELECT pid FROM ".$prefix."_pages WHERE status = '0'"));
				$num = (is_numeric($num)) ? (($num >= 1) ? "<span class=\"sl_red\">".$num."</span>" : "<span class=\"sl_green\">".$num."</span>") : "-";
				$n_cont .= "<tr><td><a href=\"".$admin_file.".php?op=page&amp;status=1\" title=\""._PAGES."\">"._PAGES."</a>:</td><td>".$num."</td></tr>";
			}
			if (is_active("shop") && is_admin_modul("shop")) {
				$num = $db->sql_numrows($db->sql_query("SELECT id FROM ".$prefix."_clients WHERE active = '2'"));
				$num = (is_numeric($num)) ? (($num >= 1) ? "<span class=\"sl_red\">".$num."</span>" : "<span class=\"sl_green\">".$num."</span>") : "-";
				$n_cont .= "<tr><td><a href=\"".$admin_file.".php?op=shop_clients\" title=\""._CLIENTS."\">"._CLIENTS."</a>:</td><td>".$num."</td></tr>";
				$num = $db->sql_numrows($db->sql_query("SELECT id FROM ".$prefix."_partners WHERE active = '2'"));
				$num = (is_numeric($num)) ? (($num >= 1) ? "<span class=\"sl_red\">".$num."</span>" : "<span class=\"sl_green\">".$num."</span>") : "-";
				$n_cont .= "<tr><td><a href=\"".$admin_file.".php?op=shop_partners\" title=\""._PARTNERS."\">"._PARTNERS."</a>:</td><td>".$num."</td></tr>";
			}
			if (is_active("whois") && is_admin_modul("whois")) {
				$num = $db->sql_numrows($db->sql_query("SELECT id FROM ".$prefix."_whois WHERE status = '0'"));
				$num = (is_numeric($num)) ? (($num >= 1) ? "<span class=\"sl_red\">".$num."</span>" : "<span class=\"sl_green\">".$num."</span>") : "-";
				$n_cont .= "<tr><td><a href=\"".$admin_file.".php?op=whois&amp;status=1\" title=\""._WHOIS."\">"._WHOIS."</a>:</td><td>".$num."</td></tr>";
			}
			$n_cont .= "</table>";
			$ablocks = tpl_block("", _NEW, $n_cont, 3);
			
			$w_cont = "<table class=\"sl_tab_bl\">";
			$num = $db->sql_numrows($db->sql_query("SELECT id FROM ".$prefix."_comment WHERE status = '0'"));
			$num = (is_numeric($num)) ? (($num >= 1) ? "<span class=\"sl_red\">".$num."</span>" : "<span class=\"sl_green\">".$num."</span>") : "-";
			$w_cont .= "<tr><td><a href=\"".$admin_file.".php?op=comm_show&amp;status=1\" title=\""._COMMENTS."\">"._COMMENTS."</a>:</td><td>".$num."</td></tr>";
			$w_cont .= "</table>";
			$ablocks .= tpl_block("", _WAITINGCONT, $w_cont, 4);

			if ($conf['sblock'] && is_admin_god()) {
				include("config/config_stat.php");
				include("config/config.php");
				
				$phpsapi = ucfirst(php_sapi_name());
				$osver = php_uname("s");
				$server = getenv('SERVER_SOFTWARE');
				$gdver = php_gd();
				
				list($dbtime) = $db->sql_fetchrow($db->sql_query("SELECT now()"));
				$dbtime = format_time($dbtime, _TIMESTRING);
				$phptime = date(_TIMESTRING);
				
				$dbver = db_version();
				$dbtotal = $dbfree = 0;
				$dbresult = $db->sql_query("SHOW TABLE STATUS FROM ".$confdb['name']);
				while ($row = $db->sql_fetchrow($dbresult)) {
					$dbtotal += $row['Data_length'] + $row['Index_length'];
					$dbfree += ($row['Data_free']) ? $row['Data_free'] : 0;
				}
				function get_modules($mod) {
					return (function_exists("apache_get_modules")) ? ((array_search($mod, apache_get_modules())) ? tricolor(1, 2, 2, 1, 0, 0, 0, 0, 0) : tricolor(0, 2, 2, 1, 0, 0, 0, 0, 0)) : tricolor(_NO_INFO, 1, 0, 0, 8, 0, 0, 0, _NO_INFO);
				}
				$gzip = (function_exists("gzopen")) ? 1 : 0;
				$bzip = (function_exists("bzopen")) ? 1 : 0;
				
				$s_cont = "<table class=\"sl_tab_bl\">"
				."<tr><td>"._SCLOSE.":</td><td>".tricolor($conf['close'], 2, 0, 0, 0, 0, 0, 0, 0)."</td></tr>"
				."<tr><td>"._STAT.":</td><td>".tricolor($confst['stat'], 2, 2, 0, 0, 0, 0, 0, 0)."</td></tr>"
				."<tr><td>"._REFERERS.":</td><td>".tricolor($confr['refer'], 2, 2, 0, 0, 0, 0, 0, 0)."</td></tr>"
				."<tr><td>"._NEWSLETTER.":</td><td>".tricolor($conf['newsletter'], 2, 2, 0, 0, 0, 0, 0, 0)."</td></tr>"
				."<tr><td>"._SICACHE.":</td><td>".tricolor($conf['cache'], 2, 2, 0, 0, 0, 0, 0, 0)."</td></tr>"
				."<tr><td>"._SIGZIP.":</td><td>".tricolor($conf['gzip'], 2, 2, 0, 0, 0, 0, 0, 0)."</td></tr>"
				."<tr><td>"._SIREWRITE.":</td><td>".tricolor($conf['rewrite'], 2, 2, 0, 0, 0, 0, 0, 0)."</td></tr>"
				."<tr><td colspan=\"2\"><hr></td></tr>"
				."<tr><td>SLAED CMS:</td><td>".tricolor($conf['version'], 1, 0, 0, 10, 0, 0, 0, $conf['version'])."</td></tr>"
				."<tr><td>OS:</td><td>".tricolor($osver, 1, 0, 0, 10, 0, 0, 0, $osver)."</td></tr>"
				."<tr><td>Server:</td><td>".tricolor($server, 1, 0, 0, 10, 0, 0, 0, $server)."</td></tr>"
				."<tr><td>PHP version:</td><td>".tricolor(PHP_VERSION, 0, 1, 0, 10, 0, "4.3.0", "5.3", PHP_VERSION)."</td></tr>"
				."<tr><td>PHP SAPI:</td><td>".tricolor($phpsapi, 1, 0, 0, 8, 0, 0, 0, $phpsapi)."</td></tr>"
				."<tr><td>PHP GD:</td><td>".tricolor($gdver, 0, 1, 0, 10, 0, "2", "2.0.2", $gdver)."</td></tr>"
				."<tr><td>MySQL:</td><td>".tricolor($dbver, 0, 1, 0, 10, 0, "4", "5", $dbver)."</td></tr>"
				."<tr><td>DB size:</td><td>".tricolor($dbtotal, 0, 0, 0, 10, 1, 26214400, 52428800, 0)."</td></tr>"
				."<tr><td>DB overhead:</td><td>".tricolor($dbfree, 0, 0, 10, 0, 1, 512000, 1048576, 0)."</td></tr>"
				."<tr><td>Post max size:</td><td>".tricolor((str_replace("M", "", ini_get("post_max_size")) * 1024 * 1024), 0, 1, 0, 10, 1, 2097152, 4194304, 0)."</td></tr>"
				."<tr><td>File uploads:</td><td>".tricolor(ini_get("file_uploads"), 2, 2, 1, 0, 0, 0, 0, 0)."</td></tr>"
				."<tr><td>Upload max file size:</td><td>".tricolor((str_replace("M", "", ini_get("upload_max_filesize")) * 1024 * 1024), 0, 1, 0, 10, 1, 2097152, 4194304, 0)."</td></tr>"
				."<tr><td>Memory limit:</td><td>".tricolor((str_replace("M", "", ini_get("memory_limit")) * 1024 * 1024), 0, 1, 0, 10, 1, 33554432, 67108864, 0)."</td></tr>"
				."<tr><td>Max input vars:</td><td>".tricolor(ini_get("max_input_vars"), 0, 1, 0, 0, 0, 3800, 4000, 0)."</td></tr>"
				."<tr><td>Execution time:</td><td>".tricolor((ini_get("max_execution_time")." "._SEC."."), 0, 1, 0, 0, 0, 15, 30, 0)."</td></tr>"
				."<tr><td colspan=\"2\"><hr></td></tr>"
				."<tr><td>Mod Rewrite:</td><td>".get_modules("mod_rewrite")."</td></tr>"
				."<tr><td>Mod Deflate:</td><td>".get_modules("mod_deflate")."</td></tr>"
				."<tr><td>Mod Expires:</td><td>".get_modules("mod_expires")."</td></tr>"
				."<tr><td>Mod GZip:</td><td>".get_modules("mod_gzip")."</td></tr>"
				."<tr><td>Mod Headers:</td><td>".get_modules("mod_headers")."</td></tr>"
				."<tr><td>Mod PageSpeed:</td><td>".get_modules("mod_pagespeed")."</td></tr>"
				."<tr><td>GZip compression:</td><td>".tricolor($gzip, 2, 2, 1, 0, 0, 0, 0, 0)."</td></tr>"
				."<tr><td>BZip2 compression:</td><td>".tricolor($bzip, 2, 2, 1, 0, 0, 0, 0, 0)."</td></tr>"
				."<tr><td colspan=\"2\"><hr></td></tr>"
				."<tr><td>PHP timezone:</td><td>".tricolor(date("H:i:s"), 3, 0, 1, 8, 0, $dbtime, $phptime, $phptime)."</td></tr>"
				."<tr><td>MySQL timezone:</td><td>".tricolor(format_time($dbtime, "H:i:s"), 3, 0, 1, 8, 0, $dbtime, $phptime, $dbtime)."</td></tr>";
				
				if (PHP_VERSION < '5.4') {
					$globals = (ini_get("register_globals") == 1) ? 1 : 0;
					$safe_mode = (ini_get("safe_mode") == 1) ? 1 : 0;
					$magic_quotes = (ini_get("magic_quotes_gpc") == 1) ? 1 : 0;
					$s_cont .= "<tr><td>Register globals:</td><td>".tricolor($globals, 2, 3, 1, 0, 0, 0, 0, 0)."</td></tr>"
					."<tr><td>Safe mode:</td><td>".tricolor($safe_mode, 2, 2, 1, 0, 0, 0, 0, 0)."</td></tr>"
					."<tr><td>Magic quotes gpc:</td><td>".tricolor($magic_quotes, 2, 2, 1, 0, 0, 0, 0, 0)."</td></tr>";
				}
				
				$s_cont .= "</table>";
				$ablocks .= tpl_block("", _SYSTEM_INFO, $s_cont, 5);
			}
		}
		$editor = intval(substr($admin[3], 0, 1));
		$e_cont = "<form method=\"post\" action=\"".$admin_file.".php\"><table><tr><td>".redaktor("1", "editor", "", $editor, 1)."<input type=\"hidden\" name=\"refer\" value=\"1\"><input type=\"hidden\" name=\"op\" value=\"changeeditor\"></td></tr></table></form>";
		$ablocks .= tpl_block("", _REDAKTOR, $e_cont, 6);
		return $ablocks;
	}
}

function php_gd() {
	ob_start();
	phpinfo(8);
	$module_info = ob_get_clean();
	$gdversion = (preg_match("#\bgd\s+version\b[^\d\n\r]+?([\d\.]+)#i", $module_info, $matches)) ? $matches[1] : 0;
	return $gdversion;
}

function db_version() {
	global $db;
	list($dbv) = $db->sql_fetchrow($db->sql_query("SELECT VERSION()"));
	return $dbv;
}

function end_chmod($dir, $chm) {
	$out = '';
	if (file_exists($dir) && intval($chm)) {
		$per = substr(decoct(fileperms($dir)), -3);
		if (php_uname('s') == 'Linux' && PHP_VERSION >= '5.3' && $per != $chm) {
			$tdir = 'config/config_chmod.php';
			chmod($tdir, '0'.$chm);
			$tper = substr(decoct(fileperms($tdir)), -3);
			if ($tper == $chm) {
				chmod($dir, '0'.$chm);
				$per = substr(decoct(fileperms($dir)), -3);
			}
		}
		$out = ($per != $chm) ? $dir.' '._ERRORPERM.' CHMOD - '.$chm : '';
	}
	return $out;
}

function save_conf($fp, $arr, $type='', $var='') {
	if (file_exists($fp) && $arr) {
		if (is_array($arr) && $var) {
			$cont = "\$".$var." = array();\n";
			foreach ($arr as $key => $value) $cont .= (preg_match('#<<<HTML#', $value)) ? "\$".$var."['".$key."'] = ".$value.";\n" : "\$".$var."['".$key."'] = \"".$value."\";\n";
		} else {
			$cont = $arr;
		}
		$cons = empty($type) ? 'FUNC_FILE' : 'ADMIN_FILE';
		$cont = "<?php\nif (!defined('".$cons."')) die('Illegal file access');\n\n".$cont."\n?>";
		$fp = fopen($fp, 'wb');
		fwrite($fp, $cont);
		fclose($fp);
	}
}

function ajax_cat() {
	global $prefix, $db, $admin_file, $conf;
	$arg = func_get_args();
	$modul = analyze($arg[0]);
	$obj = analyze($arg[1]);
	$where = ($modul) ? "WHERE a.modul = '".$modul."'" : "";
	$modlink = ($modul) ? "&amp;modul=".$modul : "";
	$result = $db->sql_query("SELECT a.id, a.modul, a.title, a.description, a.img, a.language, a.parentid, a.ordern, a.cstatus, b.id, b.modul, b.ordern, c.id, c.modul, c.ordern FROM ".$prefix."_categories AS a LEFT JOIN ".$prefix."_categories AS b ON (b.modul = a.modul AND b.ordern = a.ordern-1) LEFT JOIN ".$prefix."_categories AS c ON (c.modul = a.modul AND c.ordern = a.ordern+1) ".$where." ORDER BY a.modul, a.ordern");
	if ($db->sql_numrows($result) > 0) {
		while (list($id, $modul, $title, $description, $imgcat, $language, $parentid, $ordern, $cstatus, $con1, $modul1, $order1, $con2, $modul2, $order2) = $db->sql_fetchrow($result)) {
			$massiv[$id] = array($id, $modul, $title, $description, $imgcat, $language, $parentid, $ordern, $cstatus, $con1, $modul1, $order1, $con2, $modul2, $order2);
			unset($id, $modul, $title, $description, $imgcat, $language, $parentid, $ordern, $cstatus, $con1, $modul1, $order1, $con2, $modul2, $order2);
		}
		$fcont = "";
		foreach ($massiv as $key => $val) {
			$id = $val[0];
			$modul = $val[1];
			$title = $val[2];
			$description = $val[3];
			$imgcat = $val[4];
			$language = $val[5];
			$parentid = $val[6];
			$ordern = $val[7];
			$cstatus = $val[8];
			$con1 = $val[9];
			$modul1 = $val[10];
			$order1 = $val[11];
			$con2 = $val[12];
			$modul2 = $val[13];
			$order2 = $val[14];
			if ($modul == "faq") {
				list($pnum) = $db->sql_fetchrow($db->sql_query("SELECT Count(fid) FROM ".$prefix."_faq WHERE catid IN (".$id.")"));
			} elseif ($modul == "files") {
				list($pnum) = $db->sql_fetchrow($db->sql_query("SELECT Count(lid) FROM ".$prefix."_files WHERE cid IN (".$id.")"));
			} elseif ($modul == "forum") {
				list($pnum) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_forum WHERE catid IN (".$id.")"));
			} elseif ($modul == "help") {
				list($pnum) = $db->sql_fetchrow($db->sql_query("SELECT Count(sid) FROM ".$prefix."_help WHERE catid IN (".$id.")"));
			} elseif ($modul == "jokes") {
				list($pnum) = $db->sql_fetchrow($db->sql_query("SELECT Count(jokeid) FROM ".$prefix."_jokes WHERE cat IN (".$id.")"));
			} elseif ($modul == "links") {
				list($pnum) = $db->sql_fetchrow($db->sql_query("SELECT Count(lid) FROM ".$prefix."_links WHERE cid IN (".$id.")"));
			} elseif ($modul == "media") {
				list($pnum) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_media WHERE cid IN (".$id.")"));
			} elseif ($modul == "news") {
				list($pnum) = $db->sql_fetchrow($db->sql_query("SELECT Count(sid) FROM ".$prefix."_news WHERE catid IN (".$id.")"));
			} elseif ($modul == "pages") {
				list($pnum) = $db->sql_fetchrow($db->sql_query("SELECT Count(pid) FROM ".$prefix."_pages WHERE catid IN (".$id.")"));
			} elseif ($modul == "shop") {
				list($pnum) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_products WHERE cid IN (".$id.")"));
			}
			list($ispid) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_categories WHERE parentid IN (".$id.")"));
			$ordernm = $ordern - 1;
			$ordernp = $ordern + 1;
			$active = ($parentid) ? "<div class=\"sl_green\">"._YES."</div>" : "<div class=\"sl_red\">"._NO."</div>";
			$img = ($imgcat) ? "<div class=\"sl_green\">"._YES."</div>" : "<div class=\"sl_red\">"._NO."</div>";
			$flag = $parentid;
			while ($flag != "0") {
				$title = $massiv[$flag][2]." / ".$title;
				$flag = $massiv[$flag][6];
			}
			$descript = ($description) ? $description : _NO;
			$subcat = ($ispid) ? $ispid : _NO;
			$clang = ($conf['multilingual'] == 1) ? ((!$language) ? "<br>"._LANGUAGE.": "._ALL : "<br>"._LANGUAGE.": ".deflang($language)) : "";
			$delete = (!$pnum && !$ispid) ? "||<a href=\"".$admin_file.".php?op=cat_del&amp;id=".$id.$modlink."&amp;refer=1\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$title."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>" : "";
			$fcont .= "<tr><td>".$id."</td>"
			."<td>".title_tip(_DESCRIPTION.": ".$descript."<br>"._CATEGORIES.": ".$subcat.$clang)."<span title=\"".$title."\" class=\"sl_note\">".cutstr($title, 50)."</span></td>"
			."<td>".$pnum."</td>"
			."<td>".$active."</td>"
			."<td>".$img."</td>"
			."<td>".$ordern."</td><td>";
			$fcont .= ($con1) ? "<span OnClick=\"AjaxLoad('GET', '0', 'ajax_cat', 'go=5&amp;op=cat_order&amp;id=".$id."&amp;cid=".$con1."&amp;typ=".$ordernm."&amp;mod=".$modul."&amp;ordern=".$ordern."', ''); return false;\" title=\""._BLOCKUP."\" class=\"sl_bl_up\"></span>" : "";
			$fcont .= ($con2) ? "<span OnClick=\"AjaxLoad('GET', '0', 'ajax_cat', 'go=5&amp;op=cat_order&amp;id=".$id."&amp;cid=".$con2."&amp;typ=".$ordernp."&amp;mod=".$modul."&amp;ordern=".$ordern."', ''); return false;\" title=\""._BLOCKDOWN."\" class=\"sl_bl_down\"></span>" : "";
			$fcont .= "</td><td>".ad_status("", $cstatus)."</td>"
			."<td>".add_menu("<a href=\"".$admin_file.".php?op=cat_edit&amp;cid=".$id.$modlink."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>".$delete)."</td></tr>";
		}
		$cont = "<table class=\"sl_table_list\"><thead><tr><th>"._ID."</th><th>"._CATEGORY."</th><th>".cutstr(_CONTENT, 3, 1)."</th><th>".cutstr(_SUBCATEGORY, 3, 1)."</th><th>".cutstr(_IMG, 2, 1)."</th><th colspan=\"2\">"._WEIGHT."</th><th>"._STATUS."</th><th>"._FUNCTIONS."</th></tr></thead><tbody>".$fcont."</tbody></table>";
	} else {
		$cont = tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	if ($obj) { return $cont; } else { echo $cont; }
}

function cat_order() {
	global $prefix, $db, $admin_file;
	$modul = isset($_GET['mod']) ? analyze($_GET['mod']) : 0;
	if ($modul) {
		$typ = intval($_GET['typ']);
		$ordern = intval($_GET['ordern']);
		$id = intval($_GET['id']);
		$cid = intval($_GET['cid']);
		$db->sql_query("UPDATE ".$prefix."_categories SET ordern = '".$typ."' WHERE id = '".$id."'");
		$db->sql_query("UPDATE ".$prefix."_categories SET ordern = '".$ordern."' WHERE id = '".$cid."'");
	}
	return ajax_cat($modul, 0);
}

function catacess() {
	global $prefix, $db;
	$arg = func_get_args();
	$gids = explode("|", $arg[2]);
	$cont = "<select name=\"".$arg[0]."[]\" multiple=\"multiple\" class=\"".$arg[1]."\">";
	if ($arg[3] < 1) {
		$cont .= "<option value=\"0|0\"";
		$cont .= ($arg[2] == "0|0") ? " selected" : "";
		$cont .= ">"._ALL."</option>";
	}
	if ($arg[3] < 2) {
		$cont .= "<option value=\"1|0\"";
		$cont .= ($arg[2] == "1|0") ? " selected" : "";
		$cont .= ">"._USERS."</option>";
		$where = "";
	} else {
		$where = "WHERE extra = '1'";
	}
	$result = $db->sql_query("SELECT id, name, extra FROM ".$prefix."_groups ".$where." ORDER BY extra, points");
	while (list($id, $name, $extra) = $db->sql_fetchrow($result)) {
		$select = "";
		if ($gids[0] == 2) {
			$massiv = explode(",", $gids[1]);
			foreach ($massiv as $val) {
				if ($val != "" && $val == $id) {
					$select = " selected";
					break;
				}
			}
		}
		$title = ($extra) ? _SPEC_GROUP." \"".$name."\"" : _GROUP." \"".$name."\"";
		$cont .= "<option value=\"2|".$id."\"".$select.">".$title."</option>";
	}
	$cont .= "<option value=\"3|0\"";
	$cont .= ($arg[2] == "3|0") ? " selected" : "";
	$cont .= ">"._ADMIN."</option></select>";
	return $cont;
}

function scatacess($auth) {
	$gids = explode("|", $auth);
	foreach ($auth as $val) {
		$gids = explode("|", $val);
		if ($gids[0] == 2) {
			$acess = "2";
			$select[] = $gids[1];
		} else {
			$acess = $gids[0];
			$select = array();
			$select[] = $gids[1];
			break;
		}
	}
	return $acess."|".implode(",", $select);
}

function ajax_block() {
	global $prefix, $db, $currentlang, $conf, $admin_file;
	$fcont = "";
	$result = $db->sql_query("SELECT a.bid, a.bkey, a.title, a.url, a.bposition, a.weight, a.active, a.blanguage, a.blockfile, a.view, a.expire, a.action, b.bid, b.bposition, b.weight, c.bid, c.bposition, c.weight FROM ".$prefix."_blocks AS a LEFT JOIN ".$prefix."_blocks AS b ON (b.bposition = a.bposition AND b.weight = a.weight-1) LEFT JOIN ".$prefix."_blocks AS c ON (c.bposition = a.bposition AND c.weight = a.weight+1) ORDER BY a.bposition, a.weight");
	while (list($bid, $bkey, $title, $url, $bposition, $weight, $active, $blanguage, $blockfile, $view, $expire, $action, $con1, $bposition1, $weight1, $con2, $bposition2, $weight2) = $db->sql_fetchrow($result)) {
		if (($expire && $expire < time()) || (!$active && $expire)) {
			if ($action == "d") {
				$db->sql_query("UPDATE ".$prefix."_blocks SET active = '0', expire = '0' WHERE bid = '".$bid."'");
			} elseif ($action == "r") {
				$db->sql_query("DELETE FROM ".$prefix."_blocks WHERE bid = '".$bid."'");
			}
		}
		$weight_minus = $weight - 1;
		$weight_plus = $weight + 1;
		$exp = intval($expire - time());
		$exp = ($exp > 0) ? display_time($exp) : _UNLIMITED;
		$blang = ($conf['multilingual'] == 1) ? ((!$blanguage) ? "<br>"._LANGUAGE.": "._ALL : "<br>"._LANGUAGE.": ".deflang($blanguage)) : "";
		$fcont .= "<tr><td>".$bid."</td><td>".title_tip(_NAME.": ".$title."<br>"._PURCHASED.": ".$exp.$blang).cutstr(defconst($title), 15)."</td>";
		if ($bposition == "l") {
			$bposition = "<span title=\""._LEFTBLOCK."\" class=\"sl_note\">"._LEFT."</span>";
		} elseif ($bposition == "r") {
			$bposition = "<span title=\""._RIGHTBLOCK."\" class=\"sl_note\">"._RIGHT."</span>";
		} elseif ($bposition == "c") {
			$bposition = "<span title=\""._CENTERBLOCK."\" class=\"sl_note\">"._CENTERUP."</span>";
		} elseif ($bposition == "d") {
			$bposition = "<span title=\""._CENTERBLOCK."\" class=\"sl_note\">"._CENTERDOWN."</span>";
		} elseif ($bposition == "b") {
			$bposition = "<span title=\""._BANNER."\" class=\"sl_note\">"._BANNERUP."</span>";
		} elseif ($bposition == "f") {
			$bposition = "<span title=\""._BANNER."\" class=\"sl_note\">"._BANNERDOWN."</span>";
		}
		if ($bkey == "") {
			$type = ($url) ? "RSS/RDF" : "HTML";
			if ($blockfile != "") $type = _BLOCKFILE2;
		} elseif ($bkey != "") {
			$type = _BLOCKSYSTEM;
		}
		$fcont .= "<td>".$type."</td>";
		if ($view == 0) {
			$who_view = _MVALL;
		} elseif ($view == 1) {
			$who_view = _MVUSERS;
		} elseif ($view == 2) {
			$who_view = _MVADMIN;
		} elseif ($view == 3) {
			$who_view = _MVANON;
		}
		$fcont .= "<td>".$who_view."</td>"
		."<td>".$bposition."</td>"
		."<td>".$weight."</td><td>";
		$fcont .= ($con1) ? "<span OnClick=\"AjaxLoad('GET', '0', 'ajax_block', 'go=5&amp;op=blocks_order&amp;id=".$bid."&amp;cid=".$con1."&amp;typ=".$weight_minus."&amp;ordern=".$weight."', ''); return false;\" title=\""._BLOCKUP."\" class=\"sl_bl_up\"></span>" : "";
		$fcont .= ($con2) ? "<span OnClick=\"AjaxLoad('GET', '0', 'ajax_block', 'go=5&amp;op=blocks_order&amp;id=".$bid."&amp;cid=".$con2."&amp;typ=".$weight_plus."&amp;ordern=".$weight."', ''); return false;\" title=\""._BLOCKDOWN."\" class=\"sl_bl_down\"></span>" : "";
		$fcont .= "</td><td>".ad_status("", $active)."</td><td>".add_menu(ad_status($admin_file.".php?op=blocks_change&amp;bid=".$bid."&amp;act=".$active, $active)."||<a href=\"".$admin_file.".php?op=blocks_edit&amp;bid=".$bid."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=blocks_delete&amp;id=".$bid."\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$title."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>")."</td></tr>";
	}
	$cont = "<table class=\"sl_table_list\"><thead><tr><th>"._ID."</th><th>"._TITLE."</th><th>"._TYPE."</th><th>"._VIEW."</th><th>"._POSITION."</th><th colspan=\"2\">"._WEIGHT."</th><th>"._STATUS."</th><th>"._FUNCTIONS."</th></tr></thead><tbody>".$fcont."</tbody></table>";
	return $cont;
}

function blocks_order() {
	global $prefix, $db, $admin_file;
	$typ = intval($_GET['typ']);
	$ordern = intval($_GET['ordern']);
	$id = intval($_GET['id']);
	$cid = intval($_GET['cid']);
	$db->sql_query("UPDATE ".$prefix."_blocks SET weight = '".$typ."' WHERE bid = '".$id."'");
	$db->sql_query("UPDATE ".$prefix."_blocks SET weight = '".$ordern."' WHERE bid = '".$cid."'");
	echo ajax_block();
}

# Favorites list view
function fav_aliste() {
	global $prefix, $db, $conf, $conffav;
	$arg = func_get_args();
	$obj = empty($arg[0]) ? 0 : 1;
	
	$newlistnum = intval($conffav['anum']);
	$num = (empty($_GET['cid'])) ? "1" : intval($_GET['cid']);
	$offset = ($num-1) * $newlistnum;
	$offset = intval($offset);
	list($fav_num) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_favorites"));
	
	$result = $db->sql_query("SELECT id, modul FROM ".$prefix."_favorites ORDER BY id DESC LIMIT ".$offset.", ".$newlistnum);
	while (list($id, $modul) = $db->sql_fetchrow($result)) $fmassiv[$modul][] = $id;
	
	if (is_array($fmassiv)) {
		foreach ($fmassiv as $key => $val) {
			$fid = implode(",", $val);
			$numl = count($val);
			if ($key == "faq") {
				$result = $db->sql_query("SELECT f.id, f.fid, f.modul, n.title, u.user_name FROM ".$prefix."_favorites AS f LEFT JOIN ".$prefix."_faq AS n ON (f.fid = n.fid) LEFT JOIN ".$prefix."_users AS u ON (f.uid = u.user_id) WHERE f.id IN (".$fid.") ORDER BY f.id DESC LIMIT 0, ".$numl."");
				while (list($id, $fid, $modul, $title, $uname) = $db->sql_fetchrow($result)) $ffmassiv[] = array($id, $fid, $modul, $title, $uname);
			} elseif ($key == "files") {
				$result = $db->sql_query("SELECT f.id, f.fid, f.modul, n.title, u.user_name FROM ".$prefix."_favorites AS f LEFT JOIN ".$prefix."_files AS n ON (f.fid = n.lid) LEFT JOIN ".$prefix."_users AS u ON (f.uid = u.user_id) WHERE f.id IN (".$fid.") ORDER BY f.id DESC LIMIT 0, ".$numl."");
				while (list($id, $fid, $modul, $title, $uname) = $db->sql_fetchrow($result)) $ffmassiv[] = array($id, $fid, $modul, $title, $uname);
			} elseif ($key == "forum") {
				$result = $db->sql_query("SELECT f.id, f.fid, f.modul, n.title, u.user_name FROM ".$prefix."_favorites AS f LEFT JOIN ".$prefix."_forum AS n ON (f.fid = n.id) LEFT JOIN ".$prefix."_users AS u ON (f.uid = u.user_id) WHERE f.id IN (".$fid.") ORDER BY f.id DESC LIMIT 0, ".$numl."");
				while (list($id, $fid, $modul, $title, $uname) = $db->sql_fetchrow($result)) $ffmassiv[] = array($id, $fid, $modul, $title, $uname);
			} elseif ($key == "help") {
				$result = $db->sql_query("SELECT f.id, f.fid, f.modul, n.title, u.user_name FROM ".$prefix."_favorites AS f LEFT JOIN ".$prefix."_help AS n ON (f.fid = n.sid) LEFT JOIN ".$prefix."_users AS u ON (f.uid = u.user_id) WHERE f.id IN (".$fid.") ORDER BY f.id DESC LIMIT 0, ".$numl."");
				while (list($id, $fid, $modul, $title, $uname) = $db->sql_fetchrow($result)) $ffmassiv[] = array($id, $fid, $modul, $title, $uname);
			} elseif ($key == "links") {
				$result = $db->sql_query("SELECT f.id, f.fid, f.modul, n.title, u.user_name FROM ".$prefix."_favorites AS f LEFT JOIN ".$prefix."_links AS n ON (f.fid = n.lid) LEFT JOIN ".$prefix."_users AS u ON (f.uid = u.user_id) WHERE f.id IN (".$fid.") ORDER BY f.id DESC LIMIT 0, ".$numl."");
				while (list($id, $fid, $modul, $title, $uname) = $db->sql_fetchrow($result)) $ffmassiv[] = array($id, $fid, $modul, $title, $uname);
			} elseif ($key == "media") {
				include("config/config_media.php");
				$result = $db->sql_query("SELECT f.id, f.fid, f.modul, n.title, n.subtitle, u.user_name FROM ".$prefix."_favorites AS f LEFT JOIN ".$prefix."_media AS n ON (f.fid = n.id) LEFT JOIN ".$prefix."_users AS u ON (f.uid = u.user_id) WHERE f.id IN (".$fid.") ORDER BY f.id DESC LIMIT 0, ".$numl."");
				while (list($id, $fid, $modul, $title, $subtitle, $uname) = $db->sql_fetchrow($result)) {
					$title = ($subtitle) ? $title." ".urldecode($confm['mdefis'])." ".$subtitle : $title;
					$ffmassiv[] = array($id, $fid, $modul, $title, $uname);
				}
			} elseif ($key == "news") {
				$result = $db->sql_query("SELECT f.id, f.fid, f.modul, n.title, u.user_name FROM ".$prefix."_favorites AS f LEFT JOIN ".$prefix."_news AS n ON (f.fid = n.sid) LEFT JOIN ".$prefix."_users AS u ON (f.uid = u.user_id) WHERE f.id IN (".$fid.") ORDER BY f.id DESC LIMIT 0, ".$numl."");
				while (list($id, $fid, $modul, $title, $uname) = $db->sql_fetchrow($result)) $ffmassiv[] = array($id, $fid, $modul, $title, $uname);
			} elseif ($key == "pages") {
				$result = $db->sql_query("SELECT f.id, f.fid, f.modul, n.title, u.user_name FROM ".$prefix."_favorites AS f LEFT JOIN ".$prefix."_pages AS n ON (f.fid = n.pid) LEFT JOIN ".$prefix."_users AS u ON (f.uid = u.user_id) WHERE f.id IN (".$fid.") ORDER BY f.id DESC LIMIT 0, ".$numl."");
				while (list($id, $fid, $modul, $title, $uname) = $db->sql_fetchrow($result)) $ffmassiv[] = array($id, $fid, $modul, $title, $uname);
			} elseif ($key == "shop") {
				$result = $db->sql_query("SELECT f.id, f.fid, f.modul, n.title, u.user_name FROM ".$prefix."_favorites AS f LEFT JOIN ".$prefix."_products AS n ON (f.fid = n.id) LEFT JOIN ".$prefix."_users AS u ON (f.uid = u.user_id) WHERE f.id IN (".$fid.") ORDER BY f.id DESC LIMIT 0, ".$numl."");
				while (list($id, $fid, $modul, $title, $uname) = $db->sql_fetchrow($result)) $ffmassiv[] = array($id, $fid, $modul, $title, $uname);
			}
		}
		if ($ffmassiv) {
			$cont = "<table class=\"sl_table_list\"><thead><tr><th>"._ID."</th><th>"._TITLE."</th><th>"._MODUL."</th><th>"._POSTEDBY."</th><th>"._FUNCTIONS."</th></tr></thead><tbody>";
			foreach ($ffmassiv as $key => $val) {
				$id = $val[0];
				$fid = $val[1];
				$modul = $val[2];
				$title = $val[3];
				$uname = ($val[4]) ? user_info($val[4]) : $confu['anonym'];
				$cont .= "<tr>"
				."<td>".$id."</td>"
				."<td><span title=\"".$title."\" class=\"sl_note\">".cutstr($title, 60)."</span></td>"
				."<td>".deflmconst($modul)."</td>"
				."<td>".$uname."</td>"
				."<td>".add_menu("<a href=\"index.php?name=".$modul."&amp;op=view&amp;id=".$fid."#".$fid."\" title=\""._MVIEW."\">"._MVIEW."</a>||<a OnClick=\"AjaxLoad('GET', '0', 'fav_aliste', 'go=5&amp;op=fav_adel&amp;id=".$id."', ''); return false;\" title=\""._ONDELETE."\">"._ONDELETE."</a>")."</td>";
			}
			$cont .= "</tbody></table>";
			$numpages = ceil($fav_num / $newlistnum);
			$cont .= num_ajax("pagenum", $fav_num, $numpages, $newlistnum, $conffav['anump'], $num, "0", "5", "fav_aliste", "fav_aliste", "", "", "");
		} else {
			$cont = tpl_warn("warn", _NO_INFO, "", "", "info");
		}
	} else {
		$cont = tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	if ($obj) { return $cont; } else { echo $cont; }
}

# Favorites delete
function fav_adel() {
	global $prefix, $db;
	$id = intval($_GET['id']);
	$db->sql_query("DELETE FROM ".$prefix."_favorites WHERE id = '".$id."'");
	return fav_aliste(0);
}

# Private messages list view
function ajax_privat() {
	global $prefix, $db, $confu, $confpr;
	$arg = func_get_args();
	$obj = empty($arg[0]) ? 0 : 1;
	
	$newlistnum = intval($confpr['anum']);
	$num = empty($_GET['cid']) ? "1" : intval($_GET['cid']);
	$offset = ($num - 1) * $newlistnum;
	$offset = intval($offset);
	list($fav_num) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_privat"));
	
	$result = $db->sql_query("SELECT p.id, p.uidin, p.uidout, p.title, p.content, p.date, p.status, i.user_name, o.user_name FROM ".$prefix."_privat AS p LEFT JOIN ".$prefix."_users AS i ON (p.uidin = i.user_id) LEFT JOIN ".$prefix."_users AS o ON (p.uidout = o.user_id) ORDER BY p.date DESC LIMIT ".$offset.", ".$newlistnum);
	if ($db->sql_numrows($result) > 0) {
		$cont = "<table class=\"sl_table_list\"><thead><tr><th>"._ID."</th><th>"._TITLE."</th><th>"._PRSE."</th><th>"._PRRE."</th><th>"._DATE."</th><th>"._STATUS."</th><th>"._FUNCTIONS."</th></tr></thead><tbody>";
		while (list($id, $uidin, $uidout, $title, $content, $date, $status, $user_re, $user_se) = $db->sql_fetchrow($result)) {
			$unre = ($user_re) ? user_info($user_re) : $confu['anonym'];
			$unse = ($user_se) ? user_info($user_se) : $confu['anonym'];
			$date = format_time($date, _TIMESTRING);
			$info = bb_decode($content, "privat");
			$cont .= "<tr>"
			."<td>".$id."</td>"
			."<td>".title_tip($info)."<span title=\"".$title."\" class=\"sl_note\">".cutstr($title, 30)."</span></td>"
			."<td>".$unse."</td>"
			."<td>".$unre."</td>"
			."<td>".$date."</td>"
			."<td>".ad_status("", $status, 1)."</td>"
			."<td>".add_menu("<a OnClick=\"AjaxLoad('GET', '0', 'ajax_privat', 'go=5&amp;op=ajax_privat_del&amp;id=".$id."', ''); return false;\" title=\""._ONDELETE."\">"._ONDELETE."</a>")."</td>";
		}
		$cont .= "</tbody></table>";
		$numpages = ceil($fav_num / $newlistnum);
		$cont .= num_ajax("pagenum", $fav_num, $numpages, $newlistnum, $confpr['anump'], $num, "0", "5", "ajax_privat", "ajax_privat", "", "", "");
	} else {
		$cont = tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	if ($obj) { return $cont; } else { echo $cont; }
}

# Private message delete
function ajax_privat_del() {
	global $prefix, $db;
	$id = intval($_GET['id']);
	$db->sql_query("DELETE FROM ".$prefix."_privat WHERE id = '".$id."'");
	return ajax_privat(0);
}

# Show uploads files for admin
function ashow_files() {
	global $user;
	include("config/config_uploads.php");
	$id = isset($_GET['id']) ? analyze($_GET['id']) : 0;
	$dir = isset($_GET['dir']) ? strtolower($_GET['dir']) : "";
	$gzip = isset($_GET['cid']) ? intval($_GET['cid']) : 0;
	$con = explode("|", $confup[$dir]);
	$connum = intval($con[7]) ? $con[7] : "50";
	$file = isset($_GET['file']) ? text_filter($_GET['file']) : "";
	$num = ($gzip) ? $gzip : "1";
	$path = ($id == 1) ? "uploads/".$dir."/" : "uploads/".$dir."/thumb/";
	if (is_dir($path)) {
		if ($file && $dir) {
			if (!$gzip) {
				@unlink($path.$file);
			} else {
				zip_compress($path.$file, $path.$file);
			}
		}
		$files = array();
		$dh = opendir($path);
		while ($entry = readdir($dh)) {
			if ($entry != "." && $entry != ".." && $entry != "index.html" && !is_dir($path.$entry)) $files[] = array(filemtime($path.$entry), $entry);
		}
		closedir($dh);
		if (is_array($files)) {
			$a = 0;
			rsort($files);
			foreach ($files as $entry) {
				$filesize = filesize($path.$entry[1]);
				list($imgwidth, $imgheight) = getimagesize($path.$entry[1]);
				$type = strtolower(substr(strrchr($entry[1], "."), 1));
				$ftype = array("png", "jpg", "jpeg", "gif", "bmp");
				$dirfile = (preg_match("#php.*|js|htm|html|phtml|cgi|pl|perl|asp#i", $type)) ? "<span class=\"sl_red\">".$entry[1]."</span>" : $entry[1];
				if (in_array($type, $ftype) && $imgwidth && $imgheight) {
					$img = "<div OnClick=\"HideShow('sf-form-".$a."', 'fold', 'up', 500);\" class=\"sl_drop sl_preview_mini\" style=\"background-image: url(".$path.$entry[1].");\" title=\""._IMG."\"><span id=\"sf-form-".$a."\" class=\"sl_drop-form\"><img src=\"".$path.$entry[1]."\" alt=\""._IMG."\" title=\""._IMG."\"></span></div>";
					$isize = $imgwidth." x ".$imgheight;
				} else {
					$img = "<div class=\"sl_preview_mini\" style=\"background-image: url(templates/admin/images/admin/no.png);\" title=\""._NO."\"></div>";
					$isize = _NO;
				}
				$show = (zip_check()) ? "||<a OnClick=\"AjaxLoad('GET', '0', 'f".$id."', 'go=5&amp;op=ashow_files&amp;id=".$id."&amp;dir=".$dir."&amp;cid=1&amp;file=".$entry[1]."', ''); return false;\" title=\""._ZIP."\">"._ZIP."</a>" : "";
				$show .= "||<a OnClick=\"AjaxLoad('GET', '0', 'f".$id."', 'go=5&amp;op=ashow_files&amp;id=".$id."&amp;dir=".$dir."&amp;cid=0&amp;file=".$entry[1]."', ''); return false;\" title=\""._ONDELETE."\">"._ONDELETE."</a>";
				$contents[] = "<tr><td>".$img."</td><td>".$dirfile."</td><td>".date(_TIMESTRING, $entry[0])."</td><td>".files_size($filesize)."</td><td>".$isize."</td><td>".add_menu($show)."</td></tr>";
				$a++;
			}
		}
		$numpages = ceil($a / $connum);
		$offset = ($num - 1) * $connum;
		$tnum = ($offset) ? $connum + $offset : $connum;
		$cont = "";
		for ($i = $offset; $i < $tnum; $i++) {
			if (!empty($contents[$i])) $cont .= $contents[$i];
		}
		$contnum = ($a > $connum) ? num_ajax("pagenum", $a, $numpages, $connum, "", $num, "0", "5", "ashow_files", "f".$id, $id, "", $dir) : "";
		$content = ($cont) ? "<table class=\"sl_table_list\"><thead><tr><th>".cutstr(_IMG, 4, 1)."</th><th>"._FILE."</th><th>"._DATE."</th><th>"._SIZE."</th><th>"._WIDTH." x "._HEIGHT."</th><th>"._FUNCTIONS."</th></tr></thead><tbody>".$cont."</tbody></table>".$contnum : "";
	} else {
		$content = tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $content;
}

# Navi admin bookmarks
function navi_gen() {
	global $admin_file;
	$narg = func_get_args();
	$menutab = empty($narg[11]) ? "menutab" : $narg[11];
	$cont = "<ul id=\"".$menutab."\" class=\"reset tabmenu\">";
	$k = 0;
	foreach ($narg[4] as $val) {
		if ($val != "") {
			$sel = ($k == $narg[8]) ? " class=\"selected\"" : "";
			if ($narg[9]) {
				$scont = "<ul id=\"".$menutab."s\" class=\"reset tabsubmenu\">";
				$l= 0;
				foreach ($narg[6] as $vals) {
					if ($vals != "") {
						$ssel = ($l== $narg[10]) ? " class=\"selected\"" : "";
						$hrefs = ($narg[5][$l]) ? "href=\"".$admin_file.".php?op=".$narg[5][$l] : "rel=\"tabcs".$l."\" href=\"#";
						$scont .= "<li><a ".$hrefs."\"".$ssel."><b>".$vals."</b></a></li>";
						$l++;
					}
				}
				$scont .= "</ul>";
			}
			$href = ($narg[3][$k]) ? "href=\"".$admin_file.".php?op=".$narg[3][$k] : "rel=\"tabc".$k."\" href=\"#";
			$cont .= "<li><a ".$href."\"".$sel."><b>".$val."</b></a></li>";
			$k++;
		}
	}
	$cont .= (!empty($scont)) ? "</ul>".$scont : "</ul>";
	return tpl_eval("title", $narg[0], $narg[1], $narg[2], $cont);
}

# Format comments access
function com_access() {
	$arg = func_get_args();
	$class = ($arg[2]) ? " class=\"".$arg[2]."\"" : "";
	$cont = "<select name=\"".$arg[0]."\"".$class.">";
	$mods = array(_DEACTIVATE, _APOSTMOD, _APOSTNOMOD);
	for ($i = 0; $i < count($mods); $i++) {
		$sel = ($arg[1] == $i) ? " selected" : "";
		$cont .= "<option value=\"".$i."\"".$sel.">".$mods[$i]."</option>";
	}
	$cont .= "</select>";
	return $cont;
}

# Add voting
function add_voting() {
	global $prefix, $db, $currentlang, $conf;
	$arg = func_get_args();
	$modul = analyze($arg[0]);
	$querylang = ($conf['multilingual'] == 1) ? "(language = '".$currentlang."' OR language = '') AND modul = '".$modul."' AND date <= now() AND (enddate >= now() AND status = '0' OR status = '1')" : "modul = '".$modul."' AND date <= now() AND (enddate >= now() AND status = '0' OR status = '1')";
	$class = ($arg[3]) ? "sl_field ".$arg[3] : "sl_field";
	$cont = "<select name=\"".$arg[1]."\" class=\"".$class."\"><option value=\"0\">"._NO."</option>";
	$result = $db->sql_query("SELECT id, title FROM ".$prefix."_voting WHERE ".$querylang." ORDER BY id DESC");
	if ($db->sql_numrows($result) > 0) {
		while (list($id, $title) = $db->sql_fetchrow($result)) {
			$sel = ($arg[2] == $id) ? " selected" : "";
			$cont .= "<option value=\"".$id."\"".$sel.">".$title."</option>";
		}
	}
	$cont .= "</select>";
	return $cont;
}

# Edit select list
function edit_list() {
	global $conf;
	$arg = func_get_args();
	$modul = analyze($arg[0]);
	$class = ($arg[2]) ? " class=\"".$arg[2]."\"" : "";
	$cont = "<select name=\"".$arg[1]."\" title=\""._CHECKOP."\"".$class.">";
	$cont .= "<optgroup label=\""._OPMOD."\" class=\"sl_label\">";
	$mass = array(_ACTIVATE => "a1", _DEACTIVATE => "a0", _FIXED => "f1", _LNFIX => "f0", _LHOME => "h1", _LNHOME => "h0", _LADATE => "t", _DELETE => "d");
	foreach ($mass as $var_n => $var_v) $cont .= "<option value=\"".$var_v."\">".$var_n."</option>";
	$cont .= "</optgroup><optgroup label=\""._COMMENTS."\" class=\"sl_label\">";
	$coms = array(_DEACTIVATE => "c0", _APOSTMOD => "c1", _APOSTNOMOD => "c2");
	foreach ($coms as $var_n => $var_v) $cont .= "<option value=\"".$var_v."\">".$var_n."</option>";
	$cont .= "</optgroup><optgroup label=\""._MOVETO."\" class=\"sl_label\">".getcat($modul, "", "", "", "", "1")."</optgroup>";
	$cont .= "</select>";
	return $cont;
}

# View and edit info
function adm_info() {
	global $currentlang, $conf;
	$arg = func_get_args();
	$obj = empty($arg[0]) ? 0 : 1;
	$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
	$cont = "";
	if ($conf['adminfo'] && $id) {
		$mod = isset($_POST['mod']) ? var_filter($_POST['mod']) : "";
		$name = isset($_POST['name']) ? var_filter($_POST['name']) : "";
		$content = save_text(trim($_POST['text']));
		$fpdir = $mod."admin/info/".$name.".html";
		if ($content) {
			$fp = fopen($fpdir, "wb");
			fwrite($fp, $content);
			fclose($fp);
		}
		$thefile = (file_exists($fpdir)) ? file_get_contents($fpdir) : _NO_INFO;
	} else {
		$mod = ($arg[1]) ? "modules/".$arg[1]."/" : "";
		$file = ($arg[2]) ? $arg[2]."-" : "";
		$name = $file.$currentlang;
		$dir = $mod."admin/info/".$name.".html";
		$thefile = (file_exists($dir)) ? file_get_contents($dir) : _NO_INFO;
		if ($conf['adminfo']) {
			$permtest = end_chmod($dir, 666);
			if ($permtest) $cont = tpl_warn("warn", $permtest, "", "", "warn");
		}
	}
	$cont .= tpl_eval("open");
	$cont .= bb_decode($thefile, "info");
	if ($conf['adminfo']) {
		$cont .= "<hr><form name=\"post\" id=\"formadm_info\" method=\"post\"><table class=\"sl_table_edit\">"
		."<tr><td>".textarea("1", "text", $thefile, "info", "25")."</td></tr>"
		."<tr><td class=\"sl_center\"><input type=\"submit\" OnClick=\"AjaxLoad('POST', '1', 'adm_info', 'go=5&amp;op=adm_info&amp;id=1&amp;mod=".$mod."&amp;name=".$name."', { 'text':'"._CERROR1."' }); return false;\" value=\""._SAVECHANGES."\" title=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr>"
		."</table></form>";
	}
	$cont .= tpl_eval("close", "");
	if ($obj) { return $cont; } else { echo $cont; }
}
?>