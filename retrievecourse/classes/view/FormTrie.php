<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * Permettra de faire le trie dans FormAdmin.
 * @author Ilias
 *
 */
class FormTrie extends moodleform {
	
	protected function definition() {
		
		global $CFG;
		
		$mform = $this->_form; // Don't forget the underscore!
		$db = new ManageDB();
		
		$mform->addElement('header', 'trie', get_string('trie','report_retrievecourse'));
		$listCategory = $db->retrieveCategories();
		$mform->addElement('select', 'category', get_string('categorie','report_retrievecourse'), $listCategory);
		$mform->addElement('html', get_string("element_recherche","report_retrievecourse") . '<br/>');
		$mform->addElement('text', 'recherche', get_string('recherche', 'report_retrievecourse'));
		$mform->setType('recherche', PARAM_TEXT);
		
		$this->add_action_buttons(false, get_string('recherche_button', 'report_retrievecourse'));
		
		$mform->closeHeaderBefore('end_trie');
		

	}
	

}