<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("ADMIN_FILE") || !is_admin_modul("media")) die("Illegal file access");

include("config/config_media.php");

function media_navi() {
	panel();
	$narg = func_get_args();
	$ops = array("media", "media_add", "media&amp;status=1", "media&amp;status=2", "media_conf", "media_info");
	$lang = array(_HOME, _ADD, _NEW, _BROCMFILES, _PREFERENCES, _INFO);
	return navi_gen(_MEDIA, "media.png", "", $ops, $lang, "", "", $narg[0], $narg[1], $narg[2], $narg[3]);
}

function media() {
	global $prefix, $db, $admin_file, $confu, $confm;
	head();
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $confm['anum'];
	$offset = intval($offset);
	if ($_GET['status'] == 1) {
		$status = "0";
		$field = "op=media&amp;status=1&amp;";
		$refer = "&amp;refer=1";
		$cont = media_navi(0, 2, 0, 0);
	} elseif ($_GET['status'] == 2) {
		$status = "2";
		$field = "op=media&amp;status=2&amp;";
		$refer = "";
		$cont = media_navi(0, 3, 0, 0);
	} else {
		$status = "1";
		$field = "op=media&amp;";
		$refer = "";
		$cont = media_navi(0, 0, 0, 0);
	}
	$result = $db->sql_query("SELECT m.id, m.cid, m.name, m.title, m.subtitle, m.date, m.ip_sender, c.title, u.user_name FROM ".$prefix."_media AS m LEFT JOIN ".$prefix."_categories AS c ON (m.cid = c.id) LEFT JOIN ".$prefix."_users AS u ON (m.uid = u.user_id) WHERE m.status = '".$status."' ORDER BY m.date DESC LIMIT ".$offset.", ".$confm['anum']);
	if ($db->sql_numrows($result) > 0) {
		$cont .= tpl_eval("open");
		$cont .= "<table class=\"sl_table_list_sort\"><thead><tr><th>"._ID."</th><th>"._TITLE."</th><th>"._POSTEDBY."</th><th class=\"{sorter: false}\">"._STATUS."</th><th class=\"{sorter: false}\">"._FUNCTIONS."</th></tr></thead><tbody>";
		while (list($id, $cid, $uname, $title, $subtitle, $date, $ip_sender, $ctitle, $user_name) = $db->sql_fetchrow($result)) {
			$title = ($subtitle) ? $title." / ".$subtitle : $title;
			$post = ($user_name) ? user_info($user_name) : (($uname) ? $uname : $confu['anonym']);
			$ctitle = ($cid) ? $ctitle : _NO;
			$ip_sender = ($ip_sender) ? user_geo_ip($ip_sender, 4) : _NO;
			$broc = ($status == 2) ? "<a href=\"".$admin_file.".php?op=media_ignore&amp;id=".$id."\" title=\""._IGNORE."\">"._IGNORE."</a>||" : "";
			if ($status && time() >= strtotime($date)) {
				$ad_view = "<a href=\"index.php?name=media&amp;op=view&amp;id=".$id."\" title=\""._MVIEW."\">"._MVIEW."</a>||";
				$active = "1";
			} else {
				$ad_view = "";
				$active = "0";
			}
			$cont .= "<tr><td>".$id."</td>"
			."<td>".title_tip(_CATEGORY.": ".$ctitle."<br>"._DATE.": ".format_time($date, _TIMESTRING)."<br>"._IP.": ".$ip_sender)."<span title=\"".$title."\" class=\"sl_note\">".cutstr($title, 60)."</span></td>"
			."<td>".$post."</td>"
			."<td>".ad_status("", $active)."</td>"
			."<td>".add_menu($ad_view.$broc."<a href=\"".$admin_file.".php?op=media_add&amp;id=".$id."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=media_delete&amp;id=".$id.$refer."\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$title."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>")."</td></tr>";
		}
		$cont .= "</tbody></table>";
		$cont .= num_article("pagenum", "", $confm['anum'], $field, "id", "_media", "", "status = '".$status."'", $confm['anump']);
		$cont .= tpl_eval("close");
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function media_add() {
	global $prefix, $db, $admin_file, $confu, $confm, $stop;
	$date = getdate();
	if (isset($_REQUEST['id'])) {
		$mid = intval($_REQUEST['id']);
		$result = $db->sql_query("SELECT m.cid, m.name, m.title, m.subtitle, m.year, m.director, m.roles, m.description, m.createdby, m.duration, m.lang, m.note, m.format, m.quality, m.size, m.released, m.links, m.date, m.ihome, m.acomm, u.user_name FROM ".$prefix."_media AS m LEFT JOIN ".$prefix."_users AS u ON (m.uid = u.user_id) WHERE id = '".$mid."'");
		list($cid, $uname, $title, $subtitle, $myear, $director, $roles, $description, $createdby, $duration, $mlang, $note, $mformat, $mquality, $size, $released, $links, $mdate, $ihome, $acomm, $user_name) = $db->sql_fetchrow($result);
		$postname = ($user_name) ? $user_name : (($uname) ? $uname : $confu['anonym']);
		$links = explode(",", $links);
	} else {
		$mid = $_POST['mid'];
		$cid = $_POST['cid'];
		$postname = $_POST['postname'];
		$title = save_text($_POST['title'], 1);
		$subtitle = save_text($_POST['subtitle'], 1);
		$myear = isset($_POST['myears']) ? intval($_POST['myears']) : $date[year];
		$director = save_text($_POST['director']);
		$roles = save_text($_POST['roles']);
		$description = save_text($_POST['description']);
		$createdby = save_text($_POST['createdby']);
		$duration = save_text($_POST['duration']);
		$mlang = $_POST['lang'];
		$note = save_text($_POST['note']);
		$mformat = $_POST['format'];
		$mquality = $_POST['quality'];
		$size = save_text($_POST['size']);
		$released = save_text($_POST['released']);
		$links = $_POST['links'];
		$mdate = save_datetime(1, "mdate");
		$ihome = $_POST['ihome'];
		$acomm = $_POST['acomm'];
	}
	$mtitle = ($subtitle) ? $title." ".urldecode($confm['mdefis'])." ".$subtitle : $title;
	head();
	$cont = media_navi(0, 1, 0, 0);
	if ($stop) $cont .= tpl_warn("warn", $stop, "", "", "warn");
	if ($description) $cont .= preview($mtitle, $description, "", "", "media");
	$cont .= tpl_eval("open");
	$cont .= "<form name=\"post\" action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_form\">"
	."<tr><td>"._POSTEDBY.":</td><td>".get_user_search("postname", $postname, "25", "sl_form", "1")."</td></tr>"
	."<tr><td>"._MTITLE.":</td><td><input type=\"text\" name=\"title\" value=\"".$title."\" maxlength=\"100\" class=\"sl_form\" placeholder=\""._MTITLE."\" required></td></tr>"
	."<tr><td>"._MSUBTITLE.":</td><td><input type=\"text\" name=\"subtitle\" value=\"".$subtitle."\" maxlength=\"100\" class=\"sl_form\" placeholder=\""._MSUBTITLE."\"></td></tr>"
	."<tr><td>"._CATEGORY.":</td><td>".getcat("media", $cid, "cid", "sl_form", "<option value=\"\">"._HOMECAT."</option>")."</td></tr>"
	."<tr><td>"._MYEAR.":</td><td><select name=\"myears\" class=\"sl_form\">";
	$myears = $date[year] - 100;
	while ($myears <= ($date[year] + 1)) {
		$sel = ($myears == $myear) ? " selected" : "";
		$cont .= "<option value=\"".$myears."\"".$sel.">".$myears."</option>";
		$myears++;
	}
	$cont .= "</select></td></tr>"
	."<tr><td>"._MDIRECTOR.":</td><td><input type=\"text\" name=\"director\" value=\"".$director."\" maxlength=\"100\" class=\"sl_form\" placeholder=\""._MDIRECTOR."\"></td></tr>"
	."<tr><td>"._MROLES.":</td><td><input type=\"text\" name=\"roles\" value=\"".$roles."\" maxlength=\"255\" class=\"sl_form\" placeholder=\""._MROLES."\"></td></tr>"
	."<tr><td>"._DESCRIPTION.":</td><td>".textarea("1", "description", $description, "media", "10", _DESCRIPTION, "1")."</td></tr>"
	."<tr><td>"._MCREATEDBY.":</td><td><input type=\"text\" name=\"createdby\" value=\"".$createdby."\" maxlength=\"100\" class=\"sl_form\" placeholder=\""._MCREATEDBY."\"></td></tr>"
	."<tr><td>"._MDURATION.":</td><td><input type=\"text\" name=\"duration\" value=\"".$duration."\" maxlength=\"100\" class=\"sl_form\" placeholder=\""._MDURATION."\"></td></tr>"
	."<tr><td>"._LANGUAGE.":</td><td><select name=\"lang\" class=\"sl_form\">";
	$lang = explode(",", $confm['lang']);
	foreach ($lang as $val) {
		$sel = ($val == $mlang && $val != "") ? " selected" : "";
		$cont .= "<option value=\"".$val."\"".$sel.">".$val."</option>";
	}
	$cont .= "</select></td></tr>"
	."<tr><td>"._NOTE.":</td><td>".textarea("2", "note", $note, "media", "10", _NOTE, "0")."</td></tr>"
	."<tr><td>"._MFORMAT.":</td><td><select name=\"format\" class=\"sl_form\">"
	."<option value=\"\">"._NO_INFO."</option>";
	$format = explode(",", $confm['format']);
	foreach ($format as $val) {
		$sel = ($val == $mformat && $val != "") ? "selected" : "";
		$cont .= "<option value=\"".$val."\"".$sel.">".$val."</option>";
	}
	$cont .= "</select></td></tr>"
	."<tr><td>"._MQUALITY.":</td><td><select name=\"quality\" class=\"sl_form\">"
	."<option value=\"\">"._NO_INFO."</option>";
	$quality = explode(",", $confm['quality']);
	foreach ($quality as $val) {
		$sel = ($val == $mquality && $val != "") ? " selected" : "";
		$cont .= "<option value=\"".$val."\"".$sel.">".$val."</option>";
	}
	$cont .= "</select></td></tr>"
	."<tr><td>"._MSIZE.":</td><td><input type=\"text\" name=\"size\" value=\"".$size."\" maxlength=\"100\" class=\"sl_form\" placeholder=\""._MSIZE."\"></td></tr>"
	."<tr><td>"._MRELEASED.":</td><td><input type=\"text\" name=\"released\" value=\"".$released."\" maxlength=\"100\" class=\"sl_form\" placeholder=\""._MRELEASED."\"></td></tr>"
	."<tr><td colspan=\"2\">";
	$i = 0;
	while ($i < $confm['links']) {
		$a = $i + 1;
		$class = ($i != 0 && $links[$i] == "") ? " class=\"sl_none\"" : "";
		$cont .= "<table id=\"med".$i."\"".$class."><tr><td><a OnClick=\"HideShow('med".$a."', 'slide', 'up', 500);\" title=\""._ADD."\" class=\"sl_plus\">"._URL." - ".$a.":</a></td><td><input type=\"text\" name=\"links[]\" value=\"".text_filter($links[$i])."\" class=\"sl_form\" placeholder=\""._URL." - ".$a."\"></td></tr></table>";
		$i++;
	}
	$cont .= "</td></tr>"
	."<tr><td>"._CHNGSTORY.":</td><td>".datetime(1, "mdate", $mdate, 16, "sl_form")."</td></tr>"
	."<tr><td>"._COMMENTS.":</td><td>".com_access("acomm", $acomm, "sl_form")."</td></tr>"
	."<tr><td>"._PUBHOME."</td><td>".radio_form($ihome, "ihome")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\">".ad_save("mid", $mid, "media_save")."</td></tr></table></form>";
	$cont .= tpl_eval("close");
	echo $cont;
	foot();
}

function media_save() {
	global $prefix, $db, $admin_file, $stop;
	$mid = intval($_POST['mid']);
	$cid = intval($_POST['cid']);
	$postname = $_POST['postname'];
	$title = save_text($_POST['title'], 1);
	$subtitle = save_text($_POST['subtitle'], 1);
	$myears = intval($_POST['myears']);
	$director = text_filter($_POST['director']);
	$roles = text_filter($_POST['roles']);
	$description = save_text($_POST['description']);
	$createdby = text_filter($_POST['createdby']);
	$duration = text_filter($_POST['duration']);
	$lang = text_filter($_POST['lang']);
	$note = save_text($_POST['note']);
	$format = text_filter($_POST['format']);
	$quality = text_filter($_POST['quality']);
	$size = text_filter($_POST['size']);
	$released = text_filter($_POST['released']);
	$links = text_filter(implode(",", str_replace(",", ".", $_POST['links'])));
	$mdate = save_datetime(1, "mdate");
	$ihome = $_POST['ihome'];
	$acomm = $_POST['acomm'];
	$stop = array();
	if (!$title) $stop[] = _CERROR;
	if (!$description) $stop[] = _CERROR1;
	if (!$postname) $stop[] = _CERROR3;
	if (!$mid && $db->sql_numrows($db->sql_query("SELECT title, subtitle FROM ".$prefix."_media WHERE title = '".$title."' AND subtitle = '".$subtitle."'")) > 0) $stop[] = _MEDIAEXIST;
	if (!$stop && $_POST['posttype'] == "save") {
		$postid = (is_user_id($postname)) ? is_user_id($postname) : "";
		$postname = (!is_user_id($postname)) ? text_filter(substr($postname, 0, 25)) : "";
		if ($mid) {
			$db->sql_query("UPDATE ".$prefix."_media SET cid = '".$cid."', uid = '".$postid."', name = '".$postname."', title = '".$title."', subtitle = '".$subtitle."', year = '".$myears."', director = '".$director."', roles = '".$roles."', description = '".$description."', createdby = '".$createdby."', duration = '".$duration."', lang = '".$lang."', note = '".$note."', format = '".$format."', quality = '".$quality."', size = '".$size."', released = '".$released."', links = '".$links."', date = '".$mdate."', ihome = '".$ihome."', acomm = '".$acomm."', status = '1' WHERE id = '".$mid."'");
		} else {
			$ip = getip();
			$db->sql_query("INSERT INTO ".$prefix."_media (id, cid, uid, name, title, subtitle, year, director, roles, description, createdby, duration, lang, note, format, quality, size, released, links, date, ihome, acomm, ip_sender, status) VALUES (NULL, '".$cid."', '".$postid."', '".$postname."', '".$title."', '".$subtitle."', '".$myears."', '".$director."', '".$roles."', '".$description."', '".$createdby."', '".$duration."', '".$lang."', '".$note."', '".$format."', '".$quality."', '".$size."', '".$released."', '".$links."', '".$mdate."', '".$ihome."', '".$acomm."', '".$ip."', '1')");
		}
		header("Location: ".$admin_file.".php?op=media");
	} elseif ($_POST['posttype'] == "delete") {
		media_delete($mid);
	} else {
		media_add();
	}
}

function media_delete() {
	global $prefix, $db, $admin_file, $id;
	$arg = func_get_args();
	$id = ($arg[0]) ? $arg[0] : $id;
	if ($id) {
		$db->sql_query("DELETE FROM ".$prefix."_comment WHERE cid = '".$id."' AND modul = 'media'");
		$db->sql_query("DELETE FROM ".$prefix."_favorites WHERE fid = '".$id."' AND modul = 'media'");
		$db->sql_query("DELETE FROM ".$prefix."_media WHERE id = '".$id."'");
	}
	referer($admin_file.".php?op=media");
}

function media_conf() {
	global $admin_file, $confm;
	head();
	$cont = media_navi(0, 4, 0, 0);
	$permtest = end_chmod("config/config_media.php", 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_conf\">"
	."<tr><td>"._CDEFIS.":</td><td><input type=\"text\" name=\"defis\" value=\"".urldecode($confm['defis'])."\" maxlength=\"25\" class=\"sl_conf\" placeholder=\""._CDEFIS."\" required></td></tr>"
	."<tr><td>"._C_10.":</td><td><input type=\"number\" name=\"tabcol\" value=\"".$confm['tabcol']."\" class=\"sl_conf\" placeholder=\""._C_10."\" required></td></tr>"
	."<tr><td>"._PAGELINKNUM.":</td><td><input type=\"number\" name=\"linknum\" value=\"".$confm['linknum']."\" class=\"sl_conf\" placeholder=\""._PAGELINKNUM."\" required></td></tr>"
	."<tr><td>"._C_13.":</td><td><input type=\"number\" name=\"listnum\" value=\"".$confm['listnum']."\" class=\"sl_conf\" placeholder=\""._C_13."\" required></td></tr>"
	."<tr><td>"._C_33.":</td><td><input type=\"number\" name=\"num\" value=\"".$confm['num']."\" class=\"sl_conf\" placeholder=\""._C_33."\" required></td></tr>"
	."<tr><td>"._C_34.":</td><td><input type=\"number\" name=\"anum\" value=\"".$confm['anum']."\" class=\"sl_conf\" placeholder=\""._C_34."\" required></td></tr>"
	."<tr><td>"._C_35.":</td><td><input type=\"number\" name=\"nump\" value=\"".$confm['nump']."\" class=\"sl_conf\" placeholder=\""._C_35."\" required></td></tr>"
	."<tr><td>"._C_36.":</td><td><input type=\"number\" name=\"anump\" value=\"".$confm['anump']."\" class=\"sl_conf\" placeholder=\""._C_36."\" required></td></tr>"
	."<tr><td>"._M_1.":<div class=\"sl_small\">"._NOKOMA."</div></td><td><input type=\"text\" name=\"lang\" value=\"".$confm['lang']."\" class=\"sl_conf\" placeholder=\""._M_1."\" required></td></tr>"
	."<tr><td>"._M_2.":<div class=\"sl_small\">"._NOKOMA."</div></td><td><input type=\"text\" name=\"format\" value=\"".$confm['format']."\" class=\"sl_conf\" placeholder=\""._M_2."\" required></td></tr>"
	."<tr><td>"._M_3.":<div class=\"sl_small\">"._NOKOMA."</div></td><td><input type=\"text\" name=\"quality\" value=\"".$confm['quality']."\" class=\"sl_conf\" placeholder=\""._M_3."\" required></td></tr>"
	."<tr><td>"._M_4.":</td><td><input type=\"number\" name=\"links\" value=\"".$confm['links']."\" class=\"sl_conf\" placeholder=\""._M_4."\" required></td></tr>"
	."<tr><td>"._DEFIS.":</td><td><input type=\"text\" name=\"mdefis\" value=\"".urldecode($confm['mdefis'])."\" maxlength=\"25\" class=\"sl_conf\" placeholder=\""._DEFIS."\" required></td></tr>"
	."<tr><td>"._HOMCAT."</td><td>".radio_form($confm['homcat'], "homcat")."</td></tr>"
	."<tr><td>"._VIEWCAT."</td><td>".radio_form($confm['viewcat'], "viewcat")."</td></tr>"
	."<tr><td>"._C_32."</td><td>".radio_form($confm['catdesc'], "catdesc")."</td></tr>"
	."<tr><td>"._C_15."</td><td>".radio_form($confm['subcat'], "subcat")."</td></tr>"
	."<tr><td>"._ADDAMAIL."</td><td>".radio_form($confm['addmail'], "addmail")."</td></tr>"
	."<tr><td>"._M_7."</td><td>".radio_form($confm['add'], "add")."</td></tr>"
	."<tr><td>"._M_8."</td><td>".radio_form($confm['addquest'], "addquest")."</td></tr>"
	."<tr><td>"._M_9."</td><td>".radio_form($confm['broc'], "broc")."</td></tr>"
	."<tr><td>"._M_10."</td><td>".radio_form($confm['hide'], "hide")."</td></tr>"
	."<tr><td>"._C_37."</td><td>".radio_form($confm['autor'], "autor")."</td></tr>"
	."<tr><td>"._C_17."</td><td>".radio_form($confm['date'], "date")."</td></tr>"
	."<tr><td>"._C_18."</td><td>".radio_form($confm['read'], "read")."</td></tr>"
	."<tr><td>"._C_19."</td><td>".radio_form($confm['rate'], "rate")."</td></tr>"
	."<tr><td>"._C_20."</td><td>".radio_form($confm['letter'], "letter")."</td></tr>"
	."<tr><td>"._PAGELINK."</td><td>".radio_form($confm['link'], "link")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"media_conf_save\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close");
	echo $cont;
	foot();
}

function media_conf_save() {
	global $admin_file;
	$xdefis = ($_POST['defis']) ? urlencode($_POST['defis']) : "%3E";
	$protect = array(", ", " ,", " , ");
	$xlang = str_replace($protect, ",", $_POST['lang']);
	$xformat = str_replace($protect, ",", $_POST['format']);
	$xquality = str_replace($protect, ",", $_POST['quality']);
	$xmdefis = ($_POST['mdefis']) ? urlencode($_POST['mdefis']) : "%7C";
	$content = "\$confm = array();\n"
	."\$confm['defis'] = \"".$xdefis."\";\n"
	."\$confm['tabcol'] = \"".$_POST['tabcol']."\";\n"
	."\$confm['linknum'] = \"".$_POST['linknum']."\";\n"
	."\$confm['listnum'] = \"".$_POST['listnum']."\";\n"
	."\$confm['num'] = \"".$_POST['num']."\";\n"
	."\$confm['anum'] = \"".$_POST['anum']."\";\n"
	."\$confm['nump'] = \"".$_POST['nump']."\";\n"
	."\$confm['anump'] = \"".$_POST['anump']."\";\n"
	."\$confm['lang'] = \"".$xlang."\";\n"
	."\$confm['format'] = \"".$xformat."\";\n"
	."\$confm['quality'] = \"".$xquality."\";\n"
	."\$confm['links'] = \"".$_POST['links']."\";\n"
	."\$confm['mdefis'] = \"".$xmdefis."\";\n"
	."\$confm['homcat'] = \"".$_POST['homcat']."\";\n"
	."\$confm['viewcat'] = \"".$_POST['viewcat']."\";\n"
	."\$confm['catdesc'] = \"".$_POST['catdesc']."\";\n"
	."\$confm['subcat'] = \"".$_POST['subcat']."\";\n"
	."\$confm['addmail'] = \"".$_POST['addmail']."\";\n"
	."\$confm['add'] = \"".$_POST['add']."\";\n"
	."\$confm['addquest'] = \"".$_POST['addquest']."\";\n"
	."\$confm['broc'] = \"".$_POST['broc']."\";\n"
	."\$confm['hide'] = \"".$_POST['hide']."\";\n"
	."\$confm['autor'] = \"".$_POST['autor']."\";\n"
	."\$confm['date'] = \"".$_POST['date']."\";\n"
	."\$confm['read'] = \"".$_POST['read']."\";\n"
	."\$confm['rate'] = \"".$_POST['rate']."\";\n"
	."\$confm['letter'] = \"".$_POST['letter']."\";\n"
	."\$confm['link'] = \"".$_POST['link']."\";\n";
	save_conf("config/config_media.php", $content);
	header("Location: ".$admin_file.".php?op=media_conf");
}

function media_info() {
	head();
	echo media_navi(0, 5, 0, 0)."<div id=\"repadm_info\">".adm_info(1, "media", 0)."</div>";
	foot();
}

switch ($op) {
	case "media":
	media();
	break;
	
	case "media_add":
	media_add();
	break;
	
	case "media_save":
	media_save();
	break;
	
	case "media_delete":
	media_delete();
	break;
	
	case "media_ignore":
	$db->sql_query("UPDATE ".$prefix."_media SET status = '1' WHERE id = '".$id."'");
	header("Location: ".$admin_file.".php?op=media&status=2");
	break;
	
	case "media_conf":
	media_conf();
	break;
	
	case "media_conf_save":
	media_conf_save();
	break;
	
	case "media_info":
	media_info();
	break;
}
?>