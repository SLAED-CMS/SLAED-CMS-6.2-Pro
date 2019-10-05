<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined('BLOCK_FILE')) {
	header('Location: ../index.php');
	exit;
}

global $prefix, $db, $currentlang, $conf, $confv;
$querylang = ($conf['multilingual'] == 1) ? "(language = '".$currentlang."' OR language = '') AND modul = '' AND date <= now()" : "modul = '' AND date <= now()";
if ($confv['block'] <= 1) {
	$querylang = ($confv['block'] == 1) ? $querylang." AND enddate <= now() AND status = '1'" : $querylang." AND enddate >= now()";
	$result = $db->sql_query("SELECT id FROM ".$prefix."_voting WHERE ".$querylang." ORDER BY id DESC LIMIT 0, 1");
	list($id) = $db->sql_fetchrow($result);
	$bid = $id;
} elseif ($confv['block'] >= 2) {
	$querylang = ($confv['block'] == 3) ? $querylang." AND enddate <= now() AND status = '1'" : $querylang." AND enddate >= now()";
	$result = $db->sql_query("SELECT id FROM ".$prefix."_voting WHERE ".$querylang);
	while (list($id) = $db->sql_fetchrow($result)) $input[] = $id;
	$rkey = array_rand($input, 1);
	$bid = $input[$rkey];
}
$content = ($bid) ? '<div id="repblockvoting">'.avoting_view($bid, 'blockvoting').'</div>' : '';
?>