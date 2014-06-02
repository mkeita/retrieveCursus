<?php
defined ( 'MOODLE_INTERNAL' ) || die ();
function redirection($url) {
	echo '<script type="text/javascript">';
	echo 'window.location.href="' . $url . '";';
	echo '</script>';
}

/**
 * Permet d'initialiser certaine variable du fichier php.ini
 */
function initialize_php_ini() {
	global $CFG;
	@ignore_user_abort ( true );
	@set_time_limit ( 0 );
	raise_memory_limit ( MEMORY_HUGE );
	
	if (! isset ( $CFG->memory_limit ) || $CFG->memory_limit != NULL) {
		ini_set ( "memory_limit", $CFG->memory_limit );
	} else {
		ini_set ( "memory_limit", "-1" );
	}
	
	ini_set ( "post_max_size", $CFG->post_max_size );
	ini_set ( "upload_max_filesize", $CFG->upload_max_filesize );
	ini_set ( "max_execution_time", $CFG->max_execution_time );
	ini_set ( "max_input_time", $CFG->max_input_time );
	
	// echo 'post_max_size = ' . ini_get('post_max_size') . "</br>";
	// echo 'upload_max_filesize = ' . ini_get('upload_max_filesize') . "</br>";
	// echo 'memory_limit = ' . ini_get('memory_limit') . "</br>";
	// echo 'max_execution_time = ' . ini_get('max_execution_time') . "</br>";
	// echo 'max_input_time = ' . ini_get('max_input_time') . "</br>";
}
function message($msg, $url = '../..') {
	global $OUTPUT;
	?>
<div id="message"
	style="width: 60%; margin-left: auto; margin-right: auto; border-width: 1px; border-style: solid; background-color: #00000; border-color: #BBB; text-align: left">
					<?php
	
echo $msg . '</br></br></br>';
	if ($url != null) {
		echo $OUTPUT->continue_button ( $url );
	}
	
	?>
		</div>
<?php
}
function getNamePlugin() {
	global $CFG;
	return 'Copie ' . (substr ( $CFG->temp, 0, $CFG->tempYearOne ) + 1) . '-' . (substr ( $CFG->temp, - $CFG->tempYearTwo ) + 1) . ' du cour';
}

/**
 * Cette fonction permet de crée le shortname de l'année académique suivante.
 * Cette fonction part du principe que les derniers caractéres représentent l' année académique.
 * 
 * @param string $course        	
 * @return Le shortname du cour pour l'année académique suivante.
 */
function nextShortname($course) {
	global $CFG;
	$tailleYearOne = $CFG->tempYearOne;
	$tailleYearTwo = $CFG->tempYearTwo;
	$tailleTemp = $tailleYearOne + $tailleYearTwo;
	
	$temp = substr ( $course, - $tailleTemp );
	$yearOne = substr ( $temp, 0, $tailleYearOne );
	$yearTwo = substr ( $temp, - $tailleYearTwo );
	$yearOne += 1;
	$yearTwo = ($yearTwo + 1) % 100;
	$mnemo = substr ( $course, 0, strlen ( $course ) - $tailleTemp );
	$newShortname = $mnemo . $yearOne . $yearTwo;
	return $newShortname;
}
function progression($indice) {
	echo "<script>";
	echo "document.getElementById('pourcentage').innerHTML='$indice%';";
	echo "document.getElementById('barre').style.width='$indice%';";
	echo "</script>";
	
	ob_flush ();
	flush ();
}