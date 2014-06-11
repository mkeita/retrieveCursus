<?php
defined ( 'MOODLE_INTERNAL' ) || die ();

require_once ($CFG->libdir . '/formslib.php');
require_once (__DIR__ . '/../../outils.php');
/**
 * Permet de gérer la vue qui apparaît lorsque l'utilisateur est un professeur .
 *
 * @author Ilias
 *        
 */
class FormTeacher extends moodleform {
	// Add elements to form
	public function definition() {
		global $CFG,$PAGE;
		
		$mform = $this->_form; // Don't forget the underscore!
		$mform->addElement ( 'header', 'header_', get_string ( 'warning', 'report_retrievecourse' ) );
		$message = get_string('messageTeacher_part1', 'report_retrievecourse') . $PAGE->course->shortname . 
		get_string('messageTeacher_part2', 'report_retrievecourse') . nextShortname($PAGE->course->shortname)  . 
		get_string('messageTeacher_part3', 'report_retrievecourse') . $PAGE->course->shortname .
		get_string('messageTeacher_part4', 'report_retrievecourse');
		
		
		$mform->addElement ( 'html', $message  );
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