<?php

require_once (__DIR__ . '/../view/FormAdmin.php');
require_once (__DIR__ . '/../service/RetrieveCourseService.php');
require_once (__DIR__ . '/../model/RetrieveCourseConstante.php');
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
	private $db; 
	private $service;
	
	function __construct($formAdmin){
		global $USER;
		$this->formAdmin = $formAdmin;
		$this->db = new ManageDB();
		$this->service = new RetrieveCourseService(null, $USER->id, null);
	}
	
	function admin_submit(){
		global $PAGE;
		
		if ($this->formAdmin->is_cancelled()){
			redirect("../..");
		}elseif ($this->formAdmin->no_submit_button_pressed()) {
			//Rentrera ici lorsque l'utilisateur appuiera sur le bouton "trie" .
			$this->formAdmin->envoiInfoTrie();
		}elseif ($this->formAdmin->is_submitted()){
			$infoForm=$this->formAdmin->get_submitted_data();
			$message_cron =utf8_encode('Êtes-vous sûr de vouloir faire un backup/restore via cron?');
			$message_backup = utf8_encode('Êtes-vous sûr de vouloir faire un backup/restore immédiatement?');
			if($infoForm->choice_type_backup)
				$this->confirmation($message_backup, RetrieveCourseConstante::CONFIRMATION_BACKUP_IMMEDIAT,$infoForm->cours) ;
			else
				$this->confirmation($message_cron, RetrieveCourseConstante::CONFIRMATION_USE_CRON,$infoForm->cours) ;	
		} 
	}
	
	private function confirmation($message , $type_confirmation , $cours){
		global $PAGE,$OUTPUT;
		//Dans le cas où on a coché All.
		if($cours[0] == -1){
			//Enléve toute les valeur pour que le type de tableau est identique qu'on séléctionne all ou plusieur cour.
			//En effet , si on séléctionne pas ALL mais qu'on séléctionne des cours manuellement , '$cours' contiendra juste les id des
			//cours séléctionné.
			$cours = array_keys($this->formAdmin->getListeCour());
		}
		echo $OUTPUT->confirm($message, '/report/retrievecourse/index.php?confirmation='. $type_confirmation .
				'&cour='.json_encode($cours) , '/report/retrievecourse/index.php');
	}
	
	/**
	 * Permet de lancer le backup/restore pour une liste de cour.
	 * Permet également d'initialiser la barre de progression.
	 * @param json $courJson
	 * Liste des cours dont il faut faire le backup.
	 */
	public function backup_immediat($courJson){
		global $USER,$PAGE,$CFG;
		if(isset($courJson)){
			echo '<div id="conteneur" style="display:block; background-color:transparent; width:80%;  border:1px solid #000000;">
					<div id="barre" style="display:block; background-color:rgba(132, 232, 104, 0.7); width:0%; height:10%;float:top;clear : top ;clear:both">
						<div id="pourcentage" style="text-align:right; height:100%; font-size:1.8em;">
							&nbsp;
						</div>
					</div>
				</div>
				<label id="progress_bar_description"></label></br>
				<label id="progress_bar_course"></label>';
	
			$cour = json_decode($courJson);
			$indice = 0;
			$nbElemRestore = count($cour) ;
			$this->service->step =1/(count($nbElemRestore)*2);
			foreach ($cour as $idCourse){
				$shortname =  $this->db->getShortnameCourse($idCourse);
				$nextShortname = nextShortname($shortname);
				progression($indice);
				$this->service->currentProgress = $indice;
				if($shortname != NULL){
					var_dump($idCourse);
					var_dump($nextShortname);
					$this->service->setCourse($idCourse);
					$this->service->setNextShortName($nextShortname);
					$this->service->runService($nbElemRestore);
					$this->db->addCourse_retrievecourse($shortname , $nextShortname , $CFG->temp , $idCourse);
				}
				$indice += 100 /(count($nbElemRestore)*2 );
			}
		}
	}
	
	/**
	 * Permet de stocher une liste de cour dans la table "retrievecourse_cron". Le backup sera fait ultérieurement.
	 * @param json $courJson
	 * Liste des cours dont il faut faire le backup.
	 */
	public function admin_use_cron($courJson){
		global $CFG,$USER;
		if(isset($courJson)){
			$cour = json_decode($courJson);
			foreach ($cour as $idCourse){
				$shortname =  $this->db->getShortnameCourse($idCourse);
				if($shortname != NULL){
					$nextShortname = nextShortname($shortname);
					if(!$this->db->checkPluginUsed($idCourse)){
						$this->db->addCourse_cron($idCourse, $USER->id , $nextShortname);
						$this->db->addCourse_retrievecourse($shortname , $nextShortname , $CFG->temp , $idCourse ,false , true );
					}
					
				}
			}
			
			message(utf8_encode("Les cours seront traités ultérieurement via cron. </br>"),'/report/retrievecourse/viewCronTasks.php');
			
		}
	}
	
	
}