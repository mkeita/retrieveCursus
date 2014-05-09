<?php

defined('MOODLE_INTERNAL') || die;

function message($msg , $url = '../..'){
	global $OUTPUT;
	?>
		<div id="message"
		style="width: 60%; margin-left: auto; margin-right: auto; border-width: 1px; border-style: solid; background-color: #00000; border-color: #BBB; text-align: left">
					<?php echo $msg . '</br>';
					echo $OUTPUT->continue_button($url);
					?>
		</div>
	<?php 
}

function getNamePlugin(){
	global $CFG;
	return 'Copie '. (substr($CFG->temp,0,$CFG->tempYearOne)+1) .'-'. (substr($CFG->temp,-$CFG->tempYearTwo)+1) .' du cour';
}

/**
 * Cette fonction permet de crée le shortname de l'année académique suivante.
 * Cette fonction part du principe que les derniers caractéres représentent l' année académique.
 * @param string $course
 * @return Le shortname du cour pour l'année académique suivante.
 */
function nextShortname($course){
	global $CFG;
	$tailleYearOne = $CFG->tempYearOne;
	$tailleYearTwo = $CFG->tempYearTwo;
	$tailleTemp = $tailleYearOne + $tailleYearTwo;
	
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