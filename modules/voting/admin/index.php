<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined('ADMIN_FILE') || !is_admin_modul('voting')) die('Illegal file access');

function voting_navi() {
	panel();
	$narg = func_get_args();
	$ops = array('voting', 'voting_add', 'voting_conf', 'voting_info');
	$lang = array(_HOME, _ADD, _PREFERENCES, _INFO);
	return navi_gen(_VOTING, 'voting.png', '', $ops, $lang, '', '', $narg[0], $narg[1], $narg[2], $narg[3]);
}

function voting() {
	global $prefix, $db, $admin_file, $conf, $confv;
	head();
	$cont = voting_navi(0, 0, 0, 0);
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $confv['anum'];
	$offset = intval($offset);
	$result = $db->sql_query("SELECT id, modul, date, enddate, title, language, typ FROM ".$prefix."_voting ORDER BY id DESC LIMIT ".$offset.", ".$confv['anum']);
	if ($db->sql_numrows($result) > 0) {
		$cont .= tpl_eval("open");
		$cont .= "<table class=\"sl_table_list_sort\"><thead><tr><th>"._ID."</th><th>"._TITLE."</th>";
		if ($conf['multilingual'] == 1)  $cont .= "<th>"._LANGUAGE."</th>";
		$cont .= "<th>"._MODUL."</th><th class=\"{sorter: false}\">"._STATUS."</th><th class=\"{sorter: false}\">"._FUNCTIONS."</th></tr></thead><tbody>";
		while (list($id, $modul, $date, $enddate, $title, $language, $typ) = $db->sql_fetchrow($result)) {
			if (time() >= strtotime($date) && time() <= strtotime($enddate)) {
				$ad_view = (!$modul) ? "<a href=\"index.php?name=voting&amp;op=view&amp;id=".$id."\" title=\""._MVIEW."\">"._MVIEW."</a>||" : "";
				$active = "1";
			} else {
				$ad_view = "";
				$active = "0";
			}
			$type = ($typ == "1") ? _VOPEN : _VCLOSE;
			$cont .= "<tr><td>".$id."</td>"
			."<td>".title_tip(_CHNGSTORY.": ".format_time($date, _TIMESTRING)."<br>"._ENDDATE.": ".format_time($enddate, _TIMESTRING)."<br>"._TYPE.": ".$type)."<span title=\"".$title."\" class=\"sl_note\">".cutstr($title, 60)."</span></td>";
			if ($conf['multilingual'] == 1) {
				$language = (!$language) ? _ALL : $language;
				$cont .= "<td>".deflang($language)."</td>";
			}
			$mod = ($modul) ? deflmconst($modul) : _NONE;
			$cont .= "<td>".$mod."</td>"
			."<td>".ad_status("", $active)."</td>"
			."<td>".add_menu($ad_view."<a href=\"".$admin_file.".php?op=voting_add&amp;id=".$id."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=voting_delete&amp;id=".$id."&amp;refer=1\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$title."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>")."</td></tr>";
		}
		$cont .= "</tbody></table>";
		$cont .= num_article("pagenum", "", $confv['anum'], "op=voting&amp;", "id", "_voting", "", "", $confv['anump']);
		$cont .= tpl_eval("close");
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function voting_add() {
	global $prefix, $db, $admin_file, $conf, $confv, $stop;
	if (isset($_REQUEST['id'])) {
		$pid = intval($_REQUEST['id']);
		$result = $db->sql_query("SELECT id, modul, title, questions, answer, date, enddate, multi, language, acomm, typ, status FROM ".$prefix."_voting WHERE id = '".$pid."'");
		list($id, $modul, $title, $questions, $answer, $date, $enddate, $multi, $language, $acomm, $typ, $status) = $db->sql_fetchrow($result);
		$questions = explode("|", $questions);
		$answer = explode("|", $answer);
	} else {
		$modul = $_POST['modul'];
		$title = save_text($_POST['title'], 1);
		$questions = $_POST['questions'];
		$answer = $_POST['answer'];
		$date = save_datetime(1, "date");
		$enddate = save_datetime(1, "enddate");
		$multi = intval($_POST['multi']);
		$language = $_POST['language'];
		$acomm = intval($_POST['acomm']);
		$typ = intval($_POST['typ']);
		$status = intval($_POST['status']);
	}
	head();
	$cont = voting_navi(0, 1, 0, 0);
	if ($stop) $cont .= tpl_warn("warn", $stop, "", "", "warn");
	$cont .= ($id) ? tpl_eval("open")."<div id=\"repvoting\">".avoting_view($id, "voting")."</div>".tpl_eval("close") : "";
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_form\">";
	$mname = array("news", "shop");
	$i = 0;
	foreach ($mname as $val) {
		if ($val != "") {
			$sel = ($modul == $val) ? " selected" : "";
			$content .= "<option value=\"".$val."\"".$sel.">".deflmconst($val)."</option>";
		}
	}
	$cont .= "<tr><td>"._MODUL.":</td><td><select name=\"modul\" class=\"sl_form\"><option value=\"\">"._NO."</option>".$content."</select></td></tr>"
	."<tr><td>"._TITLE." / "._POLLTITLE.":</td><td><input type=\"text\" name=\"title\" value=\"".$title."\" class=\"sl_form\" placeholder=\""._TITLE." / "._POLLTITLE."\" required></td></tr>"
	."<tr><td colspan=\"2\">";
	$i = 0;
	while ($i < $confv['answ']) {
		$a = $i + 1;
		$class = ($i != 0 && $questions[$i] == "") ? " class=\"sl_none\"" : "";
		$cont .= "<table id=\"vot".$i."\"".$class."><tr><td><a OnClick=\"HideShow('vot".$a."', 'slide', 'up', 500);\" title=\""._ADD."\" class=\"sl_plus\">"._POLLEACH." - ".$a.":</a></td><td class=\"sl_form\"><input type=\"text\" name=\"questions[]\" value=\"".text_filter($questions[$i])."\" style=\"width: 375px;\" class=\"sl_field\" placeholder=\""._POLLEACH." - ".$a."\"> "._VOTES.": <input type=\"text\" name=\"answer[]\" value=\"".text_filter($answer[$i])."\" style=\"width: 40px;\" class=\"sl_field\" placeholder=\""._VOTES."\"></td></tr></table>";
		$i++;
	}
	$cont .= "</td></tr>"
	."<tr><td>"._CHNGSTORY.":</td><td>".datetime(1, "date", $date, 16, "sl_form")."</td></tr>"
	."<tr><td>"._ENDDATE.":</td><td>".datetime(1, "enddate", $enddate, 16, "sl_form")."</td></tr>"
	."<tr><td>"._AFTEREXPIRATION.":</td><td><select name=\"status\" class=\"sl_form\">"
	."<option value=\"1\"";
	if ($status == "1") $cont .= " selected";
	$cont .= ">"._VCLOSED."</option>"
	."<option value=\"0\"";
	if ($status == "0") $cont .= " selected";
	$cont .= ">"._VDEACT."</option>"
	."</select></td></tr>"
	."<tr><td>"._TYPE.":</td><td><select name=\"typ\" class=\"sl_form\">"
	."<option value=\"1\"";
	if ($typ == "1") $cont .= " selected";
	$cont .= ">"._VOPEN."</option>"
	."<option value=\"0\"";
	if ($typ == "0") $cont .= " selected";
	$cont .= ">"._VCLOSE."</option>"
	."</select></td></tr>";
	if ($conf['multilingual'] == 1) $cont .= "<tr><td>"._LANGUAGE.":</td><td><select name=\"language\" class=\"sl_form\">".language($language)."</select></td></tr>";
	$cont .= "<tr><td>"._COMMENTS.":</td><td>".com_access("acomm", $acomm, "sl_form")."</td></tr>"
	."<tr><td>"._MULTI."</td><td>".radio_form($multi, "multi")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\">".ad_save("id", $id, "voting_save", 1)."</td></tr></table></form>";
	$cont .= tpl_eval("close");
	echo $cont;
	foot();
}

function voting_save() {
	global $prefix, $db, $admin_file, $stop;
	$id = intval($_POST['id']);
	$modul = analyze($_POST['modul']);
	$title = save_text($_POST['title'], 1);
	$questions = $_POST['questions'];
	$answer = $_POST['answer'];
	for ($q = 0; $q < count($questions); $q++) {
		if ($questions[$q] != "") {
			$quest[] = $questions[$q];
			$answ[] = (is_numeric($answer[$q])) ? $answer[$q] : "0";
		}
	}
	$quest = is_array($quest) ? implode("|", $quest) : "";
	$answ = is_array($answ) ? implode("|", $answ) : "";
	$date = save_datetime(1, "date");
	$enddate = save_datetime(1, "enddate");
	$multi = intval($_POST['multi']);
	$language = $_POST['language'];
	$acomm = ($modul) ? "0" : intval($_POST['acomm']);
	$typ = intval($_POST['typ']);
	$status = (!$typ) ? "0" : intval($_POST['status']);
	$stop = array();
	if (!$title) $stop[] = _CERROR;
	if (!$stop && $_POST['posttype'] == "save") {
		if ($id) {
			$db->sql_query("UPDATE ".$prefix."_voting SET modul = '".$modul."', title = '".$title."', questions = '".$quest."', answer = '".$answ."', date = '".$date."', enddate = '".$enddate."', multi = '".$multi."', language = '".$language."', acomm = '".$acomm."', typ = '".$typ."', status = '".$status."' WHERE id = '".$id."'");
		} else {
			$ip = getip();
			$db->sql_query("INSERT INTO ".$prefix."_voting (id, modul, title, questions, answer, date, enddate, multi, language, acomm, ip, typ, status) VALUES (NULL, '".$modul."', '".$title."', '".$quest."', '".$answ."', '".$date."', '".$enddate."', '".$multi."', '".$language."', '".$acomm."', '".$ip."', '".$typ."', '".$status."')");
		}
		header("Location: ".$admin_file.".php?op=voting");
	} elseif ($_POST['posttype'] == "delete") {
		voting_delete($id);
	} else {
		voting_add();
	}
}

function voting_delete() {
	global $prefix, $db, $admin_file, $id;
	$arg = func_get_args();
	$id = ($arg[0]) ? $arg[0] : $id;
	if ($id) {
		$db->sql_query("DELETE FROM ".$prefix."_comment WHERE cid = '".$id."' AND modul = 'voting'");
		$db->sql_query("DELETE FROM ".$prefix."_voting WHERE id = '".$id."'");
	}
	referer($admin_file.".php?op=voting");
}

function voting_conf() {
	global $admin_file, $confv;
	head();
	$cont = voting_navi(0, 2, 0, 0);
	$permtest = end_chmod("config/config_voting.php", 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_conf\">"
	."<tr><td>"._VOTING_TIME.":</td><td><input type=\"number\" name=\"voting_t\" value=\"".intval($confv['voting_t'] / 86400)."\" class=\"sl_conf\" placeholder=\""._VOTING_TIME."\" required></td></tr>"
	."<tr><td>"._C_33.":</td><td><input type=\"number\" name=\"num\" value=\"".$confv['num']."\" class=\"sl_conf\" placeholder=\""._C_33."\" required></td></tr>"
	."<tr><td>"._C_34.":</td><td><input type=\"number\" name=\"anum\" value=\"".$confv['anum']."\" class=\"sl_conf\" placeholder=\""._C_34."\" required></td></tr>"
	."<tr><td>"._C_35.":</td><td><input type=\"number\" name=\"nump\" value=\"".$confv['nump']."\" class=\"sl_conf\" placeholder=\""._C_35."\" required></td></tr>"
	."<tr><td>"._C_36.":</td><td><input type=\"number\" name=\"anump\" value=\"".$confv['anump']."\" class=\"sl_conf\" placeholder=\""._C_36."\" required></td></tr>"
	."<tr><td>"._VANSW.":</td><td><input type=\"number\" name=\"answ\" value=\"".$confv['answ']."\" class=\"sl_conf\" placeholder=\""._VANSW."\" required></td></tr>"
	."<tr><td>"._VBLOCK.":</td><td><select name=\"block\" class=\"sl_conf\">"
	."<option value=\"0\"";
	if ($confv['block'] == "0") $cont .= " selected";
	$cont .= ">"._VLASTACT."</option>"
	."<option value=\"1\"";
	if ($confv['block'] == "1") $cont .= " selected";
	$cont .= ">"._VLASTCLO."</option>"
	."<option value=\"2\"";
	if ($confv['block'] == "2") $cont .= " selected";
	$cont .= ">"._VRANACT."</option>"
	."<option value=\"3\"";
	if ($confv['block'] == "3") $cont .= " selected";
	$cont .= ">"._VRANCLO."</option>"
	."</select></td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"voting_conf_save\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close");
	echo $cont;
	foot();
}

function voting_conf_save() {
	global $admin_file;
	$xvoting_t = (!$_POST['voting_t']) ? 86400 : intval($_POST['voting_t'] * 86400);
	$content = "\$confv = array();\n"
	."\$confv['voting_t'] = \"".$xvoting_t."\";\n"
	."\$confv['num'] = \"".$_POST['num']."\";\n"
	."\$confv['anum'] = \"".$_POST['anum']."\";\n"
	."\$confv['nump'] = \"".$_POST['nump']."\";\n"
	."\$confv['anump'] = \"".$_POST['anump']."\";\n"
	."\$confv['answ'] = \"".$_POST['answ']."\";\n"
	."\$confv['block'] = \"".$_POST['block']."\";\n";
	save_conf("config/config_voting.php", $content);
	header("Location: ".$admin_file.".php?op=voting_conf");
}

function voting_info() {
	head();
	echo voting_navi(0, 3, 0, 0)."<div id=\"repadm_info\">".adm_info(1, "voting", 0)."</div>";
	foot();
}

switch($op) {
	case "voting":
	voting();
	break;
	
	case "voting_add":
	voting_add();
	break;
	
	case "voting_save":
	voting_save();
	break;
	
	case "voting_delete":
	voting_delete();
	break;
	
	case "voting_conf":
	voting_conf();
	break;
	
	case "voting_conf_save":
	voting_conf_save();
	break;
	
	case "voting_info":
	voting_info();
	break;
}
?>