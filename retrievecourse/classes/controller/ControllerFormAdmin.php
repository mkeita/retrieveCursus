<?php
require_once (__DIR__ . '/../view/FormAdmin.php');
require_once (__DIR__ . '/../service/RetrieveCourseService.php');
require_once (__DIR__ . '/../model/RetrieveCourseConstante.php');
require_once (__DIR__ . '/../model/ManageRetrieveCourseDB.php');
require_once (__DIR__ . '/../model/ManageRetrieveCourseCronDB.php');
require_once (__DIR__ . '/../model/ManageDB.php');
require_once (__DIR__ . '/../../outils.php');
require_once (__DIR__ . '/../service/RetrieveCourseService.php');

/**
 *
 * @author Ilias
 *        
 */
class ControllerFormAdmin {
	/**
	 *
	 * @var FormAdmin
	 */
	private $formAdmin;
	/**
	 *
	 * @var ManageDB
	 */
	private $db;
	/**
	 *
	 * @var ManageRetrieveCourseDB
	 */
	private $retrievecoursedb;
	/**
	 *
	 * @var ManageRetrieveCourseCronDB
	 */
	private $crondb;
	/**
	 *
	 * @var RetrieveCourseService
	 */
	private $service;
	function __construct($formAdmin) {
		global $USER;
		$this->formAdmin = $formAdmin;
		$this->db = new ManageDB ();
		$this->crondb = new ManageRetrieveCourseCronDB ();
		$this->retrievecoursedb = new ManageRetrieveCourseDB ();
		$this->service = new RetrieveCourseService ( null, $USER->id, null );
	}
	
	/**
	 * Cette fonction est appellé dés qu'on clique sur l'un des bouton de la vue administrateur(cancel,submit,sort)
	 */
	function admin_submit() {
		global $PAGE;
		if ($this->formAdmin->is_cancelled ()) {
			// Dans le cas où on a cliquez sur 'cancel'
			redirection ( "../.." );
		} elseif ($this->formAdmin->no_submit_button_pressed ()) {
			// Rentrera ici lorsque l'utilisateur appuiera sur le bouton "sort" .
			$this->envoiInfoTrie ();
		} elseif ($this->formAdmin->is_submitted ()) {
			// Rentrera ici lorsque l'utilisateur appuiera sur le bouton "submit" .
			$infoForm = $this->formAdmin->get_submitted_data ();
			$message_cron = get_string ( 'msg_cron', 'report_retrievecourse' );
			$message_backup = get_string ( 'msg_backup', 'report_retrievecourse' );
			if ($infoForm->choice_type_backup)
				$this->confirmation ( $message_backup, RetrieveCourseConstante::CONFIRMATION_BACKUP_IMMEDIAT, $infoForm->cours , $infoForm->category, $infoForm->recherche );
			else
				$this->confirmation ( $message_cron, RetrieveCourseConstante::CONFIRMATION_USE_CRON, $infoForm->cours , $infoForm->category , $infoForm->recherche );
		}
	}
	
	/**
	 * Cette méthode permettra de trier la liste des cours.
	 */
	private function envoiInfoTrie() {
		// Ces informations seront utilisé dans la méthode initialiserListeCour() de la classe FormAdmin()
		global $PAGE;
		$data = $this->formAdmin->get_submitted_data ();
		$url = $PAGE->url . '?';
		if ($data->recherche != "") {
			$url .= 'search=' . $data->recherche;
		} else {
			$idCategory = ($data->category == - 1) ? NULL : $data->category;
			$url .= 'categories=' . $idCategory;
		}
		redirection ( $url );
	}
	
	/**
	 * Permet d'afficher la fenêtre de confirmation.
	 * 
	 * @param string $message        	
	 * @param int $type_confirmation        	
	 * @param array $cours        	
	 */
	private function confirmation($message, $type_confirmation, $cours , $category , $search) {
		// Le choix effectuer par l'utilisateur est récupérer dans la classe ControllerPrincipale.php - méthode adminDisplay()).
		// En fonction du choix effectué , il exécutera soit la méthode backup_immédiat() soit la méthode admin_use_cron() de cette classe.
		global $PAGE, $OUTPUT;
		// Dans le cas où on a coché All.
		if ($cours [0] == - 1) {
			// Enléve toute les valeur pour que le type de tableau est identique qu'on séléctionne all ou plusieur cour.
			// En effet , si on séléctionne pas ALL mais qu'on séléctionne des cours manuellement , '$cours' contiendra juste les id des
			// cours séléctionné.
			
			//Permet de prendre en compte si l'utilisateur avait au préable effectué une recherche.
			//On fera le backup juste des cours qui apparaissait dans la recherche.
			if($search != NULL){
				$cours = array_keys($this->retrievecoursedb->searchCourseNotUsedPlugin ( $search ));
			}elseif($category == '-1'){
				$cours = array_keys ( $this->formAdmin->getListeCour () );
			}else{
				$cours = array_keys (  $this->retrievecoursedb->courseNotUsedPugin ( $category ));
			}
  		}
		echo $OUTPUT->confirm ( $message, '/report/retrievecourse/index.php?confirmation=' . $type_confirmation . '&cour=' . json_encode ( $cours ), '/report/retrievecourse/index.php' );
	}
	
	/**
	 * Permet de lancer le backup/restore pour une liste de cour.
	 * Permet également d'initialiser la barre de progression.
	 * 
	 * @param json $courJson
	 *        	Liste des cours dont il faut faire le backup.
	 */
	public function backup_immediat($courJson) {
		global $USER, $PAGE, $CFG, $OUTPUT;
		
		if (isset ( $courJson )) {
			$cour = json_decode ( $courJson );
			
			$this->supprimerCourUsedPlugin ( $cour );
			
			if (count ( $cour ) == 0) {
				message ( get_string ( 'msg_error_backup_deja_effectue', 'report_retrievecourse' ) );
			} else {
				
				// Afficher la barre de progression
				echo '<div id="conteneur" style="display:block; background-color:transparent; width:80%;  border:1px solid #000000;">
					<div id="barre" style="display:block; background-color:rgba(132, 232, 104, 0.7); width:0%; height:10%;float:top;clear : top ;clear:both">
						<div id="pourcentage" style="text-align:right; height:100%; font-size:1.8em;">
							&nbsp;
						</div>
					</div>
				</div>
				<label id="progress_bar_description"></label></br>
				<label id="progress_bar_course"></label>';
				
				echo '</br> </br>';
				message ( get_string ( 'msg_backup_continue_background', 'report_retrievecourse' ), null );
				
				// $indice va permettre de suivre la progression de la barre de progression.
				$indice = 0;
				// Pour chaque cour , il y'aura un backup et un restore.
				// C'est pourquoi on double le nombre de cours.
				$nbElemRestore = count ( $cour ) * 2;
				$this->service->step = 1 / ($nbElemRestore);
				foreach ( $cour as $idCourse ) {
					$shortname = $this->db->getShortnameCourse ( $idCourse );
					$nextShortname = nextShortname ( $shortname );
					progression ( $indice );
					$this->service->currentProgress = $indice;
					if ($shortname != NULL) {
						$this->service->setCourse ( $idCourse );
						$this->service->setNextShortName ( $nextShortname );
						$this->service->runService ( $nbElemRestore );
						$this->retrievecoursedb->addCourse_retrievecourse ( $shortname, $nextShortname, $CFG->temp, $idCourse );
					}
					$indice += (100 / ($nbElemRestore)) * 2;
				}
				progression ( $indice );
				echo '</br> </br>';
				message ( get_string ( 'msg_backup_termine', 'report_retrievecourse' ), 'index.php' );
			}
		}
	}
	
	/**
	 * Cette méthode va supprimer tous les cours qui ont déjà fait les backup/restore.
	 * Cette méthode sert principalement à éviter de refaire les backup des même cours lorsqu'on réactualise la page.
	 * 
	 * @param array $cour        	
	 */
	private function supprimerCourUsedPlugin(&$cour) {
		$i = 0;
		$taille = count($cour);
		if ($cour[0] == - 1) {
			unset ( $cour[0] );
			$i = 1;
		}
		
		
		for(; $i < $taille; $i ++) {
			if ($this->retrievecoursedb->checkPluginUsed($cour[$i])) {
				unset ( $cour [$i] );
			}
		}
	}
	
	/**
	 * Permet de stocher une liste de cour dans la table "retrievecourse_cron".
	 * Le backup sera fait ultérieurement.
	 * 
	 * @param json $courJson
	 *        	Liste des cours dont il faut faire le backup.
	 */
	public function admin_use_cron($courJson) {
		global $CFG, $USER;
		if (isset ( $courJson )) {
			$cour = json_decode ( $courJson );
			foreach ( $cour as $idCourse ) {
				$shortname = $this->db->getShortnameCourse ( $idCourse );
				if ($shortname != NULL) {
					$nextShortname = nextShortname ( $shortname );
					if (! $this->retrievecoursedb->checkPluginUsed ( $idCourse )) {
						$this->crondb->addCourse_cron ( $idCourse, $USER->id, $nextShortname );
						$this->retrievecoursedb->addCourse_retrievecourse ( $shortname, $nextShortname, $CFG->temp, $idCourse, false, true );
					}
				}
			}
			message ( get_string ( 'msg_cron_ulterieurement', 'report_retrievecourse' ), '/report/retrievecourse/viewCronTasks.php' );
		}
	}
}