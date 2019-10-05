<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("ADMIN_FILE") || !is_admin_god()) die("Illegal file access");

function module_navi() {
	panel();
	$narg = func_get_args();
	$ops = array("module", "module_info");
	$lang = array(_HOME, _INFO);
	return navi_gen(_MODULES, "modules.png", "", $ops, $lang, "", "", $narg[0], $narg[1], $narg[2], $narg[3]);
}

function module() {
	global $prefix, $db, $admin_file, $infos;
	head();
	$cont = module_navi(0, 0, 0, 0);
	if (isset($infos)) $cont .= tpl_warn("warn", $infos, "", "", "info");
	$handle = opendir("modules");
	$modlist = array();
	while (false !== ($file = readdir($handle))) {
		if (!preg_match("/\./", $file) && (file_exists("modules/".$file."/index.php") || file_exists("modules/".$file."/admin/index.php"))) $modlist[] = $file;
	}
	closedir($handle);
	sort($modlist);
	for ($i = 0; $i < count($modlist); $i++) {
		if ($modlist[$i] != "") {
			list($mid) = $db->sql_fetchrow($db->sql_query("SELECT mid FROM ".$prefix."_modules WHERE title = '".$modlist[$i]."'"));
			if (!$mid) $db->sql_query("INSERT INTO ".$prefix."_modules VALUES (NULL, '".$modlist[$i]."', '0', '0', '1', '0', '0', '0')");
		}
	}
	$result = $db->sql_query("SELECT title FROM ".$prefix."_modules");
	while (list($title) = $db->sql_fetchrow($result)) {
		$a = 0;
		$handle = opendir("modules");
		while (false !== ($file = readdir($handle))) {
			if ($file == $title && (file_exists("modules/".$file."/index.php") || file_exists("modules/".$file."/admin/index.php"))) $a = 1;
		}
		closedir($handle);
		if ($a == 0) $db->sql_query("DELETE FROM ".$prefix."_modules WHERE title='".$title."'");
	}
	$cont .= tpl_eval("open");
	$cont .= "<table class=\"sl_table_list_sort\"><thead><tr><th>"._ID."</th><th>"._NAME."</th><th>"._MODUL."</th><th>"._VIEW."</th><th>"._GROUP."</th><th class=\"{sorter: false}\">"._STATUS."</th><th class=\"{sorter: false}\">"._FUNCTIONS."</th></tr></thead><tbody>";
	$result = $db->sql_query("SELECT mid, title, active, view, inmenu, mod_group FROM ".$prefix."_modules ORDER BY title ASC");
	while (list($mid, $title, $active, $view, $inmenu, $mod_group) = $db->sql_fetchrow($result)) {
		$act = ($active) ? "0" : "1";
		if ($view == 0) {
			$who_view = _MVALL;
		} elseif ($view == 1) {
			$who_view = _MVUSERS;
		} elseif ($view == 2) {
			$who_view = _MVADMIN;
		}
		$titlel = ($inmenu == 0) ? title_tip(_NO_SICHT).deflmconst($title) : deflmconst($title);
		if ($mod_group != 0) {
			$grp = $db->sql_fetchrow($db->sql_query("SELECT name FROM ".$prefix."_groups WHERE id = '".$mod_group."'"));
			$mod_group = $grp['name'];
		} else {
			$mod_group = _NONE;
		}
		if (file_exists("modules/".$title."/sql/table.sql")) {
			$filename = file_get_contents("modules/".$title."/sql/table.sql");
			$stringdump = explode(";", $filename);
			$install = "";
			for ($i = 0; $i < count($stringdump); $i++) {
				$string = str_replace("{pref}", $prefix, $stringdump[$i]);
				if (preg_match("/CREATE|ALTER|DELETE|DROP|UPDATE/i", $string)) {
					$table = explode("`", $string);
					$install = $db->sql_fetchrow($db->sql_query("SELECT Count(*) FROM ".$table[1]));
				}
			}
			if ($install) {
				$sqlimg = "||<a href=\"".$admin_file.".php?op=module_add&amp;mod=".$title."&amp;id=1\" OnClick=\"return DelCheck(this, '"._DB_DELETE." &quot;".$title."&quot;?');\" title=\""._DB_DELETE."\">"._DB_DELETE."</a>";
			} else {
				$sqlimg = "||<a href=\"".$admin_file.".php?op=module_add&amp;mod=".$title."&amp;id=2\" OnClick=\"return DelCheck(this, '"._DB_INSTALL." &quot;".$title."&quot;?');\" title=\""._DB_INSTALL."\">"._DB_INSTALL."</a>";
			}
		} else {
			$sqlimg = "";
		}
		if (file_exists("modules/".$title."/sql/update.sql")) {
			$sqluimg = "||<a href=\"".$admin_file.".php?op=module_add&amp;mod=".$title."&amp;id=3\" OnClick=\"return DelCheck(this, '"._DB_UPDATE." &quot;".$title."&quot;?');\" title=\""._DB_UPDATE."\">"._DB_UPDATE."</a>";
		} else {
			$sqluimg = "";
		}
		$cont .= "<tr><td>".$a."</td><td>".$titlel."</td><td>".$title."</td><td>".$who_view."</td><td>".$mod_group."</td><td>".ad_status("", $active)."</td><td>".add_menu(ad_status($admin_file.".php?op=module_status&amp;id=".$mid."&amp;act=".$act, $active)."||<a href=\"".$admin_file.".php?op=module_edit&amp;mid=".$mid."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>".$sqlimg.$sqluimg)."</td></tr>";
		$a++;
	}
	$cont .= "</tbody></table>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function module_edit() {
	global $prefix, $db, $admin_file;
	$mid = intval($_GET['mid']);
	list($title, $view, $inmenu, $mod_group, $blocks_m, $blocks_mc) = $db->sql_fetchrow($db->sql_query("SELECT title, view, inmenu, mod_group, blocks, blocks_c FROM ".$prefix."_modules WHERE mid = '".$mid."'"));
	head();
	$cont = module_navi(0, 0, 0, 0);
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_conf\">"
	."<tr><td>"._VIEWPRIV."</td><td><select name=\"view\" class=\"sl_conf\">";
	$privs = array(_MVALL, _MVUSERS, _MVADMIN);
	foreach ($privs as $key => $value) {
		$sel = ($view == $key ) ? " selected" : "";
		$cont .= "<option value=\"".$key."\"".$sel.">".$value."</option>";
	}
	$cont .= "</select></td></tr>";
	$numrow = $db->sql_numrows($db->sql_query("SELECT * FROM ".$prefix."_groups"));
	if ($numrow > 0) {
		$cont .= "<tr><td>"._UGROUP.":</td><td><select name=\"mod_group\" class=\"sl_conf\">";
		$result2 = $db->sql_query("SELECT id, name FROM ".$prefix."_groups");
		while (list($gid, $gname) = $db->sql_fetchrow($result2)) {
			$gsel = ($gid == $mod_group) ? " selected" : "";
			if (empty($none)) {
				$ggsel = ($mod_group == 0) ? " selected" : "";
				$cont .= "<option value=\"0\"".$ggsel.">"._NONE."</option>";
				$none = 1;
			}
			$cont .= "<option value=\"".$gid."\"".$gsel.">".$gname."</option>";
			$gsel = "";
		}
		$cont .= "</select></td></tr>";
	} else {
		$cont .= "<input type=\"hidden\" name=\"mod_group\" value=\"0\">";
	}
	$cont .= "<tr><td>"._BLOCKS_MOD.":</td><td><select name=\"blocks_m\" class=\"sl_conf\">";
	$bmods = array(_BLOCKS_MOD0, _BLOCKS_MOD1, _BLOCKS_MOD2, _BLOCKS_MOD3);
	foreach ($bmods as $key => $value) {
		$sel = ($blocks_m == $key ) ? "selected" : "";
		$cont .= "<option value=\"".$key."\" ".$sel.">".$value."</option>";
	}
	$cont .= "</select></td></tr>"
	."<tr><td>"._BLOCKS_MOD.":</td><td><select name=\"blocks_mc\" class=\"sl_conf\">";
	$bmodcs = array(_BLOCKS_MODC0, _BLOCKS_MODC1, _BLOCKS_MODC2, _BLOCKS_MODC3);
	foreach ($bmodcs as $key => $value) {
		$sel = ($blocks_mc == $key ) ? " selected" : "";
		$cont .= "<option value=\"".$key."\"".$sel.">".$value."</option>";
	}
	$cont .= "</select></td></tr>"
	."<tr><td>"._SHOWINMENU."</td><td>".radio_form($inmenu, "inmenu")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"mid\" value=\"".$mid."\"><input type=\"hidden\" name=\"op\" value=\"module_edit_save\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function module_info() {
	head();
	echo module_navi(0, 1, 0, 0)."<div id=\"repadm_info\">".adm_info(1, 0, "modules")."</div>";
	foot();
}

switch ($op) {
	case "module":
	module();
	break;
	
	case "module_status":
	$db->sql_query("UPDATE ".$prefix."_modules SET active = '".$act."' WHERE mid = '".$id."'");
	header("Location: ".$admin_file.".php?op=module");
	break;
	
	case "module_edit":
	module_edit();
	break;
	
	case "module_edit_save":
	$mid = intval($_POST['mid']);
	$view = $_POST['view'];
	$inmenu = $_POST['inmenu'];
	$mod_group = ($view != 1) ? 0 : $_POST['mod_group'];
	$blocks_m = $_POST['blocks_m'];
	$blocks_mc = $_POST['blocks_mc'];
	$result = $db->sql_query("UPDATE ".$prefix."_modules SET view = '".$view."', inmenu = '".$inmenu."', mod_group = '".$mod_group."', blocks = '".$blocks_m."', blocks_c = '".$blocks_mc."' WHERE mid = '".$mid."'");
	header("Location: ".$admin_file.".php?op=module");
	break;
	
	case "module_add":
	$module = $_GET['mod'];
	if ($module && $id) {
		$filename = ($id == 3) ? file_get_contents("modules/".$module."/sql/update.sql") : file_get_contents("modules/".$module."/sql/table.sql");
		if ($id == 1) {
			$ttitle = _DB_DELETE;
		} elseif ($id == 2) {
			$ttitle = _DB_INSTALL;
		} elseif ($id == 3) {
			$ttitle = _DB_UPDATE;
		}
		$stringdump = explode(";", $filename);
		for ($i = 0; $i < count($stringdump); $i++) {
			$string = str_replace("{pref}", $prefix, $stringdump[$i]);
			if ($id != 1) $ident = $db->sql_query(stripslashes($string));
			if (preg_match("/CREATE|ALTER|DELETE|DROP|UPDATE/i", $string)) {
				$table = explode("`", $string);
				if ($id == 1) $ident = $db->sql_query("DROP TABLE ".$table[1]);
				$info .= _TABLE.": ".$table[1]." - "._STATUS.": ".(($ident) ? "<span class=\"sl_green\">"._OK."</span>" : "<span class=\"sl_red\">"._ERROR."</span>")."<br>";
			}
		}
		$infos = $ttitle.": ".$module."<br><br>".$info;
	}
	module();
	break;
	
	case "module_info":
	module_info();
	break;
}
?>