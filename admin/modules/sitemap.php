<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined('ADMIN_FILE') || !is_admin_god()) die('Illegal file access');
include('config/config_sitemap.php');

function sitemap_navi() {
	panel();
	$narg = func_get_args();
	$ops = array('sitemap', 'sitemap_xsl', 'sitemap_conf', 'sitemap_info');
	$lang = array(_HOME, _TEMPLATE, _PREFERENCES, _INFO);
	return navi_gen(_SITEMAP, 'sitemap.png', '', $ops, $lang, '', '', $narg[0], $narg[1], $narg[2], $narg[3]);
}

function sitemap() {
	global $admin_file, $conf;
	head();
	$cont = sitemap_navi(0, 0, 0, 0);
	$file = 'sitemap.xml';
	$conts = file_get_contents($file);
	$permtest = end_chmod($file, 666);
	if ($permtest) $cont .= tpl_warn('warn', $permtest, '', '', 'warn');
	$f = $asize = 0;
	$acont = '';
	foreach (glob('sitemap*.xml*') as $file) {
		$permtest = end_chmod($file, 666);
		if ($permtest) $cont .= tpl_warn('warn', $permtest, '', '', 'warn');
		$handle = fopen($file, 'rb');
		$n = 0;
		while (!feof($handle)) {
			$bufer = fread($handle, 1048576);
			$n += substr_count($bufer, '</loc>');
		}
		fclose($handle);
		$size = filesize($file);
		$acont .= _FILE.': '.$file .'<br>'._DATE.': '.date(_TIMESTRING, filemtime($file)).'<br>'._SIZE.': '.files_size($size).'<br>'._URLS.': '.$n.'<br><br>';
		$f++;
		$asize += $size;
	}
	$cont .= tpl_warn('warn', _SITEMAP.': <a href="'.$conf['homeurl'].'/'.$file.'" target="_blank" title="'._SITEMAP.'">'.$conf['homeurl'].'/'.$file.'</a><br><br>'.$acont._FILE_M.': '.$f.'<br>'._FILE_S.': '.files_size($asize), '', '', 'info');
	$cont .= tpl_eval('open');
	$cont .= '<form action="'.$admin_file.'.php" method="post"><table class="sl_table_edit"><tr><td>'.textarea_code('code', '', 'sl_form', 'application/xml', str_replace('&', '&amp;', $conts)).'</td></tr>'
	.'<tr><td class="sl_center"><input type="hidden" name="op" value="sitemap_add"><input type="submit" value="'._UPDATE.'" class="sl_but_blue"></td></tr></table></form>';
	$cont .= tpl_eval('close', '');
	echo $cont;
	foot();
}

function sitemap_xsl() {
	global $admin_file;
	head();
	$cont = sitemap_navi(0, 1, 0, 0);
	$file = 'config/sitemap/sitemap.xsl';
	$conts = file_get_contents($file);
	$permtest = end_chmod($file, 666);
	if ($permtest) $cont .= tpl_warn('warn', $permtest, '', '', 'warn');
	$cont .= tpl_warn('warn', sprintf(_XSL_INFO, $file), '', '', 'info');
	$cont .= tpl_eval('open');
	$cont .= '<form action="'.$admin_file.'.php" method="post"><table class="sl_table_edit"><tr><td>'.textarea_code('code', 'template', 'sl_form', 'application/xml', $conts).'</td></tr>'
	.'<tr><td class="sl_center"><input type="hidden" name="op" value="sitemap_xsl_save"><input type="submit" value="'._SAVE.'" class="sl_but_blue"></td></tr></table></form>';
	$cont .= tpl_eval('close', '');
	echo $cont;
	foot();
}

function sitemap_conf() {
	global $admin_file, $confma;
	head();
	$cont = sitemap_navi(0, 2, 0, 0);
	$permtest = end_chmod('config/config_sitemap.php', 666);
	if ($permtest) $cont .= tpl_warn('warn', $permtest, '', '', 'warn');
	$cont .= tpl_eval('open');
	$cont .= '<form name="post" action="'.$admin_file.'.php" method="post"><table class="sl_table_conf">'
	.'<tr><td>'._MODULES.':<div class=\"sl_small\">'._CTRLINFO.'</div></td><td>'.modul('mod', 'sl_conf', $confma['mod'], 1).'</td></tr>';
	$frs = array('0' => _NO, 'always' => _ALWAYS, 'hourly' => _HOURLY, 'daily' => _DAILY, 'weekly' => _WEEKLY, 'monthly' => _MONTHLY, 'yearly' => _YEARLY, 'never' => _NEVER);
	$cont_h = $cont_m = $cont_c = $cont_p = '';
	foreach ($frs as $key => $val) {
		$sel_h = ($confma['fr_h'] == $key) ? ' selected' : '';
		$cont_h .= '<option value="'.$key.'"'.$sel_h.'>'.$val.'</option>';
		$sel_m = ($confma['fr_m'] == $key) ? ' selected' : '';
		$cont_m .= '<option value="'.$key.'"'.$sel_m.'>'.$val.'</option>';
		$sel_c = ($confma['fr_c'] == $key) ? ' selected' : '';
		$cont_c .= '<option value="'.$key.'"'.$sel_c.'>'.$val.'</option>';
		$sel_p = ($confma['fr_p'] == $key) ? ' selected' : '';
		$cont_p .= '<option value="'.$key.'"'.$sel_p.'>'.$val.'</option>';
	}
	$cont .= '<tr><td>'._MAP_FR_H.':<div class="sl_small">'._INFO_NO.'</div></td><td><select name="fr_h" class="sl_conf">'.$cont_h.'</select></td></tr>'
	.'<tr><td>'._MAP_FR_M.':<div class="sl_small">'._INFO_NO.'</div></td><td><select name="fr_m" class="sl_conf">'.$cont_m.'</select></td></tr>'
	.'<tr><td>'._MAP_FR_C.':<div class="sl_small">'._INFO_NO.'</div></td><td><select name="fr_c" class="sl_conf">'.$cont_c.'</select></td></tr>'
	.'<tr><td>'._MAP_FR_P.':<div class="sl_small">'._INFO_NO.'</div></td><td><select name="fr_p" class="sl_conf">'.$cont_p.'</select></td></tr>';
	$prs = array('1.0', '0.9', '0.8', '0.7', '0.6', '0.5', '0.4', '0.3', '0.2', '0.1', '0');
	$cont_h = $cont_m = $cont_c = $cont_p = '';
	foreach ($prs as $val) {
		$sel_h = ($confma['pr_h'] == $val) ? ' selected' : '';
		$cont_h .= '<option value="'.$val.'"'.$sel_h.'>'.$val.'</option>';
		$sel_m = ($confma['pr_m'] == $val) ? ' selected' : '';
		$cont_m .= '<option value="'.$val.'"'.$sel_m.'>'.$val.'</option>';
		$sel_c = ($confma['pr_c'] == $val) ? ' selected' : '';
		$cont_c .= '<option value="'.$val.'"'.$sel_c.'>'.$val.'</option>';
		$sel_p = ($confma['pr_p'] == $val) ? ' selected' : '';
		$cont_p .= '<option value="'.$val.'"'.$sel_p.'>'.$val.'</option>';
	}
	$cont .= '<tr><td>'._MAP_PR_H.':<div class="sl_small">'._INFO_NULL.'</div></td><td><select name="pr_h" class="sl_conf">'.$cont_h.'</select></td></tr>'
	.'<tr><td>'._MAP_PR_M.':<div class="sl_small">'._INFO_NULL.'</div></td><td><select name="pr_m" class="sl_conf">'.$cont_m.'</select></td></tr>'
	.'<tr><td>'._MAP_PR_C.':<div class="sl_small">'._INFO_NULL.'</div></td><td><select name="pr_c" class="sl_conf">'.$cont_c.'</select></td></tr>'
	.'<tr><td>'._MAP_PR_P.':<div class="sl_small">'._INFO_NULL.'</div></td><td><select name="pr_p" class="sl_conf">'.$cont_p.'</select></td></tr>'
	.'<tr><td>'._MAP_AUTO_T.':</td><td><input type="number" name="auto_t" value="'.intval($confma['auto_t'] / 3600).'" class="sl_conf" placeholder="'._MAP_AUTO_T.'" required></td></tr>'
	.'<tr><td>'._MAP_AUTO.'</td><td>'.radio_form($confma['auto'], 'auto').'</td></tr>'
	.'<tr><td>'._MAP_DAT_H.'</td><td>'.radio_form($confma['dat_h'], 'dat_h').'</td></tr>'
	.'<tr><td>'._MAP_DAT_M.'</td><td>'.radio_form($confma['dat_m'], 'dat_m').'</td></tr>'
	.'<tr><td>'._MAP_DAT_C.'</td><td>'.radio_form($confma['dat_c'], 'dat_c').'</td></tr>'
	.'<tr><td>'._MAP_DAT_P.'</td><td>'.radio_form($confma['dat_p'], 'dat_p').'</td></tr>'
	.'<tr><td>'._MAP_GEN_H.'</td><td>'.radio_form($confma['gen_h'], 'gen_h').'</td></tr>'
	.'<tr><td>'._MAP_GEN_M.'</td><td>'.radio_form($confma['gen_m'], 'gen_m').'</td></tr>'
	.'<tr><td>'._MAP_GEN_C.'</td><td>'.radio_form($confma['gen_c'], 'gen_c').'</td></tr>'
	.'<tr><td>'._MAP_GEN_P.'</td><td>'.radio_form($confma['gen_p'], 'gen_p').'</td></tr>'
	.'<tr><td>'._MAP_XSL.'</td><td>'.radio_form($confma['xsl'], 'xsl').'</td></tr>'
	.'<tr><td>'._MAP_SITE.'</td><td>'.radio_form($confma['txt'], 'txt').'</td></tr>'
	.'<tr><td colspan="2" class="sl_center"><input type="hidden" name="op" value="sitemap_save"><input type="submit" value="'._SAVECHANGES.'" class="sl_but_blue"></td></tr></table></form>';
	$cont .= tpl_eval('close', '');
	echo $cont;
	foot();
}

function sitemap_save() {
	global $admin_file, $confma;
	$mod = empty($_POST['mod'][0]) ? '0' : implode(',', $_POST['mod']);
	$auto_t = intval($_POST['auto_t']) ? $_POST['auto_t'] * 3600 : 3600;
	$cont = array('mod' => $mod, 'fr_h' => $_POST['fr_h'], 'fr_m' => $_POST['fr_m'], 'fr_c' => $_POST['fr_c'], 'fr_p' => $_POST['fr_p'], 'pr_h' => $_POST['pr_h'], 'pr_m' => $_POST['pr_m'], 'pr_c' => $_POST['pr_c'], 'pr_p' => $_POST['pr_p'], 'auto_t' => $auto_t, 'auto' => $_POST['auto'], 'dat_h' => $_POST['dat_h'], 'dat_m' => $_POST['dat_m'], 'dat_c' => $_POST['dat_c'], 'dat_p' => $_POST['dat_p'], 'gen_h' => $_POST['gen_h'], 'gen_m' => $_POST['gen_m'], 'gen_c' => $_POST['gen_c'], 'gen_p' => $_POST['gen_p'], 'xsl' => $_POST['xsl'], 'txt' => $_POST['txt']);
	save_conf('config/config_sitemap.php', $cont, '', 'confma');
	header('Location: '.$admin_file.'.php?op=sitemap_conf');
}

function sitemap_info() {
	head();
	echo sitemap_navi(0, 3, 0, 0).'<div id="repadm_info">'.adm_info(1, 0, 'sitemap').'</div>';
	foot();
}

switch($op) {
	case 'sitemap':
	sitemap();
	break;
	
	case 'sitemap_add':
	doSitemap();
	header('Location: '.$admin_file.'.php?op=sitemap');
	break;
	
	case 'sitemap_xsl':
	sitemap_xsl();
	break;
	
	case 'sitemap_xsl_save':
	$template = stripslashes($_POST['template']);
	if ($template) file_put_contents('config/sitemap/sitemap.xsl', $template);
	header('Location: '.$admin_file.'.php?op=sitemap_xsl');
	break;
	
	case 'sitemap_conf':
	sitemap_conf();
	break;
	
	case 'sitemap_save':
	sitemap_save();
	break;
	
	case 'sitemap_info':
	sitemap_info();
	break;
}
?>