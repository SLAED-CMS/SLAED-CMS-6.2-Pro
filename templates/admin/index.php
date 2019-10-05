<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("FUNC_FILE")) die("Illegal file access");

function tpl_head() {
	global $prefix, $db, $theme, $blocks, $admin, $admin_file, $conf;
	$lang = "";
	$cont = "";
	$arg = func_get_args();
	if (is_admin()) {
		if ($conf['multilingual'] == 1) {
			$lang = "";
			$dir = opendir("language");
			while (false !== ($file = readdir($dir))) {
				if (preg_match("/^lang\-(.+)\.php/", $file, $matches)) {
					$lfound = $matches[1];
					$title = deflang($lfound);
					$lang .= "<a href=\"".$admin_file.".php?newlang=".$lfound."\"><img src=\"".img_find("language/".$lfound."_mini.png")."\" alt=\"".$title."\" title=\"".$title."\"></a>";
				}
			}
			closedir($dir);
		}
		if (!is_admin_god()) {
			$uname = _HELLO.", ".substr($admin[1], 0, 25)."!";
			$cont = "<li class=\"sl_first\"><a href=\"#\" title=\"".$uname."\"><b>".$uname."</b></a></li>"
			."<li><a href=\"".$admin_file.".php\" title=\""._ADMINMENU."\"><b>"._HOME."</b></a></li>"
			."<li><a href=\"index.php\" target=\"_blank\" title=\""._SITE."\"><b>"._SITE."</b></a></li>"
			."<li><a href=\"index.php?name=account\" target=\"_blank\" title=\""._ACCOUNT."\"><b>"._ACCOUNT."</b></a></li>"
			."<li><a href=\"".$admin_file.".php?op=logout\" title=\""._LOGOUT."\"><b>"._LOGOUT."</b></a></li>";
		} else {
			$cont = "<li class=\"sl_first\"><a href=\"".$admin_file.".php\" title=\""._ADMINMENU."\"><b>"._HOME."</b></a></li>"
			."<li><a href=\"".$admin_file.".php?op=blocks_show\" title=\""._BLOCKS."\"><b>"._BLOCKS."</b></a></li>"
			."<li><a href=\"".$admin_file.".php?op=module\" title=\""._MODULES."\"><b>"._MODULES."</b></a></li>"
			."<li><a href=\"".$admin_file.".php?op=cat_show\" title=\""._CATEGORIES."\"><b>"._CATEGORIES."</b></a></li>"
			."<li><a href=\"index.php\" target=\"_blank\" title=\""._SITE."\"><b>"._SITE."</b></a></li>"
			."<li><a href=\"index.php?name=account\" target=\"_blank\" title=\""._ACCOUNT."\"><b>"._ACCOUNT."</b></a></li>"
			."<li><a href=\"".$admin_file.".php?op=logout\" title=\""._LOGOUT."\"><b>"._LOGOUT."</b></a></li>";
		}
		$ablocks = panelblock().admininfo().adminblock();
	} else {
		$ablocks = ($db->sql_numrows($db->sql_query("SELECT * FROM ".$prefix."_admins")) == 0) ? _ADMINLOGIN_NEW : _ADMINLOGIN;
	}
	$lan = array($cont, $lang, $ablocks);
	eval("\$r_file=\"".addslashes($arg[0])."\";");
	return stripslashes($r_file);
}

function tpl_block() {
	global $blockg, $theme, $conf;
	$arg = func_get_args();
	$lan = array(_OPCL);
	static $cach;
	if (!isset($cach)) $cach = create_function("\$arg, \$lan", "global \$blockg, \$theme, \$conf; return \"".addslashes(file_get_contents(get_theme_file("block-left")))."\";");
	return $cach($arg, $lan);
}

function tpl_foot() {
	global $blockg, $theme, $conf;
	$arg = func_get_args();
	$lan = array(_PAGETOP);
	eval("\$r_file=\"".addslashes($arg[0])."\";");
	return stripslashes($r_file);
}
?>