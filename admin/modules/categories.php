<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("ADMIN_FILE") || !is_admin_god()) die("Illegal file access");

function cat_navi() {
	global $admin_file;
	panel();
	$modul = isset($_POST['modul']) ? analyze($_POST['modul']) : (isset($_GET['modul']) ? analyze($_GET['modul']) : "forum");
	$modlink = "&amp;modul=".$modul;
	$narg = func_get_args();
	$ops = array("cat_show".$modlink, "cat_add".$modlink, "cat_sub_add".$modlink, "cat_add_edit".$modlink, "cat_fix".$modlink, "cat_info".$modlink);
	$lang = array(_HOME, _ADDCATEGORY, _ADDSUBCATEGORY, _EDIT, _FIX, _INFO);
	$sops = array("", "", "");
	$slang = array(_CATEGORY, _ACESS, _ACESSF);
	$search = tpl_eval("searchbox", "<form method=\"post\" action=\"".$admin_file.".php\"><input type=\"hidden\" name=\"op\" value=\"cat_show\">"._MODUL.": ".cat_modul("modul", "", $modul, 1)."</form>");
	return navi_gen(_CATEGORIES, "categories.png", $search, $ops, $lang, $sops, $slang, $narg[0], $narg[1], $narg[2], $narg[3], $narg[4]);
}

function cat_show() {
	$modul = isset($_POST['modul']) ? analyze($_POST['modul']) : (isset($_GET['modul']) ? analyze($_GET['modul']) : "forum");
	head();
	echo cat_navi(0, 0, 0, 0, "").tpl_warn("warn", _INFOCATDEL, "", "", "info").tpl_eval("open")."<div id=\"repajax_cat\">".ajax_cat($modul, 1)."</div>".tpl_eval("close", "");
	foot();
}

function cat_fix() {
	global $prefix, $db, $admin_file;
	$modul = isset($_POST['modul']) ? analyze($_POST['modul']) : (isset($_GET['modul']) ? analyze($_GET['modul']) : "forum");
	$result = $db->sql_query("SELECT id FROM ".$prefix."_categories WHERE modul = '".$modul."' ORDER BY ordern ASC");
	$ordern = 0;
	while (list($id) = $db->sql_fetchrow($result)) {
		$ordern++;
		$db->sql_query("UPDATE ".$prefix."_categories SET ordern = '".$ordern."' WHERE id = '".$id."'");
	}
	header("Location: ".$admin_file.".php?op=cat_show&modul=".$modul);
}

function cat_add() {
	global $prefix, $db, $conf, $admin_file;
	$modul = isset($_GET['modul']) ? analyze($_GET['modul']) : "forum";
	$path = "templates/".$conf['theme']."/images/categories/";
	head();
	$cont = cat_navi(0, 1, 1, 0, "cat_add");
	$cont .= tpl_warn("warn", _CACESSI, "", "", "info");
	$cont .= tpl_eval("open");
	$cont .= "<form name=\"post\" action=\"".$admin_file.".php\" method=\"post\">"
	."<div id=\"tabcs0\" class=\"tabcont\">"
	."<table class=\"sl_table_form\">"
	."<tr><td>"._TITLE.":</td><td><input type=\"text\" name=\"title\" class=\"sl_form\" placeholder=\""._TITLE."\" required></td></tr>"
	."<tr><td>"._DESCRIPTION.":</td><td><textarea name=\"description\" cols=\"65\" rows=\"5\" class=\"sl_form\" placeholder=\""._DESCRIPTION."\"></textarea></td></tr>";
	if ($conf['multilingual'] == 1) $cont .= "<tr><td>"._LANGUAGE.":</td><td><select name=\"language\" class=\"sl_form\">".language()."</select></td></tr>";
	$cont .= "<tr><td>"._MODUL.":</td><td>".cat_modul("modul", "sl_form", $modul)."</td></tr>"
	."<tr><td>"._IMG.":</td><td><select name=\"imgcat\" id=\"img_replace\" class=\"sl_form\">"
	."<option value=\"".$path."no.png\">"._NO."</option>";
	$dir = opendir($path);
	while (false !== ($entry = readdir($dir))) {
		if (preg_match("/(\.gif|\.png|\.jpg|\.jpeg)$/is", $entry) && $entry != "." && $entry != ".." && $entry != "no.png") $conts[] = "<option value=\"".$path.$entry."\">".$entry."</option>";
	}
	closedir($dir);
	asort($conts);
	$cont .= implode("", $conts)."</select></td></tr>"
	."<tr><td>"._PREVIEW.":</td><td><img src=\"".$path."no.png\" id=\"picture\" alt=\""._IMG."\"></td></tr>"
	."<tr><td>"._ACTIVATE2."</td><td>".radio_form("", "cstatus")."</td></tr></table>"
	."</div>"
	."<div id=\"tabcs1\" class=\"tabcont\">"
	."<table class=\"sl_table_form\">"
	."<tr><td>"._CAN." "._AUTH_VIEW.":<div class=\"sl_small\">"._ACESSI." "._CTRLINFO."</div></td><td>".catacess("auth_view", "sl_form", "", "")."</td></tr>"
	."<tr><td>"._CAN." "._AUTH_READ.":<div class=\"sl_small\">"._ACESSI." "._CTRLINFO."</div></td><td>".catacess("auth_read", "sl_form", "", "")."</td></tr></table>"
	."</div>"
	."<div id=\"tabcs2\" class=\"tabcont\">
	<table class=\"sl_table_form\">"
	."<tr><td>"._CAN." "._AUTH_POST.":<div class=\"sl_small\">"._ACESSI." "._CTRLINFO."</div></td><td>".catacess("auth_post", "sl_form", "", "")."</td></tr>"
	."<tr><td>"._CAN." "._AUTH_REPLY.":<div class=\"sl_small\">"._ACESSI." "._CTRLINFO."</div></td><td>".catacess("auth_reply", "sl_form", "", "")."</td></tr>"
	."<tr><td>"._CAN." "._AUTH_EDIT.":<div class=\"sl_small\">"._ACESSI." "._CTRLINFO."</div></td><td>".catacess("auth_edit", "sl_form", "", 1)."</td></tr>"
	."<tr><td>"._CAN." "._AUTH_DELETE.":<div class=\"sl_small\">"._ACESSI." "._CTRLINFO."</div></td><td>".catacess("auth_delete", "sl_form", "", 1)."</td></tr>"
	."<tr><td>"._CAN." "._AUTH_MOD.":<div class=\"sl_small\">"._ACESSI." "._CTRLINFO."</div></td><td>".catacess("auth_mod", "sl_form", "", 2)."</td></tr></table>"
	."</div>"
	."<script>
		var countries=new ddtabcontent(\"cat_adds\")
		countries.setpersist(true)
		countries.setselectedClassTarget(\"link\")
		countries.init()
	</script>"
	."<table class=\"sl_table_form\"><tr><td class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"cat_add_save\"><input type=\"submit\" value=\""._ADD."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}
	
function cat_sub_add() {
	global $prefix, $db, $conf, $admin_file;
	$modul = isset($_GET['modul']) ? analyze($_GET['modul']) : "forum";
	$path = "templates/".$conf['theme']."/images/categories/";
	head();
	if ($db->sql_numrows($db->sql_query("SELECT * FROM ".$prefix."_categories WHERE modul = '".$modul."'")) > 0) {
		$cont = cat_navi(0, 2, 1, 0, "cat_sub_add");
		$cont .= tpl_warn("warn", _CACESSI, "", "", "info");
		$cont .= tpl_eval("open");
		$cont .= "<form name=\"post2\" action=\"".$admin_file.".php\" method=\"post\">"
		."<div id=\"tabcs0\" class=\"tabcont\">"
		."<table class=\"sl_table_form\">"
		."<tr><td>"._TITLE.":</td><td><input type=\"text\" name=\"title\" maxlength=\"100\" class=\"sl_form\" placeholder=\""._TITLE."\" required></td></tr>"
		."<tr><td>"._DESCRIPTION.":</td><td><textarea name=\"description\" cols=\"65\" rows=\"5\" class=\"sl_form\" placeholder=\""._DESCRIPTION."\"></textarea></td></tr>";
		if ($conf['multilingual'] == 1) $cont .= "<tr><td>"._LANGUAGE.":</td><td><select name=\"language\" class=\"sl_form\">".language()."</select></td></tr>";
		$cont .= "<tr><td>"._MODUL.":</td><td>".cat_modul("modul", "sl_form", $modul)."</td></tr>"
		."<tr><td>"._CATEGORY.":</td><td>".getcat($modul, "", "cid", "sl_form")."</td></tr>"
		."<tr><td>"._IMG.":</td><td><select name=\"imgcat\" id=\"img_replace\" class=\"sl_form\">"
		."<option value=\"".$path."no.png\">"._NO."</option>";
		$dir = opendir($path);
		while (false !== ($entry = readdir($dir))) {
			if (preg_match("/(\.gif|\.png|\.jpg|\.jpeg)$/is", $entry) && $entry != "." && $entry != ".." && $entry != "no.png") $conts[] = "<option value=\"".$path.$entry."\">".$entry."</option>";
		}
		closedir($dir);
		asort($conts);
		$cont .= implode("", $conts)."</select></td></tr>"
		."<tr><td>"._PREVIEW.":</td><td><img src=\"".$path."no.png\" id=\"picture\" alt=\""._IMG."\"></td></tr>"
		."<tr><td>"._ACTIVATE2."</td><td>".radio_form("", "cstatus")."</td></tr></table>"
		."</div>"
		."<div id=\"tabcs1\" class=\"tabcont\">"
		."<table class=\"sl_table_form\">"
		."<tr><td>"._CAN." "._AUTH_VIEW.":<div class=\"sl_small\">"._ACESSI." "._CTRLINFO."</div></td><td>".catacess("auth_view", "sl_form", "", "")."</td></tr>"
		."<tr><td>"._CAN." "._AUTH_READ.":<div class=\"sl_small\">"._ACESSI." "._CTRLINFO."</div></td><td>".catacess("auth_read", "sl_form", "", "")."</td></tr></table>"
		."</div>"
		."<div id=\"tabcs2\" class=\"tabcont\">"
		."<table class=\"sl_table_form\">"
		."<tr><td>"._CAN." "._AUTH_POST.":<div class=\"sl_small\">"._ACESSI." "._CTRLINFO."</div></td><td>".catacess("auth_post", "sl_form", "", "")."</td></tr>"
		."<tr><td>"._CAN." "._AUTH_REPLY.":<div class=\"sl_small\">"._ACESSI." "._CTRLINFO."</div></td><td>".catacess("auth_reply", "sl_form", "", "")."</td></tr>"
		."<tr><td>"._CAN." "._AUTH_EDIT.":<div class=\"sl_small\">"._ACESSI." "._CTRLINFO."</div></td><td>".catacess("auth_edit", "sl_form", "", 1)."</td></tr>"
		."<tr><td>"._CAN." "._AUTH_DELETE.":<div class=\"sl_small\">"._ACESSI." "._CTRLINFO."</div></td><td>".catacess("auth_delete", "sl_form", "", 1)."</td></tr>"
		."<tr><td>"._CAN." "._AUTH_MOD.":<div class=\"sl_small\">"._ACESSI." "._CTRLINFO."</div></td><td>".catacess("auth_mod", "sl_form", "", 2)."</td></tr></table>"
		."</div>"
		."<script>
			var countries=new ddtabcontent(\"cat_sub_adds\")
			countries.setpersist(true)
			countries.setselectedClassTarget(\"link\")
			countries.init()
		</script>"
		."<table class=\"sl_table_form\"><tr><td class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"cat_add_save\"><input type=\"submit\" value=\""._ADD."\" class=\"sl_but_blue\"></td></tr></table></form>";
		$cont .= tpl_eval("close", "");
	} else {
		$cont = cat_navi(0, 2, 0, 0, "");
		$cont .= tpl_warn("warn", sprintf(_ERROR_SUBCAT, deflmconst($modul)), "", "", "info");
	}
	echo $cont;
	foot();
}

function cat_add_edit() {
	global $prefix, $db, $admin_file;
	$modul = isset($_GET['modul']) ? analyze($_GET['modul']) : "forum";
	head();
	$cont = cat_navi(0, 3, 0, 0, "");
	if ($db->sql_numrows($db->sql_query("SELECT * FROM ".$prefix."_categories WHERE modul = '".$modul."'")) > 0) {
		$cont .= tpl_eval("open");
		$cont .= "<table class=\"sl_table_form\"><form action=\"".$admin_file.".php\" method=\"post\">"
		."<tr><td>"._CATEGORY.":</td><td>".getcat($modul, "", "cid", "sl_form")."</td></tr>"
		."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"cat_edit\"><input type=\"submit\" value=\""._EDIT."\" class=\"sl_but_blue\"></td></tr></form></table>";
		$cont .= tpl_eval("close", "");
	} else {
		$cont .= tpl_warn("warn", sprintf(_ERROR_SUBCAT, deflmconst($modul)), "", "", "info");
	}
	echo $cont;
	foot();
}

function cat_edit() {
	global $prefix, $db, $conf, $admin_file;
	$cid = intval($_REQUEST['cid']);
	$path = "templates/".$conf['theme']."/images/categories/";
	$result = $db->sql_query("SELECT modul, title, description, img, language, parentid, cstatus, auth_view, auth_read, auth_post, auth_reply, auth_edit, auth_delete, auth_mod FROM ".$prefix."_categories WHERE id = '".$cid."'");
	list($modul, $title, $description, $imgcat, $language, $parentid, $cstatus, $auth_view, $auth_read, $auth_post, $auth_reply, $auth_edit, $auth_delete, $auth_mod) = $db->sql_fetchrow($result);
	head();
	$cont = cat_navi(0, 3, 1, 0, "cat_edit");
	$cont .= tpl_warn("warn", _CACESSI, "", "", "info");
	$cont .= tpl_eval("open");
	$cont .= "<form name=\"post\" action=\"".$admin_file.".php\" method=\"post\">"
	."<div id=\"tabcs0\" class=\"tabcont\">"
	."<table class=\"sl_table_form\">"
	."<tr><td>"._TITLE.":</td><td><input type=\"text\" name=\"title\" value=\"".$title."\" class=\"sl_form\" placeholder=\""._TITLE."\" required></td></tr>"
	."<tr><td>"._DESCRIPTION.":</td><td><textarea name=\"description\" cols=\"65\" rows=\"5\" class=\"sl_form\" placeholder=\""._DESCRIPTION."\">".$description."</textarea></td></tr>"
	."<tr><td>"._MODUL.":</td><td>".cat_modul("modul", "sl_form", $modul)."</td></tr>";
	if ($conf['multilingual'] == 1) $cont .= "<tr><td>"._LANGUAGE.":</td><td><select name=\"language\" class=\"sl_form\">".language($language)."</select></td></tr>";
	if ($parentid != 0) {
		$cont .= "<tr><td>"._CATEGORY.":</td><td>".getcat($modul, $parentid, "parentid", "sl_form")."</td></tr>";
	} else {
		$cont .= "<input type=\"hidden\" name=\"parentid\" value=\"0\">";
	}
	$cont .= "<tr><td>"._IMG.":</td><td><select name=\"imgcat\" id=\"img_replace\" class=\"sl_form\">"
	."<option value=\"".$path."no.png\">"._NO."</option>";
	$dir = opendir($path);
	while (false !== ($entry = readdir($dir))) {
		if (preg_match("/(\.gif|\.png|\.jpg|\.jpeg)$/is", $entry) && $entry != "." && $entry != ".." && $entry != "no.png") {
			$sel = ($imgcat == $entry) ? " selected" : "";
			$conts[] = "<option value=\"".$path.$entry."\"".$sel.">".$entry."</option>";
		}
	}
	closedir($dir);
	$imgcat = (!$imgcat) ? "no.png" : $imgcat;
	asort($conts);
	$cont .= implode("", $conts)."</select></td></tr>"
	."<tr><td>"._PREVIEW.":</td><td><img src=\"".$path.$imgcat."\" id=\"picture\" alt=\""._IMG."\"></td></tr>"
	."<tr><td>"._ACTIVATE2."</td><td>".radio_form($cstatus, "cstatus")."</td></tr></table>"
	."</div>"
	."<div id=\"tabcs1\" class=\"tabcont\">"
	."<table class=\"sl_table_form\">"
	."<tr><td>"._CAN." "._AUTH_VIEW.":<div class=\"sl_small\">"._ACESSI." "._CTRLINFO."</div></td><td>".catacess("auth_view", "sl_form", $auth_view, "")."</td></tr>"
	."<tr><td>"._CAN." "._AUTH_READ.":<div class=\"sl_small\">"._ACESSI." "._CTRLINFO."</div></td><td>".catacess("auth_read", "sl_form", $auth_read, "")."</td></tr></table>"
	."</div>"
	."<div id=\"tabcs2\" class=\"tabcont\">"
	."<table class=\"sl_table_form\">"
	."<tr><td>"._CAN." "._AUTH_POST.":<div class=\"sl_small\">"._ACESSI." "._CTRLINFO."</div></td><td>".catacess("auth_post", "sl_form", $auth_post, "")."</td></tr>"
	."<tr><td>"._CAN." "._AUTH_REPLY.":<div class=\"sl_small\">"._ACESSI." "._CTRLINFO."</div></td><td>".catacess("auth_reply", "sl_form", $auth_reply, "")."</td></tr>"
	."<tr><td>"._CAN." "._AUTH_EDIT.":<div class=\"sl_small\">"._ACESSI." "._CTRLINFO."</div></td><td>".catacess("auth_edit", "sl_form", $auth_edit, 1)."</td></tr>"
	."<tr><td>"._CAN." "._AUTH_DELETE.":<div class=\"sl_small\">"._ACESSI." "._CTRLINFO."</div></td><td>".catacess("auth_delete", "sl_form", $auth_delete, 1)."</td></tr>"
	."<tr><td>"._CAN." "._AUTH_MOD.":<div class=\"sl_small\">"._ACESSI." "._CTRLINFO."</div></td><td>".catacess("auth_mod", "sl_form", $auth_mod, 2)."</td></tr></table>"
	."</div>"
	."<script>
		var countries=new ddtabcontent(\"cat_edits\")
		countries.setpersist(true)
		countries.setselectedClassTarget(\"link\")
		countries.init()
	</script>"
	."<table class=\"sl_table_form\"><tr><td class=\"sl_center\"><input type=\"hidden\" name=\"id\" value=\"".$cid."\"><input type=\"hidden\" name=\"op\" value=\"cat_save\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function cat_info() {
	$modul = isset($_GET['modul']) ? analyze($_GET['modul']) : "forum";
	head();
	echo cat_navi(0, 5, 0, 0, "")."<div id=\"repadm_info\">".adm_info(1, 0, "categories")."</div>";
	foot();
}

switch($op) {
	case "cat_show":
	cat_show();
	break;
	
	case "cat_fix":
	cat_fix();
	break;
	
	case "cat_add":
	cat_add();
	break;
	
	case "cat_sub_add":
	cat_sub_add();
	break;
	
	case "cat_add_edit":
	cat_add_edit();
	break;
	
	case "cat_add_save":
	$modul = $_POST['modul'];
	$title = $_POST['title'];
	$description = $_POST['description'];
	$imgcat = $_POST['imgcat'];
	$language = $_POST['language'];
	$cid = (isset($_POST['cid'])) ? intval($_POST['cid']) : 0;
	$imgcat = str_replace("templates/".$conf['theme']."/images/categories/", "", $imgcat);
	$imgcat = (!$imgcat || $imgcat == "no.png") ? "" : $imgcat;
	$cstatus = intval($_POST['cstatus']);
	list($ordern) = $db->sql_fetchrow($db->sql_query("SELECT ordern FROM ".$prefix."_categories WHERE modul = '".$modul."' ORDER BY ordern DESC"));
	$ordern++;
	$auth_view = (isset($_POST['auth_view'])) ? scatacess($_POST['auth_view']) : "0|0";
	$auth_read = (isset($_POST['auth_read'])) ? scatacess($_POST['auth_read']) : "0|0";
	$auth_post = (isset($_POST['auth_post'])) ? scatacess($_POST['auth_post']) : "0|0";
	$auth_reply = (isset($_POST['auth_reply'])) ? scatacess($_POST['auth_reply']) : "0|0";
	$auth_edit = (isset($_POST['auth_edit'])) ? scatacess($_POST['auth_edit']) : "3|0";
	$auth_delete = (isset($_POST['auth_delete'])) ? scatacess($_POST['auth_delete']) : "3|0";
	$auth_mod = (isset($_POST['auth_mod'])) ? scatacess($_POST['auth_mod']) : "3|0";
	$db->sql_query("INSERT INTO ".$prefix."_categories (id, modul, title, description, img, language, parentid, cstatus, ordern, auth_view, auth_read, auth_post, auth_reply, auth_edit, auth_delete, auth_mod) VALUES (NULL, '".$modul."', '".$title."', '".$description."', '".$imgcat."', '".$language."', '".$cid."', '".$cstatus."', '".$ordern."', '".$auth_view."', '".$auth_read."', '".$auth_post."', '".$auth_reply."', '".$auth_edit."', '".$auth_delete."', '".$auth_mod."')");
	header("Location: ".$admin_file.".php?op=cat_show&modul=".$modul);
	break;
	
	case "cat_edit":
	cat_edit();
	break;
	
	case "cat_save":
	$id = intval($_POST['id']);
	$modul = $_POST['modul'];
	$title = $_POST['title'];
	$description = $_POST['description'];
	$imgcat = $_POST['imgcat'];
	$language = $_POST['language'];
	$parentid = intval($_POST['parentid']);
	$imgcat = str_replace("templates/".$conf['theme']."/images/categories/", "", $imgcat);
	$imgcat = (!$imgcat || $imgcat == "no.png") ? "" : $imgcat;
	$cstatus = intval($_POST['cstatus']);
	$auth_view = (isset($_POST['auth_view'])) ? scatacess($_POST['auth_view']) : "0|0";
	$auth_read = (isset($_POST['auth_read'])) ? scatacess($_POST['auth_read']) : "0|0";
	$auth_post = (isset($_POST['auth_post'])) ? scatacess($_POST['auth_post']) : "0|0";
	$auth_reply = (isset($_POST['auth_reply'])) ? scatacess($_POST['auth_reply']) : "0|0";
	$auth_edit = (isset($_POST['auth_edit'])) ? scatacess($_POST['auth_edit']) : "3|0";
	$auth_delete = (isset($_POST['auth_delete'])) ? scatacess($_POST['auth_delete']) : "3|0";
	$auth_mod = (isset($_POST['auth_mod'])) ? scatacess($_POST['auth_mod']) : "3|0";
	$db->sql_query("UPDATE ".$prefix."_categories SET modul = '".$modul."', title = '".$title."', description = '".$description."', img = '".$imgcat."', language = '".$language."', parentid = '".$parentid."', cstatus = '".$cstatus."', auth_view = '".$auth_view."', auth_read = '".$auth_read."', auth_post = '".$auth_post."', auth_reply = '".$auth_reply."', auth_edit = '".$auth_edit."', auth_delete = '".$auth_delete."', auth_mod = '".$auth_mod."' WHERE id = '".$id."'");
	header("Location: ".$admin_file.".php?op=cat_show&modul=".$modul);
	break;
	
	case "cat_del":
	$id = intval($_GET['id']);
	$db->sql_query("DELETE FROM ".$prefix."_categories WHERE id = '".$id."'");
	$db->sql_query("DELETE FROM ".$prefix."_categories WHERE parentid = '".$id."'");
	referer($admin_file.".php?op=cat_show");
	break;
	
	case "cat_info":
	cat_info();
	break;
}
?>