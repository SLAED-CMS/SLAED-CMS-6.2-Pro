<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("MODULE_FILE")) {
	header("Location: ../../index.php");
	exit;
}
get_lang($conf['name']);

function navigate($title, $cat="") {
	global $conf;
	$home = "<a href=\"index.php?name=".$conf['name']."\" title=\""._TOPUSERS."\" class=\"sl_but_navi\">"._HOME."</a>";
	$rule = "<a href=\"index.php?name=".$conf['name']."&amp;op=liste\" title=\""._TU_RULES."\" class=\"sl_but_navi\">"._TU_RULES."</a>";
	$stat = "<a href=\"index.php?name=".$conf['name']."&amp;op=add\" title=\""._TU_STATS."\" class=\"sl_but_navi\">"._TU_STATS."</a>";
	return tpl_eval("navi", $title, $conf['name'], "", $home, $rule, $stat, "", "", "");
}

function top_users() {
	global $prefix, $db, $conf, $confu, $confra;
	head($conf['defis']." "._TOPUSERS);
	$cont = navigate(_TOPUSERS);
	$storynum = 50;
	$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
	$offset = ($num-1) * $storynum;
	$count = ($num) ? $offset+1 : 1;
	$result = $db->sql_query("SELECT user_id, user_name, user_website, user_regdate, user_from, user_lastvisit, user_points, user_last_ip, user_gender, user_votes, user_totalvotes FROM ".$prefix."_users ORDER BY user_points DESC LIMIT ".$offset.", ".$storynum);
	if ($db->sql_numrows($result) > 0) {
		$con = explode("|", $confra['account']);
		$title_a = (is_moder($conf['name'])) ? _IP : _REG;
		$title_b = ($con[1]) ? _RATING : _LOCALITYLANG;
		$cont .= tpl_eval("open");
		$cont .= "<table class=\"sl_table_list_sort\"><thead class=\"sl_table_list_head\"><tr><th>"._ID."</th><th>"._NICKNAME."</th><th>".$title_a."</th><th>"._GENDER."</th><th>".$title_b."</th><th>"._POINTS."</th></tr></thead><tbody class=\"sl_table_list_body\">";
		while (list($user_id, $user_name, $user_website, $user_regdate, $user_from, $user_lastvisit, $user_points, $user_last_ip, $user_gender, $user_votes, $user_totalvotes) = $db->sql_fetchrow($result)) {
			$website = ($user_website) ? "<br>"._SITE.": ".$user_website : "";
			$cont_a = (is_moder($conf['name'])) ? user_geo_ip($user_last_ip, 4) : format_time($user_regdate);
			$cont_b = ($con[1]) ? "<div class=\"min-rate\"><div class=\"rate-like-box\">".ajax_rating(1, $user_id, "account", $user_votes, $user_totalvotes, "", 1)."</div></div>" : cutstr($user_from, 30);
			$cont .= "<tr id=\"".$count."\">"
			."<td><a href=\"#".$count."\" title=\"".$count."\" class=\"sl_pnum\">".$count."</a></td>"
			."<td>".title_tip(_REG.": ".format_time($user_regdate, _TIMESTRING)."<br>"._LAST_VISIT.": ".format_time($user_lastvisit, _TIMESTRING).$website).user_info($user_name)."</td>"
			."<td>".$cont_a."</td>"
			."<td>".gender($user_gender)."</td>"
			."<td>".$cont_b."</td>"
			."<td>".$user_points."</td></tr>";
			$count++;
		}
		$cont .= "</tbody></table>";
		$cont .= num_article("pagenum", $conf['name'], $storynum, "", "user_id", "_users", "", "", "5");
		$cont .= tpl_eval("close");
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

function liste() {
	global $prefix, $db, $conf, $confu;
	head($conf['defis']." "._TOPUSERS." ".$conf['defis']." "._TU_RULES);
	$cont = navigate(_TOPUSERS);
	$cont .= tpl_eval("open");
	$cont .= "<table class=\"sl_table_list_sort\"><thead class=\"sl_table_list_head\"><tr><th>"._ID."</th><th>"._TYPE."</th><th>"._DESCRIPTION."</th><th>"._POINTS."</th></tr></thead><tbody class=\"sl_table_list_body\">";
	$p = array(_POINTS01, _POINTS02, _POINTS03, _POINTS04, _POINTS05, _POINTS06, _POINTS07, _POINTS08, _POINTS09, _POINTS10, _POINTS11, _POINTS12, _POINTS13, _POINTS14, _POINTS15, _POINTS16, _POINTS17, _POINTS18, _POINTS19, _POINTS20, _POINTS21, _POINTS22, _POINTS23, _POINTS24, _POINTS25, _POINTS26, _POINTS27, _POINTS28, _POINTS29, _POINTS30, _POINTS31, _POINTS32, _POINTS33, _POINTS34, _POINTS35, _POINTS36, _POINTS37, _POINTS38, _POINTS39, _POINTS40, _POINTS41, _POINTS42, _POINTS43, _POINTS44, _POINTS45);
	$d = array(_DESC01, _DESC02, _DESC03, _DESC04, _DESC05, _DESC06, _DESC07, _DESC08, _DESC09, _DESC10, _DESC11, _DESC12, _DESC13, _DESC14, _DESC15, _DESC16, _DESC17, _DESC18, _DESC19, _DESC20, _DESC21, _DESC22, _DESC23, _DESC24, _DESC25, _DESC26, _DESC27, _DESC28, _DESC29, _DESC30, _DESC31, _DESC32, _DESC33, _DESC34, _DESC35, _DESC36, _DESC37, _DESC38, _DESC39, _DESC40, _DESC41, _DESC42, _DESC43, _DESC44, _DESC45);
	$points = explode(",", $confu['points']);
	for ($i = 0; $i < count($p); $i++) {
		$a = $i + 1;
		$cont .= "<tr id=\"".$a."\"><td><a href=\"#".$a."\" title=\"".$a."\" class=\"sl_pnum\">".$a."</a></td><td>".$p[$i]."</td><td>".$d[$i]."</td><td>".$points[$i]."</td></tr>";
	}
	$cont .= "</tbody></table>";
	$cont .= tpl_eval("close");
	$cont .= get_page($conf['name']);
	echo $cont;
	foot();
}

function add(){
	global $prefix, $db, $conf;
	head($conf['defis']." "._TOPUSERS." ".$conf['defis']." "._TU_STATS);
	$cont = navigate(_TOPUSERS);
	$result = $db->sql_query("SELECT id, name, description, points, extra, rank, color FROM ".$prefix."_groups ORDER BY points");
	if ($result) {
		$cont .= tpl_eval("open");
		$cont .= "<table class=\"sl_table_list_sort\"><thead class=\"sl_table_list_head\"><tr><th>"._RANK."</th><th>"._DESCRIPTION."</th><th>"._POINTS."</th><th>"._TU_USERSCOUNT."</th><th>".cutstr(_SPEC, 4, 1)."</th></tr></thead><tbody class=\"sl_table_list_body\">";
		while (list($grid, $grname, $description, $points, $extra, $rank, $color) = $db->sql_fetchrow($result)) {
			if (intval($extra)) {
				$extra = _YES;
				list($users_num) = $db->sql_fetchrow($db->sql_query("SELECT Count(*) FROM ".$prefix."_users WHERE user_group = '".$grid."'"));
			} else {
				$extra = _NO;
				list($users_num) = $db->sql_fetchrow($db->sql_query("SELECT Count(*) FROM ".$prefix."_users WHERE user_points >= '".$points."'"));
			}
			$trank = ($grname) ? _GROUP.": ".$grname : _RANK;
			$cont .= "<tr>"
			."<td><img src=\"".img_find("ranks/".$rank)."\" alt=\"".$trank."\" title=\"".$trank."\"></td>"
			."<td><span style=\"color: ".$color.";\">".$grname."</span><br>".$description."</td>"
			."<td>".$points."</td>"
			."<td>".$users_num."</td>"
			."<td>".$extra."</td></tr>";
		}
		$cont .= "</tbody></table>";
		$cont .= tpl_eval("close");
		$cont .= get_page($conf['name']);
	} else {
		$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
	}
	echo $cont;
	foot();
}

switch ($op) {
	default:
	top_users();
	break;

	case "liste":
	liste();
	break;

	case "add":
	add();
	break;
}
?>