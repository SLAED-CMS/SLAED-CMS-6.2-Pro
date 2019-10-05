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
include("config/config_faq.php");

function navigate($title, $cat="") {
	global $conf, $conffa;
	$scat = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
	$catf = ($scat) ? "&amp;cat=".$scat : "";
	$home = "<a href=\"index.php?name=".$conf['name']."\" title=\""._FAQ."\" class=\"sl_but_navi\">"._HOME."</a>";
	$best = ($conffa['rate']) ? "<a href=\"index.php?name=".$conf['name'].$catf."&amp;sort=1\" title=\""._BEST."\" class=\"sl_but_navi\">"._BEST."</a>" : "";
	$pop = ($conffa['rate']) ? "<a href=\"index.php?name=".$conf['name'].$catf."&amp;sort=2\" title=\""._POP."\" class=\"sl_but_navi\">"._POP."</a>" : "";
	$liste = "<a href=\"index.php?name=".$conf['name']."&amp;op=liste\" title=\""._LIST."\" class=\"sl_but_navi\">"._LIST."</a>";
	$add = ((is_user() && $conffa['add'] == 1) || (!is_user() && $conffa['addquest'] == 1)) ? "<a href=\"index.php?name=".$conf['name']."&amp;op=add\" title=\""._ADD."\" class=\"sl_but_navi\">"._ADD."</a>" : "";
	$catshow = ($cat) ? "<a OnClick=\"CloseOpen('sl_close_1', 1);\" title=\""._CATVORH."\" class=\"sl_but_navi\">"._CATEGORIES."</a>" : "";
	return tpl_eval("navi", $title, $conf['name'], "", $home, $best, $pop, $liste, $add, $catshow);
}

function faq() {
	global $prefix, $db, $admin_file, $user, $conf, $confu, $conffa, $home;
	$cwhere = catmids($conf['name'], "s.catid");
	$newnum = user_news($user[3], $conffa['num']);
	$sort = isset($_GET['sort']) ? intval($_GET['sort']) : 0;
	$scat = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
	$word = getVar('get', 'word', 'word');
	if (!$scat && $sort && $conffa['rate']) {
		$caton = 0;
		$field = "sort=".$sort."&amp;";
		if ($sort == 1) {
			$orderby = "s.score DESC";
			$page_logo = _BEST;
		} else {
			$orderby = "s.counter DESC";
			$page_logo = _POP;
		}
		$order = "WHERE s.time <= now() AND s.status != '0' ".$cwhere." ORDER BY ".$orderby;
		$ordernum = "time <= now() AND status != '0'";
		$pagetitle = $conf['defis']." "._FAQ." ".$conf['defis']." ".$page_logo;
	} elseif ($scat) {
		$field = ($sort) ? "cat=".$scat."&amp;sort=".$sort."&amp;" : "cat=".$scat."&amp;";
		$orderby = ($sort) ? (($sort == 1) ? "s.score DESC" : "s.counter DESC") : "s.time DESC";
		$orderbyf = ($sort) ? (($sort == 1) ? "score DESC" : "counter DESC") : "time DESC";
		$page_logo = ($sort) ? (($sort == 1) ? " ".$conf['defis']." "._BEST : " ".$conf['defis']." "._POP) : "";
		list($cat_title) = $db->sql_fetchrow($db->sql_query("SELECT title FROM ".$prefix."_categories WHERE id = '".$scat."'"));
		list($caid) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_categories WHERE parentid = '".$scat."'"));
		$caton = ($caid) ? 1 : 0;
		$order = "WHERE s.catid = '".$scat."' AND s.time <= now() AND s.status != '0' ".$cwhere." ORDER BY ".$orderby;
		$ordernum = "catid = '".$scat."' AND time <= now() AND status != '0'";
		$pagetitle = $conf['defis']." "._FAQ." ".$conf['defis']." ".$cat_title.$page_logo;
	} else {
		$caton = 1;
		$field = "";
		$hwhere = ($home) ? "AND s.ihome = '1'" : "";
		$hnwhere = ($home) ? "AND ihome = '1'" : "";
		$order = "WHERE s.time <= now() AND s.status != '0' ".$hwhere." ".$cwhere." ORDER BY s.time DESC";
		$ordernum = "time <= now() AND status != '0' ".$hnwhere;
		$page_logo = _FAQ;
		$pagetitle = $conf['defis']." ".$page_logo;
	}
	head($pagetitle);
	if (!$home || ($home && $conffa['homcat'])) {
		$cont = ($scat) ? navigate($cat_title, $caton) : navigate($page_logo, $caton);
		if ($scat) $cont .= tpl_eval("cat-navi", catlink($conf['name'], $scat, $conffa['defis'], _FAQ));
		if ($caton == 1) $cont .= categories($conf['name'], $conffa['tabcol'], $conffa['subcat'], $conffa['catdesc'], $scat);
	}
	if ($scat) {
		$cont .= tpl_eval("open");
		$cont .= "<table class=\"sl_table_faq\">";
		$result = $db->sql_query("SELECT fid, title FROM ".$prefix."_faq WHERE catid = '".$scat."' AND time <= now() AND status != '0' ORDER BY ".$orderbyf);
		while (list($f_id, $f_title) = $db->sql_fetchrow($result)) $cont .= "<tr><td><a href=\"#".$f_id."\" title=\"".$f_title."\" class=\"sl_faq\">".search_color($f_title, $word)."</a></td></tr>";
		$cont .= "</table>";
		$cont .= tpl_eval("close");
	}
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $newnum;
	$offset = intval($offset);
	$limit = (!$scat) ? "LIMIT ".$offset.", ".$newnum : "";
	$result = $db->sql_query("SELECT s.fid, s.catid, s.name, s.title, s.time, s.hometext, s.comments, s.counter, s.acomm, s.score, s.ratings, c.title, c.description, c.img, u.user_name FROM ".$prefix."_faq AS s LEFT JOIN ".$prefix."_categories AS c ON (s.catid = c.id) LEFT JOIN ".$prefix."_users AS u ON (s.uid = u.user_id) ".$order." ".$limit);
	if ($db->sql_numrows($result) > 0) {
		while (list($id, $catid, $uname, $stitle, $time, $hometext, $comm, $counter, $acomm, $score, $ratings, $ctitle, $cdesc, $cimg, $user_name) = $db->sql_fetchrow($result)) {
			$cdesc = ($cdesc) ? $cdesc : $ctitle;
			$ctitle = ($ctitle) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_cat\">".cutstr($ctitle, 15)."</a>" : "";
			$cimg = ($cimg) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_icat\"><img src=\"".img_find("categories/".$cimg)."\" alt=\"".$cdesc."\" title=\"".$cdesc."\"></a>" : "";
			$title = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."\" title=\"".$stitle."\">".$stitle."</a> ".new_graphic($time);
			$read = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."\" title=\"".$stitle."\" class=\"sl_but_read\">"._READMORE."</a>";
			$post = ($conffa['autor']) ? (($user_name) ? user_info($user_name) : (($uname) ? $uname : $confu['anonym'])) : "";
			$post = ($post) ? "<span title=\""._POSTEDBY."\" class=\"sl_post\">".$post."</span>" : "";
			$date = ($conffa['date']) ? "<span title=\""._CHNGSTORY."\" class=\"sl_date\">".format_time($time)."</span>" : "";
			$reads = ($conffa['read']) ? "<span title=\""._READS."\" class=\"sl_views\">".$counter."</span>" : "";
			$comm = ($acomm) ? "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."#comm\" title=\""._COMMENTS."\" class=\"sl_coms\">".$comm."</a>" : "";
			$arating = ajax_rating(0, $id, $conf['name'], $ratings, $score, "");
			$admin = (is_moder($conf['name'])) ? add_menu("<a href=\"".$admin_file.".php?op=faq_add&amp;id=".$id."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=faq_delete&amp;id=".$id."&amp;refer=1\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$stitle."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>") : "";
			$cont .= tpl_func("basic", $catid, $cimg, $ctitle, $id, $title, bb_decode($hometext, $conf['name']), $read, $post, $date, $reads, "", $comm, $arating, $admin, "", "", "");
		}
		if (!$scat) $cont .= num_article("pagenum", $conf['name'], $newnum, $field, "fid", "_faq", "catid", $ordernum, $conffa['nump']);
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function liste() {
	global $prefix, $db, $conf, $confu, $conffa;
	$cwhere = catmids($conf['name'], "s.catid");
	$listnum = intval($conffa['listnum']);
	$let = isset($_GET['let']) ? mb_substr($_GET['let'], 0, 1, "utf-8") : "";
	if ($let) {
		$field = "op=liste&amp;let=".urlencode($let)."&amp;";
		$pagetitle = $conf['defis']." "._FAQ." ".$conf['defis']." "._LIST." ".$conf['defis']." ".$let;
		$order = "WHERE UCASE(s.title) LIKE BINARY '".$let."%' AND s.time <= now() AND s.status != '0'";
	} else {
		$field = "op=liste&amp;";
		$pagetitle = $conf['defis']." "._FAQ." ".$conf['defis']." "._LIST;
		$order = "WHERE s.time <= now() AND s.status != '0'";
	}
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $listnum;
	$offset = intval($offset);
	$result = $db->sql_query("SELECT s.fid, s.catid, s.name, s.title, s.time, c.title, u.user_name FROM ".$prefix."_faq AS s LEFT JOIN ".$prefix."_categories AS c ON (s.catid = c.id) LEFT JOIN ".$prefix."_users AS u ON (s.uid = u.user_id) ".$order." ".$cwhere." ORDER BY time DESC LIMIT ".$offset.", ".$listnum);
	head($pagetitle);
	$cont = navigate(_LIST);
	if ($db->sql_numrows($result) > 0) {
		$letter = ($conffa['letter']) ? letter($conf['name']) : "";
		$cont .= tpl_eval("liste-open", $letter, _ID, _QUESTION, _CATEGORY, _POSTER, _DATE);
		while (list($id, $catid, $uname, $title, $time, $ctitle, $user_name) = $db->sql_fetchrow($result)) {
			$title = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."\" title=\"".$title."\">".cutstr($title, 40)."</a> ".new_graphic($time);
			$ctitle = ($ctitle) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$ctitle."\">".cutstr($ctitle, 15)."</a>" : _NO;
			$post = ($user_name) ? user_info($user_name) : (($uname) ? $uname : $confu['anonym']);
			$cont .= tpl_func("liste-basic", $id, $title, $ctitle, $post, format_time($time));
		}
		$cont .= tpl_eval("liste-close");
		$ordernum = ($let) ? "title LIKE BINARY '".$let."%' AND time <= now() AND status != '0'" : "time <= now() AND status != '0'";
		$cont .= num_article("pagenum", $conf['name'], $listnum, $field, "fid", "_faq", "catid", $ordernum, $conffa['nump']);
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function view() {
	global $prefix, $db, $admin_file, $conf, $confu, $conffa;
	$id = (isset($_GET['id'])) ? intval($_GET['id']) : 0;
	$pag = (isset($_GET['pag'])) ? intval($_GET['pag']) : 0;
	$word = (isset($_GET['word'])) ? text_filter($_GET['word']) : "";
	$cwhere = catmids($conf['name'], "s.catid");
	$result = $db->sql_query("SELECT s.catid, s.name, s.title, s.time, s.hometext, s.counter, s.acomm, s.score, s.ratings, c.title, c.description, c.img, u.user_name FROM ".$prefix."_faq AS s LEFT JOIN ".$prefix."_categories AS c ON (s.catid = c.id) LEFT JOIN ".$prefix."_users AS u ON (s.uid = u.user_id) WHERE s.fid = '".$id."' AND s.time <= now() AND s.status != '0' ".$cwhere);
	if ($db->sql_numrows($result) == 1) {
		$db->sql_query("UPDATE ".$prefix."_faq SET counter = counter+1 WHERE fid = '".$id."'");
		list($catid, $uname, $title, $time, $hometext, $counter, $acomm, $score, $ratings, $ctitle, $cdesc, $cimg, $user_name) = $db->sql_fetchrow($result);
		$pagetitle = (intval($catid)) ? $conf['defis']." "._FAQ." ".$conf['defis']." ".$ctitle." ".$conf['defis']." ".$title : $conf['defis']." "._FAQ." ".$conf['defis']." ".$title;
		head($pagetitle, $hometext);
		$cont = navigate(_FAQ, $conffa['viewcat']);
		if ($catid) $cont .= tpl_eval("cat-navi", catlink($conf['name'], $catid, $conffa['defis'], _FAQ));
		if ($conffa['viewcat']) $cont .= categories($conf['name'], $conffa['tabcol'], $conffa['subcat'], $conffa['catdesc'], 0);
		$conpag = explode("[pagebreak]", $hometext);
		$pageno = count($conpag);
		$pag = ($pag == "" || $pag < 1) ? 1 : $pag;
		if ($pag > $pageno) $pag = $pageno;
		$arrayelement = (int)$pag;
		$arrayelement--;
		$cdesc = ($cdesc) ? $cdesc : $ctitle;
		$ctitle = ($ctitle) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_cat\">".cutstr($ctitle, 15)."</a>" : "";
		$cimg = ($cimg) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_icat\"><img src=\"".img_find("categories/".$cimg)."\" alt=\"".$cdesc."\" title=\"".$cdesc."\"></a>" : "";
		$post = ($conffa['autor'] ) ? (($user_name) ? user_info($user_name) : (($uname) ? $uname : $confu['anonym'])) : "";
		$post = ($post) ? "<span title=\""._POSTEDBY."\" class=\"sl_post\">".$post."</span>" : "";
		$date = ($conffa['date']) ? "<span title=\""._CHNGSTORY."\" class=\"sl_date\">".format_time($time)."</span>" : "";
		$reads = ($conffa['read']) ? "<span title=\""._READS."\" class=\"sl_views\">".$counter."</span>" : "";
		$arating = ajax_rating(1, $id, $conf['name'], $ratings, $score, "");
		$admin = (is_moder($conf['name'])) ? add_menu("<a href=\"".$admin_file.".php?op=faq_add&amp;id=".$id."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=faq_delete&amp;id=".$id."\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$title."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>") : "";
		$favorites = favorview($id, $conf['name']);
		$goback = "<span OnClick=\"javascript:history.go(-1);\" title=\""._BACK."\" class=\"sl_but_back\">"._BACK."</span>";
		$cont .= tpl_eval("basic", $catid, $cimg, $ctitle, $id, search_color($title, $word), search_color(bb_decode($conpag[$arrayelement], $conf['name']), $word), "", $post, $date, $reads, "", "", $arating, $admin, $favorites, $goback, "");
		$cont .= num_pages("pagenum", $conf['name'], 1, $pageno, 1, "op=view&amp;id=".$id."&amp;", $conffa['nump']);
		if ($conffa['link']) {
			$plimit = intval($conffa['linknum']);
			list($count) = $db->sql_fetchrow($db->sql_query("SELECT Count(fid) FROM ".$prefix."_faq WHERE catid = '".$catid."' AND fid != '".$id."' AND time <= now() AND status != '0'"));
			if ($count >= $plimit) {
				$random = mt_rand(0, $count - $plimit);
				$result = $db->sql_query("SELECT fid, title, time, hometext FROM ".$prefix."_faq WHERE catid = '".$catid."' AND fid != '".$id."' AND time <= now() AND status != '0' ORDER BY time DESC LIMIT ".$random.", ".$plimit);
				$cont .= tpl_eval("assoc-open", _CATASSOC);
				while(list($aid, $title, $time, $hometext) = $db->sql_fetchrow($result)) {
					$adate = ($conffa['date']) ? "<span title=\""._CHNGSTORY."\" class=\"sl_date\">"._CHNGSTORY.": ".format_time($time)."</span>" : "";
					$atext = cutstr(htmlspecialchars(trim(strip_tags(bb_decode($hometext, $conf['name']))), ENT_QUOTES), 80);
					if (preg_match("#\[attach=(.*?)\s(.*?)\]#si", $hometext, $match)) {
						$img = "uploads/".$conf['name']."/thumb/".trim($match[1]);
					} else {
						preg_match("#\[img=(.*?)\](.*)\[/img\]#si", $hometext, $match);
						$img = isset($match[2]) ? trim($match[2]) : (isset($match[1]) ? trim($match[1]) : "");
					}
					$img = ($img) ? (file_exists($img) ? $img : img_find('logos/slaed_logo_60x60.png')) : img_find('logos/slaed_logo_60x60.png');
					$cont .= tpl_func("assoc-basic", "index.php?name=".$conf['name']."&amp;op=view&amp;id=".$aid, $title, $adate, $atext, $img);
				}
				$cont .= tpl_eval("assoc-close");
			}
		}
		if ($acomm) $cont .= show_com($id, $acomm);
		echo $cont;
		foot();
	} else {
		header("Location: index.php?name=".$conf['name']);
	}
}

function add() {
	global $prefix, $db, $user, $conf, $conffa, $confu, $stop;
	if ((is_user() && $conffa['add'] == 1) || (!is_user() && $conffa['addquest'] == 1)) {
		head($conf['defis']." "._FAQ." ".$conf['defis']." "._ADD, _SUBMIT." "._PAGENOTE);
		$cont = navigate(_ADD);
		if ($stop) $cont .= tpl_warn("warn", $stop, "", "", "warn");
		$title = save_text($_POST['title'], 1);
		$catid = intval($_POST['catid']);
		$hometext = save_text($_POST['hometext']);
		$postname = text_filter(substr($_POST['postname'], 0, 25));
		if ($hometext) $cont .= preview($title, $hometext, "", "", $conf['name']);
		$cont .= tpl_warn("warn", _SUBMIT." "._PAGENOTE, "", "", "info");
		$cont .= tpl_eval("open");
		$cont .= "<form name=\"post\" action=\"index.php?name=".$conf['name']."\" method=\"post\"><table class=\"sl_table_form\">";
		if (is_user()) {
			$cont .= "<tr><td>"._YOURNAME.":</td><td>".text_filter(substr($user[1], 0, 25))."</td></tr>";
		} else {
			$postname = ($postname) ? $postname : $confu['anonym'];
			$cont .= "<tr><td>"._YOURNAME.":</td><td><input type=\"text\" name=\"postname\" value=\"".$postname."\" class=\"sl_field ".$conf['style']."\" placeholder=\""._YOURNAME."\" required></td></tr>";
		}
		$cont .= "<tr><td>"._QUESTION.":</td><td><input type=\"text\" name=\"title\" value=\"".$title."\" maxlength=\"100\" class=\"sl_field ".$conf['style']."\" placeholder=\""._QUESTION."\" required></td></tr>"
		."<tr><td>"._CATEGORY.":</td><td>".getcat("faq", $catid, "catid", $conf['style'], "<option value=\"\">"._HOMECAT."</option>")."</td></tr>"
		."<tr><td>"._ANSWER.":</td><td>".textarea("1", "hometext", $hometext, $conf['name'], "10", _ANSWER, "1")."</td></tr>"
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
	global $prefix, $db, $user, $conf, $conffa, $stop;
	if ((is_user() && $conffa['add'] == 1) || (!is_user() && $conffa['addquest'] == 1)) {
		$postname = text_filter(substr($_POST['postname'], 0, 25));
		$title = save_text($_POST['title'], 1);
		$hometext = save_text($_POST['hometext']);
		$catid = intval($_POST['catid']);
		$stop = array();
		if (!$hometext) $stop[] = _CERROR1;
		if (!$postname && !is_user()) $stop[] = _CERROR3;
		if (captcha_check()) $stop[] = _SECCODEINCOR;
		if (!$stop && $_POST['posttype'] == "save") {
			$postid = (is_user()) ? intval($user[0]) : "";
			$uname = (!is_user()) ? $postname : "";
			$ip = getip();
			$db->sql_query("INSERT INTO ".$prefix."_faq (fid, catid, uid, name, title, time, hometext, ip_sender, status) VALUES (NULL, '".$catid."', '".$postid."', '".$uname."', '".$title."', now(), '".$hometext."', '".$ip."', '0')");
			update_points(6);
			$puname = (is_user()) ? $user[1] : $postname;
			addmail($conffa['addmail'], $conf['name'], $puname, _FAQ);
			head($conf['defis']." "._FAQ." ".$conf['defis']." "._ADD, _SUBTEXT);
			echo navigate(_ADD).tpl_warn("warn", _SUBTEXT, "?name=".$conf['name'], 10, "info");
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
	faq();
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