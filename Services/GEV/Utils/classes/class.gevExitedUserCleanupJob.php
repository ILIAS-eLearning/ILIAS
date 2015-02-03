<?php

require_once("Services/Cron/classes/class.ilCronManager.php");
require_once("Services/Cron/classes/class.ilCronJob.php");
require_once("Services/Cron/classes/class.ilCronJobResult.php");

class gevExitedUserCleanupJob extends ilCronJob {
	public function getId() {
		return "gev_exited_user_cleanup";
	}
	
	public function getTitle() {
		return "Ausgetretene Benutzer bereinigen";
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
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		
		global $ilLog, $ilDB;
		
		$exit_udf_field_id = gevSettings::getInstance()->getUDFFieldId(gevSettings::ORG_UNIT_EXITED);
		
		$res = $ilDB->query("SELECT ud.usr_id "
						   ."  FROM usr_data ud"
						   ."  JOIN udf_text udf "
						   ."    ON udf.usr_id = ud.usr_id"
						   ."   AND field_id = ".$ilDB->quote($exit_udf_field_id, "integer")
						   ." WHERE active = 1 "
						   ."   AND udf.value > CURDATE()"
						   );
		
		$orgu_tree = ilObjOrgUnitTree::getInstance();
		
		while ($rec = $ilDB->fetchAssoc($res)) {
			$usr_id = $res["usr_id"];
			$usr = new ilObjUser($usr_id);
			
			$usr->setActive(false);
			$usr->update();
			
			$ilLog->write("gevExitedUserCleanupJob: Deactivated user with id $usr_id.");
			
			$orgus = $orgu_tree->getOrgUnitOfUser($usr_id, 0, true);
			foreach ($orgus as $orgu_id) {
				$orgu_utils = gevOrgUnitUtils::getInstance($orgu_id);
				$orgu_utils->deassignUser($usr_id, "Mitarbeiter");
				$orgu_utils->deassignUser($usr_id, "Vorgesetzter");
				$ilLog->write("gevExitedUserCleanupJob: Removed user with id $usr_id from OrgUnit with id $orgu_id.");
			}
			
			// i'm alive!
			ilCronManager::ping($this->getId());
		}

		$cron_result->setStatus(ilCronJobResult::STATUS_OK);
		return $cron_result;
	}
}

?>