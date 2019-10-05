<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("BLOCK_FILE")) {
	header("Location: ../index.php");
	exit;
}

$content = "<p style=\"text-align: center;\">
	<object width=\"150\" height=\"68\" id=\"mju\">
	<param name=\"allowScriptAccess\" value=\"sameDomain\">
	<param name=\"swLiveConnect\" value=\"true\">
	<param name=\"movie\" value=\"mju.swf\">
	<param name=\"flashvars\" value=\"plugins/radio/playlist=playlist.mpl&amp;auto_run=false&amp;repeat_one=false&amp;shuffle=false\">
	<param name=\"loop\" value=\"false\">
	<param name=\"menu\" value=\"false\">
	<param name=\"quality\" value=\"high\">
	<param name=\"wmode\" value=\"transparent\">
	<embed src=\"plugins/radio/mju.swf\" flashvars=\"playlist=plugins/radio/playlist.mpl&amp;auto_run=false&amp;repeat_one=false&amp;shuffle=false\" loop=\"false\" menu=\"false\" quality=\"high\" wmode=\"transparent\" bgcolor=\"#ffffff\" width=\"150\" height=\"68\" name=\"mju\" allowScriptAccess=\"sameDomain\" swLiveConnect=\"true\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\">
	</object>
</p><p style=\"text-align: center;\">
	<input type=\"button\" value=\"Открыть в новом окне\" class= \"sl_but_blue\" OnClick=\"OpenWindow('plugins/radio/radio.html', 'Radio', '200', '100');\">
</p>";
?>