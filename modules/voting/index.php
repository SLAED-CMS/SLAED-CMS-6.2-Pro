<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("MODULE_FILE")) {
	header("Location: ../../index.php");
	exit;
}

function voting() {
	global $prefix, $db, $admin_file, $currentlang, $conf, $confv;
	$querylang = ($conf['multilingual'] == 1) ? "(language = '".$currentlang."' OR language = '') AND modul = '' AND date <= now() AND (enddate >= now() AND status = '0' OR status = '1')" : "modul = '' AND date <= now() AND (enddate >= now() AND status = '0' OR status = '1')";
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $confv['num'];
	head($conf['defis']." "._VOTING);
	$cont = tpl_eval("title", _VOTING);
	$result = $db->sql_query("SELECT id, title, answer, date, enddate, comments, acomm, typ FROM ".$prefix."_voting WHERE ".$querylang." ORDER BY id DESC LIMIT ".$offset.", ".$confv['num']);
	if ($db->sql_numrows($result) > 0) {
		$cont .= tpl_eval("voting-home-open", _ID, _TITLE, cutstr(_COMMENTS, 4, 1), cutstr(_VOTES, 3, 1));
		while (list($id, $title, $answer, $date, $enddate, $comm, $acomm, $typ) = $db->sql_fetchrow($result)) {
			$vtitle = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."\" title=\"".$title."\">".cutstr($title, 60)."</a>";
			$comm = ($acomm && $comm) ? $comm : _NO;
			$vote = array_sum(explode("|", $answer));
			$type = ($typ == "1") ? _VOPEN : _VCLOSE;
			$info = _CHNGSTORY.": ".format_time($date, _TIMESTRING)."<br>"._ENDDATE.": ".format_time($enddate, _TIMESTRING)."<br>"._TYPE.": ".$type;
			$admin = (is_moder($conf['name'])) ? add_menu("<a href=\"".$admin_file.".php?op=voting_add&amp;id=".$id."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=voting_delete&amp;id=".$id."&amp;refer=1\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$title."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>", 1) : "";
			$cont .= tpl_func("voting-home", $id, $vtitle, $comm, $vote, _INFO, $info, $admin);
		}
		$cont .= tpl_eval("voting-home-close");
		$cont .= num_article("pagenum", $conf['name'], $confv['num'], "", "id", "_voting", "", $querylang, $confv['nump']);
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function view() {
	global $db, $prefix, $conf, $confv;
	$id = intval($_GET['id']);
	$result = $db->sql_query("SELECT title, acomm FROM ".$prefix."_voting WHERE id = '".$id."' AND modul = '' AND date <= now() AND (enddate >= now() AND status = '0' OR status = '1')");
	if ($db->sql_numrows($result) > 0) {
		list($title, $acomm) = $db->sql_fetchrow($result);
		head($conf['defis']." "._VOTING." ".$conf['defis']." ".$title);
		$cont = tpl_eval("title", _VOTING).tpl_eval("voting-basic", "<div id=\"rep".$conf['name']."\">".avoting_view($id, $conf['name'])."</div>");
		if ($acomm) $cont .= show_com($id, $acomm);
	} else {
		head($conf['defis']." "._VOTING." ".$conf['defis']." "._NO_INFO);
		$cont = tpl_warn("warn", _NO_INFO, "?name=".$conf['name'], 3, "info");
	}
	echo $cont;
	foot();
}

switch($op) {
	default:
	voting();
	break;
	
	case"view":
	view();
	break;
}
?>