
<?php 

require('../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once ($CFG->libdir.'/accesslib.php');

require_once (__DIR__ . '/classes/controller/ControllerPrincipal.php');
defined('MOODLE_INTERNAL') || die;


$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

headerRetrieveCursus();

// $admin = get_admins();
// var_dump($admin);

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
	$namePlugin = 'Copie '. (substr($CFG->temp,0,$CFG->tempYearOne)+1) .'-'. (substr($CFG->temp,-$CFG->tempYearTwo)+1) .' du cours';
	$PAGE->set_title( $namePlugin);
	$PAGE->set_heading($namePlugin );
	$PAGE->set_url('/report/retrievecourse/index.php');
	echo $OUTPUT->header();
}


?>



