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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Links and settings
 *
 * Contains settings used by logs report.
 *
 * @package    report_retrievecursus
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
// just a link to course report


$ADMIN->add('reports', new admin_category('retrieveCourse', 'Retrieve Course'));


$ADMIN->add('retrieveCourse', new admin_externalpage('reportretrievecourse', $CFG->namePlugin , "$CFG->wwwroot/report/retrievecourse/index.php", 'report/log:view'));

$ADMIN->add('retrieveCourse', new admin_externalpage('reportretrievecoursecron', 'Cron Logs', "$CFG->wwwroot/report/retrievecourse/viewCronTasks.php", 'report/log:view'));

$settings = null;


// Create a page for automated backups configuration and defaults.
$temp = new admin_settingpage('retrievecourse_settings', get_string('retrievecourse_config','report_retrievecourse'));

$temp->add( new admin_setting_configcheckbox('visibilite_plugin', 'visibilite_plugin', '', 1));

$namePlugin = 'Copie '. (substr($CFG->temp,0,$CFG->tempYearOne)+1) .'-'. (substr($CFG->temp,-$CFG->tempYearTwo)+1) .' du cours';
$CFG->namePlugin = $namePlugin;

// $CFG->tempSize = $CFG->tempYearOne + $CFG->tempYearTwo;
// var_dump($CFG->tempSize);

// $temp->add(new admin_setting_configtext('namePlugin', 'Nom du plugin',
// 		get_string('retrievecourse_description', 'report_retrievecourse'), $namePlugin, PARAM_TEXT));

$temp->add(new admin_setting_configtext('tempYearOne', get_string('tempYearOne', 'report_retrievecourse'),
		get_string('retrievecourse_description', 'report_retrievecourse'), 4, PARAM_INT));

$temp->add(new admin_setting_configtext('tempYearTwo', get_string('tempYearTwo', 'report_retrievecourse'),
		get_string('retrievecourse_description', 'report_retrievecourse'), 2, PARAM_INT));


$choice1 = (substr((date('Y')-2), -$CFG->tempYearOne) . substr((date('Y')-1), -$CFG->tempYearTwo) );
$choice2 = substr((date('Y')-1), -$CFG->tempYearOne) .substr(date('Y'), -$CFG->tempYearTwo);
$choice3 = substr((date('Y')), -$CFG->tempYearOne) .substr((date('Y')+1), -$CFG->tempYearTwo);
$choice4 =  substr((date('Y')+1), -$CFG->tempYearOne) .substr((date('Y')+2), -$CFG->tempYearTwo);


$choices = array(
	$choice1 => $choice1,
	$choice2 => $choice2 ,
	$choice3 => $choice3 ,
	$choice4 => $choice4
);

$temp->add(new admin_setting_configselect('temp', 'Valeur du temp', '',$choice2, $choices));

$temp->add(new admin_setting_configtime('cron_heure_debut', 'cron_minute_debut', 'Heure debut', '', array('h'=>18,'m'=>30)));
$temp->add(new admin_setting_configtime('cron_heure_fin', 'cron_minute_fin', 'Heure fin', '', array('h'=>6,'m'=>30)));

$temp->add(new admin_setting_configtext('nbTentativeMax', get_string('nbTentativeMax', 'report_retrievecourse'),
		get_string('retrievecourse_description', 'report_retrievecourse'), 2, PARAM_INT));

$temp->add(new admin_setting_configtext('idAdminUser', get_string('adminUser', 'report_retrievecourse'),
		get_string('retrievecourse_description', 'report_retrievecourse'), 2, PARAM_INT));

$ADMIN->add('retrieveCourse', $temp);

