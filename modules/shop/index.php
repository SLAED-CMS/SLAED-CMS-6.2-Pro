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
include("config/config_shop.php");

function navigate($title, $cat="") {
	global $conf, $confso;
	$scat = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
	$catf = ($scat) ? "&amp;cat=".$scat : "";
	$home = "<a href=\"index.php?name=".$conf['name']."\" title=\""._SHOP."\" class=\"sl_but_navi\">"._HOME."</a>";
	$best = ($confso['rate']) ? "<a href=\"index.php?name=".$conf['name'].$catf."&amp;sort=1\" title=\""._BEST."\" class=\"sl_but_navi\">"._BEST."</a>" : "";
	$pop = ($confso['rate']) ? "<a href=\"index.php?name=".$conf['name'].$catf."&amp;sort=2\" title=\""._POP."\" class=\"sl_but_navi\">"._POP."</a>" : "";
	$liste = "<a href=\"index.php?name=".$conf['name']."&amp;op=liste\" title=\""._LIST."\" class=\"sl_but_navi\">"._LIST."</a>";
	$catshow = ($cat) ? "<a OnClick=\"CloseOpen('sl_close_1', 1);\" title=\""._CATVORH."\" class=\"sl_but_navi\">"._CATEGORIES."</a>" : "";
	return tpl_eval("navi", $title, $conf['name'], "", $home, $best, $pop, $liste, "", $catshow);
}

function shop() {
	global $prefix, $db, $conf, $confso, $admin_file, $home, $user;
	$cwhere = catmids($conf['name'], "p.cid");
	$shopnum = user_news($user[3], $confso['num']);
	$sort = isset($_GET['sort']) ? intval($_GET['sort']) : 0;
	$scat = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
	if (!$scat && $sort && $confso['rate']) {
		$caton = 0;
		$field = "sort=".$sort."&amp;";
		if ($sort == 1) {
			$orderby = "p.totalvotes DESC";
			$shop_logo = _BEST;
		} else {
			$orderby = "p.count DESC";
			$shop_logo = _POP;
		}
		$order = "WHERE p.time <= now() AND p.active != '0' ".$cwhere." ORDER BY ".$orderby;
		$ordernum = "time <= now() AND active != '0'";
		$pagetitle = $conf['defis']." "._SHOP." ".$conf['defis']." ".$shop_logo;
	} elseif ($scat) {
		$field = ($sort) ? "cat=".$scat."&amp;sort=".$sort."&amp;" : "cat=".$scat."&amp;";
		$orderby = ($sort) ? (($sort == 1) ? "p.totalvotes DESC" : "p.count DESC") : "p.fix DESC, p.time DESC";
		$shop_logo = ($sort) ? (($sort == 1) ? " ".$conf['defis']." "._BEST : " ".$conf['defis']." "._POP) : "";
		
		list($cat_title) = $db->sql_fetchrow($db->sql_query("SELECT title FROM ".$prefix."_categories WHERE id = '".$scat."'"));
		list($caid) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_categories WHERE parentid = '".$scat."'"));
		$caton = ($caid) ? 1 : 0;
		
		$order = "WHERE (p.cid = '".$scat."' OR p.assoc REGEXP '[[:<:]]".$scat."[[:>:]]') AND p.time <= now() AND p.active != '0' ".$cwhere." ORDER BY ".$orderby;
		$ordernum = "(cid = '".$scat."' OR assoc REGEXP '[[:<:]]".$scat."[[:>:]]') AND time <= now() AND active != '0'";
		$pagetitle = $conf['defis']." "._SHOP." ".$conf['defis']." ".$cat_title.$shop_logo;
	} else {
		$caton = 1;
		$field = "";
		$hwhere = ($home) ? "AND p.ihome = '1'" : "";
		$hnwhere = ($home) ? "AND ihome = '1'" : "";
		$order = "WHERE p.time <= now() AND p.active != '0' ".$hwhere." ".$cwhere." ORDER BY p.fix DESC, p.time DESC";
		$ordernum = "time <= now() AND active != '0' ".$hnwhere;
		$shop_logo = _SHOP;
		$pagetitle = $conf['defis']." ".$shop_logo;
	}
	head($pagetitle);
	if (!$home || ($home && $confso['homcat'])) {
		$cont = ($scat) ? navigate($cat_title, $caton) : navigate($shop_logo, $caton);
		if ($scat) $cont .= tpl_eval("cat-navi", catlink($conf['name'], $scat, $confso['defis'], _SHOP));
		if ($caton == 1) $cont .= categories($conf['name'], $confso['tabcol'], $confso['subcat'], $confso['catdesc'], $scat);
	}
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $shopnum;
	$offset = intval($offset);
	$result = $db->sql_query("SELECT p.id, p.cid, p.time, p.title, p.text, p.preis, p.acomm, p.com, p.count, p.votes, p.totalvotes, c.title, c.description, c.img FROM ".$prefix."_products AS p LEFT JOIN ".$prefix."_categories AS c ON (p.cid = c.id) ".$order." LIMIT ".$offset.", ".$shopnum);
	if ($db->sql_numrows($result) > 0) {
		$cont .= "<div id=\"shop\"><div id=\"repkasse\">".show_kasse()."</div></div>";
		$width_tab = 100 / $confso['bascol'];
		$i = 1;
		$cont .= "<table>";
		while (list($id, $catid, $time, $ptitle, $text, $ppreis, $acomm, $pcom, $counter, $votes, $totalvotes, $ctitle, $cdesc, $cimg) = $db->sql_fetchrow($result)) {
			$cdesc = ($cdesc) ? $cdesc : $ctitle;
			$ctitle = ($ctitle) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_cat\">".cutstr($ctitle, 15)."</a>" : "";
			$cimg = ($cimg) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_icat\"><img src=\"".img_find("categories/".$cimg)."\" alt=\"".$cdesc."\" title=\"".$cdesc."\"></a>" : "";
			$title = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."\" title=\"".$ptitle."\">".$ptitle."</a> ".new_graphic($time);
			$read = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."\" title=\"".$ptitle."\" class=\"sl_but_read\">"._READMORE."</a>";
			
			#### In Bearbeitung
			$post = ($confso['autor']) ? (($user_name) ? user_info($user_name) : (($uname) ? $uname : $confu['anonym'])) : "";
			$post = ($post) ? "<span title=\""._POSTEDBY."\" class=\"sl_post\">".$post."</span>" : "";
			####
			
			$date = ($confso['date']) ? "<span title=\""._CHNGSTORY."\" class=\"sl_date\">".format_time($time)."</span>" : "";
			$reads = ($confso['read']) ? "<span title=\""._READS."\" class=\"sl_views\">".$counter."</span>" : "";
			$comm = ($acomm) ? "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."#comm\" title=\""._COMMENTS."\" class=\"sl_coms\">".$pcom."</a>" : "";
			$arating = ajax_rating(0, $id, $conf['name'], $votes, $totalvotes, "");
			$admin = (is_moder($conf['name'])) ? add_menu("<a href=\"".$admin_file.".php?op=shop_products_add&amp;id=".$id."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=shop_products_admin&amp;typ=d&amp;id=".$id."&amp;refer=1\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$ptitle."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>") : "";
			
			#### In Bearbeitung
			$prtitle = ($opreis) ? _NPREIS : _PREIS;
			$preis = "<span title=\"".$prtitle."\" class=\"sl_shop_price\">".$prtitle.": ".$ppreis." ".$confso['valute']."</span>";
			$opreis = ($opreis) ? "<span title=\""._OPREIS."\" class=\"sl_shop_oprice\">"._OPREIS.": ".$ppreis." ".$confso['valute']."</span>" : "";
			$discount = ($discount) ? "<span title=\""._DISCOUNT."\" class=\"sl_shop_discount\">"._DISCOUNT.": ".$ppreis." ".$confso['valute']."</span>" : "";
			####
			
			$cart = "<a OnClick=\"AjaxLoad('GET', '0', 'kasse', 'go=2&amp;op=add_kasse&amp;id=".$id."', ''); AddBasket('".$id."'); return false;\" title=\""._SCART."\" class=\"sl_shop_add\">"._SCART."</a>";
			$kasse = "<a href=\"index.php?name=".$conf['name']."&amp;op=kasse\" title=\""._SCACH."\" class=\"sl_shop_kasse\">"._SCACH."</a>";
			if (($i - 1) % $confso['bascol'] == 0) $cont .= "<tr>";
			$cont .= "<td style=\"width: ".$width_tab."%;\">";
			$cont .= tpl_func("basic", $catid, $cimg, $ctitle, $id, $title, bb_decode($text, $conf['name']), $read, $post, $date, $reads, "", $comm, $arating, $admin, "", "", "", $preis, $opreis, $discount, $cart, $kasse);
			$cont .= "</td>";
			if ($i % $confso['bascol'] == 0) $cont .= "</tr>";
			$i++;
		}
		$cont .= "</table>";
		$cont .= num_article("pagenum", $conf['name'], $shopnum, $field, "id", "_products", "cid", $ordernum, $confso['nump']);
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function liste() {
	global $prefix, $db, $conf, $confso;
	$cwhere = catmids($conf['name'], "p.cid");
	$listnum = intval($confso['listnum']);
	$let = isset($_GET['let']) ? mb_substr($_GET['let'], 0, 1, "utf-8") : "";
	if ($let) {
		$field = "op=liste&amp;let=".urlencode($let)."&amp;";
		$pagetitle = $conf['defis']." "._SHOP." ".$conf['defis']." "._LIST." ".$conf['defis']." ".$let;
		$order = "WHERE UCASE(p.title) LIKE BINARY '".$let."%' AND p.time <= now() AND p.active != '0'";
	} else {
		$field = "op=liste&amp;";
		$pagetitle = $conf['defis']." "._SHOP." ".$conf['defis']." "._LIST;
		$order = "WHERE p.time <= now() AND p.active != '0'";
	}
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $listnum;
	$offset = intval($offset);
	$result = $db->sql_query("SELECT p.id, p.cid, p.time, p.title, p.preis, c.title FROM ".$prefix."_products AS p LEFT JOIN ".$prefix."_categories AS c ON (p.cid = c.id) ".$order." ".$cwhere." ORDER BY p.fix DESC, p.time DESC LIMIT ".$offset.", ".$listnum);
	head($pagetitle);
	$cont = navigate(_LIST);
	if ($db->sql_numrows($result) > 0) {
		$letter = ($confso['letter']) ? letter($conf['name']) : "";
		$cont .= tpl_eval("liste-open", $letter, _ID, _TITLE, _CATEGORY, _PREIS, _DATE);
		while (list($id, $catid, $time, $title, $preis, $ctitle) = $db->sql_fetchrow($result)) {
			$title = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."\" title=\"".$title."\">".cutstr($title, 40)."</a> ".new_graphic($time);
			$ctitle = ($ctitle) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$ctitle."\">".cutstr($ctitle, 15)."</a>" : _NO;
			$preis = $preis." ".$confso['valute'];
			$cont .= tpl_func("liste-basic", $id, $title, $ctitle, $preis, format_time($time));
		}
		$cont .= tpl_eval("liste-close");
		$ordernum = ($let) ? "title LIKE BINARY '".$let."%' AND time <= now() AND active != '0'" : "time <= now() AND active != '0'";
		$cont .= num_article("pagenum", $conf['name'], $listnum, $field, "id", "_products", "cid", $ordernum, $confso['nump']);
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function view() {
	global $prefix, $db, $conf, $confso, $admin_file;
	$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
	$word = isset($_GET['word']) ? text_filter($_GET['word']) : "";
	$cwhere = catmids($conf['name'], "p.cid");
	$result = $db->sql_query("SELECT p.cid, p.time, p.title, p.text, p.bodytext, p.preis, p.vote, p.assoc, p.acomm, p.count, p.votes, p.totalvotes, c.title, c.description, c.img FROM ".$prefix."_products AS p LEFT JOIN ".$prefix."_categories AS c ON (p.cid = c.id) WHERE p.id = '".$id."' AND p.time <= now() AND p.active != '0' ".$cwhere);
	if ($db->sql_numrows($result) == 1) {
		$db->sql_query("UPDATE ".$prefix."_products SET count = count+1 WHERE id = '".$id."'");
		list($catid, $time, $ptitle, $text, $bodytext, $ppreis, $vote, $passoc, $acomm, $counter, $votes, $totalvotes, $ctitle, $cdesc, $cimg) = $db->sql_fetchrow($result);
		$pagetitle = (intval($catid)) ? $conf['defis']." "._SHOP." ".$conf['defis']." ".$ctitle." ".$conf['defis']." ".$ptitle : $conf['defis']." "._SHOP." ".$conf['defis']." ".$ptitle;
		head($pagetitle, $text, $bodytext);
		$cont = navigate(_SHOP, $confso['viewcat']);
		if ($catid) $cont .= tpl_eval("cat-navi", catlink($conf['name'], $catid, $confso['defis'], _SHOP));
		if ($confso['viewcat']) $cont .= categories($conf['name'], $confso['tabcol'], $confso['subcat'], $confso['catdesc'], 0);
		$cont .= "<div id=\"shop\"><div id=\"repkasse\">".show_kasse()."</div></div>";
		$text = ($bodytext) ? $text."<br><br>".$bodytext : $text;
		$cdesc = ($cdesc) ? $cdesc : $ctitle;
		$ctitle = ($ctitle) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_cat\">".cutstr($ctitle, 15)."</a>" : "";
		$cimg = ($cimg) ? "<a href=\"index.php?name=".$conf['name']."&amp;cat=".$catid."\" title=\"".$cdesc."\" class=\"sl_icat\"><img src=\"".img_find("categories/".$cimg)."\" alt=\"".$cdesc."\" title=\"".$cdesc."\"></a>" : "";
		
		#### In Bearbeitung
		$post = ($confso['autor']) ? (($user_name) ? user_info($user_name) : (($uname) ? $uname : $confu['anonym'])) : "";
		$post = ($post) ? "<span title=\""._POSTEDBY."\" class=\"sl_post\">".$post."</span>" : "";
		####
		
		$date = ($confso['date']) ? "<span title=\""._CHNGSTORY."\" class=\"sl_date\">".format_time($time)."</span>" : "";
		$reads = ($confso['read']) ? "<span title=\""._READS."\" class=\"sl_views\">".$counter."</span>" : "";
		$arating = ajax_rating(1, $id, $conf['name'], $votes, $totalvotes, "");
		$admin = (is_moder($conf['name'])) ? add_menu("<a href=\"".$admin_file.".php?op=shop_products_add&amp;id=".$id."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=shop_products_admin&amp;typ=d&amp;id=".$id."\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$ptitle."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>") : "";
		$favorites = favorview($id, $conf['name']);
		$goback = "<span OnClick=\"javascript:history.go(-1);\" title=\""._BACK."\" class=\"sl_but_back\">"._BACK."</span>";
		$voting = ($vote) ? "<div id=\"rep".$conf['name']."\">".avoting_view($vote, $conf['name'])."</div>" : "";
		
		#### In Bearbeitung
		$prtitle = ($opreis) ? _NPREIS : _PREIS;
		$preis = "<span title=\"".$prtitle."\" class=\"sl_shop_price\">".$prtitle.": ".$ppreis." ".$confso['valute']."</span>";
		$opreis = ($opreis) ? "<span title=\""._OPREIS."\" class=\"sl_shop_oprice\">"._OPREIS.": ".$ppreis." ".$confso['valute']."</span>" : "";
		$discount = ($discount) ? "<span title=\""._DISCOUNT."\" class=\"sl_shop_discount\">"._DISCOUNT.": ".$ppreis." ".$confso['valute']."</span>" : "";
		####
		
		$cart = "<a OnClick=\"AjaxLoad('GET', '0', 'kasse', 'go=2&amp;op=add_kasse&amp;id=".$id."', ''); AddBasket('".$id."'); return false;\" title=\""._SCART."\" class=\"sl_shop_add\">"._SCART."</a>";
		$kasse = "<a href=\"index.php?name=".$conf['name']."&amp;op=kasse\" title=\""._SCACH."\" class=\"sl_shop_kasse\">"._SCACH."</a>";
		$cont .= tpl_eval("basic", $catid, $cimg, $ctitle, $id, search_color($ptitle, $word), search_color(bb_decode($text, $conf['name']), $word), "", $post, $date, $reads, "", "", $arating, $admin, $favorites, $goback, $voting, $preis, $opreis, $discount, $cart, $kasse);
		if ($confso['assoc']) {
			$limit = intval($confso['assocnum']);
			list($count) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_products WHERE cid IN (".$passoc.") AND id != '".$id."' AND time <= now() AND active != '0'"));
			if ($count >= $limit) {
				$random = mt_rand(0, $count - $limit);
				$result = $db->sql_query("SELECT id, time, title, text FROM ".$prefix."_products WHERE cid IN (".$passoc.") AND id != '".$id."' AND time <= now() AND active != '0' ORDER BY time DESC LIMIT ".$random.", ".$limit);
				$cont .= tpl_eval("assoc-open", _ASPROD);
				while (list($aid, $time, $title, $hometext) = $db->sql_fetchrow($result)) {
					$adate = ($confso['date']) ? "<span title=\""._CHNGSTORY."\" class=\"sl_date\">"._CHNGSTORY.": ".format_time($time)."</span>" : "";
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

function kasse() {
	global $prefix, $db, $conf, $confu, $confso, $stop;
	$userinfo = getusrinfo();
	$sender_id = (is_user()) ? $userinfo['user_id'] : 0;
	$sender_login = (is_user()) ? $userinfo['user_name'] : $confu['anonym'];
	$sender_email = (is_user() && !isset($_POST['sender_email'])) ? $userinfo['user_email'] : $_POST['sender_email'];
	$sender_dom = (is_user() && !isset($_POST['sender_dom'])) ? $userinfo['user_website'] : $_POST['sender_dom'];
	$sender_name = $_POST['sender_name'];
	$sender_adr = $_POST['sender_adr'];
	$sender_tel = $_POST['sender_tel'];
	$sender_message = text_filter($_POST['sender_message']);
	$cookies = (preg_match("/[^0-9,]/", base64_decode($_COOKIE['shop']))) ? "" : base64_decode($_COOKIE['shop']);
	$id_partner = (intval($_COOKIE['part'])) ? $_COOKIE['part'] : "";
	$stop = (!$cookies) ? _SERRORP : "";
	$form = "<form method=\"post\" action=\"index.php?name=".$conf['name']."\"><table class=\"sl_table_form\">"
	."<tr><td>"._C_PIN.":</td><td><input type=\"text\" name=\"sender_name\" value=\"".$sender_name."\" class=\"sl_field ".$conf['style']."\" placeholder=\""._C_PINB."\" required></td></tr>"
	."<tr><td>"._C_PIP.":</td><td><input type=\"text\" name=\"sender_adr\" value=\"".$sender_adr."\" class=\"sl_field ".$conf['style']."\" placeholder=\""._C_PIPB."\" required></td></tr>"
	."<tr><td>"._C_TEL.":</td><td><input type=\"text\" name=\"sender_tel\" value=\"".$sender_tel."\" class=\"sl_field ".$conf['style']."\" placeholder=\""._C_TELB."\" required></td></tr>"
	."<tr><td>"._C_MAIL.":</td><td><input type=\"email\" name=\"sender_email\" value=\"".$sender_email."\" class=\"sl_field ".$conf['style']."\" placeholder=\""._C_MAILB."\" required></td></tr>"
	."<tr><td>"._SDOM.":</td><td><input type=\"url\" name=\"sender_dom\" value=\"".$sender_dom."\" class=\"sl_field ".$conf['style']."\" placeholder=\""._SDOMB."\"></td></tr>"
	."<tr><td>"._C_MESSAGE.":</td><td><textarea name=\"sender_message\" cols=\"65\" rows=\"5\" class=\"sl_field ".$conf['style']."\" placeholder=\""._C_MESSAGE."\">".$sender_message."</textarea></td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"opi\" value=\"1\"><input type=\"hidden\" name=\"op\" value=\"kasse\"><input type=\"submit\" value=\""._C_SEND."\" class=\"sl_but_blue\"></td></tr></table></form>";
	head($conf['defis']." "._SHOP." ".$conf['defis']." "._C_TITLE);
	$cont = navigate(_C_TITLE);
	if (!$_POST['opi'] && $cookies) {
		$cont .= "<div id=\"repkasse\">".show_kasse()."</div>";
		$cont .= tpl_eval("title", _C_TITLE).tpl_eval("open").$form.tpl_eval("close");
	} elseif ($_POST['opi'] && $cookies) {
		$stop = array();
		checkemail($sender_email);
		if (!$sender_name || !$sender_adr || !$sender_tel || !$sender_email) {
			$stop[] = _ERROR_ALL;
		} elseif ($stop) {
			$stop[] = $stop;
		}
		if (!$stop) {
			$result = $db->sql_query("SELECT id, title, preis FROM ".$prefix."_products WHERE id IN (".$cookies.")");
			while(list($id, $title, $preis) = $db->sql_fetchrow($result)) {
				$massiv = explode(",", $cookies);
				$i = 0;
				foreach ($massiv as $val) {
					if ($val == $id) $i++;
				}
				$preis = $preis * $i;
				$preistotal += $preis;
				$content .= "<tr><td>".$id."</td><td>".$i."</td><td>".$title."</td><td>".$preis." ".$confso['valute']."</td></td></tr>";
			}
			$pinfo = "<table style=\"width: 100%;\"><tr><th>"._ID."</th><th>"._QUANTITY."</th><th>"._PRODUCT."</th><th>"._PREIS."</th></tr>".$content."<tr><td colspan=\"5\"><br><b>"._PARTNERGES.": ".$preistotal." ".$confso['valute']."</b></td></tr></table>";
			if ($confso['mailsend']) {
				$amail = ($confso['mail']) ? $confso['mail'] : $conf['adminmail'];
				$subject = $conf['sitename']." - "._C_TITLE;
				$msg = $conf['sitename']." - "._C_TITLE."<br><br>";
				$msg .= $pinfo."<br><br>";
				$msg .= "<b>"._PERSONALINFO."</b><br><br>";
				$msg .= _NICKNAME.": ".$sender_login."<br>";
				$msg .= _C_PIN.": ".$sender_name."<br>";
				$msg .= _C_PIP.": ".$sender_adr."<br>";
				$msg .= _C_TEL.": ".$sender_tel."<br>";
				$msg .= _C_MAIL.": ".$sender_email."<br>";
				$msg .= _SITEURL.": ".$sender_dom."<br>";
				$msg .= _C_MESSAGE.": ".$sender_message;
				mail_send($amail, $sender_email, $subject, $msg, 1, 1);
			}
			if ($confso['mailuser']) {
				$amail = ($confso['mail']) ? $confso['mail'] : $conf['adminmail'];
				$subject = $conf['sitename']." - "._C_TITLE;
				$msg = $conf['sitename']." - "._C_TITLE."<br><br>";
				$msg .= bb_decode($confso['sende'], $conf['name'])."<br><br>";
				$msg .= $pinfo."<br><br>";
				$msg .= "<b>"._PERSONALINFO."</b><br><br>";
				$msg .= _NICKNAME.": ".$sender_login."<br>";
				$msg .= _C_PIN.": ".$sender_name."<br>";
				$msg .= _C_PIP.": ".$sender_adr."<br>";
				$msg .= _C_TEL.": ".$sender_tel."<br>";
				$msg .= _C_MAIL.": ".$sender_email."<br>";
				$msg .= _SDOM.": ".$sender_dom."<br>";
				$msg .= _C_MESSAGE.": ".$sender_message;
				mail_send($sender_email, $amail, $subject, $msg, 0, 3);
			}
			$massiv = explode(",", $cookies);
			foreach ($massiv as $val) {
				if ($val != "") {
					$sender_regdate = time();
					$db->sql_query("INSERT INTO ".$prefix."_clients VALUES(NULL, '".$sender_id."', '".$val."', '".$id_partner."', '0', '".$sender_name."', '".$sender_adr."', '".$sender_tel."', '".$sender_email."', '".$sender_dom."', '".$sender_regdate."', '0', '0', '2')");
				}
			}
			setcookie("shop", false);
			setcookie("part", false);
			update_points(39);
			$cont .= tpl_warn("warn", bb_decode($confso['sende'], $conf['name']), "", "", "info");
		} else {
			$cont .= tpl_warn("warn", $stop, "", "", "warn");
			$cont .= "<div id=\"repkasse\">".show_kasse()."</div>";
			$cont .= tpl_eval("open").$form.tpl_eval("close");
		}
	} else {
		$cont .= tpl_warn("warn", $stop, "?name=".$conf['name'], 5, "warn");
	}
	echo $cont;
	foot();
}

function part() {
	global $conf, $confso;
	$id = isset($_GET['id']) ? intval($_GET['id']) : "";
	if ($id) setcookie("part", $id, time() + $confso['part_t']);
	header("Location: index.php?name=".$conf['name']);
}

function clients() {
	global $prefix, $db, $user, $conf, $confso;
	if (is_user() && is_active('shop')) {
		$user_id = intval($user[0]);
		head($conf['defis']." "._CLIENTINFO);
		$cont = navigate(_CLIENTINFO);
		$cont .= navi();
		$result = $db->sql_query("SELECT c.id, c.id_user, c.id_product, c.name, c.adres, c.phone, c.email, c.website, c.regdate, c.enddate, c.info, c.active, u.user_id, u.user_name, p.id, p.title, p.preis FROM ".$prefix."_clients AS c LEFT JOIN ".$prefix."_users AS u ON (u.user_id = c.id_user) LEFT JOIN ".$prefix."_products AS p ON (p.id = c.id_product) WHERE c.id_user = '".$user_id."' ORDER BY c.id ASC");
		if ($db->sql_numrows($result) > 0) {
			$cont .= tpl_eval("open");
			$cont .= "<table class=\"sl_table_list_sort\"><thead class=\"sl_table_list_head\"><tr><th>"._ID."</th><th>"._PRODUCT."</th><th>"._L_DATE."</th><th>"._STATUS."</th><th>"._FUNCTIONS."</th></tr></thead><tbody class=\"sl_table_list_body\">";
			while(list($cid, $cid_user, $cid_product, $cname, $cadres, $cphone, $cemail, $cwebsite, $cregdate, $cenddate, $cinfo, $cactive, $user_id, $user_name, $pid, $ptitle, $ppreis) = $db->sql_fetchrow($result)) {
				$website = ($cwebsite) ? "<br>"._SITE.": ".$cwebsite : "";
				$note = ($cinfo) ? "<br>"._NOTE." : ".$cinfo : "";
				$cenddate = ($cenddate != "0") ? rest_time($cenddate) : _NO;
				$rechn = add_menu("<a href=\"index.php?name=".$conf['name']."&amp;op=rech&amp;id=".$cid."\" target=\"_blank\" title=\""._RECHN_B."\">"._RECHN_B."</a>");
				$cont .= "<tr id=\"".$cid."\">"
				."<td><a href=\"#".$cid."\" title=\"".$cid."\" class=\"sl_pnum\">".$cid."</a></td>"
				."<td>".title_tip(_PREIS.": ".$ppreis." ".$confso['valute'].$website.$note)."<span title=\"".$ptitle."\">".cutstr($ptitle, 35)."</span></td>"
				."<td>".$cenddate."</td>"
				."<td>".ad_status("", $cactive)."</td>"
				."<td>".$rechn."</td></tr>";
			}
			$cont .= "</tbody></table>";
			$cont .= tpl_eval("close");
		}
		$cont .= tpl_eval("open").bb_decode($confso['userinfo'], $conf['name']).tpl_eval("close");
		echo $cont;
		foot();
	} else {
		header("Location: index.php?name=".$conf['name']);
		exit;
	}
}

function rech() {
	global $prefix, $db, $conf, $confso, $theme;
	if (is_user() && is_active('shop')) {
		$defis = urldecode($conf['defis']);
		$cid = intval($_GET['id']);
		$result = $db->sql_query("SELECT c.id, c.id_user, c.id_product, c.name, c.adres, c.phone, c.email, c.website, c.regdate, c.enddate, c.info, p.id, p.title, p.text, p.preis FROM ".$prefix."_clients AS c LEFT JOIN ".$prefix."_products AS p ON (p.id = c.id_product) WHERE c.id = '".$cid."' ORDER BY c.id ASC");
		if ($db->sql_numrows($result) > 0) {
			list($cid, $cid_user, $cid_product, $cname, $cadres, $cphone, $cemail, $cwebsite, $cregdate, $cenddate, $cinfo, $pid, $ptitle, $text, $ppreis) = $db->sql_fetchrow($result);
			$cont = "<!DOCTYPE html>\n";
			$cont .= "<html>\n";
			$cont .= "<head>\n";
			$cont .= "<meta charset=\""._CHARSET."\">\n";
			if (file_exists("templates/".$theme."/style/style.css")) {
				$cont .= "<link rel=\"stylesheet\" href=\"templates/".$theme."/style/style.css\">\n";
			} else {
				$cont .= "<link rel=\"stylesheet\" href=\"templates/".$theme."/style.css\">\n";
			}
			$cont .= "<title>".$conf['sitename']." ".$defis." "._CLIENTINFO." ".$defis." "._RECHN."</title></head>"
			."<body><table style=\"width: 640px; margin: 5%;\"><tr><td colspan=\"2\"><hr></td></tr><tr><td style=\"width: 40%;\"><img src=\"".img_find("logos/".$conf['site_logo'])."\" alt=\"".$conf['sitename']."\"></td><td style=\"text-align: right;\">".bb_decode($confso['shopinfo'], $conf['name'])."</td></tr><tr><td colspan=\"2\"><hr></td></tr><tr><td colspan=\"2\"><br><br>"._C_PIN.": ".$cname."<br>"._C_PIP.": ".$cadres."<br>"._C_TEL.": ".$cphone."<br>"._C_MAIL.": ".$cemail."<br><br><br></td></tr><tr><td colspan=\"2\"><hr></td></tr><tr><td><b>"._C_NAIM."</b></td><td style=\"text-align: right;\"><b>"._K_DATE.": ".date(_TIMESTRING, $cregdate)."</b></td></tr><tr><td colspan=\"2\"><hr></td></tr>";
			$cenddate = ($cenddate != "0") ? date(_TIMESTRING, $cenddate) : _UNLIMITED;
			$cont .= "<tr><td>"._PRODUCT.":</td><td style=\"text-align: right;\">".$ptitle."</td></tr>"
			."<tr><td>"._SDOM.":</td><td style=\"text-align: right;\">".$cwebsite."</td></tr>"
			."<tr><td>"._NOTE.":</td><td style=\"text-align: right;\">".$cinfo."</td></tr>"
			."<tr><td>"._LIZENS_END.":</td><td style=\"text-align: right;\">".$cenddate."</td></tr>"
			."<tr><td colspan=\"2\"><hr></td></tr>"
			."<tr><td colspan=\"2\"><b>"._PRODUCT_TEXT."</b></td></tr>"
			."<tr><td colspan=\"2\"><hr></td></tr>"
			."<tr><td colspan=\"2\">".bb_decode($text, $conf['name'])."</td></tr>"
			."<tr><td colspan=\"2\"><hr></td></tr>"
			."<tr><td colspan=\"2\" style=\"text-align: right;\"><b>"._PREIS_TEXT.": ".$ppreis." ".$confso['valute']."</b></td></tr>"
			."</table></body></html>";
			echo $cont;
		}
	} else {
		header("Location: index.php?name=".$conf['name']);
		exit;
	}
}

function partners() {
	global $prefix, $db, $conf, $confso, $stop;
	if (is_user() && is_active('shop')) {
		$userinfo = getusrinfo();
		$user_id = intval($userinfo['user_id']);
		$sender_email = $userinfo['user_email'];
		$sender_dom = $userinfo['user_website'];
		head($conf['defis']." "._PARTNERINFO);
		$cont = navigate(_PARTNERINFO);
		$cont .= navi();
		$result = $db->sql_query("SELECT id, id_user, name, adres, phone, email, website, webmoney, paypal, regdate, rest, bek, active FROM ".$prefix."_partners WHERE id_user = '".$user_id."'");
		if ($db->sql_numrows($result) > 0) {
			list($paid, $paid_user, $paname, $paadres, $paphone, $paemail, $pawebsite, $pawebmoney, $papaypal, $paregdate, $parest, $pabek, $paactive) = $db->sql_fetchrow($result);
			if ($paactive == 2) {
				$cont .= tpl_warn("warn", _PARTNERADD_W, "", "", "info");
			} elseif ($paactive == 0) {
				$cont .= tpl_warn("warn", _PARTNER_AUS, "", "", "warn");
			} else {
				$result = $db->sql_query("SELECT c.id, c.id_user, c.id_product, c.id_partner, c.partner_proz, c.name, c.adres, c.phone, c.email, c.website, c.regdate, c.enddate, c.info, u.user_id, u.user_name, p.id, p.title, p.preis FROM ".$prefix."_clients AS c LEFT JOIN  ".$prefix."_users AS u ON (u.user_id = c.id_user) LEFT JOIN ".$prefix."_products AS p ON (p.id = c.id_product) WHERE c.id_partner = '".$user_id."' AND c.active != 2 ORDER BY c.id ASC");
				$partsum = 0;
				$partsumges = 0;
				$a = 0;
				if ($db->sql_numrows($result) > 0) {
					$content = "";
					while(list($cid, $cid_user, $cid_product, $cid_partner, $cpartner_proz, $cname, $cadres, $cphone, $cemail, $cwebsite, $cregdate, $cenddate, $cinfo, $uuser_id, $user_name, $pid, $ptitle, $ppreis) = $db->sql_fetchrow($result)) {
						$partsum = $ppreis / 100 * $cpartner_proz;
						$partsumges += $partsum;
						$content .= "<tr id=\"".$cid."\">"
						."<td><a href=\"#".$cid."\" title=\"".$cid."\" class=\"sl_pnum\">".$cid."</a></td>"
						."<td>".user_info($user_name)."</td>"
						."<td>".title_tip(_PREIS.": ".$ppreis." ".$confso['valute']."<br>"._DATE." : ".date(_TIMESTRING, $cregdate))."<span title=\"".$ptitle."\">".cutstr($ptitle, 35)."</span></td>"
						."<td>".$cpartner_proz." %</td>"
						."<td>".$partsum." ".$confso['valute']."</td></tr>";
						$a++;
					}
					$cont .= tpl_eval("open");
					$cont .= "<table class=\"sl_table_list_sort\"><thead class=\"sl_table_list_head\"><tr><th>"._ID."</th><th>"._NICKNAME."</th><th>"._PRODUCT."</th><th>"._PERCENT."</th><th>"._SUM."</th></tr></thead><tbody class=\"sl_table_list_body\">".$content."</tbody></table>";
					$cont .= tpl_eval("close");
				}
				$cont .= tpl_eval("open");
				$cont .= "<table class=\"sl_table_list_sort\"><thead class=\"sl_table_list_head\"><tr><th>"._CLIENTEN."</th><th>"._WEBMONEY."</th><th>"._PAYPAL."</th><th>"._PARTNERGES."</th><th>"._PARTNERREST."</th><th>"._PARTNERBEK."</th></tr></thead><tbody class=\"sl_table_list_body\">"
				."<tr><td>".$a."</td><td>".$pawebmoney."</td><td>".$papaypal."</td>"
				."<td>".$partsumges." ".$confso['valute']."</td><td>".$parest." ".$confso['valute']."</td><td>".$pabek." ".$confso['valute']."</td></tr></tbody></table>";
				$cont .= tpl_eval("close");
				$cont .= tpl_warn("warn", _C_26.": ".str_replace("[id]", $user_id, $confso['partlink']), "", "", "info");
				$cont .= tpl_eval("open").bb_decode(str_replace("[id]", $user_id, $confso['partinfo2']), $conf['name']).tpl_eval("close");
			}
		} else {
			if ($stop) $cont .= tpl_warn("warn", $stop, "", "", "warn");
			$cont .= tpl_eval("open").bb_decode($confso['partinfo'], $conf['name']).tpl_eval("close");
			$cont .= tpl_eval("title", _PARTNERADD);
			$cont .= tpl_eval("open");
			$cont .= "<form method=\"post\" action=\"index.php?name=".$conf['name']."\"><table class=\"sl_table_form\">"
			."<tr><td>"._C_PIN.":</td><td><input type=\"text\" name=\"paname\" maxlength=\"255\" class=\"sl_field ".$conf['style']."\" placeholder=\""._C_PINB."\" required></td></tr>"
			."<tr><td>"._C_PIP.":</td><td><input type=\"text\" name=\"paadres\" maxlength=\"255\" class=\"sl_field ".$conf['style']."\" placeholder=\""._C_PIPB."\" required></td></tr>"
			."<tr><td>"._C_TEL.":</td><td><input type=\"text\" name=\"paphone\" maxlength=\"255\" class=\"sl_field ".$conf['style']."\" placeholder=\""._C_TELB."\" required></td></tr>"
			."<tr><td>"._EMAIL.":</td><td><input type=\"email\" value=\"".$sender_email."\" name=\"paemail\" maxlength=\"255\" class=\"sl_field ".$conf['style']."\" placeholder=\""._C_MAILB."\" required></td></tr>"
			."<tr><td>"._SITE.":</td><td><input type=\"url\" value=\"".$sender_dom."\" name=\"pawebsite\" maxlength=\"255\" class=\"sl_field ".$conf['style']."\" placeholder=\""._SDOMB."\"></td></tr>"
			."<tr><td>"._WEBMONEY.":</td><td><input type=\"text\" name=\"pawebmoney\" maxlength=\"255\" class=\"sl_field ".$conf['style']."\" placeholder=\""._C_WEBMONEYB."\"></td></tr>"
			."<tr><td>"._PAYPAL.":</td><td><input type=\"text\" name=\"papaypal\" maxlength=\"255\" class=\"sl_field ".$conf['style']."\" placeholder=\""._C_MAILB."\"></td></tr>"
			."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"paid_user\" value=\"".$user_id."\"><input type=\"hidden\" name=\"op\" value=\"partners_send\"><input type=\"submit\" value=\""._PARTNERSEND."\" class=\"sl_but_blue\"></td></tr></table></form>";
			$cont .= tpl_eval("close");
		}
		echo $cont;
		foot();
	} else {
		header("Location: index.php?name=".$conf['name']);
		exit;
	}
}

function partners_send() {
	global $prefix, $db, $user, $conf, $stop;
	if (is_user() && is_active('shop')) {
		$paname = text_filter($_POST['paname']);
		$paadres = text_filter($_POST['paadres']);
		$paphone = text_filter($_POST['paphone']);
		$paemail = text_filter($_POST['paemail']);
		$pawebsite = url_filter($_POST['pawebsite']);
		$pawebmoney = text_filter($_POST['pawebmoney']);
		$papaypal = text_filter($_POST['papaypal']);
		$paid_user = intval($_POST['paid_user']);
		$paregdate = time();
		checkemail($paemail);
		if (!$paname || !$paadres || !$paphone) $stop[] = _ERROR_ALL;
		if (!$stop) {
			$db->sql_query("INSERT INTO ".$prefix."_partners VALUES(NULL, '".$paid_user."', '".$paname."', '".$paadres."', '".$paphone."', '".$paemail."', '".$pawebsite."', '".$pawebmoney."', '".$papaypal."', '".$paregdate."', '0', '0', '2')");
			header("Location: index.php?name=".$conf['name']."&op=partners");
		} else {
			partners();
		}
	} else {
		header("Location: index.php?name=".$conf['name']);
		exit;
	}
}

switch($op) {
	default:
	shop();
	break;
	
	case "liste":
	liste();
	break;
	
	case "view":
	view();
	break;

	case "kasse":
	kasse();
	break;

	case "part":
	part();
	break;
	
	case "clients":
	clients();
	break;
	
	case "rech":
	rech();
	break;
	
	case "partners":
	partners();
	break;
	
	case "partners_send":
	partners_send();
	break;
}
?>