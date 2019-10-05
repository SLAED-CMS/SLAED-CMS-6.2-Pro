<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined('MODULE_FILE')) {
	header('Location: ../../index.php');
	exit;
}
get_lang($conf['name']);
include('config/config_news.php');
include('config/config_rss.php');
if ($conf['forum']) include('function/forum.php');

function account() {
	global $conf, $confu, $stop;
	if (!is_user()) {
		head($conf['defis'].' '._USERREGLOGIN);
		$cont = tpl_eval('title', _USERREGLOGIN);
		if ($stop) $cont .= tpl_warn('warn', $stop, '', '', 'warn');
		$cont .= tpl_eval('open');
		$cont .= "<form action=\"index.php?name=".$conf['name']."\" method=\"post\">"
		."<table class=\"sl_table_form\">"
		."<tr><td>"._NICKNAME.":</td><td><input type=\"text\" name=\"user_name\" maxlength=\"25\" class=\"sl_field ".$conf['style']."\" placeholder=\""._NICKNAME."\" required></td></tr>"
		."<tr><td>"._PASSWORD.":</td><td><input type=\"password\" name=\"user_password\" maxlength=\"25\" class=\"sl_field ".$conf['style']."\" placeholder=\""._PASSWORD."\" required></td></tr>";
		if (extension_loaded("gd") && ($conf['gfx_chk'] == 2 || $conf['gfx_chk'] == 4 || $conf['gfx_chk'] == 5 || $conf['gfx_chk'] == 7)) $cont .= get_captcha();
		$cont .= "<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"login\"><input type=\"submit\" value=\""._USERLOGIN."\" class=\"sl_but_blue\"></td></tr>"
		."<tr><td colspan=\"2\" class=\"sl_center\"><a href=\"index.php?name=".$conf['name']."&amp;op=passlost\" title=\""._PASSWORDLOST."\" class=\"sl_but_foot\">"._PASSWORDLOST."</a><a href=\"index.php?name=".$conf['name']."&amp;op=newuser\" title=\""._REGNEWUSER."\" class=\"sl_but_foot\">"._REGNEWUSER."</a></td></tr>";
		$cont .= ($confu['network']) ? "<tr><td colspan=\"2\" class=\"sl_center\">"._LOGINNETWORK."</td></tr><tr><td colspan=\"2\" class=\"sl_center\">".make_network_code()."</td></tr>" : "";
		$cont .= "</table></form>";
		$cont .= tpl_eval("close");
		echo $cont;
		foot();
	} elseif (is_user()) {
		profil();
	}
}

function checkuser($user_name, $user_email, $rulescheck) {
	global $prefix, $db, $conf, $confu, $stop;
	if ($confu['rule'] && $rulescheck != "1") $stop[] = _ERROR_RULES;
	checkemail($user_email);
	$mail_b = explode(",", $confu['mail_b']);
	foreach ($mail_b as $val) if ($val != "" && $val == strtolower($user_email)) $stop[] = _MAIL_BLOCK;
	$name_b = explode(",", $confu['name_b']);
	foreach ($name_b as $val) if ($val != "" && $val == strtolower($user_name)) $stop[] = _NAME_BLOCK;
	if (!$user_name || !analyze_name($user_name)) $stop[] = _ERRORINVNICK;
	if (strlen($user_name) > 25) $stop[] = _NICKLONG;
	if ($db->sql_numrows($db->sql_query("SELECT user_name FROM ".$prefix."_users WHERE user_name = '".$user_name."'")) > 0) $stop[] = _NICKTAKEN;
	if ($db->sql_numrows($db->sql_query("SELECT user_name FROM ".$prefix."_users_temp WHERE user_name = '".$user_name."'")) > 0) $stop[] = _NICKTAKEN;
	if ($db->sql_numrows($db->sql_query("SELECT user_email FROM ".$prefix."_users WHERE user_email = '".$user_email."'")) > 0) $stop[] = _ERROR_EMAIL;
	if ($db->sql_numrows($db->sql_query("SELECT user_email FROM ".$prefix."_users_temp WHERE user_email = '".$user_email."'")) > 0) $stop[] = _ERROR_EMAIL;
	return($stop);
}

function newuser() {
	global $db, $conf, $confu, $stop;
	if (!is_user()) {
		head($conf['defis']." "._REGNEWUSER);
		if ($stop) {
			$cont = tpl_eval("title", _NEWUSERERROR);
			$cont .= tpl_warn("warn", $stop, "", "", "warn");
		} else {
			$cont = tpl_eval("title", _REGNEWUSER);
		}
		if (!$confu['reg']) {
			$cont .= tpl_warn("warn", _NOREG, "", "", "warn");
		} else {
			$unkey = md5_salt($conf['sitekey']);
			$user_name = (isset($_POST[$unkey])) ? text_filter(substr($_POST[$unkey], 0, 25)) : "";
			$user_email = (isset($_POST['user_email'])) ? text_filter($_POST['user_email']) : "";
			$cont .= tpl_eval("open");
			$cont .= "<form action=\"index.php?name=".$conf['name']."\" method=\"post\">"
			."<table class=\"sl_table_form\">"
			."<tr><td>"._NICKNAME.":</td><td><input type=\"text\" name=\"".$unkey."\" value=\"".$user_name."\" maxlength=\"25\" class=\"sl_field ".$conf['style']."\" placeholder=\""._NICKNAME."\" required></td></tr>"
			."<tr><td>"._EMAIL.":</td><td><input type=\"text\" name=\"user_email\" value=\"".$user_email."\" maxlength=\"255\" class=\"sl_field ".$conf['style']."\" placeholder=\""._EMAIL."\" required></td></tr>"
			."<tr><td>".title_tip(_BLANKFORAUTO)._PASSWORD.":</td><td><input type=\"password\" name=\"user_password\" maxlength=\"25\" class=\"sl_field ".$conf['style']."\" placeholder=\""._PASSWORD."\"></td></tr>"
			."<tr><td>".title_tip(_BLANKFORAUTO)._RETYPEPASSWORD.":</td><td><input type=\"password\" name=\"user_password2\" maxlength=\"25\" class=\"sl_field ".$conf['style']."\" placeholder=\""._RETYPEPASSWORD."\"></td></tr>";
			if (extension_loaded("gd") && ($conf['gfx_chk'] == 3 || $conf['gfx_chk'] == 4 || $conf['gfx_chk'] == 6 || $conf['gfx_chk'] == 7)) $cont .= get_captcha();
			if ($confu['rule']) {
				$cont .= "<tr><td>"._RULES.":</td><td><textarea cols=\"50\" rows=\"10\" class=\"sl_field ".$conf['style']."\">".$confu['rules']."</textarea></td></tr>"
				."<tr><td>"._RULES_OK."</td><td><input type=\"checkbox\" name=\"rulescheck\" value=\"1\" class=\"sl_field ".$conf['style']."\" required></td></tr>";
			}
			$cont .= "<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"finnewuser\"><input type=\"submit\" value=\""._NEWUSER."\" class=\"sl_but_blue\"></td></tr>"
			."<tr><td colspan=\"2\" class=\"sl_center\"><a href=\"index.php?name=".$conf['name']."\" title=\""._USERLOGIN."\" class=\"sl_but_foot\">"._USERLOGIN."</a><a href=\"index.php?name=".$conf['name']."&amp;op=passlost\" title=\""._PASSWORDLOST."\" class=\"sl_but_foot\">"._PASSWORDLOST."</a></td></tr>";
			$cont .= ($confu['network']) ? "<tr><td colspan=\"2\" class=\"sl_center\">"._LOGINNETWORK."</td></tr><tr><td colspan=\"2\" class=\"sl_center\">".make_network_code()."</td></tr>" : "";
			$cont .= "</table></form>";
			$cont .= tpl_eval("close");
		}
		echo $cont;
		foot();
	} elseif (is_user()) {
		profil();
	}
}

function finnewuser() {
	global $prefix, $db, $conf, $confu, $stop;
	if (!$confu['reg']) {
		head($conf['defis']." "._REGNEWUSER);
		echo tpl_warn("warn", _NOREG, "", "", "warn");
		foot();
	} else {
		$unkey = md5_salt($conf['sitekey']);
		$user_name = text_filter($_POST[$unkey], 1);
		$user_email = text_filter($_POST['user_email'], 1);
		checkuser($user_name, $user_email, $_POST['rulescheck']);
		$user_password = htmlspecialchars(substr($_POST['user_password'], 0, 40));
		$user_password2 = htmlspecialchars(substr($_POST['user_password2'], 0, 40));
		if (extension_loaded("gd") && $_POST['check'] != $_SESSION['captcha'] && ($conf['gfx_chk'] == 3 || $conf['gfx_chk'] == 4 || $conf['gfx_chk'] == 6 || $conf['gfx_chk'] == 7)) $stop[] = _SECCODEINCOR;
		unset($_SESSION['captcha']);
		if ($user_password == "" && $user_password2 == "") {
			$user_password = gen_pass($confu['minpass']);
		} elseif ($user_password != $user_password2) {
			$stop[] = _ERROR_PASS;
		} elseif ($user_password == $user_password2 && strlen($user_password) < $confu['minpass']) {
			$stop[] = _CHARMIN.": ".$confu['minpass'];
		}
		if (!$stop) {
			$check_num = md5(gen_pass(10));
			$time = time();
			$finishlink = $conf['homeurl']."/index.php?name=".$conf['name']."&amp;op=activate&amp;user=".urlencode($user_name)."&amp;num=".$check_num;
			$user_name = text_filter($user_name);
			$user_email = text_filter($user_email);
			$db->sql_query("INSERT INTO ".$prefix."_users_temp (user_id, user_name, user_email, user_password, user_regdate, check_num, time) VALUES (NULL, '".$user_name."', '".$user_email."', '".$user_password."', now(), '".$check_num."', '".$time."')");
			head($conf['defis']." "._ACCOUNTCREATED);
			if ($confu['nomail'] == 1) {
				$cont = tpl_eval("title", _ACCOUNTCREATED);
				$cont .= tpl_warn("warn", _TOFINISHUSERN, "", "", "info");
				$cont .= tpl_eval("open");
				$cont .= "<form action=\"index.php\" method=\"get\">"
				."<table class=\"sl_table_form\">"
				."<tr><td>"._UNICKNAME.":</td><td>".$user_name."</td></tr>"
				."<tr><td>"._UPASSWORD.":</td><td>".$user_password."</td></tr>"
				."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"name\" value=\"".$conf['name']."\"><input type=\"hidden\" name=\"op\" value=\"activate\"><input type=\"hidden\" name=\"user\" value=\"".urlencode($user_name)."\"><input type=\"hidden\" name=\"num\" value=\"".$check_num."\"><input type=\"submit\" value=\""._ACTIVATIONSUB."\" class=\"sl_but_blue\"></td></tr></table></form>";
				$cont .= tpl_eval("close");
			} else {
				$link = "<a href=\"".$finishlink."\">".$finishlink."</a>";
				$subject = $conf['sitename']." - "._ACTIVATIONSUB;
				$message = str_replace("[text]", sprintf(_PASSFSEND, $user_email, $conf['sitename'], $link, $user_name, $user_password)."<br><br>"._IFYOUDIDNOTASK, $conf['mtemp']);
				mail_send($user_email, $conf['adminmail'], $subject, $message, 0, 3);
				$cont = tpl_eval("title", _ACCOUNTCREATED).tpl_warn("warn", _YOUAREREGISTERED."<br><br>"._FINISHUSERCONF."<br><br>"._THANKSUSER, "", 30, "info");
			}
			echo $cont;
			foot();
		} else {
			newuser();
		}
	}
}

function network() {
	global $prefix, $db, $conf, $confu;
	$confu['network'] = 1;
	if ($confu['network'] && isset($_POST['token'])) {
		$s = file_get_contents('http://ulogin.ru/token.php?token='.$_POST['token'].'&host='.$_SERVER['HTTP_HOST']); 
		$ulog = json_decode($s, true);
		if (empty($ulog['error']) && isArray($ulog)) {
			$nickname = isset($ulog['nickname']) ? ucfirst(make_translit($ulog['nickname'], 1)) : '';
			$first_name = isset($ulog['first_name']) ? ucfirst(make_translit($ulog['first_name'], 1)) : '';
			$last_name = isset($ulog['last_name']) ? ucfirst(make_translit($ulog['last_name'], 1)) : '';
			$variants = array();
			$variants[] = substr($first_name, 0, 25);
			if (!empty($nickname)) {
				$variants[] = substr($nickname, 0, 25);
				$variants[] = substr($nickname.'-'.$first_name, 0, 25);
			}
			if (!empty($last_name)) {
				$variants[] = substr($last_name, 0, 25);
				$variants[] = substr($first_name.'-'.$last_name, 0, 25);
			}
			$variants[] = substr($first_name, 0, 20).'-'.date('Y');
			$variants[] = substr($first_name, 0, 22).'-'.rand(1, 99);
			$variants[] = substr($first_name, 0, 20).'-'.gen_pass(4);
			foreach ($variants as $var) {
				if ($db->sql_numrows($db->sql_query("SELECT user_name FROM ".$prefix."_users WHERE user_name = '".$var."'")) == 0) {
					$uname = $var;
					break;
				}
			}
			$upass = md5_salt(trim($ulog['identity']));
			$uip = getip();
			$uagent = getagent();
			$result = $db->sql_query("SELECT user_id, user_name, user_password, user_storynum, user_blockon, user_theme FROM ".$prefix."_users WHERE user_password = '".$upass."'");
			list($user_id, $user_name, $user_password, $user_storynum, $user_blockon, $user_theme) = $db->sql_fetchrow($result);
			if ($db->sql_numrows($result) == 1) {
				setCookies($user_id, $user_name, $user_password, $user_storynum, $user_blockon, $user_theme);
				$db->sql_query("DELETE FROM ".$prefix."_session WHERE uname = '".$uip."' AND guest = '0'");
				$db->sql_query("UPDATE ".$prefix."_users SET user_last_ip = '".$uip."', user_lastvisit = now(), user_agent = '".$uagent."' WHERE user_id = '".$user_id."'");
				login_report(0, 1, $user_name, '');
				referer('index.php?name='.$conf['name'].'&op=profil');
			} else {
				$uemail = isset($ulog['email']) ? mb_strtolower($ulog['email']) : '';
				$network = isset($ulog['profile']) ? $ulog['profile'] : $ulog['network'];
				$db->sql_query("INSERT INTO ".$prefix."_users (user_id, user_name, user_email, user_avatar, user_regdate, user_password, user_last_ip, user_agent, user_network) VALUES (NULL, '".$uname."', '".$uemail."', 'default/00.gif', now(), '".$upass."', '".$uip."', '".$uagent."', '".$network."')");
				list($user_id, $user_name, $user_password, $user_storynum, $user_blockon, $user_theme) = $db->sql_fetchrow($db->sql_query("SELECT user_id, user_name, user_password, user_storynum, user_blockon, user_theme FROM ".$prefix."_users WHERE user_password = '".$upass."'"));
				setCookies($user_id, $user_name, $user_password, $user_storynum, $user_blockon, $user_theme);
				$db->sql_query("DELETE FROM ".$prefix."_session WHERE uname = '".$uip."' AND guest = '0'");
				$db->sql_query("UPDATE ".$prefix."_users SET user_lastvisit = now() WHERE user_id = '".$user_id."'");
				$uphoto = isset($ulog['photo']) ? $ulog['photo'] : '';
				if ($uphoto) {
					$anetwork = isset($ulog['network']) ? substr(make_translit($ulog['network'], 1), 0, 25) : 'network';
					$uavatar = upload(4, $confu['adirectory'], $confu['atypefile'], '104857600', $anetwork, '1600', '1600', $user_id, $uphoto);
					$afile = $confu['adirectory'].'/'.$uavatar;
					if (file_exists($afile)) {
						list($awidth) = getimagesize($afile);
						if ($awidth > $confu['awidth']) create_img_gd($afile, $afile, $confu['awidth']);
						$db->sql_query( "UPDATE ".$prefix."_users SET user_avatar = '".$uavatar."' WHERE user_id = '".$user_id."'");
					}
				}
				login_report(0, 1, $user_name, '');
				referer('index.php?name='.$conf['name'].'&op=profil');
			}
		} else {
			head($conf['defis'].' '._ACCOUNT);
			echo tpl_eval('title', _ERRORINPUT).tpl_warn('warn', _ERRORSESS, '?name='.$conf['name'], 15, 'warn');
			foot();
		}
	} else {
		header('Location: index.php?name='.$conf['name']);
	}
}

function activate() {
	global $db, $prefix, $conf;
	$uname = htmlspecialchars(substr(urldecode($_GET['user']), 0, 25));
	$cnum = htmlspecialchars(substr($_GET['num'], 0, 40));
	$past = time() - 86400;
	$db->sql_query("DELETE FROM ".$prefix."_users_temp WHERE time < '".$past."'");
	$result = $db->sql_query("SELECT user_name, user_email, user_password, user_regdate, check_num FROM ".$prefix."_users_temp WHERE user_name = '".$uname."' AND check_num = '".$cnum."'");
	head($conf['defis']." "._ACTIVATIONSUB);
	if ($db->sql_numrows($result) == 1) {
		list($user_name, $user_email, $user_password, $user_regdate, $check_num) = $db->sql_fetchrow($result);
		if ($cnum == $check_num) {
			$uip = getip();
			$uagent = getagent();
			$db->sql_query("INSERT INTO ".$prefix."_users (user_id, user_name, user_email, user_avatar, user_regdate, user_password, user_lang, user_last_ip, user_agent) VALUES (NULL, '".$user_name."', '".$user_email."', 'default/00.gif', '".$user_regdate."', '".md5_salt($user_password)."', '".$language."', '".$uip."', '".$uagent."')");
			$db->sql_query("DELETE FROM ".$prefix."_users_temp WHERE user_name = '".$user_name."' AND check_num = '".$check_num."'");
			$db->sql_query("DELETE FROM ".$prefix."_session WHERE uname = '".$uip."' AND guest = '0'");
			if ($conf['forum']) new_user($user_name, $user_password, $user_email);
			echo tpl_eval('title', _ACTIVATIONYES).tpl_warn('warn', _ACTMSG, '?name='.$conf['name'], 15, 'info');
		} else {
			echo tpl_eval('title', _ACTIVATIONERROR).tpl_warn('warn', _ACTERROR1, '?name='.$conf['name'], 15, 'warn');
		}
	} else {
		echo tpl_eval('title', _ACTIVATIONERROR).tpl_warn('warn', _ACTERROR2, '?name='.$conf['name'], 15, 'warn');
	}
	foot();
}

function view() {
	global $prefix, $db, $conf, $confu, $confpr, $admin_file;
	if ($confu['prof'] != 1 || ($confu['prof'] == 1 && is_user()) || is_admin()) {
		$uname = isset($_GET['uname']) ? htmlspecialchars(substr(urldecode($_GET['uname']), 0, 25)) : '';
		if ($uname) {
			$where = "BINARY user_name = '".$uname."'";
		} else {
			$get_id = getVar('get', 'id', 'num');
			$where = "user_id = '".$get_id."'";
		}
		$result = $db->sql_query("SELECT u.user_id, u.user_name, u.user_rank, u.user_email, u.user_website, u.user_avatar, u.user_regdate, u.user_occ, u.user_from, u.user_interests, u.user_sig, u.user_viewemail, u.user_lastvisit, u.user_lang, u.user_points, u.user_last_ip, u.user_warnings, u.user_birthday, u.user_gender, u.user_votes, u.user_totalvotes, u.user_field, u.user_agent, g.name, g.rank, g.color FROM ".$prefix."_users AS u LEFT JOIN ".$prefix."_groups AS g ON (g.id = u.user_group) WHERE ".$where);
		if ($db->sql_numrows($result) > 0) {
			list($user_id, $user_name, $user_rank, $user_email, $user_website, $user_avatar, $user_regdate, $user_occ, $user_from, $user_interests, $user_sig, $user_viewemail, $user_lastvisit, $user_lang, $user_points, $user_last_ip, $user_warnings, $user_birthday, $user_gender, $user_votes, $user_totalvotes, $user_field, $user_agent, $gname, $grank, $gcolor) = $db->sql_fetchrow($result);
			head($conf['defis']." "._PERSONALINFO." ".$conf['defis']." ".$user_name);
			if (is_admin()) {
				$id = array(_ID, $user_id);
				$regdate = array(_REG, format_time($user_regdate, _TIMESTRING));
				$lastvisit = array(_LAST_VISIT, format_time($user_lastvisit, _TIMESTRING));
				$ip = array(_IP, user_geo_ip($user_last_ip, 4));
				$agent = array(_BROWSER, $user_agent);
			} else {
				$id = array(_ID, _HIDE);
				$regdate = array(_REG, format_time($user_regdate));
				$lastvisit = array(_LAST_VISIT, format_time($user_lastvisit));
				$ip = array(_COUNTRY, user_geo_ip($user_last_ip, 2));
				$agent = array(_BROWSER, _HIDE);
			}
			$name = array(_NICKNAME, $user_name);
			$urank = ($user_rank) ? array(_URANK, $user_rank) : array(_URANK, "");
			$mail = ((is_admin() || $user_viewemail) && $user_email) ? array(_EMAIL, anti_spam($user_email)) : array(_EMAIL, _HIDE);
			$site = ($user_website) ? ((is_admin() || is_user()) ? array(_SITEURL, domain($user_website)) : array(_SITEURL, _HIDE)) : array(_SITEURL, _NO_INFO);
			$avatar = ($user_avatar && file_exists($confu['adirectory']."/".$user_avatar)) ? $confu['adirectory']."/".$user_avatar : $confu['adirectory']."/default/00.gif";
			$occup = ($user_occ) ? array(_OCCUPATION, $user_occ) : array(_OCCUPATION, _NO_INFO);
			$from = ($user_from) ? array(_LOCALITYLANG, $user_from) : array(_LOCALITYLANG, _NO_INFO);
			$inter = ($user_interests) ? array(_INTERESTS, $user_interests) : array(_INTERESTS, _NO_INFO);
			$sign = ((is_admin() || is_user()) && $user_sig) ? "<hr>".bb_decode($user_sig, $conf['name']) : "";
			$lang = ($user_lang) ? array(_LANGUAGE, deflang($user_lang)) : array(_LANGUAGE, deflang($conf['language']));
			$points = ($confu['point'] && $user_points) ? array(_POINTS, $user_points) : array(_POINTS, _NO_INFO);
			$warn = array(_UWARNS, warnings($user_warnings));
			if ($user_birthday) {
				preg_match("#([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})#", $user_birthday, $datetime);
				$birth = array(_BIRTHDAY, $datetime[3].".".$datetime[2].".".$datetime[1]);
			} else {
				$birth = array(_BIRTHDAY, _NO_INFO);
			}
			$gender = array(_GENDER, gender($user_gender));
			$rating = array(_RATING, ajax_rating(1, $user_id, $conf['name'], $user_votes, $user_totalvotes, "", 1));
			$field = ($user_field) ? fields_out($user_field, $conf['name']) : "";
			$sgroup = ($gname) ? array(_SPEC_GROUP, "<span style=\"color: ".$gcolor."\">".$gname."</span>") : array(_SPEC_GROUP, _NO);
			if ($confu['point'] && $user_points) {
				$result = $db->sql_query("SELECT name, rank, color FROM ".$prefix."_groups WHERE points <= '".intval($user_points)."' AND extra != '1' ORDER BY points ASC");
				$group = array();
				while(list($guname, $gurank, $gcolor) = $db->sql_fetchrow($result)) {
					$group[] = "<span style=\"color: ".$gcolor."\">".$guname."</span>";
					$rgroup[] = $guname;
					$uranks = $gurank;
				}
				$group = (is_array($group)) ? implode(", ", $group) : _NO_INFO;
				$groups = array(_USER_GROUPS, $group);
				$grank = ($grank) ? $grank : $uranks;
			} else {
				$groups = array(_USER_GROUPS, _NO);
			}
			$trank = ($gname) ? _GROUP.": ".$gname : ((is_array($rgroup)) ? _USER_GROUPS.": ".implode(", ", $rgroup) : _RANK);
			$rank = ($grank && file_exists(img_find("ranks/".$grank))) ? array(_RANK, "<img src=\"".img_find("ranks/".$grank)."\" alt=\"".$trank."\" title=\"".$trank."\">") : array("", "");
			$admin = (is_admin()) ? add_menu("<a href=\"".$admin_file.".php?op=users_add&amp;id=".$user_id."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=security_block&amp;new_ip=".$user_last_ip."\" OnClick=\"return DelCheck(this, '"._BANIPSENDER." &quot;".$user_last_ip."&quot;?');\" title=\""._BANIPSENDER."\">"._BANIPSENDER."</a>||<a href=\"".$admin_file.".php?op=users_del&amp;id=".$user_id."\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$user_name."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>") : "";
			$privat = ($confpr['act'] && $user_name) ? "<a href=\"index.php?name=account&amp;op=privat&amp;uname=".urlencode($user_name)."\" title=\""._SENDMES."\" class=\"sl_but_green\">"._MESSAGE."</a>" : "";
			$profil = (is_user() && $uname == $user_name) ? "<a href=\"index.php?name=account\" title=\""._ACCOUNT."\" class=\"sl_but\">"._ACCOUNT."</a>" : "";
			$goback = "<span OnClick=\"javascript:history.go(-1)\" title=\""._BACK."\" class=\"sl_but_back\">"._BACK."</span>";
			$title[] = _COMMENTS;
			$text[] = last($user_id, "comm");
			if (is_active('faq')) {
				$title[] = _FAQ;
				$text[] = last($user_id, "faq");
			}
			if (is_active('files')) {
				$title[] = _FILES;
				$text[] = last($user_id, "files");
			}
			if (is_active('forum')) {
				$title[] = _FORUM;
				$text[] = last($user_id, "forum");
			}
			if (is_active('jokes')) {
				$title[] = _JOKES;
				$text[] = last($user_id, "jokes");
			}
			if (is_active('links')) {
				$title[] = _LINKS;
				$text[] = last($user_id, "links");
			}
			if (is_active('media')) {
				$title[] = _MEDIA;
				$text[] = last($user_id, "media");
			}
			if (is_active('news')) {
				$title[] = _NEWS;
				$text[] = last($user_id, "news");
			}
			if (is_active('pages')) {
				$title[] = _PAGES;
				$text[] = last($user_id, "pages");
			}
			$tabs = navi_tabs(0, "tab", $title, $text);
			echo tpl_eval("account-view", $id[0], $id[1], $name[0], $name[1], $urank[0], $urank[1], $mail[0], $mail[1], $site[0], $site[1], $avatar, $regdate[0], $regdate[1], $occup[0], $occup[1], $from[0], $from[1], $inter[0], $inter[1], $sign, $lastvisit[0], $lastvisit[1], $lang[0], $lang[1], $points[0], $points[1], $ip[0], $ip[1], $warn[0], $warn[1], $birth[0], $birth[1], $gender[0], $gender[1], $rating[0], $rating[1], $field, $agent[0], $agent[1], $sgroup[0], $sgroup[1], $groups[0], $groups[1], $rank[0], $rank[1], $admin, $privat, $profil, $goback, $tabs, _PERSONALINFO);
			foot();
		} else {
			head($conf['defis']." "._PERSONALINFO);
			echo tpl_warn("warn", _USERNOEXIST, "", 3, "info");
			foot();
		}
	} else {
		head($conf['defis']." "._PERSONALINFO);
		echo tpl_warn("warn", _MODULEUSERS, "", 15, "info");
		foot();
	}
}

function profil() {
	global $user, $conf, $confrs;
	if (is_user()) {
		head($conf['defis']." "._THISISYOURPAGE);
		$cont = tpl_eval("title", _THISISYOURPAGE);
		$cont .= navi();
		$title[] = _COMMENTS;
		$text[] = last($user[0], "comm");
		if (is_active('faq')) {
			$title[] = _FAQ;
			$text[] = last($user[0], "faq");
		}
		if (is_active('files')) {
			$title[] = _FILES;
			$text[] = last($user[0], "files");
		}
		if (is_active('forum')) {
			$title[] = _FORUM;
			$text[] = last($user[0], "forum");
		}
		if (is_active('jokes')) {
			$title[] = _JOKES;
			$text[] = last($user[0], "jokes");
		}
		if (is_active('links')) {
			$title[] = _LINKS;
			$text[] = last($user[0], "links");
		}
		if (is_active('media')) {
			$title[] = _MEDIA;
			$text[] = last($user[0], "media");
		}
		if (is_active('news')) {
			$title[] = _NEWS;
			$text[] = last($user[0], "news");
		}
		if (is_active('pages')) {
			$title[] = _PAGES;
			$text[] = last($user[0], "pages");
		}
		if ($confrs['use'] == 1) {
			$url = (isset($_POST['url'])) ? url_filter($_POST['url']) : "";
			$link = ($url) ? $url : "http://";
			$title[] = _RSS;
			$text[] = "<form action=\"index.php?name=".$conf['name']."\" method=\"post\"><table class=\"sl_table_form\"><tr><td>"._SELECTASITE.":</td><td><select name=\"url\" class=\"sl_field ".$conf['style']."\">".rss_select()."</select></td><td><input type=\"submit\" value=\""._OK."\" class=\"sl_but_blue\"></td></tr></table></form>"
			."<form action=\"index.php?name=".$conf['name']."\" method=\"post\"><table class=\"sl_table_form\"><tr><td>"._ORTYPEURL.":</td><td><input type=\"url\" name=\"url\" value=\"".$link."\" maxlength=\"200\" class=\"sl_field ".$conf['style']."\" placeholder=\""._ORTYPEURL."\"></td><td><input type=\"submit\" value=\""._OK."\" class=\"sl_but_blue\"></td></tr></table></form>"
			.rss_read($url, "");
		}
		$cont .= navi_tabs(0, "tab", $title, $text);
		echo $cont;
		foot();
	} else {
		account();
	}
}

function last($uid, $modul) {
	global $prefix, $db, $user, $conf;
	$user_id = intval($uid);
	$num = user_news($user[3], 25);
	$cont = "";
	if ($modul == "comm") {
		$result = $db->sql_query("SELECT id, cid, modul, date, comment FROM ".$prefix."_comment WHERE uid = '".$user_id."' AND status != '0' ORDER BY id DESC LIMIT 0,".$num);
		if ($db->sql_numrows($result) > 0) {
			$cont .= "<table class=\"sl_table_amount\">";
			while(list($id, $cid, $modul, $date, $comment) = $db->sql_fetchrow($result)) {
				$comment = cutstr(str_replace(array(_QUOTE, _CODE), "", text_filter(bb_decode($comment, $conf['name']))), 70);
				$cont .= "<tr><td style=\"width: 15%\"><span class=\"sl_date\" title=\""._CHNGSTORY.": ".format_time($date, _TIMESTRING)."\">".format_time($date)."</span></td><td><a href=\"index.php?name=".$modul."&amp;op=view&amp;id=".$cid."#".$id."\" title=\"".$comment."\" class=\"sl_last\">".$comment."</a></td></tr>";
			}
			$cont .= "</table>";
		} else {
			$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
		}
	}
	if ($modul == "faq") {
		$result = $db->sql_query("SELECT fid, title, time FROM ".$prefix."_faq WHERE uid = '".$user_id."' AND time <= now() AND status != '0' ORDER BY fid DESC LIMIT 0,".$num);
		if ($db->sql_numrows($result) > 0) {
			$cont .= "<table class=\"sl_table_amount\">";
			while(list($id, $title, $time) = $db->sql_fetchrow($result)) $cont .= "<tr><td style=\"width: 15%\"><span class=\"sl_date\" title=\""._CHNGSTORY.": ".format_time($time, _TIMESTRING)."\">".format_time($time)."</span></td><td><a href=\"index.php?name=faq&amp;op=view&amp;id=".$id."\" title=\"".$title."\" class=\"sl_last\">".$title."</a></td></tr>";
			$cont .= "</table>";
		} else {
			$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
		}
	}
	if ($modul == "files") {
		$result = $db->sql_query("SELECT lid, title, date FROM ".$prefix."_files WHERE uid = '".$user_id."' AND date <= now() AND status != '0' ORDER BY lid DESC LIMIT 0,".$num);
		if ($db->sql_numrows($result) > 0) {
			$cont .= "<table class=\"sl_table_amount\">";
			while(list($id, $title, $time) = $db->sql_fetchrow($result)) $cont .= "<tr><td style=\"width: 15%\"><span class=\"sl_date\" title=\""._CHNGSTORY.": ".format_time($time, _TIMESTRING)."\">".format_time($time)."</span></td><td><a href=\"index.php?name=files&amp;op=view&amp;id=".$id."\" title=\"".$title."\" class=\"sl_last\">".$title."</a></td></tr>";
			$cont .= "</table>";
		} else {
			$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
		}
	}
	if ($modul == "forum") {
		$result = $db->sql_query("SELECT id, title, time FROM ".$prefix."_forum WHERE uid = '".$user_id."' AND pid = '0' AND time <= now() AND status > '1' ORDER BY id DESC LIMIT 0,".$num);
		if ($db->sql_numrows($result) > 0) {
			$cont .= "<table class=\"sl_table_amount\">";
			while(list($id, $title, $time) = $db->sql_fetchrow($result)) $cont .= "<tr><td style=\"width: 15%\"><span class=\"sl_date\" title=\""._CHNGSTORY.": ".format_time($time, _TIMESTRING)."\">".format_time($time)."</span></td><td><a href=\"index.php?name=forum&amp;op=view&amp;id=".$id."\" title=\"".$title."\" class=\"sl_last\">".$title."</a></td></tr>";
			$cont .= "</table>";
		} else {
			$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
		}
	}
	if ($modul == "jokes") {
		$result = $db->sql_query("SELECT jokeid, title, date FROM ".$prefix."_jokes WHERE uid = '".$user_id."' AND date <= now() AND status != '0' ORDER BY jokeid DESC LIMIT 0,".$num);
		if ($db->sql_numrows($result) > 0) {
			$cont .= "<table class=\"sl_table_amount\">";
			while(list($id, $title, $time) = $db->sql_fetchrow($result)) $cont .= "<tr><td style=\"width: 15%\"><span class=\"sl_date\" title=\""._CHNGSTORY.": ".format_time($time, _TIMESTRING)."\">".format_time($time)."</span></td><td><a href=\"index.php?name=jokes#".$id."\" title=\"".$title."\" class=\"sl_last\">".$title."</a></td></tr>";
			$cont .= "</table>";
		} else {
			$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
		}
	}
	if ($modul == "links") {
		$result = $db->sql_query("SELECT lid, title, date FROM ".$prefix."_links WHERE uid = '".$user_id."' AND date <= now() AND status != '0' ORDER BY lid DESC LIMIT 0,".$num);
		if ($db->sql_numrows($result) > 0) {
			$cont .= "<table class=\"sl_table_amount\">";
			while(list($id, $title, $time) = $db->sql_fetchrow($result)) $cont .= "<tr><td style=\"width: 15%\"><span class=\"sl_date\" title=\""._CHNGSTORY.": ".format_time($time, _TIMESTRING)."\">".format_time($time)."</span></td><td><a href=\"index.php?name=links&amp;op=view&amp;id=".$id."\" title=\"".$title."\" class=\"sl_last\">".$title."</a></td></tr>";
			$cont .= "</table>";
		} else {
			$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
		}
	}
	if ($modul == "media") {
		$result = $db->sql_query("SELECT id, title, date FROM ".$prefix."_media WHERE uid = '".$user_id."' AND date <= now() AND status != '0' ORDER BY id DESC LIMIT 0,".$num);
		if ($db->sql_numrows($result) > 0) {
			$cont .= "<table class=\"sl_table_amount\">";
			while(list($id, $title, $time) = $db->sql_fetchrow($result)) $cont .= "<tr><td style=\"width: 15%\"><span class=\"sl_date\" title=\""._CHNGSTORY.": ".format_time($time, _TIMESTRING)."\">".format_time($time)."</span></td><td><a href=\"index.php?name=media&amp;op=view&amp;id=".$id."\" title=\"".$title."\" class=\"sl_last\">".$title."</a></td></tr>";
			$cont .= "</table>";
		} else {
			$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
		}
	}
	if ($modul == "news") {
		$result = $db->sql_query("SELECT sid, title, time FROM ".$prefix."_news WHERE uid = '".$user_id."' AND time <= now() AND status != '0' ORDER BY sid DESC LIMIT 0,".$num);
		if ($db->sql_numrows($result) > 0) {
			$cont .= "<table class=\"sl_table_amount\">";
			while(list($id, $title, $time) = $db->sql_fetchrow($result)) $cont .= "<tr><td style=\"width: 15%\"><span class=\"sl_date\" title=\""._CHNGSTORY.": ".format_time($time, _TIMESTRING)."\">".format_time($time)."</span></td><td><a href=\"index.php?name=news&amp;op=view&amp;id=".$id."\" title=\"".$title."\" class=\"sl_last\">".$title."</a></td></tr>";
			$cont .= "</table>";
		} else {
			$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
		}
	}
	if ($modul == "pages") {
		$result = $db->sql_query("SELECT pid, title, time FROM ".$prefix."_pages WHERE uid = '".$user_id."' AND time <= now() AND status != '0' ORDER BY pid DESC LIMIT 0,".$num);
		if ($db->sql_numrows($result) > 0) {
			$cont .= "<table class=\"sl_table_amount\">";
			while(list($id, $title, $time) = $db->sql_fetchrow($result)) $cont .= "<tr><td style=\"width: 15%\"><span class=\"sl_date\" title=\""._CHNGSTORY.": ".format_time($time, _TIMESTRING)."\">".format_time($time)."</span></td><td><a href=\"index.php?name=pages&amp;op=view&amp;id=".$id."\" title=\"".$title."\" class=\"sl_last\">".$title."</a></td></tr>";
			$cont .= "</table>";
		} else {
			$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
		}
	}
	return $cont;
}

function privat() {
	global $conf, $confpr;
	if (is_user() && $confpr['act']) {
		#$typ = (isset($_GET['uname'])) ? 3 : 0;
		head($conf['defis']." "._PRIVAT);
		$title = array("<span OnClick=\"AjaxLoad('GET', '0', 'prmessin', 'go=1&amp;op=prmess&amp;typ=1', ''); return false;\">"._PRIN."</span>", "<span OnClick=\"AjaxLoad('GET', '0', 'prmessou', 'go=1&amp;op=prmess&amp;typ=2', ''); return false;\">"._PROUT."</span>", "<span OnClick=\"AjaxLoad('GET', '0', 'prmesssa', 'go=1&amp;op=prmess&amp;typ=3', ''); return false;\">"._PRSAVE."</span>", _SEND);
		$text = array("<div id=\"repprmessin\">".prmess(1, 0, 0, 1)."</div>", "<div id=\"repprmessou\">".prmess(1, 0, 0, 2)."</div>", "<div id=\"repprmesssa\">".prmess(1, 0, 0, 3)."</div>", "<div id=\"repprmessfo\">".prmess(1, 0, 0, 4)."</div>");
		$cont = tpl_eval("title", _PRIVAT).navi().navi_tabs(0, "tab", $title, $text);
		echo $cont;
		foot();
	} else {
		account();
	}
}

function favorites() {
	global $conf, $conffav;
	if (is_user() && $conffav['favact']) {
		head($conf['defis']." "._FAVORITES);
		echo tpl_eval("title", _FAVORITES).navi().tpl_eval("open")."<div id=\"repfavorliste\">".favorliste(1)."</div>".tpl_eval("close");
		foot();
	} else {
		account();
	}
}

function passlost() {
	global $conf, $stop;
	$code = (isset($_GET['code'])) ? substr($_GET['code'], 0, 10) : false;
	$email = (isset($_GET['email'])) ? $_GET['email'] : false;
	if ($email) checkemail($email);
	if (!is_user()) {
		head($conf['defis']." "._PASSWORDLOST);
		$cont = tpl_eval("title", _PASSWORDLOST);
		$info = ($email) ? _PASSLOSP : _PASSLOSC;
		$send = ($email) ? _SENDPASSWORD : _SEND;
		if ($stop) $cont .= tpl_warn("warn", $stop, "", "", "warn");
		$cont .= tpl_warn("warn", $info, "", "", "info");
		$cont .= tpl_eval("open");
		$cont .= "<form action=\"index.php?name=".$conf['name']."\" method=\"post\">"
		."<table class=\"sl_table_form\">"
		."<tr><td>"._EMAIL.":</td><td><input type=\"text\" name=\"email\" value=\"".$email."\" maxlength=\"255\" class=\"sl_field ".$conf['style']."\" placeholder=\""._EMAIL."\" required></td></tr>";
		if ($email) $cont .= "<tr><td>"._CONFIRMATIONCODE.":</td><td><input type=\"text\" name=\"code\" value=\"".$code."\" maxlength=\"10\" class=\"sl_field ".$conf['style']."\" placeholder=\""._CONFIRMATIONCODE."\" required></td></tr>";
		$cont .= "<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"passmail\"><input type=\"submit\" value=\"".$send."\" class=\"sl_but_blue\"></td></tr>"
		."<tr><td colspan=\"2\" class=\"sl_center\"><a href=\"index.php?name=".$conf['name']."\" title=\""._USERLOGIN."\" class=\"sl_but_foot\">"._USERLOGIN."</a><a href=\"index.php?name=".$conf['name']."&amp;op=newuser\" title=\""._REGNEWUSER."\" class=\"sl_but_foot\">"._REGNEWUSER."</a></td></tr></table></form>";
		$cont .= tpl_eval("close");
		echo $cont;
		foot();
	} elseif (is_user()) {
		profil();
	}
}

function passmail() {
	global $prefix, $db, $conf, $confu, $stop;
	$email = $_POST['email'];
	$code = (isset($_POST['code'])) ? substr($_POST['code'], 0, 10) : false;
	checkemail($email);
	if (!$stop) {
		$result = $db->sql_query("SELECT user_name, user_email, user_password, user_network FROM ".$prefix."_users WHERE user_email = '".$email."'");
		if ($db->sql_numrows($result) == 0) {
			$stop = _NOUSERINFO;
		} else {
			list($user_name, $user_email, $user_password, $network) = $db->sql_fetchrow($result);
			if (!empty($network)) $stop = _NETWORKPASS;
		}
	}
	if (!$stop) {
		$subpass = substr(md5($user_password), 0, 10);
		if ($code && $subpass == $code) {
			$newpass = gen_pass($confu['minpass']);
			$cryptpass = md5_salt($newpass);
			$db->sql_query("UPDATE ".$prefix."_users SET user_password = '".$cryptpass."' WHERE user_email = '".$email."'");
			if ($conf['forum']) new_pass($user_name, $newpass, $user_email);
			$link = "<a href=\"".$conf['homeurl']."/index.php?name=".$conf['name']."\">".$conf['homeurl']."/index.php?name=".$conf['name']."</a>";
			$subject = $conf['sitename']." - "._USERPASSWORD." ".$user_name;
			$message = str_replace("[text]", sprintf(_PASSSEND, $user_name, $conf['sitename'], $user_name, $newpass, $link), $conf['mtemp']);
			mail_send($user_email, $conf['adminmail'], $subject, $message, 0, 3);
			head($conf['defis']." "._PASSWORDLOST);
			echo tpl_eval("title", _PASSWORDLOST).tpl_warn("warn", _USERPASSWORD." ".$user_name." "._MAILED, "?name=".$conf['name'], 10, "info");
			foot();
		} else {
			$link = "<a href=\"".$conf['homeurl']."/index.php?name=".$conf['name']."&amp;op=passlost&amp;code=".$subpass."&amp;email=".$email."\">".$conf['homeurl']."/index.php?name=".$conf['name']."&amp;op=passlost&amp;code=".$subpass."&amp;email=".$email."</a>";
			$subject = $conf['sitename']." - "._CODEFOR." ".$user_name;
			$message = str_replace("[text]", sprintf(_PASSCSEND, $user_name, $conf['sitename'], $subpass, $link)."<br><br>"._IFYOUDIDNOTASK, $conf['mtemp']);
			mail_send($user_email, $conf['adminmail'], $subject, $message, 0, 3);
			header("Location: index.php?name=".$conf['name']."&op=passlost&email=".$email);
		}
	} else {
		passlost();
	}
}

function login() {
	global $prefix, $db, $conf, $stop;
	if (extension_loaded("gd") && $_POST['check'] != $_SESSION['captcha'] && ($conf['gfx_chk'] == 2 || $conf['gfx_chk'] == 4 || $conf['gfx_chk'] == 5 || $conf['gfx_chk'] == 7)) $stop[] = _SECCODEINCOR;
	unset($_SESSION['captcha']);
	$uname = htmlspecialchars(trim(substr($_POST['user_name'], 0, 25)));
	$upass = htmlspecialchars(trim(substr($_POST['user_password'], 0, 25)));
	if (!$uname || !$upass) $stop[] = _LOGININCOR;
	$result = $db->sql_query("SELECT user_id, user_name, user_email, user_password, user_storynum, user_blockon, user_theme FROM ".$prefix."_users WHERE user_name = '".$uname."' AND user_password = '".md5_salt($upass)."' AND user_network = ''");
	if ($db->sql_numrows($result) != 1) $stop[] = _LOGININCOR;
	list($user_id, $user_name, $user_email, $user_password, $user_storynum, $user_blockon, $user_theme) = $db->sql_fetchrow($result);
	if (!$user_id || $user_name != $uname || $user_password != md5_salt($upass)) $stop[] = _LOGININCOR;
	if (!$stop) {
		setCookies($user_id, $user_name, $user_password, $user_storynum, $user_blockon, $user_theme);
		$uip = getip();
		$uagent = getagent();
		$db->sql_query("DELETE FROM ".$prefix."_session WHERE uname = '".$uip."' AND guest = '0'");
		$db->sql_query("UPDATE ".$prefix."_users SET user_last_ip = '".$uip."', user_lastvisit = now(), user_agent = '".$uagent."' WHERE user_id = '".$user_id."'");
		login_report(0, 1, $uname, "");
		if ($conf['forum']) {
			new_user($user_name, $upass, $user_email);
			log_in($uname, $upass);
		}
		referer("index.php?name=".$conf['name']."&op=profil");
	} else {
		login_report(0, 0, $uname, $upass);
		if ($conf['forum']) check_user($uname, $upass);
		account();
	}
}

function logout() {
	global $prefix, $db, $user, $conf;
	$user_name = htmlspecialchars(substr($user[1], 0, 25));
	setcookie($conf['user_c'], false);
	$db->sql_query("DELETE FROM ".$prefix."_session WHERE uname = '".$user_name."' AND guest = '2'");
	if ($conf['forum']) log_out();
	unset($user);
	referer("index.php");
}

function edithome() {
	global $prefix, $db, $user, $conf, $confu, $confn, $confpr, $stop;
	if (is_user()) {
		head($conf['defis']." "._CHANGE);
		$userinfo = getusrinfo();
		$userinfo['user_theme'] = (!$userinfo['user_theme']) ? $conf['theme'] : $userinfo['user_theme'];
		$cont = ($stop) ? tpl_warn("warn", $stop, "", "", "warn") : "";
		$change = "<form name=\"post\" action=\"index.php?name=".$conf['name']."\" method=\"post\"><table class=\"sl_table_form\">"
		."<tr><td>"._IP.":</td><td>".$userinfo['user_last_ip']."</td></tr>"
		."<tr><td>"._REG.":</td><td>".format_time($userinfo['user_regdate'])."</td></tr>";
		if ($confu['point']) $change .= "<tr><td>"._POINTS.":</td><td>".$userinfo['user_points']."</td></tr>";
		$change .= "<tr><td>"._YOURNAME.":</td><td>".$userinfo['user_name']."</td></tr>"
		."<tr><td>"._BIRTHDAY.":</td><td>".datetime(2, "user_birthday", $userinfo['user_birthday'], 10, $conf['style'])."</td></tr>"
		."<tr><td>"._GENDER.":</td><td>".get_gender("user_gender", $userinfo['user_gender'], $conf['style'])."</td></tr>"
		."<tr><td>"._YOUREMAIL.":</td><td><input type=\"email\" name=\"user_email\" value=\"".$userinfo['user_email']."\" maxlength=\"60\" class=\"sl_field ".$conf['style']."\" placeholder=\""._YOUREMAIL."\" required></td></tr>"
		."<tr><td>"._SITEURL.":</td><td><input type=\"url\" name=\"user_website\" value=\"".$userinfo['user_website']."\" maxlength=\"100\" class=\"sl_field ".$conf['style']."\" placeholder=\""._SITEURL."\"></td></tr>"
		."<tr><td>"._OCCUPATION.":</td><td><input type=\"text\" name=\"user_occ\" value=\"".$userinfo['user_occ']."\" maxlength=\"100\" class=\"sl_field ".$conf['style']."\" placeholder=\""._OCCUPATION."\"></td></tr>"
		."<tr><td>"._LOCALITYLANG.":</td><td><input type=\"text\" name=\"user_from\" value=\"".$userinfo['user_from']."\" maxlength=\"100\" class=\"sl_field ".$conf['style']."\" placeholder=\""._LOCALITYLANG."\"></td></tr>"
		."<tr><td>"._INTERESTS.":</td><td><input type=\"text\" name=\"user_interests\" value=\"".$userinfo['user_interests']."\" maxlength=\"150\" class=\"sl_field ".$conf['style']."\" placeholder=\""._INTERESTS."\"></td></tr>"
		."<tr><td>"._SIGNATURE.":<div class=\"sl_small\">"._SIGNATURE_TEXT."</div></td><td>".textarea("1", "comment", $userinfo['user_sig'], $conf['name'], "5", _SIGNATURE, "0")."</td></tr>"
		.fields_in($userinfo['user_field'], $conf['name']);
		if ($confu['news'] == 1) {
			$change .= "<tr><td>"._C_12.":</td><td><select name=\"user_storynum\" class=\"sl_field ".$conf['style']."\">";
			$xusnum = 3;
			while ($xusnum <= 20) {
				$sel = ($xusnum == $userinfo['user_storynum']) ? " selected" : "";
				$change .= "<option value=\"".$xusnum."\"".$sel.">".$xusnum."</option>";
				$xusnum++;
			}
			$change .= "</select></td></tr>";
		} else {
			$change .= "<input type=\"hidden\" name=\"user_storynum\" value=\"".$confn['num']."\">";
		}
		$change .= "<tr><td>"._RNEWSLETTER."</td><td>".radio_form($userinfo['user_newsletter'], "user_newsletter")."</td></tr>";
		if (is_active('forum')) $change .= "<tr><td>"._FSMAIL."</td><td>".radio_form($userinfo['user_fsmail'], "user_fsmail")."</td></tr>";
		if ($confpr['act']) $change .= "<tr><td>"._PSMAIL."</td><td>".radio_form($userinfo['user_psmail'], "user_psmail")."</td></tr>";
		$change .= "<tr><td>"._ALLOWUSERS."</td><td>".radio_form($userinfo['user_viewemail'], "user_viewemail")."</td></tr>"
		."<tr><td>"._ACTIVATEPERSONAL."</td><td>".radio_form($userinfo['user_blockon'], "user_blockon")."</td></tr>"
		."<tr><td>"._MENUCONF.":<div class=\"sl_small\">"._MENUINFO."</div></td><td>".textarea("2", "comment2", $userinfo['user_block'], $conf['name'], "5", _MENUCONF, "0")."</td></tr>";
		if ($confu['theme']) {
			$tcategory = "";
			$tcount = 0;
			$dh = opendir("templates");
			while (($file = readdir($dh)) !== false) {
				if (!preg_match("/\./", $file) && $file != "admin") {
					$sel = ($file == $userinfo['user_theme']) ? " selected" : "";
					$tcategory .= "<option value=\"".$file."\"".$sel.">".$file."</option>";
					$tcount++;
				}
			}
			closedir($dh);
			if ($tcount > 1) $change .= "<tr><td>"._THEME.":</td><td><select name=\"user_theme\" class=\"sl_field ".$conf['style']."\">".$tcategory."</select></td></tr>";
		}
		$change .= "<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"user_name\" value=\"".$userinfo['user_name']."\">"
		."<input type=\"hidden\" name=\"op\" value=\"savehome\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr>"
		."</table></form>";
		$asetup = "<table class=\"sl_table_form\">";
		$user_avatar = (file_exists($confu['adirectory']."/".$userinfo['user_avatar'])) ? $userinfo['user_avatar'] : "default/00.gif";
		$asetup .= "<tr><td>"._AVATAR.":<div class=\"sl_small\">".sprintf(_AVATARINFO, $confu['awidth'], $confu['aheight'], files_size($confu['amaxsize']))."</div></td><td><img src=\"".$confu['adirectory']."/".$user_avatar."\" alt=\""._AVATAR."\" title=\""._AVATAR."\" class=\"sl_avatar\"></td></tr>";
		$asetup .= "</table>";
		if ($confu['aupload']) {
			$asetup .= "<hr><form enctype=\"multipart/form-data\" action=\"index.php?name=".$conf['name']."\" method=\"post\"><table class=\"sl_table_form\">"
			."<tr><td>"._AVATAR_USER.":</td><td><input type=\"file\" name=\"userfile\" class=\"sl_field ".$conf['style']."\"></td><td><input type=\"hidden\" name=\"op\" value=\"saveavatar\"><input type=\"submit\" value=\""._UPLOAD."\" class=\"sl_but_blue\"></td></tr>"
			."</table></form>";
		}
		$a = 6;
		$i = 1;
		$tdwidth = intval(100/$a);
		$aset = "";
		$adir = $confu['adirectory']."/default";
		$dh = opendir($adir);
		while (($file = readdir($dh)) !== false) {
			if (preg_match("#(\.gif|\.png|\.jpg|\.jpeg)$#is", $file) && !preg_match("#(\b0\.gif\b|\b00\.gif\b)$#i", $file)) {
				$filename = str_replace("_", " ", preg_replace("/^(.*)\..*$/", "\\1", $file));
				if (($i - 1) % $a == 0) $aset .= "<tr>";
				$aset .= "<td style=\"width: ".$tdwidth."%;\"><a href=\"index.php?name=".$conf['name']."&amp;op=saveavatar&amp;avatar=".$file."\"><img src=\"".$adir."/".$file."\" alt=\""._AVATARSAVE." "._ID." ".$filename."\" title=\""._AVATARSAVE." "._ID." ".$filename."\" class=\"sl_avatar\"></a></td>";
				if ($i % $a == 0) $aset .= "</tr>";
				$i++;
			}
		}
		closedir($dh);
		if ($i >= 1) $asetup .= "<hr>".tpl_warn("warn", _AVATARSELECT, "", "", "info")."<table class=\"sl_table_form\">".$aset."</table>";
		$user_id = intval($user[0]);
		list($network) = $db->sql_fetchrow($db->sql_query("SELECT user_network FROM ".$prefix."_users WHERE user_id = '".$user_id."'"));
		if (empty($network)) {
			$psetup = tpl_warn("warn", _PASSTEXT, "", "", "info");
			$psetup .= "<form action=\"index.php?name=".$conf['name']."\" method=\"post\"><table class=\"sl_table_form\">"
			."<tr><td>"._PASSNEW.":</td><td><input type=\"password\" name=\"newpass\" maxlength=\"25\" class=\"sl_field ".$conf['style']."\" placeholder=\""._PASSNEW."\" required></td></tr>"
			."<tr><td>"._PASSNEW2.":</td><td><input type=\"password\" name=\"newpass2\" maxlength=\"25\" class=\"sl_field ".$conf['style']."\" placeholder=\""._PASSNEW2."\" required></td></tr>"
			."<tr><td>"._PASSOLD.":</td><td><input type=\"password\" name=\"oldpass\" maxlength=\"25\" class=\"sl_field ".$conf['style']."\" placeholder=\""._PASSOLD."\" required></td></tr>"
			."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"savepass\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr>"
			."</table></form>";
		} else {
			$psetup = tpl_warn('warn', _NETWORKPASS, '', '', 'warn');
		}
		echo tpl_eval("title", _CHANGE).navi().$cont.navi_tabs(0, "tab", array(_CHANGE, _AVATARSETUP, _PASSSETUP), array($change, $asetup, $psetup));
		foot();
	} else {
		account();
	}
}

function savehome() {
	global $prefix, $db, $user, $conf, $stop;
	$user_email = text_filter($_POST['user_email']);
	checkemail($user_email);
	if (!$stop) {
		$user_id = intval($user[0]);
		$checkn = htmlspecialchars(substr($user[1], 0, 25));
		$checkp = htmlspecialchars($user[2]);
		list($id, $name, $pass) = $db->sql_fetchrow($db->sql_query("SELECT user_id, user_name, user_password FROM ".$prefix."_users WHERE user_id = '".$user_id."'"));
		if ($id == $user_id && $name == $checkn && $pass == $checkp) {
			$user_website = url_filter($_POST['user_website']);
			$user_occ = text_filter($_POST['user_occ']);
			$user_from = text_filter($_POST['user_from']);
			$user_interests = text_filter($_POST['user_interests']);
			$user_sig = save_text($_POST['comment']);
			$user_viewemail = intval($_POST['user_viewemail']);
			$user_storynum = intval($_POST['user_storynum']);
			$user_blockon = intval($_POST['user_blockon']);
			$user_block = save_text($_POST['comment2']);
			$user_theme = text_filter($_POST['user_theme']);
			$user_newsletter = intval($_POST['user_newsletter']);
			$user_fsmail = intval($_POST['user_fsmail']);
			$user_psmail = intval($_POST['user_psmail']);
			$user_birthday = save_datetime(2, "user_birthday");
			$user_gender = intval($_POST['user_gender']);
			$user_field = fields_save($_POST['field']);
			$db->sql_query("UPDATE ".$prefix."_users SET user_email = '".$user_email."', user_website = '".$user_website."', user_viewemail = '".$user_viewemail."', user_occ = '".$user_occ."', user_from = '".$user_from."', user_interests = '".$user_interests."', user_sig = '".$user_sig."', user_storynum = '".$user_storynum."', user_blockon = '".$user_blockon."', user_block = '".$user_block."', user_theme = '".$user_theme."', user_newsletter = '".$user_newsletter."', user_fsmail = '".$user_fsmail."', user_psmail = '".$user_psmail."', user_birthday = '".$user_birthday."', user_gender = '".$user_gender."', user_field = '".$user_field."' WHERE user_id = '".$user_id."'");
			$userinfo = getusrinfo();
			setCookies($userinfo['user_id'], $userinfo['user_name'], $userinfo['user_password'], $userinfo['user_storynum'], $userinfo['user_blockon'], $userinfo['user_theme']);
			header("Location: index.php?name=".$conf['name']."&op=edithome");
		}
	} else {
		edithome();
	}
}

function saveavatar() {
	global $user, $prefix, $db, $conf, $confu, $stop;
	$avatar = (isset($_POST['avatar'])) ? $_POST['avatar'] : $_GET['avatar'];
	if (is_user()) {
		$user_id = intval($user[0]);
		if (!$avatar && $confu['aupload']) {
			$uavatar = upload(1, $confu['adirectory'], $confu['atypefile'], $confu['amaxsize'], $conf['name'], $confu['awidth'], $confu['aheight'], $user_id);
			$avatar = (!$uavatar) ? $avatar : $uavatar;
		} elseif ($avatar) {
			$avatar = (preg_match("#(\.gif|\.png|\.jpg|\.jpeg)$#is", $avatar) && !preg_match("#(\b0\.gif\b|\b00\.gif\b)$#i", $avatar) && file_exists($confu['adirectory']."/default/".$avatar)) ? "default/".$avatar : "";
		}
		if (!$stop && $avatar) {
			$avatar = text_filter($avatar);
			$db->sql_query("UPDATE ".$prefix."_users SET user_avatar = '".$avatar."' WHERE user_id = '".$user_id."'");
			header("Location: index.php?name=".$conf['name']."&op=edithome");
		} else {
			edithome();
		}
	} else {
		edithome();
	}
}

function savepass() {
	global $user, $prefix, $db, $confu, $conf, $stop;
	$newpass = (isset($_POST['newpass'])) ? $_POST['newpass'] : false;
	$newpass2 = (isset($_POST['newpass2'])) ? $_POST['newpass2'] : false;
	$oldpass = (isset($_POST['oldpass'])) ? $_POST['oldpass'] : false;
	if (is_user() && $oldpass && $newpass && $newpass2) {
		if (strlen($newpass) >= $confu['minpass']) {
			$oldpass = md5_salt($oldpass);
			$user_id = intval($user[0]);
			list($pass) = $db->sql_fetchrow($db->sql_query("SELECT user_password FROM ".$prefix."_users WHERE user_id = '".$user_id."' AND user_network = ''"));
			if (!empty($pass) && $pass == $oldpass) {
				if ($newpass == $newpass2) {
					$userinfo = getusrinfo();
					$user_email = $userinfo['user_email'];
					$user_name = $userinfo['user_name'];
					$link = "<a href=\"".$conf['homeurl']."/index.php?name=".$conf['name']."\">".$conf['homeurl']."/index.php?name=".$conf['name']."</a>";
					$subject = $conf['sitename']." - "._USERPASSWORD." ".$user_name;
					$message = str_replace("[text]", sprintf(_PASSESEND, $user_name, $conf['sitename'], $user_name, $newpass, $link), $conf['mtemp']);
					mail_send($user_email, $conf['adminmail'], $subject, $message, 0, 3);
					$newpass = md5_salt($newpass);
					$db->sql_query("UPDATE ".$prefix."_users SET user_password = '".$newpass."' WHERE user_id = '".$user_id."'");
					if ($conf['forum']) new_pass($user_name, $newpass2, $user_email);
					header("Location: index.php?name=".$conf['name']);
				} else {
					$stop[] = _ERROR_PASS;
					edithome();
				}
			} else {
				$stop[] = _ERROROLD;
				edithome();
			}
		} else {
			$stop[] = _CHARMIN.": ".$confu['minpass'];
			edithome();
		}
	} else {
		edithome();
	}
}

switch($op) {
	default:
	account();
	break;
	
	case "newuser":
	newuser();
	break;
	
	case "finnewuser":
	finnewuser();
	break;
	
	case "network":
	network();
	break;
	
	case "privat":
	privat();
	break;
	
	case "favorites":
	favorites();
	break;
	
	case "view":
	view();
	break;
	
	case "login":
	login();
	break;
	
	case "logout":
	logout();
	break;
	
	case "edithome":
	edithome();
	break;
	
	case "savehome":
	savehome();
	break;
	
	case "passlost":
	passlost();
	break;
	
	case "passmail":
	passmail();
	break;
	
	case "activate":
	activate();
	break;
	
	case "saveavatar":
	saveavatar();
	break;
	
	case "savepass":
	savepass();
	break;
}
?>