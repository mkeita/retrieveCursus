<?php

require_once '/../view/FormTeacher.php';
require_once 'ControllerFormTeacher.php';
require_once 'ControllerFormAdmin.php';
require_once '/../model/ManageDB.php';
require_once '/../view/FormAdmin.php';
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
	
	
	function __construct(){
		$this->db = new ManageDB();
	}
	
	/**
	 * Verifie que toute les conditions sont rempli pour pouvoir utiliser le plugin.
	 */
	public function verification(){
// 		for($i = 42 ; $i <= 45 ; $i++){
// 			$this->db->dropRow($i);
// 		}
		if(!is_siteadmin()){
			$this->verifierCreationCour();
			$this->verifierPluginUtilise();
			$this->checkTeacherOfNextCourse();
		}
		
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
		
		($formAdmin->is_submitted()) ? $controllerFormAdmin->admin_submit() :$formAdmin->display();
		
	}
	
	
	private function teacherDisplay(){
		global $PAGE;
		$formTeacher = new FormTeacher();
		$controllerFormTeacher = new ControlleurFormTeacher($formTeacher);
		($formTeacher->is_submitted()) ? $controllerFormTeacher->teacher_submit($this->nextShortname($PAGE->course->shortname))
										:$formTeacher->display();
	}
	
	/**
	 * Cette fonction permet de cr�e le shortname de l'ann�e acad�mique suiavnate.
	 * Cette fonction part du principe que les derniers caract�res repr�sentent l' ann�e acad�mique.
	 * @param string $course
	 * @return Le shortname du cour pour l'ann�e acad�mique suivante.
	 */
	private function nextShortname($course ,$tailleTemp = 6, $tailleYearOne = 4,$tailleYearTwo = 2){
		$temp = substr($course, -$tailleTemp);
		$yearOne = substr($temp, 0 , $tailleYearOne);
		$yearTwo = substr($temp,-$tailleYearTwo);
		$yearOne += 1;
		$yearTwo = ($yearTwo +1) % 100 ;
		$mnemo = substr($course, 0 , strlen($course)- $tailleTemp)	;
		$newShortname = $mnemo . $yearOne . $yearTwo ;
		return $newShortname;
	}
	/**
	 * Permet de v�rifier si le cour de l'ann�e prochaine a bien �t� cr�e.
	 */
	private function verifierCreationCour(){
		global $PAGE;
		$nextShortname = $this->nextShortname($PAGE->course->shortname);
		if(!$this->db->checkCourseExist($nextShortname)){
			?> <script type="text/javascript" charset="utf-8" >
					alert("Le cour de l'ann\351e prochaine n'a pas encore \351t\351 cr\351e");
				</script>
	 		<?php 
	 		redirect('http://localhost/course/view.php?id='.$_SESSION['idCourse']);
	 	}
	 }
	
	/**
	 * Permet de v�rifier si le plugin a d�j� �t� utilis�.
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
	 * Permet de v�rifier que le cours de l'ann�e prochaine poss�de le m�me professeur que celui 
	 * de l'ann�e courante.
	 */
	private function checkTeacherOfNextCourse(){
		global $PAGE,$DB,$USER;
		$idCourseNextYear = $this->db->getCourseId($this->nextShortname($PAGE->course->shortname));
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