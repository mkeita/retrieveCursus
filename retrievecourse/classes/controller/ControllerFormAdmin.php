<?php


require_once '/../view/FormAdmin.php';
require_once '/../service/RetrieveCourseService.php';
require_once '/../model/RetrieveCourseConstante.php';


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
			$message_cron =utf8_encode('�tes-vous s�r de vouloir faire un backup/restore via cron?');
			$message_backup = utf8_encode('�tes-vous s�r de vouloir faire un backup/restore imm�diatement?');
			if($infoForm->choice_type_backup)
				$this->confirmation($message_backup, RetrieveCourseConstante::CONFIRMATION_BACKUP_IMMEDIAT,$infoForm->cours) ;
			else
				$this->confirmation($message_cron, RetrieveCourseConstante::CONFIRMATION_USE_CRON,$infoForm->cours) ;
		}else{
			redirect("../..");
		}	
		
	}
	
	private function confirmation($message , $type_confirmation , $cours){
		global $PAGE,$OUTPUT;
		//Dans le cas o� on a coch� All.
		if($cours[0] == -1){
			//Enl�ve toute les valeur pour que le type de tableau est identique qu'on s�l�ctionne all ou plusieur cour.
			//En effet , si on s�l�ctionne pas ALL mais qu'on s�l�ctionne des cours manuellement , '$cours' contiendra juste les id des
			//cours s�l�ctionn�.
			$cours = array_keys($this->formAdmin->getListeCour());
		}
		$json = json_encode($cours);
		echo $OUTPUT->confirm($message, '/report/retrievecourse/index.php?confirmation='. $type_confirmation .
				'&cour='.$json , '/report/retrievecourse/index.php');
		
	}
	
	
	
}