<?php
/**
 * Toute les requêtes sql se font à se niveau.
 * @author Ilias
 */
class ManageDB {
	
	/**
	 * Cette méthode permet de vérifier qu'un cours existe
	 * @param string $course Le shortname du cours dont on veut vérifier l'existence.
	 * @return vrai si le cour existe.
	 */
	public function checkCourseExist($course){
		global $DB;
		//"mdl_" est rajouté automatiquement à "course" -> table = mdl_course
		return $DB->record_exists('course', array("shortname"=>$course));
	}
	
	/**
	 * Permet de rajouter dans la table 'retrievecourse' tout les cours qui ont déjà utilisé le plugin.
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
	 * Permet de récupérer l'id d'un cours en fonction du shortname.
	 * @param String $shortname
	 * @return id du cours ou null dans le cas où le shortname n'existe pas.
	 */
	public function getCourseId($shortname){
		global $DB;
		$data = $DB->get_record('course', array("shortname"=>$shortname), 'id');
		return (($data != NULL) ? $data->id : NULL);
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