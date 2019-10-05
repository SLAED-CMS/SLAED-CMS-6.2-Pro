<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined('ADMIN_FILE')) die('Illegal file access');

adminmenu($admin_file.'.php?op=admins_show', _EDITADMINS, 'admins.png');
adminmenu($admin_file.'.php?op=anewsletter', _NEWSLETTER, 'newsletter.png');
adminmenu($admin_file.'.php?op=blocks_show', _BLOCKS, 'blocks.png');
adminmenu($admin_file.'.php?op=cat_show', _CATEGORIES, 'categories.png');
adminmenu($admin_file.'.php?op=comm_show', _COMMENTS, 'comments.png');
adminmenu($admin_file.'.php?op=configure', _PREFERENCES, 'preferences.png');
adminmenu($admin_file.'.php?op=database', _DATABASE, 'database.png');
adminmenu($admin_file.'.php?op=editor_function', _EDITOR_IN, 'editor.png');
adminmenu($admin_file.'.php?op=favorites', _FAVORITES, 'favorites.png');
adminmenu($admin_file.'.php?op=fields', _FIELDS, 'fields.png');
adminmenu($admin_file.'.php?op=groups', _UGROUPS, 'groups.png');
adminmenu($admin_file.'.php?op=lang_main', _LANG_EDIT, 'lang.png');
adminmenu($admin_file.'.php?op=module', _MODULES, 'modules.png');
adminmenu($admin_file.'.php?op=msg', _MESSAGES, 'messages.png');
adminmenu($admin_file.'.php?op=privat', _PRIVAT, 'privat.png');
adminmenu($admin_file.'.php?op=ratings', _RATINGS, 'ratings.png');
adminmenu($admin_file.'.php?op=referers', _REFERERS, 'referers.png');
adminmenu($admin_file.'.php?op=replace', _REPLACE, 'replace.png');
adminmenu($admin_file.'.php?op=rss_conf', _RSS, 'rss.png');
adminmenu($admin_file.'.php?op=security_show', _SECURITY, 'security.png');
adminmenu($admin_file.'.php?op=sitemap', _SITEMAP, 'sitemap.png');
adminmenu($admin_file.'.php?op=stat_show', _STAT, 'stat.png');
adminmenu($admin_file.'.php?op=template', _THEME, 'template.png');
adminmenu($admin_file.'.php?op=uploads', _UPLOADSEDIT, 'uploads.png');
adminmenu($admin_file.'.php?op=users_show', _USERS, 'users.png');
?>