<?php
/**
 * @author Ilias
 */
class ManageDB {

	/**
	 * Permet de récupérer le context id des éléments d 'un cours
	 * @param int $courseid
	 * @param string $contextlevel
	 * @return NULL en cas d'erreur sinon l'id du context.
	 */
	public function getContextid($courseid,$contextlevel = CONTEXT_COURSE){
		global $DB;
		$data = $DB->get_record('context', array("instanceid"=>$courseid ,"contextlevel"=>$contextlevel),'id');
		return (($data != NULL) ? $data->id : NULL);
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
	 * Permet de récupérer le shortname d'un cours à partir de son id
	 */
	public function getShortnameCourse($idCourse){
		global $DB;
		$data = $DB->get_record('course', array("id"=>$idCourse), 'shortname');
		return (($data != NULL) ? $data->shortname : NULL);
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
	 * Permet de vérifier qu'un professeur est bien inscrit dans un cours et qu'il est professeur pour ce cour.
	 * @param int $courseid
	 * L'id du cour qu'on doit vérifier.
	 * @param int $userid
	 * @return vrai si l'user est inscrit dans le cour et qu'il est professeur.
	 */
	public function checkUserEnroledInCourse($courseid,$userid){
		global $DB;
		$roleid = 3;
		return user_has_role_assignment($userid, $roleid , $this->getContextid($courseid));
	}
	
	/**
	 * Permet de récupérer le nombre totale de cours où le "temp" du shortname vaut le temp choisis dans les settings.
	 * @return multitype:
	 */
	public function getNbCourse(){
		global $DB, $CFG;
		$tempSize = $CFG->tempYearOne + $CFG->tempYearTwo;
		$result = $DB->get_records_sql('SELECT COUNT(*) FROM mdl_course
				WHERE SUBSTR(shortname, -'. $tempSize.') = '. $CFG->temp);
		return $result;
	}
	
	/**
	 * Permet de vérifier qu'un utilisateur existe
	 * @param unknown $userid
	 * @return boolean
	 */
	public function checkUserExist($userid){
		global $DB;
		$used = $DB->record_exists('user', array("id"=>$userid));
		return $used;
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
}