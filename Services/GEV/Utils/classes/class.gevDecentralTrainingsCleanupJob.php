<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class gevDecentralTrainingsCleanupJob
*
* CronJob: deletes unnecessary decentral trainings accoding to definition t_begin > now + 2 a 
*
* @author Denis Klöpfer
* @version $Id$
*/

require_once "Services/Cron/classes/class.ilCronManager.php";
require_once "Services/Cron/classes/class.ilCronJob.php";
require_once "Services/Cron/classes/class.ilCronJobResult.php";

require_once "Services/GEV/Utils/classes/class.gevCourseUtils.php";
require_once "Services/CourseBooking/classes/class.ilCourseBooking.php";
require_once "Modules/Course/classes/class.ilObjCourse.php";
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");


class gevDecentralTrainingsCleanupJob extends ilCronJob {
	public function getId() {
		return "gev_decentral_trainings_cleanup";
	}
	
	public function getTitle() {
		return "Löscht dezentrale Trainings die außerhalb der nächsten 2 Jahre stattfinden oder kein Datum gesetzt haben";
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
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

		
		global $ilLog, $ilDB;
		$cron_result = new ilCronJobResult();

		$settings = gevSettings::getInstance();
		$in_two_years = date("Y-m-d",mktime(0,0,0,date("m"),date("d"),date("Y")+2));

		$sql = 	"SELECT ore2.ref_id, od2.obj_id "
				."	FROM object_data od "
				."	JOIN object_reference ore "
				."		ON od.obj_id = ore.obj_id "
				."	JOIN tree t1 "
				."		ON t1.child = ore.ref_id "
				."	JOIN tree t2 "
				."		ON t1.lft < t2.lft AND t1.rgt > t2.rgt "
				."	JOIN object_reference ore2 "
				."		ON t2.child = ore2.ref_id "
				."	JOIN object_data od2 "
				."		ON ore2.obj_id = od2.obj_id "
				."	LEFT JOIN adv_md_values_date amv "
				."		ON amv.obj_id = od2.obj_id "
				."			AND amv.field_id = ".$ilDB->quote($settings->getAMDFieldId(gevSettings::CRS_AMD_START_DATE),"integer")
				."	JOIN adv_md_values_text amt "
				."		ON amt.obj_id = od2.obj_id AND amt.value = 'Nein' "
				."			AND amt.field_id = ".$ilDB->quote($settings->getAMDFieldId(gevSettings::CRS_AMD_IS_TEMPLATE),"integer")		
				."	WHERE od.type = 'cat' "
				."		AND (amv.value > ".$ilDB->quote($in_two_years,"date")." OR amv.value IS NULL) "
				."		AND od.title = 'Dezentrale Trainings' "
				."		AND od2.type = 'crs' "
				."		AND ore.deleted IS NULL "
				."		AND ore2.deleted IS NULL ";

		$res = $ilDB->query($sql);
		while($rec = $ilDB->fetchAssoc($res)) {
			$ilLog->write("cron job  DecentralTriainingCleanup: treating crs ".$rec["obj_id"]);

			$crs_utils = gevCourseUtils::getInstance($rec["obj_id"]);

			try {
				$ilLog->write("canceling crs ".$rec["obj_id"]);
				$crs_utils->cancel();
			} catch(Exception $e) {
				$ilLog->write($e->getMessage());
			}

			$crs = new ilObjCourse($rec["obj_id"], false);

			try {
				$ilLog->write("deleting crs ".$rec["obj_id"]);
				$crs->delete();
			} catch(Exception $e) {
				$ilLog->write($e->getMessage());
			}

			$ilLog->write("cron job  DecentralTriainingCleanup: crs ".$rec["obj_id"]." done");
			ilCronManager::ping($this->getId());
		}

		$cron_result->setStatus(ilCronJobResult::STATUS_OK);
		return $cron_result;

	}
}

/*SELECT od2.title, od2.type
FROM object_data od
JOIN object_reference ore ON od.obj_id = ore.obj_id
JOIN tree t1 ON t1.child = ore.ref_id
JOIN tree t2 ON t1.lft < t2.lft
AND t1.rgt > t2.rgt
JOIN object_reference ore2 ON t2.child = ore2.ref_id
JOIN object_data od2 ON ore2.obj_id = od2.obj_id
WHERE od.type = 'cat'
AND od.title = 'Dezentrale Trainings'
AND od2.type = 'crs'
AND ore.deleted IS NULL
AND ore2.deleted IS NULL*/
?>
