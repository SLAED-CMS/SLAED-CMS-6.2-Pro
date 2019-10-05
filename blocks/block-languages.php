<?php
# Copyright © 2005 - 2014 SLAED
# Website: http://www.slaed.net

if (!defined("BLOCK_FILE")) {
	header("Location: ../index.php");
	exit;
}

global $conf, $currentlang;
$handle = opendir('language');
while (false !== ($file = readdir($handle))) {
	if (preg_match("/^lang\-(.+)\.php/", $file, $matches)) {
		$langlist[] = $matches[1];
	}
}
closedir($handle);
sort($langlist);
if ($conf['flags'] == 1) {
	$content = "<div style=\"text-align: center;\">";
	for ($i = 0; $i < count($langlist); $i++) {
		if ($langlist[$i] != "") {
			$altlang = deflang($langlist[$i]);
			$content .= "<a href=\"index.php?newlang=".$langlist[$i]."\"><img src=\"".img_find("language/".$langlist[$i].".png")."\" alt=\"".$altlang."\" title=\"".$altlang."\"></a>";
		}
	}
	$content .= "</div>";
} else {
	$content = "<form action=\"index.php\" method=\"get\"><select name=\"newlanguage\" OnChange=\"top.location.href=this.options[this.selectedIndex].value\" style=\"width: 250px;\" class=\"sl_field\">";
	for ($i=0; $i < count($langlist); $i++) {
		if ($langlist[$i] != "") {
			$content .= "<option value=\"index.php?newlang=".$langlist[$i]."\" ";
			if ($langlist[$i] == $currentlang) $content .= " selected";
			$content .= ">".deflang($langlist[$i])."</option>\n";
		}
	}
	$content .= "</select></form>";
}
?>