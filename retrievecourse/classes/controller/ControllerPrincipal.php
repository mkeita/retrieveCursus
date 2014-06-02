<?php
require_once (__DIR__ . '/../view/FormTeacher.php');
require_once (__DIR__ . '/ControllerFormTeacher.php');
require_once (__DIR__ . '/ControllerFormAdmin.php');
require_once (__DIR__ . '/../model/ManageDB.php');
require_once (__DIR__ . '/../model/ManageRetrieveCourseDB.php');
require_once (__DIR__ . '/../model/ManageRetrieveCourseCronDB.php');
require_once (__DIR__ . '/../view/FormAdmin.php');
require_once (__DIR__ . '/../model/RetrieveCourseConstante.php');
require_once (__DIR__ . '/../../outils.php');

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
	private $managedb;
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
	function __construct() {
		global $USER;
		$this->managedb = new ManageDB ();
		$this->retrievecoursedb = new ManageRetrieveCourseDB ();
		$this->service = new RetrieveCourseService ( null, $USER->id, null );
	}
	
	/**
	 * Verifie que toute les conditions sont rempli pour pouvoir utiliser le plugin.
	 */
	public function verification() {
		$outcome = true;
		if (! is_siteadmin ()) {
			$outcome = $this->verifierCreationCour () && $this->verifierPluginUtilise () && $this->checkTeacherOfNextCourse ();
		}
		return $outcome;
	}
	
	/**
	 * Affiche une vue différente en fonction que la personne connecté est un administrateur ou un professeur.
	 */
	public function display() {
		(is_siteadmin ()) ? $this->adminDisplay () : $this->teacherDisplay ();
	}
	private function adminDisplay() {
		// Ces informations sont placé dans l'url dans la méthode admin_submit de la classe ControllerFormTeacher
		$confirm = optional_param ( 'confirmation', 0, PARAM_TEXT );
		$courJson = optional_param ( 'cour', 0, PARAM_TEXT );
		
		if ($confirm != NULL && $courJson != NULL) {
			$controllerFormAdmin = new ControllerFormAdmin ( null );
			switch ($confirm) {
				case RetrieveCourseConstante::CONFIRMATION_BACKUP_IMMEDIAT :
					$controllerFormAdmin->backup_immediat ( $courJson );
					break;
				case RetrieveCourseConstante::CONFIRMATION_USE_CRON :
					$controllerFormAdmin->admin_use_cron ( $courJson );
					break;
				default :
					redirect ( $PAGE->url );
					break;
			}
		} else {
			$formAdmin = new FormAdmin ();
			$controllerFormAdmin = new ControllerFormAdmin ( $formAdmin );
			($formAdmin->is_submitted ()) ? $controllerFormAdmin->admin_submit () : $formAdmin->display ();
		}
	}
	private function teacherDisplay() {
		global $PAGE;
		$formTeacher = new FormTeacher ();
		$controllerFormTeacher = new ControlleurFormTeacher ( $formTeacher );
		// Ces informations sont placé dans l'url dans la méthode teacher_submit() de la classe ControllerFormTeacher
		$confirm = optional_param ( 'confirmation', 0, PARAM_TEXT );
		$nextShortname = optional_param ( 'shortname', 0, PARAM_TEXT );
		if ($confirm != NULL && $nextShortname != NULL) {
			if ($this->managedb->checkCourseExist ( $nextShortname )) {
				switch ($confirm) {
					case RetrieveCourseConstante::CONFIRMATION_NEW_COURSE :
						$controllerFormTeacher->choiceNewCourse ( $nextShortname );
						break;
					case RetrieveCourseConstante::CONFIRMATION_BACKUP_TEACHER :
						$controllerFormTeacher->choiceRetrieve ( $nextShortname );
						break;
					default :
						redirection ( $PAGE->url );
						break;
				}
			} else {
				redirection ( $PAGE->url );
			}
		} else {
			($formTeacher->is_submitted ()) ? $controllerFormTeacher->teacher_submit ( nextShortname ( $PAGE->course->shortname ) ) : $formTeacher->display ();
		}
	}
	
	/**
	 * Permet de vérifier si le cour de l'année prochaine a bien été crée.
	 */
	private function verifierCreationCour() {
		global $PAGE;
		$outcome = true;
		$nextShortname = nextShortname ( $PAGE->course->shortname );
		if (! $this->managedb->checkCourseExist ( $nextShortname )) {
			message ( get_string ( 'msg_error_cours_non_cree', 'report_retrievecourse' ) );
			$outcome = false;
		}
		return $outcome;
	}
	
	/**
	 * Permet de vérifier si le plugin a déjà été utilisé.
	 */
	private function verifierPluginUtilise() {
		global $PAGE;
		$outcome = true;
		$course_used = $this->retrievecoursedb->checkPluginUsed ( $_SESSION ['idCourse'] );
		if ($course_used) {
			message ( get_string ( 'msg_error_plugin_deja_utlise', 'report_retirevecourse' ) );
			$outcome = false;
		}
		return $outcome;
	}
	
	/**
	 * Permet de vérifier que le cours de l'année prochaine posséde le même professeur que celui
	 * de l'année courante.
	 */
	private function checkTeacherOfNextCourse() {
		global $PAGE, $DB, $USER;
		$outcome = true;
		$idCourseNextYear = $this->managedb->getCourseId ( nextShortname ( $PAGE->course->shortname ) );
		$ok = (($idCourseNextYear != NULL) && ($this->managedb->checkUserEnroledInCourse ( $idCourseNextYear, $USER->id )));
		if (! $ok) {
			message ( get_string ( "msg_error_techar", 'report_retrievecourse' ) );
			$outcome = false;
		}
		return $outcome;
	}
}