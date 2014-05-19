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
	private $db; 
	private $retrievecoursedb;
	private $crondb;
	private $service;
	
	function __construct($formAdmin){
		global $USER;
		$this->formAdmin = $formAdmin;
		$this->db = new ManageDB();
		$this->crondb = new ManageRetrieveCourseCronDB();
		$this->retrievecoursedb = new ManageRetrieveCourseDB();
		$this->service = new RetrieveCourseService(null, $USER->id, null);
	}
	
	function admin_submit(){
		global $PAGE;
		
		if ($this->formAdmin->is_cancelled()){
			redirection("../..");
		}elseif ($this->formAdmin->no_submit_button_pressed()) {
			//Rentrera ici lorsque l'utilisateur appuiera sur le bouton "trie" .
			$this->envoiInfoTrie();
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
	
	
	private function envoiInfoTrie(){
		global $PAGE;
		$data = $this->formAdmin->get_submitted_data();
		$url = $PAGE->url . '?';
		if($data->recherche != ""){
			$url .= 'search='. $data->recherche;
		}else{
			$idCategory = ($data->category == -1) ? NULL : $data->category ;
			$url .= 'categories=' . $idCategory;
		}
		redirection($url);

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
		global $USER,$PAGE,$CFG,$OUTPUT;
		
		if(isset($courJson)){
			$cour = json_decode($courJson);
			
			$this->supprimerCourUsedPlugin($cour);
			
			if(count($cour) == 0){
				message(utf8_encode("Ce cours a déjà fait le backup et le restore"));
			}else{
				echo '<div id="conteneur" style="display:block; background-color:transparent; width:80%;  border:1px solid #000000;">
					<div id="barre" style="display:block; background-color:rgba(132, 232, 104, 0.7); width:0%; height:10%;float:top;clear : top ;clear:both">
						<div id="pourcentage" style="text-align:right; height:100%; font-size:1.8em;">
							&nbsp;
						</div>
					</div>
				</div>
				<label id="progress_bar_description"></label></br>
				<label id="progress_bar_course"></label>';
				
				$indice = 0;
				//BAckup/restore pour un cour
				$nbElemRestore = count($cour) * 2 ;
				$this->service->step =1/($nbElemRestore);
				foreach ($cour as $idCourse){
					$shortname =  $this->db->getShortnameCourse($idCourse);
					$nextShortname = nextShortname($shortname);
					progression($indice);
					$this->service->currentProgress = $indice;
					if($shortname != NULL){
						$this->service->setCourse($idCourse);
						$this->service->setNextShortName($nextShortname);
						$this->service->runService($nbElemRestore);
						$this->retrievecoursedb->addCourse_retrievecourse($shortname , $nextShortname , $CFG->temp , $idCourse);
					}
					$indice += (100 /($nbElemRestore ))*2;
				}
				
				echo '</br> </br>';
				$msg = utf8_encode("Backup/Restore terminé avec succés");
				message($msg,'index.php');
			}
			
		}
	}
	
	private function supprimerCourUsedPlugin(&$cour){
		//Cette méthode sert principalement à éviter de refaire les backup des même cours lorsqu'on réactualise la page.
		if($cour[0] == -1){
			unset($cour[0]);
		}
			
		for($i = 0 ; $i < count($cour) ; $i++){
			if($this->retrievecoursedb->checkPluginUsed($cour[$i])){
				unset($cour[$i]);
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
					if(!$this->retrievecoursedb->checkPluginUsed($idCourse)){
						$this->crondb->addCourse_cron($idCourse, $USER->id , $nextShortname);
						$this->retrievecoursedb->addCourse_retrievecourse($shortname , $nextShortname , $CFG->temp , $idCourse ,false , true );
					}
					
				}
			}
			
			message(utf8_encode("Les cours seront traités ultérieurement via cron. </br>"),'/report/retrievecourse/viewCronTasks.php');
			
		}
	}
	
	
}