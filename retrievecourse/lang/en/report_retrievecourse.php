<?php

$string['pluginname'] = 'Retrieve Course';
$string['retrievecourse_config'] = 'Plugin Settings';
$string['retrievecourse_description'] = '';
$string['tempYearOne'] = utf8_encode('Number of digits in the first year.');
$string['tempYearTwo'] = utf8_encode('Number of digits in the second year.');
$string['nbTentativeMax'] = utf8_encode('Number of attempted backup / restore');
$string['post_max_size'] = utf8_encode(" taille maximale des données reçues par méthode POST");
$string['memory_limit'] = utf8_encode("This sets the maximum amount of memory in bytes that a script is allowed to allocate.");
$string['upload_max_filesize'] = utf8_encode("La taille maximale en octets d'un fichier à charger.");
$string['max_execution_time'] = utf8_encode("Fixe le temps maximal d'exécution d'un script, en secondes.");
$string['max_input_time'] = utf8_encode("Cette option spécifie la durée maximale pour analyser les données d'entrée, via POST et GET.");

//FormTeacher
$string['warning'] = 'Warning';
$string['messageTeacher'] = '<p> <font color="#FF0000"> '.
               utf8_encode("Cour vider") .' </font></p>';
$string['messageTeacherChoice'] = utf8_encode('<p> Do you want to recover your data or start
a new court? </p>');
$string['checkbox_recuperer'] = utf8_encode('retrieve information		');
$string['checkbox_newcourse'] = utf8_encode('Start a new court	');

//FormViewAdmin
$string['header_admin'] =utf8_encode("List of courses that have not used the plugin");
$string['listeCour'] = 'List of courses: ';
$string['use_cron'] = utf8_encode('Use cron:');
$string['checkbox_usecron'] = utf8_encode('Use cron: ');
$string['checkbox_backupImmediately'] = utf8_encode('Start the backup / restore immediately');
$string['header_statistique'] = 'statistical';

$string['debut_graphique_div'] = "<div style='width='100%'' id='graphique'>  ";
$string['fin_graphique_div'] = "</div>";
$string['graphique_admin'] = "<div style='display: inline-block; text-align:left; width: 32%; ' id='graphique_admin'></div>";
$string['graphique_prof'] = "<div style='display: inline-block; text-align:left; width: 32%;' id='graphique_prof'></div>";
$string['graphique_usingPlugin'] = "<div style='display: inline-block; text-align:left; width: 32%;' id='graphique_usingPlugin'></div>";
$string['used_plugin'] = "Courses that used </br>the plugin";
$string['not_used_plugin'] = "Courses that doesn\'t </br>use the plugin";


//FormTrie
$string['trie'] = 'Sort ';
$string['recherche'] = 'Search: ';
$string['categorie'] = 'Category: ';
$string['recherche_button'] = 'search';
$string['element_recherche'] = utf8_encode('<b> Shortname or part of the desired course shortname</b>');

//FormConfirmation
$string['header_confirm'] = 'Confirmation';
$string['confirm_yes'] = 'yes';
$string['confirm_no'] = 'no';

//Email

$string['messageprovider:confirmation'] = 'Confirmation of your own quiz submissions';
$string['messageprovider:submission'] = 'Notification of quiz submissions';
