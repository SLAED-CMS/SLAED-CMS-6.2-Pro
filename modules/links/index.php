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
include("config/config_links.php");

function navigate($title, $cat="") {
	global $conf, $confl;
	$lcat = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
	$catf = ($lcat) ? "&amp;cat=".$lcat : "";
	$home = "<a href=\"index.php?name=".$conf['name']."\" title=\""._LINKS."\" class=\"sl_but_navi\">"._HOME."</a>";
	$best = ($confl['rate']) ? "<a href=\"index.php?name=".$conf['name'].$catf."&amp;sort=1\" title=\""._BEST."\" class=\"sl_but_navi\">"._BEST."</a>" : "";
	$pop = ($confl['rate']) ? "<a href=\"index.php?name=".$conf['name'].$catf."&amp;sort=2\" title=\""._POP."\" class=\"sl_but_navi\">"._POP."</a>" : "";
	$liste = "<a href=\"index.php?name=".$conf['name']."&amp;op=liste\" title=\""._LIST."\" class=\"sl_but_navi\">"._LIST."</a>";
	$add = ((is_user() && $confl['add'] == 1) || (!is_user() && $confl['addquest'] == 1)) ? "<a href=\"index.php?name=".$conf['name']."&amp;op=add\" title=\""._ADD."\" class=\"sl_but_navi\">"._ADD."</a>" : "";
	$catshow = ($cat) ? "<a OnClick=\"CloseOpen('sl_close_1', 1);\" title=\""._CATVORH."\" class=\"sl_but_navi\">"._CATEGORIES."</a>" : "";
	return tpl_eval("navi", $title, $conf['name'], "", $home, $best, $pop, $liste, $add, $catshow);
}

function links() {
	global $prefix, $db, $admin_file, $user, $conf, $confu, $confl, $home;
	$cwhere = catmids($conf['name'], "f.cid");
	$linknum = user_news($user[3], $confl['num']);
	$sort = isset($_GET['sort']) ? intval($_GET['sort']) : 0;
	$lcat = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
	if (!$lcat && $sort && $confl['rate']) {
		$caton = 0;
		$field = "sort=".$sort."&amp;";
		if ($sort == 1) {
			$orderby = "f.totalvotes DESC";
			$links_logo = _BEST;
		} else {
			$orderby = "f.hits DESC";
			$links_logo = _POP;
		}
		$order = "WHERE f.date <= now() AND f.status != '0' ".$cwhere." ORDER BY ".$orderby;
		$ordernum = "date <= now() AND status != '0'";
		$pagetitle = $conf['defis']." "._LINKS." ".$conf['defis']." ".$links_logo;
	} elseif ($lcat) {
		$field = ($sort) ? "cat=".$lcat."&amp;sort=".$sort."&amp;" : "cat=".$lcat."&amp;";
		$orderby = ($sort) ? (($sort == 1) ? "f.totalvotes DESC" : "f.hits DESC") : "f.date DESC";
		$links_logo = ($sort) ? (($sort == 1) ? " ".$conf['defis']." "._BEST : " ".$conf['defis']." "._POP) : "";
		
		list($cat_title) = $db->sql_fetchrow($db->sql_query("SELECT title FROM ".$prefix."_categories WHERE id = '".$lcat."'"));
		list($caid) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_categories WHERE parentid = '".$lcat."'"));
		$caton = ($caid) ? 1 : 0;
		
		$order = "WHERE f.cid = '".$lcat."' AND f.date <= now() AND f.status != '0' ".$cwhere." ORDER BY ".$orderby;
		$ordernum = "cid = '".$lcat."' AND date <= now() AND status != '0'";
		$pagetitle = $conf['defis']." "._LINKS." ".$conf['defis']." ".$cat_title.$links_logo;
	} else {
		$caton = 1;
		$field = "";
		$hwhere = ($home) ? "AND f.ihome = '1'" : "";
		$hnwhere = ($home) ? "AND ihome = '1'" : "";
		$order = "WHERE f.date <= now() AND f.status != '0' ".$hwhere." ".$cwhere." ORDER BY f.date DESC";
		$ordernum = "date <= now() AND status != '0' ".$hnwhere;
		$links_logo = _LINKS;
		$pagetitle = $conf['defis']." ".$links_logo;
	}
	head($pagetitle);
	if (!$home || ($home && $confl['homcat'])) {
		$cont = ($lcat) ? navigate($cat_title, $caton) : navigate($links_logo, $caton);
		if ($lcat) $cont .= tpl_eval("cat-navi", catlink($conf['name'], $lcat, $confl['defis'], _LINKS));
		if ($caton == 1) $cont .= categories($conf['name'], $confl['tabcol'], $confl['subcat'], $confl['catdesc'], $lcat);
	}
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $linknum;
	$offset = intval($offset);
	$result = $db->sql_query("SELECT f.lid, f.cid, f.name, f.title, f.description, f.date, f.counter, f.acomm, f.votes, f.totalvotes, f.totalcomments, f.hits, c.title, c.description, c.img, u.user_name FROM ".$prefix."_links AS f LEFT JOIN ".$prefix."_categories AS c ON (f.cid = c.id) LEFT JOIN ".$prefix."_users AS u ON (f.uid = u.user_id) ".$order." LIMIT ".$offset.", ".$linknum);
	if ($db->sql_numrows($result) > 0) {
		while (list($id, $catid, $uname, $ftitle, $description, $time, $counter, $acomm, $votes, $totalvotes, $comm, $hits, $ctitle, $cdesc, $cimg, $user_name) = $db->sql_fetchrow($result)) {
			$cdesc = ($cdesc) ? $cdesc : $ctitle;
			$ctitle = ($ctitle) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_cat\">".cutstr($ctitle, 15)."</a>" : "";
			$cimg = ($cimg) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_icat\"><img src=\"".img_find("categories/".$cimg)."\" alt=\"".$cdesc."\" title=\"".$cdesc."\"></a>" : "";
			$title = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."\" title=\"".$ftitle."\">".$ftitle."</a> ".new_graphic($time);
			$read = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."\" title=\"".$ftitle."\" class=\"sl_but_read\">"._READMORE."</a>";
			$post = ($confl['autor']) ? (($user_name) ? user_info($user_name) : (($uname) ? $uname : $confu['anonym'])) : "";
			$post = ($post) ? "<span title=\""._POSTEDBY."\" class=\"sl_post\">".$post."</span>" : "";
			$date = ($confl['date']) ? "<span title=\""._CHNGSTORY."\" class=\"sl_date\">".format_time($time)."</span>" : "";
			$reads = ($confl['read']) ? "<span title=\""._READS."\" class=\"sl_views\">".$counter."</span>" : "";
			$hits = ($confl['hits']) ? "<span title=\""._LINKHITS."\" class=\"sl_down\">".$hits."</span>" : "";
			$comm = ($acomm) ? "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."#comm\" title=\""._COMMENTS."\" class=\"sl_coms\">".$comm."</a>" : "";
			$arating = ajax_rating(0, $id, $conf['name'], $votes, $totalvotes, "");
			$admin = (is_moder($conf['name'])) ? add_menu("<a href=\"".$admin_file.".php?op=links_add&amp;id=".$id."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=links_delete&amp;id=".$id."&amp;refer=1\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$ftitle."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>") : "";
			$cont .= tpl_func("basic", $catid, $cimg, $ctitle, $id, $title, bb_decode($description, $conf['name']), $read, $post, $date, $reads, $hits, $comm, $arating, $admin, "", "", "");
		}
		$cont .= num_article("pagenum", $conf['name'], $linknum, $field, "lid", "_links", "cid", $ordernum, $confl['nump']);
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function liste() {
	global $prefix, $db, $conf, $confu, $confl;
	$cwhere = catmids($conf['name'], "f.cid");
	$listnum = intval($confl['listnum']);
	$let = isset($_GET['let']) ? mb_substr($_GET['let'], 0, 1, "utf-8") : "";
	if ($let) {
		$field = "op=liste&amp;let=".urlencode($let)."&amp;";
		$pagetitle = $conf['defis']." "._LINKS." ".$conf['defis']." "._LIST." ".$conf['defis']." ".$let;
		$order = "WHERE UCASE(f.title) LIKE BINARY '".$let."%' AND f.date <= now() AND f.status != '0'";
	} else {
		$field = "op=liste&amp;";
		$pagetitle = $conf['defis']." "._LINKS." ".$conf['defis']." "._LIST;
		$order = "WHERE f.date <= now() AND f.status != '0'";
	}
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $listnum;
	$offset = intval($offset);
	$result = $db->sql_query("SELECT f.lid, f.cid, f.name, f.title, f.date, c.title, u.user_name FROM ".$prefix."_links AS f LEFT JOIN ".$prefix."_categories AS c ON (f.cid = c.id) LEFT JOIN ".$prefix."_users AS u ON (f.uid = u.user_id) ".$order." ".$cwhere." ORDER BY date DESC LIMIT ".$offset.", ".$listnum);
	head($pagetitle);
	$cont = navigate(_LIST);
	if ($db->sql_numrows($result) > 0) {
		$letter = ($confl['letter']) ? letter($conf['name']) : "";
		$cont .= tpl_eval("liste-open", $letter, _ID, _TITLE, _CATEGORY, _POSTER, _DATE);
		while (list($id, $catid, $uname, $title, $time, $ctitle, $user_name) = $db->sql_fetchrow($result)) {
			$title = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."\" title=\"".$title."\">".cutstr($title, 40)."</a> ".new_graphic($time);
			$ctitle = ($ctitle) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$ctitle."\">".cutstr($ctitle, 15)."</a>" : _NO;
			$post = ($user_name) ? user_info($user_name) : (($uname) ? $uname : $confu['anonym']);
			$cont .= tpl_func("liste-basic", $id, $title, $ctitle, $post, format_time($time));
		}
		$cont .= tpl_eval("liste-close");
		$ordernum = ($let) ? "title LIKE BINARY '".$let."%' AND date <= now() AND status != '0'" : "date <= now() AND status != '0'";
		$cont .= num_article("pagenum", $conf['name'], $listnum, $field, "lid", "_links", "cid", $ordernum, $confl['nump']);
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function view() {
	global $prefix, $db, $admin_file, $conf, $confu, $confl;
	$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
	$cwhere = catmids($conf['name'], "f.cid");
	$word = isset($_GET['word']) ? text_filter($_GET['word']) : "";
	$result = $db->sql_query("SELECT f.cid, f.name, f.title, f.url, f.description, f.bodytext, f.date, f.email, f.counter, f.acomm, f.votes, f.totalvotes, f.hits, f.status, c.title, c.description, c.img, u.user_name FROM ".$prefix."_links AS f LEFT JOIN ".$prefix."_categories AS c ON (f.cid = c.id) LEFT JOIN ".$prefix."_users AS u ON (f.uid = u.user_id) WHERE lid = '".$id."' AND date <= now() AND f.status != '0' ".$cwhere);
	if ($db->sql_numrows($result) == 1) {
		$db->sql_query("UPDATE ".$prefix."_links SET counter = counter+1 WHERE lid = '".$id."'");
		list($catid, $uname, $title, $url, $description, $bodytext, $date, $aemail,  $counter, $acomm, $votes, $totalvotes, $hits, $status, $ctitle, $cdesc, $cimg, $user_name) = $db->sql_fetchrow($result);
		$pagetitle = (intval($catid)) ? $conf['defis']." "._LINKS." ".$conf['defis']." ".$ctitle." ".$conf['defis']." ".$title : $conf['defis']." "._LINKS." ".$conf['defis']." ".$title;
		head($pagetitle, $description, $bodytext);
		$cont = navigate(_LINKS, $confl['viewcat']);
		if ($catid) $cont .= tpl_eval("cat-navi", catlink($conf['name'], $catid, $confl['defis'], _LINKS));
		if ($confl['viewcat']) $cont .= categories($conf['name'], $confl['tabcol'], $confl['subcat'], $confl['catdesc'], 0);
		$text = ($bodytext) ? $description."<br><br>".$bodytext : $description;
		$cdesc = ($cdesc) ? $cdesc : $ctitle;
		$ctitle = ($ctitle) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_cat\">".cutstr($ctitle, 15)."</a>" : "";
		$cimg = ($cimg) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_icat\"><img src=\"".img_find("categories/".$cimg)."\" alt=\"".$cdesc."\" title=\"".$cdesc."\"></a>" : "";
		$post = ($confl['autor']) ? (($user_name) ? user_info($user_name) : (($uname) ? $uname : $confu['anonym'])) : "";
		$post = ($post) ? "<span title=\""._POSTEDBY."\" class=\"sl_post\">".$post."</span>" : "";
		$date = ($confl['date']) ? "<span title=\""._CHNGSTORY."\" class=\"sl_date\">".format_time($date)."</span>" : "";
		$reads = ($confl['read']) ? "<span title=\""._READS."\" class=\"sl_views\">".$counter."</span>" : "";
		$hits = ($confl['hits']) ? "<span title=\""._LINKHITS."\" class=\"sl_down\">".$hits."</span>" : "";
		$arating = ajax_rating(1, $id, $conf['name'], $votes, $totalvotes, "");
		$admin = (is_moder($conf['name'])) ? add_menu("<a href=\"".$admin_file.".php?op=links_add&amp;id=".$id."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=links_delete&amp;id=".$id."\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$title."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>") : "";
		$favorites = favorview($id, $conf['name']);
		$goback = "<span OnClick=\"javascript:history.go(-1);\" title=\""._BACK."\" class=\"sl_but_back\">"._BACK."</span>";
		if (is_user() || $confl['links'] == "1") {
			$down = "<form action=\"index.php?name=".$conf['name']."\" method=\"post\" style=\"display: inline\">"
			."<input type=\"hidden\" name=\"id\" value=\"".$id."\">"
			."<input type=\"hidden\" name=\"op\" value=\"geturl\">"
			."<input type=\"submit\" OnClick=\"window.open('".$url."')\" value=\""._DOWNLLINK."\" class=\"sl_but_green\"></form>";
		}
		$broc = ($confl['broc'] == 1 && $status != "2") ? "<a OnClick=\"javascript: Location('index.php?name=".$conf['name']."&amp;op=broken&amp;id=".$id."');\" title=\""._BROCLINK."\" class=\"sl_but_blue\">"._COMPLAINT."</a>" : "";
		$email = ($aemail) ? _AUEMAIL.": ".anti_spam($aemail) : "";
		$home = ($url) ? _SITE.": ".domain($url) : "";
		$cont .= tpl_eval("basic", $catid, $cimg, $ctitle, $id, search_color($title, $word), search_color(bb_decode($text, $conf['name']), $word), "", $post, $date, $reads, $hits, "", $arating, $admin, $favorites, $goback, "", "", "", $down, $broc, $email, $home);
		if ($confl['link']) {
			$plimit = intval($confl['linknum']);
			list($count) = $db->sql_fetchrow($db->sql_query("SELECT Count(lid) FROM ".$prefix."_links WHERE cid = '".$catid."' AND lid != '".$id."' AND date <= now() AND status != '0'"));
			if ($count >= $plimit) {
				$random = mt_rand(0, $count - $plimit);
				$result = $db->sql_query("SELECT lid, title, description, date FROM ".$prefix."_links WHERE cid = '".$catid."' AND lid != '".$id."' AND date <= now() AND status != '0' ORDER BY date DESC LIMIT ".$random.", ".$plimit);
				$cont .= tpl_eval("assoc-open", _CATASSOC);
				while (list($aid, $title, $hometext, $time) = $db->sql_fetchrow($result)) {
					$adate = ($confl['date']) ? "<span title=\""._CHNGSTORY."\" class=\"sl_date\">"._CHNGSTORY.": ".format_time($time)."</span>" : "";
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

function broken() {
	global $prefix, $db, $conf, $confl;
	$id = intval($_GET['id']);
	if ($confl['broc'] == 1 && $id) {
		$db->sql_query("UPDATE ".$prefix."_links SET status = '2' WHERE lid = '".$id."' AND status != '0'");
		head($conf['defis']." "._LINKS." ".$conf['defis']." "._BROCLINK, _BROCNOTEL);
		echo navigate(_BROCLINK).tpl_warn("warn", _BROCNOTEL, "?name=".$conf['name']."&amp;op=view&amp;id=".$id, 5, "info");
		foot();
	} else {
		header("Location: index.php?name=".$conf['name']);
	}
}

function add() {
	global $db, $prefix, $user, $conf, $confu, $confl, $stop;
	if ((is_user() && $confl['add'] == 1) || (!is_user() && $confl['addquest'] == 1)) {
		if (is_user()) {
			$userinfo = getusrinfo();
			$authormail = isset($_POST['authormail']) ? text_filter($_POST['authormail']) : $userinfo['user_email'];
			$linklink = isset($_POST['linklink']) ? url_filter($_POST['linklink']) : $userinfo['user_website'];
		} else {
			$authormail = isset($_POST['authormail']) ? text_filter($_POST['authormail']) : "";
			$linklink = isset($_POST['linklink']) ? url_filter($_POST['linklink']) : "http://";
		}
		head($conf['defis']." "._LINKS." ".$conf['defis']." "._ADD, _ADDFNOTE);
		$cont = navigate(_ADD);
		if ($stop) $cont .= tpl_warn("warn", $stop, "", "", "warn");
		$title = save_text($_POST['title'], 1);
		$cid = intval($_POST['cid']);
		$description = save_text($_POST['description']);
		$bodytext = save_text($_POST['bodytext']);
		$postname = text_filter(substr($_POST['postname'], 0, 25));
		if ($description) $cont .= preview($title, $description, $bodytext, "", $conf['name']);
		$cont .= tpl_warn("warn", _ADDFNOTE, "", "", "info");
		$cont .= tpl_eval("open");
		$cont .= "<form name=\"post\" action=\"index.php?name=".$conf['name']."\" method=\"post\"><table class=\"sl_table_form\">";
		if (is_user()) {
			$cont .= "<tr><td>"._YOURNAME.":</td><td>".text_filter(substr($user[1], 0, 25))."</td></tr>";
		} else {
			$postname = ($postname) ? $postname : $confu['anonym'];
			$cont .= "<tr><td>"._YOURNAME.":</td><td><input type=\"text\" name=\"postname\" value=\"".$postname."\" class=\"sl_field ".$conf['style']."\" placeholder=\""._YOURNAME."\" required></td></tr>";
		}
		$cont .= "<tr><td>"._AUEMAIL.":</td><td><input type=\"text\" name=\"authormail\" value=\"".$authormail."\" maxlength=\"100\" class=\"sl_field ".$conf['style']."\" placeholder=\""._AUEMAIL."\" required></td></tr>"
		."<tr><td>"._SITENAME.":</td><td><input type=\"text\" name=\"title\" value=\"".$title."\" maxlength=\"100\" class=\"sl_field ".$conf['style']."\" placeholder=\""._SITENAME."\" required></td></tr>"
		."<tr><td>"._CATEGORY.":</td><td>".getcat($conf['name'], $cid, "cid", $conf['style'], "<option value=\"\">"._HOMECAT."</option>")."</td></tr>"
		."<tr><td>"._TEXT.":</td><td>".textarea("1", "description", $description, $conf['name'], "5", _TEXT, "1")."</td></tr>"
		."<tr><td>"._ENDTEXT.":</td><td>".textarea("2", "bodytext", $bodytext, $conf['name'], "15", _ENDTEXT, "0")."</td></tr>"
		."<tr><td>"._URL.":</td><td><input type=\"text\" name=\"linklink\" value=\"".$linklink."\" maxlength=\"100\" class=\"sl_field ".$conf['style']."\" placeholder=\""._URL."\" required></td></tr>"
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
	global $prefix, $db, $user, $conf, $confl, $stop;
	if ((is_user() && $confl['add'] == 1) || (!is_user() && $confl['addquest'] == 1)) {
		$postname = text_filter(substr($_POST['postname'], 0, 25));
		$title = save_text($_POST['title'], 1);
		$description = save_text($_POST['description']);
		$bodytext = save_text($_POST['bodytext']);
		$linklink = url_filter($_POST['linklink']);
		$authormail = text_filter($_POST['authormail']);
		$cid = intval($_POST['cid']);
		$stop = array();
		if (!$title) $stop[] = _CERROR10;
		if (!$description) $stop[] = _CERROR1;
		if (!$postname && !is_user()) $stop[] = _CERROR3;
		if (!$linklink) $stop[] = _CERROR4;
		checkemail($authormail);
		if (captcha_check()) $stop[] = _SECCODEINCOR;
		if ($db->sql_numrows($db->sql_query("SELECT url FROM ".$prefix."_links WHERE url = '".$linklink."'")) > 0) $stop[] = _LINKEXIST;
		if (!$stop && $_POST['posttype'] == "save") {
			$postid = (is_user()) ? intval($user[0]) : "";
			$uname = (!is_user()) ? $postname : "";
			$ip = getip();
			$db->sql_query("INSERT INTO ".$prefix."_links (lid, cid, uid, name, title, description, bodytext, url, date, email, ip_sender, status) VALUES (NULL, '".$cid."', '".$postid."', '".$uname."', '".$title."', '".$description."', '".$bodytext."', '".$linklink."', now(), '".$authormail."', '".$ip."', '0')");
			update_points(21);
			$puname = (is_user()) ? $user[1] : $postname;
			addmail($confl['addmail'], $conf['name'], $puname, _LINKS);
			head($conf['defis']." "._LINKS." ".$conf['defis']." "._ADD, _UPLOADFINISHL);
			echo navigate(_ADD).tpl_warn("warn", _UPLOADFINISHL, "?name=".$conf['name'], 10, "info");
			foot();
		} else {
			add();
		}
	} else {
		header("Location: index.php?name=".$conf['name']);
	}
}

function geturl() {
	global $prefix, $db, $conf, $confl;
	$id = intval($_POST['id']);
	if (($id && is_user()) || ($id && $confl['links'] == "1")) {
		$db->sql_query("UPDATE ".$prefix."_links SET hits = hits+1 WHERE lid = '".$id."'");
		list($title, $url) = $db->sql_fetchrow($db->sql_query("SELECT title, url FROM ".$prefix."_links WHERE lid = '".$id."'"));
		update_points(23);
		$info = sprintf(_NOTELINKLOAD, $title, domain($url));
		head($conf['defis']." "._LINKS." ".$conf['defis']." "._DOWNLLINK, $info);
		$cont = navigate(_LINKS);
		$cont .= tpl_warn("warn", $info, "", "", "info");
		$cont .= get_page($conf['name']);
		echo $cont;
		foot();
	} else {
		header("Location: index.php?name=".$conf['name']);
	}
}

switch($op) {
	default:
	links();
	break;
	
	case "liste":
	liste();
	break;
	
	case "view":
	view();
	break;
	
	case "geturl":
	geturl();
	break;
	
	case "broken":
	broken();
	break;
	
	case "add":
	add();
	break;
	
	case "send":
	send();
	break;
}
?>