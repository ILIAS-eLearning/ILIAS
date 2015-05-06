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
		self::updateDBVToBDAssignment();
		self::updateAgentToDBVAssignment();
		
		$cron_result = new ilCronJobResult();
		$cron_result->setStatus(ilCronJobResult::STATUS_OK);
		return $cron_result;
	}
	
	static protected function getUVGOrguRefIds() {
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevUVGOrgUnits.php");
		
		$uvg_orgus = gevUVGOrgUnits::getInstance();
		$base_ref_id = $uvg_orgus->getBaseRefId();
		$uvg_orgu_ids = gevOrgUnitUtils::getAllChildren(array($base_ref_id));
		
		return array_map(function($ids) { return $ids["ref_id"]; }, $uvg_orgu_ids);
	}
	
	static public function updateDBVToBDAssignment() {
		require_once("Services/GEV/Utils/classes/class.gevUVGOrgUnits.php");
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
		
		$uvg_orgus = gevUVGOrgUnits::getInstance();
		$uvg_orgu_ref_ids = self::getUVGOrguRefIds();
		foreach ($uvg_orgu_ref_ids as $ref_id) {
			$orgu = new ilObjOrgUnit($ref_id);
			try {
				$uvg_orgus->moveToBDFromIV($orgu);
			} catch (ilPersonalOrgUnitsException $exception) {
				// This could happen as there are org units without owners
				// (for BDs and cpool) beneath the base.
			}
		}
	}
	
	static public function updateAgentToDBVAssignment() {
		require_once("Services/GEV/Utils/classes/class.gevDBVUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		
		$uvg_orgu_ref_ids = self::getUVGOrguRefIds();
		$vps = gevOrgUnitUtils::getEmployeesIn($uvg_orgu_ref_ids);
		$dbv_utils = gevDBVUtils::getInstance();
		foreach ($vps as $vp) {
			$dbv_utils->updateUsersDBVAssignmentsByShadowDB($vp);
		}
	}

}

?>