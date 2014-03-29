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
	
	public function addCourseBd($idcourse){
		
	}
	
	
}