<?php

defined('MOODLE_INTERNAL') || die;


require_once ($CFG->libdir.'/messagelib.php');
require_once ($CFG->libdir.'/datalib.php');
require_once (__DIR__ . '/../model/ManageRetrieveCourseCronDB.php');
require_once (__DIR__ . '/RetrieveCourseService.php');
require_once (__DIR__ . '/../model/RetrieveCourseConstante.php');

class cronService {

	/**
	 * 
	 * @var ManageRetrieveCourseCronDB
	 */
	private $crondb;
	/**
	 * 
	 * @var RetrieveCourseService
	 */
	private $service;
	
	function __construct(){
		$this->crondb = new ManageRetrieveCourseCronDB();
		$this->service = new RetrieveCourseService(null, null, null , RetrieveCourseConstante::USE_CRON);
	}
	
	public function launchBackupRestore(){
		$listeCron = $this->crondb->retrieveCron();
		foreach($listeCron as $cron){
			initialize_php_ini();
			if(!$this->verifierPlageHoraire()){
				break;
			}
			if($this->crondb->getFlagStatus($cron->id) != RetrieveCourseConstante::STATUS_ERROR){
				$this->initialiserService($cron->courseid, $cron->user , $cron->shortname_course_new );
				$this->crondb->updateFlagStatus($cron->id , RetrieveCourseConstante::STATUS_EXECUTE);
				$this->crondb->updateTimeStart($cron->id, time());
				
				$this->service->runService();
				$this->crondb->deleteCron($cron->id);
				$this->crondb->cronFinish($cron->id, $cron->courseid);
			//	$this->send_email($cron->user , $cron->shortname_course_new  );
			}
		} 
	}
	
// 	private function send_email($userid , $shortname){
// 		global $DB;
	
// 		$message = 'Bonjour, </br> </br>';
// 		$message .= $shortname . ' disponible';
	
// 		$userto = $DB->get_record('user', array("id"=>$userid));
	
// 		$admin = get_admin();
// 		$admin->priority = 1;
	
// 		//Send the message
// 		$eventdata = new stdClass();
// 		$eventdata->modulename        = 'moodle';
// 		$eventdata->userfrom          = $admin;
// 		$eventdata->userto            = $userto;
// 		$eventdata->subject           = utf8_encode('Récupération des informations dans le cours ' . $shortname);
// 		$eventdata->fullmessage       = $message;
// 		$eventdata->fullmessageformat = FORMAT_PLAIN;
// 		$eventdata->fullmessagehtml   = '';
// 		$eventdata->smallmessage      = '';
// 		$eventdata->component         = 'moodle';
// 		$eventdata->name         = 'backup';
// 		$eventdata->notification = 1;
// 		message_send($eventdata);
	
// 	}
	
	private function initialiserService( $idCourse , $userid , $nextShortname){
		$this->service->setCourse($idCourse);
		$this->service->setUserId($userid);
		$this->service->setNextShortName($nextShortname);
	}
	
	public function checkLaunchBackupRestore(){
		return !$this->isRunning()	&& $this->verifierPlageHoraire();
	}
	
	
	/**
	 * Permet de verifier qu' on est dans la plage horaire d'exécution des backup/restore.
	 */
	private function verifierPlageHoraire(){
		global $CFG;
		$timeDeb = mktime($CFG->cron_heure_debut, $CFG->cron_minute_debut);
		$timeFin = mktime($CFG->cron_heure_fin,$CFG->cron_minute_fin,date("s"));
		//Si le timeFin est plus petit que le timeDeb , c'est que le timeFin est situé pour le lendemain.
		if($timeDeb > $timeFin){
			$timeFin = mktime($CFG->cron_heure_fin,$CFG->cron_minute_fin,date("s"), date("n"), date("j")+1);
		}
		$timecourant = time();
		
		return ($timecourant >= $timeDeb && $timecourant <= $timeFin);
	}
	
	/**
	 * Permet de savoir si cron est déjà occuper à faire un backup.
	 * Permet également de mettre fin à un backup/restore s'il n' y a plus d'activité aprés un certain temps.
	 * @return vrai si un backup/restore est déjà en cours.
	 */
	private function isRunning(){
		global $CFG;
		$is_running = false;
		$idCron = $this->crondb->getIdCronRunning();
		//Si getIdCronRunning() retourne NULL c'est qu'il n'y aucun processus cron qui tourne.
		if($idCron != NULL){
			$timelose = 60 *90;
			$lastTime_modified = $this->crondb->getTimeModifiedCron($idCron);
			//On considére qu'aprés 1h30 d'inactivité , le processus est mort.
			if($lastTime_modified + $timelose <= time()){
				$this->crondb->updateNbTentative($idCron, $this->crondb->getCronTentative($idCron) +1 );
				if($this->crondb->getCronTentative($idCron)  == $CFG->nbTentativeMax ){
					$this->crondb->updateFlagStatus($idCron,RetrieveCourseConstante::STATUS_ERROR );
				}else{
					$this->crondb->updateFlagStatus($idCron,RetrieveCourseConstante::STATUS_WAITING );
				}
			}else{
				$is_running = true;
			}
		}
		return $is_running;
	}
	
}