<?php
defined('MOODLE_INTERNAL') || die();

global $CFG;

var_dump($CFG->dirroot);


//require('../../config.php');
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once '/../model/ManageDB.php';

/**
 * RetrieveCursusService va permettre de faire le backup et le restore. 
 * @author Ilias
 *
 */
class RetrieveCourseService {
	/**
	 * Id du cours courant.
	 * @var int
	 */
	private $course;
	
	/**
	 * Id de l'user courant.
	 * @var string $user.
	 */
	private $user;
	/**
	 * Dossier où a été crée le backup.
	 * @var string
	 */
	private $folder;
	
	/**
	 * Shortname du cour où il y'aura le restore.
	 * @var string
	 */
	private $nextShortname;
	
	/**
	 * 
	 * @var ManageDB
	 */
	private $db;
	
	/**
	 * Constructeur.
	 * @param int $course
	 *Id du cours courant.
	 * @param int $user
	 * Id de l'user courant.
	 * @param string $nextShortname
	 *  Shortname du cour vers où se fera le restore.
	 */
	function __construct($course , $user , $nextShortname){
		$this->course = $course;
		$this->user = $user;
		$this->folder = NULL;
		$this->db = new ManageDB();
		$this->nextShortname = $nextShortname;
	}
	
	public function backup(){

		$bc = new backup_controller(backup::TYPE_1COURSE, $this->course, backup::FORMAT_MOODLE,
				backup::INTERACTIVE_YES, backup::MODE_GENERAL, $this->user);
		$bc->finish_ui();
		$bc->execute_plan();
		
		$bc->get_results();
		$this->folder = $bc->get_backupid();
		
		
	}
	
	public function restore(){
		global $DB,$CFG,$USER;
		if($this->folder != NULL){
			$courseId = $this->db->retieveCourseId($this->nextShortname);
			if($courseId != NULL){
				$transaction = $DB->start_delegated_transaction();
				$options = array();
				$options['keep_roles_and_enrolments'] = 1;
				$options['keep_groups_and_groupings'] = 0;
				restore_dbops::delete_course_content($courseId, $options);
				$transaction->allow_commit();				
				$transaction = $DB->start_delegated_transaction();
				$controller = new restore_controller($this->folder, $courseId,
						backup::INTERACTIVE_NO, backup::MODE_SAMESITE, $USER->id,
						 backup::TARGET_EXISTING_DELETING);
				$controller->execute_precheck();
				$controller->execute_plan();
				$controller->destroy();
				$transaction->allow_commit();
			}else{
				echo 'il y a un petit souci </br>';
			}
			
		}
		
	}
	
	
}