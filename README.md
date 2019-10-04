System: SLAED CMS 6.2 Pro

Author: Eduard Laas

Copyright © 2005 - 2019 SLAED

License: GNU GPL 3

Website: https://slaed.net

# Минимальные требования

Минимальными требованиями для корректной работы системы являются установленные на Вашем хостинге или сервере программы: PHP 4.3 или выше, MySQL 4 или выше.

# Установка системы

1. Разархивируйте все файлы из папки html/ скачанного архива на сервер, где будет размещаться Ваш сайт
2. Создайте базу данных на Вашем хостинге или сервере в кодировке: utf8_general_ci
3. Установите права CHMOD 666 на файлы config/config.php и config/config_global.php
4. В целях безопасности Вы можете изменить стандартное название у файла admin.php
5. Запустите в адресной строке Вашего браузера: http://www.ваш_сайт.com/setup.php
6. После благополучной установки системы, удалите директорию setup/ и файл setup.php

# Установка прав доступа

1. Установите права CHMOD 666 на все файлы в директории config/ кроме файлов .htaccess и index.html
2. Установите права CHMOD 777 для папки config/backup/, config/cache/, config/counter/, config/logs/, config/sitemap/ и права 666 на их содержание, кроме файлов: .htaccess и index.html
3. Установите права CHMOD 777 для папки uploads/ и все папки содержащиеся в ней и её папках

Если у Вас возникли трудности или проблемы с установкой системы, рекомендуем, обратится в службу поддержки.

--------------------------

Приятной работы!
