<?php
defined('MOODLE_INTERNAL') || die();

global $CFG;


//require('../../config.php');
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot . '/backup/moodle2/backup_plan_builder.class.php');
require_once($CFG->dirroot . '/backup/util/ui/import_extensions.php');
require_once '/../model/ManageDB.php';
require_once '/../model/RetrieveCourseConstante.php';

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
	 * @var int $user.
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
	 * Permettra de savoir si le backup est immédiat ou s'il est fait à l'aide de cron.
	 * @var int
	 */
	private $flagcron ;
	/**
	 * 
	 * @var ManageDB
	 */
	private $db;
	

	
	public $currentProgress = 0;
	public $step = 1;
	
	/**
	 * Constructeur.
	 * @param int $course
	 *Id du cours courant.
	 * @param int $user
	 * Id de l'user courant.
	 * @param string $nextShortname
	 *  Shortname du cour vers où se fera le restore.
	 */
	function __construct($course , $user , $nextShortname , $flagcron = RetrieveCourseConstante::USE_BACKUP_IMMEDIATELLY){
		$this->db = new ManageDB();
		
		$this->setUserId($user);
		
		$this->setCourse($course);
		
		$this->folder = NULL;
		
		$this->flagcron = $flagcron;
	
		$this->setNextShortName($nextShortname);
	}
	
	/**
	 * Permet de lancer le backup du cour courant et le restore vers le cour de l'année suivante.
	 */
	public function runService(){
		if($this->course != NULL && $this->nextShortname != NULL && $this->user != NULL){
			$this->backup();
			$this->restore();
		}else{
			echo utf8_encode("Erreur!!!
					Veuillez vérifier que le cours, le shortname, userid entrer soit bien dans la base de donné");
		}
	
	}
	
	private function backup(){
		global $CFG;
		if($this->flagcron == RetrieveCourseConstante::USE_BACKUP_IMMEDIATELLY){
			echo "<script>";
			echo "document.getElementById('progress_bar_course').innerHTML='Backup du cour : ".$this->db->getShortnameCourse($this->course)."';";
			echo "</script>";
		}
		
		$bc = new backup_controller(backup::TYPE_1COURSE, $this->course, backup::FORMAT_MOODLE,
				backup::INTERACTIVE_YES, backup::MODE_GENERAL, $this->user);
		
		$backup = new import_ui($bc);
		// Process the current stage
		$backup->process();
		
		$logger = new WebCTServiceLogger($CFG->debugdeveloper ? backup::LOG_DEBUG : backup::LOG_INFO);
	    $progress = new WebCTServiceProgress($logger,$this->flagcron,$this->currentProgress,$this->step);
	    $progress->start_progress('', 1);
		$backup->get_controller()->set_progress($progress);
		$backup->get_controller()->add_logger($logger);
		
		$bc->finish_ui();
		$bc->execute_plan();	
		$bc->get_results();
		$this->folder = $bc->get_backupid();
		$this->currentProgress = $progress->getProgress();	
	}
	
	private function restore(){
		global $DB,$CFG,$USER;
		if($this->folder != NULL){
			if($this->flagcron == RetrieveCourseConstante::USE_BACKUP_IMMEDIATELLY){
				echo "<script>";
				echo "document.getElementById('progress_bar_course').innerHTML='Restauration vers le cour : ".$this->nextShortname."';";
				echo "</script>";
			}
			
			$courseId = $this->db->retieveCourseId($this->nextShortname);
			if($courseId != NULL){
				$transaction = $DB->start_delegated_transaction();
				$options = array();
				//TODO Probleme des étudiant recupéré
				$options['keep_roles_and_enrolments'] = 1;
				$options['keep_groups_and_groupings'] = 0;
				restore_dbops::delete_course_content($courseId, $options);
				$transaction->allow_commit();				
				$transaction = $DB->start_delegated_transaction();
				
				$logger = new WebCTServiceLogger($CFG->debugdeveloper ? backup::LOG_DEBUG : backup::LOG_INFO);
				$progress = new WebCTServiceProgress($logger, $this->flagcron ,$this->currentProgress, $this->step);
				
				
				$controller = new restore_controller($this->folder, $courseId,
						backup::INTERACTIVE_NO, backup::MODE_SAMESITE, $USER->id,
						 backup::TARGET_EXISTING_DELETING,$progress);
				$controller->add_logger($logger);
				
				$controller->execute_precheck();
				$controller->execute_plan();
				$controller->destroy();
				$transaction->allow_commit();
			}else{
				die('il y a un petit souci </br>');
			}
			
		}
		
	}
	
	public function setCourse($idCourse){
		$this->course = ($idCourse != NULL && $this->db->checkIdCourseExist($idCourse)) ? $idCourse : NULL;
	}
	
	public function setNextShortName($nextShortname){
		$this->nextShortname = ($nextShortname != NULL && $this->db->checkCourseExist($nextShortname)) ? $nextShortname : NULL;
	}
	
	public function setUserId($userid){
		$this->user = ($userid != NULL && $this->db->checkUserExist($userid)) ? $userid : NULL;
	}
	
	
}

class WebCTServiceLogger extends base_logger {

	protected function action($message, $level, $options = null) {
		$prefix = $this->get_prefix($level, $options);
		$depth = isset($options['depth']) ? $options['depth'] : 0;
		// Depending of running from browser/command line, format differently
		error_log($prefix . str_repeat('  ', $depth) . $message);
		//      ob_flush();
		// 		flush();
		return true;
	}
}

class WebCTServiceProgress extends core_backup_progress {
	
	/**
	 * @var WebCTServiceLogger
	 */
	protected $logger;

	protected $currentProgress;
	protected $step;
	
	private $progress;
	/**
	 * 
	 * @var ManageDb
	 */
	private $db;
	/**
	 * Permettra de savoir si le backup est immédiat ou s'il est fait à l'aide de cron.
	 * @var int
	 */
	private $flagcron;

	public function __construct($logger,$flagcron ,$currentProgress=0, $step=1) {
		$this->logger=$logger;

		$this->currentProgress=$currentProgress;
		
		$this->step = $step;
		
		$this->db = new ManageDB();
		
		$this->flagcron = $flagcron;
	}
	
	public function getProgress(){
		return $this->progress;
	}
	
	public function update_progress() {
		
		
		if($this->is_in_progress_section()){
			$range = $this->get_progress_proportion_range();
		
			if($this->flagcron == RetrieveCourseConstante::USE_CRON){
				$idCron = $this->db->getIdCronRunning();
				//idCron vaut NULL dans le cas où aucun cours n'est en cours de backup/restore avec cron.
				if($idCron != NULL){
					$this->db->updateTimeModifiedCron( $idCron , time());
				}
			}
			
			
			$this->progress = $this->currentProgress + $range[1]*100*$this->step;
			
			if($this->flagcron == RetrieveCourseConstante::USE_BACKUP_IMMEDIATELLY){
				echo "<script>";
				echo "document.getElementById('pourcentage').innerHTML='".$this->progress."%';";
				echo "document.getElementById('barre').style.width='".$this->progress."%';";
				echo "document.getElementById('progress_bar_description').innerHTML='".$this->get_current_description()."';";
				echo "</script>";
				//ob_flush();
				flush();
			}
			
		}
	}
}
