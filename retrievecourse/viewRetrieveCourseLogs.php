<?php

require('../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once ($CFG->libdir.'/accesslib.php');
require_once (__DIR__ . '/classes/model/ManageRetrieveCourseDB.php');
require_once (__DIR__ . '/outils.php');

defined('MOODLE_INTERNAL') || die;

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

echo '<link rel="stylesheet" href="lib/DataTables-1.9.4/media/css/jquery.dataTables.css" type="text/css"> ';

headerRetrieveCourse();

$retrievecoursedb = new ManageRetrieveCourseDB();
$data = $retrievecoursedb->getRetrieveCourse();
$nameColumn = $retrievecoursedb->retrieveNameColumn();

if($data == NULL){
	$msg =  utf8_encode('Le plugin n\'a pas encore été utilisé!');
	message($msg);
}else{
	echo '<table id="tableRetrieveCourse">';
	echo '<thead>';
	echo '<tr>';
	foreach ($nameColumn as $key=>$name){
		echo '<th> ' . $name  .   '</th>';
	}
	echo '<th> Supprimer </th>';
	echo '</tr>';
	echo '</thead>';
	foreach ($data as $object){
		echo '<tr>';
		foreach ($object as $key => $value){
			if(strcmp($key, "date") == 0 ){
				echo ($value == NULL) ? '<td> ' . $value . '</td>' : '<td> ' . date("d/m/y H:i:s",$value) . '</td>';
			}else if(strcmp($key, "flag_newcourse") == 0 || strcmp($key, "flag_use_cron") == 0 || strcmp($key, "flag_wait_cron_execute") == 0 ){
				$val = ($value == 0) ? "false" : "true";
				echo '<td> ' . $val . '</td>';
			}else{
				echo '<td> ' . $value . '</td>';
			}
				
		}
		$img = '<img src="/img/ico_remove.jpg"  />';
		echo '<td> ' . $img  . '</td>';
		echo '</tr>';
	}
	echo '</table>';
}

echo '<script type="text/javascript" src="lib/DataTables-1.9.4/media/js/jquery.dataTables.min.js"></script> ';

echo'<script type="text/javascript">
$(document).ready(function() {
	$(\'#tableRetrieveCourse\').dataTable();
} );</script>';


echo $OUTPUT->footer();
die();


function headerRetrieveCourse(){
	global $PAGE , $OUTPUT ;
	require_login($PAGE->course->id);
	$PAGE->set_pagelayout('standard');
	$PAGE->set_title( 'Retrieve Course Logs');
	$PAGE->set_heading('Retrieve Course Logs');
	$PAGE->set_url('/retrieveCourse/retrievecourse/viewRetrieveCourseLogs.php');
	echo $OUTPUT->header();
}
