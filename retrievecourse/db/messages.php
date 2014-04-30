<?php

defined('MOODLE_INTERNAL') || die();
$messageproviders = array (
		// Notify teacher that a student has submitted a quiz attempt
		'submission' => array (
				'capability'  => 'mod/quiz:emailnotifysubmission'
		),
		// Confirm a student's quiz attempt
		'confirmation' => array (
				'capability'  => 'mod/quiz:emailconfirmsubmission'
		)
);