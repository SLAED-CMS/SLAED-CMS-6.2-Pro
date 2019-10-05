<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("ADMIN_FILE") || !is_admin_modul("files")) die("Illegal file access");

include("config/config_files.php");

function files_navi() {
	panel();
	$narg = func_get_args();
	$ops = array("files", "files_add", "files&amp;status=1", "files&amp;status=2", "files_conf", "files_info");
	$lang = array(_HOME, _ADD, _NEW, _BROCFILES, _PREFERENCES, _INFO);
	return navi_gen(_FILES, "files.png", "", $ops, $lang, "", "", $narg[0], $narg[1], $narg[2], $narg[3]);
}

function files() {
	global $prefix, $db, $admin_file, $conff, $confu;
	head();
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $conff['anum'];
	$offset = intval($offset);
	if ($_GET['status'] == 1) {
		$status = "0";
		$field = "op=files&amp;status=1&amp;";
		$refer = "&amp;refer=1";
		$cont = files_navi(0, 2, 0, 0);
	} elseif ($_GET['status'] == 2) {
		$status = "2";
		$field = "op=files&amp;status=2&amp;";
		$refer = "";
		$cont = files_navi(0, 3, 0, 0);
	} else {
		$status = "1";
		$field = "op=files&amp;";
		$refer = "";
		$cont = files_navi(0, 0, 0, 0);
	}
	$result = $db->sql_query("SELECT f.lid, f.cid, f.name, f.title, f.date, f.ip_sender, c.title, u.user_name FROM ".$prefix."_files AS f LEFT JOIN ".$prefix."_categories AS c ON (f.cid = c.id) LEFT JOIN ".$prefix."_users AS u ON (f.uid = u.user_id) WHERE f.status = '".$status."' ORDER BY f.date DESC LIMIT ".$offset.", ".$conff['anum']);
	if ($db->sql_numrows($result) > 0) {
		$cont .= tpl_eval("open");
		$cont .= "<table class=\"sl_table_list_sort\"><thead><tr><th>"._ID."</th><th>"._TITLE."</th><th>"._POSTEDBY."</th><th class=\"{sorter: false}\">"._STATUS."</th><th class=\"{sorter: false}\">"._FUNCTIONS."</th></tr></thead><tbody>";
		while (list($id, $cid, $uname, $title, $date, $ip_sender, $ctitle, $user_name) = $db->sql_fetchrow($result)) {
			$post = ($user_name) ? user_info($user_name) : (($uname) ? $uname : $confu['anonym']);
			$ctitle = ($cid) ? $ctitle : _NO;
			$ip_sender = ($ip_sender) ? user_geo_ip($ip_sender, 4) : _NO;
			$broc = ($status == 2) ? "<a href=\"".$admin_file.".php?op=files_ignore&amp;id=".$id."\" title=\""._IGNORE."\">"._IGNORE."</a>||" : "";
			if ($status && time() >= strtotime($date)) {
				$ad_view = "<a href=\"index.php?name=files&amp;op=view&amp;id=".$id."\" title=\""._MVIEW."\">"._MVIEW."</a>||";
				$active = "1";
			} else {
				$ad_view = "";
				$active = "0";
			}
			$cont .= "<tr><td>".$id."</td>"
			."<td>".title_tip(_CATEGORY.": ".$ctitle."<br>"._DATE.": ".format_time($date, _TIMESTRING)."<br>"._IP.": ".$ip_sender)."<span title=\"".$title."\" class=\"sl_note\">".cutstr($title, 60)."</span></td>"
			."<td>".$post."</td>"
			."<td>".ad_status("", $active)."</td>"
			."<td>".add_menu($ad_view.$broc."<a href=\"".$admin_file.".php?op=files_add&amp;id=".$id."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=files_delete&amp;id=".$id.$refer."\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$title."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>")."</td></tr>";
		}
		$cont .= "</tbody></table>";
		$cont .= num_article("pagenum", "", $conff['anum'], $field, "lid", "_files", "", "status = '".$status."'", $conff['anump']);
		$cont .= tpl_eval("close");
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function files_add() {
	global $prefix, $db, $admin_file, $conff, $confu, $stop;
	if (isset($_REQUEST['id'])) {
		$fid = intval($_REQUEST['id']);
		$result = $db->sql_query("SELECT f.cid, f.name, f.title, f.description, f.bodytext, f.url, f.date, f.filesize, f.version, f.email, f.homepage, f.ihome, f.acomm, u.user_name FROM ".$prefix."_files AS f LEFT JOIN ".$prefix."_users AS u ON (f.uid = u.user_id) WHERE lid = '".$fid."'");
		list($cid, $uname, $title, $description, $bodytext, $url, $date, $filesize, $version, $email, $homepage, $ihome, $acomm, $user_name) = $db->sql_fetchrow($result);
		$postname = ($user_name) ? $user_name : (($uname) ? $uname : $confu['anonym']);
	} else {
		$fid = $_POST['fid'];
		$cid = $_POST['cid'];
		$title = save_text($_POST['title'], 1);
		$description = save_text($_POST['description']);
		$bodytext = save_text($_POST['bodytext']);
		$url = $_POST['url'];
		$path = text_filter($_POST['path']);
		$date = save_datetime(1, "date");
		$ihome = $_POST['ihome'];
		$acomm = $_POST['acomm'];
		$filesize = $_POST['filesize'];
		$version = $_POST['version'];
		$postname = $_POST['postname'];
		$email = $_POST['email'];
		$homepage = (isset($_POST['homepage'])) ? $_POST['homepage'] : "http://";
	}
	head();
	$cont = files_navi(0, 1, 0, 0);
	if ($stop) $cont .= tpl_warn("warn", $stop, "", "", "warn");
	if ($description) $cont .= preview($title, $description, $bodytext, "", "files");
	$link_url = ($url) ? "<a href=\"".$url."\" target=\"_blank\" title=\""._DOWNLLINK."\">"._URL."</a>" : _URL;
	if (file_exists($url)) {
		$handle = opendir($conff['path']);
		$directory = "";
		while (false !== ($file = readdir($handle))) {
			$selected = ($path == $conff['path']."/".$file) ? "selected" : "";
			if (!preg_match("/\./", $file)) $directory .= "<option value=\"".$conff['path']."/".$file."\" ".$selected.">".$conff['path']."/".$file."</option>";
		}
		closedir($handle);
	}
	$cont .= tpl_eval("open");
	$cont .= "<form name=\"post\" enctype=\"multipart/form-data\" action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_form\">"
	."<tr><td>"._POSTEDBY.":</td><td>".get_user_search("postname", $postname, "25", "sl_form", "1")."</td></tr>"
	."<tr><td>"._TITLE.":</td><td><input type=\"text\" name=\"title\" value=\"".$title."\" class=\"sl_form\" placeholder=\""._TITLE."\" required></td></tr>"
	."<tr><td>"._CATEGORY.":</td><td>".getcat("files", $cid, "cid", "sl_form", "<option value=\"\">"._HOMECAT."</option>")."</td></tr>"
	."<tr><td>"._TEXT.":</td><td>".textarea("1", "description", $description, "files", "5", _TEXT, "1")."</td></tr>"
	."<tr><td>"._ENDTEXT.":</td><td>".textarea("2", "bodytext", $bodytext, "files", "15", _ENDTEXT, "0")."</td></tr>"
	."<tr><td>"._AUEMAIL.":</td><td><input type=\"email\" name=\"email\" value=\"".$email."\" class=\"sl_form\" placeholder=\""._AUEMAIL."\"></td></tr>"
	."<tr><td>"._SITE.":</td><td><input type=\"url\" name=\"homepage\" value=\"".$homepage."\" class=\"sl_form\" placeholder=\""._SITE."\"></td></tr>"
	."<tr><td>"._FILE_USER.":</td><td><input type=\"file\" name=\"userfile\" class=\"sl_form\"></td></tr>"
	."<tr><td>"._FILE_SITE.":</td><td><input type=\"text\" name=\"sitefile\" class=\"sl_form\" placeholder=\""._FILE_SITE."\"></td></tr>"
	."<tr><td>".$link_url.":</td><td><input type=\"text\" name=\"url\" value=\"".$url."\" class=\"sl_form\" placeholder=\""._URL."\"></td></tr>";
	if (file_exists($url)) $cont .= "<tr><td>"._FILE_DIR.":</td><td><select name=\"path\" class=\"sl_form\"><option value=\"\">"._NO."</option><option value=\"".$conff['path']."\">".$conff['path']."</option>".$directory."</select></td></tr>";
	$cont .= "<tr><td>"._VERSION.":</td><td><input type=\"text\" name=\"version\" value=\"".$version."\" class=\"sl_form\" placeholder=\""._VERSION."\"></td></tr>"
	."<tr><td>"._SIZENOTE.":</td><td><input type=\"number\" name=\"filesize\" value=\"".$filesize."\" class=\"sl_form\" placeholder=\""._SIZENOTE."\"></td></tr>"
	."<tr><td>"._CHNGSTORY.":</td><td>".datetime(1, "date", $date, 16, "sl_form")."</td></tr>"
	."<tr><td>"._COMMENTS.":</td><td>".com_access("acomm", $acomm, "sl_form")."</td></tr>"
	."<tr><td>"._PUBHOME."</td><td>".radio_form($ihome, "ihome")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\">".ad_save("fid", $fid, "files_save")."</td></tr></table></form>";
	$cont .= tpl_eval("close");
	echo $cont;
	foot();
}

function files_save() {
	global $prefix, $db, $admin_file, $stop, $conff;
	$fid = intval($_POST['fid']);
	$cid = intval($_POST['cid']);
	$postname = $_POST['postname'];
	$title = save_text($_POST['title'], 1);
	$description = save_text($_POST['description']);
	$bodytext = save_text($_POST['bodytext']);
	$url = $_POST['url'];
	$path = text_filter($_POST['path']);
	$date = save_datetime(1, "date");
	$ihome = $_POST['ihome'];
	$acomm = $_POST['acomm'];
	$filesize = intval($_POST['filesize']);
	$version = text_filter($_POST['version']);
	$email = text_filter($_POST['email']);
	$homepage = url_filter($_POST['homepage']);
	$stop = array();
	if (!$title) $stop[] = _CERROR;
	if (!$description) $stop[] = _CERROR1;
	if (!$postname) $stop[] = _CERROR3;
	if (!$fid && $db->sql_numrows($db->sql_query("SELECT title FROM ".$prefix."_files WHERE title = '".$title."'")) > 0) $stop[] = _MEDIAEXIST;
	$filename = upload(1, $conff['path'], $conff['typefile'], $conff['max_size'], "files", "1600", "1600", '1');
	$url = ($filename) ? $conff['path']."/".$filename : $url;
	$filesize = ($filename) ? filesize($url) : $filesize;
	if ($stop) {
		$stop = $stop;
	} elseif (!$url && $_POST['posttype'] == "save") {
		$stop[] = _UPLOADEROR2;
	}
	if (!$stop && $_POST['posttype'] == "save") {
		$postid = (is_user_id($postname)) ? is_user_id($postname) : "";
		$postname = (!is_user_id($postname)) ? text_filter(substr($postname, 0, 25)) : "";
		if ($fid) {
			if ($path) {
				$filel = array_reverse(explode("/", $url));
				if (file_exists($url)) {
					$newfile = $path."/".$filel[0];
					rename($url, $newfile);
					$url = $path."/".$filel[0];
				}
			}
			$db->sql_query("UPDATE ".$prefix."_files SET cid = '".$cid."', uid = '".$postid."', name = '".$postname."', title = '".$title."', description = '".$description."', bodytext = '".$bodytext."', url = '".$url."', date = '".$date."', filesize = '".$filesize."', version = '".$version."', email = '".$email."', homepage = '".$homepage."', ihome = '".$ihome."', acomm = '".$acomm."', status = '1' WHERE lid = '".$fid."'");
		} else {
			$ip = getip();
			$db->sql_query("INSERT INTO ".$prefix."_files (lid, cid, uid, name, title, description, bodytext, url, date, filesize, version, email, homepage, ip_sender, ihome, acomm, status) VALUES (NULL, '".$cid."', '".$postid."', '".$postname."', '".$title."', '".$description."', '".$bodytext."', '".$url."', '".$date."', '".$filesize."', '".$version."', '".$email."', '".$homepage."', '".$ip."', '".$ihome."', '".$acomm."', '1')");
		}
		header("Location: ".$admin_file.".php?op=files");
	} elseif ($_POST['posttype'] == "delete") {
		files_delete($fid);
	} else {
		files_add();
	}
}

function files_delete() {
	global $prefix, $db, $admin_file, $id;
	$arg = func_get_args();
	$id = ($arg[0]) ? $arg[0] : $id;
	if ($id) {
		list($url) = $db->sql_fetchrow($db->sql_query("SELECT url FROM ".$prefix."_files WHERE lid = '".$id."'"));
		if (file_exists($url)) unlink($url);
		$db->sql_query("DELETE FROM ".$prefix."_comment WHERE cid = '".$id."' AND modul = 'files'");
		$db->sql_query("DELETE FROM ".$prefix."_favorites WHERE fid = '".$id."' AND modul = 'files'");
		$db->sql_query("DELETE FROM ".$prefix."_files WHERE lid = '".$id."'");
	}
	referer($admin_file.".php?op=files");
}

function files_conf() {
	global $prefix, $db, $admin_file, $conff;
	head();
	$cont = files_navi(0, 4, 0, 0);
	$permtest = end_chmod("config/config_files.php", 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_conf\">"
	."<tr><td>"._CDEFIS.":</td><td><input type=\"text\" name=\"defis\" value=\"".urldecode($conff['defis'])."\" maxlength=\"25\" class=\"sl_conf\" placeholder=\""._CDEFIS."\" required></td></tr>"
	."<tr><td>"._F_0.":</td><td><input type=\"text\" name=\"temp\" value=\"".$conff['temp']."\" class=\"sl_conf\" placeholder=\""._F_0."\" required></td></tr>"
	."<tr><td>"._F_1.":</td><td><input type=\"text\" name=\"path\" value=\"".$conff['path']."\" class=\"sl_conf\" placeholder=\""._F_1."\" required></td></tr>"
	."<tr><td>"._FSIZE._FIN.":</td><td><input type=\"number\" name=\"max_size\" value=\"".$conff['max_size']."\" class=\"sl_conf\" placeholder=\""._FSIZE._FIN."\" required></td></tr>"
	."<tr><td>"._FTYPE.":<div class=\"sl_small\">"._NOKOMA."</div></td><td><input type=\"text\" name=\"typefile\" value=\"".$conff['typefile']."\" class=\"sl_conf\" placeholder=\""._FTYPE."\" required></td></tr>"
	."<tr><td>"._C_10.":</td><td><input type=\"number\" name=\"tabcol\" value=\"".$conff['tabcol']."\" class=\"sl_conf\" placeholder=\""._C_10."\" required></td></tr>"
	."<tr><td>"._PAGELINKNUM.":</td><td><input type=\"number\" name=\"linknum\" value=\"".$conff['linknum']."\" class=\"sl_conf\" placeholder=\""._PAGELINKNUM."\" required></td></tr>"
	."<tr><td>"._C_13.":</td><td><input type=\"number\" name=\"listnum\" value=\"".$conff['listnum']."\" class=\"sl_conf\" placeholder=\""._C_13."\" required></td></tr>"
	."<tr><td>"._C_33.":</td><td><input type=\"number\" name=\"num\" value=\"".$conff['num']."\" class=\"sl_conf\" placeholder=\""._C_33."\" required></td></tr>"
	."<tr><td>"._C_34.":</td><td><input type=\"number\" name=\"anum\" value=\"".$conff['anum']."\" class=\"sl_conf\" placeholder=\""._C_34."\" required></td></tr>"
	."<tr><td>"._C_35.":</td><td><input type=\"number\" name=\"nump\" value=\"".$conff['nump']."\" class=\"sl_conf\" placeholder=\""._C_35."\" required></td></tr>"
	."<tr><td>"._C_36.":</td><td><input type=\"number\" name=\"anump\" value=\"".$conff['anump']."\" class=\"sl_conf\" placeholder=\""._C_36."\" required></td></tr>"
	."<tr><td>"._STREAM.":</td><td><select name=\"stream\" class=\"sl_conf\">"
	."<option value=\"0\"";
	if ($conff['stream'] == "0") $cont .= " selected";
	$cont .= ">"._STREAM_NO."</option>"
	."<option value=\"1\"";
	if ($conff['stream'] == "1") $cont .= " selected";
	$cont .= ">"._STREAM_1."</option>"
	."<option value=\"2\"";
	if ($conff['stream'] == "2") $cont .= " selected";
	$cont .= ">"._STREAM_2."</option>"
	."</select></td></tr>"
	."<tr><td>"._HOMCAT."</td><td>".radio_form($conff['homcat'], "homcat")."</td></tr>"
	."<tr><td>"._VIEWCAT."</td><td>".radio_form($conff['viewcat'], "viewcat")."</td></tr>"
	."<tr><td>"._C_32."</td><td>".radio_form($conff['catdesc'], "catdesc")."</td></tr>"
	."<tr><td>"._C_15."</td><td>".radio_form($conff['subcat'], "subcat")."</td></tr>"
	."<tr><td>"._ADDAMAIL."</td><td>".radio_form($conff['addmail'], "addmail")."</td></tr>"
	."<tr><td>"._F_8."</td><td>".radio_form($conff['add'], "add")."</td></tr>"
	."<tr><td>"._F_9."</td><td>".radio_form($conff['addquest'], "addquest")."</td></tr>"
	."<tr><td>"._F_11."</td><td>".radio_form($conff['broc'], "broc")."</td></tr>"
	."<tr><td>"._F_12."</td><td>".radio_form($conff['down'], "down")."</td></tr>"
	."<tr><td>"._UPFILE."</td><td>".radio_form($conff['upload'], "upload")."</td></tr>"
	."<tr><td>"._C_37."</td><td>".radio_form($conff['autor'], "autor")."</td></tr>"
	."<tr><td>"._C_17."</td><td>".radio_form($conff['date'], "date")."</td></tr>"
	."<tr><td>"._C_18."</td><td>".radio_form($conff['read'], "read")."</td></tr>"
	."<tr><td>"._F_2."</td><td>".radio_form($conff['hits'], "hits")."</td></tr>"
	."<tr><td>"._C_19."</td><td>".radio_form($conff['rate'], "rate")."</td></tr>"
	."<tr><td>"._C_20."</td><td>".radio_form($conff['letter'], "letter")."</td></tr>"
	."<tr><td>"._PAGELINK."</td><td>".radio_form($conff['link'], "link")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"files_conf_save\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close");
	echo $cont;
	foot();
}

function files_conf_save() {
	global $admin_file;
	$xdefis = ($_POST['defis']) ? urlencode($_POST['defis']) : "%3E";
	$protect = array("\n" => "", "\t" => "", "\r" => "", " " => "");
	$xmax_size = (!intval($_POST['max_size'])) ? 1048576 : $_POST['max_size'];
	$xtypefile = (!$_POST['typefile']) ? "zip,gzip,7z,rar,tar" : strtolower(strtr($_POST['typefile'], $protect));
	$content = "\$conff = array();\n"
	."\$conff['defis'] = \"".$xdefis."\";\n"
	."\$conff['temp'] = \"".$_POST['temp']."\";\n"
	."\$conff['path'] = \"".$_POST['path']."\";\n"
	."\$conff['max_size'] = \"".$xmax_size."\";\n"
	."\$conff['typefile'] = \"".$xtypefile."\";\n"
	."\$conff['tabcol'] = \"".$_POST['tabcol']."\";\n"
	."\$conff['linknum'] = \"".$_POST['linknum']."\";\n"
	."\$conff['listnum'] = \"".$_POST['listnum']."\";\n"
	."\$conff['num'] = \"".$_POST['num']."\";\n"
	."\$conff['anum'] = \"".$_POST['anum']."\";\n"
	."\$conff['nump'] = \"".$_POST['nump']."\";\n"
	."\$conff['anump'] = \"".$_POST['anump']."\";\n"
	."\$conff['stream'] = \"".$_POST['stream']."\";\n"
	."\$conff['homcat'] = \"".$_POST['homcat']."\";\n"
	."\$conff['viewcat'] = \"".$_POST['viewcat']."\";\n"
	."\$conff['catdesc'] = \"".$_POST['catdesc']."\";\n"
	."\$conff['subcat'] = \"".$_POST['subcat']."\";\n"
	."\$conff['addmail'] = \"".$_POST['addmail']."\";\n"
	."\$conff['add'] = \"".$_POST['add']."\";\n"
	."\$conff['addquest'] = \"".$_POST['addquest']."\";\n"
	."\$conff['broc'] = \"".$_POST['broc']."\";\n"
	."\$conff['down'] = \"".$_POST['down']."\";\n"
	."\$conff['upload'] = \"".$_POST['upload']."\";\n"
	."\$conff['autor'] = \"".$_POST['autor']."\";\n"
	."\$conff['date'] = \"".$_POST['date']."\";\n"
	."\$conff['read'] = \"".$_POST['read']."\";\n"
	."\$conff['hits'] = \"".$_POST['hits']."\";\n"
	."\$conff['rate'] = \"".$_POST['rate']."\";\n"
	."\$conff['letter'] = \"".$_POST['letter']."\";\n"
	."\$conff['link'] = \"".$_POST['link']."\";\n";
	save_conf("config/config_files.php", $content);
	header("Location: ".$admin_file.".php?op=files_conf");
}

function files_info() {
	head();
	echo files_navi(0, 5, 0, 0)."<div id=\"repadm_info\">".adm_info(1, "files", 0)."</div>";
	foot();
}

switch ($op) {
	case "files":
	files();
	break;
	
	case "files_add":
	files_add();
	break;
	
	case "files_save":
	files_save();
	break;
	
	case "files_delete":
	files_delete();
	break;
	
	case "files_ignore":
	$db->sql_query("UPDATE ".$prefix."_files SET status = '1' WHERE lid = '".$id."'");
	header("Location: ".$admin_file.".php?op=files&status=2");
	break;
	
	case "files_conf":
	files_conf();
	break;
	
	case "files_conf_save":
	files_conf_save();
	break;
	
	case "files_info":
	files_info();
	break;
}
?>