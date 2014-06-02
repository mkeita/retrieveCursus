<?php
defined ( 'MOODLE_INTERNAL' ) || die ();

require_once ($CFG->libdir . '/formslib.php');
/**
 * Permet de gérer la vue qui apparaît lorsque l'utilisateur est un professeur .
 *
 * @author Ilias
 *        
 */
class FormTeacher extends moodleform {
	// Add elements to form
	public function definition() {
		global $CFG;
		
		$mform = $this->_form; // Don't forget the underscore!
		$mform->addElement ( 'header', 'header_', get_string ( 'warning', 'report_retrievecourse' ) );
		$mform->addElement ( 'html', get_string ( 'messageTeacher', 'report_retrievecourse' ) );
		$mform->closeHeaderBefore ( 'end' );
		
		$mform->addElement ( 'header', 'header_', get_string ( 'messageTeacherChoice', 'report_retrievecourse' ) );
		$radioarray = array ();
		$radioarray [] = & $mform->createElement ( 'radio', 'choice_teacher', ' ', get_string ( 'checkbox_recuperer', 'report_retrievecourse' ), 0, "retrieve" );
		$radioarray [] = & $mform->createElement ( 'radio', 'choice_teacher', ' ', get_string ( 'checkbox_newcourse', 'report_retrievecourse' ), 1, "newcourse" );
		$mform->addGroup ( $radioarray, 'radioar', ' ', array (
				' ' 
		), false );
		$mform->closeHeaderBefore ( 'end' );
		
		$this->add_action_buttons ( false, get_string ( 'submit' ) );
	}
}