<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("MODULE_FILE")) {
	header("Location: ../../index.php");
	exit;
}
get_lang($conf['name']);
include("config/config_auto_links.php");

function navigate($title, $cat="") {
	global $conf;
	$home = "<a href=\"index.php?name=".$conf['name']."\" title=\""._A_LINKS."\" class=\"sl_but_navi\">"._HOME."</a>";
	$new = "<a href=\"index.php?name=".$conf['name']."&amp;sort=1\" title=\""._NEW."\" class=\"sl_but_navi\">"._NEW."</a>";
	$pop = "<a href=\"index.php?name=".$conf['name']."&amp;sort=2\" title=\""._POP."\" class=\"sl_but_navi\">"._POP."</a>";
	$add = "<a href=\"index.php?name=".$conf['name']."&amp;op=add\" title=\""._ADD."\" class=\"sl_but_navi\">"._ADD."</a>";
	return tpl_eval("navi", $title, $conf['name'], "", $home, $new, $pop, "", $add, "");
}

function autolink() {
	global $prefix, $db, $admin_file, $user, $conf, $confal, $home;
	$newnum = user_news($user[3], $confal['num']);
	$word = (isset($_GET['word'])) ? text_filter($_GET['word']) : "";
	$sort = (isset($_GET['sort'])) ? intval($_GET['sort']) : 0;
	if ($sort) {
		$field = "sort=".$sort."&amp;";
		if ($sort == 1) {
			$order = "added";
			$auto_logo = _NEW;
		} else {
			$order = "outs";
			$auto_logo = _POP;
		}
		$pagetitle = $conf['defis']." "._A_LINKS." ".$conf['defis']." ".$auto_logo;
	} else {
		$order = "hits";
		$field = "";
		$auto_logo = _A_LINKS;
		$pagetitle = $conf['defis']." ".$auto_logo;
	}
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $newnum;
	$offset = intval($offset);
	$a = ($num) ? $offset + 1 : 1;
	$result = $db->sql_query("SELECT id, sitename, description, hits, outs, added FROM ".$prefix."_auto_links WHERE hits != '0' ORDER BY ".$order." DESC LIMIT ".$offset.", ".$newnum);
	head($pagetitle);
	if (!$home) $cont = navigate($auto_logo);
	if ($db->sql_numrows($result) > 0) {
		while (list($id, $sitename, $description, $hits, $outs, $time) = $db->sql_fetchrow($result)) {
			$title = search_color($sitename, $word)." ".new_graphic($time);
			$read = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$id."\" target=\"_blank\" title=\"".$sitename."\" class=\"sl_but_read\">"._DOWNLLINK."</a>";
			$date = "<span title=\""._CHNGSTORY."\" class=\"sl_date\">".format_time($time)."</span>";
			$reads = "<span title=\""._OUTS."\" class=\"sl_outs\">".$outs."</span>";
			$hits = "<span title=\""._HITS."\" class=\"sl_hits\">".$hits."</span>";
			$admin = (is_moder($conf['name'])) ? add_menu("<a href=\"".$admin_file.".php?op=auto_links_add&amp;id=".$id."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=auto_links_delete&amp;id=".$id."&amp;refer=1\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$sitename."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>") : "";
			$cont .= tpl_func("basic", "", "", "", $id, $title, search_color(bb_decode($description, $conf['name']), $word), $read, "", $date, $reads, $hits, "", "", $admin, "", "", "");
		}
		$cont .= num_article("pagenum", $conf['name'], $newnum, $field, "id", "_auto_links", "", "hits != '0'", $confal['nump']);
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function view() {
	global $prefix, $db, $conf;
	$id = intval($_GET['id']);
	if ($id) {
		list($link)= $db->sql_fetchrow($db->sql_query("SELECT link FROM ".$prefix."_auto_links WHERE id = '".$id."'"));
		$db->sql_query("UPDATE ".$prefix."_auto_links SET outs = outs+1 WHERE id = '".$id."'");
		update_points(4);
		header("Location: ".$link);
	} else {
		header("Location: index.php?name=".$conf['name']);
	}
}

function add() {
	global $stop, $conf;
	if (is_user()) {
		$userinfo = getusrinfo();
		$authormail = (isset($_POST['adminemail'])) ? text_filter($_POST['adminemail']) : $userinfo['user_email'];
		$authorurl = (isset($_POST['sitelink'])) ? url_filter($_POST['sitelink']) : $userinfo['user_website'];
	} else {
		$authormail = (isset($_POST['adminemail'])) ? text_filter($_POST['adminemail']) : "";
		$authorurl = (isset($_POST['sitelink'])) ? url_filter($_POST['sitelink']) : "http://";
	}
	head($conf['defis']." "._A_LINKS." ".$conf['defis']." "._ADD, _A_LINKS_I);
	$cont = navigate(_ADD);
	if ($stop) $cont .= tpl_warn("warn", $stop, "", "", "warn");
	$sitename = (isset($_POST['sitename'])) ? save_text($_POST['sitename'], 1) : "";
	$description = (isset($_POST['description'])) ? save_text($_POST['description']) : "";
	if ($description) $cont .= preview($sitename, $description, "", "", $conf['name']);
	$cont .= tpl_warn("warn", _A_LINKS_I, "", "", "info");
	$cont .= tpl_eval("open");
	$cont .= "<form name=\"post\" action=\"index.php?name=".$conf['name']."\" method=\"post\"><table class=\"sl_table_form\">"
	."<tr><td>"._SITENAME.":</td><td><input type=\"text\" name=\"sitename\" value=\"".$sitename."\" maxlength=\"255\" class=\"sl_field ".$conf['style']."\" placeholder=\""._SITENAME."\" required></td></tr>"
	."<tr><td>"._A_LINKS_E.":</td><td><input type=\"email\" name=\"adminemail\" value=\"".$authormail."\" maxlength=\"100\" class=\"sl_field ".$conf['style']."\" placeholder=\""._A_LINKS_E."\" required></td></tr>"
	."<tr><td>"._A_LINKS_TEXT.":</td><td>".textarea("1", "description", $description, $conf['name'], "5", _A_LINKS_TEXT, "1")."</td></tr>"
	."<tr><td>"._A_LINKS_L.":</td><td><input type=\"url\" name=\"sitelink\" value=\"".$authorurl."\" maxlength=\"100\" class=\"sl_field ".$conf['style']."\" placeholder=\""._A_LINKS_L."\" required></td></tr>"
	.captcha_random()
	."<tr><td colspan=\"2\" class=\"sl_center\">".ad_save("", "", "send")."</td></tr></table></form>";
	$cont .= tpl_eval("close");
	echo $cont;
	foot();
}

function send() {
	global $prefix, $db, $user, $stop, $conf, $confal;
	$sitename = save_text($_POST['sitename'], 1);
	$description = save_text($_POST['description']);
	$sitelink = url_filter($_POST['sitelink']);
	$adminemail = text_filter($_POST['adminemail']);
	$stop = array();
	if (!$sitename) $stop[] = _CERROR10;
	if (!$description) $stop[] = _CERROR11;
	if (!$sitelink) $stop[] = _CERROR4;
	checkemail($adminemail);
	if (captcha_check()) $stop[] = _SECCODEINCOR;
	if ($db->sql_numrows($db->sql_query("SELECT link FROM ".$prefix."_auto_links WHERE link = '".$sitelink."'")) > 0) $stop[] = _LINKEXIST;
	if (!$stop && $_POST['posttype'] == "save") {
		head($conf['defis']." "._A_LINKS." ".$conf['defis']." "._ADD, _A_LINKS_OK);
		$cont = navigate(_ADD);
		$db->sql_query("INSERT INTO ".$prefix."_auto_links VALUES (NULL, '".$sitename."', '".$description."', '".$sitelink."', '".$adminemail."', 0, 0, now())");
		$puname = (is_user()) ? $user[1] : "";
		addmail($confal['addmail'], $conf['name'], $puname, _A_LINKS);
		$cont .= tpl_warn("warn", _A_LINKS_OK, "", "", "info");
		$cont .= tpl_eval("open");
		$my_link = "<a href=&quot;".$conf['homeurl']."&quot; target=&quot;_blank&quot; title=&quot;".$conf['slogan']."&quot;>".$conf['sitename']."</a>";
		$cont .= "<table class=\"sl_table_form\">"
		."<tr><td>"._A_LINKS_M.":</td><td><textarea name=\"description\" cols=\"65\" rows=\"5\" class=\"sl_field ".$conf['style']."\">".$my_link."</textarea></td></tr>";
		if ($confal['img']) {
			list($imgwidth, $imgheight) = getimagesize($conf['homeurl']."/".img_find("banners/".$confal['img']));
			$my_img_link = "<a href=&quot;".$conf['homeurl']."&quot; target=&quot;_blank&quot; title=&quot;".$conf['sitename']." - ".$conf['slogan']."&quot;><img src=&quot;".$conf['homeurl']."/".img_find("banners/".$confal['img'])."&quot; alt=&quot;".$conf['sitename']." - ".$conf['slogan']."&quot; style=&quot;border: 0; width: ".$imgwidth."; height: ".$imgheight.";&quot;></a>";
			$cont .= "<tr><td>"._A_LINKS_IMG.":</td><td><textarea name=\"description\" cols=\"65\" rows=\"5\" class=\"sl_field ".$conf['style']."\">".$my_img_link."</textarea></td></tr>";
		}
		$cont .= "</table>";
		$cont .= tpl_eval("close");
		echo $cont;
		foot();
	} else {
		add();
	}
}

switch ($op) {
	default:
	autolink();
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