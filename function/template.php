<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("FUNC_FILE")) die("Illegal file access");

if (!function_exists("tpl_head")) {
	function tpl_head() {
		global $blockg, $theme, $user, $conf, $confu;
		$arg = func_get_args();
		if (is_user()) {
			$uname = htmlspecialchars(substr($user[1], 0, 25));
			$userinfo = getusrinfo();
			$user_avatar = (file_exists($confu['adirectory']."/".$userinfo['user_avatar'])) ? $userinfo['user_avatar'] : "default/00.gif";
			$cont = tpl_eval("login-logged", _ACCOUNT, $confu['adirectory']."/".$user_avatar, $uname, _LOGOUT);
		} else {
			if ($confu['enter'] == 1) {
				$captcha = (extension_loaded("gd") && ($conf['gfx_chk'] == 2 || $conf['gfx_chk'] == 4 || $conf['gfx_chk'] == 5 || $conf['gfx_chk'] == 7)) ? get_captcha(2) : "";
				$cont = tpl_eval("login", _LOGIN, _NICKNAME, _PASSWORD, $captcha, _LOGIN, _PASSFOR, _REG);
			} else {
				$cont = tpl_eval("login-without", _BREG);
			}
		}
		$lan = array($cont, $conf['sitename'], $conf['site_logo'], $conf['homeurl'], $conf['slogan'], _HOME, _ACCOUNT, _ALBUM, _A_LINKS, _FEEDBACK, _CONTENT, _FAQ, _FILES, _FORUM, _ALBUM, _HELP, _RADIO, _JOKES, _LINKS, _MEDIA, _USERS, _NEWS, _ORDER, _PAGES, _RECOMMEND, _RSS, _SEARCH, _SHOP, _TOPUSERS, _VOTING, _S_FAVORITEN, _S_STARTSEITE);
		eval("\$r_file=\"".addslashes($arg[0])."\";");
		return stripslashes($r_file);
	}
}

if (!function_exists("tpl_block")) {
	function tpl_block() {
		global $blockg, $theme, $pos, $blockfile, $b_id, $home, $conf;
		$arg = func_get_args();
		$lan = array();
		static $bl_mass;
		if ($pos == "s" || $pos == "o") {
			$bl_name = empty($blockfile) ? "fly-block-".$b_id : "fly-".str_replace(".php", "", $blockfile);
		} else {
			$bl_name = empty($blockfile) ? "block-".$b_id : str_replace(".php", "", $blockfile);
		}
		if (!isset($bl_mass[$bl_name])) {
			$tmp_file = file_exists("templates/".$theme."/".$bl_name.".html") ? "templates/".$theme."/".$bl_name.".html" : false;
			if ($tmp_file) {
				$bl_mass[$bl_name]['f'] = create_function("\$arg, \$lan", "global \$blockg, \$theme, \$conf; return \"".addslashes(file_get_contents($tmp_file))."\";");
			} else {
				switch($pos) {
					case 'l':
					$bl_name ="block-left";
					break;
					case 'r':
					$bl_name ="block-right";
					break;
					case 'c':
					$bl_name ="block-center";
					break;
					case 'd':
					$bl_name ="block-down";
					break;
					case 's':
					$bl_name ="block-fly";
					break;
					case 'o':
					$bl_name ="block-fly";
					break;
					default:
					$bl_name ="block-all";
					break;
				}
				if (!isset($bl_mass[$bl_name])) {
					$tmp_file = get_theme_file($bl_name);
					if ($tmp_file) {
						$bl_mass[$bl_name]['f'] = create_function("\$arg, \$lan", "global \$blockg, \$theme, \$conf; return \"".addslashes(file_get_contents($tmp_file))."\";");
					} else {
						if (!isset($bl_mass['block-all'])) {
							$tmp_file = get_theme_file("block-all");
							if ($tmp_file) {
								$bl_mass[$bl_name]['f'] = create_function("\$arg, \$lan", "global \$blockg, \$theme, \$conf; return \"".addslashes(file_get_contents($tmp_file))."\";");
							} else {
								return "<fieldset><legend>".$arg[1]."</legend>".$arg[2]."</fieldset>";
							}
						}
					}
				}
			}
		}
		return $bl_mass[$bl_name]['f']($arg, $lan);
	}
}

if (!function_exists("tpl_eval")) {
	function tpl_eval() {
		global $blockg, $theme, $conf;
		$arg = func_get_args();
		$lan = array(_SEARCH);
		$cont = get_theme_file($arg[0]);
		if ($cont) eval("\$rfl = \"".addslashes(file_get_contents($cont))."\";");
		return ($cont) ? stripslashes($rfl) : tpl_warn("warn", sprintf(_ERRORTPL, $arg[0]), "", "", "warn");
	}
}

if (!function_exists("tpl_func")) {
	function tpl_func() {
		global $blockg, $theme, $conf;
		$arg = func_get_args();
		$lan = array();
		static $argc, $cach, $cont;
		if ($argc != $arg[0] || !isset($cach)) {
			$argc = $arg[0];
			$cont = get_theme_file($argc);
			if ($cont) $cach = create_function("\$arg, \$lan", "global \$blockg, \$theme, \$conf; return \"".addslashes(file_get_contents($cont))."\";");
		}
		return ($cont) ? $cach($arg, $lan) : tpl_warn("warn", sprintf(_ERRORTPL, $arg[0]), "", "", "warn");
	}
}

if (!function_exists("tpl_warn")) {
	function tpl_warn() {
		global $blockg, $theme, $conf;
		$arg = func_get_args();
		$lan = array();
		$arg[1] = (is_array($arg[1])) ? implode("<br>", $arg[1]) : $arg[1];
		if ($arg[2] || intval($arg[3])) $arg[2] = "<meta http-equiv=\"refresh\" content=\"".$arg[3]."; url=index.php".$arg[2]."\">";
		$arg[3] = $arg[4] ;
		$cont = get_theme_file($arg[0]);
		if ($cont) eval("\$rfl = \"".addslashes(file_get_contents($cont))."\";");
		return ($cont) ? stripslashes($rfl) : sprintf(_ERRORTPL, $arg[0]);
	}
}

if (!function_exists("tpl_foot")) {
	function tpl_foot() {
		global $blockg, $theme, $conf;
		$arg = func_get_args();
		
		# Под вопросом
		$cont = (isset($cont)) ? $cont : "";
		
		$lan = array($cont, $conf['sitename'], $conf['site_logo'], $conf['homeurl'], $conf['slogan'], _HOME, _ACCOUNT, _ALBUM, _A_LINKS, _FEEDBACK, _CONTENT, _FAQ, _FILES, _FORUM, _ALBUM, _HELP, _RADIO, _JOKES, _LINKS, _MEDIA, _USERS, _NEWS, _ORDER, _PAGES, _RECOMMEND, _RSS, _SEARCH, _SHOP, _TOPUSERS, _VOTING, _S_FAVORITEN, _S_STARTSEITE);
		eval("\$r_file=\"".addslashes($arg[0])."\";");
		return stripslashes($r_file);
	}
}
?>