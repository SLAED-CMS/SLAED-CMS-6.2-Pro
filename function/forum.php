<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("FUNC_FILE")) die("Illegal file access");

if ($conf['forum'] == 'ipb1.3.1' OR $conf['forum'] == 'ipb2.0.x' OR $conf['forum'] == 'ipb2.1.x' OR $conf['forum'] == 'ipb2.2.x' OR $conf['forum'] == 'ipb2.3.x') {
	include_once("forum/conf_global.php");
	$table_prefix = $INFO['sql_tbl_prefix'];
	$sql_pass = $INFO['sql_pass'];
	$sql_user = $INFO['sql_user'];
	function log_in($user_name, $user_password) {
		global $db, $conf, $table_prefix;
		$user_name = strtolower(str_replace('|', '&#124;', $user_name));
		$user_password = md5($user_password);
		if ($conf['forum'] == 'ipb2.0.x' OR $conf['forum'] == 'ipb2.1.x' OR $conf['forum'] == 'ipb2.2.x' OR $conf['forum'] == 'ipb2.3.x') {
			$db->sql_query("SELECT m.name, m.id, m.member_login_key, m.email, c.converge_pass_salt, c.converge_pass_hash FROM ".$table_prefix."members m LEFT JOIN ".$table_prefix."members_converge c ON (c.converge_id=m.id) WHERE LOWER(name)='".$user_name."'");
		} elseif ($conf['forum'] == 'ipb1.3.1') {
			$db->sql_query("SELECT name, id, password, email FROM ".$table_prefix."members WHERE LOWER(name)='".$user_name."'");
		}
		if ($member = $db->sql_fetchrow()) {
			if ($conf['forum'] == 'ipb2.1.x' OR $conf['forum'] == 'ipb2.0.x' OR $conf['forum'] == "ipb2.2.x" OR $conf['forum'] == "ipb2.3.x") {
				if ($member['converge_pass_hash'] != generate_compiled_passhash( str_replace( "\\\\" , '\\', $member['converge_pass_salt'] ), $user_password)) {
					return false;
				}
			} elseif ($conf['forum'] == 'ipb1.3.1') {
				if ($member['password'] != $user_password) {
					return false;
				}
			}
			if (!$member['id']) {
				return false;
			}
			$sid = md5(uniqid(microtime()));
			if ($conf['forum'] == 'ipb2.1.x' OR $conf['forum'] == 'ipb2.0.x' OR $conf['forum'] == 'ipb2.2.x' OR $conf['forum'] == 'ipb2.3.x') {
				$pass_hash_set = $member['member_login_key'];
			} elseif ($conf['forum'] == 'ipb1.3.1') {
				$pass_hash_set = $user_password;
			}
			if ($conf['forum'] == 'ipb2.2.x' OR $conf['forum'] == 'ipb2.3.x') stronghold_set_cookie( $member['id'], $member['member_login_key'] );
			setcookie("member_id", $member['id'], time() + intval($conf['user_c_t']));
			setcookie("pass_hash", $pass_hash_set, time() + intval($conf['user_c_t']));
			setcookie("session_id", $sid, time() + intval($conf['user_c_t']));
			$db->sql_query("UPDATE ".$table_prefix."members SET ip_address='" . $_SERVER['REMOTE_ADDR'] . "' WHERE id='".$member['id']."'");
			$db->sql_query("DELETE FROM ".$table_prefix."sessions WHERE ip_address='" . $_SERVER['REMOTE_ADDR'] . "'");
			$id = $member['id'];
			$userag = getagent();
			$browser = substr($userag, 0, 64);
			$ip = substr($_SERVER['REMOTE_ADDR'], 0, 16);
			$db->sql_query("INSERT INTO ".$table_prefix."sessions (id, member_name, member_id, running_time, member_group, ip_address, browser, login_type) VALUES ('$sid', '".$member['name']."', '".$member['id']."',  '".time()."', '".$member['mgroup']."', '$ip', '$browser', '0')");
		} else {
			return false;
		}
	}

	function log_out() {
		global $db, $table_prefix;
		$db->sql_query("UPDATE ".$table_prefix."sessions SET member_name='',member_id='0',login_type='0' WHERE id='".text_filter($_COOKIE['session_id'])."'");
		setcookie("member_id", "0");
		setcookie("pass_hash", "0");
		setcookie("session_id", "-1");
		$db->sql_query("UPDATE ".$table_prefix."members SET last_visit='".time()."', last_activity='".time()."' WHERE id='".text_filter($_COOKIE['session_id'])."'");
		return true;
	}

	function new_user($user_name, $user_password, $user_email) {
		global $db, $table_prefix, $conf;
		$user_password = md5($user_password);
		if ($conf['forum'] == "ipb1.3.1") {
			$result = $db->sql_query("SELECT id FROM ".$table_prefix."members WHERE LOWER(name)='" . strtolower($user_name) . "' OR email='" . $user_email . "'");
		} else {
			$result = $db->sql_query("SELECT id FROM ".$table_prefix."members WHERE LOWER(name)='" . strtolower($user_name) . "' OR email='" . $user_email . "' OR LOWER(members_display_name)='".strtolower($user_name)."'");
		}
		if ($db->sql_numrows($result)) {
			return false;
		}
		if ($conf['forum'] == "ipb2.1.x" OR $conf['forum'] == "ipb2.0.x" OR $conf['forum'] == "ipb2.2.x" OR $conf['forum'] == "ipb2.3.x") {
			$salt = generate_password_salt(5);
			$member_login_key = generate_auto_log_in_key();
			$passhash = generate_compiled_passhash($salt, $user_password);
		}
		list($last_id) = $db->sql_fetchrow($db->sql_query("SELECT id FROM ".$table_prefix."members ORDER by id DESC LIMIT 1")); 
		$new_id = $last_id + 1;
		$ip = substr($_SERVER['REMOTE_ADDR'], 0, 16);
		if ($conf['forum'] == "ipb2.1.x") {
			$db->sql_query("INSERT INTO ".$table_prefix."members (id, name, mgroup, email, joined, member_login_key, members_display_name, ip_address) VALUES ('$new_id', '$user_name', '3', '$user_email', '".time()."', '$member_login_key',  '$user_name', '$ip')");
		} elseif ($conf['forum'] == "ipb2.0.x") {
			$db->sql_query("INSERT INTO ".$table_prefix."members (id, name, mgroup, email, joined, member_login_key, ip_address) VALUES ('$new_id', '$user_name', '3', '$user_email', '".time()."', '$member_login_key', '$ip')");
		} elseif ($conf['forum'] == "ipb1.3.1") {
			$db->sql_query("INSERT INTO ".$table_prefix."members (id, name, password, mgroup, email, avatar, posts, vdirs, joined, ip_address) VALUES ('$new_id', '$user_name', '$user_password','3', '$user_email', 'noavatar', '0', 'in:Inbox|sent:Sent Items','".time()."', '$ip')");
		} elseif ($conf['forum'] == "ipb2.2.x" OR $conf['forum'] == 'ipb2.3.x') {
			$key_exp = time() + 604800;
			$db->sql_query("INSERT INTO ".$table_prefix."members (id, name, mgroup, email, joined, ip_address, language, member_login_key, member_login_key_expire,members_display_name,members_l_display_name,members_l_username) VALUES ('$new_id', '$user_name', '3', '$user_email', '".time()."', '$ip', 'russian', '$member_login_key', '$key_exp', '$user_name', '".strtolower($user_name)."', '".strtolower($user_name)."')");
		}
		if ($conf['forum'] == "ipb2.1.x" OR $conf['forum'] == "ipb2.0.x" OR $conf['forum'] == "ipb2.2.x" OR $conf['forum'] == 'ipb2.3.x') {
			$db->sql_query("INSERT INTO ".$table_prefix."members_converge (converge_id, converge_email, converge_joined, converge_pass_hash, converge_pass_salt) VALUES ('$new_id', '$user_email', '".time()."', '$passhash', '$salt')");
			$db->sql_query("INSERT INTO ".$table_prefix."member_extra (id, vdirs) VALUES ('$new_id', 'in:Inbox|sent:Sent Items')");
			$result = mysql_fetch_array($db->sql_query("SELECT * FROM ".$table_prefix."cache_store WHERE cs_key='stats'"));
			$stats = unserialize($result['cs_value']);
 			$stats['last_mem_name'] = $user_name;
			$stats['mem_count'] = $stats['mem_count']+1;
			$stats['last_mem_id'] = $new_id;
			$stats = serialize($stats);
			$db->sql_query("UPDATE ".$table_prefix."cache_store SET cs_value='$stats' WHERE cs_key='stats'");
		} elseif ($conf['forum'] == "ipb1.3.1") {
			$db->sql_query("INSERT INTO ".$table_prefix."member_extra (id) VALUES ('$new_id')");
			$db->sql_query("UPDATE ".$table_prefix."stats SET MEM_COUNT=MEM_COUNT+1, LAST_MEM_NAME='".$user_name."', LAST_MEM_ID='".$new_id."'");
		}
		return true;
	}

	function check_user($user_name, $user_pass) {
		global $db, $table_prefix;
		$result = $db->sql_query("SELECT a.email, b.converge_pass_hash, b.converge_pass_salt FROM ".$table_prefix."members a LEFT JOIN ".$table_prefix."members_converge b ON (b.converge_id=a.id) WHERE LOWER(a.name)='".strtolower($user_name)."'");
		$info = $db->sql_fetchrow($result);
		if ($db->sql_numrows($result) == 1) {
			$new_passhash = generate_compiled_passhash($info['converge_pass_salt'], md5($user_pass));
			if ($new_passhash == $info['converge_pass_hash']) {
				new_site_user($user_name, $user_pass, $info['email']);
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function new_pass($user_name, $newpass, $user_email) {
		global $db, $table_prefix, $conf;
		$result = $db->sql_query("SELECT id FROM ".$table_prefix."members WHERE LOWER(name)='" . strtolower($user_name) . "' AND email='" . $user_email . "'");
		if ($db->sql_numrows($result) == 1) {
			$info = $db->sql_fetchrow($result);
			$salt = generate_password_salt(5);
			$new_passhash = generate_compiled_passhash( $salt, md5($newpass) );
			if ($conf['forum'] == "ipb1.3.1") {
				$db->sql_query("UPDATE ".$table_prefix."members SET password='".md5($newpass)."' WHERE id='".$info['id']."'");
			} else {
				$db->sql_query("UPDATE ".$table_prefix."members_converge SET converge_pass_hash='$new_passhash', converge_pass_salt='$salt'  WHERE converge_id='".$info['id']."'");
			}
		} else {
			return false;
		}
	}

	function generate_compiled_passhash($salt, $md5_once_password) {
		return md5( md5( $salt ) . $md5_once_password );
	}

	function generate_password_salt($len=5) {
		$salt = '';
		for ( $i = 0; $i < $len; $i++ ) {
			$num   = rand(33, 126);
			if ( $num == '92' )	{
				$num = 93;
			}
			$salt .= chr( $num );
		}
		return $salt;
	}

	function generate_auto_log_in_key($len=60) {
		$pass = generate_password_salt(60);
		return md5($pass);
	}

	function stronghold_set_cookie( $member_id, $member_log_in_key ) {
		global $sql_pass, $sql_user;
		$ip_octets  = explode(".", $_SERVER["REMOTE_ADDR"] );
		$crypt_salt = md5( $sql_pass.$sql_user );
		$stronghold = md5( md5( $member_id . "-" . $ip_octets[0] . '-' . $ip_octets[1] . '-' . $member_log_in_key ) . $crypt_salt );
		setcookie ("ipb_stronghold", $stronghold,time()+31536000);
		return true;
	}

} elseif ($conf['forum'] == 'phpbb') {
	include_once("forum/config.php");
	$result = $db->sql_query("SELECT * FROM ".$table_prefix."config");
	while ($row = $db->sql_fetchrow($result)) {
		$board_config[$row['config_name']] = $row['config_value'];
	}

	function log_in($user_name, $user_password) {
		global $db, $table_prefix, $board_config;
		$result = $db->sql_query("SELECT user_id, username, user_password, user_active, user_level, user_login_tries, user_last_login_try FROM ".$table_prefix."users WHERE username = '".$user_name."'");
		if ($row = $db->sql_fetchrow($result)) {
			if (md5($user_password) == $row['user_password'] AND $row['user_active'] ) {	
				$user_ip = encode_ip($_SERVER['REMOTE_ADDR']);
				$cookiename = $board_config['cookie_name'];
				$cookiepath = $board_config['cookie_path'];
				$cookiedomain = $board_config['cookie_domain'];
				$cookiesecure = $board_config['cookie_secure'];
				$user_id = $row['user_id'];
				$current_time = time();
				$auto_login_key = dss_rand() . dss_rand();
				$db->sql_query("UPDATE ".$table_prefix."sessions_keys SET last_ip = '$user_ip', key_id = '".md5($auto_login_key)."', last_login = $current_time WHERE user_id='".$user_id."'");
				$e = @mysql_info();
				preg_match("/^\D+(\d+)/", $e, $matches);
				if ($matches[1] == 0) $db->sql_query("INSERT INTO ".$table_prefix."sessions_keys (key_id, user_id, last_ip, last_login) VALUES ('".md5($auto_login_key)."', '$user_id', '$user_ip', '$current_time')");
				$result = $db->sql_query("UPDATE ".$table_prefix."sessions SET session_user_id = $user_id, session_start = $current_time, session_time = $current_time, session_page = $page_id, session_logged_in = $login, session_admin = $admin	WHERE session_user_id = '".$user_id."' AND session_ip = '$user_ip'");
				if (!$result) {
					$session_id = md5(dss_rand());
					$db->sql_query("INSERT INTO ".$table_prefix."sessions (session_id, session_user_id, session_start, session_time, session_ip, session_page, session_logged_in, session_admin) VALUES ('$session_id', '$user_id', '$current_time', '$current_time', '$user_ip', '0', '1', '0')");
				}
				$sessiondata['autologinid'] = $auto_login_key;
				$sessiondata['userid'] = $user_id;
				setcookie($cookiename . '_data', serialize($sessiondata), $current_time + 31536000, $cookiepath, $cookiedomain, $cookiesecure);
				setcookie($cookiename . '_sid', $session_id, 0, $cookiepath, $cookiedomain, $cookiesecure);
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function log_out() {
		global $db, $table_prefix, $board_config;
		$cookiename = $board_config['cookie_name'];
		$cookiepath = $board_config['cookie_path'];
		$cookiedomain = $board_config['cookie_domain'];
		$cookiesecure = $board_config['cookie_secure'];
		$session_id = text_filter($_COOKIE[$cookiename."_sid"]);
		$autologin_key = unserialize($_COOKIE[$cookiename."_data"]);
		$current_time = time();
		if ($autologin_key['autologinid'] != "" AND $autologin_key['userid'] != "") {
			$autologin_key_hash = md5($autologin_key['autologinid']);
			$user_id = $autologin_key['userid'];
			$db->sql_query("DELETE FROM ".$table_prefix."sessions_keys WHERE user_id = '$user_id' AND key_id = '$autologin_key_hash'");
		}
		if ($session_id != "") $db->sql_query("DELETE FROM ".$table_prefix."sessions WHERE session_id = '$session_id'");
		setcookie($cookiename . '_data', '', $current_time - 31536000, $cookiepath, $cookiedomain, $cookiesecure);
		setcookie($cookiename . '_sid', '', $current_time - 31536000, $cookiepath, $cookiedomain, $cookiesecure);
		return true;
	}

	function new_user($user_name, $user_password, $user_email) {
		global $db, $table_prefix, $board_config;
		$user_password = md5($user_password);
		$result = $db->sql_query("SELECT user_id FROM ".$table_prefix."users WHERE LOWER(username)='".strtolower($user_name)."' OR user_email='".$user_email."'");
		if ($db->sql_numrows($result)) {
			return false;
		}
		list($last_id) = $db->sql_fetchrow($db->sql_query("SELECT user_id FROM ".$table_prefix."users ORDER by user_id DESC LIMIT 1") ); 
		$new_id = $last_id + 1;
		$db->sql_query("INSERT INTO ".$table_prefix."users (user_id, username, user_regdate, user_password, user_email, user_active, user_lang) VALUES ('$new_id', '$user_name', '".time()."', '$user_password', '$user_email', '1', 'russian')");
		return true;
	}

	function check_user($user_name, $user_pass) {
		global $db, $table_prefix;
		$result = $db->sql_query("SELECT user_email FROM ".$table_prefix."users WHERE LOWER(username)='".strtolower($user_name)."' AND user_password='".md5($user_pass)."'");
		$info = $db->sql_fetchrow($result);
		if ($db->sql_numrows($result) == 1) {
			new_site_user($user_name, $user_pass, $info['user_email']);
		} else {
			return false;
		}
	}

	function new_pass($user_name, $newpass, $user_email) {
		global $db, $table_prefix, $conf;
		$result = $db->sql_query("SELECT user_id FROM ".$table_prefix."users WHERE LOWER(username)='".strtolower($user_name)."' AND user_email='" . $user_email . "'");
		if ($db->sql_numrows($result) == 1) {
			$info = $db->sql_fetchrow($result);
			$db->sql_query("UPDATE ".$table_prefix."users SET user_password='".md5($newpass)."' WHERE user_id='".$info['user_id']."'");
		} else {
			return false;
		}
	}

	function dss_rand() {
		global $db, $board_config;
		$val = $board_config['rand_seed'] . microtime();
		$val = md5($val);
		$board_config['rand_seed'] = md5($board_config['rand_seed'] . $val . 'a');
		return substr($val, 4, 16);
	}

	function encode_ip($dotquad_ip) {
		$ip_sep = explode('.', $dotquad_ip);
		return sprintf('%02x%02x%02x%02x', $ip_sep[0], $ip_sep[1], $ip_sep[2], $ip_sep[3]);
	}

} elseif ($conf['forum'] == "phpbb3") {
	# Если у вас другая группа "пользователи", измените.
	$user_def_group = "2";
	
	# Функции из phpbb3, отвечающие за генерирование ключей, сессий и паролей
	function unique_id() {
		$val = $config['rand_seed'].microtime();
		$val = md5($val);
		$config['rand_seed'] = md5($config['rand_seed'].$val.$extra);
		return substr($val, 4, 16);
	}
	
	function _hash_crypt_private($password, $setting, &$itoa64) {
		$output = '*';
		# Check for correct hash
		if (substr($setting, 0, 3) != '$H$') return $output;
		$count_log2 = strpos($itoa64, $setting[3]);
		if ($count_log2 < 7 || $count_log2 > 30) return $output;
		$count = 1 << $count_log2;
		$salt = substr($setting, 4, 8);
		if (strlen($salt) != 8) return $output;
		$hash = md5($salt.$password, true);
		do {
			$hash = md5($hash . $password, true);
		}
		while (--$count);
		$output = substr($setting, 0, 12);
		$output .= _hash_encode64($hash, 16, $itoa64);
		return $output;
	}

	function _hash_gensalt_private($input, &$itoa64, $iteration_count_log2 = 6) {
		if ($iteration_count_log2 < 4 || $iteration_count_log2 > 31) $iteration_count_log2 = 8;
		$output = '$H$';
		$output .= $itoa64[min($iteration_count_log2 + ((PHP_VERSION >= 5) ? 5 : 3), 30)];
		$output .= _hash_encode64($input, 6, $itoa64);
		return $output;
	}

	function _hash_encode64($input, $count, &$itoa64) {
		$output = '';
		$i = 0;
		do {
			$value = ord($input[$i++]);
			$output .= $itoa64[$value & 0x3f];
			if ($i < $count) $value |= ord($input[$i]) << 8;
			$output .= $itoa64[($value >> 6) & 0x3f];
			if ($i++ >= $count) break;
			if ($i < $count) $value |= ord($input[$i]) << 16;
			$output .= $itoa64[($value >> 12) & 0x3f];
			if ($i++ >= $count) break;
			$output .= $itoa64[($value >> 18) & 0x3f];
		}
		while ($i < $count);
		return $output;
	}

	function phpbb_hash($password) {
		$itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		$random_state = unique_id();
		$random = '';
		$count = 6;
		if (($fh = @fopen('/dev/urandom', 'rb'))) {
			$random = fread($fh, $count);
			fclose($fh);
		}
		if (strlen($random) < $count) {
			$random = '';
			for ($i = 0; $i < $count; $i += 16) {
				$random_state = md5(unique_id() . $random_state);
				$random .= pack('H*', md5($random_state));
			}
			$random = substr($random, 0, $count);
		}
		$hash = _hash_crypt_private($password, _hash_gensalt_private($random, $itoa64), $itoa64);
		if (strlen($hash) == 34) return $hash;
		return md5($password);
	}

	function phpbb_check_hash($password, $hash) {
		$itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		if (strlen($hash) == 34) return (_hash_crypt_private($password, $hash, $itoa64) === $hash) ? true : false;
		return (md5($password) === $hash) ? true : false;
	}

	function get_avatar_filename($avatar_entry) {
		global $board_config;
		if ($avatar_entry[0] === 'g') {
			$avatar_group = true;
			$avatar_entry = substr($avatar_entry, 1);
		} else {
			$avatar_group = false;
		}
		$ext = substr(strrchr($avatar_entry, '.'), 1);
		$avatar_entry = intval($avatar_entry);
		return $board_config['avatar_salt'].'_'.(($avatar_group) ? 'g' : '').$avatar_entry.'.'.$ext;
	}
	# Конец функций phpbb3

	include_once("forum/config.php");
	$result = $db->sql_query("SELECT * FROM ".$table_prefix."config");
	while ($row = $db->sql_fetchrow($result)) {
		$board_config[$row['config_name']] = $row['config_value'];
	}

	function log_in($user_name, $user_password) {
		global $db, $table_prefix, $board_config;
		$user_name = strtolower(str_replace('|', '&#124;', $user_name));
		$result = $db->sql_query("SELECT user_id, username_clean, user_password, user_passchg, user_email, user_type FROM ".$table_prefix."users WHERE username_clean = '".$user_name."'");
		$row = $db->sql_fetchrow($result);
		if($user_name == $row['username_clean'] && phpbb_check_hash($user_password, $row['user_password'])) {
			$expire = time()+60*60*24*365;
			$phpbb_key = unique_id(hexdec(substr(md5(unique_id()), 0, 8)));
			$phpbb_sid = md5($phpbb_key);
			$db->sql_query("INSERT INTO ".$table_prefix."sessions_keys (key_id, user_id, last_ip, last_login) VALUES ('".$phpbb_sid."','".$row['user_id']."','".getenv("REMOTE_ADDR")."','".time()."')");
			setcookie($board_config['cookie_name']."_u",$row['user_id'],$expire,"/",$board_config['cookie_domain']);
			setcookie($board_config['cookie_name']."_k",$phpbb_key,$expire,"/",$board_config['cookie_domain']);
			setcookie($board_config['cookie_name']."_sid",$phpbb_sid,$expire,"/",$board_config['cookie_domain']);
			return true;
		} else {
			return false;
		}
	}

	function log_out() {
		global $db, $table_prefix, $board_config;
		$phpbb_key = $_COOKIE[$board_config['cookie_name']."_k"];
		$phpbb_sid = $_COOKIE[$board_config['cookie_name']."_sid"];
		$db->sql_query("DELETE FROM ".$table_prefix."sessions_keys WHERE key_id = '".md5($phpbb_key)."'");
		$db->sql_query("DELETE FROM ".$table_prefix."sessions WHERE session_id = '".$phpbb_sid."'");
		setcookie($board_config['cookie_name']."_u","1","0","/",$board_config['cookie_domain']);
		setcookie($board_config['cookie_name']."_k","","0","/",$board_config['cookie_domain']);
		setcookie($board_config['cookie_name']."_sid","","0","/",$board_config['cookie_domain']);
	}

	function new_user($user_name, $user_password, $user_email) {
		global $db, $table_prefix, $board_config, $user_def_group;
		$user_name_clean = strtolower(str_replace('|', '&#124;', $user_name));
		if(strlen($user_password) != 34){
			$phpbb_password = phpbb_hash($user_password);
		} else {
			$phpbb_password = $user_password;
		}
		$email_hash = crc32(strtolower($user_email)).strlen($user_email);
		$form_salt = unique_id();
		$user_ip = getenv("REMOTE_ADDR");
		$num_users_new = $board_config['num_users'] + 1;
		list($last_id) = $db->sql_fetchrow($db->sql_query("SELECT user_id FROM ".$table_prefix."users ORDER BY user_id DESC LIMIT 1"));
		$reg_id = $last_id + 1;
		$db->sql_query("INSERT INTO ".$table_prefix."users (user_id, group_id, user_ip, user_regdate, username, username_clean, user_password, user_passchg, user_email, user_email_hash, user_lang, user_dateformat, user_form_salt) VALUES ('".$reg_id."', '".$user_def_group."', '".$user_ip."', '".time()."', '".$user_name."', '".$user_name_clean."', '".$phpbb_password."', '".time()."', '".$user_email."', '".$email_hash."', '".$board_config['default_lang']."', '".$board_config['default_dateformat']."', '".$form_salt."')");
		$db->sql_query("INSERT INTO ".$table_prefix."user_group (group_id, user_id, group_leader, user_pending) VALUES ('2', '".$reg_id."', '0', '0')");

		$db->sql_query("UPDATE ".$table_prefix."config SET config_value = '".$reg_id."' WHERE config_name = 'newest_user_id'");
		$db->sql_query("UPDATE ".$table_prefix."config SET config_value = '".$user_name."' WHERE config_name = 'newest_username'");
		$db->sql_query("UPDATE ".$table_prefix."config SET config_value = '".$num_users_new."' WHERE config_name = 'num_users'");
	}

	function check_user($user_name, $user_pass) {
	}

	function dss_rand() {
	}

	function encode_ip($dotquad_ip) {
	}

	function update_userinfo_fromphpbb($user_name) {
		global $db, $table_prefix, $board_config, $prefix;
		$user_name = strtolower(str_replace('|', '&#124;', $user_name));
		$query = $db->sql_query("SELECT user_avatar, user_website, user_email, user_occ, user_from, user_interests, user_sig, user_allow_viewemail, user_birthday FROM ".$table_prefix."users WHERE username_clean = '".$user_name."'");
		list($user_avatar, $user_website, $user_email, $user_occ, $user_from, $user_interests, $user_sig, $user_allow_viewemail, $user_birthday) = $db->sql_fetchrow($query);
		$userinfo = $db->sql_fetchrow($db->sql_query("SELECT * FROM ".$prefix."_users WHERE LOWER(user_name) = '".$user_name."'"));
		if(!empty($user_avatar)) {
			$user_avatar = $user_avatar;
		} else {
			$user_avatar = "default/00.gif";
		}
		preg_match("/(..)(.)(..)(.)(....)/", $user_birthday, $user_birthday_ary);
		$user_birthday = date("Y-m-d", mktime(0, 0, 0, $user_birthday_ary[3], $user_birthday_ary[1], $user_birthday_ary[5]));
		if ($userinfo['user_avatar'] != $user_avatar) $db->sql_query("UPDATE ".$prefix."_users SET user_avatar = '".$user_avatar."' WHERE LOWER(user_name) = '".$user_name."'");
		if ($userinfo['user_website'] != $user_website) $db->sql_query("UPDATE ".$prefix."_users SET user_website = '".$user_website."' WHERE LOWER(user_name) = '".$user_name."'");
		if ($userinfo['user_email'] != $user_email) $db->sql_query("UPDATE ".$prefix."_users SET user_email = '".$user_email."' WHERE LOWER(user_name) = '".$user_name."'");
		if ($userinfo['user_occ'] != $user_occ) $db->sql_query("UPDATE ".$prefix."_users SET user_occ = '".$user_occ."' WHERE LOWER(user_name) = '".$user_name."'");
		if ($userinfo['user_from'] != $user_from) $db->sql_query("UPDATE ".$prefix."_users SET user_from = '".$user_from."' WHERE LOWER(user_name) = '".$user_name."'");
		if ($userinfo['user_interests'] != $user_interests) $db->sql_query("UPDATE ".$prefix."_users SET user_interests = '".$user_interests."' WHERE LOWER(user_name) = '".$user_name."'");
		if ($userinfo['user_sig'] != $user_sig) $db->sql_query("UPDATE ".$prefix."_users SET user_sig = '".$user_sig."' WHERE LOWER(user_name) = '".$user_name."'");
		if ($userinfo['user_viewemail'] != $user_allow_viewemail) $db->sql_query("UPDATE ".$prefix."_users SET user_viewemail = '".$user_allow_viewemail."' WHERE LOWER(user_name) = '".$user_name."'");
		if ($userinfo['user_birthday'] != $user_birthday) $db->sql_query("UPDATE ".$prefix."_users SET user_birthday = '".$user_birthday."' WHERE LOWER(user_name) = '".$user_name."'");
	}

	function phpbb_update_pass($user_name, $newpass) {
		global $db, $table_prefix, $board_config;
		$user_name_clean = strtolower(str_replace('|', '&#124;', $user_name));
		$phpbb_password = phpbb_hash($newpass);
		$db->sql_query("UPDATE ".$table_prefix."users SET user_password = '".$phpbb_password."', user_passchg = '".time()."' WHERE username_clean = '".$user_name_clean."'");
	}
} elseif ($conf['forum'] == 'vb') {
	include_once("forum/includes/config.php");
	$table_prefix = $config['Database']['tableprefix'];
	$fcookieprefix = $config['Misc']['cookieprefix'];
	$fcookiesalt = $config['Misc']['cookie_security_hash'];

	function log_in($user_name, $user_password) {
		global $db, $conf, $table_prefix, $fcookieprefix, $fcookiesalt;
		$user_name = strtolower(str_replace('|', '&#124;', $user_name));
		$user_password = md5($user_password);
		$db->sql_query("SELECT userid, usergroupid, membergroupids, infractiongroupids, username, password, salt FROM ".$table_prefix."user WHERE LOWER(username) = '".$user_name."'");
		if ($member = $db->sql_fetchrow()) {
			if ($member['password'] != md5($user_password.$member['salt'])) {
				return false;
			}
			$ip = substr($_SERVER['REMOTE_ADDR'], 0, 16);
			$userag = getagent();
			$session_idhash = md5($userag.$ip);
			$scriptpath = $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : $_ENV['REQUEST_URI'];
			$sessionhash = md5(time().$scriptpath.$session_idhash.$ip.mt_rand(1, 1000000));
			$browser = substr($userag, 0, 64);
			$old_s_id = text_filter($_COOKIE[$fcookieprefix."sessionhash"]);
			if ($old_s_id != "") $db->sql_query("DELETE FROM ".$table_prefix."session WHERE sessionhash = '".$old_s_id."'");
			$db->sql_query("INSERT INTO ".$table_prefix."session (sessionhash, userid, host, idhash, lastactivity, location, useragent, loggedin) VALUES ('$sessionhash', '".$member['userid']."', '$ip', '$session_idhash', '".time()."', '$scriptpath', '$browser', '1')");
			setcookie($fcookieprefix."userid", $member['userid'], time() + intval($conf['user_c_t']));
			setcookie($fcookieprefix."password", md5($member['password'].$fcookiesalt), time() + intval($conf['user_c_t']));
			setcookie($fcookieprefix."sessionhash", $sessionhash, time() + intval($conf['user_c_t']));
		} else {
			return false;
		}
	}

	function log_out() {
		global $db, $table_prefix, $fcookieprefix;
		$db->sql_query("DELETE FROM ".$table_prefix."session WHERE sessionhash='".text_filter($_COOKIE[$fcookieprefix.'sessionhash'])."'");
		setcookie($fcookieprefix."userid", "");
		setcookie($fcookieprefix."password", "");
		setcookie($fcookieprefix."sessionhash", "");
		return true;
	}

	function new_user($user_name, $user_password, $user_email) {
		global $db, $table_prefix, $conf;
		$user_password = md5($user_password);
		$result = $db->sql_query("SELECT userid FROM ".$table_prefix."user WHERE LOWER(username)='" . strtolower($user_name) . "' OR email='" . $user_email . "'");
		if ($db->sql_numrows($result)) {
			return false;
		}
		list($last_id) = $db->sql_fetchrow($db->sql_query("SELECT userid FROM ".$table_prefix."user ORDER by userid DESC LIMIT 1") ); 
		$new_id = $last_id + 1;
		$ip = substr($_SERVER['REMOTE_ADDR'], 0, 16);
		$salt = do_salt();
		$pass = md5($user_password.$salt);
		$passworddate = date("Y-m-d");
		$db->sql_query("INSERT INTO ".$table_prefix."user (userid, usergroupid, username, password, passworddate, email, joindate, lastvisit, lastactivity, ipaddress, salt) VALUES ('$new_id', '2', '$user_name', '$pass', '$passworddate', '$user_email', '".time()."', '".time()."', '".time()."', '$ip', '$salt')");
		$db->sql_query("INSERT INTO ".$table_prefix."userfield (userid) VALUES ('$new_id')");
		$db->sql_query("INSERT INTO ".$table_prefix."usertextfield (userid) VALUES ('$new_id')");
		$result = mysql_fetch_array ($db->sql_query("SELECT * FROM ".$table_prefix."datastore WHERE title='userstats'") );
		$stats = unserialize($result['data']);
		$stats['newusername'] = $user_name;
		$stats['numbermembers'] = $stats['numbermembers']+1;
		$stats['activemembers'] = $stats['activemembers']+1;
		$stats['newuserid'] = $new_id;
		$stats = serialize($stats);
		$db->sql_query("UPDATE ".$table_prefix."datastore SET data='$stats' WHERE title='userstats'");
		return true;
	}

	function check_user($user_name, $user_pass) {
		global $db, $table_prefix;
		$result = $db->sql_query("SELECT password, email, salt FROM ".$table_prefix."user WHERE LOWER(username)='".strtolower($user_name)."'");
		$info = $db->sql_fetchrow($result);
		if ($db->sql_numrows($result) == 1) {
			$new_passhash = md5(md5($user_pass).$info['salt']);
			if ($new_passhash == $info['password']) {
				new_site_user($user_name, $user_pass, $info['email']);
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function new_pass($user_name, $newpass, $user_email) {
		global $db, $table_prefix, $conf;
		$result = $db->sql_query("SELECT userid FROM ".$table_prefix."user WHERE LOWER(username)='" . strtolower($user_name) . "' AND email='" . $user_email . "'");
		if ($db->sql_numrows($result) == 1) {
			$info = $db->sql_fetchrow($result);
			$salt = do_salt();
			$pass = md5(md5($newpass).$salt);
			$passworddate = date("Y-m-d");
			$db->sql_query("UPDATE ".$table_prefix."user SET password='".$pass."', passworddate='".$passworddate."', salt='".$salt."' WHERE userid='".$info['userid']."'");
		} else {
			return false;
		}
	}

	function do_salt($length = 3) {
		$salt = '';
		for ($i = 0; $i < $length; $i++) {
			$salt .= chr(rand(32, 126));
		}
		return $salt;
	}

} elseif ($conf['forum'] == 'smf') {
	include_once("forum/Settings.php");

	function log_in($user_name, $user_password) {
		global $db, $conf, $db_prefix, $cookiename;
		$user_name = strtolower($user_name);
		$db->sql_query("SELECT passwd, ID_MEMBER, ID_GROUP, lngfile, is_activated, emailAddress, additionalGroups, memberName, passwordSalt FROM ".$db_prefix."members WHERE LOWER(memberName) = '".$user_name."'");
		if ($member = $db->sql_fetchrow()) {
				if ($member['passwd'] != sha1(strtolower($member['memberName']).un_htmlspecialchars(stripslashes($user_password))) AND $member['passwd'] != md5(md5($user_password).$member['passwordSalt'])) {
					return false;
				}
				$data = serialize(array($member['ID_MEMBER'], sha1($member['passwd'].$member['passwordSalt']), time() + intval($conf['user_c_t']), 0));
				setcookie($cookiename, $data, time() + intval($conf['user_c_t']));
		} else {
			return false;
		}
	}
	
	function log_out() {
		global $db, $db_prefix, $cookiename, $user;
		$user_name = htmlspecialchars(substr($user[1], 0, 25));
		list($forum_mem_id) = $db->sql_fetchrow($db->sql_query("SELECT ID_MEMBER FROM ".$db_prefix."members WHERE LOWER(memberName)='".strtolower($user_name)."'") ); 
		if ($forum_mem_id) $db->sql_query("DELETE FROM ".$db_prefix."log_online WHERE ID_MEMBER ='$forum_mem_id'");
		setcookie($cookiename, false);
		return true;
	}

	function new_user($user_name, $user_password, $user_email) {
		global $db, $db_prefix, $conf;
		$user_password = md5($user_password);
		$result = $db->sql_query("SELECT ID_MEMBER FROM ".$db_prefix."members WHERE LOWER(memberName)='" . strtolower($user_name) . "' OR emailAddress='" . $user_email . "'");
		if ($db->sql_numrows($result)) {
			return false;
		}
		list($last_id) = $db->sql_fetchrow($db->sql_query("SELECT ID_MEMBER FROM ".$db_prefix."members ORDER by ID_MEMBER DESC LIMIT 1") ); 
		$new_id = $last_id + 1;
		$salt = substr(md5(rand()), 0, 4);
		$passwd = md5($user_password.$salt);
		$ip = $_SERVER['REMOTE_ADDR'];
		$db->sql_query("INSERT INTO ".$db_prefix."members (ID_MEMBER, memberName, emailAddress, passwd, passwordSalt, posts, dateRegistered, memberIP, memberIP2, realName, pm_email_notify, ID_THEME, ID_POST_GROUP) VALUES ('$new_id', '$user_name', '$user_email', '$passwd', '$salt', '0', '".time()."', '$ip', '$ip', '$user_name', '1', '0', '4')");
		$db->sql_query("UPDATE ".$db_prefix."settings SET value='".time()."' WHERE variable='memberlist_updated'");
		$db->sql_query("UPDATE ".$db_prefix."settings SET value='$new_id' WHERE variable='latestMember'");
		$db->sql_query("UPDATE ".$db_prefix."settings SET value=value+1 WHERE variable='totalMembers'");
		$db->sql_query("UPDATE ".$db_prefix."settings SET value='$user_name' WHERE variable='latestRealName'");
		return true;
	}

	function check_user($user_name, $user_pass) {
		global $db, $db_prefix;
		$result = $db->sql_query("SELECT passwd, ID_MEMBER, emailAddress, memberName, passwordSalt FROM ".$db_prefix."members WHERE LOWER(memberName) = '".strtolower($user_name)."'");
		$info = $db->sql_fetchrow($result);
		if ($db->sql_numrows($result) == 1) {
			if ($info['passwd'] == sha1(strtolower($info['memberName']).un_htmlspecialchars(stripslashes($user_pass))) OR $info['passwd'] == md5(md5($user_pass).$info['passwordSalt'])) {
				new_site_user($user_name, $user_pass, $info['emailAddress']);
			} else {
				return false;
			}
		} else {
			die('cad');
			return false;
		}
	}

	function new_pass($user_name, $newpass, $user_email) {
		global $db, $table_prefix, $conf;
		$result = $db->sql_query("SELECT ID_MEMBER FROM ".$table_prefix."members WHERE LOWER(memberName)='" . strtolower($user_name) . "' AND emailAddress='" . $user_email . "'");
		if ($db->sql_numrows($result) == 1) {
			$info = $db->sql_fetchrow($result);
			$salt = substr(md5(rand()), 0, 4);
			$passwd = md5(md5($newpass).$salt);
			$db->sql_query("UPDATE ".$table_prefix."members SET passwd='".passwd."', passwordSalt='".$salt."' WHERE ID_MEMBER='".$info['ID_MEMBER']."'");
		} else {
			return false;
		}
	}

	function un_htmlspecialchars($string) {
		return strtr($string, array_flip(get_html_translation_table(HTML_SPECIALCHARS, ENT_QUOTES)) + array('&#039;' => '\'', '&nbsp;' => ' '));
	}
}

function new_site_user($user_name, $user_pass, $user_email) {
	global $db, $prefix, $conf;
	$user_password = htmlspecialchars(stripslashes(md5($user_pass)));
	$user_name = text_filter($user_name);
	$user_email = text_filter($user_email);
	$result = $db->sql_query("SELECT * FROM ".$prefix."_users WHERE user_name='$user_name' OR user_email='$user_email'");
	if ($db->sql_numrows($result)) return false;
	$result = $db->sql_query("SELECT * FROM ".$prefix."_users_temp WHERE user_name='$user_name' OR user_email='$user_email'");
	if ($db->sql_numrows($result)) return false;
	$db->sql_query("INSERT INTO ".$prefix."_users (user_id, user_name, user_email, user_password, user_avatar, user_regdate, user_lang) VALUES (NULL, '$user_name', '$user_email', '$user_password', 'default/00.gif',  now(), '".$conf['language']."')");
	list($last_id, $user_storynum, $user_blockon, $user_theme) = $db->sql_fetchrow($db->sql_query("SELECT user_id, user_storynum, user_blockon, user_theme FROM ".$prefix."_users ORDER by user_id DESC LIMIT 1") ); 
	setCookies($last_id, $user_name, $user_password, $user_storynum, $user_blockon, $user_theme);
	$uip = getip();
	$uagent = getagent();
	$db->sql_query("DELETE FROM ".$prefix."_session WHERE uname='$uip' AND guest='0'");
	$db->sql_query("UPDATE ".$prefix."_users SET user_last_ip='$uip', user_lastvisit=now(), user_agent='$uagent' WHERE user_name='$user_name'");
	log_in($user_name,$user_pass);
	return true;
}
?>