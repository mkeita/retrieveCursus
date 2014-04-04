
<?php 
require('../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once ($CFG->libdir.'/accesslib.php');
require_once 'classes/controller/ControllerPrincipal.php';


defined('MOODLE_INTERNAL') || die;

$id   = optional_param('id', 0, PARAM_INT);// Course ID
if($id != NULL){
	$_SESSION['idCourse'] = $id;	
}

headerRetrieveCursus();

$controller = new ControllerPrincipal();
$controller->verification();
$controller->display();


echo $OUTPUT->footer();
die();

function headerRetrieveCursus(){
	global $PAGE , $OUTPUT ;
	require_login($_SESSION['idCourse']);
	$PAGE->set_pagelayout('standard');
	$PAGE->set_title("Retrieve Course");
	$PAGE->set_heading("Retrieve Course");
	$PAGE->set_url('/report/retrievecourse/index.php');
	echo $OUTPUT->header();
}


	

?>



