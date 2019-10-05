$.datepicker.regional['de'] = {
	closeText: 'Schließen',
	prevText: 'Zurück',
	nextText: 'Vorwärts',
	currentText: 'Heute',
	monthNames: ['Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'],
	monthNamesShort: ['Jan','Feb','Mär','Apr','Mai','Jun','Jul','Aug','Sep','Okt','Nov','Dez'],
	dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
	dayNamesShort: ['So','Mo','Di','Mi','Do','Fr','Sa'],
	dayNamesMin: ['So','Mo','Di','Mi','Do','Fr','Sa'],
	weekHeader: 'KW',
	dateFormat: 'dd.mm.yy',
	firstDay: 1,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: ''
};
$.datepicker.setDefaults($.datepicker.regional['de']);

$.timepicker.regional['de'] = {
	timeOnlyTitle: 'Zeit wählen',
	timeText: 'Zeit',
	hourText: 'Stunden',
	minuteText: 'Minuten',
	secondText: 'Sekunden',
	millisecText: 'Millisekunden',
	microsecText: 'Mikrosekunde',
	timezoneText: 'Zeitzone',
	currentText: 'Jetzt',
	closeText: 'Schließen',
	timeFormat: 'HH:mm',
	timeSuffix: '',
	amNames: ['AM', 'A'],
	pmNames: ['PM', 'P'],
	isRTL: false
};
$.timepicker.setDefaults($.timepicker.regional['de']);