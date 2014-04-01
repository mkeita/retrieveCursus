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
		$condition = array("shortname"=>$course);
		//"mdl_" est rajouté automatiquement à "course" -> table = mdl_course
		$courseExiste = $DB->record_exists('course', $condition);
		return $courseExiste;
	}
	
	public function addCourse_retrievecourse($shortname , $temp){
		global $DB;
		$condition = array("shortname"=>$shortname);
		$ob = $DB->get_record('course', $condition, 'id');
		if($ob != null ){
				$this->deleteOldRetrieve();
				$dataobject = array('courseid_old'=>$_SESSION['idCourse'] ,'courseid_new'=>$ob->id,'shortname_course'=>$shortname,
						'annac'=>$temp ,'date'=>date('d-m-Y'));
				$DB->insert_record('retrievecourse', $dataobject);
		} 
	}
	
	/**
	 * Supprime l'ancien tuple lié à ce cour.
	 */
	public function deleteOldRetrieve(){
		global $DB,$PAGE;
		$conditions = array("courseid_new"=>$_SESSION['idCourse']);
		$used = $DB->record_exists('retrievecourse', $conditions);
		if($used){
  			$condition = array("courseid_new"=>$_SESSION['idCourse']);
			$DB->delete_records('retrievecourse', $condition);
		}
	}
	
	
	/**
	 * Retourne l'id du cour à qui appartient le shortname.
	 * @param string $shortname
	 * @return l'id du cours ou null en cas d'erreur.
	 */
	public function retieveCourseId($shortname){
		global $DB;
		$val = null;
		$condition = array("shortname"=>$shortname);
		$data = $DB->get_record('course', $condition, 'id');
		if($data != NULL ){
			$val = $data->id;
		}
		return $val;
	}
	
	/**
	 * Vérifier si le plugin a déjà utilisé pour un cour donné.
	 * Si l'id du cour existe dans la table 'retrieve_course' sous le champs 'courseid_old' alors il a déjà utilisé le plugin.
	 * @param int $id
	 * @return vrai si le plugin a déjà été utilisé.
	 */
	public function checkPluginUsed($id){
		global $DB;
		$conditions = array("courseid_old"=>$id);
		$used = $DB->record_exists('retrievecourse', $conditions);
		return $used; 
	}
	
	public function dropRow($id){
		global $DB;
		$condition = array("id"=>$id);
		$DB->delete_records('retrievecourse', $condition);
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