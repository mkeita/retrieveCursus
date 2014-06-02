<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Public API of the log report.
 *
 * Defines the APIs used by log reports
 *
 * @package report_log
 * @copyright 1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined ( 'MOODLE_INTERNAL' ) || die ();

/**
 * This function extends the navigation with the report items
 *
 * @param navigation_node $navigation
 *        	The navigation node to extend
 * @param stdClass $course
 *        	The course to object for the report
 * @param stdClass $context
 *        	The context of the course
 */
function report_retrievecourse_extend_navigation_course($navigation, $course, $context) {
	global $CFG, $PAGE, $USER;
	$tempCourse = substr ( $PAGE->course->shortname, - ($CFG->tempYearOne + $CFG->tempYearTwo) );
	if (($CFG->visibilite_plugin && $tempCourse == $CFG->temp) || array_key_exists ( $USER->id, get_admins () )) {
		if (has_capability ( 'report/retrievecourse:view', $context )) {
			$url = new moodle_url ( '/report/retrievecourse/index.php', array (
					'id' => $course->id 
			) );
			$namePlugin = 'Copie ' . (substr ( $CFG->temp, 0, $CFG->tempYearOne ) + 1) . '-' . (substr ( $CFG->temp, - $CFG->tempYearTwo ) + 1) . ' du cours';
			$navigation->add ( $namePlugin, $url, navigation_node::TYPE_SETTING, null, null, new pix_icon ( 'i/report', '' ) );
		}
	}
}
function report_retrievecourse_cron() {
	global $CFG;
	
	mtrace ( "retrievecourse" );
	
	require_once 'classes/service/cronService.php';
	
	$cronService = new cronService ();
	
	if ($cronService->checkLaunchBackupRestore ()) {
		mtrace ( "launch" );
		$cronService->launchBackupRestore ();
	}
}


