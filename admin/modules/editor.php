<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("ADMIN_FILE") || !is_admin_god()) die("Illegal file access");

function editor_navi() {
	panel();
	$narg = func_get_args();
	$ops = array("editor_function", "editor_header", "editor_rewrite", "editor_htaccess", "editor_robots", "editor_info");
	$lang = array(_EFUNCN, _EHEADN, _EREWN, _EHTN, _ERON, _INFO);
	return navi_gen(_EDITOR_IN, "editor.png", "", $ops, $lang, "", "", $narg[0], $narg[1], $narg[2], $narg[3]);
}

function editor_function() {
	global $admin_file;
	head();
	$cont = editor_navi(0, 0, 0, 0);
	$file = "config/config_function.php";
	$conts = trim(str_replace(array("<?php", "if (!defined(\"FUNC_FILE\")) die(\"Illegal file access\");", "?>"), "", file_get_contents($file)));
	$permtest = end_chmod($file, 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_warn("warn", _EFUNC.": ".$file." "._EINFO, "", "", "info");
	$cont .= tpl_warn("warn",  _EINFOPHP, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_edit\"><tr><td>".textarea_code("code", "template", "sl_form", "text/x-php", $conts)."</td></tr>"
	."<tr><td class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"editor_save\"><input type=\"hidden\" name=\"editor\" value=\"editor_function\"><input type=\"hidden\" name=\"file\" value=\"".$file."\"><input type=\"submit\" value=\""._SAVE."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function editor_header() {
	global $admin_file;
	head();
	$cont = editor_navi(0, 1, 0, 0);
	$file = "config/config_header.php";
	$conts = trim(str_replace(array("<?php", "if (!defined(\"FUNC_FILE\")) die(\"Illegal file access\");", "?>"), "", file_get_contents($file)));
	$permtest = end_chmod($file, 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_warn("warn", _EHEAD.": ".$file." "._EINFO2, "", "", "info");
	$cont .= tpl_warn("warn",  _EINFOPHP, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_edit\"><tr><td>".textarea_code("code", "template", "sl_form", "text/x-php", $conts)."</td></tr>"
	."<tr><td class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"editor_save\"><input type=\"hidden\" name=\"editor\" value=\"editor_header\"><input type=\"hidden\" name=\"file\" value=\"".$file."\"><input type=\"submit\" value=\""._SAVE."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function editor_rewrite() {
	global $admin_file;
	head();
	$cont = editor_navi(0, 2, 0, 0);
	$file = "config/config_rewrite.php";
	$conts = trim(str_replace(array("<?php", "if (!defined(\"FUNC_FILE\")) die(\"Illegal file access\");", "?>"), "", file_get_contents($file)));
	$permtest = end_chmod($file, 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_warn("warn", _EREW.": ".$file." "._EINFO3, "", "", "info");
	$cont .= tpl_warn("warn",  _EINFOPHP, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_edit\"><tr><td>".textarea_code("code", "template", "sl_form", "text/x-php", $conts)."</td></tr>"
	."<tr><td class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"editor_save\"><input type=\"hidden\" name=\"editor\" value=\"editor_rewrite\"><input type=\"hidden\" name=\"file\" value=\"".$file."\"><input type=\"submit\" value=\""._SAVE."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function editor_htaccess() {
	global $admin_file;
	head();
	$cont = editor_navi(0, 3, 0, 0);
	$file = ".htaccess";
	$conts = file_get_contents($file);
	$permtest = end_chmod($file, 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_warn("warn", _EHT.": ".$file." "._EINFO4, "", "", "info");
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_edit\"><tr><td>".textarea_code("code", "template", "sl_form", "text/x-php", $conts)."</td></tr>"
	."<tr><td class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"editor_save\"><input type=\"hidden\" name=\"editor\" value=\"editor_htaccess\"><input type=\"hidden\" name=\"file\" value=\"".$file."\"><input type=\"submit\" value=\""._SAVE."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function editor_robots() {
	global $admin_file;
	head();
	$cont = editor_navi(0, 4, 0, 0);
	$file = "robots.txt";
	$conts = file_get_contents($file);
	$permtest = end_chmod($file, 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_warn("warn", _EROB.": ".$file." "._EINFO5, "", "", "info");
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_edit\"><tr><td>".textarea_code("code", "template", "sl_form", "message/http", $conts)."</td></tr>"
	."<tr><td class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"editor_save\"><input type=\"hidden\" name=\"editor\" value=\"editor_robots\"><input type=\"hidden\" name=\"file\" value=\"".$file."\"><input type=\"submit\" value=\""._SAVE."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function editor_info() {
	head();
	echo editor_navi(1, 5, 0, 0)."<div id=\"repadm_info\">".adm_info(1, 0, "editor")."</div>";
	foot();
}

switch($op) {
	case "editor_function":
	editor_function();
	break;
	
	case "editor_header":
	editor_header();
	break;
	
	case "editor_rewrite":
	editor_rewrite();
	break;
	
	case "editor_htaccess":
	editor_htaccess();
	break;
	
	case "editor_robots":
	editor_robots();
	break;
	
	case "editor_save":
	$editor = $_POST['editor'];
	$file = $_POST['file'];
	$type = array(".htaccess", "robots.txt");
	$template = (in_array($file, $type)) ? stripslashes($_POST['template']) : "<?php\r\nif (!defined(\"FUNC_FILE\")) die(\"Illegal file access\");\r\n".stripslashes($_POST['template'])."\r\n?>";
	if ($file && $template) {
		$handle = fopen($file, "wb");
		fwrite($handle, $template);
		fclose($handle);
	}
	header("Location: ".$admin_file.".php?op=".$editor);
	break;
	
	case "editor_info":
	editor_info();
	break;
}
?>