<?php

require_once("Services/Cron/classes/class.ilCronManager.php");
require_once("Services/Cron/classes/class.ilCronJob.php");
require_once("Services/Cron/classes/class.ilCronJobResult.php");

class gevUpdateDBVJob extends ilCronJob {
	public function getId() {
		return "gev_update_dbv";
	}
	
	public function getTitle() {
		return "Update der DBV-Struktur";
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
		$this->updateDBVToBDAssignment();
		$this->updateAgentToDBVAssignment();
		$this->updateNoAssignmentVPs();
		$this->purgeEmptyOrgUnits();
		
		$cron_result = new ilCronJobResult();
		$cron_result->setStatus(ilCronJobResult::STATUS_OK);
		return $cron_result;
	}
	
	protected function getUVGOrguRefIds() {
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevUVGOrgUnits.php");
		
		$uvg_orgus = gevUVGOrgUnits::getInstance();
		$base_ref_id = $uvg_orgus->getBaseRefId();
		$uvg_orgu_ids = gevOrgUnitUtils::getAllChildren(array($base_ref_id));
		
		return array_map(function($ids) { return $ids["ref_id"]; }, $uvg_orgu_ids);
	}
	
	public function updateDBVToBDAssignment() {
		require_once("Services/GEV/Utils/classes/class.gevUVGOrgUnits.php");
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
		
		global $ilLog;
		
		$ilLog->write("\n###########################################\n\n"
					 ."gevUpdateDBVJob::updateDBVToBDAssignment\n\n"
					 ."###########################################");
		
		$uvg_orgus = gevUVGOrgUnits::getInstance();
		$uvg_orgu_ref_ids = self::getUVGOrguRefIds();
		foreach ($uvg_orgu_ref_ids as $ref_id) {
			$orgu = new ilObjOrgUnit($ref_id);
			try {
				$uvg_orgus->moveToBDFromIV($orgu);
			} catch (ilPersonalOrgUnitsException $exception) {
				// This could happen as there are org units without owners
				// (for BDs and cpool ) beneath the base.
			}
			
			ilCronManager::ping($this->getId());
		}
	}
	
	public function updateAgentToDBVAssignment() {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevDBVUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		
		global $ilLog;
		
		$ilLog->write("\n###########################################\n\n"
					 ."gevUpdateDBVJob::updateAgentToDBVAssignment\n\n"
					 ."###########################################");
		
		$uvg_orgu_ref_ids = self::getUVGOrguRefIds();
		$vps = gevOrgUnitUtils::getEmployeesIn($uvg_orgu_ref_ids);
		$dbv_utils = gevDBVUtils::getInstance();
		foreach ($vps as $vp) {
			$user_utils = gevUserUtils::getInstance($vp);
			if ($user_utils->hasRoleIn(array("VP"))) {
				$dbv_utils->updateUsersDBVAssignmentsByShadowDB_new($vp);
			}
			
			ilCronManager::ping($this->getId());
		}
	}
	
	public function updateNoAssignmentVPs() {
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		require_once("Services/GEV/Utils/classes/class.gevDBVUtils.php");
		
		global $ilLog;
		
		$ilLog->write("\n###########################################\n\n"
					 ."gevUpdateDBVJob::updateNoAssignmentVPs\n\n"
					 ."###########################################");
		
		$no_assignment_orgu_ref_id = gevSettings::getInstance()->getOrgUnitUnassignedUser();
		$no_assignment_orgu_obj_id = ilObject::_lookupObjectId($no_assignment_orgu_ref_id);
		$no_assignment_orgu_utils = gevOrgUnitUtils::getInstance($no_assignment_orgu_obj_id);
		$no_assignment_users = $no_assignment_orgu_utils->getUsers();
		$dbv_utils = gevDBVUtils::getInstance();
		
		foreach ($no_assignment_users as $user) {
			$user_utils = gevUserUtils::getInstance($user);
			if ($user_utils->hasRoleIn(array("VP"))) {
				$dbv_utils->updateUsersDBVAssignmentsByShadowDB($user);
				$no_assignment_orgu_utils->deassignUser($user, "Mitarbeiter");
			}
			
			ilCronManager::ping($this->getId());
		}
	}
	
	public function purgeEmptyOrgUnits() {
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevUVGOrgUnits.php");
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		
		global $ilLog;
		
		$ilLog->write("\n###########################################\n\n"
					 ."gevUpdateDBVJob::purgeEmptyOrgUnits\n\n"
					 ."###########################################");
		
		$uvg_orgus = gevUVGOrgUnits::getInstance();
		$base_ref_id = $uvg_orgus->getBaseRefId();
		$utils = gevOrgUnitUtils::getInstance(ilObject::_lookupObjectId($base_ref_id));
		$cpool_id = gevSettings::getInstance()->getCPoolUnitId();
		$cpool_ref_id = gevObjectUtils::getRefId($cpool_id);
		$template_ref_id = $uvg_orgus->getTemplateRefId();
		$utils->purgeEmptyChildren(2, array($cpool_ref_id, $template_ref_id));
	}
}

?>