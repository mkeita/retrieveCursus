<?php
$string ['pluginname'] = 'Retrieve Course';
$string ['retrievecourse_config'] = 'Plugin Settings';
$string ['retrievecourse_description'] = '';
$string ['tempYearOne'] = utf8_encode ( 'Number of digits in the first year.' );
$string ['tempYearTwo'] = utf8_encode ( 'Number of digits in the second year.' );
$string ['nbTentativeMax'] = utf8_encode ( 'Number of attempted backup / restore' );
$string ['post_max_size'] = utf8_encode ( " taille maximale des données reçues par méthode POST" );
$string ['memory_limit'] = utf8_encode ( "This sets the maximum amount of memory in bytes that a script is allowed to allocate." );
$string ['upload_max_filesize'] = utf8_encode ( "La taille maximale en octets d'un fichier à charger." );
$string ['max_execution_time'] = utf8_encode ( "Fixe le temps maximal d'exécution d'un script, en secondes." );
$string ['max_input_time'] = utf8_encode ( "Cette option spécifie la durée maximale pour analyser les données d'entrée, via POST et GET." );

// FormTeacher
$string ['warning'] = 'Warning';
$string ['messageTeacher_part1'] = '<p> <font color="#FF0000"> ' . utf8_encode ( "Be careful if you choose to copy the content of the course" );
$string ['messageTeacher_part2'] =  utf8_encode ( " to the course " );
$string ['messageTeacher_part3'] =  utf8_encode ( " .All course content " ) ;
$string ['messageTeacher_part4'] = utf8_encode ( " will be overwritten  " ) . ' </font></p>';
$string ['messageTeacherChoice'] = utf8_encode ( '<p> Do you want to recover your data or start
a new court? </p>' );
$string ['checkbox_recuperer'] = utf8_encode ( 'retrieve information		' );
$string ['checkbox_newcourse'] = utf8_encode ( 'Start a new court	' );

// FormViewAdmin
$string ['header_admin'] = utf8_encode ( "List of courses that have not used the plugin" );
$string ['listeCour'] = 'List of courses: ';
$string ['use_cron'] = utf8_encode ( 'Use cron:' );
$string ['checkbox_usecron'] = utf8_encode ( 'Use cron: ' );
$string ['checkbox_backupImmediately'] = utf8_encode ( 'Start the backup / restore immediately' );
$string ['header_statistique'] = 'statistical';

$string ['debut_graphique_div'] = "<div style='width='700px'' id='graphique'>  ";
$string ['fin_graphique_div'] = "</div>";
$string ['graphique_admin'] = "<div style='display: inline-block; text-align:left; width: 32%; ' id='graphique_admin'></div>";
$string ['graphique_prof'] = "<div style='display: inline-block; text-align:left; width: 32%;' id='graphique_prof'></div>";
$string ['graphique_usingPlugin'] = "<div style='display: inline-block; text-align:left; width: 32%;' id='graphique_usingPlugin'></div>";
$string ['used_plugin'] = "Courses that used </br>the plugin";
$string ['not_used_plugin'] = "Courses that doesn\'t </br>use the plugin";
$string ['trie'] = 'Sort ';
$string ['recherche'] = 'Search: ';
$string ['categorie'] = 'Category: ';
$string ['recherche_button'] = 'search';
$string ['element_recherche'] = utf8_encode ( '<b> Shortname or part of the desired course shortname</b>' );

$string ['header_confirm'] = 'Confirmation';
$string ['confirm_yes'] = 'yes';
$string ['confirm_no'] = 'no';

// ControllerFormAdmin
$string ['msg_cron'] = utf8_encode ( 'Are you sure you want to backup / restore via cron?' );
$string ['msg_backup'] = utf8_encode ( 'Are you sure you want to backup / restore immediately?' );
$string ['msg_error_backup_deja_effectue'] = utf8_encode ( "This course has already done the backup and restore" );
$string ['msg_backup_termine'] = utf8_encode ( "Backup / Restore completed successfully" );
$string ['msg_cron_ulterieurement'] = utf8_encode ( "The courses will be further processed via cron. </br>" );

// ControllerFormTeacher
$string ['msg_newcourse'] = utf8_encode ( 'Are you sure you do not want to retrieve information from your course?' );
$string ['msg_backup'] = utf8_encode ( 'Are you sure you want to retrieve information from your course </ br> 
and remove the contained during the next academic year?' );
$string ['msg_retrieve'] = utf8_encode ( 'Retrieving information of your course will be later. </br>' ) . utf8_encode ( 'You will receive an email once your information retrieved. </br></br></br>' );

// ControllerFormPrincipal
$string ['msg_error_plugin_deja_utlise'] = utf8_encode ( "The plugin has already been used." );
$string ['msg_error_cours_non_cree'] = utf8_encode ( "The court next year has not yet been created" );
$string ['msg_error_techar'] = utf8_encode ( "You are not logged one of the teacher during the next year" );

// RetrieveCourseService
$string ['msg_backup_continue_background'] = utf8_encode ( "<b> Even if the browser stops working, </ br> 
     the backup / restore continues to run in background. </ br> 
     You will be notified of the end of the backup / restore email.</b>" );
