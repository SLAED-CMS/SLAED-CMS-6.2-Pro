<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined('ADMIN_FILE') || !is_admin_god()) die('Illegal file access');

function users_navi() {
	global $admin_file;
	panel();
	$psearch = getVar('post', 'search');
	$chng_user = getVar('post', 'chng_user');
	$narg = func_get_args();
	$ops = array('users_show', 'users_add', 'users_new', 'users_null', 'users_conf', 'users_info');
	$lang = array(_HOME, _ADD, _NEW_USER, _NULLPOINTS, _PREFERENCES, _INFO);
	$search = '<form method="post" action="'.$admin_file.'.php">'._SEARCH.': <select name="search">';
	$priv = array(_ID, _NICKNAME, _EMAIL, _IP, _URL);
	foreach ($priv as $key => $value) {
		$sort = $key + 1;
		$sel = ($psearch == $sort || (!$psearch && $sort == 2)) ? ' selected' : '';
		$search .= '<option value="'.$sort.'"'.$sel.'>'.$value.'</option>';
	}
	$search .= '</select> '.get_user_search('chng_user', $chng_user, '30').' <input type="hidden" name="op" value="users_show"><input type="submit" value="'._OK.'" class="sl_but_blue"></form>';
	$search = tpl_eval('searchbox', $search);
	return navi_gen(_USERS, 'users.png', $search, $ops, $lang, '', '', $narg[0], $narg[1], $narg[2], $narg[3]);
}

function users_show() {
	global $prefix, $db, $admin_file, $conf, $confu;
	$search = getVar('req', 'search');
	$chng_user = getVar('req', 'chng_user');
	head();
	$cont = users_navi(0, 0, 0, 0);
	if (getVar('get', 'send', 'num')) $cont .= tpl_warn('warn', _MAIL_SEND, '', '', 'info');
	if ($search == 1 && $chng_user) {
		$sqlstring = "user_id LIKE '%".$chng_user."%' ORDER BY user_id ASC";
	} elseif ($search == 2 && $chng_user) {
		$sqlstring = "user_name LIKE '%".$chng_user."%' ORDER BY user_name ASC";
	} elseif ($search == 3 && $chng_user) {
		$sqlstring = "user_email LIKE '%".$chng_user."%' ORDER BY user_email ASC";
	} elseif ($search == 4 && $chng_user) {
		$sqlstring = "user_last_ip LIKE '%".$chng_user."%' ORDER BY user_last_ip ASC";
	} elseif ($search == 5 && $chng_user) {
		$sqlstring = "user_website LIKE '%".$chng_user."%' ORDER BY user_website ASC";
	} elseif ($search == 6 && $chng_user) {
		$sqlstring = "user_group = ".$chng_user." ORDER BY user_id ASC";
	} elseif ($search == 7 && $chng_user) {
		$sqlstring = "user_points >= ".$chng_user." ORDER BY user_id ASC";
	} else {
		$sqlstring = "user_id ORDER BY user_id DESC";
	}
	$num = getVar('get', 'num', 'num', '1');
	$offset = ($num-1) * $confu['anum'];
	$result = $db->sql_query("SELECT u.user_id, u.user_name, u.user_email, u.user_website, u.user_regdate, u.user_lastvisit, u.user_points, u.user_last_ip, u.user_gender, u.user_agent, g.name, g.color FROM ".$prefix."_users AS u LEFT JOIN ".$prefix."_groups AS g ON (g.id = u.user_group) WHERE ".$sqlstring." LIMIT ".$offset.", ".$confu['anum']);
	if ($db->sql_numrows($result) > 0) {
		$cont .= tpl_eval("open");
		$cont .= "<table class=\"sl_table_list_sort\"><thead><tr><th>"._ID."</th><th>"._NICKNAME."</th><th>"._IP."</th><th>"._EMAIL."</th><th>"._REG."</th><th class=\"{sorter: false}\">"._FUNCTIONS."</th></tr></thead><tbody>";
		while (list($user_id, $user_name, $user_email, $user_website, $user_regdate, $user_lastvisit, $user_points, $user_last_ip, $user_gender, $user_agent, $gname, $gcolor) = $db->sql_fetchrow($result)) {
			$sgroup = ($gname) ? "<span style=\"color: ".$gcolor."\">".$gname."</span>" : _NO;
			$website = ($user_website) ? domain($user_website, 40) : _NO;
			$cont .= "<tr><td>".$user_id."</td>"
			."<td>".title_tip(_HASH.": ".md5($user_agent)."<br>"._LAST_VISIT.": ".format_time($user_lastvisit, _TIMESTRING)."<br>"._SPEC_GROUP.": ".$sgroup."<br>"._SITE.": ".$website."<br>"._GENDER.": ".gender($user_gender)."<br>"._POINTS.": ".$user_points).search_color(user_info($user_name), $chng_user)."</td>"
			."<td>".user_geo_ip($user_last_ip, 4)."</td>"
			."<td>".search_color($user_email, $chng_user)."</td>"
			."<td>".format_time($user_regdate, _TIMESTRING)."</td>"
			."<td>".add_menu("<a href=\"".$admin_file.".php?op=users_add&amp;id=".$user_id."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||<a href=\"".$admin_file.".php?op=security_block&amp;new_ip=".$user_last_ip."\" OnClick=\"return DelCheck(this, '"._BANIPSENDER." &quot;".$user_last_ip."&quot;?');\" title=\""._BANIPSENDER."\">"._BANIPSENDER."</a>||<a href=\"".$admin_file.".php?op=users_del&amp;id=".$user_id."&amp;refer=1\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$user_name."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>")."</td></tr>";
		}
		$cont .= "</tbody></table>";
		$lsear = ($search) ? "&amp;search=".$search : "";
		$lchng = ($chng_user) ? "&amp;chng_user=".$chng_user : "";
		$cont .= num_article("pagenum", "", $confu['anum'], "op=users_show".$lsear.$lchng."&amp;", "user_id", "_users", "", $sqlstring, $confu['anump']);
		$cont .= tpl_eval("close", "");
	} else {
		$cont .= tpl_warn("warn", _USERNOEXIST, "", "", "info");
	}
	echo $cont;
	foot();
}

function users_add() {
	global $prefix, $db, $admin_file, $conf, $confu, $stop;
	include('config/config_news.php');
	$id = getVar('req', 'id', 'num');
	if (is_numeric($id)) {
		$result = $db->sql_query("SELECT user_id, user_name, user_rank, user_email, user_website, user_avatar, user_regdate, user_occ, user_from, user_interests, user_sig, user_viewemail, user_password, user_storynum, user_blockon, user_block, user_theme, user_newsletter, user_lang, user_points, user_warnings, user_acess, user_group, user_birthday, user_gender, user_field FROM ".$prefix."_users WHERE user_id = '".$id."'");
		list($user_id, $user_name, $user_rank, $user_email, $user_website, $user_avatar, $user_regdate, $user_occ, $user_from, $user_interests, $user_sig, $user_viewemail, $user_password, $user_storynum, $user_blockon, $user_block, $user_theme, $user_newsletter, $user_lang, $user_points, $user_warnings, $user_acess, $user_group, $user_birthday, $user_gender, $user_field) = $db->sql_fetchrow($result);
		$user_warnings = ($user_warnings) ? explode('|', $user_warnings) : '';
	} else {
		$user_id = getVar('post', 'user_id', 'num');
		$user_name = getVar('post', 'user_name', 'name');
		$user_rank = getVar('post', 'user_rank');
		$user_email = getVar('post', 'user_email');
		$user_website = getVar('post', 'user_website', 'url', 'http://');
		$user_avatar = getVar('post', 'user_avatar');
		$user_regdate = getVar('post', 'user_regdate');
		$user_occ = getVar('post', 'user_occ');
		$user_from = getVar('post', 'user_from');
		$user_interests = getVar('post', 'user_interests');
		$user_sig = getVar('post', 'user_sig', 'text');
		$user_viewemail = getVar('post', 'user_viewemail', 'num');
		$user_password = getVar('post', 'user_password');
		$user_storynum = getVar('post', 'user_storynum', 'num');
		$user_blockon = getVar('post', 'user_blockon', 'num');
		$user_block = getVar('post', 'user_block', 'text');
		$user_theme = getVar('post', 'user_theme');
		$user_newsletter = getVar('post', 'user_newsletter', 'num');
		$user_lang = getVar('post', 'user_lang');
		$user_points = getVar('post', 'user_points');
		$user_warnings = getVar('post', 'user_warnings');
		$user_acess = getVar('post', 'user_acess', 'num');
		$user_group = getVar('post', 'user_group');
		$user_birthday = getVar('post', 'user_birthday');
		$user_gender = getVar('post', 'user_gender');
		$user_field = getVar('post', 'user_field', 'field');
	}
	head();
	$cont = users_navi(0, 1, 0, 0);
	if ($stop) $cont .= tpl_warn('warn', $stop, '', '', 'warn');
	$cont .= tpl_eval('open');
	$cont .= "<form name=\"post\" action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_form\">"
	."<tr><td>"._NICKNAME.":</td><td><input type=\"text\" name=\"user_name\" value=\"".$user_name."\" maxlength=\"25\" class=\"sl_form\" placeholder=\""._NICKNAME."\" required></td></tr>"
	."<tr><td>"._URANK.":</td><td><input type=\"text\" name=\"user_rank\" value=\"".$user_rank."\" maxlength=\"25\" class=\"sl_form\" placeholder=\""._URANK."\"></td></tr>"
	."<tr><td>"._EMAIL.":</td><td><input type=\"email\" name=\"user_email\" value=\"".$user_email."\" maxlength=\"255\" class=\"sl_form\" placeholder=\""._EMAIL."\" required></td></tr>"
	."<tr><td>"._SITEURL.":</td><td><input type=\"url\" name=\"user_website\" value=\"".$user_website."\" maxlength=\"255\" class=\"sl_form\" placeholder=\""._SITEURL."\"></td></tr>";
	if ($user_avatar) $cont .= "<tr><td>"._AVATAR.":</td><td><input type=\"text\" name=\"user_avatar\" value=\"".$user_avatar."\" maxlength=\"255\" class=\"sl_form\" placeholder=\""._AVATAR."\"></td></tr>";
	$cont .= "<tr><td>"._REG.":</td><td>".datetime(1, "user_regdate", $user_regdate, 16, "sl_form")."</td></tr>"
	."<tr><td>"._OCCUPATION.":</td><td><input type=\"text\" name=\"user_occ\" value=\"".$user_occ."\" maxlength=\"100\" class=\"sl_form\" placeholder=\""._OCCUPATION."\"></td></tr>"
	."<tr><td>"._LOCATION.":</td><td><input type=\"text\" name=\"user_from\" value=\"".$user_from."\" maxlength=\"100\" class=\"sl_form\" placeholder=\""._LOCATION."\"></td></tr>"
	."<tr><td>"._INTERESTS.":</td><td><input type=\"text\" name=\"user_interests\" value=\"".$user_interests."\" maxlength=\"150\" class=\"sl_form\" placeholder=\""._INTERESTS."\"></td></tr>"
	."<tr><td>"._SIGNATURE.":<div class=\"sl_small\">"._SIGNATURE_TEXT."</div></td><td>".textarea("1", "user_sig", $user_sig, "account", "5", _SIGNATURE, "")."</td></tr>"
	."<tr><td>"._ALLOWUSERS."</td><td>".radio_form($user_viewemail, "user_viewemail")."</td></tr>";
	if ($confu['news'] == 1) {
		$cont .= "<tr><td>"._C_12.":</td><td><select name=\"user_storynum\" class=\"sl_form\">";
		$xusnum = 3;
		while ($xusnum <= 20) {
			$sel = ($xusnum == $user_storynum) ? " selected" : "";
			$cont .= "<option value=\"".$xusnum."\"".$sel.">".$xusnum."</option>";
			$xusnum++;
		}
		$cont .= "</select></td></tr>";
	} else {
		$cont .= "<input type=\"hidden\" name=\"user_storynum\" value=\"".$confn['num']."\">";
	}
	$cont .= "<tr><td>"._ACTIVATEPERSONAL."</td><td>".radio_form($user_blockon, "user_blockon")."</td></tr>"
	."<tr><td>"._MENUCONF.":<div class=\"sl_small\">"._MENUINFO."</div></td><td>".textarea("2", "user_block", $user_block, "account", "5", _MENUCONF, "")."</td></tr>";
	if ($confu['theme']) {
		$tcategory = "";
		$tcount = 0;
		$dh = opendir("templates");
		while (($file = readdir($dh)) !== false) {
			if (!preg_match("/\./", $file) && $file != "admin") {
				$sel = ($file == $user_theme) ? " selected" : "";
				$tcategory .= "<option value=\"".$file."\"".$sel.">".$file."</option>";
				$tcount++;
			}
		}
		closedir($dh);
		if ($tcount > 1) $cont .= "<tr><td>"._THEME.":</td><td><select name=\"user_theme\" class=\"sl_form\">".$tcategory."</select></td></tr>";
	}
	$cont .= "<tr><td>"._RNEWSLETTER.":</td><td>".radio_form($user_newsletter, "user_newsletter")."</td></tr>";
	if ($conf['multilingual'] == 1) $cont .= "<tr><td>"._LANGUAGE.":</td><td><select name=\"user_lang\" class=\"sl_form\">".language($user_lang)."</select></td></tr>";
	$cont .= "<tr><td>"._POINTS.":</td><td><input type=\"number\" name=\"user_points\" value=\"".$user_points."\" class=\"sl_form\" placeholder=\""._POINTS."\"></td></tr>"
	."<tr><td colspan=\"2\">";
	$i = 0;
	while ($i < 5) {
		$a = $i + 1;
		$user_warn = empty($user_warnings[$i]) ? "" : $user_warnings[$i];
		$class = (empty($user_warn) && $i != 0) ? " class=\"sl_none\"" : "";
		$cont .= "<table id=\"warn".$i."\"".$class."><tr><td><a OnClick=\"HideShow('warn".$a."', 'slide', 'up', 500);\" title=\""._ADD."\" class=\"sl_plus\">"._UWARN." - ".$a.":</a></td><td><input type=\"text\" name=\"user_warnings[]\" value=\"".text_filter($user_warn)."\" class=\"sl_form\" placeholder=\""._UWARN." - ".$a."\"></td></tr></table>";
		$i++;
	}
	$cont .= "</td></tr>"
	."<tr><td>"._UACESS."</td><td>".radio_form($user_acess, "user_acess")."</td></tr>"
	."<tr><td>"._SPEC_GROUP.":</td><td><select name=\"user_group\" class=\"sl_form\">"
	."<option value=\"0\">"._NO."</option>";
	$result = $db->sql_query("SELECT id, name FROM ".$prefix."_groups WHERE extra = '1'");
	while (list($grid, $grname) = $db->sql_fetchrow($result)) {
		$sel = ($grid == $user_group) ? " selected" : "";
		$cont .= "<option value=\"".$grid."\"".$sel.">".$grname."</option>";
	}
	$cont .= "</select></td></tr>"
	."<tr><td>"._BIRTHDAY.":</td><td>".datetime(2, "user_birthday", $user_birthday, 10, "sl_form")."</td></tr>"
	."<tr><td>"._GENDER.":</td><td>".get_gender("user_gender", $user_gender, "sl_form")."</td></tr>";
	$check = ($_COOKIE['sl_close_9'] == "0") ? "" : " checked";
	$cont .= fields_in($user_field, "account")
	."<tr><td>"._PASSWORD.":</td><td><input type=\"password\" name=\"user_password\" value=\"\" maxlength=\"25\" class=\"sl_form\" placeholder=\""._PASSWORD."\"></td></tr>"
	."<tr><td>"._RETYPEPASSWORD.":</td><td><input type=\"password\" name=\"user_password2\" value=\"\" maxlength=\"25\" class=\"sl_form\" placeholder=\""._RETYPEPASSWORD."\"></td></tr>"
	."<tr><td>"._MAIL_SENDE."</td><td><input type=\"checkbox\" name=\"mail\" value=\"1\" OnClick=\"CloseOpen('sl_close_9', 0);\"".$check."></td></tr>"
	."<tr><td colspan=\"2\"><div id=\"sl_close_9\"><table class=\"sl_table_form\"><tr><td>"._MAIL_TEXT.":<div class=\"sl_small\">"._MAIL_PASS_INFO."</div></td><td class=\"sl_form\">".textarea("3", "mailtext", replace_break(str_replace("[text]", _FOLLOWINGMEM."\n\n"._NICKNAME.": [login]\n"._PASSWORD.": [pass]", $conf['mtemp'])), "account", "10", _MAIL_TEXT, "")."</td></tr></table></div></td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"user_id\" value=\"".$user_id."\"><input type=\"hidden\" name=\"op\" value=\"users_add_save\"><input type=\"submit\" value=\""._SAVE."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function users_add_save() {
	global $prefix, $db, $admin_file, $conf, $stop;
	$user_id = getVar('post', 'user_id', 'num');
	$user_name = getVar('post', 'user_name', 'name');
	$user_rank = getVar('post', 'user_rank');
	$user_email = getVar('post', 'user_email');
	$user_website = getVar('post', 'user_website', 'url');
	$user_avatar = getVar('post', 'user_avatar', '', 'default/00.gif');
	$user_regdate = save_datetime(1, 'user_regdate');
	$user_occ = getVar('post', 'user_occ');
	$user_from = getVar('post', 'user_from');
	$user_interests = getVar('post', 'user_interests');
	$user_sig = getVar('post', 'user_sig', 'text');
	$user_viewemail = getVar('post', 'user_viewemail', 'num');
	$user_password = getVar('post', 'user_password');
	$user_password2 = getVar('post', 'user_password2');
	$user_storynum = getVar('post', 'user_storynum', 'num');
	$user_blockon = getVar('post', 'user_blockon', 'num');
	$user_block = getVar('post', 'user_block', 'text');
	$user_theme = getVar('post', 'user_theme');
	$user_newsletter = getVar('post', 'user_newsletter', 'num');
	$user_lang = getVar('post', 'user_lang');
	$user_points = getVar('post', 'user_points', 'num');
	$user_warnings = isArray(getVar('post', 'user_warnings')) ? text_filter(implode('|', str_replace('|', '', getVar('post', 'user_warnings')))) : 0;
	$user_acess = getVar('post', 'user_acess', 'num');
	$user_group = getVar('post', 'user_group');
	$user_birthday = save_datetime(2, 'user_birthday');
	$user_gender = getVar('post', 'user_gender');
	$user_field = getVar('post', 'user_field', 'field');
	$mail = getVar('post', 'mail', 'num');
	if (!$user_id && (!$user_name || !$user_email || !$user_password || !$user_password2)) $stop[] = _ERROR_ALL;
	if ($user_name) {
		list($uid, $uname) = $db->sql_fetchrow($db->sql_query("SELECT user_id, user_name FROM ".$prefix."_users WHERE user_name = '".$user_name."'"));
		list($tuid, $tuname) = $db->sql_fetchrow($db->sql_query("SELECT user_id, user_name FROM ".$prefix."_users_temp WHERE user_name = '".$user_name."'"));
		if (($user_id != $uid && $user_name == $uname) || ($user_id != $tuid && $user_name == $tuname)) $stop[] = _USEREXIST;
		list($uid, $email) = $db->sql_fetchrow($db->sql_query("SELECT user_id, user_email FROM ".$prefix."_users WHERE user_email = '".$user_email."'"));
		list($tuid, $temail) = $db->sql_fetchrow($db->sql_query("SELECT user_id, user_email FROM ".$prefix."_users_temp WHERE user_email = '".$user_email."'"));
		if (($user_id != $uid && $user_email == $email) || ($user_id != $tuid && $user_email == $temail)) $stop[] = _ERROR_EMAIL;
	} else {
		$stop[] = _ERROR_ALL;
	}
	if (!analyze_name($user_name)) $stop[] = _ERRORINVNICK;
	checkemail($user_email);
	if ($user_password != $user_password2) $stop[] = _ERROR_PASS;
	if (!$stop) {
		if ($user_id) {
			if ($user_password && $user_password == $user_password2) {
				$saltpass = md5_salt($user_password);
				$db->sql_query("UPDATE ".$prefix."_users SET user_name = '".$user_name."', user_rank = '".$user_rank."', user_email = '".$user_email."', user_website = '".$user_website."', user_viewemail = '".$user_viewemail."', user_avatar = '".$user_avatar."', user_regdate = '".$user_regdate."', user_occ = '".$user_occ."', user_from = '".$user_from."', user_interests = '".$user_interests."', user_sig = '".$user_sig."', user_viewemail = '".$user_viewemail."', user_password = '".$saltpass."', user_storynum = '".$user_storynum."', user_blockon = '".$user_blockon."', user_block = '".$user_block."', user_theme = '".$user_theme."', user_newsletter = '".$user_newsletter."', user_lang = '".$user_lang."', user_points = '".$user_points."', user_warnings = '".$user_warnings."', user_acess = '".$user_acess."', user_group = '".$user_group."', user_birthday = '".$user_birthday."', user_gender = '".$user_gender."', user_field = '".$user_field."' WHERE user_id = '".$user_id."'");
			} else {
				$db->sql_query("UPDATE ".$prefix."_users SET user_name = '".$user_name."', user_rank = '".$user_rank."', user_email = '".$user_email."', user_website = '".$user_website."', user_viewemail = '".$user_viewemail."', user_avatar = '".$user_avatar."', user_regdate = '".$user_regdate."', user_occ = '".$user_occ."', user_from = '".$user_from."', user_interests = '".$user_interests."', user_sig = '".$user_sig."', user_viewemail = '".$user_viewemail."', user_storynum = '".$user_storynum."', user_blockon = '".$user_blockon."', user_block = '".$user_block."', user_theme = '".$user_theme."', user_newsletter = '".$user_newsletter."', user_lang = '".$user_lang."', user_points = '".$user_points."', user_warnings = '".$user_warnings."', user_acess = '".$user_acess."', user_group = '".$user_group."', user_birthday = '".$user_birthday."', user_gender = '".$user_gender."', user_field = '".$user_field."' WHERE user_id = '".$user_id."'");
			}
		} else {
			$saltpass = md5_salt($user_password);
			$db->sql_query("INSERT INTO ".$prefix."_users (user_id, user_name, user_rank, user_email, user_website, user_avatar, user_regdate, user_occ, user_from, user_interests, user_sig, user_viewemail, user_password, user_storynum, user_blockon, user_block, user_theme, user_newsletter, user_lang, user_points, user_warnings, user_acess, user_group, user_birthday, user_gender, user_field) VALUES (NULL, '".$user_name."', '".$user_rank."', '".$user_email."', '".$user_website."', '".$user_avatar."', '".$user_regdate."', '".$user_occ."', '".$user_from."', '".$user_interests."', '".$user_sig."', '".$user_viewemail."', '".$saltpass."', '".$user_storynum."', '".$user_blockon."', '".$user_block."', '".$user_theme."', '".$user_newsletter."', '".$user_lang."', '".$user_points."', '".$user_warnings."', '".$user_acess."', '".$user_group."', '".$user_birthday."', '".$user_gender."', '".$user_field."')");
		}
		if ($mail) {
			$subject = $conf['sitename'].' - '._USERPASSWORD.' '.$user_name;
			$msg = nl2br(bb_decode(str_replace('[pass]', $user_password, str_replace('[login]', $user_name, $_POST['mailtext'])), 'account'), false);
			mail_send($user_email, $conf['adminmail'], $subject, $msg, 0, 3);
			$send = '&send=1';
		}
		header('Location: '.$admin_file.'.php?op=users_show'.$send);
	} else {
		users_add();
	}
}

function users_new() {
	global $prefix, $db, $admin_file, $conf, $confu;
	head();
	$cont = users_navi(0, 2, 0, 0);
	$num = getVar('get', 'num', 'num', '1');
	$offset = ($num-1) * $confu['anum'];
	$result = $db->sql_query("SELECT user_id, user_name, user_email, user_password, user_regdate, check_num FROM ".$prefix."_users_temp WHERE user_id LIMIT ".$offset.", ".$confu['anum']);
	if ($db->sql_numrows($result) > 0) {
		$cont .= tpl_eval("open");
		$cont .= "<table class=\"sl_table_list_sort\"><thead><tr><th>"._ID."</th><th>"._NICKNAME."</th><th>"._EMAIL."</th><th>"._PASSWORD."</th><th>"._REG."</th><th class=\"{sorter: false}\">"._FUNCTIONS."</th></tr></thead><tbody>";
		while (list($user_id, $user_name, $user_email, $user_password, $user_regdate, $check_num) = $db->sql_fetchrow($result)) {
			$cont .= "<tr><td>".$user_id."</td>"
			."<td>".$user_name."</td>"
			."<td>".$user_email."</td>"
			."<td>".$user_password."</td>"
			."<td>".$user_regdate."</td>"
			."<td>".add_menu(ad_status($conf['homeurl']."/index.php?name=account&amp;op=activate&amp;user=".urlencode($user_name)."&amp;num=".$check_num, 0)."||<a href=\"".$admin_file.".php?op=users_new_del&amp;id=".$user_id."&amp;refer=1\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$user_name."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>")."</td></tr>";
		}
		$cont .= "</tbody></table>";
		$cont .= num_article('pagenum', '', $confu['anum'], 'op=users_new&amp;', 'user_id', '_users_temp', '', '', $confu['anump']);
		$cont .= tpl_eval('close', '');
	} else {
		$cont .= tpl_warn('warn', _NO_INFO, '', '', 'info');
	}
	echo $cont;
	foot();
}

function users_null() {
	global $prefix, $db, $admin_file, $conf, $confu;
	head();
	$cont = users_navi(0, 3, 0, 0);
	$cont .= tpl_eval('open');
	$cont .= '<form name="post" action="'.$admin_file.'.php" method="post"><table class="sl_table_conf">'
	.'<tr><td>'._POINTS.':</td><td>'.radio_form(0, 'points').'</td></tr>'
	.'<tr><td>'._RATINGS.':</td><td>'.radio_form(0, 'votes').'</td></tr>'
	.'<tr><td>'._UWARNS.':</td><td>'.radio_form(0, 'warnings').'</td></tr>'
	.'<tr><td>'._SIGNATURE.':</td><td>'.radio_form(0, 'sig').'</td></tr>'
	.'<tr><td colspan="2" class="sl_center"><input type="hidden" name="op" value="users_null_save"><input type="submit" value="'._SAVECHANGES.'" class="sl_but_blue"></td></tr></table></form>';
	$cont .= tpl_eval('close', '');
	echo $cont;
	foot();
}

function users_conf() {
	global $admin_file, $confu;
	head();
	$cont = users_navi(0, 4, 0, 0);
	$permtest = end_chmod("config/config_users.php", 666);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<form name=\"post\" action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_conf\">"
	."<tr><td>"._ANONYMOUSNAME.":</td><td><input type=\"text\" name=\"anonym\" value=\"".$confu['anonym']."\" class=\"sl_conf\" placeholder=\""._ANONYMOUSNAME."\" required></td></tr>"
	."<tr><td>"._ADIR.":</td><td><input type=\"text\" name=\"adirectory\" value=\"".$confu['adirectory']."\" class=\"sl_conf\" placeholder=\""._ADIR."\" required></td></tr>"
	."<tr><td>"._ATYPE.":</td><td><input type=\"text\" name=\"atypefile\" value=\"".$confu['atypefile']."\" class=\"sl_conf\" placeholder=\""._ATYPE."\" required></td></tr>"
	."<tr><td>"._ASIZE.":</td><td><input type=\"number\" name=\"amaxsize\" value=\"".$confu['amaxsize']."\" class=\"sl_conf\" placeholder=\""._ASIZE."\" required></td></tr>"
	."<tr><td>"._AWIDTH._AIN.":</td><td><input type=\"number\" name=\"awidth\" value=\"".$confu['awidth']."\" class=\"sl_conf\" placeholder=\""._AWIDTH._AIN."\" required></td></tr>"
	."<tr><td>"._AHEIGHT._AIN.":</td><td><input type=\"number\" name=\"aheight\" value=\"".$confu['aheight']."\" class=\"sl_conf\" placeholder=\""._AHEIGHT._AIN."\" required></td></tr>"
	."<tr><td>"._VOTING_TIME.":</td><td><input type=\"number\" name=\"user_t\" value=\"".intval($confu['user_t'] / 86400)."\" class=\"sl_conf\" placeholder=\""._VOTING_TIME."\" required></td></tr>"
	."<tr><td>"._C_34.":</td><td><input type=\"number\" name=\"anum\" value=\"".$confu['anum']."\" class=\"sl_conf\" placeholder=\""._C_34."\" required></td></tr>"
	."<tr><td>"._C_36.":</td><td><input type=\"number\" name=\"anump\" value=\"".$confu['anump']."\" class=\"sl_conf\" placeholder=\""._C_36."\" required></td></tr>"
	."<tr><td>"._PASSWDLEN.":</td><td><select name=\"minpass\" class=\"sl_conf\">";
	$xminpass = 3;
	while ($xminpass <= 10) {
		$sel = ($xminpass == $confu['minpass']) ? " selected" : "";
		$cont .= "<option value=\"".$xminpass."\"".$sel.">".$xminpass."</option>";
		$xminpass++;
	}
	$cont .= "</select></td></tr>"
	."<tr><td>"._LOGINFL.":</td><td>"
	."<select name=\"enter\" class=\"sl_conf\">"
	."<option value=\"0\"";
	if ($confu['enter'] == "0") $cont .= " selected";
	$cont .= ">"._LOGINL."</option>"
	."<option value=\"1\"";
	if ($confu['enter'] == "1") $cont .= " selected";
	$cont .= ">"._LOGINF."</option>"
	."</select></td></tr>"
	."<tr><td>"._UPDATE_POINTS."</td><td>".radio_form($confu['point'], "point")."</td></tr>"
	."<tr><td>"._AUPLOAD."</td><td>".radio_form($confu['aupload'], "aupload")."</td></tr>"
	."<tr><td>"._NO_MAIL_REG."</td><td>".radio_form($confu['nomail'], "nomail")."</td></tr>"
	."<tr><td>"._USERSHOMENUM."</td><td>".radio_form($confu['news'], "news")."</td></tr>"
	."<tr><td>"._USERIPCHECK."</td><td>".radio_form($confu['check'], "check")."</td></tr>"
	."<tr><td>"._REGACT."</td><td>".radio_form($confu['reg'], "reg")."</td></tr>"
	."<tr><td>"._SELTHEME."</td><td>".radio_form($confu['theme'], "theme")."</td></tr>"
	."<tr><td>"._PROFACT."</td><td>".radio_form($confu['prof'], "prof")."</td></tr>"
	."<tr><td>"._NETWORKACTIVE."</td><td>".radio_form($confu['network'], "network")."</td></tr>"
	."<tr><td>"._RULACT."</td><td>".radio_form($confu['rule'], "rule")."</td></tr>"
	."<tr><td>"._RULES.":</td><td><textarea name=\"rules\" cols=\"65\" rows=\"10\" class=\"sl_conf\" placeholder=\""._RULES."\">".$confu['rules']."</textarea></td></tr>"
	."<tr><td>"._NETWORKCODE.":</td><td>".textarea_code('code', 'network_c', 'sl_conf', 'text/html', $confu['network_c'])."</td></tr>"
	."<tr><td>"._NAME_BLOCK.":<div class=\"sl_small\">"._NOKOMA."</div></td><td><textarea name=\"name_b\" cols=\"65\" rows=\"5\" class=\"sl_conf\" placeholder=\""._NAME_BLOCK."\">".$confu['name_b']."</textarea></td></tr>"
	."<tr><td>"._MAIL_BLOCK.":<div class=\"sl_small\">"._NOKOMA."</div></td><td><textarea name=\"mail_b\" cols=\"65\" rows=\"5\" class=\"sl_conf\" placeholder=\""._MAIL_BLOCK."\">".$confu['mail_b']."</textarea></td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"users_save\"><input type=\"submit\" value=\""._SAVECHANGES."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function users_save() {
	global $admin_file, $confu;
	$protect = array("\n" => "", "\t" => "", "\r" => "", " " => "");
	$xatypefile = (!$_POST['atypefile']) ? "gif,jpg,jpeg,png" : strtolower(strtr($_POST['atypefile'], $protect));
	$xamaxsize = (!intval($_POST['amaxsize'])) ? 51200 : $_POST['amaxsize'];
	$xawidth = (!intval($_POST['awidth'])) ? 100 : $_POST['awidth'];
	$xaheight = (!intval($_POST['aheight'])) ? 100 : $_POST['aheight'];
	$xuser_t = (!$_POST['user_t']) ? 2592000 : intval($_POST['user_t'] * 86400);
	$xanum = (!intval($_POST['anum'])) ? 50 : $_POST['anum'];
	$xanump = (!intval($_POST['anump'])) ? 10 : $_POST['anump'];
	$xname_b = strtolower(strtr($_POST['name_b'], $protect));
	$xmail_b = strtolower(strtr($_POST['mail_b'], $protect));
	$xnetwork_c = "<<<HTML\n".stripslashes($_POST['network_c'])."\nHTML";
	$cont = array('anonym' => $_POST['anonym'], 'adirectory' => $_POST['adirectory'], 'atypefile' => $xatypefile, 'amaxsize' => $xamaxsize, 'awidth' => $xawidth, 'aheight' => $xaheight, 'user_t' => $xuser_t, 'anum' => $xanum, 'anump' => $xanump, 'minpass' => $_POST['minpass'], 'enter' => $_POST['enter'], 'point' => $_POST['point'], 'aupload' => $_POST['aupload'], 'nomail' => $_POST['nomail'], 'news' => $_POST['news'], 'check' => $_POST['check'], 'reg' => $_POST['reg'], 'theme' => $_POST['theme'], 'prof' => $_POST['prof'], 'network' => $_POST['network'], 'rule' => $_POST['rule'], 'rules' => text_filter($_POST['rules'], 1), 'network_c' => $xnetwork_c, 'name_b' => $xname_b, 'mail_b' => $xmail_b, 'points' => $confu['points']);
	save_conf('config/config_users.php', $cont, '', 'confu');
	header("Location: ".$admin_file.".php?op=users_conf");
}

function users_info() {
	head();
	echo users_navi(0, 5, 0, 0).'<div id="repadm_info">'.adm_info(1, 0, 'users').'</div>';
	foot();
}

switch($op) {
	case 'users_show':
	users_show();
	break;
	
	case 'users_add':
	users_add();
	break;
	
	case 'users_add_save':
	users_add_save();
	break;
	
	case 'users_new':
	users_new();
	break;
	
	case 'users_new_del':
	$db->sql_query("DELETE FROM ".$prefix."_users_temp WHERE user_id = '".$id."'");
	referer($admin_file.'.php?op=users_show');
	break;
	
	case 'users_del':
	$db->sql_query("DELETE FROM ".$prefix."_users WHERE user_id = '".$id."'");
	$db->sql_query("DELETE FROM ".$prefix."_favorites WHERE uid = '".$id."'");
	# $db->sql_query("DELETE FROM ".$prefix."_comment WHERE uid = '".$id."'");
	referer($admin_file.'.php?op=users_show');
	break;
	
	case 'users_null':
	users_null();
	break;
	
	case 'users_null_save':
	if (intval($_POST['points']) == 1) $db->sql_query("UPDATE ".$prefix."_users SET user_points = '0'");
	if (intval($_POST['votes']) == 1) $db->sql_query("UPDATE ".$prefix."_users SET user_votes = '0', user_totalvotes = '0'");
	if (intval($_POST['warnings']) == 1) $db->sql_query("UPDATE ".$prefix."_users SET user_warnings = '0'");
	if (intval($_POST['sig']) == 1) $db->sql_query("UPDATE ".$prefix."_users SET user_sig = ''");
	header('Location: '.$admin_file.'.php?op=users_show');
	break;
	
	case 'users_conf':
	users_conf();
	break;
	
	case 'users_save':
	users_save();
	break;
	
	case 'users_info':
	users_info();
	break;
}
?>