<?php
// Language File for Sypex Dumper 2
$LNG = array(

// Information about the language file
'ver'				=> 20005, // Dumper version
'translated'		=> 'Miłosz (milosztrzcinski@wp.pl)', // Contacts
'name'				=> 'Polish', // Lang name

// Toolbar
'tbar_backup'		=> 'Export',
'tbar_restore'		=> 'Import', 
'tbar_files'		=> 'Katalog archiwów BD',
'tbar_services'		=> 'Serwis BD',
'tbar_options'		=> 'Opcje',
'tbar_createdb'		=> 'Utwórz BD',
'tbar_connects'		=> 'Połącz z serwerem MySQL',
'tbar_exit'			=> 'Wyloguj',

// Names of objects in the tree
'obj_tables'		=> 'Tabele',
'obj_views'			=> 'Podgląd',
'obj_procs'			=> 'Procedury',
'obj_funcs'			=> 'Funkcje',
'obj_trigs'			=> 'Triggers',// pol. wyzwalacz
'obj_events'		=> 'Events', //pol. wydarzenie

// Export
'zip_max'			=> 'max',
'zip_min'			=> 'min',
'zip_none'			=> 'BRAK:SQL',
'default'			=> 'domyślnie',
'combo_db'			=> 'Baza danych:', 
'combo_charset'		=> 'Kodowanie:', 
'combo_zip'			=> 'Kompresja:', 
'combo_comments'	=> 'Komentarz:',
'del_legend'		=> 'Automatycznie usuń archiwum, jeśli:',
'del_date'			=> 'utworzone zostało: %s dni temu.',
'del_count'			=> 'istnieje: %s kopii.',
'tree'				=> 'Wybierz obiekt do archiwizacji:',
'no_saved'			=> 'Nie zapisuj pracy',
'btn_save'			=> 'Zapisz',
'btn_exec'			=> 'Wykonaj',

// Import	
'combo_file'		=> 'Baza przywracana z pliku:',
'combo_strategy'	=> 'Strategia Przywracania:',
'ext_legend'		=> 'Opcja Rozszerzone:',
'correct'			=> 'Korekcja kodowania',
'autoinc'			=> 'Resetuj AUTO_INCREMENT',

// Log
'status_current'	=> 'Bierzący status:',
'status_total'		=> 'Końcowy status:',
'time_elapsed'		=> 'Upłynęło:',
'time_left'			=> 'Pozostało:',
'btn_stop'			=> 'Przerwij',
'btn_pause'			=> 'Pauza',
'btn_resume'		=> 'Wznów',
'btn_again'			=> 'Powtórz',
'btn_clear'			=> 'Wyszyść log',

// Files
'btn_delete'		=> 'Usuń',
'btn_download'		=> 'Pobierz',
'btn_open'			=> 'Otwórz',

// Services
'opt_check'			=> 'Opcje sprawdzania bazy:',
'opt_repair'		=> 'Opcje naprawy bazy:',
'btn_delete_db'		=> 'Usuń BD',
'btn_check'			=> 'Sprawdź',
'btn_repair'		=> 'Napraw',
'btn_analyze'		=> 'Analizuj',
'btn_optimize'		=> 'Optymalizuj',

// Options
'cfg_legend'		=> 'Podstawowe ustawienia:',
'cfg_time_web'		=> 'Timelimit web (sekund):',
'cfg_time_cron'		=> 'Timelimit cron (sekund):',
'cfg_backup_path'	=> 'Ścieżka do katalogu kopii:',
'cfg_backup_url'	=> 'URL do katalogu kopii:',
'cfg_globstat'		=> 'Globalne statystyki:',
'cfg_extended'		=> 'Extended settings:',
'cfg_charsets'		=> 'Filtr kodowania:',
'cfg_only_create'	=> 'Twórz tylko z typami plików:',
'cfg_auth'			=> 'Łańcuch autoryzacji:',
'cfg_confirm'		=> 'Potwierdź wykonanie:',
'cfg_conf_import'	=> 'importu',
'cfg_conf_file'		=> 'usunięcia plików kopii',
'cfg_conf_db'		=> 'usunięcia Bazy Danych',

// Connection
'con_header'		=> 'Połącz z serwerem MySQL',
'connect'			=> 'Połącz z serwerem MySQL',
'my_host'			=> 'Host:',
'my_port'			=> 'Port:',
'my_user'			=> 'Użytkownik:',
'my_pass'			=> 'Hasło:',
'my_pass_hidden'	=> 'Hasło nie pokazane',
'my_comp'			=> 'Typ Kompresji',
'my_db'				=> 'Baza Danych:',
'btn_cancel'		=> 'Anuluj',

// Save Job
'sj_header'			=> 'Profil',
'sj_job'			=> 'Specyfikacja profilu',
'sj_name'			=> 'Profil BD:',
'sj_title'			=> 'Opis:',

// Create DB
'cdb_header'		=> 'Utwórz nową Bazę Danych',
'cdb_detail'		=> 'Szczegóły',
'cdb_name'			=> 'Nazwa:',
'combo_collate'		=> 'Collation:',// pol. porównanie
'btn_create'		=> 'Utwórz',

// Authorization
'js_required'		=> 'JavaScript musi być włączony',
'auth'				=> 'Autoryzacja',
'auth_user'			=> 'Użytkownik:',
'auth_remember'		=> 'zapamiętaj',
'btn_enter'			=> 'Zaloguj',
'btn_details'		=> 'Wpisz host',

// Log messages
'not_found_rtl'		=> 'Plik RTL nie istnieje',
'backup_begin'		=> 'Rozpoczęcie Archiwizacji Bazy Danych: `%s`',
'backup_TC'			=> 'Export tabeli: `%s`',
'backup_VI'			=> 'Export podglądu `%s`',
'backup_PR'			=> 'Export procedury `%s`',
'backup_FU'			=> 'Export funkcji `%s`',
'backup_EV'			=> 'Export event `%s`',
'backup_TR'			=> 'Export trigger `%s`',
'continue_from'		=> 'od miejsca %s',
'backup_end'		=> 'Archiwizacja Bazy Danych: `%s` zakończona.',
'autodelete'		=> 'Automatycznie usuń, jeśli:',
'del_by_date'		=> '- `%s` - usuniete (przekroczona ważność kopii)',
'del_by_count'		=> '- `%s` - usuniete (przekroczona ilość kopii)',
'del_fail'			=> '- `%s` - błąd usuwania',
'del_nothing'		=> '- brak plików do usunięcia',
'set_names'			=> 'Ustawione kodowanie połączenia: `%s`',
'restore_begin'		=> 'Rozpoczynam przywracanie Bazy Danych: `%s`',
'restore_TC'		=> 'Przywracanie tabeli `%s`',
'restore_VI'		=> 'Import podglądu `%s`',
'restore_PR'		=> 'Import procedury `%s`',
'restore_FU'		=> 'Import funkcji `%s`',
'restore_EV'		=> 'Import event `%s`',
'restore_TR'		=> 'Import trigger `%s`',
'restore_keys'		=> 'Indeksowanie włączone.',
'restore_end'		=> 'DB: `%s` przywrócona z archiwum.',
'stop_1'			=> 'Wykonywanie zakończone przez użytkownika', 
'stop_2'			=> 'Wykonywanie zatrzymane przez użytkownika',
'stop_3'			=> 'Wykonywanie zatrzymane przez zegar',
'stop_4'			=> 'Wykonywanie zatrzymane przez koniec czasu',
'stop_5'			=> 'Wykonywanie przerwane ze względu na błędy',
'job_done'			=> '-- Proces zakończony pomyślnie --',
'file_size'			=> 'Wielkość pliku ',
'job_time'			=> 'Czas wykonywania ',
'seconds'			=> 'sekund',
'job_freeze'		=> 'Proces ten nie został zaktualizowany przez ponad 30 sekund. Kliknij Wznów',
'stop_job'			=> 'Zatrzymaj',

// For JS
'js' => array(
	
	// Tabs names
	'backup'		=> 'Archiwizuj Bazę Danych',
	'restore'		=> 'Przywróć Bazę Danych',
	'log'			=> 'Log',
	'result'		=> 'Serwisu BD - wynik.',
	'files'			=> 'Katalog archiwów BD',
	'services'		=> 'Serwis BD',
	'options'		=> 'Opcje',

	// Tables header
	'dt'			=> 'Data/czas',
	'action'		=> 'Akcja',
	'db'			=> 'Baza danych',
	'type'			=> 'Typ',
	'tab'			=> 'Tabel',
	'records'		=> 'Rekordów',
	'size'			=> 'Rozmiar',
	'comment'		=> 'Komentarz',

	// AJAX Status
	'load'			=> 'Ładuję ... ',
	'run'			=> 'Wykonuję ... ',
	'sdb'			=> 'Tworzę nowa Bazę Danych',
	'sc'			=> 'Zapisuję połączenie',
	'sj'			=> 'Zapisuję prace',
	'so'			=> 'Zapisuję opcje',

	// Messages
	'Msg_type'		=> 'Rodzaj',
	'Msg_text'		=> 'Stan',
	'pro'			=> 'Opcja dostępna tylko w wersji Pro',
	'err_fopen'		=> 'Nie można otworzyć pliku',
	'err_sxd2'		=> 'Wyświetlanie zawartość plików dostępne tylko dla plików utworzonych przez Sypex Dumper 2',
	'err_empty_db'	=> 'Baza danych jest pusta',
	'fdc'			=> 'Czy napewno usunąć plik archiwum?',
	'ddc'			=> 'Czy napewno usunąć wybraną bazę danych?',
	'fic'			=> 'Czy napewno usunąć przywrócić bazę z wybranego pliku?',

	// Sizes
	'sizes'			=> array('B', 'KB', 'MB', 'GB'),
)
);
?>