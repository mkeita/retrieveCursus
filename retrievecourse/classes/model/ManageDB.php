<?php
/**
 * Toute les requ�tes sql se font � se niveau.
 * @author Ilias
 */
class ManageDB {
	
	/**
	 * Cette m�thode permet de v�rifier qu'un cours existe
	 * @param string $course Le shortname du cours dont on veut v�rifier l'existence.
	 * @return vrai si le cour existe.
	 */
	public function checkCourseExist($course){
		global $DB;
		//"mdl_" est rajout� automatiquement � "course" -> table = mdl_course
		return $DB->record_exists('course', array("shortname"=>$course));
	}
	
	/**
	 * Permet de rajouter dans la table 'retrievecourse' tout les cours qui ont d�j� utilis� le plugin.
	 * @param string $shortname
	 * @param string $temp
	 * La fin du shortname.
	 */
	public function addCourse_retrievecourse($shortname , $temp){
		global $DB;
		$idCourse = $this->getCourseId($shortname);
		if($idCourse != null ){
				$this->deleteOldRetrieve();
				$dataobject = array('courseid_old'=>$_SESSION['idCourse'] ,'courseid_new'=>$idCourse,'shortname_course'=>$shortname,
						'annac'=>$temp ,'date'=>date('d-m-Y'));
				$DB->insert_record('retrievecourse', $dataobject);
		} 
	}
	/**
	 * Permet de r�cup�rer l'id d'un cours en fonction du shortname.
	 * @param String $shortname
	 * @return id du cours ou null dans le cas o� le shortname n'existe pas.
	 */
	public function getCourseId($shortname){
		global $DB;
		$data = $DB->get_record('course', array("shortname"=>$shortname), 'id');
		return (($data != NULL) ? $data->id : NULL);
	}
	
	/**
	 * Supprime l'ancien tuple li� � ce cours dans la table 'retrievecourse'.
	 */
	private function deleteOldRetrieve(){
		global $DB,$PAGE;
		$used = $DB->record_exists('retrievecourse',  array("courseid_new"=>$_SESSION['idCourse']));
		if($used){
			$DB->delete_records('retrievecourse', array("courseid_new"=>$_SESSION['idCourse']));
		}
	}
	
	
	/**
	 * Retourne l'id du cour � qui appartient le shortname.
	 * @param string $shortname
	 * @return l'id du cours ou null en cas d'erreur.
	 */
	public function retieveCourseId($shortname){
		global $DB;
		$data = $DB->get_record('course',  array("shortname"=>$shortname), 'id');
		return (($data != NULL) ? $data->id : NULL);
	}
	
	/**
	 * V�rifier si le plugin a d�j� utilis� pour un cour donn�.
	 * Si l'id du cour existe dans la table 'retrieve_course' sous le champs 'courseid_old' alors il a d�j� utilis� le plugin.
	 * @param int $id
	 * @return vrai si le plugin a d�j� �t� utilis�.
	 */
	public function checkPluginUsed($id){
		global $DB;
		$used = $DB->record_exists('retrievecourse', array("courseid_old"=>$id));
		return $used; 
	}
	
	
	
	/**
	 * Permet de v�rifier qu'un professeur est bien inscrit dans un cours et qu'il est professeur pour ce cour.
	 * @param int $courseid
	 * L'id du cour qu'on doit v�rifier.
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
	 * Permet de verifier que l'utisateur est un professeur dans un context donn�. 
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
	 * Permet de r�cup�rer le context id des �l�ments d 'un cours
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
	 * Permet de r�cup�rer l'id de la table 'mdl_enrole' .
	 * @param id $courseId
	 * @param string $enrol
	 * Par d�fault , il vaut 'manual'. Cette attribut peut encore prendre comme valeur 'guest' et 'self'.
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