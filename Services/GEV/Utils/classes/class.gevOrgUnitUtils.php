<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Utilities for OrgUnits of Generali.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @author	Fabian Kochem <fabian.kochem@concepts-and-training.de>
* @version	$Id$
*/

require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
require_once("Modules/OrgUnit/classes/Types/class.ilOrgUnitType.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

class gevOrgUnitUtils {
	static $instances = array();
	static $venue_names = null;
	static $provider_names = null;

	protected function __construct($a_orgu_id) {
		global $ilDB, $ilias, $ilLog, $rbacreview, $rbacadmin, $rbacsystem;
		$this->db = &$ilDB;
		$this->ilias = &$ilias;
		$this->log = &$ilLog;
		$this->orgu_id = $a_orgu_id;
		$this->ref_id = null;
		$this->gev_set = gevSettings::getInstance();
		$this->amd = gevAMDUtils::getInstance();
		$this->rbacreview = &$rbacreview;
		$this->rbacadmin = &$rbacadmin;
		$this->rbacsystem = &$rbacsystem;
		
		$this->local_roles = null;
		$this->flipped_local_roles = null;
		$this->role_folder = null;
		$this->orgu_instance = null;
		$this->orgu_type = false;
	}
	
	public static function getInstance($a_orgu_id) {
		if (array_key_exists($a_orgu_id, self::$instances)) {
			return self::$instances[$a_orgu_id];
		}
		
		self::$instances[$a_orgu_id] = new gevOrgUnitUtils($a_orgu_id);
		return self::$instances[$a_orgu_id];
	}
	
	public static function getInstanceByImportId($a_import_id) {
		$id = ilObject::_lookupObjIdByImportId($a_import_id);
		if (!$id || ilObject::_lookupType($id) !== "orgu") {
			throw new Exception( "gevOrgUnitUtils::getInstanceByImportId: Could not find org unit "
								."with import id '$a_import_id'.");
		}
		return gevOrgUnitUtils::getInstance($id);
	}
	
	static public function createOrgType($a_gev_setting, $a_title, $a_desc) {
		require_once("Modules/OrgUnit/classes/Types/class.ilOrgUnitType.php");
		
		$type = new ilOrgUnitType();
		$type->setTitle($a_title, "de");
		$type->setDescription($a_title, "de");
		$type->setDefaultLang("de");
		$type->create();
		
		$id = $type->getId();
		gevSettings::getInstance()->set($a_gev_setting, $id);
	}
	
	static public function assignAMDRecordsToOrgUnitType($a_gev_setting, $a_amd_records) {
		$id = gevSettings::getInstance()->get($a_gev_setting);
		$type = new ilOrgUnitType($id);

		foreach ($a_amd_records as $amd_id) {
			$type->assignAdvancedMDRecord($amd_id);
		}
		$type->update();
	}
	
	static public function getVenueNames() {
		if (gevOrgUnitUtils::$venue_names !== null) {
			return gevOrgUnitUtils::$venue_names;
		}
		
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		require_once("Modules/OrgUnit/classes/Types/class.ilOrgUnitType.php");

		$type_name1 = gevSettings::getInstance()->get(gevSettings::ORG_TYPE_VENUE);
		$type1 = ilOrgUnitType::getInstance($type_name1);
		$ou_ids1 = $type1->getOrgUnitIDs(false);
		
		$evg_ref_id = gevOrgUnitUtils::getEVGOrgUnitRefId();
		$ou_ids2 = array();
		foreach (gevOrgUnitUtils::getAllChildren(array($evg_ref_id)) as $ids) {
			$ou_ids2[] = $ids["obj_id"];
		}

		$uvg_ref_id = gevOrgUnitUtils::getUVGOrgUnitRefId();
		$ou_ids3 = array();
		foreach (gevOrgUnitUtils::getOrgUnitsOneTreeLevelBelowRefId($uvg_ref_id) as $ids) {
			$ou_ids3[] = $ids["obj_id"];
		}

		$ou_info = gevAMDUtils::getInstance()->getTable(array_merge($ou_ids1, $ou_ids2, $ou_ids3), array(gevSettings::ORG_AMD_CITY => "city"));

		gevOrgUnitUtils::$venue_names = array();
		foreach ($ou_info as $values) {
			gevOrgUnitUtils::$venue_names[$values["obj_id"]] = $values["title"].", ".$values["city"];
		}
		
		asort(gevOrgUnitUtils::$venue_names,  SORT_NATURAL | SORT_FLAG_CASE);
		
		return gevOrgUnitUtils::$venue_names;
	}
	
	static public function getProviderNames() {
		if (gevOrgUnitUtils::$provider_names !== null) {
			return gevOrgUnitUtils::$provider_names;
		}
		
		require_once("Modules/OrgUnit/classes/Types/class.ilOrgUnitType.php");
		$id = gevSettings::getInstance()->get(gevSettings::ORG_TYPE_PROVIDER);

		$type = ilOrgUnitType::getInstance($id);
		$ou_ids = $type->getOrgUnitIDs(false);
		$ou_info = gevAMDUtils::getInstance()->getTable($ou_ids, array(gevSettings::ORG_AMD_CITY => "city"));

		gevOrgUnitUtils::$provider_names = array();
		foreach ($ou_info as $values) {
			gevOrgUnitUtils::$provider_names[$values["obj_id"]] = $values["title"].", ".$values["city"];
		}
		
		return gevOrgUnitUtils::$provider_names;
	}
	
	static public function getEVGOrgUnitRefId() {
		global $ilDB;
		
		$res = $ilDB->query("SELECT DISTINCT oref.ref_id "
						   ."  FROM object_data od "
						   ."  JOIN object_reference oref ON oref.obj_id = od.obj_id "
						   ." WHERE import_id = 'evg'"
						   ."   AND oref.deleted IS NULL"
						   ."   AND od.type = 'orgu'"
						   );
		if ($rec = $ilDB->fetchAssoc($res)) {
			return $rec["ref_id"];
		}
		throw new ilException("gevOrgUnitUtils::getEVGOrgUnitRefId: could not find org unit with import_id = 'evg'");
	}

	static public function getUVGOrgUnitRefId() {
		global $ilDB;
		
		$res = $ilDB->query("SELECT DISTINCT oref.ref_id "
						   ."  FROM object_data od "
						   ."  JOIN object_reference oref ON oref.obj_id = od.obj_id "
						   ." WHERE import_id = 'uvg'"
						   ."   AND oref.deleted IS NULL"
						   ."   AND od.type = 'orgu'"
						   );
		if ($rec = $ilDB->fetchAssoc($res)) {
			return $rec["ref_id"];
		}
		throw new ilException("gevOrgUnitUtils::getEVGOrgUnitRefId: could not find org unit with import_id = 'uvg'");
	}
	
	public function getOrgUnitAbove() {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");

		$tree = ilObjOrgUnitTree::_getInstance();
		$ref_id = gevObjectUtils::getRefId($this->orgu_id);
		$above_ref_id = $tree->getParent($ref_id);
		if (!$above_ref_id) {
			return null;
		}
		$above_obj_id = gevObjectUtils::getObjId($above_ref_id);
		
		return gevOrgUnitUtils::getInstance($above_obj_id);
	}

	public function getOrgUnitsOneTreeLevelBelow() {
		global $ilDB;

		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		$ref_id = gevObjectUtils::getRefId($this->orgu_id);

		$sql = "SELECT DISTINCT oref.ref_id, oref.obj_id"
			  ." FROM object_reference oref"
			  ." JOIN object_data od ON od.obj_id = oref.obj_id"
			  ." JOIN tree tr ON tr.parent = ".$ref_id
			  ." WHERE od.type = 'orgu' AND oref.ref_id = tr.child AND oref.deleted IS NULL";

		$res = $ilDB->query($sql);
		$first_child_org = array();
		while ($rec = $ilDB->fetchAssoc($res)) {
			$first_child_org[] = array( "ref_id" => $rec["ref_id"]
										 , "obj_id" => $rec["obj_id"]
										 );
		}

		return $first_child_org;
	}

	static public function getOrgUnitsOneTreeLevelBelowRefId($a_ref_id) {
		global $ilDB;


		$sql = "SELECT DISTINCT oref.ref_id, oref.obj_id"
			  ." FROM object_reference oref"
			  ." JOIN object_data od ON od.obj_id = oref.obj_id"
			  ." JOIN tree tr ON tr.parent = ".$a_ref_id
			  ." WHERE od.type = 'orgu' AND oref.ref_id = tr.child AND oref.deleted IS NULL";

		$res = $ilDB->query($sql);
		$first_child_org = array();
		while ($rec = $ilDB->fetchAssoc($res)) {
			$first_child_org[] = array( "ref_id" => $rec["ref_id"]
										 , "obj_id" => $rec["obj_id"]
										 );
		}

		return $first_child_org;
	}

	public function getOrgUnitsOneTreeLevelBelowWithTitle() {
		global $ilDB;

		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		$ref_id = gevObjectUtils::getRefId($this->orgu_id);

		$sql = "SELECT DISTINCT oref.ref_id, oref.obj_id, od.title"
			  ." FROM object_reference oref"
			  ." JOIN object_data od ON od.obj_id = oref.obj_id"
			  ." JOIN tree tr ON tr.parent = ".$ref_id
			  ." WHERE od.type = 'orgu' AND oref.ref_id = tr.child AND oref.deleted IS NULL";

		$res = $ilDB->query($sql);
		$first_child_org = array();
		while ($rec = $ilDB->fetchAssoc($res)) {
			$first_child_org[$rec["ref_id"]] = array( "ref_id" => $rec["ref_id"]
										 , "obj_id" => $rec["obj_id"]
										 , "title" => $rec["title"]
										 );
		}

		return $first_child_org;
	}
	
	public function getOrgUnitInstance() {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
		
		if ($this->orgu_instance === null) {
			try {
				$this->orgu_instance = new ilObjOrgUnit($this->orgu_id, false);
			}
			catch (Exception $e) {
				$this->orgu_instance = null;
			}
		}
		
		return $this->orgu_instance;
	}
	
	public function getRefId() {
		if ($this->ref_id === null) {
			require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
			$this->ref_id = gevObjectUtils::getRefId($this->orgu_id);
		}
		if (!$this->ref_id) {
			throw new Exception("Could not determine ref_id for org unit with id '".$this->orgu_id."'");
		}
		
		return $this->ref_id;
	}
	
	public function getObjId() {
		return $this->orgu_id;
	}
	
	public function getTitle() {
		$inst = $this->getOrgUnitInstance();
		if ($inst === null) {
			return "";
		}
		return $inst->getTitle();
	}
	
	public function getLongTitle() {
		$title = $this->getTitle();
		if ($title) {
			return $title.", ".$this->getCity();
		}
		return "";
	}

	public function getType() {
		// this might be used a lot during setting of properties
		if ($this->orgu_type !== false) {
			return $this->orgu_type;
		}
		
		$obj = $this->getOrgUnitInstance();
		$type_id = $obj->getOrgUnitTypeId();
		$this->orgu_type = null;
		
		foreach (gevSettings::$all_org_types as $org_type) {
			if ($this->gev_set->get($org_type) == $type_id) {
				$this->orgu_type = $org_type;
			}
		}
		
		return $this->orgu_type;
	}
	
	public function setType($a_type) {
		$this->checkTypeValid("setType", $a_type);
		
		$type_id = $this->gev_set->get($a_type);
		$this->getOrgUnitInstance()->setOrgUnitTypeId($type_id);
		$this->getOrgUnitInstance()->update();
		$this->orgu_type = $a_type;
	}
	
	public function checkTypeValid($a_caller, $a_type = null) {
		if ($a_type === null) {
			$a_type = $this->getType();
		}
		
		if ( ! in_array($a_type, gevSettings::$all_org_types) ) {
			throw new Exception("gevOrgUnitUtils::".$a_caller.": unknown type '".$a_type."'");
		}
	}
	
	public function isVenue() {
		return $this->getType() == gevSettings::ORG_TYPE_VENUE;
	}
	
	public function checkIsVenue($a_caller) {
		if (!$this->isVenue()) {
			throw new Exception("gevOrgUnitUtils::".$a_caller.": orgunit '".$this->orgu_id."' is no venue.");
		}
	}
	
	public function getStreet() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_STREET);
	}
	
	public function setStreet($a_street) {
		$this->checkTypeValid("setStreet");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_STREET, $a_street);
	}
	
	public function getHouseNumber() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_HOUSE_NUMBER);
	}
	
	public function setHouseNumber($a_house_number) {
		$this->checkTypeValid("setHouseNumber");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_HOUSE_NUMBER, $a_house_number);
	}
	
	public function getZipcode() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_ZIPCODE);
	}
	
	public function setZipcode($a_zipcode) {
		$this->checkTypeValid("setZipcode");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_ZIPCODE, $a_zipcode);
	}
	
	public function getCity() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_CITY);
	}
	
	public function setCity($a_city) {
		$this->checkTypeValid("setCity");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_CITY, $a_city);
	}
	
	public function getContactName() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_CONTACT_NAME);
	}
	
	public function setContactName($a_name) {
		$this->checkTypeValid("setContactName");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_CONTACT_NAME, $a_name);
	}
	
	public function getContactPhone() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_CONTACT_PHONE);
	}
	
	public function setContactPhone($a_phone) {
		$this->checkTypeValid("setContactPhone");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_CONTACT_PHONE, $a_phone);
	}
	
	public function getContactFax() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_CONTACT_FAX);
	}
	
	public function setContactFax($a_fax) {
		$this->checkTypeValid("setContactFax");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_CONTACT_FAX, $a_fax);
	}
	
	public function getContactEmail() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_CONTACT_EMAIL);
	}
	
	public function setContactEmail($a_email) {
		$this->checkTypeValid("setContactEmail");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_CONTACT_EMAIL, $a_email);
	}
	
	public function getHomepage() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_HOMEPAGE);
	}
	
	public function setHomepage($a_homepage) {
		$this->checkTypeValid("setHomepage");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_HOMEPAGE, $a_homepage);
	}
	
	public function getLocation() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_LOCATION);
	}
	
	public function setLocation($a_loc) {
		$this->checkIsVenue("setLocation");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_LOCATION, $a_loc);
	}
	
	public function getCostsPerAccomodation() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_COSTS_PER_ACCOM);
	}
	
	public function setCostsPerAccomodation($a_costs) {
		$this->checkIsVenue("setCostsPerAccomodation");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_COSTS_PER_ACCOM, $a_costs);
	}
	
	public function getCostsPerBreakfast() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_COSTS_BREAKFAST);
	}
	
	public function setCostsPerBreakfast($a_costs) {
		$this->checkIsVenue("setCostsPerBreakfast");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_COSTS_BREAKFAST, $a_costs);
	}
	
	public function getCostsPerLunch() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_COSTS_LUNCH);
	}
	
	public function setCostsPerLunch($a_costs) {
		$this->checkIsVenue("setCostsPerLunch");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_COSTS_LUNCH, $a_costs);
	}
	
	public function getCostsPerCoffee() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_COSTS_COFFEE);
	}
	
	public function setCostsPerCoffee($a_costs) {
		$this->checkIsVenue("setCostsPerCoffee");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_COSTS_PER_COFFEE, $a_costs);
	}
	
	public function getCostsPerDinner() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_COSTS_PER_DINNER);
	}
	
	public function setCostsPerDinner($a_costs) {
		$this->checkIsVenue("setCostsPerDinner");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_COSTS_PER_DINNER, $a_costs);
	}
	
	public function getCostsPerDailyCatering() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_COSTS_FOOD);
	}
	
	public function setCostsPerDailyCatering($a_costs) {
		$this->checkIsVenue("setCostsPerDailyCatering");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_COSTS_FOOD, $a_costs);
	}
	
	public function getCostsOfHotel() {
		return $this->amd->getField($this->orgu_id, gevSettings::VENUE_AMD_COSTS_HOTEL);
	}
	
	public function setCostsOfHotel($a_costs) {
		$this->checkIsVenue("setCostsOfHotel");
		$this->amd->setField($this->orgu_id, gevSettings::VENUE_AMD_COSTS_HOTEL, $a_costs);
	}
	
	public function getCostsAllInclusive() {
		return $this->amd->getField($this->orgu_id, gevSettings::VENUE_AMD_ALL_INCLUSIVE_COSTS);
	}
	
	public function setCostsAllInclusive($a_costs) {
		$this->checkIsVenue("setCostsAllInclusive");
		$this->amd->setField($this->orgu_id, gevSettings::VENUE_AMD_ALL_INCLUSIVE_COSTS, $a_costs);
	}

	public function getFinancialAccount() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_FINANCIAL_ACCOUNT);
	}
	
	public function setFinancialAccount($a_finaccount) {
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_FINANCIAL_ACCOUNT, $a_finaccount);
	}


	// Helpers and Caching for role related stuff
	
	public function getRoleFolder() {
		if ($this->role_folder === null) {
			$rolf_data = gevRoleUtils::getRbacReview()->getRoleFolderOfObject($this->getRefId());
			$this->role_folder  = $this->ilias->obj_factory->getInstanceByRefId($rolf_data["ref_id"]);
		}
		
		return $this->role_folder;
	}
	
	public function getLocalRoles() {
		if ($this->local_roles === null) {
			$review = gevRoleUtils::getRbacReview();
			$role_ids = $review->getLocalRoles($this->getRefId());
			
			$res = $this->db->query("SELECT obj_id, title FROM object_data ".
									" WHERE ".$this->db->in("obj_id", $role_ids, false, "integer")
									);
			
			$this->local_roles = array();
			while($rec = $this->db->fetchAssoc($res)) {
				$this->local_roles[$rec["obj_id"]] = $rec["title"];
			}
		}
		
		return $this->local_roles;
	}
	
	public function getFlippedLocalRoles() {
		if ($this->flipped_local_roles === null) {
			$this->flipped_local_roles = array_flip($this->getLocalRoles());
		}
		
		return $this->flipped_local_roles;
	}

	// queries
	
	static public function getEmployeesIn($a_ref_ids) {
		global $ilDB;
		
		$res = $ilDB->query(
			 "SELECT ua.usr_id"
			."  FROM rbac_ua ua"
			."  JOIN tree tr ON ".$ilDB->in("tr.parent", $a_ref_ids, false, "integer")
			."  JOIN rbac_fa fa ON fa.parent = tr.child"
			."  JOIN object_data od ON od.obj_id = fa.rol_id"
			." WHERE ua.rol_id = fa.rol_id"
			."   AND od.title LIKE 'il_orgu_employee_%'"
			);
		$ret = array();
		while ($rec = $ilDB->fetchAssoc($res)) {
			$ret[] = $rec["usr_id"];
		}
		return $ret;
	}

	static public function getSuperiorsIn($a_ref_ids) {
		global $ilDB;
		
		$sql = "SELECT ua.usr_id"
			."  FROM rbac_ua ua"
			."  JOIN tree tr ON ".$ilDB->in("tr.parent", $a_ref_ids, false, "integer")
			."  JOIN rbac_fa fa ON fa.parent = tr.child"
			."  JOIN object_data od ON od.obj_id = fa.rol_id"
			." WHERE ua.rol_id = fa.rol_id"
			."   AND od.title LIKE 'il_orgu_superior_%'";

		$res = $ilDB->query(
			 "SELECT ua.usr_id"
			."  FROM rbac_ua ua"
			."  JOIN tree tr ON ".$ilDB->in("tr.parent", $a_ref_ids, false, "integer")
			."  JOIN rbac_fa fa ON fa.parent = tr.child"
			."  JOIN object_data od ON od.obj_id = fa.rol_id"
			." WHERE ua.rol_id = fa.rol_id"
			."   AND od.title LIKE 'il_orgu_superior_%'"
			);
		$ret = array();
		while ($rec = $ilDB->fetchAssoc($res)) {
			$ret[] = $rec["usr_id"];
		}
		return $ret;
	}
	
	// Get everyone in the given org-units.
	static public function getAllPeopleIn($a_ref_ids) {
		global $ilDB;
		
		$res = $ilDB->query(
			 "SELECT ua.usr_id"
			."  FROM rbac_ua ua"
			."  JOIN tree tr ON ".$ilDB->in("tr.parent", $a_ref_ids, false, "integer")
			."  JOIN rbac_fa fa ON fa.parent = tr.child"
			." WHERE ua.rol_id = fa.rol_id"
			);
		$ret = array();
		while ($rec = $ilDB->fetchAssoc($res)) {
			$ret[] = $rec["usr_id"];
		}
		return array_unique($ret);
	}
	
	// Get everyone who counts as Trainer in the given Org-Units
	static public function getTrainersIn($a_ref_ids) {
		global $ilDB;
		
		$res = $ilDB->query(
			 "SELECT ua.usr_id, pa.ops_id"
			."  FROM rbac_ua ua"
			."  JOIN tree tr ON ".$ilDB->in("tr.parent", $a_ref_ids, false, "integer")
			."  JOIN rbac_fa fa ON fa.parent = tr.child"
			."  JOIN rbac_pa pa ON pa.rol_id = fa.rol_id"
			." WHERE ua.rol_id = fa.rol_id"
			);
		
		$op_id = ilRbacReview::_getOperationIdByName("tep_is_tutor");
		
		$ret = array();
		while ($rec = $ilDB->fetchAssoc($res)) {
			$ops = unserialize($rec["ops_id"]);
			if (in_array($op_id, $ops)) {
				$ret[] = $rec["usr_id"];
			}
		}
		return array_unique($ret);
	}

	static public function getTitleByRefId($a_ref_id) {
		global $ilDB;
		$sql = "SELECT title FROM object_data od JOIN object_reference obr ON od.obj_id = obr.obj_id WHERE obr.ref_id = ".$a_ref_id;
		$res = $ilDB->query($sql);

		if($ilDB->numRows($res) > 0) {
			$row = $ilDB->fetchAssoc($res);
			return $row["title"];
		}

		return "";
	}
	
	public function getChildren() {
		global $tree;
		return $tree->getChildsByType($this->getRefId(), "orgu");
	}
	
	public function deleteChild($ref_id) {
		require_once("Services/Repository/classes/class.ilRepUtil.php");
		$obj_id = ilObject::_lookupObjectId($ref_id);
		unset(self::$instances[$obj_id]);
		ilRepUtil::deleteObjects($this->getRefId(), array($ref_id));
	}
	
	public function getUsers() {
		return self::getAllPeopleIn(array($this->getRefId()));
	}
	
	public function purgeEmptyChildren($min_amount_users = 1, $do_not_purge_ref_ids = array()) {
		foreach ($this->getChildren() as $child) {
			if (in_array($child["ref_id"], $do_not_purge_ref_ids)) {
				continue;
			}
			$utils = gevOrgUnitUtils::getInstance($child["obj_id"]);
			$utils->purgeEmptyChildren($min_amount_users, $do_not_purge_ref_ids);
			if (count($utils->getChildren()) == 0 && count($utils->getUsers()) < $min_amount_users) {
				$this->deleteChild($child["ref_id"]);
			}
		}
	}
	
	// Get all orgunits below the given ones. Returns ref_ids and obj_ids.
	static public function getAllChildren($a_ref_ids) {
		global $ilDB;
		
		$res = $ilDB->query(
			 "SELECT DISTINCT od.obj_id obj_id, c.child ref_id "
			." FROM tree p"
			." RIGHT JOIN tree c ON c.lft > p.lft AND c.rgt < p.rgt AND c.tree = p.tree"
			." LEFT JOIN object_reference oref ON oref.ref_id = c.child"
			." LEFT JOIN object_data od ON od.obj_id = oref.obj_id"
			." WHERE ".$ilDB->in("p.child", $a_ref_ids, false, "integer")
			."   AND od.type = 'orgu' AND oref.deleted IS NULL"
			);
	
		$ret = array();
		while($rec = $ilDB->fetchAssoc($res)) {
			$ret[] = $rec;
		}
		return $ret;
	}
	
	static public function getAllChildrenTitles($a_ref_ids) {
		global $ilDB;
		
		$res = $ilDB->query(
			 "SELECT DISTINCT od.obj_id obj_id, od.title title "
			." FROM tree p"
			." RIGHT JOIN tree c ON c.lft > p.lft AND c.rgt < p.rgt AND c.tree = p.tree"
			." LEFT JOIN object_reference oref ON oref.ref_id = c.child"
			." LEFT JOIN object_data od ON od.obj_id = oref.obj_id"
			." WHERE ".$ilDB->in("p.child", $a_ref_ids, false, "integer")
			."   AND od.type = 'orgu' AND oref.deleted IS NULL"
			);
	
		$ret = array();
		while($rec = $ilDB->fetchAssoc($res)) {
			$ret[$rec["obj_id"]] = $rec["title"];
		}
		return $ret;
	}

	static function getAllChildrenObjIdTitles($a_ref_ids) {
		global $ilDB;
		
		$res = $ilDB->query(
			 "SELECT DISTINCT od.title title "
			." FROM tree p"
			." RIGHT JOIN tree c ON c.lft > p.lft AND c.rgt < p.rgt AND c.tree = p.tree"
			." LEFT JOIN object_reference oref ON oref.ref_id = c.child"
			." LEFT JOIN object_data od ON od.obj_id = oref.obj_id"
			." WHERE ".$ilDB->in("p.child", $a_ref_ids, false, "integer")
			."   AND od.type = 'orgu' AND oref.deleted IS NULL"
			);
	
		$ret = array();
		while($rec = $ilDB->fetchAssoc($res)) {
			$ret[] = $rec["title"];
		}
		return $ret;
	}
	// assignment of users to the org-unit
	
	public function assignUser($a_user_id, $a_role_title) {
		if ($a_role_title == "Mitarbeiter") {
			$role_title = "il_orgu_employee_".$this->getRefId();
		}
		else if ($a_role_title == "Vorgesetzter") {
			$role_title = "il_orgu_superior_".$this->getRefId();	
		}
		else {
			$role_title = "Org-".$a_role_title;
		}

		$roles = $this->getFlippedLocalRoles();
		
		if (!array_key_exists($role_title, $roles)) {
			$this->log->write("gevOrgUnitUtils::assignUser: Could not find role with name ".$role_title.
							  " in Org-Unit with ref_id ".$this->getRefId());
			return;
		}
		
		gevRoleUtils::getRbacAdmin()->assignUser($roles[$role_title], $a_user_id);
	}
	
	public function deassignUser($a_user_id, $a_role_title) {
		if ($a_role_title == "Mitarbeiter") {
			$role_title = "il_orgu_employee_".$this->getRefId();
		}
		else if ($a_role_title == "Vorgesetzter") {
			$role_title = "il_orgu_superior_".$this->getRefId();	
		}
		else {
			$role_title = "Org-".$a_role_title;
		}

		$roles = $this->getFlippedLocalRoles();
		
		if (!array_key_exists($role_title, $roles)) {
			$this->log->write("gevOrgUnitUtils::assignUser: Could not find role with name ".$role_title.
							  " in Org-Unit with ref_id ".$this->getRefId());
			return;
		}
		
		gevRoleUtils::getRbacAdmin()->deassignUser($roles[$role_title], $a_user_id);
	}
	
	// setting of permissions
	public function grantPermissionsFor($a_role, $a_permissions) {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		
		$ou = $this->getOrgUnitInstance();
		$ref_id = gevObjectUtils::getRefId($ou->getId());
		$ou->setRefId($ref_id);

		if (!is_numeric($a_role)) {
			if ($a_role == "superior") {
				$role = $ou->getSuperiorRole();
			}
			elseif ($a_role == "employee") {
				$role = $ou->getEmployeeRole();
			}
			else {
				$role = gevRoleUtils::getInstance()->getRoleIdByName($a_role);
				if (!$role) {
					throw new Exception("gevOrgUnitUtils::grantPermissionFor: unknown role name '".$a_role);
				}
			}
		}
		else {
			$role = (int)$a_role;
		}
		
		$cur_ops = $this->rbacreview->getRoleOperationsOnObject($role, $ref_id);
		$grant_ops = ilRbacReview::_getOperationIdsByName($a_permissions);
		$new_ops = array_unique(array_merge($grant_ops, $cur_ops));
		$this->rbacadmin->revokePermission($ref_id, $role);
		$this->rbacadmin->grantPermission($role, $new_ops, $ref_id);
	}
	
	static public function grantPermissionsRecursivelyFor($a_start_ref, $a_role_name, $a_permissions) {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		$obj_id = gevObjectUtils::getObjId($a_start_ref);
		$ou_utils = gevOrgUnitUtils::getInstance($obj_id);

		$ou_utils->grantPermissionsFor($a_role_name, $a_permissions);
		$children = self::getAllChildren(array($a_start_ref));
		foreach($children as $child) {
			$ou_utils = gevOrgUnitUtils::getInstance($child["obj_id"]);
			$ou_utils->grantPermissionsFor($a_role_name, $a_permissions);
		}
	}
	
	// assignment and deassignment of standard org unit roles for the default org
	// units of the Generali.
	
	// by convention, the roles for org-units start with "Org-"
	static public function getRoleTemplatesForDefaultOrgUnits() {
		global $ilDB;
		
		$res = $ilDB->query("SELECT obj_id, title FROM object_data ".
							" WHERE title LIKE 'Org-%' ".
							"   AND type = 'rolt'"
							);
		$ret = array();
		while ($rec = $ilDB->fetchAssoc($res)) {
			$ret[$rec["obj_id"]] = $rec["title"];
		}
		
		return $ret;
	}
	
	public function hasRolesForDefaultOrgUnits() {
		$cur = $this->getLocalRoles();
		$to = $this->getRoleTemplatesForDefaultOrgUnits();

		foreach ($to as $id => $title) {
			if (!in_array($title, $cur)) {
				return false;
			}
		}
		return true;
	}
	
	public function addRolesForDefaultOrgUnits() {
		$review = gevRoleUtils::getRbacReview();
		$admin = gevRoleUtils::getRbacAdmin();
		$ref_id = $this->getRefId();
		$folder  = $this->getRoleFolder();
		
		$to = $this->getRoleTemplatesForDefaultOrgUnits();
		
		foreach ($to as $id => $title) {
			$role = $folder->createRole($title, "");
			$admin->copyRoleTemplatePermissions($id, ROLE_FOLDER_ID, $folder->getRefId(), $role->getId());
			$ops = $review->getOperationsOfRole($role->getId(), "orgu", $folder->getRefId());
			$admin->grantPermission($role->getId(), $ops, $ref_id);
		}
	}
	
	public function removeRolesForDefaultOrgUnits() {
		$admin = gevRoleUtils::getRbacAdmin();
		
		$cur = array_flip($this->getLocalRoles());
		$to = $this->getRoleTemplatesForDefaultOrgUnits();
		
		foreach ($to as $id => $title) {
			if (!array_key_exists($title, $cur)) {
				continue;
			}
			
			$id = $cur[$title];
			$admin->deassignUsers($id);
			$admin->deleteLocalRole($id);
		}
	}
	
	
	static public function createOrgUnit($import_id, $title, $parent_import_id){
		global $ilDB;
		
		$res = $ilDB->query("SELECT ref_id "
						   ."  FROM object_reference oref"
						   ."  JOIN object_data od ON od.obj_id = oref.obj_id"
						   ."  WHERE oref.deleted IS NULL"
						   ."    AND od.import_id = ".$ilDB->quote($parent_import_id, "text")
						   ."    AND od.type = 'orgu'"
						   );
		
		$rec = $ilDB->fetchAssoc($res);
		if (!$rec) {
			throw new Exception("gevOrgUnitUtils::createOrgUnit: Could not find org unit with import id $parent_import_id");
		}
		
		$orgu = new ilObjOrgUnit();
		$orgu->setTitle($title);
		$orgu->create();
		$orgu->createReference();
		$orgu->setImportId($import_id);
		$orgu->update();
		
		$id = $orgu->getId();
		$refId = $orgu->getRefId();
		
		$orgu->putInTree($rec["ref_id"]);
		$orgu->initDefaultRoles();
		
		$orgutils = gevOrgUnitUtils::getInstance($id);
		$orgutils->setType(gevSettings::ORG_TYPE_DEFAULT);
		
		return $orgu->getId();
	}

	public function _getAllSuperiors() {
		global $ilDB;

		$sql = "SELECT DISTINCT ua.usr_id
			  FROM rbac_ua ua
			  JOIN rbac_fa fa ON ua.rol_id = fa.rol_id
			  JOIN object_data od ON od.obj_id = fa.rol_id
			  JOIN usr_data ud ON ua.usr_id = ud.usr_id
			 WHERE od.title LIKE 'il_orgu_superior_%'";

 		$res = $ilDB->query($sql);
 		$ret = array();
 		while($row = $ilDB->fetchAssoc($res)) {
			$ret[] = $row["usr_id"];
		}

		return $ret;
	}

	static public function moveUsers($org_unit_initial_id, $org_unit_final_id) {
		global $rbacreview;

		$org_unit_initial = new ilObjOrgUnit($org_unit_initial_id);
		$oui_employee_role_id = $org_unit_initial->getEmployeeRole();
		$oui_superior_role_id = $org_unit_initial->getSuperiorRole();
		$employees_initial = $rbacreview->assignedUsers($oui_employee_role_id);
		$superiors_initial = $rbacreview->assignedUsers($oui_superior_role_id);
		$org_unit_final = new ilObjOrgUnit($org_unit_final_id);
		$org_unit_final->assignUsersToEmployeeRole($employees_initial);
		$org_unit_final->assignUsersToSuperiorRole($superiors_initial);
		
		foreach ($employees_initial as $usr_id) {
			$org_unit_initial->deassignUserFromEmployeeRole($usr_id);

		}

		foreach ($superiors_initial as $usr_id) {
			$org_unit_initial->deassignUserFromSuperiorRole($usr_id);

		}
	}

	public static function getSuperiorsOfUser($user_id) {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

		$tree = ilObjOrgUnitTree::_getInstance();
		$sups = array();
		$look_above_orgus = array();
		$orgus = $tree->getOrgUnitOfUser($user_id);

		foreach( $orgus as $ref_id ) {
			$employees = $tree->getEmployees($ref_id);
			$superiors = $tree->getSuperiors($ref_id);

			if(in_array($user_id,$employees)) {
				$sups = array_merge($sups,$superiors);
			}
		}
		foreach($orgus as $org) {

			$org_aux = $tree->getParent($org);

			while ($org_aux != ROOT_FOLDER_ID) {
				$sups = array_merge($sups,$tree->getSuperiors($org_aux));
				$org_aux = $tree->getParent($org_aux);
			}
		}
		$sups = array_unique($sups);
		return gevUserUtils::removeInactiveUsers($sups);
	}

	static public function getBDOf($org_ref_id) {
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		$ou_tree = ilObjOrgUnitTree::_getInstance();

		$parent = $ou_tree->getParent($org_ref_id);

		if($parent == ilObjOrgUnit::getRootOrgRefId()) {
			throw new Exception("NOT IN BD!");
		}

		if(self::isBD(ilObject::_lookupObjId($parent))) {
			return $parent;
		}

		return gevOrgUnitUtils::getBDOf($parent);
	}

	static public function getBDByName($bd_name, $org_ref_id = null) {
		global $tree, $ilLog;

		if(!$org_ref_id) {
			$org_ref_id = self::getUVGOrgUnitRefId();
		}

		$children = $tree->getChilds($org_ref_id);
		foreach ($children as $child) {
			if ($child["type"] == "orgu" && self::isBD($child["obj_id"]) && ilObject::_lookupTitle($child["obj_id"]) == $bd_name) {
				return $child["ref_id"];
			}
		}

		return null;
	}

	static protected function isBD($obj_id) {
		global $ilLog;
		$orgutils = gevOrgUnitUtils::getInstance($obj_id);

		if($orgutils->getType() == gevSettings::TYPE_ID_ORG_UNIT_TYPE_BD) {
			return true;
		}

		return false;
	}
}

class gevOrgUnitCache {
	protected $root_id;
	protected $cache = array();
	private $tree;

	public function __construct($root_id) {
		$this->root_id = $root_id;
		$this->tree = ilObjOrgUnitTree::_getInstance();
	}

	public function index() {
		$children = $this->tree->getAllChildren($this->root_id);
		foreach ($children as $child_id) {
			$child_obj = new ilObjOrgUnit($child_id);
			$import_id = $child_obj->getImportId();
			$this->addToCache($child_id, $import_id);
		}
	}

	public function addToCache($ref_id, $import_id) {
		if ($import_id === null) {
			$import_id = 'root';
		}

		if ($this->isImportIdInCache($import_id)) {
			die('Duplicate Import ID: ' . $import_id);
		}

		$this->cache[$import_id] = $ref_id;
	}

	public function isImportIdInCache($import_id) {
		return array_key_exists($import_id, $this->cache);
	}

	public function getRefIdByImportId($import_id) {
		return $this->cache[$import_id];
	}
}