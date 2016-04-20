<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class		gevCancelPastEmptyTrainingsJob
*
* CronJob:	Cancelles past trainings without bookings
*
* @author Denis Klöpfer
* @version $Id$
*/

require_once "Services/Cron/classes/class.ilCronManager.php";
require_once "Services/Cron/classes/class.ilCronJob.php";
require_once "Services/Cron/classes/class.ilCronJobResult.php";

class ReportsMasterJob extends ilCronJob {

	private $gIldb;
	private $gLog;
	private $gLng;
	private $gRbacadmin;

	public function __construct() {
		global $ilDB, $ilLog, $lng, $rbacadmin;
		$this->gIldb = $ilDB;
		$this->gLog = $ilLog;
		$this->gLng = $lng;
		$this->gRbacadmin = $rbacadmin;
	}

	/**
	 * Implementation of abstract function from ilCronJob
	 * @return	string
	 */
	public function getId() {
		return "report_master";
	}
	
	/**
	 * Implementation of abstract function from ilCronJob
	 * @return	string
	 */
	public function getTitle() {
		return "Führt täglich CRON-Jobs auf Reports aus.";
	}

	/**
	 * Implementation of abstract function from ilCronJob
	 * @return	bool
	 */
	public function hasAutoActivation() {
		return true;
	}
	
	/**
	 * Implementation of abstract function from ilCronJob
	 * @return	bool
	 */
	public function hasFlexibleSchedule() {
		return false;
	}
	
	/**
	 * Implementation of abstract function from ilCronJob
	 * @return	int
	 */
	public function getDefaultScheduleType() {
		return ilCronJob::SCHEDULE_TYPE_DAILY;
	}
	
	/**
	 * Implementation of abstract function from ilCronJob
	 * @return	int
	 */
	public function getDefaultScheduleValue() {
		return 1;
	}

	/**
	 * Implementation of abstract function from ilCronJob
	 * @return	ilCronJobResult
	 */
	public function run() {
		$cron_result = new ilCronJobResult();
		$this->gLog->write("### ReportMaster: STARTING ###");
		ilCronManager::ping($this->getId());
		$cron_result->setStatus(ilCronJobResult::STATUS_OK);
		return $cron_result;
	}
}