<?php

defined('MOODLE_INTERNAL') || die;



function getNamePlugin(){
	global $CFG;
	return 'Copie '. (substr($CFG->temp,0,$CFG->tempYearOne)+1) .'-'. (substr($CFG->temp,-$CFG->tempYearTwo)+1) .' du cour';
}

/**
 * Cette fonction permet de cr�e le shortname de l'ann�e acad�mique suivante.
 * Cette fonction part du principe que les derniers caract�res repr�sentent l' ann�e acad�mique.
 * @param string $course
 * @return Le shortname du cour pour l'ann�e acad�mique suivante.
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