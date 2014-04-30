<?php

require_once '/../view/FormTeacher.php';
require_once 'ControllerFormTeacher.php';
require_once 'ControllerFormAdmin.php';
require_once '/../model/ManageDB.php';
require_once '/../view/FormAdmin.php';
require_once '/../model/RetrieveCourseConstante.php';

/**
 * 
 * @author Ilias
 *
 */
class ControllerPrincipal {
	/**
	 * 
	 * @var ManageDB
	 */
	private $db;
	
	/**
	 * 
	 * @var RetrieveCourseService
	 */
	private $service;
	
	function __construct(){
		global $USER;
		$this->db = new ManageDB();
		$this->service = new RetrieveCourseService(null, $USER->id , null);
	}
	
	/**
	 * Verifie que toute les conditions sont rempli pour pouvoir utiliser le plugin.
	 */
	public function verification(){
		if(!is_siteadmin()){
			$this->verifierCreationCour();
			$this->verifierPluginUtilise();
			$this->checkTeacherOfNextCourse();
		}
		
	}
	
	
	
	/**
	 * Affiche une vue différente en fonction que la personne connecté est un administrateur ou un professeur.
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
			if($this->db->checkCourseExist($nextShortname)){
				switch($confirm){
					case RetrieveCourseConstante::CONFIRMATION_NEW_COURSE : $controllerFormTeacher->choiceNewCourse($nextShortname);break;
					case RetrieveCourseConstante::CONFIRMATION_BACKUP_TEACHER : $controllerFormTeacher->choiceRetrieve($nextShortname) ; break;
					default: redirect($PAGE->url);break;
				}	
			}else{
				redirect($PAGE->url);
			}
		}else{
			($formTeacher->is_submitted()) ? $controllerFormTeacher->teacher_submit(nextShortname($PAGE->course->shortname))
			:$formTeacher->display();
		}
		
		
	}
	
	/**
	 * Permet de vérifier si le cour de l'année prochaine a bien été crée.
	 */
	private function verifierCreationCour(){
		global $PAGE;
		$nextShortname = nextShortname($PAGE->course->shortname);
		if(!$this->db->checkCourseExist($nextShortname)){
			?> <script type="text/javascript" charset="utf-8" >
					alert("Le cour de l'ann\351e prochaine n'a pas encore \351t\351 cr\351e");
				</script>
	 		<?php 
	 		redirect('http://localhost/course/view.php?id='.$_SESSION['idCourse']);
	 	}
	 }
	
	/**
	 * Permet de vérifier si le plugin a déjà été utilisé.
	 */ 
	private function verifierPluginUtilise(){
		global $PAGE;
		$course_used = $this->db->checkPluginUsed($_SESSION['idCourse']);
		if($course_used){
			?> <script type="text/javascript" charset="utf-8" >
					alert("Le plugin a d\351j\340 \351t\351 utilis\351.");
				</script>
	 		<?php 
	 		redirect('http://localhost/course/view.php?id='.$_SESSION['idCourse']);
		}
	}
	
	/**
	 * Permet de vérifier que le cours de l'année prochaine posséde le même professeur que celui 
	 * de l'année courante.
	 */
	private function checkTeacherOfNextCourse(){
		global $PAGE,$DB,$USER;
		$idCourseNextYear = $this->db->getCourseId(nextShortname($PAGE->course->shortname));
		$ok = (($idCourseNextYear != NULL) && ($this->db->checkUserEnroledInCourse($idCourseNextYear ,$USER->id)));
		if(!$ok){
			?> <script type="text/javascript" charset="utf-8" >
					alert("Vous n' \352tes pas le professeur titulaire du cours de l'ann\351e prochaine");
				</script>
	 		<?php 
	 		redirect('http://localhost/course/view.php?id='.$_SESSION['idCourse']);
		}	
	}
	
	
	
	

}