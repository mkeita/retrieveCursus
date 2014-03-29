<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
/**
* Permet de gérer la vue qui apparaît lorsque l'utilisateur est un professeur .
* @author Ilias
*/
class FormTeacher extends moodleform {
	//Add elements to form
	public function definition() {
		global $CFG;
	
		$mform = $this->_form; // Don't forget the underscore!
		$mform->addElement('header', 'header_', get_string('warning','report_retrievecourse'));
		$mform->addElement('html', get_string('messageTeacher','report_retrievecourse' ));
		$mform->closeHeaderBefore('end');
		
		$mform->addElement('header', 'header_', get_string('messageTeacherChoice','report_retrievecourse'));
// 		$mform->addElement('advcheckbox', 'checkbox_recuperer', get_string('checkbox_recuperer', 'report_retrievecourse'), null, array('group' => 1), array(0, 1));
// 		$mform->addElement('advcheckbox', 'checkbox_newcourse', get_string('checkbox_newcourse', 'report_retrievecourse'), null, array('group' => 1), array(0, 1));
		$radioarray=array();
		$radioarray[] =& $mform->createElement('radio', 'choice_teacher', ' ', get_string('checkbox_recuperer', 'report_retrievecourse'), 0, "retrieve");
		$radioarray[] =& $mform->createElement('radio', 'choice_teacher', ' ', get_string('checkbox_newcourse', 'report_retrievecourse'), 1, "newcourse");
		$mform->addGroup($radioarray, 'radioar', ' ', array(' '), false);
		$mform->closeHeaderBefore('end');
		
		$this->add_action_buttons(false, get_string('submit'));
	}
	
	
	
	
}