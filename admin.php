<?php
# Author: Eduard Laas
# Copyright © 2005 - 2017 SLAED
# License: GNU GPL 3
# Website: slaed.net

define('ADMIN_FILE', true);
$sgtime = array_sum(explode(' ', microtime()));
include('admin/admin.php');
?>