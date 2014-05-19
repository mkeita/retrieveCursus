<?php

require_once (__DIR__ . '/ManageDB.php');
require_once (__DIR__ . '/../../outils.php');

class ManageRetrieveCourseDB extends ManageDB {
	
	public function getRetrieveCourse(){
		global $DB;
		$result = $DB->get_records('retrievecourse');
		return $result;
	}
	
	public function retrieveNameColumn(){
		global $DB;
		return array_keys($DB->get_columns("retrievecourse"));
	}
	
	/**
	 * Vérifier si le plugin a déjà utilisé pour un cour donné.
	 * Si l'id du cour existe dans la table 'retrieve_course' sous le champs 'courseid_old' alors il a déjà utilisé le plugin.
	 * @param int $id
	 * @return vrai si le plugin a déjà été utilisé.
	 */
	public function checkPluginUsed($id){
		global $DB;
		$used = $DB->record_exists('retrievecourse', array("courseid_old"=>$id));
		return $used;
	}
	
	/**
	 * Permet de rajouter dans la table 'retrievecourse' tout les cours qui ont déjà utilisé le plugin.
	 * @param string $shortname
	 * @param string $temp
	 * La fin du shortname.
	 */
	public function addCourse_retrievecourse($shortname_old , $shortname_new , $temp , $courseid_old , $flag_newcourse=0 , $flag_wait_cron=0 , $flag_use_cron=0){
		global $DB,$USER;
		$idCourse = $this->getCourseId($shortname_new);
		if($idCourse != null ){
			$this->deleteOldRetrieve();
			$dataobject = array('courseid_old'=>$courseid_old ,'courseid_new'=>$idCourse,'shortname_course_old'=>$shortname_old,
					'shortname_course_new'=>$shortname_new,'user'=>$USER->id, 'annac'=>$temp ,'date'=>time(),
					'flag_newcourse'=> $flag_newcourse , 'flag_use_cron'=>$flag_use_cron , 'flag_wait_cron_execute'=>$flag_wait_cron);
			$DB->insert_record('retrievecourse', $dataobject);
		}
	}
	
	/**
	 * Permet de récupérer 
	 * @param unknown $courseid_old
	 * @return NULL
	 */
	public function getRetrievecourseId($courseid_old){
		global $DB;
		$data = $DB->get_record('retrievecourse', array("courseid_old"=>$courseid_old), 'id');
		return (($data != NULL) ? $data->id : NULL);
	}
	
	/**
	 * Permet de modifier le flag 
	 * @param int $id
	 * @param unknown $flag
	 * @return boolean
	 */
	public function updateFlagWaitCronExecute($id , $flag){
		global $DB;
		$dataobject = array('id'=>$id , "flag_wait_cron_execute"=>$flag);
		return $DB->update_record('retrievecourse', $dataobject);
	}
	
	public function updateFlagUseCron($id , $flag){
		global $DB;
		$dataobject = array('id'=>$id , "flag_use_cron"=>$flag);
		return $DB->update_record('retrievecourse', $dataobject);
	}
	
	
	
	/**
	 * Permet de récupérer tous les cours qui n'ont pas utilisés le plugin et qui appartienne à l'année académique courante.
	 * @param int $idCagtegorie id de la categorie .
	 * Quand idCatgeorie est différent de null , seul les cours appartenant à cette categorie seront récupérer.
	 * @return Tableau associatif dont la clé est l'id du cours et la valeur le shortname du cours.
	 */
	public function courseNotUsedPugin($idCagtegorie=null){
		global $DB,$CFG;
		$manage = new ManageDB();
		$listeCours = array('-1'=>'All			');
		$cond = ($idCagtegorie == null) ? '' : ' and category =' . $idCagtegorie;
		$result = $DB->get_records_sql('SELECT mdl_course.id,mdl_course.shortname FROM mdl_course
				WHERE mdl_course.id NOT IN (SELECT mdl_retrievecourse.courseid_old FROM mdl_retrievecourse)'
				. $cond);
		$tempSize = $CFG->tempYearOne + $CFG->tempYearTwo;
	
		foreach ($result as $value){
			$temp = substr($value->shortname, -$tempSize);
			$nextshortname = nextShortname($value->shortname);
			if($temp == $CFG->temp && $manage->checkCourseExist($nextshortname)){
				
				$listeCours[$value->id] = $value->shortname;
			}
		}
		return $listeCours;
	}
	

	/**
	 * Permet de rechercher tous les cours qui contiennent le mot et qui n'ont pas utilisés le plugin.
	 * @param string $mot
	 */
	public function searchCourseNotUsedPlugin($search){
		global $DB,$CFG;
		$manage = new ManageDB();
		$listeCours = array('-1'=>'All			');
		$param = array('search'=> "%$search%");
		$result = $DB->get_records_sql('SELECT mdl_course.id,mdl_course.shortname FROM mdl_course
				WHERE mdl_course.id NOT IN (SELECT mdl_retrievecourse.courseid_old FROM mdl_retrievecourse)
				   and mdl_course.shortname LIKE :search ' , $param);
		foreach ($result as $value){
			$tempSize = $CFG->tempYearOne + $CFG->tempYearTwo;
			$temp = substr($value->shortname, -$tempSize);
			
			$nextshortname = nextShortname($value->shortname);
			
			if($temp == $CFG->temp && $manage->checkCourseExist($nextshortname) ){
				$listeCours[$value->id] = $value->shortname;
			}
		}
		return $listeCours;
	}
	
	/**
	 * Supprime l'ancien tuple lié à ce cours dans la table 'retrievecourse'.
	 */
	private function deleteOldRetrieve(){
		global $DB,$PAGE;
		$used = $DB->record_exists('retrievecourse',  array("courseid_new"=>$_SESSION['idCourse']));
		if($used){
			$DB->delete_records('retrievecourse', array("courseid_new"=>$_SESSION['idCourse']));
		}
	}
	

}