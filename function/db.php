<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

if (!defined("FUNC_FILE")) die("Illegal file access");

include("config/config.php");

switch($confdb['type']) {
	case "mysql":
	include("function/db/mysql.php");
	break;
	
	case "mysqli":
	include("function/db/mysqli.php");
	break;
	
	case "sqlite":
	include("function/db/sqlite.php");
	break;
	
	case "postgres":
	include("function/db/postgres7.php");
	break;
	
	case "mssql":
	include("function/db/mssql.php");
	break;
	
	case "oracle":
	include("function/db/oracle.php");
	break;
	
	case "msaccess":
	include("function/db/msaccess.php");
	break;
	
	case "mssql-odbc":
	include("function/db/mssql-odbc.php");
	break;
}

$db = new sql_db($confdb['host'], $confdb['uname'], $confdb['pass'], $confdb['name'], $confdb['code']);
if ($conf['dbsync']) $db->sql_query("SET LOCAL time_zone = '".date('P')."'");
if (!$db->db_connect_id) get_exit(_SQLERROR, 0);
unset($confdb);
?>