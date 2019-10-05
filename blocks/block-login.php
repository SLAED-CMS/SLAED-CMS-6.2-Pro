<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined('BLOCK_FILE')) {
	header('Location: ../index.php');
	exit;
}

global $conf, $confu;
$content = "<form action=\"index.php?name=account\" method=\"post\"><table class=\"sl_table_block\">"
."<tr><td>"._NICKNAME.":</td><td><input type=\"text\" name=\"user_name\" maxlength=\"25\" class=\"sl_field sl_bl_field\" placeholder=\""._NICKNAME."\" required></td></tr>"
."<tr><td>"._PASSWORD.":</td><td><input type=\"password\" name=\"user_password\" maxlength=\"25\" class=\"sl_field sl_bl_field\" placeholder=\""._PASSWORD."\" required></td></tr>";
if (extension_loaded("gd") && ($conf['gfx_chk'] == 2 || $conf['gfx_chk'] == 4 || $conf['gfx_chk'] == 5 || $conf['gfx_chk'] == 7)) $content .= get_captcha();
$content .= "<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"refer\" value=\"1\"><input type=\"hidden\" name=\"op\" value=\"login\"><input type=\"submit\" value=\""._LOGIN."\" class=\"sl_but_blue\"></td></tr>";
$content .= ($confu['network']) ? "<tr><td colspan=\"2\" class=\"sl_center\">"._LOGINNETWORK."</td></tr><tr><td colspan=\"2\" class=\"sl_center\">".make_network_code()."</td></tr>" : "";
$content .= "</table></form>";
?>