<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("ADMIN_FILE") || !is_admin_god()) die("Illegal file access");

function ratings_navi() {
	panel();
	$narg = func_get_args();
	$ops = array("ratings", "ratings_info");
	$lang = array(_HOME, _INFO);
	return navi_gen(_RATINGS, "ratings.png", "", $ops, $lang, "", "", $narg[0], $narg[1], $narg[2], $narg[3]);
}

function ratings() {
	global $admin_file;
	head();
	$cont = ratings_navi(0, 0, 0, 0);
	include("config/config_ratings.php");
	$permtest = end_chmod("config/config_ratings.php", 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$mods = array("account", "faq", "files", "forum", "help", "jokes", "links", "media", "news", "pages", "shop");
	$i = 0;
	$content = "";
	foreach ($mods as $val) {
		if ($val != "") {
			$con = explode("|", $confra[$val]);
			$hr = ($i == "0") ? "" : "<tr><td colspan=\"2\" class=\"sl_center\"><hr></td></tr>";
			$content .= $hr.""
			."<tr><td>"._MODUL.":</td><td><span title=\""._MODUL.": ".$val."\" class=\"sl_note\">".deflmconst($val)."</span></td></tr>"
			."<tr><td>"._VOTING_TIME.":</td><td><input type=\"number\" name=\"time[]\" value=\"".intval($con[0] / 86400)."\" class=\"sl_conf\" placeholder=\""._VOTING_TIME."\" required></td></tr>"
			."<tr><td>"._C_21."</td><td>".radio_form($con[1], $i."in")."</td></tr>"
			."<tr><td>"._C_22."</td><td>".radio_form($con[2], $i."view")."</td></tr>";
			$i++;
		}
	}
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_conf\">".$content."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"ratings_save_conf\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function ratings_save_conf() {
	global $admin_file;
	include("config/config_ratings.php");
	$content = "\$confra = array();\n";
	$mods = array("account", "faq", "files", "forum", "help", "jokes", "links", "media", "news", "pages", "shop");
	$i = 0;
	foreach ($mods as $val) {
		if ($val != "") {
			$xtime = (!intval($_POST['time'][$i])) ? 2592000 : $_POST['time'][$i] * 86400;
			$content .= "\$confra['".$val."'] = \"".$xtime."|".$_POST[$i.'in']."|".$_POST[$i.'view']."\";\n";
			$i++;
		}
	}
	save_conf("config/config_ratings.php", $content);
	header("Location: ".$admin_file.".php?op=ratings");
}

function ratings_info() {
	head();
	echo ratings_navi(0, 1, 0, 0)."<div id=\"repadm_info\">".adm_info(1, 0, "ratings")."</div>";
	foot();
}

switch($op) {
	case "ratings":
	ratings();
	break;
	
	case "ratings_save_conf":
	ratings_save_conf();
	break;
	
	case "ratings_info":
	ratings_info();
	break;
}
?>