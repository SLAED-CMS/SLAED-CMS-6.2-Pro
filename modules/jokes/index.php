<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("MODULE_FILE")) {
	header("Location: ../../index.php");
	exit;
}
get_lang($conf['name']);
include("config/config_jokes.php");

function navigate($title, $cat="") {
	global $conf, $confj;
	$fcat = (isset($_GET['cat'])) ? intval($_GET['cat']) : 0;
	$catf = ($fcat) ? "&amp;cat=".$fcat : "";
	$home = "<a href=\"index.php?name=".$conf['name']."\" title=\""._HOME."\" class=\"sl_but_navi\">"._HOME."</a>";
	$best = ($confj['rate']) ? "<a href=\"index.php?name=".$conf['name'].$catf."&amp;sort=1\" title=\""._BEST."\" class=\"sl_but_navi\">"._BEST."</a>" : "";
	$pop = ($confj['rate']) ? "<a href=\"index.php?name=".$conf['name'].$catf."&amp;sort=2\" title=\""._POP."\" class=\"sl_but_navi\">"._POP."</a>" : "";
	$add = ((is_user() && $confj['add'] == 1) || (!is_user() && $confj['addquest'] == 1)) ? "<a href=\"index.php?name=".$conf['name']."&amp;op=add\" title=\""._ADD."\" class=\"sl_but_navi\">"._ADD."</a>" : "";
	$catshow = ($cat) ? "<a OnClick=\"CloseOpen('sl_close_1', 1);\" title=\""._CATVORH."\" class=\"sl_but_navi\">"._CATEGORIES."</a>" : "";
	return tpl_eval("navi", $title, $conf['name'], "", $home, $best, $pop, "", $add, $catshow);
}

function jokes() {
	global $prefix, $db, $admin_file, $user, $conf, $confu, $confj, $home;
	$cwhere = catmids($conf['name'], "j.cat");
	$word = (isset($_GET['word'])) ? text_filter($_GET['word']) : "";
	$jokenum = user_news($user[3], $confj['num']);
	$sort = (isset($_GET['sort'])) ? intval($_GET['sort']) : 0;
	$jcat = (isset($_GET['cat'])) ? intval($_GET['cat']) : 0;
	if (!$jcat && $sort && $confj['rate']) {
		$caton = 0;
		$field = "sort=".$sort."&amp;";
		if ($sort == 1) {
			$orderby = "j.rating DESC";
			$jokes_logo = _BEST;
		} else {
			$orderby = "j.ratingtot DESC";
			$jokes_logo = _POP;
		}
		$order = "WHERE j.date <= now() AND j.status != '0' ".$cwhere." ORDER BY ".$orderby;
		$ordernum = "date <= now() AND status != '0'";
		$pagetitle = $conf['defis']." "._JOKES." ".$conf['defis']." ".$jokes_logo;
	} elseif ($jcat) {
		$field = ($sort) ? "cat=".$jcat."&amp;sort=".$sort."&amp;" : "cat=".$jcat."&amp;";
		$orderby = ($sort) ? (($sort == 1) ? "j.rating DESC" : "j.ratingtot DESC") : "j.date DESC";
		$jokes_logo = ($sort) ? (($sort == 1) ? " ".$conf['defis']." "._BEST : " ".$conf['defis']." "._POP) : "";
		list($cat_title) = $db->sql_fetchrow($db->sql_query("SELECT title FROM ".$prefix."_categories WHERE id = '".$jcat."'"));
		list($caid) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_categories WHERE parentid = '".$jcat."'"));
		$caton = ($caid) ? 1 : 0;
		$order = "WHERE j.cat = '".$jcat."' AND j.date <= now() AND j.status != '0' ".$cwhere." ORDER BY ".$orderby;
		$ordernum = "cat = '".$jcat."' AND date <= now() AND status != '0'";
		$pagetitle = $conf['defis']." "._JOKES." ".$conf['defis']." ".$cat_title.$jokes_logo;
	} else {
		$caton = 1;
		$field = "";
		$order = "WHERE j.date <= now() AND j.status != '0' ".$cwhere." ORDER BY j.date DESC";
		$ordernum = "date <= now() AND status != '0'";
		$jokes_logo = _JOKES;
		$pagetitle = $conf['defis']." ".$jokes_logo;
	}
	head($pagetitle);
	if (!$home || ($home && $confj['homcat'])) {
		$cont = ($jcat) ? navigate($cat_title, $caton) : navigate($jokes_logo, $caton);
		if ($jcat) $cont .= tpl_eval("cat-navi", catlink($conf['name'], $jcat, $confj['defis'], _JOKES));
		if ($caton == 1) $cont .= categories($conf['name'], $confj['tabcol'], $confj['subcat'], $confj['catdesc'], $jcat);
	}
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $jokenum;
	$offset = intval($offset);
	$result = $db->sql_query("SELECT j.jokeid, j.name, j.date, j.title, j.cat, j.joke, j.rating, j.ratingtot, c.title, c.description, c.img, u.user_name FROM ".$prefix."_jokes AS j LEFT JOIN ".$prefix."_categories AS c ON (j.cat=c.id) LEFT JOIN ".$prefix."_users AS u ON (j.uid=u.user_id) ".$order." LIMIT ".$offset.", ".$jokenum);
	if ($db->sql_numrows($result) > 0) {
		while (list($id, $uname, $time, $jtitle, $catid, $joke, $rating, $ratingtot, $ctitle, $cdesc, $cimg, $user_name) = $db->sql_fetchrow($result)) {
			$title = "<a href=\"#".$id."\" title=\"".$jtitle."\">".search_color($jtitle, $word)."</a> ".new_graphic($time);
			$post = ($user_name) ? user_info($user_name) : (($uname) ? $uname : $confu['anonym']);
			$post = "<span title=\""._POSTEDBY."\" class=\"sl_post\">".$post."</span>";
			$date = ($confj['date']) ? "<span title=\""._CHNGSTORY."\" class=\"sl_date\">".format_time($time)."</span>" : "";
			$cdesc = ($cdesc) ? $cdesc : $ctitle;
			$ctitle = ($ctitle) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_cat\">".cutstr($ctitle, 15)."</a>" : "";
			$cimg = ($cimg) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_icat\"><img src=\"".img_find("categories/".$cimg)."\" alt=\"".$cdesc."\" title=\"".$cdesc."\"></a>" : "";
			$arating = ajax_rating(1, $id, $conf['name'], $ratingtot, $rating, "");
			$admin = (is_moder($conf['name'])) ? add_menu("<a href=\"".$admin_file.".php?op=jokes_add&amp;id=".$id."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=jokes_delete&amp;id=".$id."&amp;refer=1\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$jtitle."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>") : "";
			$cont .= tpl_func("basic", $catid, $cimg, $ctitle, $id, $title, search_color(bb_decode($joke, $conf['name']), $word), "", $post, $date, "", "", "", $arating, $admin);
		}
		$cont .= num_article("pagenum", $conf['name'], $jokenum, $field, "jokeid", "_jokes", "cat", $ordernum, $confj['nump']);
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function add() {
	global $db, $prefix, $user, $conf, $confu, $confj, $stop;
	if ($confj['add'] == "1") {
		head($conf['defis']." "._JOKES." ".$conf['defis']." "._ADD, _ADD_JNOTE);
		$cont = navigate(_ADD);
		if ($stop) $cont .= tpl_warn("warn", $stop, "", "", "warn");
		$title = save_text($_POST['title'], 1);
		$cid = intval($_POST['cid']);
		$joke = save_text($_POST['joke']);
		$postname = text_filter(substr($_POST['postname'], 0, 25));
		if ($joke) $cont .= preview($title, $joke, "", "", "all");
		$cont .= tpl_warn("warn", _ADD_JNOTE, "", "", "info");
		$cont .= tpl_eval("open");
		$cont .= "<form name=\"post\" action=\"index.php?name=".$conf['name']."\" method=\"post\"><table class=\"sl_table_form\">";
		if (is_user()) {
			$cont .= "<tr><td>"._YOURNAME.":</td><td>".text_filter(substr($user[1], 0, 25))."</td></tr>";
		} else {
			$postname = ($postname) ? $postname : $confu['anonym'];
			$cont .= "<tr><td>"._YOURNAME.":</td><td><input type=\"text\" name=\"postname\" value=\"".$postname."\" class=\"sl_field ".$conf['style']."\" placeholder=\""._YOURNAME."\" required></td></tr>";
		}
		$cont .= "<tr><td>"._JTITLE.":</td><td><input type=\"text\" name=\"title\" value=\"".$title."\" maxlength=\"100\" class=\"sl_field ".$conf['style']."\" placeholder=\""._JTITLE."\" required></td></tr>"
		."<tr><td>"._CATEGORY.":</td><td>".getcat($conf['name'], $cid, "cid", $conf['style'], "<option value=\"\">"._HOMECAT."</option>")."</td></tr>"
		."<tr><td>"._JOKE.":</td><td>".textarea("1", "joke", $joke, $conf['name'], "10", _JOKE, "1")."</td></tr>"
		.captcha_random()
		."<tr><td colspan=\"2\" class=\"sl_center\">".ad_save("", "", "send")."</td></tr></table></form>";
		$cont .= tpl_eval("close");
		echo $cont;
		foot();
	} else {
		header("Location: index.php?name=".$conf['name']);
	}
}

function send() {
	global $prefix, $db, $user, $conf, $confj, $stop;
	if ($confj['add'] == "1") {
		$postname = text_filter(substr($_POST['postname'], 0, 25));
		$title = save_text($_POST['title'], 1);
		$cid = intval($_POST['cid']);
		$joke = save_text($_POST['joke']);
		$stop = array();
		if (!$title) $stop[] = _CERROR;
		if (!$joke) $stop[] = _CERROR1;
		if (!$postname && !is_user()) $stop[] = _CERROR3;
		if (captcha_check()) $stop[] = _SECCODEINCOR;
		if ($db->sql_numrows($db->sql_query("SELECT title FROM ".$prefix."_jokes WHERE title = '".$title."'")) > 0) $stop[] = _JOKEEXIST;
		if (!$stop && $_POST['posttype'] == "save") {
			$postid = (is_user()) ? intval($user[0]) : "";
			$uname = (!is_user()) ? $postname : "";
			$ip = getip();
			$db->sql_query("INSERT INTO ".$prefix."_jokes (jokeid, uid, name, date, title, cat, joke, ip_sender, status) VALUES (NULL, '".$postid."', '".$uname."', now(), '".$title."', '".$cid."', '".$joke."', '".$ip."', '0')");
			update_points(19);
			$puname = (is_user()) ? $user[1] : $postname;
			addmail($confj['addmail'], $conf['name'], $puname, _JOKES);
			head($conf['defis']." "._JOKES." ".$conf['defis']." "._ADD, _UPLOADFINISHJ);
			echo navigate(_ADD).tpl_warn("warn", _UPLOADFINISHJ, "?name=".$conf['name'], 10, "info");
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
	jokes();
	break;
	
	case "add":
	add();
	break;
	
	case "send":
	send();
	break;
}
?>