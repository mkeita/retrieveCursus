<?php


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once '/../model/ManageDB.php';
require_once '/../model/ManageGraphiqueDB.php';
require_once '/../service/Graphique.php';

/**
 * Permet de gérer la vue qui apparaît lorsque l'utilisateur est un administrateur .
 * @author Ilias
 */
class FormAdmin extends moodleform{
	
	private $listeCour;
	private $db;
	
	protected function definition() {
		global $CFG;
		
		$mform = $this->_form; 
		$this->db = new ManageDB();
		$idCategory = null;
		
		$this->creationGraphique($mform);
		
		$this->creationTrie($mform);
		
		$this->creationListeCour($mform);
		
		$this->add_action_buttons(true, get_string('submit'));
		
	}

	private function creationListeCour($mform){
		$this->initialiserListeCour();
		//TODO Faire les verification de départ pour chacun des éléments avant de les placer dans le select.
		$mform->addElement('header', 'header_admin', get_string('header_admin','report_retrievecourse'));
		
		$mform->addElement('select', 'cours', get_string('listeCour', 'report_retrievecourse'), $this->listeCour);
		$mform->getElement('cours')->setMultiple(true);
		//-1 représente la valeur du ALL
		$mform->getElement('cours')->setSelected(array('-1'));
		$mform->getElement('cours')->setSize(15);
		$radioarray=array();
		$radioarray[] =& $mform->createElement('radio', 'choice_type_backup', ' ', get_string('checkbox_usecron', 'report_retrievecourse'), 0, "use_cron");
		$radioarray[] =& $mform->createElement('radio', 'choice_type_backup', ' ', get_string('checkbox_backupImmediately', 'report_retrievecourse'), 1, "backupImmediately");
		$mform->addGroup($radioarray, 'radioar', ' ', array(' '), false);
		$mform->closeHeaderBefore('end');
	}
	
	
	
	private function initialiserListeCour(){
		$search = optional_param('search', '', PARAM_TEXT);
		$category = optional_param('categories', '', PARAM_TEXT);
		if($search != NULL){
			$this->listeCour = $this->db->searchCourseNotUsedPlugin($search);
		}elseif($category != NULL){
			$this->listeCour = $this->db->courseNotUsedPugin($category);
		}else{
			$this->listeCour =  $this->db->courseNotUsedPugin();
		}
	}
	
	private function creationTrie($mform){
		$mform->addElement('header', 'trie', get_string('trie','report_retrievecourse'));
		$listCategory = $this->db->retrieveCategories();
		$select = $mform->addElement('select', 'category', get_string('categorie','report_retrievecourse'), $listCategory);
		$mform->addElement('html', get_string("element_recherche","report_retrievecourse") . '<br/>');
		$mform->addElement('text', 'recherche', get_string('recherche', 'report_retrievecourse'));
		$mform->setType('recherche', PARAM_TEXT);
		$mform->registerNoSubmitButton('trie');
		$otagsgrp = array();
		$otagsgrp[] =& $mform->createElement('submit', 'trie', 'trie');
		$mform->addGroup($otagsgrp, '', '','<br>');
		$mform->closeHeaderBefore('end_trie');
	}
	
	private function creationGraphique($mform){
		$mform->addElement('header', 'header_statistique', get_string('header_statistique','report_retrievecourse'));
		$mform->addElement('html',get_string('debut_graphique_div','report_retrievecourse'));
		$mform->addElement('html',get_string('graphique_usingPlugin','report_retrievecourse'));
		$mform->addElement('html',get_string('graphique_admin','report_retrievecourse'));
		$mform->addElement('html',get_string('graphique_prof','report_retrievecourse'));
		
		$mform->addElement('html',get_string('fin_graphique_div','report_retrievecourse'));
		$mform->closeHeaderBefore('end');
		
		$graphDB = new ManageGraphiqueDB();
		$graph = new Graphique();
		$arrayAdmin = array(
				'admin backup'=> $graphDB->getNbAdminBackupImmediat(),
				'admin cron' => $graphDB->getNbAdminBackupCron()	
		);
		
		$arrayProf = array(
				'new course'=>$graphDB->getNbTeacherChoiceNewCourse(),
				'backup ' => $graphDB->getNbTeacherChoiceBackup()
		);
		
		$arrayUsedPlugin = array(
				'Courses that used </br>the plugin' => $graphDB->getNbCourUsedPlugin(),
				'Courses that doesn\'t </br>use the plugin' => $graphDB->getNbCourNotUsedPlugin()
		);
		$graph->genererGraphique($arrayAdmin, 'graphique_admin', 'Fait par admin');
		$graph->genererGraphique($arrayProf, 'graphique_prof', 'Fait par prof');
		$graph->genererGraphique($arrayUsedPlugin, 'graphique_usingPlugin', 'Utilisation du plugin');
		
	}
	
	public function envoiInfoTrie(){
		global $PAGE;
		$data = $this->get_submitted_data();
		$url = $PAGE->url . '?';
		 if($data->recherche != ""){
		 	$url .= 'search='. $data->recherche;
		 }else{
		 	$idCategory = ($data->category == -1) ? NULL : $data->category ;
		 	$url .= 'categories=' . $idCategory;
		 }
		redirect($url);
	}
	
	
	public function getListeCour(){
		return $this->listeCour;
	}

}