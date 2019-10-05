<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("ADMIN_FILE") || !is_admin_god()) die("Illegal file access");

function template_navi() {
	global $admin_file, $conf;
	panel();
	$templ = isset($_POST['templ']) ? analyze($_POST['templ']) : (isset($_GET['templ']) ? analyze($_GET['templ']) : $conf['theme']);
	$narg = func_get_args();
	$ops = array("template&amp;templ=".$templ, "template_style&amp;templ=".$templ, "template_info");
	$lang = array(_TEMPLATES, _STYLES, _INFO);
	$search = "<form method=\"post\" action=\"".$admin_file.".php\">"._THEME.": <select name=\"templ\">";
	$dir = opendir("templates");
	while (($file = readdir($dir)) !== false) {
		if (!preg_match("/\./", $file)) {
			$selected = ($file == $templ) ? " selected" : "";
			$search .= "<option value=\"".$file."\"".$selected.">".$file."</option>";
		}
	}
	closedir($dir);
	$search .= "</select> <input type=\"hidden\" name=\"op\" value=\"template\"><input type=\"submit\" value=\""._OK."\" class=\"sl_but_blue\"></form>";
	$search = tpl_eval("searchbox", $search);
	return navi_gen(_THEME, "template.png", $search, $ops, $lang, "", "", $narg[0], $narg[1], $narg[2], $narg[3]);
}

function template() {
	global $admin_file, $conf;
	$templ = isset($_POST['templ']) ? analyze($_POST['templ']) : (isset($_GET['templ']) ? analyze($_GET['templ']) : $conf['theme']);
	head();
	$cont = template_navi(0, 0, 0, 0);
	$dir = "templates/".$templ;
	if (is_dir($dir)) {
		$langs = array(".html" => "", "assoc" => _ASSOTOPIC, "all" => _ALL, "admin" => _ADMIN, "basic" => _CONTENT, "block" => _BLOCK, "bottom" => _BOTTOM, "categories" => _CATEGORIES, "cat" => _CATEGORIES, "center" => _CENTER, "code" => _CODE, "comment" => _COMMENTS, "change" => _CHANGE, "index" => _INDEX, "img" => _IMG, "hide" => _HIDE, "home" => _HOME, "listing" => _LISTING, "list" => _LISTING, "login" => _INPUT, "logged" => _LOGGED, "kasse" => _PBASKET, "messagebox" => _TMESS, "message" => _MESSAGE, "modul" => _MODUL, "navi" => _NAVI, "pagenum" => _PAGENUM, "panel" => _ADMINMENU, "post" => _SEND, "prcenter" => _CENTERDOWN, "prints" => _PRINTS, "privat" => _PRIVAT, "close" => _TCLOSE, "open" => _TOPEN, "title" => _TTITLE, "warn" => _TWARNING, "preview" => _PREVIEW, "view" => _MVIEW, "left" => _LEFT, "right" => _RIGHT, "down" => _CENTERDOWN, "info" => _INFO, "spoiler" => _SPOILER, "quote" => _QUOTE, "without" => _LOGINL, "-" => " &raquo; ");
		$i = 0;
		$conts = "";
		$dh = opendir($dir);
		while (($file = readdir($dh)) !== false) {
			if (strpos($file, ".html")) {
				$filelink = $dir."/".$file;
				$permtest = end_chmod($filelink, 666);
				if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
				$comp = deflmconst(strtr($file, $langs));
				$conts .= "<table class=\"sl_bodyline\"><tr><th class=\"sl_right\"><a OnClick=\"CloseOpen('sl_open_".$i."', 0);\" title=\""._EDIT."\" class=\"sl_plus\">".$comp." | "._FILE.": ".$file." | ".date(_TIMESTRING, filemtime($filelink))."</a></th></tr></table>"
				."<div id=\"sl_open_".$i."\"><form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_blockline\"><tr><td>".textarea_code("code_".$i."", "template", "sl_form", "text/html", file_get_contents($filelink))."</td></tr>"
				."<tr><td class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"template_save\"><input type=\"hidden\" name=\"templ\" value=\"".$templ."\"><input type=\"hidden\" name=\"filelink\" value=\"".$filelink."\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form></div>";
				$i++;
			}
		}
		closedir($dh);
		$cont .= tpl_eval("open").$conts.tpl_eval("close", "");
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function template_style() {
	global $admin_file, $conf;
	$templ = isset($_GET['templ']) ? $_GET['templ'] : $conf['theme'];
	head();
	$cont = template_navi(0, 1, 0, 0);
	$dir = is_dir("templates/".$templ."/css") ? "templates/".$templ."/css" : "templates/".$templ;
	if (is_dir($dir)) {
		$langs = array(".css" => "", "all" => _ALL, "basic" => _CONTENT, "blocks" => _BLOCKS, "calendar" => _CALENDAR, "index" => _INDEX, "home" => _HOME, "styles" => _STYLES, "style" => _STYLE, "system" => _SYSTEM, "engine" => _SYSTEM, "theme" => _THEME, "main" => _GENPREF, "-" => " &raquo; ");
		$i = 0;
		$conts = "";
		$dh = opendir($dir);
		while (($file = readdir($dh)) !== false) {
			if (strpos($file, ".css")) {
				$filelink = $dir."/".$file;
				$permtest = end_chmod($filelink, 666);
				if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
				$comp = deflmconst(strtr($file, $langs));
				$conts .= "<table class=\"sl_bodyline\"><tr><th class=\"sl_right\"><a OnClick=\"CloseOpen('sl_open_".$i."', 0);\" title=\""._EDIT."\" class=\"sl_plus\">".$comp." | "._FILE.": ".$file." | ".date(_TIMESTRING, filemtime($filelink))."</a></th></tr></table>"
				."<div id=\"sl_open_".$i."\"><form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_blockline\"><tr><td>".textarea_code("code_".$i."", "template", "sl_form", "text/css", file_get_contents($filelink))."</td></tr>"
				."<tr><td class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"template_style_save\"><input type=\"hidden\" name=\"templ\" value=\"".$templ."\"><input type=\"hidden\" name=\"filelink\" value=\"".$filelink."\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form></div>";
				$i++;
			}
		}
		closedir($dh);
		$cont .= tpl_eval("open").$conts.tpl_eval("close", "");
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function template_info() {
	head();
	echo template_navi(0, 2, 0, 0)."<div id=\"repadm_info\">".adm_info(1, 0, "template")."</div>";
	foot();
}

switch($op) {
	case "template":
	template();
	break;
	
	case "template_save":
	$templ = isset($_POST['templ']) ? "&templ=".$_POST['templ'] : "";
	$filelink = $_POST['filelink'];
	$template = stripslashes($_POST['template']);
	if ($filelink && $template) {
		$dh = fopen($filelink, "wb");
		fwrite($dh, $template);
		fclose($dh);
	}
	header("Location: ".$admin_file.".php?op=template".$templ);
	break;
	
	case "template_style":
	template_style();
	break;
	
	case "template_style_save":
	$templ = isset($_POST['templ']) ? "&templ=".$_POST['templ'] : "";
	$filelink = $_POST['filelink'];
	$template = stripslashes($_POST['template']);
	if ($filelink && $template) {
		$dh = fopen($filelink, "wb");
		fwrite($dh, $template);
		fclose($dh);
	}
	header("Location: ".$admin_file.".php?op=template_style".$templ);
	break;
	
	case "template_info":
	template_info();
	break;
}
?>