<?php


require_once '/../view/FormAdmin.php';
require_once '/../service/RetrieveCourseService.php';



/**
 * 
 * @author Ilias
 *
 */
class ControllerFormAdmin {
	/**
	 * 
	 * @var FormAdmin
	 */
	private $formAdmin;
	private $db; 
	function __construct($formAdmin){
		$this->formAdmin = $formAdmin;
		$this->db = new ManageDB();
	}
	
	function admin_submit(){
		global $PAGE;
		if(!$this->formAdmin->is_cancelled()){
			$infoForm = $this->formAdmin->get_data();
			($infoForm->choice_type_backup) ? $this->choiceBackupImmediately($infoForm->cours) : $this->choiceUseCron();
		}else{
			redirect("../..");
		}	
		
	}
	
	function choiceUseCron(){
		global $PAGE;
			
		redirect($PAGE->url);
	}
	/**
	 * 
	 * @param Array|int $cours
	 */
	private function choiceBackupImmediately($cours){
		global $USER,$OUTPUT,$PAGE;
		
		//Dans le cas o� on a coch� All.
		if($cours[0] == -1){
			//Enl�ve toute les valeur pour que le type de tableau est identique qu'on s�l�ctionne all ou plusieur cour.
			$cours = array_keys($this->formAdmin->getListeCour());
		}	
		$message = 'Are you sur?';
		$json = json_encode($cours);
		echo $OUTPUT->confirm($message, '/report/retrievecourse/index.php?confirmation=backup_immediat&cour='.$json
				, '/report/retrievecourse/index.php');
	}
	
	private function service($service,$idCourse,$shortname){
		$service->setCourse($idCourse);
		$service->setNextShortName($this->nextShortname($shortname));
		$service->runService();
		$temp = substr($this->nextShortname($shortname), -6);
		$this->db->addCourse_retrievecourse($shortname , $this->nextShortname($shortname) , $temp , $idCourse);
	}
	//TODO Chercher un moyen pour pas d�doubler la m�thode.
	/**
	 * Cette fonction permet de cr�e le shortname de l'ann�e acad�mique suiavnate.
	 * Cette fonction part du principe que les derniers caract�res repr�sentent l' ann�e acad�mique.
	 * @param string $course
	 * @return Le shortname du cour pour l'ann�e acad�mique suivante.
	 */
	private function nextShortname($course ,$tailleTemp = 6, $tailleYearOne = 4,$tailleYearTwo = 2){
		$temp = substr($course, -$tailleTemp);
		$yearOne = substr($temp, 0 , $tailleYearOne);
		$yearTwo = substr($temp,-$tailleYearTwo);
		$yearOne += 1;
		$yearTwo = ($yearTwo +1) % 100 ;
		$mnemo = substr($course, 0 , strlen($course)- $tailleTemp)	;
		$newShortname = $mnemo . $yearOne . $yearTwo ;
		return $newShortname;
	}
	
	
	
}