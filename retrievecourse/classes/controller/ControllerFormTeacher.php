<?php

require_once '/../model/ManageDB.php';
require_once '/../view/FormTeacher.php';
require_once '/../model/RetrieveCourseService.php';

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
	
	/**
	 * 
	 * @param FormTeacher $formTeacher
	 */
	function __construct($formTeacher){
		
		$this->formTeacher = $formTeacher;
		$this->db = new ManageDB();
	}
	
	
	public function teacher_submit($nextShortName){
		
		$infoForm = $this->formTeacher->get_data();
		($infoForm->choice_teacher) ?   $this->choiceNewCourse($nextShortName) : $this->choiceRetrieve($nextShortName) ;
		
	}
	
	private function choiceRetrieve($nextShortname){
		global $USER;
		echo 'retrieve </br>' ;
		echo 'techer: ' . $nextShortname . '</br>';
		$this->service = new RetrieveCourseService($_SESSION['idCourse'] , $USER->id , $nextShortname);
		$this->service->backup();
		echo '</br> début du restore </br>';
		$this->service->restore();
		
	}
	
	private function choiceNewCourse($nextShortName){
		 echo 'newcourse </br>' ;
		 $temp = substr($nextShortName, -6);	
		 $this->db->addCourse_retrievecourse($nextShortName , $temp);
	}
	
	
}