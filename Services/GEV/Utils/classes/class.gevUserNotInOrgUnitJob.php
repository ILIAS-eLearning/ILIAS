<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class gevUserNotInOrgUnitJob
*
* CronJob: Sorting User without Org Unit into specified Org Unit
*
* @author Stefan Hecken 
* @version $Id$
*/

require_once("Services/Cron/classes/class.ilCronManager.php");
require_once("Services/Cron/classes/class.ilCronJob.php");
require_once("Services/Cron/classes/class.ilCronJobResult.php");


class gevUserNotInOrgUnitJob extends ilCronJob {
	public function getId() {
		return "gev_user_not_in_org_unit";
	}
	
	public function getTitle() {
		return "Sortiert die Mitglieder ohne Organisationseinheit";
	}

	/*public function getDescription() {
		return "Die Mitglieder ohne Organisationseinheit werden in eine vorgegebene Organisationseinheit sortiert."
	}*/

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
		$this->moveToNoAssignmentOrgUnit();
		$this->moveFromNoAssignmentOrgUnit();

		$cron_result = new ilCronJobResult();
		$cron_result->setStatus(ilCronJobResult::STATUS_OK);
		return $cron_result;
	}
	
	protected function moveToNoAssignmentOrgUnit() {
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

		global $ilLog, $ilDB;

		$sql = "SELECT ud2.usr_id AS user_id"
					." FROM usr_data ud2 "
					." WHERE ud2.usr_id NOT IN ( "
							." SELECT DISTINCT ud.usr_id "
							." FROM object_data od "
								." JOIN object_reference orf ON orf.obj_id = od.obj_id "
								." JOIN tree tr ON tr.parent = orf.ref_id "
								." JOIN rbac_fa rfa ON rfa.parent = tr.child "
								." JOIN rbac_ua rua ON rua.rol_id = rfa.rol_id "
								." JOIN usr_data ud ON ud.usr_id = rua.usr_id "
							." WHERE od.type = 'orgu' "
							." ) "
						." AND ud2.usr_id NOT IN (6,13)";

		$res = $ilDB->query($sql);

		require_once("Services/GEV/Utils/classes/class.gevExpressLoginUtils.php");
		$expLoginUtils = gevExpressLoginUtils::getInstance();

		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		$gev_settings = gevSettings::getInstance();
		$org_ref_id = $gev_settings->getOrgUnitUnassignedUser();

		$org_id = ilObject::_lookupObjectId($org_ref_id);

		while($rec = $ilDB->fetchAssoc($res)){
			require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
			$user_id = $rec["user_id"];
			$user_utils = gevUserUtils::getInstance($user_id);
			
			$isExpress = $expLoginUtils->isExpressUser($user_id);

			if(!$isExpress) {			
				$user_utils->assignOrgRole($org_id,"Mitarbeiter");				
				$ilLog->write("gevUserNotInOrgUnitJob: User $user_id assigned to org_unit $org_id");
			}
			
			// i'm alive
			ilCronManager::ping($this->getId());
		}
	}
	
	protected function moveFromNoAssignmentOrgUnit() {
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");

		global $ilLog, $ilDB;

		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		$gev_settings = gevSettings::getInstance();
		$org_ref_id = $gev_settings->getOrgUnitUnassignedUser();
		$org_obj_id = ilObject::_lookupObjectId($org_ref_id);
		$unassigned_users = gevOrgUnitUtils::getEmployeesIn(array($org_ref_id));
		
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		$utils = gevOrgUnitUtils::getInstance($a_org_id);
		$utils->deassignUser($this->user_id, $a_role_title);
		
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		$tree = ilObjOrgUnitTree::_getInstance();
	
		foreach ($unassigned_users as $user) {
			if (count($tree->getOrgUnitOfUser($user)) == 1) {
				continue;
			}
			
			$utils->deassignUser($user, "Mitarbeiter");
			$ilLog->write("gevUserNotInOrgUnitJob: User $user_id deassigned from org_unit $org_id");
			
			// i'm alive
			ilCronManager::ping($this->getId());
		}
	}
}
?>
