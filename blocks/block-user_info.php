<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined('BLOCK_FILE')) {
	header('Location: ../index.php');
	exit;
}

global $prefix, $db, $currentlang, $conf, $confu, $conffav, $confpr;
if (is_user()) {
	$userinfo = getusrinfo();
	$uname = $userinfo['user_name'];
	$user_id = intval($userinfo['user_id']);
	$user_avatar = (file_exists($confu['adirectory']."/".$userinfo['user_avatar'])) ? $userinfo['user_avatar'] : "default/00.gif";
	$content = "<span class=\"sl_pos_center\"><a title=\"".$uname."\" class=\"sl_avatar\" style=\"background-image: url(".$confu['adirectory']."/".$user_avatar.");\"></a><br><b>"._HELLO.",<br>".$uname."</b></span>";
	if ($confpr['act']) {
		list($prin) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_privat WHERE uidin='".$user_id."' AND status = '0'"));
		list($prout) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_privat WHERE uidout='".$user_id."' AND status = '0'"));
		if ($prin > 0) {
			$content .= "<audio src=\"sound/privat-".$currentlang.".mp3\" autoplay=\"autoplay\" preload=\"auto\"></audio>
			<script src=\"plugins/jquery/tinycon.js\"></script>
			<script>
			$(document).ready(function() {
				setInterval(function() { Tinycon.setBubble(".$prin."); }, 1000);
				setInterval(function() { Tinycon.setBubble(false); }, 2000);
			});
			</script>";
		}
		$content .= "<hr><table class=\"sl_table_block\">
		<tr><td colspan=\"2\"><a href=\"index.php?name=account&amp;op=privat\" title=\""._PRIVAT."\">"._PRIVAT."</a></td></tr>
		<tr><td>"._PRINNO.":</td><td class=\"sl_right\">".$prin."</td></tr>
		<tr><td>"._PROUTNO.":</td><td class=\"sl_right\">".$prout."</td></tr>
		</table>";
	}
	$content .= "<hr><table class=\"sl_table_block\">";
	$content .= ($conffav['favact']) ? "<tr><td><a href=\"index.php?name=account&amp;op=favorites\" title=\""._FAVORITES."\">"._FAVORITES."</a></td></tr>": "";
	$content .= "<tr><td><a href=\"index.php?name=account&amp;op=edithome\" title=\""._CHANGE."\">"._CHANGE."</a></td></tr>
	<tr><td><a href=\"index.php?name=account&amp;op=logout&amp;refer=1\" title=\""._LOGOUT."\">"._LOGOUT."</a></td></tr>
	</table>";
} else {
	$content = "<span class=\"sl_pos_center\"><a title=\"".$confu['anonym']."\" class=\"sl_avatar\" style=\"background-image: url(".$confu['adirectory']."/default/0.gif);\"></a><br><b>"._WELCOMETO.",<br>".$confu['anonym']."</b></span>
	<hr>
	<form action=\"index.php?name=account\" method=\"post\">
	<table class=\"sl_table_block\">
	<tr><td><a href=\"index.php?name=account&amp;op=newuser\" title=\""._BREG."\">"._BREG."</a></td></tr>
	<tr><td><a href=\"index.php?name=account&amp;op=passlost\" title=\""._PASSFOR."\">"._PASSFOR."</a></td></tr>
	</table>
	<hr>
	<table class=\"sl_table_block\">
	<tr><td>"._NICKNAME.":</td><td><input type=\"text\" name=\"user_name\" maxlength=\"25\" class=\"sl_field sl_bl_field\" placeholder=\""._NICKNAME."\" required></td></tr>
	<tr><td>"._PASSWORD.":</td><td><input type=\"password\" name=\"user_password\" maxlength=\"25\" class=\"sl_field sl_bl_field\" placeholder=\""._PASSWORD."\" required></td></tr>";
	if (extension_loaded("gd") && ($conf['gfx_chk'] == 2 || $conf['gfx_chk'] == 4 || $conf['gfx_chk'] == 5 || $conf['gfx_chk'] == 7)) $content .= get_captcha();
	$content .= "<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"refer\" value=\"1\"><input type=\"hidden\" name=\"op\" value=\"login\"><input type=\"submit\" value=\""._LOGIN."\" class=\"sl_but_blue\"></td></tr>";
	$content .= ($confu['network']) ? "<tr><td colspan=\"2\" class=\"sl_center\">"._LOGINNETWORK."</td></tr><tr><td colspan=\"2\" class=\"sl_center\">".make_network_code()."</td></tr>" : "";
	$content .= "</table></form>";
}
if ($conf['session']) $content .= "<div id=\"repsinfo\">".user_sinfo(1)."</div>";
?>