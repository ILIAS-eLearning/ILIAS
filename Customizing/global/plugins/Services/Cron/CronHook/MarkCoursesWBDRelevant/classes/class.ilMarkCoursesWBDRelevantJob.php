<?php<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class		ilMarkCoursesWBDRelevantJob
*
* CronJob:	MArks courses as wbd relevant if the end date is >= th date entered in udf field "WBD Punkte nachmelden ab"
*
* @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
* @version $Id$
*/

require_once("Services/Cron/classes/class.ilCronManager.php");
require_once("Services/Cron/classes/class.ilCronJob.php");
require_once("Services/Cron/classes/class.ilCronJobResult.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/GEV/WBD/classes/class.gevWBD.php");

class ilMarkCoursesWBDRelevantJob extends ilCronJob {
	const CREATOR_ID = "-333";

	private $gIldb;
	private $gLog;
	private $gLng;
	private $gRbacadmin;

	public function __construct() {
		global $ilDB, $ilLog, $lng, $rbacadmin;

		$this->gDB = $ilDB;
		$this->gLog = $ilLog;
		$this->gLng = $lng;
		$this->gRbacadmin = $rbacadmin;
		$this->settings = gevSettings::getInstance();
	}

	/**
	 * Implementation of abstract function from ilCronJob
	 * @return	string
	 */
	public function getId() {
		return "gev_mark_courses_wbd_relevant";
	}
	
	/**
	 * Implementation of abstract function from ilCronJob
	 * @return	string
	 */
	public function getTitle() {
		return "Setzt Trainings auf WBD relevant.";
	}

	public function getDescription() {
		return "Wenn beim Benutzer das UDF Feld 'WBD Punkte nachmelden ab' einen Wert besitzt, werden alle Trainings deren Enddatum >= ist auf WBD relevant gestzt";
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
		$this->gLog->write("### ilMarkCoursesWBDRelevantJob: STARTING ###");
		$user_vals = $this->getUserWithEnteredDate();

		if(count($user_vals) == 0) {
			$this->gLog->write("### ilMarkCoursesWBDRelevantJob: no user found ###");
		}

		foreach ($user_vals as $user_val) {
			ilCronManager::ping($this->getId());
			$this->gLog->write("### ilMarkCoursesWBDRelevantJob: set for: $user_val ###");
			$this->updateTrainings($user_val);
			ilCronManager::ping($this->getId());
		}

		$this->gLog->write("### ilMarkCoursesWBDRelevantJob: FINISHED ###");

		$cron_result->setStatus(ilCronJobResult::STATUS_OK);
		return $cron_result;
	}

	/**
	 * Get all user_ids where udf field has been a value
	 * @return [int] $ret
	 */
	private function getUserWithEnteredDate() {
		$ret = array();
		$field_id = $this->settings->getUDFFieldId(gevWBD::USR_WBD_REPORT_POINTS_FROM);

		$select = "SELECT ud.usr_id, udt.value\n"
				." FROM usr_data ud\n"
				." JOIN udf_text udt ON udt.usr_id = ud.usr_id\n"
				." WHERE udt.value IS NOT NULL\n"
				."    AND udt.field_id = ".$this->gDB->quote($field_id, "integer");

		$res = $this->gDB->query($select);
		while($rec = $this->gDB->fetchAssoc($res)) {
			$ret[] = array("user_id" => $rec["usr_id"]
						, "value" => $rec["value"]);
			ilCronManager::ping($this->getId());
		}
		return $ret;
	}

	/**
	 * @param int $user_id
	 */
	private function updateTrainings($user_val) {
		$wbd = gevWBD::getInstance($user_val["user_id"]);

		if($user_val["value"]) {
			$this->gLog->write("### ilMarkCoursesWBDRelevantJob:updateTrainings ###");
				$wbd->updateHistUserCourseRows($user_val["value"], self::CREATOR_ID);
		}
	}
}