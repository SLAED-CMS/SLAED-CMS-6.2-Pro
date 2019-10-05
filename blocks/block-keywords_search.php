<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("BLOCK_FILE")) {
	header("Location: ../index.php");
	exit;
}

global $conf;
$words = $conf['keywords'];
if (is_array($words)) {
	$kwords = "";
	foreach ($words as $val) {
		if ($val != "") $kwords .= "<a href=\"index.php?name=search&amp;word=".urlencode($val)."\" title=\"".$val."\">".$val."</a> ";
	}
	$content = $kwords;
}
?>