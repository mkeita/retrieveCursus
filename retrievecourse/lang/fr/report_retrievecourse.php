<?php
$string ['pluginname'] = 'Retrieve Course';
$string ['retrievecourse_config'] = 'Configuration du plugin ';
$string ['retrievecourse_description'] = '';
$string ['tempYearOne'] = utf8_encode ( 'Nombre de chiffre dans la premi�re ann�e.' );
$string ['tempYearTwo'] = utf8_encode ( 'Nombre de chiffre dans la deuxi�me ann�e.' );
$string ['nbTentativeMax'] = utf8_encode ( 'Nombre de tentative de backup/restore' );
$string ['adminUser'] = 'Id de l\' administrateur';
$string ['post_max_size'] = utf8_encode ( " taille maximale des donn�es re�ues par m�thode POST" );

// FormTeacher
$string ['warning'] = 'Attention';
$string ['messageTeacher_part1'] = '<p> <font color="#FF0000"> ' . utf8_encode ( "Attention, si vous choisissez de copier le contenu du cours " );
$string ['messageTeacher_part2'] =  utf8_encode ( " vers le cours " );
$string ['messageTeacher_part3'] =  utf8_encode ( " .Tout le contenu du cours  " ) ;
$string ['messageTeacher_part4'] = utf8_encode ( " sera �cras�.  " ) . ' </font></p>';

$string ['messageTeacherChoice'] = utf8_encode ( '<p> D�sirez-vous r�cup�rer vos donn�es ou commencer 
		un nouveau cour ? </p>' );
$string ['checkbox_recuperer'] = utf8_encode ( 'R�cup�rer information 		' );
$string ['checkbox_newcourse'] = utf8_encode ( 'D�buter un nouveau cour 	' );

// FormViewAdmin
$string ['header_admin'] = utf8_encode ( "Liste des cours qui n'ont pas utilis�s le plugin" );
$string ['listeCour'] = 'Liste des cours: ';
$string ['use_cron'] = utf8_encode ( 'Utiliser cron: ' );
$string ['checkbox_usecron'] = utf8_encode ( 'Utiliser cron: ' );
$string ['checkbox_backupImmediately'] = utf8_encode ( 'D�marrer le backup/restore imm�diatement' );
$string ['header_statistique'] = 'Statistique';
$string ['debut_graphique_div'] = "<div style='width='100%'' id='graphique'>  ";
$string ['fin_graphique_div'] = "</div>";
$string ['graphique_admin'] = "<div style='display: inline-block; text-align:left; width: 32%; ' id='graphique_admin'></div>";
$string ['graphique_prof'] = "<div style='display: inline-block; text-align:left; width: 32%;' id='graphique_prof'></div>";
$string ['graphique_usingPlugin'] = "<div style='display: inline-block; text-align:left; width: 32%;' id='graphique_usingPlugin'></div>";
$string ['used_plugin'] = "cours qui ont </br>utilis� le plugin";
$string ['not_used_plugin'] = "cours qui n' ont pas </br>utilis� le plugin";

$string ['trie'] = 'Trie ';
$string ['recherche'] = 'Recherche: ';
$string ['categorie'] = 'Categorie: ';
$string ['recherche_button'] = 'recherche';
$string ['element_recherche'] = utf8_encode ( '<b> Shortname ou partie du shortname du cours d�sir� </b>' );

// FormConfirmation
$string ['header_confirm'] = 'Confirmation';
$string ['confirm_yes'] = 'oui';
$string ['confirm_no'] = 'non';

// ControllerFormAdmin
$string ['msg_cron'] = utf8_encode ( '�tes-vous s�r de vouloir faire un backup/restore via cron?' );
$string ['msg_backup'] = utf8_encode ( '�tes-vous s�r de vouloir faire un backup/restore imm�diatement?' );
$string ['msg_error_backup_deja_effectue'] = utf8_encode ( "Ce cours a d�j� fait le backup et le restore" );
$string ['msg_backup_termine'] = utf8_encode ( "Backup/Restore termin� avec succ�s" );
$string ['msg_cron_ulterieurement'] = utf8_encode ( "Les cours seront trait�s ult�rieurement via cron. </br>" );

// ControllerFormTeacher
$string ['msg_newcourse'] = utf8_encode ( '�tes-vous s�r de ne pas vouloir r�cup�rer les informations de votre cours  ?' );
$string ['msg_backup'] = utf8_encode ( '�tes-vous s�r de vouloir r�cup�rer les informations de votre cours </br>
											et de supprimer le contenue du cours de l\'ann�e acad�mique suivante?' );
$string ['msg_retrieve'] = utf8_encode ( 'La r�cup�ration des informations de votre cours se fera ult�rieurement. </br>' ) . utf8_encode ( 'Vous recevrez un email une fois vos informations r�cup�r�es. </br></br></br>' );

// ControllerFormPrincipal
$string ['msg_error_plugin_deja_utlise'] = utf8_encode ( "Le plugin a d�j� �t� utilis�." );
$string ['msg_error_cours_non_cree'] = utf8_encode ( "Le cour de l'ann�e prochaine n'a pas encore �t� cr�e" );
$string ['msg_error_techar'] = utf8_encode ( "Vous n' �tes pas l'un des professeur du cours de l'ann�e prochaine" );

// RetrieveCourseService
$string ['msg_backup_continue_background'] = utf8_encode ( "<b> M�me si le navigateur s'arr�te de fonctionner, </br>
    				le backup/restore continue de s'ex�cuter en background. </br>
    				Vous serez pr�venu de la fin du backup/restore par email.</b>" );
