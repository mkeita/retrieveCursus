<?php
/**
 * Toute les requ�tes sql se font � se niveau.
 * @author Ilias
 */
class ManageDB {
	
	/**
	 * Cette m�thode permet de v�rifier qu'un cours existe � partir de son shortname.
	 * @param string $course Le shortname du cours dont on veut v�rifier l'existence.
	 * @return vrai si le cour existe.
	 */
	public function checkCourseExist($course){
		global $DB;
		//"mdl_" est rajout� automatiquement � "course" -> table = mdl_course
		return $DB->record_exists('course', array("shortname"=>$course));
	}
	

	/**
	 * Cette m�thode permet de v�rifier qu'un cours existe � partir de son id.
	 * @param string $course Id du cours dont on veut v�rifier l'existence.
	 * @return vrai si le cour existe.
	 */
	public function checkIdCourseExist($course){
		global $DB;
		//"mdl_" est rajout� automatiquement � "course" -> table = mdl_course
		return $DB->record_exists('course', array("id"=>$course));
	}
	
	/**
	 * Permet de rajouter dans la table 'retrievecourse' tout les cours qui ont d�j� utilis� le plugin.
	 * @param string $shortname
	 * @param string $temp
	 * La fin du shortname.
	 */
	public function addCourse_retrievecourse($shortname , $temp , $courseid_old){
		global $DB,$USER;
		$idCourse = $this->getCourseId($shortname);
		if($idCourse != null ){
				$this->deleteOldRetrieve();
				$dataobject = array('courseid_old'=>$courseid_old ,'courseid_new'=>$idCourse,'shortname_course'=>$shortname,
						'user'=>$USER->id, 'annac'=>$temp ,'date'=>date('d-m-Y'));
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
	
	public function getShortnameCourse($idCourse){
		global $DB;
		$data = $DB->get_record('course', array("id"=>$idCourse), 'shortname');
		return (($data != NULL) ? $data->shortname : NULL);
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
	 * Permet de r�cup�rer tous les cours qui n'ont pas utilis�s le plugin.
	 * @param int $idCagtegorie id de la categorie . 
	 * Quand idCatgeorie est diff�rent de null , seul les cours appartenant � cette categorie seront r�cup�rer.
	 * @return Tableau associatif dont la cl� est l'id du cours et la valeur le shortname du cours.
	 */
	public function courseNotUsedPugin($idCagtegorie=null){
		global $DB;
		$listeCours = array('-1'=>'All			');
		$cond = ($idCagtegorie == null) ? '' : ' and category =' . $idCagtegorie;
		$result = $DB->get_records_sql('SELECT mdl_course.id,mdl_course.shortname FROM mdl_course 
				WHERE mdl_course.id NOT IN (SELECT mdl_retrievecourse.courseid_old FROM mdl_retrievecourse)' 
				. $cond);
		foreach ($result as $value){
			//TODO Temp config
			$temp = substr($value->shortname, -6);
			//TODO Recuperer des config '201314'
			if($temp == '201314'){
				$listeCours[$value->id] = $value->shortname;
			}			
		}
		return $listeCours;
	}
	
	/**
	 * Permet de rechercher tous les cours qui contiennent le mot et qui n'ont pas utilis�s le plugin.
	 * @param string $mot
	 */
	public function searchCourseNotUsedPlugin($search){
		global $DB;
		$listeCours = array('-1'=>'All			');
		$result = $DB->get_records_sql('SELECT mdl_course.id,mdl_course.shortname FROM mdl_course
				WHERE mdl_course.id NOT IN (SELECT mdl_retrievecourse.courseid_old FROM mdl_retrievecourse)
				   and mdl_course.shortname LIKE \'%' . $search . '%\'');
		
		foreach ($result as $value){
			//TODO Temp config
			$temp = substr($value->shortname, -6);
			//TODO Recuperer des config '201314'
			if($temp == '201314'){
				$listeCours[$value->id] = $value->shortname;
			}
		}
		return $listeCours;
	}
	
	
	
	public function getCategoryId($nameCategory){
		global $DB;
		$result = $DB->get_records_sql('SELECT id FROM mdl_course_categories WHERE name = \''. $nameCategory .'\'');
		($result == NULL) ? NULL : $result->id ;
	}
	
	/**
	 * Permet de r�cup�rer toute les cat�gorie
	 * @return Tableau associatif dont la cl� est l'id de la category et la valeur le nom de la categorie.
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