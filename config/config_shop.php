<?php
if (!defined("FUNC_FILE")) die("Illegal File Access");

$confso = array();
$confso['defis'] = "%C2%BB";
$confso['clients'] = "0";
$confso['clients1'] = "3";
$confso['clients2'] = "5";
$confso['proz'] = "3";
$confso['proz1'] = "5";
$confso['proz2'] = "10";
$confso['valute'] = "€";
$confso['mail'] = "info@slaed.net";
$confso['shop_t'] = "86400";
$confso['part_t'] = "86400";
$confso['bascol'] = "1";
$confso['tabcol'] = "3";
$confso['assocnum'] = "3";
$confso['listnum'] = "5";
$confso['num'] = "5";
$confso['anum'] = "50";
$confso['nump'] = "5";
$confso['anump'] = "10";
$confso['homcat'] = "1";
$confso['viewcat'] = "1";
$confso['catdesc'] = "1";
$confso['subcat'] = "1";
$confso['mailuser'] = "1";
$confso['date'] = "1";
$confso['read'] = "1";
$confso['rate'] = "1";
$confso['letter'] = "1";
$confso['assoc'] = "1";
$confso['mailsend'] = "1";
$confso['part'] = "1";
$confso['partlink'] = "http://proslaed-utf.loc/index.php?name=shop&amp;op=part&amp;id=[id]";
$confso['sende'] = <<<HTML
[center][color=red][b]Cпасибо за заказ![/b][/color][/center]<br>
<br>
Ваша заявка отправлена и поставлена в очередь на обработку. Произведите оплату по одному из указанных ниже способов. В комментариях к оплате укажите свои имя и фамилию, указанные Вами при оформлении заказа.<br>
<br>
Вы должны быть зарегистрированы на проекте. Если Вы ещё не зарегистрированы, сделайте это и сообщите нам Ваш логин и персональные данные, на которые производился заказ. При соблюдении этих условий не позже, чем через 48 часов после поступления суммы на наш счет, Вам будет выдан доступ на загрузку архива, получения лицензии и технической поддержки. На указанный Вами E-Mail адрес будет выслано уведомление о предоставлении доступа клиента системы.<br>
<br>
[center][color=red][b]Способы оплаты услуг[/b][/color][/center]<br>
<br>
[b]Через платежную систему WebMoney[/b]<br>
<br>
[url=http://www.webmoney.ru]WebMoney[/url] - Z590919569461<br>
[color=red]Оплата в системе WebMoney принимается по курсу: 1.00 € = 1.35 &#036;[/color]
HTML;
$confso['userinfo'] = <<<HTML
Информация для клиентов
HTML;
$confso['partinfo'] = <<<HTML
Информация для будущих партнеров
HTML;
$confso['partinfo2'] = <<<HTML
Информация для партнеров<br>
<br>
[url=http://localhost/index.php?name=shop&amp;op=part&amp;id=[id]]http://localhost/index.php?name=shop&amp;op=part&amp;id=[id][/url]
HTML;
$confso['shopinfo'] = <<<HTML
Генеральный директор: Test Testow<br>
Адрес: D-69079 Test, Im Test 56<br>
Телефон: +49135204064
HTML;

?>