<?php

class RetrieveCourseConstante {
	
	
	//Confirmation
	const CONFIRMATION_BACKUP_IMMEDIAT= 10;
	const CONFIRMATION_USE_CRON = 20;
	const CONFIRMATION_NEW_COURSE = 30;
	const CONFIRMATION_BACKUP_TEACHER = 40;	

	//CronService
	const CRON_NO_EXECUTE = 0;
	const CRON_EXECUTE = 1;
	
	
	const STATUS_WAITING = 'waiting';
	const STATUS_EXECUTE = 'execute';
	const STATUS_ERROR = 'error';
	
	
	//RetrieveCourseService
	const USE_CRON = 50;
	const USE_BACKUP_IMMEDIATELLY = 60;
	
}