<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined('ADMIN_FILE') || !is_admin_modul('auto_links')) die('Illegal file access');

include('config/config_auto_links.php');

function auto_links_navi() {
	global $admin_file, $confr;
	panel();
	$a_id = (isset($_POST['a_id'])) ? intval($_POST['a_id']) : (isset($_GET['a_id']) ? intval($_GET['a_id']) : '');
	$narg = func_get_args();
	$ops = array('auto_links', 'auto_links_add', 'auto_links_null', 'auto_links_noindel', 'auto_links_conf', 'auto_links_info');
	$lang = array(_HOME, _ADD, _NULLHITS, _NOINDEL, _PREFERENCES, _INFO);
	if ($a_id) {
		$search = "<form method=\"post\" action=\"".$admin_file.".php\">"._SORTE.": <select name=\"sort\">";
		$priv = array(_REF_ID, _REF_URL, _IN_ID, _IN_URL, _NAME_ID, _NAME_REF, _IP_ID, _IP_REF, _TIME_ID, _TIME_REF);
		foreach ($priv as $key => $value) {
			$sort = $key + 1;
			$sel = (isset($_POST['sort']) == $sort) ? "selected" : "";
			$search .= "<option value=\"".$sort."\" ".$sel.">".$value."</option>";
		}
		$search .= "</select><select name=\"order\">";
		$privs = array(_ASC, _DESC);
		foreach ($privs as $key => $value) {
			$sort = $key + 1;
			$sel = (isset($_POST['order']) == $sort) ? " selected" : "";
			$search .= "<option value=\"".$sort."\"".$sel.">".$value."</option>";
		}
		$search .= "</select> <input type=\"hidden\" name=\"op\" value=\"auto_links_stat\"><input type=\"hidden\" name=\"a_id\" value=\"".$a_id."\"><input type=\"submit\" value=\""._OK."\" class=\"sl_but_blue\"></form>";
		$search = tpl_eval("searchbox", $search);
	} else {
		$search = "";
	}
	$cont = navi_gen(_A_LINKS, "auto_links.png", $search, $ops, $lang, "", "", $narg[0], $narg[1], $narg[2], $narg[3]);
	$cont .= (!$confr['refer']) ? tpl_warn("warn", _A_NOTE, "", "", "warn") : "";
	return $cont;
}

function auto_links() {
	global $prefix, $db, $admin_file, $confal;
	head();
	$cont = auto_links_navi(0, 0, 0, 0);
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $confal['anum'];
	$result = $db->sql_query("SELECT id, sitename, link, hits, outs, added FROM ".$prefix."_auto_links ORDER BY hits ASC LIMIT ".$offset.", ".$confal['anum']);
	if ($db->sql_numrows($result) > 0) {
		$cont .= tpl_eval("open");
		$cont .= "<table class=\"sl_table_list_sort\"><thead><tr><th>"._ID."</th><th>"._SITENAME."</th><th>"._SITEURL."</th><th>"._HITS."</th><th>"._OUTS."</th><th class=\"{sorter: false}\">"._FUNCTIONS."</th></tr></thead><tbody>";
		while (list($a_id, $a_sitename, $a_link, $a_hits, $a_outs, $a_added)= $db->sql_fetchrow($result)) {
			$vhits = ($a_hits) ? "<a href=\"".$admin_file.".php?op=auto_links_stat&amp;a_id=".$a_id."\" title=\""._MVIEW."\">"._MVIEW."</a>||" : "";
			$cont .= "<tr><td>".$a_id."</td>"
			."<td>".title_tip(_REG.": ".format_time($a_added, _TIMESTRING))."<span title=\"".$a_sitename."\" class=\"sl_note\">".cutstr($a_sitename, 40)."</span></td>"
			."<td>".domain($a_link)."</td>"
			."<td>".$a_hits."</td>"
			."<td>".$a_outs."</td>"
			."<td>".add_menu($vhits."<a href=\"".$admin_file.".php?op=auto_links_add&amp;id=".$a_id."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=auto_links_delete&amp;id=".$a_id."&amp;refer=1\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$a_sitename."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>")."</td></tr>";
		}
		$cont .= "</tbody></table>";
		$cont .= num_article("pagenum", "", $confal['anum'], "op=auto_links&amp;", "id", "_auto_links", "", "", $confal['anump']);
		$cont .= tpl_eval('close', '');
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function auto_links_stat() {
	global $prefix, $db, $admin_file, $confal;
	$a_id = getVar('req', 'a_id', 'num');
	$sort = getVar('req', 'sort', 'num');
	$order = getVar('req', 'order', 'num');
	$num = getVar('get', 'num', 'num', '1');
	$offset = ($num - 1) * $confal['anum'];
	$tnum = ($offset) ? $confal['anum'] + $offset : $confal['anum'];
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
	$result = $db->sql_query("SELECT Count(".$count.") AS hits, uid, name, ip, referer, link, date FROM ".$prefix."_referer WHERE lid = '".$a_id."' GROUP BY ".$count." ORDER BY ".$ordby." ".$ordsc);
	head();
	$cont = auto_links_navi(0, 0, 0, 0);
	$massiv = array();
	while (list($hits, $uid, $name, $ip, $referer, $link, $date)= $db->sql_fetchrow($result)) {
		$massiv[] = array($hits, $uid, $name, $ip, $referer, $link, $date);
		$a++;
	}
	if (isArray($massiv)) {
		$cont .= tpl_eval("open");
		$cont .= "<table class=\"sl_table_list_sort\"><thead><tr><th>"._ID."</th><th>"._NICKNAME."</th><th>"._IP."</th><th>"._REF_URL."</th><th>"._IN_URL."</th></tr></thead><tbody>";
		for ($i = $offset; $i < $tnum; $i++) {
			if ($massiv[$i] != "") {
				$name = ($massiv[$i][1]) ? user_info($massiv[$i][2]) : $massiv[$i][2];
				$cont .= "<tr><td>".$massiv[$i][0]."</td>"
				."<td>".title_tip(_DATE.": ".date(_TIMESTRING, $massiv[$i][6])).$name."</td>"
				."<td>".user_geo_ip($massiv[$i][3], 4)."</td>"
				."<td>".domain($massiv[$i][4], 35)."</td>"
				."<td>".domain($massiv[$i][5], 15)."</td></tr>";
			}
		}
		$cont .= "</tbody></table>";
		$numpages = ceil($a / $confal['anum']);
		$cont .= num_page("pagenum", "", $a, $numpages, $confal['anum'], "op=auto_links_stat&amp;a_id=".$a_id."&amp;sort=".$sort."&amp;order=".$order."&amp;", $confal['anump']);
		$cont .= tpl_eval("close");
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function auto_links_add() {
	global $prefix, $db, $admin_file, $stop;
	if (isset($_REQUEST['id'])) {
		$a_id = intval($_REQUEST['id']);
		$result = $db->sql_query("SELECT id, sitename, description, link, mail, hits, outs, added FROM ".$prefix."_auto_links WHERE id = '".$a_id."'");
		list($a_id, $a_sitename, $a_description, $a_sitelink, $a_adminemail, $a_hits, $a_outs, $a_added) = $db->sql_fetchrow($result);
	} else {
		$a_id = (isset($_POST['a_id'])) ? intval($_POST['a_id']) : 0;
		$a_sitename = (isset($_POST['a_sitename'])) ? save_text($_POST['a_sitename'], 1) : "";
		$a_adminemail = (isset($_POST['a_adminemail'])) ? $_POST['a_adminemail'] : "";
		$a_description = (isset($_POST['a_description'])) ? save_text($_POST['a_description']) : "";
		$a_sitelink = (isset($_POST['a_sitelink'])) ? $_POST['a_sitelink'] : "http://";
		$a_hits = (isset($_POST['a_hits'])) ? intval($_POST['a_hits']) : 0;
		$a_outs = (isset($_POST['a_outs'])) ? intval($_POST['a_outs']) : 0;
	}
	head();
	$cont = auto_links_navi(0, 1, 0, 0);
	if ($stop) $cont .= tpl_warn("warn", $stop, "", "", "warn");
	if ($a_description) $cont .= preview($a_sitename, $a_description, "", "", "auto_links");
	$cont .= tpl_eval("open");
	$cont .= "<form name=\"post\" action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_form\">"
	."<tr><td>"._SITENAME.":</td><td><input type=\"text\" name=\"a_sitename\" value=\"".$a_sitename."\" maxlength=\"255\" class=\"sl_form\" placeholder=\""._SITENAME."\" required></td></tr>"
	."<tr><td>"._A_LINKS_E.":</td><td><input type=\"email\" name=\"a_adminemail\" value=\"".$a_adminemail."\" maxlength=\"100\" class=\"sl_form\" placeholder=\""._A_LINKS_E."\" required></td></tr>"
	."<tr><td>"._A_LINKS_TEXT.":</td><td>".textarea("1", "a_description", $a_description, "auto_links", "5", _A_LINKS_TEXT, "1")."</td></tr>"
	."<tr><td>"._A_LINKS_L.":</td><td><input type=\"url\" name=\"a_sitelink\" value=\"".$a_sitelink."\" maxlength=\"100\" class=\"sl_form\" placeholder=\""._A_LINKS_L."\" required></td></tr>"
	."<tr><td>"._HITS.":</td><td><input type=\"number\" name=\"a_hits\" value=\"".$a_hits."\" class=\"sl_form\" placeholder=\""._HITS."\"></td></tr>"
	."<tr><td>"._OUTS.":</td><td><input type=\"number\" name=\"a_outs\" value=\"".$a_outs."\" class=\"sl_form\" placeholder=\""._OUTS."\"></td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\">".ad_save("a_id", $a_id, "auto_links_save")."</td></tr></table></form>";
	$cont .= tpl_eval('close', '');
	echo $cont;
	foot();
}

function auto_links_save() {
	global $prefix, $db, $admin_file, $stop;
	$a_id = intval($_POST['a_id']);
	$a_sitename = save_text($_POST['a_sitename'], 1);
	$a_description = save_text($_POST['a_description']);
	$a_sitelink = url_filter($_POST['a_sitelink']);
	$a_adminemail = $_POST['a_adminemail'];
	$a_hits = intval($_POST['a_hits']);
	$a_outs = intval($_POST['a_outs']);
	$stop = array();
	if (!$a_sitename) $stop[] = _CERROR10;
	if (!$a_description) $stop[] = _CERROR11;
	if (!$a_sitelink) $stop[] = _CERROR4;
	if (!$stop && $_POST['posttype'] == "save") {
		if ($a_id) {
			$db->sql_query("UPDATE ".$prefix."_auto_links SET sitename = '".$a_sitename."', description = '".$a_description."', link = '".$a_sitelink."', mail = '".$a_adminemail."', hits = '".$a_hits."', outs = '".$a_outs."' WHERE id = '".$a_id."'");
		} else {
			$db->sql_query("INSERT INTO ".$prefix."_auto_links VALUES (NULL, '".$a_sitename."', '".$a_description."', '".$a_sitelink."', '".$a_adminemail."', '".$a_hits."', '".$a_outs."', now())");
		}
		header("Location: ".$admin_file.".php?op=auto_links");
	} elseif ($_POST['posttype'] == "delete") {
		auto_links_delete($a_id);
	} else {
		auto_links_add();
	}
}

function auto_links_delete() {
	global $prefix, $db, $admin_file, $id;
	$arg = func_get_args();
	$id = ($arg[0]) ? $arg[0] : $id;
	if ($id) {
		$db->sql_query("DELETE FROM ".$prefix."_auto_links WHERE id = '".$id."'");
		$db->sql_query("DELETE FROM ".$prefix."_referer WHERE lid = '".$id."'");
	}
	referer($admin_file.".php?op=auto_links");
}

function auto_links_conf() {
	global $admin_file, $conf, $confal;
	head();
	$cont = auto_links_navi(0, 4, 0, 0);
	$permtest = end_chmod("config/config_auto_links.php", 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<form name=\"post\" action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_conf\">"
	."<tr><td>"._A_1.":</td><td><select name=\"img\" id=\"img_replace\" class=\"sl_conf\">";
	$path = "templates/".$conf['theme']."/images/banners/";
	$dir = opendir($path);
	while (false !== ($entry = readdir($dir))) {
		if (preg_match("/(\.gif|\.png|\.jpg|\.jpeg)$/is", $entry) && $entry != "." && $entry != "..") {
			$sel = ($confal['img'] == $entry) ? " selected" : "";
			$cont .= "<option value=\"".$path.$entry."\"".$sel.">".$entry."</option>";
		}
	}
	closedir($dir);
	$cont .= "</select></td></tr>"
	."<tr><td>"._A_2.":</td><td><img src=\"".$path.$confal['img']."\" id=\"picture\" alt=\""._SITELOGO."\"></td></tr>"
	."<tr><td>"._C_33.":</td><td><input type=\"number\" name=\"num\" value=\"".$confal['num']."\" class=\"sl_conf\" placeholder=\""._C_33."\" required></td></tr>"
	."<tr><td>"._C_34.":</td><td><input type=\"number\" name=\"anum\" value=\"".$confal['anum']."\" class=\"sl_conf\" placeholder=\""._C_34."\" required></td></tr>"
	."<tr><td>"._C_35.":</td><td><input type=\"number\" name=\"nump\" value=\"".$confal['nump']."\" class=\"sl_conf\" placeholder=\""._C_35."\" required></td></tr>"
	."<tr><td>"._C_36.":</td><td><input type=\"number\" name=\"anump\" value=\"".$confal['anump']."\" class=\"sl_conf\" placeholder=\""._C_36."\" required></td></tr>"
	."<tr><td>"._A_4.":</td><td><input type=\"number\" name=\"strip\" value=\"".$confal['strip']."\" class=\"sl_conf\" placeholder=\""._A_4."\" required></td></tr>"
	."<tr><td>"._A_5.":</td><td><input type=\"number\" name=\"limit\" value=\"".$confal['limit']."\" class=\"sl_conf\" placeholder=\""._A_5."\" required></td></tr>"
	."<tr><td>"._ADDAMAIL."</td><td>".radio_form($confal['addmail'], "addmail")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"auto_links_conf_save\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval('close', '');
	echo $cont;
	foot();
}

function auto_links_conf_save() {
	global $admin_file, $conf;
	$ximg = str_replace("templates/".$conf['theme']."/images/banners/", "", $_POST['img']);
	$content = "\$confal = array();\n"
	."\$confal['img'] = \"".$ximg."\";\n"
	."\$confal['num'] = \"".$_POST['num']."\";\n"
	."\$confal['anum'] = \"".$_POST['anum']."\";\n"
	."\$confal['nump'] = \"".$_POST['nump']."\";\n"
	."\$confal['anump'] = \"".$_POST['anump']."\";\n"
	."\$confal['strip'] = \"".$_POST['strip']."\";\n"
	."\$confal['limit'] = \"".$_POST['limit']."\";\n"
	."\$confal['addmail'] = \"".$_POST['addmail']."\";\n";
	save_conf("config/config_auto_links.php", $content);
	header("Location: ".$admin_file.".php?op=auto_links_conf");
}

function auto_links_info() {
	head();
	echo auto_links_navi(0, 5, 0, 0)."<div id=\"repadm_info\">".adm_info(1, "auto_links", 0)."</div>";
	foot();
}

switch ($op) {
	case "auto_links":
	auto_links();
	break;
	
	case "auto_links_stat":
	auto_links_stat();
	break;
	
	case "auto_links_add":
	auto_links_add();
	break;
	
	case "auto_links_save":
	auto_links_save();
	break;
	
	case "auto_links_delete":
	auto_links_delete();
	break;
	
	case "auto_links_null":
	$db->sql_query("UPDATE ".$prefix."_auto_links SET hits = '0', outs = '0'");
	$db->sql_query("DELETE FROM ".$prefix."_referer WHERE lid != '0'");
	header("Location: ".$admin_file.".php?op=auto_links");
	break;
	
	case "auto_links_noindel":
	$db->sql_query("DELETE FROM ".$prefix."_auto_links WHERE hits = '0'");
	header("Location: ".$admin_file.".php?op=auto_links");
	break;
	
	case "auto_links_conf":
	auto_links_conf();
	break;

	case "auto_links_conf_save":
	auto_links_conf_save();
	break;
	
	case "auto_links_info":
	auto_links_info();
	break;
}
?>