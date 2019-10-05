<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("ADMIN_FILE") || !is_admin_god()) die("Illegal file access");

function rss_navi() {
	panel();
	$narg = func_get_args();
	$ops = ($narg[0] == 1) ? array("rss_conf", "rss_conf", "rss_info") : array("", "", "rss_info");
	$lang = array(_RSS, _PREFERENCES, _INFO);
	return navi_gen(_RSS, "rss.png", "", $ops, $lang, "", "", $narg[0], $narg[1], $narg[2], $narg[3], $narg[4]);
}

function rss_conf() {
	global $admin_file;
	head();
	$cont = rss_navi(0, 0, 0, 0, "rss");
	include("config/config_rss.php");
	$permtest = end_chmod("config/config_rss.php", 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$content = "";
	$fieldc = explode("||", $confrs['rss']);
	for ($c = 0; $c < 50; $c++) {
		preg_match("#(.*)\|(.*)\|(.*)#i", $fieldc[$c], $out);
		$field = "<select name=\"field3[]\" class=\"sl_conf\">";
		for ($i = 0; $i < 2; $i++) {
			$fieldname = ($i == 0) ? _RSSSITE : _RSSHOME;
			$sel = ($out[3] == $i) ? " selected" : "";
			$field .= "<option value=\"".$i."\"".$sel.">".$fieldname."</option>";
		}
		$field .= "</select>";
		$b = $c + 1;
		$display = (empty($out[1]) && empty($out[1][$c]) != "0") ? " class=\"sl_none\"" : "";
		$hr = ($c == "0") ? "" : "<hr>";
		$content .= "<div id=\"rss".$c."\"".$display.">".$hr
		."<table class=\"sl_table_conf\">"
		."<tr><td><a OnClick=\"HideShow('rss".$b."', 'slide', 'up', 500);\" title=\""._ADD."\" class=\"sl_plus\">"._RSSC.": ".$b."</a></td><td>"
		."<table><tr><td>"._NAME.":</td><td><input type=\"text\" name=\"field1[]\" value=\"".$out[1]."\" class=\"sl_conf\" placeholder=\""._NAME."\" required></td></tr>"
		."<tr><td>"._ADDRESS.":</td><td><input type=\"text\" name=\"field2[]\" value=\"".$out[2]."\" class=\"sl_conf\" placeholder=\""._ADDRESS."\" required></td></tr>"
		."<tr><td>"._USES.":</td><td>".$field."</td></tr></table>"
		."</td></tr></table></div>";
	}
	$cont .= tpl_warn("warn", _RSSDESC, "", "", "info");
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\">"
	."<div id=\"tabc0\" class=\"tabcont\">".$content."</div>"
	."<div id=\"tabc1\" class=\"tabcont\"><table class=\"sl_table_conf\">"
	."<tr><td>"._RSSMIN.":</td><td><input type=\"number\" name=\"min\" value=\"".$confrs['min']."\" class=\"sl_conf\" placeholder=\""._RSSMIN."\" required></td></tr>"
	."<tr><td>"._RSSMAX.":</td><td><input type=\"number\" name=\"max\" value=\"".$confrs['max']."\" class=\"sl_conf\" placeholder=\""._RSSMAX."\" required></td></tr>"
	."<tr><td>"._RSSTEMP.":<div class=\"sl_small\">"._RSSTEMPINFO."</div></td><td><textarea name=\"temp\" cols=\"65\" rows=\"5\" class=\"sl_conf\" placeholder=\""._RSSTEMP."\" required>".$confrs['temp']."</textarea></td></tr>"
	."<tr><td>"._RSSACT.":</td><td>".radio_form($confrs['act'], "act")."</td></tr>"
	."<tr><td>"._RSSUSE."</td><td>".radio_form($confrs['use'], "use")."</td></tr>"
	."</table></div>"
	."<table class=\"sl_table_conf\"><tr><td class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"rss_save_conf\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>"
	."<script>
		var countries=new ddtabcontent(\"rss\")
		countries.setpersist(true)
		countries.setselectedClassTarget(\"link\")
		countries.init()
	</script>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function rss_save_conf() {
	global $admin_file;
	include("config/config_rss.php");
	$xmin = (intval($_POST['min'])) ? $_POST['min'] : 10;
	$xmax = (intval($_POST['max'])) ? $_POST['max'] : 100;
	$content = "\$confrs = array();\n"
	."\$confrs['min'] = \"".$xmin."\";\n"
	."\$confrs['max'] = \"".$xmax."\";\n"
	."\$confrs['temp'] = <<<HTML\n".stripslashes($_POST['temp'])."\nHTML;\n"
	."\$confrs['act'] = \"".$_POST['act']."\";\n"
	."\$confrs['use'] = \"".$_POST['use']."\";\n";
	$rss = "";
	for ($i = 0; $i < 50; $i++) {
		$ident = ($i == 0) ? "" : "||";
		$field1 = ($_POST['field1'][$i] != "") ? $_POST['field1'][$i] : 0;
		$field2 = ($_POST['field2'][$i] != "") ? htmlspecialchars($_POST['field2'][$i]) : 0;
		$field3 = ($_POST['field3'][$i] != "") ? $_POST['field3'][$i] : 0;
		$rss .= $ident.$field1."|".$field2."|".$field3;
	}
	$content .= "\$confrs['rss'] = \"".$rss."\";\n";
	save_conf("config/config_rss.php", $content);
	header("Location: ".$admin_file.".php?op=rss_conf");
}

function rss_info() {
	head();
	echo rss_navi(1, 2, 0, 0, "")."<div id=\"repadm_info\">".adm_info(1, 0, "rss")."</div>";
	foot();
}

switch($op) {
	case "rss_conf":
	rss_conf();
	break;
	
	case "rss_save_conf":
	rss_save_conf();
	break;
	
	case "rss_info":
	rss_info();
	break;
}
?>