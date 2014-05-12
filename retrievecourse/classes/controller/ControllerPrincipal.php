<?php

require_once (__DIR__ . '/../view/FormTeacher.php');
require_once (__DIR__ . '/ControllerFormTeacher.php');
require_once (__DIR__ . '/ControllerFormAdmin.php');
require_once (__DIR__ . '/../model/ManageDB.php');
require_once (__DIR__ . '/../model/ManageRetrieveCourseDB.php');
require_once (__DIR__ . '/../model/ManageRetrieveCourseCronDB.php');
require_once (__DIR__ . '/../view/FormAdmin.php');
require_once (__DIR__ . '/../model/RetrieveCourseConstante.php');
require_once (__DIR__ . '/../../outils.php');

/**
 * 
 * @author Ilias
 *
 */
class ControllerPrincipal {
	/**
	 * @var ManageDB
	 */
	private $managedb;
	/**
	 * 
	 * @var ManageRetrieveCourseDB
	 */
	private $retrievecoursedb;
	
	/**
	 * 
	 * @var RetrieveCourseService
	 */
	private $service;
	
	function __construct(){
		global $USER;
		$this->managedb = new ManageDB();
		$this->retrievecoursedb = new ManageRetrieveCourseDB();
		$this->service = new RetrieveCourseService(null, $USER->id , null);
	}
	
	/**
	 * Verifie que toute les conditions sont rempli pour pouvoir utiliser le plugin.
	 */
	public function verification(){
		$outcome = true;
		if(!is_siteadmin()){
			$outcome = $this->verifierCreationCour() && $this->verifierPluginUtilise() && 
							$this->checkTeacherOfNextCourse();
		}
		return $outcome;
	}
	
	
	
	/**
	 * Affiche une vue diff�rente en fonction que la personne connect� est un administrateur ou un professeur.
	 */
	public function display(){
		(is_siteadmin()) ? $this->adminDisplay() : $this->teacherDisplay();
	}
	
	
	private function adminDisplay(){
		$formAdmin = new FormAdmin();
		$controllerFormAdmin = new ControllerFormAdmin($formAdmin);
		
		$confirm = optional_param('confirmation', 0, PARAM_TEXT);
		$courJson = optional_param('cour', 0, PARAM_TEXT);
		
		if($confirm != NULL && $courJson != NULL){
			switch($confirm){
				case RetrieveCourseConstante::CONFIRMATION_BACKUP_IMMEDIAT : $controllerFormAdmin->backup_immediat($courJson); break;
				case RetrieveCourseConstante::CONFIRMATION_USE_CRON : $controllerFormAdmin->admin_use_cron($courJson) ; break;
				default: redirect($PAGE->url);break;
			}
		}else{
			($formAdmin->is_submitted()) ? $controllerFormAdmin->admin_submit() : $formAdmin->display();
		}
		
		
	}
	
	
	private function teacherDisplay(){
		global $PAGE;
		$formTeacher = new FormTeacher();
		$controllerFormTeacher = new ControlleurFormTeacher($formTeacher);
		
		$confirm = optional_param('confirmation', 0, PARAM_TEXT);
		$nextShortname =  optional_param('shortname', 0, PARAM_TEXT);
		if($confirm != NULL && $nextShortname != NULL){
			if($this->managedb->checkCourseExist($nextShortname)){
				switch($confirm){
					case RetrieveCourseConstante::CONFIRMATION_NEW_COURSE : $controllerFormTeacher->choiceNewCourse($nextShortname);break;
					case RetrieveCourseConstante::CONFIRMATION_BACKUP_TEACHER : $controllerFormTeacher->choiceRetrieve($nextShortname) ; break;
					default: redirection($PAGE->url);break;
				}	
			}else{
				redirection($PAGE->url);
			}
		}else{
			($formTeacher->is_submitted()) ? $controllerFormTeacher->teacher_submit(nextShortname($PAGE->course->shortname))
			:$formTeacher->display();
		}
		
		
	}
	
	/**
	 * Permet de v�rifier si le cour de l'ann�e prochaine a bien �t� cr�e.
	 */
	private function verifierCreationCour(){
		global $PAGE;
		$outcome = true;
		$nextShortname = nextShortname($PAGE->course->shortname);
		if(!$this->managedb->checkCourseExist($nextShortname)){
			$msg = utf8_encode("Le cour de l'ann�e prochaine n'a pas encore �t� cr�e");
			message($msg);
			$outcome = false;
		 }
		 return $outcome;
	}
	
	/**
	 * Permet de v�rifier si le plugin a d�j� �t� utilis�.
	 */ 
	private function verifierPluginUtilise(){
		global $PAGE;
		$outcome = true;
		$course_used = $this->retrievecoursedb->checkPluginUsed($_SESSION['idCourse']);
		if($course_used){
			$msg = utf8_encode("Le plugin a d�j� �t� utilis�.");
			message($msg);
			$outcome = false;
		}
		return $outcome;
	}
	
	/**
	 * Permet de v�rifier que le cours de l'ann�e prochaine poss�de le m�me professeur que celui 
	 * de l'ann�e courante.
	 */
	private function checkTeacherOfNextCourse(){
		global $PAGE,$DB,$USER;
		$outcome = true;
		$idCourseNextYear = $this->managedb->getCourseId(nextShortname($PAGE->course->shortname));
		$ok = (($idCourseNextYear != NULL) && ($this->managedb->checkUserEnroledInCourse($idCourseNextYear ,$USER->id)));
		if(!$ok){
			$msg =utf8_encode("Vous n' �tes pas le professeur titulaire du cours de l'ann�e prochaine");
			message($msg);
			$outcome = false;
		}	
		return $outcome;
	}
	
	
	
	

}