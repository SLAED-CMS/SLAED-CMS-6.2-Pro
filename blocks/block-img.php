<?php
# Copyright © 2005 - 2014 SLAED
# Website: http://www.slaed.net

if (!defined("BLOCK_FILE")) {
	header("Location: ../index.php");
	exit;
}

$path = "uploads/screens/thumb";
$dir = opendir($path);
while (false !== ($file = readdir($dir))) {
	if ($file != "." && $file != ".." && $file != "index.html" && !is_dir($path."/".$file)) $ban[] = $file;
}
closedir($dir);

$content = "<div id=\"wrap\" style=\"vertical-align: middle;\">";
$sarray = array_rand($ban, count($ban));
shuffle($sarray);
foreach ($sarray as $val) {
	$content .= (!$s) ? "<a rel=\"group\" title=\"Лучшие сайты системы\" href=\"uploads/screens/".$ban[$val]."\" class=\"screens\"><img src=\"uploads/screens/thumb/".$ban[$val]."\"></a>" : "<a rel=\"group\" title=\"Лучшие сайты системы\" href=\"uploads/screens/".$ban[$val]."\" class=\"screens\"></a>";
	$s++;
}
$content .= "</div>";
?>