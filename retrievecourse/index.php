
<?php 
require('../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once ($CFG->libdir.'/accesslib.php');
require_once 'classes/controller/ControllerPrincipal.php';
require_once 'classes/service/RetrieveCourseService.php';
require_once 'classes/model/RetrieveCourseConstante.php';
require_once 'classes/model/ManageDB.php';
require_once 'outils.php';
require_once($CFG->libdir.'/cronlib.php');

defined('MOODLE_INTERNAL') || die;

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');



$progressBar = 	'<div id="conteneur" style="display:block; background-color:transparent; width:80%; border:1px solid #000000;">
					<div id="barre" style="display:block; background-color:rgba(132, 232, 104, 0.7); width:0%; height:100%;float:top;clear : top ;clear:both">
						<div id="pourcentage" style="text-align:right; height:100%; font-size:1.8em;">
							&nbsp;
						</div>
					</div>
				</div>
				<label id="progress_bar_description"></label></br>
				<label id="progress_bar_course"></label>';

$id   = optional_param('id', 0, PARAM_INT);// Course ID
$confirm = optional_param('confirmation', 0, PARAM_TEXT);// Course ID
$courJson = optional_param('cour', 0, PARAM_TEXT);
if($id != NULL){
	$_SESSION['idCourse'] = $id;	
}

if(!isset($_SESSION['idCourse'])){
	$_SESSION['idCourse'] = 1;
}


headerRetrieveCursus();


if($confirm != NULL){
	
	switch($confirm){
		case RetrieveCourseConstante::CONFIRMATION_BACKUP_IMMEDIAT : backup_immediat($courJson,$progressBar); break;
		case RetrieveCourseConstante::CONFIRMATION_USE_CRON : admin_use_cron($courJson) ; break;
		default: redirect($PAGE->url);break;
	}
	
	
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


function admin_use_cron($courJson){
	global $CFG,$USER;
	if(isset($courJson)){
		$cour = json_decode($courJson);
		$db = new ManageDB();
		foreach ($cour as $idCourse){
			$shortname =  $db->getShortnameCourse($idCourse);
			if($shortname != NULL){
				$nextShortname = nextShortname($shortname);
				$db->addCourse_cron($idCourse, $USER->id , $nextShortname);
				$db->addCourse_retrievecourse($shortname , $nextShortname , $CFG->temp , $idCourse ,false , true );
			}
		}
	}
}


/**
 * Permet de lancer le backup/restore pour une liste de cour.
 * Permet également d'initialiser la barre de progression.
 * @param json $courJson
 * Liste des cours dont il faut faire le backup.
 */
function backup_immediat($courJson, $progressBar){
	global $USER,$PAGE,$CFG;
	if(isset($courJson)){
		echo $progressBar;
		$db = new ManageDB();
		$cour = json_decode($courJson);
		$service = new RetrieveCourseService(null , $USER->id , null);
		$indice = 0;
		$nbElemRestore = count($cour) ;
		$service->step =1/(count($nbElemRestore)*2);
		foreach ($cour as $idCourse){
			$shortname =  $db->getShortnameCourse($idCourse);
			$nextShortname = nextShortname($shortname);
			progression($indice);
			$service->currentProgress = $indice;
			if($shortname != NULL){
				$service->setCourse($idCourse);
				$service->setNextShortName($nextShortname);
				$service->runService($nbElemRestore);
				$db->addCourse_retrievecourse($shortname , $nextShortname , $CFG->temp , $idCourse);
			}
			$indice += 100 /(count($nbElemRestore)*2 );
		}
	}
}



?>



