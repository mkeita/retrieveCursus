<?php 
require('../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once ($CFG->libdir.'/accesslib.php');
require_once 'classes/view/FormTeacher.php';
require_once 'classes/controller/ControllerFormTeacher.php';


defined('MOODLE_INTERNAL') || die;

$id   = optional_param('id', 0, PARAM_INT);// Course ID
if($id != NULL){
	#$_SESSION['id'] = $id;	
}
headerRetrieveCursus($id);

if(is_siteadmin()){
	echo "administrateur </br>";
}else{	
	 $formTeacher = new FormTeacher();
	 $controllerFormTeacher = new ControlleurFormTeacher($formTeacher);
	 ($formTeacher->is_submitted()) ? $controllerFormTeacher->teacher_submit($PAGE->course->shortname) 
	 													:$formTeacher->display();
}

echo $OUTPUT->footer();
die();

function headerRetrieveCursus($id){
	global $PAGE , $OUTPUT ;
	//require_login($_SESSION['id']);
	require_login(2);
	$PAGE->set_pagelayout('standard');
	$PAGE->set_title("Retrieve Course");
	$PAGE->set_heading("Retrieve Course");
	$PAGE->set_url('/report/retrievecourse/index.php');
	echo $OUTPUT->header();
}

?>