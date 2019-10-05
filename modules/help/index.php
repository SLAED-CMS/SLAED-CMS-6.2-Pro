<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("MODULE_FILE")) {
	header("Location: ../../index.php");
	exit;
}
get_lang($conf['name']);
include("config/config_help.php");

function navigate($title, $cat="") {
	global $conf, $confh;
	$scat = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
	$catf = ($scat) ? "&amp;cat=".$scat : "";
	$home = "<a href=\"index.php?name=".$conf['name']."\" title=\""._HELP."\" class=\"sl_but_navi\">"._HOME."</a>";
	$best = "<a href=\"index.php?name=".$conf['name'].$catf."&amp;sort=1\" title=\""._GLOSED."\" class=\"sl_but_navi\">"._GLOSED."</a>";
	$pop = "<a href=\"index.php?name=".$conf['name'].$catf."&amp;sort=2\" title=\""._POP."\" class=\"sl_but_navi\">"._POP."</a>";
	$liste = "<a href=\"index.php?name=".$conf['name']."&amp;op=liste\" title=\""._LIST."\" class=\"sl_but_navi\">"._LIST."</a>";
	$add = ($confh['add'] == 1) ? "<a href=\"index.php?name=".$conf['name']."&amp;op=add\" title=\""._ADD."\" class=\"sl_but_navi\">"._ADD."</a>" : "";
	$catshow = ($cat) ? "<a OnClick=\"CloseOpen('sl_close_1', 1);\" title=\""._CATVORH."\" class=\"sl_but_navi\">"._CATEGORIES."</a>" : "";
	return navi().tpl_eval("navi", $title, $conf['name'], "", $home, $best, $pop, $liste, $add, $catshow);
}

function help() {
	global $prefix, $db, $admin_file, $user, $conf, $confu, $confh, $home;
	$cwhere = catmids($conf['name'], "s.catid");
	$uid = intval($user[0]);
	$newnum = user_news($user[3], $confh['num']);
	$sort = isset($_GET['sort']) ? intval($_GET['sort']) : 0;
	$scat = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
	if (!$scat && $sort) {
		$caton = 0;
		$field = "sort=".$sort."&amp;";
		if ($sort == 1) {
			$order = "WHERE s.status != '0' AND s.pid = '0' AND s.uid = '".$uid."' AND s.time <= now() ".$cwhere." ORDER BY s.time DESC";
			$ordernum = "pid = '0' AND uid = '".$uid."' AND time <= now() AND status != '0'";
			$news_logo = _GLOSED;
		} else {
			$order = "WHERE s.pid = '0' AND s.uid = '".$uid."' AND s.time <= now() ".$cwhere." ORDER BY s.counter DESC";
			$ordernum = "pid = '0' AND uid = '".$uid."' AND time <= now()";
			$news_logo = _POP;
		}
		$pagetitle = $conf['defis']." "._HELPINFO." ".$conf['defis']." ".$news_logo;
	} elseif ($scat) {
		$field = ($sort) ? "cat=".$scat."&amp;sort=".$sort."&amp;" : "cat=".$scat."&amp;";
		list($cat_title, $cat_description) = $db->sql_fetchrow($db->sql_query("SELECT title, description FROM ".$prefix."_categories WHERE id = '".$scat."'"));
		list($caid) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_categories WHERE parentid = '".$scat."'"));
		$caton = ($caid) ? 1 : 0;
		if ($sort == 1) {
			$order = "WHERE s.status != '0' AND s.pid = '0' AND s.uid = '".$uid."' AND s.catid = '".$scat."' AND s.time <= now() ".$cwhere." ORDER BY s.time DESC";
			$ordernum = "pid = '0' AND uid = '".$uid."' AND catid = '".$scat."' AND time <= now() AND status != '0'";
			$news_logo = " ".$conf['defis']." "._GLOSED;
		} elseif ($sort == 2) {
			$order = "WHERE s.pid = '0' AND s.uid = '".$uid."' AND s.catid = '".$scat."' AND s.time <= now() ".$cwhere." ORDER BY s.counter DESC";
			$ordernum = "pid = '0' AND uid = '".$uid."' AND catid = '".$scat."' AND time <= now()";
			$news_logo = " ".$conf['defis']." "._POP;
		} else {
			$order = "WHERE s.pid = '0' AND s.uid = '".$uid."' AND s.catid = '".$scat."' AND s.time <= now() ".$cwhere." ORDER BY s.time DESC";
			$ordernum = "pid = '0' AND uid = '".$uid."' AND catid = '".$scat."' AND time <= now()";
			$news_logo = "";
		}
		$pagetitle = $conf['defis']." "._HELPINFO." ".$conf['defis']." ".$cat_title.$news_logo;
	} else {
		$caton = 1;
		$field = "";
		$order = "WHERE s.pid = '0' AND s.uid = '".$uid."' AND s.time <= now() ".$cwhere." ORDER BY s.time DESC";
		$ordernum = "pid = '0' AND uid = '".$uid."' AND time <= now()";
		$news_logo = _HELPINFO;
		$pagetitle = $conf['defis']." ".$news_logo;
	}
	head($pagetitle);
	if (!$home) {
		$cont = ($scat) ? navigate($cat_title, $caton) : navigate($news_logo, $caton);
		if ($scat) $cont .= tpl_eval("cat-navi", catlink($conf['name'], $scat, $confh['defis'], _HELP));
		if ($caton == 1) $cont .= categories($conf['name'], $confh['tabcol'], $confh['subcat'], $confh['catdesc'], $scat);
	}
	$num = isset($_GET['num']) ? intval($_GET['num']) : 1;
	$offset = ($num-1) * $newnum;
	$offset = intval($offset);
	$result = $db->sql_query("SELECT s.sid, s.catid, s.title, s.time, s.hometext, s.comments, s.counter, c.title, c.description, c.img FROM ".$prefix."_help AS s LEFT JOIN ".$prefix."_categories AS c ON (s.catid = c.id) ".$order." LIMIT ".$offset.", ".$newnum);
	if ($db->sql_numrows($result) > 0) {
		while (list($id, $catid, $stitle, $time, $hometext, $comm, $counter, $ctitle, $cdesc, $cimg) = $db->sql_fetchrow($result)) {
			$cdesc = ($cdesc) ? $cdesc : $ctitle;
			$ctitle = ($ctitle) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_cat\">".cutstr($ctitle, 15)."</a>" : "";
			$cimg = ($cimg) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_icat\"><img src=\"".img_find("categories/".$cimg)."\" alt=\"".$cdesc."\" title=\"".$cdesc."\"></a>" : "";
			$title = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."\" title=\"".$stitle."\">".$stitle."</a> ".new_graphic($time);
			$read = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."\" title=\"".$stitle."\" class=\"sl_but_read\">"._READMORE."</a>";
			$date = ($confh['date']) ? "<span title=\""._CHNGSTORY."\" class=\"sl_date\">".format_time($time)."</span>" : "";
			$reads = ($confh['read']) ? "<span title=\""._READS."\" class=\"sl_views\">".$counter."</span>" : "";
			$comm = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."#".$id."\" title=\""._MESSAGES."\" class=\"sl_coms\">".$comm."</a>";
			$cont .= tpl_func("basic", $catid, $cimg, $ctitle, $id, $title, bb_decode($hometext, $conf['name']), $read, "", $date, $reads, "", $comm, "", "");
		}
		$cont .= num_article("pagenum", $conf['name'], $newnum, $field, "sid", "_help", "catid", $ordernum, $confh['nump']);
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function liste() {
	global $prefix, $db, $user, $conf, $confu, $confh;
	$cwhere = catmids($conf['name'], "s.catid");
	$uid = intval($user[0]);
	$listnum = intval($confh['listnum']);
	$let = isset($_GET['let']) ? mb_substr($_GET['let'], 0, 1, "utf-8") : "";
	if ($let) {
		$field = "op=liste&amp;let=".urlencode($let)."&amp;";
		$pagetitle = $conf['defis']." "._HELPINFO." ".$conf['defis']." "._LIST." ".$conf['defis']." ".$let;
		$order = "WHERE UCASE(s.title) LIKE BINARY '".$let."%' AND s.time <= now() AND s.pid = '0' AND s.uid = '".$uid."'";
	} else {
		$field = "op=liste&amp;";
		$pagetitle = $conf['defis']." "._HELPINFO." ".$conf['defis']." "._LIST;
		$order = "WHERE s.time <= now() AND s.pid = '0' AND s.uid = '".$uid."'";
	}
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $listnum;
	$offset = intval($offset);
	$result = $db->sql_query("SELECT s.sid, s.catid, s.title, s.time, s.status, c.title FROM ".$prefix."_help AS s LEFT JOIN ".$prefix."_categories AS c ON (s.catid = c.id) ".$order." ".$cwhere." ORDER BY s.time DESC LIMIT ".$offset.", ".$listnum);
	head($pagetitle);
	$cont = navigate(_LIST);
	if ($db->sql_numrows($result) > 0) {
		$letter = ($confh['letter']) ? letter($conf['name']) : "";
		$cont .= tpl_eval("liste-open", $letter, _ID, _TITLE, _CATEGORY, _STATUS, _DATE);
		while (list($id, $catid, $title, $time, $status, $ctitle) = $db->sql_fetchrow($result)) {
			$title = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."\" title=\"".$title."\">".cutstr($title, 40)."</a> ".new_graphic($time);
			$ctitle = ($ctitle) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$ctitle."\">".cutstr($ctitle, 15)."</a>" : _NO;
			$status = ($status) ? 0 : 1;
			$cont .= tpl_func("liste-basic", $id, $title, $ctitle, ad_status("", $status), format_time($time));
		}
		$cont .= tpl_eval("liste-close");
		$ordernum = ($let) ? "title LIKE BINARY '".$let."%' AND time <= now() AND pid = '0' AND uid = '".$uid."'" : "time <= now() AND pid = '0' AND uid = '".$uid."'";
		$cont .= num_article("pagenum", $conf['name'], $listnum, $field, "sid", "_help", "catid", $ordernum, $confh['nump']);
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function view() {
	global $prefix, $db, $admin_file, $user, $conf, $confu, $confh;
	$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
	$word = isset($_GET['word']) ? text_filter($_GET['word']) : "";
	$uid = intval($user[0]);
	$cwhere = catmids($conf['name'], "s.catid");
	$result = $db->sql_query("SELECT s.sid, s.pid, s.catid, s.uid, s.aid, s.title, s.time, s.hometext, s.field, s.counter, s.score, s.ratings, s.status, c.title, c.description, c.img, u.user_name FROM ".$prefix."_help AS s LEFT JOIN ".$prefix."_categories AS c ON (s.catid = c.id) LEFT JOIN ".$prefix."_users AS u ON (s.aid = u.user_id) WHERE (s.sid = '".$id."' OR s.pid = '".$id."') AND s.uid = '".$uid."' AND s.time <= now() ".$cwhere." ORDER BY s.time ASC");
	if ($db->sql_numrows($result) > 0) {
		$db->sql_query("UPDATE ".$prefix."_help SET counter = counter+1 WHERE sid = '".$id."'");
		head($conf['defis']." "._HELPINFO);
		$cont = navigate(_HELPINFO);
		$a = 0;
		while (list($hid, $pid, $catid, $huid, $haid, $title, $time, $hometext, $field, $counter, $score, $ratings, $status, $ctitle, $cdesc, $cimg, $user_name) = $db->sql_fetchrow($result)) {
			$title = ($title) ? search_color($title, $word) : _MESSAGE.": ".$a;
			$fields = fields_out($field, $conf['name']);
			$fields = ($fields) ? "<br><br>".$fields : "";
			$text = $hometext.$fields;
			$post = ($user_name) ? user_info($user_name) : $confu['anonym'];
			$post = "<span title=\""._POSTEDBY."\" class=\"sl_post\">".$post."</span>";
			$date = ($confh['date']) ? "<span title=\""._CHNGSTORY."\" class=\"sl_date\">".format_time($time)."</span>" : "";
			$arating = ($haid && $huid != $haid) ? ajax_rating(1, $hid, $conf['name'], $ratings, $score, "") : "";
			if (!$pid) {
				$reads = "<span title=\""._READS."\" class=\"sl_views\">".$counter."</span>";
				$cdesc = ($cdesc) ? $cdesc : $ctitle;
				$ctitle = ($ctitle) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_cat\">".cutstr($ctitle, 15)."</a>" : "";
				$cimg = ($cimg) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_icat\"><img src=\"".img_find("categories/".$cimg)."\" alt=\"".$cdesc."\" title=\"".$cdesc."\"></a>" : "";
				$favorites = favorview($hid, $conf['name']);
				$goback = "<span OnClick=\"javascript:history.go(-1);\" title=\""._BACK."\" class=\"sl_but_back\">"._BACK."</span>";
			} else {
				$reads = "";
				$ctitle = "";
				$cimg = "";
				$favorites = "";
				$goback = "";
			}
			$cont .= tpl_func("basic", $catid, $cimg, $ctitle, $hid, $title, search_color(bb_decode($text, $conf['name']), $word), "", $post, $date, $reads, "", "", $arating, "", $favorites, $goback);
			$a++;
		}
		$cont .= add_view($id);
		echo $cont;
		foot();
	} else {
		header("Location: index.php?name=".$conf['name']);
	}
}

function add_view($id) {
	global $prefix, $db, $conf, $confh;
	if ((is_user() && $confh['add'] == 1)) {
		$result = $db->sql_query("SELECT catid, status FROM ".$prefix."_help WHERE sid = '".$id."'");
		list($hcatid, $status) = $db->sql_fetchrow($result);
		$cont = tpl_eval("open");
		$cont .= "<form name=\"post\" action=\"index.php?name=".$conf['name']."\" method=\"post\"><table class=\"sl_table_form\">"
		."<tr><td>"._TEXT.":</td><td>".textarea("1", "hometext", "", $conf['name'], "10", _TEXT, "1")."</td></tr>"
		."<tr><td>"._HELPGLOS."</td><td>".radio_form($status, "status")."</td></tr>"
		."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"pid\" value=\"".$id."\"><input type=\"hidden\" name=\"catid\" value=\"".$hcatid."\"><input type=\"hidden\" name=\"posttype\" value=\"save\"><input type=\"hidden\" name=\"op\" value=\"send\"><input type=\"submit\" value=\""._SEND."\" class=\"sl_but_blue\"></td></tr></table></form>";
		$cont .= tpl_eval("close");
		return $cont;
	}
}

function add() {
	global $prefix, $db, $conf, $confh, $confu, $stop;
	if ((is_user() && $confh['add'] == 1)) {
		head($conf['defis']." "._HELPINFO." ".$conf['defis']." "._ADD, _HSUBMIT);
		$cont = navigate(_ADD);
		if ($stop) $cont .= tpl_warn("warn", $stop, "", "", "warn");
		$title = save_text($_POST['title'], 1);
		$catid = intval($_POST['catid']);
		$hometext = save_text($_POST['hometext']);
		$field = fields_save($_POST['field']);
		if ($hometext) $cont .= preview($title, $hometext, "", $field, $conf['name']);
		$cont .= tpl_warn("warn", _HSUBMIT, "", "", "info");
		$cont .= tpl_eval("open");
		$cont .= "<form name=\"post\" action=\"index.php?name=".$conf['name']."\" method=\"post\"><table class=\"sl_table_form\">"
		."<tr><td>"._TITLE.":</td><td><input type=\"text\" name=\"title\" value=\"".$title."\" maxlength=\"100\" class=\"sl_field ".$conf['style']."\" placeholder=\""._TITLE."\" required></td></tr>"
		."<tr><td>"._CATEGORY.":</td><td>".getcat($conf['name'], $catid, "catid", $conf['style'], "<option value=\"\">"._HOMECAT."</option>")."</td></tr>"
		."<tr><td>"._TEXT.":</td><td>".textarea("1", "hometext", $hometext, $conf['name'], "10", _TEXT, "1")."</td></tr>"
		.fields_in($field, $conf['name'])
		."<tr><td colspan=\"2\" class=\"sl_center\">".ad_save("", "", "send")."</td></tr></table></form>";
		$cont .= tpl_eval("close");
		echo $cont;
		foot();
	} else {
		header("Location: index.php?name=".$conf['name']);
	}
}

function send() {
	global $prefix, $db, $user, $conf, $confh, $stop;
	if ((is_user() && $confh['add'] == 1)) {
		$pid = intval($_POST['pid']);
		$status = ($pid) ? intval($_POST['status']) : "0";
		$title = save_text($_POST['title'], 1);
		$hometext = save_text($_POST['hometext']);
		$field = fields_save($_POST['field']);
		$catid = intval($_POST['catid']);
		$stop = array();
		if (!$title && !$pid) $stop[] = _CERROR;
		if (!$hometext && !$pid) $stop[] = _CERROR1;
		if (!$stop && $_POST['posttype'] == "save") {
			$postid = intval($user[0]);
			$ip = getip();
			$db->sql_query("INSERT INTO ".$prefix."_help (sid, pid, catid, uid, aid, title, time, hometext, field, ip_sender, status) VALUES (NULL, '".$pid."', '".$catid."', '".$postid."', '".$postid."', '".$title."', now(), '".$hometext."', '".$field."', '".$ip."', '0')");
			if ($pid) $db->sql_query("UPDATE ".$prefix."_help SET comments = comments+1, status = '".$status."' WHERE sid = '".$pid."'");
			$puname = (is_user()) ? $user[1] : "";
			addmail($confh['addmail'], $conf['name'], $puname, _HELP);
			head($conf['defis']." "._HELPINFO." ".$conf['defis']." "._ADD, _HSUBTEXT);
			echo navigate(_ADD).tpl_warn("warn", _HSUBTEXT, "?name=".$conf['name'], 10, "info");
			foot();
		} else {
			add();
		}
	} else {
		header("Location: index.php?name=".$conf['name']);
	}
}

switch($op) {
	default:
	help();
	break;
	
	case "liste":
	liste();
	break;
	
	case "view":
	view();
	break;
	
	case "add":
	add();
	break;
	
	case "send":
	send();
	break;
}
?>