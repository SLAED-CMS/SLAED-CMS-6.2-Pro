<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("ADMIN_FILE") || !is_admin_god()) die("Illegal file access");

include("config/config_uploads.php");

function uploads_navi() {
	global $admin_file, $confup;
	panel();
	$dir = isset($_POST['dir']) ? analyze($_POST['dir']) : (isset($_GET['dir']) ? analyze($_GET['dir']) : $confup['dir']);
	$narg = func_get_args();
	$ops = array("uploads", "templ_conf", "uploads_conf", "uploads_info");
	$lang = array(_FILES, _TEMPLATES, _PREFERENCES, _INFO);
	$sops = ($narg[0] == 1) ? array("", "") : array("", "", "");
	$slang = ($narg[0] == 1) ? array(_GENPREF, _MODULES) : array(_EUPLOAD, "<span OnClick=\"AjaxLoad('GET', '1', 'f1', 'go=5&amp;op=ashow_files&amp;id=1&amp;dir=".$dir."', ''); return false;\">"._DGEN."</span>", "<span OnClick=\"AjaxLoad('GET', '1', 'f2', 'go=5&amp;op=ashow_files&amp;id=2&amp;dir=".$dir."', ''); return false;\">"._DTHUMB."</span>");
	$search = "<form method=\"post\" action=\"".$admin_file.".php\">"._DIR.": <select name=\"dir\" OnChange=\"submit()\">";
	$dh = opendir("uploads");
	while (($file = readdir($dh)) !== false) {
		if (!preg_match("/\./", $file)) {
			$sel = ($dir == $file) ? " selected" : "";
			$search .= "<option value=\"".$file."\"".$sel.">uploads/".$file."</option>";
		}
	}
	closedir($dh);
	$search .= "</select><input type=\"hidden\" name=\"op\" value=\"uploads\"></form>";
	$search = tpl_eval("searchbox", $search);
	return navi_gen(_UPLOADSEDIT, "uploads.png", $search, $ops, $lang, $sops, $slang, $narg[0], $narg[1], $narg[2], $narg[3], $narg[4]);
}

function uploads() {
	global $admin_file, $confup, $stop;
	$dir = isset($_POST['dir']) ? analyze($_POST['dir']) : (isset($_GET['dir']) ? analyze($_GET['dir']) : $confup['dir']);
	head();
	$cont = uploads_navi(0, 0, 1, 0, "uploads");
	if ($stop) $cont .= tpl_warn("warn", $stop, "", "", "warn");
	$permtest = end_chmod("uploads/", 777);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= "<div id=\"tabcs0\" class=\"tabcont\">";
	$cont .= tpl_warn("warn", _MODUL.": ".deflmconst($dir)."<br>"._DIR.": uploads/".$dir, "", "", "info");
	$cont .= tpl_eval("open");
	$cont .= "<form enctype=\"multipart/form-data\" action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_form\">"
	."<tr><td>"._FILE_USER.":</td><td><input type=\"file\" name=\"userfile\" class=\"sl_form\"></td></tr>"
	."<tr><td>"._FILE_SITE.":</td><td><input type=\"text\" name=\"sitefile\" class=\"sl_form\" placeholder=\""._FILE_SITE."\"></td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"uploads_save\"><input type=\"hidden\" name=\"dir\" value=\"".$dir."\"><input type=\"submit\" value=\""._EXECUTE."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	$cont .= "</div>"
	."<div id=\"tabcs1\" class=\"tabcont\">";
	$fdir = "uploads/".$dir;
	$permtest = end_chmod($fdir, 777);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	if (is_dir($fdir)) {
		$f = 0;
		$affilesize = 0;
		$dh = opendir($fdir);
		while (($file = readdir($dh)) !== false) {
			if ($file != "." && $file != ".." && $file != "index.html" && !is_dir($fdir."/".$file)) {
				$filesize = filesize($fdir."/".$file);
				$f++;
				$affilesize += $filesize;
			}
		}
		closedir($dh);
		$cont .= tpl_warn("warn", _MODUL.": ".deflmconst($dir)."<br>"._DIR.": ".$fdir."<br>"._FILE_M.": ".$f."<br>"._FILE_S.": ".files_size($affilesize), "", "", "info");
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	$cont .= tpl_eval("open")."<div id=\"repf1\"></div>".tpl_eval("close", "");
	$cont .= "</div>"
	."<div id=\"tabcs2\" class=\"tabcont\">";
	$tdir = "uploads/".$dir."/thumb";
	$permtest = end_chmod($tdir, 777);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	if (is_dir($tdir)) {
		$t = 0;
		$atfilesize = 0;
		$dh = opendir($tdir);
		while (($file = readdir($dh)) !== false) {
			if ($file != "." && $file != ".." && $file != "index.html" && !is_dir($tdir."/".$file)) {
				$filesize = filesize($tdir."/".$file);
				$t++;
				$atfilesize += $filesize;
			}
		}
		closedir($dh);
		$cont .= tpl_warn("warn", _MODUL.": ".deflmconst($dir)."<br>"._DIR.": ".$tdir."<br>"._FILE_M.": ".$t."<br>"._FILE_S.": ".files_size($atfilesize), "", "", "info");
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	$cont .= tpl_eval("open")."<div id=\"repf2\"></div>".tpl_eval("close", "");
	$cont .= "</div>"
	."<script>
		var countries=new ddtabcontent(\"uploadss\")
		countries.setpersist(true)
		countries.setselectedClassTarget(\"link\")
		countries.init()
	</script>";
	echo $cont;
	foot();
}

function templ_conf() {
	global $admin_file, $confup;
	head();
	$cont = uploads_navi(0, 1, 0, 0, "");
	$cont .= tpl_warn("warn", _TPINFO, "", "", "info");
	include("config/config_templ.php");
	$permtest = end_chmod("config/config_templ.php", 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$typm = explode(",", $confup['typ']);
	$conts = "";
	for ($i = 0; $i < count($typm); $i++) {
		$hr = ($i == "0") ? "" : "<hr>";
		$conts .= $hr."<table class=\"sl_table_edit\"><tr><td><h5>"._TPFOR.": ".$typm[$i]."</h5></td></tr><tr><td>".textarea_code("code_".$i."", "tmp[]", "sl_form", "text/html", $conftp[$typm[$i]])."</td></tr></table>";
	}
	$cont .= tpl_eval("open")."<form action=\"".$admin_file.".php\" method=\"post\">".$conts."<table class=\"sl_table_conf\"><tr><td class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"templ_save_conf\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>".tpl_eval("close", "");
	echo $cont;
	foot();
}

function templ_save_conf() {
	global $admin_file, $confup;
	$content = "\$conftp = array();\n";
	$typm = explode(",", $confup['typ']);
	for ($i = 0; $i < count($typm); $i++) $content .= "\$conftp['".$typm[$i]."'] = <<<HTML\n".stripslashes($_POST['tmp'][$i])."\nHTML;\n";
	save_conf("config/config_templ.php", $content);
	header("Location: ".$admin_file.".php?op=templ_conf");
}

function uploads_conf() {
	global $admin_file, $confup;
	head();
	$cont = uploads_navi(1, 2, 1, 0, "uploads_conf");
	$permtest = end_chmod("config/config_uploads.php", 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$dh = opendir("uploads");
	$directory = "";
	while (($file = readdir($dh)) !== false) {
		if (!preg_match("/\./", $file)) {
			$sel = ($confup['dir'] == $file) ? " selected" : "";
			$directory .= "<option value=\"".$file."\"".$sel.">uploads/".$file."</option>";
		}
	}
	closedir($dh);
	$conts = "<form action=\"".$admin_file.".php\" method=\"post\">"
	."<div id=\"tabcs0\" class=\"tabcont\">"
	."<table class=\"sl_table_conf\">"
	."<tr><td>"._DIRDEF.":</td><td><select name=\"dir\" class=\"sl_conf\">".$directory."</select></td></tr>"
	."<tr><td>"._TPFORM.":<div class=\"sl_small\">"._TPFORMIN."</div></td><td><textarea name=\"ttyp\" cols=\"65\" rows=\"5\" class=\"sl_conf\" placeholder=\""._TPFORM."\" required>".$confup['typ']."</textarea></td></tr>"
	."<tr><td>"._TPWIDTH.":</td><td><input type=\"number\" name=\"twidth\" value=\"".$confup['width']."\" class=\"sl_conf\" placeholder=\""._TPWIDTH."\" required></td></tr>"
	."<tr><td>"._TPHEIGHT.":</td><td><input type=\"number\" name=\"theight\" value=\"".$confup['height']."\" class=\"sl_conf\" placeholder=\""._TPHEIGHT."\" required></td></tr></table>"
	."</div>"
	."<div id=\"tabcs1\" class=\"tabcont\">";
	$mods = array("all", "account", "album", "auto_links", "content", "faq", "files", "forum", "help", "info", "links", "media", "news", "pages", "shop", "voting");
	$i = 0;
	foreach ($mods as $val) {
		if ($val != "") {
			$con = explode("|", $confup[$val]);
			$hr = ($i == "0") ? "" : "<hr>";
			$conts .= $hr."<table class=\"sl_table_conf\">"
			."<tr><td>"._MODUL.":</td><td>".deflmconst($val)."</td></tr>"
			."<tr><td>"._FTYPE.":</td><td><input type=\"text\" name=\"type[]\" value=\"".$con[0]."\" class=\"sl_conf\" placeholder=\""._FTYPE."\" required></td></tr>"
			."<tr><td>"._FSIZEALL._FIN.":</td><td><input type=\"number\" name=\"allsize[]\" value=\"".$con[1]."\" class=\"sl_conf\" placeholder=\""._FSIZEALL._FIN."\" required></td></tr>"
			."<tr><td>"._FSIZE._FIN.":</td><td><input type=\"number\" name=\"size[]\" value=\"".$con[2]."\" class=\"sl_conf\" placeholder=\""._FSIZE._FIN."\" required></td></tr>"
			."<tr><td>"._AWIDTH._AIN.":</td><td><input type=\"number\" name=\"width[]\" value=\"".$con[3]."\" class=\"sl_conf\" placeholder=\""._AWIDTH._AIN."\" required></td></tr>"
			."<tr><td>"._AHEIGHT._AIN.":</td><td><input type=\"number\" name=\"height[]\" value=\"".$con[4]."\" class=\"sl_conf\" placeholder=\""._AHEIGHT._AIN."\" required></td></tr>"
			."<tr><td>"._FILEUP.":</td><td><input type=\"number\" name=\"up[]\" value=\"".$con[5]."\" class=\"sl_conf\" placeholder=\""._FILEUP."\" required></td></tr>"
			."<tr><td>"._GDWIDTH.":</td><td><input type=\"number\" name=\"gdwidth[]\" value=\"".$con[6]."\" class=\"sl_conf\" placeholder=\""._GDWIDTH."\" required></td></tr>"
			."<tr><td>"._F_5.":</td><td><input type=\"number\" name=\"num[]\" value=\"".$con[7]."\" class=\"sl_conf\" placeholder=\""._F_5."\" required></td></tr>"
			."<tr><td>"._EDFILEA.":<div class=\"sl_small\">"._CONFINES."</div></td><td><input type=\"number\" name=\"asum[]\" value=\"".$con[8]."\" class=\"sl_conf\" placeholder=\""._EDFILEA."\" required></td></tr>"
			."<tr><td>"._EDFILEU.":<div class=\"sl_small\">"._CONFINES."</div></td><td><input type=\"number\" name=\"usum[]\" value=\"".$con[9]."\" class=\"sl_conf\" placeholder=\""._EDFILEU."\" required></td></tr>"
			."<tr><td>"._F_8."</td><td>".radio_form($con[10], $i."upload")."</td></tr>"
			."<tr><td>"._F_9."</td><td>".radio_form($con[11], $i."upguest")."</td></tr></table>";
			$i++;
		}
	}
	$conts .= "</div>";
	$cont .= tpl_eval("open");
	$cont .= $conts
	."<script>
		var countries=new ddtabcontent(\"uploads_confs\")
		countries.setpersist(true)
		countries.setselectedClassTarget(\"link\")
		countries.init()
	</script>"
	."<table class=\"sl_table_conf\"><tr><td class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"uploads_save_conf\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function uploads_save_conf() {
	global $admin_file, $confup;
	$protect = array("\n" => "", "\t" => "", "\r" => "", " " => "");
	$xttyp = (!$_POST['ttyp']) ? "gif,jpg,jpeg,png,bmp" : strtolower(strtr($_POST['ttyp'], $protect));
	$xtwidth = (!intval($_POST['twidth'])) ? 500 : $_POST['twidth'];
	$xtheight = (!intval($_POST['theight'])) ? 500 : $_POST['theight'];
	$content = "\$confup = array();\n"
	."\$confup['dir'] = \"".$_POST['dir']."\";\n"
	."\$confup['typ'] = \"".$xttyp."\";\n"
	."\$confup['width'] = \"".$xtwidth."\";\n"
	."\$confup['height'] = \"".$xtheight."\";\n";
	$mods = array("all", "account", "album", "auto_links", "content", "faq", "files", "forum", "help", "info", "links", "media", "news", "pages", "shop", "voting");
	$i = 0;
	foreach ($mods as $val) {
		if ($val != "") {
			$xtype = (!$_POST['type'][$i]) ? "gif,jpg,jpeg,png,zip,rar" : strtolower(strtr($_POST['type'][$i], $protect));
			$xallsize = (!intval($_POST['allsize'][$i])) ? 104857600 : $_POST['allsize'][$i];
			$xsize = (!intval($_POST['size'][$i])) ? 1048576 : $_POST['size'][$i];
			$xwidth = (!intval($_POST['width'][$i])) ? 500 : $_POST['width'][$i];
			$xheight = (!intval($_POST['height'][$i])) ? 500 : $_POST['height'][$i];
			$xup = (!intval($_POST['up'][$i])) ? 10 : $_POST['up'][$i];
			$xgdwidth = (!intval($_POST['gdwidth'][$i])) ? 150 : $_POST['gdwidth'][$i];
			$xnum = (!intval($_POST['num'][$i])) ? 10 : $_POST['num'][$i];
			$xasum = (!intval($_POST['asum'][$i])) ? 250 : $_POST['asum'][$i];
			$xusum =(!intval($_POST['usum'][$i])) ? 100 : $_POST['usum'][$i];
			$content .= "\$confup['".$val."'] = \"".$xtype."|".$xallsize."|".$xsize."|".$xwidth."|".$xheight."|".$xup."|".$xgdwidth."|".$xnum."|".$xasum."|".$xusum."|".$_POST[$i.'upload']."|".$_POST[$i.'upguest']."\";\n";
			$i++;
		}
	}
	save_conf("config/config_uploads.php", $content);
	header("Location: ".$admin_file.".php?op=uploads_conf");
}

function uploads_info() {
	head();
	echo uploads_navi(0, 3, 0, 0, "")."<div id=\"repadm_info\">".adm_info(1, 0, "uploads")."</div>";
	foot();
}

switch($op) {
	case "uploads":
	uploads();
	break;
	
	case "uploads_save":
	$sdir = analyze($_POST['dir']);
	upload(3, "uploads/".$sdir, "gif,jpg,jpeg,png,zip,rar", "104857600", $sdir, "1600", "1600", '1');
	if ($stop) {
		uploads();
	} else {
		header("Location: ".$admin_file.".php?op=uploads&dir=".$sdir);
	}
	break;
	
	case "templ_conf":
	templ_conf();
	break;
	
	case "templ_save_conf":
	templ_save_conf();
	break;
	
	case "uploads_conf":
	uploads_conf();
	break;
	
	case "uploads_save_conf":
	uploads_save_conf();
	break;
	
	case "uploads_info":
	uploads_info();
	break;
}
?>