<?php
# Copyright © 2005 - 2015 SLAED
# Website: http://www.slaed.net

if (!defined("BLOCK_FILE")) {
	header("Location: ../index.php");
	exit;
}

$content = "<table class=\"sl_table_block\">"
."<tr><td><a href=\"index.php\" title=\"Главная\">Главная</a></td></tr>"
."<tr><td><a href=\"index.php?name=news\" title=\"Новости\">Новости</a></td></tr>"
."<tr><td><hr></td></tr>"
."<tr><td><a href=\"index.php?name=faq\" title=\"Вопросы и ответы\">Вопросы и ответы</a></td></tr>"
."<tr><td><a href=\"index.php?name=pages\" title=\"Учебники\">Учебники</a></td></tr>"
."<tr><td><a href=\"index.php?name=files\" title=\"Файлы\">Файлы</a></td></tr>"
."<tr><td><a href=\"index.php?name=voting\" title=\"Опросы\">Опросы</a></td></tr>"
."<tr><td><a href=\"index.php?name=search\" title=\"Поиск\">Поиск</a></td></tr>"
."<tr><td><hr></td></tr>"
."<tr><td><a href=\"index.php?name=files&amp;op=add\" title=\"Добавить файл\">Добавить файл</a></td></tr>"
."<tr><td><a href=\"index.php?name=news&amp;op=add\" title=\"Добавить статью\">Добавить статью</a></td></tr>"
."<tr><td><hr></td></tr>"
."<tr><td><a href=\"index.php?name=recommend\" title=\"Рекомендовать\">Рекомендовать</a></td></tr>"
."<tr><td><a href=\"index.php?name=contact\" title=\"Обратная связь\">Обратная связь</a></td></tr>"
."</table>";
?>