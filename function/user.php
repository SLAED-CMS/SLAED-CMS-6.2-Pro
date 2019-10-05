<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined('FUNC_FILE')) die('Illegal file access');

# Cookie set
function setCookies($id, $name, $pass, $num, $blockon, $theme) {
	global $conf;
	$info = base64_encode($id.':'.$name.':'.$pass.':'.$num.':'.$blockon.':'.$theme);
	setcookie($conf['user_c'], $info, time() + intval($conf['user_c_t']));
}

# User account navigation
function navi() {
	global $conf, $conffav, $confpr;
	$userinfo = getusrinfo();
	$uid = intval($userinfo['user_id']);
	if ($conf['name'] != 'account') get_lang('account');
	
	$title[] = _HOME;
	$ititle[] = _RETURNACCOUNT;
	$link[] = 'index.php?name=account';
	$img[] = 'account/home.png';
	
	if ($conf['forum_link']) {
		$title[] = _ACCOUNT;
		$ititle[] = _ACCOUNT;
		$link[] = 'forum/'.$conf['forum_link'];
		$img[] = 'account/account.png';
	}
	if ($conf['forum_mess']) {
		$title[] = _MESSAGES;
		$ititle[] = _PRIVAT;
		$link[] = 'forum/'.$conf['forum_mess'];
		$img[] = 'account/messages.png';
	}
	if ($conf['forum']) {
		$title[] = _FORUM;
		$ititle[] = _FORUM;
		$link[] = 'forum/index.php';
		$img[] = 'account/forum.png';
	}
	if ($confpr['act']) {
		$title[] = _MESSAGES;
		$ititle[] = _PRIVAT;
		$link[] = 'index.php?name=account&amp;op=privat';
		$img[] = 'account/messages.png';
	}
	if (is_active('clients') && is_mod_group('clients')) {
		get_lang('clients');
		$title[] = _PRODUCTS;
		$ititle[] = _PRODUCTSINFO;
		$link[] = 'index.php?name=clients';
		$img[] = 'account/product.png';
	}
	if (is_active('shop')) {
		get_lang('shop');
		$title[] = _CLIENT;
		$ititle[] = _CLIENTINFO;
		$link[] = 'index.php?name=shop&amp;op=clients';
		$img[] = 'account/clients.png';
		include('config/config_shop.php');
		if ($confso['part'] == 1) {
			$title[] = _PARTNER;
			$ititle[] = _PARTNERINFO;
			$link[] = 'index.php?name=shop&amp;op=partners';
			$img[] = 'account/partners.png';
		}
	}
	if (is_active('help') && is_mod_group('help')) {
		get_lang('help');
		$title[] = _HELP;
		$ititle[] = _HELPINFO;
		$link[] = 'index.php?name=help';
		$img[] = 'account/help.png';
	}
	if ($conffav['favact']) {
		$title[] = _FAVORITES;
		$ititle[] = _FAVORITES;
		$link[] = 'index.php?name=account&amp;op=favorites';
		$img[] = 'account/favorites.png';
	}
	$title[] = _INFO;
	$ititle[] = _PERSONALINFO;
	$link[] = 'index.php?name=account&amp;op=view&amp;id='.$uid;
	$img[] = 'account/account.png';
	
	$title[] = _CHANGE;
	$ititle[] = _CHANGE;
	$link[] = 'index.php?name=account&amp;op=edithome';
	$img[] = 'account/preferences.png';
	
	$title[] = _LOGOUT;
	$ititle[] = _LOGOUT;
	$link[] = 'index.php?name=account&amp;op=logout';
	$img[] = 'account/exit.png';
	
	$cont = '';
	$a = 5;
	$i = 1;
	$tdwidth = intval(100/$a);
	foreach ($title as $key => $val) {
		if (($i - 1) % $a == 0) $cont .= '<tr>';
		$cont .= '<td style="width: '.$tdwidth.'%;"><a href="'.$link[$key].'" title="'.$ititle[$key].'"><img src="'.img_find($img[$key]).'" alt="'.$ititle[$key].'" title="'.$ititle[$key].'"><br>'.$title[$key].'</a></td>';
		if ($i % $a == 0) $cont .= '</tr>';
		$i++;
	}
	return tpl_eval('open').'<table class="sl_table_navi">'.$cont.'</table>'.tpl_eval('close');
}

# Check group
function is_mod_group($name) {
	global $prefix, $db, $user;
	if (is_user()) {
		$uid = intval($user[0]);
		list($points, $group) = $db->sql_fetchrow($db->sql_query("SELECT user_points, user_group FROM ".$prefix."_users WHERE user_id = '".$uid."'"));
		list($mgroup, $grpoints, $grextra) = $db->sql_fetchrow($db->sql_query("SELECT m.mod_group, g.points, g.extra FROM ".$prefix."_modules AS m LEFT JOIN ".$prefix."_groups AS g ON (m.mod_group = g.id) WHERE m.title = '".$name."'"));
		if (intval($group) && $group != "" && $group == $mgroup && $grextra == '1') {
			return 1;
		} elseif ((intval($points) && $points >= $grpoints && $grextra != '1') || $mgroup == 0) {
			return 1;
		}
	}
	return 0;
}

# Message box
function message_box() {
	global $prefix, $db, $admin_file, $conf, $currentlang, $user;
	if ($conf['message'] == 1) {
		$querylang = ($conf['multilingual'] == 1) ? "AND (mlanguage = '".$currentlang."' OR mlanguage = '')" : "";
		$result = $db->sql_query("SELECT mid, title, content, expire, view FROM ".$prefix."_message WHERE active = '1' ".$querylang);
		if ($db->sql_numrows($result) > 0) {
			while (list($mid, $title, $content, $expire, $view) = $db->sql_fetchrow($result)) {
				$mid = intval($mid);
				if ($expire && $expire < time()) $db->sql_query("UPDATE ".$prefix."_message SET active = '0', expire = '0' WHERE mid = '".$mid."'");
				$content = bb_decode($content, "All");
				$exp = intval($expire - time());
				$exp = ($exp > 0) ? display_time($exp) : _UNLIMITED;
				$message_link = "| "._PURCHASED.": ".$exp." | <a href=\"".$admin_file.".php?op=msg_add&amp;id=".$mid."\" title=\""._EDIT."\">"._EDIT."</a> ]</div>";
				if ($view == 4 && is_moder()) {
					$content .= "<div class=\"sl_center\">[ "._VIEW.": "._MVADMIN." ".$message_link;
					return tpl_eval("messagebox", $title, $content);
				} elseif (($view == 3 && is_user()) || ($view == 3 && is_user() && is_moder())) {
					if (is_moder()) $content .= "<div class=\"sl_center\">[ "._VIEW.": "._MVUSERS." ".$message_link;
					return tpl_eval("messagebox", $title, $content);
				} elseif (($view == 2 && !is_user()) || ($view == 2 && !is_user() && is_moder())) {
					if (is_moder()) $content .= "<div class=\"sl_center\">[ "._VIEW.": "._MVANON." ".$message_link;
					return tpl_eval("messagebox", $title, $content);
				} elseif ($view == 1) {
					if (is_moder()) $content .= "<div class=\"sl_center\">[ "._VIEW.": "._MVALL." ".$message_link;
					return tpl_eval("messagebox", $title, $content);
				}
			}
		}
	}
}

# Get user info
function getusrinfo() {
	global $prefix, $db, $user;
	$uid = intval($user[0]);
	if (is_user() && $uid) {
		$info = $db->sql_fetchrow($db->sql_query("SELECT * FROM ".$prefix."_users WHERE user_id = '".$uid."'"));
		return $info;
	}
}

# Show user block
function userblock() {
	global $user, $db, $prefix, $conf;
	$uid = intval($user[0]);
	$block = intval($user[4]);
	if (is_user() && $block) {
		list($userblock) = $db->sql_fetchrow($db->sql_query("SELECT user_block FROM ".$prefix."_users WHERE user_id = '".$uid."'"));
		$userblock = bb_decode(str_replace(array("\"", "$", "'", "\\"), array("&#034;", "&#036;", "&#039;", "&#092;"), $userblock), "account");
		return tpl_block("", _MENUFOR, $userblock);
	}
}

# Show comments and form
function show_com() {
	global $prefix, $db, $admin_file, $conf, $confu, $confc, $user, $currentlang;
	$arg = func_get_args();
	$cont = "<a id=\"comm\"></a><div id=\"repcsave\">".ashowcom($arg[0], $conf['name'])."</div>";
	if (!is_user() && $confc['anonpost'] == 0) {
		$cont .= tpl_warn("warn", _NOANONCOMMENTS, "", "", "warn");
	} else {
		$userinfo = getusrinfo();
		if ($arg[1] == 1 || $userinfo['user_acess'] || (!is_user() && $confc['anonpost'] == 1)) $cont .= tpl_warn("warn", _POSTNOTE, "", "", "warn");
		$cont .= tpl_eval("open");
		$cont .= "<form name=\"post\" id=\"formcsave\" method=\"post\">"
		."<table class=\"sl_table_form\">";
		if (is_user()) {
			$cont .= "<tr><td>"._YOURNAME.":</td><td>".text_filter(substr($user[1], 0, 25))."<input type=\"hidden\" name=\"name\" value=\"\"></td></tr>";
		} else {
			$cont .= "<tr><td>"._YOURNAME.":</td><td><input type=\"text\" name=\"name\" value=\"".$confu['anonym']."\" maxlength=\"25\" class=\"sl_field ".$conf['style']."\"></td></tr>";
		}
		$cont .= "<tr><td>"._COMMENT.":</td><td>".textarea(1, "text", "", $conf['name'], "5")."</td></tr>"
		.captcha_random()
		."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"submit\" OnClick=\"AjaxLoad('POST', '0', 'csave', 'go=1&amp;op=savecom&amp;id=".$arg[0]."&amp;cid=".$arg[1]."&amp;mod=".$conf['name']."', { 'text':'"._CERROR1."' }); ClearForm(formcsave); return false;\" value=\""._COMMENTREPLY."\" title=\""._COMMENTREPLY."\" class=\"sl_but_blue\"></td></tr></table></form>";
		$cont .= tpl_eval("close");
	}
	return $cont;
}

# Save comments
function savecom() {
	global $prefix, $db, $user, $conf, $confc;
	$id = (isset($_POST['id'])) ? intval($_POST['id']) : 0;
	$cid = (isset($_POST['cid'])) ? intval($_POST['cid']) : 0;
	$mod = (isset($_POST['mod'])) ? analyze($_POST['mod']) : "";
	$postname = (isset($_POST['name'])) ? text_filter(substr($_POST['name'], 0, 25)) : "";
	$ip = getip();
	$comment = trim($_POST['text']);
	list($date) = $db->sql_fetchrow($db->sql_query("SELECT date FROM ".$prefix."_comment WHERE host_name = '".$ip."' ORDER BY id DESC LIMIT 1"));
	$stime = strtotime($date) + $confc['send'];
	$checks = str_replace(array("\n", "\r", "\t"), " ", $comment);
	$e = explode(" ", $checks);
	for ($a = 0; $a < count($e); $a++) $o = strlen($e[$a]);
	$stop = "";
	if ($comment == "") $stop = _CERROR1;
	if ($o > $confc['letter']) $stop = _CERROR2;
	if ((!is_user() && $postname == "") || (!is_user() && $confc['anonpost'] == 0)) $stop = _CERROR3;
	if ($stime > time()) $stop = sprintf(_CERROR5, $confc['send']);
	if (!is_moder($mod) && (($confc['link'] == 1 && !is_user()) || ($confc['link'] == 2)) && stripos($comment, "http://") !== false) $stop = _CERROR9;
	$urlclick = (!is_moder($mod) && (($confc['alink'] == 1 && !is_user()) || ($confc['alink'] == 2))) ? 1 : 0;
	if (captcha_check(1)) $stop = _SECCODEINCOR;
	if (!$stop && $id && $mod) {
		$comment = save_text($comment, $urlclick);
		if (is_user()) {
			$postid = intval($user[0]);
			$userinfo = getusrinfo();
			$postname = $userinfo['user_name'];
			$status = (!is_moder($mod) && ($cid == 1 || $userinfo['user_acess'])) ? 0 : 1;
		} else {
			$postid = "";
			$postname = $postname;
			$status = (!is_moder($mod) && ($cid == 1 || $confc['anonpost'] == 1)) ? 0 : 1;
		}
		$db->sql_query("INSERT INTO ".$prefix."_comment VALUES (NULL, '".$id."', '".$mod."', now(), '".$postid."', '".$postname."', '".$ip."', '".$comment."', '".$status."')");
		if ($status) numcom($id, $mod, 0, $postid);
		list($lcom_id) = $db->sql_fetchrow($db->sql_query("SELECT id FROM ".$prefix."_comment WHERE cid = '".$id."' AND uid = '".$postid."' ORDER BY id DESC LIMIT 1"));
		$finishlink = $conf['homeurl']."/index.php?name=".$mod."&amp;op=view&amp;id=".$id."#".$lcom_id;
		$clink = "<a href=\"".$finishlink."\">".$finishlink."</a>";
		addmail($confc['addmail'], $mod, $postname, deflmconst($mod), 1, $clink);
		echo ashowcom($id, $mod);
	} else {
		$stop = ($stop) ? $stop : _ERROR;
		echo tpl_warn("warn", $stop, "", "", "warn");
	}
}

# Save edit forum post
function editpost() {
	global $prefix, $db, $user;
	include("config/config_forum.php");
	$id = (isset($_POST['id'])) ? ((isset($_POST['id'])) ? intval($_POST['id']) : "") : ((isset($_GET['id'])) ? intval($_GET['id']) : "");
	$catid = (isset($_POST['cid'])) ? ((isset($_POST['cid'])) ? intval($_POST['cid']) : "") : ((isset($_GET['cid'])) ? intval($_GET['cid']) : "");
	$typ = (isset($_POST['typ'])) ? ((isset($_POST['typ'])) ? intval($_POST['typ']) : "") : ((isset($_GET['typ'])) ? intval($_GET['typ']) : "");
	$mod = (isset($_POST['mod'])) ? ((isset($_POST['mod'])) ? analyze($_POST['mod']) : "") : ((isset($_GET['mod'])) ? analyze($_GET['mod']) : "");
	$text = (isset($_POST['text'])) ? trim($_POST['text']) : "";
	if ($conffo['add'] && $id && $catid) {
		list($auth_edit, $auth_mod) = $db->sql_fetchrow($db->sql_query("SELECT auth_edit, auth_mod FROM ".$prefix."_categories WHERE id = '".$catid."'"));
		$isedit = is_acess($auth_edit);
		$ismod = is_acess($auth_mod);
		list($pid, $uid, $hometext, $fstatus) = $db->sql_fetchrow($db->sql_query("SELECT pid, uid, hometext, status FROM ".$prefix."_forum WHERE id = '".$id."'"));
		if ($pid) {
			$where = (is_moder($conf['name'])) ? "WHERE id = '".$pid."'" : "WHERE id = '".$pid."' AND status != '0'";
			list($fstatus) = $db->sql_fetchrow($db->sql_query("SELECT status FROM ".$prefix."_forum ".$where));
		}
		if ($ismod || ($isedit && $uid == intval($user[0]) && $fstatus > 2)) {
			if (!$text) {
				$content = ($typ) ? textareae("for".$id, "1", "editpost", $id, $catid, "0", $mod, $hometext, "15") : bb_decode($hometext, $mod);
				echo $content;
			} else {
				$postid = (is_user()) ? intval($user[0]) : "";
				$ip = getip();
				$checks = str_replace(array("\n", "\r", "\t"), " ", $text);
				$e = explode(" ", $checks);
				for ($a = 0; $a < count($e); $a++) $o = strlen($e[$a]);
				$stop = "";
				if ($text == "") $stop[] = _CERROR1;
				if ($o > $conffo['letter']) $stop[] = _CERROR2;
				if (!$stop) {
					$htext = save_text($text);
					$db->sql_query("UPDATE ".$prefix."_forum SET hometext = '".$htext."', e_uid = '".$postid."', e_ip_send = '".$ip."', e_time = now() WHERE id = '".$id."'");
					echo bb_decode($htext, $mod);
				} else {
					return tpl_warn("warn", $stop, "", "", "warn");
				}
			}
		} else {
			return tpl_warn("warn", _ERROR, "", "", "warn");
		}
	} else {
		return tpl_warn("warn", _ERROR, "", "", "warn");
	}
}

# Private messages input view
function prmess() {
	global $prefix, $db, $user, $conf, $confu, $confpr, $currentlang;
	$arg = func_get_args();
	$obj = analyze($arg[0]);
	$stop = $arg[1];
	$info = $arg[2];
	$typ = ($arg[3]) ? $arg[3] : intval($_GET['typ']);
	$uid = intval($user[0]);
	$newlistnum = intval($confpr['num']);
	$num = ($_GET['cid']) ? intval($_GET['cid']) : "1";
	$offset = ($num-1) * $newlistnum;
	$offset = intval($offset);
	$conf['name'] = "account";
	$conf['style'] = ($conf['style']) ? $conf['style'] : "sl_account";
	get_theme_inc();
	$cont = "";
	if ($typ == 1) {
		list($pr_num) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_privat WHERE uidin = '".$uid."' AND status <= '1'"));
		if ($pr_num >= $confpr['messin']) {
			$messinfo = sprintf(_PRINEXIT, $confpr['messin']);
			$fstatus = "warn";
		} elseif ($pr_num >= ($confpr['messin'] / 2)) {
			$acmess = ($confpr['messin'] - $pr_num);
			$messinfo = sprintf(_PRINMAX, $confpr['messin'], $pr_num, $acmess);
			$fstatus = "info";
		}
		if ($fstatus) $cont .= tpl_warn("warn", $messinfo, "", "", $fstatus);
		if ($stop) {
			$cont .= tpl_warn("warn", $stop, "", "", "warn");
		} elseif ($info) {
			$cont .= tpl_warn("warn", $info, "", "", "info");
		}
		$result = $db->sql_query("SELECT p.id, p.uidin, p.uidout, p.title, p.date, p.status, u.user_name FROM ".$prefix."_privat AS p LEFT JOIN ".$prefix."_users AS u ON (p.uidout = u.user_id) WHERE p.uidin = '".$uid."' AND p.status <= '1' ORDER BY p.date DESC LIMIT ".$offset.", ".$newlistnum);
		if ($db->sql_numrows($result) > 0) {
			$cont .= "<table class=\"sl_table_list\"><thead class=\"sl_table_list_head\"><tr><th>"._TITLE."</th><th>"._PRSE."</th><th>"._DATE."</th><th>"._FUNCTIONS."</th></tr></thead><tbody class=\"sl_table_list_body\">";
			while (list($id, $uidin, $uidout, $title, $date, $status, $user_name) = $db->sql_fetchrow($result)) {
				if ($status) {
					$ititle = _PROLD;
					$hidden = " sl_hidden";
				} else {
					$ititle = _PRNEW;
					$hidden = "";
				}
				$title = "<span title=\"".$ititle."\" class=\"sl_m_in".$hidden."\"></span><a OnClick=\"AjaxLoad('GET', '0', 'prmessin', 'go=1&amp;op=prmess&amp;id=".$id."&amp;cid=1&amp;typ=4&amp;mod=1', ''); return false;\" title=\"".$title."\">".cutstr($title, 35)."</a>";
				$post = ($user_name) ? user_info($user_name) : $confu['anonym'];
				$date = format_time($date, _TIMESTRING);
				$func = add_menu("<a OnClick=\"AjaxLoad('GET', '0', 'prmessin', 'go=1&amp;op=prmess&amp;id=".$id."&amp;cid=1&amp;typ=4&amp;mod=1', ''); return false;\" title=\""._SHOW."\">"._SHOW."</a>||<a OnClick=\"AjaxLoad('GET', '0', 'prmessin', 'go=1&amp;op=prmesssave&amp;id=".$id."', ''); return false;\" title=\""._SAVE."\">"._SAVE."</a>||<a OnClick=\"AjaxLoad('GET', '0', 'prmessin', 'go=1&amp;op=prmessdel&amp;id=".$id."&amp;typ=1', ''); return false;\" title=\""._DELETE."\">"._DELETE."</a>");
				$cont .= "<tr><td>".$title."</td><td>".$post."</td><td>".$date."</td><td>".$func."</td></tr>";
			}
			$cont .= "</tbody></table>";
		} else {
			$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
		}
		$numpages = ceil($pr_num / $newlistnum);
		$cont .= num_ajax("pagenum", $pr_num, $numpages, $newlistnum, $confpr['nump'], $num, "0", "1", "prmess", "prmessin", "", "1", "");
	} elseif ($typ == 2) {
		$result = $db->sql_query("SELECT p.id, p.uidin, p.uidout, p.title, p.date, p.status, u.user_name FROM ".$prefix."_privat AS p LEFT JOIN ".$prefix."_users AS u ON (p.uidin = u.user_id) WHERE p.uidout = '".$uid."' AND p.status <= '1' ORDER BY p.date DESC LIMIT ".$offset.", ".$newlistnum);
		if ($db->sql_numrows($result) > 0) {
			$cont .= "<table class=\"sl_table_list\"><thead class=\"sl_table_list_head\"><tr><th>"._TITLE."</th><th>"._PRRE."</th><th>"._DATE."</th><th>"._FUNCTIONS."</th></tr></thead><tbody class=\"sl_table_list_body\">";
			while (list($id, $uidin, $uidout, $title, $date, $status, $user_name) = $db->sql_fetchrow($result)) {
				if ($status) {
					$ititle = _PROLD;
					$hidden = " sl_hidden";
					$del = "";
				} else {
					$ititle = _PROUTNEW;
					$hidden = "";
					$del = "||<a OnClick=\"AjaxLoad('GET', '0', 'prmessou', 'go=1&amp;op=prmessdel&amp;id=".$id."&amp;typ=2', ''); return false;\" title=\""._DELETE."\">"._DELETE."</a>";
				}
				$title = "<span title=\"".$ititle."\" class=\"sl_m_out".$hidden."\"></span><a OnClick=\"AjaxLoad('GET', '0', 'prmessou', 'go=1&amp;op=prmess&amp;id=".$id."&amp;cid=2&amp;typ=4&amp;mod=2', ''); return false;\" title=\"".$title."\">".cutstr($title, 35)."</a>";
				$post = ($user_name) ? user_info($user_name) : $confu['anonym'];
				$date = format_time($date, _TIMESTRING);
				$func = add_menu("<a OnClick=\"AjaxLoad('GET', '0', 'prmessou', 'go=1&amp;op=prmess&amp;id=".$id."&amp;cid=2&amp;typ=4&amp;mod=2', ''); return false;\" title=\""._SHOW."\">"._SHOW."</a>".$del);
				$cont .= "<tr><td>".$title."</td><td>".$post."</td><td>".$date."</td><td>".$func."</td></tr>";
			}
			$cont .= "</tbody></table>";
		} else {
			$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
		}
		list($pr_num) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_privat WHERE uidout = '".$uid."' AND status <= '1'"));
		$numpages = ceil($pr_num / $newlistnum);
		$cont .= num_ajax("pagenum", $pr_num, $numpages, $newlistnum, $confpr['nump'], $num, "0", "1", "prmess", "prmessou", "", "2", "");
	} elseif ($typ == 3) {
		list($pr_num) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_privat WHERE uidin = '".$uid."' AND status = '2'"));
		if ($pr_num >= $confpr['messsav']) {
			$messinfo = sprintf(_PRSAVEEXIT, $confpr['messsav']);
			$fstatus = "warn";
		} elseif ($pr_num >= ($confpr['messsav'] / 2)) {
			$acmess = ($confpr['messsav'] - $pr_num);
			$messinfo = sprintf(_PRSAVEMAX, $confpr['messsav'], $pr_num, $acmess);
			$fstatus = "info";
		}
		if ($fstatus) $cont .= tpl_warn("warn", $messinfo, "", "", $fstatus);
		$result = $db->sql_query("SELECT p.id, p.uidin, p.uidout, p.title, p.date, p.status, u.user_name FROM ".$prefix."_privat AS p LEFT JOIN ".$prefix."_users AS u ON (p.uidout=u.user_id) WHERE p.uidin = '".$uid."' AND p.status = '2' ORDER BY p.date DESC LIMIT ".$offset.", ".$newlistnum);
		if ($db->sql_numrows($result) > 0) {
			$cont .= "<table class=\"sl_table_list\"><thead class=\"sl_table_list_head\"><tr><th>"._TITLE."</th><th>"._PRSE."</th><th>"._DATE."</th><th>"._FUNCTIONS."</th></tr></thead><tbody class=\"sl_table_list_body\">";
			while (list($id, $uidin, $uidout, $title, $date, $status, $user_name) = $db->sql_fetchrow($result)) {
			$title = "<span title=\""._PRMOVE."\" class=\"sl_m_save\"></span><a OnClick=\"AjaxLoad('GET', '0', 'prmesssa', 'go=1&amp;op=prmess&amp;id=".$id."&amp;cid=1&amp;typ=4&amp;mod=3', ''); return false;\" title=\"".$title."\">".cutstr($title, 35)."</a>";
				$post = ($user_name) ? user_info($user_name) : $confu['anonym'];
				$date = format_time($date, _TIMESTRING);
				$func = add_menu("<a OnClick=\"AjaxLoad('GET', '0', 'prmesssa', 'go=1&amp;op=prmess&amp;id=".$id."&amp;cid=1&amp;typ=4&amp;mod=3', ''); return false;\" title=\""._SHOW."\">"._SHOW."</a>||<a OnClick=\"AjaxLoad('GET', '0', 'prmesssa', 'go=1&amp;op=prmessdel&amp;id=".$id."&amp;typ=3', ''); return false;\" title=\""._DELETE."\">"._DELETE."</a>");
				$cont .= "<tr><td>".$title."</td><td>".$post."</td><td>".$date."</td><td>".$func."</td></tr>";
			}
			$cont .= "</tbody></table>";
		} else {
			$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
		}
		$numpages = ceil($pr_num / $newlistnum);
		$cont .= num_ajax("pagenum", $pr_num, $numpages, $newlistnum, $confpr['nump'], $num, "0", "1", "prmess", "prmesssa", "", "3", "");
	} elseif ($typ == 4) {
		if ($stop) {
			$cont .= tpl_warn("warn", $stop, "", "", "warn");
		} elseif ($info) {
			$cont .= tpl_warn("warn", $info, "", "", "info");
		}
		$id = (isset($_GET['id'])) ? intval($_GET['id']) : 0;
		$qid = (isset($_GET['cid'])) ? intval($_GET['cid']) : 0;
		$mod = (isset($_GET['mod'])) ? intval($_GET['mod']) : 0;
		if ($mod == 1) {
			$prmid = "prmessin";
		} elseif ($mod == 2) {
			$prmid = "prmessou";
		} elseif ($mod == 3) {
			$prmid = "prmesssa";
		} else {
			$prmid = "prmessfo";
		}
		if ($id) {
			if ($qid == "2") {
				list($idp, $uidin, $uidout, $title, $content, $date, $ip_sender, $status, $user_name) = $db->sql_fetchrow($db->sql_query("SELECT p.id, p.uidin, p.uidout, p.title, p.content, p.date, p.ip_sender, p.status, u.user_name FROM ".$prefix."_privat AS p LEFT JOIN ".$prefix."_users AS u ON (p.uidin = u.user_id) WHERE p.id = '".$id."' AND p.uidout = '".$uid."' LIMIT 1"));
			} else {
				list($idp, $uidin, $uidout, $title, $content, $date, $ip_sender, $status, $user_name) = $db->sql_fetchrow($db->sql_query("SELECT p.id, p.uidin, p.uidout, p.title, p.content, p.date, p.ip_sender, p.status, u.user_name FROM ".$prefix."_privat AS p LEFT JOIN ".$prefix."_users AS u ON (p.uidout = u.user_id) WHERE p.id = '".$id."' AND p.uidin = '".$uid."' LIMIT 1"));
				if (!$status) $db->sql_query("UPDATE ".$prefix."_privat SET status = '1' WHERE id = '".$id."' AND uidin = '".$uid."' AND status != '2'");
			}
			if ($idp) {
				$result = $db->sql_query("SELECT u.user_id, u.user_name, u.user_rank, u.user_email, u.user_website, u.user_avatar, u.user_regdate, u.user_from, u.user_sig, u.user_viewemail, u.user_points, u.user_warnings, u.user_gender, u.user_votes, u.user_totalvotes, g.name, g.rank, g.color FROM ".$prefix."_users AS u LEFT JOIN ".$prefix."_groups AS g ON ((g.extra=1 AND u.user_group=g.id) OR (g.extra!=1 AND u.user_points>=g.points)) WHERE u.user_id = '".$uidout."' ORDER BY g.extra DESC, g.points DESC");
				list($user_id, $user_name, $user_rank, $user_email, $user_website, $user_avatar, $user_regdate, $user_from, $user_sig, $user_viewemail, $user_points, $user_warnings, $user_gender, $user_votes, $user_totalvotes, $user_gname, $user_grank, $user_gcolor) = $db->sql_fetchrow($result);
				$avname = ($user_name) ? $user_name : $com_name." (".$confu['anonym'].")";
				$date = "<span title=\""._PADD."\" class=\"sl_t_post\">".format_time($date, _TIMESTRING)."</span>";
				$ip = (is_moder($conf['name'])) ? user_geo_ip($ip_sender, 4) : "";
				$avatar = ($user_name) ? (($user_avatar && file_exists($confu['adirectory']."/".$user_avatar)) ? $confu['adirectory']."/".$user_avatar : $confu['adirectory']."/default/00.gif") : $confu['adirectory']."/default/0.gif";
				$rank = ($user_rank) ? $user_rank : "";
				$trank = ($user_gname) ? _GROUP.": ".$user_gname : _RANK;
				$rlink = ($user_grank && file_exists(img_find("ranks/".$user_grank))) ? "<img src=\"".img_find("ranks/".$user_grank)."\" alt=\"".$trank."\" title=\"".$trank."\">" : "";
				$rate = ajax_rating(0, $user_id, $conf['name'], $user_votes, $user_totalvotes, $com_id, 1);
				$rwarn = ($user_warnings) ? _UWARNS.": ".warnings($user_warnings) : "";
				$group = ($user_gname) ? _GROUP.": <span style=\"color: ".$user_gcolor."\">".$user_gname."</span>" : "";
				$point = ($confu['point'] && $user_points) ? _POINTS.": ".$user_points : "";
				$regdate = ($user_regdate) ? _REG.": ".format_time($user_regdate) : _NO_INFO;
				$gender = ($user_gender) ? _GENDER.": ".gender($user_gender) : "";
				$from = ($user_from) ? _FROM.": ".$user_from : "";
				$sig = ($user_sig) ? "<hr>".$user_sig : "";
				$personal = (!$qid || $qid == "1") ? "<a href=\"javascript: InsertCode('name', '".$avname."', '', '', '1');\" title=\""._PERSONAL."\" class=\"sl_but_blue\">"._PERS."</a>" : "";
				$profil = ($confpr['profil'] && $user_name) ? "<a href=\"index.php?name=account&amp;op=view&amp;uname=".urlencode($user_name)."\" title=\""._PERSONALINFO."\" class=\"sl_but\">"._ACCOUNT."</a>" : "";
				$web = ($confpr['web'] && $user_website) ? "<a href=\"".$user_website."\" target=\"_blank\" title=\""._DOWNLLINK."\" class=\"sl_but\">"._SITE."</a>" : "";
				
				# Будущие функции
				#$warn = "<a href=\"javascript: scroll(0, 0);\" title=\""._WARNM."\">"._WARNM."</a>";
				#$thank = "<a href=\"javascript: scroll(0, 0);\" title=\""._THANK."\">"._THANK."</a>";
				$warn = "";
				$thank = "";
				
				$edit = (($uidin == $uid) || ($uidout == $uid && !$status)) ? add_menu("<a OnClick=\"AjaxLoad('GET', '0', '".$prmid."', 'go=1&amp;op=prmessdel&amp;id=".$idp."&amp;typ=".$mod."', ''); return false;\" title=\""._ONDELETE."\">"._ONDELETE."</a>") : "";
				$cont .= tpl_eval("privat-message", $avname, $date, $ip, cutstr($title, 35), $avatar, $rank, $rlink, $rate, $rwarn, $group, $point, $regdate, $gender, $from, bb_decode($content, $conf['name']), bb_decode($sig, $conf['name']), $personal, $profil, $web, $warn, $thank, $edit);
			}
		}
		if (!$info && (!$qid || $qid == "1")) {
			$sendname = (isset($_POST['name'])) ? ((isset($_POST['name'])) ? text_filter(substr($_POST['name'], 0, 25)) : "") : ((isset($_GET['uname'])) ? text_filter(substr(urldecode($_GET['uname']), 0, 25)) : "");
			$sеndtitle = (isset($_POST['title'])) ? text_filter(trim($_POST['title'])) : "";
			$sеndcontent = (isset($_POST['text'])) ? text_filter(trim($_POST['text'])) : "";
			$rpost = ($sendname) ? $sendname : (($user_name) ? $user_name : "");
			$rtitle = ($sеndtitle) ? $sеndtitle : (($title) ? _PRREP.": ".$title : "");
			$rcontent = ($sеndcontent) ? $sеndcontent : (($content) ? "[quote]".$content."[/quote]" : "");
			
			$idp = ($id) ? "2" : "1";
			$cont .= "<form name=\"post\" id=\"form".$prmid."\" method=\"post\">"
			."<table class=\"sl_table_form\">"
			."<tr><td>"._PRRE.":</td><td>".get_user_search("name", $rpost, "25", $conf['style'], "1")."</td></tr>"
			."<tr><td>"._TITLE.":</td><td><input type=\"text\" name=\"title\" value=\"".$rtitle."\" maxlength=\"100\" class=\"sl_field ".$conf['style']."\"></td></tr>"
			."<tr><td>"._MESSAGE.":</td><td>".textarea($idp, "text", $rcontent, $conf['name'], "15")."</td></tr>"
			."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"submit\" OnClick=\"AjaxLoad('POST', '0', '".$prmid."', 'go=1&amp;op=prmesssend', { 'name':'"._CERROR6."' }); return false;\" value=\""._SEND."\" title=\""._SEND."\" class=\"sl_but_blue\"></td></tr></table></form>";
		}
	}
	if ($obj) { return $cont; } else { echo $cont; }
}

# Private message send and save
function prmesssend() {
	global $prefix, $db, $user, $conf, $confpr;
	$postname = (isset($_POST['name'])) ? text_filter(substr($_POST['name'], 0, 25)) : "";
	$title = trim($_POST['title']);
	$text = trim($_POST['text']);
	$ip = getip();

	$uidin = (is_user_id($postname)) ? is_user_id($postname) : "";
	$uidout = (is_user()) ? intval($user[0]) : "";
	
	list($date) = $db->sql_fetchrow($db->sql_query("SELECT date FROM ".$prefix."_privat WHERE uidout = '".$uidout."' ORDER BY id DESC LIMIT 1"));
	$stime = strtotime($date) + $confpr['send'];
	$checks = str_replace(array("\n", "\r", "\t"), " ", $text);
	$e = explode(" ", $checks);
	for ($a = 0; $a < count($e); $a++) $o = strlen($e[$a]);
	
	$stop = array();
	if (!$postname) {
		$stop[] = _CERROR6;
	} elseif (!$uidin) {
		$stop[] = _CERROR7;
	}
	if ($confpr['himself'] && $uidin == $uidout) $stop[] = _CERROR8;
	if (!$title) $stop[] = _CERROR;
	if (!$text) $stop[] = _CERROR1;
	if ($o > $confpr['letter']) $stop[] = _CERROR2;
	if (!$uidout) $stop[] = _CERROR3;
	if ($stime > time()) $stop[] = sprintf(_CERROR5, $confpr['send']);

	list($pr_num) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_privat WHERE uidin = '".$uidin."' AND status <= '1'"));
	if ($pr_num >= $confpr['messin']) $stop[] = sprintf(_PRSENDOVER, $postname);
	
	if (!$stop && $confpr['act'] && is_user()) {
		$title = save_text($title, 1);
		$text = save_text($text);
		$db->sql_query("INSERT INTO ".$prefix."_privat VALUES (NULL, '".$uidin."', '".$uidout."', '".$title."', '".$text."', now(), '".$ip."', '0')");
		update_points(45);
		if ($confpr['newmail']) {
			list($user_email, $user_psmail) = $db->sql_fetchrow($db->sql_query("SELECT user_email, user_psmail FROM ".$prefix."_users WHERE user_id = '".$uidin."'"));
			if ($user_email && $user_psmail) {
				list($id) = $db->sql_fetchrow($db->sql_query("SELECT id FROM ".$prefix."_privat WHERE uidin = '".$uidin."' AND uidout = '".$uidout."' ORDER BY id DESC LIMIT 1"));
				$uname = text_filter(substr($user[1], 0, 25));
				$finishlink = $conf['homeurl']."/index.php?name=account&amp;op=privat&amp;id=".$id."#prmess";
				$link = "<a href=\"".$finishlink."\">".$finishlink."</a>";
				$subject = $conf['sitename']." - "._PRIVAT;
				$message = str_replace("[text]", sprintf(_PRNEWMAIL, $uname, $link), $conf['mtemp']);
				mail_send($user_email, $conf['adminmail'], $subject, $message, 0, 3);
			}
		}
		$info = sprintf(_PRSENDED, $postname);
		return prmess(0, 0, $info, 4);
	} else {
		$stop = ($stop) ? $stop : _ERROR;
		return prmess(0, $stop, 0, 4);
	}
}

# Private message save to user
function prmesssave() {
	global $prefix, $db, $user, $confpr;
	$uid = (is_user()) ? intval($user[0]) : 0;
	$id = (isset($_GET['id'])) ? intval($_GET['id']) : 0;
	list($pr_num) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_privat WHERE uidin = '".$uid."' AND status = '2'"));
	$pr_numi = $pr_num + 1;
	if ($pr_num >= $confpr['messsav']) {
		$stop = sprintf(_PRSAVEEXIT, $confpr['messsav']);
		$info = 0;
	} elseif ($pr_numi >= ($confpr['messsav'] / 2)) {
		$acmess = ($confpr['messsav'] - $pr_numi);
		$stop = 0;
		$info = sprintf(_PRSAVEMAX, $confpr['messsav'], $pr_numi, $acmess);
	}
	if (!$stop && $confpr['act'] && $uid && $id) $db->sql_query("UPDATE ".$prefix."_privat SET status = '2' WHERE id = '".$id."' AND uidin = '".$uid."'");
	return prmess(0, $stop, $info, 1);
}

# Private message delete
function prmessdel() {
	global $prefix, $db, $user, $confpr;
	$uid = (is_user()) ? intval($user[0]) : 0;
	$id = (isset($_GET['id'])) ? intval($_GET['id']) : 0;
	$typ = (isset($_GET['typ'])) ? intval($_GET['typ']) : 1;
	if ($confpr['act'] && $uid && $id) $db->sql_query("DELETE FROM ".$prefix."_privat WHERE (id = '".$id."' AND uidin = '".$uid."') OR (id = '".$id."' AND uidout = '".$uid."' AND status = '0')");
	return prmess(0, 0, 0, $typ);
}

# Favorites view
function favorview($fid, $mod) {
	global $prefix, $db, $user, $conffav;
	$uid = (is_user()) ? intval($user[0]) : 0;
	if ($conffav['favact'] && $uid) {
		list($fav) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_favorites WHERE uid = '".$uid."' AND fid = '".$fid."' AND modul = '".$mod."'"));
		if ($fav) {
			$content = "<span title=\""._FAVOR."\" class=\"sl_favor sl_favor_on\"></span>";
		} else {
			list($fav_num) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_favorites WHERE uid = '".$uid."'"));
			if ($fav_num >= $conffav['favorites']) {
				$fav_exit = sprintf(_FAVOR_EXIT, $conffav['favorites']);
				$content = "<span title=\"".$fav_exit."\" class=\"sl_favor sl_favor_off\"></span>";
			} else {
				$content = "<span id=\"rep".$fid.$mod."\"><span OnClick=\"AjaxLoad('GET', '0', '".$fid.$mod."', 'go=1&amp;op=favoradd&amp;id=".$fid."&amp;mod=".$mod."', ''); return false;\" title=\""._FAVOR_ADD."\" class=\"sl_favor\"></span></span>";
			}
		}
		return $content;
	}
}

# Favorites add
function favoradd() {
	global $db, $prefix, $user, $conffav;
	$fid = (isset($_GET['id'])) ? intval($_GET['id']) : "";
	$mod = (isset($_GET['mod'])) ? analyze($_GET['mod']) : "";
	$uid = (is_user()) ? intval($user[0]) : 0;
	if ($conffav['favact'] && $uid && $fid && $mod) {
		list($fav) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_favorites WHERE uid = '".$uid."' AND fid = '".$fid."' AND modul = '".$mod."'"));
		if ($fav) {
			echo favorview($fid, $mod);
		} else {
			$db->sql_query("INSERT INTO ".$prefix."_favorites VALUES (NULL, '".$uid."', '".$fid."', '".$mod."')");
			update_points(44);
		}
	}
	echo favorview($fid, $mod);
}

# Favorites liste view
function favorliste() {
	global $prefix, $db, $user, $conf, $conffav;
	$arg = func_get_args();
	$obj = analyze($arg[0]);
	$uid = intval($user[0]);
	
	$newlistnum = intval($conffav['num']);
	$num = ($_GET['cid']) ? intval($_GET['cid']) : "1";
	$offset = ($num-1) * $newlistnum;
	$offset = intval($offset);
	$a = ($num) ? $offset+1 : 1;
	
	list($fav_num) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_favorites WHERE uid = '".$uid."'"));
	if ($fav_num >= $conffav['favorites']) {
		$favinfo = sprintf(_FAVOR_EXIT, $conffav['favorites']);
		$fstatus = "warn";
	} else {
		$acfavor = ($conffav['favorites'] - $fav_num);
		$favinfo = sprintf(_FAVOR_MAX, $conffav['favorites'], $fav_num, $acfavor);
		$fstatus = "info";
	}
	
	$result = $db->sql_query("SELECT fid, modul FROM ".$prefix."_favorites WHERE uid = '".$uid."' ORDER BY id DESC LIMIT ".$offset.", ".$newlistnum);
	while (list($fid, $modul) = $db->sql_fetchrow($result)) $fmassiv[$modul][] = $fid;
	
	if (is_array($fmassiv)) {
		foreach ($fmassiv as $key => $val) {
			$fid = implode(",", $val);
			$numl = count($val);
			if ($key == "faq") {
				$result = $db->sql_query("SELECT f.id, f.fid, f.modul, n.title FROM ".$prefix."_favorites AS f LEFT JOIN ".$prefix."_faq AS n ON (f.fid=n.fid) WHERE f.uid = '".$uid."' AND n.fid IN (".$fid.") ORDER BY f.id DESC LIMIT 0, ".$numl);
				while (list($id, $fid, $modul, $title) = $db->sql_fetchrow($result)) $ffmassiv[] = array($id, $fid, $modul, $title);
			} elseif ($key == "files") {
				$result = $db->sql_query("SELECT f.id, f.fid, f.modul, n.title FROM ".$prefix."_favorites AS f LEFT JOIN ".$prefix."_files AS n ON (f.fid=n.lid) WHERE f.uid = '".$uid."' AND n.lid IN (".$fid.") ORDER BY f.id DESC LIMIT 0, ".$numl);
				while (list($id, $fid, $modul, $title) = $db->sql_fetchrow($result)) $ffmassiv[] = array($id, $fid, $modul, $title);
			} elseif ($key == "forum") {
				$result = $db->sql_query("SELECT f.id, f.fid, f.modul, n.title FROM ".$prefix."_favorites AS f LEFT JOIN ".$prefix."_forum AS n ON (f.fid=n.id) WHERE f.uid = '".$uid."' AND n.id IN (".$fid.") ORDER BY f.id DESC LIMIT 0, ".$numl);
				while (list($id, $fid, $modul, $title) = $db->sql_fetchrow($result)) $ffmassiv[] = array($id, $fid, $modul, $title);
			} elseif ($key == "help") {
				$result = $db->sql_query("SELECT f.id, f.fid, f.modul, n.title FROM ".$prefix."_favorites AS f LEFT JOIN ".$prefix."_help AS n ON (f.fid=n.sid) WHERE f.uid = '".$uid."' AND n.sid IN (".$fid.") ORDER BY f.id DESC LIMIT 0, ".$numl);
				while (list($id, $fid, $modul, $title) = $db->sql_fetchrow($result)) $ffmassiv[] = array($id, $fid, $modul, $title);
			} elseif ($key == "links") {
				$result = $db->sql_query("SELECT f.id, f.fid, f.modul, n.title FROM ".$prefix."_favorites AS f LEFT JOIN ".$prefix."_links AS n ON (f.fid=n.lid) WHERE f.uid = '".$uid."' AND n.lid IN (".$fid.") ORDER BY f.id DESC LIMIT 0, ".$numl);
				while (list($id, $fid, $modul, $title) = $db->sql_fetchrow($result)) $ffmassiv[] = array($id, $fid, $modul, $title);
			} elseif ($key == "media") {
				include("config/config_media.php");
				$result = $db->sql_query("SELECT f.id, f.fid, f.modul, n.title, n.subtitle FROM ".$prefix."_favorites AS f LEFT JOIN ".$prefix."_media AS n ON (f.fid=n.id) WHERE f.uid = '".$uid."' AND n.id IN (".$fid.") ORDER BY f.id DESC LIMIT 0, ".$numl);
				while (list($id, $fid, $modul, $title, $subtitle) = $db->sql_fetchrow($result)) {
					$title = ($subtitle) ? $title." ".urldecode($confm['mdefis'])." ".$subtitle : $title;
					$ffmassiv[] = array($id, $fid, $modul, $title);
				}
			} elseif ($key == "news") {
				$result = $db->sql_query("SELECT f.id, f.fid, f.modul, n.title FROM ".$prefix."_favorites AS f LEFT JOIN ".$prefix."_news AS n ON (f.fid=n.sid) WHERE f.uid = '".$uid."' AND n.sid IN (".$fid.") ORDER BY f.id DESC LIMIT 0, ".$numl);
				while (list($id, $fid, $modul, $title) = $db->sql_fetchrow($result)) $ffmassiv[] = array($id, $fid, $modul, $title);
			} elseif ($key == "pages") {
				$result = $db->sql_query("SELECT f.id, f.fid, f.modul, n.title FROM ".$prefix."_favorites AS f LEFT JOIN ".$prefix."_pages AS n ON (f.fid=n.pid) WHERE f.uid = '".$uid."' AND n.pid IN (".$fid.") ORDER BY f.id DESC LIMIT 0, ".$numl);
				while (list($id, $fid, $modul, $title) = $db->sql_fetchrow($result)) $ffmassiv[] = array($id, $fid, $modul, $title);
			} elseif ($key == "shop") {
				$result = $db->sql_query("SELECT f.id, f.fid, f.modul, n.title FROM ".$prefix."_favorites AS f LEFT JOIN ".$prefix."_products AS n ON (f.fid=n.id) WHERE f.uid = '".$uid."' AND n.id IN (".$fid.") ORDER BY f.id DESC LIMIT 0, ".$numl);
				while (list($id, $fid, $modul, $title) = $db->sql_fetchrow($result)) $ffmassiv[] = array($id, $fid, $modul, $title);
			}
		}
	}
	$cont = tpl_warn("warn", $favinfo, "", "", $fstatus);
	if ($ffmassiv) {
		$cont .= "<table class=\"sl_table_list\"><thead class=\"sl_table_list_head\"><tr><th>"._ID."</th><th>"._TITLE."</th><th>"._FUNCTIONS."</th></tr></thead><tbody class=\"sl_table_list_body\">";
		foreach ($ffmassiv as $key => $val) {
			$id = $val[0];
			$fid = $val[1];
			$modul = $val[2];
			$title = $val[3];
			$surl = "index.php?name=".$modul."&amp;op=view&amp;id=".$fid;
			$cont .= "<tr id=\"".$a."\">"
			."<td><a href=\"#".$a."\" title=\"".$a."\" class=\"sl_pnum\">".$a."</a></td>"
			."<td><a href=\"".$surl."\" title=\"".$title."\">".cutstr($title, 100)."</a></td>"
			."<td>".add_menu("<a href=\"index.php?name=".$modul."&amp;op=view&amp;id=".$fid."\" title=\""._SHOW."\">"._SHOW."</a>||<a href=\"index.php?name=".$modul."&amp;op=view&amp;id=".$fid."\" rel=\"sidebar\" title=\"".$title."\">"._S_FAVORITEN."</a>||<a OnClick=\"AjaxLoad('GET', '0', 'favorliste', 'go=1&amp;op=favordel&amp;id=".$id."', ''); return false;\" title=\""._DELETE."\">"._DELETE."</a>")."</td>";
			$a++;
		}
		$cont .= "</tbody></table>";
		$numpages = ceil($fav_num / $newlistnum);
		$cont .= num_ajax("pagenum", $fav_num, $numpages, $newlistnum, $conffav['nump'], $num, "0", "1", "favorliste", "favorliste", "", "", "");
	} else {
		$cont = tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	if ($obj) { return $cont; } else { echo $cont; }
}

# Favorites delete
function favordel() {
	global $prefix, $db, $user, $conffav;
	$uid = (is_user()) ? intval($user[0]) : 0;
	$id = (isset($_GET['id'])) ? intval($_GET['id']) : 0;
	if ($conffav['favact'] && $uid && $id) $db->sql_query("DELETE FROM ".$prefix."_favorites WHERE id = '".$id."' AND uid = '".$uid."'");
	return favorliste(0);
}

# RSS Channel
function rss_channel() {
	global $prefix, $db, $conf;
	include("config/config_rss.php");
	get_lang();
	header("Content-Type: application/rss+xml; charset="._CHARSET);
	header("Content-Encoding: none");

	$name = (isset($_POST['name'])) ? analyze($_POST['name']) : analyze($_GET['name']);
	$hmodul = explode(",", $conf['module']);
	$hi = mt_rand(0, count($hmodul) - 1);
	$cname = $hmodul[$hi];
	$name = ($name) ? $name : $cname;
	$cat = (isset($_POST['cat'])) ? intval($_POST['cat']) : intval($_GET['cat']);
	$num = (isset($_POST['num'])) ? intval($_POST['num']) : intval($_GET['num']);
	$num = ($num) ? (($num <= $confrs['max']) ? $num : $confrs['max']) : $confrs['min'];
	$id = (isset($_POST['id'])) ? intval($_POST['id']) : intval($_GET['id']);

	if (($name == "content") && $id) {
		$result = $db->sql_query("SELECT id, title, text, time FROM ".$prefix."_content WHERE id = '".$id."' AND time <= now()");
	} elseif ($name == "faq") {
		$where = ($cat) ? "WHERE s.catid = '".$cat."' AND s.time <= now() AND s.status != '0'" : "WHERE s.time <= now() AND s.status != '0'";
		$result = $db->sql_query("SELECT s.fid, s.name, s.title, s.time, s.hometext, c.title, u.user_name FROM ".$prefix."_faq AS s LEFT JOIN ".$prefix."_categories AS c ON (s.catid=c.id) LEFT JOIN ".$prefix."_users AS u ON (s.uid=u.user_id) ".$where." ORDER BY s.time DESC LIMIT ".$num);
	} elseif ($name == "files") {
		$where = ($cat) ? "WHERE s.cid = '".$cat."' AND s.date <= now() AND s.status != '0'" : "WHERE s.date <= now() AND s.status != '0'";
		$result = $db->sql_query("SELECT s.lid, s.name, s.title, s.date, s.description, c.title, u.user_name FROM ".$prefix."_files AS s LEFT JOIN ".$prefix."_categories AS c ON (s.cid=c.id) LEFT JOIN ".$prefix."_users AS u ON (s.uid=u.user_id) ".$where." ORDER BY s.date DESC LIMIT ".$num);
	} elseif ($name == "links") {
		$where = ($cat) ? "WHERE s.cid = '".$cat."' AND s.date <= now() AND s.status != '0'" : "WHERE s.date <= now() AND s.status != '0'";
		$result = $db->sql_query("SELECT s.lid, s.name, s.title, s.date, s.description, c.title, u.user_name FROM ".$prefix."_links AS s LEFT JOIN ".$prefix."_categories AS c ON (s.cid=c.id) LEFT JOIN ".$prefix."_users AS u ON (s.uid=u.user_id) ".$where." ORDER BY s.date DESC LIMIT ".$num);
	} elseif ($name == "media") {
		$where = ($cat) ? "WHERE s.cid = '".$cat."' AND s.date <= now() AND s.status != '0'" : "WHERE s.date <= now() AND s.status != '0'";
		$result = $db->sql_query("SELECT s.id, s.name, s.title, s.date, s.description, c.title, u.user_name FROM ".$prefix."_media AS s LEFT JOIN ".$prefix."_categories AS c ON (s.cid=c.id) LEFT JOIN ".$prefix."_users AS u ON (s.uid=u.user_id) ".$where." ORDER BY s.date DESC LIMIT ".$num);
	} elseif ($name == "pages") {
		$where = ($cat) ? "WHERE s.catid = '".$cat."' AND s.time <= now() AND s.status != '0'" : "WHERE s.time <= now() AND s.status != '0'";
		$result = $db->sql_query("SELECT s.pid, s.name, s.title, s.time, s.hometext, c.title, u.user_name FROM ".$prefix."_pages AS s LEFT JOIN ".$prefix."_categories AS c ON (s.catid=c.id) LEFT JOIN ".$prefix."_users AS u ON (s.uid=u.user_id) ".$where." ORDER BY s.time DESC LIMIT ".$num);
	} elseif ($name == "shop") {
		$where = ($cat) ? "WHERE s.cid = '".$cat."' AND s.time <= now() AND s.active = '1'" : "WHERE s.time <= now() AND s.active = '1'";
		$result = $db->sql_query("SELECT s.id, s.title, s.time, s.text, c.title FROM ".$prefix."_products AS s LEFT JOIN ".$prefix."_categories AS c ON (s.cid=c.id) ".$where." ORDER BY s.time DESC LIMIT ".$num);
	} elseif ($name == "news") {
		$where = ($cat) ? "WHERE s.catid = '".$cat."' AND s.time <= now() AND s.status != '0'" : "WHERE s.time <= now() AND s.status != '0'";
		$result = $db->sql_query("SELECT s.sid, s.name, s.title, s.time, s.hometext, c.title, u.user_name FROM ".$prefix."_news AS s LEFT JOIN ".$prefix."_categories AS c ON (s.catid=c.id) LEFT JOIN ".$prefix."_users AS u ON (s.uid=u.user_id) ".$where." ORDER BY s.time DESC LIMIT ".$num);
		$name = "news";
	} else {
		$result = "";
		$name = "";
	}

	$content = "<?xml version=\"1.0\" encoding=\""._CHARSET."\"?>\n"
	."<rss version=\"2.0\">\n"
	."<channel>\n"
	."<title>".htmlspecialchars($conf['sitename'])."</title>\n"
	."<link>".$conf['homeurl']."</link>\n"
	."<description>".htmlspecialchars($conf['slogan'])."</description>\n"
	."<generator>SLAED CMS ".$conf['version']."</generator>\n"
	."<copyright>Copyright (c) SLAED CMS ".$conf['version']."</copyright>\n"
	."<language>".htmlspecialchars(substr(_LOCALE, 0, 2))."</language>\n"
	."<lastBuildDate>".date("D, j M Y H:m:s O")."</lastBuildDate>\n\n";
	if ($name && $name != "content" && $name != "shop" && $result) {
		while (list($rid, $uname, $rtitle, $rtime, $rhometext, $rctitle, $user_name) = $db->sql_fetchrow($result)) {
			$rauthor = ($user_name) ? $user_name : (($uname) ? $uname : $confu['anonym']);
			$content .= "<item>\n"
			."<title>".htmlspecialchars($rtitle)."</title>\n"
			."<pubDate>".htmlspecialchars(date("D, j M Y H:m:s O", strtotime($rtime)))."</pubDate>\n"
			."<guid>".$conf['homeurl']."/index.php?name=".$name."&amp;op=view&amp;id=".$rid."</guid>\n"
			."<link>".$conf['homeurl']."/index.php?name=".$name."&amp;op=view&amp;id=".$rid."</link>\n"
			."<description>".htmlspecialchars(bb_decode($rhometext, $name, 1))."</description>\n"
			."<comments>".$conf['homeurl']."/index.php?name=".$name."&amp;op=view&amp;id=".$rid."#".$rid."</comments>\n";
			$content .= ($rctitle) ? "<category>".htmlspecialchars($rctitle)."</category>\n" : "";
			$content .= "<author>antispam@antispam.com (".htmlspecialchars($rauthor).")</author>\n"
			."</item>\n\n";
		}
	} elseif ($name && $name == "content" && $result) {
		list($rid, $rtitle, $rhometext, $rtime) = $db->sql_fetchrow($result);
		$content .= "<item>\n"
		."<title>".htmlspecialchars($rtitle)."</title>\n"
		."<pubDate>".htmlspecialchars(date("D, j M Y H:m:s O", strtotime($rtime)))."</pubDate>\n"
		."<guid>".$conf['homeurl']."/index.php?name=".$name."&amp;op=view&amp;id=".$rid."</guid>\n"
		."<link>".$conf['homeurl']."/index.php?name=".$name."&amp;op=view&amp;id=".$rid."</link>\n"
		."<description>".htmlspecialchars(bb_decode($rhometext, $name))."</description>\n"
		."</item>\n\n";
	} elseif ($name && $name == "shop" && $result) {
		while (list($rid, $rtitle, $rtime, $rhometext, $rctitle) = $db->sql_fetchrow($result)) {
			$content .= "<item>\n"
			."<title>".htmlspecialchars($rtitle)."</title>\n"
			."<pubDate>".htmlspecialchars(date("D, j M Y H:m:s O", strtotime($rtime)))."</pubDate>\n"
			."<guid>".$conf['homeurl']."/index.php?name=".$name."&amp;op=view&amp;id=".$rid."</guid>\n"
			."<link>".$conf['homeurl']."/index.php?name=".$name."&amp;op=view&amp;id=".$rid."</link>\n"
			."<description>".htmlspecialchars(bb_decode($rhometext, $name))."</description>\n"
			."<comments>".$conf['homeurl']."/index.php?name=".$name."&amp;op=view&amp;id=".$rid."#".$rid."</comments>\n";
			$content .= ($rctitle) ? "<category>".htmlspecialchars($rctitle)."</category>\n" : "";
			$content .= "</item>\n\n";
		}
	}
	$content .= "</channel>\n</rss>";
	return $content;
}

# Open search
function open_search() {
	global $conf;
	get_lang();
	header("Content-Type: application/opensearchdescription+xml");
	header("Content-Encoding: none");
	return "<?xml version=\"1.0\" encoding=\""._CHARSET."\"?>\n"
	."<OpenSearchDescription xmlns=\"http://a9.com/-/spec/opensearch/1.1/\">\n"
	."<ShortName>".htmlspecialchars($conf['sitename'])."</ShortName>\n"
	."<Description>".htmlspecialchars($conf['slogan'])."</Description>\n"
	."<Tags>".htmlspecialchars(str_replace(",", ", ", $conf['keywords']))."</Tags>\n"
	."<Url type=\"application/atom+xml\" template=\"".$conf['homeurl']."/index.php?name=search&amp;word={searchTerms}\"/>\n"
	."<Url type=\"application/rss+xml\" template=\"".$conf['homeurl']."/index.php?name=search&amp;word={searchTerms}\"/>\n"
	."<Url type=\"text/html\" template=\"".$conf['homeurl']."/index.php?name=search&amp;word={searchTerms}\"/>\n"
	."<Image height=\"16\" width=\"16\" type=\"image/x-icon\">".$conf['homeurl']."/templates/".$conf['theme']."/favicon.ico</Image>\n"
	."<Image height=\"16\" width=\"16\" type=\"image/png\">".$conf['homeurl']."/templates/".$conf['theme']."/favicon.png</Image>\n"
	."<Attribution>Copyright (c) SLAED CMS ".$conf['version']."</Attribution>\n"
	."<Language>".htmlspecialchars(substr(_LOCALE, 0, 2))."</Language>\n"
	."</OpenSearchDescription>\n";
}

# Open xsl template
function open_xsl() {
	global $conf;
	if (file_exists('config/sitemap/sitemap.xsl')) {
		$file = file_get_contents('config/sitemap/sitemap.xsl');
		$licens = str_replace('&copy;', '©', base64_decode($conf['lic_h']).date('Y').base64_decode($conf['lic_f']));
		$title = $conf['sitename'].' - '._SITEMAP;
		$langs = array('$lan[0]' => $title, '$lan[1]' => $licens, '$lan[2]' => _SITEMAP_XML, '$lan[3]' => _URL, '$lan[4]' => _PRIORITY, '$lan[5]' => _CHANGEFREQ, '$lan[6]' => _LASTMOD);
		$cont = strtr($file, $langs);
	} else {
		$cont = '';
	}
	return $cont;
}

# Show statistic
switch(isset($_GET['stat'])) {
	case "1":
	$img = (intval($_GET['img'])) ? "_".$_GET['img'] : "";
	$spath = "config/counter/";
	$sdate = file($spath."stat.txt");
	$con = explode("|", trim($sdate[0]));
	$image = imagecreatefrompng(img_find("banners/stat".$img.".png"));
	$white = imagecolorallocate($image, 255, 255, 255);
	imagestring($image, 1, 22, 4, $con[2]."/".$con[1], $white);
	header("Content-type: image/png");
	imagepng($image);
	imagedestroy($image);
	exit;
	break;
}
?>