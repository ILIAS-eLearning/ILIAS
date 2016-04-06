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

class gevCancelPastEmptyTrainingsJob extends ilCronJob {

	private $gIldb;
	private $gLog;

	public function __construct() {
		global $ilDB, $ilLog;
		$this->gIldb = $ilDB;
		$this->gLog = $ilLog;
	}

	/**
	 * Implementation of abstract function from ilCronJob
	 * @return	string
	 */
	public function getId() {
		return "gev_cancel_past_empty_trainings";
	}
	
	/**
	 * Implementation of abstract function from ilCronJob
	 * @return	string
	 */
	public function getTitle() {
		return "Sagt vergangene Trainings ohne Teilnhemer ab.";
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
		$this->gLog->write("### gevCancelPastEmptyTrainingsJob: STARTING ###");
		$crs_ids = $this->getPastEmptyTrainingsIds();
		$cnt = count($crs_ids);

		ilCronManager::ping($this->getId());
		$this->cancelTrainings($crs_ids);

		$this->gLog->write("### gevCancelPastEmptyTrainingsJob: $cnt trainings found - cancelling ###");

		$cron_result->setStatus(ilCronJobResult::STATUS_OK);
		return $cron_result;
	}

	/**
	 * Get relevant training ids of trainings to be cancelled
	 * @return	[int]
	 */
	private function getPastEmptyTrainingsIds() {
		require_once "Services/Calendar/classes/class.ilDateTime.php";
		require_once "Services/GEV/Utils/classes/class.gevSettings.php";
		require_once "Services/ParticipationStatus/classes/class.ilParticipationStatus.php";
		require_once "Services/CourseBooking/classes/class.ilCourseBooking.php";
		$settings = gevSettings::getInstance();
		$return = array();
		
		$date_time = new ilDateTime(time(),IL_CAL_UNIX);
		$yesterday_dt = new ilDateTime($date_time->increment(ilDateTime::DAY,-1),IL_CAL_UNIX);
		$yesterday = $yesterday_dt->get(IL_CAL_DATE);

		$amd_end_date = $settings->getAmdFieldId(gevSettings::CRS_AMD_END_DATE);
		$amd_is_cancelled = $settings->getAMDFieldId(gevSettings::CRS_AMD_IS_CANCELLED);
		$amd_crs_type = $settings->getAMDFieldId(gevSettings::CRS_AMD_TYPE);
		$amd_is_template = $settings->getAMDFieldId(gevSettings::CRS_AMD_IS_TEMPLATE);
		$p_stat_final = ilParticipationStatus::STATE_FINALIZED;
		$b_stat_booked = ilCourseBooking::STATUS_BOOKED;

		$query =
			"SELECT crs.obj_id FROM object_data crs ".PHP_EOL
			."	JOIN adv_md_values_text is_tpl ".PHP_EOL
			."		ON crs.obj_id = is_tpl.obj_id AND is_tpl.field_id = ".$this->gIldb->quote($amd_is_template,'integer').PHP_EOL
			."			AND is_tpl.value = ".$this->gIldb->quote("Nein","text").PHP_EOL
			."	JOIN adv_md_values_date end_date".PHP_EOL
			."		ON crs.obj_id = end_date.obj_id AND end_date.field_id = ".$this->gIldb->quote($amd_end_date,'integer').PHP_EOL
			."			AND end_date.value <= ".$this->gIldb->quote($yesterday,"text").PHP_EOL
			."	JOIN adv_md_values_text crs_type".PHP_EOL
			."		ON crs.obj_id = crs_type.obj_id AND crs_type.field_id = ".$this->gIldb->quote($amd_crs_type,'integer').PHP_EOL
			."			AND ".$this->gIldb->in("crs_type.value",array('Webinar','Präsenztraining'),false,"text").PHP_EOL
			."	LEFT JOIN adv_md_values_text is_cncld".PHP_EOL
			."		ON crs.obj_id = is_cncld.obj_id AND is_cncld.field_id = ".$this->gIldb->quote($amd_is_cancelled,'integer').PHP_EOL
			."	LEFT JOIN crs_pstatus_crs pstatus".PHP_EOL
			."		ON pstatus.crs_id = crs.obj_id AND pstatus.state = ".$this->gIldb->quote($p_stat_final,'integer').PHP_EOL
			."	LEFT JOIN crs_book book".PHP_EOL
			."		ON book.crs_id = crs.obj_id AND book.status = ".$this->gIldb->quote($b_stat_booked,'integer').PHP_EOL
			."	WHERE crs.type = ".$this->gIldb->quote("crs","text").PHP_EOL
			."		AND (is_cncld.value = ".$this->gIldb->quote("Nein","text")." OR is_cncld.value IS NULL) ".PHP_EOL
			."		AND book.status IS NULL AND pstatus.state IS NULL";
		$res = $this->gIldb->query($query);
		while($rec = $this->gIldb->fetchAssoc($res)) {
			$return[] = $rec["obj_id"];
		}
		return $return;
	}

	private function cancelTrainings( array $crs_ids) {
		require_once 'Services/GEV/Utils/classes/class.gevCourseUtils.php';
		foreach($crs_ids as $crs_id) {
			gevCourseUtils::getInstance($crs_id)->cancel();
			$this->gLog->write("### gevCancelPastEmptyTrainingsJob: training $crs_id cancelled ###");

			ilCronManager::ping($this->getId());
		}
	}
}