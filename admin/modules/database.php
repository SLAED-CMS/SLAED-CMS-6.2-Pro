<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("ADMIN_FILE") || !is_admin_god()) die("Illegal file access");

include("config/config.php");

function database_navi() {
	global $admin_file;
	panel();
	$narg = func_get_args();
	$ops = array("database", "database&amp;type=optimize", "database&amp;type=repair", "database_dump", "database_info");
	$lang = array(_HOME, _OPTIMIZE, _REPAIR, _INQUIRY, _INFO);
	return navi_gen(_DATABASE, "database.png", "", $ops, $lang, "", "", $narg[0], $narg[1], $narg[2], $narg[3]);
}

function database() {
	global $prefix, $db, $admin_file, $confdb;
	$type = isset($_GET['type']) ? $_GET['type'] : false;
	$ftitleth = ($type == "optimize" || $type == "repair") ? _STATUS : _FUNCTIONS;
	$content ="<table class=\"sl_table_list_sort\"><thead><tr><th>"._ID."</th><th>"._TABLE."</th><th>"._DBCOLL."</th><th>"._ROWS."</th><th>"._DATE."</th><th>"._SIZE."</th><th>"._DBFREE."</th><th class=\"{sorter: false}\">".$ftitleth."</th></tr></thead><tbody>";
	$total = 0;
	$totalfree = 0;
	$i = 0;
	$result = $db->sql_query("SHOW TABLE STATUS FROM ".$confdb['name']);
	while ($info = $db->sql_fetchrow($result)) {
		$name = $info['Name'];
		$tabloc = $info['Collation'];
		$tabsize = $info['Data_length'] + $info['Index_length'];
		$total += $tabsize;
		$tabsizefr = ($info['Data_free']) ? $info['Data_free'] : 0;
		$totalfree += $tabsizefr;
		$tabsizefrc = ($tabsizefr) ? "<div class=\"sl_red\">".files_size($tabsizefr)."</div>" : "<div class=\"sl_green\">".files_size($tabsizefr)."</div>";
		$crtime = $info['Create_time'];
		$rows = $info['Rows'];
		if ($type == "optimize") {
			$ftitletd = (!$info['Data_free']) ? "<div class=\"sl_red\">"._ALREADYOPTIMIZED."</div>" : "<div class=\"sl_green\">"._OPTIMIZED."</div>";
			$db->sql_query("OPTIMIZE TABLE ".$name);
		} elseif ($type == "repair") {
			$rresult = $db->sql_query("REPAIR TABLE ".$name);
			$ftitletd = (!$rresult) ? "<div class=\"sl_red\">"._ERROR."</div>" : "<div class=\"sl_green\">"._OK."</div>";
		} else {
			$ftitletd = add_menu("<a href=\"".$admin_file.".php?op=database_del&amp;tb=".$name."&amp;id=1\" OnClick=\"return DelCheck(this, '"._CLEAN." &quot;".$name."&quot;?');\" title=\""._CLEAN."\">"._CLEAN."</a>||<a href=\"".$admin_file.".php?op=database_del&amp;tb=".$name."&amp;id=2\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$name."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>");
		}
		$i++;
		$content .= "<tr><td>".$i."</td><td>".$name."</td><td>".$tabloc."</td><td>".$rows."</td><td>".format_time($crtime, _TIMESTRING)."</td><td>".files_size($tabsize)."</td><td>".$tabsizefrc."</td><td>".$ftitletd."</td></tr>";
	}
	$content .= "</tbody></table>";
	head();
	if (!$type) {
		$cont = database_navi(0, 0, 0, 0);
		$cont .= tpl_warn("warn", _OPTTEXT, "", "", "warn");
		$cont .= tpl_warn("warn", _REPTEXT, "", "", "info");
	} elseif ($type == "optimize") {
		$cont = database_navi(0, 1, 0, 0);
		$totalspace = $total - $totalfree;
		$info = _OPTIMIZE.": ".$confdb['name']."<br>"._TOTALSPACE.": ".files_size($totalspace)."<br>"._TOTALFREE.": ".files_size($totalfree);
		$cont .= tpl_warn("warn", $info, "", "", "info");
	} elseif ($type == "repair") {
		$cont = database_navi(0, 2, 0, 0);
		$info = _REPAIR.": ".$confdb['name']."<br>"._TOTALSPACE.": ".files_size($total);
		$cont .= tpl_warn("warn", $info, "", "", "info");
	}
	echo $cont.tpl_eval("open").$content.tpl_eval("close", "");
	foot();
}

function database_dump() {
	global $prefix, $db, $admin_file, $confdb;
	$type = isset($_POST['type']) ? $_POST['type'] : false;
	$pstring = isset($_POST['string']) ? $_POST['string'] : false;
	head();
	$cont = database_navi(0, 3, 0, 0);
	if ($type == "dump") {
		$string = explode(";", $pstring);
		foreach ($string as $var) {
			if ($var != "") {
				$stringdb = str_replace("{pref}", $prefix, $var);
				$id = $db->sql_query(stripslashes($stringdb));
				if (preg_match("/CREATE|ALTER|DELETE|DROP|RENAME|UPDATE/i", $stringdb)) {
					$table = explode("`", $stringdb);
					$info .= _TABLE.": ".$table[1]."<br>"._STATUS.": ".(($id) ? "<span class=\"sl_green\">"._OK."</span>" : "<span class=\"sl_red\">"._ERROR."</span>")."<br>";
				}
			}
		}
		$cont .= ($info) ? tpl_warn("warn", _INQUIRY.": ".$confdb['name']."<br>".$info, "", "", "info") : tpl_warn("warn", _DBERROR, "", "", "warn");
	} else {
		$cont .= tpl_warn("warn", _DBINFO, "", "", "info");
		$cont .= tpl_warn("warn", _DBWARN, "", "", "warn");
	}
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_edit\"><tr><td>".textarea_code("code", "string", "sl_form", "text/x-mysql", stripslashes($pstring))."</td></tr>"
	."<tr><td class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"database_dump\"><input type=\"hidden\" name=\"type\" value=\"dump\"><input type=\"submit\" value=\""._EXECUTE."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function database_info() {
	head();
	echo database_navi(0, 4, 0, 0)."<div id=\"repadm_info\">".adm_info(1, 0, "database")."</div>";
	foot();
}

switch ($op) {
	case "database":
	database();
	break;
	
	case "database_dump":
	database_dump();
	break;
	
	case "database_del":
	$tb = isset($_GET['tb']) ? $_GET['tb'] : false;
	if ($tb && $id == 1) {
		$db->sql_query("TRUNCATE TABLE ".$tb);
	} elseif ($tb && $id == 2) {
		$db->sql_query("DROP TABLE ".$tb);
	}
	header("Location: ".$admin_file.".php?op=database");
	break;
	
	case "database_info":
	database_info();
	break;
}
?>