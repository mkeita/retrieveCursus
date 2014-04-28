<?php
/**
 * Toute les requêtes sql se font à se niveau.
 * @author Ilias
 */
class ManageDB {
	
	public function retrievecourse_cron(){
		global $DB;
		$result = $DB->get_records('retrievecourse_cron');
		return $result;
	}
	
	public function retrieveNameColumn(){
		global $DB;
		$result = $this->retrievecourse_cron();
		$columnName = array();
		foreach ($result as $object){
			foreach ($object as $key=>$value){
				$columnName[] = $key;
			}
			break;
		}
		return $columnName;
	}
	
	/**
	 * Cette méthode permet de vérifier qu'un cours existe à partir de son shortname.
	 * @param string $course Le shortname du cours dont on veut vérifier l'existence.
	 * @return vrai si le cour existe.
	 */
	public function checkCourseExist($course){
		global $DB;
		//"mdl_" est rajouté automatiquement à "course" -> table = mdl_course
		return $DB->record_exists('course', array("shortname"=>$course));
	}
	

	/**
	 * Cette méthode permet de vérifier qu'un cours existe à partir de son id.
	 * @param string $course Id du cours dont on veut vérifier l'existence.
	 * @return vrai si le cour existe.
	 */
	public function checkIdCourseExist($course){
		global $DB;
		//"mdl_" est rajouté automatiquement à "course" -> table = mdl_course
		return $DB->record_exists('course', array("id"=>$course));
	}
	
	/**
	 * Permet de rajouter dans la table 'retrievecourse_cron'.
	 * @param int $idCourse
	 * @param int $userid
	 * @param string $nextShortname
	 */
	public function addCourse_cron($idCourse, $userid , $nextShortname){
		global $DB;
		$dataobject = array('courseid'=>$idCourse , 'user'=>$userid , 'shortname_course_new'=>$nextShortname , 'status'=>0,
				 'time_created'=> time() , 'tentative' => 0);
		$DB->insert_record('retrievecourse_cron', $dataobject);
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
		//$this->deleteOldRetrieve();
		$dataobject = array('courseid_old'=>$courseid_old ,'courseid_new'=>$idCourse,'shortname_course_old'=>$shortname_old,
				'shortname_course_new'=>$shortname_new,'user'=>$USER->id, 'annac'=>$temp ,'date'=>date('d-m-Y'),
				'flag_newcourse'=> $flag_newcourse , 'flag_use_cron'=>$flag_use_cron , 'flag_wait_cron_execute'=>$flag_wait_cron);
		$DB->insert_record('retrievecourse', $dataobject);
		} 
	}
	/**
	 * Permet de récupérer l'id d'un cours en fonction du shortname.
	 * @param String $shortname
	 * @return id du cours ou null dans le cas où le shortname n'existe pas.
	 */
	public function getCourseId($shortname){
		global $DB;
		$data = $DB->get_record('course', array("shortname"=>$shortname), 'id');
		return (($data != NULL) ? $data->id : NULL);
	}
	
	public function getRetrievecourseId($courseid_old){
		global $DB;
		$data = $DB->get_record('retrievecourse', array("courseid_old"=>$courseid_old), 'id');
		return (($data != NULL) ? $data->id : NULL);
	}
	
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
	
	public function cronFinish($idCron , $courseid_old){
		 $id = $this->getRetrievecourseId($courseid_old);
		 if($id != NULL){
		 	$this->updateFlagWaitCronExecute($id, false);
		 	$this->updateFlagUseCron($id, true);
		 }
	}
	
	public function getShortnameCourse($idCourse){
		global $DB;
		$data = $DB->get_record('course', array("id"=>$idCourse), 'shortname');
		return (($data != NULL) ? $data->shortname : NULL);
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
	
	
	/**
	 * Retourne l'id du cour à qui appartient le shortname.
	 * @param string $shortname
	 * @return l'id du cours ou null en cas d'erreur.
	 */
	public function retieveCourseId($shortname){
		global $DB;
		$data = $DB->get_record('course',  array("shortname"=>$shortname), 'id');
		return (($data != NULL) ? $data->id : NULL);
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
	 * Permet de récupérer tous les id , courseid , shortname et userid de la table retrievecourse_cron;
	 * @return multitype: tableau associatif.
	 */
	public function retrieveCron(){
		global $DB;
		$result = $DB->get_records_sql('SELECT id,courseid,shortname_course_new,user FROM mdl_retrievecourse_cron');
		return $result;
	}
	
	public function updateFlagStatus($id , $flag_execute){
		global $DB;
		$dataobject = array('id'=>$id , "status"=>$flag_execute);
		return $DB->update_record('retrievecourse_cron', $dataobject);
	}
	
	
	public function updateNbTentative($id , $tentative){
		global $DB;
		$dataobject = array('id'=>$id , "tentative"=>$tentative);
		return $DB->update_record('retrievecourse_cron', $dataobject);
	}
	
	public function getCronTentative($id){
		global $DB;
		$data = $DB->get_record('retrievecourse_cron',  array("id"=>$id), 'tentative');
		return (($data != NULL) ? $data->tentative : NULL);
	}
	
	public function getFlagStatus($id){
		global $DB;
		$data = $DB->get_record('retrievecourse_cron',  array("id"=>$id), 'status');
		return (($data != NULL) ? $data->status : NULL);
	}
	
	public function deleteCron($id){
		global $DB;
		return $DB->delete_records('retrievecourse_cron', array("id"=>$id));
	}
	
	
	
	public function updateTimeStart($id , $time){
		global $DB;
		$dataobject = array('id'=>$id , "time_start"=>$time);
		return $DB->update_record('retrievecourse_cron', $dataobject);
	}
	
	/**
	 * Permet de vérifier qu'un professeur est bien inscrit dans un cours et qu'il est professeur pour ce cour.
	 * @param int $courseid
	 * L'id du cour qu'on doit vérifier.
	 * @param int $userid
	 * @return vrai si l'user est inscrit dans le cour et qu'il est professeur.
	 */
	public function checkUserEnroledInCourse($courseid,$userid){
		global $DB;
		$userIsTecher = false;
		$enrolId = $this->getEnrolId($courseid);
		$condition = array("enrolid"=>$enrolId,"userid"=>$userid);
		if($enrolId != NULL && $DB->record_exists('user_enrolments',$condition)){
			$userIsTecher = $this->checkRoleAssignement($this->getContextid($courseid), $userid);
		}
		return $userIsTecher;
	}
	
	/**
	 * Permet de récupérer tous les cours qui n'ont pas utilisés le plugin et qui appartienne à l'année académique courante.
	 * @param int $idCagtegorie id de la categorie . 
	 * Quand idCatgeorie est différent de null , seul les cours appartenant à cette categorie seront récupérer.
	 * @return Tableau associatif dont la clé est l'id du cours et la valeur le shortname du cours.
	 */
	public function courseNotUsedPugin($idCagtegorie=null){
		global $DB,$CFG;
		$listeCours = array('-1'=>'All			');
		$cond = ($idCagtegorie == null) ? '' : ' and category =' . $idCagtegorie;
		$result = $DB->get_records_sql('SELECT mdl_course.id,mdl_course.shortname FROM mdl_course 
				WHERE mdl_course.id NOT IN (SELECT mdl_retrievecourse.courseid_old FROM mdl_retrievecourse)' 
				. $cond);
		$tempSize = $CFG->tempYearOne + $CFG->tempYearTwo;
		
		foreach ($result as $value){
			$temp = substr($value->shortname, -$tempSize);
			if($temp == $CFG->temp){
				$listeCours[$value->id] = $value->shortname;
			}			
		}
		return $listeCours;
	}
	
	public function getNbCourse(){
		global $DB, $CFG;
		$tempSize = $CFG->tempYearOne + $CFG->tempYearTwo;
		$result = $DB->get_records_sql('SELECT COUNT(*) FROM mdl_course
				WHERE SUBSTR(shortname, -'. $tempSize.') = '. $CFG->temp);
		return $result;
	}
	
	
	
	/**
	 * Permet de rechercher tous les cours qui contiennent le mot et qui n'ont pas utilisés le plugin.
	 * @param string $mot
	 */
	public function searchCourseNotUsedPlugin($search){
		global $DB,$CFG;
		$listeCours = array('-1'=>'All			');
		$param = array('search'=> $search);
		$result = $DB->get_records_sql('SELECT mdl_course.id,mdl_course.shortname FROM mdl_course
				WHERE mdl_course.id NOT IN (SELECT mdl_retrievecourse.courseid_old FROM mdl_retrievecourse)
				   and mdl_course.shortname LIKE "% :search %" ' , $param);
		foreach ($result as $value){
			//TODO Temp config
			$tempSize = $CFG->tempYearOne + $CFG->tempYearTwo;
			$temp = substr($value->shortname, -$tempSize);
			//TODO Recuperer des config '201314'
			if($temp == $CFG->temp){
				$listeCours[$value->id] = $value->shortname;
			}
		}
		return $listeCours;
	}
	
	public function checkUserExist($userid){
		global $DB;
		$used = $DB->record_exists('user', array("id"=>$userid));
		return $used;
	}
	
	/**
	 * Récupére le champs 'time_modified' de la table 'retrievecourse_cron' .
	 * @param $id
	 * @return le time_modified ou null dans le cas où l'id entré n'existe pas.
	 */
	public function getTimeModifiedCron($id){
		global $DB;
		$param = array('id'=> $id);
		$result = $DB->get_records_sql('SELECT time_modified FROM mdl_retrievecourse_cron WHERE id = :id',$param);
		($result == NULL) ? NULL : $result->time_modified ;
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
	/**
	 * Permet de récupérer l'id de la table cron qui est en cour de backup/restore.
	 * @return NULL dans le cas où aucun cours n'est en cours de backup/restore avec cron.
	 */
	public function getIdCronRunning(){
		global $DB;
		$obj = $DB->get_records_sql('SELECT id from mdl_retrievecourse_cron WHERE mdl_retrievecourse_cron.status = 1');
		return ($obj == NULL) ? NULL : $obj[0]->id;
	}
	
	public function getCategoryId($nameCategory){
		global $DB;
		$param = array('nameCategory'=>$nameCategory);
		$result = $DB->get_records_sql('SELECT id FROM mdl_course_categories WHERE name = :nameCategory',$param);
		($result == NULL) ? NULL : $result->id ;
	}
	
	/**
	 * Permet de récupérer toute les catégorie
	 * @return Tableau associatif dont la clé est l'id de la category et la valeur le nom de la categorie.
	 */
	public function retrieveCategories(){
		global $DB;
		$listeCategori = array('-1'=>'None');
		$result = $DB->get_records_sql('SELECT id,name FROM mdl_course_categories');
		foreach($result as $value){
			$listeCategori[$value->id] = $value->name;
		}
		return $listeCategori;
		
	}
	/**
	 * Permet de verifier que l'utisateur est un professeur dans un context donné. 
	 * @param int $contextid
	 * @param int $userid
	 * @param int $roleid
	 */
	private function checkRoleAssignement($contextid,$userid,$roleid = 3){
		global $DB;
		$conditions = array("contextid"=>$contextid,"userid"=>$userid,"roleid"=>$roleid);
		return $DB->record_exists('role_assignments',$conditions);
	}
	
	
	/**
	 * Permet de récupérer le context id des éléments d 'un cours
	 * @param int $courseid
	 * @param string $contextlevel
	 * @return NULL en cas d'erreur sinon l'id du context.
	 */
	private function getContextid($courseid,$contextlevel = CONTEXT_COURSE){
		global $DB;
		$data = $DB->get_record('context', array("instanceid"=>$courseid ,"contextlevel"=>$contextlevel),'id');
		return (($data != NULL) ? $data->id : NULL);
	}
	
	/**
	 * Permet de récupérer l'id de la table 'mdl_enrole' .
	 * @param id $courseId
	 * @param string $enrol
	 * Par défault , il vaut 'manual'. Cette attribut peut encore prendre comme valeur 'guest' et 'self'.
	 * @return null en cas d'erreur sinon l'id
	 */
	private function getEnrolId($courseId , $enrol = 'manual'){
		global $DB;
		$data = $DB->get_record('enrol',  array("courseid"=>$courseId,"enrol"=>$enrol), 'id');
		return (($data != NULL) ? $data->id : NULL);
	}
	
	//TODO pas oublier de supprimer dropRow ansi que dropTable.
	public function dropRow($id){
		global $DB;
		$DB->delete_records('retrievecourse', array("id"=>$id));
	}
	
	
	
	public function dropTable($table){
		global $DB;
		$dbman = $DB->get_manager();
		$t = new xmldb_table($table);
		if( $dbman->table_exists($table)){
			echo 'supressionTable ' .$table .  '</br>';
			$dbman->drop_table($t);
		}
	}
	
	
	
}