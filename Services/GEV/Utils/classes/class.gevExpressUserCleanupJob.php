<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class gevExpressUserCleanupJob
*
* CronJob: deletes ExpressUser from Ilias
*
* @author Stefan Hecken 
* @version $Id$
*/

require_once("Services/Cron/classes/class.ilCronManager.php");
require_once("Services/Cron/classes/class.ilCronJob.php");
require_once("Services/Cron/classes/class.ilCronJobResult.php");


class gevExpressUserCleanupJob extends ilCronJob {
	public function getId() {
		return "gev_express_user_cleanup";
	}
	
	public function getTitle() {
		return "Express Benutzer nach Trainingsende bereinigen";
	}

	public function hasAutoActivation() {
		return true;
	}
	
	public function hasFlexibleSchedule() {
		return false;
	}
	
	public function getDefaultScheduleType() {
		return ilCronJob::SCHEDULE_TYPE_DAILY;
	}
	
	public function getDefaultScheduleValue() {
		return 1;
	}
	
	public function run() {
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

		global $ilLog,$ilDB;

		$gev_settings = gevSettings::getInstance();
		$exit_udf_field_id = $gev_settings->getUDFFieldId(gevSettings::USR_UDF_EXIT_DATE);

		$sql = "SELECT ud.usr_id "
				." FROM usr_data ud "
				." JOIN udf_text udf ON udf.usr_id = ud.usr_id AND udf.field_id = ".$ilDB->quote($exit_udf_field_id, "integer")."" 
				." JOIN rbac_ua rua ON ud.usr_id = rua.usr_id "
				." JOIN object_data od ON od.obj_id = rua.rol_id AND od.title = 'ExpressUser' "
				." WHERE ud.active = 1 AND udf.value < CURDATE()";

		$res = $ilDB->query($sql);

		while ($rec = $ilDB->fetchAssoc($res)) {
			
			$usr_id = $rec["usr_id"];

			$usr = new ilObjUser($usr_id);
			$usr_utils = gevUserUtils::getInstance($usr_id);

			foreach ($usr_utils->getBookedAndWaitingCourses() as $crs_id) {
				$crs_utils = gevCourseUtils::getInstance($crs_id);
				$crs_utils->getBookings()->cancelWithoutCosts($usr_id);
				$ilLog->write("gevExpressUserCleanupJob: User $usr_id was canceled from training $crs_id.");
			}

			$usr->delete();
			$ilLog->write("gevExpressUserCleanupJob: User $usr_id was deleted from system.");

			// i'm alive*/
			ilCronManager::ping($this->getId());
		}
		$cron_result = new ilCronJobResult();
		$cron_result->setStatus(ilCronJobResult::STATUS_OK);
		return $cron_result;
	}

}
?>