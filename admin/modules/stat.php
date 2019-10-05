<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("ADMIN_FILE") || !is_admin_god()) die("Illegal file access");

function stat_navi() {
	global $admin_file;
	panel();
	$sfile = isset($_POST['file']) ? $_POST['file'] : 0;
	$narg = func_get_args();
	$ops = array("stat_show", "stat_conf", "stat_info");
	$lang = array(_HOME, _PREFERENCES, _INFO);
	$handle = opendir("config/counter/stat/");
	while (false !== ($file = readdir($handle))) $files[] = $file;
	closedir($handle);
	rsort($files);
	$search = "<form method=\"post\" action=\"".$admin_file.".php\">"._STATFROM.": <select name=\"file\"><option value=\"\">"._NO_INFO."</option>";
	foreach ($files as $val) {
		if ($val != "" && preg_match("/^stat\_(.+)\.txt/", $val, $matches)) {
			$sel = ($sfile && $sfile == $val) ? " selected" : "";
			$search .= "<option value=\"".$val."\"".$sel.">".$matches[1]."</option>";
		}
	}
	$search .= "</select> <input type=\"hidden\" name=\"op\" value=\"stat_show\"><input type=\"submit\" value=\""._OK."\" class=\"sl_but_blue\"></form>";
	$search = tpl_eval("searchbox", $search);
	return navi_gen(_STAT, "stat.png", $search, $ops, $lang, "", "", $narg[0], $narg[1], $narg[2], $narg[3]);
}

function stat_show() {
	global $prefix, $db, $admin_file;
	$file = isset($_POST['file']) ? $_POST['file'] : 0;
	head();
	$cont = stat_navi(0, 0, 0, 0);
	$permtest = end_chmod("config/counter", 777);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$permtest = end_chmod("config/counter/stat", 777);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<img src=\"".$admin_file.".php?op=create_stat&amp;file=".$file."&amp;day=15\" alt=\""._STATGR."\" title=\""._STATGR."\">";
	if ($file || date("d") > 15) {
		$out = ($file) ? count(file("config/counter/stat/".$file)) : date("d");
		$cont .= "<hr><img src=\"".$admin_file.".php?op=create_stat&amp;file=".$file."&amp;day=".$out."\" alt=\""._STATGR."\" title=\""._STATGR."\">";
	}
	$cont .= "<hr><table class=\"sl_table_list_sort\"><thead><tr><th>"._DATE."</th><th>"._UNIQUE."</th><th>"._HITS."</th><th>"._HOME."</th><th>"._REFERERS."</th><th>"._BOTSOPT."</th><th>"._AUDIENCE."</th><th class=\"{sorter: false}\">"._USERS."</th></tr></thead><tbody>";
	if ($file) {
		$f = file("config/counter/stat/".$file);
	} else {
		if (file_exists("config/counter/days.txt")) {
			$f = file("config/counter/days.txt");
			$f = array_merge($f, file("config/counter/stat.txt"));
		} else {
			$f = file("config/counter/stat.txt");
		}
	}
	$to = count($f);
	$unique = $today = $engines = $sites = $homepage = $auditory = $regusers = 0;
	for($i = 0; $i < $to; $i++) {
		$out = explode("|", $f[$i]);
		$unique += $out[1];
		$today += $out[2];
		$engines += $out[4];
		$sites += $out[5];
		$homepage += $out[6];
		$out_aud = $out[1] - ($out[4] + $out[5]);
		$auditory += $out_aud;
		if ($auditory < 0) $auditory = 0;
		$regusers += rtrim($out[7]);
		$cont .= "<tr><td>".$out[0]."</td><td>".$out[1]."</td><td>".$out[2]."</td><td>".$out[6]."</td><td>".$out[5]."</td><td>".$out[4]."</td><td>".$out_aud."</td><td>".rtrim($out[7])."</td></tr>";
	}
	$cont .= "<tr><th>"._ALL."</th><th>".$unique."</th><th>".$today."</th><th>".$homepage."</th><th>".$sites."</th><th>".$engines."</th><th>".$auditory."</th><th>".$regusers."</th></tr></tbody></table>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function stat_conf() {
	global $admin_file;
	head();
	$cont = stat_navi(0, 1, 0, 0);
	include("config/config_stat.php");
	$permtest = end_chmod("config/config_stat.php", 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_conf\">"
	."<tr><td>"._STATBET.":</td><td><input type=\"number\" name=\"bet\" value=\"".$confst['bet']."\" class=\"sl_conf\" placeholder=\""._STATBET."\" required></td></tr>"
	."<tr><td>"._STATSHI.":</td><td><input type=\"number\" name=\"shi\" value=\"".$confst['shi']."\" class=\"sl_conf\" placeholder=\""._STATSHI."\" required></td></tr>"
	."<tr><td>"._STATACT."</td><td>".radio_form($confst['stat'], "stat")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"stat_save\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function stat_save() {
	global $admin_file;
	$xbet = (!intval($_POST['bet'])) ? 35 : $_POST['bet'];
	$xshi = (!intval($_POST['shi'])) ? 15 : $_POST['shi'];
	$content = "\$confst = array();\n"
	."\$confst['bet'] = \"".$xbet."\";\n"
	."\$confst['shi'] = \"".$xshi."\";\n"
	."\$confst['stat'] = \"".$_POST['stat']."\";\n";
	save_conf("config/config_stat.php", $content);
	header("Location: ".$admin_file.".php?op=stat_conf");
}

function stat_info() {
	head();
	echo stat_navi(0, 2, 0, 0)."<div id=\"repadm_info\">".adm_info(1, 0, "stat")."</div>";
	foot();
}

switch($op) {
	case "stat_show":
	stat_show();
	break;
	
	case "create_stat":
	create_stat();
	break;
	
	case "stat_conf":
	stat_conf();
	break;
	
	case "stat_save":
	stat_save();
	break;
	
	case "stat_info":
	stat_info();
	break;
}
?>