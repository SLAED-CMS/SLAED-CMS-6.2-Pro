<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("ADMIN_FILE") || !is_admin_god()) die("Illegal file access");

function groups_navi() {
	panel();
	$narg = func_get_args();
	$ops = array("groups", "groups_add", "groups_points", "groups_info");
	$lang = array(_HOME, _ADD, _POINTS, _INFO);
	return navi_gen(_UGROUPS, "groups.png", "", $ops, $lang, "", "", $narg[0], $narg[1], $narg[2], $narg[3]);
}

function groups() {
	global $prefix, $db, $admin_file, $conf;
	head();
	$cont = groups_navi(0, 0, 0, 0);
	$result = $db->sql_query("SELECT id, name, description, points, extra, rank, color FROM ".$prefix."_groups ORDER BY points, extra");
	if ($db->sql_numrows($result) > 0) {
		$cont .= tpl_eval("open");
		$cont .= "<table class=\"sl_table_list_sort\"><thead><tr><th>"._ID."</th><th class=\"{sorter: false}\">"._RANK."</th><th>"._GROUP."</th><th>"._POINTS."</th><th>".cutstr(_USERSCOUNT, 5, 1)."</th><th>".cutstr(_SPEC, 4, 1)."</th><th class=\"{sorter: false}\">"._FUNCTIONS."</th></tr></thead><tbody>";
		while (list($grid, $grname, $description, $points, $extra, $rank, $color) = $db->sql_fetchrow($result)) {
			if (intval($extra)) {
				$extra = _YES;
				list($users_num) = $db->sql_fetchrow($db->sql_query("SELECT Count(*) FROM ".$prefix."_users WHERE user_group = '".$grid."'"));
				$userlink = $admin_file.".php?op=users_show&amp;search=6&amp;chng_user=".$grid;
			} else {
				$extra = _NO;
				list($users_num) = $db->sql_fetchrow($db->sql_query("SELECT Count(*) FROM ".$prefix."_users WHERE user_points >= '".$points."'"));
				$userlink = $admin_file.".php?op=users_show&amp;search=7&amp;chng_user=".$points;
			}
			$cont .= "<tr>"
			."<td >".$grid."</td>"
			."<td><img src=\"templates/".$conf['theme']."/images/ranks/".$rank."\" alt=\""._RANK."\" title=\""._RANK."\"></td>"
			."<td>".title_tip(_DESCRIPTION.": ".$description)."<span style=\"color: ".$color."\">".$grname."</span></td>"
			."<td>".$points."</td>"
			."<td>".$users_num."</td>"
			."<td>".$extra."</td>"
			."<td>".add_menu("<a href=\"".$userlink."\" title=\""._MVIEW."\">"._MVIEW."</a>||<a href=\"".$admin_file.".php?op=groups_add&amp;id=".$grid."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=groups_del&amp;id=".$grid."\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$grname."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>")."</td></tr>";
		}
		$cont .= "</tbody></table>";
		$cont .= tpl_eval("close", "");
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function groups_add() {
	global $prefix, $db, $admin_file, $conf, $stop;
	if (isset($_REQUEST['id'])) {
		$id = $_REQUEST['id'];
		$result = $db->sql_query("SELECT id, name, description, points, extra, rank, color FROM ".$prefix."_groups WHERE id = '".$id."'");
		list($gid, $grname, $description, $points, $extra, $rank, $color) = $db->sql_fetchrow($result);
		$check = ($extra) ? " checked" : "";
	} else {
		$gid = isset($_POST['gid']) ? $_POST['gid'] : "";
		$grname = isset($_POST['grname']) ? $_POST['grname'] : "";
		$description = isset($_POST['description']) ? $_POST['description'] : "";
		$grextra = isset($_POST['grextra']) ? $_POST['grextra'] : "";
		$points = isset($_POST['points']) ? $_POST['points'] : "";
		$rank = isset($_POST['rank']) ? str_replace("templates/".$conf['theme']."/images/ranks/", "", $_POST['rank']) : "";
		$color = isset($_POST['color']) ? $_POST['color'] : "";
		$check = ($grextra) ? " checked" : "";
	}
	$rank = empty($rank) ? "rank_1.png" : $rank;
	head();
	$cont = groups_navi(0, 1, 0, 0);
	$cont .= tpl_warn("warn", _GROUPSI, "", "", "info");
	if ($stop) $cont .= tpl_warn("warn", $stop, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<form name=\"post\" action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_form\">"
	."<tr><td>"._NAME.":</td><td><input type=\"text\" name=\"grname\" value=\"".$grname."\" maxlength=\"255\" class=\"sl_form\" placeholder=\""._NAME."\" required></td></tr>"
	."<tr><td>"._DESCRIPTION.":</td><td><textarea name=\"description\" cols=\"65\" rows=\"5\" class=\"sl_form\" placeholder=\""._DESCRIPTION."\">".$description."</textarea></td></tr>"
	."<tr><td>"._IMG.":</td><td><select name=\"rank\" id=\"img_replace\" class=\"sl_form\">";
	$path = "templates/".$conf['theme']."/images/ranks/";
	$dir = opendir($path);
	while (false !== ($entry = readdir($dir))) {
		if (preg_match("#(\.gif|\.png|\.jpg|\.jpeg)$#is", $entry) && $entry != "." && $entry != "..") {
			$sel = ($rank == $entry) ? " selected" : "";
			$cont .= "<option value=\"".$path.$entry."\"".$sel.">".$entry."</option>";
		}
	}
	closedir($dir);
	$cont .= "</select></td></tr>"
	."<tr><td>"._RANK.":</td><td><img src=\"".$path.$rank."\" id=\"picture\" alt=\""._RANK."\"></td></tr>"
	."<tr><td>"._COLOR.":</td><td><input type=\"color\" name=\"color\" value=\"".$color."\" class=\"sl_form\"></td></tr>"
	."<tr><td>"._POINTSNEEDED.":</td><td><input type=\"number\" name=\"points\" value=\"".$points."\" class=\"sl_form\" placeholder=\""._POINTSNEEDED."\"></td></tr>"
	."<tr><td>"._SPEC_GROUP.":<div class=\"sl_small\">"._GRSINFO."</div></td><td><input type=\"checkbox\" name=\"grextra\" value=\"1\"".$check."></td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"gid\" value=\"".$gid."\"><input type=\"hidden\" name=\"op\" value=\"groups_save\"><input type=\"submit\" value=\""._SAVE."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function groups_save() {
	global $prefix, $db, $admin_file, $conf, $stop;
	$id = intval($_POST['gid']);
	$grname = $_POST['grname'];
	$description = $_POST['description'];
	$points = intval($_POST['points']);
	$grextra = intval($_POST['grextra']);
	$rank = $_POST['rank'];
	$color = $_POST['color'];
	if (!$grname) $stop[] = _CERROR;
	if (!is_numeric($points) && $grextra != "1") $stop = _NONUMVALUE;
	if (!$stop) {
		$points = ($grextra == "1") ? "0" : $points;
		$rank = str_replace("templates/".$conf['theme']."/images/ranks/", "", $rank);
		if ($id) {
			$db->sql_query("UPDATE ".$prefix."_groups SET name = '".$grname."', description = '".$description."', points = '".$points."', extra = '".$grextra."', rank = '".$rank."', color = '".$color."' WHERE id = '".$id."'");
		} else {
			$db->sql_query("INSERT INTO ".$prefix."_groups VALUES (NULL, '".$grname."', '".$description."', '".$points."', '".$grextra."', '".$rank."', '".$color."')");
		}
		header("Location: ".$admin_file.".php?op=groups");
	} else {
		groups_add();
	}
}

function groups_points() {
	global $prefix, $db, $admin_file, $confu;
	head();
	$cont = groups_navi(0, 2, 0, 0);
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\">"
	."<table class=\"sl_table_list_sort\"><thead><tr><th>"._ID."</th><th>"._NAME."</th><th>"._DESCRIPTION."</th><th class=\"{sorter: false}\">"._POINTS."</th></tr></thead><tbody>";
	$p = array(_POINTS01, _POINTS02, _POINTS03, _POINTS04, _POINTS05, _POINTS06, _POINTS07, _POINTS08, _POINTS09, _POINTS10, _POINTS11, _POINTS12, _POINTS13, _POINTS14, _POINTS15, _POINTS16, _POINTS17, _POINTS18, _POINTS19, _POINTS20, _POINTS21, _POINTS22, _POINTS23, _POINTS24, _POINTS25, _POINTS26, _POINTS27, _POINTS28, _POINTS29, _POINTS30, _POINTS31, _POINTS32, _POINTS33, _POINTS34, _POINTS35, _POINTS36, _POINTS37, _POINTS38, _POINTS39, _POINTS40, _POINTS41, _POINTS42, _POINTS43, _POINTS44, _POINTS45);
	$d = array(_DESC01, _DESC02, _DESC03, _DESC04, _DESC05, _DESC06, _DESC07, _DESC08, _DESC09, _DESC10, _DESC11, _DESC12, _DESC13, _DESC14, _DESC15, _DESC16, _DESC17, _DESC18, _DESC19, _DESC20, _DESC21, _DESC22, _DESC23, _DESC24, _DESC25, _DESC26, _DESC27, _DESC28, _DESC29, _DESC30, _DESC31, _DESC32, _DESC33, _DESC34, _DESC35, _DESC36, _DESC37, _DESC38, _DESC39, _DESC40, _DESC41, _DESC42, _DESC43, _DESC44, _DESC45);
	$points = explode(",", $confu['points']);
	for ($i = 0; $i < count($p); $i++) {
		$a = $i + 1;
		$cont .= "<tr><td>".$a."</td><td>".$p[$i]."</td><td>".$d[$i]."</td><td><input type=\"number\" value=\"".$points[$i]."\" name=\"spoints[]\" class=\"sl_field\" placeholder=\""._POINTS."\" required></td></tr>";
	}
	$cont .= "</tbody></table><table class=\"sl_table_conf\"><tr><td class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"groups_points_save\"><input type=\"submit\" value=\""._SAVE."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function groups_info() {
	head();
	echo groups_navi(0, 3, 0, 0)."<div id=\"repadm_info\">".adm_info(1, 0, "groups")."</div>";
	foot();
}

switch($op) {
	case "groups":
	groups();
	break;
	
	case "groups_add":
	groups_add();
	break;
	
	case "groups_save":
	groups_save();
	break;
	
	case "groups_del":
	$db->sql_query("DELETE FROM ".$prefix."_groups WHERE id = '".$id."'");
	$db->sql_query("UPDATE ".$prefix."_modules SET mod_group = '0' WHERE mod_group = '".$id."'");
	header("Location: ".$admin_file.".php?op=groups");
	break;
	
	case "groups_points":
	groups_points();
	break;
	
	case "groups_points_save":
	$spoints = $_POST['spoints'];
	if (isset($spoints)) {
		$npoints = implode(",", $spoints);
		$content = file_get_contents("config/config_users.php");
		$content = str_replace("\$confu['points'] = \"".$confu['points']."\";", "\$confu['points'] = \"".$npoints."\";", $content);
		$fp = fopen("config/config_users.php", "wb");
		fwrite($fp, $content);
		fclose($fp);
	}
	header("Location: ".$admin_file.".php?op=groups_points");
	break;
	
	case "groups_info":
	groups_info();
	break;
}
?>