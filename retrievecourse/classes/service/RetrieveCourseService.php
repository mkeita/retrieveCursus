<?php
defined ( 'MOODLE_INTERNAL' ) || die ();

global $CFG;

// require('../../config.php');
require_once ($CFG->dirroot . '/enrol/locallib.php');
require_once ($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once ($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once ($CFG->dirroot . '/backup/moodle2/backup_plan_builder.class.php');
require_once ($CFG->dirroot . '/backup/util/ui/import_extensions.php');
require_once (__DIR__ . '/../model/ManageDB.php');
require_once (__DIR__ . '/../model/RetrieveCourseConstante.php');

/**
 * RetrieveCursusService va permettre de faire le backup et le restore.
 *
 * @author Ilias
 *        
 */
class RetrieveCourseService {
	/**
	 * Course automated backup completed successfully
	 */
	const BACKUP_STATUS_OK = 1;
	/**
	 * Course automated backup errored
	 */
	const BACKUP_STATUS_ERROR = 0;
	
	/**
	 * Id du cours courant.
	 * 
	 * @var int
	 */
	private $course;
	
	/**
	 * Id de l'user courant.
	 * 
	 * @var int $user.
	 */
	private $user;
	/**
	 * Dossier où a été crée le backup.
	 * 
	 * @var string
	 */
	private $folder;
	
	/**
	 * Shortname du cour où il y'aura le restore.
	 * 
	 * @var string
	 */
	private $nextShortname;
	
	/**
	 * Permettra de savoir si le backup est immédiat ou s'il est fait à l'aide de cron.
	 * 
	 * @var int
	 */
	private $flagcron;
	/**
	 *
	 * @var ManageDB
	 */
	private $db;
	private $pathnamehash;
	private $contenthash;
	private $teachers;
	public $currentProgress = 0;
	public $step = 1;
	
	/**
	 * Constructeur.
	 * 
	 * @param int $course
	 *        	Id du cours courant.
	 * @param int $user
	 *        	Id de l'user courant.
	 * @param string $nextShortname
	 *        	Shortname du cour vers où se fera le restore.
	 */
	function __construct($course, $user, $nextShortname, $flagcron = RetrieveCourseConstante::USE_BACKUP_IMMEDIATELLY) {
		$this->db = new ManageDB ();
		
		$this->setUserId ( $user );
		
		$this->setCourse ( $course );
		
		$this->folder = NULL;
		
		$this->flagcron = $flagcron;
		
		$this->setNextShortName ( $nextShortname );
	}
	
	/**
	 * Permet de lancer le backup du cour courant et le restore vers le cour de l'année suivante.
	 */
	public function runService() {
		global $OUTPUT;
		
		if ($this->course != NULL && $this->nextShortname != NULL && $this->user != NULL) {
			
			$this->backup ();
			$this->restore ();
			$this->send_email ( $this->user, $this->nextShortname );
		} else {
			echo utf8_encode ( "Erreur!!!
					Veuillez vérifier que le cours " . $this->nextShortname . " existe!!!!" );
			echo $OUTPUT->continue_button ( '../..' );
		}
	}
	
	/**
	 * Launches a automated backup routine for the given course
	 *
	 * @param stdClass $course        	
	 * @param int $starttime        	
	 * @param int $userid        	
	 * @return bool
	 */
	private function backup() {
		global $CFG;
		initialize_php_ini ();
		
		if ($this->flagcron == RetrieveCourseConstante::USE_BACKUP_IMMEDIATELLY) {
			
			echo "<script>";
			echo "document.getElementById('progress_bar_course').innerHTML='Backup du cour : " . $this->db->getShortnameCourse ( $this->course ) . "';";
			echo "</script>";
		}
		$outcome = self::BACKUP_STATUS_OK;
		$config = get_config ( 'backup' );
		$dir = $config->backup_auto_destination;
		$storage = ( int ) $config->backup_auto_storage;
		
		$admin = get_admin ();
		
		$courseNextShortname = $this->db->getCourseId ( $this->nextShortname );
		$context_id = $this->db->getContextid ( $courseNextShortname );
		// $context = get_context_instance_by_id($context_id);
		$context = context::instance_by_id ( $context_id );
		$this->teachers = get_role_users ( RetrieveCourseConstante::teacher_roleid, $context );
		
		$bc = new backup_controller ( backup::TYPE_1COURSE, $this->course, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO, backup::MODE_AUTOMATED, $admin->id );
		
		$backup = new import_ui ( $bc );
		// Process the current stage
		$backup->process ();
		
		$logger = new WebCTServiceLogger ( $CFG->debugdeveloper ? backup::LOG_DEBUG : backup::LOG_INFO );
		$progress = new WebCTServiceProgress ( $logger, $this->flagcron, $this->currentProgress, $this->step );
		$progress->start_progress ( '', 1 );
		$backup->get_controller ()->set_progress ( $progress );
		$backup->get_controller ()->add_logger ( $logger );
		
		try {
			$settings = array (
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
			
			foreach ( $settings as $setting => $configsetting ) {
				if ($bc->get_plan ()->setting_exists ( $setting )) {
					if (isset ( $config->{$configsetting} )) {
						$bc->get_plan ()->get_setting ( $setting )->set_value ( $config->{$configsetting} );
					}
				}
			}
			
			// Set the default filename.
			$format = $bc->get_format ();
			$type = $bc->get_type ();
			$id = $bc->get_id ();
			$bc->get_plan ()->get_setting ( 'users' )->set_value ( '0' );
			$users = $bc->get_plan ()->get_setting ( 'users' )->get_value ();
			$anonymised = $bc->get_plan ()->get_setting ( 'anonymize' )->get_value ();
			$bc->get_plan ()->get_setting ( 'filename' )->set_value ( backup_plan_dbops::get_default_backup_filename ( $format, $type, $id, $users, $anonymised ) );
			
			$bc->set_status ( backup::STATUS_AWAITING );
			
			$bc->execute_plan ();
			$results = $bc->get_results ();
			$outcome = $this->outcome_from_results ( $results );
			$file = $results ['backup_destination']; // May be empty if file already moved to target location.
			if (! file_exists ( $dir ) || ! is_dir ( $dir ) || ! is_writable ( $dir )) {
				$dir = null;
			}
			// Copy file only if there was no error.
			if ($file && ! empty ( $dir ) && $storage !== 0 && $outcome != self::BACKUP_STATUS_ERROR) {
				$filename = backup_plan_dbops::get_default_backup_filename ( $format, $type, $this->course, $users, $anonymised, ! $config->backup_shortname );
				if (! $file->copy_content_to ( $dir . '/' . $filename )) {
					$outcome = self::BACKUP_STATUS_ERROR;
				}
				if ($outcome != self::BACKUP_STATUS_ERROR && $storage === 1) {
					$file->delete ();
				}
			}
		} catch ( moodle_exception $e ) {
			$bc->log ( 'backup_auto_failed_on_course', backup::LOG_ERROR, $this->db->getShortnameCourse ( $this->course ) ); // Log error header.
			$bc->log ( 'Exception: ' . $e->errorcode, backup::LOG_ERROR, $e->a, 1 ); // Log original exception problem.
			$bc->log ( 'Debug: ' . $e->debuginfo, backup::LOG_DEBUG, null, 1 ); // Log original debug information.
			$outcome = self::BACKUP_STATUS_ERROR;
		}
		
		// Delete the backup file immediately if something went wrong.
		if ($outcome === self::BACKUP_STATUS_ERROR) {
			
			// Delete the file from file area if exists.
			if (! empty ( $file )) {
				$file->delete ();
			}
			
			// Delete file from external storage if exists.
			if ($storage !== 0 && ! empty ( $filename ) && file_exists ( $dir . '/' . $filename )) {
				@unlink ( $dir . '/' . $filename );
			}
		}
		
		$this->folder = $bc->get_backupid ();
		$this->currentProgress = $progress->getProgress ();
		
		$stored_file = $bc->get_plan ()->get_results ();
		
		$this->contenthash = $stored_file ['backup_destination']->get_contenthash ();
		$this->pathnamehash = $stored_file ['backup_destination']->get_pathnamehash ();
		
		$bc->destroy ();
		unset ( $bc );
		
		return $outcome;
	}
	
	/**
	 * Returns the backup outcome by analysing its results.
	 *
	 * @param array $results
	 *        	returned by a backup
	 * @return int {@link self::BACKUP_STATUS_OK} and other constants
	 */
	private function outcome_from_results($results) {
		$outcome = self::BACKUP_STATUS_OK;
		foreach ( $results as $code => $value ) {
			// Each possible error and warning code has to be specified in this switch
			// which basically analyses the results to return the correct backup status.
			switch ($code) {
				case 'missing_files_in_pool' :
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
	private function send_email($userid, $shortname) {
		global $DB;
		
		$message = 'Bonjour, </br> </br>';
		$message .= $shortname . ' disponible';
		
		$userto = $DB->get_record ( 'user', array (
				"id" => $userid 
		) );
		
		$admin = get_admin ();
		$admin->priority = 1;
		
		// Send the message
		$eventdata = new stdClass ();
		$eventdata->modulename = 'moodle';
		$eventdata->userfrom = $admin;
		$eventdata->userto = $userto;
		$eventdata->subject = utf8_encode ( 'Récupération des informations dans le cours ' . $shortname );
		$eventdata->fullmessage = $message;
		$eventdata->fullmessageformat = FORMAT_PLAIN;
		$eventdata->fullmessagehtml = '';
		$eventdata->smallmessage = '';
		$eventdata->component = 'moodle';
		$eventdata->name = 'backup';
		$eventdata->notification = 1;
		message_send ( $eventdata );
	}
	private function restore() {
		global $DB, $CFG, $USER;
		initialize_php_ini ();
		
		if ($this->folder != NULL) {
			if ($this->flagcron == RetrieveCourseConstante::USE_BACKUP_IMMEDIATELLY) {
				echo "<script>";
				echo "document.getElementById('progress_bar_course').innerHTML='Restauration vers le cour : " . $this->nextShortname . "';";
				echo "</script>";
			}
			
			$courseId = $this->db->retieveCourseId ( $this->nextShortname );
			if ($courseId != NULL) {
				$transaction = $DB->start_delegated_transaction ();
				$options = array ();
				// TODO Probleme des étudiant recupéré
				$options ['keep_roles_and_enrolments'] = 0;
				$options ['keep_groups_and_groupings'] = 0;
				restore_dbops::delete_course_content ( $courseId, $options );
				$transaction->allow_commit ();
				$transaction = $DB->start_delegated_transaction ();
				
				$logger = new WebCTServiceLogger ( $CFG->debugdeveloper ? backup::LOG_DEBUG : backup::LOG_INFO );
				$progress = new WebCTServiceProgress ( $logger, $this->flagcron, $this->currentProgress, $this->step );
				
				// Récupérer de moodle fichier "restore.php" et "restorefile.php"
				$contextid = $this->db->getContextid ( $this->course );
				// Dezippage
				$fs = get_file_storage ();
				$storedfile = $fs->get_file_by_hash ( $this->pathnamehash );
				if (! $storedfile || $storedfile->get_contenthash () !== $this->contenthash) {
					throw new restore_ui_exception ( 'invalidrestorefile' );
				}
				$outcome = $this->extract_file_to_dir ( $storedfile, $contextid );
				// Récupére filepath
				$controller = new restore_controller ( $this->filepath, $courseId, backup::INTERACTIVE_NO, backup::MODE_GENERAL, get_admin ()->id, backup::TARGET_EXISTING_DELETING, $progress );
				
				$controller->add_logger ( $logger );
				
				$controller->execute_precheck ();
				$controller->execute_plan ();
				$controller->destroy ();
				
				$this->enrol_teachers ();
				
				$transaction->allow_commit ();
			} else {
				die ( utf8_encode ( "Le cours " . $this->nextShortname . " n'a pas été trouvé dans la base de donnée!!" ) );
			}
		}
	}
	private function enrol_teachers() {
		global $PAGE, $DB;
		$id_course = $this->db->getCourseId ( $this->nextShortname );
		$course = get_course ( $id_course );
		$manager = new course_enrolment_manager ( $PAGE, $course );
		$roleid = RetrieveCourseConstante::teacher_roleid;
		foreach ( $this->teachers as $teacher ) {
			$enrolid = $DB->get_record ( 'enrol', array (
					'enrol' => "manual",
					"courseid" => $id_course 
			), 'id', MUST_EXIST );
			$user = $DB->get_record ( 'user', array (
					'id' => $teacher->id 
			), '*', MUST_EXIST );
			$instances = $manager->get_enrolment_instances ();
			$plugins = $manager->get_enrolment_plugins ( true ); // Do not allow actions on disabled plugins.
			if (! array_key_exists ( $enrolid->id, $instances )) {
				throw new enrol_ajax_exception ( 'invalidenrolinstance' );
			}
			$instance = $instances [$enrolid->id];
			if (! isset ( $plugins [$instance->enrol] )) {
				throw new enrol_ajax_exception ( 'enrolnotpermitted' );
			}
			$plugin = $plugins [$instance->enrol];
			$plugin->enrol_user ( $instance, $user->id, $roleid );
		}
	}
	
	/**
	 * Extracts the file.
	 *
	 * @param string|stored_file $source
	 *        	Archive file to extract
	 */
	private function extract_file_to_dir($source, $contextid) {
		global $CFG, $USER;
		
		$this->filepath = restore_controller::get_tempdir_name ( $contextid, $USER->id );
		
		$fb = get_file_packer ( 'application/vnd.moodle.backup' );
		$result = $fb->extract_to_pathname ( $source, $CFG->tempdir . '/backup/' . $this->filepath . '/' );
		return $result;
	}
	public function setCourse($idCourse) {
		$this->course = ($idCourse != NULL && $this->db->checkIdCourseExist ( $idCourse )) ? $idCourse : NULL;
	}
	public function setNextShortName($nextShortname) {
		$this->nextShortname = ($nextShortname != NULL && $this->db->checkCourseExist ( $nextShortname )) ? $nextShortname : NULL;
	}
	public function setUserId($userid) {
		$this->user = ($userid != NULL && $this->db->checkUserExist ( $userid )) ? $userid : NULL;
	}
}
class WebCTServiceLogger extends base_logger {
	protected function action($message, $level, $options = null) {
		$prefix = $this->get_prefix ( $level, $options );
		$depth = isset ( $options ['depth'] ) ? $options ['depth'] : 0;
		// Depending of running from browser/command line, format differently
		error_log ( $prefix . str_repeat ( '  ', $depth ) . $message );
		
		return true;
	}
}
class WebCTServiceProgress extends core_backup_progress {
	
	/**
	 *
	 * @var WebCTServiceLogger
	 */
	protected $logger;
	protected $currentProgress;
	protected $step;
	private $progress;
	
	/**
	 *
	 * @var int The number of seconds that can pass without progress() calls.
	 */
	const TIME_LIMIT_WITHOUT_PROGRESS = 0;
	private $crondb;
	/**
	 * Permettra de savoir si le backup est immédiat ou s'il est fait à l'aide de cron.
	 * 
	 * @var int
	 */
	private $flagcron;
	public function __construct($logger, $flagcron, $currentProgress = 0, $step = 1) {
		$this->logger = $logger;
		
		$this->currentProgress = $currentProgress;
		
		$this->step = $step;
		
		$this->crondb = new ManageRetrieveCourseCronDB ();
		
		$this->flagcron = $flagcron;
	}
	public function getProgress() {
		return $this->progress;
	}
	public function update_progress() {
		if ($this->is_in_progress_section ()) {
			$range = $this->get_progress_proportion_range ();
			
			if ($this->flagcron == RetrieveCourseConstante::USE_CRON) {
				$idCron = $this->crondb->getIdCronRunning ();
				// idCron vaut NULL dans le cas où aucun cours n'est en cours de backup/restore avec cron.
				if ($idCron != NULL) {
					$this->crondb->updateTimeModifiedCron ( $idCron, time () );
				}
			}
			
			$this->progress = $this->currentProgress + $range [1] * 100 * $this->step;
			
			error_log ( "Backup/restore -> pourcentage: " . $this->progress . " description : " . $this->get_current_description () );
			
			if ($this->flagcron == RetrieveCourseConstante::USE_BACKUP_IMMEDIATELLY) {
				echo "<script>";
				echo "document.getElementById('pourcentage').innerHTML='" . $this->progress . "%';";
				echo "document.getElementById('barre').style.width='" . $this->progress . "%';";
				echo "document.getElementById('progress_bar_description').innerHTML='" . $this->get_current_description () . "';";
				echo "</script>";
				// ob_flush();
				flush ();
			}
		}
	}
	
	/**
	 * Indicates that progress has occurred.
	 *
	 * The progress value should indicate the total progress so far, from 0
	 * to the value supplied for $max (inclusive) in start_progress.
	 *
	 * You do not need to call this function for every value. It is OK to skip
	 * values. It is also OK to call this function as often as desired; it
	 * doesn't do anything if called more than once per second.
	 *
	 * It must be INDETERMINATE if start_progress was called with $max set to
	 * INDETERMINATE. Otherwise it must not be indeterminate.
	 *
	 * @param int $progress
	 *        	Progress so far
	 * @throws coding_exception If progress value is invalid
	 */
	public function progress($progress = self::INDETERMINATE) {
		// Ignore too-frequent progress calls (more than once per second).
		$now = $this->get_time ();
		if ($now === $this->lastprogresstime) {
			return;
		}
		
		// Check we are inside a progress section.
		$max = end ( $this->maxes );
		if ($max === false) {
			throw new coding_exception ( 'progress() without start_progress' );
		}
		
		// Check and apply new progress.
		if ($progress === self::INDETERMINATE) {
			// Indeterminate progress.
			if ($max !== self::INDETERMINATE) {
				throw new coding_exception ( 'progress() INDETERMINATE, expecting value' );
			}
		} else {
			// Determinate progress.
			$current = end ( $this->currents );
			if ($max === self::INDETERMINATE) {
				throw new coding_exception ( 'progress() with value, expecting INDETERMINATE' );
			} else if ($progress < 0 || $progress > $max) {
				throw new coding_exception ( 'progress() value out of range' );
			} else if ($progress < $current) {
				throw new coding_Exception ( 'progress() value may not go backwards' );
			}
			$this->currents [key ( $this->currents )] = $progress;
		}
		
		// Update progress.
		$this->count ++;
		$this->lastprogresstime = $now;
		set_time_limit ( self::TIME_LIMIT_WITHOUT_PROGRESS );
		$this->update_progress ();
	}
}
