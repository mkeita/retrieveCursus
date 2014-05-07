<?php
defined('MOODLE_INTERNAL') || die();

global $CFG;


//require('../../config.php');
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot . '/backup/moodle2/backup_plan_builder.class.php');
require_once($CFG->dirroot . '/backup/util/ui/import_extensions.php');
require_once (__DIR__ . '/../model/ManageDB.php');
require_once (__DIR__ . '/../model/RetrieveCourseConstante.php');


/**
 * RetrieveCursusService va permettre de faire le backup et le restore. 
 * @author Ilias
 *
 */
class RetrieveCourseService {
	/** Automated backups are active and ready to run */
	const STATE_OK = 0;
	/** Automated backups are disabled and will not be run */
	const STATE_DISABLED = 1;
	/** Automated backups are all ready running! */
	const STATE_RUNNING = 2;
	
	/** Course automated backup completed successfully */
	const BACKUP_STATUS_OK = 1;
	/** Course automated backup errored */
	const BACKUP_STATUS_ERROR = 0;
	/** Course automated backup never finished */
	const BACKUP_STATUS_UNFINISHED = 2;
	/** Course automated backup was skipped */
	const BACKUP_STATUS_SKIPPED = 3;
	/** Course automated backup had warnings */
	const BACKUP_STATUS_WARNING = 4;
	/** Course automated backup has yet to be run */
	const BACKUP_STATUS_NOTYETRUN = 5;
	
	/** Run if required by the schedule set in config. Default. **/
	const RUN_ON_SCHEDULE = 0;
	/** Run immediately. **/
	const RUN_IMMEDIATELY = 1;
	
	const AUTO_BACKUP_DISABLED = 0;
	const AUTO_BACKUP_ENABLED = 1;
	const AUTO_BACKUP_MANUAL = 2;
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
		global $OUTPUT;
		if($this->course != NULL && $this->nextShortname != NULL && $this->user != NULL){
			//$this->backup();
			$this->launch_automated_backup($this->course, time(), $this->user);
			$this->restore();
		}else{
			echo utf8_encode("Erreur!!!
					Veuillez vérifier que le cours " . $this->nextShortname . " existe!!!!");
			echo $OUTPUT->continue_button('../..');
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
				backup::INTERACTIVE_NO, backup::MODE_GENERAL, $this->user);
		
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
		$bc_results = $bc->get_results();
		
		$tmpdir = $CFG->tempdir . '/backup/';
		//var_dump($tmpdir); echo '</br>';
		$this->folder = $bc->get_backupid();
		
		echo '</br>'; var_dump($tmpdir . $this->folder);echo '</br>';
		
// 		$Bkpfile = $bc_results['backup_destination'];
		
// 		var_dump($Bkpfile);
		
		$this->currentProgress = $progress->getProgress();	
	}
	
	  /**
     * Launches a automated backup routine for the given course
     *
     * @param stdClass $course
     * @param int $starttime
     * @param int $userid
     * @return bool
     */
    private  function launch_automated_backup($course, $starttime, $userid) {
    	mtrace('launch_automated_backup');
        $outcome = self::BACKUP_STATUS_OK;
        $config = get_config('backup');
        $dir = $config->backup_auto_destination;
        $storage = (int)$config->backup_auto_storage;


        $bc = new backup_controller(backup::TYPE_1COURSE, $course, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO,
                backup::MODE_AUTOMATED, $userid);

        try {

            $settings = array(
                'users' => 'backup_auto_users',
                'role_assignments' => 'backup_auto_role_assignments',
                'activities' => 'backup_auto_activities',
                'blocks' => 'backup_auto_blocks',
                'filters' => 'backup_auto_filters',
                'comments' => 'backup_auto_comments',
                'badges' => 'backup_auto_badges',
                'completion_information' => 'backup_auto_userscompletion',
                'logs' => 'backup_auto_logs',
                'histories' => 'backup_auto_histories',
                'questionbank' => 'backup_auto_questionbank'
            );
            foreach ($settings as $setting => $configsetting) {
                if ($bc->get_plan()->setting_exists($setting)) {
                    if (isset($config->{$configsetting})) {
                        $bc->get_plan()->get_setting($setting)->set_value($config->{$configsetting});
                    }
                }
            }

            // Set the default filename.
            $format = $bc->get_format();
            $type = $bc->get_type();
            $id = $bc->get_id();
            $users = $bc->get_plan()->get_setting('users')->get_value();
            $anonymised = $bc->get_plan()->get_setting('anonymize')->get_value();
            $bc->get_plan()->get_setting('filename')->set_value(backup_plan_dbops::get_default_backup_filename($format, $type,
                    $id, $users, $anonymised));

            $bc->set_status(backup::STATUS_AWAITING);

            $bc->execute_plan();
            $results = $bc->get_results();
            $outcome = $this->outcome_from_results($results);
            $file = $results['backup_destination']; // May be empty if file already moved to target location.
            if (!file_exists($dir) || !is_dir($dir) || !is_writable($dir)) {
                $dir = null;
            }
            // Copy file only if there was no error.
            if ($file && !empty($dir) && $storage !== 0 && $outcome != self::BACKUP_STATUS_ERROR) {
                $filename = backup_plan_dbops::get_default_backup_filename($format, $type, $course, $users, $anonymised,
                        !$config->backup_shortname);
                if (!$file->copy_content_to($dir.'/'.$filename)) {
                    $outcome = self::BACKUP_STATUS_ERROR;
                }
                if ($outcome != self::BACKUP_STATUS_ERROR && $storage === 1) {
                    $file->delete();
                }
            }

        } catch (moodle_exception $e) {
            $bc->log('backup_auto_failed_on_course', backup::LOG_ERROR, $this->db->getShortnameCourse($course)); // Log error header.
            $bc->log('Exception: ' . $e->errorcode, backup::LOG_ERROR, $e->a, 1); // Log original exception problem.
            $bc->log('Debug: ' . $e->debuginfo, backup::LOG_DEBUG, null, 1); // Log original debug information.
            $outcome = self::BACKUP_STATUS_ERROR;
        }

        // Delete the backup file immediately if something went wrong.
        if ($outcome === self::BACKUP_STATUS_ERROR) {

            // Delete the file from file area if exists.
            if (!empty($file)) {
                $file->delete();
            }

            // Delete file from external storage if exists.
            if ($storage !== 0 && !empty($filename) && file_exists($dir.'/'.$filename)) {
                @unlink($dir.'/'.$filename);
            }
        }
		
        $this->folder = $bc->get_backupid();
        
        $bc->destroy();
        unset($bc);

        return $outcome;
    }
	
    /**
     * Returns the backup outcome by analysing its results.
     *
     * @param array $results returned by a backup
     * @return int {@link self::BACKUP_STATUS_OK} and other constants
     */
    private function outcome_from_results($results) {
    	$outcome = self::BACKUP_STATUS_OK;
    	foreach ($results as $code => $value) {
    		// Each possible error and warning code has to be specified in this switch
    		// which basically analyses the results to return the correct backup status.
    		switch ($code) {
    			case 'missing_files_in_pool':
    				$outcome = self::BACKUP_STATUS_WARNING;
    				break;
    		}
    		// If we found the highest error level, we exit the loop.
    		if ($outcome == self::BACKUP_STATUS_ERROR) {
    			break;
    		}
    	}
    	return $outcome;
    }
    
    
	private function restore(){
		global $DB,$CFG,$USER;
		mtrace('restore');
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
				die(utf8_encode("Le cours " . $this->nextShortname . " n'a pas été trouvé dans la base de donnée!!"));
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
		echo '</br>'; var_dump($message); echo '</br>';
				flush();
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
