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
			var_dump($infoForm);
			($infoForm->choice_type_backup) ? $this->choiceBackupImmediately($infoForm->cours) : $this->choiceUseCron();
		}else{
			redirect("../..");
		}
		
		
	}
	
	function choiceUseCron(){
		global $PAGE;
		if($this->confirm(utf8_encode("êtes-vous certain de vouloir démarrer le backup/restore via cron?"))){
			
		}
		redirect($PAGE->url);
	}
	/**
	 * 
	 * @param Array|int $cours
	 */
	private function choiceBackupImmediately($cours){
		global $USER,$OUTPUT,$PAGE;
		if($this->confirm(utf8_encode("êtes-vous certain de vouloir démarrer le backup/restore immédiatement?"))){
			$service = new RetrieveCourseService(null , $USER->id , null);
			if($cours[0] == '-1'){
				foreach($this->formAdmin->getListeCour() as $key=> $value){
					//TODO Eventuellement supprime le ALL de la lise dans post_data_definition.
					if($key != -1){
						$this->service($service, $key, $value);
					}
				}
			}else{
				foreach ($cours as $value){
					$this->service($service, $value, $this->db->getShortnameCourse($value));
				}
			}
		}
		redirect($PAGE->url);
		
	}
	
	private function service($service,$idCourse,$shortname){
		$service->setCourse($idCourse);
		$service->setNextShortName($this->nextShortname($shortname));
		$service->runService();
		$temp = substr($this->nextShortname($shortname), -6);
		$this->db->addCourse_retrievecourse($this->nextShortname($shortname) , $temp , $idCourse);
	}
	//TODO Chercher un moyen pour pas dédoubler la méthode.
	/**
	 * Cette fonction permet de crée le shortname de l'année académique suiavnate.
	 * Cette fonction part du principe que les derniers caractéres représentent l' année académique.
	 * @param string $course
	 * @return Le shortname du cour pour l'année académique suivante.
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
	
	private function confirm($message){
		?> 
		<script type="text/javascript">
			var conf = confirm('<?php echo $message; ?>');
		</script>
		<?php 
		$valRet = "<script language='Javascript'> document.write(conf); </script>";
		return ($valRet == "true") ? true : false ;
	}
}