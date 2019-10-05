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
include("config/config_media.php");

function navigate($title, $cat="") {
	global $conf, $confm;
	$mcat = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
	$catf = ($mcat) ? "&amp;cat=".$mcat : "";
	$home = "<a href=\"index.php?name=".$conf['name']."\" title=\""._MEDIA."\" class=\"sl_but_navi\">"._HOME."</a>";
	$best = ($confm['rate']) ? "<a href=\"index.php?name=".$conf['name'].$catf."&amp;sort=1\" title=\""._BEST."\" class=\"sl_but_navi\">"._BEST."</a>" : "";
	$pop = ($confm['rate']) ? "<a href=\"index.php?name=".$conf['name'].$catf."&amp;sort=2\" title=\""._POP."\" class=\"sl_but_navi\">"._POP."</a>" : "";
	$liste = "<a href=\"index.php?name=".$conf['name']."&amp;op=liste\" title=\""._LIST."\" class=\"sl_but_navi\">"._LIST."</a>";
	$add = ((is_user() && $confm['add'] == 1) || (!is_user() && $confm['addquest'] == 1)) ? "<a href=\"index.php?name=".$conf['name']."&amp;op=add\" title=\""._ADD."\" class=\"sl_but_navi\">"._ADD."</a>" : "";
	$catshow = ($cat) ? "<a OnClick=\"CloseOpen('sl_close_1', 1);\" title=\""._CATVORH."\" class=\"sl_but_navi\">"._CATEGORIES."</a>" : "";
	return tpl_eval("navi", $title, $conf['name'], "", $home, $best, $pop, $liste, $add, $catshow);
}

function media() {
	global $prefix, $db, $admin_file, $user, $conf, $confu, $confm, $home;
	$cwhere = catmids($conf['name'], "m.cid");
	$mnum = user_news($user[3], $confm['num']);
	$sort = isset($_GET['sort']) ? intval($_GET['sort']) : 0;
	$mcat = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
	if (!$mcat && $sort && $confm['rate']) {
		$caton = 0;
		$field = "sort=".$sort."&amp;";
		if ($sort == 1) {
			$orderby = "m.totalvotes DESC";
			$media_logo = _BEST;
		} else {
			$orderby = "m.hits DESC";
			$media_logo = _POP;
		}
		$order = "WHERE m.date <= now() AND m.status != '0' ".$cwhere." ORDER BY ".$orderby;
		$ordernum = "date <= now() AND status != '0'";
		$pagetitle = $conf['defis']." "._MEDIA." ".$conf['defis']." ".$media_logo;
	} elseif ($mcat) {
		$field = ($sort) ? "cat=".$mcat."&amp;sort=".$sort."&amp;" : "cat=".$mcat."&amp;";
		$orderby = ($sort) ? (($sort == 1) ? "m.totalvotes DESC" : "m.hits DESC") : "m.date DESC";
		$media_logo = ($sort) ? (($sort == 1) ? " ".$conf['defis']." "._BEST : " ".$conf['defis']." "._POP) : "";
		
		list($cat_title) = $db->sql_fetchrow($db->sql_query("SELECT title FROM ".$prefix."_categories WHERE id = '".$mcat."'"));
		list($caid) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_categories WHERE parentid = '".$mcat."'"));
		$caton = ($caid) ? 1 : 0;
		
		$order = "WHERE m.cid = '".$mcat."' AND m.date <= now() AND m.status != '0' ".$cwhere." ORDER BY ".$orderby;
		$ordernum = "cid = '".$mcat."' AND date <= now() AND status != '0'";
		$pagetitle = $conf['defis']." "._MEDIA." ".$conf['defis']." ".$cat_title.$media_logo;
	} else {
		$caton = 1;
		$field = "";
		$hwhere = ($home) ? "AND m.ihome = '1'" : "";
		$hnwhere = ($home) ? "AND ihome = '1'" : "";
		$order = "WHERE m.date <= now() AND m.status != '0' ".$hwhere." ".$cwhere." ORDER BY m.date DESC";
		$ordernum = "date <= now() AND status != '0' ".$hnwhere;
		$media_logo = _MEDIA;
		$pagetitle = $conf['defis']." ".$media_logo;
	}
	head($pagetitle);
	if (!$home || ($home && $confm['homcat'])) {
		$cont = ($mcat) ? navigate($cat_title, $caton) : navigate($media_logo, $caton);
		if ($mcat) $cont .= tpl_eval("cat-navi", catlink($conf['name'], $mcat, $confm['defis'], _MEDIA));
		if ($caton == 1) $cont .= categories($conf['name'], $confm['tabcol'], $confm['subcat'], $confm['catdesc'], $mcat);
	}
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $mnum;
	$offset = intval($offset);
	$result = $db->sql_query("SELECT m.id, m.cid, m.name, m.title, m.subtitle, m.description, m.links, m.date, m.acomm, m.votes, m.totalvotes, m.totalcom, m.hits, c.title, c.description, c.img, u.user_name FROM ".$prefix."_media AS m LEFT JOIN ".$prefix."_categories AS c ON (m.cid = c.id) LEFT JOIN ".$prefix."_users AS u ON (m.uid = u.user_id) ".$order." LIMIT ".$offset.", ".$mnum);
	if ($db->sql_numrows($result) > 0) {
		while(list($id, $catid, $uname, $title, $subtitle, $description, $links, $time, $acomm, $votes, $totalvotes, $comm, $hits, $ctitle, $cdesc, $cimg, $user_name) = $db->sql_fetchrow($result)) {
			$cdesc = ($cdesc) ? $cdesc : $ctitle;
			$ctitle = ($ctitle) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_cat\">".cutstr($ctitle, 15)."</a>" : "";
			$cimg = ($cimg) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_icat\"><img src=\"".img_find("categories/".$cimg)."\" alt=\"".$cdesc."\" title=\"".$cdesc."\"></a>" : "";
			$mtitle = ($subtitle) ? $title." ".urldecode($confm['mdefis'])." ".$subtitle : $title;
			$title = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."\" title=\"".$mtitle."\">".$mtitle."</a> ".new_graphic($time);
			$read = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."\" title=\"".$mtitle."\" class=\"sl_but_read\">"._READMORE."</a>";
			$post = ($confm['autor']) ? (($user_name) ? user_info($user_name) : (($uname) ? $uname : $confu['anonym'])) : "";
			$post = ($post) ? "<span title=\""._POSTEDBY."\" class=\"sl_post\">".$post."</span>" : "";
			$date = ($confm['date']) ? "<span title=\""._CHNGSTORY."\" class=\"sl_date\">".format_time($time)."</span>" : "";
			$reads = ($confm['read']) ? "<span title=\""._READS."\" class=\"sl_views\">".$hits."</span>" : "";
			$links = (url_types($links)) ? "<span title=\""._MDOWN.": ".url_types($links)."\" class=\"sl_down\">".url_types($links)."</span>" : "";
			$comm = ($acomm) ? "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."#comm\" title=\""._COMMENTS."\" class=\"sl_coms\">".$comm."</a>" : "";
			$arating = ajax_rating(0, $id, $conf['name'], $votes, $totalvotes, "");
			$admin = (is_moder($conf['name'])) ? add_menu("<a href=\"".$admin_file.".php?op=media_add&amp;id=".$id."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=media_delete&amp;id=".$id."&amp;refer=1\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$mtitle."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>") : "";
			$cont .= tpl_func("basic", $catid, $cimg, $ctitle, $id, $title, cutstr(bb_decode($description, $conf['name']), 800), $read, $post, $date, $reads, $links, $comm, $arating, $admin, "", "", "");
		}
		$cont .= num_article("pagenum", $conf['name'], $mnum, $field, "id", "_media", "cid", $ordernum, $confm['nump']);
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function liste() {
	global $prefix, $db, $conf, $confu, $confm;
	$cwhere = catmids($conf['name'], "m.cid");
	$listnum = intval($confm['listnum']);
	$let = isset($_GET['let']) ? mb_substr($_GET['let'], 0, 1, "utf-8") : "";
	if ($let) {
		$field = "op=liste&amp;let=".urlencode($let)."&amp;";
		$pagetitle = $conf['defis']." "._MEDIA." ".$conf['defis']." "._LIST." ".$conf['defis']." ".$let;
		$order = "WHERE UCASE(m.title) LIKE BINARY '".$let."%' AND m.date <= now() AND m.status != '0'";
	} else {
		$field = "op=liste&amp;";
		$pagetitle = $conf['defis']." "._MEDIA." ".$conf['defis']." "._LIST;
		$order = "WHERE m.date <= now() AND m.status != '0'";
	}
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $listnum;
	$offset = intval($offset);
	$result = $db->sql_query("SELECT m.id, m.cid, m.name, m.title, m.subtitle, m.date, c.title, u.user_name FROM ".$prefix."_media AS m LEFT JOIN ".$prefix."_categories AS c ON (m.cid = c.id) LEFT JOIN ".$prefix."_users AS u ON (m.uid=u.user_id) ".$order." ".$cwhere." ORDER BY date DESC LIMIT ".$offset.", ".$listnum);
	head($pagetitle);
	$cont = navigate(_LIST);
	if ($db->sql_numrows($result) > 0) {
		$letter = ($confm['letter']) ? letter($conf['name']) : "";
		$cont .= tpl_eval("liste-open", $letter, _ID, _TITLE, _CATEGORY, _POSTER, _DATE);
		while(list($id, $catid, $uname, $title, $subtitle, $time, $ctitle, $user_name) = $db->sql_fetchrow($result)) {
			$stitle = ($subtitle) ? $title." ".urldecode($confm['mdefis'])." ".$subtitle : $title;
			$title = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."\" title=\"".$stitle."\">".cutstr($stitle, 40)."</a> ".new_graphic($time);
			$ctitle = ($ctitle) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$ctitle."\">".cutstr($ctitle, 15)."</a>" : _NO;
			$post = ($user_name) ? user_info($user_name) : (($uname) ? $uname : $confu['anonym']);
			$cont .= tpl_func("liste-basic", $id, $title, $ctitle, $post, format_time($time));
		}
		$cont .= tpl_eval("liste-close");
		$ordernum = ($let) ? "title LIKE BINARY '".$let."%' AND date <= now() AND status != '0'" : "date <= now() AND status != '0'";
		$cont .= num_article("pagenum", $conf['name'], $listnum, $field, "id", "_media", "cid", $ordernum, $confm['nump']);
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function view() {
	global $prefix, $db, $admin_file, $conf, $confu, $confm;
	$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
	$word = isset($_GET['word']) ? text_filter($_GET['word']) : "";
	$cwhere = catmids($conf['name'], "m.cid");
	$result = $db->sql_query("SELECT m.cid, m.name, m.title, m.subtitle, m.year, m.director, m.roles, m.description, m.createdby, m.duration, m.lang, m.note, m.format, m.quality, m.size, m.released, m.links, m.date, m.acomm, m.votes, m.totalvotes, m.hits, m.status, c.title, c.description, c.img, u.user_name FROM ".$prefix."_media AS m LEFT JOIN ".$prefix."_categories AS c ON (m.cid = c.id) LEFT JOIN ".$prefix."_users AS u ON (m.uid = u.user_id) WHERE m.id = '".$id."' AND m.date <= now() AND m.status != '0' ".$cwhere);
	if ($db->sql_numrows($result) == 1) {
		$db->sql_query("UPDATE ".$prefix."_media SET hits = hits+1 WHERE id = '".$id."'");
		list($catid, $uname, $title, $subtitle, $year, $director, $roles, $description, $createdby, $duration, $lang, $note, $format, $quality, $size, $released, $links, $date, $acomm, $votes, $totalvotes, $hits, $status, $ctitle, $cdesc, $cimg, $user_name) = $db->sql_fetchrow($result);
		$ptitle = ($subtitle) ? $title." ".urldecode($confm['mdefis'])." ".$subtitle : $title;
		$pagetitle = (intval($catid)) ? $conf['defis']." "._MEDIA." ".$conf['defis']." ".$ctitle." ".$conf['defis']." ".$ptitle : $conf['defis']." "._MEDIA." ".$conf['defis']." ".$ptitle;
		head($pagetitle, $description);
		$cont = navigate(_MEDIA, $confm['viewcat']);
		if ($catid) $cont .= tpl_eval("cat-navi", catlink($conf['name'], $catid, $confm['defis'], _MEDIA));
		if ($confm['viewcat']) $cont .= categories($conf['name'], $confm['tabcol'], $confm['subcat'], $confm['catdesc'], 0);
		$cdesc = ($cdesc) ? $cdesc : $ctitle;
		$ctitle = ($ctitle) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_cat\">".cutstr($ctitle, 15)."</a>" : "";
		$cimg = ($cimg) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_icat\"><img src=\"".img_find("categories/".$cimg)."\" alt=\"".$cdesc."\" title=\"".$cdesc."\"></a>" : "";
		$post = ($confm['autor']) ? (($user_name) ? user_info($user_name) : (($uname) ? $uname : $confu['anonym'])) : "";
		$post = ($post) ? "<span title=\""._POSTEDBY."\" class=\"sl_post\">".$post."</span>" : "";
		$date = ($confm['date']) ? "<span title=\""._CHNGSTORY."\" class=\"sl_date\">".format_time($date)."</span>" : "";
		$reads = ($confm['read']) ? "<span title=\""._READS."\" class=\"sl_views\">".$hits."</span>" : "";
		$arating = ajax_rating(1, $id, $conf['name'], $votes, $totalvotes, "");
		$admin = (is_moder($conf['name'])) ? add_menu("<a href=\"".$admin_file.".php?op=media_add&amp;id=".$id."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=media_delete&amp;id=".$id."\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$ptitle."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>") : "";
		$favorites = favorview($id, $conf['name']);
		$goback = "<span OnClick=\"javascript:history.go(-1);\" title=\""._BACK."\" class=\"sl_but_back\">"._BACK."</span>";
		$broc = ($confm['broc'] == 1 && $status != "2") ? "<a OnClick=\"javascript: Location('index.php?name=".$conf['name']."&amp;op=broken&amp;id=".$id."');\" title=\""._BROCMEDIA."\" class=\"sl_but_blue\">"._COMPLAINT."</a>" : "";
		
		$year = ($year) ? _MYEAR.": ".$year : "";
		$director = ($director) ? _MDIRECTOR.": ".$director : "";
		$roles = ($roles) ? _MROLES.": ".$roles : "";
		$createdby = ($createdby) ? _MCREATEDBY.": ".$createdby : "";
		$duration = ($duration) ? _MDURATION.": ".$duration : "";
		$lang = ($lang) ? _LANGUAGE.": ".$lang : "";
		$format = ($format) ? _MFORMAT.": ".$format : "";
		$quality = ($quality) ? _MQUALITY.": ".$quality : "";
		$size = ($size) ? _MSIZE.": ".$size : "";
		$released = ($released) ? _MRELEASED.": ".$released : "";
		$note = ($note) ? bb_decode($note, $conf['name']) : "";
		if ($links) {
			if ((is_user() && $confm['hide'] == "0") || $confm['hide'] == "1") {
				$links = explode(",", $links);
				$e = 1;
				$i = 0;
				$mlinks = "";
				foreach($links as $val) {
					if ($val != "") {
						if (substr($val, 0, 4) == "ed2k") {
							$esize = explode("|", $val);
							$size = ($esize[3]) ? _SIZE.": ".files_size($esize[3]) : "";
							$elink = "<a href=\"".$val."\" target=\"_blank\" title=\""._URL." ".$e." - ".$size."\" class=\"sl_ed2k\">"._URL." ".$e." - ".$size."</a>";
							$mlinks .= (!$i) ? $elink : "<br>".$elink;
							$e++;
						} else {
							$hlink = "<a href=\"".$val."\" target=\"_blank\" title=\""._URL.": ".url_types($val)."\" class=\"sl_http\">"._URL.": ".url_types($val)."</a>";
							$mlinks .= (!$i) ? $hlink : "<br>".$hlink;
						}
						$i++;
					}
				}
			} else {
				$mlinks = tpl_warn("warn", _HIDETEXT, "", "", "info");
			}
		}
		$cont .= tpl_eval("basic", $catid, $cimg, $ctitle, $id, search_color($ptitle, $word), search_color(bb_decode($description, $conf['name']), $word), "", $post, $date, $reads, "", "", $arating, $admin, $favorites, $goback, "", "", "", "", $broc, "", "", $year, $director, $roles, $createdby, $duration, $lang, $format, $quality, $size, $released, $note, _MURLS, $mlinks);
		if ($confm['link']) {
			$plimit = intval($confm['linknum']);
			list($count) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_media WHERE cid = '".$catid."' AND id != '".$id."' AND date <= now() AND status != '0'"));
			if ($count >= $plimit) {
				$random = mt_rand(0, $count - $plimit);
				$result = $db->sql_query("SELECT id, title, subtitle, description, date FROM ".$prefix."_media WHERE cid = '".$catid."' AND id != '".$id."' AND date <= now() AND status != '0' ORDER BY date DESC LIMIT ".$random.", ".$plimit);
				$cont .= tpl_eval("assoc-open", _CATASSOC);
				while(list($aid, $title, $subtitle, $hometext, $time) = $db->sql_fetchrow($result)) {
					$title = ($subtitle) ? $title." ".urldecode($confm['mdefis'])." ".$subtitle : $title;
					$adate = ($confm['date']) ? "<span title=\""._CHNGSTORY."\" class=\"sl_date\">"._CHNGSTORY.": ".format_time($time)."</span>" : "";
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
	global $prefix, $db, $conf, $confm;
	$id = intval($_GET['id']);
	if ($confm['broc'] == 1 && $id) {
		$db->sql_query("UPDATE ".$prefix."_media SET status = '2' WHERE id = '".$id."' AND status != '0'");
		head($conf['defis']." "._MEDIA." ".$conf['defis']." "._BROCMEDIA, _BROCNOTEM);
		echo navigate(_BROCMEDIA).tpl_warn("warn", _BROCNOTEM, "?name=".$conf['name']."&amp;op=view&amp;id=".$id, 5, "info");
		foot();
	} else {
		header("Location: index.php?name=".$conf['name']);
	}
}

function add() {
	global $prefix, $db, $user, $conf, $confu, $confm, $stop;
	if ((is_user() && $confm['add'] == 1) || (!is_user() && $confm['addquest'] == 1)) {
		head($conf['defis']." "._MEDIA." ".$conf['defis']." "._ADD, _ADDNOTEM);
		$cont = navigate(_ADD);
		if ($stop) $cont .= tpl_warn("warn", $stop, "", "", "warn");
		$date = getdate();
		$title = save_text($_POST['title'], 1);
		$subtitle = save_text($_POST['subtitle'], 1);
		$mtitle = isset($subtitle) ? $title." ".urldecode($confm['mdefis'])." ".$subtitle : $title;
		$cid = intval($_POST['cid']);
		$myear =  isset($_POST['year']) ? intval($_POST['year']) : $date[year];
		$director = save_text($_POST['director']);
		$roles = save_text($_POST['roles']);
		$description = save_text($_POST['description']);
		$createdby = save_text($_POST['createdby']);
		$duration = save_text($_POST['duration']);
		$mlang = text_filter($_POST['lang']);
		$note = save_text($_POST['note']);
		$mformat = text_filter($_POST['format']);
		$mquality = text_filter($_POST['quality']);
		$size = save_text($_POST['size']);
		$released = save_text($_POST['released']);
		$links = $_POST['links'];
		$postname = text_filter(substr($_POST['postname'], 0, 25));
		if ($description) $cont .= preview($mtitle, $description, $note, "", $conf['name']);
		$cont .= tpl_warn("warn", _ADDNOTEM, "", "", "info");
		$cont .= tpl_eval("open");
		$cont .= "<form name=\"post\" action=\"index.php?name=".$conf['name']."\" method=\"post\"><table class=\"sl_table_form\">";
		if (is_user()) {
			$cont .= "<tr><td>"._YOURNAME.":</td><td>".text_filter(substr($user[1], 0, 25))."</td></tr>";
		} else {
			$postname = ($postname) ? $postname : $confu['anonym'];
			$cont .= "<tr><td>"._YOURNAME.":</td><td><input type=\"text\" name=\"postname\" value=\"".$postname."\" class=\"sl_field ".$conf['style']."\" placeholder=\""._YOURNAME."\" required></td></tr>";
		}
		$cont .= "<tr><td>"._MTITLE.":</td><td><input type=\"text\" name=\"title\" value=\"".$title."\" maxlength=\"100\" class=\"sl_field ".$conf['style']."\" placeholder=\""._MTITLE."\" required></td></tr>"
		."<tr><td>"._MSUBTITLE.":</td><td><input type=\"text\" name=\"subtitle\" value=\"".$subtitle."\" maxlength=\"100\" class=\"sl_field ".$conf['style']."\" placeholder=\""._MSUBTITLE."\"></td></tr>"
		."<tr><td>"._CATEGORY.":</td><td>".getcat($conf['name'], $cid, "cid", $conf['style'], "<option value=\"\">"._HOMECAT."</option>")."</td></tr>"
		."<tr><td>"._MYEAR.":</td><td><select name=\"year\" class=\"sl_field ".$conf['style']."\">";
		$year = $date[year] - 100;
		while($year <= ($date[year] + 1)) {
			$sel = ($year == $myear) ? " selected" : "";
			$cont .= "<option value=\"".$year."\"".$sel.">".$year."</option>";
			$year++;
		}
		$cont .= "</select></td></tr>"
		."<tr><td>"._MDIRECTOR.":</td><td><input type=\"text\" name=\"director\" value=\"".$director."\" maxlength=\"100\" class=\"sl_field ".$conf['style']."\" placeholder=\""._MDIRECTOR."\"></td></tr>"
		."<tr><td>"._MROLES.":</td><td><input type=\"text\" name=\"roles\" value=\"".$roles."\" maxlength=\"255\" class=\"sl_field ".$conf['style']."\" placeholder=\""._MROLES."\"></td></tr>"
		."<tr><td>"._DESCRIPTION.":</td><td>".textarea("1", "description", $description, $conf['name'], "10", _DESCRIPTION, "1")."</td></tr>"
		."<tr><td>"._MCREATEDBY.":</td><td><input type=\"text\" name=\"createdby\" value=\"".$createdby."\" maxlength=\"100\" class=\"sl_field ".$conf['style']."\" placeholder=\""._MCREATEDBY."\"></td></tr>"
		."<tr><td>"._MDURATION.":</td><td><input type=\"text\" name=\"duration\" value=\"".$duration."\" maxlength=\"100\" class=\"sl_field ".$conf['style']."\" placeholder=\""._MDURATION."\"></td></tr>"
		."<tr><td>"._LANGUAGE.":</td><td><select name=\"lang\" class=\"sl_field ".$conf['style']."\">";
		$lang = explode(",", $confm['lang']);
		foreach($lang as $val) {
			$sel = ($val == $mlang && $val != "") ? " selected" : "";
			$cont .= "<option value=\"".$val."\"".$sel.">".$val."</option>";
		}
		$cont .= "</select></td></tr>"
		."<tr><td>"._NOTE.":</td><td>".textarea("2", "note", $note, $conf['name'], "5", _NOTE, "0")."</td></tr>"
		."<tr><td>"._MFORMAT.":</td><td><select name=\"format\" class=\"sl_field ".$conf['style']."\">"
		."<option value=\"\">"._NO_INFO."</option>";
		$format = explode(",", $confm['format']);
		foreach($format as $val) {
			$sel = ($val == $mformat && $val != "") ? " selected" : "";
			$cont .= "<option value=\"".$val."\"".$sel.">".$val."</option>";
		}
		$cont .= "</select></td></tr>"
		."<tr><td>"._MQUALITY.":</td><td><select name=\"quality\" class=\"sl_field ".$conf['style']."\">"
		."<option value=\"\">"._NO_INFO."</option>";
		$quality = explode(",", $confm['quality']);
		foreach($quality as $val) {
			$sel = ($val == $mquality && $val != "") ? " selected" : "";
			$cont .= "<option value=\"".$val."\"".$sel.">".$val."</option>";
		}
		$cont .= "</select></td></tr>"
		."<tr><td>"._MSIZE.":</td><td><input type=\"text\" name=\"size\" value=\"".$size."\" maxlength=\"100\" class=\"sl_field ".$conf['style']."\" placeholder=\""._MSIZE."\"></td></tr>"
		."<tr><td>"._MRELEASED.":</td><td><input type=\"text\" name=\"released\" value=\"".$released."\" maxlength=\"100\" class=\"sl_field ".$conf['style']."\" placeholder=\""._MRELEASED."\"></td></tr>"
		."<tr><td colspan=\"2\">";
		$i = 0;
		while($i < $confm['links']) {
			$a = $i + 1;
			$display = ($i != 0 && $links[$i] == "") ? " sl_none" : "";
			$cont .= "<table id=\"med".$i."\" class=\"sl_table_form".$display."\"><tr><td><a OnClick=\"HideShow('med".$a."', 'slide', 'up', 500);\" title=\""._ADD."\" class=\"sl_plus\">"._URL." - ".$a.":</a></td><td><input type=\"text\" name=\"links[]\" value=\"".text_filter($links[$i])."\" class=\"sl_field ".$conf['style']."\"></td></tr></table>";
			$i++;
		}
		$cont .= "</td></tr>"
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
	global $prefix, $db, $user, $conf, $confm, $stop;
	if ((is_user() && $confm['add'] == 1) || (!is_user() && $confm['addquest'] == 1)) {
		$postname = text_filter(substr($_POST['postname'], 0, 25));
		$cid = intval($_POST['cid']);
		$title = save_text($_POST['title'], 1);
		$subtitle = save_text($_POST['subtitle'], 1);
		$year = intval($_POST['year']);
		$director = save_text($_POST['director']);
		$roles = save_text($_POST['roles']);
		$description = save_text($_POST['description']);
		$createdby = save_text($_POST['createdby']);
		$duration = save_text($_POST['duration']);
		$lang = text_filter($_POST['lang']);
		$note = save_text($_POST['note']);
		$format = text_filter($_POST['format']);
		$quality = text_filter($_POST['quality']);
		$size = save_text($_POST['size']);
		$released = save_text($_POST['released']);
		$links = text_filter(implode(",", str_replace(",", ".", $_POST['links'])));
		$stop = array();
		if (!$title) $stop[] = _CERROR;
		if (!$description) $stop[] = _CERROR1;
		if (!$postname && !is_user()) $stop[] = _CERROR3;
		if (captcha_check()) $stop[] = _SECCODEINCOR;
		if ($db->sql_numrows($db->sql_query("SELECT title, subtitle FROM ".$prefix."_media WHERE title = '".$title."' AND subtitle = '".$subtitle."'")) > 0) $stop[] = _MEDIAEXIST;
		if (!$stop && $_POST['posttype'] == "save") {
			$postid = (is_user()) ? intval($user[0]) : "";
			$uname = (!is_user()) ? $postname : "";
			$ip = getip();
			$db->sql_query("INSERT INTO ".$prefix."_media (id, cid, uid, name, title, subtitle, year, director, roles, description, createdby, duration, lang, note, format, quality, size, released, links, date, ip_sender, status) VALUES (NULL, '".$cid."', '".$postid."', '".$uname."', '".$title."', '".$subtitle."', '".$year."', '".$director."', '".$roles."', '".$description."', '".$createdby."', '".$duration."', '".$lang."', '".$note."', '".$format."', '".$quality."', '".$size."', '".$released."', '".$links."', now(), '".$ip."', '0')");
			update_points(25);
			$puname = (is_user()) ? $user[1] : $postname;
			addmail($confm['addmail'], $conf['name'], $puname, _MEDIA);
			head($conf['defis']." "._MEDIA." ".$conf['defis']." "._ADD, _UPLOADFINISHM);
			echo navigate(_ADD).tpl_warn("warn", _UPLOADFINISHM, "?name=".$conf['name'], 10, "info");
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
	media();
	break;
	
	case "liste":
	liste();
	break;
	
	case "view":
	view();
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