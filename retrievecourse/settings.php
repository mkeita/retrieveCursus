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
 * Links and settings
 *
 * Contains settings used by logs report.
 *
 * @package report_retrievecursus
 * @copyright 1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined ( 'MOODLE_INTERNAL' ) || die ();
// just a link to course report

$ADMIN->add ( 'reports', new admin_category ( 'retrieveCourse', 'Retrieve Course' ) );

$ADMIN->add ( 'retrieveCourse', new admin_externalpage ( 'reportretrievecourse', 'Retrieve Course', "$CFG->wwwroot/report/retrievecourse/index.php", 'report/log:view' ) );

$ADMIN->add ( 'retrieveCourse', new admin_externalpage ( 'reportretrievecoursecron', 'Cron Logs', "$CFG->wwwroot/report/retrievecourse/viewCronTasks.php", 'report/log:view' ) );

$ADMIN->add ( 'retrieveCourse', new admin_externalpage ( 'reportretrievecourselogs', 'Plugin Logs ', "$CFG->wwwroot/report/retrievecourse/viewRetrieveCourseLogs.php", 'report/log:view' ) );

$settings = null;

$temp = new admin_settingpage ( 'retrievecourse_settings', get_string ( 'retrievecourse_config', 'report_retrievecourse' ) );

$temp->add ( new admin_setting_configcheckbox ( 'visibilite_plugin', 'visibilite_plugin', '', 1 ) );

$temp->add ( new admin_setting_configtext ( 'tempYearOne', get_string ( 'tempYearOne', 'report_retrievecourse' ), get_string ( 'retrievecourse_description', 'report_retrievecourse' ), 4, PARAM_INT ) );

$temp->add ( new admin_setting_configtext ( 'tempYearTwo', get_string ( 'tempYearTwo', 'report_retrievecourse' ), get_string ( 'retrievecourse_description', 'report_retrievecourse' ), 2, PARAM_INT ) );

$yearOne = - 4;
$yearTwo = - 2;

if (isset ( $CFG->tempYearOne ) && isset ( $CFG->tempYearTwo )) {
	if ($CFG->tempYearOne != NULL && $CFG->tempYearTwo != NULL) {
		$yearOne = - $CFG->tempYearOne;
		$yearTwo = - $CFG->tempYearTwo;
	}
}

$choice1 = (substr ( (date ( 'Y' ) - 2), $yearOne ) . substr ( (date ( 'Y' ) - 1), $yearTwo ));
$choice2 = substr ( (date ( 'Y' ) - 1), $yearOne ) . substr ( date ( 'Y' ), $yearTwo );
$choice3 = substr ( (date ( 'Y' )), $yearOne ) . substr ( (date ( 'Y' ) + 1), $yearTwo );
$choice4 = substr ( (date ( 'Y' ) + 1), $yearOne ) . substr ( (date ( 'Y' ) + 2), $yearTwo );

$choices = array (
		$choice1 => $choice1,
		$choice2 => $choice2,
		$choice3 => $choice3,
		$choice4 => $choice4 
);

$temp->add ( new admin_setting_configselect ( 'temp', 'Valeur du temp', '', $choice2, $choices ) );

$temp->add ( new admin_setting_configtime ( 'cron_heure_debut', 'cron_minute_debut', 'Heure debut', '', array (
		'h' => 18,
		'm' => 30 
) ) );
$temp->add ( new admin_setting_configtime ( 'cron_heure_fin', 'cron_minute_fin', 'Heure fin', '', array (
		'h' => 6,
		'm' => 30 
) ) );

$temp->add ( new admin_setting_configtext ( 'nbTentativeMax', get_string ( 'nbTentativeMax', 'report_retrievecourse' ), get_string ( 'retrievecourse_description', 'report_retrievecourse' ), 2, PARAM_INT ) );

$temp->add ( new admin_setting_configtext ( 'time_before_update', 'time_before_update', 'Le temps accordé avant la prochaine mise à jour ', 5400, PARAM_INT ) );

$temp->add ( new admin_setting_configtext ( 'post_max_size', get_string ( 'post_max_size', 'report_retrievecourse' ), get_string ( 'retrievecourse_description', 'report_retrievecourse' ), "40G", PARAM_ALPHANUM ) );

$temp->add ( new admin_setting_configtext ( 'memory_limit', get_string ( 'memory_limit', 'report_retrievecourse' ), get_string ( 'retrievecourse_description', 'report_retrievecourse' ), "-1", PARAM_ALPHANUMEXT ) );

$temp->add ( new admin_setting_configtext ( 'upload_max_filesize', get_string ( 'upload_max_filesize', 'report_retrievecourse' ), get_string ( 'retrievecourse_description', 'report_retrievecourse' ), "40G", PARAM_ALPHANUMEXT ) );

$temp->add ( new admin_setting_configtext ( 'max_execution_time', get_string ( 'max_execution_time', 'report_retrievecourse' ), get_string ( 'retrievecourse_description', 'report_retrievecourse' ), "0", PARAM_INT ) );

$temp->add ( new admin_setting_configtext ( 'max_input_time', get_string ( 'max_input_time', 'report_retrievecourse' ), get_string ( 'retrievecourse_description', 'report_retrievecourse' ), "0", PARAM_INT ) );

$ADMIN->add ( 'retrieveCourse', $temp );

