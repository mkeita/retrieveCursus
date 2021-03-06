<?php
require ('../../config.php');
require_once ($CFG->dirroot . '/course/lib.php');
require_once ($CFG->libdir . '/accesslib.php');
require_once (__DIR__ . '/classes/model/ManageDB.php');
require_once (__DIR__ . '/classes/model/ManageRetrieveCourseCronDB.php');
require_once (__DIR__ . '/classes/model/RetrieveCourseConstante.php');
require_once (__DIR__ . '/outils.php');

defined ( 'MOODLE_INTERNAL' ) || die ();

if (! is_siteadmin ()) {
	die ( "Vous n'avez pas les droits n�cessaire pour acc�der � cette page" );
}

$PAGE->requires->jquery ();
$PAGE->requires->jquery_plugin ( 'ui' );
$PAGE->requires->jquery_plugin ( 'ui-css' );

echo '<link rel="stylesheet" href="lib/DataTables-1.9.4/media/css/jquery.dataTables.css" type="text/css"> ';

headerCron ();

$crondb = new ManageRetrieveCourseCronDB ();
$manageDB = new ManageDB ();

$data = $crondb->retrievecourse_cron ();
$nameColumn = $crondb->retrieveNameColumn ();

if ($data == NULL) {
	$msg = utf8_encode ( 'Aucun cours n\'est en attente' );
	message ( $msg );
} else {
	echo '<table id="tableCron">';
	echo '<thead>';
	echo '<tr>';
	foreach ( $nameColumn as $key => $name ) {
		echo '<th> ' . $name . '</th>';
	}
	echo '</tr>';
	echo '</thead>';
	foreach ( $data as $object ) {
		echo '<tr>';
		foreach ( $object as $key => $value ) {
			if (strcmp ( $key, "time_created" ) == 0 || strcmp ( $key, "time_start" ) == 0 || strcmp ( $key, "time_modified" ) == 0) {
				echo ($value == NULL) ? '<td> ' . $value . '</td>' : '<td> ' . date ( "d/m/y H:i:s", $value ) . '</td>';
			} else if (strcmp ( $key, "user" ) == 0) {
				echo '<td> ' . $manageDB->getUserName ( $value ) . '</td>';
			} else {
				echo '<td> ' . $value . '</td>';
			}
		}
		echo '</tr>';
	}
	echo '</table>';
}

// echo '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>';
echo '<script type="text/javascript" src="lib/DataTables-1.9.4/media/js/jquery.dataTables.min.js"></script> ';

echo '<script type="text/javascript">
$(document).ready(function() {
	$(\'#tableCron\').dataTable();
} );</script>';

echo $OUTPUT->footer ();
die ();
function headerCron() {
	global $PAGE, $OUTPUT;
	require_login ( $PAGE->course->id );
	$PAGE->set_pagelayout ( 'standard' );
	$PAGE->set_title ( 'Cron Logs' );
	$PAGE->set_heading ( 'Cron Logs' );
	$PAGE->set_url ( '/report/retrievecourse/viewCronTasks.php' );
	echo $OUTPUT->header ();
}

?>

