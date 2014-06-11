<?php
$string ['pluginname'] = 'Retrieve Course';
$string ['retrievecourse_config'] = 'Configuration du plugin ';
$string ['retrievecourse_description'] = '';
$string ['tempYearOne'] = utf8_encode ( 'Nombre de chiffre dans la premiére année.' );
$string ['tempYearTwo'] = utf8_encode ( 'Nombre de chiffre dans la deuxiéme année.' );
$string ['nbTentativeMax'] = utf8_encode ( 'Nombre de tentative de backup/restore' );
$string ['adminUser'] = 'Id de l\' administrateur';
$string ['post_max_size'] = utf8_encode ( " taille maximale des données reçues par méthode POST" );

// FormTeacher
$string ['warning'] = 'Attention';
$string ['messageTeacher_part1'] = '<p> <font color="#FF0000"> ' . utf8_encode ( "Attention, si vous choisissez de copier le contenu du cours " );
$string ['messageTeacher_part2'] =  utf8_encode ( " vers le cours " );
$string ['messageTeacher_part3'] =  utf8_encode ( " .Tout le contenu du cours  " ) ;
$string ['messageTeacher_part4'] = utf8_encode ( " sera écrasé.  " ) . ' </font></p>';

$string ['messageTeacherChoice'] = utf8_encode ( '<p> Désirez-vous récupérer vos données ou commencer 
		un nouveau cour ? </p>' );
$string ['checkbox_recuperer'] = utf8_encode ( 'Récupérer information 		' );
$string ['checkbox_newcourse'] = utf8_encode ( 'Débuter un nouveau cour 	' );

// FormViewAdmin
$string ['header_admin'] = utf8_encode ( "Liste des cours qui n'ont pas utilisés le plugin" );
$string ['listeCour'] = 'Liste des cours: ';
$string ['use_cron'] = utf8_encode ( 'Utiliser cron: ' );
$string ['checkbox_usecron'] = utf8_encode ( 'Utiliser cron: ' );
$string ['checkbox_backupImmediately'] = utf8_encode ( 'Démarrer le backup/restore immédiatement' );
$string ['header_statistique'] = 'Statistique';
$string ['debut_graphique_div'] = "<div style='width='100%'' id='graphique'>  ";
$string ['fin_graphique_div'] = "</div>";
$string ['graphique_admin'] = "<div style='display: inline-block; text-align:left; width: 32%; ' id='graphique_admin'></div>";
$string ['graphique_prof'] = "<div style='display: inline-block; text-align:left; width: 32%;' id='graphique_prof'></div>";
$string ['graphique_usingPlugin'] = "<div style='display: inline-block; text-align:left; width: 32%;' id='graphique_usingPlugin'></div>";
$string ['used_plugin'] = "cours qui ont </br>utilisé le plugin";
$string ['not_used_plugin'] = "cours qui n' ont pas </br>utilisé le plugin";

$string ['trie'] = 'Trie ';
$string ['recherche'] = 'Recherche: ';
$string ['categorie'] = 'Categorie: ';
$string ['recherche_button'] = 'recherche';
$string ['element_recherche'] = utf8_encode ( '<b> Shortname ou partie du shortname du cours désiré </b>' );

// FormConfirmation
$string ['header_confirm'] = 'Confirmation';
$string ['confirm_yes'] = 'oui';
$string ['confirm_no'] = 'non';

// ControllerFormAdmin
$string ['msg_cron'] = utf8_encode ( 'Êtes-vous sûr de vouloir faire un backup/restore via cron?' );
$string ['msg_backup'] = utf8_encode ( 'Êtes-vous sûr de vouloir faire un backup/restore immédiatement?' );
$string ['msg_error_backup_deja_effectue'] = utf8_encode ( "Ce cours a déjà fait le backup et le restore" );
$string ['msg_backup_termine'] = utf8_encode ( "Backup/Restore terminé avec succés" );
$string ['msg_cron_ulterieurement'] = utf8_encode ( "Les cours seront traités ultérieurement via cron. </br>" );

// ControllerFormTeacher
$string ['msg_newcourse'] = utf8_encode ( 'Êtes-vous sûr de ne pas vouloir récupérer les informations de votre cours  ?' );
$string ['msg_backup'] = utf8_encode ( 'Êtes-vous sûr de vouloir récupérer les informations de votre cours </br>
											et de supprimer le contenue du cours de l\'année académique suivante?' );
$string ['msg_retrieve'] = utf8_encode ( 'La récupération des informations de votre cours se fera ultérieurement. </br>' ) . utf8_encode ( 'Vous recevrez un email une fois vos informations récupérées. </br></br></br>' );

// ControllerFormPrincipal
$string ['msg_error_plugin_deja_utlise'] = utf8_encode ( "Le plugin a déjà été utilisé." );
$string ['msg_error_cours_non_cree'] = utf8_encode ( "Le cour de l'année prochaine n'a pas encore été crée" );
$string ['msg_error_techar'] = utf8_encode ( "Vous n' êtes pas l'un des professeur du cours de l'année prochaine" );

// RetrieveCourseService
$string ['msg_backup_continue_background'] = utf8_encode ( "<b> Même si le navigateur s'arrête de fonctionner, </br>
    				le backup/restore continue de s'exécuter en background. </br>
    				Vous serez prévenu de la fin du backup/restore par email.</b>" );
