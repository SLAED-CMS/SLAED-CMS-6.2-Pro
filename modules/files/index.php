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
include("config/config_files.php");

function navigate($title, $cat="") {
	global $conf, $conff;
	$fcat = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
	$catf = ($fcat) ? "&amp;cat=".$fcat : "";
	$home = "<a href=\"index.php?name=".$conf['name']."\" title=\""._FILES."\" class=\"sl_but_navi\">"._HOME."</a>";
	$best = ($conff['rate']) ? "<a href=\"index.php?name=".$conf['name'].$catf."&amp;sort=1\" title=\""._BEST."\" class=\"sl_but_navi\">"._BEST."</a>" : "";
	$pop = ($conff['rate']) ? "<a href=\"index.php?name=".$conf['name'].$catf."&amp;sort=2\" title=\""._POP."\" class=\"sl_but_navi\">"._POP."</a>" : "";
	$liste = "<a href=\"index.php?name=".$conf['name']."&amp;op=liste\" title=\""._LIST."\" class=\"sl_but_navi\">"._LIST."</a>";
	$add = ((is_user() && $conff['add'] == 1) || (!is_user() && $conff['addquest'] == 1)) ? "<a href=\"index.php?name=".$conf['name']."&amp;op=add\" title=\""._ADD."\" class=\"sl_but_navi\">"._ADD."</a>" : "";
	$catshow = ($cat) ? "<a OnClick=\"CloseOpen('sl_close_1', 1);\" title=\""._CATVORH."\" class=\"sl_but_navi\">"._CATEGORIES."</a>" : "";
	return tpl_eval("navi", $title, $conf['name'], "", $home, $best, $pop, $liste, $add, $catshow);
}

function files() {
	global $prefix, $db, $admin_file, $user, $conf, $confu, $conff, $home;
	$cwhere = catmids($conf['name'], "f.cid");
	$filenum = user_news($user[3], $conff['num']);
	$sort = isset($_GET['sort']) ? intval($_GET['sort']) : 0;
	$fcat = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
	if (!$fcat && $sort && $conff['rate']) {
		$caton = 0;
		$field = "sort=".$sort."&amp;";
		if ($sort == 1) {
			$orderby = "f.totalvotes DESC";
			$files_logo = _BEST;
		} else {
			$orderby = "f.hits DESC";
			$files_logo = _POP;
		}
		$order = "WHERE f.date <= now() AND f.status != '0' ".$cwhere." ORDER BY ".$orderby;
		$ordernum = "date <= now() AND status != '0'";
		$pagetitle = $conf['defis']." "._FILES." ".$conf['defis']." ".$files_logo;
	} elseif ($fcat) {
		$field = ($sort) ? "cat=".$fcat."&amp;sort=".$sort."&amp;" : "cat=".$fcat."&amp;";
		$orderby = ($sort) ? (($sort == 1) ? "f.totalvotes DESC" : "f.hits DESC") : "f.date DESC";
		$files_logo = ($sort) ? (($sort == 1) ? " ".$conf['defis']." "._BEST : " ".$conf['defis']." "._POP) : "";
		
		list($cat_title) = $db->sql_fetchrow($db->sql_query("SELECT title FROM ".$prefix."_categories WHERE id = '".$fcat."'"));
		list($caid) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_categories WHERE parentid = '".$fcat."'"));
		$caton = ($caid) ? 1 : 0;
		
		$order = "WHERE f.cid = '".$fcat."' AND f.date <= now() AND f.status != '0' ".$cwhere." ORDER BY ".$orderby;
		$ordernum = "cid = '".$fcat."' AND date <= now() AND status != '0'";
		$pagetitle = $conf['defis']." "._FILES." ".$conf['defis']." ".$cat_title.$files_logo;
	} else {
		$caton = 1;
		$field = "";
		$hwhere = ($home) ? "AND f.ihome = '1'" : "";
		$hnwhere = ($home) ? "AND ihome = '1'" : "";
		$order = "WHERE f.date <= now() AND f.status != '0' ".$hwhere." ".$cwhere." ORDER BY f.date DESC";
		$ordernum = "date <= now() AND status != '0' ".$hnwhere;
		$files_logo = _FILES;
		$pagetitle = $conf['defis']." ".$files_logo;
	}
	head($pagetitle);
	if (!$home || ($home && $conff['homcat'])) {
		$cont = ($fcat) ? navigate($cat_title, $caton) : navigate($files_logo, $caton);
		if ($fcat) $cont .= tpl_eval("cat-navi", catlink($conf['name'], $fcat, $conff['defis'], _FILES));
		if ($caton == 1) $cont .= categories($conf['name'], $conff['tabcol'], $conff['subcat'], $conff['catdesc'], $fcat);
	}
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $filenum;
	$offset = intval($offset);
	$result = $db->sql_query("SELECT f.lid, f.cid, f.name, f.title, f.description, f.date, f.counter, f.acomm, f.votes, f.totalvotes, f.totalcomments, f.hits, c.title, c.description, c.img, u.user_name FROM ".$prefix."_files AS f LEFT JOIN ".$prefix."_categories AS c ON (f.cid = c.id) LEFT JOIN ".$prefix."_users AS u ON (f.uid = u.user_id) ".$order." LIMIT ".$offset.", ".$filenum);
	if ($db->sql_numrows($result) > 0) {
		while (list($id, $catid, $uname, $ftitle, $description, $time, $counter, $acomm, $votes, $totalvotes, $comm, $hits, $ctitle, $cdesc, $cimg, $user_name) = $db->sql_fetchrow($result)) {
			$cdesc = ($cdesc) ? $cdesc : $ctitle;
			$ctitle = ($ctitle) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_cat\">".cutstr($ctitle, 15)."</a>" : "";
			$cimg = ($cimg) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_icat\"><img src=\"".img_find("categories/".$cimg)."\" alt=\"".$cdesc."\" title=\"".$cdesc."\"></a>" : "";
			$title = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."\" title=\"".$ftitle."\">".$ftitle."</a> ".new_graphic($time);
			$read = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."\" title=\"".$ftitle."\" class=\"sl_but_read\">"._READMORE."</a>";
			$post = ($conff['autor']) ? (($user_name) ? user_info($user_name) : (($uname) ? $uname : $confu['anonym'])) : "";
			$post = ($post) ? "<span title=\""._POSTEDBY."\" class=\"sl_post\">".$post."</span>" : "";
			$date = ($conff['date']) ? "<span title=\""._CHNGSTORY."\" class=\"sl_date\">".format_time($time)."</span>" : "";
			$reads = ($conff['read']) ? "<span title=\""._READS."\" class=\"sl_views\">".$counter."</span>" : "";
			$hits = ($conff['hits']) ? "<span title=\""._FILEHITS."\" class=\"sl_down\">".$hits."</span>" : "";
			$comm = ($acomm) ? "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."#comm\" title=\""._COMMENTS."\" class=\"sl_coms\">".$comm."</a>" : "";
			$arating = ajax_rating(0, $id, $conf['name'], $votes, $totalvotes, "");
			$admin = (is_moder($conf['name'])) ? add_menu("<a href=\"".$admin_file.".php?op=files_add&amp;id=".$id."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=files_delete&amp;id=".$id."&amp;refer=1\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$ftitle."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>") : "";
			$cont .= tpl_func("basic", $catid, $cimg, $ctitle, $id, $title, bb_decode($description, $conf['name']), $read, $post, $date, $reads, $hits, $comm, $arating, $admin, "", "", "");
		}
		$cont .= num_article("pagenum", $conf['name'], $filenum, $field, "lid", "_files", "cid", $ordernum, $conff['nump']);
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function liste() {
	global $prefix, $db, $conf, $confu, $conff;
	$cwhere = catmids($conf['name'], "f.cid");
	$listnum = intval($conff['listnum']);
	$let = isset($_GET['let']) ? mb_substr($_GET['let'], 0, 1, "utf-8") : "";
	if ($let) {
		$field = "op=liste&amp;let=".urlencode($let)."&amp;";
		$pagetitle = $conf['defis']." "._FILES." ".$conf['defis']." "._LIST." ".$conf['defis']." ".$let;
		$order = "WHERE UCASE(f.title) LIKE BINARY '".$let."%' AND f.date <= now() AND f.status != '0'";
	} else {
		$field = "op=liste&amp;";
		$pagetitle = $conf['defis']." "._FILES." ".$conf['defis']." "._LIST;
		$order = "WHERE f.date <= now() AND f.status != '0'";
	}
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $listnum;
	$offset = intval($offset);
	$result = $db->sql_query("SELECT f.lid, f.cid, f.name, f.title, f.date, c.title, u.user_name FROM ".$prefix."_files AS f LEFT JOIN ".$prefix."_categories AS c ON (f.cid = c.id) LEFT JOIN ".$prefix."_users AS u ON (f.uid = u.user_id) ".$order." ".$cwhere." ORDER BY date DESC LIMIT ".$offset.", ".$listnum);
	head($pagetitle);
	$cont = navigate(_LIST);
	if ($db->sql_numrows($result) > 0) {
		$letter = ($conff['letter']) ? letter($conf['name']) : "";
		$cont .= tpl_eval("liste-open", $letter, _ID, _TITLE, _CATEGORY, _POSTER, _DATE);
		while(list($id, $catid, $uname, $title, $time, $ctitle, $user_name) = $db->sql_fetchrow($result)) {
			$title = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."\" title=\"".$title."\">".cutstr($title, 40)."</a> ".new_graphic($time);
			$ctitle = ($ctitle) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$ctitle."\">".cutstr($ctitle, 15)."</a>" : _NO;
			$post = ($user_name) ? user_info($user_name) : (($uname) ? $uname : $confu['anonym']);
			$cont .= tpl_func("liste-basic", $id, $title, $ctitle, $post, format_time($time));
		}
		$cont .= tpl_eval("liste-close");
		$ordernum = ($let) ? "title LIKE BINARY '".$let."%' AND date <= now() AND status != '0'" : "date <= now() AND status != '0'";
		$cont .= num_article("pagenum", $conf['name'], $listnum, $field, "lid", "_files", "cid", $ordernum, $conff['nump']);
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function view() {
	global $prefix, $db, $admin_file, $conf, $confu, $conff;
	$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
	$cwhere = catmids($conf['name'], "f.cid");
	$word = isset($_GET['word']) ? text_filter($_GET['word']) : "";
	$result = $db->sql_query("SELECT f.cid, f.name, f.title, f.url, f.description, f.bodytext, f.date, f.filesize, f.version, f.email, f.homepage, f.counter, f.acomm, f.votes, f.totalvotes, f.hits, f.status, c.title, c.description, c.img, u.user_name FROM ".$prefix."_files AS f LEFT JOIN ".$prefix."_categories AS c ON (f.cid = c.id) LEFT JOIN ".$prefix."_users AS u ON (f.uid = u.user_id) WHERE f.lid = '".$id."' AND f.date <= now() AND f.status != '0' ".$cwhere);
	if ($db->sql_numrows($result) == 1) {
		$db->sql_query("UPDATE ".$prefix."_files SET counter = counter+1 WHERE lid = '".$id."'");
		list($catid, $uname, $title, $url, $description, $bodytext, $date, $fsize, $fversion, $aemail, $ahomepage, $counter, $acomm, $votes, $totalvotes, $hits, $status, $ctitle, $cdesc, $cimg, $user_name) = $db->sql_fetchrow($result);
		$pagetitle = (intval($catid)) ? $conf['defis']." "._FILES." ".$conf['defis']." ".$ctitle." ".$conf['defis']." ".$title : $conf['defis']." "._FILES." ".$conf['defis']." ".$title;
		head($pagetitle, $description, $bodytext);
		$cont = navigate(_FILES, $conff['viewcat']);
		if ($catid) $cont .= tpl_eval("cat-navi", catlink($conf['name'], $catid, $conff['defis'], _FILES));
		if ($conff['viewcat']) $cont .= categories($conf['name'], $conff['tabcol'], $conff['subcat'], $conff['catdesc'], 0);
		$text = ($bodytext) ? $description."<br><br>".$bodytext : $description;
		$cdesc = ($cdesc) ? $cdesc : $ctitle;
		$ctitle = ($ctitle) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_cat\">".cutstr($ctitle, 15)."</a>" : "";
		$cimg = ($cimg) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_icat\"><img src=\"".img_find("categories/".$cimg)."\" alt=\"".$cdesc."\" title=\"".$cdesc."\"></a>" : "";
		$post = ($conff['autor']) ? (($user_name) ? user_info($user_name) : (($uname) ? $uname : $confu['anonym'])) : "";
		$post = ($post) ? "<span title=\""._POSTEDBY."\" class=\"sl_post\">".$post."</span>" : "";
		$date = ($conff['date']) ? "<span title=\""._CHNGSTORY."\" class=\"sl_date\">".format_time($date)."</span>" : "";
		$reads = ($conff['read']) ? "<span title=\""._READS."\" class=\"sl_views\">".$counter."</span>" : "";
		$hits = ($conff['hits']) ? "<span title=\""._FILEHITS."\" class=\"sl_down\">".$hits."</span>" : "";
		$arating = ajax_rating(1, $id, $conf['name'], $votes, $totalvotes, "");
		$admin = (is_moder($conf['name'])) ? add_menu("<a href=\"".$admin_file.".php?op=files_add&amp;id=".$id."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=files_delete&amp;id=".$id."\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$title."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>") : "";
		$favorites = favorview($id, $conf['name']);
		$goback = "<span OnClick=\"javascript:history.go(-1);\" title=\""._BACK."\" class=\"sl_but_back\">"._BACK."</span>";
		$size = _SIZE.": ".files_size($fsize);
		$vers = _VERSION.": ".$fversion;
		if (is_user() || $conff['down'] == "1") {
			$onclick = (!$conff['stream']) ? "OnClick=\"window.open('".$url."')\"" : "";
			$down = "<form action=\"index.php?name=".$conf['name']."\" method=\"post\" style=\"display: inline\">"
			."<input type=\"hidden\" name=\"id\" value=\"".$id."\">"
			."<input type=\"hidden\" name=\"op\" value=\"geturl\">"
			."<input type=\"submit\" ".$onclick." value=\""._UPLOAD."\" class=\"sl_but_green\">"
			."</form>";
		}
		$broc = ($conff['broc'] == 1 && $status != "2") ? "<a OnClick=\"javascript: Location('index.php?name=".$conf['name']."&amp;op=broken&amp;id=".$id."');\" title=\""._BROCFILE."\" class=\"sl_but_blue\">"._COMPLAINT."</a>" : "";
		$email = ($aemail) ? _AUEMAIL.": ".anti_spam($aemail) : "";
		$home = ($ahomepage) ? _SITE.": ".domain($ahomepage) : "";
		$cont .= tpl_eval("basic", $catid, $cimg, $ctitle, $id, search_color($title, $word), search_color(bb_decode($text, $conf['name']), $word), "", $post, $date, $reads, $hits, "", $arating, $admin, $favorites, $goback, "", $size, $vers, $down, $broc, $email, $home);
		if ($conff['link']) {
			$plimit = intval($conff['linknum']);
			list($count) = $db->sql_fetchrow($db->sql_query("SELECT Count(lid) FROM ".$prefix."_files WHERE cid = '".$catid."' AND lid != '".$id."' AND date <= now() AND status != '0'"));
			if ($count >= $plimit) {
				$random = mt_rand(0, $count - $plimit);
				$result = $db->sql_query("SELECT lid, title, description, date FROM ".$prefix."_files WHERE cid = '".$catid."' AND lid != '".$id."' AND date <= now() AND status != '0' ORDER BY date DESC LIMIT ".$random.", ".$plimit);
				$cont .= tpl_eval("assoc-open", _CATASSOC);
				while(list($aid, $title, $hometext, $time) = $db->sql_fetchrow($result)) {
					$adate = ($conff['date']) ? "<span title=\""._CHNGSTORY."\" class=\"sl_date\">"._CHNGSTORY.": ".format_time($time)."</span>" : "";
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
	global $prefix, $db, $conf, $conff;
	$id = intval($_GET['id']);
	if ($conff['broc'] == 1 && $id) {
		$db->sql_query("UPDATE ".$prefix."_files SET status = '2' WHERE lid = '".$id."' AND status != '0'");
		head($conf['defis']." "._FILES." ".$conf['defis']." "._BROCFILE, _BROCNOTE);
		echo navigate(_BROCFILE).tpl_warn("warn", _BROCNOTE, "?name=".$conf['name']."&amp;op=view&amp;id=".$id, 5, "info");
		foot();
	} else {
		header("Location: index.php?name=".$conf['name']);
	}
}

function add() {
	global $db, $prefix, $user, $conf, $confu, $conff, $stop;
	if ((is_user() && $conff['add'] == 1) || (!is_user() && $conff['addquest'] == 1)) {
		if (is_user()) {
			$userinfo = getusrinfo();
			$authormail = isset($_POST['authormail']) ? text_filter($_POST['authormail']) : $userinfo['user_email'];
			$authorurl = isset($_POST['authorurl']) ? url_filter($_POST['authorurl']) : $userinfo['user_website'];
		} else {
			$authormail = isset($_POST['authormail']) ? text_filter($_POST['authormail']) : "";
			$authorurl = isset($_POST['authorurl']) ? url_filter($_POST['authorurl']) : "http://";
		}
		$filelink = isset($_POST['filelink']) ? url_filter($_POST['filelink']) : "http://";
		$info = _ADDFNOTE;
		if ($conff['upload'] == 1) $info .= sprintf(_ADDFNOTE2, str_replace(",", ", ", $conff['typefile']), files_size($conff['max_size']));
		$info .= " "._ADDFNOTE3;
		head($conf['defis']." "._FILES." ".$conf['defis']." "._ADD, $info);
		$cont = navigate(_ADD);
		if ($stop) $cont .= tpl_warn("warn", $stop, "", "", "warn");
		$title = save_text($_POST['title'], 1);
		$cid = intval($_POST['cid']);
		$description = save_text($_POST['description']);
		$bodytext = save_text($_POST['bodytext']);
		$postname = text_filter(substr($_POST['postname'], 0, 25));
		$fversion = text_filter($_POST['fversion']);
		$file_size = intval($_POST['file_size']) ? intval($_POST['file_size']) : "";
		if ($description) $cont .= preview($title, $description, $bodytext, "", $conf['name']);
		$cont .= tpl_warn("warn", $info, "", "", "info");
		$cont .= tpl_eval("open");
		$cont .= "<form name=\"post\" enctype=\"multipart/form-data\" action=\"index.php?name=".$conf['name']."\" method=\"post\"><table class=\"sl_table_form\">";
		if (is_user()) {
			$cont .= "<tr><td>"._YOURNAME.":</td><td>".text_filter(substr($user[1], 0, 25))."</td></tr>";
		} else {
			$postname = ($postname) ? $postname : $confu['anonym'];
			$cont .= "<tr><td>"._YOURNAME.":</td><td><input type=\"text\" name=\"postname\" value=\"".$postname."\" class=\"sl_field ".$conf['style']."\" placeholder=\""._YOURNAME."\" required></td></tr>";
		}
		$cont .= "<tr><td>"._AUEMAIL.":</td><td><input type=\"email\" name=\"authormail\" value=\"".$authormail."\" maxlength=\"100\" class=\"sl_field ".$conf['style']."\" placeholder=\""._AUEMAIL."\" required></td></tr>"
		."<tr><td>"._NAME.":</td><td><input type=\"text\" name=\"title\" value=\"".$title."\" maxlength=\"100\" class=\"sl_field ".$conf['style']."\" placeholder=\""._NAME."\" required></td></tr>"
		."<tr><td>"._CATEGORY.":</td><td>".getcat($conf['name'], $cid, "cid", $conf['style'], "<option value=\"\">"._HOMECAT."</option>")."</td></tr>"
		."<tr><td>"._TEXT.":</td><td>".textarea("1", "description", $description, $conf['name'], "5", _TEXT, "1")."</td></tr>"
		."<tr><td>"._ENDTEXT.":</td><td>".textarea("2", "bodytext", $bodytext, $conf['name'], "15", _ENDTEXT, "0")."</td></tr>"
		."<tr><td>"._SITE.":</td><td><input type=\"url\" name=\"authorurl\" value=\"".$authorurl."\" maxlength=\"100\" class=\"sl_field ".$conf['style']."\" placeholder=\""._SITE."\"></td></tr>";
		if ($conff['upload'] == 1) $cont .= "<tr><td>"._FILE_USER.":</td><td><input type=\"file\" name=\"userfile\" class=\"sl_field ".$conf['style']."\"></td></tr>";
		$cont .= "<tr><td>"._URL.":</td><td><input type=\"url\" name=\"filelink\" value=\"".$filelink."\" maxlength=\"100\" class=\"sl_field ".$conf['style']."\" placeholder=\""._URL."\"></td></tr>"
		."<tr><td>"._VERSION.":</td><td><input type=\"text\" name=\"fversion\" value=\"".$fversion."\" maxlength=\"10\" class=\"sl_field ".$conf['style']."\" placeholder=\""._VERSION."\"></td></tr>"
		."<tr><td>"._SIZE.":</td><td><input type=\"text\" name=\"file_size\" value=\"".$file_size."\" maxlength=\"10\" class=\"sl_field ".$conf['style']."\" placeholder=\""._SIZE."\"></td></tr>"
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
	global $prefix, $db, $user, $conf, $conff, $stop;
	if ((is_user() && $conff['add'] == 1) || (!is_user() && $conff['addquest'] == 1)) {
		$postname = text_filter(substr($_POST['postname'], 0, 25));
		$title = save_text($_POST['title'], 1);
		$description = save_text($_POST['description']);
		$bodytext = save_text($_POST['bodytext']);
		$url = url_filter($_POST['filelink']);
		$authorurl = url_filter($_POST['authorurl']);
		$authormail = text_filter($_POST['authormail']);
		$fversion = text_filter($_POST['fversion']);
		$filesize = intval($_POST['file_size']);
		$cid = intval($_POST['cid']);
		$stop = array();
		if (!$title) $stop[] = _CERROR;
		if (!$description) $stop[] = _CERROR1;
		if (!$postname && !is_user()) $stop[] = _CERROR3;
		checkemail($authormail);
		if (captcha_check()) $stop[] = _SECCODEINCOR;
		if ($db->sql_numrows($db->sql_query("SELECT title FROM ".$prefix."_files WHERE title = '".$title."'")) > 0) $stop[] = _MEDIAEXIST;
		$userid = isset($user[0]) ? intval($user[0]) : '0';
		$filename = upload(1, $conff['temp'], $conff['typefile'], $conff['max_size'], "files", "1600", "1600", $userid);
		$url = ($filename) ? $conff['temp']."/".$filename : $url;
		$filesize = ($filename) ? filesize($url) : $filesize;
		if ($stop) {
			$stop = $stop;
		} elseif (!$url && $_POST['posttype'] == "save") {
			$stop[] = _UPLOADEROR2;
		}
		if (!$stop && $_POST['posttype'] == "save") {
			$postid = (is_user()) ? intval($user[0]) : "";
			$uname = (!is_user()) ? $postname : "";
			$ip = getip();
			$db->sql_query("INSERT INTO ".$prefix."_files (lid, cid, uid, name, title, description, bodytext, url, date, filesize, version, email, homepage, ip_sender, status) VALUES (NULL, '".$cid."', '".$postid."', '".$uname."', '".$title."', '".$description."', '".$bodytext."', '".$url."', now(), '".$filesize."', '".$fversion."', '".$authormail."', '".$authorurl."', '".$ip."', '0')");
			update_points(9);
			$puname = (is_user()) ? $user[1] : $postname;
			addmail($conff['addmail'], $conf['name'], $puname, _FILES);
			head($conf['defis']." "._FILES." ".$conf['defis']." "._ADD, _UPLOADFINISH);
			echo navigate(_ADD).tpl_warn("warn", _UPLOADFINISH, "?name=".$conf['name'], 10, "info");
			foot();
		} else {
			add();
		}
	} else {
		header("Location: index.php?name=".$conf['name']);
	}
}

function geturl() {
	global $prefix, $db, $conf, $conff;
	$id = intval($_POST['id']);
	if (($id && is_user()) || ($id && $conff['down'] == "1")) {
		$db->sql_query("UPDATE ".$prefix."_files SET hits = hits+1 WHERE lid = '".$id."'");
		list($ftitle, $url) = $db->sql_fetchrow($db->sql_query("SELECT title, url FROM ".$prefix."_files WHERE lid = '".$id."'"));
		update_points(11);
		if ($conff['stream'] == 2) {
			$type = strtolower(substr(strrchr($url, "."), 1));
			stream($url, gen_pass(10).".".$type);
		} elseif ($conff['stream'] == 1) {
			stream($url, preg_replace("#(.*?)\/#i", "", $url));
		} else {
			$info = sprintf(_NOTEDOWNLOAD, $ftitle, "<a href=\"".$url."\" target=\"_blank\" title=\""._UPLOAD."\">".$url."</a>");
			head($conf['defis']." "._FILES." ".$conf['defis']." "._UPLOAD, $info);
			$cont = navigate(_FILES);
			$cont .= tpl_warn("warn", $info, "", "", "info");
			$cont .= get_page($conf['name']);
			echo $cont;
			foot();
		}
	} else {
		header("Location: index.php?name=".$conf['name']);
	}
}

switch($op) {
	default:
	files();
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