> + Version: SLAED CMS 6.2 Pro
> + Author: Eduard Laas
> + Copyright © 2005 - 2019 SLAED
> + License: GNU GPL 3
> + Website: https://slaed.net

#### Minimum requirements

The minimum requirements for the correct operation of the system are installed on your hosting or server programs: PHP 5 or higher, MySQL 5 or higher.

#### System installation

+ Unzip all files from the html/downloaded archive to the server where your site will be hosted
+ Create a database on your hosting or server encoded: utf8_general_ci
+ Set the rights of CHMOD 666 to _config/config.php_ and _config/config_global.php_
+ For security reasons, you can change the default name of the _admin.php_ file
+ Launch it in the address bar of your browser: _yoursite.com/setup.php_
+ After the system has been installed, delete the _setup/_ directory and the _setup.php_ file

#### Setting access rights

+ Set CHMOD 666 permissions on all files in the _config/_ directory except _.htaccess_ and _index.html_
+ Set the CHMOD 777 permissions for the _config/backup/_, _config/cache/_, _config/counter/_, _config/logs/_, _config/sitemap/_ folders and the 666 permissions for their content, except for the _.htaccess_ and _index.html_ files
+ Set CHMOD 777 permissions for the _uploads/_ folder and all folders contained in it and its folders

If you have any difficulties or problems with the system installation, we recommend that you contact our support team, please feel free to ask on the developing SLAED CMS forum in https://slaed.net

----

Have a nice job!