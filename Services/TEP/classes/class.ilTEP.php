<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * TEP application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesTEP
 */
class ilTEP
{
	//
	// CATEGORIES/CALENDARS
	//
	
	/**
	 * Get course calendar
	 * 
	 * @return int
	 */
	public static function getCourseCalendarId()
	{
		$set = new ilSetting("TEP");
		return $set->get("crs_calendar");
	}
	
	/**
	 * Get personal calendar id
	 * 
	 * @param int $a_user_id
	 * @param bool $a_create
	 * @return int
	 */
	public static function getPersonalCalendarId($a_user_id, $a_create = true)
	{
		global $lng;
		
		$prefs = ilObjUser::_getPreferences($a_user_id);
		$cat_id = $prefs["tep_cat_id"];

		if(!$cat_id && 
			(bool)$a_create)
		{
			require_once "Services/Calendar/classes/class.ilCalendarCategory.php";
			$cat = new ilCalendarCategory();
			$cat->setObjId($a_user_id);
			$cat->setType(ilCalendarCategory::TYPE_USR);
			$cat->setTitle($lng->txt("tep_personal_calendar_title"));
			$cat->setColor("#000000");
			$cat_id = $cat->add();

			ilObjUser::_writePref($a_user_id, "tep_cat_id", $cat_id);
		}

		return $cat_id;
	}
	
	
	//
	// FIND
	// 
	
	/**
	 * Get user id by entry id
	 * 
	 * @param int $a_entry_id
	 * @return int
	 */
	public static function findUserByEntryId($a_entry_id)
	{
		global $ilDB;

		require_once "Services/Calendar/classes/class.ilCalendarCategoryAssignments.php";
		$ass = new ilCalendarCategoryAssignments($a_entry_id);
		$cal_cat_id = $ass->getFirstAssignment();					
		if($cal_cat_id)
		{
			if($cal_cat_id == self::getCourseCalendarId())
			{
				return 0;
			}
			
			$set = $ilDB->query("SELECT usr_id FROM usr_pref".
				" WHERE keyword = ".$ilDB->quote("tep_cat_id", "text").
				" AND value = ".$ilDB->quote($cal_cat_id, "text"));
			$row = $ilDB->fetchAssoc($set);
			return $row["usr_id"];
		}
	}
	
	
	//
	// DESCTRUCTOR
	// 
	
	/**
	 * Delete all user entries
	 * 
	 * @param int $a_user_id
	 */
	public static function deleteUser($a_user_id)
	{
		$cat_id = self::getPersonalCalendarId($a_user_id, false);
		if(!$cat_id)
		{
			return;
		}

		// delete category (incl. entries, assignments)
		require_once "Services/Calendar/classes/class.ilCalendarCategory.php";
		$cat = new ilCalendarCategory($cat_id);
		$cat->delete();

		// delete derived
		require_once "Services/TEP/classes/class.ilCalDerivedEntry.php";
		ilCalDerivedEntry::deleteByCategoryId($cat_id);
		
		// delete operation days
		require_once "Services/TEP/classes/class.ilTEPOperationDays.php";
		ilTEPOperationDays::deleteByUserId($a_user_id);
	}
	
	
	//
	// PRESENTATION
	//
	
	/**
	 * Get all tutor names where user has "write" permission
	 * 
	 * @param ilTEPPermissions $a_permissions
	 * @return array
	 */
	public static function getEditableTutorNames(ilTEPPermissions $a_permissions)
	{		
		$res = array();
		
		$tutor_ids = array();
		
		if($a_permissions->isTutor())
		{
			$tutor_ids[]= $a_permissions->getUserId();
		}		
		
		$other_ids = $a_permissions->getEditOtherUserIds();
		if($other_ids)
		{			
			$tutor_ids = array_merge($tutor_ids, $other_ids);
		}
		
		$other_ids = $a_permissions->getEditOtherRecursiveUserIds();		
		if($other_ids)
		{			
			$tutor_ids = array_merge($tutor_ids, $other_ids);
		}
				
		if(sizeof($tutor_ids))
		{
			$res = self::getUserNames($tutor_ids);
		}
		
		return $res;
	}
	
	/**
	 * Get user name presentation
	 *
	 * @param array $a_user_ids
	 * @param bool $a_only_lastnames
	 * @return array
	 */
	public static function getUserNames(array $a_user_ids, $a_only_lastnames = false)
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT usr_id,lastname,firstname".
			" FROM usr_data".
			" WHERE ".$ilDB->in("usr_id", array_unique($a_user_ids), "", "integer").
			" ORDER BY lastname, firstname, usr_id";
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			if(!$a_only_lastnames)
			{
				$res[$row["usr_id"]] = $row["lastname"].", ".$row["firstname"];
			}
			else
			{
				$res[$row["usr_id"]] = $row["lastname"];
			}
		}
		
		return $res;
	}
	
	/**
	 * Get org unit name presentation
	 *
	 * @param array $a_org_ref_ids
	 * @return array
	 */
	public static function getOrgUnitNames(array $a_org_ref_ids)
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT od.title, oref.ref_id".
			" FROM object_data od".
			" JOIN object_reference oref ON (oref.obj_id = od.obj_id)".
			" WHERE ".$ilDB->in("oref.ref_id", array_unique($a_org_ref_ids), "", "integer").
			" AND od.type = ".$ilDB->quote("orgu", "text").
			" ORDER BY title, od.obj_id";
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{			
			$res[$row["ref_id"]] = $row["title"];
		}
		
		return $res;
	}

	/**
	 * Get org unit name presentation
	 *
	 * @param array $a_org_ref_ids
	 * @return array
	 */
	public static function getOrgUnitNamesAndIds(array $a_org_ref_ids)
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT od.title, oref.ref_id, od.obj_id".
			" FROM object_data od".
			" JOIN object_reference oref ON (oref.obj_id = od.obj_id)".
			" WHERE ".$ilDB->in("oref.ref_id", array_unique($a_org_ref_ids), "", "integer").
			" AND od.type = ".$ilDB->quote("orgu", "text").
			" ORDER BY title, od.obj_id";
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{			
			$res[$row["ref_id"]] = array("title"=>$row["title"],"ref_id"=>$row["ref_id"],"obj_id"=>$row["obj_id"]);
		}
		
		return $res;
	}
	
	/**
	 * Get all tutor names where user has "write" permission
	 * 
	 * @param ilTEPPermissions $a_permissions
	 * @return array
	 */
	public static function getViewableOrgUnits(ilTEPPermissions $a_permissions)
	{		
		$org_ids = $a_permissions->getViewOtherOrgUnits();
		$org_ids = array_merge($org_ids, $a_permissions->getViewOtherRecursiveOrgUnits());
		return self::getOrgUnitNames($org_ids);
	}
	
	//gev patch start
	/**
	 * Get all orgunit names where user has ViewOtherOrgunit and -Rekursive permission
	 * filled in two seperated arrays
	 * 
	 * @param ilTEPPermissions $a_permissions
	 * @return array
	 */
	public static function getViewableOrgUnitsSeperated(ilTEPPermissions $a_permissions)
	{		
		$org_ids = $a_permissions->getViewOtherOrgUnits();
		$org_rekru_ids = $a_permissions->getViewOtherRecursiveOrgUnitsOnlyOneTreeLevel();

		$ret = array('view' => self::getOrgUnitNamesAndIds($org_ids), 'view_rekru' => self::getOrgUnitNamesAndIds($org_rekru_ids));
		return $ret;
	}
	//gev patch end

	/**
	 * Get all tutor names (in given org units) where user has "write" permission
	 * 
	 * @param ilTEPPermissions $a_permissions
	 * @param array $a_org_ref_ids
	 * @param bool $a_recursive
	 * @return array
	 */
	public static function getViewableTutorNames(ilTEPPermissions $a_permissions, array $a_org_ref_ids = null, $a_recursive = null)
	{		
		$res = array();
		
		$tutor_ids = array();
		
		// :TODO: always include current user if is tutor?
		if($a_permissions->isTutor())
		{
			$tutor_ids[]= $a_permissions->getUserId();
		}		
		
		$other_ids = $a_permissions->getViewOtherUserIds($a_org_ref_ids);
		if($other_ids)
		{			
			$tutor_ids = array_merge($tutor_ids, $other_ids);
		}
		
		if($a_recursive)
		{
			$other_ids = $a_permissions->getViewOtherRecursiveUserIds($a_org_ref_ids);		
			if($other_ids)
			{			
				$tutor_ids = array_merge($tutor_ids, $other_ids);
			}			
		}
		
		$tutor_ids = array_unique($tutor_ids);
				
		if(sizeof($tutor_ids))
		{
			$res = self::getUserNames($tutor_ids);
		}
		
		return $res;
	}
	
	/**
	 * Get valid appointment weight options
	 * 
	 * @param bool $a_enable_zero
	 * @return array
	 */
	public static function getWeightOptions($a_enable_zero = true)
	{
		$options = array(
			100 => "100%", 
			75 => "75%", 
			50 => "50%", 
			25 => "25%");
		if($a_enable_zero)
		{
			$options[0] = "0%";
		}
		return $options;
	}

	// gev-patch start
	/**
	 * Get a list of org units that could be assigned to a tep entry.
	 */
	public static function getPossibleOrgUnitsForTEPEntries() {
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		$evg = gevOrgUnitUtils::getInstanceByImportId("evg");
		$evg_ref_id = $evg->getRefId();
		$ous = array($evg_ref_id => $evg->getTitle());
		foreach (gevOrgUnitUtils::getAllChildren(array($evg_ref_id)) as $ids) {
			$ous[$ids["ref_id"]] = ilObject::_lookupTitle($ids["obj_id"]);
		}
		
		$uvg = gevOrgUnitUtils::getInstanceByImportId("uvg");
		$ous[$uvg->getRefId()] = $uvg->getTitle();
		foreach ($uvg->getChildren() as $ids) {
			if(ilObject::_lookupObjId($ids["ref_id"]) != gevSettings::getInstance()->getDBVPOUTemplateUnitId()) {
				$ous[$ids["ref_id"]] = ilObject::_lookupTitle($ids["obj_id"]);
				$orgu = gevOrgUnitUtils::getInstance(ilObject::_lookupObjId($ids["ref_id"]));

				$childs = $orgu->getOrgUnitsOneTreeLevelBelow();
				$childs = array_map(function($v) { return $v["ref_id"];}, $childs);

				if(!empty($childs)) {
					foreach (gevOrgUnitUtils::getAllChildren($childs) as $child) {
						$ous[$child["ref_id"]] = ilObject::_lookupTitle($child["obj_id"]);
					}
				}
			}
		}

		$base = gevOrgUnitUtils::getInstanceByImportId("gev_base");
		$base_ref_id = $base->getRefId();
		
		return array( "orgu_ref_ids" => $ous
					, "root_ref_id" => $base_ref_id
					);
	}
	
	public static function getPossibleOrgUnitsForTEPEntriesSeparated() {
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		$evg = gevOrgUnitUtils::getInstanceByImportId("evg");
		$uvg = gevOrgUnitUtils::getInstanceByImportId("uvg");
		
		return array( "view" => self::getOrgUnitNamesAndIds(array($uvg->getRefId()))
					, "view_rekru" => self::getOrgUnitNamesAndIds(array($evg->getRefId()))
					);
	}

	public static function getPossibleOrgUnitsForDecentralTrainingEntriesSeparated() {
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		$evg = gevOrgUnitUtils::getInstanceByImportId("evg");
		$uvg = gevOrgUnitUtils::getInstanceByImportId("uvg");
		
		return array( "view" => array()
					, "view_rekru" => self::getOrgUnitNamesAndIds(array($evg->getRefId(),$uvg->getRefId()))
					);
	}
	// gev-patch end
}
