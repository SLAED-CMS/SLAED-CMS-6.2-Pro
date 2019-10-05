<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined('ADMIN_FILE') || !is_admin_modul('contact')) die('Illegal file access');

function contact_navi() {
	panel();
	$narg = func_get_args();
	$ops = array('contact_conf', 'contact_info');
	$lang = array(_PREFERENCES, _INFO);
	return navi_gen(_FEEDBACK, 'contact.png', '', $ops, $lang, '', '', $narg[0], $narg[1], $narg[2], $narg[3]);
}

function contact_conf() {
	global $admin_file;
	head();
	$cont = contact_navi(0, 0, 0, 0);
	include('config/config_contact.php');
	$permtest = end_chmod('config/config_contact.php', 666);
	if ($permtest) $cont .= tpl_warn('warn', $permtest, '', '', 'warn');
	$cont .= tpl_eval('open');
	$cont .= '<form name="post" action="'.$admin_file.'.php" method="post"><table class="sl_table_form">'
	.'<tr><td>'._CONTACTINFO.':</td><td>'.textarea('1', 'info', $confco['info'], 'all', '10', _CONTACTINFO, '0').'</td></tr>'
	.'<tr><td>'._CONTACTALL.'</td><td>'.radio_form($confco['admins'], 'admins').'</td></tr>'
	.'<tr><td colspan="2" class="sl_center"><input type="hidden" name="op" value="contact_conf_save"><input type="submit" value="'._SAVECHANGES.'" class="sl_but_blue"></td></tr></table></form>';
	$cont .= tpl_eval('close', '');
	echo $cont;
	foot();
}

function contact_conf_save() {
	global $admin_file;
	$xinfo = "<<<HTML\n".save_text($_POST['info'])."\nHTML";
	$cont = array('info' => $xinfo, 'admins' => $_POST['admins']);
	save_conf('config/config_contact.php', $cont, '', 'confco');
	header('Location: '.$admin_file.'.php?op=contact_conf');
}

function contact_info() {
	head();
	echo contact_navi(0, 1, 0, 0).'<div id="repadm_info">'.adm_info(1, 'contact', 0).'</div>';
	foot();
}

switch($op) {
	case 'contact_conf':
	contact_conf();
	break;
	
	case 'contact_conf_save':
	contact_conf_save();
	break;
	
	case 'contact_info':
	contact_info();
	break;
}
?>