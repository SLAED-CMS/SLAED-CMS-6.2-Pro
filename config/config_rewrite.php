<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("FUNC_FILE")) die("Illegal file access");

$out[] = "rss-\\2-\\4-num-\\6.html";
$in[] = "'(?<!/)index.php\?go=rss&(amp;)?name=([a-zA-Z0-9_]*)&(amp;)?cat=([0-9]*)&(amp;)?num=([0-9]*)'";
$out[] = "rss-\\2-\\4.html";
$in[] = "'(?<!/)index.php\?go=rss&(amp;)?name=([a-zA-Z0-9_]*)&(amp;)?cat=([0-9]*)'";
$out[] = "rss-\\2-num-\\4.html";
$in[] = "'(?<!/)index.php\?go=rss&(amp;)?name=([a-zA-Z0-9_]*)&(amp;)?num=([0-9]*)'";
$out[] = "rss-num-\\2.html";
$in[] = "'(?<!/)index.php\?go=rss&(amp;)?num=([0-9]*)'";
$out[] = "rss-\\2-id-\\4.html";
$in[] = "'(?<!/)index.php\?go=rss&(amp;)?name=([a-zA-Z0-9_]*)&(amp;)?id=([0-9]*)'";
$out[] = "rss-\\2.html";
$in[] = "'(?<!/)index.php\?go=rss&(amp;)?name=([a-zA-Z0-9_]*)'";
$out[] = "rss.html";
$in[] = "'(?<!/)index.php\?go=rss'";

$out[] = "open-search.html";
$in[] = "'(?<!/)index.php\?go=search'";

$massiv = array("account", "auto_links", "contact", "content", "faq", "files", "forum", "help", "jokes", "links", "media", "news", "pages", "recommend", "rss_info", "search", "shop", "top_users", "voting", "whois");
foreach ($massiv as $val) {
	$out[] = $val."-clients.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=clients'";
	$out[] = $val."-partners.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=partners'";
	$out[] = $val."-edithome.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=edithome'";
	$out[] = $val."-logout-refer.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=logout&(amp;)?refer=1'";
	$out[] = $val."-logout.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=logout'";
	$out[] = $val."-newuser.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=newuser'";
	$out[] = $val."-passlost.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=passlost'";
	$out[] = $val."-privat-\\3.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=privat&(amp;)?uname=([a-zA-Zа-яА-Я0-9\s%&/|{}().:;&_+\-=]*)'";
	$out[] = $val."-privat.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=privat'";
	$out[] = $val."-favorites.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=favorites'";
	$out[] = $val."-info-\\3.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=view&(amp;)?uname=([a-zA-Zа-яА-Я0-9\s%&/|{}().:;&_+\-=]*)'";
	$out[] = $val."-rech-\\3.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=rech&(amp;)?id=([0-9]*)'";
	$out[] = $val."-avatar-\\3.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=saveavatar&(amp;)?avatar=([a-zA-Zа-яА-Я0-9\s%&/|{}().:;&_+\-=]*)'";
	$out[] = $val."-view-\\3-\\5-\\7.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=view&(amp;)?id=([0-9]*)&(amp;)?pag=([0-9]*)&(amp;)?num=([0-9]*)'";
	$out[] = $val."-view-\\3-\\5.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=view&(amp;)?id=([0-9]*)&(amp;)?num=([0-9]*)'";
	$out[] = $val."-view-\\3-word-\\5.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=view&(amp;)?id=([0-9]*)&(amp;)?word=([a-zA-Zа-яА-Я0-9\s%&/|{}().:;&_+\-=]*)'";
	$out[] = $val."-view-\\3-last.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=view&(amp;)?id=([0-9]*)&(amp;)?last'";
	$out[] = $val."-view-\\3.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=view&(amp;)?id=([0-9]*)'";
	$out[] = $val."-broken-\\3.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=broken&(amp;)?id=([0-9]*)'";
	$out[] = $val."-let-\\3-\\5.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=liste&(amp;)?let=([%a-zA-Zа-яА-Я0-9]*)&(amp;)?num=([0-9]*)'";
	$out[] = $val."-let-\\3.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=liste&(amp;)?let=([%a-zA-Zа-яА-Я0-9]*)'";
	$out[] = $val."-list-\\3.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=liste&(amp;)?num=([0-9]*)'";
	$out[] = $val."-list.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=liste'";
	$out[] = $val."-kasse.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=kasse'";
	$out[] = $val."-add-\\3-0-\\5-\\7.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=add&(amp;)?cat=([0-9]*)&(amp;)?pid=([0-9]*)&(amp;)?qid=([0-9]*)'";
	$out[] = $val."-add-\\3-\\5-\\7.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=add&(amp;)?cat=([0-9]*)&(amp;)?id=([0-9]*)&(amp;)?pid=([0-9]*)'";
	$out[] = $val."-add-\\3-0-\\5.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=add&(amp;)?cat=([0-9]*)&(amp;)?pid=([0-9]*)'";
	$out[] = $val."-add-\\3.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=add&(amp;)?cat=([0-9]*)'";
	$out[] = $val."-add.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=add'";
	$out[] = $val."-delete-\\3-\\5.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?op=delete&(amp;)?cat=([0-9]*)&(amp;)?id=([0-9]*)'";
	$out[] = $val."-sort-\\2-\\4.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?sort=([0-9]*)&(amp;)?num=([0-9]*)'";
	$out[] = $val."-sort-\\2.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?sort=([0-9]*)'";
	$out[] = $val."-cat-\\2-word-\\4.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?cat=([0-9]*)&(amp;)?word=([a-zA-Zа-яА-Я0-9\s%&/|{}().:;&_+\-=]*)'";
	$out[] = $val."-cat-\\2-sort-\\4-\\6.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?cat=([0-9]*)&(amp;)?sort=([0-9]*)&(amp;)?num=([0-9]*)'";
	$out[] = $val."-cat-\\2-sort-\\4.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?cat=([0-9]*)&(amp;)?sort=([0-9]*)'";
	$out[] = $val."-cat-\\2-\\4.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?cat=([0-9]*)&(amp;)?num=([0-9]*)'";
	$out[] = $val."-cat-\\2.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?cat=([0-9]*)'";

	###
	#$out[] = $val."-\\2-atime-\\4-dtime-\\6-word-\\8-\\10.html";
	#$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?mod=([a-zA-Z0-9_]*)&(amp;)?atime=([a-zA-Zа-яА-Я0-9\s%&/|{}().:;&_+\-=]*)&(amp;)?dtime=([a-zA-Zа-яА-Я0-9\s%&/|{}().:;&_+\-=]*)&(amp;)?word=([a-zA-Zа-яА-Я0-9\s%&/|{}().:;&_+\-=]*)&(amp;)?num=([0-9]*)'";
	###

	$out[] = $val."-\\2-word-\\4-\\6-\\8.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?mod=([a-zA-Z0-9_]*)&(amp;)?word=([a-zA-Zа-яА-Я0-9\s%&/|{}().:;&_+\-=]*)&(amp;)?typ=([0-9]*)&(amp;)?num=([0-9]*)'";
	$out[] = $val."-\\2-word-\\4-\\6.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?mod=([a-zA-Z0-9_]*)&(amp;)?word=([a-zA-Zа-яА-Я0-9\s%&/|{}().:;&_+\-=]*)&(amp;)?num=([0-9]*)'";
	$out[] = $val."-\\2.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?mod=([a-zA-Z0-9_]*)'";
	$out[] = $val."-\\2.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?num=([0-9]*)'";
	$out[] = $val."-word-\\2.html";
	$in[] = "'(?<!/)index.php\?name=".$val."&(amp;)?word=([a-zA-Zа-яА-Я0-9\s%&/|{}().:;&_+\-=]*)'";
	$out[] = $val.".html";
	$in[] = "'(?<!/)index.php\?name=".$val."'";
}
?>