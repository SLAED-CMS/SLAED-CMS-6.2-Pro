<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined('ADMIN_FILE') || !is_admin_god()) die('Illegal file access');

function blocks_navi() {
	panel();
	$narg = func_get_args();
	$ops = array('blocks_show', 'blocks_new', 'blocks_file', 'blocks_file_edit', 'blocks_fix', 'blocks_info');
	$lang = array(_HOME, _ADDNEWBLOCK, _ADDNEWFILEBLOCK, _EDITBLOCK, _FIX, _INFO);
	return navi_gen(_BLOCKS, 'blocks.png', '', $ops, $lang, '', '', $narg[0], $narg[1], $narg[2], $narg[3]);
}

function blocks_show() {
	head();
	echo blocks_navi(0, 0, 0, 0).tpl_eval('open').'<div id="repajax_block">'.ajax_block().'</div>'.tpl_eval('close', '');
	foot();
}

function blocks_new() {
	global $prefix, $db, $currentlang, $conf, $admin_file;
	head();
	$cont = blocks_navi(0, 1, 0, 0);
	$cont .= tpl_eval('open');
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\">"
	."<table class=\"sl_table_form\">"
	."<tr><td>"._TITLE.":<div class=\"sl_small\">"._ADDCONST."</div></td><td><input type=\"text\" name=\"title\" maxlength=\"60\" class=\"sl_form\" placeholder=\""._TITLE."\" required></td></tr>"
	."<tr><td>"._RSSFILE.":</td><td><input type=\"text\" name=\"url\" class=\"sl_form\" placeholder=\""._RSSFILE."\"></td></tr>"
	."<tr><td><div class=\"sl_small\">"._RSSLINESINFO." "._RSSINFO."</div></td><td><select name=\"headline\" class=\"sl_form\"><option value=\"0\" selected>"._CUSTOM."</option>".rss_select()."</select></td></tr>"
	."<tr><td>"._REFRESHTIME.":<div class=\"sl_small\">"._REFINFO."</div></td><td><select name=\"refresh\" class=\"sl_form\">"
	."<option value=\"1800\">30 "._MIN.".</option>"
	."<option value=\"3600\" selected>1 "._HOUR."</option>"
	."<option value=\"18000\">5 "._HOUR.".</option>"
	."<option value=\"36000\">10 "._HOUR.".</option>"
	."<option value=\"86400\">24 "._HOUR.".</option></select></td></tr>"
	."<tr><td>"._FILENAME.":<div class=\"sl_small\">"._FILENAMEIN."</div></td><td>"
	."<select name=\"blockfile\" class=\"sl_form\">"
	."<option value=\"\" selected>"._NONE."</option>";
	$handle = opendir("blocks");
	while (false !== ($file = readdir($handle))) {
		if (preg_match("/^block\-(.+)\.php/", $file, $matches)) {
			if ($db->sql_numrows($db->sql_query("SELECT * FROM ".$prefix."_blocks WHERE blockfile = '".$file."'")) == 0) $cont .= "<option value=\"".$file."\">".$matches[0]."</option>\n";
		}
	}
	closedir($handle);
	$cont .= "</select></td></tr>"
	."<tr><td>"._CONTENT.":</td><td>".textarea("1", "content", "", "all", "15", _CONTENT, "")."</td></tr>"
	."<tr><td>"._POSITION.":</td><td><select name=\"bposition\" class=\"sl_form\">"
	."<option value=\"l\">"._LEFT."</option>"
	."<option value=\"c\">"._CENTERUP."</option>"
	."<option value=\"d\">"._CENTERDOWN."</option>"
	."<option value=\"r\">"._RIGHT."</option>"
	."<option value=\"b\">"._BANNERUP."</option>"
	."<option value=\"f\">"._BANNERDOWN."</option>"
	."</select></td></tr>"
	."<tr><td>"._BLOCK_VIEW.":</td><td><table>";
	$a = 2;
	$i = 1;
	$result = $db->sql_query("SELECT title FROM ".$prefix."_modules");
	while (list($title) = $db->sql_fetchrow($result)) {
		$tdwidth = intval(100/$a);
		if (($i - 1) % $a == 0) $cont .= "<tr>";
		$cont .= "<td style=\"width: ".$tdwidth."%;\"><input type=\"checkbox\" name=\"blockwhere[]\" value=\"".$title."\"> <span title=\""._MODUL.": ".$title."\" class=\"sl_note\">".deflmconst($title)."</span></td>";
		if ($i % $a == 0) $cont .= "</tr>";
		$i++;
	}
	$cont .= "<tr><td><input type=\"checkbox\" name=\"blockwhere[]\" value=\"ihome\"> <b>"._HOME."</b></td><td><input type=\"checkbox\" name=\"blockwhere[]\" value=\"home\"> <b>"._INHOME."</b></td></tr>"
	."<tr><td><input type=\"checkbox\" name=\"blockwhere[]\" value=\"all\"> <b>"._BLOCK_ALL."</b></td><td><input type=\"checkbox\" name=\"blockwhere[]\" value=\"otricanie\"> <b>"._DENYING."</b></td></tr>"
	."<tr><td><input type=\"checkbox\" name=\"blockwhere[]\" value=\"infly\"> <b>"._INFLY."</b></td><td><input type=\"checkbox\" name=\"blockwhere[]\" value=\"flyfix\"> <b>"._FLY_FIX."</b></td></tr></table>"
	."</td></tr>";
	if ($conf['multilingual'] == 1) $cont .= "<tr><td>"._LANGUAGE.":</td><td><select name=\"blanguage\" class=\"sl_form\">".language()."</select></td></tr>";
	$cont .= "<tr><td>"._ACTIVATE2."</td><td>".radio_form(1, "active")."</td></tr>"
	."<tr><td>"._EXPIRATION.":<div class=\"sl_small\">"._CONFINES."</div></td><td><input type=\"number\" name=\"expire\" value=\"0\" class=\"sl_form\" placeholder=\""._EXPIRATION."\" required></td></tr>"
	."<tr><td>"._AFTEREXPIRATION.":</td><td><select name=\"action\" class=\"sl_form\">"
	."<option value=\"d\">"._DEACTIVATE."</option>"
	."<option value=\"r\">"._DELETE."</option></select></td></tr>"
	."<tr><td>"._VIEWPRIV."</td><td><select name=\"view\" class=\"sl_form\">";
	$privs = array(_MVALL, _MVUSERS, _MVADMIN, _MVANON);
	foreach ($privs as $key => $value) $cont .= "<option value=\"".$key."\">".$value."</option>";
	$cont .= "</select></td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"blocks_add\"><input type=\"submit\" value=\""._CREATEBLOCK."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function blocks_file() {
	global $admin_file;
	head();
	$cont = blocks_navi(0, 2, 0, 0);
	$permtest = end_chmod("blocks/", 777);
	if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_form\">"
	."<tr><td>"._FILENAME.":</td><td><input type=\"text\" name=\"bf\" maxlength=\"200\" class=\"sl_form\" placeholder=\""._FILENAME."\" required></td></tr>"
	."<tr><td>"._TYPE.":</td><td><input type=\"radio\" name=\"flag\" value=\"php\" checked> PHP <input type=\"radio\" name=\"flag\" value=\"html\"> HTML</td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"blocks_bfile\"><input type=\"submit\" value=\""._CREATEBLOCK."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function blocks_file_edit() {
	global $prefix, $db, $admin_file;
	head();
	$cont = blocks_navi(0, 3, 0, 0);
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_form\">"
	."<tr><td>"._FILENAME.":</td><td><select name=\"bf\" class=\"sl_form\">";
	$handle = opendir("blocks");
	while (false !== ($file = readdir($handle))) {
		if (preg_match("/^block\-(.+)\.php/", $file, $matches)) {
			if ($db->sql_numrows($db->sql_query("SELECT * FROM ".$prefix."_blocks WHERE blockfile = '".$file."'")) == 0) $cont .= "<option value=\"".$file."\">".$matches[0]."</option>\n";
		}
	}
	closedir($handle);
	$cont .= "</select></td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"op\" value=\"blocks_bfile\"><input type=\"submit\" value=\""._EDITBLOCK."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function blocks_fix() {
	global $prefix, $db, $admin_file;
	$pos = array("b", "c", "d", "f", "l", "r");
	foreach ($pos as $val) {
		if ($val != "") {
			$result = $db->sql_query("SELECT bid FROM ".$prefix."_blocks WHERE bposition = '".$val."' ORDER BY weight ASC");
			$weight = 0;
			while (list($bid) = $db->sql_fetchrow($result)) {
				$weight++;
				$db->sql_query("UPDATE ".$prefix."_blocks SET weight = '".$weight."' WHERE bid = '".$bid."'");
			}
		}
	}
	header("Location: ".$admin_file.".php?op=blocks_show");
}

function blocks_add() {
	global $prefix, $db, $admin_file;
	$title = $_POST['title'];
	$content = $_POST['content'];
	$url = $_POST['url'];
	$bposition = $_POST['bposition'];
	$active = $_POST['active'];
	$refresh = $_POST['refresh'];
	$headline = $_POST['headline'];
	$blanguage = $_POST['blanguage'];
	$blockfile = $_POST['blockfile'];
	$view = $_POST['view'];
	$expire = $_POST['expire'];
	$action = $_POST['action'];
	$url = ($headline) ? $headline : $url;
	$blockwhere = $_POST['blockwhere'];
	list($weight) = $db->sql_fetchrow($db->sql_query("SELECT weight FROM ".$prefix."_blocks WHERE bposition = '".$bposition."' ORDER BY weight DESC"));
	$weight++;
	$bkey = "";
	$btime = "";
	if ($blockfile != "") {
		$url = "";
		if ($title == "") $title = str_replace("_", " ", str_replace(array("block-", ".php"), "", $blockfile));
	}
	if ($url) {
		$btime = time();
		$content = rss_read($url, 1);
	}
	if (($content == "") && ($blockfile == "")) {
		head();
		echo blocks_navi(0, 1, 0, 0).tpl_warn("warn", _RSSFAIL, "", "", "warn").tpl_eval("open")."<table><tr><td class=\"sl_center\">"._GOBACK."</td></tr></table>".tpl_eval("close", "");
		foot();
	} else {
		if ($expire == "" || $expire == 0) {
			$expire = 0;
		} else {
			$expire = time() + ($expire * 86400);
		}
		if (isset($blockwhere)) {
			$which = "";
			$which = (in_array("all", $blockwhere)) ? "all" : $which;
			$which = (in_array("home", $blockwhere)) ? "home" : $which;
			if ($which == "") $which = implode(",", $blockwhere);
		}
		$db->sql_query("INSERT INTO ".$prefix."_blocks VALUES (NULL, '".$bkey."', '".$title."', '".$content."', '".$url."', '".$bposition."', '".$weight."', '".$active."', '".$refresh."', '".$btime."', '".$blanguage."', '".$blockfile."', '".$view."', '".$expire."', '".$action."', '".$which."')");
		header("Location: ".$admin_file.".php?op=blocks_show");
	}
}

function blocks_bfile() {
	global $prefix, $db, $admin_file;
	if ($_POST['bf'] != "") {
		$bf = $_POST['bf'];
		if (isset($_POST['flag'])) {
			$flaged = $_POST['flag'];
			$bf = str_replace(array("block-", ".php"), "", $bf);
			$bf = "block-".$bf.".php";
		} else {
			$bfstr = file_get_contents("blocks/".$bf);
			if (strpos($bfstr,"BLOCKHTML") === false) {
				$flaged = "php";
				preg_match("/<\?php.*if.*\(\!defined\(\"BLOCK_FILE\"\)\).*exit;.*?}(.*)\?>/is", $bfstr, $out);
				unset($out[0]);
			} else {
				$flaged = "html";
				preg_match("/<<<BLOCKHTML(.*)BLOCKHTML;/is", $bfstr, $out);
				unset($out[0]);
			}
		}
		head();
		$cont = blocks_navi(0, 3, 0, 0);
		$permtest = end_chmod("blocks/", 777);
		if ($permtest) $cont .= tpl_warn("warn", $permtest, "", "", "warn");
		$cont .= tpl_warn("warn", _BLOCK.": ".$bf, "", "", "info");
		if (file_exists("blocks/".$bf)) {
			$permtestf = end_chmod("blocks/".$bf, 666);
			if ($permtestf) $cont .= tpl_warn("warn", $permtestf, "", "", "warn");
			$cont .= tpl_warn("warn", _B_FEDIT, "", "", "warn");
		}
		$cont .= tpl_warn("warn", _EINFOPHP, "", "", "warn");
		$cont .= tpl_eval("open");
		$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_edit\">"
		."<tr><td>".textarea_code("code", "blocktext", "sl_form", "text/x-php", trim($out[1]))."</td></tr>"
		."<tr><td class=\"sl_center\"><input type=\"hidden\" name=\"bf\" value=\"".$bf."\">"
		."<input type=\"hidden\" name=\"flag\" value=\"".$flaged."\">"
		."<input type=\"hidden\" name=\"op\" value=\"blocks_bfile_save\">"
		."<input type=\"submit\" value=\""._SAVE."\" class=\"sl_but_blue\"> "._GOBACK."</td></tr></table></form>";
		$cont .= tpl_eval("close", "");
		echo $cont;
		foot();
	} else {
		header("Location: ".$admin_file.".php?op=blocks_file");
	}
}

function blocks_bfile_save() {
	global $prefix, $db, $admin_file;
	if (isset($_POST['blocktext'])) {
		if (!empty($_POST['blocktext'])) {
			if (isset($_POST['bf'])) {
				$bf = $_POST['bf'];
				if ($handle = fopen("blocks/".$bf, "wb")) {
					$html_b = "";
					$html_e = "";
					if (isset($_POST['flag'])) {
						$flaged = $_POST['flag'];
						if ($flaged == 'html') {
							$html_b = "\$content = <<<BLOCKHTML\r\n";
							$html_e = "\r\nBLOCKHTML;\r\n";
						}
					}
					$str_set = stripslashes($_POST['blocktext']);
					fwrite($handle, "<?php\r\n# Copyright © 2005 - ".date("Y")." SLAED\r\n# Website: http://www.slaed.net\r\n\r\nif (!defined(\"BLOCK_FILE\")) {\r\n\theader(\"Location: ../index.php\");\r\n\texit;\r\n}\r\n\r\n".$html_b.$str_set.$html_e."\r\n?>");
					header("Location: ".$admin_file.".php?op=blocks_show");
				}
				fclose($handle);
			}
		}
	}
}

function blocks_edit() {
	global $prefix, $db, $admin_file, $conf;
	head();
	$cont = blocks_navi(0, 1, 0, 0);
	$bid = intval($_GET['bid']);
	list($bkey, $title, $content, $url, $bposition, $weight, $active, $refresh, $blanguage, $blockfile, $view, $expire, $action, $which) = $db->sql_fetchrow($db->sql_query("SELECT bkey, title, content, url, bposition, weight, active, refresh, blanguage, blockfile, view, expire, action, which FROM ".$prefix."_blocks WHERE bid = '".$bid."'"));
	if ($url != "") {
		$type = "("._BLOCKRSS.")";
	} elseif ($blockfile != "") {
		$type = "("._BLOCKFILE.")";
	} else {
		$type = "("._BLOCKHTML.")";
	}
	$cont .= tpl_warn("warn", _BLOCK.": ".$title." ".$type, "", "", "info");
	$cont .= tpl_eval("open");
	$cont .= "<form action=\"".$admin_file.".php\" method=\"post\"><table class=\"sl_table_form\">"
	."<tr><td>"._TITLE.":<div class=\"sl_small\">"._ADDCONST."</div></td><td><input type=\"text\" name=\"title\" maxlength=\"50\" value=\"".$title."\" class=\"sl_form\" placeholder=\""._TITLE."\" required></td></tr>";
	if ($blockfile != "") {
		$cont .= "<tr><td>"._FILENAME.":</td><td><select name=\"blockfile\" class=\"sl_form\">";
		$dir = opendir("blocks");
		while (false !== ($file = readdir($dir))) {
			if (preg_match("/^block\-(.+)\.php/", $file, $matches)) {
				$selected = ($blockfile == $file) ? " selected" : "";
				$cont .= "<option value=\"".$file."\"".$selected.">".$matches[0]."</option>";
			}
		}
		closedir($dir);
		$cont .= "</select></td></tr>";
	} else {
		if ($url != "") {
			$cont .= "<tr><td>"._RSSFILE.":</td><td><input type=\"text\" name=\"url\" maxlength=\"200\" value=\"".$url."\" class=\"sl_form\" placeholder=\""._RSSFILE."\"></td></tr>"
			."<tr><td>"._REFRESHTIME.":</td><td><select name=\"refresh\" class=\"sl_form\">"
			."<option value='1800'";
			if ($refresh == "1800") $cont .= " selected";
			$cont .= ">30 "._MIN.".</option>"
			."<option value='3600'";
			if ($refresh == "3600") $cont .= " selected";
			$cont .= ">1 "._HOUR."</option>"
			."<option value='18000'";
			if ($refresh == "18000") $cont .= " selected";
			$cont .= ">5 "._HOUR.".</option>"
			."<option value='36000'";
			if ($refresh == "36000") $cont .= " selected";
			$cont .= ">10 "._HOUR.".</option>"
			."<option value='86400'";
			if ($refresh == "86400") $cont .= " selected";
			$cont .= ">24 "._HOUR.".</option>"
			."</select></td></tr>";
		} else {
			$cont .= "<tr><td>"._CONTENT.":</td><td>".textarea("1", "content", $content, "all", "15", _CONTENT, "")."</td></tr>";
		}
	}
	$sel1 = ($bposition == "l") ? " selected" : "";
	$sel2 = ($bposition == "c") ? " selected" : "";
	$sel3 = ($bposition == "r") ? " selected" : "";
	$sel4 = ($bposition == "d") ? " selected" : "";
	$sel5 = ($bposition == "b") ? " selected" : "";
	$sel6 = ($bposition == "f") ? " selected" : "";
	$cont .= "<tr><td>"._POSITION.":</td><td><select name=\"bposition\" class=\"sl_form\">"
	."<option value=\"l\"".$sel1.">"._LEFT."</option>"
	."<option value=\"c\"".$sel2.">"._CENTERUP."</option>"
	."<option value=\"d\"".$sel4.">"._CENTERDOWN."</option>"
	."<option value=\"r\"".$sel3.">"._RIGHT."</option>"
	."<option value=\"b\"".$sel5.">"._BANNERUP."</option>"
	."<option value=\"f\"".$sel6.">"._BANNERDOWN."</option>"
	."</select></td></tr>";
	$cont .= "<tr><td>"._BLOCK_VIEW.":</td><td><table>";
	$where_mas = explode(",", $which);
	$a = 2;
	$i = 1;
	$result = $db->sql_query("SELECT title FROM ".$prefix."_modules");
	while (list($title) = $db->sql_fetchrow($result)) {
		$mel = "";
		foreach ($where_mas as $val) if ($val == $title) $mel = " checked";
		$tdwidth = intval(100/$a);
		if (($i - 1) % $a == 0) $cont .= "<tr>";
		$cont .= "<td style=\"width: ".$tdwidth."%;\"><input type=\"checkbox\" name=\"blockwhere[]\" value=\"".$title."\"".$mel."> <span title=\""._MODUL.": ".$title."\" class=\"sl_note\">".deflmconst($title)."</span></td>";
		if ($i % $a == 0) $cont .= "</tr>";
		$i++;
	}
	$cel = "";
	$hel = "";
	if (in_array("infly", $where_mas)) {
		switch ($where_mas[0]) {
			case "all":
			$cel = " checked";
			break;
			case "home":
			$hel = " checked";
			break;
			case "infly":
			$fel = " checked";
			break;
		}
	}
	$iel = (in_array("ihome", $where_mas)) ? " checked" : "";
	$hel = (in_array("home", $where_mas)) ? " checked" : "";
	$cel = (in_array("all", $where_mas) && empty($hel)) ? " checked" : "";
	$fel = (in_array("infly", $where_mas)) ? " checked" : "";
	$oel = (in_array("otricanie", $where_mas)) ? " checked" : "";
	$xel = (in_array("flyfix", $where_mas)) ? " checked" : "";
	$cont .= "<tr><td><input type=\"checkbox\" name=\"blockwhere[]\" value=\"ihome\"".$iel."> <b>"._HOME."</b></td><td><input type=\"checkbox\" name=\"blockwhere[]\" value=\"home\"".$hel."> <b>"._INHOME."</b></td></tr>"
	."<tr><td><input type=\"checkbox\" name=\"blockwhere[]\" value=\"all\"".$cel."> <b>"._BLOCK_ALL."</b></td><td><input type=\"checkbox\" name=\"blockwhere[]\" value=\"otricanie\"".$oel."> <b>"._DENYING."</b></td></tr>"
	."<tr><td><input type=\"checkbox\" name=\"blockwhere[]\" value=\"infly\"".$fel."> <b>"._INFLY."</b></td><td><input type=\"checkbox\" name=\"blockwhere[]\" value=\"flyfix\"".$xel."> <b>"._FLY_FIX."</b></td></tr></table>"
	."</td></tr>";
	if ($conf['multilingual'] == 1) $cont .= "<tr><td>"._LANGUAGE.":</td><td><select name=\"blanguage\" class=\"sl_form\">".language($blanguage)."</select></td></tr>";
	if ($expire != 0) {
		$newexpire = 0;
		$oldexpire = $expire;
		$expire = intval($expire - time());
		$exp_day = $expire / 86400;
		$expire_text = "<input type=\"hidden\" name=\"expire\" value=\"".$oldexpire."\">"._PURCHASED.": ".display_time($expire)." (".round($exp_day, 3)." "._DAYS.")";
	} else {
		$newexpire = 1;
		$expire_text = "<input type=\"number\" name=\"expire\" value=\"0\" class=\"sl_form\" placeholder=\""._EXPIRATION."\" required>";
	}
	$selact1 = ($action == "d") ? " selected" : "";
	$selact2 = ($action == "r") ? " selected" : "";
	$cont .= "<tr><td>"._ACTIVATE2."</td><td>".radio_form($active, "active")."</td></tr>"
	."<tr><td>"._EXPIRATION.":<div class=\"sl_small\">"._CONFINES."</div></td><td>".$expire_text."</td></tr>"
	."<tr><td>"._AFTEREXPIRATION.":</td><td><select name=\"action\" class=\"sl_form\">"
	."<option value=\"d\"".$selact1.">"._DEACTIVATE."</option>"
	."<option value=\"r\"".$selact2.">"._DELETE."</option></select></td></tr>"
	."<tr><td>"._VIEWPRIV."</td><td><select name=\"view\" class=\"sl_form\">";
	$privs = array(_MVALL, _MVUSERS, _MVADMIN, _MVANON);
	foreach ($privs as $key => $value) {
		$sel = ($view == $key) ? " selected" : "";
		$cont .= "<option value=\"".$key."\"".$sel.">".$value."</option>";
	}
	$cont .= "</select></td></tr>"
	."<tr><td colspan=\"2\" class=\"sl_center\">"
	."<input type=\"hidden\" name=\"oldposition\" value=\"".$bposition."\">"
	."<input type=\"hidden\" name=\"bid\" value=\"".$bid."\">"
	."<input type=\"hidden\" name=\"newexpire\" value=\"".$newexpire."\">"
	."<input type=\"hidden\" name=\"bkey\" value=\"".$bkey."\">"
	."<input type=\"hidden\" name=\"weight\" value=\"".$weight."\">"
	."<input type=\"hidden\" name=\"op\" value=\"blocks_edit_save\">"
	."<input type=\"submit\" value=\""._SAVE."\" class=\"sl_but_blue\"></td></tr></table></form>";
	$cont .= tpl_eval("close", "");
	echo $cont;
	foot();
}

function blocks_edit_save() {
	global $prefix, $db, $admin_file;
	$newexpire = $_POST['newexpire'];
	$bid = $_POST['bid'];
	$bkey = $_POST['bkey'];
	$title = $_POST['title'];
	$content = $_POST['content'];
	$url = $_POST['url'];
	$oldposition = $_POST['oldposition'];
	$bposition = $_POST['bposition'];
	$active = $_POST['active'];
	$refresh = $_POST['refresh'];
	$weight = $_POST['weight'];
	$blanguage = $_POST['blanguage'];
	$blockfile = $_POST['blockfile'];
	$view = $_POST['view'];
	$expire = $_POST['expire'];
	$action = $_POST['action'];
	$blockwhere = $_POST['blockwhere'];
	if (isset($blockwhere)) {
		$which = '';
		if (in_array('all', $blockwhere)) $which = 'all';
		if (in_array('home', $blockwhere)) $which = 'home';
		if ($which == '') {
			$which = implode(',', $blockwhere);
		} else {
			if (in_array('otricanie', $blockwhere)) $which .= ',otricanie';
			if (in_array('flyfix', $blockwhere)) $which .= ',flyfix';
		}
		if (in_array('infly', $blockwhere)) {
			if (in_array('flyfix', $blockwhere)) {
				$which = 'infly,'.str_replace('infly,', '', $which);
			} else {
				$which = 'infly,';
			}
		}
		if (in_array('ihome', $blockwhere) && $which != 'home') {
			$which = 'ihome,'.str_replace(',ihome', '', $which);
		}
		$db->sql_query("UPDATE ".$prefix."_blocks SET which = '".$which."' WHERE bid = '".$bid."'");
	} else {
		$db->sql_query("UPDATE ".$prefix."_blocks SET which = '' WHERE bid = '".$bid."'");
	}
	if ($url) {
		$bkey = "";
		$btime = time();
		$content = rss_read($url, 1);
		if ($oldposition != $bposition) {
			$result = $db->sql_query("SELECT bid FROM ".$prefix."_blocks WHERE weight >= '".$weight."' AND bposition = '".$bposition."'");
			$fweight = $weight;
			$oweight = $weight;
			while (list($nbid) = $db->sql_fetchrow($result)) {
				$weight++;
				$db->sql_query("UPDATE ".$prefix."_blocks SET weight = '".$weight."' WHERE bid = '".$nbid."'");
			}
			$result2 = $db->sql_query("SELECT bid FROM ".$prefix."_blocks WHERE weight > '".$oweight."' AND bposition = '".$oldposition."'");
			while (list($obid) = $db->sql_fetchrow($result2)) {
				$db->sql_query("UPDATE ".$prefix."_blocks SET weight = '".$oweight."' WHERE bid = '".$obid."'");
				$oweight++;
			}
			list($lastw) = $db->sql_fetchrow($db->sql_query("SELECT weight FROM ".$prefix."_blocks WHERE bposition = '".$bposition."' ORDER BY weight DESC LIMIT 0,1"));
			if ($lastw <= $fweight) {
				$lastw++;
				$db->sql_query("UPDATE ".$prefix."_blocks SET title = '".$title."', content = '".$content."', bposition = '".$bposition."', weight = '".$lastw."', active = '".$active."', refresh = '".$refresh."', blanguage = '".$blanguage."', blockfile = '".$blockfile."', view = '".$view."' WHERE bid = '".$bid."'");
			} else {
				$db->sql_query("UPDATE ".$prefix."_blocks SET title = '".$title."', content = '".$content."', bposition = '".$bposition."', weight = '".$fweight."', active = '".$active."', refresh = '".$refresh."', blanguage = '".$blanguage."', blockfile = '".$blockfile."', view = '".$view."' WHERE bid = '".$bid."'");
			}
		} else {
			$db->sql_query("UPDATE ".$prefix."_blocks SET bkey = '".$bkey."', title = '".$title."', content = '".$content."', url = '".$url."', bposition = '".$bposition."', weight = '".$weight."', active = '".$active."', refresh = '".$refresh."', blanguage = '".$blanguage."', blockfile = '".$blockfile."', view = '".$view."' WHERE bid = '".$bid."'");
		}
		header("Location: ".$admin_file.".php?op=blocks_show");
	} else {
		if ($oldposition != $bposition) {
			$result5 = $db->sql_query("SELECT bid FROM ".$prefix."_blocks WHERE weight >= '".$weight."' AND bposition = '".$bposition."'");
			$fweight = $weight;
			$oweight = $weight;
			while (list($nbid) = $db->sql_fetchrow($result5)) {
				$weight++;
				$db->sql_query("UPDATE ".$prefix."_blocks SET weight = '".$weight."' WHERE bid = '".$nbid."'");
			}
			$result6 = $db->sql_query("SELECT bid FROM ".$prefix."_blocks WHERE weight > '".$oweight."' AND bposition = '".$oldposition."'");
			while (list($obid) = $db->sql_fetchrow($result6)) {
				$db->sql_query("UPDATE ".$prefix."_blocks SET weight = '".$oweight."' WHERE bid = '".$obid."'");
				$oweight++;
			}
			list($lastw) = $db->sql_fetchrow($db->sql_query("SELECT weight FROM ".$prefix."_blocks WHERE bposition = '".$bposition."' ORDER BY weight DESC LIMIT 0,1"));
			if ($lastw <= $fweight) {
				$lastw++;
				$db->sql_query("UPDATE ".$prefix."_blocks SET title = '".$title."', content = '".$content."', bposition = '".$bposition."', weight = '".$lastw."', active = '".$active."', refresh = '".$refresh."', blanguage = '".$blanguage."', blockfile = '".$blockfile."', view = '".$view."' WHERE bid = '".$bid."'");
			} else {
				$db->sql_query("UPDATE ".$prefix."_blocks SET title = '".$title."', content = '".$content."', bposition = '".$bposition."', weight = '".$fweight."', active = '".$active."', refresh = '".$refresh."', blanguage = '".$blanguage."', blockfile = '".$blockfile."', view = '".$view."' WHERE bid = '".$bid."'");
			}
		} else {
			if ($expire == "") $expire = 0;
			if ($newexpire == 1 && $expire != 0) $expire = time() + ($expire * 86400);
			$result8 = $db->sql_query("UPDATE ".$prefix."_blocks SET bkey = '".$bkey."', title = '".$title."', content = '".$content."', url = '".$url."', bposition = '".$bposition."', weight = '".$weight."', active = '".$active."', refresh = '".$refresh."', blanguage = '".$blanguage."', blockfile = '".$blockfile."', view = '".$view."', expire = '".$expire."', action = '".$action."' WHERE bid = '".$bid."'");
		}
		header("Location: ".$admin_file.".php?op=blocks_show");
	}
}

function blocks_change() {
	global $prefix, $db, $admin_file;
	$bid = intval($_GET['bid']);
	$active = (intval($_GET['act'])) ? 0 : 1;
	$db->sql_query("UPDATE ".$prefix."_blocks SET active = '".$active."' WHERE bid = '".$bid."'");
	header("Location: ".$admin_file.".php?op=blocks_show");
}

function blocks_info() {
	head();
	echo blocks_navi(0, 5, 0, 0)."<div id=\"repadm_info\">".adm_info(1, 0, "blocks")."</div>";
	foot();
}

switch($op) {
	case "blocks_show":
	blocks_show();
	break;
	
	case "blocks_new":
	blocks_new();
	break;
	
	case "blocks_file":
	blocks_file();
	break;
	
	case "blocks_file_edit":
	blocks_file_edit();
	break;
	
	case "blocks_fix":
	blocks_fix();
	break;
	
	case "blocks_add":
	blocks_add();
	break;
	
	case "blocks_bfile":
	blocks_bfile();
	break;
	
	case "blocks_bfile_save":
	blocks_bfile_save();
	break;
	
	case "blocks_edit":
	blocks_edit();
	break;
	
	case "blocks_edit_save":
	blocks_edit_save();
	break;
	
	case "blocks_change":
	blocks_change();
	break;
	
	case "blocks_delete":
	list($bposition, $weight) = $db->sql_fetchrow($db->sql_query("SELECT bposition, weight FROM ".$prefix."_blocks WHERE bid = '".$id."'"));
	$result = $db->sql_query("SELECT bid FROM ".$prefix."_blocks WHERE weight > '".$weight."' AND bposition = '".$bposition."'");
	while (list($nbid) = $db->sql_fetchrow($result)) {
		$db->sql_query("UPDATE ".$prefix."_blocks SET weight = '".$weight."' WHERE bid = '".$nbid."'");
		$weight++;
	}
	$db->sql_query("DELETE FROM ".$prefix."_blocks WHERE bid = '".$id."'");
	header("Location: ".$admin_file.".php?op=blocks_show");
	break;

	case "blocks_info":
	blocks_info();
	break;
}
?>