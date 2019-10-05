<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("MODULE_FILE")) {
	header("Location: ../../index.php");
	exit;
}
get_lang($conf['name']);
include("config/config_rss.php");

function info() {
	global $db, $prefix, $conf, $confrs;
	$url = (isset($_POST['url'])) ? url_filter($_POST['url']) : "";
	$mod = (isset($_POST['mod'])) ? analyze($_POST['mod']) : "news";
	$cat = (isset($_POST['cat'])) ? intval($_POST['cat']) : "";
	$num = (isset($_POST['num'])) ? intval($_POST['num']) : "";
	
	$rssmod = ($mod) ? "&amp;name=".$mod : "";
	$rsscat = ($cat) ? "&amp;cat=".$cat : "";
	$rssnum = ($num) ? "&amp;num=".$num : "";
	$rsslink = $conf['homeurl']."/index.php?go=rss".$rssmod.$rsscat.$rssnum;
	
	$content = "<form action=\"index.php?name=".$conf['name']."\" method=\"post\"><table class=\"sl_table_form\">"
	."<tr><td colspan=\"2\">"._RSS_INFO_TEXT."</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><hr></td></tr>"
	."<tr><td>"._RSS_INFO_TIP.":</td><td>"
	."<select name=\"mod\" OnChange=\"submit()\" class=\"sl_field ".$conf['style']."\">";
	$mods = array("faq" => _FAQ, "files" => _FILES, "links" => _LINKS, "media" => _MEDIA, "news" => _NEWS, "pages" => _PAGES, "shop" => _SHOP);
	foreach ($mods as $key => $val) {
		if (is_active($key)) {
			$sel = ($key == $mod) ? " selected" : "";
			$content .= "<option value=\"".$key."\"".$sel.">".$val."</option>";
		}
	}
	$content .= "</select></td></tr>"
	."<tr><td>"._CATEGORIES.":</td><td>".getcat($mod, $cat, "cat", $conf['style'], "<option value=\"\" selected>"._RSS_INFO_ALL."</option>")."</td></tr>"
	."<tr><td>"._RSS_INFO_MENG.":</td><td>"
	."<select name=\"num\" class=\"sl_field ".$conf['style']."\">";
	$lim = 1;
	while ($lim <= $confrs['max']) {
		$rsslim = ($num) ? $num : $confrs['min'];
		$sel = ($lim == $rsslim) ? " selected" : "";
		$content .= "<option value=\"".$lim."\"".$sel.">"._RSS_INFO_MENG." - ".$lim."</option>";
		$lim++;
	}
	$content .= "</select></td></tr>"
	."<tr><td>"._CODE.":</td><td><textarea cols=\"45\" rows=\"3\" OnClick=\"this.select()\" class=\"sl_field ".$conf['style']."\">".$rsslink."</textarea></td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"info\"><input type=\"submit\" value=\""._RSS_INFO_CODE."\" class=\"sl_but_blue\"></td></tr></table></form>";
	
	head($conf['defis']." "._RSS, _RSS_INFO_TEXT);
	$cont = tpl_eval("title", _RSS);
	$cont .= tpl_eval("open").$content.tpl_eval("close");
	if ($confrs['use'] == 1) {
		$link = ($url) ? $url : "http://";
		$content = "<hr><form action=\"index.php?name=".$conf['name']."\" method=\"post\"><table class=\"sl_table_form\"><tr><td>"._SELECTASITE.":</td><td><select name=\"url\" class=\"sl_field ".$conf['style']."\">".rss_select()."</select></td><td><input type=\"submit\" value=\""._OK."\" class=\"sl_but_blue\"></td></tr></table></form>"
		."<form action=\"index.php?name=".$conf['name']."\" method=\"post\"><table class=\"sl_table_form\"><tr><td>"._ORTYPEURL.":</td><td><input type=\"url\" name=\"url\" value=\"".$link."\" maxlength=\"200\" class=\"sl_field ".$conf['style']."\" placeholder=\""._ORTYPEURL."\"></td><td><input type=\"submit\" value=\""._OK."\" class=\"sl_but_blue\"></td></tr></table></form>";
		$content .= rss_read($url, "");
		$cont .= tpl_eval("open").$content.tpl_eval("close");
	}
	echo $cont;
	foot();
}

switch($op) {
	default:
	info();
	break;
}
?>