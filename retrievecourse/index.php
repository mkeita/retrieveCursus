
<?php 
require('../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once ($CFG->libdir.'/accesslib.php');
require_once 'classes/controller/ControllerPrincipal.php';
require_once 'classes/service/RetrieveCourseService.php';
require_once 'classes/model/ManageDB.php';

defined('MOODLE_INTERNAL') || die;
$progressBar = 	'<div id="conteneur" style="display:none; background-color:transparent; width:80%; border:1px solid #000000;">
					<div id="barre" style="display:block; background-color:rgba(132, 232, 104, 0.7); width:0%; height:100%;float:top;clear : top ;clear:both">
						<div id="pourcentage" style="text-align:right; height:100%; font-size:1.8em;">
							&nbsp;
						</div>
					</div>
				</div>
				<label id="progress_bar_description"></label>';

$id   = optional_param('id', 0, PARAM_INT);// Course ID
$confirm = optional_param('confirmation', 0, PARAM_TEXT);// Course ID
$courJson = optional_param('cour', 0, PARAM_TEXT);
if($id != NULL){
	$_SESSION['idCourse'] = $id;	
}

headerRetrieveCursus();


if($confirm != NULL && $courJson != NULL){
	echo $progressBar;
	activerAffichage();
	backup_immediat($courJson);
	
}else{
	
	$controller = new ControllerPrincipal();
	$controller->verification();
	$controller->display();
		
}

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


function backup_immediat($courJson){
	global $USER,$PAGE;
	$db = new ManageDB();
	$cour = json_decode($courJson);
	$service = new RetrieveCourseService(null , $USER->id , null);
	$indice = 0;
	$nbElemRestore = count($cour) ;
	$service->step =1/(count($nbElemRestore)*2); 
	foreach ($cour as $idCourse){
		$shortname =  $db->getShortnameCourse($idCourse);
		progression($indice);
		$service->currentProgress = $indice;
		if($shortname != NULL){
			$service->setCourse($idCourse);
			$service->setNextShortName(nextShortname($shortname));
			$service->runService($nbElemRestore);
			$temp = substr(nextShortname($shortname), -6);
		//	$db->addCourse_retrievecourse($shortname , nextShortname($shortname) , $temp , $idCourse);
		}
		$indice += 100 /(count($nbElemRestore)*2 );
	}
//	progression($indice);
}

//TODO Chercher un moyen pour pas dédoubler la méthode.
/**
 * Cette fonction permet de crée le shortname de l'année académique suiavnate.
 * Cette fonction part du principe que les derniers caractéres représentent l' année académique.
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

function progression($indice)
{
	echo "<script>";
	echo "document.getElementById('pourcentage').innerHTML='$indice%';";
	echo "document.getElementById('barre').style.width='$indice%';";
	echo "</script>";

	ob_flush();
	flush();
}

function activerAffichage(){
	echo "<script>";
	echo "document.getElementById('conteneur').style.display = \"block\";";
	echo "</script>";
	echo "<br/>";
	ob_flush();
	flush();
}

?>



