<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("BLOCK_FILE")) {
	header("Location: ../index.php");
	exit;
}

global $prefix, $db;
list($count) = $db->sql_fetchrow($db->sql_query("SELECT Count(jokeid) FROM ".$prefix."_jokes WHERE date <= now() AND status != '0'"));
$random = mt_rand(0, $count - 1);
$result = $db->sql_query("SELECT joke FROM ".$prefix."_jokes ORDER BY jokeid DESC LIMIT ".$random.", 1");
list($joke) = $db->sql_fetchrow($result);
$content = $joke;
?>