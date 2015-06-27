/* French initialisation for the jQuery UI date picker plugin. */
/* Written by Keith Wood (kbwood@virginbroadband.com.au) and Stéphane Nahmani (sholby@sholby.net). */
jQuery(function($){
    $.datepicker.regional['fr'] = {clearText: 'Effacer', clearStatus: '',
        closeText: 'Fermer', closeStatus: 'Fermer sans modifier',
        prevText: '<Pr&eacute;c', prevStatus: 'Voir le mois pr&eacute;c&eacute;dent',
        nextText: 'Suiv>', nextStatus: 'Voir le mois suivant',
        currentText: 'Courant', currentStatus: 'Voir le mois courant',
        monthNames: ['Janvier','F&eacute;vrier','Mars','Avril','Mai','Juin',
        'Juillet','Ao&ucirc;t','Septembre','Octobre','Novembre','D&eacute;cembre'],
        monthNamesShort: ['Jan','F&eacute;v','Mar','Avr','Mai','Jun',
        'Jul','Ao&ucirc;','Sep','Oct','Nov','D&eacute;c'],
        monthStatus: 'Voir un autre mois', yearStatus: 'Voir un autre ann&eacute;e',
        weekHeader: 'Sm', weekStatus: '',
        dayNames: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
        dayNamesShort: ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'],
        dayNamesMin: ['Di','Lu','Ma','Me','Je','Ve','Sa'],
        dayStatus: 'Utiliser DD comme premier jour de la semaine', dateStatus: 'Choisir le DD, MM d',
        dateFormat: 'yy-mm-dd', firstDay: 0,
        initStatus: 'Choisir la date', isRTL: false, changeMonth: true, changeYear: true};
    $.datepicker.setDefaults($.datepicker.regional['fr']);
});


(function($) {
    if ($.timepicker != undefined)
    {
        $.timepicker.regional['fr'] = {
        timeOnlyTitle: 'Choisir une heure',
        timeText: 'Heure',
        hourText: 'Heures',
        minuteText: 'Minutes',
        secondText: 'Secondes',
        millisecText: 'Millisecondes',
        timezoneText: 'Fuseau horaire',
        currentText: 'Maintenant',
        closeText: 'Terminé',
        timeFormat: 'hh:mm',
        amNames: ['AM', 'A'],
        pmNames: ['PM', 'P'],
        ampm: false
        };
        $.timepicker.setDefaults($.timepicker.regional['fr']);
    }
})(jQuery);