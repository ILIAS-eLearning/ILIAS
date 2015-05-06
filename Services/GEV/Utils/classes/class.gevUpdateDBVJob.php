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
	
	static public function updateDBVToBDAssignment() {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevUVGOrgUnits.php");
		
		$uvg_orgus = gevUVGOrgUnits::getInstance();
		$base_ref_id = $uvg_orgus->getBaseRefId();
		$uvg_orgu_ids = gevOrgUnitUtils::getAllChildren(array($base_ref_id));
		
		foreach ($uvg_orgu_ids as $ids) {
			$orgu = new ilObjOrgUnit($ids["ref_id"]);
			try {
				$uvg_orgus->moveToBDFromIV($orgu);
			} catch (ilPersonalOrgUnitsException $exception) {
				// This could happen as there are org units without owners
				// (for BDs) beneath the base.
			}
		}
	}
	
	static public function updateAgentToDBVAssignment() {
		// Find out, who is an Agent
		// For every DB: get his current DBVs from IV-data
		//               check, whether his current assignment is correct
		//               if not, correct assignments
	}

}

?>