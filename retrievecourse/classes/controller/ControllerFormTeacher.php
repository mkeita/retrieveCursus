<?php
require_once (__DIR__ . '/../model/ManageDB.php');
require_once (__DIR__ . '/../view/FormTeacher.php');
require_once (__DIR__ . '/../service/RetrieveCourseService.php');
require_once (__DIR__ . '/../model/RetrieveCourseConstante.php');
require_once (__DIR__ . '/../../outils.php');
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
		global $OUTPUT;
		$infoForm = $this->formTeacher->get_data();
		$message_newcourse =utf8_encode('Êtes-vous sûr de ne pas vouloir récupérer les informations de votre cours  ?');
		$message_backup = utf8_encode('Êtes-vous sûr de vouloir récupérer les informations de votre cours </br>
											et de supprimer le contenue du cours de l\'année académique suivante?');
		if($infoForm->choice_teacher){
			echo $OUTPUT->confirm($message_newcourse, '/report/retrievecourse/index.php?confirmation='.RetrieveCourseConstante::CONFIRMATION_NEW_COURSE 
					.'&shortname='.$nextShortName , '/report/retrievecourse/index.php');
		}else{
			echo $OUTPUT->confirm($message_backup, '/report/retrievecourse/index.php?confirmation='. RetrieveCourseConstante::CONFIRMATION_BACKUP_TEACHER .
					'&shortname='.$nextShortName , '/report/retrievecourse/index.php');
		}		
	}
	
	public function choiceRetrieve($nextShortname){
		global $USER , $OUTPUT, $PAGE;
		$this->db->addCourse_cron($_SESSION['idCourse'], $USER->id , $nextShortname);
		$this->courseUsePlugin(0, 1,$nextShortname);
		$msg = utf8_encode('La récupération des informations de votre cours se fera ultérieurement. </br>') ;
		$msg .=utf8_encode('Vous recevrez un email une fois vos informations récupérées. </br></br></br>') ;
		
		message($msg);
		
		
		
	}
	
	public function choiceNewCourse($nextShortname){
		$this->courseUsePlugin(1, 0,$nextShortname);
 		redirect('../..');
	}
	
	/**
	 * Permet d'enregistrer le cour dans la table 'retrievecourse'.
	 * @param int $flag_newcourse Vaut 1 si c'est on commence un nouveau cour.
	 */
	private function courseUsePlugin($flag_newcourse , $flag_wait_cron , $nextShortname){
		global $CFG;
		$shortname = $this->db->getShortnameCourse($_SESSION['idCourse']);
		$taille = $CFG->tempYearOne + $CFG->tempYearTwo ; 
		$temp = substr($shortname, -$taille );
		$this->db->addCourse_retrievecourse($shortname , $nextShortname , $temp , $_SESSION['idCourse'] , $flag_newcourse , $flag_wait_cron );
	}
	
}