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
		$nbAdmin = 0;
		$result = $DB->get_records_sql('SELECT id,user FROM mdl_retrievecourse WHERE flag_use_cron = false 
					AND flag_wait_cron_execute = false ' );
		foreach ($result as $key=>$value){
			if(array_key_exists($value->user , get_admins())){
				$nbAdmin++;
			}
		}
		return $nbAdmin;
	}
	
	
	/**
	 * Permet de retourner le nombre de backup via cron  qu'a fait l'administrateur.
	 * @return string:
	 */
	public function getNbAdminBackupCron(){
		global $DB,$CFG;
		$nbAdmin = 0;
		$result = $DB->get_records_sql('SELECT id,user FROM mdl_retrievecourse WHERE flag_use_cron = true
					OR flag_wait_cron_execute = true ' );
		foreach ($result as $key=>$value){
			if(array_key_exists($value->user , get_admins())){
				$nbAdmin++;
			}
		}
		return $nbAdmin;
	}
	
	/**
	 * 
	 * @return NULL
	 */
	public function getNbTeacherChoiceNewCourse(){
		global $DB,$CFG;
		$nbAdmin = 0;
		$result = $DB->get_records_sql('SELECT id,user FROM mdl_retrievecourse WHERE flag_newcourse = true ' );
		foreach ($result as $key=>$value){
			if(!array_key_exists($value->user , get_admins())){
				$nbAdmin++;
			}
		}
		return $nbAdmin;
	}
	
	public function getNbTeacherChoiceBackup(){
		global $DB,$CFG;
		$nbAdmin = 0;
		$result = $DB->get_records_sql('SELECT id,user FROM mdl_retrievecourse WHERE (flag_use_cron = true
				 OR flag_wait_cron_execute = true) ' );
		foreach ($result as $key=>$value){
			if(!array_key_exists($value->user , get_admins())){
				$nbAdmin++;
			}
		}
		return $nbAdmin;
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