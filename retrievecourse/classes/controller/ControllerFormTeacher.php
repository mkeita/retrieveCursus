<?php
require_once (__DIR__ . '/../model/ManageDB.php');
require_once (__DIR__ . '/../view/FormTeacher.php');
require_once (__DIR__ . '/../service/RetrieveCourseService.php');
require_once (__DIR__ . '/../model/ManageRetrieveCourseDB.php');
require_once (__DIR__ . '/../model/ManageRetrieveCourseCronDB.php');
require_once (__DIR__ . '/../model/RetrieveCourseConstante.php');
require_once (__DIR__ . '/../../outils.php');
/**
 *
 * @author Ilias
 *        
 */
class ControlleurFormTeacher {
	/**
	 *
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
	 * @var ManageRetrieveCourseCronDB
	 */
	private $crondb;
	
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
	private $nextShortname;
	
	/**
	 *
	 * @param FormTeacher $formTeacher        	
	 */
	function __construct($formTeacher) {
		$this->formTeacher = $formTeacher;
		$this->db = new ManageDB ();
		$this->crondb = new ManageRetrieveCourseCronDB ();
		$this->retrievecoursedb = new ManageRetrieveCourseDB ();
	}
	
	/**
	 * Cette fonction est appellé dés qu'on clique sur l'un des bouton de la vue des professeurs(cancel,submit,sort)
	 */
	public function teacher_submit($nextShortName) {
		global $OUTPUT;
		$infoForm = $this->formTeacher->get_data ();
		$message_newcourse = get_string ( 'msg_newcourse', 'report_retrievecourse' );
		$message_backup = get_string ( 'msg_backup', 'report_retrievecourse' );
		
		// Le choix effectuer par l'utilisateur est récupérer dans la classe ControllerPrincipale.php - méthode teacherDisplay()).
		// En fonction du choix effectué , il exécutera soit la méthode choiceRetrieve() soit la méthode choiceNewCourse() de cette classe.
		if ($infoForm->choice_teacher) {
			echo $OUTPUT->confirm ( $message_newcourse, '/report/retrievecourse/index.php?confirmation=' . RetrieveCourseConstante::CONFIRMATION_NEW_COURSE . '&shortname=' . $nextShortName, '/report/retrievecourse/index.php' );
		} else {
			echo $OUTPUT->confirm ( $message_backup, '/report/retrievecourse/index.php?confirmation=' . RetrieveCourseConstante::CONFIRMATION_BACKUP_TEACHER . '&shortname=' . $nextShortName, '/report/retrievecourse/index.php' );
		}
	}
	public function choiceRetrieve($nextShortname) {
		global $USER, $OUTPUT, $PAGE;
		
		$this->courseUsePlugin ( 0, 1, $nextShortname );
		$this->crondb->addCourse_cron ( $_SESSION ['idCourse'], $USER->id, $nextShortname );
		
		message ( get_string ( 'msg_retrieve', 'report_retrievecourse' ) );
	}
	public function choiceNewCourse($nextShortname) {
		$this->courseUsePlugin ( 1, 0, $nextShortname );
		redirection ( '../..' );
	}
	
	/**
	 * Permet d'enregistrer le cour dans la table 'retrievecourse'.
	 * 
	 * @param int $flag_newcourse
	 *        	Vaut 1 si c'est on commence un nouveau cour.
	 */
	private function courseUsePlugin($flag_newcourse, $flag_wait_cron, $nextShortname) {
		global $CFG;
		$shortname = $this->db->getShortnameCourse ( $_SESSION ['idCourse'] );
		$taille = $CFG->tempYearOne + $CFG->tempYearTwo;
		$temp = substr ( $shortname, - $taille );
		$this->retrievecoursedb->addCourse_retrievecourse ( $shortname, $nextShortname, $temp, $_SESSION ['idCourse'], $flag_newcourse, $flag_wait_cron );
	}
}