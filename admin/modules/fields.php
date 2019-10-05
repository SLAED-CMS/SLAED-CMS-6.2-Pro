<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("ADMIN_FILE") || !is_admin_god()) die("Illegal file access");

function fields_navi() {
	panel();
	$narg = func_get_args();
	$ops = ($narg[0] == 1) ? array("fields", "fields", "fields", "fields", "fields", "fields", "fields_info") : array("", "", "", "", "", "", "fields_info");
	$lang = array(_ACCOUNT, _CONTENT, _FORUM, _HELP, _NEWS, _ORDER, _INFO);
	return navi_gen(_FIELDS, "fields.png", "", $ops, $lang, "", "", $narg[0], $narg[1], $narg[2], $narg[3], $narg[4]);
}

function fields() {
	global $admin_file;
	head();
	include("config/config_fields.php");
	$cont = fields_navi(0, 0, 0, 0, "fields");
	$permtest = end_chmod("config/config_fields.php", 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$mods = array("account", "content", "forum", "help", "news", "order");
	$content = "";
	$k = 0;
	foreach ($mods as $val) {
		if ($val != "") {
			$fieldc = explode("||", $conffi[$val]);
			$content .= "<div id=\"tabc".$k."\" class=\"tabcont\">";
			for ($c = 0; $c < 10; $c++) {
				preg_match("#(.*)\|(.*)\|(.*)\|(.*)#i", $fieldc[$c], $out);
				$field = "<select name=\"field3".$k."[]\" class=\"sl_conf\">";
				$fieldname = array(_FIELDINPUT, _FIELDAREA, _FIELDSELECT, _FIELDTIME, _FIELDDATE);
				foreach ($fieldname as $key => $val) {
					$i = $key + 1;
					$sel = ($out[3] == $i) ? " selected" : "";
					$field .= "<option value=\"".$i."\"".$sel.">".$val."</option>";
				}
				$field .= "</select>";
				$field2 = "<select name=\"field4".$k."[]\" class=\"sl_conf\">";
				$fieldname2 = array(_FIELDIN, _FIELDOUT);
				foreach ($fieldname2 as $key => $val) {
					$a = $key + 1;
					$sel2 = ($out[4] == $a) ? " selected" : "";
					$field2 .= "<option value=\"".$a."\"".$sel2.">".$val."</option>";
				}
				$field2 .= "</select>";
				$b = $c + 1;
				$display = (empty($out[1]) && empty($out[1][$c]) != "0") ? " class=\"sl_none\"" : "";
				$hr = ($c == "0") ? "" : "<hr>";
				$content .= "<div id=\"fi".$k.$c."\"".$display.">".$hr
				."<table class=\"sl_table_conf\">"
				."<tr><td><a OnClick=\"HideShow('fi".$k.$b."', 'slide', 'up', 500);\" title=\""._ADD."\" class=\"sl_plus\">"._FIELD.": ".$b."</a></td><td>"
				."<table><tr><td>"._NAME.":</td><td><input type=\"text\" name=\"field1".$k."[]\" value=\"".$out[1]."\" class=\"sl_conf\" placeholder=\""._NAME."\" required></td></tr>"
				."<tr><td>"._CONTENT.":</td><td><input type=\"text\" name=\"field2".$k."[]\" value=\"".$out[2]."\" class=\"sl_conf\" placeholder=\""._CONTENT."\" required></td></tr>"
				."<tr><td>"._TYPE.":</td><td>".$field."</td></tr>"
				."<tr><td>"._USES.":</td><td>".$field2."</td></tr></table>"
				."</td></tr></table></div>";
			}
			$content .= "</div>";
			$k++;
		}
	}
	$cont .= tpl_warn("warn", _FIELDINFO, "", "", "info");
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\">".$content."<table class=\"sl_table_conf\"><tr><td class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"fields_save_conf\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>"
	."<script>
		var countries=new ddtabcontent(\"fields\")
		countries.setpersist(true)
		countries.setselectedClassTarget(\"link\")
		countries.init()
	</script>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function fields_save_conf() {
	global $admin_file;
	include("config/config_fields.php");
	$content = "\$conffi = array();\n";
	$mods = array("account", "content", "forum", "help", "news", "order");
	$a = 0;
	foreach ($mods as $val) {
		if ($val != "") {
			$fields = "";
			for ($i = 0; $i < 10; $i++) {
				$ident = ($i == 0) ? "" : "||";
				$field1 = ($_POST['field1'.$a][$i] != "") ? $_POST['field1'.$a][$i] : 0;
				$field2 = ($_POST['field2'.$a][$i] != "") ? $_POST['field2'.$a][$i] : 0;
				$field3 = ($_POST['field3'.$a][$i] != "") ? $_POST['field3'.$a][$i] : 0;
				$field4 = ($_POST['field4'.$a][$i] != "") ? $_POST['field4'.$a][$i] : 0;
				$fields .= $ident.$field1."|".$field2."|".$field3."|".$field4;
			}
			$a++;
			$content .= "\$conffi['".$val."'] = \"".$fields."\";\n";
		}
	}
	save_conf("config/config_fields.php", $content);
	header("Location: ".$admin_file.".php?op=fields");
}

function fields_info() {
	head();
	echo fields_navi(1, 6, 0, 0, "")."<div id=\"repadm_info\">".adm_info(1, 0, "fields")."</div>";
	foot();
}

switch($op) {
	case "fields":
	fields();
	break;
	
	case "fields_save_conf":
	fields_save_conf();
	break;
	
	case "fields_info":
	fields_info();
	break;
}
?>