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
	 * Cette fonction permet de cr�e le shortname de l'ann�e acad�mique suiavnate.
	 * Cette fonction part du principe que les dernier caract�re repr�sent l' ann�e acad�mique.
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
	
	private function msgCoursExistePas($course){
		global $PAGE;
		echo utf8_encode('Le cour '. $course . ' n\' a pas encore �t� cr�e. </br>');
		echo utf8_encode('Veuillez r�essayer ult�rieurement');
		//header($PAGE->url,2);
	}
	
}