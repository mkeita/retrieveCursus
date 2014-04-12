<?php


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once '/../model/ManageDB.php';
require_once 'FormTrie.php';

/**
 * Permet de gérer la vue qui apparaît lorsque l'utilisateur est un administrateur .
 * @author Ilias
 */
class FormAdmin extends moodleform{
	
	private $listeCour;
	
	protected function definition() {
		global $CFG;
	
		$mform = $this->_form; // Don't forget the underscore!
		$db = new ManageDB();
		$idCategory = null;
		$this->listeCour =  $db->courseNotUsedPugin();
		$formTrie = new FormTrie();
		if($formTrie->is_submitted()){
			 $data = $formTrie->get_data();
			 if($data->recherche != ""){
			 	$this->listeCour = $db->searchCourseNotUsedPlugin($data->recherche);
			 }else{
			 	$idCategory = ($data->category == -1) ? NULL : $data->category ;
			 	$this->listeCour = $db->courseNotUsedPugin($idCategory);
			 }		
		}
		if(!$this->is_submitted() && !$this->is_cancelled()){
			$formTrie->display();
		}
		
		
		//TODO Faire les verification de départ pour chacun des éléments avant de les placer dans le select.
		$mform->addElement('header', 'header_admin', get_string('header_admin','report_retrievecourse'));
		
		$mform->addElement('select', 'cours', get_string('listeCour', 'report_retrievecourse'), $this->listeCour);
		$mform->getElement('cours')->setMultiple(true);
		$mform->getElement('cours')->setSelected(array('-1'));
		$mform->getElement('cours')->setSize(15);
		$radioarray=array();
		$radioarray[] =& $mform->createElement('radio', 'choice_type_backup', ' ', get_string('checkbox_usecron', 'report_retrievecourse'), 0, "use_cron");
		$radioarray[] =& $mform->createElement('radio', 'choice_type_backup', ' ', get_string('checkbox_backupImmediately', 'report_retrievecourse'), 1, "backupImmediately");
		$mform->addGroup($radioarray, 'radioar', ' ', array(' '), false);
		$mform->closeHeaderBefore('end');
		
		$this->add_action_buttons(true, get_string('submit'));
		
	}
	
	public function getListeCour(){
		return $this->listeCour;
	}

}