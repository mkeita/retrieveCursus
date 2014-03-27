<?php

require_once '/../model/ManageDB.php';
require_once '/../view/FormTeacher.php';

class ControlleurFormTeacher {
	/**
	 * @var FormTeacher
	 */
	private $formTeacher;
	/**
	 * 
	 * @var ManageDB
	 */
	private $db;
	
	/**
	 * 
	 * @param FormTeacher $formTeacher
	 */
	function __construct($formTeacher){
		$this->formTeacher = $formTeacher;
		$this->db = new ManageDB();
	}
	
	
	public function teacher_submit($course_shortname){
		$nextShortName = $this->nextShortname($course_shortname);
		if($this->db->checkCourseExist($nextShortName)){
			$infoForm = $this->formTeacher->get_data();
		   $t = ($infoForm->choice_teacher) ?  'newcourse' :  'retrieve' ;
		   echo $t;
		}else{
			$this->msgCoursExistePas($nextShortName);
		}
	}
	
	 /**
	 * Cette fonction permet de crée le shortname de l'année académique suiavnate.
	 * Cette fonction part du principe que les dernier caractére représent l' année académique.
	 * @param string $course 
	 * @return Le shortname du cour pour l'année académique suivante.
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
	
	private function msgCoursExistePas($course){
		global $PAGE;
		echo utf8_encode('Le cour '. $course . ' n\' a pas encore été crée. </br>');
		echo utf8_encode('Veuillez réessayer ultérieurement');
		//header($PAGE->url,2);
	}
	
}