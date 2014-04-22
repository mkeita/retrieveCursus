<?php
/**
 * Contient toutes les requêtes sql utilisé pour créer les graphiques.
 * @author Ilias
 *
 */
class ManageGraphiqueDB {
	/**
	 * Permet de retourner le nombre de backup immédiat qu'a fait l'administrateur.
	 * @return string:
	 */
	public function getNbAdminBackupImmediat(){
		global $DB,$CFG;
		$result = $DB->get_records_sql('SELECT COUNT(*) as nbadmin FROM mdl_retrievecourse WHERE user = :userid
				AND flag_use_cron = false AND flag_wait_cron_execute = false ' , array('userid'=>$CFG->idAdminUser));
		foreach ($result as $nb);
		return ($result != NULL) ? $nb->nbadmin : NULL;
	}
	
	
	/**
	 * Permet de retourner le nombre de backup via cron  qu'a fait l'administrateur.
	 * @return string:
	 */
	public function getNbAdminBackupCron(){
		global $DB,$CFG;
		$result = $DB->get_records_sql('SELECT COUNT(*) as nbadmin FROM mdl_retrievecourse WHERE user = :userid
				AND (flag_use_cron = true OR flag_wait_cron_execute = true) ' , array('userid'=>$CFG->idAdminUser));
		foreach ($result as $nb);
		return ($result != NULL) ? $nb->nbadmin : NULL;
	}
	
	/**
	 * 
	 * @return NULL
	 */
	public function getNbTeacherChoiceNewCourse(){
		global $DB,$CFG;
		$result = $DB->get_records_sql('SELECT COUNT(*) as nbadmin FROM mdl_retrievecourse WHERE user != :userid
			AND flag_newcourse = true ' , array('userid'=>$CFG->idAdminUser));
		foreach ($result as $nb);
		return ($result != NULL) ? $nb->nbadmin : NULL;
	}
	
	public function getNbTeacherChoiceBackup(){
		global $DB,$CFG;
		$result = $DB->get_records_sql('SELECT COUNT(*) as nbadmin FROM mdl_retrievecourse WHERE user != :userid
				AND (flag_use_cron = true OR flag_wait_cron_execute = true) ' , array('userid'=>$CFG->idAdminUser));
		foreach ($result as $nb);
		return ($result != NULL) ? $nb->nbadmin : NULL;
	}
	
	public function getNbCourNotUsedPlugin(){
		global $DB,$CFG;
		$tailleTemp = $CFG->tempYearOne + $CFG->tempYearTwo;
		$result = $DB->get_records_sql('SELECT COUNT(*) as nbadmin FROM mdl_course 
				WHERE (SUBSTR(mdl_course.shortname,-:taille) = :temp) AND mdl_course.id NOT IN (SELECT mdl_retrievecourse.courseid_old FROM mdl_retrievecourse)'
		, array('taille'=>$tailleTemp,'temp'=>$CFG->temp));
		foreach ($result as $nb);
		return ($result != NULL) ? $nb->nbadmin : NULL;
	}
	
	public function getNbCourUsedPlugin(){
		global $DB,$CFG;
		$result = $DB->get_records_sql('SELECT COUNT(*) as nbadmin FROM mdl_retrievecourse');
		foreach ($result as $nb);
		return ($result != NULL) ? $nb->nbadmin : NULL;
	}
	
}