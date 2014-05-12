

<?php

require_once (__DIR__ . '/RetrieveCourseConstante.php');
require_once (__DIR__ . '/ManageDB.php');
require_once (__DIR__ . '/ManageRetrieveCourseDB.php');

class ManageRetrieveCourseCronDB extends ManageDB{
	
	//Retrieve
	/**
	 * Permet de récupérer tous les tuples de la table "mdl_retrievecourse_cron"
	 * @return multitype:
	 */
	public function retrievecourse_cron(){
		global $DB;
		$result = $DB->get_records('retrievecourse_cron');
		return $result;
	}
	
	
	/**
	 * Permet de récupérer tous les id , courseid , shortname et userid de la table retrievecourse_cron;
	 * @return multitype: tableau associatif.
	 */
	public function retrieveCron(){
		global $DB;
		$result = $DB->get_records_sql('SELECT id,courseid,shortname_course_new,user FROM mdl_retrievecourse_cron');
		return $result;
	}
	
	/**
	 * Permet de récupérer le nom des colonnes de la table 'retrievecourse_cron'
	 * @return multitype:unknown
	 */
	public function retrieveNameColumn(){
		global $DB;
		return array_keys($DB->get_columns("retrievecourse_cron"));
	}
	
	public function cronFinish($idCron , $courseid_old){
		$retrieveCourseDb = new ManageRetrieveCourseDB();
		$id = $retrieveCourseDb->getRetrievecourseId($courseid_old);
		if($id != NULL){
			$retrieveCourseDb->updateFlagWaitCronExecute($id, false);
			$retrieveCourseDb->updateFlagUseCron($id, true);
		}
	}
	
	//ADD
	/**
	 * Permet de rajouter dans la table 'retrievecourse_cron'.
	 * @param int $idCourse
	 * @param int $userid
	 * @param string $nextShortname
	 */
	public function addCourse_cron($idCourse, $userid , $nextShortname){
		global $DB;
		$dataobject = array('courseid'=>$idCourse , 'user'=>$userid , 'shortname_course_new'=>$nextShortname , 'status'=>RetrieveCourseConstante::STATUS_WAITING,
				'time_created'=> time() , 'tentative' => 0);
		$DB->insert_record('retrievecourse_cron', $dataobject);
	}
	
	//UPDATE
	public function updateNbTentative($id , $tentative){
		global $DB;
		$dataobject = array('id'=>$id , "tentative"=>$tentative);
		return $DB->update_record('retrievecourse_cron', $dataobject);
	}
	
	
	/**
	 * Permet de modifier le champs timeModified.
	 * @param unknown $id L'id du tuple qu'il faut modifier.
	 * @param unknown $timeModified La nouvelle valeur de timeModified.
	 * @return boolean vrai si la modification a été effectué.
	 */
	public function updateTimeModifiedCron($id , $timeModified){
		global $DB;
		$dataobject = array('id'=>$id , "time_modified"=>$timeModified);
		return $DB->update_record('retrievecourse_cron', $dataobject);
	}
	
	public function updateTimeStart($id , $time){
		global $DB;
		$dataobject = array('id'=>$id , "time_start"=>$time);
		return $DB->update_record('retrievecourse_cron', $dataobject);
	}
	
	/**
	 * Permet de modifier le champs "status" de la table "retrievecourse_cron".
	 * @param int $id
	 * @param unknown $flag_execute
	 * Doit être un RetrieveCourseConstante::STAUS_*
	 * @return boolean
	 */
	public function updateFlagStatus($id , $flag_execute){
		global $DB;
		$dataobject = array('id'=>$id , "status"=>$flag_execute);
		return $DB->update_record('retrievecourse_cron', $dataobject);
	}
	
	//GET
	public function getCronTentative($id){
		global $DB;
		$data = $DB->get_record('retrievecourse_cron',  array("id"=>$id), 'tentative');
		return (($data != NULL) ? $data->tentative : NULL);
	}
	

	/**
	 * Récupére le champs 'time_modified' de la table 'retrievecourse_cron' .
	 * @param $id
	 * @return le time_modified ou null dans le cas où l'id entré n'existe pas.
	 */
	public function getTimeModifiedCron($id){
		global $DB;
		$param = array('id'=> $id);
		$result = $DB->get_records_sql('SELECT id,time_modified FROM mdl_retrievecourse_cron WHERE id = :id',$param);
		var_dump($result);
		($result == NULL) ? NULL : $result[$id]->time_modified ;
	}
	
	/**
	 * Permet de récupérer l'id de la table cron qui est en cour de backup/restore.
	 * @return NULL dans le cas où aucun cours n'est en cours de backup/restore avec cron.
	 */
	public function getIdCronRunning(){
		global $DB;
		$obj = $DB->get_records_sql('SELECT id from mdl_retrievecourse_cron WHERE mdl_retrievecourse_cron.status = "execute"' );
		$id;
		foreach($obj as $val){
			$id = $val->id;
		}
		return ($obj == NULL) ? NULL : $id;
	}
	
	public function getFlagStatus($id){
		global $DB;
		$data = $DB->get_record('retrievecourse_cron',  array("id"=>$id), 'status');
		return (($data != NULL) ? $data->status : NULL);
	}
	
	//DELETE
	public function deleteCron($id){
		global $DB;
		return $DB->delete_records('retrievecourse_cron', array("id"=>$id));
	}
	
}