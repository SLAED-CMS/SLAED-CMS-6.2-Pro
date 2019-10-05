<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("BLOCK_FILE")) {
	header("Location: ../index.php");
	exit;
}

$content = "<form action=\"index.php?name=search\" method=\"post\"><table class=\"sl_table_block\"><tr><td><input type=\"text\" name=\"word\" maxlength=\"100\" class=\"sl_field\" placeholder=\""._SEARCH."\" required></td><td><input type=\"submit\" title=\""._SEARCH."\" value=\""._OK."\" class=\"sl_but_blue\"></td></tr></table></form>";
?>