<?php

require_once '/../model/ManageDB.php';
require_once '/../view/FormTeacher.php';
require_once '/../service/RetrieveCourseService.php';
/**
 * 
 * @author Ilias
 *
 */
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
	 * @var RetrieveCourseService
	 */
	private $service;
	
	private $nextShortname;
	
	/**
	 * 
	 * @param FormTeacher $formTeacher
	 */
	function __construct($formTeacher){
		
		$this->formTeacher = $formTeacher;
		$this->db = new ManageDB();
	}
	
	
	public function teacher_submit($nextShortName){
		$this->nextShortname = $nextShortName;
		$infoForm = $this->formTeacher->get_data();
		($infoForm->choice_teacher) ?   $this->choiceNewCourse() : $this->choiceRetrieve() ;
		
	}
	
	private function choiceRetrieve(){
		global $USER;
		$this->db->addCourse_cron($_SESSION['idCourse'], $USER->id , $this->nextShortname);
		$this->courseUsePlugin(0, 1);
		
	}
	
	private function choiceNewCourse(){
		$this->courseUsePlugin(1, 0);
		
	}
	
	/**
	 * Permet d'enregistrer le cour dans la table 'retrievecourse'.
	 * @param int $flag_newcourse Vaut 1 si c'est on commence un nouveau cour.
	 */
	private function courseUsePlugin($flag_newcourse , $flag_wait_cron){
		global $CFG;
		$shortname = $this->db->getShortnameCourse($_SESSION['idCourse']);
		$taille = $CFG->tempYearOne + $CFG->tempYearTwo ; 
		$temp = substr($shortname, -$taille );
		$this->db->addCourse_retrievecourse($shortname , $this->nextShortname , $temp , $_SESSION['idCourse'] , $flag_newcourse , $flag_wait_cron );
	}
	
}