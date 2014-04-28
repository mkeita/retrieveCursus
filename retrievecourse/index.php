
<?php 
require('../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once ($CFG->libdir.'/accesslib.php');
require_once 'classes/controller/ControllerPrincipal.php';

defined('MOODLE_INTERNAL') || die;

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

headerRetrieveCursus();

$controller = new ControllerPrincipal();
$controller->verification();
$choice = $controller->choice_type_backup();
if(!$choice){
	$controller->display();
}

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


?>



