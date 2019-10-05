INSERT INTO `{pref}_blocks` VALUES
(1, '', 'Навигация', '', '', 'r', 2, 1, 0, '', '', 'block-modules.php', 0, '0', 'd', 'all'),
(2, 'admin', 'Администрация', '<a href=\"javascript:OpenWindow(\'plugins/sxd/index.php\', \'DB Backup - Sypex Dumper\', \'600\', \'500\')\" title=\"DB Backup - Sypex Dumper\">DB Backup - Sypex Dumper</a>', '', 'r', 3, 1, 0, '0', '', '', 2, '0', 'd', 'all'),
(3, '', 'Выбор языка', '', '', 'r', 1, 1, 0, '', '', 'block-languages.php', 0, '0', 'd', 'all'),
(4, 'userbox', 'Блок пользователя', '', '', 'r', 4, 1, 0, '', '', '', 1, '0', 'd', 'all'),
(5, '', 'Информация пользователя', '', '', 'r', 5, 1, 0, '', '', 'block-user_info.php', 0, '0', 'd', 'all'),
(6, '', 'Счетчик посещений', '', '', 'r', 6, 1, 0, '', '', 'block-stat.php', 0, '0', 'd', 'all'),
(7, '', 'Реклама', '', '', 'd', 1, 1, 0, '', '', 'block-banner_random.php', 0, '0', 'd', 'all'),
(8, '', 'Форум внизу', '', '', 'r', 7, 1, 0, '', '', 'block-forum.php', 0, '0', 'd', 'infly,');

INSERT INTO `{pref}_categories` VALUES
(1, 'news', 'Internet', 'Internet news', 'network.png', '', 0, 1, 1, 0, 0, 0, '0|0', '0|0', '1|0', '1|0', '3|0', '3|0', '3|0'),
(2, 'news', 'Soft', 'Software', 'cup.png', '', 0, 1, 2, 0, 0, 0, '0|0', '0|0', '1|0', '1|0', '3|0', '3|0', '3|0'),
(3, 'forum', 'Категория форума', 'Описание категории', '', '', 0, 1, 1, 3, 0, 0, '0|0', '0|0', '1|0', '1|0', '1|0', '3|0', '3|0'),
(4, 'forum', 'Демонстрация форума', 'Описание демонстрации', '', '', 3, 1, 2, 3, 0, 3, '0|0', '0|0', '1|0', '1|0', '1|0', '3|0', '3|0'),
(5, 'forum', 'Устаревшие сообщения', 'Форум, используемый в качестве корзины', '', '', 3, 1, 3, 0, 0, 0, '0|0', '0|0', '1|0', '1|0', '1|0', '3|0', '3|0');

INSERT INTO `{pref}_forum` VALUES
(1, 0, 4, 0, 'SLAED', 'Защита и безопасность', '2017-04-27 19:58:00', 'Установка дополнительного пароля и логина значительным образом повышает уровень безопасности системы и практически исключает несанкционированный доступ к панели управления. Обратите внимание, «HTTP-аутентификация» возможна только при запуске РНР как Apache-модуля или в режиме FastCGI с установленным модулем Mod Rewrite. Заметьте, эта функция не работает на Microsoft IIS-сервере и с CGI-версией PHP. Во избежание проблем с доступом, рекомендуем проконсультироваться у Вашего хостинг провайдера.', '', 0, 1, 0, 0, '127.0.0.1', 0, 'SLAED', 0, '2017-04-27 19:58:00', 0, '', NULL, 3),
(2, 0, 4, 0, 'SLAED', 'Языковые версии статей', '2017-04-27 20:01:00', 'Чтобы создать статьи для какой-то языковой версии сайта (к примеру, для английской) необходимо создать категорию в разделе «Категории» и указать, в какой языковой версии она должна отображаться (категорию создавать соответственно на английском языке). Далее при создании англоязычной статьи закрепите её за предварительно созданной языковой категорией. Пользователь при переходе к английской версии будет видеть соответственно статьи, закрепленные за английской категорией.', '', 0, 2, 0, 0, '127.0.0.1', 0, 'SLAED', 0, '2017-04-27 20:01:00', 0, '', NULL, 3),
(3, 0, 4, 0, 'SLAED', 'Добавление под-категории', '2017-04-27 20:04:00', 'Для добавления новой под-категории выбранного модуля перейдите во вкладку «Добавить под-категорию» и заполните информацию о новой под-категории.<br>\r\nФорма и процесс добавления под-категории аналогичны форме и процессу добавления категории за исключением выбора категории, для которой добавляется под-категория. Выбор категории происходит во вкладке «Категория».<br>\r\nПод-категория может быть добавлена как для категории, так и для другой под-категории, что позволяет создавать иерархию с практически неограниченным уровнем вложенности.', '', 0, 5, 0, 0, '127.0.0.1', 0, 'SLAED', 0, '2017-04-27 20:04:00', 0, '', NULL, 3);

INSERT INTO `{pref}_modules` VALUES
(1, 'account', 1, 0, 1, 0, 0, 0),
(2, 'auto_links', 1, 0, 1, 0, 0, 0),
(3, 'contact', 1, 0, 1, 0, 0, 0),
(4, 'content', 1, 0, 1, 0, 0, 0),
(5, 'faq', 1, 0, 1, 0, 0, 0),
(6, 'files', 1, 0, 1, 0, 0, 0),
(7, 'forum', 1, 0, 1, 0, 0, 0),
(8, 'help', 1, 0, 1, 0, 0, 0),
(9, 'jokes', 1, 0, 1, 0, 0, 0),
(10, 'links', 1, 0, 1, 0, 0, 0),
(11, 'media', 1, 0, 1, 0, 0, 0),
(12, 'news', 1, 0, 1, 0, 0, 0),
(13, 'order', 1, 0, 1, 0, 0, 0),
(14, 'pages', 1, 0, 1, 0, 0, 0),
(15, 'recommend', 1, 0, 1, 0, 0, 0),
(16, 'rss_info', 1, 0, 1, 0, 0, 0),
(17, 'search', 1, 0, 1, 0, 0, 0),
(18, 'shop', 1, 0, 1, 0, 0, 0),
(19, 'top_users', 1, 0, 1, 0, 0, 0),
(20, 'voting', 1, 0, 1, 0, 0, 0);

INSERT INTO `{pref}_news` VALUES
(1, 2, 0, 'SLAED', 'SLAED CMS 6.1 Pro – первая Open Source версия SLAED CMS', '2017-04-27 21:00:00', '[img=left alt=Современные тенденции в дизайне и технологиях]uploads/news/slaed_logo_design.png[/img] [justify][b][i]О завершении очередного этапа развития проекта: SLAED CMS адаптирована к современным тенденциям в дизайне и технологиях[/i][/b]<br><br>Компания SLAED рада представить версию 6.1 нашего флагманского продукта SLAED CMS Pro. Главное отличие новой версии от всех предыдущих заключается не в функциях, а в лицензии – распространяться версия 6.1 будет абсолютно бесплатно на базе лицензии GNU GPLv3. Более подробно о переходе на Open Source мы рассказали совсем недавно и теперь перешли от слов к действиям.<br><br>Постоянная аудитория и партнёры проекта уже заметили, что сайт проекта обновился, обновился кардинально. Новый сайт – это публичное отражение возможностей новой версии SLAED CMS 6.1.<br><br>Да! Проекту SLAED CMS вот-вот исполнится 12 лет - 30.04.2017 г. мы будем праздновать юбилей. Это серьёзный срок для CMS-системы, как и для любого ИТ-проекта. За 12 лет наш проект кардинально менялся, улучшался и постоянно внедрялся: именно в этот момент тысячи сайтов работают на базе SLAED CMS.[/justify]', '[justify]Отпуская в свободное плавание SLAED CMS, хочется вспомнить несколько интересных фактов о системе, накопившихся за 12 лет её существования.<br><br>[b]«Живучесть» при нагрузках[/b]<br><br>Никого не удивить термином «хабраэффект» (Слэшдот-эффект) – его ждут и боятся одновременно. Многие CMS к нему не готовы на уровне «коробка+стандартный хостинг», и владельцу сайта придётся хорошо попотеть, чтобы пережить хабраэффект у сайта, CMS которого может «положить» хостинг во время пиковых нагрузок. Многие наблюдения за сайтами, разработанными на базе SLAED CMS, говорят о том, что у CMS одни из самых низких требований к серверным ресурсам (хостингу).<br><br>Вот лишь один пример таких наблюдений:<br><br>На одном из сайтов для взрослых, работающем на базе SLAED CMS, была зафиксирована посещаемость порядка 38 000 уникальных посетителей единовременно, причём такое количество посетителей было не мгновенным явлением, а постоянным в течение суток. Сайт при такой посещаемости работал без фиксации каких-либо задержек, не смотря на то, что размещался на стандартной хостинг площадке и не имел кастомизаций на уровне CMS (базовая поставка).<br><br>За всю историю проекта не поступало жалоб о том, что SLAED CMS создаёт высокую нагрузку на сервер.<br><br>[b]7 лет без единой уязвимости[/b]<br><br>Последний раз уязвимость, причём пассивная, в системе была выявлена 27.02.2010 года (7 лет назад) в версии SLAED CMS 4.0 Pro. А начиная с версии SLAED CMS 4.1 Pro в системе уязвимостей больше не находили, но старались.<br><br>Некоторые хакеры охотно используют систему в виду стабильной работы и низкой нагрузки на сервер, а  в 2009 году появилась панель управления СПАМ/DDos ботами на базе ядра SLAED. Панель весьма популярна в определённых круга и используется до сих пор.<br><br>[b]Гибкая мультиязычность[/b]<br><br>Кто всерьёз занимается веб-разработкой, тот знает, что сделать по-настоящему мультиязычный сайт – это не самое простое занятие. Языковые версии могут иметь разную структуру (причём она может быть разной для всех версий), постоянно «вылезают» непереведённые слова в интерфейсе, могут быть абсолютно разные дизайн-блоки, да и множество других особенностей. Практически, все нюансы создания языковых версий учтены в SLAED CMS и за время существования системы на ней были созданы сайты на языках: русский, немецкий, английский, еврейский, чешский, арабский, индийский, болгарский  и множество других.<br><br>[b]Дополнения и популярность[/b]<br><br>Пик популярности SLAED CMS пришёлся на 2008 год, через 2 года после первого релиза Pro-версии. Тогда, согласно независимым рейтингам, система входила в пятёрку самых популярных CMS в российском сегменте интернета.<br><br>За период существования системы сторонними разработчиками создано множество бесплатных и платных дополнений: 695 бесплатных  дополнения доступны на нашем сайте и примерно столько же платных и бесплатных разбросано на просторах интернета.<br><br>За 12 лет команда проекта выпустила 37 полноценных версий системы, включая:<br><br>[u]Бесплатные[/u]<br>7 версий SLAED CMS<br>7 версий SLAED CMS Lite<br>4 версии Open SLAED<br><br>[u]Платные[/u]<br>19 версий SLAED CMS Pro<br><br>Было ещё множество промежуточных релизов с мелкими правками и устранением уязвимостей, которые остались вне версионного подсчета.<br><br>[b]Встречаем Open Source[/b]<br><br>Версия SLAED CMS Pro 6.1. – это отправная точка нашего Open Source решения, которую уже сейчас можно скачать для бесплатного использования в рамках лицензии GNU GPLv3. Команда компании SLAED продолжает работу над планомерным развитием CMS и приглашает отраслевых специалистов (дизайнеры, программисты, верстальщики) принять посильное участие в развитии SLAED CMS.[/justify]', '', 1, 0, 14, 1, 1, 5, 1, '2', '127.0.0.1', 0, 1),
(2, 1, 0, 'SLAED', 'SLAED CMS переходит к Open Source модели на базе GNU GPL 3', '2017-04-27 17:00:00', '[img=left alt=SLAED CMS переходит к Open Source модели на базе лицензии GNU GPL 3]uploads/news/news-slaed-open.png[/img] [justify]2017 год компания SLAED решила начать с отказа от проприетарной модели распространения SLAED CMS в пользу Open Source. Новая версия SLAED CMS переходит в общественную собственность и будет абсолютно бесплатно распространяться на базе лицензии GNU GPLv3.<br><br>«Столь кардинальный шаг в нашей лицензионной политике – это шаг вперёд, который позволит существенно расширить границы распространения SLAED CMS, количество новых модулей и изменений, профессионально вносимых в систему. Мне, как автору проекта, важно, чтобы система была максимально доступной и как можно больше современных сайтов делалось на базе SLAED CMS, поэтому я готов сделать этот большой шаг в сторону Open Source!» - прокомментировал изменения в лицензионной политике автор и идеолог проекта Eduard Laas.[/justify]', '[justify]Отметим, что лицензия GNU GPLv3 предоставляет пользователю права копировать, модифицировать и распространять (в том числе на коммерческой основе) SLAED CMS, с гарантированием, что и пользователи всех производных программ получат вышеперечисленные права.<br><br>Грамотный переход к Open Source – это не просто отмена прайса, присвоение лицензии и полное открытие исходных кодов, - это, прежде всего, формирование активного сообщества, которое будет вносить изменения в систему, а также регулировать процесс принятия вносимых правок. В настоящий момент компания SLAED прорабатывает организационные и правовые шаги на пути к полноценному переходу к модели Open Source. В ближайшее время планируется:<br><br>1. Сформировать  Сommunity (сообщество) и определить основные принципы его работы.<br><br>2. Выбрать среду для технического и организационного взаимодействия членов сообщества.<br><br>3. Внести изменения в информационное содержимое сайта slaed.net, который продолжит быть официальным сайтом SLAED CMS.<br><br>Компания SLAED уже сейчас приглашает всех неравнодушных пользователей и разработчиков высказать свои предложения и соображения по поводу формирования сообщества SLAED CMS. Комментарии к новости отслеживаются – будем рады любым конструктивным предложениям и мнениям.<br><br>Отвечая на вопрос о том, что подтолкнуло компанию SLAED к принятию решения о переводе SLAED CMS на Open Source, отметим,  что компании и её основателю важно, чтобы проект был «живым» и развивающимся, а в современных технологических и политических условиях этого можно достигнуть только посредством перехода к Open Source.<br><br>SLAED CMS и раньше имела бесплатную полностью открытую ветку – Open SLAED, которая не была в полной мере Open Source продуктом, но многие ключевые моменты совпадали.  Мы рассчитываем, что Open Source позволит привлечь к развитию проекта относительно широкую команду разработчиков (Community), которая будет включать в себя как специалистов-энтузиастов, так и веб-студии, которые будут готовы делать свои проекты на базе SLAED CMS и делиться наработками с сообществом.<br><br>Лицензия и политика компании не запрещают оказывать коммерческие услуги, используя SLAED CMS, в том числе разрабатывать платно (на заказ) новые модули и визуальные представления для системы или заниматься платным сопровождением сайтов, разработанных на базе SLAED CMS.<br><br>В самое ближайшее время тема перевода SLAED CMS на модель Open Source будет продолжена с учётом предложений и мнений, высказанных в комментариях.[/justify]', '', 0, 0, 9, 1, 1, 5, 1, '1', '127.0.0.1', 0, 1),
(3, 1, 0, 'SLAED', 'В ногу со временем или Web 2 как пройденный этап', '2017-04-27 15:00:00', '[justify]Не для кого не секрет что в последнее время компьютерные технологии начали развиваться с большой скоростью, ещё больше это развитие отобразилось на сферу Интернета и технологиях применяемых в ней. Появились новые, модные на сегодняшний день тенденции, такие как Web 2 и AJAX. Буквально 3-5 лет назад, основная масса сайтов общего направления сети Интернет состояла из HTML страниц, в лучшем случае не сложных скриптов которые их генерируют. Не говоря об использование возможностей и эффектов JavaScript, которые считались спецификой, и применялись весьма редко, можно сказать неохотно в виду слабой поддержки браузеров. На сегодняшний день JavaScript пережил второе рождение и появился снова, но уже под названием AJAX.[/justify]', '[justify]Нечто подобное мы наблюдаем с использованием CMS (Систем построения сайтов), получивших высокую популярность в последние годы по причине универсальности, возможностях внедрения, расширения, адоптации под свои нужды. Хочу, заметит, что в системах подобного рода, уже в то время, использовалась тенденция, ране не существовавшая, а ныне известная как Web 2, парадоксально, но факт. Что же представляет собой Web 2? Это ничто иное, как участие пользователей в жизни проекта, комментарии к статьям, рейтинги и прочие функции, которые с не за памятных времён применялись в портальных системах и были практически не доступны на HTML сайтах, за редким своим исключением. Именно данные возможности и применение Web 2 на портальных системах послужило сильному повышению их популярности.<br>\r\n<br>\r\nС выпуском новой версии мы переходим на более высокий уровень развития, а именно, использования системы, как портальной системы построения сайтов, на уровень выше, чем Web 2. Начиная с версии SLAED CMS 3.3 Pro, мы предоставляем возможность не просто пассивного прибывания пользователей, а активного участие всех посетителей в жизни сайта. Скажу больше, пользователи получают возможность оценки, комментариев, публикаций статей, материалов, участие в опросах, добавления файлов, графических элементов, объектов, оценки друг друга, рейтинга, комментариев и многого другого почти во всех основных отделах проекта в полном объеме. Пользователи и посетители при их желании смогут стать не только наблюдателями, но и принимать активное участие в развитии сайтов. Исходя из этого, система отвечает не только тенденции Web 2, но и её последующим модификациям как Web 3. Это значит что  любой посетитель, естественно при желании и одобрении администратора, может иметь неограниченную возможность участия в развитии и наполнении всего проекта, всех его отделов.<br>\r\n<br>\r\nИдя в ногу со временем наша задача не опережать его, как это было с JavaScrit который опередил его и не получил заслуженную популярность в своё время. Естественно, что мы за использование новых технологий, но только за проверенные, востребованные временем, а главное безопасные. Основные факторы, которые ставились и ставятся при разработке системы это простота в использовании, функциональность, универсальность скорость, а главное безопасность. Наверняка и в новой версии Вы сможете по достоинству не только оценить проделанную работу,  но и активно использовать новые возможности системы, с основными изменениями которой Вы будете ознакомлены в следующей статье.[/justify]', '', 0, 0, 5, 1, 1, 5, 1, '1', '127.0.0.1', 0, 1);

INSERT INTO `{pref}_voting` VALUES
(1, 'news', 'Как вы оцениваете новый дизайн проекта?', 'Отлично|Хорошо|Неплохо|Так себе|Старый был лучше|Мне все равно|А что изменилось?', '77|8|5|2|1|5|8', '2017-04-27 21:00:00', '2020-04-25 21:00:00', 1, 0, '', 0, '127.0.0.1', 1, 0);