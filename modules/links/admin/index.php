<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("ADMIN_FILE") || !is_admin_modul("links")) die("Illegal file access");

include("config/config_links.php");

function links_navi() {
	panel();
	$narg = func_get_args();
	$ops = array("links", "links_add", "links&amp;status=1", "links&amp;status=2", "links_conf", "links_info");
	$lang = array(_HOME, _ADD, _NEW, _BROCLINKS, _PREFERENCES, _INFO);
	return navi_gen(_LINKS, "links.png", "", $ops, $lang, "", "", $narg[0], $narg[1], $narg[2], $narg[3]);
}

function links() {
	global $prefix, $db, $admin_file, $confl, $confu;
	head();
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $confl['anum'];
	$offset = intval($offset);
	if ($_GET['status'] == 1) {
		$status = "0";
		$field = "op=links&amp;status=1&amp;";
		$refer = "&amp;refer=1";
		$cont = links_navi(0, 2, 0, 0);
	} elseif ($_GET['status'] == 2) {
		$status = "2";
		$field = "op=links&amp;status=2&amp;";
		$refer = "&amp;refer=1";
		$cont = links_navi(0, 3, 0, 0);
	} else {
		$status = "1";
		$field = "op=links&amp;";
		$refer = "&amp;refer=1";
		$cont = links_navi(0, 0, 0, 0);
	}
	$result = $db->sql_query("SELECT l.lid, l.cid, l.name, l.title, l.url, l.date, l.ip_sender, c.title, u.user_name FROM ".$prefix."_links AS l LEFT JOIN ".$prefix."_categories AS c ON (l.cid = c.id) LEFT JOIN ".$prefix."_users AS u ON (l.uid = u.user_id) WHERE l.status = '".$status."' ORDER BY l.date DESC LIMIT ".$offset.", ".$confl['anum']);
	if ($db->sql_numrows($result) > 0) {
		$cont .= tpl_eval("open");
		$cont .= "<table class=\"sl_table_list_sort\"><thead><tr><th>"._ID."</th><th>"._TITLE."</th><th>"._SITEURL."</th><th>"._POSTEDBY."</th><th class=\"{sorter: false}\">"._STATUS."</th><th class=\"{sorter: false}\">"._FUNCTIONS."</th></tr></thead><tbody>";
		while (list($id, $cid, $uname, $title, $url, $date, $ip_sender, $ctitle, $user_name) = $db->sql_fetchrow($result)) {
			$post = ($user_name) ? user_info($user_name) : (($uname) ? $uname : $confu['anonym']);
			$ctitle = ($cid) ? $ctitle : _NO;
			$ip_sender = ($ip_sender) ? user_geo_ip($ip_sender, 4) : _NO;
			$broc = ($status == 2) ? "<a href=\"".$admin_file.".php?op=links_ignore&amp;id=".$id."\" title=\""._IGNORE."\">"._IGNORE."</a>||" : "";
			if ($status && time() >= strtotime($date)) {
				$ad_view = "<a href=\"index.php?name=links&amp;op=view&amp;id=".$id."\" title=\""._MVIEW."\">"._MVIEW."</a>||";
				$active = "1";
			} else {
				$ad_view = "";
				$active = "0";
			}
			$cont .= "<tr><td>".$id."</td>"
			."<td>".title_tip(_CATEGORY.": ".$ctitle."<br>"._DATE.": ".format_time($date, _TIMESTRING)."<br>"._IP.": ".$ip_sender)."<span title=\"".$title."\" class=\"sl_note\">".cutstr($title, 50)."</span></td>"
			."<td>".domain($url)."</td>"
			."<td>".$post."</td>"
			."<td>".ad_status("", $active)."</td>"
			."<td>".add_menu($ad_view.$broc."<a href=\"".$admin_file.".php?op=links_add&amp;id=".$id."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=links_delete&amp;id=".$id.$refer."\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$title."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>")."</td></tr>";
		}
		$cont .= "</tbody></table>";
		$cont .= num_article("pagenum", "", $confl['anum'], $field, "lid", "_links", "", "status = '".$status."'", $confl['anump']);
		$cont .= tpl_eval("close");
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function links_add() {
	global $prefix, $db, $admin_file, $confu, $stop;
	if (isset($_REQUEST['id'])) {
		$fid = intval($_REQUEST['id']);
		$result = $db->sql_query("SELECT l.cid, l.name, l.title, l.description, l.bodytext, l.url, l.date, l.email, l.ihome, l.acomm, u.user_name FROM ".$prefix."_links AS l LEFT JOIN ".$prefix."_users AS u ON (l.uid = u.user_id) WHERE lid = '".$fid."'");
		list($cid, $uname, $title, $description, $bodytext, $url, $date, $email, $ihome, $acomm, $user_name) = $db->sql_fetchrow($result);
		$postname = ($user_name) ? $user_name : (($uname) ? $uname : $confu['anonym']);
	} else {
		$fid = $_POST['fid'];
		$cid = $_POST['cid'];
		$title = save_text($_POST['title'], 1);
		$description = save_text($_POST['description']);
		$bodytext = save_text($_POST['bodytext']);
		$url = (isset($_POST['url'])) ? $_POST['url'] : "http://";
		$date = save_datetime(1, "date");
		$ihome = $_POST['ihome'];
		$acomm = $_POST['acomm'];
		$postname = $_POST['postname'];
		$email = $_POST['email'];
	}
	head();
	$cont = links_navi(0, 1, 0, 0);
	if ($stop) $cont .= tpl_warn("warn", $stop, "", "", "warn");
	if ($description) $cont .= preview($title, $description, $bodytext, "", "links");
	$link_url = ($url) ? "<a href=\"".$url."\" target=\"_blank\" title=\""._DOWNLLINK."\">"._URL."</a>" : _URL;
	$cont .= tpl_eval("open");
	$cont .= "<form name=\"post\" action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_form\">"
	."<tr><td>"._POSTEDBY.":</td><td>".get_user_search("postname", $postname, "25", "sl_form", "1")."</td></tr>"
	."<tr><td>"._TITLE.":</td><td><input type=\"text\" name=\"title\" value=\"".$title."\" class=\"sl_form\" placeholder=\""._TITLE."\" required></td></tr>"
	."<tr><td>"._CATEGORY.":</td><td>".getcat("links", $cid, "cid", "sl_form", "<option value=\"\">"._HOMECAT."</option>")."</td></tr>"
	."<tr><td>"._TEXT.":</td><td>".textarea("1", "description", $description, "links", "5", _TEXT, "1")."</td></tr>"
	."<tr><td>"._ENDTEXT.":</td><td>".textarea("2", "bodytext", $bodytext, "links", "15", _ENDTEXT, "0")."</td></tr>"
	."<tr><td>"._AUEMAIL.":</td><td><input type=\"email\" name=\"email\" value=\"".$email."\" class=\"sl_form\" placeholder=\""._AUEMAIL."\" required></td></tr>"
	."<tr><td>".$link_url.":</td><td><input type=\"url\" name=\"url\" value=\"".$url."\" class=\"sl_form\" placeholder=\""._URL."\" required></td></tr>"
	."<tr><td>"._CHNGSTORY.":</td><td>".datetime(1, "date", $date, 16, "sl_form")."</td></tr>"
	."<tr><td>"._COMMENTS.":</td><td>".com_access("acomm", $acomm, "sl_form")."</td></tr>"
	."<tr><td>"._PUBHOME."</td><td>".radio_form($ihome, "ihome")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\">".ad_save("fid", $fid, "links_save")."</td></tr></table></form>";
	$cont .= tpl_eval("close");
	echo $cont;
	foot();
}

function links_save() {
	global $prefix, $db, $admin_file, $stop;
	$fid = intval($_POST['fid']);
	$cid = intval($_POST['cid']);
	$title = save_text($_POST['title'], 1);
	$description = save_text($_POST['description']);
	$bodytext = save_text($_POST['bodytext']);
	$url = url_filter($_POST['url']);
	$date = save_datetime(1, "date");
	$ihome = $_POST['ihome'];
	$acomm = $_POST['acomm'];
	$postname = $_POST['postname'];
	$email = text_filter($_POST['email']);
	$stop = array();
	if (!$title) $stop[] = _CERROR;
	if (!$description) $stop[] = _CERROR1;
	if (!$postname) $stop[] = _CERROR3;
	if (!$fid && $db->sql_numrows($db->sql_query("SELECT title FROM ".$prefix."_links WHERE title = '".$title."'")) > 0) $stop[] = _LINKEXIST;
	if (!$stop && $_POST['posttype'] == "save") {
		$postid = (is_user_id($postname)) ? is_user_id($postname) : "";
		$postname = (!is_user_id($postname)) ? text_filter(substr($postname, 0, 25)) : "";
		if ($fid) {
			$db->sql_query("UPDATE ".$prefix."_links SET cid = '".$cid."', uid = '".$postid."', name = '".$postname."', title = '".$title."', description = '".$description."', bodytext = '".$bodytext."', url = '".$url."', date = '".$date."', email = '".$email."', ihome = '".$ihome."', acomm = '".$acomm."', status = '1' WHERE lid = '".$fid."'");
		} else {
			$ip = getip();
			$db->sql_query("INSERT INTO ".$prefix."_links (lid, cid, uid, name, title, description, bodytext, url, date, email, ip_sender, ihome, acomm, status) VALUES (NULL, '".$cid."', '".$postid."', '".$postname."', '".$title."', '".$description."', '".$bodytext."', '".$url."', '".$date."', '".$email."', '".$ip."', '".$ihome."', '".$acomm."', '1')");
		}
		header("Location: ".$admin_file.".php?op=links");
	} elseif ($_POST['posttype'] == "delete") {
		links_delete($fid);
	} else {
		links_add();
	}
}

function links_delete() {
	global $prefix, $db, $admin_file, $id;
	$arg = func_get_args();
	$id = ($arg[0]) ? $arg[0] : $id;
	if ($id) {
		$db->sql_query("DELETE FROM ".$prefix."_comment WHERE cid = '".$id."' AND modul = 'links'");
		$db->sql_query("DELETE FROM ".$prefix."_favorites WHERE fid = '".$id."' AND modul = 'links'");
		$db->sql_query("DELETE FROM ".$prefix."_links WHERE lid = '".$id."'");
	}
	referer($admin_file.".php?op=links");
}

function links_conf() {
	global $admin_file, $confl;
	head();
	$cont = links_navi(0, 4, 0, 0);
	$permtest = end_chmod("config/config_links.php", 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_conf\">"
	."<tr><td>"._CDEFIS.":</td><td><input type=\"text\" name=\"defis\" value=\"".urldecode($confl['defis'])."\" maxlength=\"25\" class=\"sl_conf\" placeholder=\""._CDEFIS."\" required></td></tr>"
	."<tr><td>"._C_10.":</td><td><input type=\"number\" name=\"tabcol\" value=\"".$confl['tabcol']."\" class=\"sl_conf\" placeholder=\""._C_10."\" required></td></tr>"
	."<tr><td>"._PAGELINKNUM.":</td><td><input type=\"number\" name=\"linknum\" value=\"".$confl['linknum']."\" class=\"sl_conf\" placeholder=\""._PAGELINKNUM."\" required></td></tr>"
	."<tr><td>"._C_13.":</td><td><input type=\"number\" name=\"listnum\" value=\"".$confl['listnum']."\" class=\"sl_conf\" placeholder=\""._C_13."\" required></td></tr>"
	."<tr><td>"._C_33.":</td><td><input type=\"number\" name=\"num\" value=\"".$confl['num']."\" class=\"sl_conf\" placeholder=\""._C_33."\" required></td></tr>"
	."<tr><td>"._C_34.":</td><td><input type=\"number\" name=\"anum\" value=\"".$confl['anum']."\" class=\"sl_conf\" placeholder=\""._C_34."\" required></td></tr>"
	."<tr><td>"._C_35.":</td><td><input type=\"number\" name=\"nump\" value=\"".$confl['nump']."\" class=\"sl_conf\" placeholder=\""._C_35."\" required></td></tr>"
	."<tr><td>"._C_36.":</td><td><input type=\"number\" name=\"anump\" value=\"".$confl['anump']."\" class=\"sl_conf\" placeholder=\""._C_36."\" required></td></tr>"
	."<tr><td>"._HOMCAT."</td><td>".radio_form($confl['homcat'], "homcat")."</td></tr>"
	."<tr><td>"._VIEWCAT."</td><td>".radio_form($confl['viewcat'], "viewcat")."</td></tr>"
	."<tr><td>"._C_32."</td><td>".radio_form($confl['catdesc'], "catdesc")."</td></tr>"
	."<tr><td>"._C_15."</td><td>".radio_form($confl['subcat'], "subcat")."</td></tr>"
	."<tr><td>"._ADDAMAIL."</td><td>".radio_form($confl['addmail'], "addmail")."</td></tr>"
	."<tr><td>"._L_8."</td><td>".radio_form($confl['add'], "add")."</td></tr>"
	."<tr><td>"._L_9."</td><td>".radio_form($confl['addquest'], "addquest")."</td></tr>"
	."<tr><td>"._L_11."</td><td>".radio_form($confl['broc'], "broc")."</td></tr>"
	."<tr><td>"._L_12."</td><td>".radio_form($confl['links'], "links")."</td></tr>"
	."<tr><td>"._C_37."</td><td>".radio_form($confl['autor'], "autor")."</td></tr>"
	."<tr><td>"._C_17."</td><td>".radio_form($confl['date'], "date")."</td></tr>"
	."<tr><td>"._C_18."</td><td>".radio_form($confl['read'], "read")."</td></tr>"
	."<tr><td>"._L_1."</td><td>".radio_form($confl['hits'], "hits")."</td></tr>"
	."<tr><td>"._C_19."</td><td>".radio_form($confl['rate'], "rate")."</td></tr>"
	."<tr><td>"._C_20."</td><td>".radio_form($confl['letter'], "letter")."</td></tr>"
	."<tr><td>"._PAGELINK."</td><td>".radio_form($confl['link'], "link")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"links_conf_save\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close");
	echo $cont;
	foot();
}

function links_conf_save() {
	global $admin_file;
	$xdefis = ($_POST['defis']) ? urlencode($_POST['defis']) : "%3E";
	$content = "\$confl = array();\n"
	."\$confl['defis'] = \"".$xdefis."\";\n"
	."\$confl['tabcol'] = \"".$_POST['tabcol']."\";\n"
	."\$confl['linknum'] = \"".$_POST['linknum']."\";\n"
	."\$confl['listnum'] = \"".$_POST['listnum']."\";\n"
	."\$confl['num'] = \"".$_POST['num']."\";\n"
	."\$confl['anum'] = \"".$_POST['anum']."\";\n"
	."\$confl['nump'] = \"".$_POST['nump']."\";\n"
	."\$confl['anump'] = \"".$_POST['anump']."\";\n"
	."\$confl['homcat'] = \"".$_POST['homcat']."\";\n"
	."\$confl['viewcat'] = \"".$_POST['viewcat']."\";\n"
	."\$confl['catdesc'] = \"".$_POST['catdesc']."\";\n"
	."\$confl['subcat'] = \"".$_POST['subcat']."\";\n"
	."\$confl['addmail'] = \"".$_POST['addmail']."\";\n"
	."\$confl['add'] = \"".$_POST['add']."\";\n"
	."\$confl['addquest'] = \"".$_POST['addquest']."\";\n"
	."\$confl['broc'] = \"".$_POST['broc']."\";\n"
	."\$confl['links'] = \"".$_POST['links']."\";\n"
	."\$confl['autor'] = \"".$_POST['autor']."\";\n"
	."\$confl['date'] = \"".$_POST['date']."\";\n"
	."\$confl['read'] = \"".$_POST['read']."\";\n"
	."\$confl['hits'] = \"".$_POST['hits']."\";\n"
	."\$confl['rate'] = \"".$_POST['rate']."\";\n"
	."\$confl['letter'] = \"".$_POST['letter']."\";\n"
	."\$confl['link'] = \"".$_POST['link']."\";\n";
	save_conf("config/config_links.php", $content);
	header("Location: ".$admin_file.".php?op=links_conf");
}

function links_info() {
	head();
	echo links_navi(0, 5, 0, 0)."<div id=\"repadm_info\">".adm_info(1, "links", 0)."</div>";
	foot();
}

switch ($op) {
	case "links":
	links();
	break;
	
	case "links_add":
	links_add();
	break;
	
	case "links_save":
	links_save();
	break;
	
	case "links_delete":
	links_delete();
	break;
	
	case "links_ignore":
	$db->sql_query("UPDATE ".$prefix."_links SET status = '1' WHERE lid = '".$id."'");
	header("Location: ".$admin_file.".php?op=links&status=2");
	break;
	
	case "links_conf":
	links_conf();
	break;
	
	case "links_conf_save":
	links_conf_save();
	break;
	
	case "links_info":
	links_info();
	break;
}
?>