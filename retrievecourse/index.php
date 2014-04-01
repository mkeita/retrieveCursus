
<?php 
require('../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once ($CFG->libdir.'/accesslib.php');
require_once 'classes/view/FormTeacher.php';
require_once 'classes/controller/ControllerFormTeacher.php';


defined('MOODLE_INTERNAL') || die;

$id   = optional_param('id', 0, PARAM_INT);// Course ID
if($id != NULL){
	$_SESSION['idCourse'] = $id;	
}
headerRetrieveCursus();

verifierCreationCour();
verifierPluginUtilise();




if(is_siteadmin()){
	echo "administrateur </br>";
	global $DB;
	$dbman = $DB->get_manager();
	if( $dbman->table_exists('retrievecourse')){
		echo 'existe </br>';
		
	}else{
		echo 'existe pas </br>';
	}
}else{	
	 $formTeacher = new FormTeacher();
	 $controllerFormTeacher = new ControlleurFormTeacher($formTeacher);
	 ($formTeacher->is_submitted()) ? $controllerFormTeacher->teacher_submit(nextShortname($PAGE->course->shortname)) 
	 													:$formTeacher->display();
}

echo $OUTPUT->footer();
die();

function headerRetrieveCursus(){
	global $PAGE , $OUTPUT ;
	require_login($_SESSION['idCourse']);
	//require_login(2);
	$PAGE->set_pagelayout('standard');
	$PAGE->set_title("Retrieve Course");
	$PAGE->set_heading("Retrieve Course");
	$PAGE->set_url('/report/retrievecourse/index.php');
	echo $OUTPUT->header();
}

/**
 * Cette fonction permet de crée le shortname de l'année académique suiavnate.
 * Cette fonction part du principe que les dernier caractére représent l' année académique.
 * @param string $course
 * @return Le shortname du cour pour l'année académique suivante.
 */
 function nextShortname($course ,$tailleTemp = 6, $tailleYearOne = 4,$tailleYearTwo = 2){
	$temp = substr($course, -$tailleTemp);
	$yearOne = substr($temp, 0 , $tailleYearOne);
	$yearTwo = substr($temp,-$tailleYearTwo);
	$yearOne += 1;
	$yearTwo = ($yearTwo +1) % 100 ;
	$mnemo = substr($course, 0 , strlen($course)- $tailleTemp)	;
	$newShortname = $mnemo . $yearOne . $yearTwo ;
	return $newShortname;
}
/**
 * Permet de vérifier si le cour de l'année prochaine a bien été crée. 
 */
function verifierCreationCour(){
 	global $PAGE;
 	$db = new ManageDB();
 	$nextShortname = nextShortname($PAGE->course->shortname);
 	if(!$db->checkCourseExist($nextShortname)){
 		?> <script type="text/javascript" charset="utf-8" >
				alert("Le cour de l'ann\351e prochaine n'a pas encore \351t\351 cr\351e");
			</script>
 		<?php 
 		redirect('http://localhost/course/view.php?id='.$_SESSION['idCourse']);
 	}
 }

/**
 * Permet de vérifier si le plugin a déjà été utilisé.
 */ 
function verifierPluginUtilise(){
	global $PAGE;
	$db = new ManageDB();
	$course_used = $db->checkPluginUsed($_SESSION['idCourse']);
	if($course_used){
		?> <script type="text/javascript" charset="utf-8" >
				alert("Le plugin a d\351j\340 \351t\351 utilis\351.");
			</script>
 		<?php 
 		redirect('http://localhost/course/view.php?id='.$_SESSION['idCourse']);
	}
	
}
?>



