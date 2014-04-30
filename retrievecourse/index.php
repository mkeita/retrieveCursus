
<?php 
require('../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once ($CFG->libdir.'/accesslib.php');

require_once 'classes/controller/ControllerPrincipal.php';

defined('MOODLE_INTERNAL') || die;
define('MDL_PERF', true);
define('MDL_PERFDB', true);
define('MDL_PERFTOLOG', true);
define('MDL_PERFTOFOOT', true);

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

headerRetrieveCursus();

$controller = new ControllerPrincipal();
$controller->verification();
$controller->display();

echo $OUTPUT->footer();

die();

function headerRetrieveCursus(){
	global $PAGE , $OUTPUT,$CFG ;
	
	$id   = optional_param('id', 0, PARAM_INT);// Course ID
	
	if($id != NULL){
		$_SESSION['idCourse'] = $id;
	}
	
	if(!isset($_SESSION['idCourse'])){
		$_SESSION['idCourse'] = 1;
	}
	
	require_login($_SESSION['idCourse']);
	$PAGE->set_pagelayout('standard');
	$PAGE->set_title( $CFG->namePlugin);
	$PAGE->set_heading($CFG->namePlugin );
	$PAGE->set_url('/report/retrievecourse/index.php');
	echo $OUTPUT->header();
}

// function send_email($userid , $shortname){
// 	global $DB;
	
// 	$message = 'Bonjour, </br> </br>';
// 	$message .= $shortname . ' disponible';
	
// 	$userto = $DB->get_record('user', array("id"=>$userid));
	
// 	$admin = get_admin();
// 	$admin->priority = 1;
	
// 	//Send the message
// 	$eventdata = new stdClass();
// 	$eventdata->modulename        = 'moodle';
// 	$eventdata->userfrom          = $admin;
// 	$eventdata->userto            = $userto;
// 	$eventdata->subject           = utf8_encode('Récupération des informations dans le cours ' . $shortname);
// 	$eventdata->fullmessage       = $message;
// 	$eventdata->fullmessageformat = FORMAT_PLAIN;
// 	$eventdata->fullmessagehtml   = '';
// 	$eventdata->smallmessage      = '';
// 	$eventdata->component         = 'moodle';
// 	$eventdata->name         = 'backup';
// 	$eventdata->notification = 1;
// 	message_send($eventdata);
	
// }
?>



