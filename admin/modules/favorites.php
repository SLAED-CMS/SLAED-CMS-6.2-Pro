<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("ADMIN_FILE") || !is_admin_god()) die("Illegal file access");

function favor_navi() {
	panel();
	$narg = func_get_args();
	$ops = array("favorites", "favor_conf", "favor_info");
	$lang = array(_HOME, _PREFERENCES, _INFO);
	return navi_gen(_FAVORITES, "favorites.png", "", $ops, $lang, "", "", $narg[0], $narg[1], $narg[2], $narg[3]);
}

function favorites() {
	head();
	echo favor_navi(0, 0, 0, 0).tpl_eval("open")."<div id=\"repfav_aliste\">".fav_aliste(1)."</div>".tpl_eval("close", "");
	foot();
}

function favor_conf() {
	global $admin_file;
	head();
	include("config/config_favorites.php");
	$cont = favor_navi(0, 1, 0, 0);
	$permtest = end_chmod("config/config_favorites.php", 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<form name=\"post\" action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_conf\">"
	."<tr><td>"._C_33.":</td><td><input type=\"number\" name=\"num\" value=\"".$conffav['num']."\" class=\"sl_conf\" placeholder=\""._C_33."\" required></td></tr>"
	."<tr><td>"._C_34.":</td><td><input type=\"number\" name=\"anum\" value=\"".$conffav['anum']."\" class=\"sl_conf\" placeholder=\""._C_34."\" required></td></tr>"
	."<tr><td>"._C_35.":</td><td><input type=\"number\" name=\"nump\" value=\"".$conffav['nump']."\" class=\"sl_conf\" placeholder=\""._C_35."\" required></td></tr>"
	."<tr><td>"._C_36.":</td><td><input type=\"number\" name=\"anump\" value=\"".$conffav['anump']."\" class=\"sl_conf\" placeholder=\""._C_36."\" required></td></tr>"
	."<tr><td>"._FAVOR_UMAX.":</td><td><input type=\"number\" name=\"favorites\" value=\"".$conffav['favorites']."\" class=\"sl_conf\" placeholder=\""._FAVOR_UMAX."\" required></td></tr>"
	."<tr><td>"._FAVOR_ACT."</td><td>".radio_form($conffav['favact'], "favact")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"favor_conf_save\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function favor_conf_save() {
	global $admin_file;
	$xnum = (!intval($_POST['num'])) ? 15 : $_POST['num'];
	$xanum = (!intval($_POST['anum'])) ? 15 : $_POST['anum'];
	$xnump = (!intval($_POST['nump'])) ? 5 : $_POST['nump'];
	$xanump = (!intval($_POST['anump'])) ? 5 : $_POST['anump'];
	$content = "\$conffav = array();\n"
	."\$conffav['num'] = \"".$xnum."\";\n"
	."\$conffav['anum'] = \"".$xanum."\";\n"
	."\$conffav['nump'] = \"".$xnump."\";\n"
	."\$conffav['anump'] = \"".$xanump."\";\n"
	."\$conffav['favorites'] = \"".$_POST['favorites']."\";\n"
	."\$conffav['favact'] = \"".$_POST['favact']."\";\n";
	save_conf("config/config_favorites.php", $content);
	header("Location: ".$admin_file.".php?op=favor_conf");
}

function favor_info() {
	head();
	echo favor_navi(0, 2, 0, 0)."<div id=\"repadm_info\">".adm_info(1, 0, "favorites")."</div>";
	foot();
}

switch($op) {
	case "favorites":
	favorites();
	break;
	
	case "favor_conf":
	favor_conf();
	break;
	
	case "favor_conf_save":
	favor_conf_save();
	break;
	
	case "favor_info":
	favor_info();
	break;
}
?>