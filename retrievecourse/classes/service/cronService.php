<?php

defined('MOODLE_INTERNAL') || die;

require_once '/../model/ManageDB.php';
require_once 'RetrieveCourseService.php';
require_once '/../model/RetrieveCourseConstante.php';


class cronService {
	
	
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
	function __construct(){
		$this->db = new ManageDB();
		$this->service = new RetrieveCourseService(null, null, null , RetrieveCourseConstante::USE_CRON);
	}
	
	public function launchBackupRestore(){
		$listeCron = $this->db->retrieveCron();
		var_dump($listeCron);
		foreach($listeCron as $cron){
			if(!$this->verifierPlageHoraire()){
				break;
			}
			if($this->db->getFlagStatus($cron->id) != RetrieveCourseConstante::STATUS_ERROR){
				$this->initialiserService($cron->courseid, $cron->user , $cron->shortname_course_new );
				$this->db->updateFlagStatus($cron->id , RetrieveCourseConstante::CRON_EXECUTE);
				$this->db->updateTimeStart($cron->id, time());
				$this->service->runService();
				$this->db->deleteCron($cron->id);
				$this->db->cronFinish($cron->id, $cron->courseid);
			}
		} 
	}
	
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
		$idCron = $this->db->getIdCronRunning();
		//Si getIdCronRunning() retourne NULL c'est qu'il n'y aucun processus cron qui tourne.
		if($idCron != NULL){
			$timelose = 60 *90;
			$lastTime_modified = $this->db->getTimeModifiedCron($id);
			//On considére qu'aprés 1h30 d'inactivité , le processus est mort.
			if($lastTime_modified + $timelose <= time()){
				$this->db->updateNbTentative($idCron, $this->db->getCronTentative($idCron) +1 );
				if($this->db->getCronTentative($idCron)  == $CFG->nbTentativeMax ){
					$this->db->updateFlagStatus($idCron,RetrieveCourseConstante::STATUS_ERROR );
				}else{
					$this->db->updateFlagStatus($idCron,RetrieveCourseConstante::STATUS_WAITING );
				}
			}else{
				$is_running = true;
			}
		}
		return $is_running;
	}
	
}