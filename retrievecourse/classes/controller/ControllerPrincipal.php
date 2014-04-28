<?php

require_once '/../view/FormTeacher.php';
require_once 'ControllerFormTeacher.php';
require_once 'ControllerFormAdmin.php';
require_once '/../model/ManageDB.php';
require_once '/../view/FormAdmin.php';
require_once '/../../outils.php';
require_once '/../model/RetrieveCourseConstante.php';
require_once '/../service/RetrieveCourseService.php';
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
	 * Permet de vérifier si un choix de backup a été séléctionner.
	 * Cette fonction permettra également de lancer le service associé au choix efféctué.
	 * @return boolean
	 * retourne vrai si  un choix de backup a été séléctionner.
	 */
	public function choice_type_backup(){
		$confirm = optional_param('confirmation', 0, PARAM_TEXT);
		$courJson = optional_param('cour', 0, PARAM_TEXT);
		
		if($confirm != NULL){
			switch($confirm){
				case RetrieveCourseConstante::CONFIRMATION_BACKUP_IMMEDIAT : $this->backup_immediat($courJson); break;
				case RetrieveCourseConstante::CONFIRMATION_USE_CRON : $this->admin_use_cron($courJson) ; break;
				default: redirect($PAGE->url);break;
			}
		}
		return $confirm != NULL;
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
		($formAdmin->is_submitted()) ? $controllerFormAdmin->admin_submit() : $formAdmin->display();
		
	}
	
	
	private function teacherDisplay(){
		global $PAGE;
		$formTeacher = new FormTeacher();
		$controllerFormTeacher = new ControlleurFormTeacher($formTeacher);
		($formTeacher->is_submitted()) ? $controllerFormTeacher->teacher_submit(nextShortname($PAGE->course->shortname))
										:$formTeacher->display();
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
	
	
	/**
	 * Permet de lancer le backup/restore pour une liste de cour.
	 * Permet également d'initialiser la barre de progression.
	 * @param json $courJson
	 * Liste des cours dont il faut faire le backup.
	 */
	private function backup_immediat($courJson){
		global $USER,$PAGE,$CFG;
		if(isset($courJson)){
			echo '<div id="conteneur" style="display:block; background-color:transparent; width:80%; border:1px solid #000000;">
					<div id="barre" style="display:block; background-color:rgba(132, 232, 104, 0.7); width:0%; height:100%;float:top;clear : top ;clear:both">
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
	private function admin_use_cron($courJson){
		global $CFG,$USER;
		if(isset($courJson)){
			$cour = json_decode($courJson);
			foreach ($cour as $idCourse){
				$shortname =  $this->db->getShortnameCourse($idCourse);
				if($shortname != NULL){
					$nextShortname = nextShortname($shortname);
					$this->db->addCourse_cron($idCourse, $USER->id , $nextShortname);
					$this->db->addCourse_retrievecourse($shortname , $nextShortname , $CFG->temp , $idCourse ,false , true );
				}
			}
		}
	}
	
	

}