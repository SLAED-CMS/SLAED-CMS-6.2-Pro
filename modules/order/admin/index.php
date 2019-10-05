<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("ADMIN_FILE") || !is_admin_modul("order")) die("Illegal file access");

include("config/config_order.php");

function order_navi() {
	panel();
	$narg = func_get_args();
	$ops = array("order", "order_add", "order_conf", "order_info");
	$lang = array(_HOME, _ADD, _PREFERENCES, _INFO);
	return navi_gen(_ORDER, "order.png", "", $ops, $lang, "", "", $narg[0], $narg[1], $narg[2], $narg[3]);
}

function order() {
	global $prefix, $db, $admin_file, $conf, $confor;
	head();
	$cont = order_navi(0, 0, 0, 0);
	if (isset($_GET['send'])) $cont .= tpl_warn("warn", _OR_8, "", "", "info");
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $confor['anum'];
	$offset = intval($offset);
	$result = $db->sql_query("SELECT id, mail, info, com, ip, agent, date, status FROM ".$prefix."_order ORDER BY date DESC LIMIT ".$offset.", ".$confor['anum']);
	if ($db->sql_numrows($result) > 0) {
		$cont .= tpl_eval("open");
		list($numstories) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_order"));
		$r = $numstories;
		if ($numstories > $offset) $r -= $offset;
		$numpages = ceil($numstories / $confor['anum']);
		$cont .= "<table class=\"sl_table_list_sort\"><thead><tr><th>"._ID."</th><th>"._EMAIL."</th><th>"._IP."</th><th>"._DATE."</th><th class=\"{sorter: false}\">"._STATUS."</th><th class=\"{sorter: false}\">"._FUNCTIONS."</th></tr></thead><tbody>";
		while (list($id, $mail, $info, $com, $ip, $agent, $date, $status) = $db->sql_fetchrow($result)) {
			$act = ($status) ? 0 : 1;
			$infos = fields_out($info, "order");
			$cont .= "<tr><td>".$id."</td>"
			."<td>".title_tip($infos."<br>"._COMMENT.": ".$com."<br><br>"._BROWSER.": ".$agent).anti_spam($mail)."</td>"
			."<td>".user_geo_ip($ip, 4)."</td>"
			."<td>".format_time($date, _TIMESTRING)."</td>"
			."<td>".ad_status("", $status)."</td>"
			."<td>".add_menu(ad_status($admin_file.".php?op=order_active&amp;id=".$id."&amp;act=".$act, $status)."||<a href=\"".$admin_file.".php?op=order_add&amp;id=".$id."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=order_delete&amp;id=".$id."\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;"._ID.": ".$id."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>")."</td></tr>";
			$r--;
		}
		$cont .= "</tbody></table>";
		$cont .= num_page("pagenum", "", $numstories, $numpages, $confor['anum'], "op=order&amp;", $confor['anump']);
		$cont .= tpl_eval("close");
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function order_add() {
	global $prefix, $db, $admin_file, $stop, $confor;
	if (isset($_REQUEST['id'])) {
		$mid = intval($_REQUEST['id']);
		$result = $db->sql_query("SELECT mail, info, com, date FROM ".$prefix."_order WHERE id = '".$mid."'");
		list($mail, $info, $com, $date) = $db->sql_fetchrow($result);
	} else {
		$mid = $_POST['mid'];
		$mail = $_POST['mail'];
		$info = fields_save($_POST['field']);
		$com = save_text($_POST['com'], 1);
		$date = save_datetime(1, "date");
	}
	head();
	$cont = order_navi(0, 1, 0, 0);
	if ($stop) $cont .= tpl_warn("warn", $stop, "", "", "warn");
	if ($info) $cont .= preview($mail, $info, _COMMENT.": ".$com, "", "all");
	$cont .= tpl_eval("open");
	$cont .= "<form name=\"post\" action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_form\">"
	."<tr><td>"._OR_9.":</td><td><input type=\"email\" name=\"mail\" value=\"".$mail."\" maxlength=\"255\" class=\"sl_form\" placeholder=\""._OR_9."\" required></td></tr>"
	.fields_in($info, "order")
	."<tr><td>"._OR_10.":</td><td><textarea name=\"com\" cols=\"65\" rows=\"5\" class=\"sl_form\" placeholder=\""._OR_10."\">".$com."</textarea></td></tr>"
	."<tr><td>"._CHNGSTORY.":</td><td>".datetime(1, "date", $date, 16, "sl_form")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\">".ad_save("mid", $mid, "order_save")."</td></tr></table></form>";
	$cont .= tpl_eval("close");
	echo $cont;
	foot();
}

function order_save() {
	global $prefix, $db, $admin_file, $stop;
	$mid = intval($_POST['mid']);
	$mail = text_filter($_POST['mail']);
	$info = fields_save($_POST['field']);
	$com = text_filter($_POST['com']);
	$date = save_datetime(1, "date");
	checkemail($mail);
	if (!$stop && $_POST['posttype'] == "save") {
		if ($mid) {
			$db->sql_query("UPDATE ".$prefix."_order SET mail = '".$mail."', info = '".$info."', com = '".$com."', date = '".$date."' WHERE id = '".$mid."'");
		} else {
			$ip = getip();
			$agent = getagent();
			$db->sql_query("INSERT INTO ".$prefix."_order VALUES (NULL, '".$mail."', '".$info."', '".$com."', '".$ip."', '".$agent."', '".$date."', '1')");
		}
		header("Location: ".$admin_file.".php?op=order");
	} elseif ($_POST['posttype'] == "delete") {
		order_delete($mid);
	} else {
		order_add();
	}
}

function order_delete() {
	global $prefix, $db, $admin_file, $id;
	$arg = func_get_args();
	$id = ($arg[0]) ? $arg[0] : $id;
	if ($id) $db->sql_query("DELETE FROM ".$prefix."_order WHERE id = '".$id."'");
	referer($admin_file.".php?op=order");
}

function order_conf() {
	global $admin_file, $confor;
	head();
	$cont = order_navi(0, 2, 0, 0);
	$permtest = end_chmod("config/config_order.php", 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_conf\">"
	."<tr><td>"._OR_1.":</td><td><input type=\"email\" name=\"mail\" value=\"".$confor['mail']."\" maxlength=\"255\" class=\"sl_conf\" placeholder=\""._OR_1."\" required></td></tr>"
	."<tr><td>"._C_34.":</td><td><input type=\"number\" name=\"anum\" value=\"".$confor['anum']."\" class=\"sl_conf\" placeholder=\""._C_34."\" required></td></tr>"
	."<tr><td>"._C_36.":</td><td><input type=\"number\" name=\"anump\" value=\"".$confor['anump']."\" class=\"sl_conf\" placeholder=\""._C_36."\" required></td></tr>"
	."<tr><td>"._OR_2."</td><td>".radio_form($confor['an'], "an")."</td></tr>"
	."<tr><td>"._OR_3."</td><td>".radio_form($confor['pr'], "pr")."</td></tr>"
	."<tr><td>"._OR_4."</td><td>".radio_form($confor['ad'], "ad")."</td></tr>"
	."<tr><td>"._OR_5.":</td><td>".textarea("1", "text", $confor['text'], "all", "5", _OR_5, "1")."</td></tr>"
	."<tr><td>"._OR_6.":</td><td>".textarea("2", "info", $confor['info'], "all", "5", _OR_6, "1")."</td></tr>"
	."<tr><td>"._OR_7.":</td><td>".textarea("3", "sendinfo", $confor['sendinfo'], "all", "5", _OR_7, "1")."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"order_conf_save\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close");
	echo $cont;
	foot();
}
function order_conf_save() {
	global $admin_file;
	$xtext= save_text($_POST['text']);
	$xinfo = save_text($_POST['info']);
	$xsendinfo = save_text($_POST['sendinfo']);
	$content = "\$confor = array();\n"
	."\$confor['mail'] = \"".$_POST['mail']."\";\n"
	."\$confor['anum'] = \"".$_POST['anum']."\";\n"
	."\$confor['anump'] = \"".$_POST['anump']."\";\n"
	."\$confor['an'] = \"".$_POST['an']."\";\n"
	."\$confor['pr'] = \"".$_POST['pr']."\";\n"
	."\$confor['ad'] = \"".$_POST['ad']."\";\n"
	."\$confor['text'] = <<<HTML\n".$xtext."\nHTML;\n"
	."\$confor['info'] = <<<HTML\n".$xinfo."\nHTML;\n"
	."\$confor['sendinfo'] = <<<HTML\n".$xsendinfo."\nHTML;\n";
	save_conf("config/config_order.php", $content);
	header("Location: ".$admin_file.".php?op=order_conf");
}

function order_info() {
	head();
	echo order_navi(0, 3, 0, 0)."<div id=\"repadm_info\">".adm_info(1, "order", 0)."</div>";
	foot();
}

switch($op) {
	case "order":
	order();
	break;
	
	case "order_add":
	order_add();
	break;
	
	case "order_save":
	order_save();
	break;
	
	case "order_active":
	$db->sql_query("UPDATE ".$prefix."_order SET status = '".$act."' WHERE id = '".$id."'");
	if ($act) {
		list($mail) = $db->sql_fetchrow($db->sql_query("SELECT mail FROM ".$prefix."_order WHERE id = '".$id."'"));
		$amail = ($confor['mail']) ? $confor['mail'] : $conf['adminmail'];
		$subject = $conf['sitename']." - "._ORDER;
		$msg = $conf['sitename']." - "._ORDER."<br><br>";
		$msg .= bb_decode($confor['sendinfo'], "all");
		mail_send($mail, $amail, $subject, $msg, 0, 3);
		header("Location: ".$admin_file.".php?op=order&send=1");
	} else {
		header("Location: ".$admin_file.".php?op=order");
	}
	break;
	
	case "order_delete":
	order_delete();
	break;
	
	case "order_conf":
	order_conf();
	break;
	
	case "order_conf_save":
	order_conf_save();
	break;
	
	case "order_info":
	order_info();
	break;
}
?>