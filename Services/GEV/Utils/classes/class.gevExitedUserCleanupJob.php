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
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevNAUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
		require_once("Services/GEV/WBD/classes/class.gevWBD.php");
		
		global $ilLog, $ilDB;
		
		$gev_settings = gevSettings::getInstance();
		$exit_udf_field_id = $gev_settings->getUDFFieldId(gevSettings::USR_UDF_EXIT_DATE);
		$orgu_tree = ilObjOrgUnitTree::_getInstance();
		$exit_orgu_ref_id = $gev_settings->getOrgUnitExited();
		$exit_orgu_obj_id = gevObjectUtils::getObjId($exit_orgu_ref_id);
		$exit_orgu_utils = gevOrgUnitUtils::getInstance($exit_orgu_obj_id);
		$na_utils = gevNAUtils::getInstance();
		$na_no_adviser_orgu_utils = gevOrgUnitUtils::getInstance($gev_settings->getNAPOUNoAdviserUnitId());
		
		$res = $ilDB->query("SELECT ud.usr_id "
						   ."  FROM usr_data ud"
						   ."  JOIN udf_text udf "
						   ."    ON udf.usr_id = ud.usr_id"
						   ."   AND field_id = ".$ilDB->quote($exit_udf_field_id, "integer")
						   ." WHERE active = 1 "
						   ."   AND udf.value < CURDATE()"
						   );
		
		// I know, this is not timezone safe.
		$now = @date("Y-m-d");
		
		$cron_result = new ilCronJobResult();
		
		while ($rec = $ilDB->fetchAssoc($res)) {
			$usr_id = $rec["usr_id"];
			$usr = new ilObjUser($usr_id);
			$usr_utils = gevUserUtils::getInstance($usr_id);
			$wbd_utils = gevWBD::getInstance($usr_id);
			
			foreach ($usr_utils->getBookedAndWaitingCourses() as $crs_id) {
				$crs_utils = gevCourseUtils::getInstance($crs_id);
				$start_date = $crs_utils->getStartDate();
				if ($start_date === null) {
					$ilLog->write("gevExitedUserCleanupJob: User $usr_id was not removed from training $crs_id, since"
								 ." the start date of the training could not be determined.");
					continue;
				}
				
				if ($start_date->get(IL_CAL_DATE) >= $now) {
					$crs_utils->getBookings()->cancelWithoutCosts($usr_id);
					$mails = new gevCrsAutoMails($crs_id);
					$mails->send("participant_left_corporation", array($usr_id));
					$ilLog->write("gevExitedUserCleanupJob: User $usr_id was canceled from training $crs_id.");
				}
				else {
					$ilLog->write("gevExitedUserCleanupJob: User $usr_id was not removed from training $crs_id, since"
								 ." training start date expired: ".$start_date->get(IL_CAL_DATE)." < ".$now);
				}
			}
			
			$usr->setActive(false);
			$ilLog->write("gevExitedUserCleanupJob: Deactivated user with id $usr_id.");
			
			$orgus = $orgu_tree->getOrgUnitOfUser($usr_id, 0, true);
			foreach ($orgus as $orgu_id) {
				$orgu_utils = gevOrgUnitUtils::getInstance($orgu_id);
				$orgu_utils->deassignUser($usr_id, "Mitarbeiter");
				$orgu_utils->deassignUser($usr_id, "Vorgesetzter");
				$ilLog->write("gevExitedUserCleanupJob: Removed user with id $usr_id from OrgUnit with id $orgu_id.");
			}
			
			$exit_orgu_utils->assignUser($usr_id, "Mitarbeiter");
			$ilLog->write("gevExitedUserCleanupJob: Moved user with id $usr_id to exit-OrgUnit.");
			
			try {
				$nas = $na_utils->getNAsOf($usr_id);
				foreach ($nas as $na) {
					$na_no_adviser_orgu_utils->assignUser($na, "Mitarbeiter");
					$ilLog->write("gevExitedUserCleanupJob: Moved na $na of user $usr_id to no-adviser-OrgUnit.");
				}
				if (count($nas) > 0) {
					$ilLog->write("gevExitedUserCleanupJob: Removed NA-OrgUnit of $usr_id.");
					$na_utils->removeNAOrgUnitOf($usr_id);
				}
			}
			catch (Exception $e) {
				$ilLog->write("gevExitedUserCleanupJob: ".$e);
			}

			try {
				if($wbd_utils->getWBDTPType() == gevWBD::WBD_TP_SERVICE) {
					$wbd_utils->setNextWBDAction(gevWBD::USR_WBD_NEXT_ACTION_RELEASE);
					$ilLog->write("gevExitedUserCleanupJob: Set next wbd action to release for user: ".$usr_id.".");
				}
			}catch (Exception $e) {
				$ilLog->write("gevExitedUserCleanupJob: ".$e);
			}

			
			//update user and create a history entry
			$usr->read();
			$usr->setActive(false);
			$usr->update();
			
			// i'm alive!
			ilCronManager::ping($this->getId());
		}
		
		$ilLog->write("gevExitedUserCleanupJob: purging empty na-org units.");
		
		$na_base_utils = gevOrgUnitUtils::getInstance($gev_settings->getNAPOUBaseUnitId());
		$no_adviser_ref_id = gevObjectUtils::getRefId($gev_settings->getNAPOUNoAdviserUnitId());
		$template_ref_id = gevObjectUtils::getRefId($gev_settings->getNAPOUTemplateUnitId());
		$na_base_utils->purgeEmptyChildren(2, array($no_adviser_ref_id, $template_ref_id));

		$cron_result->setStatus(ilCronJobResult::STATUS_OK);
		return $cron_result;
	}
}

?>