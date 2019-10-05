<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("BLOCK_FILE")) {
	header("Location: ../index.php");
	exit;
}

global $prefix, $db, $conf;
$mods_1 = "<tr><td><a href=\"index.php\" title=\""._HOME."\" class=\"sl_modul\">"._HOME."</a></td></tr>";
$mods_1 .= ($conf['forum']) ? "<tr><td><a href=\"forum/index.php\" title=\""._FORUM."\" class=\"sl_modul\">"._FORUM."</a></td></tr>" : "";
$mods_2 = ""; $mods_3 = ""; $mods_4 = "";
$result = $db->sql_query("SELECT title, view, active, inmenu FROM ".$prefix."_modules ORDER BY title ASC");
while (list($m_title, $view, $active, $inmenu) = $db->sql_fetchrow($result)) {
	$m_title2 = deflmconst($m_title);
	if ($inmenu == 1 && $active == 1 && $view != 2) {
		if ((is_moder($m_title) && $view == 2) || $view != 2) $mods_1 .= "<tr><td><a href=\"index.php?name=".$m_title."\" title=\"".$m_title2."\" class=\"sl_modul\">".$m_title2."</a></td></tr>";
	} elseif (is_moder($m_title) && $inmenu == 0 && $active == 1) {
		$mods_2 .= "<tr><td><a href=\"index.php?name=".$m_title."\" class=\"sl_modul\">".$m_title2."</a></td></tr>";
	} elseif (is_moder($m_title) && $active == 0) {
		$mods_3 .= "<tr><td><a href=\"index.php?name=".$m_title."\" class=\"sl_modul\">".$m_title2."</a></td></tr>";
	} elseif (is_moder($m_title) && $view == 2) {
		$mods_4 .= "<tr><td><a href=\"index.php?name=".$m_title."\" class=\"sl_modul\">".$m_title2."</a></td></tr>";
	}
}
$mods_2 = ($mods_2) ? "<tr><td><b>"._INVISIBLEMODULES."</b><br>"._ACTIVEBUTNOTSEE."</td></tr>".$mods_2 : "";
$mods_3 = ($mods_3) ? "<tr><td><b>"._NOACTIVEMODULES."</b><br>"._FORADMINTESTS."</td></tr>".$mods_3 : "";
$mods_4 = ($mods_4) ? "<tr><td><b>"._ADMINS."</b><br>"._FORADMINTESTS."</td></tr>".$mods_4 : "";
$content = "<table class=\"sl_table_block\">".$mods_1.$mods_2.$mods_3.$mods_4."</table>";
?>