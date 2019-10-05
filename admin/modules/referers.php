<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("ADMIN_FILE") || !is_admin_god()) die("Illegal file access");

function referers_navi() {
	global $admin_file;
	panel();
	$narg = func_get_args();
	$ops = array("referers", "referers_conf", "referers_del", "referers_info");
	$lang = array(_HOME, _PREFERENCES, _DELETE, _INFO);
	$search = "<form method=\"post\" action=\"".$admin_file.".php\">"._SORTE.": <select name=\"sort\">";
	$priv = array(_REF_ID, _REF_URL, _IN_ID, _IN_URL, _NAME_ID, _NAME_REF, _IP_ID, _IP_REF, _TIME_ID, _TIME_REF);
	$psort = isset($_POST['sort']) ? $_POST['sort'] : "";
	$porder = isset($_POST['order']) ? $_POST['order'] : "";
	foreach ($priv as $key => $value) {
		$sort = $key + 1;
		$sel = ($psort == $sort) ? " selected" : "";
		$search .= "<option value=\"".$sort."\"".$sel.">".$value."</option>";
	}
	$search .= "</select> <select name=\"order\">";
	$privs = array(_ASC, _DESC);
	foreach ($privs as $key => $value) {
		$sort = $key + 1;
		$sel = ($porder == $sort) ? " selected" : "";
		$search .= "<option value=\"".$sort."\"".$sel.">".$value."</option>";
	}
	$search .= "</select> <input type=\"hidden\" name=\"op\" value=\"referers\"><input type=\"submit\" value=\""._OK."\" class=\"sl_but_blue\"></form>";
	$search = tpl_eval("searchbox", $search);
	return navi_gen(_REFERERS, "referers.png", $search, $ops, $lang, "", "", $narg[0], $narg[1], $narg[2], $narg[3]);
}

function referers() {
	global $prefix, $db, $admin_file, $confr;
	$sort = isset($_POST['sort']) ? intval($_POST['sort']) : (isset($_GET['sort']) ? intval($_GET['sort']) : "");
	$order = isset($_POST['order']) ? intval($_POST['order']) : (isset($_GET['order']) ? intval($_GET['order']) : "");
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num - 1) * $confr['anum'];
	$tnum = ($offset) ? $confr['anum'] + $offset : $confr['anum'];
	if ($sort == 1) {
		$count = "referer";
		$ordby = "hits";
	} elseif ($sort == 2) {
		$count = "referer";
		$ordby = "referer";
	} elseif ($sort == 3) {
		$count = "link";
		$ordby = "hits";
	} elseif ($sort == 4) {
		$count = "link";
		$ordby = "link";
	} elseif ($sort == 5) {
		$count = "name";
		$ordby = "hits";
	} elseif ($sort == 6) {
		$count = "name";
		$ordby = "name";
	} elseif ($sort == 7) {
		$count = "ip";
		$ordby = "hits";
	} elseif ($sort == 8) {
		$count = "ip";
		$ordby = "ip";
	} elseif ($sort == 9) {
		$count = "date";
		$ordby = "hits";
	} else {
		$count = "date";
		$ordby = "date";
	}
	$ordsc = ($order == 1) ? "ASC" : "DESC";
	$result = $db->sql_query("SELECT Count(".$count.") AS hits, uid, name, ip, referer, link, date FROM ".$prefix."_referer GROUP BY ".$count." ORDER BY ".$ordby." ".$ordsc);
	head();
	$cont = referers_navi(0, 0, 0, 0);
	if ($db->sql_numrows($result) > 0) {
		$cont .= tpl_eval("open");
		while (list($hits, $uid, $name, $ip, $referer, $link, $date)= $db->sql_fetchrow($result)) {
			$massiv[] = array($hits, $uid, $name, $ip, $referer, $link, $date);
			$a++;
		}
		$cont .= "<table class=\"sl_table_list_sort\"><thead><tr><th>"._IP."</th><th>"._HITS."</th><th>"._REFERERS."</th><th>"._SWORD."</th><th class=\"{sorter: false}\">"._ID."</th></tr></thead><tbody>";
		for ($i = $offset; $i < $tnum; $i++) {
			if ($massiv[$i] != "") {
				$name = ($massiv[$i][1]) ? user_info($massiv[$i][2]) : $massiv[$i][2];
				$words = (engines_word($massiv[$i][4])) ? engines_word($massiv[$i][4]) : _NO;
				$cont .= "<tr>"
				."<td>".title_tip(_NICKNAME.": ".$name."<br>"._DATE.": ".format_time($massiv[$i][6], _TIMESTRING)).user_geo_ip($massiv[$i][3], 4)."</td>"
				."<td>".domain($massiv[$i][5], 30)."</td>"
				."<td>".domain($massiv[$i][4], 30)."</td>"
				."<td><span title=\"".$words."\" class=\"sl_note\">".cutstr($words, 25)."</span></td>"
				."<td>".$massiv[$i][0]."</td></tr>";
			}
		}
		$cont .= "</tbody></table>";
		$numpages = ceil($a / $confr['anum']);
		$cont .= num_page("pagenum", "", $a, $numpages, $confr['anum'], "op=referers&amp;sort=".$sort."&amp;order=".$order."&amp;", $confr['anump']);
		$cont .= tpl_eval("close", "");
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function referers_conf() {
	global $admin_file, $confr;
	head();
	$cont = referers_navi(0, 1, 0, 0);
	$permtest = end_chmod("config/config_referers.php", 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_conf\">"
	."<tr><td>"._C_34.":</td><td><input type=\"number\" name=\"anum\" value=\"".$confr['anum']."\" class=\"sl_conf\" placeholder=\""._C_34."\" required></td></tr>"
	."<tr><td>"._C_36.":</td><td><input type=\"number\" name=\"anump\" value=\"".$confr['anump']."\" class=\"sl_conf\" placeholder=\""._C_36."\" required></td></tr>"
	."<tr><td>"._REFER_T.":</td><td><input type=\"number\" name=\"refer_t\" value=\"".intval($confr['refer_t'] / 86400)."\" class=\"sl_conf\" placeholder=\""._REFER_T."\" required></td></tr>"
	."<tr><td>"._REFER."</td><td>".radio_form($confr['refer'], "refer")."</td></tr>"
	."<tr><td>"._REFERB."</td><td>".radio_form($confr['referb'], "referb")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"referers_save\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function referers_save() {
	global $admin_file, $confr;
	include("config_referers.php");
	$xanum = (!intval($_POST['anum'])) ? 50 : $_POST['anum'];
	$xanump = (!intval($_POST['anump'])) ? 10 : $_POST['anump'];
	$xrefer_t = (!intval($_POST['refer_t'])) ? 2592000 : $_POST['refer_t'] * 86400;
	$xrefer = (!intval($_POST['refer'])) ? "0" : "1";
	$xreferb = (!intval($_POST['referb'])) ? "0" : "1";
	$content = "\$confr = array();\n"
	."\$confr['anum'] = \"".$xanum."\";\n"
	."\$confr['anump'] = \"".$xanump."\";\n"
	."\$confr['refer_t'] = \"".$xrefer_t."\";\n"
	."\$confr['refer'] = \"".$xrefer."\";\n"
	."\$confr['referb'] = \"".$xreferb."\";\n";
	save_conf("config/config_referers.php", $content);
	header("Location: ".$admin_file.".php?op=referers_conf");
}

function referers_info() {
	head();
	echo referers_navi(0, 3, 0, 0)."<div id=\"repadm_info\">".adm_info(1, 0, "referers")."</div>";
	foot();
}

switch($op) {
	case "referers":
	referers();
	break;
	
	case "referers_conf":
	referers_conf();
	break;

	case "referers_save":
	referers_save();
	break;
	
	case "referers_del":
	$db->sql_query("DELETE FROM ".$prefix."_referer WHERE lid = '0'");
	header("Location: ".$admin_file.".php?op=referers");
	break;
	
	case "referers_info":
	referers_info();
	break;
}
?>