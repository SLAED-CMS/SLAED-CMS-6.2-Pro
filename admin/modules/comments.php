<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("ADMIN_FILE") || !is_admin_god()) die("Illegal file access");

function comm_navi() {
	panel();
	$narg = func_get_args();
	$ops = array("comm_show", "comm_show&amp;status=1", "comm_conf", "comm_info");
	$lang = array(_HOME, _WAITINGCONT, _PREFERENCES, _INFO);
	return navi_gen(_COMMENTS, "comments.png", "", $ops, $lang, "", "", $narg[0], $narg[1], $narg[2], $narg[3]);
}

function comm_show() {
	head();
	$id = empty($_GET['status']) ? 0 : 1;
	echo comm_navi(0, $id, 0, 0).ashowcom();
	foot();
}

function comm_edit() {
	global $db, $prefix, $admin_file;
	$id = intval($_GET['id']);
	head();
	$cont = comm_navi(0, 0, 0, 0);
	$result = $db->sql_query("SELECT id, modul, comment FROM ".$prefix."_comment WHERE id = '".$id."'");
	list($id, $modul, $com_text) = $db->sql_fetchrow($result);
	$cont .= tpl_eval("open");
	$cont .= "<form name=\"post\" action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_form\">"
	."<tr><td>"._COMMENT.":</td><td>".textarea("1", "comment", $com_text, $modul, "10", _COMMENT, "1")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"id\" value=\"".$id."\"><input type=\"hidden\" name=\"op\" value=\"comm_edit_save\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function comm_edit_save() {
	global $prefix, $db, $admin_file;
	$id = intval($_POST['id']);
	$com_text = save_text($_POST['comment']);
	$db->sql_query("UPDATE ".$prefix."_comment SET comment = '".$com_text."' WHERE id = '".$id."'");
	header("Location: ".$admin_file.".php?op=comm_show");
}

function comm_conf() {
	global $admin_file;
	head();
	$cont = comm_navi(0, 2, 0, 0);
	include("config/config_comments.php");
	$permtest = end_chmod("config/config_comments.php", 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_conf\">"
	."<tr><td>"._C_33.":</td><td><input type=\"number\" name=\"num\" value=\"".$confc['num']."\" class=\"sl_conf\" placeholder=\""._C_33."\" required></td></tr>"
	."<tr><td>"._C_34.":</td><td><input type=\"number\" name=\"anum\" value=\"".$confc['anum']."\" class=\"sl_conf\" placeholder=\""._C_34."\" required></td></tr>"
	."<tr><td>"._C_35.":</td><td><input type=\"number\" name=\"nump\" value=\"".$confc['nump']."\" class=\"sl_conf\" placeholder=\""._C_35."\" required></td></tr>"
	."<tr><td>"._C_36.":</td><td><input type=\"number\" name=\"anump\" value=\"".$confc['anump']."\" class=\"sl_conf\" placeholder=\""._C_36."\" required></td></tr>"
	."<tr><td>"._COMLETTER.":</td><td><input type=\"number\" name=\"letter\" value=\"".$confc['letter']."\" class=\"sl_conf\" placeholder=\""._COMLETTER."\" required></td></tr>"
	."<tr><td>"._CEDITT.":</td><td><input type=\"number\" name=\"edit\" value=\"".intval($confc['edit'] / 60)."\" class=\"sl_conf\" placeholder=\""._CEDITT."\" required></td></tr>"
	."<tr><td>"._CSEND.":</td><td><input type=\"number\" name=\"send\" value=\"".$confc['send']."\" class=\"sl_conf\" placeholder=\""._CSEND."\" required></td></tr>"
	."<tr><td>"._SORT.":</td><td><select name=\"sort\" class=\"sl_conf\">"
	."<option value=\"1\"";
	if ($confc['sort'] == "1") $cont .= " selected";
	$cont .= ">"._ASC."</option>"
	."<option value=\"0\"";
	if ($confc['sort'] == "0") $cont .= " selected";
	$cont .= ">"._DESC."</option>"
	."</select></td></tr>"
	."<tr><td>"._ALLOWANONPOST."</td><td>".com_access("anonpost", $confc['anonpost'], "sl_conf")."</td></tr>"
	."<tr><td>"._NOLINKP.":<div class=\"sl_small\">"._NOAUM."</div></td><td><select name=\"link\" class=\"sl_conf\">"
	."<option value=\"0\"";
	if ($confc['link'] == "0") $cont .= " selected";
	$cont .= ">"._NO."</option>"
	."<option value=\"1\"";
	if ($confc['link'] == "1") $cont .= " selected";
	$cont .= ">"._ANONIMP."</option>"
	."<option value=\"2\"";
	if ($confc['link'] == "2") $cont .= " selected";
	$cont .= ">"._ALLUSER."</option>"
	."</select></td></tr>"
	."<tr><td>"._NOALINKP.":<div class=\"sl_small\">"._NOAUM."</div></td><td><select name=\"alink\" class=\"sl_conf\">"
	."<option value=\"0\"";
	if ($confc['alink'] == "0") $cont .= " selected";
	$cont .= ">"._NO."</option>"
	."<option value=\"1\"";
	if ($confc['alink'] == "1") $cont .= " selected";
	$cont .= ">"._ANONIMP."</option>"
	."<option value=\"2\"";
	if ($confc['alink'] == "2") $cont .= " selected";
	$cont .= ">"._ALLUSER."</option>"
	."</select></td></tr>"
	."<tr><td>"._ADDAMAIL."</td><td>".radio_form($confc['addmail'], "addmail")."</td></tr>"
	."<tr><td>"._VPRIVAT."</td><td>".radio_form($confc['privat'], "privat")."</td></tr>"
	."<tr><td>"._VPROFIL."</td><td>".radio_form($confc['profil'], "profil")."</td></tr>"
	."<tr><td>"._VWEB."</td><td>".radio_form($confc['web'], "web")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"comm_save\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function comm_save() {
	global $admin_file;
	$xnum = (!intval($_POST['num'])) ? 15 : $_POST['num'];
	$xanum = (!intval($_POST['anum'])) ? 15 : $_POST['anum'];
	$xnump = (!intval($_POST['nump'])) ? 5 : $_POST['nump'];
	$xanump = (!intval($_POST['anump'])) ? 5 : $_POST['anump'];
	$xletter = (!intval($_POST['letter'])) ? 50 : $_POST['letter'];
	$xedit = (!intval($_POST['edit'])) ? 600 : $_POST['edit'] * 60;
	$xsend = (!intval($_POST['send'])) ? 30 : $_POST['send'];
	$content = "\$confc = array();\n"
	."\$confc['num'] = \"".$xnum."\";\n"
	."\$confc['anum'] = \"".$xanum."\";\n"
	."\$confc['nump'] = \"".$xnump."\";\n"
	."\$confc['anump'] = \"".$xanump."\";\n"
	."\$confc['letter'] = \"".$xletter."\";\n"
	."\$confc['edit'] = \"".$xedit."\";\n"
	."\$confc['send'] = \"".$xsend."\";\n"
	."\$confc['sort'] = \"".$_POST['sort']."\";\n"
	."\$confc['anonpost'] = \"".$_POST['anonpost']."\";\n"
	."\$confc['link'] = \"".$_POST['link']."\";\n"
	."\$confc['alink'] = \"".$_POST['alink']."\";\n"
	."\$confc['addmail'] = \"".$_POST['addmail']."\";\n"
	."\$confc['privat'] = \"".$_POST['privat']."\";\n"
	."\$confc['profil'] = \"".$_POST['profil']."\";\n"
	."\$confc['web'] = \"".$_POST['web']."\";\n";
	save_conf("config/config_comments.php", $content);
	header("Location: ".$admin_file.".php?op=comm_conf");
}

function comm_info() {
	head();
	echo comm_navi(0, 3, 0, 0)."<div id=\"repadm_info\">".adm_info(1, 0, "comments")."</div>";
	foot();
}

switch($op) {
	case "comm_show":
	comm_show();
	break;
	
	case "comm_edit":
	comm_edit();
	break;
	
	case "comm_edit_save":
	comm_edit_save();
	break;
	
	case "comm_act":
	$id = (isset($_POST['id'])) ? ((isset($_POST['id'])) ? $_POST['id'] : "") : ((isset($_GET['id'])) ? array($_GET['id']) : "");
	if (is_array($id)) {
		foreach ($id as $val) {
			if (intval($val)) {
				list($cid, $mod, $uid, $status) = $db->sql_fetchrow($db->sql_query("SELECT cid, modul, uid, status FROM ".$prefix."_comment WHERE id = '".$val."'"));
				if (!$status && $cid && $mod) {
					$db->sql_query("UPDATE ".$prefix."_comment SET status = '1' WHERE id = '".$val."'");
					numcom($cid, $mod, 0, $uid);
				}
			}
		}
	}
	referer($admin_file.".php?op=comm_show");
	break;
	
	case "comm_del":
	$id = (isset($_POST['id'])) ? ((isset($_POST['id'])) ? $_POST['id'] : "") : ((isset($_GET['id'])) ? array($_GET['id']) : "");
	if (is_array($id)) {
		foreach ($id as $val) {
			if (intval($val)) {
				list($cid, $mod, $uid, $status) = $db->sql_fetchrow($db->sql_query("SELECT cid, modul, uid, status FROM ".$prefix."_comment WHERE id = '".$val."'"));
				if ($cid && $mod) {
					$db->sql_query("DELETE FROM ".$prefix."_comment WHERE id = '".$val."'");
					if ($status) numcom($cid, $mod, 1, $uid);
				}
			}
		}
	}
	referer($admin_file.".php?op=comm_show");
	break;
	
	case "comm_conf":
	comm_conf();
	break;
	
	case "comm_save":
	comm_save();
	break;
	
	case "comm_info":
	comm_info();
	break;
}
?>