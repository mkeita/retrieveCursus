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
		echo 'retrieve </br>' ;
		$this->service = new RetrieveCourseService($_SESSION['idCourse'] , $USER->id , $this->nextShortname);
		$this->service->runService();
		$this->savePluginUsed();	
	}
	
	private function choiceNewCourse(){
		 echo 'newcourse </br>' ;
		 $this->savePluginUsed();	
	}
	
	private function savePluginUsed(){
		//TODO Il faudrait  faire un fichier config pour les différentes taille du temp du shortaname.
		$temp = substr($this->nextShortname, -6);
		$this->db->addCourse_retrievecourse($this->nextShortname , $temp , $_SESSION['idCourse']);
	}
	
	
}