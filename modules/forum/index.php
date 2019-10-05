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
include("config/config_forum.php");

function forum() {
	global $prefix, $db, $user, $conf, $confu, $conffo, $currentlang;
	$mod = ($conf['name']) ? analyze($conf['name']) : 0;
	$id = (isset($_POST['cat'])) ? ((isset($_POST['cat'])) ? intval($_POST['cat']) : 0) : ((isset($_GET['cat'])) ? intval($_GET['cat']) : 0);
	if ($id) {
		$where = "WHERE c.modul = '".$mod."' AND (c.parentid = '".$id."' OR c.id = '".$id."')";
	} elseif ($id && $conf['multilingual']) {
		$where = "WHERE c.modul = '".$mod."' AND (c.parentid = '".$id."' OR c.id = '".$id."') AND (c.language = '".$currentlang."' OR c.language = '')";
	} elseif ($conf['multilingual']) {
		$where = "WHERE c.modul = '".$mod."' AND (c.language = '".$currentlang."' OR c.language = '')";
	} else {
		$where = "WHERE c.modul = '".$mod."'";
	}
	$result = $db->sql_query("SELECT c.id, c.title, c.description, c.img, c.parentid, c.cstatus, c.ordern, c.topics, c.posts, c.lpost_id, c.auth_view, c.auth_read, c.auth_post, c.auth_reply, c.auth_edit, c.auth_delete, c.auth_mod, f.title, f.l_uid, f.l_name, f.l_id, f.l_time FROM ".$prefix."_categories AS c LEFT JOIN ".$prefix."_forum AS f ON (c.lpost_id = f.id) ".$where." ORDER BY c.ordern");
	while (list($cid, $title, $description, $img, $parentid, $status, $ordern, $topics, $posts, $lpost_id, $auth_view, $auth_read, $auth_post, $auth_reply, $auth_edit, $auth_delete, $auth_mod, $ftitle, $fuid, $fname, $flid, $fltime) = $db->sql_fetchrow($result)) {
		$massiv[] = array($cid, $title, $description, $img, $parentid, $status, $ordern, $topics, $posts, $lpost_id, $auth_view, $auth_read, $auth_post, $auth_reply, $auth_edit, $auth_delete, $auth_mod, $ftitle, $fuid, $fname, $flid, $fltime);
		unset($cid, $title, $description, $img, $parentid, $status, $ordern, $topics, $posts, $lpost_id, $auth_view, $auth_read, $auth_post, $auth_reply, $auth_edit, $auth_delete, $auth_mod, $ftitle, $fuid, $fname, $flid, $fltime);
	}
	if ($massiv) {
		$isview = is_acess($massiv[0][10]);
		$isread = is_acess($massiv[0][11]);
		$istopic = is_acess($massiv[0][12]);
		$isreply = is_acess($massiv[0][13]);
		$isedit = is_acess($massiv[0][14]);
		$isdelete = is_acess($massiv[0][15]);
		$ismod = is_acess($massiv[0][16]);
		$userinfo = getusrinfo();
		$ulastvisit = ($userinfo['user_lastvisit']) ? $userinfo['user_lastvisit'] : 0;
		$pagetitle = ($id) ? $conf['defis']." "._FORUM." ".$conf['defis']." ".$massiv[0][1] : $conf['defis']." "._FORUM;
		head($pagetitle);
		$a = 0;
		foreach ($massiv as $val) {
			if ($val[4] == $id && is_acess($val[10])) {
				if ($id) {
					$cont = (!$a) ? tpl_func("forum-cat-open", "<a href=\"index.php?name=".$conf['name']."\" title=\""._FORUM."\">"._FORUM."</a> ".urldecode($conffo['defis'])." <a href=\"index.php?name=".$mod."&amp;cat=".$massiv[0][0]."\" title=\"".$massiv[0][1]."\">".$massiv[0][1]."</a>", _FORUM, _NEWTOPICS, cutstr(_MESSAGES, 5, 1), _LASTMESSAGE) : "";
					$ttitle= ($val[2]) ? $val[2] : $val[1];
					$tlink = ($val[5] || is_moder($conf['name'])) ? "<a href=\"index.php?name=".$mod."&amp;cat=".$val[0]."\" title=\"".$ttitle."\">".$val[1]."</a>" : $val[1];
					if (!$val[5]) {
						$imglink = ($val[3]) ? "<img src=\"".img_find("categories/".$val[3])."\" alt=\""._FCLOSED."\" title=\""._FCLOSED."\" class=\"sl_hidden\">" : "<span title=\""._FCLOSED."\" class=\"sl_f_clos\"></span>";
						$timg = (is_moder($conf['name'])) ? "<a href=\"index.php?name=".$mod."&amp;cat=".$val[0]."\" title=\""._FCLOSED."\">".$imglink."</a>" : $imglink;
					} elseif ($val[21] > $ulastvisit) {
						$imglink = ($val[3]) ? "<img src=\"".img_find("categories/".$val[3])."\" alt=\""._ISNEWPOST."\" title=\""._ISNEWPOST."\">" : "<span title=\""._ISNEWPOST."\" class=\"sl_f_new\"></span>";
						$timg = "<a href=\"index.php?name=".$mod."&amp;cat=".$val[0]."\" title=\""._ISNEWPOST."\">".$imglink."</a>";
					} else {
						$imglink = ($val[3]) ? "<img src=\"".img_find("categories/".$val[3])."\" alt=\""._NONEWPOST."\" title=\""._NONEWPOST."\" class=\"sl_hidden\">" : "<span title=\""._NONEWPOST."\" class=\"sl_f_old\"></span>";
						$timg = "<a href=\"index.php?name=".$mod."&amp;cat=".$val[0]."\" title=\""._NONEWPOST."\">".$imglink."</a>";
					}
					if ($val[9]) {
						$data = _DATE.": ".format_time($val[21], _TIMESTRING);
						$topic = ($val[5]) ? _TOPIC.": <a href=\"index.php?name=".$mod."&amp;op=view&amp;id=".$val[9]."\" title=\"".$val[17]."\">".cutstr($val[17], 14)."</a>" : _TOPIC.": ".cutstr($val[17], 14);
						$post = ($val[18]) ? user_info($val[19]) : $val[19];
						$post = _POSTER.": ".$post;
						$lid = ($val[20]) ? $val[20] : $val[9];
						$lpost = ($val[5]) ? "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$val[9]."&amp;last#".$lid."\" title=\""._LASTMESSAGE."\"><span title=\""._LASTMESSAGE."\" class=\"sl_f_last\"></span></a>" : "<span title=\""._LASTMESSAGE."\" class=\"sl_f_last\"></span>";
					} else {
						$data = _NO_INFO;
						$topic = $post = $lpost = "";
					}
					$cont .= tpl_func("forum-cat-basic", $timg, $tlink, $val[2], $val[7], $val[8], $data, $topic, $post, $lpost);
					echo $cont;
				} else {
					$cont = tpl_func("forum-cat-open", "<a href=\"index.php?name=".$mod."&amp;cat=".$val[0]."\" title=\"".$val[1]."\">".$val[1]."</a>", _FORUM, _NEWTOPICS, cutstr(_MESSAGES, 5, 1), _LASTMESSAGE);
					foreach ($massiv as $val2) {
						if ($val[0] == $val2[4] && is_acess($val2[10])) {
							$ttitle= ($val2[2]) ? $val2[2] : $val2[1];
							$tlink = ($val2[5] || is_moder($conf['name'])) ? "<a href=\"index.php?name=".$mod."&amp;cat=".$val2[0]."\" title=\"".$ttitle."\">".$val2[1]."</a>" : $val2[1];
							if (!$val2[5]) {
								$imglink = ($val2[3]) ? "<img src=\"".img_find("categories/".$val2[3])."\" alt=\""._FCLOSED."\" title=\""._FCLOSED."\" class=\"sl_hidden\">" : "<span title=\""._FCLOSED."\" class=\"sl_f_clos\"></span>";
								$timg = (is_moder($conf['name'])) ? "<a href=\"index.php?name=".$mod."&amp;cat=".$val2[0]."\" title=\""._FCLOSED."\">".$imglink."</a>" : $imglink;
							} elseif ($val2[21] > $ulastvisit) {
								$imglink = ($val2[3]) ? "<img src=\"".img_find("categories/".$val2[3])."\" alt=\""._ISNEWPOST."\" title=\""._ISNEWPOST."\">" : "<span title=\""._ISNEWPOST."\" class=\"sl_f_new\"></span>";
								$timg = "<a href=\"index.php?name=".$mod."&amp;cat=".$val2[0]."\" title=\""._ISNEWPOST."\">".$imglink."</a>";
							} else {
								$imglink = ($val2[3]) ? "<img src=\"".img_find("categories/".$val2[3])."\" alt=\""._NONEWPOST."\" title=\""._NONEWPOST."\" class=\"sl_hidden\">" : "<span title=\""._NONEWPOST."\" class=\"sl_f_old\"></span>";
								$timg = "<a href=\"index.php?name=".$mod."&amp;cat=".$val2[0]."\" title=\""._NONEWPOST."\">".$imglink."</a>";
							}
							if ($val2[9]) {
								$data = _DATE.": ".format_time($val2[21], _TIMESTRING);
								$topic = ($val2[5]) ? _TOPIC.": <a href=\"index.php?name=".$mod."&amp;op=view&amp;id=".$val2[9]."\" title=\"".$val2[17]."\">".cutstr($val2[17], 14)."</a>" : _TOPIC.": ".cutstr($val2[17], 14);
								$post = ($val2[18]) ? user_info($val2[19]) : $val2[19];
								$post = _POSTER.": ".$post;
								$lid = ($val2[20]) ? $val2[20] : $val2[9];
								$lpost = ($val2[5]) ? "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$val2[9]."&amp;last#".$lid."\" title=\""._LASTMESSAGE."\"><span title=\""._LASTMESSAGE."\" class=\"sl_f_last\"></span></a>" : "<span title=\""._LASTMESSAGE."\" class=\"sl_f_last\"></span>";
							} else {
								$data = _NO_INFO;
								$topic = $post = $lpost = "";
							}
							$cont .= tpl_func("forum-cat-basic", $timg, $tlink, $val2[2], $val2[7], $val2[8], $data, $topic, $post, $lpost);
						}
					}
					$cont .= tpl_func("forum-cat-close");
					echo $cont;
				}
				$a++;
			}
		}
		$teml = true;
		unset($cont);
		if ($id) {
			if (!$a) {
				if ($isview) {
					$cat = intval($id);
					$lang = ($conf['multilingual']) ? "AND (c.language = '".$currentlang."' OR c.language = '') AND s.catid = '".$cat."'" : "AND s.catid = '".$cat."'";
					$listnum = intval($conffo['listnum']);
					$ordern = (is_moder($conf['name'])) ? "WHERE s.pid = '0'" : "WHERE s.pid = '0' AND s.time <= now() AND s.status != '0'";
					$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
					$offset = ($num-1) * $listnum;
					$offset = intval($offset);
					$result = $db->sql_query("SELECT s.id, s.catid, s.name, s.title, s.time, s.comments, s.counter, s.score, s.ratings, s.ip_send, s.l_uid, s.l_name, s.l_id, s.l_time, s.status, c.id, c.title, u.user_name FROM ".$prefix."_forum AS s LEFT JOIN ".$prefix."_categories AS c ON (s.catid=c.id) LEFT JOIN ".$prefix."_users AS u ON (s.uid=u.user_id) ".$ordern." ".$lang." ORDER BY s.status DESC, s.l_time DESC LIMIT ".$offset.", ".$listnum);
					$newtop = ($istopic) ? "<a href=\"index.php?name=".$conf['name']."&amp;op=add&amp;cat=".$massiv[0][0]."\" title=\""._NEWTOPIC."\" class=\"sl_but\">"._OPEN."</a>" : "<span title=\"".sprintf(_ACINFOT, _NOTCAN)."\" class=\"sl_but sl_hidden\">"._OPEN."</span>";
					$catlink = catlink($conf['name'], $cat, $conffo['defis'], _FORUM);
					$cont = tpl_eval("forum-list-open", $newtop, $catlink, "<a href=\"index.php?name=".$mod."&amp;cat=".$massiv[0][0]."\" title=\"".$massiv[0][1]."\">".$massiv[0][1]."</a>");
					if ($db->sql_numrows($result) > 0) {
						$b = "";
						$cont .= tpl_eval("forum-list-basic-open", _NEWTOPICS, _POSTS, _POSTER, cutstr(_TVIEWS, 5, 1), _LASTMESSAGE);
						$cont .= ($ismod) ? "<form action=\"index.php?name=".$conf['name']."\" method=\"post\">" : "";
						while (list($sid, $catid, $uname, $title, $time, $comments, $counter, $score, $ratings, $ip_send, $l_uid, $l_name, $l_id, $l_time, $status, $cid, $ctitle, $user_name) = $db->sql_fetchrow($result)) {
							$view = 0;
							if (!$status && is_moder($conf['name'])) {
								$timg = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$sid."\" title=\"".$title."\"><span title=\""._TOPICM."\" class=\"sl_t_clos_m\"></span></a>";
								$tlink = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$sid."\" title=\"".$title."\">".$title."</a>";
								$lpost = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$sid."&amp;last#".$l_id."\" title=\""._LASTMESSAGE."\"><span title=\""._LASTMESSAGE."\" class=\"sl_f_last\"></span></a>";
								$view = 1;
							} elseif ($status == 1) {
								if (is_moder($conf['name'])) {
									$timg = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$sid."\" title=\"".$title."\"><span title=\""._TOPICA."\" class=\"sl_t_clos_a\"></span></a>";
									$tlink = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$sid."\" title=\"".$title."\">".$title."</a>";
									$lpost = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$sid."&amp;last#".$l_id."\" title=\""._LASTMESSAGE."\"><span title=\""._LASTMESSAGE."\" class=\"sl_f_last\"></span></a>";
								} else {
									$timg = "<span title=\""._TOPICA."\" class=\"sl_t_clos_a\"></span>";
									$tlink = $title;
									$lpost = "<span title=\""._LASTMESSAGE."\" class=\"sl_f_last\"></span>";
								}
								$view = 1;
							} elseif ($status == 2) {
								$timg = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$sid."\" title=\"".$title."\"><span title=\""._TOPICN."\" class=\"sl_t_clos_n\"></span></a>";
								$tlink = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$sid."\" title=\"".$title."\">".$title."</a>";
								$lpost = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$sid."&amp;last#".$l_id."\" title=\""._LASTMESSAGE."\"><span title=\""._LASTMESSAGE."\" class=\"sl_f_last\"></span></a>";
								$view = 1;
							} elseif ($status == 3 && $time <= date("Y-m-d H:i:s")) {
								if ($l_time > $ulastvisit) {
									$timg = ($comments > $conffo['pop']) ? "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$sid."\" title=\"".$title."\"><span title=\""._TPOPN."\" class=\"sl_t_pop\"></span></a>" : "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$sid."\" title=\"".$title."\"><span title=\""._ISNEWPOST."\" class=\"sl_t_new\"></span></a>";
								} else {
									$timg = ($comments > $conffo['pop']) ? "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$sid."\" title=\"".$title."\"><span title=\""._TPOP."\" class=\"sl_t_pold\"></span></a>" : "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$sid."\" title=\"".$title."\"><span title=\""._NONEWPOST."\" class=\"sl_t_old\"></span></a>";
								}
								$tlink = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$sid."\" title=\"".$title."\">".$title."</a>";
								$lpost = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$sid."&amp;last#".$l_id."\" title=\""._LASTMESSAGE."\"><span title=\""._LASTMESSAGE."\" class=\"sl_f_last\"></span></a>";
								$view = 1;
							} elseif ($status == 3 && $time > date("Y-m-d H:i:s") && is_moder($conf['name'])) {
								$timg = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$sid."\" title=\"".$title."\"><span title=\""._TOPICP."\" class=\"sl_t_clos_p\"></span></a>";
								$tlink = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$sid."\" title=\"".$title."\">".$title."</a>";
								$lpost = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$sid."&amp;last#".$l_id."\" title=\""._LASTMESSAGE."\"><span title=\""._LASTMESSAGE."\" class=\"sl_f_last\"></span></a>";
								$view = 1;
							} elseif ($status == 4 || $status == 5) {
								$timg = ($status == 4) ? "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$sid."\" title=\"".$title."\"><span title=\""._THOT."\" class=\"sl_t_hot\"></span></a>" : "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$sid."\" title=\"".$title."\"><span title=\""._TANNOUN."\" class=\"sl_t_announ\"></span></a>";
								$tlink = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$sid."\" title=\"".$title."\">".$title."</a>";
								$lpost = "<a href=\"index.php?name=".$conf['name']."&amp;op=view&amp;id=".$sid."&amp;last#".$l_id."\" title=\""._LASTMESSAGE."\"><span title=\""._LASTMESSAGE."\" class=\"sl_f_last\"></span></a>";
								$view = 1;
							}
							$ldata = _DATE.": ".format_time($l_time, _TIMESTRING);
							$post = ($user_name) ? user_info($user_name) : $uname." (".$confu['anonym'].")";
							$lposter = ($l_uid) ? _POSTER.": ".user_info($l_name) : _POSTER.": ".$l_name;
							if ($ismod) {
								$checkb = (!$b) ? "<br>"._CHECKALL." <input type=\"checkbox\" name=\"markcheck\" id=\"markcheck\" OnClick=\"CheckBox('#markcheck', '.sl_check')\"> | <input type=\"checkbox\" name=\"id[]\" class=\"sl_check\" value=\"".$sid."\">" : " <input type=\"checkbox\" name=\"id[]\" class=\"sl_check\" value=\"".$sid."\">";
								$b++;
							} else {
								$checkb = "";
							}
							$cont .= ($view) ? tpl_func("forum-list-basic", $timg, $tlink, $comments, $post, $counter, $ldata, $lposter, $lpost.$checkb) : "";
						}
						$cont .= tpl_eval("forum-list-basic-close");
						if ($ismod) {
							$selmm = tmoder(1)."<input type=\"hidden\" name=\"op\" value=\"move\"><input type=\"hidden\" name=\"cat\" value=\"".$cat."\"> <input type=\"submit\" value=\""._OK."\" class=\"sl_but_blue\"></form>";
							$cont .= tpl_eval("forum-view-change", _CHECKOP, $selmm);
						}
					} else {
						$cont .= tpl_warn("warn", _NO_INFO, "", "", "info");
					}
					$ordernum = (is_moder($conf['name'])) ? "pid = '0' AND catid = '".$cat."'" : "pid = '0' AND catid = '".$cat."' AND time <= now() AND status != '0'";
					$pnum = num_article("forum-pagenum", $conf['name'], $listnum, "cat=".$cat."&amp;", "id", "_forum", "catid", $ordernum, $conffo['pnum']);
					$cont .= tpl_eval("forum-list-close", $newtop, $catlink, $pnum);
					$infov = ($isview) ? sprintf(_ACINFOV, "<b>"._ISCAN."</b>") : sprintf(_ACINFOV, "<b>"._NOTCAN."</b>");
					$infor = ($isread) ? sprintf(_ACINFOR, "<b>"._ISCAN."</b>") : sprintf(_ACINFOR, "<b>"._NOTCAN."</b>");
					$infot = ($istopic) ? sprintf(_ACINFOT, "<b>"._ISCAN."</b>") : sprintf(_ACINFOT, "<b>"._NOTCAN."</b>");
					$infop = ($isreply) ? sprintf(_ACINFOP, "<b>"._ISCAN."</b>") : sprintf(_ACINFOP, "<b>"._NOTCAN."</b>");
					$infoe = ($isedit) ? sprintf(_ACINFOE, "<b>"._ISCAN."</b>") : sprintf(_ACINFOE, "<b>"._NOTCAN."</b>");
					$infod = ($isdelete) ? sprintf(_ACINFOD, "<b>"._ISCAN."</b>") : sprintf(_ACINFOD, "<b>"._NOTCAN."</b>");
					$infom = ($ismod) ? sprintf(_ACINFOM, "<b>"._ISCAN."</b>") : sprintf(_ACINFOM, "<b>"._NOTCAN."</b>");
					$cont .= tpl_eval("forum-list-info", "<span title=\""._ISNEWPOST."\" class=\"sl_t_new\">"._ISNEWPOST."</span>", "<span title=\""._NONEWPOST."\" class=\"sl_t_old\">"._NONEWPOST."</span>", "<span title=\""._TPOPN."\" class=\"sl_t_pop\">"._TPOPN."</span>", "<span title=\""._TPOP."\" class=\"sl_t_pold\">"._TPOP."</span>", "<span title=\""._TANNOUN."\" class=\"sl_t_announ\">"._TANNOUN."</span>", "<span title=\""._THOT."\" class=\"sl_t_hot\">"._THOT."</span>", "<span title=\""._TOPICM."\" class=\"sl_t_clos_m\">"._TOPICM."</span>", "<span title=\""._TOPICA."\" class=\"sl_t_clos_a\">"._TOPICA."</span>", "<span title=\""._TOPICN."\" class=\"sl_t_clos_n\">"._TOPICN."</span>", "<span title=\""._TOPICP."\" class=\"sl_t_clos_p\">"._TOPICP."</span>", $infov, $infor, $infot, $infop, $infoe, $infod, $infom);
				} else {
					$cont = tpl_warn("warn", _NOVIEW, "?name=".$conf['name'], 5, "warn");
				}
				$teml = false;
			} else {
				$cont = tpl_eval("forum-cat-close");
			}
		} else {
			$cont = "";
		}
		if ($teml) $cont .= tpl_eval("forum-cat-info", "<span title=\""._ISNEWPOST."\" class=\"sl_f_new\">"._ISNEWPOST."</span>", "<span title=\""._NONEWPOST."\" class=\"sl_f_old\">"._NONEWPOST."</span>", "<span title=\""._FCLOSED."\" class=\"sl_f_clos\">"._FCLOSED."</span>");
		echo $cont;
	}
	foot();
}

function view() {
	global $prefix, $db, $admin_file, $user, $conf, $confu, $confpr, $conffo, $currentlang;
	$id = (isset($_GET['id'])) ? intval($_GET['id']) : 0;
	$last = (isset($_GET['last'])) ? 1 : 0;
	$ordern = (is_moder($conf['name'])) ? "WHERE (id = '".$id."' OR pid = '".$id."')" : "WHERE (id = '".$id."' OR pid = '".$id."') AND time <= now() AND status != '0'";
	list($numfor) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_forum ".$ordern));
	if ($id && $numfor > 0) {
		$fornum = user_news($user[3], $conffo['num']);
		$numpages = ceil($numfor / $fornum);
		$num = isset($_GET['num']) ? intval($_GET['num']) : "1";
		$num = ($last && $conffo['sort']) ? $numpages : $num;
		$offset = ($num-1) * $fornum;
		if ($conffo['sort']) {
			$sort = "ASC";
			$a = ($num) ? $offset+1 : 1;
		} else {
			$sort = "DESC";
			$a = $numfor;
			if ($numfor > $offset) $a -= $offset;
		}
		$word = (isset($_GET['word'])) ? text_filter($_GET['word']) : "";
		$orderw = (is_moder($conf['name'])) ? "WHERE (s.id = '".$id."' OR s.pid = '".$id."')" : "WHERE (s.id = '".$id."' OR s.pid = '".$id."') AND s.time <= now() AND s.status != '0'";
		$result = $db->sql_query("SELECT s.id, s.pid, s.catid, s.uid, s.name, s.title, s.time, s.hometext, s.field, s.comments, s.counter, s.score, s.ratings, s.ip_send, s.e_uid, s.e_ip_send, s.e_time, s.status, c.title, c.auth_read, c.auth_post, c.auth_reply, c.auth_edit, c.auth_delete, c.auth_mod FROM ".$prefix."_forum AS s LEFT JOIN ".$prefix."_categories AS c ON (s.catid=c.id) ".$orderw." ORDER BY s.time ".$sort." LIMIT ".$offset.", ".$fornum);
		$db->sql_query("UPDATE ".$prefix."_forum SET counter=counter+1 WHERE id = '".$id."'");
		while (list($sid, $pid, $catid, $uid, $name, $title, $time, $hometext, $field, $comments, $counter, $score, $ratings, $ip_send, $e_uid, $e_ip_send, $e_time, $status, $ctitle, $auth_read, $auth_post, $auth_reply, $auth_edit, $auth_delete, $auth_mod) = $db->sql_fetchrow($result)) {
			$cmassiv[] = array($sid, $pid, $catid, $uid, $name, $title, $time, $hometext, $field, $comments, $counter, $score, $ratings, $ip_send, $e_uid, $e_ip_send, $e_time, $status, $ctitle, $auth_read, $auth_post, $auth_reply, $auth_edit, $auth_delete, $auth_mod);
			if ($uid) $where[] = $uid;
			unset($sid, $pid, $catid, $uid, $name, $title, $time, $hometext, $field, $comments, $counter, $score, $ratings, $ip_send, $e_uid, $e_ip_send, $e_time, $status, $ctitle, $auth_read, $auth_post, $auth_reply, $auth_edit, $auth_delete, $auth_mod);
		}
		if ($where) {
			$result2 = $db->sql_query("SELECT u.user_id, u.user_name, u.user_rank, u.user_email, u.user_website, u.user_avatar, u.user_regdate, u.user_from, u.user_sig, u.user_viewemail, u.user_points, u.user_warnings, u.user_gender, u.user_votes, u.user_totalvotes, g.name, g.rank, g.color FROM ".$prefix."_users AS u LEFT JOIN ".$prefix."_groups AS g ON ((g.extra=1 AND u.user_group=g.id) OR (g.extra!=1 AND u.user_points>=g.points)) WHERE u.user_id IN (".implode(", ", $where).") ORDER BY g.extra ASC, g.points ASC");
			while (list($user_id, $user_name, $user_rank, $user_email, $user_website, $user_avatar, $user_regdate, $user_from, $user_sig, $user_viewemail, $user_points, $user_warnings, $user_gender, $user_votes, $user_totalvotes, $user_gname, $user_grank, $user_gcolor) = $db->sql_fetchrow($result2)) {
				$umassiv[] = array($user_id, $user_name, $user_rank, $user_email, $user_website, $user_avatar, $user_regdate, $user_from, $user_sig, $user_viewemail, $user_points, $user_warnings, $user_gender, $user_votes, $user_totalvotes, $user_gname, $user_grank, $user_gcolor);
				unset($user_id, $user_name, $user_rank, $user_email, $user_website, $user_avatar, $user_regdate, $user_from, $user_sig, $user_viewemail, $user_points, $user_warnings, $user_gender, $user_votes, $user_totalvotes, $user_gname, $user_grank, $user_gcolor);
			}
		}
		if ($num == 1) {
			$tstatus = $cmassiv[0][17];
		} else {
			list($tstatus) = $db->sql_fetchrow($db->sql_query("SELECT status FROM ".$prefix."_forum WHERE id = '".$id."'"));
		}
		$isread = is_acess($cmassiv[0][19]);
		$istopic = is_acess($cmassiv[0][20]);
		$isreply = is_acess($cmassiv[0][21]);
		$isedit = is_acess($cmassiv[0][22]);
		$isdelete = is_acess($cmassiv[0][23]);
		$ismod = is_acess($cmassiv[0][24]);
		head($conf['defis']." "._FORUM." ".$conf['defis']." ".$cmassiv[0][18]." ".$conf['defis']." ".$cmassiv[0][5], $cmassiv[0][7]);
		if ($ismod || ($isread && $tstatus > 1)) {
			$catlink = catlink($conf['name'], $cmassiv[0][2], $conffo['defis'], _FORUM);
			$atopic = (is_moder($conf['name']) || $istopic) ? "<a href=\"index.php?name=".$conf['name']."&amp;op=add&amp;cat=".$cmassiv[0][2]."\" title=\""._NEWTOPIC."\" class=\"sl_but\">"._OPEN."</a>" : "<span title=\"".sprintf(_ACINFOT, _NOTCAN)."\" class=\"sl_but sl_hidden\">"._OPEN."</span>";
			$areply = (is_moder($conf['name']) || ($isreply && $tstatus)) ? "<a href=\"index.php?name=".$conf['name']."&amp;op=add&amp;cat=".$cmassiv[0][2]."&amp;pid=".$id."\" title=\""._TOPICREPLY."\" class=\"sl_but\">"._REPLY."</a>" : "<span title=\"".sprintf(_ACINFOP, _NOTCAN)."\" class=\"sl_but sl_hidden\">"._REPLY."</span>";
			$pnum = num_page("forum-pagenum", $conf['name'], $numfor, $numpages, $fornum, "op=view&amp;id=".$id."&amp;", $conffo['pnum'], $num);
			$favor = favorview($id, $conf['name']);
			$cont = tpl_eval("forum-view-open", $catlink, $atopic, $areply, search_color($cmassiv[0][5], $word), $pnum, $favor);
			foreach ($cmassiv as $val) {
				$for_id = $val[0];
				$for_sid = $val[1];
				$for_catid = $val[2];
				/*
				$sid = $val[0];
				$pid = $val[1];
				$catid = $val[2];
				$uid = $val[3];
				$name = $val[4];
				$title = $val[5];
				$time = $val[6];
				$hometext = $val[7];
				$field = $val[8];
				$comments = $val[9];
				$counter = $val[10];
				$score = $val[11];
				$ratings = $val[12];
				$ip_send = $val[13];
				$e_uid = $val[14];
				$e_ip_send = $val[15];
				$e_time = $val[16];
				$status = $val[17];
				*/
				unset($user_id, $user_name, $user_rank, $user_email, $user_website, $user_avatar, $user_regdate, $user_from, $user_sig, $user_viewemail, $user_points, $user_warnings, $user_gender, $user_votes, $user_totalvotes, $user_gname, $user_grank, $user_gcolor);
				if ($umassiv) {
					foreach ($umassiv as $val2) {
						if (strtolower($val[3]) == strtolower($val2[0])) {
							$user_id = $val2[0];
							$user_name = $val2[1];
							$user_rank = $val2[2];
							$user_email = $val2[3];
							$user_website = $val2[4];
							$user_avatar = $val2[5];
							$user_regdate = $val2[6];
							$user_from = $val2[7];
							$user_sig = $val2[8];
							$user_viewemail = $val2[9];
							$user_points = $val2[10];
							$user_warnings = $val2[11];
							$user_gender = $val2[12];
							$user_votes = $val2[13];
							$user_totalvotes = $val2[14];
							$user_gname = $val2[15];
							$user_grank = $val2[16];
							$user_gcolor = $val2[17];
						}
					}
				}
				$avname = (!empty($user_name)) ? $user_name : $val[4]." (".$confu['anonym'].")";
				$avatar = (!empty($user_name)) ? (($user_avatar && file_exists($confu['adirectory']."/".$user_avatar)) ? $confu['adirectory']."/".$user_avatar : $confu['adirectory']."/default/00.gif") : $confu['adirectory']."/default/0.gif";
				$date = (($ismod || $conffo['ledit']) && $val[16]) ? "<span title=\""._PADD."\" class=\"sl_t_post\">".format_time($val[6], _TIMESTRING)."</span><span title=\""._PEDIT."\" class=\"sl_t_edit\">".format_time($val[16], _TIMESTRING)."</span>" : "<span title=\""._PADD."\" class=\"sl_t_post\">".format_time($val[6], _TIMESTRING)."</span>";
				$rating = ($a == 1) ? ajax_rating(1, $for_id, $conf['name'], $val[12], $val[11], "", 1) : "";
				$ip = ($ismod && $val[13]) ? user_geo_ip($val[13], 4) : "";
				$amess = "<a href=\"#".$for_id."\" title=\""._MESSAGE.": ".$a."\" class=\"sl_pnum\">".$a."</a>";
				$rank = (!empty($user_rank)) ? $user_rank : "";
				$trank = (!empty($user_gname)) ? _GROUP.": ".$user_gname : _RANK;
				$rlink = (!empty($user_grank) && file_exists(img_find("ranks/".$user_grank))) ? "<img src=\"".img_find("ranks/".$user_grank)."\" alt=\"".$trank."\" title=\"".$trank."\">" : "";
				$rate = (!empty($user_id)) ? ajax_rating(0, $user_id, "account", $user_votes, $user_totalvotes, $for_id, 1) : "";
				$rwarn = (!empty($user_warnings)) ? _UWARNS.": ".warnings($user_warnings) : "";
				$group = (!empty($user_gname)) ? _GROUP.": <span style=\"color: ".$user_gcolor."\">".$user_gname."</span>" : "";
				$point = ($confu['point'] && !empty($user_points)) ? _POINTS.": ".$user_points : "";
				$regdate = (!empty($user_regdate)) ? _REG.": ".format_time($user_regdate) : _NO_INFO;
				$gender = (!empty($user_gender)) ? _GENDER.": ".gender($user_gender) : "";
				$from = (!empty($user_from)) ? _FROM.": ".$user_from : "";
				$fields = fields_out($val[8], $conf['name']);
				$sig = (!empty($user_sig)) ? "<hr>".$user_sig : "";
				$personal = (is_moder($conf['name']) || ($isreply && $tstatus && $conffo['qreply'])) ? "<a href=\"javascript: InsertCode('name', '".$avname."', '', '', '1');\" title=\""._PERSONAL."\" class=\"sl_but_blue\">"._PERS."</a>" : "";
				$privat = ($conffo['privat'] && $confpr['act'] && !empty($user_name)) ? "<a href=\"index.php?name=account&amp;op=privat&amp;uname=".urlencode($user_name)."\" title=\""._SENDMES."\" class=\"sl_but_green\">"._MESSAGE."</a>" : "";
				$profil = ($conffo['profil'] && !empty($user_name)) ? "<a href=\"index.php?name=account&amp;op=view&amp;uname=".urlencode($user_name)."\" title=\""._PERSONALINFO."\" class=\"sl_but\">"._ACCOUNT."</a>" : "";
				$web = ($conffo['web'] && !empty($user_website)) ? "<a href=\"".$user_website."\" target=\"_blank\" title=\""._DOWNLLINK."\" class=\"sl_but\">"._SITE."</a>" : "";
				
				# Будущие функции
				#$warn = "<a href=\"javascript: scroll(0, 0);\" title=\""._WARNM."\">"._WARNM."</a>";
				#$thank = "<a href=\"javascript: scroll(0, 0);\" title=\""._THANK."\">"._THANK."</a>";
				$warn = "";
				$thank = "";
				
				$qreply = (is_moder($conf['name']) || ($isreply && $tstatus)) ? "<a href=\"index.php?name=".$conf['name']."&amp;op=add&amp;cat=".$for_catid."&amp;pid=".$id."&amp;qid=".$for_id."\" title=\""._QREPLY."\" class=\"sl_but_blue\">"._REPLY."</a>" : "";
				$edit = ($ismod || ($isedit && $val[3] == intval($user[0]) && $tstatus)) ? "<a href=\"#\" OnClick=\"AjaxLoad('GET', '1', 'for".$for_id."', 'go=1&amp;op=editpost&amp;id=".$for_id."&amp;cid=".$for_catid."&amp;typ=1&amp;mod=".$conf['name']."', ''); return false;\" title=\""._ONEDIT."\">"._ONEDIT."</a>||<a href=\"index.php?name=".$conf['name']."&amp;op=add&amp;cat=".$for_catid."&amp;id=".$for_id."&amp;pid=".$id."\" title=\""._FULLEDIT."\">"._FULLEDIT."</a>||" : "";
				$edit .= ($ismod || ($isdelete && $val[3] == intval($user[0]))) ? "<a href=\"index.php?name=".$conf['name']."&amp;op=delete&amp;cat=".$for_catid."&amp;id=".$for_id."\" OnClick=\"return DelCheck(this, '"._DELETE." &quot;".$val[5]."&quot;?');\" title=\""._ONDELETE."\">"._ONDELETE."</a>" : "";
				$edit = ($edit) ? add_menu($edit) : "";
				$hclass = (!$val[17]) ? "title=\""._PCLOSED."\" class=\"sl_hidden\"" : "";
				$text = ($fields) ? "<div id=\"repfor".$for_id."\">".search_color(bb_decode($val[7], $conf['name']), $word)."</div>".search_color(bb_decode("<br><br>".$fields, $conf['name']), $word) : "<div id=\"repfor".$for_id."\">".search_color(bb_decode($val[7], $conf['name']), $word)."</div>";
				$cont .= tpl_eval("forum-view-basic", $for_id, $avname, $date, $rating, $ip, $amess, $avatar, $rank, $rlink, $rate, $rwarn, $group, $point, $regdate, $gender, $from, $text, bb_decode($sig, $conf['name']), $personal, $privat, $profil, $web, $warn, $thank, $qreply, $edit, $hclass);
				if ($conffo['sort']) { $a++; } else { $a--; }
			}
			$pnum = num_page("forum-pagenum", $conf['name'], $numfor, $numpages, $fornum, "op=view&amp;id=".$id."&amp;", $conffo['pnum'], $num);
			$cont .= tpl_eval("forum-view-close", $atopic, $areply, $catlink, $pnum);
			if ($ismod) {
				$selmm = "<form action=\"index.php?name=".$conf['name']."\" method=\"post\">".tmoder(1)." <input type=\"hidden\" name=\"op\" value=\"move\"><input type=\"hidden\" name=\"cat\" value=\"".$cmassiv[0][2]."\"><input type=\"hidden\" name=\"id[]\" value=\"".$id."\"> <input type=\"submit\" value=\""._OK."\" class=\"sl_but_blue\"></form>";
				$cont .= tpl_eval("forum-view-change", _OPMOD.": ", $selmm);
			}
			if (is_moder($conf['name']) || ($isreply && $tstatus)) $cont .= quickreply($id, $cmassiv[0][2], $cmassiv[0][5]);
		} else {
			$cont = tpl_warn("warn", _NOVIEW, "?name=".$conf['name'], 5, "warn");
		}
		echo $cont;
		foot();
	} else {
		header("Location: index.php?name=".$conf['name']);
	}
}

function quickreply($id, $catid, $subject) {
	global $prefix, $db, $conf, $confu, $conffo;
	if ($conffo['qreply'] == 1) {
		$cont = "<form name=\"post\" action=\"index.php?name=".$conf['name']."\" method=\"post\"><table class=\"sl_table_form\">";
		$cont .= (!is_user()) ? "<tr><td>"._YOURNAME.":</td><td><input type=\"text\" name=\"postname\" value=\"".$confu['anonym']."\" class=\"sl_field ".$conf['style']."\" placeholder=\""._YOURNAME."\" required></td></tr>" : "";
		$cont .= "<tr><td>"._TEXT.":</td><td>".textarea("1", "hometext", "", $conf['name'], "10", _TEXT, "1")."</td></tr>"
		.fields_in(isset($field), $conf['name'])
		."<tr><td colspan=\"2\" class=\"sl_center\"><input type=\"hidden\" name=\"subject\" value=\"".$subject."\"><input type=\"hidden\" name=\"pid\" value=\"".$id."\"><input type=\"hidden\" name=\"cat\" value=\"".$catid."\"><input type=\"hidden\" name=\"posttype\" value=\"save\"><input type=\"hidden\" name=\"op\" value=\"send\"><input type=\"submit\" value=\""._SEND."\" class=\"sl_but_blue\"></td></tr>"
		."</table></form>";
		return tpl_eval("forum-all-open", _QUICKREPLY).$cont.tpl_eval("forum-all-close");
	}
}

function move() {
	global $prefix, $db, $user, $conf, $conffo;
	$catid = intval($_POST['cat']);
	if ($conffo['add'] && $catid) {
		list($auth_mod) = $db->sql_fetchrow($db->sql_query("SELECT auth_mod FROM ".$prefix."_categories WHERE id = '".$catid."'"));
		$ismod = is_acess($auth_mod);
		$id = isset($_POST['id']) ? $_POST['id'] : "";
		$vtmove = isset($_POST['tmove']) ? $_POST['tmove'] : "";
		$tmove = (is_numeric($vtmove[0])) ? intval($vtmove) : intval(substr($vtmove, 1));
		if ($ismod && is_array($id) && $vtmove[0]) {
			foreach ($id as $val) {
				if (intval($val)) {
					if ($vtmove[0] == "s") {
						$db->sql_query("UPDATE ".$prefix."_forum SET status = '".$tmove."' WHERE id = '".$val."'");
					} elseif ($vtmove[0] == "d") {
						delete($catid, $val);
					} elseif (is_numeric($vtmove[0])) {
						$rcatids = catids($conf['name'], $tmove);
						$db->sql_query("UPDATE ".$prefix."_forum SET catid = '".$tmove."' WHERE id = '".$val."' OR pid = '".$val."'");
						list($rnpost) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_forum WHERE pid = '".$val."'"));
						$wrnpost = ($rnpost) ? ", posts=posts+".$rnpost : "";
						$db->sql_query("UPDATE ".$prefix."_categories SET topics=topics+1".$wrnpost.", lpost_id = '".$val."' WHERE id IN (".$rcatids.")");
			
						$catids = catids($conf['name'], $catid);
						list($l_id) = $db->sql_fetchrow($db->sql_query("SELECT lpost_id FROM ".$prefix."_categories WHERE id = '".$catid."'"));
						list($npost) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_forum WHERE pid = '".$val."'"));
						$wnpost = ($npost) ? ", posts=posts-".$npost : "";
						if ($l_id == $val) {
							list($lid) = $db->sql_fetchrow($db->sql_query("SELECT id FROM ".$prefix."_forum WHERE catid = '".$catid."' AND ((pid != '0' && status = '1') || (pid = '0' && status > '1')) ORDER BY id DESC LIMIT 1"));
							$db->sql_query("UPDATE ".$prefix."_categories SET topics=topics-1".$wnpost.", lpost_id = '".$lid."' WHERE id IN (".$catids.")");
						} else {
							$db->sql_query("UPDATE ".$prefix."_categories SET topics=topics-1".$wnpost." WHERE id IN (".$catids.")");
						}
					}
				}
			}
		}
	}
	$link = ($catid) ? "&cat=".$catid : "";
	header("Location: index.php?name=".$conf['name'].$link);
}

function add() {
	global $prefix, $db, $user, $conf, $confu, $conffo, $stop;
	$catid = (isset($_POST['cat'])) ? intval($_POST['cat']) : intval($_GET['cat']);
	list($ctitle, $auth_post, $auth_reply, $auth_edit, $auth_mod) = $db->sql_fetchrow($db->sql_query("SELECT title, auth_post, auth_reply, auth_edit, auth_mod FROM ".$prefix."_categories WHERE id = '".$catid."'"));
	$istopic = is_acess($auth_post);
	$isreply = is_acess($auth_reply);
	$isedit = is_acess($auth_edit);
	$ismod = is_acess($auth_mod);
	
	$form = false;
	$id = (isset($_POST['id'])) ? intval($_POST['id']) : (isset($_GET['id']) ? intval($_GET['id']) : "");
	$pid = (isset($_POST['pid'])) ? intval($_POST['pid']) : (isset($_GET['pid']) ? intval($_GET['pid']) : "");
	
	$where = (is_moder($conf['name'])) ? "WHERE id = '".$pid."'" : "WHERE id = '".$pid."' AND status != '0'";
	list($fstatus) = $db->sql_fetchrow($db->sql_query("SELECT status FROM ".$prefix."_forum ".$where));

	# Редактируем сообщение или тему
	if ($conffo['add'] && $id) {
		$fid = $id;
		list($qpid, $uid, $subject, $time, $hometext, $field, $status) = $db->sql_fetchrow($db->sql_query("SELECT pid, uid, title, time, hometext, field, status FROM ".$prefix."_forum WHERE id = '".$id."'"));
		if ($ismod || ($isedit && $uid == intval($user[0]) && $fstatus > 2)) {
			$subh = ($qpid) ? 1 : 0;
			$info = _EDITS.": ".$subject;
			$pagetitle = $conf['defis']." "._FORUM." ".$conf['defis']." ".$ctitle." ".$conf['defis']." ".$info;
			$form = true;
		}
		$subject = (isset($_POST['subject'])) ? save_text($_POST['subject'], 1) : $subject;
		$hometext = (isset($_POST['hometext'])) ? save_text($_POST['hometext']) : $hometext;

	# Отвечаем и создаём
	} elseif ($conffo['add'] && ($istopic || $isreply)) {
		$fid = (isset($_POST['fid'])) ? intval($_POST['fid']) : "";

		$qid = (isset($_GET['qid'])) ? intval($_GET['qid']) : "";
		$subh = (!empty($pid) || !empty($qpid)) ? 1 : 0;

		# Отвечаем в существующей теме
		if ($pid) {
			$id = ($qid) ? $qid : $pid;
			list($ftitle, $ftext, $status) = $db->sql_fetchrow($db->sql_query("SELECT title, hometext, status FROM ".$prefix."_forum WHERE id = '".$id."'"));
			$form = (is_moder($conf['name'])) ? true : (($fstatus > 2) ? true : false);
		
		# Создаём новую тему
		} else {
			$form = true;
		}

		$postname = (isset($_POST['postname'])) ? $_POST['postname'] : "";
		$subject = (!empty($ftitle)) ? $ftitle : (isset($_POST['subject']) ? save_text($_POST['subject'], 1) : "");
		$hometext = ($qid && $ftext) ? "[quote]".$ftext."[/quote]" : (isset($_POST['hometext']) ? save_text($_POST['hometext']) : "");
		$field = (isset($_POST['field'])) ? fields_save($_POST['field']) : "";
		$status = (isset($_POST['status'])) ? intval($_POST['status']) : 3;
		$time = save_datetime(1, "time");
		$info = (!empty($ftext)) ? _PUBLICIN.": ".$ftitle : _PUBLICIN.": ".$ctitle;
		$pagetitle = $conf['defis']." "._FORUM." ".$conf['defis']." ".$ctitle." ".$conf['defis']." ".$info;
		
	}
	if ($form) {
		head($pagetitle);
		$cont = ($stop) ? tpl_warn("warn", $stop, "", "", "warn") : "";
		$psubject = (!$subh) ? $subject : "";
		if ($hometext) $cont .= preview($psubject, $hometext, "", $field, $conf['name']);
		$userinfo = getusrinfo();
		if ($userinfo['user_acess'] || (!is_user() && !$conffo['anonpost'])) $cont .= tpl_warn("warn", _POSTNOTE, "", "", "warn");
		$cont .= tpl_eval("forum-all-open", $info);
		$cont .= "<form name=\"post\" action=\"index.php?name=".$conf['name']."\" method=\"post\"><table class=\"sl_table_form\">";
		$cont .= (!is_user()) ? "<tr><td>"._YOURNAME.":</td><td><input type=\"text\" name=\"postname\" value=\"".$confu['anonym']."\" class=\"sl_field ".$conf['style']."\" placeholder=\""._YOURNAME."\" required></td></tr>" : "";
		$cont .= ($subh) ? "<input type=\"hidden\" name=\"subject\" value=\"".$subject."\">" : "<tr><td>"._TITLE.":</td><td><input type=\"text\" name=\"subject\" value=\"".$subject."\" maxlength=\"100\" class=\"sl_field ".$conf['style']."\" placeholder=\""._TITLE."\" required></td></tr>";
		$cont .= "<tr><td>"._TEXT.":</td><td>".textarea("1", "hometext", $hometext, $conf['name'], "15", _TEXT, "1")."</td></tr>".fields_in($field, $conf['name']);
		$cont .= ($ismod) ? "<tr><td>"._OPMOD.":</td><td>".pmoder($status, $subh)."</td></tr><tr><td>"._CHNGSTORY.":</td><td>".datetime(1, "time", $time, 16, $conf['style'])."</td></tr>" : "";
		$cont .= "<tr><td colspan=\"2\" class=\"sl_center\">"
		."<input type=\"hidden\" name=\"id\" value=\"".$id."\">"
		."<input type=\"hidden\" name=\"fid\" value=\"".$fid."\">"
		."<input type=\"hidden\" name=\"pid\" value=\"".$pid."\">"
		."<input type=\"hidden\" name=\"cat\" value=\"".$catid."\">"
		.ad_save("", "", "send")."</td></tr></table></form>";
	} else {
		$info = ($conffo['add']) ? _NOVIEW : _WARNPF;
		$pagetitle = $conf['defis']." "._FORUM." ".$conf['defis']." ".$ctitle." ".$conf['defis']." ".$ctitle;
		head($pagetitle);
		$cont = tpl_eval("forum-all-open", $ctitle);
		$cont .= tpl_warn("warn", $info, "?name=".$conf['name'], 5, "warn");
	}
	$cont .= tpl_eval("forum-all-close");
	echo $cont;
	foot();
}

function tmoder($typ) {
	global $conf;
	$cont = "<select name=\"tmove\" title=\""._CHECKOP."\" class=\"sl_field ".$conf['style']."\">";
	$cont .= "<optgroup label=\""._OPMOD."\" class=\"sl_label\">";
	$mass = array(_FMODC => "s0", _FMODCA => "s1", _FMODCR => "s2", _FMODCW => "s3", _FMODCH => "s4", _FMODCO => "s5");
	$mass = ($typ) ? array_merge($mass, array(_DELETE => "d")) : $mass;
	foreach ($mass as $var_n => $var_v) $cont .= "<option value=\"".$var_v."\">".$var_n."</option>";
	$cont .= "</optgroup><optgroup label=\""._MOVETO."\" class=\"sl_label\">".getcat($conf['name'], "", "", "", "", "1")."</optgroup>";
	$cont .= "</select>";
	return $cont;
}

function pmoder($status, $subh) {
	global $conf;
	$cont = "<select name=\"status\" title=\""._CHECKOP."\" class=\"sl_field ".$conf['style']."\">";
	$mass = ($subh) ? array(_CLOSE => 0, _OPEN => 1) : array(_FMODC => 0, _FMODCA => 1, _FMODCR => 2, _FMODCW => 3, _FMODCH => 4, _FMODCO => 5);
	foreach ($mass as $var_n => $var_v) {
		$sel = ($status == $var_v) ? " selected" : "";
		$cont .= "<option value=\"".$var_v."\"".$sel.">".$var_n."</option>";
	}
	$cont .= "</select>";
	return $cont;
}

function send() {
	global $prefix, $db, $user, $conf, $conffo, $stop;
	$catid = (isset($_POST['cat'])) ? intval($_POST['cat']) : intval($_GET['cat']);
	if ($conffo['add'] && $catid) {
		list($ctitle, $auth_post, $auth_reply, $auth_edit, $auth_mod) = $db->sql_fetchrow($db->sql_query("SELECT title, auth_post, auth_reply, auth_edit, auth_mod FROM ".$prefix."_categories WHERE id = '".$catid."'"));
		$istopic = is_acess($auth_post);
		$isreply = is_acess($auth_reply);
		$isedit = is_acess($auth_edit);
		$ismod = is_acess($auth_mod);
		
		$id = (isset($_POST['fid'])) ? intval($_POST['fid']) : "";
		$pid = (isset($_POST['pid'])) ? intval($_POST['pid']) : "";
		$postname = (isset($_POST['postname'])) ? text_filter(substr($_POST['postname'], 0, 25)) : "";
		$subject = (isset($_POST['subject'])) ? save_text($_POST['subject'], 1) : "";
		$hometext = (isset($_POST['hometext'])) ? $_POST['hometext'] : "";

		$checks = str_replace(array("\n", "\r", "\t"), " ", $hometext);
		$e = explode(" ", $checks);
		for ($a = 0; $a < count($e); $a++) $o = strlen($e[$a]);
		$hometext = save_text($hometext);
		$status = (isset($_POST['status'])) ? intval($_POST['status']) : 0;
		
		$field = fields_save($_POST['field']);
		$time = ($ismod) ? save_datetime(1, "time") : save_datetime(1);
		$postid = (is_user()) ? intval($user[0]) : "";
		$ip = getip();
		
		$stop = array();
		if (!$subject) $stop[] = _CERROR;
		if (!$hometext) $stop[] = _CERROR1;
		if ($o > $conffo['letter']) $stop[] = _CERROR2;
		if (!$postname && !is_user()) $stop[] = _CERROR3;

		if (!$stop && $_POST['posttype'] == "save") {
			$where = (is_moder($conf['name'])) ? "WHERE id = '".$pid."'" : "WHERE id = '".$pid."' AND status != '0'";
			list($fstatus) = $db->sql_fetchrow($db->sql_query("SELECT status FROM ".$prefix."_forum ".$where));
			
			# Редактируем сообщение или тему
			if ($id) {
				list($fpid, $uid, $ftime) = $db->sql_fetchrow($db->sql_query("SELECT pid, uid, time FROM ".$prefix."_forum WHERE id = '".$id."'"));
				$fpid = ($fpid) ? $fpid : $id;
				if ($ismod || ($isedit && $uid == intval($user[0]) && $fstatus > 2)) {
					$ftime = ($ismod) ? $time : $ftime;
					if ($ismod) {
						$db->sql_query("UPDATE ".$prefix."_forum SET title = '".$subject."', time = '".$ftime."', hometext = '".$hometext."', field = '".$field."', e_uid = '".$postid."', e_ip_send = '".$ip."', e_time = now(), status = '".$status."' WHERE id = '".$id."'");
					} else {
						$db->sql_query("UPDATE ".$prefix."_forum SET title = '".$subject."', time = '".$ftime."', hometext = '".$hometext."', field = '".$field."', e_uid = '".$postid."', e_ip_send = '".$ip."', e_time = now() WHERE id = '".$id."'");
					}
				}
			
			# Отвечаем и создаём
			} else {
				if ($ismod) {
					$userinfo = getusrinfo();
					$postname = ($userinfo['user_name']) ? $userinfo['user_name'] : $postname;
					$status = ($status) ? $status : (($pid) ? 1 : 3);
				} elseif (is_user()) {
					$userinfo = getusrinfo();
					$postname = $userinfo['user_name'];
					$status = ($userinfo['user_acess']) ? 0 : (($pid) ? 1 : 3);
				} else {
					$postid = "";
					$postname = $postname;
					$status = ($conffo['anonpost'] == 1) ? (($pid) ? 1 : 3) : 0;
				}
				$insert = false;

				# Отвечаем в существующей теме
				if ($pid && $isreply) {
					$insert = (is_moder($conf['name'])) ? true : (($fstatus > 2) ? true : false);
					
				# Создаём новую тему
				} elseif ($istopic) {
					$insert = true;
				}
				
				if ($insert) {
					$catids = catids($conf['name'], $catid);
					$db->sql_query("INSERT INTO ".$prefix."_forum (id, pid, catid, uid, name, title, time, hometext, field, ip_send, l_uid, l_name, l_time, status) VALUES (NULL, '".$pid."', '".$catid."', '".$postid."', '".$postname."', '".$subject."', '".$time."', '".$hometext."', '".$field."', '".$ip."', '".$postid."', '".$postname."', '".$time."', '".$status."')");
					list($lpost_id, $ltime) = $db->sql_fetchrow($db->sql_query("SELECT id, time FROM ".$prefix."_forum WHERE catid = '".$catid."' AND uid = '".$postid."' ORDER BY id DESC LIMIT 1"));
					if ($pid) {
						$lname = ($uname) ? $uname : $postname;
						$db->sql_query("UPDATE ".$prefix."_forum SET comments = comments+1, l_uid = '".$postid."', l_name = '".$lname."', l_id = '".$lpost_id."', l_time = '".$time."' WHERE id = '".$pid."'");
						$db->sql_query("UPDATE ".$prefix."_categories SET posts = posts+1, lpost_id = '".$pid."' WHERE id IN (".$catids.")");
						if ($conffo['addmail']) {
							list($muid) = $db->sql_fetchrow($db->sql_query("SELECT uid FROM ".$prefix."_forum WHERE id = '".$pid."'"));
							if ($postid != $muid) {
								list($user_email, $user_fsmail) = $db->sql_fetchrow($db->sql_query("SELECT user_email, user_fsmail FROM ".$prefix."_users WHERE user_id = '".$muid."'"));
								if ($user_email && $user_fsmail) {
									$finishlink = $conf['homeurl']."/index.php?name=forum&amp;op=view&amp;id=".$pid."#".$lpost_id;
									$link = "<a href=\"".$finishlink."\">".$finishlink."</a>";
									$subject = $conf['sitename']." - "._FORUM;
									$message = str_replace("[text]", sprintf(_ADDMAILF, $postname, $link), $conf['mtemp']);
									mail_send($user_email, $conf['adminmail'], $subject, $message, 0, 3);
								}
							}
						}
						update_points(14);
					} else {
						if (strtotime($ltime) > time()) {
							$db->sql_query("UPDATE ".$prefix."_categories SET topics = topics+1, posts = posts+1 WHERE id IN (".$catids.")");
						} else {
							$db->sql_query("UPDATE ".$prefix."_categories SET topics = topics+1, posts = posts+1, lpost_id = '".$lpost_id."' WHERE id IN (".$catids.")");
						}
						update_points(13);
					}
				}
			}
			$lid = ($fpid) ? $fpid : (($pid) ? $pid."&last#".$lpost_id : "");
			$link = ($lid) ? "&op=view&id=".$lid : "&cat=".$catid;
			header("Location: index.php?name=".$conf['name'].$link);
		} else {
			add();
		}
	} else {
		header("Location: index.php?name=".$conf['name']);
	}
}

function delete() {
	global $prefix, $db, $user, $conf, $conffo;
	$arg = func_get_args();
	$catid = ($arg[0]) ? $arg[0] : ((isset($_POST['cat'])) ? intval($_POST['cat']) : intval($_GET['cat']));
	$id = ($arg[1]) ? $arg[1] : ((isset($_POST['id'])) ? intval($_POST['id']) : intval($_GET['id']));
	if ($conffo['add'] && $catid && $id) {
		list($auth_delete, $auth_mod) = $db->sql_fetchrow($db->sql_query("SELECT auth_delete, auth_mod FROM ".$prefix."_categories WHERE id = '".$catid."'"));
		$isdelete = is_acess($auth_delete);
		$ismod = is_acess($auth_mod);
		
		list($pid, $uid) = $db->sql_fetchrow($db->sql_query("SELECT pid, uid FROM ".$prefix."_forum WHERE id = '".$id."'"));
		if ($ismod || ($isdelete && $uid == intval($user[0]))) {
			$recycle = intval($conffo['recycle']);
			
			# Перенос в форум, используемый в качестве корзины
			
			if ($recycle && $recycle != $catid) {
				$rcatids = catids($conf['name'], $recycle);
				# Сообщение
				if ($pid) {
					$db->sql_query("UPDATE ".$prefix."_forum SET pid = '0', catid = '".$recycle."' WHERE id = '".$id."'");
					$db->sql_query("UPDATE ".$prefix."_categories SET topics = topics+1, lpost_id = '".$id."' WHERE id IN (".$rcatids.")");
				# Тема
				} else {
					$db->sql_query("UPDATE ".$prefix."_forum SET catid = '".$recycle."' WHERE id = '".$id."' OR pid = '".$id."'");
					list($rnpost) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_forum WHERE pid = '".$id."'"));
					$wrnpost = ($rnpost) ? ", posts=posts+".$rnpost : "";
					$db->sql_query("UPDATE ".$prefix."_categories SET topics = topics+1".$wrnpost.", lpost_id = '".$id."' WHERE id IN (".$rcatids.")");
				}
			}
			
			# Синхронизация форумов и тем
			
			# Сообщение
			$catids = catids($conf['name'], $catid);

			if ($pid) {
				list($l_id) = $db->sql_fetchrow($db->sql_query("SELECT l_id FROM ".$prefix."_forum WHERE id = '".$pid."'"));
				if ($l_id == $id) {
					list($lid, $luid, $lname, $ltime) = $db->sql_fetchrow($db->sql_query("SELECT id, uid, name, time FROM ".$prefix."_forum WHERE pid = '".$pid."' OR id = '".$pid."' ORDER BY id DESC LIMIT 1"));
					$db->sql_query("UPDATE ".$prefix."_forum SET comments = comments-1, l_uid = '".$luid."', l_name = '".$lname."', l_id = '".$lid."', l_time = '".$ltime."' WHERE id = '".$pid."'");
				} else {
					$db->sql_query("UPDATE ".$prefix."_forum SET comments = comments-1 WHERE id = '".$pid."'");
				}
				$db->sql_query("UPDATE ".$prefix."_categories SET posts = posts-1 WHERE id IN (".$catids.")");

			# Тема
			} else {
				list($l_id) = $db->sql_fetchrow($db->sql_query("SELECT lpost_id FROM ".$prefix."_categories WHERE id = '".$catid."'"));
				list($npost) = $db->sql_fetchrow($db->sql_query("SELECT Count(id) FROM ".$prefix."_forum WHERE pid = '".$id."'"));
				$wnpost = ($npost) ? ", posts=posts-".$npost : "";
				if ($l_id == $id) {
					list($lid) = $db->sql_fetchrow($db->sql_query("SELECT id FROM ".$prefix."_forum WHERE catid = '".$catid."' AND ((pid != '0' && status = '1') || (pid = '0' && status > '1')) ORDER BY id DESC LIMIT 1"));
					$db->sql_query("UPDATE ".$prefix."_categories SET topics = topics-1".$wnpost.", lpost_id = '".$lid."' WHERE id IN (".$catids.")");
				} else {
					$db->sql_query("UPDATE ".$prefix."_categories SET topics = topics-1".$wnpost." WHERE id IN (".$catids.")");
				}
			}
			
			# Удаление тем и сообщений
			
			if (!$recycle || $recycle == $catid) {
			
				# Удаление пунктов пользователей за тему или сообщение
				if ($uid) {
					# Сообщение
					if ($pid) {
						update_points(14, $uid, 1);
					# Тема
					} else {
						update_points(13, $uid, 1);
					}
				}
				# Проверка, добавлена ли тема в фавориты, если да, удаление фаворитов и пунктов за них
				list($fid, $fuid) = $db->sql_fetchrow($db->sql_query("SELECT id, uid FROM ".$prefix."_favorites WHERE fid = '".$id."' AND modul = 'forum'"));
				if ($fid) {
					if ($fuid) update_points(44, $fuid, 1);
					$db->sql_query("DELETE FROM ".$prefix."_favorites WHERE id = '".$fid."'");
				}
				# Удаление темы и сообщений
				$db->sql_query("DELETE FROM ".$prefix."_forum WHERE id = '".$id."' OR pid = '".$id."'");
			}
			
		}
		
		$lid = ($pid) ? $pid."&last#".$lid : "";
		$link = ($lid) ? "&op=view&id=".$lid : "&cat=".$catid;
		if (!$arg[0] && !$arg[1]) header("Location: index.php?name=".$conf['name'].$link);
	} else {
		header("Location: index.php?name=".$conf['name']);
	}
}

switch($op) {
	default:
	forum();
	break;
	
	case "view":
	view();
	break;
	
	case"move":
	move();
	break;
	
	case "add":
	add();
	break;
	
	case "send":
	send();
	break;
	
	case "delete":
	delete();
	break;
}
?>