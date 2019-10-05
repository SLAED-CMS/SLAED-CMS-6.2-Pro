<?php
if (!defined("FUNC_FILE")) die("Illegal file access");

$confrs = array();
$confrs['min'] = "10";
$confrs['max'] = "50";
$confrs['temp'] = <<<HTML
<table class="sl_table_form">
<tr><td><span title="[date]" class="sl_date">[date]</span> - <a href="[guid]" target="_blank" title="[title]">[title]</a></td></tr>
<tr><td style="text-align: justify;">[description]</td></tr>
</table>
HTML;
$confrs['act'] = "1";
$confrs['use'] = "1";
$confrs['rss'] = "My site name|https://slaed.net/index.php?go=rss|1||SLAED CMS - News|https://slaed.net/index.php?go=rss&amp;name=news|0||SLAED CMS - Links|https://slaed.net/index.php?go=rss&amp;name=links|0||SLAED CMS - Files|https://slaed.net/index.php?go=rss&amp;name=files|0||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1||0|0|1";

?>